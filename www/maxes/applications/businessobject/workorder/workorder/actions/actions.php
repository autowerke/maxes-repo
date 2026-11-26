<?php
require_once ("../../../eat.php");

class OprAssignmentHelper {

    /**
     * Helper 1: Resource Readiness
     * Menghitung persentase material yang sudah di-reserve/issue dibandingkan total material di WO.
     */
    public static function getChartReadiness($wonum) {
        $sql = "SELECT 
                    SUM(CASE WHEN invreservenum IS NOT NULL THEN 1 ELSE 0 END) as ready_count,
                    COUNT(*) as total_count
                FROM woplanmaterial WHERE wonum = ?";
        $res = Set::fetchAll($sql, [$wonum]);
        
        $ready = (int)($res['ready_count'] ?? 0);
        $total = (int)($res['total_count'] ?? 0);
        $pct = ($total > 0) ? ($ready / $total) * 100 : 0;
        
        return ["ready" => $pct, "not_ready" => 100 - $pct];
    }

    /**
     * Helper 2: Task Progress
     * Mengambil data untuk Bar Chart progres per Task.
     */
    public static function getChartProgress($wonum) {
        $sql = "SELECT description, progress FROM wotask WHERE wonum = ? ORDER BY tasklinenum ASC";
        $tasks = Set::fetchAll($sql, [$wonum]);
        
        $cats = []; $vals = [];
        foreach($tasks as $t) {
            $cats[] = $t['description'];
            $vals[] = (float)$t['progress'];
        }
        return ["categories" => $cats, "values" => $vals];
    }

    /**
     * Helper 3: Pipeline Timeline
     * Mengambil ETA material yang belum sampai.
     */
    // SESUDAH — tambah dates_count & format tanggal ke timestamp
	public static function getChartPipeline($wonum) {
		$sql = "SELECT pl.description, po.estdatearrival 
				FROM poline pl
				LEFT JOIN po ON pl.ponum = po.ponum
				WHERE pl.wonum = ? AND (pl.receiptstatus IS NULL OR pl.receiptstatus != 'COMPLETE')";
		$data = Set::fetchAll($sql, [$wonum]);
		
		$dates = []; $items = []; $dates_count = [];
		foreach($data as $d) {
			$items[] = $d['description'];
			$dates[] = $d['estdatearrival'];
			// Hitung selisih hari dari sekarang ke ETA
			$eta = strtotime($d['estdatearrival']);
			$today = strtotime(date('Y-m-d'));
			$daysLeft = $eta ? max(0, round(($eta - $today) / 86400)) : 0;
			$dates_count[] = $daysLeft;
		}
		return ["items" => $items, "dates" => $dates, "dates_count" => $dates_count];
	}
}

class ResourceWorkbenchHelper {
	public static function getHolidays($filter)
	{
		return Set::fetchAll(
			"SELECT holidaycode, description, startdate, enddate, holidaytype 
			FROM pmholiday
			WHERE startdate <= ? AND enddate >= ?",
			[$filter['date_to'], $filter['date_from']]
		);
	}
	public static function loadWorkClassificationAndType(){
		$workclass = [];
		$worktype = [];

		$wotypeSet = (new Set())->useTransactionManager()->MboSetRemote("wotype")->MboRemote("worktype, description")->getMboSet();
		while($row = $wotypeSet->fetch()){
			$worktype[] = [
				"worktype"=> $row["worktype"],
				"description" => $row["description"]
			];
		}
		

		$woclassSet = (new Set())->useTransactionManager()->MboSetRemote("woclass")->MboRemote("classcode, description")->getMboSet();

		while($row = $woclassSet->fetch()){
			$workclass[] = [
				"classcode"=> $row["classcode"],
				"description" => $row["description"]
			];
		}

		Set::throws([
			"worktype" => $worktype,
			"workclass" => $workclass
		]);

	}
	public static function getEmployeesWorkload($filter)
	{
		$dateFrom = $filter['date_from'];
		$dateTo   = $filter['date_to'];

		// ── 1. Fetch semua data sekaligus 

		$holidays = Set::fetchAll(
			"SELECT holidaycode, description, holidaytype, startdate, enddate FROM pmholiday",
			[]
		);

		$leavesRaw = Set::fetchAll(
			"SELECT personid, startdate, enddate, leavetype, description FROM resourceleave
			WHERE startdate <= ? AND enddate >= ?",
			[$dateTo, $dateFrom]
		);
		$leavesByPerson = [];
		foreach ($leavesRaw as $leave) {
			$pid = strtolower($leave['personid']);
			$leavesByPerson[$pid][] = $leave;
		}

		// untuk display (semua leaves dalam filter period)
		$leavesDisplay = Set::fetchAll(
			"SELECT personid, startdate, enddate, leavetype, description FROM resourceleave
			WHERE startdate >= ? AND enddate <= ?",
			[$dateFrom, $dateTo]
		);

		$leavesByPersonDisplay = [];
		foreach ($leavesDisplay as $leave) {
			$pid = strtolower($leave['personid']);
			$leavesByPersonDisplay[$pid][] = $leave;
		}
		
		
		$teamMembers = Set::fetchAll(
			"SELECT DISTINCT et.personid, e.fullname
			FROM empteam et
			LEFT JOIN employees e ON e.personid = et.personid",
			[]
		);

		$laborPlans = Set::fetchAll(
			"SELECT 
				l.personid                              AS direct_personid,
				l.teamnum,
				l.reghours,
				l.startdate,
				l.enddate,
				w.wonum, w.description, w.status, w.progress, w.workorderid,
				COALESCE(l.personid, et.personid)       AS resolved_personid,
				e.fullname,
				COALESCE(tc.member_count, 1)            AS member_count
			FROM woplanlabor l
			LEFT JOIN workorder w   ON w.wonum     = l.wonum
			LEFT JOIN empteam et    ON et.teamnum  = l.teamnum AND l.personid IS NULL
			LEFT JOIN employees e   ON e.personid  = COALESCE(l.personid, et.personid)
			LEFT JOIN (
				SELECT teamnum, COUNT(personid) AS member_count
				FROM empteam
				GROUP BY teamnum
			) tc ON tc.teamnum = l.teamnum AND l.personid IS NULL
			WHERE l.startdate <= ? AND l.enddate >= ?",
			[$dateTo, $dateFrom]
		);

		$actualLabor = Set::fetchAll(
			"SELECT 
				l.wonum, l.teamnum, l.personid,
				l.startdate, l.enddate,
				l.totalhours, l.reghours,
				l.unitcost, l.overtimecost,
				l.totalovertimecost, l.prelinecost,
				l.linecost, l.applyovertime,
				w.description, w.status, w.progress, 
				w.workorderid
			FROM labortransline l
			LEFT JOIN workorder w ON w.wonum    = l.wonum
			LEFT JOIN employees e ON e.personid = l.personid
			WHERE l.startdate <= ? AND l.enddate >= ?",
			[$dateTo, $dateFrom]
		);
		
		
		// ── 2. Helper: hitung working days ────────────────────────────────

		$countWorkingDays = function (
			DateTime $start,
			DateTime $end,
			array $holidays,
			array $personalLeaves = []
		) {
			$days    = 0;
			$current = clone $start;

			while ($current <= $end) {
				$isWeekend = (int)$current->format('N') >= 6;

				if (!$isWeekend) {
					$isOff = false;

					foreach ($holidays as $h) {
						$hStart = new DateTime((new DateTime($h['startdate']))->format('Y-m-d'));
						$hEnd   = new DateTime((new DateTime($h['enddate']))->format('Y-m-d'));
						if ($current >= $hStart && $current <= $hEnd) {
							$isOff = true;
							break;
						}
					}

					if (!$isOff) {
						foreach ($personalLeaves as $leave) {
							$lStart = new DateTime((new DateTime($leave['startdate']))->format('Y-m-d'));
							$lEnd   = new DateTime((new DateTime($leave['enddate']))->format('Y-m-d'));
							if ($current >= $lStart && $current <= $lEnd) {
								$isOff = true;
								break;
							}
						}
					}

					if (!$isOff) $days++;
				}

				$current->modify('+1 day');
			}

			return $days;
		};

		// ── 3. Setup filter range (strip timestamp) ────────────────────────

		$filterStart = new DateTime((new DateTime($dateFrom))->format('Y-m-d'));
		$filterEnd   = new DateTime((new DateTime($dateTo))->format('Y-m-d'));

		// ── 4. Group & kalkulasi planned labor ────────────────────────────

		$groupedData = [];

		foreach ($laborPlans as $row) {
			$pid = strtolower($row['resolved_personid']);

			if (empty($pid)) continue;

			if (!isset($groupedData[$pid])) {
				$groupedData[$pid] = [
					'person_id'          => $pid,
					'person_name'        => $row['fullname'] ?? '-',
					'total_capacity'     => 0,
					'total_reghours'     => 0,
					'proportioned_hours' => 0,
					'tasks'              => [],
				];
			}

			$memberCount = (int)($row['member_count'] ?? 1);
			$reghours    = (float)$row['reghours'] / $memberCount;

			$woStart    = new DateTime((new DateTime($row['startdate']))->format('Y-m-d'));
			$woEnd      = new DateTime((new DateTime($row['enddate']))->format('Y-m-d'));
			$clampStart = max($woStart, $filterStart);
			$clampEnd   = min($woEnd,   $filterEnd);

			$personalLeaves = $leavesByPerson[$pid] ?? [];
			$totalWoDays    = $countWorkingDays($woStart,    $woEnd,    $holidays, $personalLeaves);
			$overlapWoDays  = $countWorkingDays($clampStart, $clampEnd, $holidays, $personalLeaves);

			$proportionedHours = ($totalWoDays > 0)
				? $reghours * ($overlapWoDays / $totalWoDays)
				: 0;

			$groupedData[$pid]['total_reghours']     += $reghours;
			$groupedData[$pid]['proportioned_hours'] += $proportionedHours;
			$groupedData[$pid]['tasks'][] = [
				'wonum'              => $row['wonum'],
				'description'        => $row['description'],
				'status'             => $row['status'],
				'progress'           => $row['progress'],
				'reghours'           => round($reghours, 2),
				'proportioned_hours' => round($proportionedHours, 2),
				'startdate'          => $row['startdate'],
				'enddate'            => $row['enddate'],
				'via_team'           => $row['teamnum'] ?? null,
				'workorderid'		 => $row['workorderid']
			];
		}

		// ── 5. Hitung total_capacity per orang yang assigned ──────────────

		foreach ($groupedData as $pid => &$data) {
			$personalLeaves      = $leavesByPerson[$pid] ?? [];
			$data['total_capacity'] = $countWorkingDays(
				$filterStart, $filterEnd, $holidays, $personalLeaves
			) * 9;
		}
		unset($data);

		// ── 6. Merge team members yang tidak ada di planned (available) ───

		$assignedIds = array_keys($groupedData);
		foreach ($teamMembers as $member) {
			$pid = strtolower($member['personid']);
			if (in_array($pid, $assignedIds)) continue;

			$personalLeaves = $leavesByPerson[$pid] ?? [];
			$groupedData[$pid] = [
				'person_id'          => $pid,
				'person_name'        => $member['fullname'],
				'total_capacity'     => $countWorkingDays(
											$filterStart, $filterEnd,
											$holidays, $personalLeaves
										) * 9,
				'total_reghours'     => 0,
				'proportioned_hours' => 0,
				'tasks'              => [],
			];
		}

		// ── 7. Group actual labor by personid ─────────────────────────────

		$actualByPerson = [];
		foreach ($actualLabor as $row) {
			$pid = strtolower($row['personid']);
			if (empty($pid)) continue;

			if (!isset($actualByPerson[$pid])) {
				$actualByPerson[$pid] = [
					'total_reghours'      => 0,
					'total_hours'         => 0,
					'total_overtime_hours'=> 0,
					'total_prelinecost'   => 0,
					'total_linecost'      => 0,
					'jobs'                => [],
				];
			}

			$overtimeHours = (float)$row['totalhours'] - (float)$row['reghours'];

			$actualByPerson[$pid]['total_reghours']       += (float)$row['reghours'];
			$actualByPerson[$pid]['total_hours']           += (float)$row['totalhours'];
			$actualByPerson[$pid]['total_overtime_hours']  += $overtimeHours;
			$actualByPerson[$pid]['total_prelinecost']     += (float)$row['prelinecost'];
			$actualByPerson[$pid]['total_linecost']        += (float)$row['linecost'];
			$actualByPerson[$pid]['jobs'][] = [
				'wonum'             => $row['wonum'],
				'description'       => $row['description'],
				'status'            => $row['status'],
				'startdate'         => $row['startdate'],
				'enddate'           => $row['enddate'],
				'reghours'          => (float)$row['reghours'],
				'totalhours'        => (float)$row['totalhours'],
				'overtime_hours'    => $overtimeHours,
				'unitcost'          => (float)$row['unitcost'],
				'overtimecost'      => (float)$row['overtimecost'],
				'totalovertimecost' => (float)$row['totalovertimecost'],
				'prelinecost'       => (float)$row['prelinecost'],
				'linecost'          => (float)$row['linecost'],
				'applyovertime'     => $row['applyovertime'],
				'via_team'          => $row['teamnum'] ?? null,
				'workorderid'		=> $row['workorderid']
			];
		}

		// ── 8. Format output final ────────────────────────────────────────

		$workloadData = [];
		foreach ($groupedData as $data) {
			$pid      = $data['person_id'];
			$capacity = $data['total_capacity'];
			$percent  = ($capacity > 0)
				? ($data['proportioned_hours'] / $capacity) * 100
				: 0;

			$colorClass = 'success';
			if ($percent >= 100)    $colorClass = 'danger';
			elseif ($percent >= 80) $colorClass = 'warning';
			elseif ($percent > 0)   $colorClass = 'primary';

			$workloadData[] = [
				'person_id'          => $pid,
				'person_name'        => $data['person_name'],
				'total_capacity'     => $capacity,
				'total_reghours'     => round($data['total_reghours'], 2),
				'proportioned_hours' => round($data['proportioned_hours'], 2),
				'percentage'         => round($percent, 1),
				'color_class'        => $colorClass,
				'tasks'              => $data['tasks'],
				'actual'             => isset($actualByPerson[$pid])
											? $actualByPerson[$pid]
											: null,
				'leaves'             => array_map(fn($l) => [
                                'startdate'   => $l['startdate'],
                                'enddate'      => $l['enddate'],
                                'leavetype'   => $l['leavetype'],
                                'description' => $l['description']
                            ], $leavesByPersonDisplay[$pid] ?? [])
			];
		}

		usort($workloadData, fn($a, $b) => $b['percentage'] <=> $a['percentage']);
		
		return $workloadData;
	}
	// Model
	public static function getGlobalCapacity(array $employeeData)
	{
		if (empty($employeeData)) {
			return [
				"allocated"    => 0,
				"available"    => 0,
				"actual_hours" => 0,
				"actual_cost"  => 0,
			];
		}

		$totalCapacity     = array_sum(array_column($employeeData, 'total_capacity'));
		$totalProportioned = array_sum(array_column($employeeData, 'proportioned_hours'));
		$netAvailable      = max(0, $totalCapacity - $totalProportioned);

		$actualHours = 0;
		$actualCost  = 0;
		foreach ($employeeData as $emp) {
			if (!empty($emp['actual'])) {
				$actualHours += $emp['actual']['total_hours'];
				$actualCost  += $emp['actual']['total_linecost'];
			}
		}

		$plannedPct   = ($totalCapacity > 0) ? ($totalProportioned / $totalCapacity) * 100 : 0;
		$actualPct    = ($totalCapacity > 0) ? ($actualHours / $totalCapacity) * 100 : 0;
		$availablePct = ($totalCapacity > 0) ? ($netAvailable / $totalCapacity) * 100 : 0;
		$variance     = $actualHours - $totalProportioned;


		return [
			"total_capacity"   => round($totalCapacity, 2),
			"allocated"        => round($totalProportioned, 2),
			"allocated_pct"    => round($plannedPct, 1),
			"available"        => round($netAvailable, 2),
			"available_pct"    => round($availablePct, 1),
			"actual_hours"     => round($actualHours, 2),
			"actual_pct"       => round($actualPct, 1),
			"actual_cost"      => round($actualCost, 2),
			"variance"         => round($variance, 2),  // positif = over planned, negatif = under
		];
	}
	public static function getWorkOrders($filter) 
	{
		// 1. Siapkan parameter dasar
		$params = [$filter['date_from'], $filter['date_to']];
		$whereClauses = ["w.reportdate BETWEEN ? AND ?"];

		// 2. Gunakan pendekatan array untuk parameter agar AMAN dari SQL Injection
		if (!empty($filter['status']) && is_array($filter['status'])) {
			$statusList = $filter['status'];
			
			// Buat placeholder ?,?,? sebanyak jumlah status
			$placeholders = implode(',', array_fill(0, count($statusList), '?'));
			
			$whereClauses[] = "w.status IN ($placeholders)";
			// Gabungkan status ke dalam array parameter
			$params = array_merge($params, $statusList);
		}

		// Gabungkan semua kondisi WHERE dengan AND
		$whereSql = "WHERE " . implode(" AND ", $whereClauses);

		// Query Utama
		$sql = "SELECT 
					w.wonum, w.description, w.reportdate, w.progress,
					w.plannedcost, w.actualcost, w.rollupcost, w.workorderid,					
					-- Planning Costs
					COALESCE((SELECT SUM(linecost) FROM woplanmaterial WHERE wonum = w.wonum), 0) as plan_m,
					COALESCE((SELECT SUM(prelinecost) FROM woplanlabor WHERE wonum = w.wonum), 0) as plan_l,
					COALESCE((SELECT SUM(linecost) FROM woplanservice WHERE wonum = w.wonum), 0) as plan_s,
					COALESCE((SELECT SUM(linecost) FROM woplantool WHERE wonum = w.wonum), 0) as plan_t,
					
					-- Actual Costs
					COALESCE((SELECT SUM(linecost) FROM matusetrans WHERE wonum = w.wonum), 0) as act_m,
					COALESCE((SELECT SUM(linecost) FROM labortrans WHERE wonum = w.wonum), 0) as act_l,
					COALESCE((SELECT SUM(linecost) FROM servrectrans WHERE wonum = w.wonum), 0) as act_s,
					COALESCE((SELECT SUM(linecost) FROM tooltrans WHERE wonum = w.wonum), 0) as act_t
				FROM workorder w 
				$whereSql";
		
		return Set::fetchAll($sql, $params);
	}

}
class ActionSet {
	public static function productionOrderMultiplier()
	{
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// 2. Ambil dan validasi payload secara aman
			$wonum = Super::getParam("wonum");
			$payloadData = Super::getParam("productionOrderMultiplier");
			$data = $payloadData["data"] ?? [];

			if (empty($wonum)) {
				throw new InvalidArgumentException("Work Order Number (wonum) is required.");
			}
			if (empty($data) || !is_array($data)) {
				throw new InvalidArgumentException("No items provided for multiplication.");
			}

			// 3. Looping data dan lakukan proses UPDATE
			$updateCount = 0;
			foreach ($data as $row) {
				// Pastikan data itemnum ada
				if (empty($row["itemnum"])) {
					continue; 
				}

				$itemnum = $row["itemnum"];
				$quantity = (float)($row["quantity"] ?? 0);
				$unitcost = (float)($row["unitcost"] ?? 0);
				$prodorderqty = (float)($row["prodorderqty"] ?? 0);

				// Kalkulasi Bisnis
				$newQuantity = $quantity * $prodorderqty;
				$lineCost = $newQuantity * $unitcost;

				// 4. Eksekusi UPDATE menggunakan library Set
				// Kita gunakan useTransactionManager agar masuk ke dalam transaksi
				(new Set())->useTransactionManager()
					->MboSetRemote("woplanmaterial")
					->where("wonum = ? AND itemnum = ?", $wonum, $itemnum)
					->setValue("quantity", $newQuantity) 
					->setValue("linecost", $lineCost)
					->change();
					
				$updateCount++;
			}

			if ($updateCount === 0) {
				throw new Exception("No valid items were updated.");
			}

			// 5. Jika SEMUA item berhasil di-update, Commit perubahannya!
			$tm->commit();

			Super::sendJsonResponse(
				"success",
				"$updateCount item(s) have been successfully multiplied."
			);

		} catch (Exception $e) {
			// 6. Jika terjadi error di titik manapun, Rollback semua perubahan
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to multiply items: " . $e->getMessage());
		}
	}
	public static function getWOQualityScore()
	{
		try {
			$payload = Super::getParam("woqualityalert");
			$wonum = $payload['wonum'] ?? null;

			if (!$wonum) throw new Exception("WONUM is required.");

			$sql = "
				SELECT 
					wo.wonum, wo.owner, wo.leader,
					-- Data Owner
					e_owner.fullname as owner_name, -- Tambahan Fullname
					e_owner.title as owner_title,
					e_owner.department as owner_dept,
					e_owner.section as owner_sect,
					e_owner.primaryphone as owner_phone,
					-- Data Leader
					e_leader.fullname as leader_name, -- Tambahan Fullname
					e_leader.title as leader_title,
					e_leader.department as leader_dept,
					e_leader.section as leader_sect,
					e_leader.primaryphone as leader_phone,
					-- Scoring Counts (Subqueries)
					(SELECT COUNT(*) FROM wotask WHERE wonum = wo.wonum) as task_cnt,
					(SELECT COUNT(*) FROM woplanlabor WHERE wonum = wo.wonum) as labor_cnt,
					(SELECT COUNT(*) FROM woplanmaterial WHERE wonum = wo.wonum) as mat_cnt,
					(SELECT COUNT(*) FROM woplanservice WHERE wonum = wo.wonum) as serv_cnt,
					(SELECT COUNT(*) FROM woplantool WHERE wonum = wo.wonum) as tool_cnt
				FROM workorder wo
				LEFT JOIN employees e_owner ON wo.owner = e_owner.personid
				LEFT JOIN employees e_leader ON wo.leader = e_leader.personid
				WHERE wo.wonum = ?
			";



			$data = Set::fetchAll($sql, [$wonum]);
			if (empty($data)) throw new Exception("Work Order not found.");

			$row = $data[0];
			$score = 0;
			$messages = [];

			// --- Logic Scoring & Message Types ---
			
			// 1. TASK (ALERT - Critical)
			if ($row['task_cnt'] > 0) {
				$score += 40;
			} else {
				$messages[] = [
					"type" => "ALERT",
					"text" => "<b>This Work Order has no task steps defined.</b> Clear task steps help ensure the job is done correctly. Please add at least one task step."
				];
			}

			// 2. LABOR (WARNING - Medium)
			if ($row['labor_cnt'] > 0) {
				$score += 30;
			} else {
				$messages[] = [
					"type" => "WARNING",
					"text" => "<b>This Work Order has no labor planning.</b> Please assign personnel responsible for this job."
				];
			}

			// 3. MAT, SERV, TOOL (INFO - Suggestions)
			$missingItems = [];
			if ($row['mat_cnt'] <= 0) $missingItems[] = "Material";
			if ($row['serv_cnt'] <= 0) $missingItems[] = "Service";
			if ($row['tool_cnt'] <= 0) $missingItems[] = "Tools";

			if ($row['mat_cnt'] > 0) $score += 10;
			if ($row['serv_cnt'] > 0) $score += 10;
			if ($row['tool_cnt'] > 0) $score += 10;

			if (!empty($missingItems)) {
				$itemsList = implode(", ", $missingItems);
				$messages[] = [
					"type" => "INFO",
					"text" => "This Work Order is missing the following planning elements: <b>$itemsList</b>. Please complete them if applicable."
				];
			}

			Super::sendJsonResponse("success", "Calculated", [
				"score" => $score,
				"alerts" => $messages,
				"responsible" => [
					"owner" => [
						"id" => $row['owner'],
						"name" => $row['owner_name'], // Masukkan ke payload
						"title" => $row['owner_title'],
						"dept" => $row['owner_dept'],
						"sect" => $row['owner_sect'],
						"phone" => $row['owner_phone']
					],
					"leader" => [
						"id" => $row['leader'],
						"name" => $row['leader_name'], // Masukkan ke payload
						"title" => $row['leader_title'],
						"dept" => $row['leader_dept'],
						"sect" => $row['leader_sect'],
						"phone" => $row['leader_phone']
					]
				]
			]);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}


	public static function removeWOParent()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("removeWOParent");
			$parentwonum = $payload["parentwonum"] ?? null;
			$subwonum = $payload["subwonum"] ?? null;

			// Validation 1: Input Check
			if (empty($parentwonum) || empty($subwonum)) {
				throw new InvalidArgumentException("Parent WO and Sub WO numbers are required.");
			}

			// Fetch both WOs in a single query to check existence, status, and current relationship
			$wosData = (new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->whereIn("wonum", [$parentwonum, $subwonum])
				->MboRemote("wonum, status, parent")
				->getMboSet()
				->fetchDataAssoc("wonum");

			// Validation 2: Ensure both WOs exist
			if (!isset($wosData[$parentwonum])) {
				throw new Exception("Parent Work Order '$parentwonum' does not exist.");
			}
			if (!isset($wosData[$subwonum])) {
				throw new Exception("Sub Work Order '$subwonum' does not exist.");
			}

			$parentWo = $wosData[$parentwonum];
			$subWo = $wosData[$subwonum];

			// Validation 3: Check WO Status (Prevent modifying relationships of Closed/Canceled WOs)
			$invalidStatuses = ['CLOSED', 'CANCEL'];
			if (in_array(strtoupper($parentWo['status']), $invalidStatuses)) {
				throw new Exception("Cannot remove relationship. Parent Work Order is Closed or Canceled.");
			}
			if (in_array(strtoupper($subWo['status']), $invalidStatuses)) {
				throw new Exception("Cannot remove relationship. Sub Work Order is Closed or Canceled.");
			}

			// Validation 4: Verify the relationship actually exists
			// Memastikan bahwa WO Anak ini BENAR-BENAR anak dari WO Parent yang dikirim
			if (empty($subWo['parent']) || $subWo['parent'] !== $parentwonum) {
				throw new Exception("Work Order '$subwonum' is not currently assigned to parent '$parentwonum'.");
			}

			// Execution 1: Delete the relationship from 'wosubs' table
			(new Set())->useTransactionManager()
				->MboSetRemote("wosubs")
				->where("wonum = ? AND subwonum = ?", $parentwonum, $subwonum)
				->delete(); 
			
			// Execution 2: Update the 'workorder' table to clear the 'parent' column
			(new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->where("wonum = ?", $subwonum)
				->setValue("parent", null) 
				->change();

			$tm->commit();
			Super::sendJsonResponse("success", "Work Order $subwonum has been successfully removed from parent: $parentwonum.");

		} catch (Exception $e) {
			// Rollback jika ada validasi yang gagal atau error database
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}

	public static function assignWOParent()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("assignWOParent");
			$parentwonum = $payload["parentwonum"] ?? null;
			$subwonum = $payload["subwonum"] ?? null;

			if (empty($parentwonum) || empty($subwonum)) {
				throw new InvalidArgumentException("Parent WO and Sub WO numbers are required.");
			}

			// Validation 1: Prevent self-assignment (WO tidak bisa jadi parent untuk dirinya sendiri)
			if ($parentwonum === $subwonum) {
				throw new Exception("A Work Order cannot be assigned as its own parent.");
			}

			// Fetch both WOs in a single step using whereIn
			$wosData = (new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->whereIn("wonum", [$parentwonum, $subwonum])
				->MboRemote("wonum, sitecode, orgcode, status, parent") 
				->getMboSet()
				->fetchDataAssoc("wonum"); 

			// Validation 2: Ensure both WOs exist
			if (!isset($wosData[$parentwonum])) {
				throw new Exception("Parent Work Order '$parentwonum' does not exist.");
			}
			if (!isset($wosData[$subwonum])) {
				throw new Exception("Sub Work Order '$subwonum' does not exist.");
			}

			$parentWo = $wosData[$parentwonum];
			$subWo = $wosData[$subwonum];

			// Validation 2.5: Prevent redundant assignment (Sudah ter-assign ke parent yang sama)
			if (isset($subWo['parent']) && $subWo['parent'] === $parentwonum) {
				throw new Exception("Work Order '$subwonum' is already assigned to parent '$parentwonum'.");
			}

			// Validation 3: Check Site and Org match
			if ($parentWo['sitecode'] !== $subWo['sitecode'] || $parentWo['orgcode'] !== $subWo['orgcode']) {
				throw new Exception("Parent and Sub Work Orders must belong to the same Site and Organization.");
			}

			// Validation 4: Check WO Status (PERBAIKAN BUG: Gunakan huruf kapital agar cocok dengan strtoupper)
			$invalidStatuses = ['CLOSED', 'CANCEL'];
			if (in_array(strtoupper($parentWo['status']), $invalidStatuses)) {
				throw new Exception("Cannot assign a child to a Parent Work Order that is Closed or Canceled.");
			}
			if (in_array(strtoupper($subWo['status']), $invalidStatuses)) {
				throw new Exception("Cannot assign a Closed or Canceled Work Order to a new parent.");
			}

			// Validation 5: Prevent immediate Circular Dependency (A -> B, B -> A)
			$circularCheck = (new Set())->useTransactionManager()
				->MboSetRemote("wosubs")
				->where("wonum = ? AND subwonum = ?", $subwonum, $parentwonum)
				->getMboValue("wosubsid"); 

			if ($circularCheck) {
				throw new Exception("Circular reference detected. Work Order '$parentwonum' is currently a child of '$subwonum'.");
			}

			$enterdate = Xbo::setEnterDate();
			$enterby = Xbo::getLoginUser();

			// Execution 1: Delete existing subwonum in wosubs table (Hapus parent lama jika ada)
			(new Set())->useTransactionManager()
				->MboSetRemote("wosubs")
				->where("subwonum = ?", $subwonum)
				->delete(); 
			
			// Execution 2: Insert new Parent-Child relationship ke wosubs
			(new Set())->useTransactionManager()
				->MboSetRemote("wosubs")
				->setValue("wonum", $parentwonum)
				->setValue("subwonum", $subwonum)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->add();

			// Execution 3: Update kolom parent di tabel workorder
			(new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->where("wonum = ?", $subwonum)
				->setValue("parent", $parentwonum)
				->change();
		
			$tm->commit();
			Super::sendJsonResponse("success", "Work Order $subwonum has been successfully assigned to parent: $parentwonum.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function getLaborCost(){
		$payload = Super::getParam("getLaborCost");
		$teamnum = $payload["teamnum"];
		$sumUnitCost = (new Set())->useTransactionManager()->MboSetRemote("empteam")->where("teamnum=?", $teamnum)->MboRemote("sum(unitcost) as sumUnitCost")->getMboValue("sumUnitCost");

		Super::sendJsonResponse("success", "TEAMNUM: $teamnum", ["sumUnitCost"=>$sumUnitCost]);
	}
	public static function getWBSCode(){
		$payload = Super::getParam("getWBSCode");
		$wonum = $payload["wonum"];
		$budgetnum = $payload["budgetnum"];
		$wbscode = (new Set())->useTransactionManager()->MboSetRemote("budget")->where("budgetnum=?", $budgetnum)->getMboValue("wbscode");
		Super::sendJsonResponse("success", "WBSCode Retrieved", ["wbscode"=>$wbscode]);
	}

	public static function resourceWorkbench() {
		$payload = Super::getParam("rsrcwb");
		
		$filter = [
			'date_from' => $payload['date_from'] ?? '',
			'date_to'   => $payload['date_to'] ?? '',
			'worktype'  => $payload['worktype'] ?? '',
			'workclass' => $payload['workclass'] ?? '',
			'owner'     => $payload['owner'] ?? '',
			'leader'    => $payload['leader'] ?? '',
			'status'    => $payload['status'] ?? []
		];

		try {
			// 1. Ambil employee workload SEKALI
			$employeeLoad = ResourceWorkbenchHelper::getEmployeesWorkload($filter);			
			// 2. Pass data ke getGlobalCapacity
			$capacity = ResourceWorkbenchHelper::getGlobalCapacity($employeeLoad);			
			// 3. Work Orders tetap pakai filter
			$woData = ResourceWorkbenchHelper::getWorkOrders($filter);
			// 4. Ambil Hari Libur
			$holidays     = ResourceWorkbenchHelper::getHolidays($filter);
			
			$responseData = [
				"capacity"      => $capacity,
				"holidays"      => $holidays,
				"wo_list"       => $woData,
				"employee_load" => $employeeLoad
			];

			Super::sendJsonResponse("success", "Data retrieved successfully", $responseData);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", "Failed to retrieve workbench data: " . $e->getMessage());
		}
	}


	public static function updateWoTaskProgress(){

		$payload = Super::getParam("updateWoTaskProgress");

		$wonum = $payload["wonum"] ?? null;
		$data  = $payload["data"] ?? null;
		$statusdate = Xbo::setEnterDate();

		// validate wonum
		if (empty($wonum)) {
			Super::sendJsonResponse("error", "WO number is required.");
			return;
		}

		// validate data
		if (!is_array($data) || empty($data)) {
			Super::sendJsonResponse("error", "Task progress data is missing.");
			return;
		}

		$total_progress = 0;
   		$task_count = count($data);
		$woStatus = strtolower(
			(new Set())
				->useTransactionManager()
				->MboSetRemote("workorder")
				->where("wonum = ?", $wonum)
				->getMboValue("status")
		);

		// Define disallowed statuses
		$restrictedStatuses = ["waitappr", "wappr", "approved", "waitmat"];

		if (in_array($woStatus, $restrictedStatuses, true)) {
			Super::sendJsonResponse(
				"error",
				"Progress updates are only permitted when the Work Order status is In Progress, Completed, or Closed."
			);
		}

		//update targetcompletion to 100%
		(new Set())->useTransactionManager()->MboSetRemote("wotask")->where("wonum = ?", $wonum)->setValue("targetcompletion", 100)->change();

		foreach ($data as $row) {

			$tasklinenum = $row["tasklinenum"] ?? null;
			$progress    = $row["progress"] ?? null;
			$wotaskid    = $row["wotaskid"] ?? null;

			// validate required fields
			if (empty($wotaskid) || $tasklinenum === null) {
				Super::sendJsonResponse(
					"error",
					"Invalid task data detected."
				);
				return;
			}

			// validate numeric progress
			if (!is_numeric($progress)) {
				Super::sendJsonResponse(
					"error",
					"Progress for Task Line {$tasklinenum} must be a number."
				);
				return;
			}

			$progress = floatval($progress);

			// validate range
			if ($progress < 0 || $progress > 100) {
				Super::sendJsonResponse(
					"error",
					"Progress for Task Line {$tasklinenum} must be between 0 and 100."
				);
				return;
			}

			$gapprogress = 100-$progress;
			$total_progress += $progress;
			(new Set())->useTransactionManager()->MboSetRemote("wotask")
			->where("wonum=? AND tasklinenum=?", $wonum, $tasklinenum)
			->setValue("progress", $progress)
			->setValue("status", $woStatus)
			->setValue("gapprogress", $gapprogress)
			->setValue("statusdate", $statusdate)
			->change();
		}

		// jika semua valid
		$average_progress = $total_progress / $task_count;
		(new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)->setValue("progress", $progress)->change();

		Super::sendJsonResponse("success", "Work Progress for task and work order has been updated!");
	}

	public static function newWOProgress(){

		$payload = Super::getParam("newWOProgress");

		$wonum = $payload["wonum"] ?? null;
		$new_progress = $payload["new_progress"] ?? null;

		// validate WO number
		if (empty($wonum)) {
			Super::sendJsonResponse("error", "WO number payload not found!");
		}

		// validate progress exists
		if ($new_progress === null || $new_progress === "") {
			Super::sendJsonResponse("error", "Progress value is required.");
		}

		// validate numeric
		if (!is_numeric($new_progress)) {
			Super::sendJsonResponse("error", "Progress must be a number between 0 and 100.");
		}

		$new_progress = floatval($new_progress);

		// validate range
		if ($new_progress < 0 || $new_progress > 100) {
			Super::sendJsonResponse("error", "Progress value must be between 0 and 100.");
		}

		(new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->setValue("progress", $new_progress)->change();

		Super::sendJsonResponse("success", "Work order progress updated successfully.", [
			"wonum" => $wonum,
			"new_progress" => $new_progress
		]);
	}
	public static function iswohastask(){
		$wonum = Super::getParam("iswohastask");
		if(empty($wonum)){
			Super::sendJsonResponse("error", "Workorder number does not exist.");
		}

		$woTaskSet = (new Set())->MboSetRemote("wotask")->where("wonum=?", $wonum)->getMboSet();
		if($woTaskSet->isEmpty()){
			Super::sendJsonResponse("success", "hasnotask");
		}else{
		 	Super::sendJsonResponse("success", "hastask");
		}
	}

    // Helper 1: KPI User (Bug alias 'p' diperbaiki & pakai LEFT JOIN agar aman)
	/**
	 	* Mengambil KPI pengguna yang mencakup pekerjaan individu dan pekerjaan tim.
	*/
	
	public static function getUserKPI($userId) {
		// 1. Dapatkan personid dari userid
		$personId = (new Set())->useTransactionManager()
							->MboSetRemote("mxsusers")
							->where("userid = ?", $userId)
							->getMboValue("personid");

		if (empty($personId)) return null;

		// 2. Kueri KPI yang menggabungkan beban kerja individu & tim
		$sql = "
			SELECT 
				? as personid,
				SUM(w.workhours) as target, 
				SUM(l.reghours) as logged
			FROM woplanlabor w
			LEFT JOIN labortrans l ON w.wonum = l.wonum AND l.personid = w.personid
			WHERE (
				-- Kondisi yang sama dengan getTaskQueue:
				w.personid = ? 
				OR 
				w.teamnum IN (SELECT teamnum FROM empteam WHERE personid = ?)
			)
		";
		$result = Set::fetchAll($sql, [$personId, $personId, $personId]);
		
		return !empty($result) ? $result[0] : null;
	}

   // Helper 2: Task Queue 
	public static function getTaskQueue($userId) {
		// personid user untuk mengecek keanggotaan tim
		$userSet = (new Set())->useTransactionManager()
							->MboSetRemote("mxsusers")
							->where("userid = ?", $userId)
							->getMboValue("personid");

		if (empty($userSet)) return []; // User tidak ditemukan

		$personId = $userSet;

		// Kueri yang menggabungkan WO personal dan WO tim dalam satu result set
		$sql = "
			SELECT w.wonum, w.description, 'normal' as priority, w.status
			FROM workorder w
			JOIN woplanlabor l ON w.wonum = l.wonum
			WHERE w.status NOT IN ('COMPLETED', 'CLOSED')
			AND (
				-- Jalur 1: Ditugaskan ke diri sendiri
				l.personid = ? 
				OR 
				-- Jalur 2: Ditugaskan ke tim, dan user adalah anggota tim tersebut
				l.teamnum IN (SELECT teamnum FROM empteam WHERE personid = ?)
			)
			GROUP BY w.wonum, w.description, w.status";

		return Set::fetchAll($sql, [$personId, $personId]);
	}

    // Helper 3: Execution & Staging (Bacth Processing / Bulk Fetch)
    public static function getBulkTaskDetails(array $wonums) {
        if (empty($wonums)) return["task_steps" => [], "staging_readiness" =>[]];
        
		
        $placeholders = implode(',', array_fill(0, count($wonums), '?'));
        
        // 1. Ambil Tasks dengan Fallback
        try {
			$set = new Set();
            $tasksRaw = $set::fetchAll("SELECT wonum, tasklinenum, description, progress FROM wotask WHERE wonum IN ($placeholders)", $wonums);

        } catch (Exception $e) {
            error_log("Query Failed (wotask): " . $e->getMessage());
            $tasksRaw =[]; // Fallback ke array kosong jika kueri gagal
        }

		
        // 2. Ambil Material Staging dengan Fallback
        try {
			$set = new Set();
            $stagingRaw = $set::fetchAll("SELECT wp.wonum, wp.itemnum, item.description, wp.quantity, wp.warehouse, wp.racknum, inv.status
			FROM woplanmaterial wp 
			LEFT JOIN item on wp.itemnum = item.itemnum 
			LEFT JOIN invreserve inv on wp.itemnum = inv.itemnum AND wp.wonum = inv.wonum WHERE wp.wonum IN ($placeholders)", $wonums);
        } catch (Exception $e) {
            error_log("Query Failed (woplanmaterial): " . $e->getMessage());
            $stagingRaw =[]; // Fallback ke array kosong jika kueri gagal
        }
		
        // Jaring pengaman terakhir: pastikan variabel benar-benar array (bukan false/null)
        $tasksRaw = is_array($tasksRaw) ? $tasksRaw : [];
        $stagingRaw = is_array($stagingRaw) ? $stagingRaw :[];
        
        // Kelompokkan data
        $tasksGrouped = [];
        $stagingGrouped =[];
        
        foreach ($tasksRaw as $t) {
            $tasksGrouped[$t['wonum']][] = $t;
        }
        foreach ($stagingRaw as $s) {
            $stagingGrouped[$s['wonum']][] = $s;
        }
        
		
        return["task_steps" => $tasksGrouped, "staging_readiness" => $stagingGrouped];
    }

    // Helper 4: Supply Pipeline (Batch Processing / Bulk Fetch)
    public static function getBulkSupplyPipeline(array $wonums) {
        if (empty($wonums)) return[];
        
        $placeholders = implode(',', array_fill(0, count($wonums), '?'));
        $params = array_merge($wonums, $wonums); // Duplikasi parameter untuk UNION ALL
        
        $sql = "SELECT wonum, prnum as doc_ref, 'PR' as doc_type, description, orderqty as qty 
                FROM prline WHERE wonum IN ($placeholders) AND ponum IS NULL 
                UNION ALL
                SELECT wonum, ponum as doc_ref, 'PO' as doc_type, description, orderqty as qty 
                FROM poline WHERE wonum IN ($placeholders) AND receiptstatus != 'COMPLETE'";
                
        // Tambahkan Fallback untuk Supply Pipeline
        try {
            $pipelineRaw = Set::fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Query Failed (Supply Pipeline): " . $e->getMessage());
            $pipelineRaw = []; 
        }

        $pipelineRaw = is_array($pipelineRaw) ? $pipelineRaw :[];
        
        $pipelineGrouped =[];
        foreach ($pipelineRaw as $p) {
            $pipelineGrouped[$p['wonum']][] = $p;
        }
        
        return $pipelineGrouped;
    }
	
    // Main Function
    public static function getOprAssignment() 
    {
        try {
            $payload = Super::getParam("getOprAssignment");
            $userId = $payload['userid'] ?? '';

            if (empty($userId)) {
                Super::sendJsonResponse("error", "User ID not found");
                return;
            }

            // 1. Ambil Task Queue & KPI
            $tasks = self::getTaskQueue($userId);
            $kpi = self::getUserKPI($userId);
			
            $taskDetails =[];
            
		
            if (!empty($tasks)) {
                // Ekstrak semua wonum ke dalam satu array
                $wonums = array_column($tasks, 'wonum');
					
                // 2. Ambil detail dalam BATCH (Hanya 2 Kueri!)
                $bulkDetails = self::getBulkTaskDetails($wonums);
				
                $bulkPipeline = self::getBulkSupplyPipeline($wonums);

                // 3. Mapping data kembali ke setiap task
                foreach ($tasks as $t) {
                    $wonum = $t['wonum'];
                    $workorderid = (new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboValue("workorderid"); //Ambil Work Order id

                    $taskDetails[$wonum] =[
                        "execution" => [
                            "status" => $t['status'], 
                            // Ambil dari data yang sudah dikelompokkan, jika kosong set array kosong
                            "task_steps" => $bulkDetails['task_steps'][$wonum] ??[]
                        ],
						"workorderid" => $workorderid, //Penambahan Workorder id
                        "staging_readiness" => $bulkDetails['staging_readiness'][$wonum] ??[],
                        "supply_pipeline" => $bulkPipeline[$wonum] ??[],
						// Penambahan data ECharts
						"echarts_data" => [
							"readiness" => OprAssignmentHelper::getChartReadiness($wonum),
							"progress"  => OprAssignmentHelper::getChartProgress($wonum),
							"pipeline"  => OprAssignmentHelper::getChartPipeline($wonum)
						]
                    ];
                }
            }

            // 4. Final Response
            $responseData =[
                "user_kpi" => $kpi,
                "task_queue" => $tasks,
                "task_details" => $taskDetails
            ];

            Super::sendJsonResponse("success", "Data retrieved successfully", $responseData);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", "Failed to retrieve assignment: " . $e->getMessage());
        }
    }

	
	public static function checkavailablelabors()
	{
		try {
			$payload = Super::getParam("checkavailablelabors");
			$wonum = $payload["wonum"];
			
			if (empty($wonum)) {
				Super::sendJsonResponse("error", "Work Order Number is missing.");
				return;
			}

			// 1. Get WO Data
			$woData = (new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->where("wonum = ?", $wonum)
				->MboRemote("wonum, targstartdate, targcompdate, parent")
				->getMboSet()
				->fetch();

			if (!$woData) {
				Super::sendJsonResponse("error", "Work Order not found.");
				return;
			}

			// 2. Determine WO Type & Get Related WOs
			// Check if this WO is a parent (has children in wosubs)
			$childrenCheck = (new Set())->useTransactionManager()
				->MboSetRemote("wosubs")
				->where("wonum = ?", $wonum)
				->MboRemote("COUNT(*) as child_count")
				->getMboSet()
				->fetch();
			
			$isParent = !empty($childrenCheck) && $childrenCheck['child_count'] > 0;
			$hasParent = !empty($woData['parent']);
			$relatedWOs = [$wonum]; // Always include current WO
			
			if ($isParent) {
				// Get all child WOs from wosubs table
				$children = (new Set())->useTransactionManager()
					->MboSetRemote("wosubs")
					->where("wonum = ?", $wonum)
					->MboRemote("subwonum")
					->getMboSet()
					->fetchData();
				
				foreach ($children as $child) {
					$relatedWOs[] = $child['subwonum'];
				}
			} elseif ($hasParent) {
				// Get parent and all siblings from wosubs
				$relatedWOs[] = $woData['parent'];
				
				$siblings = (new Set())->useTransactionManager()
					->MboSetRemote("wosubs")
					->where("wonum = ?", $woData['parent'])
					->MboRemote("subwonum")
					->getMboSet()
					->fetchData();
				
				foreach ($siblings as $sibling) {
					if ($sibling['subwonum'] != $wonum) {
						$relatedWOs[] = $sibling['subwonum'];
					}
				}
			}

			// 3. Determine Date Range
			if ($isParent) {
				// For parent: get min/max from all children
				$childDates = (new Set())->useTransactionManager()
					->MboSetRemote("workorder")
					->whereIn("wonum", $relatedWOs)
					->MboRemote("MIN(targstartdate) as min_start, MAX(targcompdate) as max_end")
					->getMboSet()
					->fetch();
				
				$startDate = !empty($childDates['min_start']) ? $childDates['min_start'] : date('Y-m-d 08:00:00');
				$endDate = !empty($childDates['max_end']) ? $childDates['max_end'] : date('Y-m-d 17:00:00', strtotime('+7 days'));
			} else {
				// For child/standalone: use own dates
				$startDate = !empty($woData['targstartdate']) ? $woData['targstartdate'] : date('Y-m-d 08:00:00');
				$endDate = !empty($woData['targcompdate']) ? $woData['targcompdate'] : date('Y-m-d 17:00:00', strtotime('+7 days'));
			}

			// 4. Get Resources (Labor Planning)
			$personList = [];
			$teamMap = [];
			
			// Determine which WOs to get labor from:
			// - Parent WO: get labor from ALL children (aggregate)
			// - Child/Standalone WO: get labor ONLY from current WO
			$woForLabor = $isParent ? $relatedWOs : [$wonum];
			
			// Get labor plans from the determined WOs
			foreach ($woForLabor as $wo) {
				// A. Check for team-based planning
				$teamPlans = (new Set())->useTransactionManager()
					->MboSetRemote("woplanlabor")
					->where("wonum = ? AND teamnum IS NOT NULL", $wo)
					->MboRemote("DISTINCT teamnum")
					->getMboSet()
					->fetchData();

				if (!empty($teamPlans)) {
					$teams = array_column($teamPlans, 'teamnum');
					
					// Get team members
					$members = (new Set())->useTransactionManager()
						->MboSetRemote("empteam")
						->whereIn("teamnum", $teams)
						->MboRemote("personid, teamnum")
						->getMboSet()
						->fetchData();

					foreach ($members as $m) {
						$personList[] = $m['personid'];
						if (!isset($teamMap[$m['personid']])) {
							$teamMap[$m['personid']] = $m['teamnum'];
						}
					}
				}
				
				// B. Check for direct person assignment (no team)
				$directAssigns = (new Set())->useTransactionManager()
					->MboSetRemote("woplanlabor")
					->where("wonum = ? AND personid IS NOT NULL", $wo)
					->MboRemote("DISTINCT personid")
					->getMboSet()
					->fetchData();
				
				foreach ($directAssigns as $assign) {
					$personList[] = $assign['personid'];
				}
			}

			$personList = array_unique($personList);
			
			if (empty($personList)) {
				Super::sendJsonResponse("error", "No labor resources found for this work order.");
				return;
			}

			// 5. Get ALL Assignments for these persons (Global Workload)
			// This shows what else they're working on
			
			// First, get list of teams from teamMap
			$teamList = !empty($teamMap) ? array_unique(array_values($teamMap)) : [];
			
			$assignments = [];
			
			// PART A: Get direct person assignments
			if (!empty($personList)) {
				$directAssignments = (new Set())->useTransactionManager()
					->MboSetRemote("woplanlabor")
					->setRelationship("workorder", "woplanlabor.wonum = workorder.wonum")
					->whereIn("woplanlabor.personid", $personList)
					->whereIn("workorder.status", ['approved', 'waitappr', 'inprogress', 'wappr', 'wmatl', 'wsch'])
					->where("(woplanlabor.startdate BETWEEN ? AND ? OR woplanlabor.enddate BETWEEN ? AND ? OR (woplanlabor.startdate <= ? AND woplanlabor.enddate >= ?))", 
						$startDate, $endDate, $startDate, $endDate, $startDate, $endDate)
					->order("woplanlabor.startdate ASC")
					->MboRemote("woplanlabor.personid, woplanlabor.wonum, woplanlabor.startdate, woplanlabor.enddate, woplanlabor.totalhours, workorder.description, woplanlabor.woplanlaborid")
					->getMboSet()
					->fetchData();
				
				// Add direct assignments
				foreach ($directAssignments as $assign) {
					$assignments[] = [
						'woplanlaborid' => $assign['woplanlaborid'],
						'personid' => $assign['personid'],
						'wonum' => $assign['wonum'],
						'startdate' => $assign['startdate'],
						'enddate' => $assign['enddate'],
						'totalhours' => $assign['totalhours'],
						'description' => $assign['description']
					];
				}
			}
			
			// PART B: Get team-based assignments and expand them
			if (!empty($teamList)) {
				$teamAssignments = (new Set())->useTransactionManager()
					->MboSetRemote("woplanlabor")
					->setRelationship("workorder", "woplanlabor.wonum = workorder.wonum")
					->whereIn("woplanlabor.teamnum", $teamList)
					->where("woplanlabor.personid IS NULL OR woplanlabor.personid = ''")
					->whereIn("workorder.status", ['approved', 'waitappr', 'inprogress', 'wappr', 'wmatl', 'wsch'])
					->where("(woplanlabor.startdate BETWEEN ? AND ? OR woplanlabor.enddate BETWEEN ? AND ? OR (woplanlabor.startdate <= ? AND woplanlabor.enddate >= ?))", 
						$startDate, $endDate, $startDate, $endDate, $startDate, $endDate)
					->order("woplanlabor.startdate ASC")
					->MboRemote("woplanlabor.teamnum, woplanlabor.wonum, woplanlabor.startdate, woplanlabor.enddate, woplanlabor.totalhours, workorder.description")
					->getMboSet()
					->fetchData();
				
				// Expand team assignments to individual members
				foreach ($teamAssignments as $assign) {
					// Get team members
					$teamMembers = (new Set())->useTransactionManager()
						->MboSetRemote("empteam")
						->where("teamnum = ?", $assign['teamnum'])
						->MboRemote("personid")
						->getMboSet()
						->fetchData();
					
					// Extract personid array
					$memberIds = array_column($teamMembers, 'personid');
					
					// Filter: only expand to members in our personList
					$relevantMembers = array_intersect($memberIds, $personList);
					
					// Create individual assignment for each relevant member
					foreach ($relevantMembers as $memberId) {
						$assignments[] = [
							'personid' => $memberId,
							'wonum' => $assign['wonum'],
							'startdate' => $assign['startdate'],
							'enddate' => $assign['enddate'],
							'totalhours' => $assign['totalhours'],
							'description' => $assign['description']
						];
					}
				}
			}

			// 6. Get Leave/Cuti Data
			$leaves = (new Set())->useTransactionManager()
				->MboSetRemote("resourceleave")
				->whereIn("personid", $personList)
				->where("(startdate BETWEEN ? AND ? OR enddate BETWEEN ? AND ? OR (startdate <= ? AND enddate >= ?))", 
					$startDate, $endDate, $startDate, $endDate, $startDate, $endDate)
				->MboRemote("personid, startdate, enddate, leavetype, description")
				->getMboSet()
				->fetchData();

			// 7. Build Response Payload
			$payload = [
				"wo_info" => [
					"wonum" => $wonum,
					"is_parent" => $isParent,
					"has_parent" => $hasParent,
					"parent_wo" => $hasParent ? $woData['parent'] : null,
					"related_wos" => $relatedWOs
				],
				"chart_config" => [
					"range_start" => $startDate,
					"range_end" => $endDate
				],
				"resources" => array_values($personList),
				"team_map" => !empty($teamMap) ? $teamMap : null,
				"assignments" => $assignments,
				"leaves" => $leaves
			];

			Super::sendJsonResponse("success", "Available Labors Retrieved", $payload);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}

	
	public static function woTaskRecommendation(){
		
		$payload = Super::getParam("woTaskRecom");
		$wonum = $payload["wonum"];
		
		if(empty($wonum)){
			Super::sendJsonResponse("error", "WO Number is required");
		}

		$woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet();
		if($woSet->isEmpty()){
			Super::sendJsonResponse("error", "Work Order is not found!");
		}

		$wo = $woSet->getMbo();
		$assetnum       = $wo->getString("assetnum");
		$failurecode    = $wo->getString("failurecode");
		$wodescription	= $wo->getString("description");
		$targstartdate  = $wo->getString("targstartdate");
		$targcompdate    = $wo->getString("targcompdate");

		if (empty($assetnum)) {
			Super::sendJsonResponse("error", "assetnum is required!");
		}

		if (empty($failurecode)) {
			Super::sendJsonResponse("error", "failurecode is required!");
		}

		if (empty($wodescription)) {
			Super::sendJsonResponse("error", "description is required!");
		}

		if (empty($targstartdate)) {
			Super::sendJsonResponse("error", "targstartdate is required!");
		}

		if (empty($targcompdate)) {
			Super::sendJsonResponse("error", "targcompdate is required!");
		}



		$failureDescription = (new Set())->useTransactionManager()->MboSetRemote("assetfailure")->where("failurecode=?", $failurecode)->getMboValue("description");
		$assetDescription = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum=?", $assetnum)->getMboValue("description");

		$data = [
			"wonum"=>$wonum, 
			"target_start_date" => $targstartdate,
			"target_completed_date" => $targcompdate,
			"wo_description" => $wodescription,
			"asset_description"=>$assetDescription,
			"failure_description" => $failureDescription
		];

		
		$jsonPayload = json_encode($data);
		
		$endpoint = "/suggest-workorder-jobtask";
		$rawResponse = Super::sendToML($endpoint, $jsonPayload, true);
		$dataAI = json_decode($rawResponse, true); 
		if (!$dataAI || isset($dataAI['error'])) {
             $errorMsg = $dataAI['error'] ?? "Invalid JSON response";
             Super::sendJsonResponse("error", $errorMsg); // Exit
        }
		$woNum = $dataAI['wonum']; 
		$tasks = $dataAI['tasks'];

		$sqlData = [];
		$enterdate = Xbo::setEnterDate();
		$enterby = "ORION";

		
		foreach ($tasks as $task) {
			$sqlData [] = [
				"wonum" => $woNum,
				"tasklinenum" => $task['tasklinenum'],
				"description" => $task['description'],
				"targstartdate" => $task['targstartdate'],
				"targcompdate" => $task['targcompdate'],
				"status" => $task['status'],
				"statusdate" => $enterdate,
				"enterdate" => $enterdate,
				"enterby" => $enterby
			];
		}

		
		(new Set())->useTransactionManager()->MboSetRemote("wotask")->where("wonum=?", $woNum)->remove();
		(new Set())->useTransactionManager()->MboSetRemote("wotask")->addStream($sqlData);
		Super::sendJsonResponse("success", "Job Task AI Generation Completed", $sqlData);
	}
	public static function failureRecommendation(){
		$payload = Super::getParam("failRecom");
		$jsonPayload = json_encode($payload);
		$endpoint = "/suggest-failure-code";
		$response = Super::sendToML($endpoint, $jsonPayload, true);
		Super::sendJsonResponse("success", $response);
	}

	public static function createWT()
	{
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// Langkah 2: Validasi semua input di awal
			$payload = Super::getParam("createWT");
			$wonum = $payload["wonum"] ?? null;
			$wtnum = $payload["new_wtnum"] ?? null;
			$new_description = $payload["new_description"] ?? null;

			if (empty($wtnum) || empty($wonum) || empty($new_description)) {
				throw new InvalidArgumentException("Work Template Num, description, and source WO Num are required.");
			}
			
			// Validasi #1: Cek apakah WT sudah ada
			$existingWtCount = (new Set())->useTransactionManager()->MboSetRemote("worktemplates")->where("wtnum=?", $wtnum)->countRecords();
			if ($existingWtCount > 0) {
				throw new Exception("Work Template with number '$wtnum' already exists.");
			}

			// Validasi #2: Ambil semua data sumber (WO Header, Tasks, Materials)
			$woData = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet()->fetch();
			if (!$woData) {
				throw new Exception("Source Work Order '$wonum' not found.");
			}

			$woTasks = (new Set())->useTransactionManager()->MboSetRemote("wotask")->where("wonum=?", $wonum)->getMboSet()->fetchData();
			if (empty($woTasks)) {
				throw new Exception("No tasks found in the source Work Order '$wonum'. Cannot create an empty Work Template.");
			}

			$woMaterials = (new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->where("wonum=?", $wonum)->getMboSet()->fetchData();

			// Langkah 3: Lakukan semua operasi INSERT
			$enterdate = Xbo::setEnterDate();
			$enterby = Xbo::getLoginUser();

			// A. Insert Work Template Header
			(new Set())->useTransactionManager()->MboSetRemote("worktemplates")
				->setValue("wtnum", $wtnum)
				->setValue("description", $new_description)
				->setValue("assetnum", $woData["assetnum"])
				->setValue("location", $woData["location"])
				->setValue("worktype", $woData["worktype"])
				->setValue("workclass", $woData["workclass"])
				->setValue("owner", $woData["owner"])
				->setValue("leader", $woData["leader"])
				->setValue("status", "active")
				->setValue("statusdate", $enterdate)
				->setValue("enterdate", $enterdate)
				->setValue("enterby", $enterby)
				->add();

			// B. Siapkan dan Bulk Insert Work Template Tasks
			$wtTaskData = [];
			$tasklinenum = 10;
			foreach ($woTasks as $task) {
				$wtTaskData[] = [
					"wtnum" => $wtnum,
					"tasklinenum" => $tasklinenum,
					"description" => $task["description"],
					"enterdate" => $enterdate,
					"enterby" => $enterby
				];
				$tasklinenum += 10;
			}
			(new Set())->useTransactionManager()->MboSetRemote("wttask")->addStream($wtTaskData);

			// C. Siapkan dan Bulk Insert Work Template Materials (jika ada)
			if (!empty($woMaterials)) {
				$wtMaterialData = [];
				foreach ($woMaterials as $material) {
					$wtMaterialData[] = [
						"wtnum" => $wtnum,
						// Gunakan planlinenum dari data asli untuk menjaga urutan
						"planlinenum" => $material["planlinenum"],
						"itemnum" => $material["itemnum"],
						"quantity" => $material["quantity"],
						"unitcost" => $material["unitcost"],
						"linecost" => $material["linecost"],
						"conditiontype" => $material["conditiontype"],
						"warehouse" => $material["warehouse"],
						"racknum" => $material["racknum"],
						"enterby" => $enterby,
						"enterdate" => $enterdate
					];
				}
				(new Set())->useTransactionManager()->MboSetRemote("wtplanmaterial")->addStream($wtMaterialData);
			}

			// Langkah 4: Jika semua berhasil, commit
			$tm->commit();
			Super::sendJsonResponse("success", "Work Template '$wtnum' has been created based on WO '$wonum'.");

		} catch (Exception $e) {
			// Jika ada error di manapun, rollback semua perubahan
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}


	public static function submitKHS(){
		$payload = Super::getParam("submitKHS");
		$wonum = $payload["wonum"];
		$linetype = $payload["lineType"];
		$serviceitemnum = $payload["serviceItem"];
		$description = $payload["description"];
		$quantity = $payload["quantity"];
		$orderunit = $payload["orderUnit"];
		$unitcost = $payload["unitCost"];
		$linecost = $payload["lineCost"];
		$contractnum = $payload["contractnum"];
		$remarks = $payload["remarks"];
		$transtype = "RECEIPT";
		$gldebitacct = Xbo::getwogl($wonum);
		$financialPeriod = Xbo::getFinancialPeriod();
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$orgInfo = Xbo::getOrgcode($enterby);
		$orgcode = $orgInfo->orgcode;
		$sitecode = $orgInfo->sitecode;

		//check status WO
		$status = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum =? ", $wonum)->getMboValue("status");
		$status = strtolower($status);
		if($status == "cancel" || $status == "waitappr" || $status == "wappr" || $status == "close" || $status == "approved" || $status == "appr"){
			Super::sendJsonResponse("error", "Cannot insert vendor service on status : Waiting for Approval AND Approved ");
		}


		$servrecSet = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")
		->where("wonum=? AND (serviceitemnum=? OR description =?)", $wonum, $serviceitemnum, $description)->getMboSet();

		if($servrecSet->isEmpty()){
			(new Set())->useTransactionManager()->MboSetRemote("servrectrans")
			->setValue("wonum", $wonum)
			->setValue("linetype", $linetype)
			->setValue("serviceitemnum", $serviceitemnum)
			->setValue("description", $description)
			->setValue("quantity", $quantity)
			->setValue("orderunit", $orderunit)
			->setValue("unitcost", $unitcost)
			->setValue("linecost", $linecost)
			->setValue("transtype", $transtype)
			->setValue("gldebitacct", $gldebitacct)
			->setValue("financialPeriod", $financialPeriod)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("receiptnotes", $remarks)
			->setValue("contractnum", $contractnum)
			->add();

			Super::sendJsonResponse("success", "Vendor Service has been added.");
		}else{
			Super::sendJsonResponse("error", "Vendor Service has already been exist.");
		}

	}
	public static function getServiceItemDetail(){
		$payload = Super::getParam("gSeritDetail");
		$serviceitemnum = $payload["serviceitemnum"];

		$si = (new Set())->useTransactionManager()->MboSetRemote("serviceitems")->where("serviceitemnum=?", $serviceitemnum)->getMboSet()->getMbo();
		$data = [
			"description" => $si->getString("description"),
			"serviceunit" => $si->getString("serviceunit"),
			"stdcost" =>  $si->getString("stdcost")
		];
		

		Super::sendJsonResponse("success", "Service Item Details", $data);

	}
	public static function sendJsonResponse($status, $message, $data = []) {
		
		$response = ['status' => $status, 'message' => $message];
		if (!empty($data)) {
			$response['data'] = $data;
		}
		echo json_encode($response);
		
		exit();
	}
	
	//another function besides calculateAllWoCost
	public static function calculateAllWos(){
		
		$payload = Super::getParam("calculateAllWos");
		$startdate = $payload["startdate"];
		$enddate = $payload["enddate"];
		
		$workorderSet = (new Set())->MboSetRemote("workorder")
							->where("status NOT IN ('cancel')") 
							->where("targstartdate BETWEEN ? AND ?", $startdate, $enddate)
							->MboRemote("wonum")
							->getMboSet();
		$wos = [];					
		while ($workorder = $workorderSet->fetch()) {
			$wonum = $workorder["wonum"];
			$wos[] = $wonum;
		}

		foreach ($wos as $wonum) {
			Xbo::calculateWOCost($wonum);
		}

		
		Super::sendJsonResponse("success", "All WOs Calculation Completed.");

	}
	public static function getToolType()
    {
        try {
            
            $payload = Super::getParam("getToolType");
            if (!isset($payload["wonum"]) || empty($payload["wonum"])) {
                throw new InvalidArgumentException("Parameter 'wonum' is required.");
            }
            $wonum = $payload["wonum"];
            
            $set = new Set();
            $toolTypesData = $set->MboSetRemote("woplantool wpt")
                                ->setRelationship("tools t", "wpt.toolnum = t.toolnum")
                                ->where("wpt.wonum = ?", $wonum)
                                ->MboRemote("t.tooltype")
                                ->getMboSet("distinct") 
                                ->fetchData(); 
           
            $toolTypeList = [];
            if (!empty($toolTypesData)) {
                $toolTypeList = array_column($toolTypesData, 'tooltype');
				self::sendJsonResponse("success", "Tool types retrieved successfully.", $toolTypeList);
            }else{
				self::sendJsonResponse("error", "This Work Order doesn’t plan to use any tools at all");
			}
            
        } catch (InvalidArgumentException $e) {
            self::sendJsonResponse("error", $e->getMessage());
        } catch (Exception $e) {
            self::sendJsonResponse("error", "An unexpected error occurred: " . $e->getMessage());
        }
    }
	
	public static function notifyTeamForToolsLending($fromwonum, $towonum)
	{
		try {

			//Get Owner / Leader dari fromwonum

			$fromWonumSet = (new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->where("wonum = ?", $fromwonum)
				->MboRemote("leader, owner") 
				->getMboSet();
			$fromWorkorder = $fromWonumSet->fetch();

			if (!$fromWorkorder) {
				error_log("Failed to send notification: Source Work Order '$fromwonum' not found.");
				return; 
			}

		
			$personIdsToNotify = [];
			if (!empty($fromWorkorder['leader'])) {
				//ambil userid dari personid
				$personIdsToNotify[] = Xbo::getUsername($fromWorkorder['leader']);
			}
			if (!empty($fromWorkorder['owner'])) {
				$personIdsToNotify[] = Xbo::getUsername($fromWorkorder['owner']);
			}

			if(empty($personIdsToNotify)){
				return; 
			}

			//Ambil Informasi WO Peminjam.

			$woSet = (new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->where("wonum = ?", $towonum)
				->MboRemote("workorderid, leader, owner, description, wonum, description") 
				->getMboSet();

			$workorder = $woSet->fetch(); 
			if (!$workorder) {
				error_log("Failed to send notification: Work Order '$towonum' not found.");
				return; 
			}
			
				
			
			if (empty($personIdsToNotify)) {
				return;
			}
			
			$uniquePersonIds = array_unique($personIdsToNotify);
			
			foreach ($uniquePersonIds as $username) {
				if (!empty($username)) {
					$notificationData = [
						'username'    => $username, 
						'message'     => "A request to borrow tool(s) has been made for WO {$workorder['wonum']}. Details: {$workorder['description']}",
						'objectname'  => 'workorder',
						'objectid'    => $workorder['workorderid'], 
						'objectnum'   => $workorder['wonum'], 
						'processname' => 'TOOL_BORROWING'
					];

					Super::sendNotification($notificationData);	
				}
			}
		} catch (Exception $e) {
			error_log("Error in notifyTeamForToolsLending for WO '$towonum': " . $e->getMessage());
		}
	}
	
	
	public static function transferTools() {
        $tm = TransactionManager::getInstance();
        try {
            $tm->beginTransaction();

            // LANGKAH 1: PERSIAPAN & PARSING INPUT
            $payload = Super::getParam("transferTools");
            if (!isset($payload["wonum"]) || !isset($payload["data"]) || !is_array($payload["data"]) || empty($payload["data"])) {
                throw new InvalidArgumentException("Parameter 'wonum' (destination) and a non-empty 'data' array are required.");
            }
            
            $toWoNum = $payload["wonum"]; // WO Tujuan (yang sedang dibuka di layar)
            $data = $payload["data"];
            $requestIds = array_column($data, 'toolrequestid');
            $transferedBy = Xbo::getPersonid(Xbo::getLoginUser());
            $transferedDate = Xbo::setEnterDate();

            // PENGAMBILAN DATA AWAL & VALIDASI
            // Ambil detail lengkap dari semua request yang akan diproses
            $requests = (new Set())->useTransactionManager()
                                   ->MboSetRemote("toolrequest")
                                   ->whereIn("toolrequestid", $requestIds)
                                   ->getMboSet()->fetchData();

            if (empty($requests)) {
                throw new Exception("No valid tool requests found to process.");
            }

            // Asumsikan semua request berasal dari WO yang sama
            $fromWoNum = $requests[0]['fromwonum']; 
            
            // VALIDASI #1: OTORISASI PENGGUNA
            
            $fromWoDetails = (new Set())->useTransactionManager()->MboSetRemote("workorder")
                                        ->where("wonum = ?", $fromWoNum)->getMboSet()->getMbo();
            if (!$fromWoDetails) {
                throw new Exception("Source Work Order '$fromWoNum' not found.");
            }
            $fromWoOwner = $fromWoDetails->getString("owner");
            $fromWoLeader = $fromWoDetails->getString("leader");

            if ($transferedBy !== $fromWoOwner && $transferedBy !== $fromWoLeader) {
                throw new Exception("Authorization failed: User '$transferedBy' is not the owner or leader of the source Work Order '$fromWoNum'.");
            }
            
            // Siapkan data untuk operasi selanjutnya
            $toolNumsToTransfer = array_column($requests, 'toolnum');
            $toolTransDataRows = [];

            // Ambil detail WO tujuan untuk update lokasi
            $toWoDetails = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum = ?", $toWoNum)->getMboSet()->getMbo();
			$toLocation = $toWoDetails->getString("location");
			$toLeader = $toWoDetails->getString("owner");

			// Ambil detail lengkap dari tools, termasuk deskripsi dan biaya
			$currentToolHolders = (new Set())->useTransactionManager()->MboSetRemote("tools")
											 ->whereIn("toolnum", $toolNumsToTransfer)
											 ->MboRemote("toolnum, wonum, description, unitcost, locnum") // Ambil semua yang dibutuhkan
											 ->getMboSet()->fetchData();
			
		
			$toolDetailsMap = array_column($currentToolHolders, null, 'toolnum');

			$toolTransDataRows = [];
			$currentMaxLineNum = (new Set())->useTransactionManager()->MboSetRemote("tooltrans")->where("wonum=?", $toWoNum)->MboRemote("MAX(planlinenum) AS maxline")->getMboSet()->getMbo()->getInt("maxline") ?? 0;
				
            // LOOP VALIDASI DETAIL & PERSIAPAN DATA INSERT
			
			foreach ($requests as $req) {
				$toolnum = $req['toolnum'];
				
				if ($req['status'] !== 'PENDING') {
					throw new Exception("Action failed: Request ID " . $req['toolrequestid'] . " for tool '" . $req['toolnum'] . "' has already been processed.");
				}
				
				if (!isset($toolDetailsMap[$toolnum]) || $toolDetailsMap[$toolnum]['wonum'] !== $fromWoNum) {
					throw new Exception("Action failed: Tool '$toolnum' is no longer assigned to your Work Order '$fromWoNum'.");
				}
				$toolDetails = $toolDetailsMap[$toolnum];
				$unitcost = $toolDetails['unitcost'];
				$currentMaxLineNum += 10; 
				
				// Ambil detail dari map
				$toolTransDataRows[] = [
					'planlinenum' => $currentMaxLineNum,
					'toolnum' => $toolnum,
					'description' => $toolDetails['description'],
					'unitcost' => $unitcost,
					'linecost' => $unitcost, 
					'quantity' => 1,
					'transtype' => 'TRANSFER',
					'transdate' => $transferedDate,
					'wonum' => $toWoNum,
					'fromwonum' => $fromWoNum,
					'towonum' => $toWoNum,
					'fromlocnum' => $toolDetails['locnum'], 
					'tolocnum' => $toLocation,
					'personid' => $transferedBy
				];
			}


            if (empty($toolTransDataRows)) {
				throw new Exception("No tools were eligible for transfer after validation.");
			}
            // EKSEKUSI OPERASI DATABASE
           (new Set())->useTransactionManager()->MboSetRemote("tooltrans")->addStream($toolTransDataRows);
			(new Set())->useTransactionManager()->MboSetRemote("tools")->whereIn("toolnum", $toolNumsToTransfer)->setValue("wonum", $toWoNum)->setValue("locnum", $toLocation)->setValue("personid", $toLeader)->change();
			(new Set())->useTransactionManager()->MboSetRemote("toolrequest")->whereIn("toolrequestid", $requestIds)->setValue("status", "COMPLETED")->change();
        
			$tm->commit();
			self::sendJsonResponse("success", count($toolNumsToTransfer) . " tools have been successfully transferred to " . $toWoNum);

        } catch (Exception $e) {
            $tm->rollback();
            self::sendJsonResponse("error", $e->getMessage());
        }
    }
	
	public static function lendTools() {
        $tm = TransactionManager::getInstance();
        try {
           
            $tm->beginTransaction();
            
            $payload = Super::getParam("lendTools");
            if (!isset($payload["wonum"]) || !isset($payload["data"]) || !is_array($payload["data"]) || empty($payload["data"])) {
                throw new InvalidArgumentException("Parameter 'wonum' and a non-empty 'data' array are required.");
            }
            
            $requestingWoNum = $payload["wonum"]; // WO yang meminta
            $data = $payload["data"];
            
           
            $woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $requestingWoNum)->getMboSet();
            if ($woSet->isEmpty()) {
                throw new Exception("Requesting Work Order '$requestingWoNum' not found.");
            }
            
            $requestingPersonId = Xbo::getLoginUser();
            $requestDate = Xbo::setEnterDate();

            $toolNumsToRequest = array_column($data, 'toolnum');

            // Siapa (WO mana) yang sedang memegang setiap tool yang diminta.
            $toolHoldersSet = (new Set())->useTransactionManager()->MboSetRemote("tools");
            $toolHolders = $toolHoldersSet->whereIn("toolnum", $toolNumsToRequest)
                                          ->MboRemote("toolnum, wonum AS currentwonum") // Ambil wonum pemegang saat ini
                                          ->getMboSet()
                                          ->fetchData();

            if (empty($toolHolders)) {
                throw new Exception("Could not find current holder information for the requested tools.");
            }
            
            $toolHolderMap = array_column($toolHolders, 'currentwonum', 'toolnum');
            $potentialToolRequestDataRows  = [];
			
            foreach ($toolNumsToRequest as $toolnum) {
               
                if (!isset($toolHolderMap[$toolnum])) {
                    throw new Exception("Tool '$toolnum' is no longer in use and cannot be requested. Please use Dispatch instead.");
                }
                
                $potentialToolRequestDataRows [] = [
                    'toolnum' => $toolnum,
                    'fromwonum' => $toolHolderMap[$toolnum], // WO pemegang saat ini
                    'towonum' => $requestingWoNum,        
                    'requestorid' => $requestingPersonId,
                    'requestdate' => $requestDate,
                    'status' => 'PENDING' // Status awal untuk setiap permintaan baru
                ];
            }
            
            if (empty($potentialToolRequestDataRows )) {
                 throw new Exception("No valid tools to request.");
            }
			// Menghindari duplikasi , Cek ke database untuk semua tool yang diminta oleh WO ini.

			 $existingRequestSet = (new Set())->useTransactionManager()
				->MboSetRemote("toolrequest")
				->where("towonum = ?", $requestingWoNum)
				->whereIn("toolnum", $toolNumsToRequest)
				->MboRemote("toolnum")
				->getMboSet();
				
				
			// Kumpulkan toolnum yang sudah pernah di-request oleh WO ini
			$existingRequestedTools = array_column($existingRequestSet->fetchData(), 'toolnum');
			
			$finalToolRequestDataRows = array_filter($potentialToolRequestDataRows, function($row) use ($existingRequestedTools) {
				return !in_array($row['toolnum'], $existingRequestedTools);
			});
			
            //Panggil addStream HANYA JIKA ada data baru untuk dimasukkan
			if (!empty($finalToolRequestDataRows)) {
            (new Set())->useTransactionManager()
                       ->MboSetRemote("toolrequest")
                       ->addStream($finalToolRequestDataRows);
			}
			
			
			 //Kirim notifikasi dan respons
			// Notifikasi mungkin hanya perlu dikirim jika ada permintaan baru
			
			if (!empty($finalToolRequestDataRows)){
				 self::notifyTeamForToolsLending($toolHolderMap[$toolnum], $requestingWoNum);
			}
			
            $tm->commit();
          
            $insertedCount = count($finalToolRequestDataRows);
			$message = $insertedCount > 0 ? "$insertedCount new tool requests have been sent successfully." : "No new tool requests were sent as they already exist.";
			self::sendJsonResponse("success", $message, $data);

        } catch (Exception $e) {
            $tm->rollback();
            self::sendJsonResponse("error", $e->getMessage());
        }
    }
	
	
	public static function dispatchTools() {
        
        $tm = TransactionManager::getInstance();

        try {
        
            $tm->beginTransaction();
            $payload = Super::getParam("dispatchTools");
            if (!isset($payload["wonum"]) || !isset($payload["data"]) || !is_array($payload["data"]) || empty($payload["data"])) {
                throw new InvalidArgumentException("Parameter 'wonum' and a non-empty 'data' array are required.");
            }
            
            $wonum = $payload["wonum"];
            $data = $payload["data"]; 

            $woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet();
            if ($woSet->isEmpty()) {
                throw new Exception("Work Order '$wonum' not found.");
            }
            $workorder = $woSet->getMbo();
            $locnum = $workorder->getString("location");
          
            $woLeader = $workorder->getString("leader"); 
			if(empty($woLeader)) $woleader = $workorder->getString("owner");

            $transdate = Xbo::setEnterDate();
            $personid = Xbo::getLoginUser();

            
            $toolNumsToDispatch = array_column($data, 'toolnum');

            $validationSet = (new Set())->useTransactionManager()->MboSetRemote("tools");
            $unavailableTools = $validationSet->whereIn("toolnum", $toolNumsToDispatch)
                                              ->where("status != ?", "AVAILABLE")
                                              ->MboRemote("toolnum, status")
                                              ->getMboSet()
                                              ->fetchData();

            if (!empty($unavailableTools)) {
                $errorMsg = "The following tools cannot be dispatched: ";
                foreach ($unavailableTools as $tool) {
                    $errorMsg .= $tool['toolnum'] . " (Status: " . $tool['status'] . "), ";
                }
                throw new Exception(rtrim($errorMsg, ', '));
            }

            // BULK INSERT
            $toolDetailsSet = (new Set())->useTransactionManager()->MboSetRemote("tools")
                                         ->whereIn("toolnum", $toolNumsToDispatch)
                                         ->getMboSet();

            $toolTransDataRows = [];
			$planlinenum = 0;
            while ($tool = $toolDetailsSet->fetch()) {
				$planlinenum = $planlinenum + 10;
				$toolNum = $tool["toolnum"];

				$woplantool = (new Set())->useTransactionManager()->MboSetRemote("woplantool")->where("wonum=? AND toolnum=?", $wonum, $toolNum)->MboRemote("quantity, unitcost")->getMboSet()->fetch();
				$toolQty = $woplantool["quantity"];
				$unitCost = $woplantool["unitcost"];
				$lineCost = $toolQty*$unitCost;

                $toolTransDataRows[] = [
                    'toolnum' => $toolNum,
                    'transtype' => 'ISSUE',
                    'transdate' => $transdate,
                    'quantity' => $toolQty,
                    'unitcost' => $unitCost,
                    'linecost' => $lineCost,
                    'fromlocnum' => $tool["locnum"],
                    'tolocnum' => $locnum,
                    'towonum' => $wonum,
					'wonum' => $wonum,
                    'personid' => $personid,
					'planlinenum' => $planlinenum,
					'orderunit' => $tool["measureunit"],
					'description' => $tool["description"]
                ];
            }

            
            if (empty($toolTransDataRows)) {
                throw new Exception("No valid tool details found for dispatch.");
            }

            //Bulk Insert ke tooltrans menggunakan addStream
            (new Set())->useTransactionManager()
                       ->MboSetRemote("tooltrans")
                       ->addStream($toolTransDataRows);

            //Bulk Update status di tabel master tools
            (new Set())->useTransactionManager()
                       ->MboSetRemote("tools")
                       ->whereIn("toolnum", $toolNumsToDispatch) // Update semua tool
                       ->setValue("status", "IN-USE")
                       ->setValue("wonum", $wonum)
                       ->setValue("locnum", $locnum)
                       ->setValue("personid", $woLeader) // Penanggung jawab diupdate ke leader WO
                       ->change();

            
			
			$tm->commit();
            self::sendJsonResponse("success", count($toolNumsToDispatch) . " tools have been successfully dispatched to " . $wonum);

        } catch (Exception $e) {
            $tm->rollback();
            self::sendJsonResponse("error", $e->getMessage());
        }
    }
	
	public static function DispatchLabor(){
		$data = $_POST['dispatchlabor'];
		$set = new Set();
		$countdata = count($data);
		for($i=0; $i<$countdata; $i++){
			$id = $data[$i]["woplanlaborid"];
			$set = new Set();
			$woplanlaborSet = $set->MboSetRemote("woplanlabor")->setWhere("woplanlaborid='$id'")->MboRemote("laborlinenum")->MboRemote("teamnum")->MboRemote("personid")->MboRemote("duration")->MboRemote("tasklinenum")
							->MboRemote("startdate")->MboRemote("enddate")->MboRemote("workhours")->MboRemote("reghours")->MboRemote("freehours")->MboRemote("maxhours")->MboRemote("totalhours")->MboRemote("applyovertime")
							->MboRemote("unitcost")->MboRemote("overtimecost")->MboRemote("totalovertimecost")->MboRemote("prelinecost")->MboRemote("linecost")->MboRemote("regdays")->MboRemote("dailyunitcost")
							->MboRemote("dailylinecost")->MboRemote("permitted")->MboRemote("wonum")
							->getMboSet();
			
			$woplanlabor = 	$woplanlaborSet->getMbo();
			$laborlinenum = $woplanlabor->getString("laborlinenum");
			$teamnum = $woplanlabor->getString("teamnum");
			$personid = $woplanlabor->getString("personid");
			$duration = $woplanlabor->getString("duration");
			$startdate = $woplanlabor->getString("startdate");
			$enddate = $woplanlabor->getString("enddate");
			$workhours = $woplanlabor->getString("workhours");
			$reghours = $woplanlabor->getString("reghours");
			$freehours = $woplanlabor->getString("freehours");
			$maxhours = $woplanlabor->getString("maxhours");
			$totalhours = $woplanlabor->getString("totalhours");
			$tasklinenum = $woplanlabor->getString("tasklinenum");
			$applyovertime = $woplanlabor->getString("applyovertime");
			$unitcost = $woplanlabor->getString("unitcost");
			$overtimecost = $woplanlabor->getString("overtimecost");
			$totalovertimecost = $woplanlabor->getString("totalovertimecost");
			$prelinecost = $woplanlabor->getString("prelinecost");
			$linecost = $woplanlabor->getString("linecost");
			$regdays = $woplanlabor->getString("regdays");
			$dailyunitcost = $woplanlabor->getString("dailyunitcost");
			$dailylinecost = $woplanlabor->getString("dailylinecost");
			$permitted = "Y";
			$wonum = $woplanlabor->getString("wonum");
			
			$set2 = new Set();
			$lset = $set2->MboSetRemote("labortrans")->setWhere("wonum='$wonum' and laborlinenum='$laborlinenum'")->MboRemote("laborlinenum")->getMboSet();
			$isExist = $lset->counts();
			if($isExist == 0){
				$set3 = new Set();
				
				$set3->MboSetRemote("labortrans")->setValue("laborlinenum", $laborlinenum)->setValue("teamnum", $teamnum)->setValue("personid", $personid)
							->setValue("tasklinenum", $tasklinenum)->setValue("unitcost", $unitcost)->setValue("overtimecost", $overtimecost)->setValue("dailyunitcost", $dailyunitcost)
							->setValue("permitted", $permitted)->setValue("wonum", $wonum)->add();			
			}else{
			}
			
		}
		
		Set::throws(["msg"=>"added"]);
	}
	
	public static function createAssets() {
	
		$tm = TransactionManager::getInstance();

		try {
			$payload = Super::getParam("createAssets");
			$assetsToCreate = $payload['assets']; 
			$unitcost = $payload['unitCost'];
			$endDate = $payload['endDate'];
			
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate(); // Waktu saat ini
			$orgInfo = Xbo::getOrgcode($enterby);
			$orgcode = $orgInfo->orgcode;
			$sitecode = $orgInfo->sitecode;

			$installDate = !empty($endDate) ? $endDate : $enterdate;

			$assetNumbersToCheck = array_column($assetsToCreate, 'assetNumber');
			
			if (!empty($assetNumbersToCheck)) {
				$existingAssetsSet = (new Set())->useTransactionManager()
												->MboSetRemote("assets")
												->whereIn("assetnum", $assetNumbersToCheck) // Cek semua nomor sekaligus
												->MboRemote("assetnum")
												->getMboSet();

				if (!$existingAssetsSet->isEmpty()) {
					$existingAsset = $existingAssetsSet->fetch();
					// Jika ada satu saja yang sudah ada, batalkan seluruh proses
					throw new Exception("Asset Number '" . $existingAsset['assetnum'] . "' already exists in the database. No assets were created.");
				}
			}
			
			$tm->beginTransaction(); 

			$createdAssetNumbers = []; 
			
			foreach ($assetsToCreate as $asset) {
				$set = new Set();
				$set->useTransactionManager()
					->MboSetRemote("assets")
					->setValue("assetnum", $asset['assetNumber'])
					->setValue("description", $asset['assetDescription'])
					->setValue("locnum", $asset['locnum'])
					->setValue("assettype", 'PRODUCTION')      // Aturan Bisnis
					->setValue("status", 'STANDBY')           // Aturan Bisnis
					->setValue("isdown", 'N')                 
					->setValue("healthrate", 100)             // Aset baru 100% sehat
					->setValue("purchaseprice", $unitcost)    // Biaya produksi = harga beli
					->setValue("manufacturer", $orgcode)      // Dibuat oleh organisasi sendiri
					->setValue("installdate", $installDate)
					->setValue("statusdate", $enterdate)
					->setValue("enterdate", $enterdate)
					->setValue("changedate", $enterdate)
					->setValue("lasthealthupdatedate", $enterdate)
					->setValue("enterby", $enterby)
					->setValue("changeby", $enterby)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->add();
				$createdAssetNumbers[] = $asset['assetNumber'];
			}

			$tm->commit(); 

			$count = count($createdAssetNumbers);
			$message = "$count new asset(s) have been created successfully: " . implode(', ', $createdAssetNumbers);
			Super::sendJsonResponse("success", $message);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to create assets. Error: " . $e->getMessage());
		}
	}
	public static function generateSimpleBatchNumber($payload) {
    
		$itemnum = $payload['itemNumber']; 
		$startDateObj = date_create($payload['startDate']);
		
		$productionDate = date_format($startDateObj, 'ymd');
		
		$dateForQuery = date_format($startDateObj, 'Y-m-d');

		$set = (new Set())->useTransactionManager()
						  ->MboSetRemote("productionbatches") 
						  ->MboRemote("COUNT(*) as total_count")
						  ->where("itemnum = ? AND DATE(startdate) = ?", $itemnum, $dateForQuery); 
						  
		$result = $set->getMboSet()->fetch();
		
		$countToday = $result ? (int)$result['total_count'] : 0;
		$sequenceNumber = $countToday + 1;

		$formattedSequence = str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);
		$batchnum = "{$itemnum}-{$productionDate}-{$formattedSequence}";
		
		return $batchnum;
	}

	/* Create Helper for Production Logs */

		/* HELPER function to create production Logs */
	public static function createMatProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum){
		
		$matusetransSet = (new Set())->useTransactionManager()->MboSetRemote("matusetrans")->where("wonum=?", $wonum)->getMboSet();
		if($matusetransSet->isEmpty()){
			return null;
		}
		$logdate = Xbo::setEnterDate();
		$materials = [];
		while($row = $matusetransSet->fetch()){
			$materials [] = [
				"wonum" => $row["wonum"],
				"materialnum" => $row["itemnum"],
				"description" => $row["description"], 
				"quantity" => $row["quantity"],
				"unitcost" => $row["unitcost"],
				"linecost" => $row["linecost"],
				"warehouse" => $row["warehouse"],
				"racknum" => $row["racknum"],
				"batchnum" => $batchnum,
				"logdate" => $logdate,
				"prodstartdate" => $prodstartdate, 
				"prodenddate" => $prodenddate,
				"itemnum" => $productnum
			];
		}
		
		if(!empty($materials)){
			(new Set())->MboSetRemote("matprodtrans")->addStream($materials);
		}
	}
	public static function createServProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum){
		$services = [];
		$servprodtransSet = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->where("wonum=?", $wonum)->getMboSet();
		if($servprodtransSet->isEmpty()){
			return null;
		}
		$logdate = Xbo::setEnterDate();
		while($row = $servprodtransSet->fetch()){
			$services [] = [
				"wonum" => $row["wonum"],
				"serviceitemnum" => $row["serviceitemnum"],
				"description" => $row["description"], 
				"ponum" => $row["ponum"],
				"polinenum" => $row["polinenum"],
				"quantity" => $row["quantity"],
				"orderunit" => $row["orderunit"],
				"unitcost" => $row["unitcost"],
				"linecost" => $row["linecost"],
				"batchnum" => $batchnum,
				"logdate" => $logdate,
				"prodstartdate" => $prodstartdate, 
				"prodenddate" => $prodenddate,
				"itemnum" => $productnum
			];
		}

		if(!empty($services)){
			(new Set())->MboSetRemote("servprodtrans")->addStream($services);
		}

		return null;
	}
	public static function createToolProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum){
		$tools = [];
		$toolprodtransSet = (new Set())->useTransactionManager()->MboSetRemote("tooltrans")->where("wonum=?", $wonum)->getMboSet();
		if($toolprodtransSet->isEmpty()){
			return null;
		}
		$logdate = Xbo::setEnterDate();
		while($row = $toolprodtransSet->fetch()){
			$tools [] = [
				"wonum" => $row["wonum"],
				"toolnum" => $row["toolnum"],
				"description" => $row["description"], 
				"quantity" => $row["quantity"],
				"orderunit" => $row["orderunit"],
				"unitcost" => $row["unitcost"],
				"linecost" => $row["linecost"],
				"batchnum" => $batchnum,
				"logdate" => $logdate,
				"prodstartdate" => $prodstartdate, 
				"prodenddate" => $prodenddate,
				"itemnum" => $productnum
			];
		}
		if(!empty($tools)){
			(new Set())->MboSetRemote("toolprodtrans")->addStream($tools);
		}
		return null;
	}
	public static function createProductionLogs($wos, $batchnum, $prodstartdate, $prodenddate, $productnum){
		/* $wos = [{"wonum":"WO-295-2025","assetnum":"CVY-3000-BLT"},
		{"wonum":"CM-CNVY3000-20240105-001","assetnum":"CNVY-COAL-3000"},....,{"wonum":"CM-CNVT3000-20240915-001","assetnum":"CNVY-COAL-3000"}]*/

		if (is_array($wos) && !empty($wos)) {
			foreach ($wos as $wo) {
				$wonum = $wo['wonum'];
				self::createMatProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum);
				self::createServProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum);
				self::createToolProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum);
			}
		} else {
			return null;
		}
		
	}

	public static function createItems() {
	
		$tm = TransactionManager::getInstance();

		try {
		
			$payload = Super::getParam("createItems");
			$wonum = $payload['wonum'];
			$assetnum = $payload['assetName'];
			$totalCost = $payload['totalCost'];
			$startdate = $payload['startDate'];
			$enddate = $payload['endDate'];
			$itemnum = $payload['itemNumber'];
			$quantity = $payload['quantity'];
			$unitcost = $payload['unitCost'];
			$warehouse = $payload['warehouse'];

			$wos = Super::getParam("wos");
			

			if(empty($startdate) || empty($enddate)){
				Super::sendJsonResponse("error", "Start Date and End Date is required.");
			}

			
			$batchnum = self::generateSimpleBatchNumber($payload);
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate(); 
			$orgInfo = Xbo::getOrgcode($enterby);
			$orgcode = $orgInfo->orgcode;
			$sitecode = $orgInfo->sitecode;

			$transactionDate = !empty($enddate) ? $enddate : $enterdate;
			
			$transactionType = 'PRODUCTION';
			
			
			$tm->beginTransaction(); 
			
			$itemConditionSet = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->where("itemnum=? and conditionrate=100", $itemnum)->MboRemote("conditiontype")->getMboSet();
			if($itemConditionSet->isEmpty()){
				$conditiontype = null;
			}else{
				$conditiontype = $itemConditionSet->getMbo()->getString("conditiontype");
			}
			
			//Check Inventory recordstatus
			$inventorySet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("itemnum")->getMboSet();
			if ($inventorySet->isEmpty()) {
			
				$issuetype = "FIFO";
				$insertInventorySet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("racknum", $defrack)
				->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
				->setValue("eoqqty", 1)->setValue("reorder", "Y")->setValue("issuetype", $issuetype);
				
				
				$insertInventorySet->add();
			}
			
			//Create Production Log
			$issueunit = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $itemnum)->getMboValue("issueunit");
			$linecost = $quantity*$unitcost;
			$unitprice= 0;
			$lineprice = 0;
			$healthrate = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum =?", $assetnum)->getMboValue("healthrate");
			$teamnum = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum =?", $assetnum)->getMboValue("teamnum");
			$unitprice = floatval((new Set())->useTransactionManager()->MboSetRemote("invprice")->where("itemnum=? AND defaultprice='Y'", $itemnum)->getMboValue("unitprice"));
			$lineprice = $unitprice*$quantity;

			$productionLog =[
				"itemnum" => $itemnum, 
				"issueunit" => $issueunit,
				"productiondate" => $transactionDate,
				"quantity" => $quantity,
				"unitcost" => $unitcost,
				"linecost" => $linecost,
				"unitprice" => $unitprice,
				"lineprice" => $lineprice,
				"assetnum" => $assetnum,
				"enterby" => $enterby,
				"enterdate" => $enterdate,
				"wonum" => $wonum, 
				"healthrate" => $healthrate,
				"teamnum" => $teamnum,
				"orgcode" => $orgcode,
				"sitecode" => $sitecode,
				'startdate' => $startdate,
				'enddate' => $enddate,
				'outputtype' => "ITEM",
				'batchnum' => $batchnum
			];

			$sqlToInsert [] = $productionLog;

			
			 $overlapCheckSet = (new Set())->useTransactionManager()
			->MboSetRemote("productionlog")
			->where("assetnum = ?", $assetnum)
			->where("startdate <= ? AND enddate >= ?", $enddate, $startdate)
			->limit(1)
			->getMboSet();

			
			if (!$overlapCheckSet->isEmpty()) {
				$existingLog = $overlapCheckSet->fetch();
				throw new Exception(
					"Validation failed: The provided date range ({$startdate} to {$enddate}) " .
					"overlaps with an existing production log (ID: {$existingLog['productionlogid']} from {$existingLog['startdate']} to {$existingLog['enddate']}) " .
					"for the same asset."
				);
			}
			//Create Production Log record.
			(new Set())->useTransactionManager()->MboSetRemote("productionlog")->addStream($sqlToInsert);
			$financialPeriod = Xbo::getFinancialPeriod();
			$set = new Set();
			$set->useTransactionManager()
				->MboSetRemote("invfifolifocost")
				->setValue("itemnum", $itemnum)
				->setValue("batchnum", $batchnum)
				->setValue("unitcost", $unitcost)
				->setValue("quantity", $quantity)
				->setValue("physqty", $quantity) 
				->setValue("costdate", $transactionDate)
				->setValue("transdate", $transactionDate)
				->setValue("transtype", $transactionType)
				->setValue("conditiontype", $conditionType)
				->setValue("warehouse", $warehouse)
				->setValue("sitecode", $sitecode)
				->setValue("orgcode", $orgcode)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->setValue("wonum", $wonum)
				->setValue("financialperiod", $financialPeriod)
				->add();

			$productnum = $itemnum;
			self::createProductionLogs($wos, $batchnum, $startdate, $enddate, $productnum);

			$tm->commit();

			// --- LANGKAH 4: KIRIM RESPON SUKSES ---
			Super::sendJsonResponse("success", "Items have been created with batch number: $batchnum and moved to Warehouse: $warehouse");

		} catch (Exception $e) {
			
			$tm->rollback(); 

			Super::sendJsonResponse("error", "Failed to create items. Error: " . $e->getMessage());
		}
	}
	
	
	public static function updateLastStartDate(){
		$payload = Super::getParam("updateLastStartDate");
		$wonum = $payload["wonum"];
		$actual_start_date = $payload["changedate"]; // Tanggal Status WO Berubah ke INPRG
		$enterby = Xbo::getLoginUser();

		if (empty($wonum) || empty($actual_start_date)) {
			return;
		}
		
		//check existing actual start date if exist
		$existing_actstartdate = (new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboValue("actstartdate");
		if(!empty($existing_actstartdate)) $actual_start_date = $existing_actstartdate;
		
		// Update actstartdate dari WO
		(new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)
		->setValue("actstartdate", $actual_start_date)->change();
		
		
		// 1. Ambil info PM dari WO yang baru dimulai.
		$woSet = (new Set())->MboSetRemote("workorder")->where("wonum = ?", $wonum)->getMboSet();
		if ($woSet->isEmpty()) return;
		
		$wo_mbo = $woSet->getMbo();
		$pmnum = $wo_mbo->getString("pmnum");
		$forecastdate_str = $wo_mbo->getString("forecastdate");
		$actual_start_date_str = $wo_mbo->getString("actstartdate");

		$pmdeviation = null; 
		
		
		// hitung selisih tanggal forecast dengan tanggal wo actual_start_date
		if (!empty($forecastdate_str) && !empty($actual_start_date_str)) {
			// pmdeviation
			 try {
				$forecast_date_obj = new DateTime($forecastdate_str);
				$actual_start_date_obj = new DateTime($actual_start_date_str);
				
				$interval = $forecast_date_obj->diff($actual_start_date_obj);
				$deviation_in_days = $interval->days;
				
				if ($interval->invert === 1) {
					// Pekerjaan dimulai LEBIH AWAL dari yang direncanakan
					$pmdeviation = -$deviation_in_days;
				} else {
					// Pekerjaan dimulai TEPAT WAKTU atau TERLAMBAT
					$pmdeviation = $deviation_in_days;
				}
				
				(new Set())->useTransactionManager()
                   ->MboSetRemote("workorder")
                   ->where("wonum = ?", $wonum)
                   ->setValue("pmdeviation", $pmdeviation) // Asumsi ada kolom 'pmdeviation' di tabel 'workorder'
                   ->change();
				 
			 }catch (Exception $e) {
				error_log("Failed to calculate PM deviation for WO {$wonum}: " . $e->getMessage());
				$pmdeviation = null; // Gagal dengan aman
			}
		}
		if (empty($pmnum)) {
			self::sendJsonResponse("success", "The Work Order is now in progress");
		}
		
		// 2. Ambil data PM Master untuk perbandingan dan mendapatkan tipe PM.
		$pmSet = (new Set())->useTransactionManager()->MboSetRemote("pm")->where("pmnum = ?", $pmnum)->getMboSet();
		
		if ($pmSet->isEmpty()) return;
		
		$pm_mbo = $pmSet->getMbo();
		$pm_type = $pm_mbo->getString("pmtype");
		$existing_last_startdate = $pm_mbo->getString("laststarteddate"); // Ambil tanggal yang ada

		$should_update_master_and_reschedule = false;
		if (empty($existing_last_startdate) || (new DateTime($actual_start_date) > new DateTime($existing_last_startdate))) {
			// Hanya update dan reschedule jika tanggal baru > tanggal lama
			$should_update_master_and_reschedule = true;
		}

		// Hanya lanjutkan jika ada pembaruan yang diperlukan, mencegah "pemunduran" jadwal karena WO dikerjakan tidak berurutan.
		if ($should_update_master_and_reschedule) {
			// Update Last Started Date di PM Master
			(new Set())->useTransactionManager()
					   ->MboSetRemote("pm")
					   ->where("pmnum = ?", $pmnum)
					   ->setValue("laststarteddate", $actual_start_date)
					   ->setValue("changeby", $enterby)
					   ->setValue("changedate", Xbo::setEnterDate())
					   ->change();
						   
			
			if ($pm_type === 'FLOATING_START') {
				$base_date_for_reschedule = new DateTime($actual_start_date);
				self::rescheduleFutureWos($pmnum, $pm_mbo, $base_date_for_reschedule);
			}
		}
		self::sendJsonResponse("success", "The Work Order is now in progress");
	}
	
	
	private static function rescheduleFutureWos($pmnum, $pm_mbo, DateTime $base_date) {
		// A. Ambil semua WO "saudara" yang statusnya masih belum complete/close/cancel
		$futureWoSet = (new Set())->useTransactionManager()
								  ->MboSetRemote("workorder")
								  ->where("pmnum = ? AND status IN ('WAITSCHED', 'SCHED', 'waitappr')", $pmnum)
								  ->order("targstartdate ASC")
								  ->getMboSet();

		if (!$futureWoSet->isEmpty()) {
			
			$interval_value = $pm_mbo->getInt("intervalvalue");
			$interval_unit = $pm_mbo->getString("intervalunit");
			$direction = $pm_mbo->getString("holidayaction");
			
			// Tanggal dasar untuk perhitungan BUKANLAH tanggal WO pemicu,
			// tapi tanggal penyelesaian/mulai TERAKHIR dari WO yang sudah complete atau sedang berjalan
			$anchor_date = self::getLatestAnchorDate($pmnum, $base_date);
			$current_base_date = new DateTime($anchor_date);
			
			while ($future_wo = $futureWoSet->fetch()) {
				
				$new_target_date_obj = clone $current_base_date;
				$new_target_date_obj->modify("+{$interval_value} {$interval_unit}");

				$resolved_date_obj = ActionSet::findNextValidWorkday($new_target_date_obj, $pm_mbo, $direction);
				$new_target_date_str = $resolved_date_obj->format('Y-m-d H:i:s');
				
				(new Set())->useTransactionManager()
						   ->MboSetRemote("workorder")
						   ->where("wonum = ?", $future_wo['wonum'])
						   ->setValue("targstartdate", $new_target_date_str)
						   ->setValue("targcompdate", $new_target_date_str)
						   ->change();

				$current_base_date = $resolved_date_obj;
			}
		}
	}

	/**
	 * Fungsi helper untuk menemukan titik awal yang benar untuk penjadwalan ulang.
	 */
	private static function getLatestAnchorDate($pmnum, DateTime $trigger_date) {
		// Cari tanggal mulai/selesai terakhir dari WO yang sudah terkunci
		$lastLockedWoSet = (new Set())->useTransactionManager()
									  ->MboSetRemote("workorder")
									  ->where("pmnum = ? AND status IN ('completed', 'inprogress')", $pmnum)
									  ->order("COALESCE(actfinishdate, actstartdate) DESC") 
									  ->limit(1)
									  ->getMboSet();
		
		if (!$lastLockedWoSet->isEmpty()) {
			$lastLockedWo = $lastLockedWoSet->getMbo();
			$last_date_str = $lastLockedWo->getString("actfinishdate") ?: $lastLockedWo->getString("actstartdate");
			return $last_date_str;
		}
		
		return $trigger_date->format('Y-m-d H:i:s');
	}

	public static function updateLastCompDate(){
		
		$payload = Super::getParam("updateLastCompDate");
		$wonum = $payload["wonum"];
		$actual_finish_date = $payload["changedate"]; 
		$enterby = Xbo::getLoginUser();
		
		if (empty($wonum) || empty($actual_finish_date)) {
			return;
		}
		
		$is_actfinishdate_exist = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboValue("actfinishdate");
		if(!empty($is_actfinishdate_exist)) $actual_finish_date = $is_actfinishdate_exist;
	

		(new Set())->useTransactionManager()
                   ->MboSetRemote("tools")
                   ->where("wonum = ?", $wonum) 
                   ->setValue("status", "AVAILABLE") 
                   ->setValue("wonum", null) 
                   ->setValue("personid", null)
                   ->setValue("changedate", Xbo::setEnterDate())
                   ->setValue("changeby", $enterby)
                   ->change();

		// end tool release

		// Update actfinishdate dari WO
		(new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)
		->setValue("actfinishdate", $actual_finish_date)->change();
		

		// 1. Ambil info PM dari WO yang sudah complete.
		$woSet = (new Set())->MboSetRemote("workorder")->where("wonum = ?", $wonum)->getMboSet();
		if ($woSet->isEmpty()) return;
		$pmnum = $woSet->getMbo()->getString("pmnum");
		if (empty($pmnum)) { 
			// do nothing 
		}

		// 2. Ambil data PM Master untuk perbandingan dan mendapatkan tipe PM.
		$pmtype = null;
		$pmSet = (new Set())->useTransactionManager()->MboSetRemote("pm")->where("pmnum = ?", $pmnum)->getMboSet();
		if ($pmSet->isEmpty()) { 
			// do nothing 
		}else{
			$pm_mbo = $pmSet->getMbo();
			$existing_last_compdate = $pm_mbo->getString("lastcompleteddate");
			$pm_type = $pm_mbo->getString("pmtype");
		}
		
		
		
		$should_update_master = false;
		if (empty($existing_last_compdate) || (new DateTime($actual_finish_date) > new DateTime($existing_last_compdate))) {
			$should_update_master = true;
		}

		if ($should_update_master && !empty($pmnum)) {
			(new Set())->useTransactionManager()
					   ->MboSetRemote("pm")
					   ->where("pmnum = ?", $pmnum)
					   ->setValue("lastcompleteddate", $actual_finish_date)
					   ->setValue("changeby", $enterby)
					   ->setValue("changedate", Xbo::setEnterDate())
					   ->change();
		}

		
		// berjalan untuk tipe FLOATING_COMP
		if ($pm_type === 'FLOATING_COMP' && $should_update_master) {
			$base_date_for_reschedule = new DateTime($actual_finish_date);
			self::rescheduleFutureWos($pmnum, $pm_mbo, $base_date_for_reschedule);
		}
		
		Super::sendJsonResponse("success", "The Work Order is now complete");
		
	}
	public static function findNextValidWorkday(DateTime $start_date_obj, $pm_mbo, $direction = 'AFTER') {
    
		// Pengecekan Aturan IGNORE di Awal
		$holiday_action = $pm_mbo->getString("holidayaction");
		if ($holiday_action === 'IGNORE') {
			return $start_date_obj;
		}

		// Buat salinan objek tanggal
		$current_date = clone $start_date_obj;

		// Ambil aturan weekend dari objek PM
		$allow_saturday = ($pm_mbo->getString("allowsaturday") === 'Y');
		$allow_sunday = ($pm_mbo->getString("allowsunday") === 'Y');
		
		// Tentukan modifier untuk maju (+1 hari) atau mundur (-1 hari)
		$modifier = ($direction === 'BEFORE') ? '-1 day' : '+1 day'; // Default ke FORWARD/AFTER

		// Loop sampai menemukan hari yang valid
		while (true) {
			$current_date_str = $current_date->format('Y-m-d');
			$is_conflict = false;

			// 1. Cek Hari Libur
			$holidaySet = (new Set())->useTransactionManager()
									 ->MboSetRemote("pmholiday")
									 ->where("? BETWEEN startdate AND enddate", $current_date_str)
									 ->getMboSet();
			
			if (!$holidaySet->isEmpty()) {
				$is_conflict = true;
			}

			// 2. Cek Akhir Pekan
			if (!$is_conflict) {
				$day_of_week = (int)$current_date->format('N');
				if (($day_of_week == 6 && !$allow_saturday) || ($day_of_week == 7 && !$allow_sunday)) {
					$is_conflict = true;
				}
			}

			// 3. Jadinnya ini...
			if (!$is_conflict) {
				return $current_date;
			} else {
				$current_date->modify($modifier);
			}
		}
	}
	public static function getAssetHealth(){
		$wonum = Super::getParam("getAssetHealth");
		if(!$wonum || empty($wonum)){
			self::sendJsonResponse("Error", "Work Order Number(wonum) is required.");
		}
		
		$set = new Set();
		$assetnum = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("assetnum")->getMboSet()->getMbo()->getString("assetnum");
		
		$set = new Set();
		$healthrate = $set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->MboRemote("healthrate")->getMboSet()->getMbo()->getString("healthrate");
		
		$data = new stdClass();
		$data->assetnum = $assetnum; 
		$data->healthrate = $healthrate;
		Set::throws($data);
	}
	public static function saveAssetHealth(){
		$payload = Super::getParam("save_asset_health");
		$healthrate = $payload["healthRate"];
		$assessmentDate = $payload["assessmentDate"];
		$basis = $payload["basis"];
		$notes = $payload["notes"];
		$assessedBy = $payload["assessedBy"];
		$wonum = $payload["wonum"];
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$assetnum = (new Set())->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("assetnum")->getMboSet()->getMbo()->getString("assetnum");
		
		if(empty($assetnum)) {
			self::sendJsonResponse("error", "Work Order does not have asset to assigned with.");
		}
		if(empty($healthrate)){
			self::sendJsonResponse("error", "Health Rate cannot be empty");
		}
		
		$whereClause = "assetnum='$assetnum' and wonum='$wonum'";
		
		
		// Void yang lama (jika ada) dan catet ID-nya

		$lastActiveId = null; 
		// Cari record ACTIVE terakhir untuk Work Order ini
		$lastActiveSet = (new Set())->MboSetRemote("assethealthrate")->setWhere("wonum='$wonum' AND assetnum='$assetnum' AND recordstatus='ACTIVE'")->getMboSet();
		
		// Jika ditemukan record lama yang aktif
		if(!$lastActiveSet->isEmpty()){
			$lastActiveMbo = $lastActiveSet->getMbo(0);
			$lastActiveId = $lastActiveMbo->getString("assethealthrateid");

			// VOID previous record
			// Whenever the Set() finish with getMbo(), it means the last class of Set() is destroyed. so we need to redeclare a new Set()
			(new Set())->MboSetRemote("assethealthrate")->setWhere("assethealthrateid='$lastActiveId'")
			->setValue("recordstatus", "VOID")
			->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)
			->change();
			
		}

		// SELALU INSERT RECORD BARU
		
		
		$addSet = new Set();
		$addSet->MboSetRemote("assethealthrate")
			->setValue("assetnum", $assetnum)
			->setValue("wonum", $wonum)
			->setValue("healthrate", $healthrate)
			->setValue("assessmentdate", $assessmentDate)
			->setValue("basis", $basis)
			->setValue("notes", $notes)
			->setValue("assessedby", $assessedBy)
			->setValue("enterby", $enterby)
			->setValue("recordstatus", "ACTIVE")
			->setValue("replacesid", $lastActiveId) 
			->add();
		
		
		// LAST ONE - update ASSETS.assethealthrate 
		$set = new Set();
		$set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->setValue("healthrate", $healthrate)->change();
		
		$message = ($lastActiveId === null) 
			? "Asset Health Rate history has been successfully recorded." 
			: "Asset Health Rate record has been successfully corrected.";
			
		self::sendJsonResponse("success", $message);
		
		
	}
	
	
	private static function validateReportDate($assetNum, $reportDate) {
		// 1. Tanggal Tidak Boleh di Masa Depan
		if ($reportDate > date('Y-m-d H:i:s')) {
			return "Report Date cannot be in the future.";
		}

		$set = new Set();

		// 2. Ambil Tanggal Downtime Terawal
		$minDowntimeStartSet = $set->MboSetRemote("assetdowntimehistory")
			->setWhere("assetnum = '$assetNum'")
			->MboRemote("min(downtimestart) as minDowntimeStart") // Alias untuk kolom
			->getMboSet();

		$minDowntimeStartMbo = $minDowntimeStartSet->getMbo();
		$minDowntimeStart    = $minDowntimeStartMbo ? $minDowntimeStartMbo->getString("minDowntimeStart") : null; //handle jika kosong
		
		// 3. Ambil Tanggal Uptime Terakhir
		$set = new Set();
		$maxDowntimeEndSet = $set->MboSetRemote("assetdowntimehistory")
			->setWhere("assetnum = '$assetNum'")
			->MboRemote("max(downtimeend) as maxDowntimeEnd") // Alias untuk kolom
			->getMboSet();

		$maxDowntimeEndMbo = $maxDowntimeEndSet->getMbo();
		$maxDowntimeEnd    = $maxDowntimeEndMbo ? $maxDowntimeEndMbo->getString("maxDowntimeEnd") : null; //handle jika kosong

		// 4. Validasi terhadap Rentang Waktu di Histori (JIKA ada data di histori)
		if ($minDowntimeStart && $maxDowntimeEnd) {
			if ($reportDate > $minDowntimeStart && $reportDate < $maxDowntimeEnd) {
				return "Report Date overlaps with an existing downtime period.";
			}
		}

		return null; // Validasi Sukses
	}
	
	public static function reportDowntime() {

		// 1. Ambil Semua Parameter dari Request (dan Validasi)
		$assetnum    = trim(Super::getParam("reportDowntime")); //Asset Number
		$assetstate  = strtolower(trim(Super::getParam("assetstate"))); //"up" atau "down"
		$failurecode = trim(Super::getParam("failurecode")); //Kode penyebab Downtime
		$wonum = trim(Super::getParam("wonum"));       //Nomor Work Order (jika ada)
		$comments    = trim(Super::getParam("comments"));       //Komentar
		$reportdate  = Super::getParam("reportdate");       //Tanggal dan waktu pelaporan
		$enterby    = Xbo::getLoginUser();   // user yg lapor
		$enterdate = Xbo::setenterdate();

		if (empty($assetnum) || empty($assetstate) || !in_array($assetstate, ['up', 'down']) || empty($reportdate)) {
			Set::throws(["msg" => "Asset Number, Asset State (up/down), and Report Date are required."]);
			return; // Hentikan eksekusi jika validasi gagal
		}

		// 2. Validasi Tanggal (Tambahan)
		$validationError = self::validateReportDate($assetnum, $reportdate);
		if ($validationError) {
			Set::throws(["msg" => $validationError]);
			return;
		}

		// 3. Definisikan Status Aset (Berbasis Keadaan "UP" atau "DOWN")
		$newAssetStatus = ($assetstate == "down") ? "shutdown" : "operating";

		// 4. Periksa Status Aset TERKINI dari Database
		$set = new Set();
		$assetsSet = $set->MboSetRemote("assets")
						->setWhere("assetnum = '$assetnum'")
						->MboRemote("status")
						->MboRemote("statusdate")
						->getMboSet();

		$asset          = $assetsSet->getMbo();
		$currentStatus  = $asset->getString("status");
		$statusDate     = $asset->getString("statusdate");
		
		// 5. Logika Utama: Apakah Aset Sudah Dalam Keadaan yang Sama?
		if (($assetstate == "down" && $currentStatus == "shutdown") || ($assetstate == "up" && $currentStatus != "shutdown")) {
			// Kasus 1: Aset SUDAH shutdown, dilaporkan shutdown LAGI
			// Kasus 2: Aset SUDAH operating, dilaporkan operating LAGI
			Set::throws(["msg" => "Asset is already in the reported state."]);
			return; // Tidak ada yang perlu dilakukan
		}

		// 6. Logika: Catat Event ke assetdowntime dan Update Status Aset (JIKA perlu)
		if ($assetstate == "down") { // Aset berubah menjadi DOWN
			$isdown = "Y";
			// a. Catat Event ke tabel `assetdowntime`
			$set = new Set();
			$set->MboSetRemote("assetdowntime")
				->setValue("assetnum",      $assetnum)
				->setValue("assetstate",    $assetstate)
				->setValue("reportdate",    $reportdate)
				->setValue("enterby",       $enterby)
				->setValue("enterdate",     $enterdate)
				->setValue("failurecode",   $failurecode)
				->setValue("wonum",         $wonum)
				->setValue("comments",      $comments)
				->add();

			// b. Update Status Aset di tabel `assets` (JIKA Berubah)
			if ($currentStatus != "shutdown") {
				$set = new Set();
				$set->MboSetRemote("assets")->setWhere("assetnum = '$assetnum'")
					->setValue("status",     $newAssetStatus)
					->setValue("statusdate", $reportdate)
					->setValue("isdown",     $isdown)
					->change();
			}

		} else { // $assetstate == "up" Aset berubah menjadi UP (Logika yang Berubah)

			$isdown = "N";
			$set = new Set();
			// **A. Cari Periode Downtime Terakhir (Jika Ada)**
			$downtimeSet = $set->MboSetRemote("assetdowntime")
			->setWhere("assetnum = '$assetnum' AND assetstate = 'down'")
			->MboRemote("max(reportdate) as reportdate")
			->MboRemote("failurecode")
			->MboRemote("assetdowntimeid")
			->order("reportdate DESC") //Terakhir
			->getMboSet();

			if ($downtimeSet && $downtimeSet->counts() > 0) {

				$lastDowntime = $downtimeSet->getMbo();
				$downtimeStart = $lastDowntime->getString("reportdate");
				$assetdowntimeid =  $lastDowntime->getString("assetdowntimeid");
			  

				// **B. Hitung Durasi Downtime**
				$downtimeStartObj = new DateTime($downtimeStart);
				$reportDateObj    = new DateTime($reportdate);

				$interval         = $downtimeStartObj->diff($reportDateObj);
				$durationMinutes  = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

				// C. Simpan ke `assetdowntimehistory`
				$set = new Set();
				$set->MboSetRemote("assetdowntimehistory")
					->setValue("assetnum",       $assetnum)
					->setValue("downtimestart",  $downtimeStart)
					->setValue("downtimeend",    $reportdate)
					->setValue("durationminutes", $durationMinutes)
					->setValue("failurecode",    $lastDowntime->getString("failurecode"))
					->setValue("wonum",          $wonum)
					->add();

				/* D. Hapus Record Downtime Saat Ini (assetdowntime)
				$set = new Set();
				$set->MboSetRemote("assetdowntime")
					->setWhere("assetdowntimeid = ".$assetdowntimeid)
					->delete();
	*/
				// E. Perbarui `assetproductivity.actualvalue` (BARU!)
				self::updateActualProductivity($assetnum, $reportdate);
			}

			//a. Catat Event ke tabel `assetdowntime`
			$set = new Set();
			$set->MboSetRemote("assetdowntime")
				->setValue("assetnum",      $assetnum)
				->setValue("reportdate", $reportdate)
				->setValue("assetstate",    $assetstate) // "up"
				->setValue("failurecode",      null)
				->setValue("wonum",  $wonum)
				->setValue("enterby",    $enterby)
				->setValue("enterdate",  $enterdate)
				->setValue("comments",      $comments)
				->add();

			// b. Update Status Aset di tabel `assets` (JIKA Berubah)
			if ($currentStatus == "shutdown") {
				$set = new Set();
				$set->MboSetRemote("assets")->setWhere("assetnum = '$assetnum'")
					->setValue("status",     $newAssetStatus) // "operating"
					->setValue("statusdate", $reportdate)
					->setValue("isdown",     $isdown)
					->change();
			}
		}
		Set::throws(["msg" => "Asset has been change to ".$newAssetStatus]);
	}

	public static function getFailureCodeOptions() {
		$set = new Set();
		$failureCodesSet = $set->MboSetRemote("assetfailure")->getMboSet()->order("category, failurecode asc"); // Dapatkan semua record

		$optionsHTML = '';
		if (!$failureCodesSet->isEmpty()) {
			$counts = $failureCodesSet->counts();
			for($i=0; $i<$counts; $i++){
				$failureCodes = $failureCodesSet->getMbo();
				$failurecode = $failureCodes->getString("failurecode");
				$description = $failureCodes->getString("description");
				$category = $failureCodes->getString("category");

				// Gabungkan Failurecode, Description, dan Category
				$displayValue = htmlspecialchars("$category - $failurecode - $description", ENT_QUOTES, 'UTF-8');

				$optionsHTML .= "<option value='" . htmlspecialchars($failurecode, ENT_QUOTES, 'UTF-8') . "'>" . $displayValue . "</option>";
			}
		} else {
			$optionsHTML = "<option value=''>No Failure Codes Found</option>";
		}

		Set::throws(["options"=> $optionsHTML]);
	}
	
	
	public static function selectedZone() {
		$payload = Super::getParam();
		$wonum   = $payload["wonum"] ?? null;
		$zoneid  = $payload["selectedZone"] ?? null;

		if (empty($wonum) || empty($zoneid)) {
			self::sendJsonResponse("error", "Missing required parameters: wonum or selectedZone.");
		}

		$zoneSet = (new Set())
			->MboSetRemote("zone")
			->setWhere("zoneid='{$zoneid}'")
			->MboRemote("zonename")
			->getMboSet();

		if ($zoneSet->isEmpty()) {
			self::sendJsonResponse("error", "Zone does not exist.");
		}

		$zone = $zoneSet->getMbo();
		$zonename = $zone->getString("zonename");

		
		// Ambil worootcause
		
		$whereClause = "wonum='{$wonum}'";
		$worootcauseSet = (new Set())
			->MboSetRemote("worootcause")
			->setWhere($whereClause)
			->getMboSet();

		if ($worootcauseSet->isEmpty()) {
			self::sendJsonResponse("error", "The work order {$wonum} does not have Failure Hierarchy.");
		}

		
		// Update Zone Sekaligus untuk Semua
		
		(new Set())->MboSetRemote("worootcause")->setWhere($whereClause)->setValue("zone", $zonename)->change();

		self::sendJsonResponse("success", "Zone of Failure Code(s) has been assigned.");
	}


	public static function viewFailureHierarchy(){
		$wonum = Super::getParam("viewFailureHierarchy");
		
		$worootcauseSet = (new Set())->MboSetRemote("worootcause")->setWhere("wonum='$wonum'")
		->MboRemote("failurecode")->MboRemote("failurelevel")->MboRemote("zone")->getMboSet();
		
		if($worootcauseSet->isEmpty()){
			self::sendJsonResponse("error", "Failure Code Hierarchy not found");
			
		}
		
		$fch = [];
		for($i=0; $i<$worootcauseSet->counts(); $i++){
			$worootcause = $worootcauseSet->getMbo();
			$failurecode = $worootcause->getString("failurecode");
			$failurelevel = $worootcause->getString("failurelevel");
			$zone = $worootcause->getString("zone");
			$oo = new stdClass;
			$oo->failurecode = $failurecode;
			$oo->failurelevel = $failurelevel;
			$fch [] = $oo;
		}
		
		self::sendJsonResponse("success", "Failure Code Hierarchy", $fch);
	}
	public static function rootcause() {
		$payload = Super::getParam();

		if (!$payload) {
			self::sendJsonResponse("error", "No payload received");
			return;
		}

		// Ambil rootcause (wonum)
		$wonum = $payload["rootcause"] ?? null;
		$data = $payload["data"] ?? [];

		if (!$wonum || empty($data)) {
			self::sendJsonResponse("error", "Missing rootcause or data");
			return;
		}

		$inserted = [];
		$skipped = [];

		foreach ($data as $i => $row) {
			$id = $row["assetfailureid"] ?? null;
			if (!$id) continue;

			// Ambil assetfailure
			$set = new Set();
			$assetFailureSet = $set->MboSetRemote("assetfailure")
								  ->setWhere("assetfailureid='$id'")
								  ->MboRemote("failurecode")
								  ->getMboSet();

			if ($assetFailureSet->isEmpty()) {
				$skipped[] = $id;
				continue;
			}

			$assetFailure = $assetFailureSet->getMbo();
			$failurecode = $assetFailure->getString("failurecode");
			$failurelevel = $i + 1;

			// Cek worootcause
			$worootcauseSet = (new Set())->MboSetRemote("worootcause")
										->setWhere("wonum = '$wonum' AND failurecode = '$failurecode'")
										->getMboSet();

			// Kalau belum ada, insert baru
			if ($worootcauseSet->isEmpty()) {
				$newSet = new Set();
				$newSet->MboSetRemote("worootcause")
					   ->setValue("wonum", $wonum)
					   ->setValue("failurecode", $failurecode)
					   ->setValue("failurelevel", $failurelevel)
					   ->add();

				$inserted[] = $failurecode;
			} else {
				$skipped[] = $failurecode;
			}
		}

		self::sendJsonResponse('success',  "Failure Code has been Added", [
			'inserted' => $inserted,
			'skipped' => $skipped,
			'data' => $data
		]);
	}

	public static function QuickReservation() { 
		
		// 1. Dapatkan User & Data Input (REVISI)
		$loggedInUser = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();

		// Ambil seluruh object data yang di-post
		$payload = Super::getParam(); // Asumsi getParam() tanpa argumen akan mengembalikan seluruh object

		// Cek keberadaan 'wonum' di level atas
		if (!isset($payload['wonum']) || empty($payload['wonum'])) {
			self::sendJsonResponse('error', 'Missing Work Order number (wonum).');
		}
		$wonum = $payload['wonum'];
		
		//get orgcode and sitecode 
		$set = new Set();
		$woSet = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$wo = $woSet->getMbo();
		$orgcode = $wo->getString("orgcode");
		$sitecode = $wo->getString("sitecode");
		$gldebitacct = Xbo::getWOAccount($wonum, $orgcode, $sitecode);
		// Cek keberadaan object 'quick_reservation' dan array 'data' di dalamnya
		if (!isset($payload['quick_reservation']) || !isset($payload['quick_reservation']['data'])) {
			self::sendJsonResponse('error', 'Invalid payload structure. Missing quick_reservation.data object.'); //400
		}
		$requestedItems = $payload['quick_reservation']['data']; // Ini adalah array yang akan di loop

		// Tambahan: Validasi jika array-nya kosong
		if (empty($requestedItems)) {
			self::sendJsonResponse('error', 'No items were selected for reservation.');
		}

		
		// MULAI TRANSAKSI 
		

		try {
			// 2. Loop untuk setiap item yang diminta user dari UI
			foreach ($requestedItems as $itemRequest) {
				$itemnum = $itemRequest['itemnum'];
				$planningqty = floatval($itemRequest['planningqty']);
				$warehouse_request = $itemRequest['warehouse']; // Gudang yang diminta user

				if ($planningqty <= 0) continue; // Lewati jika user tidak input kuantitas

				$whereClauseStok = "itemnum='$itemnum' AND warehouse='$warehouse_request' AND quantity > 0";
				
				$setInv = new Set();
				$invMbo = $setInv->MboSetRemote("invfifolifocost")->setWhere($whereClauseStok)->MboRemote("SUM(quantity) as total_available")->getMboSet()->getMbo();
				$total_available = floatval($invMbo->getString("total_available"));
				
				// --- Langkah B: Validasi Ketersediaan Stok ---
				if ($total_available < $planningqty) {
					$errorMessage = "Planning failed for item {$itemnum}. Needed: {$planningqty}, Available: {$total_available}.";
					self::sendJsonResponse('error', $errorMessage); // 409 Conflict
				}

				// --- Langkah C: "Panen" Stok dari invfifolifocost (FIFO) ---
				$setFifo = new Set();
				$fifoStockSet = $setFifo->MboSetRemote("invfifolifocost")
										->setWhere($whereClauseStok)
										->order("costdate ASC")
										->getMboSet();
				
				$remaining_needed = $planningqty;
				// Loop melalui setiap batch fisik untuk memenuhi permintaan
				for ($k = 0; $k < $fifoStockSet->counts(); $k++) {
					if ($remaining_needed <= 0) break; 
					$fifoMbo = $fifoStockSet->getMbo($k);
					$fifo_qty = floatval($fifoMbo->getString('quantity'));
					$conditiontype = $fifoMbo->getString('conditiontype');
					$take_from_this_batch = min($fifo_qty, $remaining_needed);

					// --- Langkah D: INSERT baris detail ke woplanmaterial ---
					$planlinenum = Xbo::getLineNumber("woplanmaterial", "planlinenum", "wonum='$wonum'");
					$unitcost = floatval($fifoMbo->getString('unitcost'));
					$linecost = $take_from_this_batch * $unitcost;

					$setPlan = new Set();
					$from_warehouse = $fifoMbo->getString('warehouse');
					
					$glcreditacct = Xbo::getControlAcct($from_warehouse, $orgcode, $sitecode);
					$setPlan->MboSetRemote("woplanmaterial")
						->setValue("wonum", $wonum)
						->setValue("planlinenum", $planlinenum)
						->setValue("itemnum", $itemnum)
						->setValue("quantity", $take_from_this_batch) // Qty yang diambil dari batch ini
						->setValue("unitcost", $unitcost)
						->setValue("linecost", $linecost)
						->setValue("conditiontype", $conditiontype)
						->setValue("warehouse", $from_warehouse) // Lokasi fisik aktual
						->setValue("racknum", $fifoMbo->getString('racknum'))     // Lokasi fisik aktual
						->setValue("invfifolifocostid", $fifoMbo->getString('invfifolifocostid')) // Jejak audit
						->setValue("enterby", $loggedInUser)
						->setValue("enterdate", $enterdate)
						->setValue("gldebitacct", $gldebitacct)
						->setValue("glcreditacct", $glcreditacct)
						->add();
					
					$remaining_needed -= $take_from_this_batch;
				}
			} // End of foreach loop untuk setiap item

			
			// SELESAI
			

		} catch (Exception $e) {
			self::sendJsonResponse('error', 'A critical error occurred: ' . $e->getMessage()); //500
		}
	
		self::sendJsonResponse('success', 'Work Order materials planned successfully.');
	}
	public static function cpw_services($prnum, $wonum, $orgcode, $sitecode, $budgetlinenum=null){
		$linetype = "services";
		$receipt_status = "NONE";
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$whereclause = "wonum='$wonum'";
	
		$woplanServiceSet = (new Set())->useTransactionManager()->MboSetRemote("woplanservice")->setWhere($whereclause)
		->MboRemote("description")->MboRemote("orderunit")->MboRemote("quantity")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("planlinenum")
		->MboRemote("serviceitemnum")
		->getMboSet();
		
		$counts = $woplanServiceSet->counts();
		if($counts > 0){
			for($i=0; $i<$counts; $i++){
				$woplanservice = $woplanServiceSet->getMbo();
				$prlinenum = Xbo::getLineNumber("prline", "prlinenum", "prnum='$prnum'");
				$description = $woplanservice->getString("description");
				$orderunit = $woplanservice->getString("orderunit");
				$quantity = $woplanservice->getString("quantity");
				$unitcost = $woplanservice->getString("unitcost");
				$linecost = $woplanservice->getString("linecost");
				$planlinenum = $woplanservice->getString("planlinenum");
				$serviceitemnum = $woplanservice->getString("serviceitemnum");
				
				$whereclause_prline = "prnum='$prnum' and linetype = 'services' and description ='$description'";
				$prlineSet =(new Set())->useTransactionManager()->MboSetRemote("prline")->setWhere($whereclause_prline)->getMboSet();
				$count_prline = $prlineSet->counts();
				
				$wo_account =   Xbo::getWOAccount($wonum, $orgcode, $sitecode);		
				
				if($count_prline == 0){
					(new Set())->useTransactionManager()->MboSetRemote("prline")
					->setValue("prnum", $prnum)
					->setValue("prlinenum", $prlinenum)
					->setValue("linetype", $linetype)
					->setValue("description", $description)
					->setValue("orderqty", $quantity)
					->setValue("orderunit", $orderunit)
					->setValue("unitcost", $unitcost)
					->setValue("linecost", $linecost)
					->setValue("wonum", $wonum)
					->setValue("gldebitacct", $wo_account)
					->setValue("receiptstatus", $receipt_status)
					->setValue("enterdate", $enterdate)
					->setValue("enterby", $enterby)
					->setValue("serviceitemnum", $serviceitemnum)
					->setValue("conversionfactor", 1)
					->setValue("budgetlinenum", $budgetlinenum)
					->add();
					
					//update prnum/prline information to woplanservice
					(new Set())->useTransactionManager()->MboSetRemote("woplanservice")->setWhere("wonum='$wonum' and planlinenum='$planlinenum'")
					->setValue("prnum", $prnum)->setValue("prlinenum", $prlinenum)->change();
					
				}else{
					continue;
				}
			}
			$msg = "PR-Service has been created";
		}else{
			$msg = "No Planning Service Found";
		}
		
		return $msg;
	}
	public static function cpw_item($prnum, $wonum, $orgcode, $sitecode, $budgetlinenum=null){
		$linetype = "item";
		$receipt_status = "NONE";
		$whereclause = "wonum='$wonum'";
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$woplanmaterialSet = (new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->setWhere($whereclause)
		->MboRemote("planlinenum")->MboRemote("itemnum")->MboRemote("quantity")->MboRemote("unitcost")->MboRemote("linecost")
		->MboRemote("conditiontype")
		->MboRemote("warehouse")->MboRemote("racknum")->getMboSet();
		$counts = $woplanmaterialSet->counts();
		if($counts>0){
			for($i=0; $i<$counts; $i++){
				
				$woplanmaterial = $woplanmaterialSet->getMbo();
				$prlinenum = Xbo::getLineNumber("prline", "prlinenum", "prnum='$prnum'");
				$itemnum = $woplanmaterial->getString("itemnum");
				$quantity = $woplanmaterial->getString("quantity");
				$unitcost = $woplanmaterial->getString("unitcost");
				$linecost = $woplanmaterial->getString("linecost");
				$conditiontype = $woplanmaterial->getString("conditiontype");
				$warehouse = $woplanmaterial->getString("warehouse");
				$racknum = $woplanmaterial->getString("racknum");
				$planlinenum = $woplanmaterial->getString("planlinenum");
				
				//get order unit from item 
				$item = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->getMboSet()->getMbo();
				$orderunit = $item->getString("orderunit");
				$description = $item->getString("description");
				
				//check if wo plan item is exist in prline
				$where_prl = "itemnum = '$itemnum' and wonum = '$wonum'";
			
				$prlineSet = (new Set())->useTransactionManager()->MboSetRemote("prline")->setWhere($where_prl)->getMboSet();
				if($prlineSet->counts() ==0 ){
					
					//get Debit Account
					$warehouse_account = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
					
					(new Set())->useTransactionManager()->MboSetRemote("prline")
					->setValue("prnum", $prnum)
					->setValue("linetype", $linetype)
					->setValue("prlinenum", $prlinenum)
					->setValue("itemnum", $itemnum)
					->setValue("description", $description)
					->setValue("orderqty", $quantity)
					->setValue("orderunit", $orderunit)
					->setValue("unitcost", $unitcost)
					->setValue("linecost", $linecost)
					->setValue("conditiontype", $conditiontype)
					->setValue("warehouse", $warehouse)
					->setValue("gldebitacct", $warehouse_account)
					->setValue("receiptstatus", $receipt_status)
					->setValue("enterdate", $enterdate)
					->setValue("enterby", $enterby)
					->setValue("conversionfactor",1)
					->setValue("budgetlinenum",$budgetlinenum)
					->add();
					
					//update prnum/prline information to woplanservice
					(new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->setWhere("wonum='$wonum' and planlinenum='$planlinenum'")
					->setValue("prnum", $prnum)->setValue("prlinenum", $prlinenum)->change();
					
				}else{
					//if exist, continue
					continue;
				}
			}
			$msg = "PR-Item has been created";
		}else{
			$msg = "No Planning Materials Found.";
		}
		
		return $msg;
	}
	
	
	public static function cpw_sq_item($wonum, $orgcode, $sitecode)
	{
		$woplanmaterialSet = (new Set())->useTransactionManager()
		->MboSetRemote("woplanmaterial wmt")->where("wmt.wonum=?", $wonum)
		->setRelationship("workorder wo", "wmt.wonum = wo.wonum")
		->setRelationship("item it", "wmt.itemnum = it.itemnum")
		->setRelationship("warehouse wh", "wmt.warehouse = wh.warehouse")
		->MboRemote("wmt.itemnum, wmt.quantity, wmt.unitcost, wmt.linecost, 
		wmt.warehouse, wmt.racknum, wo.glaccount, it.description, 
		it.orderunit, it.manufacturer, wh.controlacc")
		->getMboSet();

		if ($woplanmaterialSet->isEmpty()) {
			return null;
		}

		$sqlInsert = [];   // HARUS array kosong

		while ($result = $woplanmaterialSet->fetch()) {
			
			$sqlInsert[] = [
				"linetype"       => "item",
				"serviceitemnum" => null,
				"sellqty"       => $result["quantity"],
				"unitcost"       => $result["unitcost"],
				"linecost"       => $result["linecost"],
				"wonum"          => null,
				"glcreditacct"   => $result["controlacc"],
				"description"    => $result["description"],
				"orderunit"      => $result["orderunit"],
				"itemnum"		 => $result["itemnum"], 
				"warehouse"		 => $result["warehouse"],
				"manufacturer"	 => $result["manufacturer"]
			];
			
		}

		return $sqlInsert;
	}


	public static function cpw_sq_services($wonum, $orgcode, $sitecode)
	{
		$woplanserviceSet = (new Set())->useTransactionManager()
			->MboSetRemote("woplanservice wps")
			->setRelationship("serviceitems sit", "wps.serviceitemnum = sit.serviceitemnum")
			->setRelationship("workorder wo", "wps.wonum = wo.wonum")
			->where("wps.wonum=?", $wonum)
			->MboRemote("wps.serviceitemnum, wps.quantity, wps.unitcost, wps.linecost, 
						wps.wonum, wo.glaccount, wps.description, wps.orderunit")
			->getMboSet();

		if ($woplanserviceSet->isEmpty()) {
			return null;
		}

		$sqlInsert = [];

		while ($result = $woplanserviceSet->fetch()) {
			
			$sqlInsert[] = [
				"linetype"       => "services",
				"serviceitemnum" => $result["serviceitemnum"],
				"sellqty"       => $result["quantity"],
				"unitcost"       => $result["unitcost"],
				"linecost"       => $result["linecost"],
				"wonum"          => $result["wonum"],
				"glcreditacct"   => $result["glaccount"],
				"description"    => $result["description"],
				"orderunit"      => $result["orderunit"],
				"itemnum"		 => null, 
				"warehouse"		 => null, 
				"manufacturer"	 => null,
			];
		}

		
		return $sqlInsert;
	}

	public static function calculatePRCost($prnum){

		$prSet = (new Set())->useTransactionManager()->MboSetRemote("prline prl")
		->setRelationship("pr", "pr.prnum = prl.prnum")
		->setWhere("prl.prnum='$prnum'")
		->MboRemote("sum(prl.linecost) pretaxtotal")
		->MboRemote("sum(prl.taxcost) totaltax")
		->MboRemote("pr.exchangerate")
		->getMboSet();
		$pr = $prSet->getMbo();
		$pretaxtotal = $pr->getString("pretaxtotal");
		$totaltax =  $pr->getString("totaltax");
		$exchangerate =  $pr->getString("exchangerate");
		
		$totalcost = floatval($pretaxtotal) + floatval($totalcost);
		$totalbasecost = $totalcost*floatval($exchangerate);
		
		//update Cost - PR Header 
		(new Set())->useTransactionManager()->MboSetRemote("pr")->setWhere("prnum='$prnum'")
		->setValue("pretaxtotal", $pretaxtotal)
		->setValue("totaltax", $totaltax)
		->setValue("totalcost", $totalcost)
		->setValue("totalbasecost", $totalbasecost)
		->change();
		
	}

	public static function calculateSQCost($sqnum){
		$response = "ok";
		return $response;
	}

	public static function createsqfromwo(){
		$wonum = Super::getParam("createsqfromwo");
		$sqnum = Super::getParam("sqnum");
		$sqdesc = Super::getParam("sqdesc");
		$linetype = Super::getParam("linetype");
		$woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet();

		if ($woSet->isEmpty()) {
			Super::sendJsonResponse("error", "Work Order not found: $wonum");
		}

		$wo = $woSet->getMbo();
		$orgcode = $wo->getString("orgcode");
		$sitecode = $wo->getString("sitecode");
		$requestedby = $wo->getString("owner");
		$status = $wo->getString("status");
		$customercode = $wo->getString("customercode");


		if($status == "cancel" || $status == "close" || $status == "complete"){
			Super::sendJsonResponse("error", "Cannnot create SQ because Work Order Status has been $status.");
		}

		if(empty($customercode)){
			Super::sendJsonResponse("error", "Cannnot create SQ because Customer Code is empty.");
		}
		//Check SQ with current WORK ORDER
		
		$sqSet =(new Set())->useTransactionManager()->MboSetRemote("salesquotation")->where("wonum=?", $wonum)->MboRemote("salesquotenum")->getMboSet();
		if(!$sqSet->isEmpty()){
			$sq = $sqSet->getMbo();
			$existing_sqnum = $sq->getString("salesquotenum");
			$message = "SQ $existing_sqnum has already Exist for Work Order : $wonum";
		}


		$currencycode = (new Set())->useTransactionManager()->MboSetRemote("organization")->where("orgcode =?", $orgcode)->getMboValue("basecurrency");
		
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$orderdate = $enterdate;
		$sentstatus = "NOTSENT";
		$exchangerate = 1;
		$status = "new";

		(new Set())->useTransactionManager()->MboSetRemote("salesquotation")
		->setValue("salesquotenum", $sqnum)
		->setValue("description", $sqdesc)
		->setValue("customercode", $customercode)
		->setValue("orderdate", $orderdate)
		->setValue("sentstatus", $sentstatus)
		->setValue("enterdate", $enterdate)
		->setValue("orgcode", $orgcode)
		->setValue("sitecode", $sitecode)
		->setValue("currencycode", $currencycode)
		->setValue("exchangerate", $exchangerate)
		->setValue("statusdate", $enterdate)
		->setValue("wonum", $wonum)
		->add();

		$sqList = [];

		$data_item = null;
		$data_services = null;

		if ($linetype == "item") {
			$data_item = self::cpw_sq_item($wonum, $orgcode, $sitecode);
		} elseif ($linetype == "services") {
			$data_services = self::cpw_sq_services($wonum, $orgcode, $sitecode);
		} elseif ($linetype == "item_services") {

			$data_item = self::cpw_sq_item($wonum, $orgcode, $sitecode);
			$data_services = self::cpw_sq_services($wonum, $orgcode, $sitecode);
		}

		if (!empty($data_item)) {
			$sqList = array_merge($sqList, $data_item);
		}

		if (!empty($data_services)) {
			$sqList = array_merge($sqList, $data_services);
		}

		$line = 10; // mulai dari 10
		foreach ($sqList as $index => $row) {
			$sqList[$index]['salesquotenum'] = $sqnum;
			$sqList[$index]['salesquotationlinenum'] = $line;

			$line += 10;
		}
		
		(new Set())->useTransactionManager()->MboSetRemote("salesquotationline")->addStream($sqList);
		

		Super::sendJsonResponse("success", "Sales Quotation has been created : $sqnum", $sqList);
	}

	// Create PR Purchase Requisition from WO
	public static function createprfromwo() {
		$wonum = Super::getParam("createprfromwo");
		$prnum = Super::getParam("prnum");
		$requesteddate = Super::getParam("requesteddate");
		$linetype = Super::getParam("linetype");
		$prdesc = Super::getParam("prdesc");
		
		$response = array("status" => "error", "msg" => null, "msg_item" => null, "msg_service" => null); 

		try {
			// Retrieve WO information
			$woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)
				->MboRemote("orgcode, sitecode, owner, budgetnum, budgetlinenum, budgetlinenum2")->getMboSet();

			if ($woSet->counts() == 0) {
				throw new Exception("Work Order not found: $wonum");
			}

			$wo = $woSet->getMbo();
			$orgcode = $wo->getString("orgcode");
			$sitecode = $wo->getString("sitecode");
			$requestedby = $wo->getString("owner");
			$budgetnum = $wo->getString("budgetnum");
			$budgetlinenum = $wo->getString("budgetlinenum");
			$budgetlinenum2 = $wo->getString("budgetlinenum2");

			if(!empty($budgetlinenum2)) {
				$budgetlinenum = $budgetlinenum2;
			}

			// Check if existing PR for this work order exist
			$whereclause = "wonum='$wonum' AND status not in ('cancel')";
		
			$prSet =(new Set())->useTransactionManager()->MboSetRemote("pr")->setWhere($whereclause)->MboRemote("prnum")->getMboSet();
			$counts = $prSet->counts();

			if ($counts > 0) {
				$pr = $prSet->getMbo();
				$existing_prnum = $pr->getString("prnum");
				$response["msg"] = "PR $existing_prnum has already Exist for Work Order : $wonum";
			} else {
				// Create PR Header
				// 1. get Base Currency
				$currencycode = (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("orgcode ='$orgcode'")->MboRemote("basecurrency")->getMboSet()->getMbo()->getString("basecurrency");
				
				// 2. get Login User;
				$enterby = Xbo::getLoginUser();
				// 3. get Enter Date( timestamp )
				$enterdate = Xbo::setEnterDate();
				// PR init Status 
				$status = "new";
				(new Set())->useTransactionManager()->MboSetRemote("pr")
					->setValue("prnum", $prnum)
					->setValue("description", $prdesc)
					->setValue("requesteddate", $requesteddate)
					->setValue("requestedby", $requestedby)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("currencycode", $currencycode)
					->setValue("status", $status)
					->setValue("statusdate", $enterdate)
					->setValue("wonum", $wonum)
					->setValue("budgetnum", $budgetnum)
					->add();

				if ($linetype == "item") {
					$response["msg_item"] = self::cpw_item($prnum, $wonum, $orgcode, $sitecode, $budgetlinenum);
				} elseif ($linetype == "services") {
					$response["msg_service"] = self::cpw_services($prnum, $wonum, $orgcode, $sitecode, $budgetlinenum);
				} elseif ($linetype == "item_services") {
					$response["msg_item"] = self::cpw_item($prnum, $wonum, $orgcode, $sitecode, $budgetlinenum);
					$response["msg_service"] = self::cpw_services($prnum, $wonum, $orgcode, $sitecode, $budgetlinenum);
				}

				
				self::calculatePRCost($prnum);
				
				$response["status"] = "success";
				$response["msg"] = "PR $prnum created successfully.";
				
				
			}
		} catch (Exception $e) {
			$response["msg"] = "Error: " . $e->getMessage();
		}

		Set::throws($response);
	}
	public static function getSQNumber(){
		$wonum = Super::getParam("getSQNumber");
		
		//check if prnum is exist
		$set = new Set();
		$sqSet = $set->MboSetRemote("salesquotation")->setWhere("wonum='$wonum'")->MboRemote("salesquotenum")->getMboSet();
		$counts = $sqSet->counts();
		
		if($counts == 0 ){
			$getAutoNum = new Super();
			$sqnum = $getAutoNum->getAutoNumber("salesquotation", "update_next_value")->autonumber;
			Set::throws(["new_sqnum"=>$sqnum, "msg"=>"ok"]);
		}else{
			$sq = $sqSet->getMbo();
			$existing_sqnum = $sq->getString("sqnum");
			Set::throws(["new_sqnum"=>$existing_sqnum, "msg"=>"sq_exist"]);
		}
		
	}
	public static function getPRNumber(){
		$wonum = Super::getParam("getPRNumber");
	
		$set = new Set();
		$prSet = $set->MboSetRemote("pr")->where("wonum=?", $wonum)->MboRemote("prnum")->getMboSet();
		$counts = $prSet->counts();
	
		if($counts == 0 ){
			$getAutoNum = new Super();
			$prnum = $getAutoNum->getAutoNumber("pr", "update_next_value")->autonumber;
			Set::throws(["new_prnum"=>$prnum, "msg"=>"ok"]);
		}else{
			$pr = $prSet->getMbo();
			$existing_prnum = $pr->getString("prnum");
			
			//check apakah PR cancel?
			$status = (new Set())->useTransactionManager()->MboSetRemote("pr")->where("prnum=?", $existing_prnum)->getMboValue("status");
		
			if($status == "cancel"){
				$getAutoNum = new Super();
				$prnum = $getAutoNum->getAutoNumber("pr", "update_next_value")->autonumber;
				Set::throws(["new_prnum"=>$prnum, "msg"=>"ok"]);
				exit;
			}else{
				Set::throws(["new_prnum"=>$existing_prnum, "msg"=>"pr_exist"]);
			}
		}
		
	}
	public static function calculateLaborCost(){
		$ss = new stdClass;
		

		$actualworkhours = filter_input(INPUT_POST, "actualworkhours", FILTER_VALIDATE_FLOAT);
		$maxovertimehours =  filter_input(INPUT_POST, "maxovertimehours", FILTER_VALIDATE_FLOAT);
		$unitcost =  filter_input(INPUT_POST, "unitcost", FILTER_VALIDATE_FLOAT);
		$overtimecost =  filter_input(INPUT_POST, "overtimecost", FILTER_VALIDATE_FLOAT);
		$startdate =  $_POST["startdate"]; 
		$enddate =  $_POST["enddate"];
		$wonum = $_POST["calculateLaborCost"];
		

		if ($actualworkhours === false || $maxovertimehours === false || $unitcost === false || $overtimecost === false) {
			Super::sendJsonResponse("error", "Input number is not valid.");
			return;
		}


		$strStartDate = strtotime($startdate);
		$strEndDate =  strtotime($enddate);

		if ($strEndDate < $strStartDate) {
			Super::sendJsonResponse("error", "End Date cannot be earlier than Start Date.");
			return;
		}


		
		$countSeconds = ($strEndDate-$strStartDate);
		$countHours = ($countSeconds)/3600;
		$countDays = $countHours/24;
		$countFreeHours = $countHours - $actualworkhours;
		$totalcostOvertime = $overtimecost*$maxovertimehours;
		$totalcost = $unitcost*$actualworkhours;
		$totalcostAndOvertime = $totalcostOvertime+$totalcost;
		$totalworkHours = $actualworkhours+$maxovertimehours;
		
		$ss->msg = "success";
		$ss->countDays = $countDays;
		$ss->countHours = $countHours;
		$ss->countFreeHours = $countFreeHours;
		$ss->totalcostOvertime = $totalcostOvertime;
		$ss->totalcost = $totalcost;
		$ss->totalcostAndOvertime = $totalcostAndOvertime;
		$ss->totalworkHours = $totalworkHours;
		Set::throws($ss);
	}
	public static function maximoChangeStatusWorkOrder($data){
		
		
		$wonum = $data->changestatuswo;
		$orgcode = $data->orgcode;  
		$objectname = $data->objectname; 
		$status = $data->status; 
		if(isset($data->status)){
			$status = $data->status;
		}else{
			$status = "approved";
		}
		
		$wc = "wonum='$wonum' and orgcode = '$orgcode'";
		$set = new Set();
		$workorderSet = $set->MboSetRemote("workorder")->setWhere($wc)->getMboSet();
		$countWO = $workorderSet->counts();
		if($countWO == 0){
			$msg="WO_DOES_NOT_EXIST";
		}else{
			$statusdate = Xbo::setEnterDate();
			$set = new Set();
			$workorderSet = $set->MboSetRemote("workorder")->setWhere($wc)->setValue("status", $status)->setValue("statusdate", $statusdate)->change();
			
			$enterdate = Xbo::setEnterDate();
			$enterby = Xbo::getLoginUser();
			$changeby = $enterby;
			$changedate = $enterdate;
			$xboset = $objectname;
			if ($xboset == "workorder") {
				$set = new Set();
				$woplanmaterialSet = $set->MboSetRemote("woplanmaterial")->setWhere($wc)->MboRemote("wonum")
				->MboRemote("itemnum")->MboRemote("quantity")->MboRemote("conditiontype")
				->MboRemote("warehouse")->MboRemote("racknum")->getMboSet();
				
				$countWOPlan = $woplanmaterialSet->counts();
				if($countWOPlan >0 ){
					for ($i = 0; $i < $woplanmaterialSet->counts(); $i++) {
						$woplanmaterial = $woplanmaterialSet->getMbo();
						$wonum = $woplanmaterial->getString("wonum");
						$itemnum = $woplanmaterial->getString("itemnum");
						$quantity = $woplanmaterial->getString("quantity");
						$warehouse = $woplanmaterial->getString("warehouse");
						$conditiontype = $woplanmaterial->getString("conditiontype");
						$racknum = $woplanmaterial->getString("racknum");
						$set2 = new Set();
						$set2->MboSetRemote("invreserve")->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)
						->setValue("quantity", $quantity)->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)
						->setValue("racknum", $racknum)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
					}
				}
				
			}
			if ($xboset == "mr") {
				$set = new Set();
				$mrlineSet = $set->MboSetRemote("mrline")->setRelationship("mr", "mr.mrnum = mrline.mrnum")->setWhere("mrline.mrnum='$wonum'")
				->MboRemote("mrline.mrnum")->MboRemote("mr.wonum")->MboRemote("mrline.itemnum")->MboRemote("mrline.quantity")
				->MboRemote("mrline.conditiontype")->MboRemote("mrline.warehouse")
				->MboRemote("mrline.racknum")->getMboSet();
				
				$countMR = $mrlineSet->counts();
				if($countMR >0){
					for ($i = 0; $i < $mrlineSet->counts(); $i++) {
						$mrline = $mrlineSet->getMbo();
						$mrnum = $mrline->getString("mrnum");
						$wonum = $mrline->getString("wonum");
						$itemnum = $mrline->getString("itemnum");
						$quantity = $mrline->getString("quantity");
						$warehouse = $mrline->getString("warehouse");
						$conditiontype = $mrline->getString("conditiontype");
						$racknum = $mrline->getString("racknum");
						
						$set2 = new Set();
						$set2->MboSetRemote("invreserve")->setValue("mrnum", $mrnum)
						->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)->setValue("quantity", $quantity)
						->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)
						->setValue("racknum", $racknum)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
					}
				}
				
			}
			
			$msg = "WO_STATUS_CHANGED";
		}
		
		$ss = new stdClass();
		$ss->msg = $msg;
		Set::throws($ss);
	}
	
	public static function maximoAddWorkOrderPlanMaterial(){
		
		$wonum = $_POST["wonum"];
		$invreservenum = $_POST["invreservenum"];
		$itemnum = $_POST["itemnum"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		$conditiontype = $_POST["conditiontype"];
		$quantity = $_POST["quantity"];
		$unitcost = $_POST["unitcost"];
		$linecost = $_POST["linecost"];
		$enterby = $_POST["enterby"];
		$glcreditacct = $_POST["glcreditacct"];
		$gldebitacct = $_POST["gldebitacct"];
		$remarks = $_POST["gldebitacct"];
		$enterdate = Xbo::setEnterDate();
		$planlinenum = Xbo::getLineNumber("woplanmaterial", "planlinenum", "wonum='$wonum'");
		
		if (empty($conditiontype)) {
			$condval = " and (conditiontype is null or conditiontype='') ";
		}else{
			$condval = " and conditiontype='$conditiontype'";
		}
		if (empty($racknum)) {
			$rackval = " and (racknum is null or racknum='')";
		}else{
			$rackval = " and racknum='$racknum'";
		}
		
		
		$wc = "wonum='$wonum' and invreservenum='$invreservenum' and warehouse='$warehouse' $condval $rackval ";
		$set = new Set();
		$woplanSet = $set->MboSetRemote("woplanmaterial")->setWhere($wc)->getMboSet();
		$counts = $woplanSet->counts();
		
		if($counts == 0){
			$woplan = $woplanSet->getMbo();
			$set2 = new Set();
			$set2->MboSetRemote("woplanmaterial")
			->setValue("invreservenum", $invreservenum)
			->setValue("planlinenum", $planlinenum)
			->setValue("itemnum", $itemnum)
			->setValue("warehouse", $warehouse)
			->setValue("racknum", $racknum)
			->setValue("conditiontype", $conditiontype)
			->setValue("quantity", $quantity)
			->setValue("unitcost", $unitcost)
			->setValue("linecost", $linecost)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("gldebitacct", $gldebitacct)
			->setValue("glcreditacct", $glcreditacct)
			->setValue("remarks", $remarks)
			->setValue("wonum", $wonum)
			->add();
			$msg = "added";
			$sql = "";
			
			//wonum ,itemnum , quantity, warehouse, racknum, conditiontype, enterby, enterdate
			
			$set = new Set();
			$set->MboSetRemote("invreserve")
			->setValue("wonum", $wonum)
			->setValue("itemnum", $itemnum)
			->setValue("quantity", $quantity)
			->setValue("warehouse", $warehouse)
			->setValue("racknum", $racknum)
			->setValue("conditiontype", $conditiontype)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("invreservenum", $invreservenum)
			->add();
			
			
		}else{
			$msg = "changed";
			$sql = $woplanSet->sql;
		}
		
		$ss = new stdClass;
		$ss->msg = $msg;
		$ss->sql = $sql;
		Set::throws($ss);
		
	}
	
	public static function maximoAddWorkOrder(){
		
		$ss = new stdClass;	
		$msg  = null;
		$wonum = $_POST["maximo_add_wonum"];
		$wodesc = $_POST["description"];
		$assetnum = $_POST["assetnum"];
		$location =  $_POST["location"];
		$owner =  $_POST["owner"];
		$leader =  $_POST["leader"];
		$targstartdate =  $_POST["targstartdate"];
		$targcompdate = $_POST["targcompdate"];
		$priority =  $_POST["priority"];
		$status =  $_POST["status"];
		$orgcode =  $_POST["orgcode"];
		$sitecode =  $_POST["sitecode"];
		$maximo_workorderid =  $_POST["maximo_workorderid"];
		$statusdate = Xbo::setEnterDate();
		
		$wc = "wonum='$wonum' and orgcode = '$orgcode'";
		$set = new Set();
		$WOSet = $set->MboSetRemote("workorder")->setWhere($wc)->getMboSet();
		$counts = $WOSet->counts();
		
		if($counts == 0){
			$set = new Set();
			$set->MboSetRemote("workorder")
			->setValue("wonum", $wonum)
			->setValue("description", $wodesc)
			->setValue("status", $status)
			->setValue("statusdate", $statusdate)
			->setValue("assetnum", $assetnum)
			->setValue("location", $location)
			->setValue("priority", $priority)
			->setValue("owner", $owner)
			->setValue("leader", $leader)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("targstartdate", $targstartdate)
			->setValue("targcompdate", $targcompdate)
			->add();
			$ss->msg = "added";
		}else{
			$set = new Set();
			$set->MboSetRemote("workorder")->setWhere($wc)
			->setValue("description", $wodesc)
			->setValue("status", $status)
			->setValue("statusdate", $statusdate)
			->setValue("assetnum", $assetnum)
			->setValue("location", $location)
			->setValue("priority", $priority)
			->setValue("owner", $owner)
			->setValue("leader", $leader)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("targstartdate", $targstartdate)
			->setValue("targcompdate", $targcompdate)
			->change();
			$ss->msg = "changed";
		}
		
		$aa = XboSet::createBarcode("workorders", $maximo_workorderid, $wonum);
		
		$ss->barcode_folder =  $aa->barcode_folder;
		$ss->filePath =  $aa->filePath;
		$ss->barcode_content =  $aa->barcode_content;
		Set::throws($ss);
	}
	
	
	public static function calculateworkprogress(){
		$wonum = $_POST["calculateworkprogress"];
		$ss = new stdClass;
		$ss->wonum = $wonum;
		$msg = null;
		$currentdate = Xbo::setEnterDate();
		$timeCurrentDate = strtotime($currentdate);
		//check the startdate and enddate of wo task
		
		$set = new Set();
		$wotaskSet = $set->MboSetRemote("wotask")->setWhere("wonum='$wonum'")
		->MboRemote("tasklinenum")->MboRemote("description")->MboRemote("targstartdate")->MboRemote("targcompdate")->MboRemote("progress")->MboRemote("wotaskid")
		->getMboSet();
		
		$wotaskCount = $wotaskSet->counts();
		
		$wotaskr = [];
		if($wotaskCount>0){
			for($i=0; $i<$wotaskCount; $i++){
				
				$sr = new stdClass;
				$wotask = $wotaskSet->getMbo();
				$tasklinenum = $wotask->getString("tasklinenum");
				$description = $wotask->getString("description");
				$targstartdate = $wotask->getString("targstartdate");
				$targstartdate = date("Y-m-d", strtotime($targstartdate));  
				
				$targfinishdate = $wotask->getString("targcompdate");
				$targfinishdate =date("Y-m-d", strtotime($targfinishdate));  
				
				$progress = $wotask->getString("progress");
				$wotaskid = $wotask->getString("wotaskid");
				
				$sr->tasklinenum = $tasklinenum;
				$sr->description = $description;
				$sr->targstartdate = $targstartdate;
				$sr->targfinishdate = $targfinishdate;
				$sr->progress = $progress;
				$sr->duration = 0;
				$sr->targetcompletion = 0;
				$sr->gap = 0;
				
				if( ($targstartdate && $targfinishdate != null) || ($targstartdate && $targfinishdate != "") ){
					
					$date1 = strtotime($targstartdate);
					$date2 = strtotime($targfinishdate);
					$diff = $date2 - $date1;
					$sr->duration = floor($diff / (60 * 60 * 24))+1;
					$targcom_minus_currentdate = $date2-$timeCurrentDate;
					$sr->currentDuration = 	floor($targcom_minus_currentdate / (60 * 60 * 24))+1;
					$sr->targetcompletion = ($sr->duration-$sr->currentDuration)/$sr->duration;	
					if($sr->targetcompletion >= 100) $sr->targetcompletion = 100;
					if($sr->targetcompletion <= 0) $sr->targetcompletion = 0;
					$sr->gap = round($sr->targetcompletion-$sr->progress);
					$sr->targetcompletion = $sr->targetcompletion*100;
					$sr->msg = "calculated";
					array_push($wotaskr, $sr);
					$setwt = new Set();
					$setwt->MboSetRemote("wotask")->setWhere("wotaskid='$wotaskid'")
					->setValue("targetcompletion", $sr->targetcompletion )
					->setValue("gapprogress", $sr->gap )
					->setValue("duration", $sr->duration )
					->change();
					
				}else{
					$sr->msg = "Target Start Date or Target Completion Date is null on Line $tasklinenum - $description";
					array_push($wotaskr, $sr);
					break;
				}
				
			}
		}
		
		$ss->wotasks = $wotaskr;
		Set::throws($ss);
	}
	public static function getItemIssueType(){
		$itemnum = $_POST["getItemIssueType"];
		$warehouse = $_POST["warehouse"];
		
		$set = new Set();
		$issuetype = $set->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->getMboSet()->getMbo()->getString("issuetype");
		$ob = new stdClass();
		$ob->issuetype = $issuetype;
		Set::throws($ob);
	}
	public static function getDefaultItemInfo(){
		$itemnum = $_POST["getDefaultItemInfo"];
		$ob=new stdClass();
		$set = new Set();
		$conditiontype = $set->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' and conditionrate=100")->MboRemote("conditiontype")->getMboSet()->getMbo()->getString("conditiontype");
		
		$set = new Set();
		$itemSet = $set->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("issueunit")->MboRemote("orderunit")->MboRemote("itemgroup")->MboRemote("stocktype")->getMboSet();
		$item = $itemSet->getMbo();
		
		$ob->issueunit = $item->getString("issueunit");
		$ob->orderunit = $item->getString("orderunit");
		$ob->itemgroup = $item->getString("itemgroup");		
		$ob->stocktype = $item->getString("stocktype");
		
		
		$ob->conditiontype = $conditiontype;
		Set::throws($ob);
	}

	
	public static function ModifyLaborTransLine(){
		$value = Super::getParam("modifylabortransline");
		$wonum = Super::getParam("wonum");
		$enterdate = Xbo::setEnterDate(); 
		$enterby = Xbo::getLoginUser(); 
		
		// 1. Dapatkan Plan Labor (Mapping Harga)
		$woplanlabor = (new Set())
			->useTransactionManager()
			->MboSetRemote("woplanlabor")
			->where("wonum=?", $wonum)
			->getMboSet()
			->fetchData();

		// Mapping Harga (Pisahkan Team vs Person supaya lookup cepat)
		$costByTeam = [];
		$costByPerson = [];

		foreach ($woplanlabor as $labor) {
			$dataCost = [
				"unitcost"     => (float) $labor["unitcost"],
				"maxhours"     => (float) $labor["maxhours"],
				"overtimecost" => (float) $labor["overtimecost"]
			];

			if (!empty($labor["teamnum"])) {
				$costByTeam[$labor["teamnum"]] = $dataCost;
			} 
			if (!empty($labor["personid"])) {
				$costByPerson[$labor["personid"]] = $dataCost;
			}
		}

		// 2. Dapatkan Member Team (Hanya jika ada input Team)
		$teams = array_values(array_unique(array_filter(array_column($woplanlabor, "teamnum"))));
		
		$membersByTeam = [];
		if (!empty($teams)) {
			$teamMembers = (new Set())
				->useTransactionManager()
				->MboSetRemote("empteam")
				->whereIn("teamnum", $teams)
				->MboRemote("personid, teamnum")
				->getMboSet()
				->fetchData();

			foreach ($teamMembers as $tm) {
				$membersByTeam[$tm["teamnum"]][] = $tm["personid"];
			}
		}

		// 3. Proses Loop Input Data
		foreach ($value as $row) {
			$startdateReal  = $row["startdate"]; // Tanggal Kerja Asli
			$enddateReal    = $row["enddate"];
			$workhours      = (float) $row["workhours"];
			$regularhours   = (float) $row["regularhours"];
			$extendedhours  = (float) $row["extendedhours"];
			$teamnum        = $row["teamnum"] ?? null;
			$personidInput  = $row["personid"] ?? null;
			$applyovertime  = $row["applyovertime"] ?? "N";

			// Tentukan siapa saja yang akan di-insert ke Detail (Line)
			$targetPersons = [];
			if (!empty($teamnum) && isset($membersByTeam[$teamnum])) {
				$targetPersons = $membersByTeam[$teamnum]; // Expand Team Members
			} else if (!empty($personidInput)) {
				$targetPersons = [$personidInput]; // Single Person
			}

			// Variabel penampung akumulasi untuk Header (LaborTrans)
			$headerLineCost = 0;
			$headerPreLineCost = 0;
			$headerOvertimeCost = 0;
			
			// Data batch insert untuk Line
			$datatoInsertLines = [];
			$headerData =[];
			// Loop per Orang (Detail)
			foreach ($targetPersons as $pid) {
				// -- LOOKUP COST --
				$unitcost = 0; $maxhours = 0; $overtimecost = 0;

				// Prioritas 1: Cek harga Team (jika input via Team)
				if (!empty($teamnum) && isset($costByTeam[$teamnum])) {
					$unitcost     = $costByTeam[$teamnum]["unitcost"];
					$maxhours     = $costByTeam[$teamnum]["maxhours"];
					$overtimecost = $costByTeam[$teamnum]["overtimecost"];
				} 
				// Prioritas 2: Cek harga Individu (jika input via Person atau fallback)
				else if (isset($costByPerson[$pid])) {
					$unitcost     = $costByPerson[$pid]["unitcost"];
					$maxhours     = $costByPerson[$pid]["maxhours"];
					$overtimecost = $costByPerson[$pid]["overtimecost"];
				}

				// -- HITUNG JAM LEMBUR (Dibatasi MaxHours) --
				$finalOvertimeHours = 0;
				if ($applyovertime === "Y" && $extendedhours > 0) {
					if ($maxhours > 0) {
						$finalOvertimeHours = min($extendedhours, $maxhours);
					} else {
						$finalOvertimeHours = $extendedhours; // Tidak ada limit
					}
				}

				// -- HITUNG UANG --
				$costNormal  = $regularhours * $unitcost;
				$costLembur  = $finalOvertimeHours * $overtimecost;
				$costTotal   = $costNormal + $costLembur;

				// Akumulasi ke Header
				$headerLineCost += $costTotal;
				$headerPreLineCost += $costNormal;
				$headerOvertimeCost += $costLembur;

				// Siapkan Data Insert Line
				$datatoInsertLines[] = [
					"wonum"           => $wonum,
					"teamnum"         => $teamnum,
					"personid"        => $pid,
					"startdate"       => $startdateReal, // Pakai tanggal kerja asli
					"enddate"         => $enddateReal,
					"totalhours"      => $workhours,     // Total Durasi (Reg + Ext)
					"reghours"    	  => $regularhours,
					"extendedhours"   => $finalOvertimeHours,
					"hourtype"        => "ONWORK",
					"unitcost"        => $unitcost,
					"overtimecost"    => $overtimecost,
					"prelinecost"     => $costNormal,
					"totalovertimecost"=> $costLembur, // Field baru (Saran)
					"linecost"        => $costTotal,
					"applyovertime"   => $applyovertime,
					"enterby"         => $enterby,
					"enterdate"       => $enterdate
				];
				// remove existing records
				if(!empty($personid)){
					(new Set())->useTransactionManager()->MboSetRemote("labortransline")->where("personid=? and wonum=?", $pid, $wonum)->remove();
				}
				if(!empty($teamnum)){
					(new Set())->useTransactionManager()->MboSetRemote("labortransline")->where("teamnum=? and wonum=?", $teamnum, $wonum)->remove();
				}
			}

			// 4. EKSEKUSI INSERT LINES (Detail per Orang)
			
			if (!empty($datatoInsertLines)) {
				(new Set())
					->useTransactionManager()
					->MboSetRemote("labortransline")
					->addStream($datatoInsertLines);
			}

			// 5. INSERT HEADER (LaborTrans) - Satu baris per Input Form
			// simpan total akumulasi biaya satu geng/satu orang tadi
			
			// Ambil cost referensi untuk header (bisa ambil dari Team atau Orang pertama)
			// Header biasanya cuma nyatat Rate standar, tapi Total Cost-nya adalah Sum
			$headerUnitCost = 0; $headerOvertimeRate = 0; $headerMaxHours = 0;
			
			if (!empty($teamnum) && isset($costByTeam[$teamnum])) {
				$headerUnitCost = $costByTeam[$teamnum]["unitcost"];
				$headerOvertimeRate = $costByTeam[$teamnum]["overtimecost"];
				$headerMaxHours = $costByTeam[$teamnum]["maxhours"];
			} elseif (!empty($personidInput) && isset($costByPerson[$personidInput])) {
				$headerUnitCost = $costByPerson[$personidInput]["unitcost"];
				$headerOvertimeRate = $costByPerson[$personidInput]["overtimecost"];
				$headerMaxHours = $costByPerson[$personidInput]["maxhours"];
			}

			$headerData [] = [
				"wonum"             => $wonum,
				"teamnum"           => $teamnum,
				"personid"          => $personidInput, // Bisa null kalau input by Team
				"startdate"         => $startdateReal,
				"enddate"           => $enddateReal,
				"workhours"         => $workhours,       // Durasi input user
				"reghours"      	=> $regularhours,
				"extendedhours"     => $extendedhours,   // Durasi lembur input user (sebelum dicap Max)
				"maxhours"          => $headerMaxHours,
				"applyovertime"     => $applyovertime,
				"unitcost"          => $headerUnitCost,      // Rate referensi
				"overtimecost"      => $headerOvertimeRate,  // Rate referensi
				
				// KOLOM TOTAL (AKUMULASI DARI ANAK BUAH/LINES)
				"prelinecost"       => $headerPreLineCost,      // Total Biaya Normal semua anak buah
				"totalovertimecost" => $headerOvertimeCost,     // Total Biaya Lembur semua anak buah
				"linecost"          => $headerLineCost,         // Grand Total Biaya
				"permitted"         => "Y"
			];
		}

		//update data header 
		if(!empty($teamnum)){
			(new Set())->useTransactionManager()->MboSetRemote("labortrans")->where("wonum=? AND teamnum=?", $wonum, $teamnum)
			->setValue("startdate", $startdateReal)
			->setValue("enddate", $enddateReal)
			->setValue("workhours", $workhours)
			->setValue("reghours", $regularhours)
			->setValue("extendedhours", $extendedhours)
			->setValue("maxhours", $headerMaxHours)
			->setValue("applyovertime", $applyovertime)
			->setValue("unitcost", $headerUnitCost)
			->setValue("overtimecost", $headerOvertimeRate)
			->setValue("prelinecost", $headerPreLineCost)
			->setValue("totalovertimecost", $headerOvertimeCost)
			->setValue("linecost", $headerLineCost)
			->setValue("permitted", "Y")
			->change();
		}

		if(!empty($personid)){
			(new Set())->useTransactionManager()->MboSetRemote("labortrans")->where("wonum=? AND personid=?", $wonum, $personidInput)
			->setValue("startdate", $startdateReal)
			->setValue("enddate", $enddateReal)
			->setValue("workhours", $workhours)
			->setValue("reghours", $regularhours)
			->setValue("extendedhours", $extendedhours)
			->setValue("maxhours", $headerMaxHours)
			->setValue("applyovertime", $applyovertime)
			->setValue("unitcost", $headerUnitCost)
			->setValue("overtimecost", $headerOvertimeRate)
			->setValue("prelinecost", $headerPreLineCost)
			->setValue("totalovertimecost", $headerOvertimeCost)
			->setValue("linecost", $headerLineCost)
			->setValue("permitted", "Y")
			->change();
		}
		Super::sendJsonResponse("success", "Labor Transaction has been updated");
	}
	
	public static function ActualProgress(){
		$data = $_POST['actprogress'];
		$wonum = $_POST["wonum"];
		$set = new Set();
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$countdata = count($data);
		for($i=0; $i<$countdata; $i++){
			$dt = $data[$i];
			$id = $dt["wotaskid"];
			
			$set = new Set();
			$wtaskset = $set->MboSetRemote("wotask")->setWhere("wotaskid='$id'")->getMboSet();
			$wtcount = $wtaskset->counts();
			
			for($j=0; $j<$wtcount; $j++){
				$wt = $wtaskset->getMbo();
				$wonum = $wt->getString("wonum");
				$tasklinenum = $wt->getString("tasklinenum");
				$description = $wt->getString("description");
				$targstartdate = $wt->getString("targstartdate");
				$targcompdate = $wt->getString("targcompdate");
				$loglinenum = Xbo::getLineNumber("wologs", "loglinenum", "wonum='$wonum'");
				
				$set2 = new Set();
				$set2->MboSetRemote("wologs")->setValue("loglinenum", $loglinenum)->setValue("wonum", $wonum)->setValue("tasklinenum", $tasklinenum)->setValue("description", $description)
				->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)->add();
					
			}
		}
		
		Set::throws(["msg"=>"added"]);
	}
	public static function ModifyShipment(){
		$data = $_POST['viewmodshipment'];
		$wonum = $_POST["wonum"];
		$set = new Set();
		$countdata = count($data);
		for($i=0; $i<$countdata; $i++){
			$dt = $data[$i];
			$id = $dt["woshipmentid"];
			$remarks = $dt["remarks"];
			$longlat = $dt["longlat"];
			
			$set = new Set();
			$set->MboSetRemote("woshipment")->setWhere("woshipmentid='$id'")->setValue("remarks", $remarks)->setValue("longlat", $longlat)->change();
		}
		Set::throws(["msg"=>"modded"]);
	}
	

	public static function DispatchServices() {
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$data = Super::getParam("dispatchservices"); 
			$wonum = Super::getParam("wonum");
			$actualdate = Super::getParam("actualdate");

			if (empty($wonum)) {
				throw new Exception("Work Order number is required.");
			}
			if (empty($data) || !is_array($data)) {
				throw new Exception("Please select at least one service record to receive.");
			}

			$errorMessages = [];
			$servicesToInsert = [];
			
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();
			$orgInfo = Xbo::getOrgcode($enterby);
			$financialPeriod = Xbo::getFinancialPeriod();
			
			foreach ($data as $row) {
				$ponum = $row["ponum"] ?? null;
				$polinenum = $row["polinenum"] ?? null;
				$linetype = $row["linetype"] ?? null;
					
				if (strtolower($linetype) !== "service" && strtolower($linetype) !== "services") {
					$errorMessages[] = "<li>PO: {$ponum}, Line: {$polinenum} is an 'ITEM' type and cannot be received here. Please use the Goods Receipt application.</li>";
					continue;
				}

				$polineMbo = (new Set())->useTransactionManager()
										->MboSetRemote("poline")
										->where("ponum = ? AND polinenum = ?", $ponum, $polinenum)
										->getMboSet()->getMbo();

				if (!$polineMbo) {
					$errorMessages[] = "<li>PO Line not found for PO: {$ponum}, Line: {$polinenum}.</li>";
					continue;
				}

				$quantityToReceive = (float)($row["quantity"] ?? 0);
				$orderedQty = $polineMbo->getDecimal("orderqty");

			
				$previouslyReceivedQty = (new Set())->useTransactionManager()
													->MboSetRemote("servrectrans")
													->where("ponum = ? AND polinenum = ?", $ponum, $polinenum)
													->MboRemote("SUM(quantity) as total_received")
													->getMboSet()->getMbo()->getDecimal('total_received') ?? 0;

			
				if ($previouslyReceivedQty >= $orderedQty) {
					$errorMessages[] = "<li>Service for PO: {$ponum}, Line: {$polinenum} has already been fully received.</li>";
					continue; 
				}
				
				// 3. Jika belum lunas, pastikan penerimaan baru tidak melebihi sisa
				$remainingQty = $orderedQty - $previouslyReceivedQty;
				if ($quantityToReceive > $remainingQty) {

					$errorMessages[] = "<li>Receipt quantity ({$quantityToReceive}) for PO: {$ponum}, Line: {$polinenum} exceeds the remaining quantity ({$remainingQty}). Use SRI instead.</li>";
					continue;
				}
			
				$serviceTrans = [
					"ponum" => $ponum, 
					"polinenum" => $polinenum,
					"wonum" => $wonum,
					"linetype" => 'services',
					"description" => $row["description"],
					"remarks" => "Service Receipt from Work Order application",
					"quantity" => $quantityToReceive,
					"orderunit" => $row["orderunit"],
					"unitcost" => (float)($row["unitcost"] ?? 0),
					"linecost" => (float)($row["linecost"] ?? 0),
					"taxcost" => $polineMbo->getDecimal("taxcost"),
					"gldebitacct" => $polineMbo->getString("gldebitacct"),
					"glcreditacct" => $polineMbo->getString("glcreditacct"),
					"transtype" => "RECEIPT",
					"orgcode" => $orgInfo->orgcode,
					"sitecode" => $orgInfo->sitecode,
					"actualdate" => $actualdate,
					"enterby" => $enterby,
					"enterdate" => $enterdate,
					"financialperiod" => $financialPeriod
				];

				$servicesToInsert[] = $serviceTrans;
			}

			if (!empty($servicesToInsert)) {
				(new Set())->useTransactionManager()
						->MboSetRemote("servrectrans")
						->addStream($servicesToInsert);
			}

			$responseMessage = "";
			if (!empty($servicesToInsert)) {
				$responseMessage .= count($servicesToInsert) . " service(s) have been successfully received.";
			}

			if (!empty($errorMessages)) {
				$responseMessage .= "<br><br>However, some items were skipped:<hr><ul>" . implode("", $errorMessages) . "</ul>";
				if (empty($servicesToInsert)) {
					throw new Exception(implode(' | ', $errorMessages));
				}
			}
			
			$tm->commit();
			Super::sendJsonResponse("success", $responseMessage);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "The process failed: " . $e->getMessage());
		}
	}
	public static function ChangeShippingStatus(){
		
		$wonum = $_POST["wonum"];
		$cntshipstatus = count($_POST["changeshippingstatus"]);
		$statusdate = Xbo::setEnterDate();
		
		$set = new Set();
		$wocustSet = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("customercode")->MboRemote("orgcode")->getMboSet();
		$wocust = $wocustSet->getMbo();
		$customercode = $wocust->getString("customercode");
		$orgcode = $wocust->getString("orgcode");
		
		$set = new Set();
		$custSet = $set->MboSetRemote("customer")->setWhere("customercode='$customercode'")->MboRemote("maxcost")->getMboSet();
		$cust = $custSet->getMbo();
		$maxcost = $cust->getString("maxcost");
		
		if(empty($maxcost) || $maxcost == null) $maxcost == 0;
		if($maxcost == 0) $maxcost = 0;
		
		$set = new Set();
		$currentholdSet = $set->MboSetRemote("custbalanceprice")->setWhere("customercode='$customercode'")->MboRemote("unitpricediscount")->MboRemote("curbal")->getMboSet();
		$currentCount = $currentholdSet->counts();
		
		$totalcurrentPrice = 0;
		for($i=0; $i<$currentCount; $i++){
			$curr = $currentholdSet->getMbo();
			$unitpricediscount = $curr->getString("unitpricediscount");
			$curbal = $curr->getString("curbal");
			$linepricediscount = $unitpricediscount*$curbal;
			$totalcurrentPrice = $linepricediscount+$totalcurrentPrice;
		}
		
		$totalcurrentPrice = $totalcurrentPrice;
		
		
		//echo "$totalcurrentPrice vs $maxcost";
		for($i=0; $i<$cntshipstatus; $i++){
			if($totalcurrentPrice <= $maxcost || $maxcost == 0){
				$a = $_POST["changeshippingstatus"][$i];
				$shipstatus = $a["shipstatus"];
				$shipagent = $a["shipagent"];
				$remarks = $a["remarks"];
				$actualdate = $a["actualdate"];
				$matusetransid = $a["matusetransid"];
				$longlat = $a["longlat"];
				$wc = "matusetransid=$matusetransid";
				
				$setm = new Set();
				$matuseSet = $setm->MboSetRemote("matusetrans")->setWhere("matusetransid = '$matusetransid' ")->MboRemote("linepricediscount")->getMboSet();
				$matuse = $matuseSet->getMbo();
				$linepricediscount = $matuse->getString("linepricediscount");
				
				$totalcurrentPrice2 = $totalcurrentPrice+$linepricediscount;
				if($totalcurrentPrice2 <= $maxcost){
					if(empty($shipstatus)){
						$msg = "nullshipstatus";
					}else{
						$sm = new Set();
						$matuseSet = $sm->MboSetRemote("matusetrans")->setRelationship("workorder", "matusetrans.wonum = workorder.wonum")->setWhere("matusetrans.matusetransid = '$matusetransid'")->MboRemote("matusetrans.wonum")->MboRemote("matusetrans.expireddate")
						->MboRemote("matusetrans.costdate")->MboRemote("matusetrans.conditiontype")->MboRemote("matusetrans.racknum")->MboRemote("matusetrans.warehouse")->MboRemote("matusetrans.quantity")
						->MboRemote("matusetrans.itemnum")->MboRemote("matusetrans.batchnum")->MboRemote("matusetrans.issuetype")->MboRemote("matusetrans.matusetransid")->MboRemote("workorder.customercode")
						->MboRemote("matusetrans.unitcommission")->MboRemote("matusetrans.linecommission")
						->getMboSet();
						
						$matuse = $matuseSet->getMbo();
						$batchnum = $matuse->getString("batchnum");
						$costdate = $matuse->getString("costdate");
						$expireddate = $matuse->getString("expireddate");
						$conditiontype = $matuse->getString("conditiontype");
						$racknum = $matuse->getString("racknum");
						$warehouse = $matuse->getString("warehouse");
						$quantity = $matuse->getString("quantity");
						$itemnum = $matuse->getString("itemnum");
						$customercode = $matuse->getString("customercode");
						$costtype = $matuse->getString("issuetype");
						$unitcommission = $matuse->getString("unitcommission");
						$linecommission = $matuse->getString("linecommission");
						
						if($shipstatus == "DELIVERED"){
							$custbalObj = new stdClass();
							$custbalObj->batchnum = $batchnum;
							$custbalObj->costdate = $costdate;
							$custbalObj->wonum = $wonum;
							$custbalObj->expireddate = $expireddate;
							$custbalObj->conditiontype = $conditiontype;
							$custbalObj->racknum = $racknum;
							$custbalObj->warehouse = $warehouse;
							$custbalObj->quantity = $quantity*-1;
							$custbalObj->itemnum = $itemnum;
							$custbalObj->actualdate = $actualdate;
							$custbalObj->costtype = $costtype;
							
							$scs = new Set();
							$custshipmentSet = $scs->MboSetRemote("custshipment")
							->setWhere("wonum='$wonum' and itemnum='$itemnum' and tocustomercode='$customercode'")
							->MboRemote("quantity")->getMboSet();
							$custshipment = $custshipmentSet->getMbo();
							$remainingQty = $custshipment->getString("quantity");
							if($remainingQty == null || $remainingQty == $custbalObj->quantity) $remainingQty = 0;
							
							
							$custbalObj->quantity = $custbalObj->quantity-$remainingQty;
							$ss = XboSet::AddToCustBalance($custbalObj);
							
							$sq = new Set();
							$sq->MboSetRemote("custshipment")->setWhere("wonum='$wonum' and itemnum='$itemnum' and tocustomercode='$customercode'")
							->remove();			
								
							if($ss == "notreseller"){
									$msg = $ss;
							}else{
								
								$set2 = new Set();
								$set2->MboSetRemote("matusetrans")->setWhere($wc)->setValue("shipagent", $shipagent)
								->setValue("shipstatus", $shipstatus)->setValue("longlat", $longlat)->setValue("shipremarks", $remarks)->setValue("arrivaldate", $actualdate)->change();
								$msg = "issued";
								
								$set3 = new Set();
								$woshipmentSet = $set3->MboSetRemote("woshipment")->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)->setValue("keyid", $matusetransid)->setValue("status", $shipstatus)
								->setValue("statusdate", $statusdate)->setValue("remarks", $remarks)->setValue("longlat", $longlat);
								/**/
								if($shipstatus == "DELIVERED"){
									$woshipmentSet = $woshipmentSet->setValue("customercode", $customercode);
									$setss = new Set();
									$setss->MboSetRemote("matusetrans")->setWhere($wc)->setValue("isconsignment", "Y")->change();
								}
								$woshipmentSet = $woshipmentSet->add();
							}
							
							
							
						}else{
							if($shipstatus == "SHIPPED"){
								$set2 = new Set();
								$set2->MboSetRemote("matusetrans")->setWhere($wc)->setValue("shipagent", $shipagent)
								->setValue("shipstatus", $shipstatus)->setValue("longlat", $longlat)->setValue("shipremarks", $remarks)->setValue("shipdate", $actualdate)->change();
								
								$set3 = new Set();
								$woshipmentSet = $set3->MboSetRemote("woshipment")->setValue("customercode", $customercode)->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)->setValue("keyid", $matusetransid)->setValue("status", $shipstatus)
								->setValue("statusdate", $statusdate)->setValue("remarks", $remarks)->setValue("longlat", $longlat)->add();
								
							}
							
							$set3 = new Set();
							//wonum, itemnum, customercode = orgcode, tocustomercode, keyid, quantity, status, statusdate, shipdate, receipdate, remarks, longlat
							if($quantity<0) $quantity = $quantity*-1;
							$set3->MboSetRemote("custshipment")->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)->setValue("customercode", $orgcode)
							->setValue("tocustomercode", $customercode)->setValue("keyid", $matusetransid)->setValue("quantity", $quantity)
							->setValue("status", $shipstatus)->setValue("statusdate", $statusdate)->setValue("shipdate", $actualdate)->setValue("remarks", $remarks)->setValue("longlat", $longlat)
							->add();
							$msg = "updated";
						}
						
					}
				}else{
					$msg = "exceed";
				}
				
				
			}else{
				$msg = "exceed";
			}
		}
		Set::throws(["msg" => $msg]);
		
	}
	public static function CheckShipItems(){
		$wonum = $_POST['checkshipitems'];
		$set = new Set();
		
		$MatuseSet = $set->MboSetRemote("matusetrans")->setWhere("wonum='$wonum' and (shipstatus not in('DELIVERED') or shipstatus is null)")
		->MboRemote("shipvia")->MboRemote("shipagent")->MboRemote("shipdate")->MboRemote("shipstatus")->MboRemote("estdatearrival")->MboRemote("longlat")->MboRemote("shipremarks")->getMboSet();
		
		$count = $MatuseSet->counts();
		if($count==0) {
			Set::throws(["msg"=>"itemnotexist"]);
		}else{
			$matuse = $MatuseSet->getMbo();
			$shipvia = $matuse->getString("shipvia");
			$shipagent = $matuse->getString("shipagent");
			$shipdate = $matuse->getString("shipdate");
			$shipstatus = $matuse->getString("shipstatus");
			$estdatearrival = $matuse->getString("estdatearrival");
			$longlat = $matuse->getString("longlat");
			$remarks = $matuse->getString("shipremarks");
			
			Set::throws(["msg"=>"itemexist", 
			"shipvia"=>$shipvia,
			"shipagent"=>$shipagent,
			"shipdate"=>$shipdate,
			"newshipstatus"=>$shipstatus,
			"estdatearrival"=>$estdatearrival,
			"longlat"=>$longlat,
			"remarks"=>$remarks
			]);
		}
	}
	
	public static function CheckSubRecord($node = null){
	
		$chksubrecord = $_POST['chksubrecord'];
		$chksubkey = $_POST['chksubkey'];
		$tablename = $_POST['ddtablename'];
		
		
		$count = 0;
		if($tablename == "assets"){
			$tablesubs = "assetsubs";
			$tablesubswhere = "subassetnum='$chksubrecord'";
			$tablesubcol =  "subassetnum";
			$tablecol =  "assetnum";
		}else{
			if($tablename == "workorder"){
				$tablesubs = "wosubs";
				$tablesubswhere = "subwonum='$chksubrecord'";
				$tablesubcol =  "subwonum";
				$tablecol =  "wonum";
			}
		}
		$setas = new Set();
		$subassetSet = $setas->MboSetRemote($tablesubs)->setWhere($tablesubswhere)->MboRemote($tablesubcol)->MboRemote($tablecol)->getMboSet();
	
		$subasset = $subassetSet->getMbo();		
		$newkey = $subasset->getString($tablecol);
		$newsubassetnum = $subasset->getString($tablesubcol);
		$count = $subassetSet->counts();
		
		if($count > 0){
			Set::throws(["msg"=>"exist", "parent"=>$newkey]);
		}else{
		
			$a = new XboSet();
			$isparentExist = $a->CheckAllParent($chksubkey, $chksubrecord, $tablename);
		}
	}
	public static function UpdateTicket(){
		$origrecordclass = $_POST["origrecordclass"];
		$origrecordid = $_POST["origrecordid"];
		$wostatus = $_POST["wostatus"];
		$wonum = explode("|", $_POST["wonum"])[0];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		
		$actual_finish_date = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboValue("actfinishdate");
		if(!empty($actual_finish_date)) $changedate = $actual_finish_date;

		
		$set = new Set();		
		if(!empty ($origrecordid)){
			$set->MboSetRemote("ticket")->setWhere("ticketnum='$origrecordid'")->setValue("status", $wostatus)->setValue("statusdate", $changedate)->change();
			$set->MboSetRemote("ticketstatus")->setValue("ticketnum", $origrecordid)->setValue("status", $wostatus)->setValue("changedate", $changedate)
			->setValue("changeby", $changeby)->setValue("remarks", "Changed from WO: $wonum")->add();
		}
		
		$set = new Set();
		if($wostatus == "inprogress") {
			$set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->setValue("actstartdate", $changedate)->change();
		}else{
			if($wostatus == "completed") {
				$set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->setValue("actfinishdate", $changedate)->change();
			}
		}
		echo "$changedate";
	}
	public static function ValidateMaterialPlanQty()
	{
		$payload = Super::getParam("validatematplanqty");
		$itemnum = $payload["itemnum"];
		$warehouse = $payload["warehouse"];
		$racknum = $payload["racknum"];
		$conditiontype = $payload["conditiontype"];
		$quantity = $payload["quantity"];
		$condval = "";
		$rackval = "";
		
		if (empty($conditiontype)) {
			$condval = " and (conditiontype is null or conditiontype='') ";
		}else{
			$condval = " and conditiontype='$conditiontype'";
		}
		
		$set = new Set();
		$mboSet = $set->MboSetRemote("v_invbalance")
					  ->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")
					  ->MboRemote("sum(curbal) as curbal")
					  ->getMboSet();

		if ($mboSet->counts() > 0) {
			$curbal = $mboSet->getMbo()->getString("curbal");
			$curbal = (float) $curbal;
		} else {
			$curbal = 0;
		}

		if ($quantity > 0) {
			if ($quantity > $curbal) {
				Set::throws(["msg" => "greaterplannedqty", "plannedqty" => $curbal]);
			} else {
				Set::throws(["msg" => "ok", "plannedqty" => $curbal]);
			}
		} else {
			Set::throws(["msg" => "lesserthanzero", "plannedqty" => $curbal]);
		}
	}
	
	public static function RemoveReservedItems()
	{
		
		$payload = Super::getParam("removereserveditems");
		$wonum = $payload["wonum"];
		$set = new Set();
		$set->MboSetRemote("invreserve")->setWhere("wonum='$wonum'")->remove();
	}
	

	public static function ToolsReservation($wonum) {
		
		$dispatchby = Xbo::getLoginUser(); 
		$dispatchdate = Xbo::setEnterDate(); 
		
		$message = ""; 
		$reservedTools = [];
		$requestedTools = [];
		$unavailableTools = [];

		$tm = TransactionManager::getInstance();

		try {
			
			$tm->beginTransaction();

			$set = new Set();
			$set->useTransactionManager();

			$plannedTools = $set->MboSetRemote("woplantool wpt")
				->setRelationship("tools t", "t.toolnum = wpt.toolnum")
				->where("wpt.wonum = ?", $wonum)
				->MboRemote("wpt.toolnum, wpt.quantity, t.status AS toolstatus, t.wonum AS currentwonum") // Ambil status dan WO pemegang saat ini
				->getMboSet()
				->fetchData();

			if (empty($plannedTools)) {
				$tm->commit(); 
				return "No tools planned for this Work Order.";
			}
			
			$reservation_status = "RESERVED";
			
			foreach ($plannedTools as $tool) {
				$toolnum = $tool["toolnum"];
				$toolStatus = $tool["toolstatus"];
				$currentWoNum = $tool["currentwonum"];

				// KASUS 1: Tool tersedia di gudang
				if ($toolStatus == "AVAILABLE") {
					// Buat reservasi/dispatch.
					$reservationSet = new Set();
					$reservationSet->useTransactionManager()
						->MboSetRemote("toolreservation")
						->setValue("toolnum", $toolnum)
						->setValue("wonum", $wonum)
						->setValue("status", $reservation_status) 
						->setValue("dispatchby", $dispatchby)
						->setValue("dispatchdate", $dispatchdate)
						->add();
					
					$reservedTools[] = $toolnum;

				// KASUS 2: Tool sedang dipakai oleh WO lain
				} else if ($toolStatus == "IN USE" || $toolStatus == "IN-USE") {
					$requestSet = new Set();
					$requestSet->useTransactionManager()
						->MboSetRemote("toolrequest")
						->setValue("toolnum", $toolnum)
						->setValue("fromwonum", $currentWoNum) 
						->setValue("towonum", $wonum)
						->setValue("requestorid", $dispatchby)
						->setValue("requestdate", $dispatchdate)
						->setValue("status", "PENDING") 
						->add();
					$requestedTools[] = "$toolnum (from WO: $currentWoNum)";

				} else {
					$unavailableTools[] = "$toolnum (Status: $toolStatus)";
				}
			}

			$messageParts = [];
			if (!empty($reservedTools)) {
				$messageParts[] = "Tool(s) are successfully reserved: " . implode(', ', $reservedTools) . ".";
			}
			if (!empty($requestedTools)) {
				$messageParts[] = "Sent lend request for Tool(s): " . implode(', ', $requestedTools) . ".";
			}
			if (!empty($unavailableTools)) {
				$messageParts[] = "Could not process Tool((s): " . implode(', ', $unavailableTools) . ". Please find alternatives.";
			}
			
			$message = implode(' ', $messageParts);
			
			$tm->commit();

		} catch (Exception $e) {
			
			$tm->rollback();
			return "An error occurred during tool reservation: " . $e->getMessage();
		}

		return $message;
	}
	public static function AddItemToInvReserve()
	{
		$payload = Super::getParam("addinvreserve");
		
		$keynum = $payload["wonum"];
		$objectname = $payload["objectname"];
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		
		if ($objectname == "workorder") {
			// 1. Get Work Order Info (Owner & Target Start Date)
			
			$set = new Set();
			$workorderSet = $set->useTransactionManager()->MboSetRemote("workorder")->setWhere("wonum='$keynum'")->MboRemote("owner")->MboRemote("targstartdate")->getMboSet();
			$workorder = $workorderSet->getMbo();
			$reservedfor = $workorder->getString("owner");
			$targstartdate = $workorder->getString("targstartdate");

			// 2. Loop Through All Planned Materials for the Work Order
			$set = new Set();
			$woplanmaterialSet = $set->useTransactionManager()->MboSetRemote("woplanmaterial")->setWhere("wonum='$keynum'")->MboRemote("wonum")->MboRemote("itemnum")->MboRemote("quantity")->MboRemote("conditiontype")->MboRemote("warehouse")->MboRemote("racknum")->getMboSet();
			
			// Pastikan ada material sebelum looping
			if ($woplanmaterialSet->counts() > 0) {
				for ($i = 0; $i < $woplanmaterialSet->counts(); $i++) {
					
					$woplanmaterial = $woplanmaterialSet->getMbo(); 
					$wonum = $woplanmaterial->getString("wonum");
					$itemnum = $woplanmaterial->getString("itemnum");
					$quantity_needed = floatval($woplanmaterial->getString("quantity")); 
					$warehouse = $woplanmaterial->getString("warehouse");
					$conditiontype = $woplanmaterial->getString("conditiontype");
					$racknum = $woplanmaterial->getString("racknum");
					
					// Variabel untuk audit trail
					$enterby = Xbo::getLoginUser(); 
					$enterdate = date("Y-m-d H:i:s");
					$changeby = $enterby; // Saat add, changeby = enterby

					
					// START: BUSINESS LOGIC PENENTUAN STATUS (BEST PRACTICE)
					// A. Buat klausa WHERE yang konsisten untuk query inventory & reservation
					
					$rackval = Xbo::RackWhereClause($racknum);
					$condval = Xbo::CondTypeWhereClause($conditiontype);
					$whereclauseItem = "itemnum='$itemnum' AND warehouse='$warehouse' $condval";
					// B. Hitung total stok fisik yang ada di gudang
					$setInv = new Set();
					$inventorySet = $setInv->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($whereclauseItem)->MboRemote("SUM(quantity) as total_qty")->getMboSet();
					$inventory = $inventorySet->getMbo();				
					$total_qty_on_hand = $inventory ? floatval($inventory->getString("total_qty")) : 0;
					
					// C. Hitung total stok yang sudah direservasi oleh orang lain
					$setRes = new Set();
					$invreserveSet = $setRes->useTransactionManager()->MboSetRemote("invreserve")->setWhere($whereclauseItem . " AND status='RESERVED'")->MboRemote("SUM(quantity) as total_qty_reserved")->getMboSet();
					$invreserve = $invreserveSet->getMbo();
					$total_qty_reserved = $invreserve ? floatval($invreserve->getString("total_qty_reserved")) : 0;
					
					// D. Hitung stok yang benar-benar tersedia (Available Stock)
					$available_stock = $total_qty_on_hand - $total_qty_reserved;
					
					// E. Tentukan status berdasarkan ketersediaan stok
					if ($available_stock >= $quantity_needed) {
						$status = "RESERVED";
					} else {
						$status = "WAITING_LIST";
					}
					// END: BUSINESS LOGIC PENENTUAN STATUS
					// START: BUSINESS LOGIC PEMBUATAN INVRESERVENUM (BEST PRACTICE)
					// Metode: PREFIX + KONTEKS + KEUNIKAN
					// - PREFIX: 'RES-' agar mudah diidentifikasi.
					// - KONTEKS: Nomor Work Order ($wonum).
					// - KEUNIKAN: Bagian akhir dari uniqid() untuk mencegah duplikasi jika item yang sama ada 2 kali di satu WO.
					
					$unique_part = substr(uniqid(), -4); // Ambil 4 karakter terakhir dari ID unik berbasis microtime
					$invreservenum = "RES-" . $wonum . "-" . $unique_part;
					// END: BUSINESS LOGIC PEMBUATAN INVRESERVENUM
					// F. Simpan data reservasi ke database
					$setAdd = new Set();
					$setAdd->useTransactionManager()->MboSetRemote("invreserve")
						->setValue("wonum", $wonum)
						->setValue("invreservenum", $invreservenum) 
						->setValue("status", $status)
						->setValue("itemnum", $itemnum)
						->setValue("quantity", $quantity_needed)
						->setValue("warehouse", $warehouse)
						->setValue("conditiontype", $conditiontype)
						->setValue("racknum", $racknum) 
						->setValue("enterby", $enterby)
						->setValue("enterdate", $enterdate)
						->setValue("changeby", $changeby)
						->setValue("reservedfor", $reservedfor)
						->setValue("targstartdate", $targstartdate)
						->add();

					(new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->where("wonum=? AND itemnum=? AND quantity=?", $wonum, $itemnum, $quantity_needed)
					->setValue("invreservenum", $invreservenum)->change();
				}
			}
			
			//$tool_message = self::ToolsReservation($wonum); //we don't need tool reservation
			self::sendJsonResponse("success", "WO has been approved <br>". $tool_message);
		}
		//start mr
		if ($objectname == "mr") {
			// 1. Get MR Header Info (Requester & Required Date)
			$setHeader = new Set();
			$mrHeaderSet = $setHeader->useTransactionManager()->MboSetRemote("mr")->where("mrnum=?", $keynum)->MboRemote("requestedby")->MboRemote("requireddate")->getMboSet();
			$mrHeader = $mrHeaderSet->getMbo();
			
			$reservedfor = $mrHeader->getString("requestedby");
			$targstartdate = $mrHeader->getString("requireddate");

			// 2. Loop Through All Lines in the Material Requisition
			$set = new Set();
			$mrlineSet = $set->useTransactionManager()->MboSetRemote("mrline")->setRelationship("mr", "mr.mrnum = mrline.mrnum")
			->setWhere("mrline.mrnum='$keynum'")->MboRemote("mrline.mrnum")
			->MboRemote("mr.wonum")->MboRemote("mrline.itemnum")
			->MboRemote("mrline.quantity")->MboRemote("mrline.conditiontype")
			->MboRemote("mrline.warehouse")->MboRemote("mrline.racknum")->getMboSet();
			
			if ($mrlineSet->counts() > 0) {
				for ($i = 0; $i < $mrlineSet->counts(); $i++) {
					$mrline = $mrlineSet->getMbo($i);
					$mrnum = $mrline->getString("mrnum");
					$wonum = $mrline->getString("wonum"); 
					$itemnum = $mrline->getString("itemnum");
					$quantity_needed = floatval($mrline->getString("quantity"));
					$warehouse = $mrline->getString("warehouse");
					$conditiontype = $mrline->getString("conditiontype");
					$racknum = $mrline->getString("racknum");

					$enterby = Xbo::getLoginUser();
					$enterdate = date("Y-m-d H:i:s");
					$changeby = $enterby;
					// START: BUSINESS LOGIC PENENTUAN STATUS 
					$rackval = Xbo::RackWhereClause($racknum);
					$condval = Xbo::CondTypeWhereClause($conditiontype);
					$whereclauseItem = "itemnum='$itemnum' AND warehouse='$warehouse' $condval";

					// B. Hitung total stok fisik yang ada di gudang
					$setInv = new Set();
					$inventorySet = $setInv->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($whereclauseItem)->MboRemote("SUM(quantity) as total_qty")->getMboSet();
					$inventory = $inventorySet->getMbo();				
					$total_qty_on_hand = floatval($inventory->getString("total_qty"));

					// C. Hitung total stok yang sudah direservasi oleh orang lain
					$setRes = new Set();
					$invreserveSet = $setRes->useTransactionManager()->MboSetRemote("invreserve")->setWhere($whereclauseItem . " AND status='RESERVED'")->MboRemote("SUM(quantity) as total_qty_reserved")->getMboSet();
					$invreserve = $invreserveSet->getMbo();
					$total_qty_reserved = floatval($invreserve->getString("total_qty_reserved"));
					
					// D. Hitung stok yang benar-benar tersedia (Available Stock)
					$available_stock = $total_qty_on_hand - $total_qty_reserved;
					
					// E. Tentukan status berdasarkan ketersediaan stok
					if ($available_stock >= $quantity_needed) {
						$status = "RESERVED";
					} else {
						$status = "WAITING_LIST";
					}
					// END: BUSINESS LOGIC PENENTUAN STATUS
					// START: BUSINESS LOGIC PEMBUATAN INVRESERVENUM (BEST PRACTICE)
					// Format diadaptasi untuk konteks MR
					$unique_part = substr(uniqid(), -6);
					$invreservenum = "RES-MR-" . $mrnum . "-" . $unique_part;
					// END: BUSINESS LOGIC PEMBUATAN INVRESERVENUM
					// F. Simpan data reservasi ke database dengan logika yang sudah lengkap
					$set2 = new Set();
					$set2->useTransactionManager()->MboSetRemote("invreserve")
						->setValue("mrnum", $mrnum)
						->setValue("wonum", $wonum)
						->setValue("invreservenum", $invreservenum) 
						->setValue("status", $status)
						->setValue("itemnum", $itemnum)
						->setValue("quantity", $quantity_needed)
						->setValue("warehouse", $warehouse)
						->setValue("conditiontype", $conditiontype)
						->setValue("racknum",$racknum)
						->setValue("enterby", $enterby)
						->setValue("enterdate", $enterdate)
						->setValue("changeby", $changeby)
						->setValue("reservedfor", $reservedfor) 
						->setValue("targstartdate", $targstartdate)
						->add();
				}
			}
			
			self::sendJsonResponse("success", "MR has been approved");
		}
		
	}
	


	private static function _validateBaseFields( $wo, array &$missingFields, array &$focusFields) {
		

		if (empty($wo->getString('assetnum')) && empty($wo->getString('location')) && empty($wo->getString('customercode'))) {
			$missingFields[] = "At least one of the following must be filled: Asset, Location, or Customer";
			$focusFields[] = "assetnum"; 
			$focusFields[] = "location"; 
			$focusFields[] = "customercode"; 
		}

		// Validasi Field Wajib
		$required = [
			'glaccount'   => 'GL Account',
			'owner'       => 'Owner', 
			'leader'      => 'Leader', 
			'worktype'    => 'Work Type', 
			'workclass'   => 'Work Class', 
			'priority'    => 'Priority', 
			'reportdate'  => 'Reported Date',
			'description' => 'Description',
			'duration' => 'Duration'
		];
		
		foreach ($required as $field => $label) {
			if (empty($wo->getString($field))) {
				$missingFields[] = $label;
				$focusFields[] = $field;
			}
		}
	}


	private static function _validateApprovalFields($wo, array &$missingFields, array &$focusFields) {
	
		if (empty($wo->getString('targstartdate'))) {
			$missingFields[] = "Target Start Date";
			$focusFields[] = "targstartdate";
		}
		if (empty($wo->getString('targcompdate'))) {
			$missingFields[] = "Target Completion Date";
			$focusFields[] = "targcompdate";
		}
	}

	private static function _validateInProgressFields($wo, array &$missingFields, array &$focusFields) {
	
		if (empty($wo->getString('actstartdate'))) {
			$missingFields[] = "Actual Start Date";
			$focusFields[] = "actstartdate";
		}
	}

	private static function _validateCompletionFields($wo, array &$missingFields, array &$focusFields) {
		
		
		if (empty($wo->getString('actfinishdate'))) {
			$missingFields[] = "Actual Finish Date";
			$focusFields[] = "actfinishdate";
		}

		if($wo->getString("workclass") == "PRODUCTION" || $wo->getString("worktype") == "PRODUCTION" || $wo->getString("workclass") == "PRD" || $wo->getString("workclass") == "ENG") {
			return;
		}
		if (empty($wo->getString('failurecode'))) {
			$missingFields[] = "Failure Code";
			$focusFields[] = "failurecode";
		}
	}

	private static function _validateTransactionsExist(string $wonum, string $new_status) {
		// Fungsi ini akan memeriksa semua jenis transaksi
		$transactions = [
			"invreserve" => "Inventory Reservation",
			"matusetrans" => "Material Issue",
			"servrectrans" => "Service Receipt",
			"tooltrans" => "Tool Usage"
		];
		
		foreach ($transactions as $table => $label) {
			$set = (new Set())->useTransactionManager()->MboSetRemote($table)->where("wonum=?", $wonum)->getMboSet();
			if (!$set->isEmpty()) {
				throw new Exception("Cannot change status to {$new_status}. This WO is linked to a {$label} transaction.");
			}
		}
	}

	private static function _validatePostMortemData(string $wonum, string $new_status) {
		// Fungsi ini memeriksa data setelah pekerjaan selesai

		$workclass = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboValue("workclass");

		if($workclass == "PRODUCTION" || $workclass == "PRD"){
			return;
		}
		
		$worootcauseSet = (new Set())->useTransactionManager()->MboSetRemote("worootcause")->where("wonum=?", $wonum)->getMboSet();
		if ($worootcauseSet->isEmpty()) {

			$buttons = ["addfailurecode", "failurehierarchy", "setfailurezone"];
			Super::sendJsonResponse("error", "Root Cause data is missing. Please describe the failure hierarchy.", ["buttons"=>$buttons]);
			
		}
		
		$wologsSet = (new Set())->useTransactionManager()->MboSetRemote("wologs")->where("wonum=?", $wonum)->getMboSet();
		if ($wologsSet->isEmpty()) {

			$buttons = ["addwologs", "addactualtask"];
			Super::sendJsonResponse("error", "Work Log is missing. Please document the job activities.", ["buttons"=>$buttons]);

		}
		
		$wo =  (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet()->getMbo();
		$progress = $wo->getString("progress");
		if(($status == "completed" || $status = "complete" || $status == "comp") && $progress <= 0){
			Super::sendJsonResponse("error", "The work order is marked as $new_status, , but progress is not 100%. Please update the Work Order Progress.", ["progress"]);
		}
		
		
	}

	private static function _validateOnCancelWorkorder(string $wonum){
		$matuseSet = (new Set())->useTransactionManager()->MboSetRemote("matusetrans")->where("wonum=?", $wonum)->getMboSet();
		if ($matuseSet->isEmpty()) {
			Super::sendJsonResponse("error", "Cannot Cancel Work Order. This Work Order is related to Material Issue Transaction.");
		}


		$servrecSet = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->where("wonum=?", $wonum)->getMboSet();
		if ($servrecSet->isEmpty()) {
			Super::sendJsonResponse("error", "Cannot Cancel Work Order. This Work Order is related to Service Transaction.");
		}


		$tooltransSet = (new Set())->useTransactionManager()->MboSetRemote("tooltrans")->where("wonum=?", $wonum)->getMboSet();
		if ($tooltransSet->isEmpty()) {
			Super::sendJsonResponse("error", "Cannot Cancel Work Order. This Work Order is related to Tool Transactions");
		}


		$labtransSet = (new Set())->useTransactionManager()->MboSetRemote("labortrans")->where("wonum=?", $wonum)->getMboSet();
		if ($labtransSet->isEmpty()) {
			Super::sendJsonResponse("error", "Cannot Cancel Work Order. This Work Order is related to Labor Transactions");
		}

		$invResSet = (new Set())->useTransactionManager()->MboSetRemote("invreserve")->where("wonum=?", $wonum)->getMboSet();
		if ($invResSet->isEmpty()) {
			Super::sendJsonResponse("error", "Cannot Cancel Work Order. This Work Order is related to Item Reservation");
		}

	}


	private static function _validateDateLogic($wo, array &$missingFields) {
		
		
		
		$reportDate = $wo->getDateTimeObject('reportdate');
		$targetStartDate = $wo->getDateTimeObject('targstartdate');
		$targetCompDate = $wo->getDateTimeObject('targcompdate');
		$actualStartDate = $wo->getDateTimeObject('actstartdate');
		$actualFinishDate = $wo->getDateTimeObject('actfinishdate');
		
	
		$today = (new DateTime())->setTime(0, 0);

		// Aturan #1: Validasi Tanggal Lapor (reportdate)
	
		if ($reportDate && $reportDate->setTime(0, 0) > $today) {
			$missingFields[] = "Reported Date cannot be in the future.";
		}

		if ($targetStartDate && $targetCompDate && $targetStartDate > $targetCompDate) {
			$missingFields[] = "Target Start Date must be on or before the Target Completion Date.";
		}
		if ($reportDate && $targetStartDate && $targetStartDate < $reportDate) {
			$missingFields[] = "Target Start Date cannot be earlier than the Reported Date.";
		}

		if ($actualStartDate && $actualFinishDate && $actualStartDate > $actualFinishDate) {
			$missingFields[] = "Actual Start Date must be on or before the Actual Finish Date.";
		}
		if ($reportDate && $actualStartDate && $actualStartDate < $reportDate) {
			$missingFields[] = "Actual Start Date cannot be earlier than the Reported Date.";
		}
		if ($actualFinishDate && $actualFinishDate->setTime(0, 0) > $today) {
			$missingFields[] = "Actual Finish Date cannot be in the future.";
		}
	}

	public static function validateStatusChange() {
		try {
			$payload = Super::getParam("validateStatusChange");
			$wonum = $payload["wonum"];
			$new_status = strtolower($payload["new_status"]); 

			$workorderSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet();
			
			if ($workorderSet->isEmpty()) {
				throw new Exception("Work Order is not found.");
			}

			$wo = $workorderSet->getMbo();
			$current_status= $wo->getString("status");
			$current_status = strtolower($current_status);
			if (in_array($current_status, ["cancel", "closed"])) {
				throw new Exception("You cannot change status once the work order has been {$current_status}.");
			}

			// --- Inisialisasid Error ---
			$missingFields = [];
			$focusFields = [];
			$buttons = [];
			
			// --- Jalankan Aturan Berdasarkan Status Baru ---
			switch ($new_status) {
				case "wappr":
				case "waitappr":
					self::_validateBaseFields($wo, $missingFields, $focusFields);
					self::_validateTransactionsExist($wonum, $new_status);
					break;

				case "appr":
				case "approved":
					self::_validateBaseFields($wo, $missingFields, $focusFields);
					self::_validateApprovalFields($wo, $missingFields, $focusFields);
					break;
				
				case "inprogress":
					self::_validateBaseFields($wo, $missingFields, $focusFields);
					self::_validateApprovalFields($wo, $missingFields, $focusFields);
					self::_validateInProgressFields($wo, $missingFields, $focusFields);
					break;
				case "cancel": 
					self::_validateOnCancelWorkorder($wonum);
					break;
				case "comp":
				case "complete":
				case "completed":
				case "closed":	
					self::_validateBaseFields($wo, $missingFields, $focusFields);
					self::_validateApprovalFields($wo, $missingFields, $focusFields);
					self::_validateInProgressFields($wo, $missingFields, $focusFields);
					self::_validateCompletionFields($wo, $missingFields, $focusFields);
					
					// Jika semua field wajib sudah terisi, baru periksa data post-mortem
					if (empty($missingFields)) {
						self::_validatePostMortemData($wonum, $new_status);
					}
					break;
				
				// Tambahkan case untuk status lain jika ada
			}
			

			//if (!in_array($current_status, ["wappr", "waitappr", "inprogress", "comp", "completed", "complete", "cancel", "close", "closed", "waitmat"])) { 
				self::_validateDateLogic($wo, $missingFields);
			//}

			// --- Cek Hasil Akhir dan Kirim Respons ---
			if (!empty($missingFields)) {
				$message = "Please fill in the following mandatory fields: <br>" . implode("<br>", $missingFields) . ".";
				
				Super::sendJsonResponse("error", $message, array_unique($focusFields));
				return;
			}

			Super::sendJsonResponse("success", "Validation passed. Ready to change status.");

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}

	public static function CreateAssetFromWorkOrder()
	{
		
		$assetnum = $_POST["createassetfromwo"];
		$description = $_POST["description"];
		$location = $_POST["location"];
		$serialnum = $_POST["serialnum"];
		$assettype = $_POST["assettype"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$wonum = $_POST["wonum"];
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		$set = new Set();
		$isExist = $set->MboSetRemote("woproduction")->setWhere("keynum='$assetnum' or wonum='$wonum'")->MboRemote("wonum")->getMboSet()->counts();
		if ($isExist == 0) {
			$set = new Set();
			$set->MboSetRemote("assets")->setValue("assetnum", $assetnum)->setValue("description", $description)->setValue("locnum", $location)->setValue("serialnum", $serialnum)
			->setValue("assettype", $assettype)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
			->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("refwo", $wonum)->add();
			$set = new Set();
			$set->MboSetRemote("assetsstatus")->setValue("assetnum", $assetnum)->setValue("status", "standby")->setValue("changeby", $changeby)->setValue("changedate", $changedate)
			->setValue("remarks", "Created from WO $wonum")->add();
			$set = new Set();
			$set->MboSetRemote("woproduction")->setValue("wonum", $wonum)->setValue("ownertable", "assets")->setValue("keynum", $assetnum)
			->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
			$set = new Set();
			$actualcost = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("actualcost")->getMboSet()->getMbo()->getString("actualcost");
			$set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->setValue("purchaseprice", $actualcost)->setValue("purchasedate", $enterdate)->change();
			$setm = new Set();
			$matuseSet = $setm->MboSetRemote("matusetrans")->setWhere("wonum='$wonum'")->group("description")->MboRemote("sum(quantity)*-1 as quantity")->MboRemote("description")
			->MboRemote("itemnum")->MboRemote("unitcost")->MboRemote("linecost")->getMboSet();
			$countmatuse = $matuseSet->counts();
			
			for($i=0; $i<$countmatuse; $i++){
				$matuse = $matuseSet->getMbo();
				$quantity = $matuse->getString("quantity");
				$description = $matuse->getString("description");
				$itemnum = $matuse->getString("itemnum");
				$unitcost = $matuse->getString("unitcost");
				$linecost = $matuse->getString("linecost");
				if($itemnum != null){
					$setic = new Set();
					$assetsparepartSet = $setic->MboSetRemote("assetsparepart")->setWhere("itemnum='$itemnum' and assetnum='$assetnum'")->MboRemote("itemnum")->getMboSet();
					$countAssetSparepart = $assetsparepartSet->counts();
					$itemStruc = $assetsparepartSet->getMbo();
					if($countAssetSparepart == 0){
						if($itemnum != null) {
							$setis = new Set();
							if($linecost<0) {
								$linecost = $linecost*-1;
							}
							$setis->MboSetRemote("assetsparepart")->setValue("itemnum", $itemnum)->setValue("assetnum", $assetnum)->setValue("quantity", $quantity)
							->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("wonum", $wonum)->add();
						}else{
							continue;
						}
					}else{
						$setis = new Set();
						if($linecost<0) {
							$linecost = $linecost*-1;
						}
						$setis->MboSetRemote("assetsparepart")->setWhere("assetnum='$assetnum' and itemnum='$itemnum'")->setValue("quantity", $quantity)->setValue("unitcost", $unitcost)
						->setValue("linecost", $linecost)->setValue("wonum", $wonum)->change();
					}
				}else{
					continue;
				}
			}
			
			$getAutoNum = new Super();
			$getAutoNum->getAutoNumber("assets", "update_next_value");
			Set::throws(["msg"=>"added"]);
		}
		else {
			Set::throws(["msg"=>"itemexist"]);
		}
	}


	public static function CheckActualMaterialIfExist()
	{
		$set = new Set();
		$wonum = $_GET["checkactmat"];
		$iswoprodExist = $set->MboSetRemote("woproduction")->setWhere("wonum='$wonum'")->MboRemote("wonum")->getMboSet()->counts();
		if ($iswoprodExist == 0) {
			$set = new Set();
			$isMatUseExist = $set->MboSetRemote("matusetrans")->setWhere("wonum='$wonum'")->MboRemote("wonum")->getMboSet()->counts();
			if ($isMatUseExist == 0) {
				$set = new Set();
				$isservExist = $set->MboSetRemote("servrectrans")->setWhere("wonum='$wonum'")->MboRemote("wonum")->getMboSet()->counts();
				if ($isservExist == 0) {
					echo "actmatnotexist";
					return null;
				}else{
					echo "actmatexist";
					return null;
				}
				echo "actmatnotexist";
				return null;
			}else{
				echo "actmatexist";
				return null;
			}
		}else{
			echo "proditemexist";
			return null;
		}
	}




	public static function CreateItemFromWorkOrder()

	{
		$ob = new stdClass();	
		$ob->itemnum = $_POST["createitemfromwo"];
		$ob->itemdesc = $_POST["description"];
		$ob->warehouse = $_POST["warehouse"];
		$ob->curbal = $_POST["quantity"];
		$ob->itemgroup = $_POST["itemgroup"];
		$ob->conditiontype = $_POST["conditiontype"];
		
		$ob->orgcode = $_POST["organization"];
		$ob->sitecode = $_POST["sitecode"];
		$ob->defrack = $_POST["racknum"];
		$ob->stocktype = $_POST["stocktype"];
		$ob->orderunit =$_POST["orderunit"];
		$ob->issueunit = $_POST["issueunit"];
		$ob->wonum = $_POST["wonum"];
		$ob->usetotalresource = $_POST["usetotalresource"];
		$ob->issuetype = $_POST["issuetype"];
		
		$ob->enterdate = Xbo::setEnterDate();
		$ob->enterby = Xbo::getLoginUser();
		$ob->changeby = $ob->enterby;
		$ob->changedate = $ob->enterdate;
	
		$ob->conditionrate = Xbo::getConditionRate($ob->conditiontype);
		
		$ob->glaccount = Xbo::getGLAccount($ob->itemnum);
		
		$ob->shrinkageacc = Xbo::getShrinkageAcct($ob->warehouse, $ob->orgcode, $ob->sitecode);
		$ob->invcostadjacc = Xbo::getInvCostAdjAcct($ob->warehouse, $ob->orgcode, $ob->sitecode);
		$ob->controlacct = Xbo::getControlAcct($ob->warehouse, $ob->orgcode, $ob->sitecode);
		$ob->financialperiod = Xbo::getFinancialPeriod();
		
		$set = new Set();
		$workSet = $set->MboSetRemote("workorder")->setWhere("wonum='".$ob->wonum."'")->MboRemote("actualcost")->MboRemote("rollupcost")->getMboSet();
		$work = $workSet->getMbo();
		$actcost = $work->getString("actualcost");
		$rollupcost = $work->getString("rollupcost");
		
		$ob->actualcost = $actcost;
		if( $rollupcost >= $actcost ) $ob->actualcost = $rollupcost;
		
		$ob->actualcostperitem = $ob->actualcost / $ob->curbal;
		
		if (empty($ob->conditiontype)) {
			$ob->conditiontype = null;
		}
		$setlp = new Set();
		$WOSet = $setlp->MboSetRemote("matusetrans")-> setWhere("wonum = '".$ob->wonum."'")->MboRemote("sum(linecost) as sumitemcost")->getMboSet();
		$WO = $WOSet->getMbo();
		$ob->sumitemcost = $WO->getString("sumitemcost");
		if(empty($ob->sumitemcost)) $ob->sumitemcost = 0;
		
		$ob->itemprodcost = $ob->sumitemcost;
		if($ob->itemprodcost<0) $ob->itemprodcost = $ob->itemprodcost*-1;
		else $ob->itemprodcost = $ob->itemprodcost;
		$ob->itemprodcost = $ob->itemprodcost/$ob->curbal;
		
		if($ob->actualcost == 0) $ob->actualcost = $ob->sumitemcost;
		if($ob->actualcostperitem == 0) $ob->actualcostperitem = $ob->itemprodcost;
		
		ActionSet::CallWOSubs($ob);
	}
	
	public static function CallWOSubs($ob){
		$parentwo = $ob->wonum;
	
		$set = new Set();
		$wosubset = $set->MboSetRemote("wosubs")->setWhere("wonum='$parentwo'")->MboRemote("subwonum")->getMboSet();
		$wosubcnt = $wosubset->counts();
		
		for($i=0; $i<$wosubcnt; $i++){
			$wosubs = $wosubset->getMbo();
			$subwonum = $wosubs->getString("subwonum");
			echo $subwonum."<br>";
		}
	}
	public static function InsertItemStatus($ob){
		$set = new Set();
		$set->MboSetRemote("itemstatus")->setValue("itemnum", $ob->itemnum)->setValue("status", "inactive")->setValue("changeby", $ob->changeby)
		->setValue("changedate", $ob->changedate)->setValue("remarks", "created from WO ".$ob->wonum)->add();
	}
	public static function InsertItemStructure($ob){
		$setm = new Set();
		$matuseSet = $setm->MboSetRemote("matusetrans")->setWhere("wonum='".$ob->wonum."'")->group("description")->MboRemote("sum(quantity)*-1 as quantity")->MboRemote("description")
		->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("unitcost")->MboRemote("linecost")->getMboSet();
		$countmatuse = $matuseSet->counts();
		for($i=0; $i<$countmatuse; $i++){
			$matuse = $matuseSet->getMbo();
			$quantity = $matuse->getString("quantity");
			$description = $matuse->getString("description");
			$subitemnum = $matuse->getString("itemnum");
			$warehouse = $matuse->getString("warehouse");
			$unitcost = $matuse->getString("unitcost");
			$linecost = $matuse->getString("linecost");
			if($ob->itemnum != null){
				$setic = new Set();
				$itemStrucSet = $setic->MboSetRemote("itemstruct")->setWhere("subitemnum='$subitemnum' and itemnum='".$ob->itemnum."'")->MboRemote("subitemnum")->getMboSet();
				$countItemStruct = $itemStrucSet->counts();
				$itemStruc = $itemStrucSet->getMbo();
				if($countItemStruct == 0){
					if($subitemnum != null) {
						if($linecost<0) {
							$linecost = $linecost*-1;
						}
						$setis = new Set();
						$setis->MboSetRemote("itemstruct")->setValue("subitemnum", $subitemnum)->setValue("itemnum", $ob->itemnum)->setValue("quantity", $quantity)->setValue("warehouse", $warehouse)
						->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->add();
					}else{
						continue;
					}
				}else{
					if($linecost<0) {
						$linecost = $linecost*-1;
					}
					$setis = new Set();
					$setis->MboSetRemote("itemstruct")->setWhere("subitemnum='$subitemnum' and itemnum='".$ob->itemnum."'")
					->setValue("quantity", $quantity)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->change();
				}
			}else{
				continue;
			}
		}
	}
	public static function InsertItemCondition($ob){
		if ($ob->conditiontype != null) {
			$set = new Set();
			$isExistSet = $set->MboSetRemote("itemcondition")->setWhere("itemnum='".$ob->itemnum."' and conditiontype = '".$ob->conditiontype."'")->getMboSet();
			$isExist = $isExistSet->counts();
			if($isExist == 0){
				$set2 = new Set();
				$set2->MboSetRemote("itemcondition")->setValue("itemnum", $ob->itemnum)->setValue("conditiontype", $ob->conditiontype)->setValue("conditionrate", 100)
				->setValue("enterby", $ob->enterby)->setValue("enterdate", $ob->enterdate)->setValue("changeby", $ob->changeby)->setValue("changedate", $ob->changedate)->add();
			}
		}
	}
	public static function InsertInventory($ob){
		$set = new Set();
		$inventorySet = $set->MboSetRemote("inventory")->setWhere("itemnum='".$ob->itemnum."' and warehouse = '".$ob->warehouse."'")->getMboSet();
		$isExist = $inventorySet->counts();
		if($isExist == 0){
			$set->MboSetRemote("inventory")->setValue("itemnum", $ob->itemnum)->setValue("warehouse", $ob->warehouse)->setValue("racknum", $ob->defrack)
			->setValue("orgcode", $ob->orgcode)->setValue("sitecode", $ob->sitecode)
			->setValue("eoqqty", 1)->setValue("reorder", "Y")->setValue("issuetype", $ob->issuetype)->add();
		}
	}
	public static function InsertItem($ob){
		$set = new Set();
		$itemSet = $set->MboSetRemote("item")->setWhere("itemnum='".$ob->itemnum."'")->getMboSet();
		$isExist = $itemSet->counts();
		if($isExist == 0) {
			$set2 = new Set();
			$set2->MboSetRemote("item")->setValue("itemnum", $ob->itemnum)
			->setValue("description", $ob->itemdesc)->setValue("itemgroup", $ob->itemgroup)->setValue("orgcode", $ob->orgcode)->setValue("sitecode", $ob->sitecode)
			->setValue("stocktype", $ob->stocktype)->setValue("orderunit", $ob->orderunit)->setValue("issueunit", $ob->issueunit)->setValue("originator", $ob->wonum)
			->setValue("status", "inactive")->setValue("statusdate", $ob->changedate)->setValue("enterby", $ob->enterby)->setValue("enterdate", $ob->enterdate)
			->setValue("changeby", $ob->changeby)->setValue("changedate", $ob->changedate)->add();
			
			$getAutoNum = new Super();
			$getAutoNum->getAutoNumber("item", "update_next_value");	
			
		}
	}
	public static function InsertInvtrans($ob){
		$set = new Set();
		$itemSet = $set->MboSetRemote("invtrans")->setWhere("itemnum='".$ob->itemnum."' and wonum='".$ob->wonum."' and transtype='PRODITEM'")->MboRemote("itemnum")->getMboSet();
		
		$isExist = $itemSet->counts();
		if($isExist == 0) {
			$set2 = new Set();
			$set2->MboSetRemote("invtrans")->setValue("itemnum", $ob->itemnum)->setValue("transtype", "PRODITEM")->setValue("warehouse", $ob->warehouse)->setValue("racknum", $ob->defrack)
			->setValue("conditiontype", $ob->conditiontype)->setValue("quantity", $ob->curbal)->setValue("oldquantity", 0)->setValue("cost", $ob->itemprodcost)->setValue("oldcost", 0)
			->setValue("gldebitacct", $ob->glaccount)->setValue("glcreditacct", $ob->controlacct)->setValue("variancecost", $ob->itemprodcost)->setValue("varianceqty", $ob->curbal)
			->setValue("wonum", $ob->wonum)
			->setValue("orgcode", $ob->orgcode)
			->setValue("sitecode", $ob->sitecode)
			->setValue("enterby", $ob->enterby)->setValue("enterdate", $ob->enterdate)->setValue("financialperiod", $ob->financialperiod)->add();
		}
		
	}
	public static function InsertWOProduction($ob){
	
		$set = new Set();
		$set->MboSetRemote("woproduction")
		->setValue("wonum", $ob->wonum)
		->setValue("ownertable", "item")
		->setValue("keynum", $ob->itemnum)
		->setValue("quantity", $ob->curbal)
		->setValue("enterby", $ob->enterby)
		->setValue("enterdate", $ob->enterdate)
		->setValue("changeby", $ob->changeby)
		->setValue("actualcost", $ob->actualcost)
		->setValue("actualcostperitem", $ob->actualcostperitem)
		->setValue("changedate", $ob->changedate)
		->setValue("issueunit", $ob->issueunit)
		->add();
	}
	public static function InsertInvfifolifocost($ob){
		//echo json_encode($ob, JSON_PRETTY_PRINT);
		$fl = new stdClass();
		$fl->itemnum = $ob->itemnum;
		$fl->racknum = $ob->defrack;
		$fl->warehouse = $ob->warehouse;
		$fl->conditiontype = $ob->conditiontype;
		$fl->enterby = $ob->enterby;
		$fl->costdate = $ob->enterdate;
		$fl->financialperiod = $ob->financialperiod;
		$fl->conditionrate = $ob->conditionrate;
		$fl->orgcode = $ob->orgcode;
		$fl->sitecode = $ob->sitecode;
		$fl->unitcost = $ob->actualcostperitem;
		$fl->quantity = $ob->curbal;
		$fl->wonum = $ob->wonum;
		ActionSet::ReceiptFIFOLIFO($fl);
	}
	
	public static function setConditionTypeValue($ob){
		
		if (!empty($ob->conditiontype)) $condval = " and conditiontype='".$ob->conditiontype."' ";
		else $condval = " and (conditiontype='' or conditiontype is null)";
		
		return $condval;
	}
	public static function setRacknumValue($ob){
		
		if (!empty($ob->defrack)) $rackval = " and racknum='".$ob->defrack."' ";
		else $rackval = " and (racknum='' or racknum is null)";
		return $rackval;
	}
	
	public function InsertInvbalance($ob){
		$condval = ActionSet::setConditionTypeValue($ob);
		$rackval = ActionSet::setRacknumValue($ob);
		$this->oldcurbal = 0;
		$this->curbal = 0;
		
		$whereclause = "itemnum='".$ob->itemnum."' and warehouse='".$ob->warehouse."' $rackval $condval";
		$set = new Set();
		$invbalanceSet = $set->MboSetRemote("invbalance")->setWhere($whereclause)->MboRemote("curbal")->getMboSet();
	
		$invbalance = $invbalanceSet->getMbo();
		$this->oldcurbal = $invbalance->getString("curbal");
		
		
		$set2 = new Set();
		$warehouseSet = $set2->MboSetRemote("warehouse")->setWhere("warehouse='".$ob->warehouse."' and orgcode='".$ob->orgcode."' and sitecode='".$ob->sitecode."'")->MboRemote("isinspection")->getMboSet()->getMbo();
		$isinspection = $warehouseSet->getString("isinspection");
		
		if($isinspection == "Y"){
			$inspstatus = 'waitinsp';
		}else{
			$inspstatus = null;
		}
		
		$ob->racknum = $ob->defrack;
		if (empty($this->oldcurbal)) {
			$this->curbal = $ob->curbal;
			$this->oldcurbal = $ob->curbal;
			$set = new Set();
			$set->MboSetRemote("invbalance")->setValue("itemnum", $ob->itemnum)->setValue("conditiontype", $ob->conditiontype)->setValue("warehouse", $ob->warehouse)
			->setValue("racknum", $ob->racknum)->setValue("curbal", $this->oldcurbal)->setValue("oldcurbal", $this->curbal)->setValue("enterby", $ob->enterby)->setValue("inspectstatus", $inspstatus)
			->setValue("enterdate", $ob->enterdate)->setValue("changeby", $ob->enterby)->setValue("changedate", $ob->changedate)->setValue("orgcode", $ob->orgcode)
			->setValue("sitecode", $ob->sitecode)->add();
		}
		else {
			$this->curbal = $ob->curbal + $this->oldcurbal;
			$set = new Set();
			$set->MboSetRemote("invbalance")->setWhere($whereclause)->setValue("curbal", $this->curbal)->setValue("oldcurbal", $this->oldcurbal)
			->setValue("changeby", $ob->enterby)->setValue("changedate", $ob->changedate)->setValue("inspectstatus", $inspstatus)
			->setValue("sitecode", $ob->sitecode)->change();
		}
		
		$this->oldcurbal = $this->oldcurbal;
		$this->curbal = $this->curbal;
		
		return $this;
	}
	
	public static function InsertInvbalanceInvcost($ob){
		
		$i = new ActionSet();
		$invcost = $i->InsertInvcost($ob);
		$oldavgcost = $invcost->oldavgcost;
		$oldavgprodcost = $invcost->oldavgprodcost;
		
		$invbalance = $i->InsertInvbalance($ob);
		$oldcurbal = $invbalance->oldcurbal;
		$curbal = $invbalance->curbal;
		
		if ($oldavgcost == 0) {
			$newAvgCost = $ob->itemprodcost;
			$newavgprodcost = $ob->actualcostperitem;
		}
		else {
			$actualcostperitem = $ob->actualcostperitem;
			$newAvgCost = (($oldcurbal * $oldavgcost) + ($curbal * $actualcostperitem)) / ($oldcurbal + $curbal);
			$newavgprodcost = (($oldcurbal * $oldavgprodcost) + ($curbal * $actualcostperitem)) / ($oldcurbal + $curbal);
			Xbo::AdjustWTMaterialCost($ob->itemnum, $ob->warehouse, $newAvgCost);
		}
		
		if($ob->usetotalresource  == "Y"){
			$newAvgCost2 = $newAvgCost;
			$newAvgCost = $newavgprodcost;
			$newavgprodcost = $newAvgCost2;
		}
		$set = new Set();
		if(empty($ob->conditiontype)) $condval = " and (conditiontype='' or conditiontype is null)";
		else $condval = "and conditiontype='".$ob->conditiontype."'";
		
		$whereclause = "itemnum='".$ob->itemnum."' and warehouse='".$ob->warehouse."' $condval";
		$set = new Set();
		$invcostSet = $set->MboSetRemote("invcost")->setWhere($whereclause)->getMboSet();
		
		$isExist = $invcostSet->counts();
		
		$set = new Set();
		if ($isExist == 0) {
			$set = new Set();
			$set->MboSetRemote("invcost")->setValue("itemnum", $ob->itemnum)->setValue("warehouse", $ob->warehouse)->setValue("conditiontype", $ob->conditiontype)
			->setValue("conditionrate", $ob->conditionrate)->setValue("stdcost", $newAvgCost)->setValue("avgcost", $newAvgCost)->setValue("oldcost", $oldavgcost)
			->setValue("lastcost", $newAvgCost)->setValue("glaccount", $ob->glaccount)->setValue("shrinkageacc", $ob->shrinkageacc)->setValue("invcostadjacc", $ob->invcostadjacc)
			->setValue("orgcode", $ob->orgcode)->setValue("sitecode", $ob->sitecode)->setValue("lastprodcost", $newavgprodcost)->setValue("wonum", $ob->wonum)->setValue("avgprodcost", $newavgprodcost)
			->setValue("enterdate", $ob->enterdate)->setValue("enterby", $ob->enterby)->setValue("changeby", $ob->enterby)->setValue("changedate", $ob->changedate)->add();
		}else {
			$condval = ActionSet::setConditionTypeValue($ob);
			$set = new Set();
			$set->MboSetRemote("invcost")->setWhere("itemnum='".$ob->itemnum."' and warehouse='".$ob->warehouse."' $condval")->setValue("stdcost", $newAvgCost)
			->setValue("avgcost", $newAvgCost)->setValue("oldcost", $oldavgcost)->setValue("lastcost", $newAvgCost)->setValue("glaccount", $ob->glaccount)
			->setValue("shrinkageacc", $ob->shrinkageacc)->setValue("invcostadjacc", $ob->invcostadjacc)->setValue("lastprodcost", $newavgprodcost)->setValue("wonum", $ob->wonum)
			->setValue("avgprodcost", $newavgprodcost)->setValue("enterdate", $ob->enterdate)->setValue("enterby", $ob->enterby)->setValue("changeby", $ob->enterby)
			->setValue("changedate", $ob->changedate)->change();
		}
	}
	public function InsertInvcost($ob){
		$this->oldavgcost = 0;
		$this->oldavgprodcost = 0;
		$condval = ActionSet::setConditionTypeValue($ob);
		$whereclause = "itemnum='".$ob->itemnum."' and warehouse='".$ob->warehouse."' $condval";
		
		$set = new Set();
		$invcostSet = $set->MboSetRemote("invcost")->setWhere($whereclause)->MboRemote("avgcost")->MboRemote("avgprodcost")->getMboSet();
		$isExist = $invcostSet->counts();
	
		if($isExist > 0){
			$invcost = $invcostSet->getMbo();
			$this->oldavgcost = $invcost->getString("avgcost");
			$this->oldavgprodcost = $invcost->getString("avgprodcost");
			
		}else{
			$this->oldavgcost = 0;
			$this->oldavgprodcost = 0;
		}
		return $this;
	}
	public static function ProcessCreateItem($ob){
		ActionSet::InsertWOProduction($ob);
		ActionSet::InsertItem($ob);
		ActionSet::InsertItemStatus($ob);
		ActionSet::InsertItemStructure($ob);
		ActionSet::InsertItemCondition($ob);
		ActionSet::InsertInventory($ob);
		ActionSet::InsertInvbalanceInvcost($ob);
		ActionSet::InsertInvtrans($ob);
		ActionSet::InsertInvfifolifocost($ob);
		
		Set::throws(["msg"=>"added"]);
	}
	public static function ReceiptFIFOLIFO($fl){
		$enterdate = Xbo::setEnterDate();
		$set = new Set();
		$batchnum = Xbo::getBatchnum();
		$set->MboSetRemote("invfifolifocost")
		->setValue("itemnum", $fl->itemnum)
		->setValue("conditiontype", $fl->conditiontype)
		->setValue("costdate", $fl->costdate)
		->setValue("financialperiod", $fl->financialperiod)
		->setValue("unitcost", $fl->unitcost)
		->setValue("quantity", $fl->quantity)
		->setValue("racknum", $fl->racknum)
		->setValue("conditionrate", $fl->conditionrate)
		->setValue("orgcode", $fl->orgcode)
		->setValue("sitecode", $fl->sitecode)
		->setValue("warehouse", $fl->warehouse)
		->setValue("transtype", "PRODITEM")
		->setValue("enterdate", $enterdate)
		->setValue("batchnum", $batchnum)
		->setValue("wonum", $fl->wonum)
		->add();
	}
	
	public static function CopyWorkPlan()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$wonum = Super::getParam("wonum");
			$wtnum = Super::getParam("copyworkplan"); // ID template

			if (empty($wonum) || empty($wtnum)) {
				throw new Exception("Work Order Number and Template Number are required.");
			}

			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();

			// --- 1. Dapatkan Data Header Template ---
			$wtHeader = (new Set())->useTransactionManager()
				->MboSetRemote("worktemplates")
				->where("wtnum = ?", $wtnum)
				->MboRemote("description, worktype, assetnum, location, workclass, leader, owner, priority")
				->getMboSet()->fetch();

			if (!$wtHeader) {
				throw new Exception("Template $wtnum not found.");
			}

			// --- 2. Update Header Work Order ---
			// update asset/location jika WO asal masih kosong
			$woHeader = (new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->where("wonum = ?", $wonum)
				->MboRemote("assetnum, description")
				->getMboSet()->fetch();

			$updateWO = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum = ?", $wonum);
			$updateWO->setValue("description", $wtHeader['description'] ?? $woHeader['description'])
					->setValue("worktype", $wtHeader['worktype'])
					->setValue("workclass", $wtHeader['workclass'])
					->setValue("leader", $wtHeader['leader'])
					->setValue("owner", $wtHeader['owner'])
					->setValue("priority", $wtHeader['priority']);

			if (empty($woHeader['assetnum'])) {
				$updateWO->setValue("assetnum", $wtHeader['assetnum'])
						->setValue("location", $wtHeader['location']);
			}
			$updateWO->change();

			// --- 3. Pembersihan Data Plan WO Lama ---
			$tablesToClean = ["wotask", "woplanlabor", "woplantool", "woplanservice", "woplanmaterial"];
			foreach ($tablesToClean as $tbl) {
				(new Set())->useTransactionManager()->MboSetRemote($tbl)->where("wonum = ?", $wonum)->delete();
			}

			// --- 4. Proses Copy Per Tabel (Batch) ---
		

			// A. TASKS
			$tasks = (new Set())->MboSetRemote("wttask")->where("wtnum = ?", $wtnum)->getMboSet()->fetchData();
			if (!empty($tasks)) {
				$lineNum = 0;
				foreach ($tasks as &$t) {
					unset($t['wttaskid'], $t['ownership'], $t['wtnum']);
					$lineNum += 10;
       				$t['tasklinenum'] = $lineNum;
					$t['wonum'] = $wonum;
					$t['status'] = 'REVIEWING';
					$t['enterby'] = $enterby; $t['enterdate'] = $enterdate;
				}
				(new Set())->useTransactionManager()->MboSetRemote("wotask")->addStream($tasks);
			}

			// B. LABORS
			$labors = (new Set())->MboSetRemote("wtplanlabor")->where("wtnum = ?", $wtnum)->getMboSet()->fetchData();
			if (!empty($labors)) {
				$lineNum = 0;
				foreach ($labors as &$l) {
					$lineNum += 10;
        			$l['laborlinenum'] = $lineNum; // Fix Increment
					unset($l['wtplanlaborid'], $l['ownership'], $l['wtnum']);
					$l['wonum'] = $wonum;
					$l['startdate'] = null; $l['enddate'] = null; // Reset schedule
				}
				(new Set())->useTransactionManager()->MboSetRemote("woplanlabor")->addStream($labors);
			}

			// C. TOOLS
			$tools = (new Set())->MboSetRemote("wtplantool")->where("wtnum = ?", $wtnum)->getMboSet()->fetchData();
			if (!empty($tools)) {
				$lineNum = 0;
				foreach ($tools as &$to) {
					$lineNum += 10;
					$to['planlinenum'] = $lineNum;
					unset($to['wtplantoolid'], $to['ownership'], $to['wtnum']);
					$to['wonum'] = $wonum;
				}
				(new Set())->useTransactionManager()->MboSetRemote("woplantool")->addStream($tools);
			}

			// D. SERVICES
			$services = (new Set())->MboSetRemote("wtplanservice")->where("wtnum = ?", $wtnum)->getMboSet()->fetchData();
			if (!empty($services)) {
				$lineNum = 0;
				foreach ($services as &$s) {
					$lineNum += 10;
					$s['planlinenum'] = $lineNum;
					unset($s['wtplanserviceid'], $s['ownership'], $s['wtnum']);
					$s['wonum'] = $wonum;
					$s['prnum'] = null; $s['prlinenum'] = null; $s['ponum'] = null; $s['polinenum'] = null;
					$s['enterby'] = $enterby; $s['enterdate'] = $enterdate;
				}
				(new Set())->useTransactionManager()->MboSetRemote("woplanservice")->addStream($services);
			}

			// E. MATERIALS
			$materials = (new Set())->MboSetRemote("wtplanmaterial")
				->where("wtnum = ?", $wtnum)
				->getMboSet()
				->fetchData();

			if (!empty($materials)) {
				// 1. Kumpulkan kombinasi unique item & warehouse untuk pencarian biaya
				$lookupKeys = [];
				foreach ($materials as $m) {
					$lookupKeys[] = ['itemnum' => $m['itemnum'], 'warehouse' => $m['warehouse']];
				}

				// 2. Ambil harga terbaru dari invfifolifocost untuk semua item+warehouse tersebut
				// subquery atau IN clause untuk mendapatkan harga "latest"
				// Asumsi: Harga terbaru adalah yang memiliki ID terbesar atau date terbaru
				$costMap = [];
				if (!empty($lookupKeys)) {
					// Kueri ini mengambil unitcost terbaru berdasarkan group item+warehouse
					$sqlCost = "
						SELECT itemnum, warehouse, unitcost 
						FROM invfifolifocost 
						WHERE (itemnum, warehouse) IN (
							SELECT itemnum, warehouse FROM invfifolifocost -- Sesuaikan jika Oracle/SQLServer
							GROUP BY itemnum, warehouse
						)
					";
					// Tips: Jika kueri di atas kompleks, Anda bisa gunakan JOIN ke tabel temp atau 
					// hanya ambil semua lalu filter di PHP jika datanya tidak jutaan baris.
					
					$costs = (new Set())->useTransactionManager()
						->MboSetRemote("invfifolifocost")
						->MboRemote("itemnum, warehouse, unitcost")
						->getMboSet()
						->fetchData();

					// 3. Masukkan ke Map untuk akses cepat: $costMap['ITEM01_WH01'] = 15000
					foreach ($costs as $c) {
						$costMap[$c['itemnum'] . '_' . $c['warehouse']] = (float)$c['unitcost'];
					}
				}

				// 4. Proses Loop Material
				$lineNum = 0;
				foreach ($materials as &$m) {
					$lineNum += 10;
					$m['planlinenum'] = $lineNum;
					
					// Cari unit cost dari Map yang sudah dibuat
					$key = $m['itemnum'] . '_' . $m['warehouse'];
					$latestUnitCost = $costMap[$key] ?? (float)$m['unitcost']; // Fallback ke unitcost lama jika tidak ada
					
					// Kalkulasi
					$m['unitcost'] = $latestUnitCost;
					$m['linecost'] = (float)$m['quantity'] * $latestUnitCost;

					// Bersihkan dan set field lainnya
					unset($m['wtplanmaterialid'], $m['ownership'], $m['wtnum']);
					$m['wonum'] = $wonum;
					$m['prnum'] = null; 
					$m['prlinenum'] = null; 
					$m['invreservenum'] = null;
					$m['docnum'] = null; 
					$m['invfifolifocostid'] = null;
					$m['enterby'] = $enterby; 
					$m['enterdate'] = $enterdate;
				}
				
				(new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->addStream($materials);
			}

			// --- 5. Kalkulasi Ulang Planned Cost ---
			// hitung dari semua sub-tabel planning
			$sqlSum = "
				SELECT (
					(SELECT COALESCE(SUM(linecost), 0) FROM woplanlabor WHERE wonum = ?) +
					(SELECT COALESCE(SUM(linecost), 0) FROM woplantool WHERE wonum = ?) +
					(SELECT COALESCE(SUM(linecost), 0) FROM woplanservice WHERE wonum = ?) +
					(SELECT COALESCE(SUM(linecost), 0) FROM woplanmaterial WHERE wonum = ?)
				) as total_plan";
			
			

			$tm->commit();
			Super::sendJsonResponse("success", "Job Plan $wtnum successfully applied to Work Order $wonum", ["plannedcost" => $totalPlan]);
			
		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to copy Job Plan: " . $e->getMessage());
		}
		$set = new Set();
		$totalPlan = $set->fetchAll($sqlSum, [$wonum, $wonum, $wonum, $wonum])[0]['total_plan'] ?? 0;

		(new Set())->useTransactionManager()->MboSetRemote("workorder")
			->where("wonum = ?", $wonum)
			->setValue("plannedcost", $totalPlan)
			->setValue("variancecost", $totalPlan)
			->change();

	}


	public static function ValidateIssueItem()

	{
		$payload = Super::getParam("validateIssueItem");
		$xid = $payload["tableid"];
		$wonum = $payload["wonum"];
		

		$vibSet = (new Set())->MboSetRemote("v_invbalance")->where("v_invbalanceid=? ", $xid)->getMboSet()->fetch();
		
		if (!$vibSet) {
			return;
		}


		$itemnum = $vibSet["itemnum"];
		$warehouse = $vibSet["warehouse"];
		$racknum = $vibSet["racknum"];
		$conditiontype = $vibSet["conditiontype"];
		$curbal =  $vibSet["curbal"];

		$invCostSet = (new Set())->MboSetRemote("invfifolifocost")
                         ->where("itemnum = ?", $itemnum)
						 ->where("quantity = ?", $curbal)
                         ->where("warehouse = ?", $warehouse);

		if (empty($racknum)) {
			$invCostSet->whereNull("racknum");
		} else {
			$invCostSet->where("racknum = ?", $racknum);
		}

		if (empty($conditiontype)) {
			$invCostSet->whereNull("conditiontype");
		} else {
			$invCostSet->where("conditiontype = ?", $conditiontype);
		}


		$invfifolifocostSet = $invCostSet->MboRemote("itemnum, warehouse, racknum, conditiontype, sitecode, orgcode, unitcost, quantity")->getMboSet();
	
		
		$invfifolifocost = $invfifolifocostSet->getMbo();
		
		$itemnum = $invfifolifocost->getString("itemnum");
		$warehouse = $invfifolifocost->getString("warehouse");
		$racknum = $invfifolifocost->getString("racknum");
		$conditiontype = $invfifolifocost->getString("conditiontype");
		$unitcost = $invfifolifocost->getString("unitcost");
		if (empty($conditiontype)) {
			$condval = "(conditiontype='' or conditiontype is null)";
		}
		else {
			$condval = "conditiontype='$conditiontype'";
		}
		$sitecode = $invfifolifocost->getString("sitecode");
		$curbal = $invfifolifocost->getString("quantity");
		$orgcode = $invfifolifocost->getString("orgcode");
		
		if($unitcost == 0){
			$set = new Set();
			$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and $condval and orgcode='$orgcode' and sitecode='$sitecode'")
			->MboRemote("avgcost")->getMboSet();
			$unitcost = $invcostSet->getMbo()->getString("avgcost");
			if ($unitcost == null) $unitcost = 0;
		}
		$glcreditacct = Xbo::getGLAccount($itemnum);
		if(empty($glcreditacct)) {
			$glcreditacct = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
		}
		$gldebitacct = Xbo::getWOAccount($wonum, $orgcode, $sitecode);
		if ($curbal < 1) {
			echo json_encode(["msg" => "ZeroBalance", 
				"enterby" => $enterby, "itemnum"=>$itemnum, "enterdate" => $enterdate, "warehouse" => $warehouse, "racknum" => $racknum, "conditiontype" => $conditiontype, "curbal" => $curbal, "unitcost" => $unitcost, "gldebitacct" => $gldebitacct, "glcreditacct" => $glcreditacct
			]);
		}
		
		if ($curbal >= 1) {
			$enterby = Xbo::getLoginUser();
			$enterdate = date("Y-m-d h:i:s");
			$set = new Set();
			$gldebitacct = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("glaccount")->getMboSet()->getMbo()->getString("glaccount");
			echo json_encode(["enterby" => $enterby, "itemnum"=>$itemnum, "enterdate" => $enterdate, "warehouse" => $warehouse, "racknum" => $racknum, "conditiontype" => $conditiontype, "curbal" => $curbal, "unitcost" => $unitcost, "gldebitacct" => $gldebitacct, "glcreditacct" => $glcreditacct]);
		}
	}
	public static function UpdateAssetParent(){
		$recid = $_GET['updassetparent'];
		$set = new Set();
		$ts = explode("^", $recid);
		$table = $ts[0];
		$id = $ts[1];
		if(empty($id)) Super::sendJsonResponse("success", "Parent not found");

		$tablenameid = $table."id";
		$tablenamekey = "assetnum";
		$colnamesub = "subassetnum";
		$tablenamesub = "assetsubs";
		if($table == "workorder"){
			$tablenamekey = "wonum";
			$colnamesub = "subwonum";
			$tablenamesub = "wosubs";
		}
		$set = new Set();
		$assetSet = $set->MboSetRemote($table)->setWhere("$tablenameid='$id'")->MboRemote($tablenamekey)->getMboSet();		
		$assetnum = $assetSet->getMbo()->getString($tablenamekey);
		$set2 = new Set();
		$assetSubSet = $set2->MboSetRemote($tablenamesub)->setWhere("$tablenamekey='$assetnum'")->MboRemote($colnamesub)->getMboSet();
		$cnt = $assetSubSet->counts();
		for($i=0; $i<$cnt; $i++){
			$assetsub = $assetSubSet->getMbo();
			$subassetnum = $assetsub->getString($colnamesub);
			$set3 = new Set();
			$set3->MboSetRemote($table)->setWhere("$tablenamekey='$subassetnum'")->setValue("parent", $assetnum)->change();
		}

		Super::sendJsonResponse("success", "Asset Parent has been updated");
	}
	
	public static function calculateAllWoCost($wonum){
	
		$plannedcost = (new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->setWhere("wonum='$wonum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
		

		$actualcost = (new Set())->useTransactionManager()->MboSetRemote("matusetrans")->setWhere("wonum='$wonum' AND donotcalculateres != 'Y' ")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
		if ($actualcost < 0) {
			$actualcost = $actualcost * -1;
		}
		
		$actualcost2 = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->setWhere("wonum='$wonum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
		if ($actualcost2 < 0) {
			$actualcost2 = $actualcost2 * -1;
		}
	
		$sumactuallaborcost = (new Set())->useTransactionManager()->MboSetRemote("labortrans")->setWhere("wonum='$wonum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
	
		$sumactualtoolcost = (new Set())->useTransactionManager()->MboSetRemote("tooltrans")->setWhere("wonum='$wonum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
		
		if($actualcost == null) $actualcost = 0;
		if($actualcost2 == null) $actualcost2 = 0;
		if($sumactuallaborcost == null) $sumactuallaborcost = 0;
		if($sumactualtoolcost == null) $sumactualtoolcost = 0;
		
		$actualcost = $actualcost + $actualcost2 + $sumactuallaborcost + $sumactualtoolcost;
	
		$woplanSet2 = (new Set())->useTransactionManager()->MboSetRemote("woplanservice")->setRelationship("prline", "woplanservice.prnum = prline.prnum and woplanservice.prlinenum = prline.prlinenum")->setWhere("woplanservice.wonum='$wonum'")->MboRemote("sum(prline.linecost) as sumplannedcost2")->getMboSet();
		$woplan2 = $woplanSet2->getMbo();
		$sumplannedcost2 = $woplan2->getString("sumplannedcost2");
		
		$woplanSet3 = (new Set())->useTransactionManager()->MboSetRemote("woplanservice")->setWhere("woplanservice.wonum='$wonum'")->MboRemote("sum(linecost) as sumplannedcost3")->getMboSet();
		$woplan3 = $woplanSet3->getMbo();
		$sumplannedcost3 = $woplan3->getString("sumplannedcost3");
		
		$woplanLaborSet = (new Set())->useTransactionManager()->MboSetRemote("woplanlabor")->setWhere("woplanlabor.wonum='$wonum'")->MboRemote("sum(linecost) as sumlaborcost")->getMboSet();
		$woplanlabor = $woplanLaborSet->getMbo();
		$sumlaborcost = $woplanlabor->getString("sumlaborcost");
	
		$woplanToolSet = (new Set())->useTransactionManager()->MboSetRemote("woplantool")->setWhere("woplantool.wonum='$wonum'")->MboRemote("sum(linecost) as sumtoolcost")->getMboSet();
		$woplantool = $woplanToolSet->getMbo();
		$sumtoolcost = $woplantool->getString("sumtoolcost");
		
		if($plannedcost == null) $plannedcost = 0;
		if($sumplannedcost2 == null) $sumplannedcost2 = 0;
		if($sumlaborcost == null) $sumlaborcost = 0;
		if($sumtoolcost == null) $sumtoolcost = 0;
		
		$plannedcost = $plannedcost + $sumplannedcost2 + $sumplannedcost3 + $sumlaborcost + $sumtoolcost;
		
		$variancecost = $plannedcost - $actualcost;
		
		(new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)
		->setValue("actualcost", $actualcost)
		->setValue("plannedcost", $plannedcost)
		->setValue("variancecost", $variancecost)
		->change();
	}
	public static function getAllWOs(){
		$set = new Set();
		
		$workorderSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->MboRemote("wonum")->getMboSet();
		
		while($wo = $workorderSet->fetch()){
			$wonum = $wo["wonum"];
			self::calculateAllWoCost($wonum);
		}
		
	}



	public static function SumWOCost($wo = null)
	{
		$wonum = $wo ?? Super::getParam("sumwocost");
		if (empty($wonum)) {
			Set::throws(["error" => "Work Order Number is required."]);
			return;
		}

		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// 0. PLANNED DATES DARI WOPLANLABOR
			$planTeamSet = (new Set())->useTransactionManager()->MboSetRemote("woplanlabor")
				->where("wonum = ? AND teamnum IS NOT NULL", $wonum)->group("teamnum")
				->MboRemote("teamnum, MIN(startdate) as plan_start, MAX(enddate) as plan_end")
				->getMboSet()->fetchDataAssoc('teamnum');

			$planPersonSet = (new Set())->useTransactionManager()->MboSetRemote("woplanlabor")
				->where("wonum = ? AND personid IS NOT NULL", $wonum)->group("personid")
				->MboRemote("personid, MIN(startdate) as plan_start, MAX(enddate) as plan_end")
				->getMboSet()->fetchDataAssoc('personid');

			// Konstanta standar jam kerja per hari
			$standardHoursPerDay = 8; 

			// ====================================================================
			// 1. RECALCULATE LABORTRANS (TEAMNUM)
			// ====================================================================
			$teamLaborData = (new Set())->useTransactionManager()
				->MboSetRemote("labortransline")
				->where("wonum = ? AND teamnum IS NOT NULL", $wonum)
				->group("teamnum")
				->MboRemote("teamnum, MIN(startdate) as startdate, MAX(enddate) as enddate, SUM(linecost) as linecost, SUM(prelinecost) as prelinecost, SUM(totalovertimecost) as totalovertimecost, SUM(reghours) as reghours, SUM(extendedhours) as extendedhours")
				->getMboSet()
				->fetchData();

			foreach ($teamLaborData as $row) {
				$teamnum = $row['teamnum'];
				$calculatedRegDays = (float)$row['reghours'] / $standardHoursPerDay;

				$actStart = $row['startdate'];
				$actEnd   = $row['enddate'];
				$startDelayHrs = 0;
				$finishDelayHrs = 0;
				$isTargetAchieved = 'N';

				// Hitung Delay dan Target (Khusus Team)
				if (isset($planTeamSet[$teamnum]) && !empty($actStart) && !empty($actEnd)) {
					$planStart = $planTeamSet[$teamnum]['plan_start'];
					$planEnd   = $planTeamSet[$teamnum]['plan_end'];

					if (!empty($planStart) && !empty($planEnd)) {
						$startDelayHrs  = (strtotime($actStart) - strtotime($planStart)) / 3600;
						$finishDelayHrs = (strtotime($actEnd) - strtotime($planEnd)) / 3600;
						if ($finishDelayHrs <= 0) {
							$isTargetAchieved = 'Y';
						}
					}
				}

				(new Set())->useTransactionManager()->MboSetRemote("labortrans")
					->where("teamnum = ? AND wonum = ?", $teamnum, $wonum)
					->setValue("startdate", $actStart)
					->setValue("enddate", $actEnd)
					->setValue("regdays", round($calculatedRegDays, 2))
					->setValue("startdelay_hrs", round($startDelayHrs, 2))
					->setValue("finishdelay_hrs", round($finishDelayHrs, 2))
					->setValue("istargetachieved", $isTargetAchieved)
					->setValue("prelinecost", $row['prelinecost'])
					->setValue("totalovertimecost", $row['totalovertimecost'])
					->setValue("reghours", $row['reghours'])
					->setValue("extendedhours", $row['extendedhours'])
					->setValue("linecost", $row['linecost'])
					->change();
			}

			// ====================================================================
			// 2. RECALCULATE LABORTRANS (PERSONID)
			// ====================================================================
			$personLaborData = (new Set())->useTransactionManager()
				->MboSetRemote("labortransline")
				->where("wonum = ? AND personid IS NOT NULL", $wonum)
				->group("personid")
				->MboRemote("personid, MIN(startdate) as startdate, MAX(enddate) as enddate, SUM(linecost) as linecost, SUM(prelinecost) as prelinecost, SUM(totalovertimecost) as totalovertimecost, SUM(reghours) as reghours, SUM(extendedhours) as extendedhours")
				->getMboSet()
				->fetchData();

			foreach ($personLaborData as $row) {
				$personid = $row['personid'];
				$calculatedRegDays = (float)$row['reghours'] / $standardHoursPerDay;

				$actStart = $row['startdate'];
				$actEnd   = $row['enddate'];
				$startDelayHrs = 0;
				$finishDelayHrs = 0;
				$isTargetAchieved = 'N';

				// Hitung Delay dan Target (Khusus Person)
				if (isset($planPersonSet[$personid]) && !empty($actStart) && !empty($actEnd)) {
					$planStart = $planPersonSet[$personid]['plan_start'];
					$planEnd   = $planPersonSet[$personid]['plan_end'];

					if (!empty($planStart) && !empty($planEnd)) {
						$startDelayHrs  = (strtotime($actStart) - strtotime($planStart)) / 3600;
						$finishDelayHrs = (strtotime($actEnd) - strtotime($planEnd)) / 3600;
						if ($finishDelayHrs <= 0) {
							$isTargetAchieved = 'Y';
						}
					}
				}

				(new Set())->useTransactionManager()->MboSetRemote("labortrans")
					->where("personid = ? AND wonum = ?", $personid, $wonum)
					->setValue("startdate", $actStart)
					->setValue("enddate", $actEnd)
					->setValue("regdays", round($calculatedRegDays, 2))
					->setValue("startdelay_hrs", round($startDelayHrs, 2))
					->setValue("finishdelay_hrs", round($finishDelayHrs, 2))
					->setValue("istargetachieved", $isTargetAchieved)
					->setValue("prelinecost", $row['prelinecost'])
					->setValue("totalovertimecost", $row['totalovertimecost'])
					->setValue("reghours", $row['reghours'])
					->setValue("extendedhours", $row['extendedhours'])
					->setValue("linecost", $row['linecost'])
					->change();
			}

			// ====================================================================
			// 3. MASTER AGGREGATION QUERY (Menghitung Total Cost WO)
			// ====================================================================
			$sql = "
				SELECT
					-- ACTUAL COSTS
					(SELECT COALESCE(ABS(SUM(linecost)), 0) FROM matusetrans WHERE wonum = wo.wonum AND donotcalculateres != 'Y') AS act_mat,
					(SELECT COALESCE(ABS(SUM(linecost)), 0) FROM servrectrans WHERE wonum = wo.wonum) AS act_serv,
					(SELECT COALESCE(SUM(linecost), 0) FROM labortrans WHERE wonum = wo.wonum) AS act_labor,
					(SELECT COALESCE(SUM(linecost), 0) FROM tooltrans WHERE wonum = wo.wonum) AS act_tool,

					-- PLANNED COSTS
					(SELECT COALESCE(SUM(linecost), 0) FROM woplanmaterial WHERE wonum = wo.wonum) AS plan_mat,
					(SELECT COALESCE(SUM(linecost), 0) FROM woplanservice WHERE wonum = wo.wonum) AS plan_serv,
					(SELECT COALESCE(SUM(prelinecost), 0) FROM woplanlabor WHERE wonum = wo.wonum) AS plan_labor,
					(SELECT COALESCE(SUM(linecost), 0) FROM woplantool WHERE wonum = wo.wonum) AS plan_tool,

					-- PROGRESS
					(SELECT COALESCE(AVG(progress), 0) FROM wotask WHERE wonum = wo.wonum) AS task_progress
				FROM workorder wo
				WHERE wo.wonum = ?
			";

			$results = Set::fetchAll($sql, [$wonum]);

			if (empty($results)) {
				throw new Exception("Work Order '$wonum' is not found when calculating its cost.");
			}

			$cost = (object) $results[0];

			// ====================================================================
			// 4. KALKULASI FINAL
			// ====================================================================
			$actualcost  = $cost->act_mat + $cost->act_serv + $cost->act_labor + $cost->act_tool;
			$plannedcost = $cost->plan_mat + $cost->plan_serv + $cost->plan_labor + $cost->plan_tool;
			$variancecost = $plannedcost - $actualcost;
			$progress = round($cost->task_progress, 2);

			$resultData =[
				"actualcost" => $actualcost, 
				"plannedcost" => $plannedcost, 
				"variancecost" => $variancecost, 
				"progress" => $progress
			];

			// Selesaikan Transaksi!
			$tm->commit();
			
			if ($wo == null) {
				Set::throws($resultData); 
			} else {
				return $resultData; 
			}

		} catch (Exception $e) {
			$tm->rollback();
			if ($wo == null) {
				Set::throws(["error" => $e->getMessage()]);
			} else {
				throw $e;
			}
		}
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
		$can_delete = false;

		if($childTable == "woplanmaterial"){
			foreach($selectedRows as $row){
				$rowid = $row["woplanmaterialid"];
				$woplanMat = (new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->where("woplanmaterialid=?", $rowid)->getMboSet()->getMbo();
				$wonum = $woplanMat->getString("wonum");
				$itemnum = $woplanMat->getString("itemnum");


				// Check InvReserve 
				$invReserveSet = (new Set())->useTransactionManager()->MboSetRemote("invreserve")->where("wonum=? AND itemnum = ? ", $wonum, $itemnum)->getMboSet();
				if($invReserveSet->isEmpty()){

					// Check PM CAMPAIGN 
					$pmcampaignSet = (new Set())->useTransactionManager()->MboSetRemote("pmcampaignline")->where("wonum=?", $wonum)->getMboSet();
					if($pmcampaignSet->isEmpty()){
						$can_delete = true;
						//$_POST["delChildPackage"] = $dataPackage;
						//Super::deleteChildren();			
					}else{
						$pmcamp = $pmcampaignSet->getMbo();
						$pmcampaignnum = $pmcamp->getString("pmcampaignnum");
						 Super::sendJsonResponse("error", " Cannot Delete. This WO is under Work Campaign, Num: $pmcampaignnum");
					}
					
				}else{
					$invres = $invReserveSet->getMbo();
					$invReserveNum = $invres->getString("invreservenum");
					$reservedFor = $invres->getString("reservedFor");
					$resFor = ", reserved For : $reservedFor";
					if(empty($reservedFor)) $resFor="";
					 Super::sendJsonResponse("error", " Cannot Delete. Reservation Exist on Reserve Num: $invReserveNum $resFor");
				}
				
			}

			if($can_delete == true){
				$_POST["delChildPackage"] = $dataPackage;
				Super::deleteChildren();	
			}
			

		}elseif($childTable == "matusetrans" || $childTable == "servrectrans" ){
			Super::sendJsonResponse("error", " Cannot Delete Transaction Record.");
		}elseif($childTable == "wosubs"){
			$WoSubsList = [];
			foreach($selectedRows as $row){
				$rowid = $row["wosubsid"];
				$wosubs = (new Set())->useTransactionManager()->MboSetRemote("wosubs")->where("wosubsid=?", $rowid)->getMboSet()->getMbo();
				$wonum = $wosubs->getString("wonum");
				$subwonum = $wosubs->getString("subwonum");
				//hapus parent WO dari workorder child.
				(new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $subwonum)->setValue("parent", null)->change();
				$can_delete = true;

			}
			
			if($can_delete == true){
				$_POST["delChildPackage"] = $dataPackage;
				Super::deleteChildren();	
			}
				
		}else{
			$_POST["delChildPackage"] = $dataPackage;
			Super::deleteChildren();		
		}
		
	}

	public static function deleteToolTrans() {
        $tm = TransactionManager::getInstance();
        try {
     
            $tm->beginTransaction();

            $payload = Super::getParam("deleteToolTrans");
            $dataPackage = $payload["dataPackage"];

            if (!isset($dataPackage["selectedRows"]) || !is_array($dataPackage["selectedRows"]) || empty($dataPackage["selectedRows"])) {
                throw new InvalidArgumentException("No tool transactions selected for deletion.");
            }

            $toolTransIdsToDelete = array_column($dataPackage["selectedRows"], 'tooltransid');

            $transSet = (new Set())->useTransactionManager()->MboSetRemote("tooltrans");
            $transactions = $transSet->whereIn("tooltransid", $toolTransIdsToDelete)
                                     ->getMboSet()
                                     ->fetchData();

            if (empty($transactions)) {
                throw new Exception("No valid tool transactions found for the given IDs.");
            }
            // Ambil Toolnum
            $toolNumsToRevert = array_column($transactions, 'toolnum');

            foreach ($transactions as $trans) {
                $toolnum = $trans['toolnum'];
                $originalLocation = $trans['fromlocnum'];

                // Balikkin status tool ini ke AVAILABLE, kosongkan wonum, dan balikkin ke lokasi asal.
                (new Set())->useTransactionManager()
                           ->MboSetRemote("tools")
                           ->where("toolnum = ?", $toolnum)
                           ->setValue("status", "AVAILABLE")
                           ->setValue("wonum", null) 
                           ->setValue("personid", null) // Kosongkan penanggung jawab
                           ->setValue("locnum", $originalLocation) // Kembalikan ke lokasi asal
                           ->change();
            }

            // Setelah semua tool berhasil dikembalikan statusnya, baru di hapus log transaksinya.
          
            (new Set())->useTransactionManager()
                       ->MboSetRemote("tooltrans")
                       ->whereIn("tooltransid", $toolTransIdsToDelete)
                       ->delete();

            $tm->commit();
            Super::sendJsonResponse("success", count($toolTransIdsToDelete) . " tool transactions have been successfully deleted and reverted.");
			
        } catch (Exception $e) {
            $tm->rollback();
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }
}
	

?>  