<?php

class WorkTemplateDrawer
{
    public static function getDrawerData()
    {
        try {
            $payload = Super::getParam("getDrawerWorkTemplateData");
            $action = $payload['action'] ?? 'list';

            if ($action === 'list') {
                self::getWTList($payload);
            } elseif ($action === 'detail') {
                self::getWTDetail($payload);
            } else {
                Super::sendJsonResponse("error", "Unknown drawer action.");
            }
        } catch (Exception $e) {
            Super::sendJsonResponse("error", "Server Error: " . $e->getMessage());
        }
    }

    /**
     * Tab List: Server-Side Search
     */
    private static function getWTList($payload)
    {
        $search = $payload['search'] ?? '';
        $wtSet = (new Set())->useTransactionManager()->MboSetRemote("worktemplates");

        if (!empty($search)) {
            $wtSet->where(
                "(wtnum LIKE ? OR description LIKE ? OR assetnum LIKE ? OR location LIKE ?)", 
                "%$search%", "%$search%", "%$search%", "%$search%"
            );
        }

        $data = $wtSet->MboRemote("wtnum, description, assetnum, location, worktype, workclass")
            ->limit(500)
            ->getMboSet()
            ->fetchData();

        Super::sendJsonResponse("success", "Work Template list loaded", $data);
    }

    /**
     * Tab Detail: Header + 4 Plan Tables
     */
    private static function getWTDetail($payload)
    {
        $wtnum = $payload['wtnum'] ?? '';
        if (empty($wtnum)) {
            throw new InvalidArgumentException("Work Template Number (wtnum) is required.");
        }

        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();

        try {
            // 2.1 Header Work Template
            $header = (new Set())->useTransactionManager()
                ->MboSetRemote("worktemplates")
                ->where("wtnum = ?", $wtnum)
                ->getMboSet()
                ->fetch();

            if (!$header) throw new Exception("Work Template '$wtnum' not found.");

            /* === Update di WorkTemplateDrawer.php > getWTDetail === */

            // 2.2 Material Plans (Base Data)
            $materials = (new Set())->useTransactionManager()
                ->MboSetRemote("wtplanmaterial")
                ->where("wtnum = ?", $wtnum)
                ->getMboSet()
                ->fetchData();
            
            if (!empty($materials)) {
                $itemNums = array_values(array_unique(array_column($materials, 'itemnum')));
                $placeholders = rtrim(str_repeat('?,', count($itemNums)), ',');

                /// 2.2.1 Get Item Master Info
                $itemMaster = (new Set())->useTransactionManager()
                    ->MboSetRemote("item")
                    ->whereIn("itemnum", $itemNums)
                    ->MboRemote("itemnum, description, manufacturer, brand")
                    ->getMboSet()
                    ->fetchDataAssoc("itemnum");

                // 2.2.2 Get Technical Specs
                $sqlSpecs = "
                    SELECT ic.itemnum, ic.attributename, a.description as attr_desc, ic.value, ic.unit
                    FROM itemclassification ic
                    LEFT JOIN attribute a ON ic.attributename = a.attributename
                    WHERE ic.itemnum IN ($placeholders)
                ";
                // $itemNums sekarang indeksnya 0, 1, 2... sampai 70 (Total 71)
                $specsRaw = Set::fetchAll($sqlSpecs, $itemNums);
                
                $specsGrouped = [];
                foreach ($specsRaw as $s) {
                    $specsGrouped[$s['itemnum']][] = $s;
                }
               
                // 2.2.3 Get Real-time Stock (invfifolifocost)
                $sqlStock = "
                    SELECT itemnum, conditiontype, expdate, unitcost, quantity, warehouse, racknum, batchnum, costdate
                    FROM invfifolifocost
                    WHERE itemnum IN ($placeholders) AND quantity > 0
                    ORDER BY costdate ASC
                ";
                $stockRaw = Set::fetchAll($sqlStock, $itemNums);
                $stockGrouped = [];
                foreach ($stockRaw as $st) {
                    $stockGrouped[$st['itemnum']][] = $st;
                }

                // Gabungkan semua data ke array $materials
                foreach ($materials as &$m) {
                    $m['master'] = $itemMaster[$m['itemnum']] ?? null;
                    $m['specs']  = $specsGrouped[$m['itemnum']] ?? [];
                    $m['avail_stock'] = $stockGrouped[$m['itemnum']] ?? [];
                }
            }

            // 2.3 Service Plans
            $services = (new Set())->useTransactionManager()
                ->MboSetRemote("wtplanservice")
                ->where("wtnum = ?", $wtnum)
                ->MboRemote("serviceitemnum, description, quantity, orderunit, unitcost, linecost")
                ->getMboSet()
                ->fetchData();

            // 2.4 Tool Plans
            $tools = (new Set())->useTransactionManager()
                ->MboSetRemote("wtplantool")
                ->where("wtnum = ?", $wtnum)
                ->getMboSet()
                ->fetchData();

            if (!empty($tools)) {
                $toolNums = array_values(array_unique(array_column($tools, 'toolnum')));
                $tPlaceholders = rtrim(str_repeat('?,', count($toolNums)), ',');

                // 2.4.1 Get Tool Master Data
                $toolMaster = (new Set())->useTransactionManager()
                    ->MboSetRemote("tools")
                    ->whereIn("toolnum", $toolNums)
                    ->MboRemote("toolnum, name, serialnumber, brand, category, modelnumber, tooltype, lastcalibrationdate, nextcalibrationdate")
                    ->getMboSet()
                    ->fetchDataAssoc("toolnum");

                // 2.4.2 Get Tool Stock Availability (Berdasarkan ToolType)
                $toolTypes = array_values(array_unique(array_column($toolMaster, 'tooltype')));
        
                if (!empty($toolTypes)) {
                    // Gunakan fetchData() biasa, BUKAN fetchDataAssoc()
                    $toolStockRaw = (new Set())->useTransactionManager()
                        ->MboSetRemote("toolstock")
                        ->whereIn("tooltype", $toolTypes)
                        ->MboRemote("tooltype, curbal, qtyavailable, locnum")
                        ->getMboSet()
                        ->fetchData();

                    // Lakukan Grouping Manual (Satu ToolType bisa punya BANYAK lokasi)
                    $toolStockGrouped =[];
                    foreach ($toolStockRaw as $st) {
                        $type = $st['tooltype'];
                        // Masukkan sebagai sub-array []
                        $toolStockGrouped[$type][] = $st; 
                    }
                } else {
                    $toolStockGrouped =[];
                }

                // Gabungkan data ke array $tools utama
                foreach ($tools as &$t) {
                    $master = $toolMaster[$t['toolnum']] ?? null;
                    $t['master'] = $master;
                    if ($master) {
                        // Ambil array lokasi dari hasil grouping. Jika tidak ada, berikan array kosong []
                        $t['stock_info'] = $toolStockGrouped[$master['tooltype']] ??[];
                    } else {
                        $t['stock_info'] =[];
                    }
                }
                unset($t); // Hapus referensi untuk mencegah bug
            }

            // 2.5 Labor Plans (Rencana Tenaga Kerja)
            $labor = (new Set())->useTransactionManager()
                ->MboSetRemote("wtplanlabor")
                ->where("wtnum = ?", $wtnum)
                ->getMboSet()
                ->fetchData();

            if (!empty($labor)) {
                // 2.5.1 Kumpulkan semua Team dan Person yang unik
                $teamIds = array_values(array_unique(array_filter(array_column($labor, 'teamnum'))));
                $personIds = array_values(array_unique(array_filter(array_column($labor, 'personid'))));

                // --- JALUR TIM ---
                $teamDataGrouped = [];
                if (!empty($teamIds)) {
                    $tPlaceholders = rtrim(str_repeat('?,', count($teamIds)), ',');
                    
                    // Ambil info Team + Breakdown Member + Info Employee sekaligus
                    $sqlTeamMembers = "
                        SELECT 
                            t.teamnum, t.description as team_desc,
                            et.personid, et.priority, et.skilllevel, et.unitcost as member_rate,
                            e.fullname, e.department, e.section, e.primaryemail, e.primaryphone
                        FROM teams t
                        INNER JOIN empteam et ON t.teamnum = et.teamnum
                        INNER JOIN employees e ON et.personid = e.personid
                        WHERE t.teamnum IN ($tPlaceholders)
                        ORDER BY t.teamnum ASC, et.priority ASC
                    ";
                    $resTeam = Set::fetchAll($sqlTeamMembers, $teamIds);
                    
                    // Grouping berdasarkan teamnum
                    foreach ($resTeam as $row) {
                        $teamDataGrouped[$row['teamnum']]['description'] = $row['team_desc'];
                        $teamDataGrouped[$row['teamnum']]['members'][] = [
                            'personid'     => $row['personid'],
                            'fullname'     => $row['fullname'],
                            'priority'     => $row['priority'],
                            'skilllevel'   => $row['skilllevel'],
                            'unitcost'     => $row['member_rate'],
                            'department'   => $row['department'],
                            'section'      => $row['section'],
                            'primaryemail' => $row['primaryemail'],
                            'primaryphone' => $row['primaryphone']
                        ];
                    }
                }

                // --- JALUR INDIVIDU ---
                $individualData = [];
                if (!empty($personIds)) {
                    $pPlaceholders = rtrim(str_repeat('?,', count($personIds)), ',');
                    
                    // Ambil info Employee + Rate dari empteam (jika ada)
                    $sqlIndividu = "
                        SELECT 
                            e.personid, e.fullname, e.department, e.section, e.primaryemail, e.primaryphone,
                            MAX(et.unitcost) as personal_rate, 
                            MAX(et.skilllevel) as skilllevel
                        FROM employees e
                        LEFT JOIN empteam et ON e.personid = et.personid
                        WHERE e.personid IN ($pPlaceholders)
                        GROUP BY e.personid, e.fullname, e.department, e.section, e.primaryemail, e.primaryphone
                    ";
                    $individualData = Set::fetchAll($sqlIndividu, $personIds);
                    $individualData = array_combine(array_column($individualData, 'personid'), $individualData);
                }

                // 2.5.2 Gabungkan Data ke Array Labor Utama
                foreach ($labor as &$l) {
                    if (!empty($l['teamnum'])) {
                        // Jika penugasan berdasarkan TIM
                        $l['type'] = 'TEAM';
                        $l['detail'] = $teamDataGrouped[$l['teamnum']] ?? null;
                    } elseif (!empty($l['personid'])) {
                        // Jika penugasan berdasarkan INDIVIDU
                        $l['type'] = 'INDIVIDUAL';
                        $l['detail'] = $individualData[$l['personid']] ?? null;
                    }
                }
                unset($l);
            }
                

            // URL & Context Link
            $appUrl = Super::getApplicationURL("worktemplates");
            $applinksownernum = "$wtnum%worktemplates";

            $tm->commit();

            // 2.6 Work Tasks (Instruksi Kerja)
            $tasks = (new Set())->useTransactionManager()
                ->MboSetRemote("wttask")
                ->where("wtnum = ?", $wtnum)
                ->MboRemote("tasklinenum, description")
                ->order("tasklinenum ASC") 
                ->getMboSet()
                ->fetchData();

            $tm->commit();

            Super::sendJsonResponse("success", "Work Template detail loaded", [
                'header' => $header,
                'plans' => [
                    'materials' => $materials,
                    'services'  => $services,
                    'tools'     => $tools,
                    'labor'     => $labor,
                    'tasks'     => $tasks 
                ],
                'appUrl' => $appUrl,
                'applinksownernum' => $applinksownernum
            ]);

        } catch (Exception $e) {
            $tm->rollback();
            throw $e;
        }
    }
}