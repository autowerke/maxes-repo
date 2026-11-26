<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once("../eat.php"); 

/**
 * Class API_Auth
 * Menangani semua request terkait autentikasi.
 */
class API_Auth {
	
    public static function login() {
        // Pastikan request adalah POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Super::sendJsonResponse("error", "This action requires a POST request.");
            return;
        }
        
        // Baca raw input dari body request
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data);

        // Validasi input
        if (!isset($data->username) || !isset($data->password)) {
            Super::sendJsonResponse("error", "Username and password are required.");
            return;
        }

        $username = $data->username;
        $password_md5 = md5($data->password); // Enkripsi password yang dikirim user

        try {
            $set = new Set();
            $userSet = $set->useTransactionManager()->MboSetRemote("mxsusers m")->setRelationship("employees e", "m.personid = e.personid")
                          ->where("m.username = ? AND m.status = 'ACTIVE'", $username)
                          ->MboRemote("m.username")
                          ->MboRemote("m.password")
						  ->MboRemote("m.personid")
						  ->MboRemote("e.fullname")
                          ->getMboSet();
            
			
            if ($userSet->isEmpty()) {
                Super::sendJsonResponse("error", "Invalid username or user is not active.");
                return;
            }

            $user = $userSet->fetch();
            
            // Bandingkan password MD5 dari user dengan yang ada di database
            if ($user['password'] === $password_md5) {
                // Login Berhasil!
                $response_data = [
                    'username' => $user['username'],
                    'displayName' => $user['fullname'],
					'personid' => $user['personid']
                ];
                Super::sendJsonResponse("success", "Login successful", $response_data);

            } else {
                // Password Salah
                Super::sendJsonResponse("error", "Invalid password.");
            }

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }
	
	public static function getNotifications(){
		try{
			if(Super::hasParam("page")){
			$page = Super::getParam("page");
			}else{
				$page = 1;
			}
			if(Super::hasParam("limit")){
				 $limit  = Super::getParam("limit");
			}else{
				$limit = 20;
			}

			$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
			 
			$offset = ($page - 1) * $limit;
			$set = new Set();
			
			 $mxsnotificationSet = $set->MboSetRemote("mxsnotification")
						  ->MboRemote("username")     
						  ->MboRemote("message") 
						  ->MboRemote("isread")     
						  ->MboRemote("enterdate")
						  ->MboRemote("object")
						  ->MboRemote("objectnum")
						  ->MboRemote("objectid")
						  ->MboRemote("processname")
						  ->order("enterdate DESC");
							
				if (!empty($searchTerm)) {
					// klausa WHERE yang mencari di beberapa kolom sekaligus
					$whereClause = "objectnum LIKE ? OR message LIKE ? OR object LIKE ? OR processname LIKE ?";
					$likeTerm = '%' . $searchTerm . '%';
					$set->where($whereClause, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
				}
				 $set->limit("$offset, $limit")->getMboSet();


			// 3. Ubah hasil query menjadi array
			$assets = [];
			if (!$mxsnotificationSet->isEmpty()) {
				while ($aset = $mxsnotificationSet->fetch()) {
					$assets[] = $aset;
				}
			}
			// 4. Kirim sebagai JSON
			// Kita buat struktur respons yang bagus, menyertakan informasi halaman
			$response = [
				'page' => $page,
				'data' => $assets
			];
		   Super::sendJsonResponse("success", "Data Loaded", $response);
		}catch(Exception $e){
			
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	
	
	public static function getDashboardStats() {
		try {
			$set = new Set();
			// 1. Hitung total aset
			$assetCount = (new Set())->useTransactionManager()->MboSetRemote("assets")->getMboSet()->counts();
			$peopleCount = (new Set())->useTransactionManager()->MboSetRemote("employees")->getMboSet()->counts();
			$locationCount = (new Set())->useTransactionManager()->MboSetRemote("assets")->MboRemote("distinct locnum")->getMboSet()->counts();
			// Siapkan data untuk dikirim
			$statsData = [
				'assetCount' => $assetCount,
				'peopleCount' => $peopleCount,
				'locationCount' => $locationCount
			];
			Super::sendJsonResponse("success", "Dashboard stats loaded", $statsData);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	
	public static function getProfilePicture(){
		
		$personid = Super::getParam("personid");
		$set = new Set();
		$ownerid = (new Set())->MboSetRemote("employees")->where("personid=?", $personid)->MboRemote("employeesid")->getMboSet()->getMbo()->getString("employeesid");
		$filename = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownerid = '$ownerid' and ownertable = 'employees'")->MboRemote("filename")->getMboSet()->getMbo()->getString("filename");
		
		$fsname = ContextPath::getFullHostName();
		$thumbPath = $fsname . "/attachments/thumbs/";
		$prefix = "thumb_";
		
		if($filename){
			$thumbnail_path = $thumbPath.$prefix.$filename;
		}else{
			$thumbnail_path = null;
		}
		$ss = new stdClass;
		$ss->url = $thumbnail_path;
		
		Super::sendJsonResponse("success", "Profile Picture Loaded", $ss);
	}


}


$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
	case 'getProfilePicture' : 
		API_Auth::getProfilePicture();
		break;
	case 'getDashboardStats' : 
		API_Auth::getDashboardStats();
		break;
	case 'getNotifications' : 
		API_Auth::getNotifications();
		break;
    case 'login':
        API_Auth::login();
        break;
    default:
        Super::sendJsonResponse("error", "Invalid or missing 'action' parameter.");
        break;
}
?>