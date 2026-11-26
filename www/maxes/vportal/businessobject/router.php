
<?php
/**
 * VENDOR PORTAL API ROUTER
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../applications/businessobject/objects.php';
require_once __DIR__ . '/../../applications/businessobject/TransactionManager.php';

session_start();

// --- FIX: FORWARD AUTHORIZATION HEADER ---
// Beberapa server (Apache) tidak forward Authorization header ke PHP secara otomatis
// Kita perlu ekstrak dari berbagai kemungkinan lokasi

if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
    // Cek di Apache RewriteRule variable
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    // Cek di CGI variable
    elseif (!empty($_SERVER['HTTP_X_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['HTTP_X_AUTHORIZATION'];
    }
    // Cek di getallheaders() function (jika available)
    elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (!empty($headers['Authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
        }
    }
}

// DEBUG: Log semua headers (hapus di production)
error_log('=== DEBUG HEADERS ===');
error_log('HTTP_AUTHORIZATION: ' . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'KOSONG'));
if (function_exists('getallheaders')) {
    error_log('All headers: ' . json_encode(getallheaders()));
}

// --- KONFIGURASI DASAR & HEADERS ---

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// --- AUTOLOADER ---
spl_autoload_register(function ($className) {
    $actionFile = __DIR__ . '/actions/' . $className . '.php';
    if (file_exists($actionFile)) {
        require_once $actionFile;
        return;
    }
    $coreFile = __DIR__ . '/core/' . $className . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }
});

// --- ACTION MAP ---
$actionMap = [
    'login' => [AuthActions::class, 'login'],
    'register_vendor' => [AuthActions::class, 'register'],
    'check_token' => [AuthActions::class, 'validateToken'],
    'get_rfq_list'   => [RfqActions::class, 'getList'],
    'get_rfq_detail' => [RfqActions::class, 'getDetail'],
    'save_quotation' => [RfqActions::class, 'saveQuotation'],
    'get_rfq_attachments' => [RfqActions::class, 'getRFQAttachments'],
    'upload_attachment' => [RfqActions::class, 'upload_attachment'],
    'forgotpassword' => [AuthActions::class, 'getForgotPasswordInstruction'],
    'getVendorProfile' => [RfqActions::class, 'getVendorProfile'],
    'updateVendorInformation' => [RfqActions::class, 'updateVendorInformation'],
    'changePassword'  => [RfqActions::class, 'changePassword'],
    'uploadVendorDoc' => [RfqActions::class, 'uploadVendorDoc'],
    'get_ntp' =>  [RfqActions::class, 'get_ntp']
];

$publicActions = [
    'login',
    'register_vendor',
    'forgotpassword'
];

try {
    $request = new Request();
    $actionName = $request->get('actionname');
   
    if (!$actionName) {
        Response::error('Parameter "actionname" is required.', 400);
    }
    // --- MIDDLEWARE: Token Validation ---
    if (!in_array($actionName, $publicActions)) {
        $auth = new AuthActions();
        $userData = $auth->validateTokenFromRequest($request);
        $request->setUser($userData);
    }

    // --- DISPATCHER ---
    if (isset($actionMap[$actionName])) {
        $handler = $actionMap[$actionName];
        $controllerClass = $handler[0];
        $method = $handler[1];

        if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
            $controller = new $controllerClass();
            $controller->$method($request);
        } else {
            Response::error("Handler for action '{$actionName}' is not configured correctly.", 500);
        }
    } else {
        Response::notFound("Action '{$actionName}' not found.");
    }

} catch (Exception $e) {
    Response::error('An unexpected server error occurred: ' . $e->getMessage(), 500);
}

class Properties{
    public static function loadProperties(){
        $appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
        if(!file_exists($filePath)) {
			echo "Property File is not found: " . $filePath;
			return null;
		}
		$xml = simplexml_load_file($filePath);
		
		if($xml){
			return $xml;
		} else {
			//echo "Error on XML Parsing: " . $filePath;
			return null;
		}
    }

    public static function getContext(){
        $property = self::loadProperties();
        return (string)$property->contextPath;
    }
    public static function getHost(){
        $property = self::loadProperties();
        return (string)$property->host;
    }
    public static function getAttachFolder(){
        $property = self::loadProperties();
        return (string)$property->attachFolder;
    }
    public static function getAIServer(){
        $property = self::loadProperties();
        return (string)$property->aiServer;
    }
    public static function getDockerAIServer(){
        $property = self::loadProperties();
        return (string)$property->docker_aiServer;
    }
    public static function getClientID(){
        $property = self::loadProperties();
        return (string)$property->client_id;
    }
}
?>