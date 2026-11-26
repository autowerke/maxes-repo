<?php
ob_start();
include_once __DIR__ . '/../../../vendor/autoload.php'; 
include_once __DIR__ . '/../objects.php'; 
include_once __DIR__ . '/../TransactionManager.php'; 
require_once  __DIR__ . '/request.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');


class Network {
    public static function getClientIPSimple() {
        $ip = 'UNKNOWN';
        
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Convert IPv6 localhost ke IPv4
        if ($ip === '::1') {
            $ip = '127.0.0.1';
        }
        
        return $ip;
    }
    public static function getClientIP($trustProxyHeaders = true, $convertLocalhost = true) {
        // Jika tidak trust proxy headers, langsung return REMOTE_ADDR
        if (!$trustProxyHeaders) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            // Convert IPv6 localhost ke IPv4 localhost
            if ($convertLocalhost && $ip === '::1') {
                return '127.0.0.1';
            }
            return $ip;
        }
        
        // Daftar headers yang biasa digunakan untuk IP forwarding
        $headers = [
            'HTTP_CF_CONNECTING_IP',  // Cloudflare
            'HTTP_X_REAL_IP',         // Nginx proxy
            'HTTP_X_FORWARDED_FOR',   // Standard proxy
            'HTTP_CLIENT_IP',         // Proxy lain
            'REMOTE_ADDR'             // Fallback ke IP langsung
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle multiple IPs (biasanya di X-Forwarded-For)
                // Format: client, proxy1, proxy2
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]); // Ambil IP pertama (client IP)
                }
                
                // Convert IPv6 localhost ke IPv4 localhost
                if ($convertLocalhost && $ip === '::1') {
                    $ip = '127.0.0.1';
                }
                
                // Validasi IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
                
                // Jika validasi strict gagal, coba validasi biasa
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return null;
    }
}

class Auth {
    private static $currentUserData = null;
    
   public static function getCurrentUserData() {

        if (self::$currentUserData !== null) {
            return self::$currentUserData;
        }

        $token = self::getBearerToken();
        
        if ($token) {
            try {
                $payload = JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
                self::$currentUserData = $payload;
                return self::$currentUserData;
            } catch (Exception $e) {
                error_log("JWT Validation Failed: " . $e->getMessage());
            }
        }

        // FALLBACK: Jika tidak ada token atau JWT gagal, coba ambil dari session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (isset($_SESSION['userid'])) {
            // Buat object yang mirip dengan JWT payload
            self::$currentUserData = (object)[
                'sub' => $_SESSION['userid'],
                'username' => $_SESSION['username'] ?? $_SESSION['userid'],
                'source' => 'session' // Untuk debugging
            ];
            return self::$currentUserData;
        }

        // Jika JWT dan Session tidak ada, return null
        return null;
    }

   public static function getApiCookieOptions() {

        // dirname() akan menghapus nama file (misal: /mxs_security_router.php) jadi cuman menyisakan url path directory nya saja
        
        $path = dirname($_SERVER['SCRIPT_NAME']) . '/';

        // Pakae HTTPS?
        $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

        return [
            'path' => $path,
            'secure' => $isSecure
        ];
    }
    private static function getSecretKey() {
        // Pastikan kunci ini SAMA PERSIS dengan yang di class Login
        return '~L41ll4H41ll4**4ll4h**_MuH4mm4D_R4sul_**Allah**!~';
    }

    private static function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice to have)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * Mengambil Bearer Token dari HTTP Authorization header
     */
   private static function getBearerToken() {

        $headers = self::getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

    
        if (Request::hasParam('customHeaders')) {
            $customHeaders = Request::getParam('customHeaders');
            if (isset($customHeaders['Authorization'])) {
                $authHeader = $customHeaders['Authorization'];
                if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                    return $matches[1];
                }
            }
        }
        
        return null;
    }
  
    public static function validateToken() {
        $userData = self::getCurrentUserData();
    
        if ($userData === null) {
            Request::sendJsonResponse('error', 'Access Token not found or invalid.', ['code' => 'AUTH_FAILED']);
            exit();
        }
        return $userData;
    }
    public static function validateToken_v1() {
        $token = self::getBearerToken();

        if (!$token) {
            Request::sendJsonResponse('error', 'Access Token not found in request header.', ['code' => 'TOKEN_NOT_FOUND']);
            exit();
        }

        try {
           
            $payload = JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
     
            return $payload;
        } catch (\Firebase\JWT\ExpiredException $e) {
            Request::sendJsonResponse('error', 'Provided token has expired.', ['code' => 'TOKEN_EXPIRED']);
            exit();
        } catch (Exception $e) {
            Request::sendJsonResponse('error', 'Invalid Access Token.', ['code' => 'TOKEN_INVALID']);
            exit();
        }
    }
}

class Logout{
    public static function logoutUser(){
       
        // Cek apakah cookie-nya diterima oleh server.
        //var_dump($_COOKIE);
        // exit(); // 
         $cookiePath = Auth::getApiCookieOptions()['path'];
        if (!isset($_COOKIE['mxs_refresh_token'])) {
            Request::sendJsonResponse("success", "No active session to log out from (no cookie found).");
            exit();
        }
        $refreshToken = $_COOKIE['mxs_refresh_token'];
        $refreshTokenHash = hash('sha256', $refreshToken);

        $tokenSet = new Set();
        $tokenSet->useTransactionManager()
                    ->MboSetRemote("mxs_refreshtokens")
                    ->where("token_hash = ?", $refreshTokenHash)
                    ->delete(); 

        setcookie('mxs_refresh_token', '', time() - 3600, $cookiePath);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();

        Request::sendJsonResponse("success", "Successfully logged out and session revoked.");
    }
}
class Login{

    public static function validateLogin(){
        $secretKey = '~L41ll4H41ll4**4ll4h**_MuH4mm4D_R4sul_**Allah**!~'; 
        $accessTokenLifetime = 15 * 60; // 15 menit
        $refreshTokenLifetime = 7 * 24 * 60 * 60; // 7 hari
      


        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        try {

            $input = Request::getParam("loginInfo");
            $username = $input['user'];
            $password = $input['pass'];

            
            $userSet = (new Set())->MboSetRemote("mxsusers")
                        ->where("userid = ?", $username)
                        ->getMboSet();
            


            if($userSet->isEmpty()){
                Request::sendJsonResponse("error", "User not found.");
            }
            
            $user = $userSet->getMbo();

            $password_hash_fromDb = $user->getString("password_hash");
            
            if (!password_verify($password, $password_hash_fromDb)) {
                // Password tidak cocok
                Request::sendJsonResponse("error", "Username or Password does not match.");
            }
            // 5. Buat Payload untuk Access Token
            $issuedAt = time();
            $expire = $issuedAt + $accessTokenLifetime;
            $userId = $user->getString("userid"); // Ambil userid

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['userid'] = $userId;
            $_SESSION['username'] = $username;


            
            $payload = [
                'iat' => $issuedAt,          // Issued at: Waktu token dibuat
                'exp' => $expire,             // Expiration time
                'sub' => $userId,             // Subject: User ID, ini adalah identitas utama
                'username' => $username
            ];

            // 6. Generate Access Token
            $accessToken = JWT::encode($payload, $secretKey, 'HS256');

            // 7. Generate Refresh Token (string acak yang aman)
            $refreshToken = bin2hex(random_bytes(32));
            $refreshTokenHash = hash('sha256', $refreshToken); // Simpan hash-nya di DB, bukan token mentah
            $refreshTokenExpire = time() + $refreshTokenLifetime;

            // 8. Simpan Refresh Token ke database
            // (Asumsi kamu sudah punya tabel mxs_refreshtokens)
            $tokenSet = new Set();
            $tokenSet->useTransactionManager()
                    ->MboSetRemote("mxs_refreshtokens")
                    ->setValue("token_hash", $refreshTokenHash)
                    ->setValue("userid", $userId)
                    ->setValue("expiry_date", date('Y-m-d H:i:s', $refreshTokenExpire))
                    ->add();


            $userIP = Network::getClientIP(false);  
            $record = [
                "username" => $userId,
                "logintime" => date("Y-m-d H:i:s"),
                "token_hash" => $refreshTokenHash,
                "ipaddress" => $userIP
            ];

            $sqlToInsert = [$record];
            (new Set())->MboSetRemote("mxssession")->addStream($sqlToInsert);

            $cookieOptions = Auth::getApiCookieOptions(); 
            // 9. Kirim Refresh Token sebagai HttpOnly cookie
            setcookie(
                'mxs_refresh_token',
                $refreshToken,
                [
                    'expires' => $refreshTokenExpire,
                    'path' => $cookieOptions['path'],
                    'domain' => '', 
                    'secure' => $cookieOptions['secure'],
                    'httponly' => true, // Mencegah akses via JavaScript
                    'samesite' => 'Strict' //  CSRF Protection
                ]
            );
            
            
            // 10. Kirim Access Token sebagai respons JSON
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Login Success',
                'access_token' => $accessToken
            ]);

        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            http_response_code($statusCode);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

    }
}


?>