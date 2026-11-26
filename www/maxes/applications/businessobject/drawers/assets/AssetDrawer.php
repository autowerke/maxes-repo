<?php

class AssetDrawer
{
    /**
     * Entry point utama yang akan dipanggil oleh run.php
     */
    public static function getDrawerData()
    {
        try {
            // Ambil payload dari xhr.commonRun
            $payload = Super::getParam("getDrawerAssetData");
            
            // Tentukan action: apakah minta 'list' atau 'detail'
            $action = $payload['action'] ?? 'list'; 

            if ($action === 'list') {
                self::getAssetList($payload);
            } elseif ($action === 'detail') {
                self::getAssetDetail($payload);
            } else {
                Super::sendJsonResponse("error", "Unknown drawer action.");
            }

        } catch (Exception $e) {
            Super::sendJsonResponse("error", "Server Error: " . $e->getMessage());
        }
    }

   
    private static function getAssetList($payload)
    {
        $search = $payload['search'] ?? ''; 

        $assetSet = (new Set())->useTransactionManager()->MboSetRemote("assets");

        // LOGIKA SEARCH GLOBAL
        if (!empty($search)) {
            $assetSet->where(
                "(assetnum LIKE ? OR description LIKE ? OR locnum LIKE ? OR model LIKE ?)", 
                "%$search%", "%$search%", "%$search%", "%$search%"
            );
        }

        $assets = $assetSet->MboRemote("assetnum, description, locnum, status, assettype")
                        ->limit(500) // Tetap batasi hasil yang dikirim ke browser
                        ->getMboSet()
                        ->fetchData();

        Super::sendJsonResponse("success", "Loaded", $assets);
    }

    /**
     * Task 2: Mengambil detail Asset + Relasinya ketika di-klik
     */
    private static function getAssetDetail($payload)
    {
        $assetnum = $payload['assetnum'] ?? '';
        if (empty($assetnum)) {
            throw new InvalidArgumentException("Assetnum is required to fetch details.");
        }

        // 2.1 Header Asset 
        $assetData = (new Set())->useTransactionManager()
            ->MboSetRemote("assets")
            ->where("assetnum = ?", $assetnum)
            ->getMboSet()
            ->fetch();

        if (!$assetData) {
            throw new Exception("Asset '$assetnum' not found.");
        }

        // 2.2.A Relationship: Classification
        $classifications = (new Set())->useTransactionManager()
            ->MboSetRemote("assetclassification")
            ->where("assetnum = ?", $assetnum)
            ->MboRemote("attributename, classificationcode, description, value, unit")
            ->getMboSet()
            ->fetchData();

        // 2.2.B Relationship: Maintenance (workclass not in PRD, PRODUCTION)
        $maintenance = (new Set())->useTransactionManager()
            ->MboSetRemote("workorder")
            ->where("assetnum = ?", $assetnum)
            ->where("workclass NOT IN (?, ?)", 'PRD', 'PRODUCTION') // AMAN dari SQL Injection
            ->MboRemote("wonum, owner, leader, targstartdate, targcompdate, actstartdate, actfinishdate, actualcost, plannedcost")
            ->order("targstartdate DESC")
            ->getMboSet()
            ->fetchData();

        // 2.2.C Relationship: Production Log
        $productionLogs = (new Set())->useTransactionManager()
            ->MboSetRemote("productionlog")
            ->where("assetnum = ?", $assetnum)
            ->MboRemote("itemnum, issueunit, productiondate, quantity, unitcost, linecost, outputtype, batchnum")
            ->order("productiondate DESC")
            ->getMboSet()
            ->fetchData();

        // 2.2.D Relationship: Asset Meter Reading (LEFT JOIN ke tabel meter)
        $sqlMeter = "
            SELECT 
                am.meternum, am.lastreading, am.lastreadingdate, 
                am.avgdailyusage, am.predictedduedate, am.daysuntildue, am.unitstogo,
                m.description, m.metertype, m.measureunit
            FROM assetmeter am
            LEFT JOIN meter m ON am.meternum = m.meternum
            WHERE am.assetnum = ?
        ";
        $meters = Set::fetchAll($sqlMeter, [$assetnum]);
        $appUrl = Super::getApplicationURL("assets");
        $applinksownernum = "$assetnum%assets";

        $responseData =[
            'header' => $assetData,
            'appUrl' => $appUrl,
            'applinksownernum' => $applinksownernum,
            'classifications' => $classifications,
            'maintenance' => $maintenance,
            'production' => $productionLogs,
            'meters' => $meters
        ];

        Super::sendJsonResponse("success", "Asset details loaded", $responseData);
    }
}
?>