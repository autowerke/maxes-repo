<?php
require_once ("../../../eat.php");



class BudgetInsightHelper 
{
    

	/**
     * HELPER 1: Get Macro Data (KPI & Master Chart)
     * Mengambil ringkasan dari tabel budget.
     */
    public static function getMacroData($budgetNums = []) 
    {
        $sql = "SELECT 
                    budgetnum, 
                    COALESCE(totalbudgetamount, 0) as budget_limit, 
                    COALESCE(totalcommittedamount, 0) as committed, 
                    COALESCE(totalactualexpense, 0) as actual, 
                    COALESCE(totalremainingamount, 0) as remaining 
                FROM budget";
        
        $params = [];
        if (!empty($budgetNums)) {
            // Gunakan placeholders ? untuk keamanan (Anti SQL Injection)
            $placeholders = rtrim(str_repeat('?,', count($budgetNums)), ',');
            $sql .= " WHERE budgetnum IN ($placeholders)";
            $params = $budgetNums;
        }
        
        $sql .= " ORDER BY budgetnum ASC";

        // fetchAll transaction-aware
        $budgets = Set::fetchAll($sql, $params);

        // Inisialisasi struktur output sesuai kontrak JSON
        $result = [
            "global_kpi" => [
                "total_budget" => 0,
                "total_committed" => 0,
                "total_actual" => 0,
                "total_remaining" => 0
            ],
            "master_chart" => [
                "categories" => [], // Isi: budgetnum
                "series" => [
                    "budget_limit" => [],
                    "committed" => [],
                    "actual" => []
                ]
            ],
            "budget_list" => [] // Untuk kebutuhan looping di main function nanti
        ];

        foreach ($budgets as $row) {
            $bNum = $row['budgetnum'];
            $bLimit = (float)$row['budget_limit'];
            $bComm = (float)$row['committed'];
            $bAct = (float)$row['actual'];
            $bRem = (float)$row['remaining'];

            // 1. Akumulasi untuk Global KPI Scorecard
            $result["global_kpi"]["total_budget"] += $bLimit;
            $result["global_kpi"]["total_committed"] += $bComm;
            $result["global_kpi"]["total_actual"] += $bAct;
            $result["global_kpi"]["total_remaining"] += $bRem;

            // 2. Data untuk ECharts Bar Chart (Master Chart)
            $result["master_chart"]["categories"][] = $bNum;
            $result["master_chart"]["series"]["budget_limit"][] = $bLimit;
            $result["master_chart"]["series"]["committed"][] = $bComm;
            $result["master_chart"]["series"]["actual"][] = $bAct;

            // Simpan daftar budgetnum untuk drill-down
            $result["budget_list"][] = $bNum;
        }

        return $result;
    }

    /**
     * HELPER 2: Get PO Type Breakdown
     * Chart Donat: Distribusi kategori belanja berdasarkan potype.
     */
	public static function getPoTypeBreakdown($budgetNum)
	{
		$sql = "SELECT 
					COALESCE(potype, 'UNCLASSIFIED') AS type,
					SUM(COALESCE(totalbasecost, 0))  AS amount
				FROM po
				WHERE budgetnum = ?
				AND status NOT IN ('cancel', 'hold')
				GROUP BY potype";

		$rows = Set::fetchAll($sql, [$budgetNum]);

		$chartData = [];
		foreach ($rows as $row) {
			$chartData[] = [
				"name"  => $row['type'],
				"value" => (float)$row['amount']
			];
		}

		return $chartData;
	}

	/**
     * HELPER 3: Get Execution MLST
     * Breakdown biaya aktual (Material, Labor, Service, Tools) per Work Order.
     */
	public static function getExecutionMLST($budgetNum)
	{
		$sqlWo = "SELECT 
					w.wonum,
					w.budgetnum,
					w.budgetlinenum,
					bl1.budgetlinecode as bl_code1,
					bl1.description    as bl_desc1,
					w.budgetlinenum2,
					bl2.budgetlinecode as bl_code2,
					bl2.description    as bl_desc2
				FROM workorder w
				LEFT JOIN budgetline bl1 ON w.budgetlinenum = bl1.budgetlinenum
				LEFT JOIN budgetline bl2 ON w.budgetlinenum2 = bl2.budgetlinenum
				WHERE w.budgetnum = ?
					AND w.wonum IS NOT NULL
					AND w.wonum != ''";

		$woRowsRaw = Set::fetchAll($sqlWo, [$budgetNum]);

		$result = [
			"categories" => [], 
			"metadata"   => [], 
			"series"     => [
				"material" => [], "labor" => [], "service" => [], "tools" => []
			]
		];

		if (empty($woRowsRaw)) return $result;

		// ✅ 1: Paksa Unik di level PHP
		// Buat array baru dengan WONUM sebagai KEY agar otomatis menimpa jika ada duplikat
		$woRows = [];
		foreach ($woRowsRaw as $row) {
			$woRows[$row['wonum']] = $row;
		}

		// ✅ 2: woList unik
		$woList = array_keys($woRows); 
		$result["categories"] = $woList;

		foreach ($woRows as $wonum => $row) {
			$isUsingAlt = (!empty($row['budgetlinenum2']) && $row['budgetlinenum2'] !== '-');
			
			$result["metadata"][$wonum] = [
				"is_alternate" => $isUsingAlt,
				"active_code"  => $isUsingAlt ? $row['bl_code2'] : $row['bl_code1'],
				"active_desc"  => $isUsingAlt ? $row['bl_desc2'] : $row['bl_desc1'],
				"ref_code"     => $isUsingAlt ? $row['bl_code1'] : null,
				"ref_desc"     => $isUsingAlt ? $row['bl_desc1'] : null
			];
		}

		// Ambil data biaya menggunakan woList yang sudah unik
		$matData = (new Set())->useTransactionManager()->MboSetRemote("matusetrans")
			->whereIn("wonum", $woList)->MboRemote("wonum, SUM(linecost) as cost")
			->group("wonum")->getMboSet()->fetchDataAssoc('wonum');

		$labData = (new Set())->useTransactionManager()->MboSetRemote("labortrans")
			->whereIn("wonum", $woList)->MboRemote("wonum, SUM(linecost) as cost")
			->group("wonum")->getMboSet()->fetchDataAssoc('wonum');

		$srvData = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")
			->whereIn("wonum", $woList)->MboRemote("wonum, SUM(linecost) as cost")
			->group("wonum")->getMboSet()->fetchDataAssoc('wonum');

		$tolData = (new Set())->useTransactionManager()->MboSetRemote("tooltrans")
			->whereIn("wonum", $woList)->MboRemote("wonum, SUM(linecost) as cost")
			->group("wonum")->getMboSet()->fetchDataAssoc('wonum');

		foreach ($woList as $wo) {
			$result["series"]["material"][] = (float)($matData[$wo]['cost'] ?? 0);
			$result["series"]["labor"][]    = (float)($labData[$wo]['cost'] ?? 0);
			$result["series"]["service"][]  = (float)($srvData[$wo]['cost'] ?? 0);
			$result["series"]["tools"][]    = (float)($tolData[$wo]['cost'] ?? 0);
		}

		return $result;
	}

	public static function getExecutionMLSTDetail($budgetNum)
	{
		// Ambil daftar WO langsung dari workorder.budgetnum
		$sqlWo = "SELECT DISTINCT wonum
				FROM workorder
				WHERE budgetnum = ?
					AND wonum IS NOT NULL
					AND wonum != ''";

		$woRows = Set::fetchAll($sqlWo, [$budgetNum]);

		if (empty($woRows)) return [];

		$woList       = array_column($woRows, 'wonum');
		$placeholders = rtrim(str_repeat('?,', count($woList)), ',');

		$sql = "SELECT 
					mt.itemnum,
					mt.wonum,
					mt.quantity         AS issuedqty,
					COALESCE(ir.quantity, 0) AS reservedqty,
					ir.status           AS reservedstatus,
					COALESCE(vb.curbal, 0)   AS current_balance,
					i.issueunit,
					mt.unitcost,
					mt.linecost,
					mt.batchnum,
					mt.requestedby
				FROM matusetrans mt
				LEFT JOIN item i
					ON mt.itemnum = i.itemnum
				LEFT JOIN invreserve ir
					ON mt.itemnum = ir.itemnum
				AND mt.wonum   = ir.wonum
				LEFT JOIN v_invbalance vb
					ON mt.itemnum = vb.itemnum
				WHERE mt.wonum IN ($placeholders)
				ORDER BY mt.wonum, mt.itemnum";

		$rows = Set::fetchAll($sql, $woList);

		return array_map(function ($row) {
			return [
				"wonum"           => $row['wonum']           ?: '-',
				"itemnum"         => $row['itemnum']          ?: '-',
				"issuedqty"       => (float)$row['issuedqty'],
				"reservedqty"     => (float)$row['reservedqty'],
				"reservedstatus"  => $row['reservedstatus']   ?: null,
				"current_balance" => (float)$row['current_balance'],
				"issueunit"       => $row['issueunit']        ?: '-',
				"unitcost"        => (float)$row['unitcost'],
				"linecost"        => (float)$row['linecost'],
				"batchnum"        => $row['batchnum']         ?: '-',
				"requestedby"     => $row['requestedby']      ?: '-'
			];
		}, $rows);
	}

	public static function getProductionLog($budgetNum)
	{
		
		$sqlWo = "SELECT DISTINCT wonum
				FROM workorder
				WHERE budgetnum = ?";

		$woRows = Set::fetchAll($sqlWo, [$budgetNum]);

		$result = [
			"chart" => [
				"categories" => [],
				"series"     => [
					"quantity" => [],
					"linecost" => []
				]
			],
			"kpi" => [
				"total_quantity" => 0,
				"total_cost"     => 0,
				"total_entries"  => 0,
				"unique_items"   => 0
			],
			"table" => []
		];

		if (empty($woRows)) return $result;

		$woList       = array_column($woRows, 'wonum');
		$placeholders = rtrim(str_repeat('?,', count($woList)), ',');

		$sqlLog = "SELECT
						itemnum,
						productiondate,
						quantity,
						unitcost,
						linecost,
						enterdate,
						enterby,
						wonum
				FROM productionlog
				WHERE wonum IN ($placeholders)
				ORDER BY productiondate DESC, enterdate DESC";

		$logs = Set::fetchAll($sqlLog, $woList);

		if (empty($logs)) return $result;

		$result["kpi"]["total_entries"]  = count($logs);
		$result["kpi"]["total_quantity"] = array_sum(array_column($logs, 'quantity'));
		$result["kpi"]["total_cost"]     = array_sum(array_column($logs, 'linecost'));
		$result["kpi"]["unique_items"]   = count(array_unique(array_column($logs, 'itemnum')));

		$byItem = [];
		foreach ($logs as $row) {
			$item = $row['itemnum'] ?: 'UNKNOWN';
			if (!isset($byItem[$item])) {
				$byItem[$item] = ['quantity' => 0, 'linecost' => 0];
			}
			$byItem[$item]['quantity'] += (float)$row['quantity'];
			$byItem[$item]['linecost'] += (float)$row['linecost'];
		}

		arsort($byItem);

		foreach ($byItem as $item => $val) {
			$result["chart"]["categories"][]        = $item;
			$result["chart"]["series"]["quantity"][] = $val['quantity'];
			$result["chart"]["series"]["linecost"][] = $val['linecost'];
		}

		$result["table"] = array_map(function ($row) {
			return [
				"itemnum"        => $row['itemnum']        ?: '-',
				"productiondate" => $row['productiondate'] ?: null,
				"quantity"       => (float)$row['quantity'],
				"unitcost"       => (float)$row['unitcost'],
				"linecost"       => (float)$row['linecost'],
				"enterdate"      => $row['enterdate']      ?: null,
				"enterby"        => $row['enterby']        ?: '-',
				"wonum"          => $row['wonum']          ?: '-'
			];
		}, $logs);

		return $result;
	}

    /**
     * HELPER 4: Get Sales Alignment & SO Status
     * Perbandingan Planned (SQ) vs Actual Price (SO) dan Status Piutang.
     */
    public static function getSalesAlignmentAndStatus($budgetNum) 
    {
        // 1. Cari SO yang terlibat
        $sqlSo = "SELECT DISTINCT w.sonum 
                  FROM workorder w 
                  JOIN poline l ON w.wonum = l.wonum 
                  JOIN po p ON l.ponum = p.ponum 
                  WHERE p.budgetnum = ? AND w.sonum IS NOT NULL AND w.sonum != ''";

        $soRows = Set::fetchAll($sqlSo, [$budgetNum]);

        $result = [
            "sales_alignment" => [
                "categories" => [],
                "series" => ["sq_planned_cost" => [], "sq_price" => [], "so_actual_price" => []]
            ],
            "so_payment_status" => [
                "kpi_total_so" => 0, "kpi_unpaid" => 0, "kpi_paid" => 0,
                "chart_data" => [] // Untuk Pie Chart AR
            ]
        ];

        if (empty($soRows)) return $result;

        $soList = array_column($soRows, 'sonum');

        // 2. Ambil KPI Piutang (AR) secara agregat
        $sqlAr = "SELECT 
                    SUM(totalprice) as t_so, 
                    SUM(unpaidprice) as t_unpaid, 
                    SUM(paidprice) as t_paid 
                  FROM so WHERE sonum IN (".rtrim(str_repeat('?,', count($soList)), ',').")";
        
        $arRow = Set::fetchAll($sqlAr, $soList)[0];

        $result["so_payment_status"]["kpi_total_so"] = (float)$arRow['t_so'];
        $result["so_payment_status"]["kpi_unpaid"]   = (float)$arRow['t_unpaid'];
        $result["so_payment_status"]["kpi_paid"]     = (float)$arRow['t_paid'];
        
        $result["so_payment_status"]["chart_data"] = [
            ["name" => "Paid", "value" => (float)$arRow['t_paid']],
            ["name" => "Unpaid", "value" => (float)$arRow['t_unpaid']]
        ];

        // 3. Ambil Alignment (SQ vs SO)
        // Subquery digunakan untuk menghitung rencana modal (Planned Cost) dari SQLine
        $sqlAlign = "SELECT 
                        so.sonum, 
                        so.totalprice as so_actual_price,
                        sq.salesquotenum,
                        sq.totalprice as sq_price,
                        (SELECT SUM(linecost) FROM salesquotationline sql_in WHERE sql_in.salesquotenum = sq.salesquotenum) as sq_planned_cost
                     FROM so 
                     JOIN salesquotation sq ON so.salesquotenum = sq.salesquotenum 
                     WHERE so.sonum IN (".rtrim(str_repeat('?,', count($soList)), ',').")";
        
        $alignRows = Set::fetchAll($sqlAlign, $soList);

        foreach ($alignRows as $row) {
            $label = $row['sonum'] . " (" . $row['salesquotenum'] . ")";
            $result["sales_alignment"]["categories"][] = $label;
            $result["sales_alignment"]["series"]["sq_planned_cost"][] = (float)$row['sq_planned_cost'];
            $result["sales_alignment"]["series"]["sq_price"][]        = (float)$row['sq_price'];
            $result["sales_alignment"]["series"]["so_actual_price"][] = (float)$row['so_actual_price'];
        }

        return $result;
    }

	/**
     * HELPER 5: Get Pipeline Table (Encumbered PR & Committed PO)
     * Menggabungkan PR yang belum jadi PO dan PO yang belum selesai di-receipt.
     */
   public static function getPipelineTable($budgetNum)
	{
		$pipelineData = [];

		// ──────────────────────────────────────────────────────────────
		// 1. PR ENCUMBERED
		// ──────────────────────────────────────────────────────────────
		$sqlPR = "SELECT 
					'PR'                                        AS doc_type,
					prl.prnum                                   AS doc_number,
					NULL                                        AS potype,
					COALESCE(prl.description, 'No Description') AS description,
					'Encumbered'                                AS status,
					COALESCE(pr.status, '-')                    AS actual_status,
					COALESCE(pr.vendor, '-')                    AS vendor,
					pr.estdatearrival,
					COALESCE(pr.requestedby, '-')               AS requested_by,
					COALESCE(prl.linecost, 0)                   AS amount
				FROM prline prl
				JOIN pr ON prl.prnum = pr.prnum
				WHERE pr.budgetnum = ?
					AND pr.status NOT IN ('cancel', 'hold')
					AND NOT EXISTS (
						SELECT 1 
						FROM poline pol 
						WHERE pol.prnum    = prl.prnum 
						AND pol.prlinenum = prl.prlinenum
						LIMIT 1
					)";

		$prRows = Set::fetchAll($sqlPR, [$budgetNum]);

		// ──────────────────────────────────────────────────────────────
		// 2. PO COMMITTED — Single Year (budgetspan = 1)
		//    Nett = linecost - invoicetrans (level ponum + budgetlinenum)
		// ──────────────────────────────────────────────────────────────
		$sqlPO_Single = "SELECT 
							'PO'                                        AS doc_type,
							l.ponum                                     AS doc_number,
							COALESCE(p.potype, '-')                     AS potype,
							COALESCE(l.description, 'No Description')   AS description,
							'Committed'                                  AS status,
							COALESCE(p.status, '-')                     AS actual_status,
							COALESCE(p.vendor, '-')                     AS vendor,
							p.estdatearrival,
							COALESCE(p.buyer, '-')                      AS requested_by,
							GREATEST(
								0,
								COALESCE(l.linecost, 0) - COALESCE((
									SELECT ABS(SUM(ivt.linecost))
									FROM invoicetrans ivt
									WHERE ivt.budgetnum     = p.budgetnum
									AND ivt.budgetlinenum  = l.budgetlinenum
									AND ivt.ponum          = l.ponum
								), 0)
							)                                            AS amount
						FROM poline l
						JOIN po p ON l.ponum = p.ponum
						WHERE p.budgetnum  = ?
						AND p.budgetspan = 1
						AND p.status     NOT IN ('cancel', 'hold')";
						//AND (l.receiptstatus IS NULL OR l.receiptstatus != 'COMPLETE')";

		$poSingleRows = Set::fetchAll($sqlPO_Single, [$budgetNum]);

		// ──────────────────────────────────────────────────────────────
		// 3. PO COMMITTED — Multi Year (budgetspan > 1)
		//    Nett = allocatedamount - invoicetrans (level ponum + budgetlinenum)
		// ──────────────────────────────────────────────────────────────
		$sqlPO_Multi = "SELECT 
							'PO'                                        AS doc_type,
							p.ponum                                     AS doc_number,
							COALESCE(p.potype, '-')                     AS potype,
							COALESCE(p.description, 'No Description')   AS description,
							'Committed (Multi-Year)'                     AS status,
							COALESCE(p.status, '-')                     AS actual_status,
							COALESCE(p.vendor, '-')                     AS vendor,
							p.estdatearrival,
							COALESCE(p.buyer, '-')                      AS requested_by,
							GREATEST(
								0,
								COALESCE(aba.allocatedamount, 0) - COALESCE((
									SELECT ABS(SUM(ivt.linecost))
									FROM invoicetrans ivt
									WHERE ivt.budgetnum     = aba.budgetnum
									AND ivt.budgetlinenum  = aba.budgetlinenum
									AND ivt.ponum          = aba.ponum
								), 0)
							)                                            AS amount
						FROM annualbudgetalloc aba
						JOIN po p ON aba.ponum = p.ponum
						WHERE aba.budgetnum = ?
						AND p.budgetspan  > 1
						AND p.status      NOT IN ('cancel', 'hold')
						AND aba.status    = 'active'";

		$poMultiRows = Set::fetchAll($sqlPO_Multi, [$budgetNum]);

		// ──────────────────────────────────────────────────────────────
		// 4. Merge & Sort
		// ──────────────────────────────────────────────────────────────
		$pipelineData = array_merge($prRows, $poSingleRows, $poMultiRows);

		usort($pipelineData, function ($a, $b) {
			return $b['amount'] <=> $a['amount'];
		});

		return $pipelineData;
	}


	/**
	 * HELPER 8: Get Work Order Overview
	 * Data WO berdasarkan workorder.budgetnum
	 * Join ke assets untuk healthrate
	 *
	 * @param string $budgetNum
	 * @return array
	 */
	public static function getWorkOrderOverview($budgetNum)
	{
		$sql = "SELECT
					w.wonum,
					w.description,
					w.status,
					w.targstartdate,
					w.targcompdate AS targfinishdate,
					w.actstartdate,
					w.actfinishdate,
					COALESCE(w.progress, 0)     AS progress,
					COALESCE(w.failurecode, '-') AS failurecode,
					COALESCE(w.workclass, '-')   AS workclass,
					COALESCE(w.owner, '-')       AS owner,
					COALESCE(w.leader, '-')      AS leader,
					COALESCE(w.assetnum, '-')    AS assetnum,
					COALESCE(w.customercode, '-')    AS customer,
					COALESCE(a.healthrate, 0)    AS healthrate,
					COALESCE(w.budgetlinenum, '-')  AS budgetlinenum,
					COALESCE(w.budgetlinenum2, '-') AS budgetlinenum2
				FROM workorder w
				LEFT JOIN assets a ON w.assetnum = a.assetnum
				WHERE w.budgetnum = ?
				ORDER BY w.targstartdate ASC";

		$rows = Set::fetchAll($sql, [$budgetNum]);

		$result = [
			"kpi"   => [
				"total"        => 0,
				"completed"    => 0,
				"on_track"     => 0,
				"overdue"      => 0,
				"not_started"  => 0,
				"avg_progress" => 0,
				"avg_health"   => 0
			],
			"gantt" => [],
			"table" => []
		];

		if (empty($rows)) return $result;

		$today        = date("Y-m-d");
		$totalProgress = 0;
		$totalHealth   = 0;
		$healthCount   = 0;

		foreach ($rows as $row) {
			$result["kpi"]["total"]++;
			$totalProgress += (float)$row['progress'];

			if ((float)$row['healthrate'] > 0) {
				$totalHealth += (float)$row['healthrate'];
				$healthCount++;
			}

			// ── Status klasifikasi untuk KPI ──
			$isCompleted  = !empty($row['actfinishdate']);
			$isNotStarted = empty($row['actstartdate']);
			$isOverdue    = !$isCompleted
							&& !empty($row['targfinishdate'])
							&& $row['targfinishdate'] < $today;

			if ($isCompleted)       $result["kpi"]["completed"]++;
			elseif ($isOverdue)     $result["kpi"]["overdue"]++;
			elseif ($isNotStarted)  $result["kpi"]["not_started"]++;
			else                    $result["kpi"]["on_track"]++;

			// ── Gantt data ──
			$result["gantt"][] = [
				"wonum"       => $row['wonum'],
				"description" => $row['description'] ?: $row['wonum'],
				"status"      => $row['status'],
				"start_plan"  => $row['targstartdate'],
				"end_plan"    => $row['targfinishdate'],
				"start_act"   => $row['actstartdate'],
				"end_act"     => $row['actfinishdate'],
				"progress"    => (float)$row['progress']
			];

			// ── Table data (raw — countdown dihitung di JS) ──
			$result["table"][] = [
				"wonum"          => $row['wonum'],
				"description"    => $row['description'] ?: '-',
				"status"         => $row['status']       ?: '-',
				"targstartdate"  => $row['targstartdate'],
				"targfinishdate" => $row['targfinishdate'],
				"actstartdate"   => $row['actstartdate'],
				"actfinishdate"  => $row['actfinishdate'],
				"progress"       => (float)$row['progress'],
				"failurecode"    => $row['failurecode'],
				"workclass"      => $row['workclass'],
				"owner"          => $row['owner'],
				"leader"         => $row['leader'],
				"assetnum"       => $row['assetnum'],
				"customer"       => $row['customer'],
				"healthrate"     => (float)$row['healthrate'],
				"budgetline"     => $row['budgetlinenum'],
				"budgetline2"    => $row['budgetlinenum2']
			];
		}

		// ── Avg KPI ──
		$total = $result["kpi"]["total"];
		$result["kpi"]["avg_progress"] = $total > 0
			? round($totalProgress / $total, 1) : 0;
		$result["kpi"]["avg_health"]   = $healthCount > 0
			? round($totalHealth / $healthCount, 1) : 0;

		return $result;
	}

	/**
	 * HELPER 9: Get Budget Line Detail
	 * Mengambil semua line item untuk satu budgetnum tertentu.
	 */
	public static function getBudgetLineDetail($budgetNum)
	{
		
		$sql = "SELECT 
					budgetlinenum, budgetlinecode, description, 
					fundingsource, budgetamount, committedamount, 
					actualexpense, remainingamount, wbscode
				FROM budgetline 
				WHERE budgetnum = ?
				ORDER BY budgetlinecode ASC";

		$rows = Set::fetchAll($sql, [$budgetNum]);
		
		return array_map(function($row) {
			return [
				"linenum"    => $row['budgetlinenum'],
				"code"       => $row['budgetlinecode'],
				"desc"       => $row['description'] ?: '-',
				"funding"    => $row['fundingsource'] ?: '-',
				"wbs"        => $row['wbscode'] ?: '-',
				"limit"      => (float)$row['budgetamount'],
				"committed"  => (float)$row['committedamount'],
				"actual"     => (float)$row['actualexpense'],
				"remaining"  => (float)$row['remainingamount']
			];
		}, $rows);
	}

	/**
	 * HELPER 10: Get Document Line Breakdown (PR/PO) with Details
	 */
	public static function getDocumentLineBreakdown($docNum, $docType, $budgetNum)
	{
		$rows =[];
		
		// Tarik data dengan kolom spesifik sesuai tabelnya
		if ($docType === 'PR') {
			$sql = "SELECT 
						bl.budgetlinecode, bl.description as line_desc, 
						l.linetype, l.linecost, l.prlinenum as doclinenum, 
						l.itemnum, l.description as item_desc, l.unitcost, 
						l.orderqty, l.orderunit, l.requireddate, l.requestedby, 
						l.wonum, '-' as receiptstatus, '-' as contractnum
					FROM prline l
					JOIN budgetline bl ON l.budgetlinenum = bl.budgetlinenum
					WHERE l.prnum = ? AND bl.budgetnum = ?";
			$rows = Set::fetchAll($sql, [$docNum, $budgetNum]);
		} else {
			$sql = "SELECT 
						bl.budgetlinecode, bl.description as line_desc, 
						l.linetype, l.linecost, l.polinenum as doclinenum, 
						l.itemnum, l.description as item_desc, l.unitcost, 
						l.orderqty, l.orderunit, l.requireddate, l.requestedby, 
						l.wonum, COALESCE(l.receiptstatus, '-') as receiptstatus, 
						COALESCE(l.contractnum, '-') as contractnum
					FROM poline l
					JOIN budgetline bl ON l.budgetlinenum = bl.budgetlinenum
					WHERE l.ponum = ?  AND bl.budgetnum = ?";
			$rows = Set::fetchAll($sql, [$docNum, $budgetNum]);
		}

		$grouped =[];

		// Grouping data berdasarkan Budget Line dan Linetype
		foreach ($rows as $row) {
			$key = $row['budgetlinecode'] . '|' . $row['linetype'];
			
			if (!isset($grouped[$key])) {
				$grouped[$key] = [
					"code"    => $row['budgetlinecode'],
					"desc"    => $row['line_desc'] ?: '-',
					"type"    => $row['linetype'],
					"cost"    => 0,
					"details" =>[]
				];
			}
			
			$grouped[$key]['cost'] += (float)$row['linecost'];
			
			// Simpan setiap line item ke dalam array details
			$grouped[$key]['details'][] =[
				"linenum"   => $row['doclinenum'],
				"item"      => $row['itemnum'] ?: '-',
				"desc"      => $row['item_desc'] ?: '-',
				"qty"       => (float)$row['orderqty'],
				"unit"      => $row['orderunit'] ?: '-',
				"unitcost"  => (float)$row['unitcost'],
				"linecost"  => (float)$row['linecost'],
				"reqdate"   => $row['requireddate'],
				"reqby"     => $row['requestedby'] ?: '-',
				"wonum"     => $row['wonum'] ?: '-',
				"status"    => $row['receiptstatus'],
				"contract"  => $row['contractnum']
			];
		}

		return array_values($grouped); // Reset keys to index 0,1,2...
	}

}
Class ActionSet {

	public static function validateBudgetLineCode(){
		$payload = Super::getParam("validateBudgetLineCode");
		$budgetlinecode = $payload["budgelinecode"];
		
		$budgetLineSet = (new Set())->useTransactionManager()->MboSetRemote("budgetline")->where("budgetlinecode=?", $budgetlinecode)->getMboSet();
		if ($budgetLineSet->isEmpty()) {
			Super::sendJsonResponse("success", "Budget Code does not exist yet.");
		} else {
			Super::sendJsonResponse("error", "Budget Code already exists.");
		}
	}
	
    /**
     * Entry Point untuk Request Detail Budget Line dari Drawer (JS)
     */
	public static function getDocumentLineBreakdown()
    {
        try {
            $payload   = Super::getParam("getDocumentLineBreakdown");
            $docNum    = $payload['doc_number'] ?? null;
            $docType   = $payload['doc_type'] ?? null;
            
            // ✅ Tangkap parameter baru
            $budgetNum = $payload['budgetnum'] ?? null; 

            // Cek kelengkapan payload
            if (!$docNum || !$docType || !$budgetNum) {
                Set::throws(["status" => "error", "message" => "Document info or Budget Number missing"]);
            }

            // ✅ Kirim 3 parameter ke Helper
            $data = BudgetInsightHelper::getDocumentLineBreakdown($docNum, $docType, $budgetNum);

            Set::throws([
                "status" => "success",
                "data"   => $data
            ]);

        } catch (Exception $e) {
            Set::throws([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
    public static function getBudgetLineDetail()
    {
        try {
            // 1. Ambil payload dari key yang dikirim JS
            $payload = Super::getParam("getBudgetLineDetail");
            $budgetNum = $payload['budgetnum'] ?? null;

            if (!$budgetNum) {
                Set::throws(["status" => "error", "message" => "Budget Number is required"]);
            }

            // 2. Panggil Helper 9 yang tadi sudah kita buat
            $data = BudgetInsightHelper::getBudgetLineDetail($budgetNum);

            // 3. CETAK KE RESPONSE (JSON)
            Set::throws([
                "status" => "success",
                "data"   => $data
            ]);

        } catch (Exception $e) {
            Set::throws([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }



	 
    public static function budgetInsightChart()
	{
		try {

            $userLogin = Xbo::getLoginUser();
            $orgInfo = Xbo::getOrgcode($userLogin);
            $orgcode = $orgInfo->orgcode;
           
            $currencycode = (new Set())->MboSetRemote("organization")->where("orgcode=?", $orgcode)->getMboValue("basecurrency");

			// 1. Ambil Payload
			$payload = Super::getParam("budgetInsightChart");
			$filterBudgetNums = !empty($payload['budgetnum']) ? [$payload['budgetnum']] : [];

			// 2. Panggil Helper 1 untuk data Macro (KPI & Master Bar Chart)
			$macro = BudgetInsightHelper::getMacroData($filterBudgetNums);

			// 3. Siapkan struktur Final Response
			$response = [
				"global_kpi" => $macro["global_kpi"],
				"master_chart" => $macro["master_chart"],
				"budget_details" => []
			];

			// 4. Drill-down Detail per Budget (Micro Data)
			// Berdasarkan budget_list yang dikembalikan Helper 1
			foreach ($macro["budget_list"] as $bNum) {
				
				// Integrasikan semua Helper ke dalam Key Budgetnum masing-masing
				$response["budget_details"][$bNum] = [
					"po_type_breakdown" => BudgetInsightHelper::getPoTypeBreakdown($bNum),
					"execution_mlst"    => BudgetInsightHelper::getExecutionMLST($bNum),
					"execution_mlst_detail" => BudgetInsightHelper::getExecutionMLSTDetail($bNum),
					"production_log"    => BudgetInsightHelper::getProductionLog($bNum),
					"workorder_overview" => BudgetInsightHelper::getWorkOrderOverview($bNum),
					"sales_alignment"   => [],
					"so_payment_status" => [],
					"pipeline_table"    => BudgetInsightHelper::getPipelineTable($bNum)
				];

				// Helper 4 mengembalikan dua data: Sales Alignment & AR Status
				$salesData = BudgetInsightHelper::getSalesAlignmentAndStatus($bNum);
				$response["budget_details"][$bNum]["sales_alignment"]   = $salesData["sales_alignment"];
				$response["budget_details"][$bNum]["so_payment_status"] = $salesData["so_payment_status"];
			}

			// 5. ECharts
			Set::throws([
				"status" => "success",
				"data"   => $response,
                "currencycode" => $currencycode
			]);

		} catch (Exception $e) {
			// Gunakan Global Exception Handler atau tangkap manual
			error_log("Budget Insight Error: " . $e->getMessage());
			Set::throws([
				"status" => "error",
				"message" => "Oops, failed to load charts: " . $e->getMessage()
			]);
		}
	}


	public static function updateBudgetDummy()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("updateBudgetDummy");
			
			if (!$payload) {
				throw new Exception("Payload data is empty.");
			}

			$data = $payload["data"] ??[];
			$oldBudgetGroup = $payload["oldbudgetgroup"] ?? null;
			$newBudgetGroup = $payload["newbudgetgroup"] ?? null;

			if (empty($oldBudgetGroup)) {
				throw new Exception("Parameter 'oldbudgetgroup' is required.");
			}
			// 1. UPDATE BUDGETLINE CODES MENGGUNAKAN ROWINDEX (BUDGETLINEID)
			foreach ($data as $item) {
				// Ambil rowindex dari JSON, ini adalah Primary Key (budgetlineid)
				$budgetlineId = $item['rowindex'] ?? null; 
				$oldLineCode  = $item['budgetlinecode'] ?? ''; 
				$newLineCode  = $item['newbudgetlinecode'] ?? '';

				// Pastikan kita punya new code DAN rowindex (budgetlineid)
				if ($newLineCode !== '' && !empty($budgetlineId)) {
					
					(new Set())->useTransactionManager()
						->MboSetRemote("budgetline")
						// Targetkan BARIS SPESIFIK menggunakan Primary Key-nya!
						->where("budgetlineid = ?", $budgetlineId) 
						// Opsional: Tambahkan budgetnum sebagai jaring pengaman ekstra
						->where("budgetnum = ?", $oldBudgetGroup) 
						->setValue("budgetlinecode", $newLineCode)
						->change();

				} else if ($newLineCode !== '' && empty($budgetlineId)) {
					// Beri warning jika frontend lupa ngirim rowindex
					throw new Exception("Cannot update budget line. Missing 'rowindex' for code: " . $oldLineCode);
				}
			}

			// 2. UPDATE BUDGET GROUP / BUDGETNUM 
			if (!empty($newBudgetGroup) && $newBudgetGroup !== $oldBudgetGroup) {
				$tablesToUpdate =[
					"budget"     => "budgetnum",
					"pr"         => "budgetnum",
					"po"         => "budgetnum",
					"workorder"  => "budgetnum",
					"solinewo"   => "budgetnum",
					"budgetline" => "budgetnum"
				];

				foreach ($tablesToUpdate as $table => $column) {
					(new Set())->useTransactionManager()
						->MboSetRemote($table)
						->where("$column = ?", $oldBudgetGroup)
						->setValue($column, $newBudgetGroup)
						->change();
				}
			}

			// 3. Commit semua perubahan
			$tm->commit();
			Super::sendJsonResponse("success", "Budget updated successfully.");

		} catch (Exception $e) {
			$tm->rollback();
			error_log("updateBudgetDummy Error: " . $e->getMessage());
			Super::sendJsonResponse("error", "Failed to update budget: " . $e->getMessage());
		}
	}

	public static function modifyBudgetLine() {
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("modifyBudgetLine");
			$budgetnum = $payload["budgetnum"] ?? null;
			$data = $payload["data"] ??[];

			if (empty($data)) {
				throw new Exception("No Budget Line to be modified.");
			}
			if (empty($budgetnum)) {
				throw new Exception("Select Budget Number");
			}

			$changeby = Xbo::getLoginUser();
			$changedate = Xbo::setEnterDate();
			
			$sqlToInsert = [];

			foreach ($data as $row) {
				$budgetlinenum = $row["budgetlinenum"];
				$budgetlinecode = $row["budgetlinecode"];
				$newbudgetamount = (float) $row["newbudgetamount"]; // Pastikan jadi float
				$fundingsource = $row["fundingsource"] ?? '';

				// 1. Ambil data current (Budget Lama & Sisa Lama)
				$currentSet = (new Set())->useTransactionManager()
					->MboSetRemote("budgetline")
					->where("budgetnum=? AND budgetlinenum=? AND budgetlinecode=?", $budgetnum, $budgetlinenum, $budgetlinecode)
					->MboRemote("budgetamount, remainingamount, budgetversion")
					->getMboSet();
				
				$currentData = $currentSet->fetch();

				if (!$currentData) {
					throw new Exception("Budget line not found: $budgetlinenum - $budgetlinecode");
				}

				$oldBudgetAmount = (float) ($currentData['budgetamount'] ?? 0);
				$oldRemainingAmount = (float) ($currentData['remainingamount'] ?? 0);
				$budgetVersion = (int) ($currentData['budgetversion'] ?? 0);

				// FIX: Assign variabel fromamount yang sebelumnya hilang!
				$fromamount = $oldBudgetAmount;

				// 2. Kalkulasi Pintar di PHP (Tanpa View!)
				// Hitung uang yang sudah terpakai
				$spentAmount = $oldBudgetAmount - $oldRemainingAmount;
				// Hitung sisa uang berdasarkan budget yang baru
				$newRemainingAmount = $newbudgetamount - $spentAmount;

				$newVersion = $budgetVersion + 1;

				// 3. Lakukan 1x UPDATE untuk semua perubahan sekaligus
				(new Set())->useTransactionManager()
					->MboSetRemote("budgetline")
					->where("budgetnum=? AND budgetlinenum=? AND budgetlinecode=?", $budgetnum, $budgetlinenum, $budgetlinecode)
					->setValue("budgetversion", $newVersion)
					->setValue("budgetamount", $newbudgetamount)
					->setValue("remainingamount", $newRemainingAmount) // Update Sisa Baru langsung!
					->change();

				// 4. Siapkan data History
				$sqlToInsert[] =[
					"budgetnum" => $budgetnum,
					"budgetlinenum" => $budgetlinenum,
					"budgetlinecode" => $budgetlinecode,
					"fromamount" => $fromamount, // Sekarang terdefinisi
					"toamount" => $newbudgetamount,
					"changeby" => $changeby,
					"changedate" => $changedate,
					"fundingsource" => $fundingsource
				];
			}

			// 5. Bulk Insert History
			if (!empty($sqlToInsert)) {
				(new Set())->useTransactionManager()
					->MboSetRemote("budgetlinechange")
					->addStream($sqlToInsert);
			}

			$tm->commit();
			Super::sendJsonResponse("success", "Budgetline has been updated successfully");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to modify budget: " . $e->getMessage());
		}
	}


	public static function calculateBudget(){
		$budgetnum = Super::getParam("calculateBudget");
		
	
		$summaryLines = (new Set())->useTransactionManager()->MboSetRemote("budgetline_summary")
			->where("budgetnum=?", $budgetnum) 
			->MboRemote("budgetamount, committed_amount, remaining_amount, allocated_amount, actual_expense, budgetlinenum")
			->getMboSet()
			->fetchData(); 

		if (empty($summaryLines)) {
			Super::sendJsonResponse("info", "No budget lines found for budget number: $budgetnum");
			return;
		}
		
		$totals = [
			'total_budgetamount' => 0.0,
			'total_committedamount' => 0.0,
			'total_remainingamount' => 0.0,
			'total_allocatedamount' => 0.0,
			'total_actualexpense' => 0.0
		];

		$tm = TransactionManager::getInstance();
	
		try {
			$tm->beginTransaction();

			foreach ($summaryLines as $line) {
				$budgetlinenum = $line["budgetlinenum"];
				
				 (new Set())->useTransactionManager()->MboSetRemote("budgetline")
					->where("budgetnum = ? AND budgetlinenum = ?", $budgetnum, $budgetlinenum) 
					->setValue("allocatedamount", $line["allocated_amount"])
					->setValue("remainingamount", $line["remaining_amount"])
					->setValue("committedamount", $line["committed_amount"])
					->setValue("actualexpense", $line["actual_expense"])
					->change();
				
				// Akumulasi total
				$totals['total_budgetamount'] += (float)$line["budgetamount"];
				$totals['total_committedamount'] += (float)$line["committed_amount"];
				$totals['total_remainingamount'] += (float)$line["remaining_amount"];
				$totals['total_allocatedamount'] += (float)$line["allocated_amount"];
				$totals['total_actualexpense'] += (float)$line["actual_expense"];
			}

			 (new Set())->useTransactionManager()->MboSetRemote("budget")
				->where("budgetnum = ?", $budgetnum) // <-- Praktik Aman
				->setValue("totalbudgetamount", $totals['total_budgetamount'])
				->setValue("totalremainingamount", $totals['total_remainingamount'])
				->setValue("totalcommittedamount", $totals['total_committedamount'])
				->setValue("totalallocatedamount", $totals['total_allocatedamount'])
				->setValue("totalactualexpense", $totals['total_actualexpense'])
				->change();

			 $tm->commit();

		} catch (Exception $e) {
			  $tm->rollback(); // Batalkan semua perubahan jika ada error
			Super::sendJsonResponse("error", "Failed to calculate budget: " . $e->getMessage());
			return;
		}
		
		Set::throws($totals);
	}

}


Class ValidateTables {
	public static function tbcx(){
		$payload = Super::getParam("tbcx");
		$dataPackage = $payload["dataPackage"];

		$childTable = $dataPackage["datasetChild"];
		$deleteBy = $dataPackage["ownership"]["userid"];
		$parent = $dataPackage["ownership"]["xboset"];
		$parentid = $dataPackage["ownership"]["xbosetid"];
		$selectedRows =  $dataPackage["selectedRows"];
		
		
		$budgetnum = $dataPackage["ownership"]["owner"][0]["budgetnum"];
		if($childTable == "budgetline"){
			
		}
		
		foreach($selectedRows as $row){
			$budgetlineid = $row["budgetlineid"];
			$budgetline = (new Set())->useTransactionManager()->MboSetRemote("budgetline")->where("budgetlineid = ?", $budgetlineid)->getMboSet()->getMbo();
			$budgetlinecode = $budgetline->getString("budgetlinecode");
			$budgetlinenum = $budgetline->getString("budgetlinenum");
			$budgetamount = $budgetline->getDecimal("budgetamount");
			$budgetnum = $budgetline->getString("budgetnum");
			
			if($budgetamount > 0){
				Super::sendJsonResponse("error", "Unable to delete. Budget $budgetlinecode on line $budgetlinenum still has remaining funds.");

			}else{
				//Check apakah budget code ini digunakan di PRLINE. 
				$prSet = (new Set())->useTransactionManager()->MboSetRemote("pr")->where("budgetlinenum=? and budgetnum=?", $budgetlinenum, $budgetnum)->setRelationship("prline prl", "pr.prnum = prl.prnum")
				->MboRemote("pr.status, prl.prnum, prl.prlinenum, prl.description, prl.budgetlinenum")->getMboSet();
				
				if($prSet->isEmpty()){
					//Check POLINE 
					$poSet = (new Set())->useTransactionManager()->MboSetRemote("po")->where("budgetlinenum=? and budgetnum=?", $budgetlinenum, $budgetnum)->setRelationship("poline pol", "po.ponum = pol.ponum")
					->MboRemote("po.status, pol.polinenum, pol.ponum, pol.description, pol.budgetlinenum")->getMboSet();
					if($poSet->isEmpty()){
						//Bisa Didelete
						$_POST["delChildPackage"] = $dataPackage;
						Super::deleteChildren();			
					}else{
						$po = $poSet->getMbo();
						$ponum = $po->getString("ponum");
						$polinenum = $po->getString("polinenum");
						$description = $po->getString("description");
						
						if($status == "cancel" ){
							//Bisa Didelete
							$_POST["delChildPackage"] = $dataPackage;
							Super::deleteChildren();	
							
						}else{
							Super::sendJsonResponse("error", "Unable to delete. Budget line $budgetlinecode is used in PO: $ponum, Line: $polinenum - $description.");
						}
					}
				}else{
					$pr = $prSet->getMbo();
					$prnum = $pr->getString("prnum");
					$prlinenum = $pr->getString("prlinenum");
					$description = $pr->getString("description");
					
					if($status == "cancel"){
						$_POST["delChildPackage"] = $dataPackage;
						Super::deleteChildren();	
					}else{
						Super::sendJsonResponse("error", "Unable to delete. Budget line $budgetlinecode is used in PR: $prnum, Line: $prlinenum - $description.");
					}
				}
				
			}
		}
		Super::sendJsonResponse("error", "You're not allowed to delete budget records: $budgetnum" );
	}
}

?>