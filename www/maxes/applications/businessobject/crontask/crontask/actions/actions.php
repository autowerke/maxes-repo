<?php

$dir =  __DIR__ . '/../../../eat.php';
require_once ($dir);
define('DEFAULT_PM_ALERT_LEAD_DAYS', 7);


/* MACHINE LEARNING/AI Cron Task */



/**
 * AICronTasks Class
 * Contains all scheduled jobs related to the Maxes ML Engine.
 */
class AICronTasks {
    /**
     * Calculates the current weighted health score for a given asset.
     * Supports both positive and negative meter ranges with smooth score gradation.
     *
     * @param string $assetnum The asset number to calculate the score for.
     * @param object|null $tm The TransactionManager instance if running within a transaction.
     * @return float|null The calculated health score (0-100), or null if calculation fails.
     */
    public static function calculateAssetHealthScore($assetnum, $tm = null) {
        echo "    -> Calculating current health score for asset: $assetnum\n";
        
        try {
            // Helper untuk menggunakan TransactionManager jika tersedia
            $newSet = function() use ($tm) {
                $set = new Set();
                return $tm ? $set->useTransactionManager() : $set;
            };

            // 1. Ambil Konfigurasi Kesehatan untuk Aset ini
            $configs = $newSet()->MboSetRemote('assetmeterconfig')
                                ->where("assetnum = ?", $assetnum)
                                ->getMboSet()
                                ->fetchDataAssoc('meternum');
            
            if (empty($configs)) {
                echo "    WARN: No assetmeterconfig found for $assetnum. Cannot calculate health score.\n";
                return null;
            }

            // 2. Ambil Pembacaan Meter Terakhir untuk Aset ini
            $readings = $newSet()->MboSetRemote('assetmeter')
                                ->where("assetnum = ?", $assetnum)
                                ->getMboSet()
                                ->fetchDataAssoc('meternum');
                                
            if (empty($readings)) {
                echo "    WARN: No current meter readings found for $assetnum. Cannot calculate health score.\n";
                return null;
            }

            $weighted_score_sum = 0;
            $total_weight = 0;
            $meter_details = []; // Untuk debugging

            // 3. Loop melalui setiap konfigurasi meter
            foreach ($configs as $meternum => $config) {
                // Cek jika ada pembacaan untuk meter ini dan ada bobotnya
                if (!isset($readings[$meternum]) || $config['health_impact_weight'] <= 0) {
                    continue;
                }
                
                $reading_value = (float)$readings[$meternum]['lastreading'];
                $weight = (float)$config['health_impact_weight'];
                
                // Konversi ambang batas dari string ke float
                $limit_high = (float)($config['limit_high'] ?? 0);
                $warning_high = (float)($config['warning_high'] ?? 0);
                $normal_high = (float)($config['normal_high'] ?? 0);
                $normal_low = (float)($config['normal_low'] ?? 0);
                $warning_low = (float)($config['warning_low'] ?? 0);
                $limit_low = (float)($config['limit_low'] ?? 0);

                // 4. HITUNG SKOR METER (0-100) dengan gradasi halus
                $meter_score = self::calculateMeterScore(
                    $reading_value,
                    $normal_low, $normal_high,
                    $warning_low, $warning_high,
                    $limit_low, $limit_high
                );
                
                // 5. Akumulasi skor tertimbang
                $weighted_score_sum += $meter_score * $weight;
                $total_weight += $weight;
                
                // Debugging info
                $meter_details[] = [
                    'meter' => $meternum,
                    'value' => $reading_value,
                    'score' => round($meter_score, 1),
                    'weight' => $weight,
                    'weighted' => round($meter_score * $weight, 1)
                ];
            }
            
            if ($total_weight > 0) {
                $final_score = $weighted_score_sum / $total_weight;
                echo "    -> Calculated Health Score: " . round($final_score, 2) . "\n";
                
                // Debug: Tampilkan breakdown per meter
                foreach ($meter_details as $detail) {
                    echo "       [{$detail['meter']}] Value: {$detail['value']}, Score: {$detail['score']}, Weight: {$detail['weight']}, Weighted: {$detail['weighted']}\n";
                }
                
                return $final_score;
            } else {
                echo "    WARN: No meters with valid weights found. Returning default score.\n";
                return 100.0;
            }

        } catch (Exception $e) {
            error_log("Error in calculateAssetHealthScore for $assetnum: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate individual meter health score (0-100) based on reading value and thresholds.
     * Supports both positive and negative ranges with smooth gradation.
     *
     * @param float $value Current meter reading
     * @param float $normalLow Lower bound of normal (green) zone
     * @param float $normalHigh Upper bound of normal (green) zone
     * @param float $warningLow Lower bound of warning (yellow) zone
     * @param float $warningHigh Upper bound of warning (yellow) zone
     * @param float $limitLow Lower critical limit (red zone)
     * @param float $limitHigh Upper critical limit (red zone)
     * @return float Score from 0 (critical) to 100 (optimal)
     */
    private static function calculateMeterScore($value, $normalLow, $normalHigh, $warningLow, $warningHigh, $limitLow, $limitHigh) {
        
        // Deteksi apakah ini range negatif (vacuum/draft)
        $isNegativeRange = ($limitLow < 0 || $normalLow < 0 || $value < 0);
        
        // Null-safe: Jika threshold adalah 0, anggap tidak ada batas
        $hasNormalLow = $normalLow != 0;
        $hasNormalHigh = $normalHigh != 0;
        $hasWarningLow = $warningLow != 0;
        $hasWarningHigh = $warningHigh != 0;
        $hasLimitLow = $limitLow != 0;
        $hasLimitHigh = $limitHigh != 0;
        
        // ========== KASUS 1: RANGE NORMAL (Nilai ideal di tengah) ==========
        if ($hasNormalLow && $hasNormalHigh) {
            
            // Zona HIJAU (100 poin): Dalam range normal
            if ($value >= $normalLow && $value <= $normalHigh) {
                return 100.0;
            }
            
            // Zona KUNING ATAS (50-99 poin): Antara normal_high dan warning_high/limit_high
            if ($value > $normalHigh) {
                $upper_warning = $hasWarningHigh ? $warningHigh : ($hasLimitHigh ? $limitHigh : $normalHigh * 1.2);
                
                if ($value <= $upper_warning) {
                    // Gradasi linear dari 100 (di normal_high) ke 50 (di warning_high)
                    return 100 - (($value - $normalHigh) / ($upper_warning - $normalHigh)) * 50;
                }
                
                // Zona MERAH ATAS (0-49 poin): Di atas warning_high
                $upper_limit = $hasLimitHigh ? $limitHigh : $upper_warning * 1.2;
                if ($value <= $upper_limit) {
                    return 50 - (($value - $upper_warning) / ($upper_limit - $upper_warning)) * 50;
                }
                
                return 0.0; // Melewati limit
            }
            
            // Zona KUNING BAWAH (50-99 poin): Antara warning_low/limit_low dan normal_low
            if ($value < $normalLow) {
                $lower_warning = $hasWarningLow ? $warningLow : ($hasLimitLow ? $limitLow : $normalLow * 0.8);
                
                if ($value >= $lower_warning) {
                    // Gradasi linear dari 100 (di normal_low) ke 50 (di warning_low)
                    return 100 - (($normalLow - $value) / ($normalLow - $lower_warning)) * 50;
                }
                
                // Zona MERAH BAWAH (0-49 poin): Di bawah warning_low
                $lower_limit = $hasLimitLow ? $limitLow : $lower_warning * 0.8;
                if ($value >= $lower_limit) {
                    return 50 - (($lower_warning - $value) / ($lower_warning - $lower_limit)) * 50;
                }
                
                return 0.0; // Melewati limit
            }
        }
        
        // ========== KASUS 2: HANYA BATAS BAWAH (Nilai tinggi lebih baik) ==========
        else if ($hasNormalLow && !$hasNormalHigh) {
            
            // Zona HIJAU: Di atas normal_low
            if ($value >= $normalLow) {
                return 100.0;
            }
            
            // Zona KUNING: Antara warning_low dan normal_low
            $lower_warning = $hasWarningLow ? $warningLow : ($hasLimitLow ? $limitLow : $normalLow * 0.8);
            if ($value >= $lower_warning) {
                return 100 - (($normalLow - $value) / ($normalLow - $lower_warning)) * 50;
            }
            
            // Zona MERAH: Di bawah warning_low
            $lower_limit = $hasLimitLow ? $limitLow : $lower_warning * 0.8;
            if ($value >= $lower_limit) {
                return 50 - (($lower_warning - $value) / ($lower_warning - $lower_limit)) * 50;
            }
            
            return 0.0;
        }
        
        // ========== KASUS 3: HANYA BATAS ATAS (Nilai rendah lebih baik) ==========
        else if (!$hasNormalLow && $hasNormalHigh) {
            
            // Zona HIJAU: Di bawah normal_high
            if ($value <= $normalHigh) {
                return 100.0;
            }
            
            // Zona KUNING: Antara normal_high dan warning_high
            $upper_warning = $hasWarningHigh ? $warningHigh : ($hasLimitHigh ? $limitHigh : $normalHigh * 1.2);
            if ($value <= $upper_warning) {
                return 100 - (($value - $normalHigh) / ($upper_warning - $normalHigh)) * 50;
            }
            
            // Zona MERAH: Di atas warning_high
            $upper_limit = $hasLimitHigh ? $limitHigh : $upper_warning * 1.2;
            if ($value <= $upper_limit) {
                return 50 - (($value - $upper_warning) / ($upper_limit - $upper_warning)) * 50;
            }
            
            return 0.0;
        }
        
        // ========== FALLBACK: Tidak ada konfigurasi yang valid ==========
        return 100.0; // Default optimal jika tidak ada threshold
    }

    /**
     * UTILITY: Get individual meter health score with details (for API endpoint)
     * 
     * @param string $assetnum Asset number
     * @param string $meternum Meter number
     * @return array|null ['score' => float, 'zone' => string, 'value' => float, 'config' => array]
     */
    public static function getMeterHealthDetail($assetnum, $meternum) {
        try {
            // Ambil config
            $config = (new Set())
                ->MboSetRemote('assetmeterconfig')
                ->where("assetnum = ? AND meternum = ?", $assetnum, $meternum)
                ->getMboSet()
                ->fetch();
            
            if (!$config) {
                return null;
            }
            
            // Ambil reading
            $reading = (new Set())
                ->MboSetRemote('assetmeter')
                ->where("assetnum = ? AND meternum = ?", $assetnum, $meternum)
                ->getMboSet()
                ->fetch();
            
            if (!$reading) {
                return null;
            }
            
            $value = (float)$reading['lastreading'];
            
            // Hitung score
            $score = self::calculateMeterScore(
                $value,
                (float)$config['normal_low'],
                (float)$config['normal_high'],
                (float)$config['warning_low'],
                (float)$config['warning_high'],
                (float)$config['limit_low'],
                (float)$config['limit_high']
            );
            
            // Tentukan zona
            $zone = 'UNKNOWN';
            if ($score >= 90) $zone = 'OPTIMAL';
            else if ($score >= 75) $zone = 'GOOD';
            else if ($score >= 50) $zone = 'WARNING';
            else if ($score >= 25) $zone = 'CRITICAL';
            else $zone = 'DANGER';
            
            return [
                'meternum' => $meternum,
                'value' => $value,
                'score' => round($score, 2),
                'zone' => $zone,
                'weight' => (float)$config['health_impact_weight'],
                'config' => [
                    'normal_low' => (float)$config['normal_low'],
                    'normal_high' => (float)$config['normal_high'],
                    'warning_low' => (float)$config['warning_low'],
                    'warning_high' => (float)$config['warning_high'],
                    'limit_low' => (float)$config['limit_low'],
                    'limit_high' => (float)$config['limit_high']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error in getMeterHealthDetail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calls a specific endpoint of the ML Engine API.
     * @param string $endpoint The endpoint to call (e.g., 'predict-failure-risk').
     */
    
    private static function callMLEngine($endpoint, $payload) {
       // $ai_base_url = 'http://127.0.0.1:8000'; // ML Engine base URL
        $ai_base_url = Super::getAIServer();
        $url = $ai_base_url . '/' . $endpoint;
        $data_string = json_encode($payload);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $http_code >= 400) {
            error_log("AI API Call Error to '$endpoint': [HTTP $http_code] " . $curl_error);
            return null;
        }

        return json_decode($response, true);
    }


    // --- Cron Task Method 1: Update Asset Health Scores ---
    public static function updateHealthScores() {
        echo "\n[AI_TASK] Starting Health Score Update...\n";
        
        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();
        
        try {
            // Get list of critical assets to monitor
            $assetSet = (new Set())->useTransactionManager();
            $assets = $assetSet->MboSetRemote('assets')
                               ->where("autotrainml = ?", 'Y')
                               ->getMboSet()
                               ->fetchData();

            if (empty($assets)) {
                echo "INFO: No assets marked for AI training ('autotrainml' = 'Y'). Job finished.\n";
                $tm->commit();
                return;
            }
            
            echo "INFO: Found " . count($assets) . " assets to update.\n";

            foreach ($assets as $asset) {
                $assetnum = $asset['assetnum'];
                echo "--> Processing asset: $assetnum ... ";

                // Call both prediction APIs
                $risk_payload = ['assetnum' => $assetnum];
                $risk_data = self::callMLEngine('predict-failure-risk', $risk_payload);
                $ttf_data = self::callMLEngine('predict-time-to-failure', $risk_payload);
                
                // Validate API responses
                if (!$risk_data || !$ttf_data || isset($risk_data['error']) || isset($ttf_data['error'])) {
                    echo "WARN: Failed to get AI prediction. Skipping.\n";
                    error_log("AI Cron (updateHealthScores): Failed to get prediction for $assetnum. RiskResp: " . json_encode($risk_data) . " TTFResp: " . json_encode($ttf_data));
                    continue; // Skip to the next asset
                }

                // Check if a record already exists
                $healthScoreSet = (new Set())->useTransactionManager();
                $count = $healthScoreSet->MboSetRemote('assethealthscore')
                                        ->where("assetnum = ?", $assetnum)
                                        ->counts();

                // Prepare the data to be saved
                $updateSet = (new Set())->useTransactionManager()
                                        ->MboSetRemote('assethealthscore')
                                        ->setValue('lastriskscore', $risk_data['risk_score'])
                                        ->setValue('risklevel', $risk_data['risk_level'])
                                        ->setValue('predictedtimetofail', $ttf_data['predicted_ttf_days'])
                                        ->setValue('predictedfailuredate', $ttf_data['predicted_failure_date']);

                $current_health_score = self::calculateAssetHealthScore($asset['assetnum'], $tm);
                echo json_encode($current_health_score);
                $updateSet->setValue('currenthealthscore', $current_health_score);
                if ($count == 0) {
                    $updateSet->setValue('assetnum', $assetnum)->add();
                    echo "INSERTED new score.\n";
                } else {
                    $updateSet->where("assetnum = ?", $assetnum)->change();
                    echo "UPDATED existing score.\n";
                }

                (new Set())->useTransactionManager()->MboSetRemote('assets')->where("assetnum=?", $assetnum)->setValue("healthrate", $current_health_score)->change();
            }
            
            $tm->commit();
            echo "[AI_TASK] Health Score Update finished successfully.\n";

        } catch (Exception $e) {
            $tm->rollback();
            $error_message = "FATAL ERROR during Health Score Update: " . $e->getMessage();
            echo $error_message . "\n";
            error_log("AI Cron Task (updateHealthScores) Exception: " . $e->getMessage());
        }
    }


    // --- Cron Task Method 2: Update Financial Summary : Recalculates and updates the financial impact summary table. ---

    public static function updateFinancialSummary() {
        echo "\n[AI_TASK] Starting Financial Summary Update...\n";
        
        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();
        
        try {
            // The aggregation query from our notebook
            $sql = "
                SELECT 
                    failurecode,
                    SUM(actualcost) as totalcost,
                    COUNT(*) as eventcount,
                    AVG(actualcost) as averagecost
                FROM workorder
                WHERE failurecode IS NOT NULL AND failurecode != '' AND actualcost > 0
                GROUP BY failurecode
            ";
            
            // Use the static fetchAll for raw queries
            $summary_data = Set::fetchAll($sql);
            
            if (empty($summary_data)) {
                 echo "INFO: No financial data to summarize. Job finished.\n";
                 $tm->commit();
                 return;
            }

            // A quick way to update is to truncate and re-insert
            (new Set())->useTransactionManager()->MboSetRemote('failurecostsummary')->truncate();
            echo "INFO: Truncated 'failurecostsummary' table. Inserting " . count($summary_data) . " new summary rows...\n";
            
            // Use addStream for efficient bulk inserting
            (new Set())->useTransactionManager()->MboSetRemote('failurecostsummary')->addStream($summary_data);

            $tm->commit();
            echo "[AI_TASK] Financial Summary Update finished successfully.\n";

        } catch (Exception $e) {
            $tm->rollback();
            $error_message = "FATAL ERROR during Financial Summary Update: " . $e->getMessage();
            echo $error_message . "\n";
            error_log("AI Cron Task (updateFinancialSummary) Exception: " . $e->getMessage());
        }
    }
}



Class CronTasks {
	
	
		
	private static function notifyTeamForNewPMWo($wonum, $teamnum, $wo_description, $trigger_source) {
    
    $usersToNotify = [];

    if (empty($teamnum)) {
        error_log("Cannot send notification for WO {$wonum}: No team is assigned.");
        return;
    }

    // Ambil semua anggota tim
    $teamMemberSet = (new Set())->useTransactionManager()
                                ->MboSetRemote("empteam")
                                ->where("teamnum=?", $teamnum)
                                ->getMboSet();

    if (!$teamMemberSet->isEmpty()) {
        while ($teamMember = $teamMemberSet->fetch()) {
            $username = Xbo::getUserName($teamMember["personid"]); 
            if ($username) {
                $usersToNotify[] = $username;
            }
        }
    }

    if (empty($usersToNotify)) {
        error_log("No users found in team {$teamnum} to notify for WO {$wonum}.");
        return;
    }

    $uniqueUsers = array_unique($usersToNotify);
    $workorderid = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum = ?", $wonum)->getMboSet()->getMbo()->getString("workorderid");
    
    $processName = "PM_WO_GENERATED"; // Default
    if ($trigger_source === 'METER_FREQ') {
        $processName = "PM_METER_FREQ_WO";
    }

    foreach ($uniqueUsers as $username) {
        $notificationData = [
            'username'    => $username, 
            'message'     => "New PM Work Order created: #{$wonum} - {$wo_description}",
            'objectname'  => 'workorder',
            'objectid'    => $workorderid, 
            'objectnum'   => $wonum, 
            'processname' => $processName
        ];
        Super::sendNotification($notificationData);
    }
}
    
	
	public static function executeReservationForWo($wonum, $enterby, $enterdate, $reservedfor){
		$set = new Set();
        $woplanmaterialSet = $set->useTransactionManager()->MboSetRemote("woplanmaterial")->setWhere("wonum='$wonum'")
		->MboRemote("itemnum")
		->MboRemote("quantity")
		->MboRemote("conditiontype")
		->MboRemote("warehouse")
		->MboRemote("racknum")->getMboSet();
		
		while($material = $woplanmaterialSet->fetch()){
			$itemnum = $material["itemnum"];
			$quantity_needed = floatval($material["quantity"]); 
			$warehouse = $material["warehouse"];
			$conditiontype = $material["conditiontype"];
			$racknum = $material["racknum"];
			
			$changeby = $enterby;
			// A. Buat klausa WHERE yang konsisten untuk query inventory & reservation
					
			$rackval = Xbo::RackWhereClause($racknum);
			$condval = Xbo::CondTypeWhereClause($conditiontype);
			$whereclauseItem = "itemnum='$itemnum' AND warehouse='$warehouse' $condval";
			// B. Hitung total stok fisik yang ada di gudang
			$inventorySet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($whereclauseItem)->MboRemote("SUM(quantity) as total_qty")->getMboSet();
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
			
			// START: BUSINESS LOGIC PEMBUATAN INVRESERVENUM 
			
			// Metode: 
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
				->setValue("targstartdate", $enterdate)
				->add();

		}
    }
	
	
	
	public static function calculateAverageDailyUsage() {
		echo "Starting calculation of Average Daily Usage for all meters...\n";

		// 1. Tentukan rentang waktu untuk analisis (misalnya, 90 hari terakhir).
		// Ini mencegah data yang sangat lama dan tidak relevan mempengaruhi perhitungan.
		$days_to_analyze = 90;
		$date_threshold = (new DateTime())->modify("-{$days_to_analyze} days")->format('Y-m-d H:i:s');

		// 2. Ambil daftar unik semua kombinasi aset & meter yang memiliki histori dalam rentang waktu tersebut.
		$meterListSet = (new Set())->useTransactionManager()
								   ->MboSetRemote("assetmeterhistory")
								   ->where("readingdate >= ?", $date_threshold)
								   ->MboRemote("DISTINCT assetnum, meternum")
								   ->getMboSet();
		
		if ($meterListSet->isEmpty()) {
			echo "No recent meter readings found to calculate. Process finished.\n";
			return;
		}

		echo "Found " . $meterListSet->counts() . " active asset-meter combinations to analyze.\n";

		while ($meter_info = $meterListSet->fetch()) {
			$assetnum = $meter_info['assetnum'];
			$meternum = $meter_info['meternum'];

			// 3. Untuk setiap kombinasi, ambil pembacaan pertama dan terakhir dalam rentang waktu.
			// Membutuhkan dua query terpisah untuk mendapatkan MIN dan MAX dengan andal.
			$firstReadingSet = (new Set())->useTransactionManager()
										  ->MboSetRemote("assetmeterhistory")
										  ->where("assetnum = ? AND meternum = ? AND readingdate >= ?", $assetnum, $meternum, $date_threshold)
										  ->order("readingdate ASC")
										  ->limit(1)
										  ->getMboSet();
			
			$lastReadingSet = (new Set())->useTransactionManager()
										 ->MboSetRemote("assetmeterhistory")
										 ->where("assetnum = ? AND meternum = ? AND readingdate >= ?", $assetnum, $meternum, $date_threshold)
										 ->order("readingdate DESC")
										 ->limit(1)
										 ->getMboSet();

			if ($firstReadingSet->isEmpty() || $lastReadingSet->isEmpty()) {
				continue; // Seharusnya tidak terjadi, tapi ini jaring pengaman.
			}

			$first_reading_mbo = $firstReadingSet->getMbo();
			$last_reading_mbo = $lastReadingSet->getMbo();

			$first_value = (float)$first_reading_mbo->getString("value");
			$last_value = (float)$last_reading_mbo->getString("value");
			
			$first_date = new DateTime($first_reading_mbo->getString("readingdate"));
			$last_date = new DateTime($last_reading_mbo->getString("readingdate"));

			// 4. Hitung selisih nilai dan selisih hari.
			$value_difference = $last_value - $first_value;
			
			// Tambahkan 1 untuk menghindari pembagian dengan nol jika hanya ada satu data dalam satu hari.
			$days_difference = $last_date->diff($first_date)->days + 1; 

			// 5. Hitung rata-rata penggunaan harian.
			$avg_daily_usage = 0;
			if ($days_difference > 0 && $value_difference > 0) {
				$avg_daily_usage = $value_difference / $days_difference;
			}

			echo " - {$assetnum}/{$meternum}: Value diff {$value_difference} over {$days_difference} days. Avg: {$avg_daily_usage}\n";
			
			$predicted_due_date = null;
			
			if ($avg_daily_usage > 0) {
                // A. Ambil aturan PM Frekuensi yang relevan untuk meter ini.
                $ruleSet = (new Set())->useTransactionManager()
                                      ->MboSetRemote("pmmeterfreq")
                                      ->where("assetnum = ? AND meternum = ? AND isactive = 'Y'", $assetnum, $meternum)
                                      ->getMboSet();

                if (!$ruleSet->isEmpty()) {
                    $rule = $ruleSet->getMbo();
                    $frequency = (float)$rule->getString("frequency");
                    $current_reading = $last_value; // Pembacaan terakhir adalah pembacaan saat ini

                    // B. Tentukan siklus berikutnya
                    $current_cycle = floor($current_reading / $frequency);
                    $next_cycle_trigger_point = ($current_cycle + 1) * $frequency;
                    
                    // C. Hitung sisa unit dan perkirakan sisa hari
                    $units_to_go = $next_cycle_trigger_point - $current_reading;
                    if ($units_to_go > 0) {
                        $days_until_due = ceil($units_to_go / $avg_daily_usage);
                        
                        // D. Hitung tanggal perkiraan
                        $predicted_date_obj = new DateTime(); // Mulai dari hari ini
                        $predicted_date_obj->modify("+{$days_until_due} days");
                        $predicted_due_date = $predicted_date_obj->format('Y-m-d H:i:s');
                        
                        echo "   -> Predicted Due Date: {$predicted_due_date} ({$days_until_due} days from now)\n";
                    }
                }
            }

			// 6. Update hasil perhitungan ke tabel master 'assetmeter'.
			(new Set())->useTransactionManager()
					   ->MboSetRemote("assetmeter")
					   ->where("assetnum = ? AND meternum = ?", $assetnum, $meternum)
					   ->setValue("avgdailyusage", $avg_daily_usage)
					   ->setValue("predictedduedate", $predicted_due_date)
					   ->setValue("daysuntildue", $days_until_due)
					   ->setValue("unitstogo", $units_to_go)
					   ->change();
		}

		echo "Average Daily Usage calculation finished successfully.\n";
	}
	//CreateWO Meter Frequency
	private static function createMeterFrequencyWorkOrder($rule_data, $trigger_point, $cycle_number, $notice_message=null) {
		echo "=========================\n";
		echo "Assembling Work Order...\n";
		echo "=========================\n";
		echo "1. Assembling Header... \n";
		$pmnum = $rule_data['pmnum'];
		$assetnum = $rule_data['assetnum'];
		$meternum = $rule_data['meternum'];
		$pmmeterfreqid = $rule_data["pmmeterfreqid"];
		$auto_reserve = ($rule_data['autoreserve'] === 'Y');

		// --- 1. Mengumpulkan Informasi Header Work Order ---
		$wonum = (new Super())->getAutoNumber("workorder", "update_next_value")->autonumber;
		$enterdate = Xbo::setEnterDate();
		$enterby = "PM:METER_FREQUENCY";
		$status = "WAITSCHED";
		$auto_desc = "Manual Reservation";
		if($auto_reserve) {
			$status = "SCHED";
			$auto_desc = "Auto Reservation";
		}
		$description = "Auto Generated PM Meter Freq. for $meternum, trigger point: $trigger_point at cycle $cycle_number, $auto_desc";
		
		
		$pmSet = (new Set())->useTransactionManager()->MboSetRemote("pm")->where("pmnum=?", $pmnum)->getMboSet();
		$pm = $pmSet->getMbo();
		echo "2. Assembling Job Plans... \n";
		$def_teamnum = $pm->getString("teamnum");
		$def_leader = $pm->getString("leader");
		$def_glaccount = $pm->getString("glaccount");
		$owner = $pm->getString("owner");
		$orgcode = $pm->getString("orgcode");
		$sitecode = $pm->getString("sitecode");
		$worktype = "PM-FREQ";
		$pmmetercycle = $cycle_number;
		// --- Ambil SEMUA Job Plan yang ditugaskan ---
		$assignedJobPlansSet = (new Set())->useTransactionManager()
			->MboSetRemote("pmmeterfreqjp")
			->where("pmnum=? and pmmeterfreqid=?", $pmnum, $pmmeterfreqid)
			->order("sequence ASC")
			->getMboSet();
		
		if ($assignedJobPlansSet->isEmpty()) {
			echo "WARNING: No Job Plans assigned to this rule. Creating an empty WO.\n";
		}

		// --- 2. Logika Override untuk Team, Leader, GL Account ---
		$final_teamnum = $def_teamnum;
		$final_leader = $def_leader;
		$final_glaccount = $def_glaccount;

		$assignedJobPlansData = $assignedJobPlansSet->fetchData(); 
		if (!empty($assignedJobPlansData)) {
			$last_jp_rule = end($assignedJobPlansData); // Ambil elemen terakhir (sequence tertinggi)
			$final_teamnum = !empty($last_jp_rule["teamnum"]) ? $last_jp_rule["teamnum"] : $def_teamnum;
			$final_leader = !empty($last_jp_rule["leader"]) ? $last_jp_rule["leader"] : $def_leader;
			$final_glaccount = !empty($last_jp_rule["glaccount"]) ? $last_jp_rule["glaccount"] : $def_glaccount;
		}
		
		
		// --- 3. Buat "header" Work Order ---
		(new Set())->useTransactionManager()->MboSetRemote("workorder")
			->setValue("wonum", $wonum)->setValue("description", $description)
			->setValue("targstartdate", $enterdate)->setValue("targcompdate", $enterdate)
			->setValue("teamnum", $final_teamnum)->setValue("owner", $owner)
			->setValue("pmnum", $pmnum)->setValue("glaccount", $final_glaccount)
			->setValue("leader", $final_leader)->setValue("statusdate", $enterdate)
			->setValue("status", $status)->setValue("assetnum", $assetnum)
			->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
			->setValue("worktype", $worktype)->setValue("meternum", $meternum)
			->setValue("pmmetercycle", $pmmetercycle)
			->setValue("progress", 0)
			->setValue("notice", $notice_message)
			->add();
		
		// --- 4. Loop SETIAP Job Plan untuk Task dan Material ---
		foreach ($assignedJobPlansData as $jp_rule) {
			$wtnum = $jp_rule["wtnum"];

			// Salin task
			
			$wtTaskSet = (new Set())->useTransactionManager()->MboSetRemote("wttask")->where("wtnum = ?", $wtnum)->getMboSet();
			while ($task_row = $wtTaskSet->fetch()) {
				$tasklinenum = Xbo::getLinenum("wotask", "tasklinenum", ["wonum"=>$wonum]);
				(new Set())->useTransactionManager()->MboSetRemote("wotask")
					->setValue("wonum", $wonum)->setValue("wtnum", $wtnum)
					->setValue("tasklinenum", $tasklinenum)
					->setValue("description", $task_row["description"])
					->setValue("status", $status)->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)->setValue("progress", 0)->add();
			}

			// Salin material
			$wtplanMaterialSet = (new Set())->useTransactionManager()->MboSetRemote("wtplanmaterial")->where("wtnum=?", $wtnum)->getMboSet();
			while ($material_row = $wtplanMaterialSet->fetch()) {
				$planlinenum = Xbo::getLinenum("woplanmaterial", "planlinenum", ["wonum"=>$wonum]);
				$linecost = floatval($material_row["quantity"]) * floatval($material_row["unitcost"]);
				(new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")
					->setValue("wonum", $wonum)->setValue("itemnum", $material_row["itemnum"])
					->setValue("planlinenum", $planlinenum)
					->setValue("quantity", $material_row["quantity"])
					->setValue("unitcost", $material_row["unitcost"])->setValue("linecost", $linecost)
					->setValue("conditiontype", $material_row["conditiontype"])
					->setValue("warehouse", $material_row["warehouse"])->setValue("racknum", $material_row["racknum"])
					->setValue("gldebitacct", $glaccount)->setValue("glcreditacct", $material_row["glcreditacct"])
					->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
					->add();
			}
		}
		
		 if ($auto_reserve) {
			echo "3. Submiting Social Reservation... \n";
			$reservedfor = $owner;
            self::executeReservationForWo($wonum, $enterby, $enterdate, $reservedfor); //check 1 - ok
        }else{
			echo "3. Manual Approval Reservation... \n";
		}
		echo "4 Calculate WO Cost... \n";
		// Hitung WO Cost 
		$costData = Xbo::calculateWOCost($wonum);
		if ($costData) {
			$updateSet = (new Set())->useTransactionManager();
			$updateSet->MboSetRemote("workorder")
					  ->where("wonum = ?", $wonum)
					  ->setValue("actualcost", $costData->actualcost)
					  ->setValue("plannedcost", $costData->plannedcost)
					   ->setValue("variancecost", $costData->variancecost)
					  ->change();
		}
		echo "5. Send Last WO and the date to PM... \n";
		// Update Last WO dan Last WO
		(new Set())->useTransactionManager()
		   ->MboSetRemote("pmmeterfreq")
		   ->where("pmmeterfreqid = ?", $pmmeterfreqid)
		   ->setValue("lastwonum", $wonum)
		   ->setValue("lastwodate", $enterdate)
		   ->change();
		
		
		
		echo "=========================\n";
		echo "Work Order Assembled...\n";
		echo "=========================\n";

		return $wonum; // Kembalikan wonum yang baru dibuat
	} 
	
	public static function processMeterFrequencyPMs() {
        echo "\nStarting Meter Frequency PM processing (Cycle-Aware Engine)...\n";

        $rulesSet = (new Set())->useTransactionManager()
                               ->MboSetRemote("pmmeterfreq")
                               ->where("isactive = 'Y'")
                               ->getMboSet();

        if ($rulesSet->isEmpty()) {
            echo "No active meter frequency rules found. Process finished.\n";
            return;
        }

        while ($rule = $rulesSet->fetch()) {
            $pmmeterfreqid = $rule['pmmeterfreqid'];
            $assetnum = $rule['assetnum'];
            $meternum = $rule['meternum'];
            $pmnum = $rule['pmnum']; // pmnum untuk query WO
            $frequency = (float)$rule['frequency'];
            $generate_wo_ahead = (float)$rule['generatewoahead'];
            
            // Periksa apakah frekuensi valid untuk mencegah pembagian dengan nol
            if ($frequency <= 0) {
                echo "WARNING: PM: $pmnum , Rule ID {$pmmeterfreqid} has an invalid frequency of {$frequency}. Skipping.\n";
                continue;
            }

            $assetMeterSet = (new Set())->useTransactionManager()
                                       ->MboSetRemote("assetmeter")
                                       ->where("assetnum = ? AND meternum = ?", $assetnum, $meternum)
                                       ->getMboSet();
            echo "\nStarting Meter Frequency PM: $pmnum, Asset: $assetnum, Meter: $meternum \n";  
            if ($assetMeterSet->isEmpty()) {
                echo " \nWARNING:  PM: $pmnum , No assetmeter record found for {$assetnum} / {$meternum}. Skipping rule.\n";
                continue;
            }
            $current_reading = (float)$assetMeterSet->getMbo()->getString("lastreading");

            // --- "Frequency Cycles" ---

            // 1. Tentukan siklus terakhir yang seharusnya WO nya sudah selesai dikerjakan berdasarkan pembacaan saat ini.
            $current_cycle  = ceil($current_reading / $frequency);
            if ($current_cycle == 0) $current_cycle = 1; // Pastikan minimal siklus 1

            // 2. Check Loop dari siklus pertama hingga siklus terakhir kalau ada yang kelewat
            // Ini untuk "mengejar" PM yang mungkin terlewat.
            for ($cycle_to_check = 1; $cycle_to_check <= $current_cycle ; $cycle_to_check++) {
                
                /// 3. Cek "memori": Apakah WO untuk siklus ini sudah pernah dibuat?
				$existingWoSet = (new Set())->useTransactionManager()
                                        ->MboSetRemote("workorder")
                                        ->where("pmnum = ? AND meternum = ? AND worktype = 'PM-FREQ' AND pmmetercycle = ?", $pmnum, $meternum, $cycle_to_check)
                                        ->getMboSet();
                
                if (!$existingWoSet->isEmpty()) {
                    echo " \nINFO:  PM: $pmnum , WO for cycle {$cycle_to_check} on {$assetnum}/{$meternum} already exists. Skipping.\n";
                    continue;
                }

                // 4. Jika debug sampai di sini, berarti WO untuk siklus ini BELUM dibuat.
                // Karena debug memeriksa siklus yang SUDAH TERLEWATI (berdasarkan floor()),
                // berarti `current_reading` PASTI sudah melewati `generation_point`.
                // Jadi, bisa langsung membuat WO.
                
                $cycle_trigger_point = $cycle_to_check * $frequency; // Untuk info di deskripsi WO
				$generation_point = $cycle_trigger_point - $generate_wo_ahead;
              
                
				if ($current_reading >= $generation_point) {
					
					$pmSet = (new Set())->useTransactionManager()->MboSetRemote("pm")->where("pmnum=?", $pmnum)->getMboSet();
					$pm = $pmSet->getMbo();
					$generationlogic = $pm->getString("generationlogic");
					$rule['generationlogic']  = $generationlogic;
					$notice_for_wo = null;
					echo " \n### Generation Logic: $generationlogic ###";
					if ( $generationlogic === 'OR') { 
						
						 $existing_time_wo = self::findSupersedingTimeWO($pmnum, $cycle_to_check);
						// echo "Existing : $existing_time_wo";
						// echo json_encode($existing_time_wo);
						 
						if ($existing_time_wo) {
							// WAKTU SUDAH MENANG! Planner sudah membuat WO manual.
							// Jangan buat WO meter. Cukup tinggalkan jejak & hentikan proses untuk siklus ini.
							
							echo "\nSkipping Meter-Based WO: Superseded by manual Time-Based WO " . $existing_time_wo['wonum'];

							// Tandai siklus ini sebagai "selesai" di 'memori' agar tidak dicek lagi.
							(new Set())->useTransactionManager()
							   ->MboSetRemote("pmmeterfreq")
							   ->where("pmmeterfreqid = ?", $rule['pmmeterfreqid'])
							   ->setValue("lastwonum", $existing_time_wo['wonum'])
							   ->setValue("lastwodate", $existing_time_wo['statusdate']) // Gunakan tanggal WO dibuat
							   ->change();

							// SANGAT PENTING: Lanjut ke aturan berikutnya, jangan proses yang ini lebih jauh.
							continue; 
						}
								 
						 
					}	 
					
					$notice_for_wo = null;
					if ($rule['generationlogic'] === 'OR') {
						
						$superseded_forecast = self::findSupersededTimeForecast($pmnum, $current_reading);
						
						if ($superseded_forecast) {
							// WAKTU Surpressed.
							$notice_for_wo = "This WO supersedes the upcoming Time-Based WO forecast for " . 
											 (new DateTime($superseded_forecast['targstartdate']))->format('Y-m-d');
							
							// Update remarks di pmwoline (forecast) yang ditekan
							$notice_message = "[SUPPRESSED] Superseded by this Meter-Based WO.";
							(new Set())->useTransactionManager()
									   ->MboSetRemote("pmwoline")
									   ->where("pmwolineid = ?", $superseded_forecast['pmwolineid'])
									   ->setValue("raceremarks", $notice_message)
									   ->change();
						}
					}
			
					echo "\n\nTRIGGERED! Generating overdue Work Order for {$assetnum} for cycle {$cycle_to_check} (trigger point @ {$cycle_trigger_point}) , PM: $pmnum...\n";
					$new_wonum = self::createMeterFrequencyWorkOrder($rule, $cycle_trigger_point, $cycle_to_check, $notice_message); //check 2 - ok
					
					 if ($new_wonum) {
						echo "SUCCESS: WO {$new_wonum} created for cycle {$cycle_to_check}.\n";
						// Setelah berhasil membuat WO yang terlewat, Proses Loop Selesai.
						break;
					}
				}
            }
        }
        echo "\nMeter Frequency PM processing finished.\n";
    }
	
	private static function findSupersedingTimeWO($pmnum, $current_meter_cycle) {
		echo "\n--- Checking for Superseding Time WO for Meter Cycle: {$current_meter_cycle} ---\n";

		// Langsung cari WO Waktu dengan NOMOR SIKLUS YANG SAMA PERSIS.
		$woSet = (new Set())->useTransactionManager()
						   ->MboSetRemote("workorder")
						   ->where("pmnum = ? AND worktype = 'PM' AND pmtimecycle = ? AND status NOT IN ('complete', 'close', 'cancel')",
								  $pmnum, $current_meter_cycle)
						   ->limit(1)
						   ->getMboSet();
		
		if (!$woSet->isEmpty()) {
			echo "\nRESULT: Found a superseding Time WO with the same cycle number.\n";
			return $woSet->fetch();
		}
		
		echo "\nRESULT: No superseding Time WO found for this cycle.\n";
		return null;
	}
	 
	/**
     * Helper untuk mengkonversi berbagai unit interval menjadi bulan.
     */
	private static function convertIntervalToMonths($value, $unit) {
		switch(strtolower($unit)) {
			case 'day': return $value / 30;
			case 'week': return $value / 4.3;
			case 'month': return $value;
			case 'year': return $value * 12;
			default: return 0;
		}
	}

	/**
     * Helper untuk mengkonversi berbagai unit interval menjadi hari.
     */
    private static function convertIntervalToDays($value, $unit) {
        switch (strtolower($unit)) {
            case 'day':
            case 'days':
                return $value;
            case 'week':
            case 'weeks':
                return $value * 7;
            case 'month':
            case 'months':
                return $value * 30.44; // Perkiraan sederhana, cukup untuk logika ini
            case 'year':
            case 'years':
                return $value * 365.25;
            default:
				error_log("Unknown interval unit '{$unit}' passed to convertIntervalToDays. Defaulting to 30 days.");
                return 30; // Default yang aman
        }
    }
	
	 
	 /**
     * Spectator untuk engine Meter. Mencari forecast PM berbasis Waktu yang akan
     * digantikan (superseded) oleh WO berbasis Meter yang akan dibuat.
  
     */
    private static function findSupersededTimeForecast($pmnum, $current_reading) {
        
        // 1. Ambil detail PM Master untuk mendapatkan interval waktu.
        $pmSet = (new Set())->useTransactionManager()->MboSetRemote("pm")->where("pmnum = ?", $pmnum)->getMboSet();
        if ($pmSet->isEmpty()) {
            return null; 
        }
        $pm = $pmSet->getMbo();
        $interval_value = $pm->getInt("intervalvalue");
        $interval_unit = $pm->getString("intervalunit");

        // 2.  Logika untuk mencari forecast Waktu yang jatuh tempo dalam rentang:
        // (Hari Ini + Setengah dari Interval Waktu).
        // untuk mencegah salah identifikasi.
        
        // Hitung setengah interval. Contoh: jika interval 1 bulan (~30 hari), setengahnya 15 hari.
        $half_interval_days = self::convertIntervalToDays($interval_value, $interval_unit) / 2;
        
        $start_date_check = new DateTime(); // Mulai dari hari ini
        $end_date_check = (new DateTime())->modify("+{$half_interval_days} days");

        $start_date_str = $start_date_check->format('Y-m-d H:i:s');
        $end_date_str = $end_date_check->format('Y-m-d H:i:s');

        // 3. Lakukan pencarian di tabel pmwoline.
        $forecastSet = (new Set())->useTransactionManager()
                                  ->MboSetRemote("pmwoline")
                                  // Cari forecast untuk PM yang sama, yang belum ditekan (raceremarks kosong)
                                  ->where("pmnum = ? AND (raceremarks IS NULL OR raceremarks = '') AND targstartdate BETWEEN ? AND ?",
                                         $pmnum, $start_date_str, $end_date_str)
                                  ->order("targstartdate ASC") // Ambil yang paling dekat
                                  ->limit(1)
                                  ->getMboSet();
        
        if ($forecastSet->isEmpty()) {
            return null; // Tidak ada forecast Waktu yang akan jatuh tempo dalam waktu dekat.
        }

        // 4. Jika ditemukan, kembalikan data forecast tersebut.
        return $forecastSet->fetch();
    }
	
	
	public static function processPMLeadDayAlerts() {
		echo "[MAXES] Starting PM Alert Check...\n";
        echo "[MAXES] Current Time is " . date('Y-m-d H:i:s') . "\n";
		
        // Mencari semua PM WO yang:
        // 1. Belum dikirimi notifikasi (isalertsent = 'N').
        // 2. Tanggal targetnya (targstartdate) akan datang.
        // 3. Jarak antara hari ini dan tanggal target <= dari alertleaddays yang diset di PM (atau default 7 hari).
		
		 $sql = "
            SELECT
                pmwo.pmwolineid,
                pmwo.pmnum,
                pmwo.wonum,
                pmwo.targstartdate,
                pmwo.owner,
                pmwo.leader
            FROM
                pmwoline pmwo
            JOIN
                pm ON pm.pmnum = pmwo.pmnum
            WHERE
                pmwo.isalertsent = 'N'
                AND pmwo.targstartdate >= CURDATE()
                AND DATEDIFF(pmwo.targstartdate, CURDATE()) <= COALESCE(pm.alertleaddays, " . DEFAULT_PM_ALERT_LEAD_DAYS . ")
        ";
		
		 try {
            // Gunakan fetchAll untuk mengambil semua data yang cocok sekaligus
            $alertsToSend = Set::fetchAll($sql);

            if (empty($alertsToSend)) {
                echo "[MAXES] No alerts to send today. Exiting.\n";
                return;
            }

            echo count($alertsToSend) . " PM WO(s) found for alerting.\n";

            // Loop melalui setiap WO yang perlu dinotifikasi
            foreach ($alertsToSend as $alert) {
                $pmwolineid = $alert['pmwolineid'];
                $wonum = $alert['wonum'];
                $pmnum = $alert['pmnum'];
                $owner = Xbo::getUsername($alert['owner']);
                $leader = Xbo::getUsername($alert['leader']);
                $dueDate = date("d-M-Y", strtotime($alert['targstartdate']));

                $message = "Reminder: PM Work Order {$wonum} for PM {$pmnum} is due on {$dueDate}.";
                echo "  - Preparing alert for WONUM: {$wonum}...\n";
				
				$workorderid = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->MboRemote("workorderid")->getMboSet()->getMbo()->getString("workorderid");
                // Kirim notifikasi ke Owner (jika ada)
                if (!empty($owner)) {
                    Super::sendNotification([
                        'username'    => $owner,
                        'message'     => $message,
                        'objectname'  => 'workorder',
                        'objectnum'   => $wonum,
						'objectid' => $workorderid,
                        'processname' => 'PM_ALERT'
                    ]);
                }

                // Kirim notifikasi ke Leader (jika ada dan bukan orang yang sama dengan Owner)
                if (!empty($leader) && $leader !== $owner) {
                    Super::sendNotification([
                        'username'    => $leader,
                        'message'     => $message,
                        'objectname'  => 'workorder',
                        'objectnum'   => $wonum,
						'objectid' => $workorderid,
                        'processname' => 'PM_ALERT'
                    ]);
                }

                // SANGAT PENTING: Update flag agar tidak dikirim lagi
                (new Set())->useTransactionManager()->MboSetRemote("pmwoline")
                           ->where("pmwolineid = ?", $pmwolineid)
                           ->setValue("isalertsent", "Y")
                           ->change();
                
                echo "[MAXES] Alert sent and flag updated for PMWOLINEID: {$pmwolineid}.\n";
            }

            echo "[MAXES] PM Lead Day Alert process finished successfully.\n";

        } catch (Exception $e) {
            echo "ERROR during PM Lead Day Alert process: " . $e->getMessage() . "\n";
            error_log("Cron Task (processPMLeadDayAlerts) Error: " . $e->getMessage());
			}
		}
}
	
