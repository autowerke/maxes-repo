<?php
require_once ("../../../eat.php");


class Dashboard{
    
    
    public static function getCategoryAndStatusDistribution() {
        try {
            $payload = Super::getParam("getCategoryAndStatusDistribution");
            $selectedManual = trim($payload["manualnum"] ?? 'DEFAULT');

            $set = new Set();

            // 1. Tentukan manual default jika 'DEFAULT' atau kosong
            if ($selectedManual === 'DEFAULT' || empty($selectedManual)) {
                $activeManual = (new Set())->useTransactionManager()
                    ->MboSetRemote("integmanual")
                    ->where("status = ?", "active")
                    ->MboRemote("integmanualnum")
                    ->limit(1)
                    ->getMboSet()
                    ->fetch();
                if (!$activeManual) {
                    $activeManual = (new Set())->useTransactionManager()
                        ->MboSetRemote("integmanual")
                        ->MboRemote("integmanualnum")
                        ->limit(1)
                        ->getMboSet()
                        ->fetch();
                }
                $selectedManual = trim($activeManual['integmanualnum'] ?? '');
            }

            // 2. Ambil daftar proceduresnum yang terikat ke manual ini
            $itemSet = (new Set())->useTransactionManager()
                ->MboSetRemote("integmanualitem")
                ->where("integmanualnum = ?", $selectedManual)
                ->MboRemote("proceduresnum")
                ->getMboSet()
                ->fetchData();

            $procedureNums = array_filter(array_unique(array_column($itemSet, 'proceduresnum')));

            $categoryCounts = [];
            $statusCounts = [
                "active" => 0,
                "pending" => 0,
                "draft" => 0,
                "inactive" => 0
            ];

            if (!empty($procedureNums)) {
                // Ambil data kategori dari tabel procedures
                $procSet = (new Set())->useTransactionManager()
                    ->MboSetRemote("procedures")
                    ->whereIn("proceduresnum", $procedureNums)
                    ->MboRemote("category, status")
                    ->getMboSet()
                    ->fetchData();

                foreach ($procSet as $p) {
                    $cat = trim($p['category'] ?? 'UNASSIGNED');
                    $st  = strtolower(trim($p['status'] ?? 'draft'));

                    // Hitung kategori
                    if (!isset($categoryCounts[$cat])) {
                        $categoryCounts[$cat] = 0;
                    }
                    $categoryCounts[$cat]++;

                    // Hitung status
                    if (isset($statusCounts[$st])) {
                        $statusCounts[$st]++;
                    } else {
                        $statusCounts['draft']++; // Fallback
                    }
                }
            }

            // Format data kategori untuk ECharts Bar Chart
            $catLabels = array_keys($categoryCounts);
            $catValues = array_values($categoryCounts);

            // Format data status untuk ECharts Donut Chart
            $statusData = [
                ["value" => $statusCounts['active'], "name" => "Active", "itemStyle" => ["color" => "#36b37e"]],
                ["value" => $statusCounts['pending'], "name" => "Pending", "itemStyle" => ["color" => "#ffab00"]],
                ["value" => $statusCounts['draft'], "name" => "Draft", "itemStyle" => ["color" => "#5e6c84"]],
                ["value" => $statusCounts['inactive'], "name" => "Inactive", "itemStyle" => ["color" => "#0065ff"]]
            ];

            Super::sendJsonResponse("success", "Category and status distribution loaded", [
                "selected_manual" => $selectedManual,
                "categories" => [
                    "labels" => $catLabels,
                    "values" => $catValues
                ],
                "statuses" => $statusData
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }

    public static function getFilteredDocumentTableData() {
        try {
            $payload = Super::getParam("getFilteredDocumentTableData");
            $selectedManual = trim($payload["manualnum"] ?? 'DEFAULT');
            $clickedNodeId  = trim($payload["nodeid"] ?? ''); 
            
            // Parameter baru dari klik Stacked Bar Chart
            $docTypeFilter  = trim($payload["doctype_filter"] ?? ''); // 'procedures', 'workinstr', 'docform'
            $statusFilter   = trim($payload["status_filter"] ?? '');  // 'active', 'inactive', 'in_progress'

            $records = [];
            $columns = [];
            $targetTable = 'integmanual'; // Default

            // PRIORITAS 1: Jika user mengklik Node spesifik di Knowledge Graph
            if (!empty($clickedNodeId)) {
                if (strpos($clickedNodeId, 'MT-') === 0) {
                    $targetTable = 'integmanual';
                } elseif (strpos($clickedNodeId, 'PRC-') === 0) {
                    $targetTable = 'procedures';
                } elseif (strpos($clickedNodeId, 'WI-') === 0) {
                    $targetTable = 'workinstr';
                } elseif (strpos($clickedNodeId, 'FRM-') === 0) {
                    $targetTable = 'docform';
                }
            }
            // PRIORITAS 2: Jika user mengklik Stacked Bar Chart (Filter berdasarkan Tipe Dokumen & Status)
            elseif (!empty($docTypeFilter)) {
                $targetTable = $docTypeFilter;
            }

            // Tentukan Manual Default jika bernilai DEFAULT atau kosong
            if ($selectedManual === 'DEFAULT' || empty($selectedManual)) {
                $activeManual = (new Set())->useTransactionManager()->MboSetRemote("integmanual")->where("status = ?", "active")->MboRemote("integmanualnum")->limit(1)->getMboSet()->fetch();
                $selectedManual = trim($activeManual['integmanualnum'] ?? '');
            }

            // Ambil daftar procedure yang terikat ke Manual Terpilih (sebagai batasan hierarki SMT)
            $itemSet = (new Set())->useTransactionManager()->MboSetRemote("integmanualitem")->where("integmanualnum = ?", $selectedManual)->MboRemote("proceduresnum")->getMboSet()->fetchData();
            $procedureNums = array_filter(array_unique(array_column($itemSet, 'proceduresnum')));

            // Helper untuk kondisi status query
            $applyStatusFilter = function($query, $stFilter) {
                if ($stFilter === 'active') {
                    $query->where("status = ?", "active");
                } elseif ($stFilter === 'inactive') {
                    $query->where("status = ?", "inactive");
                } elseif ($stFilter === 'in_progress') {
                    $query->where("status IN ('draft', 'pending', 'in_progress')");
                }
            };

            // ====================================================================
            // EKsekusi Query Berdasarkan Target Tabel & Filter
            // ====================================================================

            if ($targetTable === 'procedures') {
                $columns = [
                    ["title" => "Procedure Num", "data" => "proceduresnum"],
                    ["title" => "Doc Identifier", "data" => "docidentifier"],
                    ["title" => "Document Name", "data" => "docname"],
                    ["title" => "Category", "data" => "category"],
                    ["title" => "Status", "data" => "status"],
                    ["title" => "Owner Team", "data" => "ownerteam"],
                    ["title" => "Enter Date", "data" => "enterdate"]
                ];

                $query = (new Set())->useTransactionManager()->MboSetRemote("procedures");
                
                if (!empty($clickedNodeId)) {
                    $query->where("proceduresnum = ?", $clickedNodeId);
                } else {
                    if (!empty($procedureNums)) {
                        $query->whereIn("proceduresnum", $procedureNums);
                    } else {
                        $query->where("1 = 0");
                    }
                    // Terapkan filter status dari Stacked Bar jika ada
                    if (!empty($statusFilter)) {
                        $applyStatusFilter($query, $statusFilter);
                    }
                }

                $rs = $query->MboRemote("proceduresnum, docidentifier, docname, category, status, ownerteam, enterdate")->getMboSet();
                while ($row = $rs->fetch()) {
                    $records[] = [
                        "proceduresnum" => trim($row['proceduresnum']),
                        "docidentifier" => trim($row['docidentifier']),
                        "docname" => trim($row['docname']),
                        "category" => trim($row['category']),
                        "status" => trim($row['status']),
                        "ownerteam" => trim($row['ownerteam'] ?? '-'),
                        "enterdate" => date('Y-m-d', strtotime($row['enterdate']))
                    ];
                }

            } elseif ($targetTable === 'workinstr') {
                $columns = [
                    ["title" => "Work Instr Num", "data" => "workinstrnum"],
                    ["title" => "Parent Proc", "data" => "proceduresnum"],
                    ["title" => "Doc Identifier", "data" => "docidentifier"],
                    ["title" => "Work Instr Name", "data" => "workinstrname"],
                    ["title" => "Category", "data" => "category"],
                    ["title" => "Status", "data" => "status"],
                    ["title" => "Owner Team", "data" => "ownerteam"],
                    ["title" => "Enter Date", "data" => "enterdate"]
                ];

                $query = (new Set())->useTransactionManager()->MboSetRemote("workinstr");

                if (!empty($clickedNodeId)) {
                    $query->where("workinstrnum = ?", $clickedNodeId);
                } else {
                    if (!empty($procedureNums)) {
                        $query->whereIn("proceduresnum", $procedureNums);
                    } else {
                        $query->where("1 = 0");
                    }
                    if (!empty($statusFilter)) {
                        $applyStatusFilter($query, $statusFilter);
                    }
                }

                $rs = $query->MboRemote("workinstrnum, proceduresnum, docidentifier, workinstrname, category, status, ownerteam, enterdate")->getMboSet();
                while ($row = $rs->fetch()) {
                    $records[] = [
                        "workinstrnum" => trim($row['workinstrnum']),
                        "proceduresnum" => trim($row['proceduresnum']),
                        "docidentifier" => trim($row['docidentifier']),
                        "workinstrname" => trim($row['workinstrname']),
                        "category" => trim($row['category']),
                        "status" => trim($row['status']),
                        "ownerteam" => trim($row['ownerteam'] ?? '-'),
                        "enterdate" => date('Y-m-d', strtotime($row['enterdate']))
                    ];
                }

            } elseif ($targetTable === 'docform') {
                $columns = [
                    ["title" => "Doc Form Num", "data" => "docformnum"],
                    ["title" => "Parent WI", "data" => "workinstrnum"],
                    ["title" => "Doc Identifier", "data" => "docformidentifier"],
                    ["title" => "Doc Form Name", "data" => "docformname"],
                    ["title" => "Category", "data" => "category"],
                    ["title" => "Status", "data" => "status"],
                    ["title" => "Enter Date", "data" => "enterdate"]
                ];

                $query = (new Set())->useTransactionManager()->MboSetRemote("docform");

                if (!empty($clickedNodeId)) {
                    $query->where("docformnum = ?", $clickedNodeId);
                } else {
                    // Cari work instructions yang terikat ke prosedur manual ini dulu untuk ambil docform
                    $wiSet = (new Set())->useTransactionManager()->MboSetRemote("workinstr")->whereIn("proceduresnum", $procedureNums)->MboRemote("workinstrnum")->getMboSet()->fetchData();
                    $wiNums = array_filter(array_unique(array_column($wiSet, 'workinstrnum')));

                    if (!empty($wiNums)) {
                        $query->whereIn("workinstrnum", $wiNums);
                    } else {
                        $query->where("1 = 0");
                    }

                    if (!empty($statusFilter)) {
                        $applyStatusFilter($query, $statusFilter);
                    }
                }

                $rs = $query->MboRemote("docformnum, workinstrnum, docformidentifier, docformname, category, status, enterdate")->getMboSet();
                while ($row = $rs->fetch()) {
                    $records[] = [
                        "docformnum" => trim($row['docformnum']),
                        "workinstrnum" => trim($row['workinstrnum'] ?? '-'),
                        "docformidentifier" => trim($row['docformidentifier']),
                        "docformname" => trim($row['docformname']),
                        "category" => trim($row['category']),
                        "status" => trim($row['status']),
                        "enterdate" => date('Y-m-d', strtotime($row['enterdate']))
                    ];
                }

            } else {
                // Default: Manual Integrasi
                $columns = [
                    ["title" => "Manual Num", "data" => "integmanualnum"],
                    ["title" => "Doc Identifier", "data" => "docidentifier"],
                    ["title" => "Manual Title", "data" => "manualtitle"],
                    ["title" => "Edition", "data" => "edition"],
                    ["title" => "Status", "data" => "status"],
                    ["title" => "Enter Date", "data" => "enterdate"]
                ];

                $rs = (new Set())->useTransactionManager()->MboSetRemote("integmanual")->where("integmanualnum = ?", $selectedManual)->MboRemote("integmanualnum, docidentifier, manualtitle, edition, status, enterdate")->getMboSet();
                while ($row = $rs->fetch()) {
                    $records[] = [
                        "integmanualnum" => trim($row['integmanualnum']),
                        "docidentifier" => trim($row['docidentifier']),
                        "manualtitle" => trim($row['manualtitle']),
                        "edition" => trim($row['edition']),
                        "status" => trim($row['status']),
                        "enterdate" => date('Y-m-d', strtotime($row['enterdate']))
                    ];
                }
            }

            Super::sendJsonResponse("success", "Table schema & filtered data loaded", [
                "columns" => $columns,
                "data" => $records
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }

    
    public static function getDocumentTypeRecordsFiltered() {
        try {
            $payload = Super::getParam("getDocumentTypeRecordsFiltered");
            $selectedManual = trim($payload["manualnum"] ?? 'DEFAULT');

            $set = new Set();

            // 1. Jika DEFAULT, cari manual active/pertama
            if ($selectedManual === 'DEFAULT' || empty($selectedManual)) {
                $activeManual = (new Set())->useTransactionManager()
                    ->MboSetRemote("integmanual")
                    ->where("status = ?", "active")
                    ->MboRemote("integmanualnum")
                    ->limit(1)
                    ->getMboSet()
                    ->fetch();
                if (!$activeManual) {
                    $activeManual = (new Set())->useTransactionManager()
                        ->MboSetRemote("integmanual")
                        ->MboRemote("integmanualnum")
                        ->limit(1)
                        ->getMboSet()
                        ->fetch();
                }
                $selectedManual = trim($activeManual['integmanualnum'] ?? '');
            }

            // 2. Ambil semua proceduresnum yang terikat ke manual ini dari integmanualitem
            $itemSet = (new Set())->useTransactionManager()
                ->MboSetRemote("integmanualitem")
                ->where("integmanualnum = ?", $selectedManual)
                ->MboRemote("proceduresnum")
                ->getMboSet()
                ->fetchData();

            $procedureNums = array_filter(array_unique(array_column($itemSet, 'proceduresnum')));

            if (empty($procedureNums)) {
                Super::sendJsonResponse("success", "Filtered records loaded", [
                    "procedures" => ["active" => 0, "inactive" => 0, "in_progress" => 0, "total" => 0],
                    "workinstr"  => ["active" => 0, "inactive" => 0, "in_progress" => 0, "total" => 0],
                    "docform"    => ["active" => 0, "inactive" => 0, "in_progress" => 0, "total" => 0]
                ]);
                return;
            }

            // 3. Hitung Statistik Procedures yang terikat
            $procStats = (new Set())->useTransactionManager()
                ->MboSetRemote("procedures")
                ->whereIn("proceduresnum", $procedureNums)
                ->MboRemote("
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count,
                    SUM(CASE WHEN status IN ('draft', 'pending', 'in_progress') THEN 1 ELSE 0 END) as in_progress_count,
                    COUNT(*) as total_count
                ")
                ->getMboSet()
                ->fetch();

            $procedureData = [
                "active" => (int)($procStats['active_count'] ?? 0),
                "inactive" => (int)($procStats['inactive_count'] ?? 0),
                "in_progress" => (int)($procStats['in_progress_count'] ?? 0),
                "total" => (int)($procStats['total_count'] ?? 0)
            ];

            // 4. Ambil Work Instructions yang terikat ke prosedur-prosedur tersebut
            $wiSet = (new Set())->useTransactionManager()
                ->MboSetRemote("workinstr")
                ->whereIn("proceduresnum", $procedureNums)
                ->MboRemote("workinstrnum")
                ->getMboSet()
                ->fetchData();

            $wiNums = array_filter(array_unique(array_column($wiSet, 'workinstrnum')));

            $workInstrData = ["active" => 0, "inactive" => 0, "in_progress" => 0, "total" => 0];
            if (!empty($wiNums)) {
                $wiStats = (new Set())->useTransactionManager()
                    ->MboSetRemote("workinstr")
                    ->whereIn("workinstrnum", $wiNums)
                    ->MboRemote("
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count,
                        SUM(CASE WHEN status IN ('draft', 'pending', 'in_progress') THEN 1 ELSE 0 END) as in_progress_count,
                        COUNT(*) as total_count
                    ")
                    ->getMboSet()
                    ->fetch();

                $workInstrData = [
                    "active" => (int)($wiStats['active_count'] ?? 0),
                    "inactive" => (int)($wiStats['inactive_count'] ?? 0),
                    "in_progress" => (int)($wiStats['in_progress_count'] ?? 0),
                    "total" => (int)($wiStats['total_count'] ?? 0)
                ];
            }

            // 5. Ambil Doc Forms yang terikat ke work instructions tersebut
            $docFormData = ["active" => 0, "inactive" => 0, "in_progress" => 0, "total" => 0];
            if (!empty($wiNums)) {
                $formSet = (new Set())->useTransactionManager()
                    ->MboSetRemote("docform")
                    ->whereIn("workinstrnum", $wiNums)
                    ->MboRemote("docformnum")
                    ->getMboSet()
                    ->fetchData();

                $formNums = array_filter(array_unique(array_column($formSet, 'docformnum')));

                if (!empty($formNums)) {
                    $formStats = (new Set())->useTransactionManager()
                        ->MboSetRemote("docform")
                        ->whereIn("docformnum", $formNums)
                        ->MboRemote("
                            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count,
                            SUM(CASE WHEN status IN ('draft', 'pending', 'in_progress') THEN 1 ELSE 0 END) as in_progress_count,
                            COUNT(*) as total_count
                        ")
                        ->getMboSet()
                        ->fetch();

                    $docFormData = [
                        "active" => (int)($formStats['active_count'] ?? 0),
                        "inactive" => (int)($formStats['inactive_count'] ?? 0),
                        "in_progress" => (int)($formStats['in_progress_count'] ?? 0),
                        "total" => (int)($formStats['total_count'] ?? 0)
                    ];
                }
            }

            Super::sendJsonResponse("success", "Filtered document type records loaded", [
                "selected_manual" => $selectedManual,
                "procedures" => $procedureData,
                "workinstr"  => $workInstrData,
                "docform"    => $docFormData
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }
    public static function getFilteredKnowledgeGraph() {
        try {
            $payload = Super::getParam("getFilteredKnowledgeGraph");
            $selectedManual = trim($payload["manualnum"] ?? 'DEFAULT');

            $nodes = [];
            $links = [];
            $nodeRegistry = [];

            $addNode = function($name, $category, $size, $value) use (&$nodes, &$nodeRegistry) {
                if (!isset($nodeRegistry[$name])) {
                    $nodeRegistry[$name] = true;
                    $nodes[] = ["name" => $name, "category" => $category, "symbolSize" => $size, "value" => $value];
                }
            };

            // 1. Tentukan Manual yang akan digunakan
            if ($selectedManual === 'DEFAULT' || empty($selectedManual)) {
                $activeManual = (new Set())->useTransactionManager()
                    ->MboSetRemote("integmanual")
                    ->where("status = ?", "active")
                    ->MboRemote("integmanualnum")
                    ->limit(1)
                    ->getMboSet()
                    ->fetch();

                if (!$activeManual) {
                    $activeManual = (new Set())->useTransactionManager()
                        ->MboSetRemote("integmanual")
                        ->MboRemote("integmanualnum")
                        ->limit(1)
                        ->getMboSet()
                        ->fetch();
                }

                $selectedManual = trim($activeManual['integmanualnum'] ?? '');
            }

            if (empty($selectedManual)) {
                Super::sendJsonResponse("error", "No manual found.");
                return;
            }

            // 2. Ambil Data Manual Utama (1 Kueri)
            $manualData = (new Set())->useTransactionManager()
                ->MboSetRemote("integmanual")
                ->where("integmanualnum = ?", $selectedManual)
                ->MboRemote("integmanualnum, manualtitle, status")
                ->getMboSet()
                ->fetch();

            if (!$manualData) {
                Super::sendJsonResponse("error", "Manual '$selectedManual' not found.");
                return;
            }

            $mNum = trim($manualData['integmanualnum']);
            $addNode($mNum, 0, 55, "Manual: " . $manualData['manualtitle'] . " (" . $manualData['status'] . ")");

            // 3. Ambil SEMUA Procedures yang terikat dengan Manual ini (1 Kueri)
            $itemSet = (new Set())->useTransactionManager()
                ->MboSetRemote("integmanualitem")
                ->where("integmanualnum = ?", $mNum)
                ->MboRemote("proceduresnum")
                ->getMboSet()
                ->fetchData();

            $procedureNums = array_filter(array_unique(array_column($itemSet, 'proceduresnum')));

            if (empty($procedureNums)) {
                // Jika tidak ada procedure, langsung kembalikan node manual saja
                Super::sendJsonResponse("success", "Single filtered graph loaded", [
                    "selected_manual" => $selectedManual,
                    "nodes" => $nodes, 
                    "links" => $links
                ]);
                return;
            }

            // Tambahkan Node & Link untuk Procedures
            foreach ($procedureNums as $pNum) {
                $addNode($pNum, 1, 40, "Procedure");
                $links[] = ["source" => $mNum, "target" => $pNum, "label" => ["show" => true, "formatter" => "GOVERNS"]];
            }

            // 4. Ambil SEMUA Work Instructions yang terikat dengan Procedure-procedure ini (1 Kueri dengan whereIn!)
            $wiSet = (new Set())->useTransactionManager()
                ->MboSetRemote("workinstr")
                ->whereIn("proceduresnum", $procedureNums)
                ->MboRemote("workinstrnum, proceduresnum")
                ->getMboSet()
                ->fetchData();

            $wiMap = []; // Untuk melacak relasi parent-child
            $wiNums = [];
            foreach ($wiSet as $wi) {
                $wiNum = trim($wi['workinstrnum']);
                $pNum = trim($wi['proceduresnum']);
                if (!empty($wiNum)) {
                    $wiNums[] = $wiNum;
                    $wiMap[$wiNum] = $pNum; // Simpan parent-nya (pNum)

                    $addNode($wiNum, 2, 28, "Work Instruction");
                    $links[] = ["source" => $pNum, "target" => $wiNum, "label" => ["show" => true, "formatter" => "HAS_IK"]];
                }
            }
            $wiNums = array_unique($wiNums);

            if (empty($wiNums)) {
                Super::sendJsonResponse("success", "Single filtered graph loaded", [
                    "selected_manual" => $selectedManual,
                    "nodes" => $nodes, 
                    "links" => $links
                ]);
                return;
            }

            // 5. Ambil SEMUA Doc Forms yang terikat dengan Work Instruction ini (1 Kueri dengan whereIn!)
            $formSet = (new Set())->useTransactionManager()
                ->MboSetRemote("docform")
                ->whereIn("workinstrnum", $wiNums)
                ->MboRemote("docformnum, workinstrnum")
                ->getMboSet()
                ->fetchData();

            foreach ($formSet as $f) {
                $fNum = trim($f['docformnum']);
                $wiNum = trim($f['workinstrnum']);
                if (!empty($fNum)) {
                    $addNode($fNum, 3, 20, "Doc Form");
                    $links[] = ["source" => $wiNum, "target" => $fNum, "label" => ["show" => true, "formatter" => "USES_FORM"]];
                }
            }

            // 6. Kirim Respons Akhir
            Super::sendJsonResponse("success", "Single filtered graph loaded", [
                "selected_manual" => $selectedManual,
                "nodes" => $nodes, 
                "links" => $links
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }
    public static function getManualDropdownList() {
        try {
            $set = new Set();
            $manuals = [];

            // Ambil data dari tabel integmanual (bisa difilter yang aktif saja atau semuanya)
            $manualSet = $set->useTransactionManager()
                ->MboSetRemote("integmanual")
                ->MboRemote("integmanualnum, manualtitle, edition, status")
                ->getMboSet();

            while ($m = $manualSet->fetch()) {
                $manuals[] = [
                    "num" => trim($m['integmanualnum']),
                    "title" => trim($m['manualtitle']),
                    "edition" => trim($m['edition']),
                    "status" => trim($m['status'])
                ];
            }

            Super::sendJsonResponse("success", "Manual dropdown list loaded", [
                "manuals" => $manuals
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }

    public static function getKnowledgeGraphData() {
        try {
            $nodes = [];
            $links = [];
            $nodeRegistry = []; 

            $addNode = function($name, $category, $size, $value) use (&$nodes, &$nodeRegistry) {
                if (!empty($name) && !isset($nodeRegistry[$name])) {
                    $nodeRegistry[$name] = true;
                    $nodes[] = [
                        "name" => trim($name),
                        "category" => (int)$category,
                        "symbolSize" => (int)$size,
                        "value" => (string)$value
                    ];
                }
            };

            // ==========================================
            // 1. AMBIL SEMUA DATA DALAM 4-5 KUERI UTAMA SAJA
            // ==========================================

            // A. Ambil semua Manual
            $manuals = (new Set())->useTransactionManager()
                ->MboSetRemote("integmanual")
                ->MboRemote("integmanualnum, manualtitle, status")
                ->getMboSet()
                ->fetchData();

            if (empty($manuals)) {
                Super::sendJsonResponse("success", "Knowledge graph data loaded", ["nodes" => [], "links" => []]);
                return;
            }

            $manualNums = array_column($manuals, 'integmanualnum');

            // B. Ambil semua Manual Item (Relasi Manual -> Procedures)
            $manualItems = (new Set())->useTransactionManager()
                ->MboSetRemote("integmanualitem")
                ->whereIn("integmanualnum", $manualNums)
                ->MboRemote("integmanualnum, proceduresnum")
                ->getMboSet()
                ->fetchData();

            // C. Ambil semua Procedures
            $procedures = (new Set())->useTransactionManager()
                ->MboSetRemote("procedures")
                ->MboRemote("proceduresnum, docname, ownerteam")
                ->getMboSet()
                ->fetchData();

            $procNums = array_column($procedures, 'proceduresnum');

            // D. Ambil semua Work Instructions turunan dari Procedures tersebut (Relasi Procedure -> WI)
            $workInstructions = [];
            if (!empty($procNums)) {
                $workInstructions = (new Set())->useTransactionManager()
                    ->MboSetRemote("workinstr")
                    ->whereIn("proceduresnum", $procNums)
                    ->MboRemote("proceduresnum, workinstrnum, workinstrname")
                    ->getMboSet()
                    ->fetchData();
            }

            $wiNums = array_column($workInstructions, 'workinstrnum');

            // E. Ambil semua Doc Forms turunan dari WI tersebut (Relasi WI -> Form)
            $docForms = [];
            if (!empty($wiNums)) {
                $docForms = (new Set())->useTransactionManager()
                    ->MboSetRemote("docform")
                    ->whereIn("workinstrnum", $wiNums)
                    ->MboRemote("workinstrnum, docformnum, docformname")
                    ->getMboSet()
                    ->fetchData();
            }

            // ==========================================
            // 2. PROSES DAN PETAKAN DATA DI MEMORI PHP
            // ==========================================

            // Proses 1: Manual & Relasinya ke Procedure
            foreach ($manuals as $m) {
                $mNum = trim($m['integmanualnum']);
                $addNode($mNum, 0, 55, "Manual: " . ($m['manualtitle'] ?? ''));
            }

            foreach ($manualItems as $item) {
                $mNum = trim($item['integmanualnum']);
                $pNum = trim($item['proceduresnum']);
                if (!empty($mNum) && !empty($pNum)) {
                    $addNode($pNum, 1, 40, "Procedure");
                    $links[] = [
                        "source" => $mNum,
                        "target" => $pNum,
                        "label" => ["show" => true, "formatter" => "GOVERNS"]
                    ];
                }
            }

            // Proses 2: Procedures, Owner Team, dan Relasi ke Work Instructions
            // Buat map work instructions berdasarkan proceduresnum agar pencarian cepat
            $wiMap = [];
            foreach ($workInstructions as $wi) {
                $pNum = trim($wi['proceduresnum']);
                $wiMap[$pNum][] = $wi;
            }

            // Buat map doc forms berdasarkan workinstrnum
            $formMap = [];
            foreach ($docForms as $f) {
                $wiNum = trim($f['workinstrnum']);
                $formMap[$wiNum][] = $f;
            }

            foreach ($procedures as $p) {
                $pNum = trim($p['proceduresnum']);
                $addNode($pNum, 1, 40, "Procedure");

                // Relasi Owner Team
                $ownerTeam = trim($p['ownerteam'] ?? '');
                if (!empty($ownerTeam)) {
                    $addNode($ownerTeam, 4, 32, "Team Decree");
                    $links[] = [
                        "source" => $ownerTeam,
                        "target" => $pNum,
                        "label" => ["show" => true, "formatter" => "OWNED_BY"]
                    ];
                }

                // Ambil WI dari Map yang sudah disiapkan (tanpa kueri database tambahan!)
                if (isset($wiMap[$pNum])) {
                    foreach ($wiMap[$pNum] as $wi) {
                        $wiNum = trim($wi['workinstrnum']);
                        $addNode($wiNum, 2, 28, "Work Instruction");
                        $links[] = [
                            "source" => $pNum,
                            "target" => $wiNum,
                            "label" => ["show" => true, "formatter" => "HAS_IK"]
                        ];

                        // Ambil Forms dari Map (tanpa kueri database tambahan!)
                        if (isset($formMap[$wiNum])) {
                            foreach ($formMap[$wiNum] as $f) {
                                $fNum = trim($f['docformnum']);
                                $addNode($fNum, 3, 20, "Doc Form");
                                $links[] = [
                                    "source" => $wiNum,
                                    "target" => $fNum,
                                    "label" => ["show" => true, "formatter" => "USES_FORM"]
                                ];
                            }
                        }
                    }
                }
            }

            Super::sendJsonResponse("success", "Knowledge graph data loaded", [
                "nodes" => $nodes,
                "links" => $links
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }
    public static function getDocumentTypeRecords() {
        try {
            // Helper function yang dioptimalkan menggunakan Conditional Aggregation (1 Kueri per Tabel)
            $getCountsByTable = function($tableName) {
                // Sanitasi nama tabel untuk keamanan (menggunakan helper yang sudah ada di library)
                $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

                // Satu kueri pintar untuk menghitung semua status sekaligus di level database!
                $stats = (new Set())->useTransactionManager()
                    ->MboSetRemote($safeTable)
                    ->MboRemote("
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count,
                        SUM(CASE WHEN status IN ('draft', 'pending', 'in_progress') THEN 1 ELSE 0 END) as in_progress_count,
                        COUNT(*) as total_count
                    ")
                    ->getMboSet()
                    ->fetch(); // Ambil satu baris hasil agregasi

                // Jika tabel kosong atau error, berikan nilai default 0
                $active = (int)($stats['active_count'] ?? 0);
                $inactive = (int)($stats['inactive_count'] ?? 0);
                $inProgress = (int)($stats['in_progress_count'] ?? 0);
                $total = (int)($stats['total_count'] ?? 0);

                return [
                    "active" => $active,
                    "inactive" => $inactive,
                    "in_progress" => $inProgress,
                    "total" => $total
                ];
            };

            // Ambil data untuk 3 entitas dokumen (Sekarang masing-masing hanya 1 kueri!)
            $procedureData = $getCountsByTable("procedures");
            $workInstrData = $getCountsByTable("workinstr");
            $docFormData   = $getCountsByTable("docform");

            Super::sendJsonResponse("success", "Document type records loaded", [
                "procedures" => $procedureData,
                "workinstr"  => $workInstrData,
                "docform"    => $docFormData
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }

    public static function getKpiSummary() {
        try {
            
            // 1. Hitung Active Procedures (status = 'active')
            $procSet = (new Set())->useTransactionManager()
                ->MboSetRemote("procedures")
                ->where("status = ?", "active")
                ->MboRemote("proceduresnum")
                ->getMboSet();
            $activeProcCount = $procSet->counts();

            // 2. Hitung Work Instructions (status = 'active')
            $wiSet = (new Set())->useTransactionManager()
                ->MboSetRemote("workinstr")
                ->where("status = ?", "active")
                ->MboRemote("workinstrnum")
                ->getMboSet();
            $activeWiCount = $wiSet->counts();

            // 3. Hitung Active Team Decrees / SK Tim (status = 'active')
            $skSet = (new Set())->useTransactionManager()
                ->MboSetRemote("teamdecree")->setRelationship("procedures", "teamdecree.teamdecreenum = procedures.ownerteam")
                ->where("teamdecree.status = ?", "active")
                ->MboRemote("teamdecree.teamdecreenum")
                ->MboRemote("procedures.proceduresnum")
                ->getMboSet();
              
            $activeSkCount = $skSet->counts();

            // 4. Hitung Open Change Requests / Doc Tickets (status IN ('new', 'in_progress'))
            // Menggunakan parameterized query dengan placeholder berulang atau logika aman
            $ticketSet = (new Set())->useTransactionManager()
                ->MboSetRemote("docticket")
                ->where("status = ? OR status = ?", "new", "in_progress")
                ->MboRemote("docticketnum")
                ->getMboSet();
            $openTicketCount = $ticketSet->counts();

            // Kirim response JSON bersih ke frontend
            Super::sendJsonResponse("success", "KPI data loaded successfully", [
                "procedures_count" => $activeProcCount,
                "workinstr_count" => $activeWiCount,
                "teamdecree_count" => $activeSkCount,
                "docticket_count" => $openTicketCount
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }
    
}

?>