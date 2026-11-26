<?php


class MaxesAI {

    public static function generalAIChatStream() {

        $rawPayload = Super::getParam("generalAIChatStream");
        $payload    = json_decode($rawPayload, true);

        while (ob_get_level() > 0) ob_end_clean();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        if (empty($payload)) {
            echo "data: " . json_encode([
                "type" => "error",
                "text" => "Error: empty payload"
            ]) . "\n\n";
            flush();
            exit;
        }

        // Ambil config AI server
        $config    = Super::getConfig();
        $baseUrl   = self::is_docker()
            ? $config['docker_aiServer']
            : $config['aiServer'];

        // ⭐ Sekarang ke KG pipeline — bukan /api/chat/stream
        $aiUrl     = rtrim($baseUrl, '/') . "/api/kg/chat/stream";

        // Build payload untuk KG endpoint
        $kgPayload = [
            "userid"       => $payload['userid']      ?? '',
            "userMessage"  => $payload['userMessage'] ?? '',
            "chat_history" => $payload['chat_history'] ?? [],
            "currentURL"   => $payload['currentURL']  ?? '',
            "date_from"    => $payload['date_from']   ?? null,
            "date_to"      => $payload['date_to']     ?? null,
        ];

        $jsonData = json_encode($kgPayload);
        $apiKey   = $config['aiApiKey'] ?? 'MAXES001';

        $ch = curl_init($aiUrl);
        curl_setopt($ch, CURLOPT_POST,          true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,    $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER,    [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
            'X-API-Key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT,        300);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION,  function($ch, $chunk) {
            echo $chunk;
            flush();
            return strlen($chunk);
        });

        curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo "data: " . json_encode([
                "type" => "error",
                "text" => "Connection error: $curlError"
            ]) . "\n\n";
            flush();
        }

        exit;
    }

    // ─── Reload KG Cache (dipanggil dari admin aigraph) ───────────────────
    public static function reloadKGCache() {
        $config  = Super::getConfig();
        $baseUrl = self::is_docker()
            ? $config['docker_aiServer']
            : $config['aiServer'];

        $url    = rtrim($baseUrl, '/') . "/api/kg/reload-cache";
        $apiKey = $config['aiApiKey'] ?? 'MAXES001';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     '{}');
        curl_setopt($ch, CURLOPT_HTTPHEADER,     [
            'Content-Type: application/json',
            'X-API-Key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,        30);

        $response = curl_exec($ch);
        curl_close($ch);

        Super::sendJsonResponse("success", "Cache reloaded", json_decode($response, true));
    }

    private static function is_docker() {
        return file_exists('/.dockerenv');
    }
    
    public static function generalAIChatStream_v1() {
        // getParam dapat string JSON karena dikirim sebagai FormData
        $rawPayload = Super::getParam("generalAIChatStream");
        $payload = json_decode($rawPayload, true);

        while (ob_get_level() > 0) ob_end_clean();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        if (empty($payload)) {
            echo "data: " . json_encode(["token" => "Error: payload kosong", "done" => true]) . "\n\n";
            flush();
            exit;
        }

        $aiServerUrl = "http://mlai:8000/api/chat/stream";
        $jsonData = json_encode($payload);

        $ch = curl_init($aiServerUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: MAXES001'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) {
            echo $chunk;
            flush();
            return strlen($chunk);
        });

        curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo "data: " . json_encode(["token" => "cURL error: $curlError", "done" => true]) . "\n\n";
            flush();
        }

        exit;
    }

    public static function generalAIChat() {
        // 1. Ambil payload dari frontend JS
        $payload = Super::getParam("generalAIChat");
        
        // Jaring pengaman jika kosong
        if (empty($payload)) {
            Super::sendJsonResponse("error", "Payload kosong dari frontend.", null);
            return;
        }

        // 2. Tentukan URL API Server AI
        // PENTING: Karena PHP ada di dalam Docker network (maxnet), 
        // kita panggil service FastAPI pakai nama containernya: 'mlai'
        $aiServerUrl = "http://mlai:8000/api/chat";

        // 3. Setup cURL untuk kirim data ke FastAPI
        $ch = curl_init($aiServerUrl);
        $jsonData = json_encode($payload);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER,[
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        // Eksekusi cURL
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // 4. Handle Response dari FastAPI
        if ($httpCode == 200 && $response) {
            $aiData = json_decode($response, true);
            
            // Ambil teks balasan dari JSON Python ("reply")
            $replyText = $aiData['reply'] ?? "Maaf, AI tidak merespon format teks.";

            // 5. Kirim balasan ke Frontend JS
            // (Asumsi frontend lo merender pesan dari parameter kedua fungsi ini)
            Super::sendJsonResponse("success", $replyText, $aiData);
        } else {
            // Jika FastAPI mati atau error 500
            $errorMsg = "Gagal terhubung ke AI Engine. Code: $httpCode. Error: $curlError";
            Super::sendJsonResponse("error", $errorMsg, null);
        }
    }
}



class EnhancedAIChat {
    
    /**
     * Main chat handler dengan improved features
     * 
     * @param array $allowedFunctions Map of function_name => callable
     * @param bool $includeDebug Include SQL/path debug info in response
     */
     public static function generalAIChat($allowedFunctions = [], $includeDebug = false) {
        ignore_user_abort(true);
        set_time_limit(0); 

        $payload = Super::getParam("generalAIChat");

        // Safety check jika payload kosong
        if (!$payload) {
            Super::sendJsonResponse("error", "Empty payload received.");
            return;
        }

        if (isset($payload['action']) && $payload['action'] === 'get_history') {
            $history = self::getChatHistory();
            
            // Ambil pesan terakhir dari agent untuk ditampilkan sebagai notifikasi/message
            $lastEntry = end($history);
            $lastResponse = ($lastEntry && isset($lastEntry['agent'])) ? $lastEntry['agent'] : "History restored.";

            // Kirim balik seluruh history ke JS
            Super::sendJsonResponse("success", $lastResponse, [
                'full_history' => $history,
                'is_recovery' => true
            ]);
            return; // Stop eksekusi di sini
        }

        $userMessage = $payload["userMessage"];
        $chat_history = $payload["chat_history"] ?? [];
        
        if(Super::hasParam("allowedFunctions")) $allowedFunctions = Super::getParam("allowedFunctions");
        
        // Build request payload
        $data = [ 
            "question" => $userMessage,
            "history" => $chat_history,
            "allowed_functions" => array_keys($allowedFunctions),
            "include_debug" => $includeDebug
        ];
        
        $jsonPayload = json_encode($data);
        $endpoint = "/chat/general-knowledge"; 
        
        try {
            $resultRaw = Super::sendToML($endpoint, $jsonPayload, true);
            $data = json_decode($resultRaw, true);
            
            if ($data === null) {
                // Handle jika response bukan JSON valid
                throw new Exception("Invalid JSON response from AI service.");
            }
            
            // --- 1. HANDLE FUNCTION EXECUTION ---
            if (isset($data['type']) && $data['type'] === 'FUNCTION') {
                // ... (Kode function handling Anda tetap sama) ...
                $funcName = $data['function'];
                $params = $data['parameters'];
                
                self::logDebug("AI requested function: $funcName", $params);
                
                if (array_key_exists($funcName, $allowedFunctions)) {
                    try {
                        $methodCall = $allowedFunctions[$funcName];
                        if (!is_callable($methodCall)) throw new Exception("Function handler '$funcName' is not callable");
                        
                        $executionResult = call_user_func($methodCall, $params);
                        $feedbackMessage = "✅ Action completed: " . ($executionResult['message'] ?? 'Success');
                        
                        $chat_history[] = ['user' => $userMessage, 'agent' => "I executed the action: $funcName"];
                        $chat_history[] = ['user' => '[System Feedback]', 'agent' => $feedbackMessage];
                        self::updateChatHistory($chat_history);
                        
                        $metaData = ['function_executed' => $funcName, 'execution_result' => $executionResult['data'] ?? null];
                        Super::sendJsonResponse("success", $feedbackMessage, $metaData);
                        
                    } catch (Exception $e) {
                        self::logError("Function execution failed: $funcName", $e->getMessage());
                        Super::sendJsonResponse("error", "Failed to execute action: " . $e->getMessage());
                    }
                } else {
                    $warningMsg = "⚠️ I understand you want to run '$funcName', but I am not authorized to execute that command.";
                    Super::sendJsonResponse("success", $warningMsg);
                }
                return;
            }
            
            // --- 2. HANDLE TEXT RESPONSE ---
            if (isset($data['type']) && $data['type'] === 'TEXT') {
                $reply = $data['content'];
                
                // Append to chat history
                $chat_history[] = [
                    'user' => $userMessage,
                    'agent' => $reply
                ];
                self::updateChatHistory($chat_history);
                
                // Prepare metadata
                $metaData = [];
                if ($includeDebug) {
                    if (isset($data['debug_sql'])) $metaData['debug_sql'] = $data['debug_sql'];
                    if (isset($data['debug_path'])) $metaData['debug_path'] = $data['debug_path'];
                    if (isset($data['metadata'])) $metaData['ai_metadata'] = $data['metadata'];
                }
                
                Super::sendJsonResponse("success", $reply, $metaData);
                return;
            }

            // --- 3. HANDLE ERROR RESPONSE (BARU) ---
            // Menangkap error spesifik dari AI Logic (misal: entity tidak ditemukan, ambiguity, dll)
            if (isset($data['type']) && $data['type'] === 'ERROR') {
                
                // Ambil pesan error, cek key 'message' (prioritas) atau 'content' (fallback)
                $errorMessage = $data['message'] ?? $data['content'] ?? "Unknown AI error occurred.";
                
                self::logError("AI Logic Error", $errorMessage);
                
                // Kirim pesan error spesifik ke frontend
                Super::sendJsonResponse("error", $errorMessage);
                return;
            }
            
            // --- 4. UNRECOGNIZED RESPONSE TYPE ---
            self::logError("Unrecognized AI response type", json_encode($data));
            Super::sendJsonResponse("error", "AI returned unexpected response format: " . ($data['type'] ?? 'Unknown Type'));
            
        } catch (Exception $e) {
            self::logError("AI Chat Error", $e->getMessage());
            Super::sendJsonResponse("error", "System error: " . $e->getMessage());
        }
    }
    
    
    /**
     * Update chat history in session
     * Maintain only last 10 exchanges untuk tidak overload context
     */
    private static function updateChatHistory($history) {
        // Keep only last 10 exchanges
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }
        
        // Save to session
        $_SESSION['ai_chat_history'] = $history;
    }
    
    
    /**
     * Get current chat history dari session
     */
    public static function getChatHistory() {
        return $_SESSION['ai_chat_history'] ?? [];
    }
    
    
    /**
     * Clear chat history (reset conversation)
     */
    public static function clearChatHistory() {
        $_SESSION['ai_chat_history'] = [];
        Super::sendJsonResponse("success", "Chat history cleared");
    }
    
    
    /**
     * Debug logging
     */
    private static function logDebug($message, $data = null) {
        error_log("[AI Chat Debug] $message: " . print_r($data, true));
    }
    
    /**
     * Error logging
     */
    private static function logError($context, $error) {
        error_log("[AI Chat Error] $context: $error");
    }
}
?>