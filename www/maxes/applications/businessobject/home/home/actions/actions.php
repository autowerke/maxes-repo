<?php
require_once ("../../../eat.php");


class ReliabilityBotHandler {
    
	public static function generateFriendlyData_meantime($assetnum, $year, $meantime_data){
		
		// --- PHASE 1: Fetch Work Orders & Failure Codes ---
		$startDate = "$year-01-01";
		$endDate = "$year-12-31 23:59:59";

		$failureSet = (new Set())->useTransactionManager()
			->MboSetRemote("workorder")
			->where("assetnum = ? AND reportdate >= ? AND reportdate <= ? AND failurecode IS NOT NULL", 
					$assetnum, $startDate, $endDate)
			->MboRemote("wonum, reportdate, failurecode")
			->getMboSet();

		$monthlyFailures = []; 
		$allWonums = [];
		$failureTimeline = []; 

		while ($wo = $failureSet->fetch()) {
			$woDate = new DateTime($wo['reportdate']);
			$monthIndex = (int)$woDate->format('n') - 1; 
			$code = trim($wo['failurecode']);
			$wonum = $wo['wonum'];

			if (!empty($code)) {
				// Struktur bulanan
				if (!isset($monthlyFailures[$monthIndex][$code])) {
					$monthlyFailures[$monthIndex][$code] = ['count' => 0, 'wonums' => []];
				}
				$monthlyFailures[$monthIndex][$code]['count']++;
				$monthlyFailures[$monthIndex][$code]['wonums'][] = $wonum;
				
				$failureTimeline[$code][] = $monthIndex; 
				
				$allWonums[] = $wonum;
			}
		}
	
		// --- PHASE 2: Fetch Material Usage (Batch Query) ---
		$materialsByWo = []; 
		$allItemNums = [];

		if (!empty($allWonums)) {
			$allWonums = array_unique($allWonums);
			
			$matSet = (new Set())->useTransactionManager()
				->MboSetRemote("matusetrans")
				->whereIn("wonum", $allWonums)
				->MboRemote("wonum, itemnum, quantity, unitcost, linecost")
				->getMboSet();

			while ($mat = $matSet->fetch()) {
				$woKey = $mat['wonum'];
				$itemKey = $mat['itemnum'];
				
				$materialsByWo[$woKey][] = [
					'itemnum' => $itemKey,
					'quantity' => (float)$mat['quantity'],
					'linecost' => (float)$mat['linecost']
				];
				$allItemNums[] = $itemKey;
			}
		}
	
		// --- PHASE 3: Fetch Inventory Data (Batch Query) ---
		$inventoryMap = [];

		if (!empty($allItemNums)) {
			$allItemNums = array_unique($allItemNums);

			$invSet = (new Set())->useTransactionManager()
				->MboSetRemote("invfifolifocost")
				->whereIn("itemnum", $allItemNums)
				->MboRemote("itemnum, SUM(quantity) as total_stock")
				->group("itemnum")
				->getMboSet();

			while ($inv = $invSet->fetch()) {
				$inventoryMap[$inv['itemnum']] = (float)$inv['total_stock'];
			}
		}

		// --- PHASE 4: Data Assembly & Enrichment ---
		
		$cats = $meantime_data["categories"];
	
		$months = [];
		$globalSparepartStats = []; // Agregasi global untuk Python

		foreach ($cats as $i => $monthName) {
			$failuresInMonth = $monthlyFailures[$i] ?? [];
			$formattedFailures = [];

			foreach ($failuresInMonth as $fCode => $fData) {
				$sparePartsSummary = [];
				$totalCostForFailure = 0;

				foreach ($fData['wonums'] as $wonum) {
					if (isset($materialsByWo[$wonum])) {
						foreach ($materialsByWo[$wonum] as $mat) {
							$item = $mat['itemnum'];
							$qty = $mat['quantity'];
							$cost = $mat['linecost'];
							
							// 1. Data per Bulan
							if (!isset($sparePartsSummary[$item])) {
								$currentStock = $inventoryMap[$item] ?? 0;
								
								// Logic Status Stok (Biar Python gak usah mikir logic ini)
								$stockStatus = "SAFE";
								if ($currentStock <= 0) $stockStatus = "CRITICAL";
								elseif ($currentStock < 3) $stockStatus = "LOW";

								$sparePartsSummary[$item] = [
									'itemnum' => $item,
									'used_qty' => 0,
									'total_cost' => 0,
									'current_stock' => $currentStock,
									'stock_status' => $stockStatus 
								];
							}
							$sparePartsSummary[$item]['used_qty'] += $qty;
							$sparePartsSummary[$item]['total_cost'] += $cost;
							
							$totalCostForFailure += $cost;

							// 2. Data Global (untuk Pareto Analysis di Python)
							if (!isset($globalSparepartStats[$fCode])) {
								$globalSparepartStats[$fCode] = 0;
							}
							$globalSparepartStats[$fCode] += $cost;
						}
					}
				}

				$formattedFailures[$fCode] = [
					"frequency" => $fData['count'],
					"total_repair_cost" => $totalCostForFailure,
					"spareparts" => array_values($sparePartsSummary)
				];
			}

			$months[] = [
				$monthName => [
					"mtbf" => isset($meantime_data["mtbf"][$i]) ? (float)$meantime_data["mtbf"][$i] : 0,
					"mttr" => isset($meantime_data["mttr"][$i]) ? (float)$meantime_data["mttr"][$i] : 0,
					"failures" => empty($formattedFailures) ? null : $formattedFailures
				]
			];
		}
		
		// Siapkan struktur timeline agar Python mudah mendeteksi pola (e.g., [0, 3, 6] -> "Setiap 3 bulan")
		foreach($failureTimeline as $code => $indices) {
			sort($failureTimeline[$code]);
		}
		
		$friendlyPayload = [
			"asset_context" => [
				"assetnum" => $assetnum,
				"year" => (int)$year,
			],
			"global_analysis" => [
				// Python akan membuat "Summary" tanpa harus loop ulang months
				"failure_timeline_indices" => $failureTimeline, // [ "ELEC": [0, 3, 6], "MECH": [5, 8, 11] ]
				"failure_cost_pareto" => $globalSparepartStats, // [ "MECH": 25000000, "ELEC": 5000000 ]
				"summary_metrics" => $meantime_data["summary"] ?? []
			],
			"monthly_data" => $months
		];

		return $friendlyPayload;
	}

    /**
     * Main chat endpoint
     * Handles AI responses and formats them for frontend
     */
    public static function reliability_bot_chat(){
        $payload = Super::getParam("reliability_bot_chat");
        
        // Build request to Python ML service
        $jsonPayload = [
            "assetnum" => $payload["assetnum"],
            "year" => $payload["year"],
            "chat_history" => $payload["chat_history"], 
            "main_data" => self::generateFriendlyData_meantime(
                $payload["assetnum"], 
                $payload["year"],  
                $payload["meantime_data"]
            )
        ];
        
        $endpoint = "/chat/reliability_engineer"; 
        $result = Super::sendToML($endpoint, $jsonPayload, true);
        
        // Decode Python response
        $data = json_decode($result, true);
        
        // ERROR HANDLING
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            error_log("Raw response: " . substr($result, 0, 500));
            Super::sendJsonResponse("error", "Invalid response from AI service", ["error_details" => json_last_error_msg()] );
            return;
        }
        
        // Check for Python-side errors
        if (isset($data['error'])) {
            error_log("AI error: " . $data['error']);
			$response_data = [
				"error_type" => $data['error_type'] ?? 'UNKNOWN',
                "generation_time" => $data['generation_time'] ?? 0
			];
            Super::sendJsonResponse("error", "AI processing failed: " . $data['error'], $response_data);
        }
        
        // Extract response data
        $response_type = $data['response_type'] ?? 'TEXT';
        $narrative_analysis = $data['narrative_analysis'] ?? '';
        $generation_time = $data['generation_time'] ?? 0;
        
        // HANDLE BASED ON TYPE
        if ($response_type === 'ACTION') {
            // AI wants to execute a function
            self::handleActionResponse($narrative_analysis, $generation_time);
            
        } else {
            // TEXT - normal conversation
            self::handleTextResponse($narrative_analysis, $generation_time);
        }
    }
    
    
    private static function handleTextResponse($text, $generation_time) {
        // Additional cleaning (backup, Python should already clean)
        $cleaned_text = trim($text);
        $cleaned_text = preg_replace('/^(TEXT|Answer|Response|Assistant):\s*/i', '', $cleaned_text);
        $cleaned_text = preg_replace('/\s*(TEXT|Answer|Response):\s*$/i', '', $cleaned_text);
        
        // Final trim
        $cleaned_text = trim($cleaned_text);
        
        // Log for debugging
        error_log("AI TEXT response: " . substr($cleaned_text, 0, 100) . "... (time: {$generation_time}s)");
        
        // Send to frontend

		$response_data = [
			"type" => "text",
			"generation_time" => $generation_time
		];
        Super::sendJsonResponse("success", $cleaned_text, $response_data);
    }
    
    /**
     * Handle ACTION response (function call from AI)
     */
    private static function handleActionResponse($action_data, $generation_time) {
        // Validate action data
        if (!is_array($action_data)) {
            error_log("Invalid action data: not an array");
            Super::sendJsonResponse("error", "Invalid action format from AI" );
            return;
        }
        
        $function = $action_data['function'] ?? null;
        
        if (!$function) {
            error_log("Action missing 'function' key: " . json_encode($action_data));
            Super::sendJsonResponse("error", "Invalid action: missing function name" );
            return;
        }
        
        // Log action
        error_log("AI ACTION: {$function} with params: " . json_encode($action_data));
        
        // Format as human-readable text
        $display_text = self::formatActionAsText($action_data);
        
        // Send structured response to frontend
		$response_data = [
            "type" => "action",
            "function" => $function,
            "params" => $action_data,
            "generation_time" => $generation_time,
            "confirmation_message" => self::getConfirmationMessage($function)
        ];
        Super::sendJsonResponse("action", $display_text, $response_data);
    }
    
    /**
     * Convert AI action into human-readable text
     */
    private static function formatActionAsText($action_data) {
        $function = $action_data['function'] ?? 'unknown';
        
        switch ($function) {
            case 'create_pm_strategy':
                $freq = $action_data['frequency'] ?? '?';
                $desc = $action_data['description'] ?? 'preventive maintenance plan';
				return "💡 PM is Created. Take a look on PM Number: PM-BOIL-PLTU-2000-01";
                //return "AI Recommendation: Create a Preventive Maintenance strategy with {$freq}-day frequency.\n\nStrategy: {$desc}\n\nWould you like me to create this PM plan?";
            
            case 'create_investigation_wo':
                $code = $action_data['failurecode'] ?? '?';
                $priority = $action_data['priority'] ?? 'NORMAL';
                $asset = $action_data['assetnum'] ?? '?';
				return "🔍 Work Order WO-202409-004 is created for failure code '{$code}' on asset {$asset}.\n\nPriority: {$priority}. ";
                
            case 'check_sparepart_detail':
                $itemnum = $action_data['itemnum'] ?? '?';
                return "📦 AI Recommendation: Check spare part details for item number '{$itemnum}'.\n\nThis part may need attention based on usage patterns.\n\nWould you like to view the details?";
            
            case 'show_failure_history':
                $code = $action_data['failurecode'] ?? '?';
                $asset = $action_data['assetnum'] ?? '?';
                return "📊 AI Recommendation: Review failure history for code '{$code}' on asset {$asset}.\n\nThis can help identify recurring patterns.\n\nWould you like to see the history?";
            
            default:
                return "💡 AI suggests the following action:\n\nFunction: {$function}\nParameters: " . json_encode($action_data);
        }
    }
    
    /**
     * Get confirmation message for frontend dialog
     */
    private static function getConfirmationMessage($function) {
        $messages = [
            'create_pm_strategy' => "Do you want to create this PM strategy?",
            'create_investigation_wo' => "Do you want to create this investigation work order?",
            'check_sparepart_detail' => "Do you want to check this spare part?",
            'show_failure_history' => "Do you want to view the failure history?"
        ];
        
        return $messages[$function] ?? "Do you want to execute this action?";
    }
    
    /**
     * Execute AI-suggested action (called when user confirms)
     */
    public static function execute_ai_action() {
        $params = Super::getParam("action_params");
        $function = $params['function'] ?? null;
        
        if (!$function) {
            Super::sendJsonResponse("error", "Missing function name");
            return;
        }
        
        // Route to appropriate handler
        switch ($function) {
            case 'create_pm_strategy':
                return self::executePMStrategy($params);
            
            case 'create_investigation_wo':
                return self::executeInvestigationWO($params);
            
            case 'check_sparepart_detail':
                return self::executeSparepartCheck($params);
            
            case 'show_failure_history':
                return self::executeFailureHistory($params);
            
            default:
                Super::sendJsonResponse("error", "Unknown function: {$function}");
        }
    }
    
    // ========================================
    // ACTION EXECUTORS
    // ========================================
    
    private static function executePMStrategy($params) {
        $assetnum = $params['assetnum'] ?? null;
        $frequency = $params['frequency'] ?? 30;
        $description = $params['description'] ?? '';
        
        // Call your existing PM creation logic
        // Example:
        // $pm_id = PMService::createStrategy($assetnum, $frequency, $description);
        $response_data = [
           
            "pm_id" => "PM-" . time(), // Replace with actual ID
            "details" => [
                "asset" => $assetnum,
                "frequency" => $frequency . " days",
                "description" => $description
            ]
			];

        Super::sendJsonResponse("success", "PM Strategy created successfully", $response_data);
    }
    
    private static function executeInvestigationWO($params) {
        $assetnum = $params['assetnum'] ?? null;
        $failurecode = $params['failurecode'] ?? null;
        $priority = $params['priority'] ?? 'NORMAL';
        
        // Call your existing WO creation logic
        // Example:
        // $wo_id = WorkOrderService::createInvestigation($assetnum, $failurecode, $priority);
        $response_data = [
            "message" => "Investigation work order created",
            "wo_id" => "WO-" . time(), // Replace with actual ID
            "details" => [
                "asset" => $assetnum,
                "failure_code" => $failurecode,
                "priority" => $priority
            ]
		];
        Super::sendJsonResponse("success", "Investigation work order created", $response_data);
    }
    
    private static function executeSparepartCheck($params) {
        $itemnum = $params['itemnum'] ?? null;
        
        // Fetch spare part details
        // Example:
        // $part_details = InventoryService::getPartDetails($itemnum);
        $response_data = [
            "part_details" => [
                "itemnum" => $itemnum,
                "description" => "Example Part Description",
                "stock" => 10,
                "status" => "SAFE"
                // Replace with actual data
            ]
		];
        Super::sendJsonResponse("success", "Spare part details retrieved", $response_data);
    }
    
    private static function executeFailureHistory($params) {
        $assetnum = $params['assetnum'] ?? null;
        $failurecode = $params['failurecode'] ?? null;
        
        // Fetch failure history
        // Example:
        // $history = FailureService::getHistory($assetnum, $failurecode);
        $response_data = [
            "message" => "Failure history retrieved",
            "history" => [
                // Replace with actual history data
                ["date" => "2024-01-15", "description" => "..."],
                ["date" => "2024-03-20", "description" => "..."]
            ]
		];
        Super::sendJsonResponse("success", "Failure history retrieved",$response_data );
    }
}

Class Charts{
	
	public static function mtbfmttrAI(){
		try {
			$payload = Super::getParam("mtbfmttrAI");
			if (empty($payload["assetnum"])) {
				throw new InvalidArgumentException("Asset Number is required.");
			}

			$assetnum = $payload["assetnum"];
			$year = $payload["year"] ?? date('Y');

			if (empty($payload["meantime_data"]["categories"])) {
				throw new InvalidArgumentException("Invalid MTBF/MTTR data structure.");
			}

			// --- PHASE 1: Fetch Work Orders & Failure Codes ---
			$startDate = "$year-01-01";
			$endDate = "$year-12-31 23:59:59";

			$failureSet = (new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				->where("assetnum = ? AND reportdate >= ? AND reportdate <= ? AND failurecode IS NOT NULL", 
						$assetnum, $startDate, $endDate)
				->MboRemote("wonum, reportdate, failurecode")
				->getMboSet();

			$monthlyFailures = []; 
			$allWonums = [];
			$failureTimeline = []; // Penting untuk Python deteksi pola (Cyclical/Random)

			while ($wo = $failureSet->fetch()) {
				$woDate = new DateTime($wo['reportdate']);
				$monthIndex = (int)$woDate->format('n') - 1; 
				$code = trim($wo['failurecode']);
				$wonum = $wo['wonum'];

				if (!empty($code)) {
					// Struktur bulanan
					if (!isset($monthlyFailures[$monthIndex][$code])) {
						$monthlyFailures[$monthIndex][$code] = ['count' => 0, 'wonums' => []];
					}
					$monthlyFailures[$monthIndex][$code]['count']++;
					$monthlyFailures[$monthIndex][$code]['wonums'][] = $wonum;
					
					// Struktur Timeline Global (untuk Python deteksi pola)
					$failureTimeline[$code][] = $monthIndex; 
					
					$allWonums[] = $wonum;
				}
			}

			// --- PHASE 2: Fetch Material Usage (Batch Query) ---
			$materialsByWo = []; 
			$allItemNums = [];

			if (!empty($allWonums)) {
				$allWonums = array_unique($allWonums);
				
				$matSet = (new Set())->useTransactionManager()
					->MboSetRemote("matusetrans")
					->whereIn("wonum", $allWonums)
					->MboRemote("wonum, itemnum, quantity, unitcost, linecost")
					->getMboSet();

				while ($mat = $matSet->fetch()) {
					$woKey = $mat['wonum'];
					$itemKey = $mat['itemnum'];
					
					$materialsByWo[$woKey][] = [
						'itemnum' => $itemKey,
						'quantity' => (float)$mat['quantity'],
						'linecost' => (float)$mat['linecost']
					];
					$allItemNums[] = $itemKey;
				}
			}

			// --- PHASE 3: Fetch Inventory Data (Batch Query) ---
			$inventoryMap = [];

			if (!empty($allItemNums)) {
				$allItemNums = array_unique($allItemNums);

				$invSet = (new Set())->useTransactionManager()
					->MboSetRemote("invfifolifocost")
					->whereIn("itemnum", $allItemNums)
					->MboRemote("itemnum, SUM(quantity) as total_stock")
					->group("itemnum")
					->getMboSet();

				while ($inv = $invSet->fetch()) {
					$inventoryMap[$inv['itemnum']] = (float)$inv['total_stock'];
				}
			}

			// --- PHASE 4: Data Assembly & Enrichment ---
			$cats = $payload["meantime_data"]["categories"];
			$months = [];
			$globalSparepartStats = []; // Agregasi global untuk Python

			foreach ($cats as $i => $monthName) {
				$failuresInMonth = $monthlyFailures[$i] ?? [];
				$formattedFailures = [];

				foreach ($failuresInMonth as $fCode => $fData) {
					$sparePartsSummary = [];
					$totalCostForFailure = 0;

					foreach ($fData['wonums'] as $wonum) {
						if (isset($materialsByWo[$wonum])) {
							foreach ($materialsByWo[$wonum] as $mat) {
								$item = $mat['itemnum'];
								$qty = $mat['quantity'];
								$cost = $mat['linecost'];
								
								// 1. Data per Bulan
								if (!isset($sparePartsSummary[$item])) {
									$currentStock = $inventoryMap[$item] ?? 0;
									
									// Logic Status Stok (Biar Python gak usah mikir logic ini)
									$stockStatus = "SAFE";
									if ($currentStock <= 0) $stockStatus = "CRITICAL";
									elseif ($currentStock < 3) $stockStatus = "LOW";

									$sparePartsSummary[$item] = [
										'itemnum' => $item,
										'used_qty' => 0,
										'total_cost' => 0,
										'current_stock' => $currentStock,
										'stock_status' => $stockStatus 
									];
								}
								$sparePartsSummary[$item]['used_qty'] += $qty;
								$sparePartsSummary[$item]['total_cost'] += $cost;
								
								$totalCostForFailure += $cost;

								// 2. Data Global (untuk Pareto Analysis di Python)
								if (!isset($globalSparepartStats[$fCode])) {
									$globalSparepartStats[$fCode] = 0;
								}
								$globalSparepartStats[$fCode] += $cost;
							}
						}
					}

					$formattedFailures[$fCode] = [
						"frequency" => $fData['count'],
						"total_repair_cost" => $totalCostForFailure,
						"spareparts" => array_values($sparePartsSummary)
					];
				}

				$months[] = [
					$monthName => [
						"mtbf" => isset($payload["meantime_data"]["mtbf"][$i]) ? (float)$payload["meantime_data"]["mtbf"][$i] : 0,
						"mttr" => isset($payload["meantime_data"]["mttr"][$i]) ? (float)$payload["meantime_data"]["mttr"][$i] : 0,
						"failures" => empty($formattedFailures) ? null : $formattedFailures
					]
				];
			}

			// Siapkan struktur timeline agar Python mudah mendeteksi pola (e.g., [0, 3, 6] -> "Setiap 3 bulan")
			foreach($failureTimeline as $code => $indices) {
				sort($failureTimeline[$code]);
			}

			$friendlyPayload = [
				"asset_context" => [
					"assetnum" => $assetnum,
					"year" => (int)$year,
				],
				"global_analysis" => [
					// Python akan membuat "Summary" tanpa harus loop ulang months
					"failure_timeline_indices" => $failureTimeline, // [ "ELEC": [0, 3, 6], "MECH": [5, 8, 11] ]
					"failure_cost_pareto" => $globalSparepartStats, // [ "MECH": 25000000, "ELEC": 5000000 ]
					"summary_metrics" => $payload["meantime_data"]["summary"] ?? []
				],
				"monthly_data" => $months
			];
			
			$jsonPayload = json_encode($friendlyPayload);
			
			$endpoint = "/generate/pm-recommendation"; 
			$result = Super::sendToML($endpoint, $jsonPayload, true);
			$data = json_decode($result, true);

			$assetnum = $data['asset'];
			$assetDescription = (new Set())->MboSetRemote("assets")->where("assetnum=?", $assetnum)->getMboValue("description");
			$header = "Asset: $assetnum - $assetDescription <hr>";
			$narrative_analysis = $data['narrative_analysis'];
			Super::sendJsonResponse("success", $header.$narrative_analysis);


		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function getMtbfMttrAnalysis() {
		try {
			$payload = Super::getParam("getMtbfMttr");
			$assetnum = $payload["assetnum"] ?? '';
			$year = $payload["year"] ?? date('Y');

			// Validation
			if (empty($assetnum)) {
				throw new InvalidArgumentException("Asset Number is required.");
			}

			if (!is_numeric($year) || $year < 1900 || $year > 2100) {
				throw new InvalidArgumentException("Invalid year format.");
			}

			// Query dengan filter status yang lebih ketat
			$sql = "
				SELECT 
					MONTH(reportdate) as month_num,
					COUNT(wonum) as failure_count,
					SUM(
						CASE 
							WHEN actfinishdate > reportdate 
							THEN TIMESTAMPDIFF(MINUTE, reportdate, actfinishdate) / 60 
							ELSE 0 
						END
					) as total_downtime_hours
				FROM workorder 
				WHERE assetnum = ? 
				AND YEAR(reportdate) = ?
				AND reportdate IS NOT NULL
				AND reportdate <= NOW()
				AND status NOT IN ('cancel')
				AND actfinishdate IS NOT NULL 
				AND actfinishdate >= reportdate 
				GROUP BY MONTH(reportdate)
				ORDER BY month_num ASC
			";

			$rawData = Set::fetchAll($sql, [$assetnum, $year]);

			// Map data by month
			$monthlyData = [];
			foreach ($rawData as $row) {
				$monthlyData[(int)$row['month_num']] = [
					'failure_count' => (int)$row['failure_count'],
					'total_downtime_hours' => max(0, (float)$row['total_downtime_hours'])
				];
			}

			// Prepare result arrays
			$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
			$mtbfSeries = [];
			$mttrSeries = [];
			$totalUptime = 0; // FIX: Track actual uptime

			// Calculate MTBF & MTTR for each month
			for ($i = 1; $i <= 12; $i++) {
				$daysInMonth = (int)date('t', mktime(0, 0, 0, $i, 1, $year));
				$totalHoursInMonth = $daysInMonth * 24;

				if (isset($monthlyData[$i])) {
					$downtime = $monthlyData[$i]['total_downtime_hours'];
					$failureCount = $monthlyData[$i]['failure_count'];

					// Ensure downtime doesn't exceed available hours
					if ($downtime > $totalHoursInMonth) {
						error_log("WARNING: Downtime ($downtime hrs) exceeds month capacity for asset $assetnum in $i/$year");
						$downtime = $totalHoursInMonth;
					}

					if ($failureCount > 0) {
						// MTTR: Mean Time To Repair
						$mttr = $downtime / $failureCount;

						// MTBF: Mean Time Between Failures
						$uptime = max(0, $totalHoursInMonth - $downtime);
						$mtbf = $uptime / $failureCount;

						$mttrSeries[] = round($mttr, 2);
						$mtbfSeries[] = round($mtbf, 2);
						
						$totalUptime += $uptime; // FIX: Accumulate uptime
					} else {
						$mttrSeries[] = 0;
						$mtbfSeries[] = null; // FIX: null instead of total hours
						$totalUptime += $totalHoursInMonth;
					}
				} else {
					// No failures in this month
					$mttrSeries[] = 0;
					$mtbfSeries[] = null; // FIX: null instead of total hours
					$totalUptime += $totalHoursInMonth;
				}
			}

			// Additional metrics
			$totalFailures = array_sum(array_column($monthlyData, 'failure_count'));
			$totalDowntime = array_sum(array_column($monthlyData, 'total_downtime_hours'));
			$avgMTTR = $totalFailures > 0 ? round($totalDowntime / $totalFailures, 2) : 0;
			$avgMTBF = $totalFailures > 0 ? round($totalUptime / $totalFailures, 2) : 0; // FIX: Use totalUptime

			Super::sendJsonResponse("success", "Data loaded successfully", [
				"categories" => $months,
				"mtbf" => $mtbfSeries,
				"mttr" => $mttrSeries,
				"summary" => [
					"total_failures" => $totalFailures,
					"total_downtime_hours" => round($totalDowntime, 2),
					"avg_mttr" => $avgMTTR,
					"avg_mtbf" => $avgMTBF,
					"year" => $year,
					"asset" => $assetnum
				]
			]);

		} catch (InvalidArgumentException $e) {
			Super::sendJsonResponse("error", $e->getMessage(), null);
		} catch (Exception $e) {
			error_log("MTBF/MTTR Analysis Error: " . $e->getMessage());
			Super::sendJsonResponse("error", "An error occurred while processing the data.", null);
		}
	}
	

	public static function unplannedDowntimeAI()
	{
		try {
			$payload = Super::getParam("unplannedDowntimeAI");
			
			if (empty($payload["assetnum"])) {
				throw new InvalidArgumentException("Asset Number is required.");
			}

			$assetnum = $payload["assetnum"];
			$year = $payload["year"] ?? date('Y');

			// Naikkan limit group_concat agar JSON tidak terpotong
			$tm = TransactionManager::getInstance();
			$conn = $tm->getConnection(); // Atau DB::getMXConnection()
			$conn->query("SET SESSION group_concat_max_len = 1000000");

			$sql = "
				WITH 
				Scope_WO AS (
					SELECT * 
					FROM workorder 
					WHERE assetnum = ? 
					AND (YEAR(actfinishdate) = ? OR (actfinishdate IS NULL AND YEAR(targcompdate) = ?))
				),

				Analisa_Reliability AS (
					SELECT 
						af.category,
						af.failurecode,
						af.description as failure_desc,
						COUNT(wo.wonum) as failure_count,
						COALESCE(SUM(wo.actualcost), 0) as total_repair_cost
					FROM Scope_WO wo
					JOIN worootcause wrc ON wo.wonum = wrc.wonum
					JOIN assetfailure af ON wrc.failurecode = af.failurecode
					GROUP BY af.category, af.failurecode, af.description
				),

				Analisa_Financial AS (
					SELECT 
						COUNT(wonum) as total_wo,
						COALESCE(SUM(plannedcost), 0) as total_plan_cost,
						COALESCE(SUM(actualcost), 0) as total_actual_cost,
						(COALESCE(SUM(actualcost), 0) - COALESCE(SUM(plannedcost), 0)) as cost_variance,
						CASE 
							WHEN SUM(plannedcost) > 0 THEN ((SUM(actualcost) - SUM(plannedcost)) / SUM(plannedcost)) * 100 
							ELSE 0 
						END as variance_percentage
					FROM Scope_WO
				),

				Analisa_SupplyChain AS (
					SELECT 
						mt.itemnum,
						SUM(mt.quantity) as qty_used,
						MAX(COALESCE(iv.leadtime,0) + COALESCE(iv.extleadtime,0) + COALESCE(iv.intleadtime,0)) as max_leadtime_days,
						MIN(ifc.expdate) as nearest_expiration,
						MAX(ifc.conditionrate) as stock_condition,
						SUM(ifc.quantity) as current_stock,
						inv.safetystock as safety_stock,
						GROUP_CONCAT(DISTINCT po.vendor SEPARATOR ', ') as vendors
					FROM matusetrans mt
					JOIN Scope_WO wo ON mt.wonum = wo.wonum
					LEFT JOIN invvendor iv ON mt.itemnum = iv.itemnum
					LEFT JOIN invfifolifocost ifc ON mt.itemnum = ifc.itemnum
					LEFT JOIN poline pl ON mt.itemnum = pl.itemnum
					LEFT JOIN po ON pl.ponum = po.ponum
					LEFT JOIN inventory inv ON mt.itemnum = inv.itemnum
					GROUP BY mt.itemnum
					ORDER BY max_leadtime_days DESC
					LIMIT 5 
				),

				Analisa_Budget AS (
					SELECT 
						b.budgetnum,
						SUM(bl.budgetamount) as total_budget,
						SUM(bl.actualamount) as total_spent,
						SUM(bl.remainingamount) as remaining,
						CASE 
							WHEN SUM(bl.budgetamount) > 0 THEN (SUM(bl.actualamount) / SUM(bl.budgetamount)) * 100 
							ELSE 0 
						END as burn_rate_percent
					FROM budgetline bl
					JOIN budget b ON bl.budgetnum = b.budgetnum
					WHERE (bl.assetnum = ? OR bl.assetnum IS NULL)
					AND b.budgetperiod = ?
					GROUP BY b.budgetnum
				)

				-- FINAL ASSEMBLY (MariaDB Compatible JSON Construction)
				SELECT JSON_OBJECT(
					'asset_tag', ? ,
					'analysis_year', ? ,
					
					'financial_summary', (
						SELECT JSON_OBJECT(
							'total_wo', total_wo,
							'total_plan_cost', total_plan_cost,
							'total_actual_cost', total_actual_cost,
							'cost_variance', cost_variance,
							'variance_percentage', variance_percentage
						) FROM Analisa_Financial
					),
					
					'failure_patterns', (
						SELECT COALESCE(
							CONCAT('[', 
								GROUP_CONCAT(
									JSON_OBJECT(
										'category', category,
										'failurecode', failurecode,
										'desc', failure_desc,
										'frequency', failure_count,
										'impact_cost', total_repair_cost
									) SEPARATOR ','
								), 
							']'), 
						'[]') 
						FROM Analisa_Reliability
					),
					
					'supply_chain_risks', (
						SELECT COALESCE(
							CONCAT('[', 
								GROUP_CONCAT(
									JSON_OBJECT(
										'itemnum', itemnum,
										'qty_used', qty_used,
										'max_leadtime_days', max_leadtime_days,
										'nearest_expiration', nearest_expiration,
										'current_stock', current_stock,
										'safety_stock', safety_stock,
										'vendors', vendors
									) SEPARATOR ','
								), 
							']'), 
						'[]')
						FROM Analisa_SupplyChain
					),
					
					'budget_status', (
						SELECT COALESCE(
							CONCAT('[', 
								GROUP_CONCAT(
									JSON_OBJECT(
										'budgetnum', budgetnum,
										'total_budget', total_budget,
										'total_spent', total_spent,
										'remaining', remaining,
										'burn_rate_percent', burn_rate_percent
									) SEPARATOR ','
								), 
							']'), 
						'[]')
						FROM Analisa_Budget
					)
				) as llm_context_payload;
			";

			$params = [
				$assetnum, 
				$year,     
				$year,     
				$assetnum, 
				$year,     
				$assetnum, 
				$year      
			];

			$result = Set::fetchAll($sql, $params);

			if (empty($result) || !isset($result[0]['llm_context_payload'])) {
				Super::sendJsonResponse("error", "No context data found for Asset: $assetnum");
				return;
			}

			$rawJson = $result[0]['llm_context_payload'];
			$contextData = json_decode($rawJson, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				$contextData = []; 
				error_log("JSON Decode Error: " . json_last_error_msg());
			}
			$jsonPayload = json_encode($contextData);
			//echo $jsonPayload;
			//exit;
			$endpoint = "/generate/downtime-analysis";
			$result = Super::sendToML($endpoint, $jsonPayload, true);
			$data = json_decode($result, true);

			$toon = $data['toon_data'];
			$assetnum = $data['asset'];
			$assetDescription = (new Set())->MboSetRemote("assets")->where("assetnum=?", $assetnum)->getMboValue("description");
			$header = "Asset: $assetnum - $assetDescription <hr>";
			$narrative_analysis = $data['narrative_analysis'];
			//$generation_time = $data['generation_time'];

			Super::sendJsonResponse("success", $header.$narrative_analysis);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}


	public static function getUnplannedDowntime() {
		try {
			$payload = Super::getParam("getUnplannedDowntime");
			
			$assetnum = $payload["assetnum"] ?? '';
			$year = $payload["year"] ?? date('Y');

			if (empty($assetnum)) {
				throw new InvalidArgumentException("Asset Number is required.");
			}

			$sql = "
				SELECT 
					MONTH(reportdate) as month_num,
					YEAR(reportdate) as year_num,
					-- Total Downtime (Jam)
					ROUND(SUM(TIMESTAMPDIFF(MINUTE, reportdate, actfinishdate)) / 60, 2) as total_downtime, 
					-- MTTR (Jam) -> Rata-rata durasi perbaikan
					ROUND(AVG(TIMESTAMPDIFF(MINUTE, actstartdate, actfinishdate)) / 60, 2) as mttr_val 
				FROM workorder 
				WHERE assetnum = ? 
				AND YEAR(reportdate) = ?
				AND status NOT IN ('cancel')
				AND actfinishdate IS NOT NULL
				AND actfinishdate >= reportdate
				AND actfinishdate >= actstartdate
				GROUP BY YEAR(reportdate), MONTH(reportdate)
				ORDER BY year_num ASC, month_num ASC
			";

			$rawData = Set::fetchAll($sql, [$assetnum, $year]);
			$monthsLabel = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
			
			// Isi default dengan 0 untuk semua 12 bulan
			$downtimeSeries = array_fill(0, 12, 0.0); 
			$mttrSeries = array_fill(0, 12, 0.0);

			// Petakan hasil SQL ke dalam array
			foreach ($rawData as $row) {
				$index = (int)$row['month_num'] - 1;
				
				if ($index >= 0 && $index < 12) {
					$downtimeSeries[$index] = (float)$row['total_downtime'];
					$mttrSeries[$index] = (float)$row['mttr_val'];
				}
			}

			Super::sendJsonResponse("success", "Data loaded", [
				"categories" => $monthsLabel,
				"downtime" => $downtimeSeries,
				"mttr" => $mttrSeries
			]);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function assetPerformanceSummary()
	{
		try {
			// Langkah 1: Ambil data aset dasar (purchase price, ytd cost)
	
			$assets = (new Set())->useTransactionManager()
				->MboSetRemote("assets")
				->where("assets.purchaseprice > 0")
				->MboRemote("assetnum, description, purchaseprice, SUM(ytdcost) as sumYtdCost")
				->group("assetnum, description, purchaseprice") // Group by semua kolom non-agregat
				->having("SUM(ytdcost) > ?", 0)
				->getMboSet()
				->fetchDataAssoc('assetnum');

			if (empty($assets)) {
				Super::sendJsonResponse("success", "No asset data found to generate summary.");
				return;
			}

			$assetnums = array_keys($assets);

			// Ambil total maintenance cost untuk semua aset yang relevan dalam satu batch
			$maintenanceCosts = (new Set())->useTransactionManager()
				->MboSetRemote("matusetrans")
				->whereIn("assetnum", $assetnums)
				->MboRemote("assetnum, ABS(SUM(linecost)) as totalMaintenanceCost")
				->group("assetnum")
				->getMboSet()
				->fetchDataAssoc('assetnum');

			// Gabungkan data dan hitung LCC Ratio
			foreach ($assets as $assetnum => &$asset) { // Gunakan reference (&)
				$totalMaintenanceCost = (float)($maintenanceCosts[$assetnum]['totalMaintenanceCost'] ?? 0);
				
				$asset['sumYtdCost'] = (float)$asset['sumYtdCost'];
				$asset['purchaseprice'] = (float)$asset['purchaseprice'];
				$asset['totalMaintenanceCost'] = $totalMaintenanceCost;
				$asset['lccRatio'] = 0;

				if ($asset["purchaseprice"] > 0) {
					$asset["lccRatio"] = ($totalMaintenanceCost / $asset["purchaseprice"]) * 100;
				}
			}
			unset($asset); // Hapus reference

			if (empty($assets)) {
				Super::sendJsonResponse("success", "No assets to compare.");
				return;
			}

			$sorted = array_values($assets);
			usort($sorted, function ($a, $b) {
				return $a["lccRatio"] <=> $b["lccRatio"];
			});

			$best = $sorted[0];
			$worst = end($sorted);

			// Format summary (DENGAN KUNCI YANG BENAR)
			$summary = [
				"best_performance" => [
					"assetnum"          => $best["assetnum"],
					"description"       => $best["description"],
					"maintenance_cost"  => $best["totalMaintenanceCost"], 
					"efficiency_ratio"  => $best["lccRatio"],
				],
				"worst_performance" => [
					"assetnum"          => $worst["assetnum"],
					"description"       => $worst["description"],
					"maintenance_cost"  => $worst["totalMaintenanceCost"], 
					"efficiency_ratio"  => $worst["lccRatio"],
				],
				"all_assets" => $assets
			];

			Super::sendJsonResponse("success", "Asset Performance Summary", $summary);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", "An error occurred: " . $e->getMessage());
		}
	}


	public static function woPerformance()
	{
		
		/*
		$todayFunc = 'CURDATE()'; // Default MySQL
		if ($dbtype == 'sqlserver') $todayFunc = 'GETDATE()';
		if ($dbtype == 'oracle') $todayFunc = 'TRUNC(SYSDATE)';
		$sql = str_replace('CURDATE()', $todayFunc, $sql);
		*/
		$set = new Set();
		$sql = "
			SELECT
				-- Hitung total WO untuk perhitungan persentase
				COUNT(workorderid) AS total_wo,

				-- Kategori 1: Scheduled
				SUM(CASE WHEN LOWER(status) IN ('approved', 'waitmat', 'wappr', 'waitappr') THEN 1 ELSE 0 END) AS scheduled_count,

				-- Kategori 2: In Progress
				SUM(CASE WHEN LOWER(status) IN ('inprogress', 'completed') THEN 1 ELSE 0 END) AS inprogress_count,

				-- Kategori 3: Close
				SUM(CASE WHEN LOWER(status) = 'closed' THEN 1 ELSE 0 END) AS close_count,

				-- Kategori 4: Cancel
				SUM(CASE WHEN LOWER(status) = 'cancel' THEN 1 ELSE 0 END) AS cancel_count,

				-- Kategori 5: Due Date (Lewat Tenggat Waktu)
				-- Kondisi: Belum Close/Cancel DAN Target Completion Date sudah lewat dari hari ini.
				SUM(CASE 
					WHEN LOWER(status) NOT IN ('closed', 'cancel') AND targcompdate < CURDATE() 
					THEN 1 
					ELSE 0 
				END) AS duedate_count,

				-- Kategori 6: Backlog
				-- Kondisi: Semua WO yang statusnya belum Close atau Cancel.
				SUM(CASE WHEN LOWER(status) NOT IN ('closed', 'cancel') THEN 1 ELSE 0 END) AS backlog_count

			FROM
				workorder
		";

		$results = $set::fetchAll($sql);
		
		if (empty($results)) {
			$performanceData = [
				'total' => 0,
				'scheduled' => ['count' => 0, 'percentage' => 0],
				'inprogress' => ['count' => 0, 'percentage' => 0],
				'close' => ['count' => 0, 'percentage' => 0],
				'cancel' => ['count' => 0, 'percentage' => 0],
				'duedate' => ['count' => 0, 'percentage' => 0],
				'backlog' => ['count' => 0, 'percentage' => 0],
			];

			Super::sendJsonResponse("success", "WO Performance", $performanceData);

		}
		
		$counts = $results[0];
		$total = (int) $counts['total_wo'];

		// Hindari pembagian dengan nol jika tidak ada WO sama sekali.
		if ($total === 0) {
			$total = 1; 
		}

		$performanceData = [
			'total' => (int) $counts['total_wo'],
			'scheduled' => [
				'count' => (int) $counts['scheduled_count'],
				'percentage' => round(((int) $counts['scheduled_count'] / $total) * 100, 2)
			],
			'inprogress' => [
				'count' => (int) $counts['inprogress_count'],
				'percentage' => round(((int) $counts['inprogress_count'] / $total) * 100, 2)
			],
			'close' => [
				'count' => (int) $counts['close_count'],
				'percentage' => round(((int) $counts['close_count'] / $total) * 100, 2)
			],
			'cancel' => [
				'count' => (int) $counts['cancel_count'],
				'percentage' => round(((int) $counts['cancel_count'] / $total) * 100, 2)
			],
			'duedate' => [
				'count' => (int) $counts['duedate_count'],
				'percentage' => round(((int) $counts['duedate_count'] / $total) * 100, 2)
			],
			'backlog' => [
				'count' => (int) $counts['backlog_count'],
				'percentage' => round(((int) $counts['backlog_count'] / $total) * 100, 2)
			],
		];

		Super::sendJsonResponse("success", "WO Performance", $performanceData);
	}


	public static function sendJsonResponse($status, $message) {			
		echo json_encode(['status' => $status, 'message' => $message]);
		exit();
	}

	public static function loadNotificationData()
	{
		try {
			$username = Super::getParam("loadNotificationData");

			if (empty($username)) {
				Super::sendJsonResponse("error", "Username is required.");
				return;
			}

			// Ambil data dalam SATU KALI JALAN (Batch) menggunakan method yang Aman
			$notifData = (new Set())->useTransactionManager()
				->MboSetRemote("mxsnotification")
				->where("username = ?", $username) 
				->order("enterdate DESC")
				->getMboSet()
				->fetchData(); 

			if (empty($notifData)) {
				// Tetap kembalikan array kosong agar DataTables tidak error
				Super::sendJsonResponse("success", ["data" => []]);
				return;
			}

			$data =[]; 
			
			foreach ($notifData as $notif) {
				$data[] = [
					'username'    => $notif["username"] ?? '',
					'message'     => $notif["message"] ?? '',
					'url'         => $notif["url"] ?? '',
					'isread'      => $notif["isread"] ?? null, 
					'enterdate'   => $notif["enterdate"] ?? '',
					'object'      => $notif["object"] ?? '',
					'objectnum'   => $notif["objectnum"] ?? '',
					'objectid'    => $notif["objectid"] ?? '',
					'processname' => $notif["processname"] ?? '',
					'mxsnotificationid' => $notif["mxsnotificationid"] ?? ''
				];
			}

			Super::sendJsonResponse("success", ["data" => $data]);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", "An error occurred: " . $e->getMessage());
		}
	}
	public static function productivity_chart() {
		$assetNum = Super::getParam("productivity_chart"); 
        $year = Super::getParam("year");
        $month = Super::getParam("month");

		if (empty($assetNum)) {
            throw new InvalidArgumentException("Asset Number is required.");
        }
		if (!empty($month) && !is_numeric($month)) {
            $month = (int)date('m', strtotime($month));
        }

		$set = (new Set())->useTransactionManager();
        $dbtype = (new dbo())->dbtype();

		$set->MboSetRemote("assetprodhistory aph")
            ->setRelationship("assetproductivity ap", "aph.assetnum = ap.assetnum AND aph.assetparamnum = ap.assetparamnum")
            ->setRelationship("assetparam af", "aph.assetparamnum = af.assetparamnum")
            ->where("aph.assetnum = ? AND ap.type = 'GAIN'", $assetNum);
		

		if (!empty($year)) {
            switch ($dbtype) {
                case 'mysql':
                case 'sqlserver':
                    $set->where("YEAR(aph.startdate) = ?", $year);
                    if (!empty($month)) {
                        $set->where("MONTH(aph.startdate) = ?", $month);
                    }
                    break;
                case 'oracle':
                    $set->where("EXTRACT(YEAR FROM aph.startdate) = ?", $year);
                    if (!empty($month)) {
                        $set->where("EXTRACT(MONTH FROM aph.startdate) = ?", $month);
                    }
                    break;
            }
        }

		$set->MboRemote("
            aph.assetparamnum,
            af.description,
            af.measureUnit,
            aph.value as potential_value, 
            aph.actualvalue,     
            aph.variance,
            aph.unitcost,
            aph.startdate,
            aph.enddate
        ");

		$set->order("aph.startdate ASC");
		$productivityData = $set->getMboSet()->fetchData();

			

			
		if (!empty($year)) {
			// LOGIKA TAHUNAN
			$daysInYear = (date('L', strtotime("$year-01-01")) ? 366 : 365);
			$totalHours = $daysInYear * 24; 
		

			if (!empty($year) && !empty($month)) {
				// Konversi "Jan" ke 1
				$monthInt = is_numeric($month) ? $month : date('m', strtotime($month));
				// Hitung hari di bulan itu (misal Jan = 31)
				$daysInMonth = (int)date('t', mktime(0, 0, 0, $monthInt, 1, $year));
				// Set Total Hours jadi 744 (31 * 24)
				$totalHours = $daysInMonth * 24;
			}

			
			$reliabilities = self::downtime_stats_sqlQuery($totalHours, $year, $month, null, null, $assetNum);
			$total_assets = 1;
			$availabilities = self::calculateAvailability($reliabilities, $total_assets, $totalHours);

		} else {
			// LOGIKA RANGE TANGGAL / BULANAN
			$start = new DateTime($prodStartDate);
			$end = new DateTime($prodEndDate);
			
			// Tambah 1 detik agar diff akurat inklusif, atau hitung hari + 1
			// Cara paling aman hitung jam:
			$interval = $start->diff($end);
			// (Total Hari * 24) + Jam sisa. +24 jam karena end date biasanya inklusif sampai jam 23:59
			$totalDays = $interval->days + 1; 
			$totalHours = $totalDays * 24; 

			// Contoh Januari: Start 1 Jan, End 31 Jan. interval->days = 30. Total = 31 hari = 744 jam.
			$total_assets = 1;
			$reliabilities = self::downtime_stats_sqlQuery($totalHours, $year, null, $start, $end, $assetNum);
			$availabilities = self::calculateAvailability($reliabilities, $total_assets, $totalHours);
		}


		$performances = self::performance_stats_sqlQuery(["startDate"=>null, "enddate" => null, "year"=>$year, "month"=>$month, "assetnum" => $assetNum]);
		
		$productionPerformanceDetails = self::calculateGlobalPerformance($performances);
		$quality = self::calculateQuality(null, null, $year, $assetNum);

		

		$response = [
			"productivityParams" => $productivityData,
			"availabilities" => $availabilities,
			"productionPerformanceDetails" => $productionPerformanceDetails,
			"quality" => $quality
		];

		echo json_encode($response); 
	}
		
	public static function totaldowntime_detail_chart() {
		// 1. Ambil Parameter
		$year = Super::getParam("totaldowntime_detail_chart");
		$monthName = Super::getParam("month");
		//$month = date('m', strtotime($monthName));
		$months = [
			'Jan' => '01',
			'Feb' => '02',
			'Mar' => '03',
			'Apr' => '04',
			'May' => '05',
			'Jun' => '06',
			'Jul' => '07',
			'Aug' => '08',
			'Sep' => '09',
			'Oct' => '10',
			'Nov' => '11',
			'Dec' => '12',
		];

		$month = $months[$monthName];

		$downtimeDetails = [];

		// 2. - Menggunakan COALESCE untuk menangani failurecode yang kosong (jadi 'Unspecified')
		// - Menambahkan filter durasi < 1000 jam (Anti Zombie Data)
		$sql = "SELECT 
					wo.assetnum,
					a.description AS assetDescription,
					COALESCE(af.description, 'Unspecified') AS failureDescription,
					ROUND(SUM(TIMESTAMPDIFF(MINUTE, wo.reportdate, wo.actfinishdate)) / 60, 2) AS totalDowntimeHours
				FROM 
					workorder wo
				JOIN
					assets a ON wo.assetnum = a.assetnum
				LEFT JOIN
					assetfailure af ON wo.failurecode = af.failurecode
				WHERE 
					YEAR(wo.reportdate) = ? 
					AND MONTH(wo.reportdate) = ?
					AND wo.status NOT IN ('cancel')
					AND wo.actfinishdate IS NOT NULL
					AND wo.actfinishdate >= wo.reportdate
					-- SAFETY NET: Abaikan jika durasi > 1000 jam (Data Error/Zombie)
					AND TIMESTAMPDIFF(HOUR, wo.reportdate, wo.actfinishdate) < 1000
				GROUP BY 
					wo.assetnum, a.description, COALESCE(af.description, 'Unspecified')
				ORDER BY 
					wo.assetnum";

		try {
			// 3. Eksekusi menggunakan Library Set (Aman & Transaksional)
			$results = Set::fetchAll($sql, [$year, $month]);

			// 4. Proses Hasil (Looping di PHP)
			foreach ($results as $row) {
				$assetNum = $row['assetnum'];
				$assetFailure = $row['failureDescription']; // Sudah pasti terisi karena COALESCE
				$totalHours = (float)$row['totalDowntimeHours'];

				// Inisialisasi struktur array per asset jika belum ada
				if (!isset($downtimeDetails[$assetNum])) {
					$downtimeDetails[$assetNum] = [
						'assetNum' => $assetNum,
						'assetDescription' => $row['assetDescription'],
						'failureCodes' => [],
						// Panggil helper performance (asumsi helper ini sudah aman)
						'productionPerformance' => self::calculateProductionPerformance($assetNum)
					];
				}

				// Masukkan data downtime
				$downtimeDetails[$assetNum]['failureCodes'][$assetFailure] = $totalHours;
			}

		} catch (Exception $e) {
			error_log("Error in totaldowntime_detail_chart: " . $e->getMessage());
			header('Content-Type: application/json');
			echo json_encode([]);
			return;
		}

		// 5. Output JSON
		header('Content-Type: application/json');
		echo json_encode(array_values($downtimeDetails)); 
	}

	public static function downtime_stats_sqlQuery(
		int $totalHoursInPeriod = 8760,
		?int $year = null,
		$month = null, 
		?string $startDate = null,
		?string $endDate = null,
		?string $assetnum = null
	) {
		
		// 1. KONVERSI BULAN (String "Jan" -> Int 1)
		if (!empty($month) && !is_numeric($month)) {
			$month = (int)date('m', strtotime($month));
		}

		// 2. LOGIKA TOTAL JAM DINAMIS (FIXED)
		// Menggunakan date('t') sebagai pengganti cal_days_in_month
		if (!empty($year) && !empty($month)) {
			$daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
			$totalHoursInPeriod = $daysInMonth * 24; 
		}

		$dbtype = (new dbo())->dbtype();
		$whereClause = "";
		$whereParams = [];

		// 3. Filter Wajib
		$baseConditions = " 
			AND status NOT IN ('cancel') 
			AND actfinishdate IS NOT NULL 
			AND actfinishdate >= reportdate
		";

		// 4. Tentukan WHERE clause
		if (!is_null($year)) {
			switch ($dbtype) {
				case 'mysql':
				case 'sqlserver':
					$whereClause = "AND YEAR(reportdate) = ?";
					if (!empty($month)) {
						$whereClause .= " AND MONTH(reportdate) = ?";
					}
					break;

				case 'oracle':
					$whereClause = "AND EXTRACT(YEAR FROM reportdate) = ?";
					if (!empty($month)) {
						$whereClause .= " AND EXTRACT(MONTH FROM reportdate) = ?";
					}
					break;
			}
			
			$whereParams[] = $year;
			if (!empty($month)) {
				$whereParams[] = $month;
			}
			
		} elseif (!is_null($startDate) && !is_null($endDate)) {
			switch ($dbtype) {
				case 'mysql':
					$whereClause = "AND DATE(reportdate) BETWEEN ? AND ?";
					break;
				case 'sqlserver':
					$whereClause = "AND CAST(reportdate AS DATE) BETWEEN ? AND ?";
					break;
				case 'oracle':
					$whereClause = "AND TRUNC(reportdate) BETWEEN TO_DATE(?, 'YYYY-MM-DD') AND TO_DATE(?, 'YYYY-MM-DD')";
					break;
			}
			$whereParams[] = $startDate;
			$whereParams[] = $endDate;
		}

		// 5. Filter Asset Specific
		if ($assetnum != null) {
			$whereClause .= " AND assetnum = ?";
			$whereParams[] = $assetnum;
		}

		$fullWhereClause = "WHERE 1=1 " . $baseConditions . $whereClause;

		// 6. Build SQL
		switch ($dbtype) {
			case 'mysql':
				$sql = "
					WITH DowntimeCalc AS (
						SELECT 
							assetnum,
							SUM(TIMESTAMPDIFF(MINUTE, reportdate, actfinishdate)) / 60.0 AS total_downtime_hours
						FROM workorder
						$fullWhereClause
						AND TIMESTAMPDIFF(MINUTE, reportdate, actfinishdate) < 60000 
						GROUP BY assetnum
					)
					SELECT 
						assetnum,
						ROUND(total_downtime_hours, 2) AS total_downtime_hours,
						ROUND((? - total_downtime_hours), 2) AS total_uptime_hours,
						ROUND(((? - total_downtime_hours) / ?) * 100, 2) AS availability_pct
					FROM DowntimeCalc;
				";
				break;

			case 'sqlserver':
				$sql = "
					WITH DowntimeCalc AS (
						SELECT 
							assetnum,
							SUM(DATEDIFF(MINUTE, reportdate, actfinishdate)) / 60.0 AS total_downtime_hours
						FROM workorder
						$fullWhereClause
						AND DATEDIFF(MINUTE, reportdate, actfinishdate) < 60000
						GROUP BY assetnum
					)
					SELECT 
						assetnum,
						ROUND(total_downtime_hours, 2) AS total_downtime_hours,
						ROUND((CAST(? AS FLOAT) - total_downtime_hours), 2) AS total_uptime_hours,
						ROUND(((CAST(? AS FLOAT) - total_downtime_hours) / CAST(? AS FLOAT)) * 100, 2) AS availability_pct
					FROM DowntimeCalc;
				";
				break;

			case 'oracle':
				$sql = "
					WITH DowntimeCalc AS (
						SELECT 
							assetnum,
							SUM((actfinishdate - reportdate) * 24) AS total_downtime_hours
						FROM workorder
						$fullWhereClause
						AND ((actfinishdate - reportdate) * 24) < 1000
						GROUP BY assetnum
					)
					SELECT 
						assetnum,
						ROUND(total_downtime_hours, 2) AS total_downtime_hours,
						ROUND((? - total_downtime_hours), 2) AS total_uptime_hours,
						ROUND(((? - total_downtime_hours) / ?) * 100, 2) AS availability_pct
					FROM DowntimeCalc
				";
				break;

			default:
				throw new Exception("Unsupported database type: " . $dbtype);
		}

		// 7. Gabungkan Parameter
		$params = array_merge(
			$whereParams,
			[$totalHoursInPeriod, $totalHoursInPeriod, $totalHoursInPeriod]
		);
		
		$results = Set::fetchAll($sql, $params);

		return empty($results) ? [] : $results;
	}
		
	public static function totaldowntime_chart() {

		// 1. Ambil & Validasi Parameter
		$year = Super::hasParam("totaldowntime_chart") ? Super::getParam("totaldowntime_chart") : null;
		$prodStartDate = Super::hasParam("prodstartdate") ? Super::getParam("prodstartdate") : null;
		$prodEndDate = Super::hasParam("prodenddate") ? Super::getParam("prodenddate") : null;
		
		if (empty($year) && (empty($prodStartDate) || empty($prodEndDate))) {
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Please provide either year or both start and end dates']);
			return;
		}
		
		// Inisialisasi array 1-12 dengan nilai 0
		$monthlyDowntime = array_fill(1, 12, 0); 

		try {
			// 2. Bangun Query Menggunakan Library Maxes
			$set = (new Set())->useTransactionManager()
				->MboSetRemote("workorder")
				// Filter Data Valid (Sama seperti detail chart)
				->where("status NOT IN ('cancel')")
				->whereNotNull("actfinishdate")
				->where("actfinishdate >= reportdate"); // Validasi logis

			// Terapkan Filter Waktu (Tahun atau Rentang Tanggal)
			if (!empty($year)) {
				$set->where("YEAR(reportdate) = ?", $year);
			} else {
				$set->where("DATE(reportdate) BETWEEN ? AND ?", $prodStartDate, $prodEndDate);
			}

			// 3. Eksekusi Agregasi
			// Hitung selisih menit, lalu jumlahkan per bulan
			$downtimeData = $set->MboRemote("MONTH(reportdate) as month_num")
				->MboRemote("SUM(TIMESTAMPDIFF(MINUTE, reportdate, actfinishdate)) as total_minutes")
				->group("MONTH(reportdate)")
				->order("month_num ASC")
				->getMboSet()
				->fetchData();

			// 4. Mapping Hasil ke Array Bulanan
			foreach ($downtimeData as $row) {
				$month = (int)$row['month_num'];
				// Simpan dalam MENIT agar konsisten dengan logika lama (assetdowntimehistory.durationminutes)
				// Jika ingin JAM, tinggal dibagi 60.
				// $monthlyDowntime[$month] = (float)$row['total_minutes']; 
				$monthlyDowntime[$month] = (float)$row['total_minutes']/60; 
			}

		} catch (Exception $e) {
			error_log("Error in totaldowntime_chart: " . $e->getMessage());
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Data retrieval error']);
			return;
		}

		// 5. Kalkulasi KPI (Logika Bisnis Lama Dipertahankan)
		// Bagian ini tidak perlu koneksi DB manual lagi karena fungsi helper (kemungkinan) sudah pakai Set/Maxes
		
		header('Content-Type: application/json');
		
		// Hitung total aset menggunakan cara efisien countRecords()
		$total_assets = (new Set())->MboSetRemote("assets")->countRecords();

		// Variabel KPI default
		$reliabilities = [];
		$availabilities = [];
		$quality = [];
		$performances = [];
		$productionPerformanceDetails = [];

		if (!empty($year)) {
			$daysInYear = (date('L', strtotime("$year-01-01")) ? 366 : 365);
   			$totalHours = $daysInYear * 24; // Sekitar 8760 atau 8784
			
			$reliabilities = self::downtime_stats_sqlQuery($totalHours, $year, null, null, null, null);
			$availabilities = self::calculateAvailability($reliabilities, $total_assets, $totalHours);
			$quality = self::calculateQuality(null, null, $year);

			$performances = self::performance_stats_sqlQuery(["startDate"=>$prodStartDate, "enddate" => $prodEndDate, "year"=>$year, "month"=>"Jan"]);
			
			$productionPerformanceDetails = self::calculateGlobalPerformance($performances);
			
		} else {
		
			$start = new DateTime($prodStartDate);
			$end = new DateTime($prodEndDate);
			$interval = $start->diff($end);
			$totalDays = $interval->days + 1; 
  			$totalHours = $totalDays * 24; 
			
			$reliabilities = self::downtime_stats_sqlQuery($totalHours, null, null, null, $prodStartDate, $prodEndDate);
			$availabilities = self::calculateAvailability($reliabilities, $total_assets, $totalHours);
			$quality = self::calculateQuality($prodStartDate, $prodEndDate, null);

			$performances = self::performance_stats_sqlQuery(["startDate"=>$prodStartDate, "enddate" => $prodEndDate, "year"=>$year, "month"=>"Jan"]);
			$productionPerformanceDetails = self::calculateGlobalPerformance($performances);
		}

		// Ekstrak data performa (dengan handling null safe)
		$totalPerformance = $productionPerformanceDetails['global_performance_pct'] ?? 0;
		$avgPerformance = $productionPerformanceDetails['avg_performance_pct'] ?? 0;
		$totalActualOutput = $productionPerformanceDetails['total_actual_output'] ?? 0;
		$totalTheoreticalMax = $productionPerformanceDetails['total_theoretical_max'] ?? 0;
		$totalAssets = $productionPerformanceDetails['total_assets'] ?? 0;
		$bestPerformer = $productionPerformanceDetails['best_performer'] ?? null;
		// $worstPerformer = $productionPerformanceDetails['worst_performer'] ?? null; // Tidak dipakai di response lama, tapi ada di logika

		$response = [
			"downtimeMonths" => $monthlyDowntime, // Data baru dari Workorder
			"availabilities" => $availabilities,
			"quality" => $quality,
			"productionPerformanceDetails" => $productionPerformanceDetails,
			"totalPerformance" => $totalPerformance,
			"avgPerformance" => $avgPerformance,
			"totalActualOutput" => $totalActualOutput,
			"totalTheoreticalMax" => $totalTheoreticalMax,
			"totalAssets" => $totalAssets,
			"bestPerformer" => $bestPerformer
		];
		
		echo json_encode($response); 
	}

	public static function performance_stats_sqlQuery(array $options = []) 
	{
		
		// 1. Ekstrak Parameter
		$startDate = $options['startDate'] ?? null;
		$endDate = $options['endDate'] ?? null;
		$year = $options['year'] ?? null;
		$assetnum = $options['assetnum'] ?? null;
		$month = $options["month"] ?? null;

		// 2. Konversi Bulan (String "Jan" -> Int 1)
		if (!empty($month) && !is_numeric($month)) {
			$month = (int)date('m', strtotime($month));
		}

		$dbtype = (new dbo())->dbtype();
		$whereClause = "";
		$params = [];

		// 3. Tentukan WHERE Clause
		if (!is_null($year)) {
			// MODE: Filter by Year (dan Month jika ada)
			switch ($dbtype) {
				case 'mysql':
				case 'sqlserver':
					$whereClause = "WHERE YEAR(p.productiondate) = ?";
					// Tambahkan filter bulan jika ada
					if (!empty($month)) {
						$whereClause .= " AND MONTH(p.productiondate) = ?";
					}
					break;
				case 'oracle':
					$whereClause = "WHERE EXTRACT(YEAR FROM p.productiondate) = ?";
					if (!empty($month)) {
						$whereClause .= " AND EXTRACT(MONTH FROM p.productiondate) = ?";
					}
					break;
			}
			
			$params[] = $year;
			if (!empty($month)) {
				$params[] = $month;
			}

		} else {
			// MODE: Filter by Date Range
			// Catatan: Jika ingin cross-platform penuh, sesuaikan syntax DATE() seperti fungsi sebelumnya
			$whereClause = "WHERE p.startdate >= ? AND p.enddate <= ?";
			$params[] = $startDate;
			$params[] = $endDate;
		}

		if ($assetnum != null) {
			$whereClause .= " AND p.assetnum = ?";
			$params[] = $assetnum;
		}

		$defaultCapacity = 10;

		// 4. Bangun SQL
		// Jika ingin support SQL Server/Oracle, bagian ini perlu switch case seperti downtime_stats_sqlQuery.
	
		$sql = "
		SELECT 
			p.assetnum,
			-- Asset Info
			COALESCE(a.description, 'Unknown Asset') as asset_description,
			COALESCE(a.design_capacity, {$defaultCapacity}) as design_capacity_per_hour,
			
			-- Actual Production
			SUM(COALESCE(p.quantity, 0)) as total_actual_output,
			
			-- Running Hours (Total jam produksi berjalan)
			SUM(
				GREATEST(0, TIMESTAMPDIFF(HOUR, p.startdate, p.enddate))
			) as total_running_hours,
			
			-- Theoretical Maximum Output (Running Hours * Design Capacity)
			(
				SUM(GREATEST(0, TIMESTAMPDIFF(HOUR, p.startdate, p.enddate))) 
				* COALESCE(a.design_capacity, {$defaultCapacity})
			) as theoretical_max_output,
			
			-- Performance Efficiency (%)
			-- (Actual Output / Theoretical Max) * 100
			CASE 
				WHEN SUM(TIMESTAMPDIFF(HOUR, p.startdate, p.enddate)) > 0 THEN
					ROUND(
						(
							SUM(COALESCE(p.quantity, 0)) / 
							(
								SUM(TIMESTAMPDIFF(HOUR, p.startdate, p.enddate)) 
								* COALESCE(a.design_capacity, {$defaultCapacity})
							)
						) * 100
					, 2)
				ELSE 0
			END as performance_efficiency_pct,
			
			-- Production Rate (Actual Ton/Hour)
			CASE 
				WHEN SUM(TIMESTAMPDIFF(HOUR, p.startdate, p.enddate)) > 0 THEN
					ROUND(
						SUM(COALESCE(p.quantity, 0)) / 
						SUM(TIMESTAMPDIFF(HOUR, p.startdate, p.enddate))
					, 2)
				ELSE 0
			END as actual_production_rate,
			
			COUNT(*) as production_log_count
			
		FROM productionlog p
		LEFT JOIN assets a ON p.assetnum = a.assetnum
		{$whereClause}
		GROUP BY p.assetnum, a.description, a.design_capacity
		ORDER BY performance_efficiency_pct DESC
		";
		
		// 5. Eksekusi
		$results = Set::fetchAll($sql, $params);
		

		return empty($results) ? [] : $results;
	}
	
	public static function calculateProductionPerformance(
		?string $assetnum = null,
		array $options = []
	) {
		// ============================================
		// 1. CONFIGURATION & DEFAULTS
		// ============================================
		
		$defaultCapacity = $options['defaultCapacity'] ?? 10; // TON per hour
		$startDate = $options['startDate'] ?? null;
		$endDate = $options['endDate'] ?? null;
		
		// 2. BUILD QUERY
		
		$whereClauses = ["enddate > startdate"]; 
		$params = [];
		
		// Filter by specific asset or all assets
		if (!is_null($assetnum)) {
			$whereClauses[] = "p.assetnum = ?";
			$params[] = $assetnum;
		}
		
		// Optional date range filter
		if ($startDate) {
			$whereClauses[] = "startdate >= ?";
			$params[] = $startDate;
		}
		if ($endDate) {
			$whereClauses[] = "enddate <= ?";
			$params[] = $endDate;
		}
		
		$whereClause = "WHERE " . implode(" AND ", $whereClauses);
		
		// ============================================
		// 3. QUERY WITH DESIGN CAPACITY FROM DB
		// ============================================
		
			$sql = "
			SELECT 
				p.assetnum,
				-- Asset Info (Optional: Join dengan asset master)
				COALESCE(a.description, 'Unknown Asset') as asset_description,
				COALESCE(a.design_capacity, {$defaultCapacity}) as design_capacity_per_hour,
				
				-- Actual Production
				SUM(COALESCE(p.quantity, 0)) as total_actual_output,
				
				-- Running Hours
				SUM(
					GREATEST(0, TIMESTAMPDIFF(HOUR, p.startdate, p.enddate))
				) as total_running_hours,
				
				-- Theoretical Maximum Output
				(
					SUM(GREATEST(0, TIMESTAMPDIFF(HOUR, p.startdate, p.enddate))) 
					* COALESCE(a.design_capacity, {$defaultCapacity})
				) as theoretical_max_output,
				
				-- Performance Efficiency (%)
				CASE 
					WHEN SUM(TIMESTAMPDIFF(HOUR, p.startdate, p.enddate)) > 0 THEN
						ROUND(
							(
								SUM(COALESCE(p.quantity, 0)) / 
								(
									SUM(TIMESTAMPDIFF(HOUR, p.startdate, p.enddate)) 
									* COALESCE(a.design_capacity, {$defaultCapacity})
								)
							) * 100
						, 2)
					ELSE 0
				END as performance_efficiency_pct,
				
				-- Production Rate (Actual Ton/Hour)
				CASE 
					WHEN SUM(TIMESTAMPDIFF(HOUR, p.startdate, p.enddate)) > 0 THEN
						ROUND(
							SUM(COALESCE(p.quantity, 0)) / 
							SUM(TIMESTAMPDIFF(HOUR, p.startdate, p.enddate))
						, 2)
					ELSE 0
				END as actual_production_rate,
				
				-- Count production logs
				COUNT(*) as production_log_count
				
			FROM productionlog p
			LEFT JOIN assets a ON p.assetnum = a.assetnum
			{$whereClause}
			GROUP BY p.assetnum, a.description, a.design_capacity
			ORDER BY performance_efficiency_pct DESC;
			";
		
		
		
		// 4. EXECUTE QUERY
		$set = new Set();
		$results = $set::fetchAll($sql, $params);
		
		// 5. CALCULATE GLOBAL METRICS (if not specific asset)
		
		if (is_null($assetnum) && !empty($results)) {
			$globalMetrics = self::calculateGlobalPerformance($results);
			
			return [
				'type' => 'global',
				'assets' => $results,
				'summary' => $globalMetrics
			];
		}
		
		// ============================================
		// 6. RETURN SINGLE ASSET OR EMPTY
		// ============================================
		
		if (!is_null($assetnum)) {
			return [
				'type' => 'single',
				'asset' => !empty($results) ? $results[0] : null
			];
		}
		
		return [
			'type' => 'global',
			'assets' => [],
			'summary' => [
				'total_actual_output' => 0,
				'total_running_hours' => 0,
				'avg_performance_pct' => 0,
				'total_assets' => 0
			]
		];
	}

	private static function calculateGlobalPerformance(array $assetResults) {
		
		if (empty($assetResults)) {
			return [
				'total_actual_output' => 0,
				'total_running_hours' => 0,
				'total_theoretical_max' => 0,
				'global_performance_pct' => 0,
				'avg_performance_pct' => 0,
				'total_assets' => 0,
				'best_performer' => null,
				'worst_performer' => null
			];
		}
		
		$totalActualOutput = 0;
		$totalRunningHours = 0;
		$totalTheoreticalMax = 0;
		$performanceSum = 0;
		$assetCount = count($assetResults);
		
		$bestPerformer = $assetResults[0];
		$worstPerformer = $assetResults[0];
		
		foreach ($assetResults as $asset) {
			$totalActualOutput += floatval($asset['total_actual_output']);
			$totalRunningHours += floatval($asset['total_running_hours']);
			$totalTheoreticalMax += floatval($asset['theoretical_max_output']);
			$performanceSum += floatval($asset['performance_efficiency_pct']);
			
			// Track best/worst
			if ($asset['performance_efficiency_pct'] > $bestPerformer['performance_efficiency_pct']) {
				$bestPerformer = $asset;
			}
			if ($asset['performance_efficiency_pct'] < $worstPerformer['performance_efficiency_pct']) {
				$worstPerformer = $asset;
			}
		}
		
		// Global Performance (weighted by actual output)
		$globalPerformancePct = 0;
		if ($totalTheoreticalMax > 0) {
			$globalPerformancePct = round(($totalActualOutput / $totalTheoreticalMax) * 100, 2);
		}
		
		// Average Performance (simple average)
		$avgPerformancePct = round($performanceSum / $assetCount, 2);
		
		return [
			'total_actual_output' => round($totalActualOutput, 2),
			'total_running_hours' => round($totalRunningHours, 2),
			'total_theoretical_max' => round($totalTheoreticalMax, 2),
			'global_performance_pct' => $globalPerformancePct, // Weighted
			'avg_performance_pct' => $avgPerformancePct, // Simple average
			'total_assets' => $assetCount,
			'best_performer' => [
				'assetnum' => $bestPerformer['assetnum'],
				'performance_pct' => $bestPerformer['performance_efficiency_pct']
			],
			'worst_performer' => [
				'assetnum' => $worstPerformer['assetnum'],
				'performance_pct' => $worstPerformer['performance_efficiency_pct']
			]
		];
	}


	public static function calculateQuality(
		?string $prodStartDate = null, 
		?string $prodEndDate = null, 
		?int $year = null,
		?string $assetnum = null
	) {
		// Validasi: Pastikan ada salah satu parameter yang diisi
		if (is_null($year) && (is_null($prodStartDate) || is_null($prodEndDate))) {
			throw new Exception("Please provide either year or both start and end dates for quality calculation");
		}

		// Tentukan WHERE clause berdasarkan parameter
		$whereClause = "";
		$params = [];

		if (!is_null($year)) {
			// MODE: Filter by Year
			$whereClause = "WHERE YEAR(productiondate) = ?";
			$params[] = $year;
		} else {
			// MODE: Filter by Date Range
			$whereClause = "WHERE productiondate BETWEEN ? AND ?";
			$params[] = $prodStartDate;
			$params[] = $prodEndDate;
		}


		if($assetnum != null) {
			$whereClause = $whereClause. " AND assetnum=?";
			$params[] = $assetnum;
		}

		$sql = "
		WITH 
		
		RejectedItems AS (
			SELECT
				batchnum,
				itemnum, 
				conditiontype,
				SUM(quantity) AS total_rejected
			FROM
				invfifolifocost
			WHERE
				conditionrate < 100 AND conditiontype is not null
			GROUP BY
				batchnum, itemnum 
		),
		
		ProducedItems AS (
			SELECT
				assetnum,
				itemnum,
				batchnum,
				SUM(quantity) AS total_produced
			FROM
				productionlog
			{$whereClause}
			GROUP BY
				assetnum, itemnum, batchnum
		)
		-- Gabungkan keduanya
		SELECT 
			p.assetnum,
			p.itemnum,
			SUM(p.total_produced) as total_produced,
			SUM(COALESCE(r.total_rejected, 0)) as total_rejected,
			-- Hitung Quality Rate
			CASE 
				WHEN SUM(p.total_produced) = 0 THEN 0
				ELSE ROUND(
					(
						(SUM(p.total_produced) - SUM(COALESCE(r.total_rejected, 0))) 
						/ SUM(p.total_produced)
					) * 100
				, 2)
			END as quality_rate
		FROM 
			ProducedItems p
		LEFT JOIN 
			RejectedItems r ON p.batchnum = r.batchnum AND p.itemnum = r.itemnum 
		GROUP BY
			p.assetnum, p.itemnum;
		";

		$set = new Set();
		$results = $set::fetchAll($sql, $params);
		
		// Langkah 1: Inisialisasi variabel untuk total keseluruhan
		$grand_total_produced = 0;
		$grand_total_rejected = 0;

		// Langkah 2: Loop melalui hasil dan akumulasikan total
		foreach ($results as $row) {
			
			$grand_total_produced += (float)$row['total_produced'];
			$grand_total_rejected += (float)$row['total_rejected'];
		}


		$overall_quality_rate = 0; // Nilai default
		
		if ($grand_total_produced > 0) {
			$total_good_products = $grand_total_produced - $grand_total_rejected;
		
			$overall_quality_rate = ($total_good_products / $grand_total_produced) * 100;
		}
		$formatted_quality_rate = number_format($overall_quality_rate, 2);
		return $formatted_quality_rate;
	}



	public static function calculateAvailability($dataAssetDownData, $totalAssetPopulation, $periodTotalHours) {
    
		// 1. SETUP PARAMETER DINAMIS
		// Pastikan target jam sesuai periode (Jan = 744, Tahun = 8760)
		$targetHours = ($periodTotalHours > 0) ? floatval($periodTotalHours) : 8760;
		
		// Pastikan populasi valid
		$totalAssetPopulation = max(0, intval($totalAssetPopulation));
		
		// Struktur Return Default (Kosong/Aman untuk GUI)
		$defaultResult = [
			"assets" => [],
			"total" => [
				"totalAssets"      => $totalAssetPopulation,
				"healthyAssets"    => $totalAssetPopulation,
				"assetsWithIssues" => 0,
				"totalDowntimePct" => 0.00,
				"totalUptimePct"   => ($totalAssetPopulation > 0) ? 100.00 : 0.00,
				"EA_pct"           => ($totalAssetPopulation > 0) ? 100.00 : 0.00
			]
		];

		if ($totalAssetPopulation == 0 || empty($dataAssetDownData)) {
			return $defaultResult;
		}
		
		// 2. PROSES DATA (Langsung, tanpa agregasi ulang)
		// Karena SQL 'downtime_stats_sqlQuery' sudah melakukan GROUP BY assetnum
		
		$perAsset = [];
		$sumDowntimeAllAssets = 0;
		$uniqueAssetsWithIssues = count($dataAssetDownData);

		// Anomaly Check: Jika data downtime lebih banyak dari total populasi aset
		if ($totalAssetPopulation < $uniqueAssetsWithIssues) {
			$totalAssetPopulation = $uniqueAssetsWithIssues;
		}

		foreach ($dataAssetDownData as $item) {
			$assetnum = $item["assetnum"];
			// Ambil downtime dari SQL. Gunakan floatval untuk keamanan.
			$downtime = max(0, floatval($item['total_downtime_hours'] ?? 0));
			
			// Normalisasi: Downtime tidak boleh melebihi jam periode
			if ($downtime > $targetHours) {
				$downtime = $targetHours; 
			}
			
			// Hitung Uptime
			$uptime = $targetHours - $downtime;
			
			// Hitung Persentase
			$downtimePct = ($downtime / $targetHours) * 100;
			$uptimePct = ($uptime / $targetHours) * 100;
			
			// Masukkan ke array hasil per aset
			$perAsset[] = [
				"assetnum"      => $assetnum,
				"downtimePct"   => round($downtimePct, 2),
				"uptimePct"     => round($uptimePct, 2),
				"EA_pct"        => round($uptimePct, 2),
				// Tambahan info jam (opsional, berguna untuk tooltip chart)
				"downtimeHours" => $downtime, 
				"uptimeHours"   => $uptime
			];
			
			$sumDowntimeAllAssets += $downtime;
		}
		
		// 3. HITUNG GLOBAL METRICS (Termasuk aset yang sehat)
		
		// Total Jam Populasi (Semua aset x Jam Periode)
		$grandTotalHoursAvailable = $totalAssetPopulation * $targetHours;
		
		// Total Uptime Global = Total Kapasitas - Total Downtime Terjadi
		$grandTotalUptime = $grandTotalHoursAvailable - $sumDowntimeAllAssets;
		
		// Hitung Persentase Global
		$totalDowntimePct = 0;
		$totalUptimePct = 0;

		if ($grandTotalHoursAvailable > 0) {
			$totalDowntimePct = ($sumDowntimeAllAssets / $grandTotalHoursAvailable) * 100;
			$totalUptimePct = ($grandTotalUptime / $grandTotalHoursAvailable) * 100;
		}
		
		// 4. RETURN HASIL (Struktur sama persis agar GUI aman)
		return [
			"assets" => $perAsset,
			"total" => [
				"totalAssets"      => $totalAssetPopulation,
				"healthyAssets"    => $totalAssetPopulation - $uniqueAssetsWithIssues,
				"assetsWithIssues" => $uniqueAssetsWithIssues,
				"totalDowntimePct" => round($totalDowntimePct, 2),
				"totalUptimePct"   => round($totalUptimePct, 2),
				"EA_pct"           => round($totalUptimePct, 2)
			]
		];
	}

	public static function Spending_category_overtime_year() {
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type');

		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			http_response_code(200);
			exit();
		}

		try {
		
		$year = isset($_POST['prpo_analytics_year']) ? intval($_POST['prpo_analytics_year']) : date('Y');
			$mode = isset($_POST['type']) ? $_POST['type'] : 'detail';
			$period = isset($_POST['period']) ? $_POST['period'] : 'monthly';
			
			if ($year < 1900 || $year > 2100) {
				http_response_code(400);
				echo json_encode(['error' => 'Year is not valid', 'year' => $year]);
				return;
			}

			error_log("Request received - Year: $year, Mode: $mode, Period: $period");

			$conn = DB::getMXConnection();
			
			if (!$conn) {
				throw new Exception("Database connection failed");
			}

			switch ($mode) {
				case 'count':
					self::handle_count_mode($conn, $year, $period);
					break;
				case 'cost':
					self::handle_cost_mode($conn, $year);
					break;
				case 'detail':
				default:
					self::handle_detail_mode($conn, $year);
					break;
			}

			if ($conn) {
				$conn->close();
			}
		} catch (Exception $e) {
			error_log("Database error: " . $e->getMessage());
			http_response_code(500);
			echo json_encode([
				'error' => 'Terjadi kesalahan database', 
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
		}
	}


	private static function handle_detail_mode($conn, $year) {
	
		if (!is_numeric($year) || $year < 1900 || $year > 2100) {
			http_response_code(400); // Bad Request
			echo json_encode(['error' => 'Invalid year format.']);
			return;
		}
		$year = (int)$year;
		$sql = "
			SELECT 
				pr.prnum, 
				pr.description, 
				pr.requestedby, 
				pr.status, 
				DATE(pr.requesteddate) AS order_date, 
				MONTH(pr.requesteddate) AS order_month, 
				COALESCE(pr.totalbasecost, 0) AS cost, 
				'PR' AS source_type, 
				NULL AS ponum, 
				NULL AS vendor, 
				NULL AS buyer 
			FROM pr pr 
			WHERE YEAR(pr.requesteddate) = '" . $year . "' AND COALESCE(pr.totalbasecost, 0) > 0
			UNION ALL
			SELECT 
				NULL AS prnum, 
				po.description, 
				NULL AS requestedby, 
				po.status, 
				DATE(po.orderdate) AS order_date, 
				MONTH(po.orderdate) AS order_month, 
				COALESCE(po.totalbasecost, 0) AS cost, 
				'PO' AS source_type, 
				po.ponum, 
				po.vendor, 
				po.buyer 
			FROM po po 
			WHERE YEAR(po.orderdate) = '" . $year . "' AND COALESCE(po.totalbasecost, 0) > 0
			ORDER BY order_date
		";

		$result = mysqli_query($conn, $sql);
		if (!$result) {
		
			error_log("Detail mode SQL error: " . mysqli_error($conn));
			http_response_code(500);
			echo json_encode(['error' => 'SQL query failed.']);
			return;
		}

		$data = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$row['order_month'] = (int)$row['order_month'] - 1; 
			$row['cost'] = (float)$row['cost'];
			$data[] = $row;
		}

		mysqli_free_result($result);

		error_log("Detail mode returned " . count($data) . " records");
		echo json_encode($data);
	}

	private static function handle_cost_mode($conn, $year) {
		
		if (!is_numeric($year) || $year < 1900 || $year > 2100) {
			http_response_code(400);
			echo json_encode(['error' => 'Invalid year format.']);
			return;
		}
		$year = (int)$year;
		$sql = "
			SELECT 
				m.month_num,
				COALESCE(pr_costs.total_cost, 0) AS pr_cost,
				COALESCE(po_costs.total_cost, 0) AS po_cost
			FROM 
				(SELECT 1 AS month_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
				UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
				UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) AS m
			LEFT JOIN 
				(SELECT MONTH(requesteddate) AS month_num, SUM(COALESCE(totalbasecost, 0)) AS total_cost 
				FROM pr WHERE YEAR(requesteddate) = '" . $year . "' GROUP BY MONTH(requesteddate)) AS pr_costs 
				ON m.month_num = pr_costs.month_num
			LEFT JOIN 
				(SELECT MONTH(orderdate) AS month_num, SUM(COALESCE(totalbasecost, 0)) AS total_cost 
				FROM po WHERE YEAR(orderdate) = '" . $year . "' GROUP BY MONTH(orderdate)) AS po_costs 
				ON m.month_num = po_costs.month_num
			ORDER BY m.month_num
		";

		$result = mysqli_query($conn, $sql);
		if (!$result) {
			error_log("Cost mode SQL error: " . mysqli_error($conn));
			http_response_code(500);
			echo json_encode(['error' => 'SQL query failed.']);
			return;
		}

		$labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		$pr_costs = [];
		$po_costs = [];

		while ($row = mysqli_fetch_assoc($result)) {
			$pr_costs[] = (float)$row['pr_cost'];
			$po_costs[] = (float)$row['po_cost'];
		}

		mysqli_free_result($result);

		$response = [
			'labels' => $labels,
			'pr_costs' => $pr_costs,
			'po_costs' => $po_costs
		];

		error_log("Cost mode response: " . json_encode($response));
		echo json_encode($response);
	}

	private static function handle_count_mode($conn, $year, $period) {
		if (!is_numeric($year) || $year < 1900 || $year > 2100) {
			http_response_code(400);
			echo json_encode(['error' => 'Invalid year format.']);
			return;
		}
		$year = (int)$year;
		$date_format_pr = "DATE(pr.requesteddate)";
		$date_format_po = "DATE(po.orderdate)";

		if ($period === 'weekly') {
			$date_format_pr = "DATE_FORMAT(pr.requesteddate, '%x-%v')";
			$date_format_po = "DATE_FORMAT(po.orderdate, '%x-%v')";
		} elseif ($period === 'monthly') {
			$date_format_pr = "DATE_FORMAT(pr.requesteddate, '%Y-%m')";
			$date_format_po = "DATE_FORMAT(po.orderdate, '%Y-%m')";
		}

		$sql = "
			SELECT 
				time_period,
				SUM(pr_count) AS total_pr,
				SUM(po_count) AS total_po
			FROM (
				SELECT {$date_format_pr} AS time_period, COUNT(*) AS pr_count, 0 AS po_count 
				FROM pr pr WHERE YEAR(pr.requesteddate) = '" . $year . "' 
				GROUP BY time_period
				UNION ALL
				SELECT {$date_format_po} AS time_period, 0 AS pr_count, COUNT(*) AS po_count 
				FROM po po WHERE YEAR(po.orderdate) = '" . $year . "' 
				GROUP BY time_period
			) AS combined
			GROUP BY time_period
			ORDER BY time_period
		";
		
		$result = mysqli_query($conn, $sql);
		if (!$result) {
			error_log("Count mode SQL error: " . mysqli_error($conn));
			http_response_code(500);
			echo json_encode(['error' => 'SQL query failed.']);
			return;
		}

		$labels = [];
		$pr_counts = [];
		$po_counts = [];

		while($row = mysqli_fetch_assoc($result)){
			$labels[] = $row['time_period'];
			$pr_counts[] = (int)$row['total_pr'];
			$po_counts[] = (int)$row['total_po'];
		}

		mysqli_free_result($result);

		$response = [
			'labels' => $labels,
			'pr_counts' => $pr_counts,
			'po_counts' => $po_counts
		];

		error_log("Count mode response: " . json_encode($response));
		echo json_encode($response);
	}


	public static function budget_allocation_breakdown_year(){
		header('Content-Type: application/json');

		$year = isset($_POST['budget_allocation_breakdown_year']) ? $_POST['budget_allocation_breakdown_year'] : date('Y');
		$budgetlineNum = isset($_POST['budgetlinenum']) ? $_POST['budgetlinenum'] : null;
		$budgetNum = isset($_POST['budgetnum']) ? $_POST['budgetnum'] : null;

		$conn = DB::getMXConnection();

		if ($budgetlineNum === null || $budgetNum === null || $budgetlineNum === '' || $budgetNum === '') {
			// Query untuk mendapatkan daftar budget lines
			$sqlBudgetLines = "
				SELECT DISTINCT bl.budgetlinenum, bl.budgetnum, bl.description, b.budgetperiod
				FROM budgetline bl
				LEFT JOIN budget b ON bl.budgetnum = b.budgetnum
				WHERE b.status in ('active', 'approved') AND b.budgetperiod = '$year'
				ORDER BY bl.budgetnum, bl.budgetlinenum
			";
		
			$resultBudgetLines = $conn->query($sqlBudgetLines);
			$budgetLines = array();
			
			if ($resultBudgetLines && $resultBudgetLines->num_rows > 0) {
				while ($row = $resultBudgetLines->fetch_assoc()) {
					$budgetLines[] = array(
						'budgetlinenum' => $row['budgetlinenum'],
						'budgetnum' => $row['budgetnum'],
						'description' => $row['description']
					);
				}
			}
			
			$conn->close();
			echo json_encode($budgetLines);
			return;
		}

		// 1. Query untuk Budget Line (untuk budgetamount dan budgetperiod)
		$sqlBudget = "
			SELECT budgetamount, b.budgetperiod
			FROM budgetline bl
			LEFT JOIN budget b ON bl.budgetnum = b.budgetnum
			WHERE budgetlinenum = '$budgetlineNum' AND bl.budgetnum = '$budgetNum'
		";
		$resultBudget = $conn->query($sqlBudget);
		$budgetAmount = 0;
		$budgetPeriod = null;
		if ($resultBudget && $resultBudget->num_rows > 0) {
			$row = $resultBudget->fetch_assoc();
			$budgetAmount = floatval($row["budgetamount"]);
			$budgetPeriod = $row["budgetperiod"];
		} else {
			echo json_encode(["error" => "Budget not found for selected parameters"]);
			$conn->close();
			return;
		}

   	 // 2. Query untuk PR (Committed Amount)
		$sqlPR = "
			SELECT
				DATE_FORMAT(pr.requesteddate, '%Y-%m') AS month,
				SUM(prl.linecost + prl.taxcost) AS committed_amount
			FROM
				prline prl
			LEFT JOIN
				pr ON pr.prnum = prl.prnum
			WHERE
				YEAR(pr.requesteddate) = '$year'
				AND prl.budgetlinenum = '$budgetlineNum'
				AND pr.budgetnum = '$budgetNum'
				AND pr.status NOT IN ('cancel')
				AND NOT EXISTS (
					SELECT 1 FROM poline pol 
					WHERE pol.prnum = prl.prnum 
					AND pol.prlinenum = prl.prlinenum
				)
			GROUP BY
				DATE_FORMAT(pr.requesteddate, '%Y-%m')
			ORDER BY
				DATE_FORMAT(pr.requesteddate, '%Y-%m');
		";
		

		$resultPR = $conn->query($sqlPR);
		$prData = array();
		if ($resultPR && $resultPR->num_rows > 0) {
			while ($row = $resultPR->fetch_assoc()) {
				$prData[$row["month"]] = floatval($row["committed_amount"]);
			}
		}

		// 3. PERBAIKAN 2: Query PO yang diperbaiki
		$sqlPO = "
			SELECT
				DATE_FORMAT(po.ORDERDATE, '%Y-%m') AS month,
				SUM(
					CASE 
						WHEN po.BUDGETSPAN <= 1 THEN 
							(poline.LINECOST + poline.TAXCOST)
						WHEN po.BUDGETSPAN > 1 THEN 
							COALESCE(
								(SELECT SUM(ABA.ALLOCATEDAMOUNT)
								FROM annualbudgetalloc ABA 
								WHERE ABA.PONUM = po.PONUM 
								AND ABA.polineNUM = poline.polineNUM
								AND ABA.BUDGETNUM = po.BUDGETNUM 
								AND ABA.BUDGETLINENUM = poline.BUDGETLINENUM
								AND ABA.STATUS = 'active'
								), 0
							)
						ELSE 0
					END
				) AS committed_amount
			FROM
				po
			LEFT JOIN
				poline ON po.PONUM = poline.PONUM
			WHERE
				YEAR(po.ORDERDATE) = '$year'
				AND poline.BUDGETLINENUM = '$budgetlineNum'
				AND po.BUDGETNUM = '$budgetNum'
				AND po.BUDGETNUM IS NOT NULL
			GROUP BY
				DATE_FORMAT(po.ORDERDATE, '%Y-%m')
			ORDER BY
				DATE_FORMAT(po.ORDERDATE, '%Y-%m');
		";
		
		$resultPO = $conn->query($sqlPO);
		$poData = array();
		if ($resultPO && $resultPO->num_rows > 0) {
			while ($row = $resultPO->fetch_assoc()) {
				$month = $row["month"];
				// Akumulasi jika ada multiple PO di bulan yang sama
				if (isset($poData[$month])) {
					$poData[$month] += floatval($row["committed_amount"]);
				} else {
					$poData[$month] = floatval($row["committed_amount"]);
				}
			}
		}

		$conn->close();

		// 4. Generate bulan berdasarkan tahun yang diminta
		$months = array();
		for ($i = 1; $i <= 12; $i++) {
			$months[] = sprintf('%d-%02d', $year, $i);
		}

		// 5. Siapkan data untuk chart
		$seriesData = array();
		$remainingBudget = $budgetAmount;
		
		foreach ($months as $month) {
			$prCommitted = isset($prData[$month]) ? $prData[$month] : 0;
			$poCommitted = isset($poData[$month]) ? $poData[$month] : 0;
			$totalCommitted = $prCommitted + $poCommitted;

			$seriesData[] = array(
				"month" => $month,
				"prCommitted" => $prCommitted,
				"poCommitted" => $poCommitted,
				"totalCommitted" => $totalCommitted,
				"remainingBudget" => max(0, $remainingBudget - $totalCommitted)
			);

			$remainingBudget -= $totalCommitted;
			if ($remainingBudget < 0) {
				$remainingBudget = 0;
			}
		}
		
		echo json_encode($seriesData);
	}

	public static function budget_actual_spending_year()
	{
	
		header('Content-Type: application/json');

		try {
			// Ambil tahun dari POST atau gunakan tahun sekarang secara aman
			$year = $_POST['budget_actual_spending_year'] ?? date('Y');

	
			$sql = "
				SELECT
					budgetnum,
					budgetlinenum,
					description AS budget_header_description,
					budgetline_description,
					budgetlinecode,
					budgetamount,
					budgetperiod,
					allocated_amount,
					committed_amount,
					remaining_amount
				FROM
					budgetline_summary
				WHERE
					budgetperiod = ?
				ORDER BY
					budgetlinenum;
			";

			$data = Set::fetchAll($sql, [$year]);

			echo json_encode($data);

		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode([
				"error" => true,
				"message" => "Gagal memuat data budget: " . $e->getMessage()
			]);
		}
	}
   	public static function lead_time_item()
	{
		header('Content-Type: application/json');

		try {
			
			$year = $_POST['chart_lead_time_item'] ?? date('Y');

			$sql = "
				WITH InternalLeadTime AS (
					SELECT
						prline.itemnum,
						item.description AS item_description,
						pr.sitecode,
						CAST(po.orderdate AS DATE) AS orderdate,
						CAST(pr.requesteddate AS DATE) AS requesteddate
					FROM
						prline
					JOIN
						pr ON prline.prnum = pr.prnum
					JOIN
						poline ON prline.itemnum = poline.itemnum AND pr.prnum = poline.prnum
					JOIN
						po ON poline.ponum = po.ponum
					JOIN
						item ON prline.itemnum = item.itemnum
					WHERE
						pr.requesteddate IS NOT NULL
						AND po.orderdate IS NOT NULL
						AND YEAR(po.orderdate) = ?
				),
				ExternalLeadTime AS (
					SELECT
						poline.itemnum,
						item.description AS item_description,
						po.sitecode,
						CAST(matrectrans.actualdate AS DATE) AS actualdate,
						CAST(po.orderdate AS DATE) AS orderdate
					FROM
						poline
					JOIN
						po ON poline.ponum = po.ponum
					JOIN
						matrectrans ON poline.ponum = matrectrans.ponum AND poline.polinenum = matrectrans.polinenum
					JOIN
						item ON poline.itemnum = item.itemnum
					WHERE
						po.status = 'SENT'
						AND matrectrans.actualdate IS NOT NULL
						AND po.orderdate IS NOT NULL
						AND YEAR(po.orderdate) = ?
				),
				CombinedLeadTimes AS (
					SELECT
						itemnum,
						item_description,
						DATEDIFF(orderdate, requesteddate) AS internal_lead_time,
						NULL AS external_lead_time
					FROM
						InternalLeadTime

					UNION ALL

					SELECT
						itemnum,
						item_description,
						NULL AS internal_lead_time,
						DATEDIFF(actualdate, orderdate) AS external_lead_time
					FROM
						ExternalLeadTime
				),
				GroupedLeadTimes AS (
					SELECT
						itemnum,
						item_description,
						AVG(internal_lead_time) AS avg_internal_lead_time,
						AVG(external_lead_time) AS avg_external_lead_time
					FROM
						CombinedLeadTimes
					GROUP BY
						itemnum,
						item_description
				)
				SELECT
					itemnum,
					item_description,
					COALESCE(ROUND(avg_internal_lead_time, 2), 0) AS average_internal_lead_time,
					COALESCE(ROUND(avg_external_lead_time, 2), 0) AS average_external_lead_time,
					COALESCE(ROUND(
							CASE
								WHEN avg_internal_lead_time IS NULL THEN avg_external_lead_time
								WHEN avg_external_lead_time IS NULL THEN avg_internal_lead_time
								ELSE (avg_internal_lead_time + avg_external_lead_time)
							END
						, 2), 0) AS average_total_lead_time
				FROM
					GroupedLeadTimes
				ORDER BY
					itemnum
			";

			$data = Set::fetchAll($sql, [$year, $year]);

			echo json_encode($data);

		} catch (Exception $e) {
			http_response_code(500);
			echo json_encode([
				"error" => true,
				"message" => "Gagal memuat data lead time: " . $e->getMessage()
			]);
		}
	}
	public static function lead_time_year()
	{
		try {
			$leadtimeYear = Super::getParam('lead_time_year', date('Y'));
			$dbtype = (new dbo())->dbtype();

			$sqlDiffFunc = '';
			$sqlYearFunc = '';

			if ($dbtype === 'sqlserver') {
				// Note: Parameter start & end terbalik dibanding MySQL untuk hasil positif yang sama
				$sqlYearFunc = 'YEAR(po.ORDERDATE)';
				$sqlDiffFunc = 'DATEDIFF(day, pr.REQUESTEDDATE, po.ORDERDATE)'; 
			} 
			elseif ($dbtype === 'oracle') {
				$sqlYearFunc = 'EXTRACT(YEAR FROM po.ORDERDATE)';
				$sqlDiffFunc = 'TRUNC(po.ORDERDATE) - TRUNC(pr.REQUESTEDDATE)';
			} 
			else {
				$sqlYearFunc = 'YEAR(po.ORDERDATE)';
				$sqlDiffFunc = 'DATEDIFF(po.ORDERDATE, pr.REQUESTEDDATE)';
			}

			$set = new Set();

			// 1. Query untuk Internal Lead Time
			// Langsung masukkan variabel string yang sudah disiapkan di atas

			$internalSql = "
				SELECT
					po.PONUM,
					po.ORDERDATE,
					pr.PRNUM,
					pr.REQUESTEDDATE,
					$sqlDiffFunc AS INTERNAL_LEADTIME
				FROM po
				JOIN poline ON po.PONUM = poline.PONUM
				JOIN pr ON poline.PRNUM = pr.PRNUM
				WHERE po.STATUS = 'sent'
				AND pr.STATUS IN ('new', 'approved')
				AND $sqlYearFunc = ?
			";

		
			// fetchAll lebih cocok di sini daripada method chaining panjang
			$internalData = $set::fetchAll($internalSql, [$leadtimeYear]);
			$internalDataMap = !empty($internalData) ? array_column($internalData, null, 'PONUM') : [];

			
			// 2. Query untuk External Lead Time, menggunakan Set & fetchDataAssoc
			// --- SETUP LOGIKA DATABASE (Sama seperti sebelumnya) ---
			$dbtype = (new dbo())->dbtype();
			$sqlYearFunc = '';
			$sqlExternalDiff = '';

			if ($dbtype === 'sqlserver') {
				// SQL Server: DATEDIFF(day, START, END)
				$sqlYearFunc = 'YEAR(po.ORDERDATE)';
				// Start: OrderDate, End: Max ActualDate
				$sqlExternalDiff = 'DATEDIFF(day, po.ORDERDATE, MAX(matrectrans.ACTUALDATE))';
			} 
			elseif ($dbtype === 'oracle') {
				// Oracle: END - START
				$sqlYearFunc = 'EXTRACT(YEAR FROM po.ORDERDATE)';
				$sqlExternalDiff = 'TRUNC(MAX(matrectrans.ACTUALDATE)) - TRUNC(po.ORDERDATE)';
			} 
			else {
				// MySQL / Default: DATEDIFF(END, START)
				$sqlYearFunc = 'YEAR(po.ORDERDATE)';
				// End: Max ActualDate, Start: OrderDate
				$sqlExternalDiff = 'DATEDIFF(MAX(matrectrans.ACTUALDATE), po.ORDERDATE)';
			}

			// --- QUERY EXTERNAL LEAD TIME ---

			$set = new Set();

			// Variabel SQL string sudah disiapkan di atas, tinggal masukin variable php-nya
			$externalSql = "
				SELECT
					po.PONUM,
					po.VENDOR,
					po.ORDERDATE,
					MAX(matrectrans.ACTUALDATE) AS LAST_RECEIVEDDATE,
					$sqlExternalDiff AS EXTERNAL_LEADTIME
				FROM po
				JOIN matrectrans ON po.PONUM = matrectrans.PONUM
				WHERE po.STATUS = 'sent'
				AND $sqlYearFunc = ?
				GROUP BY
					po.PONUM, po.VENDOR, po.ORDERDATE
			";

			// Eksekusi
			$externalData = $set::fetchAll($externalSql, [$leadtimeYear]);

			// 3. Gabungkan Data 
			$finalData = [];
			foreach ($externalData as $external) {
				$ponum = $external['PONUM'];
				$finalData[$ponum] = $external;
				if (isset($internalDataMap[$ponum])) {
					$finalData[$ponum]["INTERNAL_LEADTIME"] = $internalDataMap[$ponum]["INTERNAL_LEADTIME"];
					$finalData[$ponum]["TOTAL_LEADTIME"] = (int)$external["EXTERNAL_LEADTIME"] + (int)$internalDataMap[$ponum]["INTERNAL_LEADTIME"];
					// Tambahkan data lain jika perlu
				} else {
					$finalData[$ponum]["INTERNAL_LEADTIME"] = null;
					$finalData[$ponum]["TOTAL_LEADTIME"] = (int)$external["EXTERNAL_LEADTIME"];
				}
			}
			
			// 4. Agregasi Data untuk Chart
			$chartData = [];
	
			foreach ($finalData as $ponum => $data) {
				$vendor = $data["VENDOR"];
				$internalLeadTime = isset($data["INTERNAL_LEADTIME"]) ? (int)$data["INTERNAL_LEADTIME"] : 0;
				$externalLeadTime = (int)$data["EXTERNAL_LEADTIME"];

				if (!isset($chartData[$vendor])) {
					$chartData[$vendor] = ["vendor" => $vendor, "internalLeadTime" => 0, "externalLeadTime" => 0];
				}
				$chartData[$vendor]["internalLeadTime"] += $internalLeadTime;
				$chartData[$vendor]["externalLeadTime"] += $externalLeadTime;
			}
			// 5. Struktur Output JSON 

			$countPOs = (new Set())->useTransactionManager()->MboSetRemote("po")->where("po.status=?", "sent")->getMboSet()->counts();

			Super::sendJsonResponse("success", "Lead time data loaded", [
				"datatableData" => array_values($finalData),
				"chartData" => array_values($chartData),
				"poCount" => $countPOs
			]);


		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function loadGoodReceiptFunnelData() {
		$conn = DB::getMXConnection();
		$result = [];

		try {
		
			// 1. Hitung item yang diterima
			$items_accepted_count = (new Set())->useTransactionManager()
											->MboSetRemote("rfsinsitem")
											->where("inspectiontype = ?", 'accepted')
											->countRecords();

			// 2. Hitung item yang diterima (dari rfsline)
			$items_received_count = (new Set())->useTransactionManager()
											->MboSetRemote("rfsline")
											->countRecords();

			// 3. Hitung RFS yang dibuat
			$rfs_created_count = (new Set())->useTransactionManager()
											->MboSetRemote("rfs")
											->MboRemote("COUNT(DISTINCT rfsnum) as total")
											->getMboValue("total", 0);

			// 4. Hitung RFS yang diinspeksi
			$items_inspected_count = (new Set())->useTransactionManager()
												->MboSetRemote("rfsinsitem")
												->MboRemote("COUNT(DISTINCT rfsnum) as total")
												->getMboValue("total", 0);

			

		} catch (Exception $e) {
			// Handle error
			error_log("Failed to count Item Inspections " . $e->getMessage());
		}
		// Hitung persentase untuk setiap tahapan
		$total_items_received = $items_received_count;

		// Persentase dihitung relatif terhadap total_items_received atau tahap sebelumnya jika relevan
		$percent_inspected = ($total_items_received > 0) ? round(($items_inspected_count / $total_items_received) * 100, 2) : 0;
		$percent_rfs_created = ($total_items_received > 0) ? round(($rfs_created_count / $total_items_received) * 100, 2) : 0;

		// Tidak mungkin Accepted > Inspected
		$percent_accepted = ($items_inspected_count > 0) ? min(100, round(($items_accepted_count / $items_inspected_count) * 100, 2)) : 0;

		// Ambil data durasi dan biaya jika tersedia
		$avg_time_to_inspect = "Inspection Duration data is not available"; 
		$avg_cost_per_inspection = "Inspection Cost Data is not available"; 
		$top_rejection_reasons = "Return/Reject Data is not available.";

		$result['data'] = [
			['value' => $items_accepted_count, 'name' => 'Items Accepted'],
			['value' => $items_received_count, 'name' => 'Items Received'],
			['value' => $rfs_created_count, 'name' => 'GRI Created'],
			['value' => $items_inspected_count, 'name' => 'Items Inspected']
		];

		$inspection_stats = (new Set())->useTransactionManager()->MboSetRemote("rfsinsitem")->MboRemote("count(*) as receiptCount, inspectiontype")->group("inspectiontype")->getMboSet()->fetchData();

		$result['inspection_stats'] = $inspection_stats;

		$avgInspectionTimeSet = (new Set())->useTransactionManager()->MboSetRemote("rfsinsitem")
			->setRelationship("rfs", "rfsinsitem.rfsnum = rfs.rfsnum")
			->MboRemote("rfs.rfsdate, rfsinsitem.inspcompdate, rfsinsitem.inspectiontype")
			->getMboSet()->fetchData();
		// AVERAGE INSPECTION TIME
		$totalDays = 0;
		$count = 0;

		foreach ($avgInspectionTimeSet as $row) {

			if (
				empty($row['rfsdate']) ||
				empty($row['inspcompdate']) ||
				substr($row['rfsdate'], 0, 4) == "1900" ||
				substr($row['inspcompdate'], 0, 4) == "1900"
			) {
				continue;
			}

			$rfs = new DateTime($row['rfsdate']);
			$insp = new DateTime($row['inspcompdate']);

			$diff = $rfs->diff($insp)->days;

			if ($diff >= 0) {
				$totalDays += $diff;
				$count++;
			}
		}

		$averageInspectionTime = $count > 0 ? ($totalDays / $count) : 0;
		$averageInspectionTime = round($averageInspectionTime, 2);

		$result['average_inspection_time'] = $averageInspectionTime;
		// REJECTION RATE & PASS RATE

		$total = 0;
		$rejected = 0;
		$accepted = 0;

		foreach ($avgInspectionTimeSet as $row) {

			$total++;

			// accepted count untuk PASS RATE
			if ($row['inspectiontype'] === 'accepted') {
				$accepted++;
			}

			// rejected kalau bukan "accepted" atau "inspect"
			if ($row['inspectiontype'] !== 'accepted' && $row['inspectiontype'] !== 'inspect') {
				$rejected++;
			}
		}

		$rejectionRate = $total > 0 ? (($rejected / $total) * 100) : 0;
		$rejectionRate = round($rejectionRate, 2);

		$passRate = $total > 0 ? (($accepted / $total) * 100) : 0;
		$passRate = round($passRate, 2);

		$result['rejection_rate'] = $rejectionRate;
		$result['pass_rate'] = $passRate;
		$set = new Set();
		$sql = "
			SELECT 
				m.ponum,
				p.vendor,
				m.transtype,
				m.actualdate AS last_receiveddate, 
				p.vendordate AS requireddate, 
				
				CASE 
					WHEN m.actualdate <= p.vendordate THEN 'ON TIME'
					ELSE 'LATE'
				END AS delivery_status
				
			FROM matrectrans m
			JOIN po p 
				ON m.ponum = p.ponum 
			WHERE 
				m.transtype = 'RECEIPT' 
				AND p.vendordate IS NOT NULL
				AND p.status NOT IN ('cancel', 'new') 
			ORDER BY 
				m.actualdate DESC";
		
		$results = $set::fetchAll($sql);

		$total = count($results);
		$ontime = 0;

		foreach ($results as $item) {
			if (isset($item["delivery_status"]) && strtoupper($item["delivery_status"]) === "ON TIME") {
				$ontime++;
			}
		}
		$otd_percentage = ($total > 0) ? ($ontime / $total) * 100 : 0;
		$result['ontimedelivery'] = round($otd_percentage, 2);
		$poSet = (new Set())->MboSetRemote("po")->whereNotIn("status", ["cancel"])->whereNotIn("receipt", ["COMPLETE"])->getMboSet()->countRecords();
		$result['active_pos'] = $poSet;
		$result['description'] = "
				<h5 style='font-size: 14px; font-weight: bold; margin-bottom: 5px;'>1. Stage Explanation:</h5>
				<ul style='list-style-type: disc; margin-left: 20px; margin-bottom: 15px;'>
					<li><strong>Items Accepted:</strong> Number of items that have passed inspection and meet quality standards. High levels here assure smooth and efficient supply process.</li>
					<li><strong>Items Received:</strong> Total number of items received from suppliers. This stage acts as your potential for GRI and inspection.</li>
					<li><strong>GRI Created:</strong> Number of 'Good Receipt Inspection' (GRI) created, each typically representing a Purchase Order. More number of GRI created means more detail for the material and suppliers.</li>
					<li><strong>Items Inspected:</strong> Number of distinct RFSnums for inspected Items.</li>
				</ul>
				<h5 style='font-size: 14px; font-weight: bold; margin-bottom: 5px;'>2. Factors Influencing Funnel Shape:</h5>
				<ul style='list-style-type: disc; margin-left: 20px; margin-bottom: 15px;'>
					<li><strong>GRI Created:</strong> Approximately {$percent_rfs_created}% of the total Items Received result in an GRI for inspection. This reflects the scope of inspection.</li>
					<li><strong>Average time to create an GRI:</strong> {$avg_time_to_inspect}.</li>
					<li><strong>Items Inspected:</strong> Approximately {$percent_inspected}% of the total Items Received was inspected. A lower percentage may indicate resource constraints or risk-based inspection.</li>
					<li><strong>Average cost per inspection:</strong> {$avg_cost_per_inspection}.</li>
					<li><strong>Items Accepted:</strong> {$percent_accepted}% of Items Inspected are finally accepted after inspection, passing required quality levels. A low percentage indicates quality issues with received items.</li>
				</ul>
				<h5 style='font-size: 14px; font-weight: bold; margin-bottom: 5px;'>3. Recommendations:</h5>
				<ul style='list-style-type: disc; margin-left: 20px;'>
					<li>Analyze trends in the funnel over time to identify systemic issues.</li>
					<li>Investigate causes for rejected items to improve supplier quality and reduce waste. Top rejection reasons are {$top_rejection_reasons}.</li>
					<li>Consider risk-based inspection approaches if inspection resources are limited.</li>
				</ul>";

		header('Content-Type: application/json');
		echo json_encode($result);
	}

	public static function loadRfsPerMonthInspectionTypeData() {
		$result = [];

		// Daftar semua bulan
		$all_months = [
			'01' => 'January', '02' => 'February', '03' => 'March',
			'04' => 'April', '05' => 'May', '06' => 'June',
			'07' => 'July', '08' => 'August', '09' => 'September',
			'10' => 'October', '11' => 'November', '12' => 'December'
		];

		// Query utama
		$sql = "SELECT
					DATE_FORMAT(r.estdatearrival, '%m') AS month_num,
					DATE_FORMAT(r.estdatearrival, '%M') AS month_name,
					COUNT(r.rfsnum) AS rfs_count,
					COALESCE(rit.inspectiontype, 'No Inspection') AS inspectiontype
				FROM rfs r
				LEFT JOIN rfsinsitem rit ON r.rfsnum = rit.rfsnum
				WHERE r.estdatearrival IS NOT NULL  -- Handle estdatearrival NULL
				GROUP BY month_num, inspectiontype
				ORDER BY month_num";

		$conn = DB::getMXConnection(); // Menggunakan fungsi koneksi yang sudah ada
		$queryResult = $conn->query($sql);

		// Inisialisasi data
		$data = [];
		foreach ($all_months as $month_num => $month_name) {
			$data[$month_name] = [
				'accepted' => 0,
				'rejected' => 0,
				'intransit' => 0,
				'inspect' => 0,
				'No Inspection' => 0, // Untuk data yang tidak memiliki inspectiontype
			];
		}


		// Isi data dari hasil query
		while ($row = $queryResult->fetch_assoc()) {
			$month_name = $row['month_name'];
			$inspectiontype = $row['inspectiontype'];
			$rfs_count = (int)$row['rfs_count'];

			// Pastikan inspectiontype yang ada sesuai dengan yang diharapkan
			if (isset($data[$month_name][$inspectiontype])) {
				$data[$month_name][$inspectiontype] = $rfs_count;
			} else {
				// Handle jika ada inspectiontype baru yang tidak terduga
				$data[$month_name][$inspectiontype] = $rfs_count; // Menambah kategori inspectiontype baru jika diperlukan
			}
		}

		$conn->close();

		// Format data untuk ECharts
		$categories = array_keys($all_months); //January, february ...
		$series_data = [];
		$inspection_types = ['accepted', 'rejected', 'intransit', 'inspect', 'No Inspection']; // Daftar inspectiontype yang mungkin

		foreach ($inspection_types as $type) {
			$series_data[$type] = [];
			foreach ($all_months as $month_num => $month_name) {
			   $series_data[$type][] = $data[$month_name][$type];
			}
		}

		$result['categories'] = array_values($all_months); // Month Names
		$result['series_data'] = $series_data;
		$result['inspection_types'] = $inspection_types;

		

		header('Content-Type: application/json');
		echo json_encode($result);
	}

	public static function loadABCItem(){
		$itemnum = $_POST['loadABCItem'];  //Sanitize input!!!!

        $conn = DB::getMXConnection(); // Pastikan DB adalah class yang benar dan koneksi berhasil

        //Sanitize input (PENTING!!!)
        $itemnum = mysqli_real_escape_string($conn, $itemnum);

        $sql = "
            SELECT
                po.ponum,
                po.vendor,
                v.name as vendorname,
                po.orderdate,
                po.estdatearrival,
                m.itemnum,
                i.description, 
                m.actualdate,
                DATEDIFF(m.actualdate, po.orderdate) AS leadtime,
                CASE
                    WHEN DATE(m.actualdate) > DATE(po.estdatearrival) THEN 'Y'
                    ELSE 'N'
                END AS islate,
                m.remarks
            FROM
                matrectrans m
            LEFT JOIN
                po ON m.ponum = po.ponum
            LEFT JOIN
                vendor v ON po.vendor = v.vendorcode   /* tambahkan join ke tabel vendor */
            LEFT JOIN item i ON m.itemnum = i.itemnum /*join item table*/
            WHERE m.itemnum = '$itemnum'";
		
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Query gagal: " . mysqli_error($conn));
        }

        $html = ''; // Inisialisasi string HTML
		$linenum = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $html .= '
			    <div class="form-list-item">
				  <div class="field">
					<center><label>Num.</label>
					'.$linenum.'</center>
				  </div>
				  <div class="field">
					<label>Vendor</label>
					<input type="text" value="' . htmlspecialchars($row['vendor']) . '" readonly data-attribute="vendor">
				  </div>
				  <div class="field">
					<label>Vendor Name</label>
					<input type="text" value="' . htmlspecialchars($row['vendorname']) . '" readonly data-attribute="vendorname">
				  </div>
				  <div class="field">
					<label>PO</label>
					<input type="text" value="' . htmlspecialchars($row['ponum']) . '" readonly data-attribute="ponum">
				  </div>
				  <div class="field">
					<label>Order Date</label>
					<input type="text" value="' . htmlspecialchars($row['orderdate']) . '" readonly data-attribute="orderdate">
				  </div>
				  <div class="field">
					<label>Estimated Date Arrival</label>
					<input type="text" value="' . htmlspecialchars($row['estdatearrival']) . '" readonly data-attribute="estdatearrival">
				  </div>
				  <div class="field">
					<label>Actual Received Date</label>
					<input type="text" value="' . htmlspecialchars($row['actualdate']) . '" readonly data-attribute="actualdate">
				  </div>
				  <div class="field">
					<label>Lead Time</label>
					<input type="text" value="' . htmlspecialchars($row['leadtime']) . '" readonly data-attribute="leadtime">
				  </div>
				  <div class="field">
					<label>Is Late?</label>
					<input type="text" value="' . htmlspecialchars($row['islate']) . '" readonly data-attribute="islate">
				  </div>
				</div>
			';
			$linenum++;
        }

        mysqli_close($conn);

        $response = array('success' => true, 'html' => $html);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
	}
    public static function ABCAnalysis() {
        $startDate = $_POST["startdate"];
        $endDate = $_POST["enddate"];

        $conn = DB::getMXConnection();

        $startDate = mysqli_real_escape_string($conn, $startDate);
        $endDate = mysqli_real_escape_string($conn, $endDate);

        $sql = "
            SELECT
                i.itemnum,
                i.description,
                SUM(m.quantity) AS issueqty,
                m.unitcost,
                SUM(m.quantity * m.unitcost)*-1	 AS usage_value 
            FROM item i
            JOIN matusetrans m ON i.itemnum = m.itemnum
            WHERE m.enterdate >= '$startDate' AND m.enterdate <= '$endDate'
            GROUP BY i.itemnum, i.description, m.unitcost
            ORDER BY usage_value DESC  
        ";
		
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            die("Query gagal: " . mysqli_error($conn));
        }

        $items = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        // **Urutkan array PHP jika pengurutan di query SQL tidak berhasil**
        usort($items, function($a, $b) {
            return $b['usage_value'] - $a['usage_value']; // Urutkan descending
        });
		
        // Hitung total nilai penggunaan tahunan (untuk persentase kontribusi)
        $totalValue = 0;
        foreach ($items as $item) {
            $totalValue += floatval($item['usage_value']);
        }

        // Handle jika tidak ada data atau totalValue nol
        if (count($items) == 0) {
            echo json_encode(['data' => [], 'message' => 'No data found for given date range.']);
            exit;
        }

        if ($totalValue == 0) {
            echo json_encode(['data' => [], 'message' => '0 Usage Value. Make sure the data is correct.']);
            exit;
        }
        // Hitung nilai penggunaan, persentase kontribusi, dan persentase kumulatif
        $cumulativePercentageValue = 0;
        $cumulativePercentageItems = 0;
        $numItems = count($items);
        $results = [];

        // Data untuk Chart ECharts
        $chartCategories = []; // Kategori (misalnya, nama item)
        $chartValues = [];     // Nilai (Cumulative Percentage Value)


        // Hitung persentase dan kumulatif *sebelum* menentukan ranking
        foreach ($items as $index => $item) {
            $usageValue = floatval($item['usage_value']); // Gunakan usage_value
            $percentageContribution = ($totalValue != 0) ? ($usageValue / $totalValue) * 100 : 0;
            $cumulativePercentageItems = (($index + 1) / $numItems) * 100;

            $results[] = [
                'itemnum' => $item['itemnum'],
                'description' => $item['description'],
                'percentage_contribution' => round($percentageContribution, 2),
                'cumulative_percentage_value' => round(0, 2),  // Akan dihitung nanti
                'cumulative_percentage_items' => round($cumulativePercentageItems, 2),
                'ranking' => ''  // Placeholder untuk ranking
				
            ];

             // Data untuk Chart (Line Chart)
            $chartCategories[] = $item['description'];
            
        }

        // Tentukan Ranking *setelah* semua persentase dihitung
        $classAThreshold = 70;  // Batas untuk Kelas A
        $classBThreshold = 90;  // Batas untuk Kelas B
        $cumulativeValue = 0;

        foreach ($results as &$result) {  // Gunakan reference (&) agar perubahan mempengaruhi array asli
            $cumulativeValue += $result['percentage_contribution'];  // Menjumlahkan kontribusi
             $result['cumulative_percentage_value'] = round($cumulativeValue, 2); //Update Cumulative Value
            $chartValues[] = round($cumulativeValue, 2);  // Menambah data untuk chart
            if ($cumulativeValue <= $classAThreshold) {
                $result['ranking'] = 'A';
            } elseif ($cumulativeValue <= $classBThreshold) {
                $result['ranking'] = 'B';
            } else {
                $result['ranking'] = 'C';
            }
			
			$set = new Set();
			$itemnum = $result['itemnum'];
			$usageranking = $result['ranking'];
			$set->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->setValue("usageranking",$usageranking )->change();
        }

        //data untuk ECharts
        $chartResult = [
            'categories' => $chartCategories,
            'values' => $chartValues,
            'type' => 'line'  // Tipe chart
        ];

		
		// Menghitung jumlah item di kelas A
		$numClassAItems = 0;
		foreach ($results as $result) {
			if ($result['ranking'] == 'A') {
				$numClassAItems++;
			}
		}

		// Menghitung persentase total inventory yang diatur ( Kelas A dan B)
		$totalValueControlled = 0;
		foreach ($results as $result) {
			if ($result['ranking'] == 'A' || $result['ranking'] == 'B') {
				$totalValueControlled += $result['percentage_contribution'];
			}
		}
		$totalValueControlled = round($totalValueControlled, 2);
		//not used.
		$statements = [
			"By focusing on the top " . round(($numClassAItems / $numItems) * 100, 0) . "% of items (Class A), you control " . $totalValueControlled . "% of your total inventory value.",
			"Managing just the top " . round(($numClassAItems / $numItems) * 100, 0) . "% of items (Class A) allows you to effectively oversee " . $totalValueControlled . "% of your inventory investment.",
			"Implementing ABC analysis enables you to optimize inventory management by focusing on the key ".$numClassAItems ." items that account for " . $totalValueControlled . "% of total usage."
		];
		$randomIndex = array_rand($statements);
		$selectedStatement = $statements[$randomIndex];


		$statementTemplates = [
			[
				"template" => "Concentrating on a high value subset of approximately {itemCount} items responsible for {percentageValue}% of total inventory ensures strategic oversight.",
				"explanation" => "Strategically focusing on essential items significantly impacts inventory management."
			],
			[
				"template" => "By concentrating the inventory management on the {classAItems} A class items with approximately {classAValue} percent, your key expenses will be controlled .",
				"explanation" => "This will ensure a high impact to reduce expenses, the A category represents high value item."
			],
			 [
				"template" => "Prioritizing Class A inventory items directly mitigates the business risks associated with downtime and material unavailability.",
				"explanation" => "Better managing valuable inventory will avoid any unnecessary risk associated with downtime and item shortage."
			],
			 [
				"template" => "Our focus on the highest value inventory, representing about {percentageValue}% of total asset value, directly reduces storage and administrative overhead costs.",
				"explanation" => "Focusing on high-value items allows for more efficient inventory management, reducing storage expenses and minimizing the risk of obsolescence."
			]
		];
		$randomIndex = array_rand($statementTemplates);
		$selectedTemplate = $statementTemplates[$randomIndex];
		$selectedStatement = str_replace("{itemCount}", $numItems, $selectedTemplate["template"]);
		$selectedStatement = str_replace("{percentageValue}", $totalValueControlled, $selectedStatement);
		$selectedStatement = str_replace("{classAItems}", $numClassAItems, $selectedStatement);
		$selectedStatement = str_replace("{classAValue}", round(($numClassAItems / $numItems) * 100, 0), $selectedStatement);


		$abc_analysis_statements = [
			"statement" => $selectedStatement,
			"explanation" => $selectedTemplate["explanation"]
		];
		

        // Mengembalikan data dalam format JSON yang sesuai untuk Datatable dan Chart
        header('Content-Type: application/json');
        echo json_encode(['data' => $results, 'charts' => $chartResult, 'abc_analysis_statements' => $abc_analysis_statements]);
        exit;

        mysqli_close($conn);
    }
	
    public static function chartType() {
		$charttype = $_POST["chartType"];
		$startDate = $_POST["startDate"];
		$monthsAhead = $_POST["monthsAhead"];
		
		// Panggil fungsi recomorder yang sudah dioptimasi
		$reservations = Charts::recomorder($startDate, $monthsAhead);

		if ($charttype === "forecastDemand_supplyTimeLine") {

			$scatterData = [];

			foreach ($reservations as $reservation) {
				$itemnum = $reservation['itemnum'];

				// 1. Work Order Data (Struktur array objek langsung)
				if (!empty($reservation['workorders'])) {
					foreach ($reservation['workorders'] as $workorder) {
						// Validasi tanggal agar tidak error di strtotime
						if (empty($workorder['targstartdate'])) continue;

						$targstartdate = date('Y-m-d', strtotime($workorder['targstartdate']));
						$reservedqty = floatval($workorder['reservedqty']);

						$scatterData[] = [
							'name' => $workorder['wonum'],
							'value' => [$targstartdate, $reservedqty],
							'itemnum' => $itemnum,
							'quantity' => $reservedqty,
							'late' => $workorder['late'],         // Data baru dari recomorder
							'lateDays' => $workorder['lateDays'], // Data baru dari recomorder
							'type' => 'WO'
						];
					}
				}

				// 2. Purchase Order Data (PERUBAHAN UTAMA DI SINI)
				// Struktur baru: stockOnTheWay['po'] adalah array objek langsung.
				// Tidak perlu nested loop 'as $poArray'.
				if (!empty($reservation['stockOnTheWay']['po'])) {
					
					foreach ($reservation['stockOnTheWay']['po'] as $po) {
						// Validasi tanggal
						if (empty($po['estdatearrival'])) continue;

						$estdatearrival = date('Y-m-d', strtotime($po['estdatearrival']));
						$remainingqty = floatval($po['remainingqty']);
						$orderdate = !empty($po['orderdate']) ? date('Y-m-d', strtotime($po['orderdate'])) : null;

						$scatterData[] = [
							'name' => $po['ponum'],
							'value' => [$estdatearrival, $remainingqty],
							'itemnum' => $itemnum,
							'quantity' => $remainingqty,
							'orderDate' => $orderdate,
							'type' => 'PO'
						];
					}
				}
			}

			// Output JSON
			header('Content-Type: application/json');
			echo json_encode($scatterData);
			exit;
		}
	}

	public static function recomorder($startdate, $monthsahead)
	{
		// Validasi input
		if (empty($startdate) || empty($monthsahead)) {
			throw new InvalidArgumentException("Start Date dan Months Ahead wajib diisi.");
		}

		$endDate = date('Y-m-d', strtotime($startdate . ' + ' . intval($monthsahead) . ' months'));

		// 1. AMBIL SUMMARY DATA (Kueri Cerdas sebelumnya)
		$sqlSummary = "
			WITH 
			ReservationData AS (
				SELECT wop.itemnum, i.description AS itemdesc, i.issueunit, SUM(wop.quantity) AS total_reserved_qty
				FROM woplanmaterial wop
				JOIN workorder wo ON wo.wonum = wop.wonum
				JOIN item i ON i.itemnum = wop.itemnum
				WHERE wo.targstartdate >= ? AND wo.targstartdate <= ?
				GROUP BY wop.itemnum, i.description, i.issueunit
			),
			IncomingStock AS (
				SELECT pl.itemnum,
					SUM(pl.orderqty - COALESCE((SELECT SUM(quantity) FROM matrectrans mrt WHERE mrt.ponum = pl.ponum AND mrt.polinenum = pl.polinenum), 0)) AS total_incoming_qty
				FROM poline pl
				JOIN po ON pl.ponum = po.ponum
				WHERE po.status = 'SENT' AND po.orderdate >= ? AND po.orderdate <= ? AND pl.receiptstatus != 'COMPLETE' 
				GROUP BY pl.itemnum
			)
			SELECT 
				res.itemnum, res.itemdesc, res.issueunit,
				COALESCE(res.total_reserved_qty, 0) AS quantity,
				COALESCE(inc.total_incoming_qty, 0) AS stock_on_the_way,
				COALESCE(vib.curbal, 0) AS curbal,
				COALESCE(vib.avgcost, 0) AS avgcost
			FROM ReservationData res
			LEFT JOIN IncomingStock inc ON res.itemnum = inc.itemnum
			LEFT JOIN v_invbalance vib ON res.itemnum = vib.itemnum
			ORDER BY res.itemnum
		";

		$summaryData = Set::fetchAll($sqlSummary, [$startdate, $endDate, $startdate, $endDate]);

		if (empty($summaryData)) {
			return [];
		}

		// 2. AMBIL DETAIL MENGGUNAKAN HELPER BARU
		// Kumpulkan semua itemnum yang ditemukan
		$itemNums = array_column($summaryData, 'itemnum');
		
		// Panggil helper untuk mengambil detail WO dan PO sekaligus
		$details = self::fetchRecomDetails($itemNums, $startdate, $endDate);
		
		// 3. GABUNGKAN DATA & HITUNG LOGIKA 'LATE' (SCATTER CHART LOGIC)
		$formattedResult = [];
		
		foreach ($summaryData as $row) {
			$itemnum = $row['itemnum'];
			
			// Ambil detail WO dan PO dari hasil helper
			$workOrders = $details['wos'][$itemnum] ?? [];
			$purchaseOrders = $details['pos'][$itemnum] ?? [];

			// Hitung Keterlambatan per Work Order (Logika Scatter Chart)
			// Bandingkan Target Start WO dengan Estimasi Kedatangan PO Tercepat
			
			// Cari tanggal kedatangan PO paling awal untuk item ini
			$earliestArrival = null;
			foreach ($purchaseOrders as $po) {
				if (!empty($po['estdatearrival'])) {
					$arrivalDate = new DateTime($po['estdatearrival']);
					if ($earliestArrival === null || $arrivalDate < $earliestArrival) {
						$earliestArrival = $arrivalDate;
					}
				}
			}

			// Loop setiap WO untuk menentukan status Late
			foreach ($workOrders as &$wo) {
				$wo['late'] = "Unknown";
				$wo['lateDays'] = 0;

				if (!empty($wo['targstartdate']) && $earliestArrival) {
					$targDate = new DateTime($wo['targstartdate']);
					// Reset waktu ke 00:00:00 agar adil
					$targDate->setTime(0,0,0);
					$earliestArrival->setTime(0,0,0);

					if ($targDate < $earliestArrival) {
						$wo['late'] = "Yes"; // Butuh sebelum barang datang
						$wo['lateDays'] = $earliestArrival->diff($targDate)->days;
					} else {
						$wo['late'] = "No";
					}
				}
			}
			unset($wo); // Hapus referensi

			// Hitung Rekomendasi Order
			$gap = $row['quantity'] - ($row['curbal'] + $row['stock_on_the_way']);
			$recomQty = $gap > 0 ? $gap : 0;
			
			$justification = $recomQty > 0 
				? "Forecasted demand exceeds available stock." 
				: "Stock sufficient.";

			// Susun Struktur Akhir
			$formattedResult[] = [
				'itemnum' => $itemnum,
				'itemdesc' => $row['itemdesc'],
				'quantity' => (float)$row['quantity'],
				'orderunit' => $row['issueunit'],
				'workorders' => $workOrders, // Detail WO lengkap untuk Scatter Chart
				'stockOnTheWay' => [
					'quantity' => (float)$row['stock_on_the_way'],
					'po' => $purchaseOrders // Detail PO lengkap
				],
				'currentbalance' => [
					'curbal' => (float)$row['curbal'],
					'avgcost' => (float)$row['avgcost']
				],
				'recomendedOrderQty' => (float)$recomQty,
				'recomendedJustification' => $justification
			];
		}

		return $formattedResult;
	}


	private static function fetchRecomDetails(array $itemNums, $startDate, $endDate)
	{
		if (empty($itemNums)) {
			return ['wos' => [], 'pos' => []];
		}

		// Siapkan placeholder untuk klausa IN (?,?,?)
		$placeholders = rtrim(str_repeat('?,', count($itemNums)), ',');

		
		
		// --- AMBIL DETAIL WORK ORDER ---
		// Parameter: startDate, endDate, ...itemNums
		$woParams = array_merge([$startDate, $endDate], $itemNums);
		
		$sqlWO = "
			SELECT 
				wop.itemnum, 
				wo.wonum, 
				wo.description as wodesc, 
				wo.targstartdate, 
				wop.quantity as reservedqty
			FROM woplanmaterial wop
			JOIN workorder wo ON wop.wonum = wo.wonum
			WHERE wo.targstartdate >= ? AND wo.targstartdate <= ?
			AND wop.itemnum IN ($placeholders)
			ORDER BY wo.targstartdate ASC
		";

		$woRaw = Set::fetchAll($sqlWO, $woParams);
		
		// --- AMBIL DETAIL PURCHASE ORDER ---
		// Parameter: startDate, endDate, ...itemNums
		$poParams = array_merge([$startDate, $endDate], $itemNums);

		// Subquery untuk receivedqty agar akurat per line
		$sqlPO = "
			SELECT 
				pl.itemnum, 
				pl.ponum, 
				pl.orderqty, 
				COALESCE((SELECT SUM(quantity) FROM matrectrans mrt WHERE mrt.ponum = pl.ponum AND mrt.polinenum = pl.polinenum), 0) as receivedqty,
				po.orderdate, 
				po.estdatearrival
			FROM poline pl
			JOIN po ON pl.ponum = po.ponum
			WHERE po.status = 'SENT' 
			AND po.orderdate >= ? AND po.orderdate <= ?
			AND pl.receiptstatus != 'COMPLETE'
			AND pl.itemnum IN ($placeholders)
			ORDER BY po.estdatearrival ASC
		";

		$poRaw = Set::fetchAll($sqlPO, $poParams);

		// --- GROUPING DI PHP ---
		// Ubah array datar menjadi array asosiatif: $result['ITEM01'] = [list of WOs]
		
		$groupedWOs = [];
		foreach ($woRaw as $row) {
			$groupedWOs[$row['itemnum']][] = [
				'wonum' => $row['wonum'],
				'wodesc' => $row['wodesc'],
				'targstartdate' => $row['targstartdate'],
				'reservedqty' => (float)$row['reservedqty']
			];
		}

		$groupedPOs = [];
		foreach ($poRaw as $row) {
			$remaining = (float)$row['orderqty'] - (float)$row['receivedqty'];
			//if ($remaining > 0) {
				$groupedPOs[$row['itemnum']][] = [
					'ponum' => $row['ponum'],
					'orderqty' => (float)$row['orderqty'],
					'remainingqty' => $remaining,
					'orderdate' => $row['orderdate'],
					'estdatearrival' => $row['estdatearrival']
				];
			//}
		}
	
		return [
			'wos' => $groupedWOs,
			'pos' => $groupedPOs
		];
	}
	public static function assetYtdCostChart() {
		$limit = $_POST["assetYtdCostChart"];
		$startdate =  $_POST["startdate"];
		$enddate =  $_POST["enddate"];
		$where = "";
		$set = new Set();
		$assetSet = $set->useTransactionManager()->MboSetRemote("assets a");
		
		if (!empty($startdate) && !empty($enddate)) {
			$where = "w.reportdate >= '$startdate' AND w.reportdate < '$enddate'";
			$assetSet = $assetSet->setWhere($where);
		}
		
		$assetSet = $assetSet->setRelationship(" workorder w", "w.assetnum = a.assetnum")
			->group("a.assetnum, a.description, a.purchaseprice, a.rollupcost HAVING SUM(w.actualcost) > 0")
			->order("purchaseprice")
			//->limit($limit)
			->MboRemote("a.assetnum")
			->MboRemote("a.description")
			->MboRemote("a.purchaseprice")
			->MboRemote("SUM(w.actualcost) AS ytdcost")
			->MboRemote("a.rollupcost AS rollupcost")
			->getMboSet();
		

		$labels = [];
		$ytdcosts  = [];
		$rollupcosts = [];
		$purchaseprices = [];
		$total_ytdcost = 0;
		$total_purchaseprice = 0;
		for ($i = 0; $i < $assetSet->counts(); $i++) {
			$row = $assetSet->getMbo();
			$assetnum = $row->getString("assetnum");
			$desc = $row->getString("description");
			$label = $assetnum . " - " . substr($desc, 0, 20); // truncate jika panjang
			$labels[] = $label;
			$ytdcost = (float)$row->getString("ytdcost");
			$ytdcosts [] = $ytdcost;
			$total_ytdcost = $ytdcost+$total_ytdcost;

			$rollupcosts [] = (float)$row->getString("rollupcost");
			$purch_price = (float) $row->getString("purchaseprice");
			$total_purchaseprice = $total_purchaseprice+$purch_price;
			$purchaseprices[] = $purch_price;
		}

		$res = new stdClass;
		$res->labels = $labels;
		$res->ytdcosts  = $ytdcosts ;
		$res->total_ytdcost  = $total_ytdcost ;
		$res->rollupcosts  = $rollupcosts ;
		$res->purchaseprices = $purchaseprices;
		$res->total_purchaseprice = $total_purchaseprice;
		Set::throws($res);
	}
	 
	public static function assetByModelOrManufacturerChart() {
        $conn = DB::getMXConnection();
        if (!$conn) {
            error_log("Connection failed");
            return null;
        }
		$startdate = $_POST['startdate'];
		$enddate = $_POST['enddate'];
		
        $query = "
            SELECT
                a.assetnum,
                a.model,
                ap.purchaseprice AS total_investment,
				ap.rollupcost,
                COALESCE(mc.total_maintenance_cost, 0) AS total_maintenance_cost
            FROM
                assets a
            LEFT JOIN
                (SELECT assetnum, purchaseprice, rollupcost FROM assets) ap ON a.assetnum = ap.assetnum
            LEFT JOIN
                (SELECT assetnum, SUM(actualcost) AS total_maintenance_cost FROM workorder WHERE
			workorder.reportdate >= '$startdate' AND workorder.reportdate <= '$enddate'  GROUP BY assetnum) mc ON a.assetnum = mc.assetnum
						ORDER BY a.model, a.assetnum;  
					";
			//echo $query;
      	  $result = $conn->query($query);

			if (!$result) {
				error_log("Query failed: " . $conn->error);
				$conn->close();
				return null;
			}

			$assets = []; // Keyed by assetnum
			$models = []; // Unique models
			$data = [];  // Used for chart series data
			// Initialize variables to track max values
			$maxMaintenanceCost = 0;
			$maxPurchasePrice = 0;
			while ($row = $result->fetch_assoc()) {
				$assetnum = $row['assetnum'];

				$assets[$assetnum] = [
					'assetnum' => $assetnum,
					'model' => $row['model'] ?: "UNKNOWN",
					'total_investment' => (float)$row['total_investment'],
					'rollupcost' => (float)$row['rollupcost'],
					'total_maintenance_cost' => (float)$row['total_maintenance_cost']
				];

				if(!in_array($assets[$assetnum]['model'], $models)) {
					$models[] = $assets[$assetnum]['model'];
				}

			$data[] = [
					(float)$row['total_investment'],
					(float)$row['total_maintenance_cost'],
					(float)$row['rollupcost'],
					$assetnum,
					$row['model'] ?: "UNKNOWN",

				];
				// Track max values
			$maxMaintenanceCost = max($maxMaintenanceCost, (float)$row['total_maintenance_cost']);
			$maxPurchasePrice = max($maxPurchasePrice, (float)$row['total_investment']);
			}
		
			$result->free();
			$conn->close();

			$response = [
				'assets' => $assets,
				'models' => $models,  // Kirim daftar model yang unik
				'data' => $data,
				'maxMaintenanceCost' => $maxMaintenanceCost,
				'maxPurchasePrice' => $maxPurchasePrice
			];

			header('Content-Type: application/json');
			echo json_encode($response);
   	}
	
	public static function assetPerLocationChart() {
        $conn = DB::getMXConnection(); // Mendapatkan koneksi database
		$startdate = $_POST["startdate"];
		$enddate = $_POST["enddate"];
        if (!$conn) {
            // Tangani kesalahan koneksi, misalnya:
            error_log("Koneksi database gagal!");
            return null; // Atau lempar exception
        }

            $query = "
			SELECT
				ac.locnum,
				ac.total_assets,
				ap.rollupcost,
				SUM(ap.purchaseprice) AS total_investment,
				SUM(COALESCE(mc.total_maintenance_cost, 0)) AS total_maintenance_cost
			FROM
				(SELECT a.locnum, COUNT(a.assetnum) AS total_assets FROM assets a GROUP BY a.locnum) ac
			LEFT JOIN
				assets a ON ac.locnum = a.locnum
			LEFT JOIN
				(SELECT a.assetnum, a.purchaseprice, a.rollupcost FROM assets a) ap ON a.assetnum = ap.assetnum
			LEFT JOIN
				(
					SELECT wo.assetnum, SUM(wo.actualcost) AS total_maintenance_cost
					FROM workorder wo
					WHERE wo.reportdate >= '$startdate' AND wo.reportdate <= '$enddate'  
					GROUP BY wo.assetnum
				) mc ON a.assetnum = mc.assetnum
			GROUP BY
				ac.locnum, ac.total_assets
			ORDER BY
				ac.locnum;
		";

        $result = $conn->query($query);

        if (!$result) {
            // Tangani kesalahan query, misalnya:
            error_log("Query gagal: " . $conn->error);
            $conn->close();
            return null; // Atau lempar exception
        }

        $labels = [];
        $totals = []; // Ini sebenarnya jumlah asset per lokasinya.

        $data = []; // Array untuk menampung semua data termasuk investasi dan maintenance

        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['locnum'] ?: "UNKNOWN";
            $totals[] = (int)$row['total_assets']; // Jumlah asset
            $data[] = [
                'locnum' => $row['locnum'] ?: "UNKNOWN",
                'total_assets' => (int)$row['total_assets'],
                'total_investment' => (float)$row['total_investment'],
				'rollupcost' => (float)$row['rollupcost'],
                'total_maintenance_cost' => (float)$row['total_maintenance_cost']
            ];
        }

        $result->free();
        $conn->close();

        $res = new stdClass();
        $res->labels = $labels;  // Lokasi
        $res->totals = $totals;  // Jumlah Aset per Lokasi

        $res->data = $data; // Semua data lokasi, jumlah aset, investasi, dan maintenance
       
        header('Content-Type: application/json');
        echo json_encode($res); // Mengirim response sebagai JSON

    }

	public static function chart_moving_average_forecasting() {
		$year = $_POST['chart_moving_average_forecasting'];
		$filterType = $_POST['filter_type'];     // 'item' atau 'itemgroup'
		$filterValue = $_POST['filter_value'];   // kode item atau itemgroup
		$windowSize = 6; // jumlah bulan untuk moving average
		
		$set = new Set();
		
		$itemGroups = [];
		$itemGroupSet = $set->MboSetRemote("item")        
			->MboRemote("itemgroup")
			->getMboSet("distinct");
			
		for ($i = 0; $i < $itemGroupSet->counts(); $i++) {
			$row = $itemGroupSet->getMbo();
			$itemGroups[] = $row->getString("itemgroup");
		}
		
		$set = new Set();
		$where = "YEAR(m.enterdate) = '$year' AND m.transtype = 'ISSUE'";

		if ($filterType === 'item') {
			$where .= " AND m.itemnum = '$filterValue'";
		} elseif ($filterType === 'itemgroup') {
			$where .= " AND i.itemgroup = '$filterValue'";
		}

		$issueSet = $set->MboSetRemote("matusetrans m")
			->setRelationship("item i", "m.itemnum = i.itemnum")
			->setWhere($where)
			->group("MONTH(m.enterdate)")
			->order("MONTH(m.enterdate)")
			->MboRemote("MONTH(m.enterdate) as bulan")
			->MboRemote("SUM(m.quantity) as total_issue")
			->getMboSet();

		$labels = [];
		$historicalData = [];
		$movingAvgForecast = [];

		for ($i = 0; $i < $issueSet->counts(); $i++) {
			$row = $issueSet->getMbo();
			$bulan = (int)$row->getString("bulan");
			$qty = abs((int)$row->getString("total_issue"));

			$labels[] = date('M', mktime(0, 0, 0, $bulan, 1));
			$historicalData[] = $qty;
		}

		// Moving average calculation
		$n = count($historicalData);
		for ($i = 0; $i < $windowSize; $i++) {
			if ($n >= $windowSize) {
				$lastData = array_slice($historicalData, -$windowSize);
				$avg = round(array_sum($lastData) / $windowSize);
			} else {
				$avg = 0;
			}

			$movingAvgForecast[] = $avg;
			$labels[] = date('M', mktime(0, 0, 0, $n + $i + 1, 1));
			$historicalData[] = null; // to separate visually in chart
		}

		$output = [
			"itemGroups" => $itemGroups,
			"labels" => $labels,
			"series" => [
				[
					"name" => "Historical Usage",
					"type" => "line",
					"data" => $historicalData
				],
				[
					"name" => "Prediction Usage",
					"type" => "line",
					"data" => array_merge(array_fill(0, $n, null), $movingAvgForecast)
				]
			]
		];

		Set::throws($output);
	}

	public static function chart_forecasting() {
		
		
		
		$year = $_POST['chart_forecasting'];
		$filterType = $_POST['filter_type'];     // 'item' atau 'itemgroup'
		$filterValue = $_POST['filter_value'];   // kode item atau itemgroup
		
		$set = new Set();
		
		$itemGroups = [];
		$itemGroupSet = $set->MboSetRemote("item")        
			->MboRemote("itemgroup")
			->getMboSet("distinct");
			
		for ($i = 0; $i < $itemGroupSet->counts(); $i++) {
			$row = $itemGroupSet->getMbo();
			$itemGroups[] = $row->getString("itemgroup");
		}
		
		$set = new Set();
		$where = "YEAR(m.enterdate) = '$year' AND m.transtype = 'ISSUE'";

		// Tambahkan filter berdasarkan tipe
		if ($filterType === 'item') {
			$where .= " AND m.itemnum = '$filterValue'";
		} elseif ($filterType === 'itemgroup') {
			$where .= " AND i.itemgroup = '$filterValue'";
		}
		
		$issueSet = $set->MboSetRemote("matusetrans m")
			->setRelationship("item i", "m.itemnum = i.itemnum")
			->setWhere($where)
			->group("MONTH(m.enterdate)")
			->order("MONTH(m.enterdate)")
			->MboRemote("MONTH(m.enterdate) as bulan")
			->MboRemote("SUM(m.quantity) as total_issue")
			->getMboSet();

		// Salin isi issueSet ke array agar bisa diproses ulang tanpa reset
		$rows = [];
		for ($i = 0; $i < $issueSet->counts(); $i++) {
			$row = $issueSet->getMbo();
			$rows[] = [
				"bulan" => (int)$row->getString("bulan"),
				"total_issue" => abs((int)$row->getString("total_issue"))
			];
		}

		// Siapkan data grafik
		$labels = [];
		$historicalData = [];
		$prediksiData = [];

		$jumlahBulan = count($rows);
		$totalQty = 0;
		$bulanAwal = isset($rows[0]) ? $rows[0]['bulan'] : 1;

		foreach ($rows as $row) {
			$labels[] = date('M', mktime(0, 0, 0, $row['bulan'], 1));
			$historicalData[] = $row['total_issue'];
			$totalQty += $row['total_issue'];
		}

		// Prediksi 6 bulan ke depan
		$forecastQty = $jumlahBulan > 0 ? round($totalQty / $jumlahBulan) : 0;

		for ($i = 1; $i <= 6; $i++) {
			$bulanPrediksi = $bulanAwal + $jumlahBulan + $i - 1;
			$labels[] = date('M', mktime(0, 0, 0, $bulanPrediksi, 1));
			$prediksiData[] = $forecastQty;
		}

		// Output data untuk chart
		$output = [
			"itemGroups" => $itemGroups,
			"labels" => $labels,
			"series" => [
				[
					"name" => "Historical Usage",
					"type" => "line",
					"data" => array_merge($historicalData, array_fill(0, 3, null))
				],
				[
					"name" => "Prediction Usage ",
					"type" => "line",
					"data" => array_merge(array_fill(0, count($historicalData), null), $prediksiData)
				]
			]
		];

		Set::throws($output);
	}

	public static function chart_inventoryValue(){
		$year = $_POST['chart_inventoryValue'];
		$labels = [];
		$data = [];

		$set = new Set();
		$result = $set->MboSetRemote("(
		select fl.itemnum, it.itemgroup, 
		fl.unitcost, fl.quantity, fl.warehouse, (fl.unitcost*quantity) as total_value
		from invfifolifocost fl 
		left join item it on fl.itemnum = it.itemnum ) a ")
		->group("a.itemgroup")
		->order("total_value DESC")
		->MboRemote("COALESCE(itemgroup, 'UNKNOWN') AS itemgroup")
		->MboRemote("sum(total_value) as total_value")
		->getMboSet();
		
		$counts = $result->counts();
		for ($i = 0; $i < $counts; $i++) {
			$row = $result->getMbo();
			$group = $row->getString("itemgroup");
			$value = (float)$row->getString("total_value");

			$labels[] = $group;
			$data[] = [
				'name' => $group,
				'value' => $value
			];
		}
		
		$response = new stdClass();
		$response->labels = $labels;
		$response->data = $data;
		Set::throws($response);
	}
	
	public static function chart_slowMoving(){
		// Validate input year
		$deadStockThresholdMonths = 24;
		$slowMovingQtyThreshold = 3;
		$slowMovingPercentThreshold = 1;
		
		$fastMovingQtyThreshold = 50;     // Example: issued >= 50 units in the year
		$fastMovingPercentThreshold = 100; // Example: issued >= 100% of current stock in the year (turnover)
		
		$year = isset($_POST['chart_slowMoving']) ? (int)$_POST['chart_slowMoving'] : date('Y');
		if (!filter_var($year, FILTER_VALIDATE_INT) || $year < 1900 || $year > 2100) {
			$year = date('Y'); // Default or handle error
		}

		$startDate = $year . '-01-01';
		$endDate = $year . '-12-31';
		$deadStockDateThreshold = date('Y-m-d', strtotime("-{$deadStockThresholdMonths} months"));

		$conn = DB::getMXConnection();

		$sql = "
			WITH ItemMovementStatus AS (
				SELECT
					v.itemnum,
					v.conditiontype,
					v.warehouse,
					v.racknum,
					v.curbal,
					v.avgcost,
					v.curbal * v.avgcost AS stock_value,
					mu_last.last_issue_date,
					COALESCE(mu_period.total_issued_period, 0) AS total_issued_period,
					CASE
						-- KASUS 1: DEAD STOCK
						WHEN mu_last.last_issue_date IS NULL OR mu_last.last_issue_date < '{$deadStockDateThreshold}'
						THEN 'Dead Stock'

						-- KASUS 2: SLOW MOVING
						-- Only evaluated if NOT Dead Stock
						WHEN COALESCE(mu_period.total_issued_period, 0) < {$slowMovingQtyThreshold}
							OR (v.curbal > 0 AND COALESCE(mu_period.total_issued_period, 0) < (v.curbal * {$slowMovingPercentThreshold} / 100.0))
						THEN 'Slow Moving'

						-- KASUS 3: FAST MOVING  -- ADDED
						-- Only evaluated if NOT Dead Stock and NOT Slow Moving
						WHEN COALESCE(mu_period.total_issued_period, 0) >= {$fastMovingQtyThreshold}
							OR (v.curbal > 0 AND COALESCE(mu_period.total_issued_period, 0) >= (v.curbal * {$fastMovingPercentThreshold} / 100.0))
						THEN 'Fast Moving'
						-- KASUS 4: NORMAL MOVING -- Fallback for items that don't fit above
						ELSE 'Normal Moving'
					END AS movement_status
				FROM
					v_invbalance v
				LEFT JOIN (
					SELECT itemnum, MAX(actualdate) AS last_issue_date FROM matusetrans WHERE actualdate >= '{$startDate}' AND actualdate <= '{$endDate}' 
					GROUP BY itemnum 
				) mu_last ON v.itemnum = mu_last.itemnum
				LEFT JOIN (
					SELECT itemnum, SUM(ABS(quantity)) AS total_issued_period FROM matusetrans WHERE actualdate >= '{$startDate}' AND actualdate <= '{$endDate}' GROUP BY itemnum
				) mu_period ON v.itemnum = mu_period.itemnum
				WHERE v.curbal > 0
			)
			-- Select all columns from the CTE for the detailed table data
			SELECT
				ItemMovementStatus.itemnum,
				description,
				conditiontype,
				warehouse,
				racknum,
				curbal,
				avgcost,
				stock_value,
				last_issue_date,
				total_issued_period,
				movement_status
			FROM ItemMovementStatus
			LEFT JOIN item 
			on item.itemnum = ItemMovementStatus.itemnum
			ORDER BY warehouse, movement_status, itemnum;
		";

		$result = $conn->query($sql);

		$warehouses = [];
		$detailedStatusData = [];
		$overallTotals = [
			'Dead Stock' => ['value' => 0, 'count' => 0],
			'Slow Moving' => ['value' => 0, 'count' => 0],
			'Fast Moving' => ['value' => 0, 'count' => 0], // ADDED
			'Normal Moving' => ['value' => 0, 'count' => 0],
			'Total Inventory' => ['value' => 0, 'count' => 0]
		];
		$itemDetails = [];

		if ($result) {
			while ($row = $result->fetch_assoc()) {
				$itemDetails[] = $row;

				// --- Start: Aggregation for Chart and Statement ---
				$warehouse = $row['warehouse'];
				$status = $row['movement_status']; // Get the status from the query result
				$value = (float)$row['stock_value'];

				if (!in_array($warehouse, $warehouses)) {
					$warehouses[] = $warehouse;
					// Initialize all possible statuses for the warehouse
					$detailedStatusData[$warehouse] = [
						'Dead Stock' => ['value' => 0, 'count' => 0],
						'Slow Moving' => ['value' => 0, 'count' => 0],
						'Fast Moving' => ['value' => 0, 'count' => 0], // ADDED
						'Normal Moving' => ['value' => 0, 'count' => 0]
					];
				}

				// Store value and increment count based on the status from the row
				if (isset($detailedStatusData[$warehouse][$status])) { // Ensure status is valid
					$detailedStatusData[$warehouse][$status]['value'] += $value;
					$detailedStatusData[$warehouse][$status]['count']++;
				} else {
					// Handle unexpected status if necessary
					error_log("Unknown movement status encountered: " . $status);
				}


				// Update overall totals (Ensure status is valid)
				if (isset($overallTotals[$status])) {
					$overallTotals[$status]['value'] += $value;
					$overallTotals[$status]['count']++;
				}
				$overallTotals['Total Inventory']['value'] += $value;
				$overallTotals['Total Inventory']['count']++;
				// --- End: Aggregation ---
			}
			$result->free();
		} else {
			header('Content-Type: application/json');
			echo json_encode(['error' => $conn->error, 'sql' => $sql]);
			$conn->close();
			exit;
		}

		sort($warehouses);

		// Prepare seriesData for ECharts (Uses aggregated data)
		$seriesData = [];
		// Define the order of statuses, INCLUDING 'Fast Moving'
		// The order here should ideally match the order in the JS color palette
		$statuses = ['Dead Stock', 'Slow Moving', 'Fast Moving', 'Normal Moving']; // ADDED 'Fast Moving'

		$warehouseTotals = []; // Still needed for JS tooltip percentages
		foreach ($warehouses as $wh) {
			// Sum up values for all statuses in this warehouse
			$warehouseTotals[$wh] = array_sum(array_column($detailedStatusData[$wh], 'value'));
		}


		foreach ($statuses as $status) {
			$dataValues = [];
			foreach ($warehouses as $warehouse) {
				// Get the value for this status and warehouse
				$value = isset($detailedStatusData[$warehouse][$status]) ? $detailedStatusData[$warehouse][$status]['value'] : 0;
				$dataValues[] = $value;
			}
			$seriesData[] = [
				'name' => $status,
				'type' => 'bar',
				'stack' => 'total', // Stack bars together per warehouse
				'emphasis' => ['focus' => 'series'],
				'data' => $dataValues,
			];
    	}
	
		$zoraAI = [
			'title' => 'Inventory Movement Status per Warehouse (' . $year . ')',
			'year' => $year,
			'warehouses' => $warehouses,
			'APACHE_ECHART_series' => $seriesData,
			'warehouseTotals' => $warehouseTotals,
			'detailedStatusData' => $detailedStatusData,
			'overallTotals' => $overallTotals,
			'deadStockThresholdMonths' => $deadStockThresholdMonths,
			'slowMovingQtyThreshold' => $slowMovingQtyThreshold,
			'slowMovingPercentThreshold' => $slowMovingPercentThreshold,
			'fastMovingQtyThreshold' => $fastMovingQtyThreshold, // ADDED
			'fastMovingPercentThreshold' => $fastMovingPercentThreshold, // ADDED
			'dataTable' => $itemDetails
		];
	
	
		$summary = null;
		$zora_data = [
			'title' => 'Inventory Movement Status per Warehouse (' . $year . ')',
			'year' => $year,
			'warehouses' => $warehouses,
			'warehouseTotals' => $warehouseTotals,
			'detailedStatusData' => $detailedStatusData,
			'overallTotals' => $overallTotals,
			'deadStockThresholdMonths' => $deadStockThresholdMonths,
			'slowMovingQtyThreshold' => $slowMovingQtyThreshold,
			'slowMovingPercentThreshold' => $slowMovingPercentThreshold,
			'fastMovingQtyThreshold' => $fastMovingQtyThreshold, // ADDED
			'fastMovingPercentThreshold' => $fastMovingPercentThreshold, // ADDED
			'dataTable' => $itemDetails
		];
		
	
		$outputData = [
			'title' => 'Inventory Movement Status per Warehouse (' . $year . ')',
			'year' => $year,
			'warehouses' => $warehouses,
			'series' => $seriesData,
			'warehouseTotals' => $warehouseTotals,
			'detailedStatusData' => $detailedStatusData,
			'overallTotals' => $overallTotals,
			'deadStockThresholdMonths' => $deadStockThresholdMonths,
			'slowMovingQtyThreshold' => $slowMovingQtyThreshold,
			'slowMovingPercentThreshold' => $slowMovingPercentThreshold,
			'fastMovingQtyThreshold' => $fastMovingQtyThreshold, // ADDED
			'fastMovingPercentThreshold' => $fastMovingPercentThreshold, // ADDED
			'dataTable' => $itemDetails /*,
			'summary' => $summary */
			
		];
	
		header('Content-Type: application/json');
		echo json_encode($outputData);

		$conn->close();
	}

	public static function chart_monthlyaggregate(){
		$year = $_POST["chart_monthlyaggregate"];
		$set = new Set();
		$aggSet = $set->MboSetRemote("matusefifolifo")
			->setWhere("YEAR(enterdate) = '$year'")
			->group("MONTH(enterdate), warehouse")
			->order("bulan, warehouse")
			->MboRemote("MONTH(enterdate) AS bulan")
			->MboRemote("warehouse")
			->MboRemote("SUM(issueqty) AS total_qty")
			->MboRemote("SUM(linecost) AS line_cost")
			->getMboSet();

		$counts = $aggSet->counts();
		$bulans = [];  // Inisialisasi $bulans
		$warehouses = [];
		$seriesData = [];
		$nama_bulan = [1=>'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

		for ($i = 1; $i <= 12; $i++) {
			$bulans[$i] = $nama_bulan[$i];
		}

		$bulan = []; // Inisialisasi $bulan sebagai array
		for($i=0; $i<$counts; $i++){
			$agg = $aggSet->getMbo();
			$warehouse = $agg->getString("warehouse");
			$total_qty = $agg->getString("total_qty");
			$line_cost = $agg->getString("line_cost");
			if($total_qty<0) $total_qty *=-1;
			if($line_cost<0) $line_cost *=-1;
			$bulan_index = $agg->getString("bulan"); // Get bulan index

			// Convert bulan index to bulan name
			$bulan_nama = $nama_bulan[$bulan_index];

			//$bulans[] = $nama_bulan[$bulan];

			if (!isset($bulans[$bulan_index])) {
				$bulans[$bulan_index] = $nama_bulan[$bulan_index];
			}

			// Menambahkan warehouse baru ke array jika belum ada
			if (!in_array($warehouse, $warehouses)) {
				$warehouses[] = $warehouse;
			}
			// Menyimpan data stok per warehouse per bulan
			$seriesData[$warehouse][$bulan_index] = [
				'qty' => (int)$total_qty,
				'linecost' => round((float)$line_cost, 3)
			];
		}
		if (is_array($bulan) && !empty($bulan)) {
			ksort($bulan);  // Pastikan $bulan adalah array dan tidak kosong
		}
		$bulans = array_values($bulans);  // Ambil nilai-nilai dari $bulans

		$ss = new stdClass;

		$totalLineCostPerMonth = [];
		foreach ($seriesData as $warehouse => $dataPerBulan) {
			foreach ($dataPerBulan as $bulan => $val) {
				$totalLineCostPerMonth[$bulan] = ($totalLineCostPerMonth[$bulan] ?? 0) + $val['linecost'];
			}
		}

		$avg = count($totalLineCostPerMonth) > 0 ? array_sum($totalLineCostPerMonth) / count($totalLineCostPerMonth) : 0;
		$ss->averageCostPerMonth = round($avg, 3);
		$ss->warehouses = $warehouses;
		$ss->bulans = $bulans;
		$ss->seriesData  = $seriesData ;
		$ss->year = $year;
		
		$totalItems = (new Set())->useTransactionManager()->MboSetRemote("item")->MboRemote("count(*) as totalItems")->getMboValue("totalItems");
		$ss->totalItems = $totalItems;

		$totalLineCost = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->MboRemote("(quantity*unitcost) as linecost")->getMboSet();
		$totalInventoryValue = 0;
		while($row = $totalLineCost->fetch()){
			$linecost = $row["linecost"];
			$totalInventoryValue += $linecost;
		}
		$ss->totalInventoryValue = $totalInventoryValue;
		Set::throws($ss);  // Pastikan ini mengembalikan data dalam format JSON
	}

	public static function chart_toplevel_safetystock(){
		$conn = DB::getMXConnection();

		// Query untuk ambil avgcost per itemnum
		$avgCostSql = "SELECT itemnum, AVG(quantity * unitcost) AS avgcost 
					   FROM invfifolifocost 
					   GROUP BY itemnum";
		$avgCostRes = $conn->query($avgCostSql);

		$avgCostMap = [];
		while($row = $avgCostRes->fetch_assoc()){
			$avgCostMap[$row['itemnum']] = (float)$row['avgcost'];
		}

		// Query utama
		$sql = "SELECT b.itemnum, it.description AS itemname, 
					   SUM(b.curbal) AS quantity, 
					   iv.safetystock, 
					   SUM(b.curbal) - iv.safetystock AS selisih
				FROM v_invbalance b
				LEFT JOIN inventory iv ON b.itemnum = iv.itemnum
				LEFT JOIN item it ON b.itemnum = it.itemnum
				WHERE iv.safetystock > 1
				GROUP BY b.itemnum, it.description, iv.safetystock
				HAVING SUM(b.curbal) < iv.safetystock  
				ORDER BY selisih ASC";
				//LIMIT 10";
		
		$res = $conn->query($sql);

		$labels = [];
		$qty = [];
		$minStock = [];
		$lineCosts = [];

		while($row = $res->fetch_assoc()){
			$itemnum = $row['itemnum'];
			$avgCost = $avgCostMap[$itemnum] ?? 0;

			$qtyVal = (int)$row['quantity'];
			$minVal = (int)$row['safetystock'];

			$lineCosts[] = [
				'itemnum' => $itemnum,
				'avgcost' => $avgCost,
				'quantity' => $qtyVal,
				'safetystock' => $minVal,
				'selisih' => $qtyVal - $minVal,
				'linecost_current' => $qtyVal * $avgCost,
				'linecost_min' => $minVal * $avgCost,
				'gapcost' => ($minVal - $qtyVal) > 0 ? ($minVal - $qtyVal) * $avgCost : 0
			];

			$labels[] = $row['itemname'];
			$qty[] = $qtyVal;
			$minStock[] = $minVal;
		}

		$ss = new stdClass;
		$ss->labels = $labels;
		$ss->qty = $qty;
		$ss->minStock = $minStock;
		$ss->costs = $lineCosts;

		Set::throws($ss);
	}
	public static function chart_itemgroup(){
		$set = new Set();
		$invSet = $set->MboSetRemote("v_invbalance")->setRelationship("item", "v_invbalance.itemnum = item.itemnum")->group("item.itemgroup, v_invbalance.warehouse")
		->MboRemote("item.itemgroup")->MboRemote("warehouse")->MboRemote("sum(v_invbalance.curbal) as quantity")
		->getMboSet();
		
		$data = [];
		$counts = $invSet->counts();
		for($i=0; $i<$counts; $i++){
			$inv = $invSet->getMbo();
			$itemgroup = $inv->getString("itemgroup");
			$warehouse = $inv->getString("warehouse");
			$quantity = $inv->getString("quantity");
			
			$data[$itemgroup][$warehouse] = $quantity;
		}
		
		Set::throws($data);
	}


	
	
}
Class ValidateTables {
	
}
	

?>