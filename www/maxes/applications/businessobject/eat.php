<?php 

ob_start();
date_default_timezone_set('Asia/Jakarta');
include ("context.php");
include ("eatbo.php");
require_once  __DIR__ . '/api/mxs_login.php';


$common_inventory = __DIR__ . '/common/inventory/inventory.php';
$composer_phpmailer = __DIR__ . '/composers/PHPMailer';
$composer_phpbarcode = __DIR__ . '/composers/phpbarcode';
$composer_crunz = __DIR__ . '/composers/crunz';
require_once $composer_phpmailer.'/src/DSNConfigurator.php';
require_once $composer_phpmailer.'/src/Exception.php';
//require_once $composer_phpmailer.'/src/OAuth.php';
//require_once $composer_phpmailer.'/src/OAuthTokenProvider.php';
require_once $composer_phpmailer.'/src/PHPMailer.php';
require_once $composer_phpmailer.'/src/POP3.php';
require_once $composer_phpmailer.'/src/SMTP.php';
require_once $common_inventory;
require_once $composer_phpbarcode.'/vendor/autoload.php';
require_once 'enhanced_ai_chat.php';



/* LOAD DRAWERS */

$drawer_assets = __DIR__ . '/drawers/assets/AssetDrawer.php';
if (file_exists($drawer_assets)) {
    require_once $drawer_assets;
}


$drawer_employees = __DIR__ . '/drawers/employees/EmployeeDrawer.php';
if (file_exists($drawer_employees)) {
    require_once $drawer_employees;
}

$drawer_item = __DIR__ . '/drawers/item/ItemDrawer.php';
if (file_exists($drawer_item)) {
    require_once $drawer_item;
}

$drawer_vendor = __DIR__ . '/drawers/vendor/VendorDrawer.php';
if (file_exists($drawer_vendor)) {
    require_once $drawer_vendor;
}
$drawer_wt = __DIR__ . '/drawers/worktemplates/WorkTemplateDrawer.php';
if (file_exists($drawer_wt)) {
    require_once $drawer_wt;
}

/* END DRAWERS */

use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$subitem = [];

class TimeLength{
	public static function getDateAhead($settlement_time, $startotime){
		$settlement_otime = strtotime($settlement_time);
		$nextdate = $settlement_otime + $startotime;
		$dateahead = date("Y-m-d H:i", $nextdate);
		return $dateahead;
		
	}
	public static function oneYear(){
		return 31536000;
	}
	public static function sixMonths(){
		return 15768000;
	}
	
	public static function threeMonths(){
		return 7884000;
	}
	
	public static function oneMonth(){
		return 2628000;
	}
	public static function oneWeek(){
		return 657000;
	}
	public static function twoWeeks(){
		return 1314000;
	}
	public static function threeWeeks(){
		return 1971000;
	}
}
class XboSet {
	
	private static $objectname; 
	private static $clause;
	private static $subassetarray = [];
	private static $subassetarray2 = [];
	private static $ytdcosttemp = [];

	
	public static function getDataURL() {
		$payload = Super::getParam("getDataURL");

		$columnname  = $payload["columnname"];
		$columnvalue = $payload["columnvalue"];

		$appnameMap = [
			"itemnum"  => "item",
			"wonum"    => "workorder",
			"personid" => "employees",
			"leader" => "employees",
			"owner" => "employees",
			"ponum"    => "po"

		];

		// ambil appname berdasarkan columnname
		$appname = $appnameMap[$columnname] ?? "";
		$fsname = ContextPath::getFullHostName();
		$applinks = $fsname . "/ui/" .
			(new Set())
				->useTransactionManager()
				->MboSetRemote("mxsapp")
				->setWhere("appname='$appname'")
				->MboRemote("url")
				->getMboSet()
				->getMbo()
				->getString("url");

		$url = [
			"applinksurl"      => $applinks,
			"applinksownernum" => "$columnvalue%$appname"
		];

		Super::sendJsonResponse("success", "Data Retrieved", $url);
	}
	public static function syncAiLookup() {
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// 1. Ambil semua metadata kolom yang memiliki definisi Lookup
			$columnsToCache = (new Set())->useTransactionManager()
				->MboSetRemote("mxsaicolumns")
				->whereNotNull("lookuptable")
				->whereNotNull("lookupcolumns")
				->getMboSet()
				->fetchData();

			foreach ($columnsToCache as $col) {
				$colId = $col['mxsaicolumnsid'];
				$lookupTable = $col['lookuptable'];
				
				// Parsing daftar kolom. Contoh: "worktype, description, remarks"
				$lookupColsRaw = explode(',', $col['lookupcolumns']);
				$lookupCols = array_map('trim', $lookupColsRaw); // Bersihkan spasi
				
				$keyCol = $lookupCols[0]; // Kolom pertama selalu jadi KEY (yang masuk ke SQL)
				$descCols = array_slice($lookupCols, 1); // Ambil semua kolom sisanya untuk DESKRIPSI

				// 2. Eksekusi Kueri berdasarkan tipe sumber data
				$cacheArray = [];
				$q = (new Set())->useTransactionManager()->MboSetRemote($lookupTable);

				if (strtolower($lookupTable) === 'mxsattrvalue') {
					// SUMBER 2: Tabel mxsattrvalue (Standard Lookup Maxes)
					$lookupAttribute = $col['lookupattribute']; 
					$dataSet = $q->where("name = ?", $lookupAttribute)
						->MboRemote("value, remarks")
						->getMboSet()
						->fetchData();
					
					foreach ($dataSet as $row) {
						if (!empty($row['value'])) {
							// Opsi 1: Value -> Remarks
							$cacheArray[$row['value']] = $row['remarks'];
						}
					}
				} else {
					// SUMBER 1: Tabel Aplikasi Dinamis (Bisa banyak kolom)
					// Daftarkan semua kolom yang diminta ke MboRemote
					$q->MboRemote(implode(', ', $lookupCols));
					
					// Gunakan group untuk memastikan nilai unik (seperti DISTINCT)
					$q->group(implode(', ', $lookupCols));
					
					$dataSet = $q->getMboSet()->fetchData();

					foreach ($dataSet as $row) {
						$keyValue = $row[$keyCol];
						if ($keyValue === null || $keyValue === '') continue;

						// LOGIKA OPSI 1: Gabungkan semua kolom deskripsi dengan separator " | "
						$combinedDescParts = [];
						foreach ($descCols as $dCol) {
							if (!empty($row[$dCol])) {
								$combinedDescParts[] = $row[$dCol];
							}
						}

						// Jika tidak ada kolom deskripsi tambahan, gunakan key itu sendiri
						$finalDesc = !empty($combinedDescParts) 
									? implode(' | ', $combinedDescParts) 
									: $keyValue;

						$cacheArray[$keyValue] = $finalDesc;
					}
				}

				// 3. Konversi ke JSON padat (Minify)
				$jsonCache = json_encode($cacheArray, JSON_UNESCAPED_UNICODE);

				// 4. Update kembali ke master metadata AI
				(new Set())->useTransactionManager()
					->MboSetRemote("mxsaicolumns")
					->where("mxsaicolumnsid = ?", $colId)
					->setValue("lookupvalues", $jsonCache) // Gunakan nama kolom baru
					->change();
			}

			$tm->commit();
			return "AI Lookup Cache (Option 1) synced successfully.";

		} catch (Exception $e) {
			$tm->rollback();
			// Log error untuk audit di mxsaifeedback jika diperlukan nanti
			error_log("syncAiLookupCache Error: " . $e->getMessage());
			return "Failed to sync cache: " . $e->getMessage();
		}
	}
	public static function generalAIChat($allowedFunctions = []){
		EnhancedAIChat::generalAIChat($allowedFunctions, false); 
	}

	public static function generalAIWelcomeScreen(){
		$userLogin =  Xbo::getLoginUser();
		$personid = (new Set())->MboSetRemote("mxsusers")->where("userid=?", $userLogin)->getMboValue("personid");
		$fullname = (new Set())->MboSetRemote("employees")->where("personid=?", $personid)->getMboValue("fullname");
		Super::sendJsonResponse(
			"success",
			"Hello $fullname. Welcome to ORION."
		);

	}

	public static function getFirstRecord(){
		
		$payload = Super::getParam("getFirstRecord");
		$appkey = $payload["appkey"];
		$keyvalue = $payload["keyvalue"];
		$childObject = $payload["childObject"];
		$parentObject = $payload["parentObject"];
		
		$parentIdName = $parentObject."id";
		$set = new Set();
		$tableSet = $set->MboSetRemote($parentObject)->where("$appkey = ?", $keyvalue)->order("$appkey ASC")->limit(1)->MboRemote($parentIdName)->getMboSet();
		$table = $tableSet->getMbo();
		
		$ss =new stdClass();
		$ss->parentObject = $parentObject;
		$ss->parentIdName = $parentIdName;
		$ss->parentIdValue = $table->getString($parentIdName);
		
		Set::throws($ss);
	}
	public static function add(){
		$payload = Super::getParam();
		Super::add();
	}
	public function CheckAllParent($assetnum, $newsubassetnum, $tablename=null){
		$set = new Set();
		$newsb = $newsubassetnum;
	
		if($tablename == "workorder"){
			$tablesub = "wosubs";
			$subclause = "subwonum='$assetnum'";
			$col = "wonum";
		}else{
			$tablesub = "assetsubs";
			$subclause = "subassetnum='$assetnum'";
			$col = "assetnum";
		}
		
		$parentSet = $set->MboSetRemote($tablesub)->setWhere($subclause)->MboRemote($col)->getMboSet();
		
		$parent = $parentSet->getMbo();
		$parentname = $parent->getString($col);
		$ret = null;
		if($parentname == null){
			Set::throws(["msg"=>"notexist"]);
		}else{
			if($parentname != null) {
				if($parentname == $newsb){
					Set::throws(["msg"=>"exist", "parent"=>"topparent"]);
					$ret = "exist";
				}else{
					$a = new XboSet();
					$parentname = $a->CheckAllParent($parentname, $newsb)->ret;
				}
			}else{
				Set::throws(["msg"=>"notexist"]);
				$ret = "notexist";
			}
		}
		$this->ret = $ret;
		return $this;
	}
	
	public static function updateMaxesProperties(){
		$syspath = Super::setSysPath() . "\\";
		$sysfile = $syspath . 'properties.xml';
		$handle = fopen($sysfile, 'w') or die('Cannot open file:  ' . $sysfile);
		
		$data = $_POST["updateMaxesProperties"];
		fwrite($handle, $data);
	}
	public static function createBarcode($appname, $filename, $barcode_content){
		$save_barcode_folder = Super::getBarcodeFolder($appname);
		$barcodeUrl = Super::getBarcodeUrl($appname);
		
		$blackColor = [0, 0, 0];
		$generator = new Picqer\Barcode\BarcodeGeneratorJPG();
		//filename using workorderid
		$filePath = $save_barcode_folder.$filename.".jpg";
		$barcodeURLPath = $barcodeUrl.$filename.".jpg";
		
		//barcode using wonum, type 128
		file_put_contents($filePath, $generator->getBarcode($barcode_content, $generator::TYPE_CODE_128, 2, 100, $blackColor));
		
		$ss = new stdClass;
		$ss->barcode_folder = $barcodeURLPath;
		$ss->filePath = $filePath;
		$ss->barcode_content = $barcode_content;
		
		return $ss;
	}
	public static function removeCompanyLogo(){
		$ownership = $_POST["removeCompanyLogo"];
		$set = new Set();
		$ownerEmailSet = (new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$ownership'")->MboRemote("orgcode")->getMboSet();
		$ownerEmail = $ownerEmailSet->getMbo();
		$orgcode = $ownerEmail->getString("orgcode");
		
		$set = new Set();
		$orgSet = (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")
		->MboRemote("organizationid")->getMboSet();
		
		$org = $orgSet->getMbo();
		$organizationid = $org->getString("organizationid");
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("fileinfo")
		->setWhere("fileinfo.ownerid='$organizationid' and fileinfo.ownertable='organization' and filedefault='Y'")
		->remove();
		
		$as = new stdClass;
		$as->msg = "deleted";
		Set::throws($as);
	}
	public static function updateAndroidProfile(){
		$orgname = $_POST["orgname"];
		$email = $_POST["updateAndroidProfile"];
		$username = $_POST["username"];
		$fullname = $_POST["fullname"];
		$address = $_POST["address"];
		$city = $_POST["city"];
		$province = $_POST["province"];
		$postalcode = $_POST["postalcode"];
		$country = $_POST["country"];
		$phone = $_POST["phone"];
		$officephone = $_POST["officephone"];
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("address")
		->setValue("country", $country)
		->setValue("address", $address)
		->setValue("city", $city)
		->setValue("stateprovince", $province)
		->setValue("zippostalcode", $postalcode)
		->setValue("country", $country)
		->setValue("officephone", $officephone)
		->setValue("phone", $phone)
		->change();
		
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("organization")
		->setValue("name1", $fullname)
		->setValue("description", $orgname)
		->change();
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$email'")
		->setValue("fullname", $fullname)
		->setValue("phone", $phone)
		->change();
		
		$ss =new stdClass;
		$ss->msg = "updated";
		
		Set::throws($ss);
		
	}
	public static function androidSignIn(){
		$email = $_POST["androidSignIn"];
		$password = $_POST["androidPassword"];
		$md5password = md5($password);
		$set = new Set();
		
		
		$msg = "notok";
		$ss = new stdClass;
		$ownerEmailSet = (new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$email'")->MboRemote("password")->MboRemote("phone")->getMboSet();
		$owneremail = $ownerEmailSet->getMbo();
		$oepassword = $owneremail->getString("password");
		$phone = $owneremail->getString("phone");
		$ss->sql = $ownerEmailSet->sql;
		
		if($oepassword == $md5password){
			$ss->msg = "ok";
		}else{
			$ss->msg = "notok";
			$ss->phone= "";
			$ss->orgcode = "";
			$ss->address = "";
			$ss->fullname = "";
			$ss->orgname = "";
			$ss->warehouse ="";
			$ss->warehousename ="";
			$ss->city = "";
			$ss->province = "";
			$ss->postalcode = "";
			$ss->country = "";
			$ss->officephone = "";
		}
		
		if($ss->msg == "ok"){
			
			$set = new Set();
			$warehouseSet = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->setWhere("ownership='$email'")->MboRemote("warehouse")->MboRemote("description as whdesc")->getMboSet();
			$wh = $warehouseSet->getMbo();
			$set = new Set();
			$orgSet = (new Set())->useTransactionManager()->MboSetRemote("address")->setRelationship("organization", "address.orgcode = organization.orgcode")
			->setWhere("address.ownership='$email'")
			->MboRemote("organization.description as orgdesc")
			->MboRemote("organization.name1 as fullname")
			->MboRemote("address.orgcode")
			->MboRemote("address.country")
			->MboRemote("address.zippostalcode")
			->MboRemote("address.stateprovince")
			->MboRemote("address.city")
			->MboRemote("address.addresscode")
			->MboRemote("address.officephone")
			->MboRemote("address.address")
			->MboRemote("address.phone")
			->getMboSet();
			
			$org = $orgSet->getMbo();
			$ss->orgcode = $org->getString("orgcode");
			$ss->address = $org->getString("address");
			$ss->fullname = $org->getString("fullname");
			$ss->orgname = $org->getString("orgdesc");
			$ss->warehouse = $wh->getString("warehouse");
			$ss->warehousename =  $wh->getString("whdesc");
			$ss->city = $org->getString("city");
			$ss->province = $org->getString("stateprovince");
			$ss->postalcode = $org->getString("zippostalcode");
			$ss->country = $org->getString("country");
			$ss->officephone = $org->getString("officephone");
			$ss->phone = $org->getString("phone");
			$ss->email = $email;
			$set = new Set();
			$siteSet = (new Set())->useTransactionManager()->MboSetRemote("site")->setWhere("ownership='$email'")->MboRemote("sitecode")->getMboSet();
			$site = $siteSet->getMbo();
			$sitecode = $site->getString("sitecode");
			$ss->sitecode = $sitecode;
		}
		Set::throws($ss);
	}
	public static function sendEmail($to_email, $subject, $body, $altbody){
		$ss = new stdClass;
		$mail = new PHPMailer(true);
			try {
				//Server settings
				$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
				$mail->isSMTP();                                            //Send using SMTP
				$mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
				$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
				$mail->Username   = 'maxesqriven@gmail.com';                     //SMTP username
				//$mail->Password   = 'arilasso123456';                               //SMTP password
				$mail->Password = "inwjkkomgaqzqpvc";
				$mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
				//$mail->port			= 587;
				//$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  
				
				//Recipients
				$mail->setFrom('maxesqriven@gmail.com', 'Maxes Inventory');
				$mail->addAddress($to_email, 'Maxes User');     //Add a recipient
				//$mail->addAddress('ellen@example.com');               //Name is optional
				//$mail->addReplyTo('info@example.com', 'Information');
				//$mail->addCC('cc@example.com');
				//$mail->addBCC('bcc@example.com');

				//Attachments
				//$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
				//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

				//Content
				$mail->isHTML(true);                                  //Set email format to HTML
				$mail->Subject = $subject;
				$mail->Body    = $body;
				$mail->AltBody = $altbody;

				$mail->send();
				$msg = "emailreset";
				$errormsg = "emailreset";
			}catch (Exception $e) {
				$msg = "sendfailed";
				$errormsg = $mail->ErrorInfo;
				//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			}
		
		$ss->msg = $msg;
		$ss->errormsg= $errormsg;
		return $ss;
	}
	public static function resetPassword(){
		$resetpassword = $_GET["resetPassword"];
		$set = new Set();
		$requesttoreset = "N";
		$requestresetdate = $_GET["resetdate"];
		//$linkapp = "http://maxeswms189.app";
		//$linkapp = "intent:#Intent;action=com.example.maxesscanner;category=android.intent.category.DEFAULT;category=android.intent.category.BROWSABLE;S.email=$resetpassword";
		//$linkapp = "http://202.157.187.252/#Intent;scheme=http;action=com.example.maxesscanner;category=android.intent.category.DEFAULT;category=android.intent.category.BROWSABLE;S.email=$resetpassword";
		//$linkapp = "intent://invoice/#Intent;scheme=company;package=company.printapp;S.doctype=FRA;S.docno=FRA1234;S.browser_fallback_url=http%3A%2F%2Fgoogle.com;end";
		//$linkapp = "intent://maxeswms/#Intent;scheme=maxes;package=com.example.maxesscanner;S.email=$resetpassword;end";
		//$linkapp = "intent://hostname/#Intent;scheme=schemename;package=com.YOURPACKAGENAME.www;end"
		//$linkapp = "intent://202.157.187.252/#Intent;scheme=http;package=com.example.maxesscanner;end";
		//$linkapp = "http://202.157.187.252";
		//
		$linkapp = "http://202.157.187.252/maxessd/resetpassword.php?owneremail=$resetpassword";
		
		(new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$resetpassword'")->setValue("requesttoreset", $requesttoreset)->setValue("requestresetdate", $requestresetdate)->change();
		header("Location: $linkapp");
	}
	public static function forgotEmail(){
		$email = $_POST["forgotEmail"];
		$to_email = $email;
		
		$headers = "From: maxesqriven@gmail.com";
		$url = ContextPath::getBusinessObjectDirectory("android");
		$resetdate = Xbo::setEnterDate();
		$param = "?resetPassword=$to_email&resetdate=$resetdate";
		
		$reseturl = $url.$param;
		
		$href = "<a href='$reseturl'>Click Here to Reset</a>";
		$reseturl2 = "<a href='$reseturl'>$reseturl</a>";
	
		$set = new Set();
		$owneremailSet = (new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$email'")->MboRemote("email")->getMboSet();
		$owneremailCnt = $owneremailSet->counts();
		$msg = "";
		
		if($owneremailCnt == 0){
			$ss = new stdClass;
			$ss->msg = "emailnotfound";
			$ss->errormsg = "emailnotfound";
		}else{
			$subject = "Email Reset Maxes Inventory";
			$body = "Hello, click link to reset your password. <br><b>$href</b><br> Thank you.";
			$altbody = "";
			$ss = XboSet::sendEmail($to_email, $subject, $body, $altbody);
		}
		echo "˅";
		Set::throws($ss);
	}
	public static function uploadErrorLogAndroid(){
		$target_dir = Super::getAndroidErrorLogFolder();
		$file = $_POST["uploadErrorLogAndroid"];
		$filename = $_POST["filename"];
		$uploadPath = $target_dir . $filename;	
		
		if(!empty($_POST['uploadErrorLogAndroid'])){
			if(file_put_contents($uploadPath,
			base64_decode($_POST["uploadErrorLogAndroid"]))){
				$msg =  "success";
			}
			else{
				$msg =  "failtoupload";
			}
		}else{
			$msg = "filedoesnotexist";
		}
		$ss = new stdClass;
		$ss->msg = $msg;
		Set::throws($ss);
	}
	public static function deletePlanItem(){
		$itemnum = $_POST["deletePlanItem"];
		$wonum = $_POST["dpi_wonum"];
		$resqty = $_POST["dpi_quantity"];
		
		$set = new Set();
		$matusetransSet = $set ->MboSetRemote("matusetrans")->setWhere("wonum='$wonum' and itemnum='$itemnum'")->group("wonum, itemnum")->MboRemote("sum(quantity) as quantity")
		->getMboSet();
		$matusetrans = $matusetransSet->getMbo();
		$matuseqty = $matusetrans->getString("quantity");
		if($matuseqty == null) $matuseqty = 0;
		if($matuseqty<0) $matuseqty = $matuseqty*-1;
		
		if($matuseqty == 0){
			$msg = "ok";
		}else{
			$msg = "notok";
			if($resqty > $matuseqty){
				$remainingqty = $restqty-$matuseqty;
			}else if($resqty <= $matuseqty){
				
			}
		}
		
		$ss = new stdClass;
		$ss->msg = $msg;
		$ss->matuseqty = $matuseqty;
		$ss->resqty = $resqty;
		$ss->remainingqty = $remainingqty;
		$ss->matusesql = $matusetransSet->sql;
		Set::throws($ss);
	}
	public static function createAndroidWO(){
		$cwo = json_decode($_POST["createAndroidWO"]);
		$ari = json_decode($_POST["addAndroidReservedItems"]);
		
		$requestedby = $cwo->requestedby;
		$assetnum = $cwo->assetnum;
		$description = $cwo->description;
		$customercode = $cwo->customercode;
		$requesteddate = $cwo->requesteddate;
		$wonum = $cwo->wonum;
		$enterby = $cwo->enterby;
		$orgcode = $cwo->orgcode;
		$sitecode = $cwo->sitecode;

		$enterdate = Xbo::setEnterDate();
		$wc = "workorder.wonum='$wonum'";
		$remarks = "Created from Android Mobile";
		$status = "approved";
		
		if($requesteddate == null){
			$requesteddate = $enterdate;
		}
		
		if($requestedby == null){
			$requestedby = "android_user";
		}
		
		$changeby = $requestedby;
		$changedate = $requesteddate;
		
		
		$set = new Set();
		$woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->setWhere("$wc")->MboRemote("wonum")->getMboSet();
		$countWO = $woSet->counts();
		if($countWO ==0){
			
			$set2 = new Set();
			$woSet = $set2->MboSetRemote("workorder")->setWhere($wc)->setValue("wonum", $wonum)->setValue("description", $description)->setValue("enterby", $enterby)
			->setValue("assetnum", $assetnum)->setValue("customercode", $customercode)->setValue("reportdate", $requesteddate)->setValue("enterdate", $requesteddate)
			->setValue("changeby", $changeby)->setValue("changedate", $requesteddate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
			->add();
			
			$set3 = new Set();
			$woStatusSet = $set3->MboSetRemote("wostatus")->setValue("wonum", $wonum)->setValue("status", $status)->setValue("changedate", $requesteddate)->setValue("remarks", $remarks)
			->add();
		}else{
			$set2 = new Set();
			$woSet = $set2->MboSetRemote("workorder")->setWhere($wc)->setValue("wonum", $wonum)->setValue("description", $description)->setValue("enterby", $enterby)
			->setValue("assetnum", $assetnum)->setValue("customercode", $customercode)->setValue("reportdate", $requesteddate)->setValue("enterdate", $requesteddate)
			->setValue("changeby", $changeby)->setValue("changedate", $requesteddate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
			->change();
			
		}
		if($ari != null){
			$cntAri = count($ari);
			for($i=0; $i<$cntAri; $i++){
				$item = $ari[$i];
				$itemnum = $item->itemnum;
				$linecost = $item->linecost;
				$quantity = $item->quantity;
				$unitcost = $item->unitcost;
				$description = $item->description;
				$warehouse = $item->warehouse;
				$racknum = $item->racknum;
				$issueunit = $item->issueunit;
				$submited = "Y";
				$set = new Set();
				if($wonum != null && $itemnum!=null){
					(new Set())->useTransactionManager()->MboSetRemote("invreserve")->setValue("wonum", $wonum)->setValue("submited", $submited)->setValue("itemnum", $itemnum)
					->setValue("quantity", $quantity)->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("enterby", $requestedby)
					->setValue("enterdate", $requesteddate)->setValue("changeby", $requestedby)->setValue("changedate", $requesteddate)
					->add();
					
					
					$set2 = new Set();
					$planlinenum = Xbo::getLineNumber("woplanmaterial", "planlinenum", "wonum='$wonum'");
					$set2->MboSetRemote("woplanmaterial")->setValue("wonum", $wonum)->setValue("submited", $submited)->setValue("itemnum", $itemnum)
					->setValue("quantity", $quantity)->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("enterby", $requestedby)
					->setValue("enterdate", $requesteddate)->setValue("changeby", $requestedby)->setValue("changedate", $requesteddate)
					->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
					->add();
				}
			}
			
			
		}else{
			$cntAri = 0;
		}
		
		
		$aa = new stdClass;
		$aa->msg = "added";
		$aa->countitems = $cntAri;
		
		Set::throws($aa);
	}
	public static function checkowneremail(){
		$checkowneremail = $_POST["checkowneremail"];
		$set = new Set();
		$ownerEmailSet = (new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$checkowneremail'")->MboRemote("email")->getMboSet();
		$cntOwner = $ownerEmailSet->counts();
		if($cntOwner==0){
			$msg = "ok";
		}else{
			$msg = "notok";
		}
		
		$ss = new stdClass;
		$ss->msg = $msg;
		$ss->sql = $ownerEmailSet->sql;
		Set::throws($ss);
			
			
	}
	public static function CreateUsername(){
		$username = $_POST["createusername"];
		$password = $_POST["password"];
		$confirmpassword = $_POST["confirmpassword"];
		$md5pwd = md5($password);
		$ownershipdata = $_POST["ownershipdata"];
		$owner = json_decode($ownershipdata);
		$set = new Set();
		$wc = "username='$username'";
		
		$mxsusersSet = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setWhere($wc)->MboRemote("username")->getMboSet();
		$counts = $mxsusersSet->counts();
		
		if($counts == 0){
			$set = new Set();
			(new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setValue("userid", $username)->setValue("username", $username)->setValue("password", $md5pwd)->setValue("status", "ACTIVE")->add();

		}else{
			$set = new Set();
			(new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setWhere($wc)->setValue("password", $md5pwd)->setValue("status", "ACTIVE")->change();
		}
		
		$country = $owner->country;
		$address = $owner->address;
		$city = $owner->city;
		$orgcode = $owner->orgcode;
		$warehouse = $owner->warehouse;
		$sitecode = $owner->sitecode;
		$province = $owner->province;
		$orgname = $owner->orgname;
		$zippostalcode = $owner->postalcode;
		$sitename = $owner->sitename;
		$fullname = $owner->fullname;
		$email = $owner->email;
		$phone = $owner->phone;
		$warehousename = $owner->warehousename;
		$expired = "N";
		$trial = "Y";
		$enterdate = Xbo::setEnterDate();
		$expireddate = TimeLength::getDateAhead($enterdate, TimeLength::oneWeek());
		$set = new Set();
		$ownerEmailSet = (new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$email'")
		->MboRemote("email")->getMboSet();
		$countOwn = $ownerEmailSet->counts();
		if($countOwn == 0){
			$set = new Set();
			(new Set())->useTransactionManager()->MboSetRemote("owneremail")
			->setValue("email", $email)
			->setValue("password", $md5pwd)
			->setValue("origpassword", $password)
			->setValue("fullname", $fullname)
			->setValue("phone", $phone)
			->setValue("expired", $expired)
			->setValue("expireddate", $expireddate)
			->setValue("enterdate", $enterdate)
			->setValue("enterby", $email)
			->setValue("orgcode", $orgcode)
			->setValue("trial", $trial)
			->add();
			
			
			$_POST["xhr_profile"] = $orgcode;
			$_POST["description"] = $orgname;
			$_POST["npwp"] = "000";
			$_POST["name1"] = $orgname;
			$_POST["title1"] = "owner";
			$_POST["addresscode"] = $sitecode;
			$_POST["addrdescription"] = $address;
			$_POST["stateprovince"]= $province;
			$_POST["zippostalcode"]= $zippostalcode;
			$_POST["officephone"] = $phone;
			$_POST["city"] = $city;
			$_POST["country"] = $country;
			$_POST["warehouse"] = $warehouse;
			$_POST["whdesc"] = $warehousename;
			$_POST["ownership"] = $email;
			XboSet::ProfileUpdate();
		}else{
			$ss = new stdClass;
			$ss->msg = "hasexist";
			Set::throws($ss);
		}
		
	}
	public static function deleteImageFromAndroid(){
		$id = $_POST["id"];
		$filename = $_POST["deleteImageFromAndroid"];
		$pathToFile = XboSet::getAttachFolder();
		$pathToThumbFile = XboSet::getAttachThumbsFolder();
		
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("fileinfoid='$id'")->remove();
		
		$filename = $pathToFile . $filename;
		$thumbfilename = $pathToThumbFile . "thumb_" . $filename;
		if (file_exists($filename)) {
			unlink($filename);
			if (file_exists($thumbfilename)) {
				unlink($thumbfilename);
				$msg = "deletesuccess";
			}else{
				$msg = "thumb_filenotfound";
			}
			
		}else{
			$msg = "filenotfound";
		}
			
		$aa = new stdClass;
		$aa->msg = $msg;
		Set::throws($aa);
		
	}
	public static function loadCompanyLogo(){
		
		$ownership = $_POST["loadCompanyLogo"];
		$set = new Set();
		$ownerEmailSet = (new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$ownership'")->MboRemote("orgcode")->getMboSet();
		$ownerEmail = $ownerEmailSet->getMbo();
		$orgcode = $ownerEmail->getString("orgcode");
		
		$set = new Set();
		$orgSet = (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")
		->MboRemote("organizationid")->getMboSet();
		$org = $orgSet->getMbo();
		$organizationid = $org->getString("organizationid");
		$fsname = ContextPath::getFullHostName();
		$origPath = $fsname . "/attachments/";
		
		$set = new Set();
		$fileinfoSet = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")
		->setWhere("fileinfo.ownerid='$organizationid' and fileinfo.ownertable='organization' and filedefault='Y'")->MboRemote("fileinfo.ownerid")
		->MboRemote("fileinfo.description")->MboRemote("fileinfo.filename")->MboRemote("fileinfo.fileinfoid")
		->getMboSet();
		$fileinfo = $fileinfoSet->getMbo();
		
		$as = new stdClass;
		$as->filename = $fileinfo->getString("filename");
		$as->filepath = $origPath . $as->filename;
		$as->sql = $orgSet->sql;
		$as->fileinfosql = $fileinfoSet->sql;
		Set::throws($as);
	}
	public static function loadItemCatalog(){
		$itemnum = $_POST["loadItemCatalog"];
		$warehouse = $_POST["warehouse"];
		
		$set = new Set();
		$itemSet = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("itemid")->MboRemote("orderunit")->MboRemote("issueunit")->MboRemote("brand")->MboRemote("manufacturer")->MboRemote("issueunit")->getMboSet();
		$item = $itemSet->getMbo();
		$itemid = $item->getString("itemid");
		$issueunit = $item->getString("issueunit");
		$orderunit = $item->getString("orderunit");
		$brand = $item->getString("brand");
		$manufacturer = $item->getString("manufacturer");
		$set = new Set();
		$fileinfoSet = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")
		->setWhere("fileinfo.ownerid='$itemid' and fileinfo.ownertable='item'")->MboRemote("fileinfo.ownerid")
		->MboRemote("fileinfo.description")->MboRemote("fileinfo.filename")->MboRemote("fileinfo.fileinfoid")
		->getMboSet();
		
		$fileinfoCounts = $fileinfoSet->counts();
		$fsname = ContextPath::getFullHostName();
		$origPath = $fsname . "/attachments/";
		$rr = [];
		for($i=0; $i<$fileinfoCounts; $i++){
			$as = new stdClass;
			$fileinfo = $fileinfoSet->getMbo();
			$as->filename = $fileinfo->getString("filename");
			$as->description = $fileinfo->getString("description");
			$as->filepath = $origPath . $as->filename;
			$as->id = $fileinfo->getString("fileinfoid");;
			array_push($rr, $as);
		}
		
		
		$set = new Set();
		$inventorySet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")
		->MboRemote("safetystock")->MboRemote("vendorcode")->MboRemote("issuetype")
		->getMboSet();
		$inventory = $inventorySet->getMbo();
		
		$safetystock = $inventory->getString("safetystock");
		$vendorcode = $inventory->getString("vendorcode");
		$issuetype = $inventory->getString("issuetype");
		
		$set = new Set();
		$invpriceSet = (new Set())->useTransactionManager()->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and (unitprice is not null or unitprice != '' )")->MboRemote("unitprice")->getMboSet();
		$invprice = $invpriceSet->getMbo();
		$unitprice = $invprice->getString("unitprice");
		
		
		$aa = new stdClass();
		$aa->result= $rr;
		$aa->issueunit = $issueunit;
		$aa->orderunit = $orderunit;
		$aa->brand = $brand;
		$aa->manufacturer = $manufacturer;
		$aa->vendorcode = $vendorcode;
		$aa->safetystock = $safetystock;
		$aa->issuetype = $issuetype;
		$aa->unitprice = $unitprice;
		
		Set::throws($aa);
	}
	public static function modAndroidItemCatalog(){
		$itemnum = $_POST["modAndroidItemCatalog"];
		$brand = $_POST["brand"];
		$manufacturer = $_POST["manufacturer"];
		$orderunit = $_POST["orderunit"];
		$issueunit = $_POST["issueunit"];
		$safetystock = $_POST["safetystock"];
		$vendorcode = $_POST["vendorcode"];
		$description = $_POST["description"];
		$unitcost = $_POST["unitcost"];
		$unitprice = $_POST["unitprice"];
		$issuetype = $_POST["issuetype"];
		$invfifolifocostid = $_POST["invfifolifocostid"];
		$expdate = $_POST["expdate"];
		
		$warehouse = $_POST["warehouse"];
		
		
		$set2 = new Set();
		$set2->MboSetRemote("item")->setWhere("itemnum='$itemnum'")
		->setValue("brand", $brand)->setValue("manufacturer", $manufacturer)->setValue("orderunit", $orderunit)->setValue("issueunit", $issueunit)
		->setValue("issueunit", $issueunit)->setValue("issueunit", $issueunit)->setValue("description", $description)
		->change();
		
		
		$set3 = new Set();
		$set2->MboSetRemote("inventory")->setWhere("itemnum='$itemnum'")
		->setValue("safetystock", $safetystock)->setValue("vendorcode", $vendorcode)->setValue("issuetype", $issuetype)
		->change();
		
		$setc = new Set();
		$invpriceSet = $setc->MboSetRemote("invprice")->setWhere("itemnum='$itemnum'")->getMboSet();
		$invPriceCount = $invpriceSet->counts();
		$enterdate = Xbo::setEnterDate();
		$pricecode = "NORMAL";
		$defaultprice = "Y";
		if($invPriceCount == 0){
			$setwh = new Set();
			$warehouseSet = $setwh->MboSetRemote("warehouse")->MboRemote("warehouse")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
			$cnt = $warehouseSet->counts();
			if($cnt>0){
				for($i=0; $i<$cnt ; $i++){
					$wh = $warehouseSet->getMbo();
					$warehouse = $wh->getString("warehouse");
					$orgcode = $wh->getString("orgcode");
					$sitecode = $wh->getString("sitecode");
					
					$set = new Set();
					(new Set())->useTransactionManager()->MboSetRemote("invprice")
					->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)
					->setValue("pricecode", $pricecode)->setValue("unitprice", $unitprice)->setValue("defaultprice", $defaultprice)
					->setValue("unitprice1", $unitprice)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
					->setValue("enterdate", $enterdate)
			->add();
				}
			}
			
		}else{
			$set4 = new Set();
			$set4->MboSetRemote("invprice")->setWhere("itemnum='$itemnum'")
			->setValue("unitprice", $unitprice)->setValue("unitprice1", $unitprice)->change();
		}
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$invfifolifocostid'")->setValue("expdate", $expdate)->change();
		
		$ss = new stdClass;
		$ss->msg = "updated";
		
		Set::throws($ss);
		
	}
	public static function uploadCompanyLogo(){
		$target_dir = XboSet::getAttachFolder();
		$targetThumbs_dir = XboSet::getAttachThumbsFolder();
		$ownership = $_POST["ownership"];
		$set = new Set();
		$orgSet = (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("ownership='$ownership'")->MboRemote("organizationid")->getMboSet();
		$org = $orgSet->getMbo();
		$organizationid = $org->getString("organizationid");
		
		
		$description = "uploaded from android";
		$ownertable = "organization";
		$ownerID = $organizationid;
		$dateNow = date("Y-m-d");
		
		$encoded_string = $_POST["uploadCompanyLogo"];
		$imgdata = base64_decode($encoded_string);
		$f = finfo_open();
		$mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
		$fileExtension = explode("/", $mime_type)[1];
		
		
		$newfilename = $ownertable . "_" . $ownerID . "_" . round(microtime(true))."_".rand(1000, 10000);
		$uploadPath = $target_dir . $newfilename;	
		$filedefault = "Y";				
		$filesize = Super::filesizeformat(filesize($uploadPath));	
		if(!empty($_POST['uploadCompanyLogo'])){
			
			if(file_put_contents($uploadPath, base64_decode($_POST["uploadCompanyLogo"]))){
				echo "success";
				$set = new Set();
				(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownerTable = '$ownertable'")->remove();
				$set = new Set();
				(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setValue("filename", $newfilename)->setValue("description", $description)
				->setValue("uploaddate", $dateNow)->setValue("ownerid", $ownerID)->setValue("filedefault", $filedefault)
				->setValue("ownertable", $ownertable)->setValue("hits", 0)->setValue("filesize", $filesize)
				->setValue("type", $fileExtension)
				->add();
			}else{
				echo "failtouploadimage";
			}
		}else{
			echo "noimagefound";
		}
		
	}
	public static function UploadImageAndroid(){
		
		$target_dir = XboSet::getAttachFolder();
		$targetThumbs_dir = XboSet::getAttachThumbsFolder();
		$itemnum = $_POST["itemnum"];
		
		$set = new Set();
		$itemSet = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("itemid")->getMboSet();
		$item = $itemSet->getMbo();
		$itemid = $item->getString("itemid");
		
		
		
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
		$description = "uploaded from android";
		$ownertable = "item";
		$ownerID = $itemid;
		$dateNow = date("Y-m-d");
		$imageFileType = "jpg";
		$newfilename = $ownertable . "_" . $ownerID . "_" . round(microtime(true))."_".rand(1000, 10000).".".$imageFileType;
		$uploadPath = $target_dir . $newfilename;	
						
		$filesize = Super::filesizeformat(filesize($uploadPath));	
				
		
		if(!empty($_POST['uploadImageAndroid'])){
			
			if(file_put_contents($uploadPath,
			base64_decode($_POST["uploadImageAndroid"]))){
				
				echo "success";
				
				$set = new Set();
				(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setValue("filename", $newfilename)->setValue("description", $description)
				->setValue("uploaddate", $dateNow)->setValue("ownerid", $ownerID)
				->setValue("ownertable", $ownertable)->setValue("type", $imageFileType)
				->setValue("hits", 0)->setValue("filesize", $filesize)->add();
						
				$originalImage = imagecreatefromjpeg($uploadPath);
				$imgWidth = imagesx($originalImage);
				$imgHeight = imagesy($originalImage);
				$thumbWidth = 261;
				$thumbHeight = ($thumbWidth / $imgWidth) * $imgHeight;
				$imageThumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
				imagecopyresampled($imageThumb, $originalImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight);
				imagejpeg($imageThumb, $targetThumbs_dir . "thumb_" . $newfilename);
				imagedestroy($originalImage);
				imagedestroy($imageThumb);		
			}
			else{
				echo "failtouploadimage";
			}
		}else{
			echo "noimagefound";
		}
	}
	public static function getDataList(){
		android_renderData();
	}
	public static function BarcodeUserVerification(){
		$set = new Set();
		$mxsusersSet = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setWhere("username='barcodetest01'")->MboRemote("status")->getMboSet();
		$mxsusers = $mxsusersSet->getMbo();
		$status = $mxsusers->getString("status");
		$statusmsg = "ok";
		$ss = new stdClass();
		
		if($status == "BLOCKED"){
			$statusmsg = "Mohon registrasi ulang untuk program ini. Tech. Support: (WA) 0811-899-388 (EMAIL) barcode@javarave.com";
		}else{
			$statusmsg = "ok";
		}
		$ss->status = $status;
		$ss->statusmsg = $statusmsg;
		Set::throws($ss);
	}
	public static function SetBarcode(){

		$s = $_POST["setBarcodeItem"];
		$table = $s["xxchildtable"];
		$tableid = $s["xxtableid"];
		$id = $s["xxtableidvalue"];
		
		$set = new Set();
		$itembarcodeSet = (new Set())->useTransactionManager()->MboSetRemote("itembarcode")->setWhere("itembarcodeid='$id'")
		->MboRemote("expdate")->MboRemote("itemnum")->MboRemote("assetnum")
		->MboRemote("expired")->MboRemote("barcode")->MboRemote("barcodeheight")->MboRemote("barcodewidth")->MboRemote("barcodeformat")
		->MboRemote("barcodefontsize")->MboRemote("barcodelastchar")
		->getMboSet();
		$ib = $itembarcodeSet->getMbo();
		
		$expired = $ib->getString("expired");
		$itemnum = $ib->getString("itemnum");
		$assetnum = $ib->getString("assetnum");
		$barcode = $ib->getString("barcode");
		$barcodeheight = $ib->getString("barcodeheight");
		if(empty($barcodeheight)) $barcodeheight = 30;
		$barcodewidth = $ib->getString("barcodewidth");
		if(empty($barcodewidth)) $barcodewidth = 2;
		$barcodeformat = $ib->getString("barcodeformat");
		$barcodefontsize = $ib->getString("barcodefontsize");
		if(empty($barcodefontsize)) $barcodefontsize = 14;
		$barcodelastchar = $ib->getString("barcodelastchar");
		
	
		$msg = "ok";
		$set = new Set();
		if(!empty($assetnum)){
			(new Set())->useTransactionManager()->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")
			->setValue("barcode", $barcode)
			->setValue("barcodeheight", $barcodeheight)
			->setValue("barcodewidth", $barcodewidth)
			->setValue("barcodeformat", $barcodeformat)
			->setValue("barcodefontsize", $barcodefontsize)
			->setValue("barcodelastchar", $barcodelastchar)
			->change();
		}else{
			if(!empty($itemnum)){
				(new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")
				->setValue("barcode", $barcode)
				->setValue("barcodeheight", $barcodeheight)
				->setValue("barcodewidth", $barcodewidth)
				->setValue("barcodeformat", $barcodeformat)
				->setValue("barcodefontsize", $barcodefontsize)
				->setValue("barcodelastchar", $barcodelastchar)
				->change();
			}
		}
	

		if($barcodeformat != "EAN13"){
			$msg = "ok";
			if(!empty($assetnum)){
				(new Set())->useTransactionManager()->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")
				->setValue("barcode", $barcode)
				->setValue("barcodeheight", $barcodeheight)
				->setValue("barcodewidth", $barcodewidth)
				->setValue("barcodeformat", $barcodeformat)
				->setValue("barcodefontsize", $barcodefontsize)
				->setValue("barcodelastchar", $barcodelastchar)
				->change();
			}else{
				if(!empty($itemnum)){
					(new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")
					->setValue("barcode", $barcode)
					->setValue("barcodeheight", $barcodeheight)
					->setValue("barcodewidth", $barcodewidth)
					->setValue("barcodeformat", $barcodeformat)
					->setValue("barcodefontsize", $barcodefontsize)
					->setValue("barcodelastchar", $barcodelastchar)
					->change();
				}
			}
		}
		
		$m = new stdClass();
		$m->msg = $msg;
		Set::throws($m);
	}
	public static function BarcodeCheckResult(){
		$ms = new stdClass();
		
		$data = $_POST['bccheckresult'];
		$cnt = count($data);
		
		for($i=0; $i<$cnt; $i++){
			$dt = $data[$i];
			$approved = $dt['approved'];
			$approveddate = $dt['approveddate'];
			$await = $dt['await'];
			$barcode = $dt['barcode'];
			$expdate = $dt['expdate'];
			$expired = $dt['expired'];
			$set = new Set();
			(new Set())->useTransactionManager()->MboSetRemote("itembarcode")->setWhere("barcode='$barcode'")
			->setValue("registered", $approved)
			->setValue("regdate", $approveddate)
			->setValue("await", $await)
			->setValue("barcode", $barcode)
			->setValue("expdate", $expdate)
			->setValue("expired", $expired)
			->change();
			
			if($expired == "Y"){
				$sa = new Set();
				$sa->MboSetRemote("item")->setWhere("barcode='$barcode'")->setValue("barcode", null)->setValue("barcodeformat", null)->change();
			}
		}
		$ms->msg = "success";
		$ms->cnt = $cnt;
		Set::throws($ms);
	}
	public static function GetBarcodeList(){
		$set = new Set();
		$itembarcodeSet = (new Set())->useTransactionManager()->MboSetRemote("itembarcode")->MboRemote("itemnum")->MboRemote("assetnum")->MboRemote("barcode")->MboRemote("barcodeformat")
		->MboRemote("enterby")->MboRemote("enterdate")->MboRemote("barcodelastchar")->MboRemote("registered")->MboRemote("regdate")->MboRemote("regby")->MboRemote("expdate")
		->getMboSet();
			$dd = [];
		$cnt = $itembarcodeSet->counts();
		
		if($cnt == 0){
			$msg = "emptydata";
		}else{
			$msg = "ok";
		
			for($i=0; $i<$cnt; $i++){
				$m = new stdClass();
				$ib = $itembarcodeSet->getMbo();
				$m->itemnum = $ib->getString("itemnum");
				$m->assetnum = $ib->getString("assetnum");
				$m->barcode = $ib->getString("barcode");
				$m->barcodeformat = $ib->getString("barcodeformat");
				$m->enterby = $ib->getString("enterby");
				$m->enterdate = $ib->getString("enterdate");
				$m->barcodelastchar = $ib->getString("barcodelastchar");
				$m->registered = $ib->getString("registered");
				$m->regdate = $ib->getString("regdate");
				$m->regby = $ib->getString("regby");
				$m->expdate = $ib->getString("expdate");
				
				if(!empty($m->itemnum)){
					$sq = new Set();
					$description = $sq->MboSetRemote("item")->setWhere("itemnum='{$m->itemnum}'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");
				}else{
					if(!empty($m->assetnum)){
						$sq = new Set();
						$description = $sq->MboSetRemote("assets")->setWhere("assetnum='{$m->assetnum}'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");
					}else{
						$description = null;
					}
				}
				$m->description = $description;
				$set2 = new Set();
				$itemSet = $set2->MboSetRemote("item")->setWhere("itemnum='{$m->itemnum}'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
				$item = $itemSet->getMbo();
				$m->orgcode = $item->getString("orgcode");
				$m->sitecode = $item->getString("sitecode");
				
				
				$ss = new Set();
				$orgSet = $ss->MboSetRemote("organization")->setWhere("orgcode='{$m->orgcode}'")->MboRemote("name1")->MboRemote("description")->getMboSet();
				$org = $orgSet->getMbo();
				$m->contact = $org->getString("name1");
				$m->orgdesc = $org->getString("description");
				
				$set3 = new Set();
				$addressSet = $set3->MboSetRemote("address")->setWhere("orgcode='{$m->orgcode}' and addresscode='{$m->sitecode}'")
				->MboRemote("description")->MboRemote("stateprovince")->MboRemote("zippostalcode")
				->MboRemote("officephone")->MboRemote("city")->MboRemote("country")->getMboSet();
				$address = $addressSet->getMbo();
			
				$m->addrdescription = $address->getString("description");
				$m->stateprovince = $address->getString("stateprovince");
				$m->zippostalcode = $address->getString("zippostalcode");
				$m->officephone = $address->getString("officephone");
				$m->city = $address->getString("city");
				$m->country = $address->getString("country");
				
				
				array_push($dd, $m);
			}
		}
		
		
		$n = new stdClass();
		$n->barcodedata = $dd;
		$n->msg = $msg;
		Set::throws($n);
	}
	public static function addBarcode(){
		$payload = Super::getParam("addBarcode");
		$barcode = $payload["newbarcode"];
		$barcodeformat = $payload["newbarcodeformat"];
		$key = $payload["itemnum"];
		$table = $payload["object"];

		$m = new stdClass();
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$set = new Set();
		if($table == "item") { 
			$keynum = "itemnum"; 
		}
		if($table == "asset") { 
			$keynum = "assetnum";
		}
		
		if(empty($barcode) || empty($barcodeformat)) {
			$msg = "cannotnull";
		}else{
			$tableSet = (new Set())->useTransactionManager()->MboSetRemote("itembarcode")->setWhere("barcode = '$barcode'")->getMboSet();
			$tableCnt = $tableSet->counts();
			if($tableCnt == 0){
				$msg = "addbarcode";
				$set = new Set();
				(new Set())->useTransactionManager()->MboSetRemote("itembarcode")->setValue($keynum, $key)->setValue("barcode", $barcode)->setValue("barcodeformat", $barcodeformat)
				->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->add();
				
			}else{
				$msg = "recordexist";
			}
		}
		
		
		
		$m->msg = $msg;
		Set::throws($m);
	}
	public static function UserLongLat(){
		$userid = $_POST["userlonglat"];
		$longitude = $_POST["longitude"];
		$latitude = $_POST["latitude"];
		$accuracy = $_POST["accuracy"];
		
		
		if($userid == "nologin") return null;
		
		$set = new Set();
		$userlonglatSet = (new Set())->useTransactionManager()->MboSetRemote("userlonglat")->setWhere("userid='$userid'")->MboRemote("userid")
		->MboRemote("longitude")->MboRemote("latitude")->MboRemote("prevlatitude")->MboRemote("prevlongitude")
		->getMboSet();
		
		$ucn = $userlonglatSet->counts();
		$userll = $userlonglatSet->getMbo();
		
		$prevlongitude = $userll->getString("longitude");
		$prevlatitude = $userll->getString("latitude");
		
		$prevlongitude2 = $userll->getString("prevlongitude");
		$prevlatitude2 = $userll->getString("prevlatitude");
		
		
		if(empty($prevlongitude)) $prevlongitude = $longitude;
		if(empty($prevlatitude)) $prevlatitude = $latitude;
		
		
		$trackdate = Xbo::setEnterDate();
		$si = new Set();
		if($ucn == 0){
			$si->MboSetRemote("userlonglat")
			->setValue("userid", $userid)->setValue("trackdate", $trackdate)
			->setValue("longitude", $longitude)->setValue("latitude", $latitude)
			->setValue("prevlongitude", $prevlongitude)->setValue("prevlatitude", $prevlatitude)
			->setValue("accuracy", $accuracy)
			->add();
		}else{
			$prevlongitude = round($prevlongitude, 6);
			$prevlatitude = round($prevlatitude, 6);
			$prevlongitude2 = round($prevlongitude2, 6);
			$prevlatitude2 = round($prevlatitude2, 6);
			$longitude = round($longitude, 6);
			$latitude = round($latitude, 6);
			
			
			if($prevlongitude == $longitude && $prevlatitude == $latitude){
				
				$si->MboSetRemote("userlonglat")
				->setWhere("userid='$userid'")
				->setValue("trackdate", $trackdate)
				->setValue("accuracy", $accuracy)
				->change();
			}else{
				if($prevlongitude2 == $longitude && $prevlatitude2 == $latitude){
					
					$si->MboSetRemote("userlonglat")
					->setWhere("userid='$userid'")
					->setValue("trackdate", $trackdate)
					->setValue("accuracy", $accuracy)
					->change();
				}else{
					$si->MboSetRemote("userlonglat")
					->setWhere("userid='$userid'")
					->setValue("trackdate", $trackdate)
					->setValue("longitude", $longitude)->setValue("latitude", $latitude)
					->setValue("prevlongitude", $prevlongitude)->setValue("prevlatitude", $prevlatitude)
					->setValue("accuracy", $accuracy)
					->change();
				}
			}
		}
		
		$sss = new stdClass();
		$sss->userid = $userid;
		$sss->longitude = $longitude;
		$sss->latitude = $latitude;
		$sss->prevlongitude = $prevlongitude;
		$sss->prevlatitude = $prevlatitude;
		$sss->trackdate = $trackdate;
		$sss->accuracy = $accuracy;
		Set::throws($sss);
	}
	public static function RFSMatrectrans(){
		$rfsnum = Super::getParam('rfsnum');
		$conn = DB::getMXConnection();

		// Sanitasi input
		$rfsnum_safe = mysqli_real_escape_string($conn, $rfsnum);

		// Ambil PONUM dari tabel rfs
		$ponum_sql = "SELECT ponum FROM rfs WHERE rfsnum = '$rfsnum_safe'";
		$ponum_result = mysqli_query($conn, $ponum_sql);

		if ($ponum_result && mysqli_num_rows($ponum_result) > 0) {
			$ponum_row = mysqli_fetch_assoc($ponum_result);
			$ponum = $ponum_row['ponum'];
		} else {
			echo "Error: PONUM tidak ditemukan untuk RFS: $rfsnum";
			return;
		}

		// Query utama untuk mendapatkan data item dan kuantitas tersedia
		$sql = "SELECT
					pl.ponum,
					pl.polinenum,
					pl.itemnum,
					pl.description,
					pl.orderqty,
					COALESCE(SUM(CASE WHEN mt.transtype = 'RECEIPT' THEN mt.quantity ELSE 0 END), 0) + COALESCE(SUM(CASE WHEN mt.transtype = 'RETURN' THEN mt.quantity ELSE 0 END), 0) AS qty_tersedia,
					po.vendor,
					pl.orderunit,
					ic.conditiontype,
					pl.unitcost,  
					(COALESCE(SUM(CASE WHEN mt.transtype = 'RECEIPT' THEN mt.quantity ELSE 0 END), 0) + COALESCE(SUM(CASE WHEN mt.transtype = 'RETURN' THEN mt.quantity ELSE 0 END), 0)) * pl.unitcost AS linecost ,
					mt.warehouse,
					mt.racknum
				FROM
					poline pl
				LEFT JOIN matrectrans mt ON pl.ponum = mt.ponum AND pl.polinenum = mt.polinenum AND pl.itemnum = mt.itemnum
				LEFT JOIN po ON pl.ponum = po.ponum
				LEFT JOIN itemcondition ic ON pl.itemnum = ic.itemnum
				WHERE
					pl.ponum = '$ponum'
				GROUP BY
					pl.ponum, pl.polinenum
				HAVING qty_tersedia > 0
				ORDER BY
					pl.polinenum, pl.itemnum";

		$result = mysqli_query($conn, $sql);

		if (!$result) {
			echo "Error executing query: " . mysqli_error($conn);
			return;
		}

		$tbodyArr = array();
		$i=1;
		while ($row = mysqli_fetch_assoc($result)) {
			$polinenum = htmlspecialchars($row['polinenum']);
			$itemnum = htmlspecialchars($row['itemnum']);
			$description = htmlspecialchars($row['description']);
			$warehouse = htmlspecialchars($row['warehouse']);
			$racknum = htmlspecialchars($row['racknum']);
			// Format Angka (qty_tersedia)
			$restqty = $row['qty_tersedia'];
			$restqty_formatted = number_format($restqty, 2, ',', '.'); // Format dengan 2 desimal

			// Hilangkan desimal jika ,00
			if (strpos($restqty_formatted, ',00') !== false) {
				$restqty_formatted = str_replace(',00', '', $restqty_formatted);
			}

			$returnUnit = htmlspecialchars($row['orderunit']);
			$conditiontype = htmlspecialchars($row['conditiontype']);
				// Format Angka (unitcost)
			$unitcost = $row['unitcost'];
			$unitcost_formatted = number_format($unitcost, 2, ',', '.'); // Format dengan 2 desimal

			// Hilangkan desimal jika ,00
			if (strpos($unitcost_formatted, ',00') !== false) {
				$unitcost_formatted = str_replace(',00', '', $unitcost_formatted);
			}
			// Format Angka (linecost)
			$linecost = $row['linecost'];
			$linecost_formatted = number_format($linecost, 2, ',', '.'); // Format dengan 2 desimal

			// Hilangkan desimal jika ,00
			if (strpos($linecost_formatted, ',00') !== false) {
				$linecost_formatted = str_replace(',00', '', $linecost_formatted);
			}

			$tbody = "<tr>
				<td data-attribute='ponum' >$ponum</td>
				<td data-attribute='polinenum'>$polinenum</td>
				<td data-attribute='itemnum'>$itemnum</td>
				<td data-attribute='description'>$description</td>
				<td data-attribute='restqty' ><input type='text' data-type='decimal' data-attribute='restqty' value ='$restqty_formatted'></td>
				<td data-attribute='itemnum.issueunit'>$returnUnit</td>
				<td data-attribute='conditiontype'>$conditiontype</td>			
				<td data-attribute='unitcost' data-type='decimal'>$unitcost_formatted</td>
				<td data-attribute='warehouse'>$warehouse</td>				
				<td data-attribute='racknum'>$racknum</td>
				<td data-attribute='rfsmatrec' class='hide'>$i</td>
			</tr>";

			array_push($tbodyArr, $tbody);
			$i++;
		}

		$tbodyIm = implode(" ", $tbodyArr);
		$tbody = "<tbody>" . $tbodyIm . "</tbody>";

		$thead = "<thead><tr>
		
						<th>PO#</th>
						<th>Line</th>
						<th>Item</th>
						<th>Description</th>
						<th>Return Qty</th>
						<th>Return Unit</th>
						<th>Condition Type</th>
						<th>Unit Cost</th>
						<th>Warehouse</th>
						<th>Rack</th>
						<th class='hide'>Line</th>
					</tr></thead>";

		$tfoot = "<tfoot><tr>
		<th>PO#</th>
						<th>Line</th>
						<th>Item</th>
						<th>Description</th>
						<th>Return Qty</th>
						<th>Return Unit</th>
						<th>Condition Type</th>
						<th>Unit Cost</th>
						<th>Warehouse</th>
						<th>Rack</th>
						<th class='hide'>Line</th>
					</tr></tfoot>";

		$renderTable = $thead . $tfoot . $tbody;
		echo $renderTable;
	}
	public static function rfsinput(){
		$rfsnum = Super::getParam('rfsinput');
		$conn = DB::getMXConnection();
	

		$thead = "<thead>
						<tr>
							 <th>Line</th>
							<th>Item</th>
							<th>Description</th>
							<th>Ordered Qty</th>
							<th>Inspect Qty</th>
							<th>Accepted Qty</th>
							<th>In Transit Qty</th>
							<th>Remaining Qty (Vendor)</th>
							<th>Total Received Qty</th>
							<th>Order Unit</th>
							<th>Unit Cost</th>
							<th>Line Cost</th>
							<th class='hide'>rfslineid</th>
						</tr>
					</thead>";
		$tfoot = "<tfoot>
						<tr>
							 <th>Line</th>
							<th>Item</th>
							<th>Description</th>
							<th>Ordered Qty</th>
							<th>Inspect Qty</th>
							<th>Accepted Qty</th>
							<th>In Transit Qty</th>
							<th>Remaining Qty (Vendor)</th>
							<th>Total Received Qty</th>
							<th>Order Unit</th>
							<th>Unit Cost</th>
							<th>Line Cost</th>
							<th class='hide'>rfslineid</th>
						</tr>
					</tfoot>";

		$tbody = "<tbody></tbody>";

		$set = new Set();
		$rfslineSet = (new Set())->useTransactionManager()->MboSetRemote("rfsline")->setWhere("rfsnum='$rfsnum'")->MboRemote("rfslinenum")->MboRemote("itemnum")->MboRemote("description")->MboRemote("orderqty")
			->MboRemote("orderunit")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("rfslineid")->getMboSet();

		$tbodyArr = [];
		$countrfs = $rfslineSet->counts();
		if($countrfs == 0){
			$tbody = "<tbody></tbody>";
			$renderTable = $thead . $tfoot . $tbody;
		} else {
			for($i=0; $i<$countrfs; $i++){
				$rfsline = $rfslineSet->getMbo();
				$rfslinenum = $rfsline->getString("rfslinenum");
				$itemnum = $rfsline->getString("itemnum");
				$description = $rfsline->getString("description");
				$orderqty = $rfsline->getString("orderqty");
				$orderunit = $rfsline->getString("orderunit");
				$unitcost = $rfsline->getString("unitcost");
				$linecost = $rfsline->getString("linecost");
				$rfslineid = $rfsline->getString("rfslineid");

				// Gunakan Query SQL untuk menghitung remainingqty dan qty lainnya
				$rfslinenum_safe = mysqli_real_escape_string($conn, $rfslinenum);
				$itemnum_safe = mysqli_real_escape_string($conn, $itemnum);

				// Dapatkan PONUM dari RFS terlebih dahulu
				$sql_ponum = "SELECT ponum FROM rfs WHERE rfsnum = '$rfsnum'";
				$result_ponum = mysqli_query($conn, $sql_ponum);
				$ponum = '';
				if ($result_ponum && mysqli_num_rows($result_ponum) > 0) {
					$row_ponum = mysqli_fetch_assoc($result_ponum);
					$ponum = $row_ponum['ponum'];
				}

				$sql = "SELECT
					pl.orderqty, pl.conversionfactor, po.exchangerate,
					COALESCE(SUM(CASE WHEN ri.inspectiontype = 'accepted' THEN ri.inspectqty ELSE 0 END), 0) AS total_accepted_qty,
					COALESCE(SUM(CASE WHEN ri.inspectiontype = 'intransit' THEN ri.inspectqty ELSE 0 END), 0) AS total_intransit_qty,
					COALESCE(SUM(CASE WHEN ri.inspectiontype = 'inspect' THEN ri.inspectqty ELSE 0 END), 0) AS total_inspect_qty,
					CASE
						WHEN pl.orderqty - COALESCE(SUM(CASE WHEN ri.inspectiontype = 'accepted' THEN ri.inspectqty ELSE 0 END), 0) < 0 THEN 0
						ELSE pl.orderqty - COALESCE(SUM(CASE WHEN ri.inspectiontype = 'accepted' THEN ri.inspectqty ELSE 0 END), 0)
					END AS remainingqty,  -- Sisa di vendor
					CASE
						WHEN pl.orderqty - (COALESCE(SUM(CASE WHEN ri.inspectiontype = 'accepted' THEN ri.inspectqty ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN ri.inspectiontype = 'intransit' THEN ri.inspectqty ELSE 0 END), 0)) < 0 THEN 0
						ELSE pl.orderqty - (COALESCE(SUM(CASE WHEN ri.inspectiontype = 'accepted' THEN ri.inspectqty ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN ri.inspectiontype = 'intransit' THEN ri.inspectqty ELSE 0 END), 0))
					END AS qty_dibutuhkan -- Kuantitas yang masih dibutuhkan
				FROM
					poline pl
				LEFT JOIN
					rfsinsitem ri ON pl.ponum = (SELECT r.ponum FROM rfs r WHERE r.rfsnum = ri.rfsnum) AND pl.polinenum = ri.rfsinslinenum AND pl.itemnum = ri.itemnum
				LEFT JOIN
					po on pl.ponum = po.ponum
				WHERE
					pl.ponum = '$ponum' AND pl.polinenum = '$rfslinenum_safe' AND pl.itemnum = '$itemnum_safe'
				GROUP BY
					pl.ponum, pl.polinenum, pl.itemnum, pl.orderqty";


				//echo $sql ."<hr>";
				$result = mysqli_query($conn, $sql);

				if ($result && mysqli_num_rows($result) > 0) {
					$row = mysqli_fetch_assoc($result);
					$qtyinspected = $row['remainingqty']; //sekarang sisa di vendor
					$qty_dibutuhkan = $row['qty_dibutuhkan'];
					$total_accepted = $row['total_accepted_qty'];
					$total_return = $row['total_intransit_qty'];
					$total_inspect = $row['total_inspect_qty'];
					$conversionfactor = $row['conversionfactor'];
					$exchangerate = $row['exchangerate'];
					 // Hitung total kuantitas yang diterima dari matrectrans
					$sql_total_received = "SELECT COALESCE(SUM(quantity), 0) AS total_received
											FROM matrectrans
											WHERE ponum = '$ponum' AND polinenum = '$rfslinenum_safe' GROUP BY ponum, polinenum";
											
					//echo $sql_total_received ." | ";
					$result_total_received = mysqli_query($conn, $sql_total_received);
					if ($result_total_received) {
						$row_total_received = mysqli_fetch_assoc($result_total_received);

						$total_received = $row_total_received['total_received']/$conversionfactor;
					} else {
						$total_received = 0;
					}
				
					//echo "total Received : $total_received | ";	
					// Hitung total return after post 
					$variance_receipt_qty = $orderqty-$total_received;
					//$total_return_after_post = $qtyinspected-$total_received;
					//if($total_return_after_post<-1) $total_return_after_post=$total_return_after_post*-1;
					$total_return_after_post = $variance_receipt_qty;
					
					//Hitung total return. 
					 // Hitung total kuantitas yang diterima dari matrectrans
					$sql_total_return = "SELECT COALESCE(SUM(quantity), 0) AS total_received
											FROM matrectrans
											WHERE ponum = '$ponum' AND polinenum = '$rfslinenum_safe' AND transtype='RETURN' GROUP BY ponum, polinenum";
					
					
					$result_total_return = mysqli_query($conn, $sql_total_return);
					if ($result_total_return) {
						$row_total_return = mysqli_fetch_assoc($result_total_return);
						$total_return_qty = floatval($row_total_return['total_received']);
					} else {
						$total_return_qty = 0;
					}

					//adjust total Accepted. 
					$total_accepted = $total_accepted+$total_return_qty;

				} else {
					$qtyinspected = $orderqty; // Jika tidak ada data inspeksi, tampilkan orderqty
					$qty_dibutuhkan = 0;
					$total_accepted = 0;
					$total_return = 0;
					$total_inspect = 0;
					$total_received = 0;
					$total_return_after_post = 0;
				}
				
				if (empty($qtyinspected)) $qtyinspected = $orderqty;

					$trtd = "<tr>
							<td data-attribute='rfslinenum'>$rfslinenum</td>
							<td data-attribute='itemnum'>$itemnum</td>
							<td data-attribute='description'>$description</td>
							<td data-attribute='plorderqty'>$orderqty</td>
							 <td data-attribute='total_inspect' data-type='decimal'>$total_inspect</td>
							<td data-attribute='total_accepted' data-type='decimal'>$total_accepted</td>
							<td data-attribute='total_return' data-type='decimal'>$total_return</td>						
							<td data-attribute='orderqty' data-type='decimal'>$total_return_after_post</td>
							<td data-attribute='total_received' data-type='decimal'>$total_received</td>
							<td data-attribute='orderunit'>$orderunit</td>
							<td data-attribute='unitcost' data-type='decimal'>$unitcost</td>
							<td data-attribute='linecost' data-type='decimal'>$unitcost</td>                       
							<td data-attribute='rfslineid' class='hide'>$rfslineid</td>
						</tr>";
					array_push($tbodyArr, $trtd);
				
			}

			$counttbody = count($tbodyArr);
			if($counttbody==0){
				$tbody = "<tbody></tbody>";
			} else {
				$tbody = "<tbody>".implode(" ", $tbodyArr)."</tbody>";
			}

			$renderTable = $thead . $tfoot . $tbody;
		}

		echo $renderTable;
	}
	public static function InvoiceInput(){
		$pts = explode("|", Super::getParam("invoiceinput"));
		$mbosource = $pts[0];
		$key = $pts[1];
		$invoicenum = $pts[2];
		$set = new Set();
		$setinv = new Set();
		
		$isrefund = $setinv->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("refund")->getMboSet()->getMbo()->getString("refund");
	
		$l=10;
		if ($mbosource == "invoicecharge") {
		
			$thead = "<thead>
						<tr>
							<th>Charge Line</th>
							<th>Description</th>
							<th>Jml</th>
							<th>Biaya</th>
							<th>Pajak</th>
							<th>Origin</th>
							<th>SO/PO</th>
							<th class='hide'>chargeid</th>
						</tr>
					</thead>";
			$tfoot = "<tfoot>
						<tr>
							<th>Charge Line</th>
							<th>Jml</th>
							<th>Quantity</th>
							<th>Biaya</th>
							<th>Pajak</th>
							<th>Origin</th>
							<th>SO/PO</th>
							<th class='hide'>chargeid</th>
						</tr>
					</tfoot>";
			$tbody = "<tbody></tbody>";
			$tbodyArr = array();
			
			
			$taxSet0 = new Set();
			if($isrefund == "Y"){
				$originname = "return-tax";
			}else{
				$originname = "tax";
			}
			$totalSOTaxSet = $taxSet0->MboSetRemote("invoicecharge")->setWhere("sonum='$key' and origin like '%tax%' ")->MboRemote("sum(linecost)as totalsotax")->getMboSet();
			$totalSOTax = $totalSOTaxSet->getMbo()->getString("totalsotax");
			if($totalSOTax == null) $totalSOTax=0;
			
			$discSet = new Set();
			$discountpercentage = $discSet->MboSetRemote("so")->setWhere("sonum='$key'")->MboRemote("discountpercentage")->getMboSet()->getMbo()->getString("discountpercentage");
			if($discountpercentage == null) $discountpercentage = 0;
			
			$taxSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("taxcost!=0 and invoicenum='$invoicenum'")->MboRemote("sum(taxcost)as taxcost")->getMboSet();
			$taxs = $taxSet->getMbo();
			$tax = $taxs->getString("taxcost");	
			
			if($tax == null) $tax=0;
			
			$tax = $tax - ($tax * ($discountpercentage / 100));
			
			if($isrefund == "Y"){
				$totalSOTax = $totalSOTax*-1;
				if($tax <0) $tax = $tax*-1;
			}
			
			$remainingTax = $tax;
			
			
			$checkso = new Set();
			$checkSOSet = $checkso->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("sonum")->getMboSet();
			$checkSO = $checkSOSet->getMbo();
			$issonumexist = $checkSO->getString("sonum");
				
			if ($issonumexist != null ){
				//SO TAX
				$qtySet = new Set();
				$totalQtySet = $qtySet->MboSetRemote("invoicecharge")->setWhere("sonum='$key' and origin like '%tax%'")->MboRemote("sum(quantity)as totalqty")->getMboSet();
				
				$totalSOTax2 = $totalQtySet->getMbo()->getString("totalqty");
				if(empty($totalSOTax2)) $totalSOTax2=0;
				if($isrefund == "N"){
					$totalSOTax2 = $totalSOTax2*-1;
				}
				$quantity = $totalSOTax2;
				if($isrefund == "Y") $quantity = $quantity*-1;
				if($totalSOTax2 == 0) $quantity = 1-$totalSOTax2;
				if($quantity <= 0) $quantity = 1+$quantity;
				
				
				if($remainingTax <=0){
					$tbodyTEMP=null;
					if($isrefund == "Y"){
						if($remainingTax == 0){
							$tbodyTEMP = "";
						}else{
							$tbodyTEMP = "<tr>
							<td data-attribute='invoicechargelinenum'>$l</td>
							<td data-attribute='description'>Total Return Tax</td>
							<td data-attribute='quantity' data-type='decimal'>$quantity</td>
							<td data-attribute='unitcost' data-type='decimal'>$remainingTax</td>
							<td data-attribute='taxcost' data-type='decimal'>0</td>
							<td data-attribute='origin'>$originname</td>
							<td data-attribute='sonum'>$key</td>
							<td data-attribute='invoicechargeid' class='hide'>9999</td>
							</tr>";
						}
					}
				}else{
					
					if($quantity == 0){
						$tbodyTEMP = "";
					}else{
						if($isrefund == "N"){
							if($quantity <=0){
								$tbodyTEMP = "";
							}else{
								if($totalSOTax2 == 0){
									$tbodyTEMP = "";
									if($remainingTax!=0 ){
										$tbodyTEMP = "<tr>
										<td data-attribute='invoicechargelinenum'>$l</td>
										<td data-attribute='description'>Total Tax</td>
										<td data-attribute='quantity' data-type='decimal'>$quantity</td>
										<td data-attribute='unitcost' data-type='decimal'>$remainingTax</td>
										<td data-attribute='taxcost' data-type='decimal'>0</td>
										<td data-attribute='origin'>$originname</td>
										<td data-attribute='sonum'>$key</td>
										<td data-attribute='invoicechargeid' class='hide'>9999</td>
										</tr>";
									}
								}else{
									
									$tbodyTEMP = "<tr>
									<td data-attribute='invoicechargelinenum'>$l</td>
									<td data-attribute='description'>Total Tax</td>
									<td data-attribute='quantity' data-type='decimal'>$quantity</td>
									<td data-attribute='unitcost' data-type='decimal'>$remainingTax</td>
									<td data-attribute='taxcost' data-type='decimal'>0</td>
									<td data-attribute='origin'>$originname</td>
									<td data-attribute='sonum'>$key</td>
									<td data-attribute='invoicechargeid' class='hide'>9999</td>
									</tr>";
								}
								
							}
						}else{
							if($quantity > 0){
								$tbodyTEMP = "";
								
							}else{
								
								if($totalSOTax2 == 0){
									$tbodyTEMP = "";
								}else{
									$tbodyTEMP = "<tr>
									<td data-attribute='invoicechargelinenum'>$l</td>
									<td data-attribute='description'>Total Tax</td>
									<td data-attribute='quantity' data-type='decimal'>$quantity</td>
									<td data-attribute='unitcost' data-type='decimal'>$remainingTax</td>
									<td data-attribute='taxcost' data-type='decimal'>0</td>
									<td data-attribute='origin'>$originname</td>
									<td data-attribute='sonum'>$key</td>
									<td data-attribute='invoicechargeid' class='hide'>9999</td>
									</tr>";
								}
							}
						}
					}
				}
				
				array_push($tbodyArr, $tbodyTEMP);
				
			}else{
				//PO TAX
				if($isrefund == "Y"){
					$originname = "return-tax";
				}else{
					$originname = "tax";
				}
				
				$taxSet1 = new Set();
				$totalPOTaxSet = $taxSet1->MboSetRemote("invoicecharge")->setWhere("ponum='$key' ")->MboRemote("sum(linecost)as totalpotax")->getMboSet();		
				$totalPOTax = $totalPOTaxSet->getMbo()->getString("totalpotax");
				if($totalPOTax == null) $totalPOTax=0;
				
				
				$taxSetPO = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("taxcost>0 and ponum='$key'")->MboRemote("sum(taxcost)as taxcost")->getMboSet()->getMbo();
				$taxPO = $taxSet->getString("taxcost");
				
				if($taxPO == null) $taxPO=0;
				
				$remainingTaxPO = $taxPO-$totalPOTax;				
				if($isrefund == "Y"){
					$remainingTaxPO = $taxPO;
				}
				$qtySet = new Set();
				$totalQtySet = $qtySet->MboSetRemote("invoicecharge")->setRelationship("invoice", "invoice.invoicenum = invoicecharge.invoicenum")
				->setWhere("invoicecharge.ponum='$key' ")->MboRemote("sum(invoicecharge.quantity)as totalqty")->getMboSet();
			
				$totalQty = $totalQtySet->getMbo()->getString("totalqty");
				if($totalQty == null) $totalQty=0;
				
				if($isrefund == "N"){
					$totalQty = $totalQty*-1;
				}
				$quantity = $totalQty;
				if($isrefund == "Y"){
					$quantity = $quantity*-1;
				}
				if($totalPOTax == 0){
					$quantity = 1-$totalPOTax;
				}
					
				if($remainingTaxPO >0){
					$thead = "<thead>
							<tr>
								<th>Charge Line</th>
								<th>Description</th>
								<th>Quantity</th>
								<th>Cost</th>
								<th>Origin</th>
								<th>PO</th>
								<th class='hide'>chargeid</th>
							</tr>
						</thead>";
				$tfoot = "<tfoot>
							<tr>
								<th>Charge Line</th>
								<th>Description</th>
								<th>Quantity</th>
								<th>Cost</th>
								<th>Origin</th>
								<th>PO</th>
								<th class='hide'>chargeid</th>
							</tr>
						</tfoot>";
				}
				if($remainingTaxPO <=0){
					$tbodyTEMP=null; 
				}else{
					
					if($quantity <= 0 ){
						$tbodyTEMP = "";
					}else{
						
						$tbodyTEMP = "<tr>
								<td data-attribute='invoicechargelinenum'>$l</td>
								<td data-attribute='description'>Total Tax</td>
								<td data-attribute='quantity' data-type='decimal'>$quantity</td>
								<td data-attribute='unitcost' data-type='decimal'>$remainingTaxPO</td>
								<td data-attribute='origin'>$originname</td>
								<td data-attribute='ponum'>$key</td>
								<td data-attribute='invoicechargeid' class='hide'>9999</td>
							</tr>";
					}
					
				}
				
				array_push($tbodyArr, $tbodyTEMP);
			}
			
			$tbody = implode(" ", $tbodyArr);
			
			$renderTable = $thead.$tbody .$tfoot;
			echo $renderTable;
		}
		
		if ($mbosource == "poline") {
			$thead = "<thead>
						<tr>
							<th>No. Baris</th>
							<th>Tipe</th>
							<th>Item</th>
							<th>Deskripsi</th>
							<th>Jumlah</th>
							<th>Order Unit</th>
							<th>Harga</th>
							<th>Total Harga</th>
							<th>Pajak</th>
							<th>Debit</th>
							<th>Kredit</th>
							<th>PO</th>
							<th class='hide'>polineid</th>
						</tr>
					</thead>";
			$tfoot = "<tfoot>
						<tr>
							<th>No. Baris</th>
							<th>Tipe</th>
							<th>Item</th>
							<th>Deskripsi</th>
							<th>Jumlah</th>
							<th>Order Unit</th>
							<th>Harga</th>
							<th>Total Harga</th>
							<th>Pajak</th>
							<th>Debit</th>
							<th>Kredit</th>
							<th>PO</th>
							<th class='hide'>polineid</th>
						</tr>
					</tfoot>";
			$ponum = $key;
			$polineSet = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("ponum='$ponum'")->MboRemote("ponum")->MboRemote("polineid")->MboRemote("polinenum")->MboRemote("itemnum")->MboRemote("linetype")->MboRemote("description")->MboRemote("orderqty")->MboRemote("orderunit")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("tax")->MboRemote("taxcost")->MboRemote("glcreditacct")->getMboSet();
			$set2 = new Set();
			$apaccount = $set2->MboSetRemote("vendor")->setRelationship("po", "po.vendor=vendor.vendorcode")->setWhere("po.ponum='$ponum'")->MboRemote("vendor.apacct")->getMboSet()->getMbo()->getString("apacct");
			if ($polineSet->counts() == 0) {
				$tbody = "";
			}
			else {
				$polcount = $polineSet->counts();
				$tbodArr = array();
				for ($i = 0; $i < $polcount; $i++) {
					$poline = $polineSet->getMbo();
					$set3 = new Set();
					$qtyinvoiced = $set3->MboSetRemote("invoiceline")
					->setWhere("ponum='$ponum' and invoicelinenum='" . $poline->getString("polinenum") . "'")
					->group("invoiceline.invoicelinenum")->MboRemote("sum(quantity) as qtyinvoiced")->getMboSet()->getMbo()->getString("qtyinvoiced");
					if (empty($qtyinvoiced)) $qtyinvoiced = 0;
					$quantity = $poline->getString("orderqty");
					$unitcost = $poline->getString("unitcost");
					$uninvoiceQty = $quantity - $qtyinvoiced;
					
					if ($isrefund == "Y") {
						$uninvoiceQty = $qtyinvoiced*-1;
					}
					$uninvoiceLinecost = $uninvoiceQty * $unitcost;
					$taxSet = new Set();
					$taxcost = $taxSet->MboSetRemote("tax")->setWhere("taxcode = '" . $poline->getString("tax") . "'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
					if($taxcost == null) $taxcost=0;
					$uninvoicedTax = $uninvoiceLinecost * ($taxcost / 100);
					if ($uninvoiceQty == 0) {
						$tbody = "";
					}
					else {
						if($uninvoiceQty == 0 && $isrefund == "Y"){
							$tbody = "";
						}else{
							$tbody = "<tr>
									<td data-attribute='polinenum'>" . $poline->getString("polinenum") . "</td>
									<td data-attribute='linetype'>" . $poline->getString("linetype") . "</td>
									<td data-attribute='itemnum'>" . $poline->getString("itemnum") . "</td>
									<td data-attribute='description'>" . $poline->getString("description") . "</td>
									<td data-attribute='orderqty' data-type='decimal'>" . $uninvoiceQty . "</td>
									<td data-attribute='orderunit'>" . $poline->getString("orderunit") . "</td>
									<td data-attribute='unitcost' data-type='decimal'>" . $unitcost . "</td>
									<td data-attribute='linecost' data-type='decimal'>" . $uninvoiceLinecost . "</td>
									<td data-attribute='taxcost' data-type='decimal'>" . $uninvoicedTax . "</td>
									<td data-attribute='apaccount' >" . $apaccount . "</td>
									<td data-attribute='glcreditacct' >" . $poline->getString("glcreditacct") . "</td>
									<td data-attribute='ponum' >" . $poline->getString("ponum") . "</td>
									<td class='hide'>" . $poline->getString("polineid") . "</td>
							</tr>";
							array_push($tbodArr, $tbody);
						}
					}
				}
				$tbody = "<tbody>" . implode(" ", $tbodArr) . "</tbody>";
			}
			$renderTable = $thead.$tbody .$tfoot;
			echo $renderTable;
		}
		if ($mbosource == "soline") {
			$thead = "<thead>
						<tr>
							<th>SO Line</th>
							<th>Line Type</th>
							<th>Item</th>
							<th>Description</th>
							<th>Quantity</th>
							<th>Sale Unit</th>
							<th>Unit Price</th>
							<th>Line Price</th>
							<th>Tax Price</th>
							<th>SO</th>
							<th class='hide'>solineid</th>
						</tr>
					</thead>";
			$tfoot = "<tfoot>
						<tr>
							<th>SO Line</th>
							<th>Line Type</th>
							<th>Item</th>
							<th>Description</th>
							<th>Quantity</th>
							<th>Sale Unit</th>
							<th>Unit Price</th>
							<th>Line Price</th>
							<th>Tax Price</th>
							<th>SO</th>
							<th class='hide'>solineid</th>
						</tr>
					</tfoot>";
			$sonum = $key;
			$tbody = "";
			$solineSet = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum'")->MboRemote("solinenum")->MboRemote("linetype")->MboRemote("itemnum")->MboRemote("description")
			->MboRemote("sellqty")->MboRemote("orderunit")->MboRemote("unitprice")->MboRemote("lineprice")->MboRemote("taxcode")->MboRemote("taxprice")->MboRemote("sonum")
			->MboRemote("solineid")->getMboSet();
			
			
			if ($solineSet->counts() == 0) {
				$tbody = "";
			}
			else {
				$tbodArr = array();
				
				for ($i = 0; $i < $solineSet->counts(); $i++) {
					$soline = $solineSet->getMbo();
					$linetype = $soline->getString("linetype");
					
					if ( $linetype == "services") {
						$sellunit = $soline->getString("orderunit");
					}
					if ( $linetype == "item") {
						$set2 = new Set();
						$sellunit = $set2->MboSetRemote("item")->setWhere("itemnum='" . $soline->getString("itemnum") . "'")->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
					}
					$solinenum = $soline->getString("solinenum");
					$unitprice = $soline->getString("unitprice");
					$sellqty = $soline->getString("sellqty");
					
					$taxcode = $soline->getString("taxcode");
					$itemnum = $soline->getString("itemnum");
					$description = $soline->getString("description");
					$sonum = $soline->getString("sonum");
					$solineid = $soline->getString("solineid");
					
					$ss = new Set();
					$ivclineSet = $ss->MboSetRemote("invoiceline")->setRelationship("invoice", "invoice.invoicenum = invoiceline.invoicenum")
								->setWhere("invoiceline.sonum='$key' and invoiceline.invoicelinenum='$solinenum' ")->group("invoiceline.invoicelinenum")
								->MboRemote("sum(invoiceline.quantity) as qtyinvoiced")->getMboSet();
								//and invoice.refund='$isrefund'
					$ivcline = $ivclineSet->getMbo();
					$qtyinvoiced = $ivcline->getString("qtyinvoiced");
					
					if (empty($qtyinvoiced)) $qtyinvoiced = 0;
					$uninvoiceQty = $sellqty - $qtyinvoiced;
					
					if($isrefund == "Y"){
						$uninvoiceQty = $qtyinvoiced;
						$uninvoiceQty = $uninvoiceQty*-1;
					}
					$uninvoiceLinecost = $uninvoiceQty * $unitprice;
					
					$taxSet = new Set();
					$taxcost = $taxSet->MboSetRemote("tax")->setWhere("taxcode = '" . $taxcode . "'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
					if(empty($taxcost)) $taxcost = 0;
					$uninvoicedTax = $uninvoiceLinecost * ($taxcost / 100);
					
					if ($uninvoiceQty == 0) {
						$tbody = "";
					}
					else {
						$tbody = "<tr>
									<td data-attribute='solinenum'>" . $solinenum . "</td>
									<td data-attribute='linetype'>" . $linetype . "</td>
									<td data-attribute='itemnum'>" . $itemnum. "</td>
									<td data-attribute='description'>" . $description . "</td>
									<td data-attribute='sellqty' data-type='decimal'>" . $uninvoiceQty . "</td>
									<td data-attribute='sellunit' >" . $sellunit . "</td>
									<td data-attribute='unitprice' data-type='decimal'>" . $unitprice . "</td>
									<td data-attribute='lineprice' data-type='decimal'>" . $uninvoiceLinecost . "</td>
									<td data-attribute='taxprice' data-type='decimal'>" . $uninvoicedTax . "</td>
									<td data-attribute='sonum'>" . $sonum . "</td>
									<td data-attribute='solineid' class='hide'>" . $solineid . "</td>
								</tr>";
						array_push($tbodArr, $tbody);
					}
				}
				$tbody = "<tbody>" . implode(" ", $tbodArr) . "</tbody>";
			}
			$renderTable = $thead.$tbody .$tfoot;
			echo $renderTable;
		}
	
		if ($mbosource == "matsaletrans") {
			//requires to validate previous invoice
			$thead = "<thead>
						<tr>
							<th>SO Line</th>
							<th>Line Type</th>
							<th>Item</th>
							<th>Description</th>
							<th>Tgl. FIFO/LIFO</th>
							<th>Quantity</th>
							<th>Sale Unit</th>
							<th>Unit Price</th>
							<th>Line Price</th>
							<th>Tax Price</th>
							<th>Warehouse</th>
							<th>SO</th>
						</tr>
					</thead>";
			$tfoot = "<tfoot>
						<tr>
							<th>SO Line</th>
							<th>Line Type</th>
							<th>Item</th>
							<th>Description</th>
							<th>Tgl. FIFO/LIFO</th>
							<th>Quantity</th>
							<th>Sale Unit</th>
							<th>Unit Price</th>
							<th>Line Price</th>
							<th>Tax Price</th>
							<th>Warehouse</th>
							<th>SO</th>
						</tr>
					</tfoot>";
			$tbody = "";
			$matsaleSet = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")
			->setWhere("sonum='$key'")->group("matsaletrans.solinenum, matsaletrans.costdate")->MboRemote("solinenum")->MboRemote("linetype")->MboRemote("itemnum")->MboRemote("description")
			->MboRemote("sum(quantity) as quantity")->MboRemote("unitprice")->MboRemote("costdate")->MboRemote("warehouse")->getMboSet();
			if ($matsaleSet->counts() == 0) {
				$tbody = "";
			}
			else {
				$tbodArr = array();
				for ($i = 0; $i < $matsaleSet->counts(); $i++) {
					$matsale = $matsaleSet->getMbo();
					$solinenum = $matsale->getString("solinenum");
					$linetype = $matsale->getString("linetype");
					$itemnum = $matsale->getString("itemnum");
					$itemdesc = $matsale->getString("description");
					$quantity = $matsale->getString("quantity");
					$costdate = $matsale->getString("costdate");
					$unitprice = $matsale->getString("unitprice");
					$warehouse = $matsale->getString("warehouse");
					$slineset = new Set();
					$soline = $slineset->MboSetRemote("soline")->setWhere("sonum='$key' and solinenum='$solinenum'")->MboRemote("orderunit")->MboRemote("taxcode")->MboRemote("sellqty")->getMboSet()->getMbo();
					$taxcode = $soline->getString("taxcode");
					$sellqty = $soline->getString("sellqty");
					$wheredate = Super::whereDate("costdate", $costdate);
					$taxSet = new Set();
					$taxcost = $taxSet->MboSetRemote("tax")->setWhere("taxcode = '" . $taxcode . "'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
					if ($linetype == "services") {
						$sellunit = $soline->getMboSet()->getMbo()->getString("orderunit");
					}
					if ($linetype == "item") {
						$set2 = new Set();
						$sellunit = $set2->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
					}
					
					
					/////
					/////
					$ss = new Set();
					$ivclineSet = $ss->MboSetRemote("invoiceline")->setRelationship("invoice", "invoice.invoicenum = invoiceline.invoicenum")
								->setWhere("invoiceline.sonum='$key' and invoiceline.invoicelinenum='$solinenum' $wheredate")->group("invoiceline.invoicelinenum")
								->MboRemote("sum(invoiceline.quantity) as qtyinvoiced")->getMboSet();
								
					
					$cntivclineSet = $ivclineSet->counts();
					if($cntivclineSet == 0){
						//advance payment
						$ivclineSet = $ss->MboSetRemote("invoiceline")->setRelationship("invoice", "invoice.invoicenum = invoiceline.invoicenum")
								->setWhere("invoiceline.sonum='$key' and invoiceline.invoicelinenum='$solinenum'")->group("invoiceline.invoicelinenum")
								->MboRemote("sum(invoiceline.quantity) as qtyinvoiced")->getMboSet();
					}
						
					$ivcline = $ivclineSet->getMbo();
					$qtyinvoiced = $ivcline->getString("qtyinvoiced");
								
					if (empty($qtyinvoiced)) $qtyinvoiced = 0;
					$quantity = $quantity * -1;
					
					if($isrefund == "Y"){
						$qtyinvoiced = $qtyinvoiced*-1;
					}
					
					
					$uninvoiceQty = $quantity - $qtyinvoiced;
					
					if ($isrefund == "Y") { 
						$uninvoiceQty = $quantity - $qtyinvoiced;
						$uninvoiceQty = $uninvoiceQty*-1;
					}
					$uninvoiceLinecost = $uninvoiceQty * $unitprice;
					$uninvoicedTax = $uninvoiceLinecost * ($taxcost / 100);
					
					if ($uninvoiceQty <= 0 && $isrefund == "N") {
						$tbody = "";
					}
					else {
						if($isrefund == "Y" && $uninvoiceQty ==0 ){
							$tbody = "";
						}else{
							$tbody = "<tr>
									<td data-attribute='solinenum'>" . $solinenum . " </td>
									<td data-attribute='linetype'>" . $linetype . "</td>
									<td data-attribute='itemnum'>" . $itemnum . "</td>
									<td data-attribute='description'>" . $itemdesc . "</td>
									<td data-attribute='costdate'>" . $costdate . "</td>
									<td data-attribute='sellqty' data-type='decimal'>" . $uninvoiceQty . "</td>
									<td data-attribute='sellunit' >" . $sellunit . "</td>
									<td data-attribute='unitprice' data-type='decimal'>" . $unitprice . "</td>
									<td data-attribute='lineprice' data-type='decimal'>" . $uninvoiceLinecost . "</td>
									<td data-attribute='taxprice' data-type='decimal'>" . $uninvoicedTax . "</td>
									<td data-attribute='warehouse'>" . $warehouse . "</td>
									<td data-attribute='sonum'>" . $key . "</td>
								</tr>";
						}
						array_push($tbodArr, $tbody);
					}
				}
				$tbody = "<tbody>" . implode(" ", $tbodArr) . "</tbody>";
			}
			$renderTable = $thead.$tbody.$tfoot;
			echo $renderTable;
		}
		if ($mbosource == "matrectrans") {
			
			$matrecSet = (new Set())->useTransactionManager()->MboSetRemote("matrectrans")->setWhere("ponum='$key'")->group("matrectrans.polinenum")
			->MboRemote("polinenum")->MboRemote("linetype")->MboRemote("itemnum")->MboRemote("description")
			->MboRemote("sum(quantity) as quantity")->MboRemote("unitcost")->MboRemote("warehouse")->getMboSet();
			$thead = "<thead>
						<tr>
							<th>PO Line</th>
							<th>Line Type</th>
							<th>Item</th>
							<th>Description</th>
							<th>Quantity</th>
							<th>Sale Unit</th>
							<th>Unit Price</th>
							<th>Line Price</th>
							<th>Tax Price</th>
							<th>PO</th>
							<th>Warehouse</th>
						</tr>
					</thead>";
			$tfoot = "<tfoot>
						<tr>
							<th>PO Line</th>
							<th>Line Type</th>
							<th>Item</th>
							<th>Description</th>
							<th>Quantity</th>
							<th>Order Unit</th>
							<th>Unit Cost</th>
							<th>Line Cost</th>
							<th>Tax Cost</th>
							<th>Warehouse</th>
							<th>PO</th>
						</tr>
					</tfoot>";
			$tbody = "";
			if ($matrecSet->counts() == 0) {
				$tbody = "";
			}
			else {
				$tbodArr = array();
				for ($i = 0; $i < $matrecSet->counts(); $i++) {
					$matrec = $matrecSet->getMbo();
					$polinenum = $matrec->getString("polinenum");
					$linetype = $matrec->getString("linetype");
					$itemnum = $matrec->getString("itemnum");
					$itemdesc = $matrec->getString("description");
					$quantity = $matrec->getString("quantity");
					$unitcost = $matrec->getString("unitcost");
					$warehouse = $matrec->getString("warehouse");
					$plineset = new Set();
					$poline = $plineset->MboSetRemote("poline")->setWhere("ponum='$key' and polinenum='$polinenum'")->MboRemote("orderunit")->MboRemote("tax")->getMboSet()->getMbo();
					$taxcode = $poline->getString("tax");
					$taxSet = new Set();
					$taxcost = $taxSet->MboSetRemote("tax")->setWhere("taxcode = '" . $taxcode . "'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
					if($taxcost == null) $taxcost=0;
					if ($linetype == "services") {
						$orderunit = $poline->getMboSet()->getMbo()->getString("orderunit");
					}
					if ($linetype == "item") {
						$set2 = new Set();
						$orderunit = $set2->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
					}
					$set3 = new Set();
					$qtyinvoiced = $set3->MboSetRemote("invoiceline")->setWhere("ponum='$key' and invoicelinenum='$polinenum'")->group("invoiceline.invoicelinenum")->MboRemote("sum(quantity) as qtyinvoiced")->getMboSet()->getMbo()->getString("qtyinvoiced");
					if (empty($qtyinvoiced)) $qtyinvoiced = 0;
					$uninvoiceQty = $quantity - $qtyinvoiced;
					if ($isrefund == "Y") $uninvoiceQty = $uninvoiceQty * -1;
					$uninvoiceLinecost = $uninvoiceQty * $unitcost;
					if($uninvoiceLinecost == null) $uninvoiceLinecost=0;
					$uninvoicedTax = $uninvoiceLinecost * ($taxcost / 100);
					if ($uninvoiceQty <= 0 && $isrefund == "N") {
						$tbody = "";
					}
					else {
						if($uninvoiceQty <= 0 && $isrefund == "Y"){
							$tbody = "";
						}else{
							$tbody = "<tr>
									<td data-attribute='polinenum'>" . $polinenum . "</td>
									<td data-attribute='linetype'>" . $linetype . "</td>
									<td data-attribute='itemnum'>" . $itemnum . "</td>
									<td data-attribute='description'>" . $itemdesc . "</td>
									<td data-attribute='orderqty' data-type='decimal'>" . $uninvoiceQty . "</td>
									<td data-attribute='orderunit' >" . $orderunit . "</td>
									<td data-attribute='unitcost' data-type='decimal'>" . $unitcost . "</td>
									<td data-attribute='linecost' data-type='decimal'>" . $uninvoiceLinecost . "</td>
									<td data-attribute='taxcost' data-type='decimal'>" . $uninvoicedTax . "</td>
									<td data-attribute='warehouse'>" . $warehouse . "</td>
									<td data-attribute='ponum'>" . $key . "</td>
								</tr>";
							array_push($tbodArr, $tbody);
						}
						
					}
				}
				$tbody = "<tbody>" . implode(" ", $tbodArr) . "</tbody>";
			}
			$renderTable = $thead.$tbody.$tfoot;
			echo $renderTable;
		}
		if ($mbosource == "servsaletrans") {
			$thead = "<thead>
						<tr>
							<th>SO Line</th>
							<th>Line Type</th>
							<th>Description</th>
							<th>Quantity</th>
							<th>Sale Unit</th>
							<th>Unit Price</th>
							<th>Line Price</th>
							<th>Tax Price</th>
							<th>GL Debit</th>
							<th>GL Credit</th>
							<th>SO</th>
						</tr>
					</thead>";
			$tfoot = "<tfoot>
						<tr>
							<th>SO Line</th>
							<th>Line Type</th>
							<th>Description</th>
							<th>Quantity</th>
							<th>Sale Unit</th>
							<th>Unit Price</th>
							<th>Line Price</th>
							<th>Tax Price</th>
							<th>GL Debit</th>
							<th>GL Credit</th>
							<th>SO</th>
						</tr>
					</tfoot>";
			$tbody = "";
			$servSaleSet = (new Set())->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("sonum='$key'")->group("servsaletrans.solinenum")->MboRemote("solinenum")->MboRemote("linetype")->MboRemote("description")->MboRemote("sum(quantity) as quantity")->MboRemote("unitprice")->MboRemote("gldebitacct")->MboRemote("glcreditacct")->getMboSet();
			if ($servSaleSet->counts() == 0) {
				$tbody = "";
			}
			else {
				$tbodArr = array();
				for ($i = 0; $i < $servSaleSet->counts(); $i++) {
					$servsale = $servSaleSet->getMbo();
					$solinenum = $servsale->getString("solinenum");
					$linetype = $servsale->getString("linetype");
					$itemdesc = $servsale->getString("description");
					$quantity = $servsale->getString("quantity");
					$unitprice = $servsale->getString("unitprice");
					$gldebitacct = $servsale->getString("gldebitacct");
					$glcreditacct = $servsale->getString("glcreditacct");
					$slineset = new Set();
					$soline = $slineset->MboSetRemote("soline")->setWhere("sonum='$key' and solinenum='$solinenum'")->MboRemote("orderunit")->MboRemote("taxcode")->getMboSet()->getMbo();
					$taxcode = $soline->getString("taxcode");
					$taxSet = new Set();
					$taxcost = $taxSet->MboSetRemote("tax")->setWhere("taxcode = '" . $taxcode . "'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
					if(empty($taxcost)) $taxcost = 0;
					if ($linetype == "services") {
						$saleunit = $soline->getString("orderunit");
					}
					$set3 = new Set();
					$qtyinvoiced = $set3->MboSetRemote("invoiceline")->setWhere("sonum='$key' and invoicelinenum='$solinenum'")->group("invoiceline.invoicelinenum")->MboRemote("sum(quantity) as qtyinvoiced")->getMboSet()->getMbo()->getString("qtyinvoiced");
					if (empty($qtyinvoiced)) $qtyinvoiced = 0;
					$quantity = $quantity;
					$uninvoiceQty = $quantity - $qtyinvoiced;
					if ($isrefund == "Y") $uninvoiceQty = $quantity * -1;
					$uninvoiceLinecost = $uninvoiceQty * $unitprice;
					$uninvoicedTax = $uninvoiceLinecost * ($taxcost / 100);
					if ($uninvoiceQty <= 0 && $isrefund == "N") {
						$tbody = "";
					}
					else {
						$tbody = "<tr>
									<td data-attribute='solinenum'>" . $solinenum . "</td>
									<td data-attribute='linetype'>" . $linetype . "</td>
									<td data-attribute='description'>" . $itemdesc . "</td>
									<td data-attribute='sellqty' data-type='decimal'>" . $uninvoiceQty . "</td>
									<td data-attribute='saleunit' >" . $saleunit . "</td>
									<td data-attribute='unitprice' data-type='decimal'>" . $unitprice . "</td>
									<td data-attribute='lineprice' data-type='decimal'>" . $uninvoiceLinecost . "</td>
									<td data-attribute='taxprice' data-type='decimal'>" . $uninvoicedTax . "</td>
									<td data-attribute='gldebitacct'>" . $gldebitacct . "</td>
									<td data-attribute='glcreditacct'>" . $glcreditacct . "</td>
									<td data-attribute='sonum'>" . $key . "</td>
								</tr>";
						array_push($tbodArr, $tbody);
					}
				}
				$tbody = "<tbody>" . implode(" ", $tbodArr) . "</tbody>";
			}
			$renderTable = $thead.$tbody .$tfoot;
			echo $renderTable;
		}
		if ($mbosource == "servrectrans") {
			
			$thead = "<thead>
						<tr>
							<th>PO Line</th>
							<th>Line Type</th>
							<th>Description</th>
							<th>Quantity</th>
							<th>Order Unit</th>
							<th>Unit Cost</th>
							<th>Line Cost</th>
							<th>Tax Cost</th>
							<th>GL Debit</th>
							<th>GL Credit</th>
							<th>PO</th>
						</tr>
					</thead>";
			$tfoot = "<tfoot>
						<tr>
							<th>PO Line</th>
							<th>Line Type</th>
							<th>Description</th>
							<th>Quantity</th>
							<th>Sale Unit</th>
							<th>Unit Cost</th>
							<th>Line Cost</th>
							<th>Tax Cost</th>
							<th>GL Debit</th>
							<th>GL Credit</th>
							<th>PO</th>
						</tr>
					</tfoot>";
			$tbody = "";
			$servrecSet = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->setWhere("ponum='$key'")->group("servrectrans.polinenum")->MboRemote("polinenum")->MboRemote("linetype")->MboRemote("description")->MboRemote("sum(quantity) as quantity")->MboRemote("unitcost")->MboRemote("gldebitacct")->MboRemote("glcreditacct")->getMboSet();
			if ($servrecSet->counts() == 0) {
				$tbody = "";
			}
			else {
				$tbodArr = array();
				for ($i = 0; $i < $servrecSet->counts(); $i++) {
					$servrec = $servrecSet->getMbo();
					$polinenum = $servrec->getString("polinenum");
					$linetype = $servrec->getString("linetype");
					$itemdesc = $servrec->getString("description");
					$quantity = $servrec->getString("quantity");
					$unitcost = $servrec->getString("unitcost");
					$gldebitacct = $servrec->getString("gldebitacct");
					$glcreditacct = $servrec->getString("glcreditacct");
					$plineSet = new Set();
					$poline = $plineSet->MboSetRemote("poline")->setWhere("ponum='$key' and polinenum='$polinenum'")->MboRemote("orderunit")->MboRemote("tax")->getMboSet()->getMbo();
					$taxcode = $poline->getString("tax");
					$taxSet = new Set();
					$taxcost = $taxSet->MboSetRemote("tax")->setWhere("taxcode = '" . $taxcode . "'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
					if ($linetype == "services") {
						$orderunit = $poline->getString("orderunit");
					}
					$set3 = new Set();
					$qtyinvoiced = $set3->MboSetRemote("invoiceline")->setWhere("ponum='$key' and invoicelinenum='$polinenum'")->group("invoiceline.invoicelinenum")->MboRemote("sum(quantity) as qtyinvoiced")->getMboSet()->getMbo()->getString("qtyinvoiced");
					
					if (empty($qtyinvoiced)) $qtyinvoiced = 0;
					$quantity = $quantity;
					$uninvoiceQty = $quantity - $qtyinvoiced;
					if ($isrefund == "Y") {
						$uninvoiceQty = $qtyinvoiced*-1;
					}
					
					$uninvoiceLinecost = $uninvoiceQty * $unitcost;
					$uninvoicedTax = $uninvoiceLinecost * ($taxcost / 100);
					if ($uninvoiceQty <= 0 && $isrefund == "N") {
						$tbody = "";
					}
					
					else {
						if($uninvoiceQty == 0 && $isrefund == "Y"){
							$tbody = "";
						}else{
							$tbody = "<tr>
									<td data-attribute='polinenum'>" . $polinenum . "</td>
									<td data-attribute='linetype'>" . $linetype . "</td>
									<td data-attribute='description'>" . $itemdesc . "</td>
									<td data-attribute='orderqty' data-type='decimal'>" . $uninvoiceQty . "</td>
									<td data-attribute='orderunit' >" . $orderunit . "</td>
									<td data-attribute='unitcost' data-type='decimal'>" . $unitcost . "</td>
									<td data-attribute='linecost' data-type='decimal'>" . $uninvoiceLinecost . "</td>
									<td data-attribute='taxcost' data-type='decimal'>" . $uninvoicedTax . "</td>
									<td data-attribute='gldebitacct'>" . $gldebitacct . "</td>
									<td data-attribute='glcreditacct'>" . $glcreditacct . "</td>
									<td data-attribute='ponum'>" . $key . "</td>
								</tr>";
							array_push($tbodArr, $tbody);
						}
					}
				}
				$tbody = "<tbody>" . implode(" ", $tbodArr) . "</tbody>";
			}
			$renderTable = $thead.$tbody .$tfoot;
			echo $renderTable;
		}
	}
	public static function changePassword(){
		
		$payload = Super::getParam("changePassword");
		$username = $payload["username"];
		
		
		if(!$payload) {
			Super::sendJsonResponse("error", "No Payload sent.");
		}

		if(empty($username)){
			Super::sendJsonResponse("error", "Username is not sent.");
		}

		 try {
			
			$newpassword =  $payload["newpassword"];
			$confirmPassword =  $payload["confirmnewpassword"];

			if (empty($newpassword) || empty($confirmPassword)) {
                Super::sendJsonResponse("error", "New Pasword And Confirm Password cannot be null");
           
            }

            if ($newpassword !== $confirmPassword) {
                Super::sendJsonResponse("error", "New Password And Confirm Password is not match.");
               
            }

            if (strlen($newpassword) < 8) {
                Super::sendJsonResponse("error", "Password must be 8 characters minimal.");
           
            }

			$newPasswordHash = password_hash($newpassword, PASSWORD_DEFAULT);

			(new Set())->useTransactionManager()
					->MboSetRemote("mxsusers")
					->where("userid = ?", $username)
					->setValue("password_hash", $newPasswordHash)
					->setValue("password", null)
					->setValue("changedate", Xbo::setEnterDate())
					->setValue("changeby", Xbo::getLoginUser())
					->change();

			 Super::sendJsonResponse("success", "Password has been changed.");
		 }catch (Exception $e) {
            // Tangani error umum atau error dari Auth::validateToken()
            Super::sendJsonResponse("error", "Something Wrong: " . $e->getMessage());
        }
	
	}

	
	public static function receiptInput(){
		$a = explode("|", urldecode(Super::getParam('receiptInput')));
		$table = $a[2];
		$x = explode("`", $a[0]);
		$y = explode("`", $a[3]);
		$z = $a[1];
		$cls1 = $x[0] . "='" . $x[1] . "'";
		$cls2 = $y[0] . "='" . $y[1] . "'";
		
		
		$set = new Set();
		if ($x[1] == 'item') {
			if ($z == 'receipt') {
				$polineSet = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere(" $cls1 and $cls2 ")->MboRemote("ponum")->MboRemote("polinenum")->MboRemote("itemnum")->MboRemote("description")->MboRemote("orderqty")->MboRemote("orderunit")->MboRemote("warehouse")->MboRemote("conversionfactor")->MboRemote("polineid")->getMboSet();
				$datacount = $polineSet->counts();
				$tbodyArr = array();
				for ($i = 0; $i < $datacount; $i++) {
					$poline = $polineSet->getMbo();
					$polinenum = $poline->getString("polinenum");
					$itemnum = $poline->getString("itemnum");
					$description = $poline->getString("description");
					$orderqty = $poline->getString("orderqty");
					$orderunit = $poline->getString("orderunit");
					$warehouse = $poline->getString("warehouse");
					$conversionfactor = $poline->getString("conversionfactor");
					if(empty($conversionfactor)){
						$conversionfactor=1;
					}
					$polineid = $poline->getString("polineid");
					
					$set2 = new Set();
					$quantityReturn = $set2->MboSetRemote("matrectrans")->setWhere("$cls1 and $cls2 and polinenum = '$polinenum' and transtype = 'RETURN'")
					->group("polinenum")->MboRemote("sum(quantity) as quantity")->getMboSet()->getMbo()->getString("quantity");
					
					
					if(empty($quantityReturn)) {
						$quantityReturn = 0;
						$quantityReturnConversion = 0;
					}else{
						$quantityReturnConversion = $quantityReturn/$conversionfactor;
					}
					
					
					
					$set2 = new Set();
					$quantityReceipt = $set2->MboSetRemote("matrectrans")->setWhere("$cls1 and $cls2 and polinenum = '$polinenum' and transtype = 'RECEIPT'")
					->group("polinenum")->MboRemote("sum(quantity) as quantity")->getMboSet()->getMbo()->getString("quantity");
					
					
					if(empty($quantityReceipt)) {
						$quantityReceipt = 0;
					}
					
					$quantity = $quantityReturn+$quantityReceipt;
					
					if (empty($quantity)) $quantity = 0;
					$restQty = $orderqty - $quantity;
					if ($restQty == 0) {
						$tbody = " ";
					}
					else {
						if($quantity == 0) $restQty = $restQty;
						else $restQty = $restQty;
						
						$batchnum = Xbo::GetBatchNum();	
						
						$tbody = "<tr> 
							<td data-attribute='polinenum'>" . $polinenum . "</td>
							<td data-attribute='itemnum'>" . $itemnum . "</td>
							<td data-attribute='description'>" . $description . "</td>
							<td data-attribute='restqty'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"restqty\" value='" . $restQty . " ' style=\"background:rgb(255,255,255); width:80px\"></td>
							<td data-attribute='orderunit'>" . $orderunit . "</td>
							<td data-attribute='batchnum'><input data-attribute='batchnum' value='".$batchnum."'></td>
							<td data-attribute='warehouse'><input data-attribute='warehouse' data-lookup='warehouse' value='".$warehouse."'></td>
							<td data-attribute='racknum'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"racknum\" data-lookup = \"rack|warehouse\" style=\"background:rgb(255,255,255); width:80px\"><input type=\"hidden\" data-attribute=\"warehouse\" value=\"" . $warehouse . "\"></td>
							<td data-attribute='warehouse'><input data-attribute='actualdate' data-type='date'></td>
							<td data-attribute='warehouse'><input data-attribute='remarks'></td>
							
							<td data-attribute='polineid' class='hide'>" . $polineid . "</td>
							</tr>";
					}
					array_push($tbodyArr, $tbody);
				}
				$tbodyIm = implode(" ", $tbodyArr);
				$tbody = "<tbody>" . $tbodyIm . "</tbody>";
				$thead = "<thead><tr>
							<th>No.</th>
							<th>Item</th>
							<th>Deskripsi</th>
							<th>Jml. Diterima</th>
							<th>Satuan Order</th>
							<th>Batch No.</th>
							<th>Gudang</th>
							<th>Rak</th>
							<th>Tgl. Diterima</th>
							<th>Catatan</th>
							<th class='hide'>polineid</th>
							</tr></thead>";
				$tfoot = "<tfoot><tr>
							<th>No.</th>
							<th>Item</th>
							<th>Deskripsi</th>
							<th>Jml. Diterima</th>
							<th>Satuan Order</th>
							<th>Batch No.</th>
							<th>Gudang</th>
							<th>Rak</th>
							<th>Tgl. Diterima</th>
							<th>Catatan</th>
							<th class='hide'>polineid</th>
							</tr>
					</tfoot>";
				$renderTable = $thead . $tfoot . $tbody;
				echo $renderTable;
			}
			if ($z == "return") {
				$matrecSet = (new Set())->useTransactionManager()->MboSetRemote("matrectrans")->setRelationship("item", "matrectrans.itemnum = item.itemnum")
				->setRelationship("poline", "matrectrans.ponum = poline.ponum and matrectrans.polinenum = poline.polinenum")
				->setWhere("matrectrans.$cls1 and matrectrans.$cls2 ")->group("matrectrans.ponum, matrectrans.polinenum")
				->MboRemote("matrectrans.polinenum")->MboRemote("matrectrans.itemnum")->MboRemote("matrectrans.description")
				->MboRemote("sum(quantity) as restqty")->MboRemote("matrectrans.warehouse")->MboRemote("matrectrans.racknum")
				->MboRemote("matrectrans.matrectransid")
				->MboRemote("item.issueunit")->MboRemote("poline.conversionfactor")
				->getMboSet();
				$datacount = $matrecSet->counts();
				$tbody = "";
				$tbodyArr = array();
				for ($i = 0; $i < $datacount; $i++) {
					$matrec = $matrecSet->getMbo();
					$polinenum = $matrec->getString("polinenum");
					$itemnum = $matrec->getString("itemnum");
					$description = $matrec->getString("description");
					$restqty = $matrec->getString("restqty");
					$warehouse = $matrec->getString("warehouse");
					$racknum = $matrec->getString("racknum");
					$returnUnit = $matrec->getString("issueunit");
					$conversionfactor = $matrec->getString("conversionfactor");
					$matrectransid = $matrec->getString("matrectransid");
					$conditiontypetd = "<td data-attribute='conditiontype'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"conditiontype\" data-lookup = \"itemcondition|itemnum\" style=\"background:rgb(255,255,255); width:80px\"></td>";
					
					$setic = new Set();
					$itemConditionSet = $setic->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' and conditionrate=100")->MboRemote("conditionrate")->getMboSet();
					$countic = $itemConditionSet->counts();
					
					if($countic >0){
						$conditiontypetd = $conditiontypetd;
					}else{
						$conditiontypetd = "<td data-attribute='conditiontype'></td>";
					}
					if(empty($conversionfactor)){
						$conversionfactor=1;
					}
					//matrecreturn fifo
					
					$st = new Set();
					$inventorySet = $st->MboSetRemote("inventory")->setWhere("itemnum ='$itemnum' and warehouse='$warehouse'")->MboRemote("issuetype")->getMboSet();
					$inventory = $inventorySet->getMbo();
					$issuetype = $inventory->getString("issuetype");
					
					if($issuetype == "FIFO" || $issuetype == "LIFO"){
						$mrt = new Set();
						$matrecreturnSet = $mrt->MboSetRemote("matrectrans")->setWhere("itemnum='$itemnum' and $cls2")->group("matrectrans.batchnum")
						->MboRemote("sum(quantity) as quantity")->MboRemote("costdate")
						->MboRemote("batchnum")->MboRemote("polinenum")->MboRemote("issuetype")->MboRemote("matrectransid")->getMboSet();
						
						$conmt = $matrecreturnSet->counts();
						for($j=0; $j<$conmt; $j++){
							$matrecreturn = $matrecreturnSet->getMbo();
							$costdate = $matrecreturn->getString("costdate");
							$issuetype2 = $matrecreturn->getString("issuetype");
							$quantity = $matrecreturn->getString("quantity");	
							$batchnum = $matrecreturn->getString("batchnum");
							$matrectransid = $matrecreturn->getString("matrectransid");
							if ($quantity == 0) {
								$tbody = "";
							}else{
								$recqtyconversion = $quantity*$conversionfactor;
								$tbody = "<tr> 
										<td data-attribute='polinenum'>" . $polinenum . "</td>
										<td data-attribute='itemnum'>" . $itemnum . "</td>
										<td data-attribute='description'>" . $description . "</td>
										<td data-attribute='issuetype'>" . $issuetype2 . "</td>
										<td data-attribute='batchnum'>$batchnum</td>
										<td data-attribute='costdate' data-type='date'>$costdate</td>
										<td data-attribute='restqty'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"restqty\" value='" . $recqtyconversion . " ' style=\"background:rgb(255,255,255); width:80px\"></td>
										<td data-attribute='itemnum.issueunit'>" . $returnUnit . "</td>
										$conditiontypetd
										<td data-attribute='warehouse'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"warehouse\" data-lookup = \"warehouse\" style=\"background:rgb(255,255,255); width:80px\"></td>
										<td data-attribute='racknum'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"racknum\" data-lookup = \"rack|itemnum,conditiontype,warehouse\" style=\"background:rgb(255,255,255); width:80px\"></td>
										<td data-attribute='actualdate'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"actualdate\"  data-type='date'></td>
										<td data-attribute='remarks'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"remarks\" ></td>
										
										<td data-attribute='matrectransid' class='hide'>" . $matrectransid . "</td>
									</tr>";
									array_push($tbodyArr, $tbody);
								
							}
						}
					}else{
						if ($restqty == 0) {
							$tbody = "";
						}
						else {
							$conversionfactor = $conversionfactor;
							$restqtyConversion = $restqty * $conversionfactor;
							$tbody = "<tr> 
								<td data-attribute='polinenum'>" . $polinenum . "</td>
								<td data-attribute='itemnum'>" . $itemnum . "</td>
								<td data-attribute='description'>" . $description . "</td>
								<td data-attribute='issuetype'>" . $issuetype . "</td>
								<td data-attribute='batchnum'></td>
								<td data-attribute='costdate' data-type='date'></td>
								<td data-attribute='restqty'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"restqty\" value='" . $restqtyConversion . " ' style=\"background:rgb(255,255,255); width:80px\"></td>
								<td data-attribute='itemnum.issueunit'>" . $returnUnit . "</td>
								$conditiontypetd
								<td data-attribute='warehouse'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"warehouse\" data-lookup = \"warehouse\" style=\"background:rgb(255,255,255); width:80px\"></td>
								<td data-attribute='racknum'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"racknum\" data-lookup = \"rack|itemnum,conditiontype,warehouse\" style=\"background:rgb(255,255,255); width:80px\"></td>
								<td data-attribute='actualdate'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"actualdate\"  data-type='date'></td>
								<td data-attribute='remarks'><input type=\"text\" class=\"nonpersistent\" data-attribute=\"remarks\" ></td>
								<td data-attribute='matrectransid' class='hide'>" . $matrectransid . "</td>
							</tr>";
						}
						array_push($tbodyArr, $tbody);
					}
					//
				}
				$tbodyIm = implode(" ", $tbodyArr);
				$tbody = "<tbody>" . $tbodyIm . "</tbody>";
				$thead = "<thead><tr>
								<th>Baris</th>
								<th>Item</th>
								<th>Deskripsi</th>
								<th>Tipe Issue</th>
								<th>No. Batch</th>
								<th>Tgl. Masuk</th>
								<th>Jml. yg Dikembalikan</th>
								<th>Satuan</th>
								<th>Condition Type</th>
								<th>Gudang</th>
								<th>Rak</th>
								<th>Tgl. Return</th>
								<th>Catatan</th>
								<th  class='hide'>Line</th>
								</tr></thead>";
				$tfoot = "<tfoot><tr>
								<th>Baris</th>
								<th>Item</th>
								<th>Deskripsi</th>
								<th>Tipe Issue</th>
								<th>No. Batch</th>
								<th>Tgl. Masuk</th>
								<th>Jml. yg Dikembalikan</th>
								<th>Satuan</th>
								<th>Condition Type</th>
								<th>Gudang</th>
								<th>Rak</th>
								<th>Tgl. Return</th>
								<th>Catatan</th>
								<th  class='hide'>Line</th>
							</tr>
					</tfoot>";
				$renderTable = $thead . $tfoot . $tbody;
				echo $renderTable;
			}
		}
		else {
			if ($x[1] == 'services') {
				if ($z == 'receipt') {
					$polineSet = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("$cls1 and $cls2")->MboRemote("polinenum")->MboRemote("description")->MboRemote("orderqty")->MboRemote("orderunit")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("polineid")->getMboSet();
					$datacount = $polineSet->counts();
					$tbodyArr = array();
					$tbody = " ";
					for ($i = 0; $i < $datacount; $i++) {
						$poline = $polineSet->getMbo();
						$polinenum = $poline->getString("polinenum");
						$description = $poline->getString("description");
						$orderqty = $poline->getString("orderqty");
						$orderunit = $poline->getString("orderunit");
						$unitcost = $poline->getString("unitcost");
						$linecost = $poline->getString("linecost");
						$polineid = $poline->getString("polineid");
						$set2 = new Set();
						$qtyreceived = $set2->MboSetRemote("servrectrans")->setWhere("$cls1 and $cls2 and description = '" . $description . "'")->group("polinenum")->MboRemote("polinenum")->MboRemote("sum(quantity) as quantity")->getMboSet()->getMbo()->getString("quantity");
						if (empty($qtyreceived)) $qtyreceived = 0;
						$restQty = $orderqty - $qtyreceived;
						if ($restQty == 0) {
							$tbody = " ";
						}
						else {
							$tbody = "<tr> 
								<td data-attribute='polinenum'>" . $polinenum . "</td>
								<td data-attribute='description'>" . $description . "</td>
								<td data-attribute='orderqty'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"restqty\" value='" . $restQty . " ' style=\"background:rgb(255,255,255);\"></td>
								<td data-attribute='orderunit'>" . $orderunit . "</td>
								<td data-attribute='unitcost' data-type='decimal'>" . $unitcost . "</td>
								<td data-attribute='linecost' data-type='decimal'>" . $linecost . "</td>
								<td data-attribute='actualdate'> <input type=\"text\" data-type=\"date\" class=\"nonpersistent\" data-attribute=\"actualdate\"></td>
								<td data-attribute='remarks'> <input type=\"text\"  class=\"nonpersistent\" data-attribute=\"remarks\"> </td>
								<td data-attribute='polineid' class='hide'>" . $polineid . "</td>
							</tr>";
						}
						array_push($tbodyArr, $tbody);
						$tbodyIm = implode(" ", $tbodyArr);
						$tbody = "<tbody>" . $tbodyIm . "</tbody>";
					}
					$thead = "<thead><tr>
											<th>Baris</th>
											<th>Deskripsi</th>
											<th>Jml. Diterima</th>
											<th>Satuan</th>
											<th>Biaya per Unit</th>
											<th>Total</th>
											<th>Tgl. Diterima</th>
											<th>Catatan</th>
											<th class='hide'>polineid</th>
										</tr></thead>";
					$tfoot = "<tfoot><tr>
											<th>Baris</th>
											<th>Deskripsi</th>
											<th>Jml. Diterima</th>
											<th>Satuan</th>
											<th>Biaya per Unit</th>
											<th>Total</th>
											<th>Tgl. Diterima</th>
											<th>Catatan</th>
											<th class='hide'>polineid</th>
										</tr>
								</tfoot>";
					$renderTable = $thead . $tfoot . $tbody;
					echo $renderTable;
				}
				if ($z == "return") {
					$servrectransSet = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->setWhere(" $cls1 and $cls2 ")->group("polinenum, description")->MboRemote("polinenum")->MboRemote("description")->MboRemote("sum(quantity) as quantity")->getMboSet();
					$datacount = $servrectransSet->counts();
					$tbodyArr = array();
					$tbody = " ";
					for ($i = 0; $i < $datacount; $i++) {
						$servrectrans = $servrectransSet->getMbo();
						$polinenum = $servrectrans->getString("polinenum");
						$quantity = $servrectrans->getString("quantity");
						$description = $servrectrans->getString("description");
						if ($quantity == 0) {
							$tbody = " ";
						}
						else {
							$tbody = "<tr> 
								<td data-attribute='polinenum'>" . $polinenum . "</td>
								<td data-attribute='description'>" . $description . "</td>
								<td data-attribute='restqty'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"restqty\" value='" . $quantity . " ' style=\"background:rgb(255,255,255);\"></td>
								<td data-attribute='actualdate'> <input type=\"text\"  data-type=\"date\"  class=\"nonpersistent\" data-attribute=\"actualdate\"></td>
								<td data-attribute='remarks'> <input type=\"text\" class=\"nonpersistent\" data-attribute=\"remarks\"></td>
								<td data-attribute='polinenum'  class='hide'>" . $polinenum . "</td>
								</tr>";
						}
						array_push($tbodyArr, $tbody);
					}
					$tbodyIm = implode(" ", $tbodyArr);
					$tbody = "<tbody>" . $tbodyIm . "</tbody>";
					$thead = "<thead><tr>
								<th>Baris</th>
								<th>Deskripsi</th>
								<th>Jml. Dikembalikan</th>
								<th>Tgl. Dikembalikan</th>
								<th>Catatan</th>
								<th  class='hide'>Line</th>
								</tr></thead>";
					$tfoot = "<tfoot><tr>
								<th>Baris</th>
								<th>Deskripsi</th>
								<th>Jml. Dikembalikan</th>
								<th>Tgl. Dikembalikan</th>
								<th>Catatan</th>
								<th  class='hide'>Line</th>
								</tr>
						</tfoot>";
					$renderTable = $thead . $tfoot . $tbody;
					echo $renderTable;
				}
			}
			
		}
	}
	public static function getTaxRate($taxcode){
		$value = (new Set())->useTransactionManager()->MboSetRemote("tax")->where("taxcode = ?", $taxcode)->getMboValue("value");
		return $value;
	}
	public static function getTaxValue($taxcode=null){
		$set = new Set();
		$getTax = null;
		if($taxcode == null){
			if(isset($_GET['getTaxValue'])) {
				$getTax = $_GET['getTaxValue'];
			}else{
				$getTax = $taxcode;
			}
			$value = trim($getTax, " "); 
			echo (new Set())->useTransactionManager()->MboSetRemote("tax")->setWhere("taxcode = '$value'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
		}else{
			$value = (new Set())->useTransactionManager()->MboSetRemote("tax")->setWhere("taxcode = '$taxcode'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
			
		}
		if(empty($value))$value = 0;
		return $value;
	}
	public static function SumWOCost($wo = null, $noprint = false)

	{
		
		if($wo != null) $wonum = $wo;
		else{
			$wonum = $_GET["sumwocost"];
		}
		/* SUM ACTUAL COST OF THE WORK ORDER */
		$set = new Set();
		$materialCost = floatval((new Set())->useTransactionManager()->MboSetRemote("matusetrans")->setWhere("wonum='$wonum'")->MboRemote(" abs(sum(linecost)) as linecost ")->getMboSet()->getMbo()->getString("linecost"));
		
		$set = new Set();
		$serviceCost = floatval((new Set())->useTransactionManager()->MboSetRemote("servrectrans")->setWhere("wonum='$wonum'")->MboRemote(" abs(sum(linecost)) as linecost")->getMboSet()->getMbo()->getString("linecost"));
		
		$set = new Set();
		$sumactuallaborcost = floatval((new Set())->useTransactionManager()->MboSetRemote("labortrans")->setWhere("wonum='$wonum'")->MboRemote(" abs(sum(linecost)) as linecost ")->getMboSet()->getMbo()->getString("linecost"));
		
		$set = new Set();
		$sumactualtoolcost = floatval((new Set())->useTransactionManager()->MboSetRemote("tooltrans")->setWhere("wonum='$wonum'")->MboRemote(" abs(sum(linecost)) as linecost")->getMboSet()->getMbo()->getString("linecost"));
				
		$actualcost = $materialCost + $serviceCost + $sumactuallaborcost + $sumactualtoolcost;
		
		
		
		/* SUM PLANNDED COST OF THE WORK ORDER */
		
		$set = new Set();
		$materialPlannedCost = floatval((new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->setWhere("wonum='$wonum'")->MboRemote(" sum(linecost) as materialPlannedCost ")->getMboSet()->getMbo()->getString("materialPlannedCost"));
		
		$set = new Set();
		$woplanSet2 = (new Set())->useTransactionManager()->MboSetRemote("woplanservice")->setRelationship("prline", "woplanservice.prnum = prline.prnum and woplanservice.prlinenum = prline.prlinenum")
		->setWhere("woplanservice.wonum='$wonum'")->MboRemote("sum(prline.linecost) as sumServicePlannedCost")->getMboSet();
		$woplan2 = $woplanSet2->getMbo();
		$sumServicePlannedCost = floatval($woplan2->getString("sumServicePlannedCost"));
		
		$set = new Set();
		$woplanLaborSet = (new Set())->useTransactionManager()->MboSetRemote("woplanlabor")->setWhere("woplanlabor.wonum='$wonum'")->MboRemote("sum(linecost) as sumlaborcost")->getMboSet();
		$woplanlabor = $woplanLaborSet->getMbo();
		$sumlaborcost = floatval($woplanlabor->getString("sumlaborcost"));
		
		$set = new Set();
		$woplanToolSet = (new Set())->useTransactionManager()->MboSetRemote("woplantool")->setWhere("woplantool.wonum='$wonum'")->MboRemote("sum(linecost) as sumtoolcost")->getMboSet();
		$woplantool = $woplanToolSet->getMbo();
		$sumtoolcost = floatval($woplantool->getString("sumtoolcost"));
		
		
		$plannedcost = $materialPlannedCost + $sumServicePlannedCost + $sumlaborcost + $sumtoolcost;
		$variancecost = $plannedcost - $actualcost;
		
		
		//completion progress
		$set = new Set();
		$wologsSet = (new Set())->useTransactionManager()->MboSetRemote("wologs")->setWhere("wonum='$wonum'")->MboRemote("avg(progress) as progress")->getMboSet();
		$wologs = $wologsSet->getMbo();
		$progress = $wologs->getString("progress");
		if(empty($progress)) $progress = 0;
		
		//end completion progress
		$progress = round($progress,2);
		$su = new Set();
		$woSet = $su->MboSetRemote("workorder")->setValue("actualcost", $actualcost)->setValue("plannedcost", $plannedcost)->setValue("variancecost", $variancecost)->setValue("progress", $progress)->change();
		
		if($noprint == false){
			Set::throws(["actualcost" => $actualcost, "plannedcost" => $plannedcost, "variancecost" => $variancecost, "progress"=>$progress]);
		}else{
			$data = [
				"actualcost" => $actualcost, "plannedcost" => $plannedcost, "variancecost" => $variancecost, "progress"=>$progress
			];
			return $data; 
		}
		
	}

	public static function costAdjustUI()

	{
	
		$x = explode("|", Super::getParam("costAdjustUI"));
		$itemnum = $x[0];
		$wh = $x[1];
		$orgcode = $x[2];
		$sitecode = $x[3];
		$set = new Set();
		$invcostSet = (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$wh' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("itemnum")->MboRemote("conditiontype")->MboRemote("stdcost")->MboRemote("avgcost")->MboRemote("invcostid")->getMboSet();
		$datacount = $invcostSet->counts();
		$tbodyArr = array();
		for ($i = 0; $i < $datacount; $i++) {
			$invcost = $invcostSet->getMbo();
			$itemnum = $invcost->getString("itemnum");
			$oldstdcost = $invcost->getString("stdcost");
			$oldavgcost = $invcost->getString("avgcost");
			$invcostid = $invcost->getString("invcostid");
			$conditiontype = $invcost->getString("conditiontype");
			if (empty($itemnum)) {
				$tbody = " ";
			}
			else {
				$tbody = "<tr> 
				<td data-attribute='conditiontype'>" . $conditiontype . "</td>
				<td data-attribute='newstdcost'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"newstdcost\" value='" . $oldstdcost . " ' style=\"background:rgb(255,255,255);\"></td>
				<td data-attribute='newavgcost'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"newavgcost\" value='" . $oldavgcost . " ' style=\"background:rgb(255,255,255);\"></td>
				<td data-attribute='invcostid' class='hide'>" . $invcostid . "</td>
				</td>
			</tr>";
			}
			array_push($tbodyArr, $tbody);
		}
		$tbodyIm = implode(" ", $tbodyArr);
		$tbody = "<tbody>" . $tbodyIm . "</tbody>";
		$thead = "<thead><tr>
							<th>Condition Type</th>							
							<th>New Std Cost</th>
							<th>New Avg Cost</th>
							<th class='hide'>invcostid</th>
							</tr></thead>";
		$tfoot = "<tfoot><tr>
							<th>Condition Type</th>							
							<th>New Std Cost</th>
							<th>New Avg Cost</th>
							<th class='hide'>invcostid</th>
							</tr>
					</tfoot>";
		$renderTable = $thead . $tfoot . $tbody;
		echo $renderTable;
	}
	public static function PhysicalCountUI(){
		$x = explode("|", Super::getParam("physcountui"));
		$itemnum = $x[0];
		$wh = $x[1];
		$orgcode = $x[2];
		$sitecode = $x[3];
		$set = new Set();
		
		$invbalanceSet = (new Set())->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$wh' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("itemnum")->MboRemote("racknum")->MboRemote("physcount")->MboRemote("invbalanceid")->MboRemote("conditiontype")->getMboSet();
		$datacount = $invbalanceSet->counts();
		$tbodyArr = array();
		for ($i = 0; $i < $datacount; $i++) {
			$invbalance = $invbalanceSet->getMbo();
			$itemnum = $invbalance->getString("itemnum");
			$racknum = $invbalance->getString("racknum");
			$oldphyscount = $invbalance->getString("physcount");
			$invbalanceid = $invbalance->getString("invbalanceid");
			$conditiontype = $invbalance->getString("conditiontype");
			
			if(empty($oldphyscount)) $oldphyscount = 0;
			
			if (empty($itemnum)) {
				$tbody = " ";
			}
			else {
				$tbody = "<tr> 
					<td data-attribute='racknum'>" . $racknum . "</td>
					<td data-attribute='conditiontype'>" . $conditiontype . "</td>							
					<td data-attribute='newphyscount'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"newphyscount\" value='" . $oldphyscount . " ' style=\"background:rgb(255,255,255);\"></td>
					<td data-attribute='invbalanceid' class='hide'>" . $invbalanceid . "</td>
					</td>
				</tr>";
			}
			array_push($tbodyArr, $tbody);
		}
		$tbodyIm = implode(" ", $tbodyArr);
		$tbody = "<tbody>" . $tbodyIm . "</tbody>";
		$thead = "<thead><tr>
						<th>Rack</th>
						<th>Condition Type</th>	
						<th>New Phys. Count</th>
						<th class='hide'>invbalanceid</th>
					</tr></thead>";
		$tfoot = "<tfoot><tr>
							<th>Rack</th>
							<th>Condition Type</th>	
							<th>New Phys. Count</th>
							<th class='hide'>invbalanceid</th>
							</tr>
					</tfoot>";
		$renderTable = $thead . $tfoot . $tbody;
		echo $renderTable;
		
	}
	public static function balanceAdjustUI()

	{
		$x = explode("|", Super::getParam("balanceAdjustUI"));
		$itemnum = $x[0];
		$wh = $x[1];
		$orgcode = $x[2];
		$sitecode = $x[3];
		$set = new Set();
		$invbalanceSet = (new Set())->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$wh' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("itemnum")->MboRemote("racknum")->MboRemote("curbal")->MboRemote("invbalanceid")->MboRemote("conditiontype")->getMboSet();
		$datacount = $invbalanceSet->counts();
		$tbodyArr = array();
		for ($i = 0; $i < $datacount; $i++) {
			$invbalance = $invbalanceSet->getMbo();
			$itemnum = $invbalance->getString("itemnum");
			$racknum = $invbalance->getString("racknum");
			$oldcurbal = $invbalance->getString("curbal");
			$invbalanceid = $invbalance->getString("invbalanceid");
			$conditiontype = $invbalance->getString("conditiontype");
			if (empty($itemnum)) {
				$tbody = " ";
			}
			else {
				$tbody = "<tr> 
					<td data-attribute='racknum'>" . $racknum . "</td>
					<td data-attribute='conditiontype'>" . $conditiontype . "</td>							
					<td data-attribute='newcurbal'><input type=\"text\" data-type=\"decimal\" class=\"nonpersistent\" data-attribute=\"newcurbal\" value='" . $oldcurbal . " ' style=\"background:rgb(255,255,255);\"></td>
					<td data-attribute='invbalanceid' class='hide'>" . $invbalanceid . "</td>
					</td>
				</tr>";
			}
			array_push($tbodyArr, $tbody);
		}
		$tbodyIm = implode(" ", $tbodyArr);
		$tbody = "<tbody>" . $tbodyIm . "</tbody>";
		$thead = "<thead><tr>
						<th>Rack</th>
						<th>Condition Type</th>	
						<th>New Balance</th>
						<th class='hide'>invbalanceid</th>
					</tr></thead>";
		$tfoot = "<tfoot><tr>
							<th>Rack</th>
							<th>Condition Type</th>	
							<th>New Balance</th>
							<th class='hide'>invbalanceid</th>
							</tr>
					</tfoot>";
		$renderTable = $thead . $tfoot . $tbody;
		echo $renderTable;
	}
		public static function sumcrcost($crn=null){
		if(isset($_GET["sumcrcost"])){
			$cashreceiptnum = $_GET["sumcrcost"];
		}else{
			$cashreceiptnum = $crn;
		}
		$set = new Set();
		$additionalcost = (new Set())->useTransactionManager()->MboSetRemote("cashrecaddcost")->setWhere("cashreceiptnum='$cashreceiptnum'")->MboRemote("sum(linecost) as additionalcost")
		->getMboSet()->getMbo()->getString("additionalcost");
		if($additionalcost==null)$additionalcost=0;
		$totalcashinbank = (new Set())->useTransactionManager()->MboSetRemote("cashinbank")->setWhere("cashreceiptnum='$cashreceiptnum'")->MboRemote("sum(totaltransfer) as totalcashinbank")
		->getMboSet()->getMbo()->getString("totalcashinbank");
		if($totalcashinbank==null)$totalcashinbank=0;
		$crset = (new Set())->useTransactionManager()->MboSetRemote("cashreceipt")->setWhere("cashreceiptnum='$cashreceiptnum'")->MboRemote("closebalancetax")->MboRemote("openbalance")->getMboSet();
		$cr = $crset->getMbo();
		$closebalancetax = $cr->getString("closebalancetax");
		$openbalance = $cr->getString("openbalance");
		
		$totalExpSet = (new Set())->useTransactionManager()->MboSetRemote("cashrecbalance")->setWhere("cashreceiptnum='$cashreceiptnum'")->MboRemote("sum(linecost) as totalexpense")->getMboSet();
		$totalExp = $totalExpSet->getMbo();
		$totalexpense = $totalExp->getString("totalexpense");
		if($totalexpense == null) $totalexpense = 0;
		$totalexpense = $totalexpense*-1;
		$variancecost = $closebalancetax-$totalcashinbank-$additionalcost;
		
		$totalExpSet2 = (new Set())->useTransactionManager()->MboSetRemote("cashoutline")->setWhere("cashreceiptnum='$cashreceiptnum'")->MboRemote("sum(linecost) as totalexpense")->getMboSet();
		$totalExp2 = $totalExpSet2->getMbo();
		$totalexpense2 = $totalExp2->getString("totalexpense");
		if($totalexpense2 == null) $totalexpense2 = 0;
		$totalexpense2 = $totalexpense2*-1;
		
		$totalexpense = $totalexpense+$totalexpense2;
		
		$variancecost2 = (new Set())->useTransactionManager()->MboSetRemote("cashrecaddcost")->setWhere("cashreceiptnum='$cashreceiptnum' and costtype='unallocated'")->MboRemote("sum(linecost) as additionalcost")
		->getMboSet()->getMbo()->getString("additionalcost");
		if($variancecost2==null)$variancecost2=0;
		
		$bankcost = (new Set())->useTransactionManager()->MboSetRemote("cashrecaddcost")->setWhere("cashreceiptnum='$cashreceiptnum' and costtype='bankcost'")->MboRemote("sum(linecost) as bankcost")
		->getMboSet()->getMbo()->getString("bankcost");
		if($bankcost==null)$bankcost=0;
		
		$paidlineprice = (new Set())->useTransactionManager()->MboSetRemote("taxreceipt")->setWhere("cashreceiptnum='$cashreceiptnum' and status='paid' and taxtype='out'")->MboRemote("sum(lineprice) as paidlineprice")
		->getMboSet()->getMbo()->getString("paidlineprice");
		if($paidlineprice==null)$paidlineprice=0;
		
		$totalcashinbank = $totalcashinbank+$openbalance;
		$closebalance = $totalcashinbank+$closebalancetax-$paidlineprice-$totalexpense-$bankcost;
		$closebalance2 = $closebalance-$variancecost2;		
		
		$v = new stdClass();
		$v->bankcost = $bankcost;
		$v->totalexpense = $totalexpense;
		$v->closebalance = $closebalance;
		$v->paidlineprice = $paidlineprice;
		$v->additionalcost = $additionalcost;
		$v->totalcashinbank = $totalcashinbank;
		$v->variancecost = $variancecost;
		$v->variancecost2 = $variancecost2;
		$v->closebalance2 = $closebalance2;
		
		Set::throws(["totalexpense"=>$totalexpense, "closebalance"=>$closebalance, "paidlineprice"=>$paidlineprice, 
		"additionalcost"=>$additionalcost, "totalcashinbank"=>$totalcashinbank, "variancecost"=>$variancecost, 
		"variancecost2"=>$variancecost2,"closebalance2"=>$closebalance2, "bankcost"=>$bankcost]);
		
		return $v;
	}
	public static function TaxPayment($taxreceiptnum){
		$set = new Set();
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$taxreceiptSet = (new Set())->useTransactionManager()->MboSetRemote("taxreceipt")->setRelationship("invoice", "taxreceipt.invoicenum = invoice.invoicenum")->setWhere("taxreceipt.taxreceiptnum='$taxreceiptnum' and taxtype='out'")
		->MboRemote("taxreceipt.taxreceiptnum")->MboRemote("taxreceipt.invoicenum")->MboRemote("invoice.sonum")->MboRemote("invoice.subtotalcost")->MboRemote("taxreceipt.lineprice as taxcost")
		->MboRemote("invoice.totalcost")->MboRemote("invoice.invoicedate")->MboRemote("taxreceipt.statusdate2 as transferdate")->MboRemote("transtype")
		->MboRemote("taxreceipt.gldebitacct")->MboRemote("taxreceipt.glcreditacct")->MboRemote("taxreceipt.statusdate")->MboRemote("taxreceipt.financialperiod")->MboRemote("taxreceipt.taxreceiptid")->getMboSet();
		
		$taxreceipt = $taxreceiptSet->getMbo();
		$taxreceiptnum = $taxreceipt->getString("taxreceiptnum");
		$invoicenum = $taxreceipt->getString("invoicenum");
		$sonum = $taxreceipt->getString("sonum");
		$subtotalcost = $taxreceipt->getString("subtotalcost");
		$taxcost = $taxreceipt->getString("taxcost");
		$totalcost = $taxreceipt->getString("totalcost");
		$invoicedate = $taxreceipt->getString("invoicedate"); 
		$transferdate = $taxreceipt->getString("transferdate");
		$transtype = $taxreceipt->getString("transtype");
		$glcreditacct = $taxreceipt->getString("glcreditacct");
		$gldebitacct = $taxreceipt->getString("gldebitacct");
		$paiddate =  $taxreceipt->getString("statusdate");
		$financialperiod =  $taxreceipt->getString("financialperiod");
		$taxreceiptid  =  $taxreceipt->getString("taxreceiptid");
		
		if($transtype == "sales") {
			if($subtotalcost>0) $subtotalcost = $subtotalcost*-1;
			if($taxcost>0) $taxcost = $taxcost*-1;
			if($totalcost>0) $totalcost = $totalcost*-1;
			
		}
		
		$set2=new Set();
		$set2->MboSetRemote("taxtrans")->setValue("taxreceiptnum", $taxreceiptnum)->setValue("invoicenum", $invoicenum)->setValue("sonum", $sonum)
		->setValue("subtotalcost", $subtotalcost)->setValue("taxcost", $taxcost)->setValue("totalcost", $totalcost)->setValue("invoicedate", $invoicedate)
		->setValue("transferdate", $transferdate)->setValue("transtype", $transtype)->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)
		->setValue("paiddate", $paiddate)->setValue("financialperiod", $financialperiod)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)->add();
		
		$set3 = new Set();
		$set3->MboSetRemote("taxreceipt")->setWhere("taxreceiptid='$taxreceiptid'")->setValue("lineprice", $taxcost)->setValue("paiddate", $paiddate)->change();
	}
	public static function IssuedFIFOLIFO($fl){
		return CommonInventory::IssuedFIFOLIFO($fl);
	}
	public static function AdjustFIFOLIFO($fl){
		CommonInventory::AdjustFIFOLIFO($fl);
	}
	
	public static function AdjustPhysicalCountLoader($a = null){
		
		$itemnum = $a->itemnum; 
		$warehouse = $a->warehouse;
		$conditiontype = $a->conditiontype;
		$orgcode = $a->orgcode;
		$sitecode = $a->sitecode;
		$invbalanceid = $a->id;
		$racknum = $a->racknum;
		$newphyscount = $a->newphyscount;
		
		$physdate = Xbo::setEnterDate();
		$financialperiod = Xbo::getFinancialPeriod();
		$enterby = Xbo::getLoginUser();
		
		$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
		$controlacc = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
		
		$s = new Set();
		$invbalanceSet = $s->MboSetRemote("invbalance")->setWhere("invbalanceid=$invbalanceid")->MboRemote("curbal")->MboRemote("conditiontype")->getMboSet();
		$invbalance = $invbalanceSet->getMbo();
		$oldcurbal = $invbalance->getString("curbal");
		$conditiontype =  $invbalance->getString("conditiontype");
		if ($newphyscount > $oldcurbal) {
			$gldebitacct = $controlacc;
			$glcreditacct = $shrinkageacc;
		}else{
			if ($newphyscount < $oldcurbal) {
				$gldebitacct = $shrinkageacc;
				$glcreditacct = $controlacc;
			}else{
				if ($newphyscount == $oldcurbal){
					$gldebitacct = $controlacc;
					$glcreditacct = $controlacc;
				}
			}
		}
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("invbalance")->setWhere("invbalanceid=$invbalanceid")->setValue("physcount", $newphyscount)->setValue("physcountdate", $physdate)->change();
		
		
		if (empty($conditiontype)) {
			$condval = " and (conditiontype='' or conditiontype is null)";
		}
		else {
			$condval = " and conditiontype='$conditiontype'";
		}
		$invcostSet = (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval");
		$invcost = $invcostSet->MboRemote("avgcost")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
		$avgcost = $invcost->getString("avgcost");
		
		$varianceqty = $newphyscount-$oldcurbal;
		
		$cost = $newphyscount*$avgcost;
		$oldcost = $oldcurbal*$avgcost;
		$variancecost  = $varianceqty*$avgcost;
		
		if($oldcost != $cost){
			$set2 = new Set();
			$set2->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", "PHYSCNTADJ")->setValue("warehouse", "$warehouse")->setValue("racknum", "$racknum")
				->setValue("conditiontype", "$conditiontype")->setValue("quantity", "$newphyscount")->setValue("oldquantity", "$oldcurbal")->setValue("cost", "$cost")
				->setValue("oldcost", "$oldcost")->setValue("varianceqty", "$varianceqty")->setValue("variancecost", "$variancecost")->setValue("gldebitacct", "$gldebitacct")
				->setValue("glcreditacct", "$glcreditacct")->setValue("orgcode", "$orgcode")->setValue("sitecode", "$sitecode")->setValue("enterby", "$enterby")->setValue("enterdate", "$physdate")
				->setValue("financialperiod", "$financialperiod")->add();
		}
		$set2 = new Set();
		$msg= "ok";
		return $msg;
	}
	
	public static function ReconcileBalanceAdjustment($data, $financialperiod){
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$invtransData = [];

		foreach($data as $row){

			$itemnum   = $row["itemnum"];
			$warehouse = $row["warehouse"];
			$racknum   = $row["racknum"];
			$conditiontype = $row["conditiontype"];
			$physqty   = $row["physqty"];
			$physcountdate = $row["physcountdate"];
			$orgcode   = $row["orgcode"];
			$sitecode  = $row["sitecode"];
			$oldCurbal = $row["quantity"];
			$cost      = $row["unitcost"];
			$avgcost   = $row["avgcost"];
			$invfifolifocostid = $row["invfifolifocostid"];
			$ifl_financialperiod = $row["financialperiod"]; // not used
			$batchnum = $row["batchnum"];
			$costdate = $row["costdate"];
			$expdate = $row["expdate"];
			$oldcost = $cost*$oldCurbal;

			if (empty($orgcode) || empty($sitecode)) {
				$item = (new Set())
					->useTransactionManager()
					->MboSetRemote("item")
					->where("itemnum=?", $itemnum)
					->MboRemote("orgcode, sitecode")
					->getMboSet()
					->getMbo();

				$orgcode = $item->getString("orgcode");
				$sitecode = $item->getString("sitecode");
			}

			if(empty($physcountdate)) continue;
			$physCountFP = Xbo::getFinancialPeriod($physcountdate);

			if ($financialperiod == $physCountFP) {
				// NEW BALANCE
				$newCurbal = $physqty;

				// GL accounts
				$controlacc   = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
				$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
				$transtype    = 'RECBALADJ';

				if ($newCurbal == $oldCurbal) {
					continue; // no variance -> no transaction
				}

				if ($newCurbal > $oldCurbal) {
					$gldebitacct = $controlacc;
					$glcreditacct = $shrinkageacc;
				} else {
					$gldebitacct = $shrinkageacc;
					$glcreditacct = $controlacc;
				}

				// variance
				$varianceqty = $newCurbal - $oldCurbal;
				$variancecost = $varianceqty * $cost;

				// push invtrans row
				$invtransData[] = [
					"itemnum" => $itemnum,
					"transtype" => $transtype,
					"warehouse" => $warehouse,
					"racknum" => $racknum,
					"conditiontype" => $conditiontype,
					"quantity" => $newCurbal, 
					"oldquantity" => $oldCurbal,
					"cost" => $cost,
					"oldcost" => $oldcost,
					"varianceqty" => $varianceqty,
					"variancecost" => $variancecost,
					"stdcost" => $cost,
					"avgcost" => $avgcost,
					"gldebitacct" => $gldebitacct,
					"glcreditacct" => $glcreditacct,
					"orgcode" => $orgcode,
					"sitecode" => $sitecode,
					"enterby" => $enterby,
					"enterdate" => $enterdate,
					"batchnum" => $batchnum,
					"costdate" => $costdate,
					"expdate" => $expdate,
					"financialperiod" => $financialperiod
				];
			
				(new Set())
					->MboSetRemote("invfifolifocost")
					->where("invfifolifocostid=?", $invfifolifocostid)
					->setValue("quantity", $newCurbal)
					->setValue("transtype", $transtype)
					->setValue("transdate", $enterdate)
					->change();
			}
		}

		// save transactions
		if (!empty($invtransData)) {
			(new Set())->MboSetRemote("invtrans")->addStream($invtransData);
		}
	}

	public static function CurrentBalanceAdjustment($value = null, $valueobj = null, $nomsg = null)

	{
		$batchnum = null;

		if(isset($_POST["maximoUpdateCurrentBalance"])){
			$itemnum = $_POST["maximoUpdateCurrentBalance"];
			$warehouse = $_POST["warehouse"];
			$racknum = $_POST["racknum"];
			$conditiontype = $_POST["conditiontype"];
			$orgcode = $_POST["orgcode"];
			$sitecode = $_POST["sitecode"];
			$enterdate = Xbo::setEnterDate();
			$changedate = $enterdate;
			$expireddate = $_POST["expireddate"];
			$costdate = $changedate;
			$newCurbal = $_POST["newcurbal"];
			
			$rackval = Xbo::RackWhereClause($racknum);
			$condval = Xbo::CondTypeWhereClause($conditiontype);
			$whereexpireddate = Super::whereDate("expdate", $expireddate);
			
			$wc = "itemnum='$itemnum' and warehouse='$warehouse' and orgcode='$orgcode' and sitecode='$sitecode' $condval $rackval $whereexpireddate";
			
			$set = new Set();
			$invbalanceSet = (new Set())->useTransactionManager()->MboSetRemote("invbalance")->setWhere($wc)->MboRemote("invbalanceid")->getMboSet();
			$invbalance = $invbalanceSet->getMbo();
			$id = $invbalance->getString("invbalanceid");
			
			if($id==null || $id==""){
				Set::throws(["msg"=>"record_invbalance_notfound", 
				"sql"=>$invbalanceSet->sql, 
				"todo"=>"Check the Rack, Condition Type, Expired Date, Warehouse, Orgcode, and Sitecode if the item is exist"]);
				return null;
			}
			
		}else{
			//CURBALADJ
			$isarray = is_array($value);
			$isobject = is_object($value);
			
			if($value == null) {
				if(isset ($_POST["curbalAdj"])){
					$colID = explode("`", $_POST["curbalAdj"]);
					$id = $colID[1];
					$newCurbal = $_POST["curbal"];
					$batchnum = $_POST["batchnum"];
				}else{
					$id = $valueobj->id;
					$newCurbal = $valueobj->curbal;
					$batchnum = $valueobj->batchnum;
				}
			}else{
				if($isobject == 1) {
					$newCurbal = $value->curbal;
					$batchnum = $value->batchnum;
				}
				else {
					$newCurbal = $value["curbal"];
					$batchnum = $value["batchnum"];
				}
			}
			
			if($valueobj != null){
				$id = $valueobj->id;
				$newCurbal = $valueobj->curbal;
				$batchnum = $valueobj->batchnum;
			}
			
			$whereexpireddate = "";
		}
		
		//Mulai update New Current Balance

		if ($newCurbal > - 1) {
			$set = new Set();
			if($value == null){
				$invbalanceSet = (new Set())->useTransactionManager()->MboSetRemote("invbalance")->setWhere("invbalanceid = $id")->MboRemote("itemnum")
				->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("conditiontype")->MboRemote("curbal")->MboRemote("orgcode")->MboRemote("sitecode")
				->MboRemote("enterdate")->MboRemote("changedate")->getMboSet()->getMbo();
				
				$itemnum = $invbalanceSet->getString("itemnum");
				$warehouse = $invbalanceSet->getString("warehouse");
				$racknum = $invbalanceSet->getString("racknum");
				$conditiontype = $invbalanceSet->getString("conditiontype");
				$orgcode = $invbalanceSet->getString("orgcode");
				$sitecode = $invbalanceSet->getString("sitecode");
				$oldCurbal = $invbalanceSet->getString("curbal");
				$enterdate = $invbalanceSet->getString("enterdate");
				$changedate = $invbalanceSet->getString("changedate");
				$costdate = $changedate;
				if(empty($costdate)) $costdate = $enterdate;
				if(empty($oldCurbal)) $oldCurbal =0;
				
			}else{
				if($isobject == 1) $itemnum = $value->itemnum;
				else $itemnum = $value["itemnum"];				
				
				if($isobject == 1) $warehouse = $value->warehouse;
				else $warehouse = $value["warehouse"];	
				
				if($isobject == 1) $racknum = $value->racknum;
				else $racknum = $value["racknum"];	
				
				if($isobject == 1) $conditiontype = $value->conditiontype;
				else $conditiontype = $value["conditiontype"];	
				
				if (empty($conditiontype)) $condval = " and (conditiontype='' or conditiontype is null)";
				else $condval = " and conditiontype='$conditiontype'";
				if (empty($racknum)) $rackval = "and (racknum ='' or racknum is null) ";
				else $rackval = "and racknum='$racknum'";
				
				$invbalanceSet = (new Set())->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")
				->MboRemote("curbal")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("changedate")->MboRemote("enterdate")->getMboSet()->getMbo();
				$orgcode = $invbalanceSet->getString("orgcode");
				$sitecode = $invbalanceSet->getString("sitecode");
				$oldCurbal = $invbalanceSet->getString("curbal");
				$enterdate = $invbalanceSet->getString("enterdate");
				$changedate = $invbalanceSet->getString("changedate");
				$costdate = $changedate;
				if(empty($costdate)){
					$costdate = $enterdate;
				}
				if(empty($oldCurbal)) $oldCurbal =0;
				
				
				if($isobject == 1) $newCurbal = $value->curbal;
				else $newCurbal = $value["newcurbal"];	
				
				if(empty($newCurbal)) $newCurbal =0;
				
			}
			
			$condval = "";
			$rackval = "";
			$financialperiod = Xbo::getFinancialPeriod();	
			if (empty($conditiontype)) $condval = " and (conditiontype='' or conditiontype is null)";
			else $condval = " and conditiontype='$conditiontype'";
			if (empty($racknum)) $rackval = "and (racknum ='' or racknum is null) ";
			else $rackval = "and racknum='$racknum'";
			
			$invcostSet =  (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")
			->MboRemote("stdcost")
			->MboRemote("avgcost")
			->MboRemote("conditionrate")
			->getMboSet();
			$invcost = $invcostSet->getMbo();
			$avgcost = $invcost->getString("avgcost");
			$stdcost = $invcost->getString("stdcost");
			$conditionrate  = $invcost->getString("conditionrate");
			if($conditionrate == null) $$conditionrate= null;
			if($avgcost == null) $avgcost=0;
			$changeby = Xbo::getLoginUser();
			$changedate = Xbo::setEnterDate();
			$controlacc = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
			$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
			$transtype = 'CURBALADJ';
		
			if(isset($valueobj->transtype)) $transtype = $valueobj->transtype;
			
			$gldebitacct=null;
			$glcreditacct=null;
			
			if ($newCurbal > $oldCurbal) {
				$gldebitacct = $controlacc;
				$glcreditacct = $shrinkageacc;
			}
			if ($newCurbal < $oldCurbal) {
				$gldebitacct = $shrinkageacc;
				$glcreditacct = $controlacc;
			}
			
			$newCurbalAsString = number_format($newCurbal, 2);
			
			if ($newCurbalAsString == $oldCurbal) {
				
				if(isset($_POST["synfifolifoqty"])) $synfifolifo = $_POST["synfifolifoqty"];
				else $synfifolifo = "Y";
				
				if($nomsg == "nomsg"){
					if($synfifolifo == "Y"){
						$fl = new stdClass();
						$fl->itemnum = $itemnum;
						$fl->warehouse  = $warehouse;
						$fl->conditiontype = $conditiontype;
						$fl->racknum = $racknum;
						$fl->conditionrate = $conditionrate;
						$fl->newcurbal = $newCurbal;
						$fl->avgcost = $avgcost;
						$fl->financialperiod = $financialperiod;
						$fl->costdate = $costdate;
						$fl->orgcode = $orgcode;
						$fl->sitecode = $sitecode;
						$fl->transtype= $transtype;
						$fl->gldebitacct= $gldebitacct;
						$fl->glcreditacct= $glcreditacct;
						$fl->stdcost= $stdcost;
						$fl->whereexpireddate = $whereexpireddate;
						$fl->oldcurbal = $oldCurbal;
						$fl->batchnum = $batchnum;
						XboSet::AdjustFIFOLIFO($fl);
						return "curbaladjusted";
					}else{
						return "NoBalanceAdjustment";
					}
				}else{
					if($synfifolifo == "Y"){
						$fl = new stdClass();
						$fl->itemnum = $itemnum;
						$fl->warehouse  = $warehouse;
						$fl->conditiontype = $conditiontype;
						$fl->racknum = $racknum;
						$fl->conditionrate = $conditionrate;
						$fl->newcurbal = $newCurbal;
						$fl->avgcost = $avgcost;
						$fl->financialperiod = $financialperiod;
						$fl->costdate = $costdate;
						$fl->orgcode = $orgcode;
						$fl->sitecode = $sitecode;
						$fl->transtype= $transtype;
						$fl->gldebitacct= $gldebitacct;
						$fl->glcreditacct= $glcreditacct;
						$fl->stdcost= $stdcost;
						$fl->whereexpireddate = $whereexpireddate;
						$fl->batchnum = $batchnum;
						$fl->oldcurbal = $oldCurbal;
						$fl->transdate = Xbo::setEnterDate();
						XboSet::AdjustFIFOLIFO($fl);
					}else{
						Set::throws(["msg"=>"NoBalanceAdjustment"]);
					}
				}
			}else{
				
				$oldcost = $oldCurbal*$avgcost;
				$newcost = $newCurbal*$avgcost;
				$qtyVariance = $newCurbal - $oldCurbal;
				$variancecost = $qtyVariance * $avgcost;
							
				
				$snt = new Set();
				$invtransSet = $snt->MboSetRemote("invtrans")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->MboRemote("cost")->getMboSet();
				$invtrans = $invtransSet->getMbo();
				$prevcost = $invtrans->getString("cost");
				
				
				(new Set())->useTransactionManager()->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", $transtype)->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)
				->setValue("conditiontype", $conditiontype)->setValue("oldquantity", $oldCurbal)->setValue("varianceqty", $qtyVariance)->setValue("quantity", $newCurbal)
				->setValue("oldcost", $oldcost)->setValue("cost", $newcost)->setValue("variancecost", $variancecost)->setValue("avgcost", $avgcost)->setValue("stdcost", $stdcost)
				->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $changeby)
				->setValue("enterdate", $changedate)->setValue("financialperiod", $financialperiod)->add();
				
				
				$fl = new stdClass();
				$fl->itemnum = $itemnum;
				$fl->warehouse  = $warehouse;
				$fl->conditiontype = $conditiontype;
				$fl->racknum = $racknum;
				$fl->conditionrate = $conditionrate;
				$fl->newcurbal = $newCurbal;
				$fl->avgcost = $avgcost;
				$fl->financialperiod = $financialperiod;
				$fl->costdate = $costdate;
				$fl->orgcode = $orgcode;
				$fl->sitecode = $sitecode;
				$fl->transtype= $transtype;
				$fl->gldebitacct= $gldebitacct;
				$fl->glcreditacct= $glcreditacct;
				$fl->stdcost= $stdcost;
				$fl->batchnum = $batchnum;
				$fl->transdate = Xbo::setEnterDate();
				$fl->oldcurbal = $oldCurbal;
				
				XboSet::AdjustFIFOLIFO($fl);
				
				XboSet::AdjustConversionStock($itemnum, $warehouse);
			
				if($nomsg == "nomsg"){
					return "curbaladjusted";
				}else{			
				}
			}
		}else {
			if($nomsg == "nomsg"){
				return "CannotAdjustLessThanZero";
			}else{
				Set::throws(["msg"=>"CannotAdjustLessThanZero",
							"todo" =>"Check your new quantity. Maxes cannot adjust minus Quantity."
						]); 
			}
			
		}
	}
	public static function AdjustConversionStock($itemnum, $warehouse){
		$set = new Set();
		$sumcurbal = (new Set())->useTransactionManager()->MboSetRemote("invbalance")
		->where("itemnum=? and warehouse=?", $itemnum, $warehouse)->MboRemote("sum(curbal) as sumcurbal")->getMboSet()->getMbo()->getString("sumcurbal");
		
		$invconversionSet = (new Set())->useTransactionManager()->MboSetRemote("invconversion")->where("itemnum=? and warehouse=?", $itemnum, $warehouse)
		->MboRemote("tomeasureunit")->MboRemote("conversion")
		->getMboSet();
		
		while($result = $invconversionSet->fetch()){
			$conversion = $result["conversion"];
			$tomeasureunit = $result["tomeasureunit"];
			$curbalconversion = $sumcurbal/$conversion;
			$set2 = new Set();
			$set2->MboSetRemote("invconversion")->where("itemnum=? and warehouse=? and tomeasureunit = ? " , $itemnum, $warehouse, $tomeasureunit)
			->setValue("curbalconversion", $curbalconversion)->change();
		}
		
		$msg = "multiconversion_updated";
		return $msg;
	}
	public static function AddToCustBalance($value){
		// add customer stock 
		 
		$itemnum = $value->itemnum;
		$wonum = $value->wonum;
		$expireddate = $value->expireddate;
		$conditiontype = $value->conditiontype;
		$racknum = $value->racknum;
		$warehouse = $value->warehouse;
		$quantity = $value->quantity;
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$batchnum = $value->batchnum;
		$costdate = $value->costdate;
		$actualdate = $value->actualdate;
		$costtype = $value->costtype;
		if (empty($conditiontype)) {
			$condval = " and (conditiontype is null or conditiontype='') ";
		}
		else {
			$condval = " and conditiontype = '$conditiontype' ";
		}
		if (empty($racknum)) {
			$rackval = " and (fromracknum is null or fromracknum='') ";
		}
		else {
			$rackval = " and fromracknum = '$racknum' ";
		}
		
		$woSet = new Set();
		$workorderSet = $woSet->MboSetRemote("workorder")->setWhere("workorder.wonum='$wonum'")
						->setRelationship("customer", "customer.customercode = workorder.customercode")
						->MboRemote("workorder.customercode")->MboRemote("customer.reseller")->MboRemote("customer.distributor")->getMboSet();
		$workorder = $workorderSet->getMbo();			
							
		$isDistributor = $workorder->getString("distributor");
		$isReseller = $workorder->getString("reseller");
		$customercode = $workorder->getString("customercode");	
		if(!empty($customercode)){
			
			if($isDistributor=="Y" || $isReseller == "Y"){
				
				$custbal = new Set();
				$dbo = new dbo();
				$dbtype = $dbo->dbtype();
				$whereexpireddate = Super::whereDate("expdate", $expireddate);	
				$wherecostdate =  Super::whereDate("costdate", $costdate);	
				
									
				$isCustBalExistSet = $custbal->MboSetRemote("custbalance")->setWhere("itemnum='$itemnum' and customercode='$customercode' and wonum='$wonum' and isinvoiced='N' $whereexpireddate $wherecostdate $condval $rackval")
				->MboRemote("itemnum")->MboRemote("droppedqty")->MboRemote("issuedqty")->MboRemote("curbal")->MboRemote("expdate")->getMboSet();
								
				$isCustBalExistCounts = $isCustBalExistSet->counts();
				$isCustBalExist = $isCustBalExistSet->getMbo();							
				$droppedqty = $isCustBalExist->getString("droppedqty");
				$issuedqty = $isCustBalExist->getString("issuedqty");
				$curbal = $isCustBalExist->getString("curbal");
								
				if($isCustBalExistCounts == 0 ){
					
					$custbal->MboSetRemote("custbalance")->setValue("customercode", $customercode)->setValue("wonum", $wonum)
					->setValue("itemnum", $itemnum)->setValue("expdate", $expireddate)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
					->setValue("droppedqty", $quantity)->setValue("issuedqty", 0)->setValue("curbal", $quantity)->setValue("conditiontype", $conditiontype)->setValue("fromwarehouse", $warehouse)
					->setValue("fromracknum", $racknum)->setValue("batchnum", $batchnum)->setValue("arriveddate", $actualdate)->setValue("costdate", $costdate)->setValue("costtype", $costtype)
					->add();
				
				}else{
					
					$newcurbal = $curbal+$quantity; 
					$newdroppedqty = $droppedqty+$quantity;
					
					if (empty($batchnum)) {
						$batchval = " and (batchnum is null or batchnum='') ";
					}
					else {
						$batchval = " and batchnum = '$batchnum' ";
					}
					
					$s = new Set();
					$isbatchExist = $s->MboSetRemote("custbalance")->setWhere("itemnum='$itemnum' $whereexpireddate $wherecostdate and wonum='$wonum' $condval $rackval $batchval")->getMboSet();
					$cntb = $isbatchExist->counts();
					
					if($cntb == 0){ 
						
						$custbal->MboSetRemote("custbalance")->setWhere("itemnum='$itemnum' $whereexpireddate  $wherecostdate and wonum='$wonum' $condval $rackval")->setValue("customercode", $customercode)->setValue("wonum", $wonum)
						->setValue("itemnum", $itemnum)->setValue("expdate", $expireddate)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
						->setValue("droppedqty", $newdroppedqty)->setValue("curbal", $newcurbal)->setValue("conditiontype", $conditiontype)->setValue("fromwarehouse", $warehouse)
						->setValue("fromracknum", $racknum)->setValue("batchnum", $batchnum)->setValue("arriveddate", $actualdate)->setValue("costdate", $costdate)->setValue("costtype", $costtype)
						->add();
					}else{
						
						$custbal->MboSetRemote("custbalance")->setWhere("itemnum='$itemnum' $whereexpireddate  $wherecostdate and wonum='$wonum' $condval $rackval")->setValue("customercode", $customercode)->setValue("wonum", $wonum)
						->setValue("itemnum", $itemnum)->setValue("expdate", $expireddate)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
						->setValue("droppedqty", $newdroppedqty)->setValue("curbal", $newcurbal)->setValue("conditiontype", $conditiontype)->setValue("fromwarehouse", $warehouse)
						->setValue("fromracknum", $racknum)->setValue("batchnum", $batchnum)->setValue("arriveddate", $actualdate)->setValue("costtype", $costtype)
						->change();
					}			
				}
				return null;
			}else{
				return "notreseller";
			}
		}
		//end add customer stock;	
	}
	
	private static function _updateOrCreateInvPrice($itemnum, $conditiontype, $warehouse, $orgcode, $sitecode, $unitprice, $enterdate)
    {
        if ($unitprice <= 0) {
            return; // Jangan lakukan apa-apa jika harga tidak valid
        }

        $wc_invprice = "itemnum='$itemnum' and warehouse='$warehouse' and orgcode='$orgcode' and sitecode='$sitecode'";
        $invpriceSet = (new Set())->useTransactionManager()->MboSetRemote("invprice")->setWhere($wc_invprice)->MboRemote("itemnum")->getMboSet();
        
        $pricecode = "NORMAL";
        $defaultprice = "Y";

        $set = (new Set())->useTransactionManager()->MboSetRemote("invprice")
            ->setValue("pricecode", $pricecode)
            ->setValue("unitprice", $unitprice)
            ->setValue("defaultprice", $defaultprice)
            ->setValue("orgcode", $orgcode)
            ->setValue("unitprice1", $unitprice)
            ->setValue("sitecode", $sitecode);

        if ($invpriceSet->isEmpty()) {
            // Add new price
            $set->setValue("itemnum", $itemnum)
                ->setValue("conditiontype", $conditiontype)
                ->setValue("warehouse", $warehouse)
                ->setValue("enterdate", $enterdate)
                ->add();
        } else {
            // Change existing price
            $set->setWhere($wc_invprice)
                ->setValue("changedate", $enterdate)
                ->change();
        }
    }
	
	public static function AddKITItems($jsonarr, &$processedItems = []){
        $js = (object)$jsonarr;
        $parentitem = $js->itemnum;
       
        if (in_array($parentitem, $processedItems)) {
            return;
        }
        $processedItems[] = $parentitem; // Tandai item ini sebagai sudah diproses

        $warehouse = $js->warehouse;
        $defrack = $js->defrack;
        $orgcode = $js->orgcode;
        $sitecode = $js->sitecode;
        $conditiontype = (isset($js->conditiontype)) ? $js->conditiontype : null;

        $ItemStructSet = (new Set())->useTransactionManager()->MboSetRemote("itemstruct")->setWhere("itemnum='$parentitem'")
            ->MboRemote("subitemnum")->MboRemote("unitcost")->MboRemote("quantity")->getMboSet();
        
        if ($ItemStructSet->isEmpty()) {
            return; // Tidak ada sub-item, tidak ada yang perlu dilakukan.
        }

        $totalcost = 0;
        
        // Asumsi fungsi ini ada dan mengembalikan semua baris dalam array
        $allStructItems = $ItemStructSet->getAllMbos(); 

        // EFISIENSI: Ambil semua condition type untuk semua sub-item dalam satu kueri
        $subItemNumbers = array_map(function($item) { return $item->getString("subitemnum"); }, $allStructItems);
        if (!empty($subItemNumbers)) {
            $inClause = "'" . implode("','", $subItemNumbers) . "'";
            $conditionsSet = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->setWhere("itemnum IN ($inClause) and conditionrate=100")->getMboSet();
            $conditionMap = [];
            while($cond = $conditionsSet->getMbo()){
                $conditionMap[$cond->getString('itemnum')] = $cond->getString('conditiontype');
            }
        }
        
        foreach ($allStructItems as $item) {
            $subitemnum = $item->getString("subitemnum");
            $unitcost = $item->getDecimal("unitcost") ?? 0;
            $totalcost += $unitcost;

            // Ambil dari map yang sudah dibuat, bukan dari kueri baru di dalam loop
            $subitemconditiontype = $conditionMap[$subitemnum] ?? null;

            $itemJsonArr = [
                "itemnum" => $subitemnum, 
                "warehouse" => $warehouse, 
                "curbal" => 0, // Kit parts ditambahkan dengan saldo 0
                "orgcode" => $orgcode, 
                "sitecode" => $sitecode, 
                "conditiontype" => $subitemconditiontype, 
                "defrack" => $defrack,
                "cost" => $unitcost
            ];
            
            // Panggil AddToInventory dengan melewatkan array item yang sudah diproses
            self::AddToInventory(null, $itemJsonArr, "kit_parts", true, $processedItems);
        }
        
        // Update total cost dari item parent/kit
        $wc_invcost_kit = "itemnum='$parentitem' and warehouse='$warehouse'";
        if (!empty($conditiontype)) {
            $wc_invcost_kit .= " and conditiontype='$conditiontype'";
        } else {
            $wc_invcost_kit .= " and (conditiontype='' or conditiontype is null)";
        }

        (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere($wc_invcost_kit)
            ->setValue("stdcost", $totalcost)->setValue("avgcost", $totalcost)->change();
    }
	
	
	
	public static function AddToInventory($param = null, $jsonarr = null, $isKitItem = null, $showjson = true, &$processedItems = []){
        // =================================================================
        // 1. MANAJEMEN TRANSAKSI & PENCEGAHAN REKURSI
        // =================================================================
        $tm = TransactionManager::getInstance();
        $isAlreadyInTransaction = $tm->isTransactionActive();

        // Hanya mulai transaksi baru jika tidak ada transaksi yang sedang berjalan
        if (!$isAlreadyInTransaction) {
            $tm->beginTransaction();
        }
		
		
		try {
			 if ($param != null && $jsonarr == null) {
				if(isset($param["AddToInventory"])){
					$itemnum = $param["AddToInventory"];
				}else{
					$itemnum = $param["mapAddToInventory"];
				}
				$warehouse = $param["warehouse"];
				$defrack = $param["defrack"];
				$orgcode = $param["orgcode"];
				$sitecode = $param["sitecode"];
				$conditiontype = $param["conditiontype"];
				$curbal =  $param["curbal"];
				$unitcost =  $param["unitcost"];
				$issuetype =  $param["issuetype"];
				$batchnum =  $param["batchnum"];
				if(isset($param["conditionrate"]) ){
					$conditionrate = $param["conditionrate"];
				}else{
					$conditionSet = (new Set())->useTransactionManager()->MboSetRemote("conditiontype")->setWhere("conditiontype = '$conditiontype'")->MboRemote("conditionrate")->getMboSet();
					$condition = $conditionSet->getMbo();
					$conditionrate = $condition->getString("conditionrate");
				}
			}
			
			
			if ($param == null && $jsonarr != null) {
				$json = json_encode($jsonarr);
				$js = json_decode($json);
				$itemnum = $js->itemnum;
				$warehouse = $js->warehouse;
				$defrack = $js->defrack;
				$orgcode = $js->orgcode;
				$sitecode = $js->sitecode;
				$conditiontype = (isset($js->conditiontype)) ? $js->conditiontype : null;
				$kitstdcost = (isset($js->cost)) ? $js->cost : null;
				$curbal = $js->curbal;
				$unitcost = (isset($js->unitcost)) ? $js->unitcost : 0;
				$issuetype = (isset($js->issuetype)) ? $js->issuetype : "AVG";
				$batchnum = (isset($js->batchnum)) ? $js->batchnum : null;
			}
			 // Validasi input penting
            if (empty($itemnum)) throw new Exception("Item Number is required.");
            if (empty($warehouse)) {
                if ($showjson) Set::throws(["msg"=>"warehousenull"]);
                return "warehousenull";
            }
			
			
			$financialperiod = Xbo::getFinancialPeriod();
            $enterby = Xbo::getLoginUser();
            $enterdate = Xbo::setEnterDate();

            if ($isKitItem !== null && in_array($itemnum, $processedItems)) return;
            $processedItems[] = $itemnum;
			
			
			//check if rack is null or rack= 'somevalue' ? 
			$rackval = Xbo::RackWhereClause($defrack);
			//check if condition type  is null or rack= 'somevalue' ? 
			$condval = Xbo::CondTypeWhereClause($conditiontype);
			
			//If Warehouse does not exist, add warehouse automatically
			$warehouseSet = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->setWhere("warehouse='$warehouse'")->getMboSet();
			
			if($warehouseSet->isEmpty()){
				$isinspection = "Y";
				(new Set())->useTransactionManager()->MboSetRemote("warehouse")
				->setValue("warehouse", $warehouse)
				->setValue("isinspection", $isinspection)
				->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)
				->add();
			}
			
			
			
			$unitcost = floatval($unitcost);
			//Check if Item is Condition Enabled (has condition record from itemcondition table)
			$itemcondSet = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum'")->MboRemote("itemnum")->MboRemote("conditiontype")->getMboSet();
			$countitemcond = $itemcondSet->counts();
			
			
			if($countitemcond >0 && $conditiontype == null){
				$condr = [];
				for($i=0; $i<$countitemcond; $i++){
					$itemcond = $itemcondSet->getMbo();
					$conditionenabled = $itemcond->getString("conditiontype");
					array_push($condr, $conditionenabled);
				}
				$condst = implode(", ",$condr);
				
				if($isKitItem == null ) {
					if( $showjson == true ) {
						Set::throws(["msg"=>"conditionenabled", "conditiontype"=>$condst]);
						return "conditionenabled";
					}
					else return "conditionenabled";
				}else{
					return "conditionenabled";
				}
			}else{
				
			
			
				//1. Mulai Memasukkan Item ke Inventor Warehouse. 
				if($warehouse != null){
					//1.1. Get Account Code for the warehouse
					$warehouseSet = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->setWhere("warehouse='$warehouse' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("controlacc")->MboRemote("shrinkageacc")
					->MboRemote("invcostadjacc")->MboRemote("isinspection")->getMboSet()->getMbo();
					$controlacc = $warehouseSet->getString("controlacc");
					$shrinkageacc = $warehouseSet->getString("shrinkageacc");
					$invcostadjacc = $warehouseSet->getString("invcostadjacc");
					$isinspection = $warehouseSet->getString("isinspection");
					
					$newAvgCost = $unitcost;
					$kitstdcost = $newAvgCost;
					$enterdate = Xbo::setEnterDate();
					
					// jika parameter yang diberikan user memiliki condition type, maka 
					if (!empty($conditiontype)) {
						//cek apakah conditiontype itu terdaftar di item condition? 
						
						$itemConditionSet = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' and conditiontype = '$conditiontype'")->MboRemote("conditionrate")->getMboSet();
						//jika tidak terdaftar, daftarkan.
						if($itemConditionSet->isEmpty()){
							// Cek apakah condition type terdaftar di Condition Type master table 
							$conditiontypeSet = (new Set())->useTransactionManager()->MboSetRemote("conditiontype")->setWhere("conditiontype='$conditiontype'")->MboRemote("conditionrate")->getMboSet();
							
							// Jika tidak terdaftar, daftarkan di tabel Master "condition type" 
							if($conditiontypeSet->isEmpty()){
								 $system = "SYSTEM";
								 (new Set())->useTransactionManager()->MboSetRemote("conditiontype")
								 ->setValue("conditiontype", $conditiontype)
								 ->setValue("description", "auto generated from by system from user input")
								 ->setValue("conditionrate", 100) //user must recheck and define the condition type after data is loaded.
								 ->setValue("enterby", $system)
								 ->setValue("enterdate", $enterdate)
								 ->add();
								 
								 //Setelah dimasukkan ke tabel master, masukkan condition type ke item condition 
								 (new Set())->useTransactionManager()->MboSetRemote("itemcondition")
								 ->setValue("itemnum", $itemnum)
								 ->setValue("conditiontype", $conditiontype)
								 ->setValue("conditionrate", 100)
								 ->setValue("enterby", $system)
								 ->setValue("enterdate", $enterdate)
								 ->add();
							}else{
								//Jika ada di tabel Master "Conditiontype", maka ambil condition rate nya
								$conditionTypeMbo = $conditiontypeSet->getMbo();
								$conditionrate = $conditionTypeMbo->getString("conditionrate");
							}
						}else{
							//jika itemconditiontype tidak kosong, maka ambol conditionrate 
							$condition = $itemConditionSet->getMbo();
							$conditionrate = $condition->getString("conditionrate");
							//jika conditionrate = 100% maka, set new average cost = unitcost. 
							// Jika conditionrate < 100%, maka set sesuaikan newAvgCost berdasarkan rate
							if($conditionrate < 100){
								$avgcost = (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and conditionrate=100")->MboRemote("avgcost")->getMboSet()->getMbo()->getString("avgcost");
								if($avgcost == null) $avgcost=0;
								$newAvgCost = $avgcost * ($conditionrate / 100);
							}
						}
					}else{
						// Jika user tidak memasukkan conditiontype pada function parameter 
						$conditionrate = null;
					}
					
					//2. Cek apakah sudah ada item yang sama di tabel Inventory.
					
					$inventorySet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("itemnum")->getMboSet();
					if ($inventorySet->isEmpty()) {
						//buat object insert Inventory
						
						$insertInventorySet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("racknum", $defrack)
						->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
						->setValue("eoqqty", 1)->setValue("reorder", "Y");
						
						//Jika user mendefinisikan issue type dari data loader, maka tambahkan issuetype by user
						if(isset($js->issuetype)) {
							$insertInventorySet = $insertInventorySet->setValue("issuetype", $js->issuetype);
						}else{
							//jika tidak , gunakan issue type yang didefinisan dari sistem
							$insertInventorySet = $insertInventorySet->setValue("issuetype", $issuetype);
						}
						
						$insertInventorySet->add();
					}
					
					//Check Apakah Item memiliki Account Code nya.
					$glaccount = (new Set())->useTransactionManager()->MboSetRemote("itemgroup")
					->setRelationship("item", "itemgroup.groupname = item.itemgroup and itemgroup.orgcode = item.orgcode and itemgroup.sitecode = item.sitecode")
					->setWhere("itemnum='$itemnum'")->MboRemote("itemgroup.glaccount")->getMboSet()->getMbo()->getString("glaccount");
				
					
					$wc = "itemnum='$itemnum' and warehouse='$warehouse'";
					$wc_invcost = "itemnum='$itemnum' and warehouse='$warehouse' $condval";
					
					//Cek Apakah sudah ada record Inventory Cost (invcost) untuk si item
					$invcostSet = (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere($wc_invcost)->group("itemnum, warehouse")
					->MboRemote("itemnum")->MboRemote("avg(avgcost) as avgcost")->MboRemote("oldcost")->MboRemote("lastcost")
					->getMboSet();
					
					//3. Cek apakah Item sudah ada di tabel InvCost?
					if ($invcostSet->isEmpty()) {
						//Masukkan ke tabel Invcost
						(new Set())->useTransactionManager()->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)
						->setValue("conditionrate", $conditionrate)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)
						->setValue("stdcost", $kitstdcost)->setValue("avgcost", $newAvgCost)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
						
						//Masukkan ke tabel invtrans
						$newcost = $newAvgCost*$curbal;
						(new Set())->useTransactionManager()->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", "ADDITEM")->setValue("warehouse", $warehouse)->setValue("racknum", $defrack)
						->setValue("conditiontype", $conditiontype)->setValue("quantity", $curbal)->setValue("gldebitacct", $controlacc)->setValue("glcreditacct", $controlacc)
						->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("financialperiod", $financialperiod)
						->setValue("cost", $newcost)->setValue("variancecost", $newcost)->setValue("avgcost", $newAvgCost)->setValue("stdcost", $newAvgCost)
						->add();
						
						//invprice
						if(isset($_POST["unitprice"])){
							$unitprice = $_POST["unitprice"];
							if($unitprice > 0 ){
								$wc_invprice = $wc;
								$invpriceSet = (new Set())->useTransactionManager()->MboSetRemote("invprice")->setWhere($wc_invprice)->MboRemote("itemnum")->getMboSet();
								$invpriceSetCount = $invpriceSet->counts();
								$pricecode = "NORMAL";
								
								$defaultprice = "Y";
								if($invpriceSetCount == 0 ){
									(new Set())->useTransactionManager()->MboSetRemote("invprice")
									->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)
									->setValue("pricecode", $pricecode)->setValue("unitprice", $unitprice)->setValue("defaultprice", $defaultprice)
									->setValue("orgcode", $orgcode)->setValue("unitprice1", $unitprice)->setValue("sitecode", $sitecode)
									->setValue("enterdate", $enterdate)
									->add();
								}else{
									(new Set())->useTransactionManager()->MboSetRemote("invprice")->setWhere($wc_invprice)
									->setValue("pricecode", $pricecode)->setValue("unitprice", $unitprice)->setValue("defaultprice", $defaultprice)
									->setValue("orgcode", $orgcode)->setValue("unitprice1", $unitprice)->setValue("sitecode", $sitecode)
									->setValue("changedate", $enterdate)
									->change();
								}
							}
						}
					}else{
						//Jika item sudah ada di invcost, ambil costing semua.
						$invcost = $invcostSet->getMbo();
						$avgcost = $invcost->getString("avgcost");
						$lastcost = $invcost->getString("lastcost");
						$oldcost = $invcost->getString("oldcost");
						//Hitung ulang Average cost nya
						$newOldCost = $avgcost;
						$newAvgCost = ($newAvgCost+$avgcost)/2;
						$newlastcost = $avgcost;
						
						//Ubah Average cost lama menjadi average cost baru
						(new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere($wc_invcost)
							->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)
							->setValue("conditionrate", $conditionrate)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)
							->setValue("stdcost", $kitstdcost)->setValue("avgcost", $newAvgCost)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->change();
					}

					//4. Cek Apakah Item sudah ada di invfifolifocost
					$batchval = Xbo::BatchWhereClause($batchnum);
						
					$wc_invfifo = $wc_invcost." $rackval $batchval";
					$set = new Set();
					$invfifolifocostSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wc_invfifo)->MboRemote("quantity")->getMboSet();
					
					if(empty($batchnum)){
						$batchnum = Xbo::GetBatchNum();
					}
					
					if($invfifolifocostSet->isEmpty()){
						$costdate = Xbo::setEnterDate();
						(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
						->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("costdate", $costdate)
						->setValue("financialperiod", $financialperiod)->setValue("unitcost", $newAvgCost)->setValue("quantity", $curbal)->setValue("racknum", $defrack)
						->setValue("conditionrate", $conditionrate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("warehouse", $warehouse)
						->setValue("batchnum", $batchnum)
						->add();
					}else{
						$costdate = Xbo::setEnterDate();
						$invfifolifocost = $invfifolifocostSet->getMbo();
						$oldqty = $invfifolifocost->getString("quantity");
						$newqty = $oldqty+$curbal;
						
						(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wc_invfifo)
						->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("costdate", $costdate)
						->setValue("financialperiod", $financialperiod)->setValue("unitcost", $newAvgCost)->setValue("quantity", $newqty)->setValue("racknum", $defrack)
						->setValue("conditionrate", $conditionrate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("warehouse", $warehouse)
						->setValue("batchnum", $batchnum)
						->change();
					}
					//5. Apakah ada Unit Price?
					// Proses InvPrice menggunakan helper function
					if (isset($_POST["unitprice"])){
						self::_updateOrCreateInvPrice($itemnum, $conditiontype, $warehouse, $orgcode, $sitecode, $_POST["unitprice"], $enterdate);
					}
					
					//Cek Apakah item memiliki KIT Parts atau tidak. 
					 // Proses sub-item jika ini adalah item KIT utama
					if ($isKitItem === null) {
						self::AddKITItems([
							"itemnum" => $itemnum, "warehouse" => $warehouse, "defrack" => $defrack,
							"orgcode" => $orgcode, "sitecode" => $sitecode, "conditiontype" => $conditiontype
						], $processedItems);
					}
						
					// =================================================================
					// 4. COMMIT & KIRIM RESPON
					// =================================================================
					
					// Hanya commit jika ini adalah awal dari rantai transaksi
					if (!$isAlreadyInTransaction) {
						$tm->commit();
					}
					if ($showjson) Set::throws(["msg"=>"added"]);
					return "added";
				}
			}
		} catch (Exception $e) {
             // Jika terjadi error di mana saja, batalkan seluruh pekerjaan
            if (!$isAlreadyInTransaction) {
                $tm->rollback();
            }
            throw $e;
        }
		
		
		
	}
	
	
	public static function checkNewRecord()
	{
		$recordOwner = $_POST['recordOwner'];
		$super = new Super();
		$super->checkNewRecord($recordOwner);
	}
	public static function extendAttr()

	{
		
		$x = explode("|", $_POST["extendAttr"]);
		$ownertable = $x[0];
		
		$y = explode("`", $x[1]);
		$colname = $y[0];
		$value = $y[1];
		$ids = $_POST["ids"];
		$datachild = $_POST["datachild"];
		$where = "and $colname = '$value'";
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$msg = null;
		for($i=0; $i<count($ids); $i++){
			$p = explode("`", $ids[$i]);
			$attributeid = $p[1];
			$set = new Set();
			$attrSet = (new Set())->useTransactionManager()->MboSetRemote("attribute")->setWhere("attributeid = '$attributeid'")->MboRemote("attributename")->MboRemote("description")->MboRemote("type")->getMboSet()->getMbo();
			$attname = $attrSet->getString("attributename");
			$description = $attrSet->getString("description");
			$type = $attrSet->getString("type");
			$set2 = new Set();
			$attributename = $set2->MboSetRemote($datachild)->setWhere("attributename = '$attname' $where")->MboRemote("attributename")->getMboSet()->getMbo()->getString('attributename');
			if ($attributename == null) {
				$datachildid = $datachild . "id";
				$set3 = new Set();
				$set3->MboSetRemote($datachild)->setValue($colname, $value)->setValue("attributename", $attname)->setValue("type", $type)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->add();
				$msg = "ok";
			}
			else {
				$msg = "exist";
				break;
			}
		}
		
		Set::throws(["msg"=>$msg, "datachild" =>$datachild]);
	}
	
	public static function rollupkey(){
		$table = $_POST['rolluptable'];
		$tablekey = $_POST['rollupkey'];
		$data = $_POST['data'];
		$count = count($data);
		
		for($i=0; $i<$count; $i++){
			$d = $data[$i];
			$keynum = $d["keynum"];
			$keyvalue = $d["keyvalue"];
			$set = new Set();
			if($table == "assets") $colname = "rollupcost";
			if($table == "workorder") $colname = "rollupcost";
			$set->useTransactionManager()->MboSetRemote($table)->setWhere("$tablekey='$keynum'")->setValue($colname, $keyvalue)->change();
		}

		Super::sendJsonResponse("success", "Asset Cost Rollup Updated");
	}
	
	public static function showcostrollup(){
		$assetnum = $_POST['showcostrollup'];
		$startdate = $_POST['ddfromdate'];
		$enddate = $_POST['ddtodate'];
		$worktype = $_POST['ddworktype'];
		$ob = new stdClass();
		$ob->ddfromdate = $startdate;
		$ob->ddtodate = $enddate;
		$ob->ddworktype = $worktype;
		$ob->showcostrollup = "yes";
		
		XboSet::resetAssetCost();
		XboSet::viewdrilldown($assetnum, "rollupcost", $ob);
	}
	public static function showptt($node=null, $ob=null, $tbname = null){
		
		$tablename = $_POST['tablename'];
		if($node==null) { $node = $_POST['showptt']; 	}
		else{ $node = $node; }
		$rendercost = $_POST["rendercost"];
		
		if(isset($_POST['ddfromdate'])){
			$ob = new stdClass();
			$ob->ddfromdate = $_POST['ddfromdate'];
			$ob->ddtodate = $_POST['ddtodate'];
			$ob->ddworktype = $_POST['ddworktype'];
			self::$subassetarray2 = [];
		}
		
		
		if($tablename == "assets") {
			$keyname = "assetnum";
		}
		if($tablename == "workorder"){
			$keyname = "wonum";
		}
		$set = new Set();
		$parentname = $set->useTransactionManager()->MboSetRemote($tablename)->setWhere("$keyname='$node'")->MboRemote("parent")->getMboSet()->getMbo()->getString("parent");
		if($parentname == null){
			$parentname = $node;
		}else{
			array_push(self::$subassetarray, $parentname);
			$parentname = XboSet::showptt($parentname);
		}
		if($parentname ==  $_POST['showptt'] ){
			$parentname = $_POST['showptt'];
		}
		if($ob != null){
			
			XboSet::viewdrilldown($parentname, "rollup", $ob);
		}else{
			
			XboSet::viewdrilldown($parentname);
		}
	}
	
	
	public function drawDrillDown($node, $rollupcost, $ob=null, $tbname=null){
		
		//global $subassetarray;
		//global $subassetarray2;
		//global $ytdcosttemp;
		
		$as_obj = new stdclass;
		
		$ytdcost = null;
		$ytdcostText = null;
		$set = new Set();
		$tbname = $_POST['tablename'];
		$tbsub = "assetsubs"; $keyname = "assetnum"; $keysub = "subassetnum"; $keyid = "assetsid"; $costcol = "ytdcost";
		if($tbname == "workorder") { $tbsub = "wosubs"; $keyname = "wonum"; $keysub = "subwonum"; $keyid = "workorderid"; $costcol = "actualcost"; }
		$assetsubSet = $set->useTransactionManager()->MboSetRemote($tbsub)->setWhere("$keyname='$node'")->MboRemote("$keysub")->getMboSet();
		$c = $assetsubSet->counts();
		$r = array();
		$flag = 0;
		for($i=0; $i<$c; $i++){
			$assetsub = $assetsubSet->getMbo();
			$subassetnum = $assetsub->getString($keysub);
			$set1 = new Set();
			if($tbname == "assets"){
				$assets = $set1->useTransactionManager()->MboSetRemote($tbname)->setWhere("$keyname='$subassetnum'")->MboRemote("description")->MboRemote("locnum")
				->MboRemote($costcol)->MboRemote($keyid)->getMboSet()->getMbo();
				$description = $assets->getString("description");
				$locnum =  $assets->getString("locnum");
			}else{
				$assets = $set1->useTransactionManager()->MboSetRemote($tbname)->setWhere("$keyname='$subassetnum'")->MboRemote("description")
				->MboRemote($costcol)->MboRemote($keyid)->getMboSet()->getMbo();
				$description = $assets->getString("description");
				$locnum =  "";
			}
			
			$assetsid = $assets->getString($keyid);
			$bluetext = "<font color='blue'><b>$subassetnum </b></font>";
			$text = "$subassetnum " ;
			
			if(isset($_POST['showptt'])) {
				$subass = $_POST['showptt'];
				if($subass == $subassetnum) $text = $bluetext;
				else $text = $text;
			}else{
				$text = $text;
			}
			if($ob == null ){
				$ytdcost = null;
				$ytdcostText = null;
			}else{
				if(isset($ob->ddfromdate)) {
					$get = new Xbo();
					if($tbname == "assets") $cost = $get->updateallassetscost($subassetnum, $ob->ddworktype, $ob->ddfromdate, $ob->ddtodate);
					if($tbname == "workorder")$cost = $get->updateallwocost($subassetnum, $ob->ddworktype, $ob->ddfromdate, $ob->ddtodate);
					$rucost = $cost;
					$ytdcost = $rucost->totalcost;
					$owncost = $rucost->owncost;
					if($ytdcost == null){
						$ytdcost = 0;
					}else{
						$ytdcost = $ytdcost;
					}
					$ytdcostText = "<div class ='rightalign'> + <span data-type='decimal' class='coloritalic'>".number_format($owncost, 2)
					."</span> <span class='coloritalic2'><i class='fa fa-caret-square-up'></i></span><span class='rollup' data-keynum='$subassetnum' data-keyvalue='$ytdcost'> "
					.number_format($ytdcost, 2)."</span> </div>";	
				}else{
					$ytdcost = null;
					$ytdcostText = null;
				} 
			}
			$applinks = "data-drilldown='$keyid%60".$assetsid."' data-keynum='$subassetnum'";
			array_push(self::$subassetarray2, $subassetnum);
			
			$saim = count(self::$subassetarray2);
			
			$saim = "<font color=#FFB400><i class='fa fa-caret-square-right'></i></font>";
			$li = "<li> $saim <a href='javascript:;'>$text</a> - <a href='javascript:;' $applinks >$description </a> | <font color='orange'>$locnum</font> |  $ytdcostText</li>";
			$setr = new Set();
			$assetsubSet2 = $setr->useTransactionManager()->MboSetRemote("$tbsub")->setWhere("$keyname='$subassetnum'")->MboRemote($keysub)->getMboSet();
			$c2 = $assetsubSet2->counts();
			$dd = new XboSet();
			if($c2 > 0) {
				for($j=0; $j<count(self::$subassetarray); $j++){
					if(self::$subassetarray[$j] == $subassetnum) {
						$text = $bluetext;
					}else{
						$text = $text;
					}
					if(end(self::$subassetarray2)==$subassetnum){
					}else{
						array_push(self::$subassetarray2, $subassetnum);						
					}
				}
				if($ob==null){
					$aa = $dd::drawDrillDown($subassetnum, $rollupcost)->ul;
					
				}else{
					$aa = $dd::drawDrillDown($subassetnum, $rollupcost, $ob, self::$ytdcosttemp)->ul;
				}
				array_pop(self::$subassetarray2);
				$li = "<li> $saim <a href='javascript:;'>$text</a> - <a href='javascript:;' $applinks>$description </a> | <font color='orange'>$locnum</font> |  $ytdcostText ".$aa;
			}else{
				array_pop(self::$subassetarray2);
			}
			array_push($r,$li);
		}
		$ul = "<ul>".implode("", $r)."</ul></li>";
		$this->ul = $ul;
		$this->subassetarray2 = self::$subassetarray2;
		$this->subassetarray = self::$subassetarray;
		$this->ytdcosttemp = self::$ytdcosttemp;
		return $this;
	}
	
	public static function resetAssetCost(){
		$set = new Set();
		$set->useTransactionManager()->MboSetRemote("assets")->setWhere("assetnum is not null")
		->setValue("rollupcost", 0)
		->setValue("ytdcost", 0)
		->change();
	}

	public static function viewdrilldown($viewnode = null, $rollupcost=null, $ob=null){
		//global $subassetarray;
		//global $subassetarray2;	
			
		if(isset($_POST['viewdrilldown']) ) $recid = $_POST['viewdrilldown'];
		else $recid = $viewnode;
		
			

		$ytdcostText =null;
		$ytdcost = 0;
		$owncost = 0;
		
		$startdate = null;
		$enddate = null;
		
		if($recid != null){
			
			$keynum = $recid;
			$dd = new XboSet();
			array_push(self::$subassetarray2, $keynum);
				
			$tablename = $_POST['tablename'];
			
			$drawDrillDown = $dd->drawDrillDown($keynum, $rollupcost, $ob, $tablename);
			$node = $drawDrillDown->ul;
			self::$subassetarray  = $drawDrillDown->subassetarray;
			self::$subassetarray2  = $drawDrillDown->subassetarray2;
			self::$ytdcosttemp  = $drawDrillDown->ytdcosttemp;
			
				
			if($tablename == "assets") $keyname = "assetnum";
			if($tablename == "workorder") $keyname = "wonum";
			$AssetSets = (new Set())->useTransactionManager()->MboSetRemote($tablename)->setWhere("$keyname='$keynum'")->MboRemote("description")->getMboSet();
			$Assets = $AssetSets->getMbo();
			if($ob != null ){
				$startdate = $ob->ddfromdate;
				$enddate = $ob->ddtodate;
		
				$description = $Assets->getString("description");
				if(isset($ob->ddfromdate)) { 
					$get = new Xbo();
					if($tablename == "assets") $cost = $get->updateallassetscost($keynum, $ob->ddworktype, $ob->ddfromdate, $ob->ddtodate);
					if($tablename == "workorder") $cost = $get->updateallwocost($keynum, $ob->ddworktype, $ob->ddfromdate, $ob->ddtodate);
					$rucost = $cost;
					$ytdcost = $rucost->totalcost;
					$owncost = $rucost->owncost;
					$ytdcostText = "<div class ='rightalign'>+ <span data-type='decimal' class='coloritalic'>".number_format($owncost, 2)."</span>  <span class='rollup' data-keynum='$keynum'  data-keyvalue='$ytdcost'>"
					.number_format($ytdcost, 2)."</span></div>";
				}else{
					$ytdcostText=null;
				}
					
			}else{
				$set1 = new Set();
				$description = $set1->useTransactionManager()->MboSetRemote($tablename)->setWhere("$keyname='$keynum'")->MboRemote("description")
				->getMboSet()->getMbo()->getString("description");
				$ytdcost = null;
				$ytdcostText = null;
			}
			$bluetext = "<font color='blue'><b>$keynum </b></font>";
			$text = "$keynum " ;

		

			for($j=0; $j<count(self::$subassetarray); $j++){
				if(self::$subassetarray[$j] == $keynum) {
					$text = $bluetext;
				}else{
					$text = $text;
				}
			}
			
			if(isset($ob->showcostrollup)) { //Show Cost Roll Up? yes atau no 
			
				// kalau ada parameter "rendercost", current level anak tidak di cetak
				if(isset($_POST['rendercost'])){ 
					return null; //tidak dicetak 
				}else{
					$li = "<ul class='file-tree'><li><a href='javascript:;'>$text</a> - $description $ytdcostText $node</ul>"; 
				}
				
			}
			else {
				//inisialisasi tampilan awal, ketika user mengklik "View Drill Down"
				$li = "<ul class='file-tree'><li><a href='javascript:;'>$text</a> - $description $ytdcostText $node</ul>";
			}
			
			//$totalwos = array_unique(Xbo::$drilldownWos);
			$totalwos = Xbo::$drilldownWos;
			$totalwos = array_unique($totalwos, SORT_REGULAR);
			$totalwos = array_values($totalwos);
			Super::sendJsonResponse("success", "Asset Hierarchy Loaded",
			[
				"hierarchy"=>$li, 
				"ytdcost"=>$ytdcost, 
				"owncost" => $owncost,
				 "startdate"=>$startdate, 
				 "enddate" => $enddate, 
				 "keynum" => $keynum,
				 "wos" => $totalwos
			]);
		}
		
	}
	
	
	public static function EnterMeterReadingInput(){
		$assetnum = Super::getParam("assetnum");
		$set = new Set();
		$assetMeterSet = (new Set())->useTransactionManager()->MboSetRemote("assetmeter")->setRelationship("meter", "assetmeter.meternum = meter.meternum")
		->setWhere("assetmeter.assetnum = '$assetnum'")->MboRemote("assetmeter.meternum")->MboRemote("meter.metertype")
		->MboRemote("assetmeter.meterlookup")->MboRemote("assetmeter.lastreading")->MboRemote("assetmeter.lastreadingdate")
		->MboRemote("meter.description as meterdesc")->getMboSet("distinct");
		$assmtrcnt = $assetMeterSet->counts();
		
		$trr = [];
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		for($i=0; $i<$assmtrcnt; $i++){
			$stdob = new stdClass();
			$assetMeter = $assetMeterSet->getMbo();
			$meternum = $assetMeter->getString("meternum");
			$metertype = $assetMeter->getString("metertype");
			$meterdesc = $assetMeter->getString("meterdesc");
			$meterlookup = $assetMeter->getString("meterlookup");
			$lastreading = $assetMeter->getString("lastreading");
			$lastreadingdate = $assetMeter->getString("lastreadingdate");
			$mtrlookupname = "";
			if(!empty($meterlookup) != null){
				$mtrlookupname = " data-lookup='$meterlookup'";
			}
			$tr = "<tr> 
					<td data-attribute='meternum'>$meternum</td> 
					<td data-attribute='meterdesc'>$meterdesc</td> 
					<td data-attribute='metertype'>$metertype</td> 
					<td data-attribute='lastreading'><center>$lastreading</center></td> 
					<td data-attribute='lastreadingdate'>$lastreadingdate</td> 
					<td data-attribute='delta'><center><input type='checkbox' data-attribute='delta'></center></td>
					<td data-attribute='metertanggal'><input type='text' data-attribute='metertanggal' data-type='date'></td>
					<td data-attribute='enterby'><input type='text' data-attribute='enterby' value = '$enterby' data-lookup='employees'></td> 
					<td data-attribute='value'><input type='text' data-attribute='value' $mtrlookupname></td>
			</tr>";
			array_push($trr, $tr);
			
		}
		$tbody = implode(" ", $trr);
		$th = "<th>Meter Name</th> <th>Meter Description</th> <th>Meter Type</th> <th>Last Reading</th> <th>Last Reading Date</th><th>Is Delta ?</th> <th>Enter Date</th> <th>Enter By</th> <th>Value</th>";
		$thead = "<thead>$th</thead>";
		$tfoot = "<tfoot>$th</tfoot>";
		$renderTable = $thead . $tfoot . $tbody;
		echo $renderTable;
	}
	
	public static function getMaxesViewer()

	{
		return Super::getMaxesViewer();
	}
	public static function getReportFolder()

	{
		return Super::getReportFolder();
	}
	public static function runReportParam()
	{
		Super::runReportparam();
	}
	public static function runReport()

	{
		Super::runReport();
	}
	public static function delRptdesign()

	{
		$target_dir = urlencode(XboSet::getReportFolder());
		$filename = urldecode($target_dir) . $_GET['delRptdesign'];
		if (file_exists($filename)) {
			unlink($filename);
			echo "file Exist";
		}
	}
	
	public static function rptUpload()
	{
		// 1. Cek session
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		// 2. Cek ketersediaan file
		if (!isset($_FILES['rptToUpload']) || !is_uploaded_file($_FILES['rptToUpload']['tmp_name'])) {
			Super::sendJsonResponse("error", 'No file was uploaded.');
			return;
		}

		$file = $_FILES['rptToUpload'];
		
		// 3. Cek Error PHP Native
		if ($file['error'] !== UPLOAD_ERR_OK) {
			Super::sendJsonResponse("error", 'Upload error. Code: ' . $file['error']);
			return;
		}

		$tmpName = $file['tmp_name'];
		$rawName = basename($file['name']);
		$fileExtension = strtolower(pathinfo($rawName, PATHINFO_EXTENSION));
		
		// --- SECURITY 1: STRICT EXTENSION CHECK ---
		if ($fileExtension !== 'rptdesign') {
			Super::sendJsonResponse("error", 'Invalid file type. Only .rptdesign files are allowed.');
			return;
		}

		// --- SECURITY 2: MIME TYPE / CONTENT CHECK ---
		// Pastikan ini file teks/xml, bukan binary executable yang disamarkan
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($tmpName);
		
		// RPTDESIGN biasanya terdeteksi sebagai 'text/xml', 'application/xml', atau 'text/plain'
		$allowedMimes = ['text/xml', 'application/xml', 'text/plain'];
		if (!in_array($mime, $allowedMimes)) {
			// Double check header file (Magic Bytes) untuk memastikan bukan ELF/EXE
			$handle = fopen($tmpName, 'rb');
			$header = fread($handle, 4);
			fclose($handle);
			
			// Tolak jika header file adalah binary executable (ELF, MZ)
			if (strpos($header, 'ELF') !== false || strpos($header, 'MZ') !== false) {
				Super::sendJsonResponse("error", 'Security Alert: Invalid file content.');
				return;
			}
		}

		// --- SECURITY 3: FILENAME SANITIZATION (Strict) ---
		// Ambil nama file tanpa ekstensi dulu
		$nameWithoutExt = pathinfo($rawName, PATHINFO_FILENAME);
		
		// Hanya izinkan Alphanumeric, Underscore, dan Dash. 
		// Hapus titik di tengah nama file untuk mencegah Double Extension (shell.php.rptdesign)
		$cleanName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $nameWithoutExt);
		
		// Susun ulang nama file
		$reportName = $cleanName . '.' . $fileExtension;
		
		// Validasi panjang nama file
		if (strlen($reportName) > 255) {
			Super::sendJsonResponse("error", 'Filename is too long.');
			return;
		}

		// 4. Validasi ukuran (Max 20MB)
		if ($file['size'] > 20000000) { 
			Super::sendJsonResponse("error", "File is too large (max 20MB).");
			return;
		}

		try {
			$targetDir = XboSet::getReportFolder(); 
			$targetDir = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR;
			$targetFile = $targetDir . $reportName;

			// --- FEATURE: BACKUP EXISTING FILE (Opsional) ---
			// Jika file sudah ada, rename jadi .bak sebelum ditimpa
			if (file_exists($targetFile)) {
				$backupFile = $targetFile . ".bak";
				// Hapus backup lama jika ada
				if (file_exists($backupFile)) unlink($backupFile); 
				copy($targetFile, $backupFile);
			}

			// 5. Pindahkan File
			if (move_uploaded_file($tmpName, $targetFile)) {
				
				$dateTime = date("Y-m-d H:i:s");
				$sessionName = $_SESSION['username'] ?? 'unknown'; 

				// --- DB UPDATE ---
				(new Set())->useTransactionManager()
						->MboSetRemote("report")
						->setWhere("reportname='$reportName'") 
						->setValue("importeddate", $dateTime)
						->setValue("importedby", $sessionName)
						->change();

				Super::sendJsonResponse("success", "The file '$reportName' has been uploaded successfully.");

			} else {
				// Log error asli ke server log (bukan ke user)
				error_log("Upload Failed: Could not move $tmpName to $targetFile");
				Super::sendJsonResponse("error", "Error: Could not move uploaded file. Check server permissions.");
			}
		} catch (Exception $e) {
			error_log("DB Error during RptUpload: " . $e->getMessage());
			Super::sendJsonResponse("error", 'A server error occurred during database update.');
		}
	}

	
	public static function displayReportList()

	{
		$reportApp = Super::getParam("reportApp");
		Xbo::select("Report Description", "description");
		XboSet::getXboSet("report", "appname='$reportApp'");
		XboSet::displayData();
	}
	public static function changeStatus()
	{
		
		$super = new Super();
		$super->changeStatus();
	}
	
	public static function linkTable()

	{
		$package = $_POST['linktable'];
		$super = new Super();
		$super->linkTable($package);
	}
	public static function delLookupPackage()

	{
		$package = $_GET['delLookupPackage'];
		$super = new Super();
		$super->delLookupPackage($package);
	}
	
	public static function EsigUpdate(){
		$cbvalue = Super::getParam("esigupdate"); 
		$filename = Super::getParam("filename");  
		$personid =  Super::getParam("personid"); 
		$ownerTable =Super::getParam("ownertable"); 
		
		if(empty($esigupdate)) $esigupdate = "N";
		else "Y";
		
		$set = new Set();
		$ownerid = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("filename='$filename'")->MboRemote("ownerid")->getMboSet("remove_duplicate")->getMbo()->getString("ownerid");
		
		if($cbvalue == "Y"){
			(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownerid='$ownerid' and ownertable='$ownerTable' and signature='Y'")->setValue("signature", "N")->change();
			(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere(" filename='$filename' ")->setValue("signature", $cbvalue)->change();
		}else{
			(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere(" filename='$filename' ")->setValue("signature", $cbvalue)->change();
		}
		
		
		
		$msg = new stdClass();
		$msg->msg = "esig_updated";
		Set::throws($msg);
		
	}
	public static function updateFileInfo()

	{
		$cbvalue =  Super::getParam("filedefault");
		$filename = Super::getParam("filename"); 
		$filedesc = Super::getParam("updateFileInfo");  
		$ownerTable =Super::getParam("ownertable");  
		
		if ($cbvalue == "") {
			$cbvalue = "N";
		}
		
		$ownerid = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("filename='$filename'")->MboRemote("ownerid")->getMboSet("remove_duplicate")->getMbo()->getString("ownerid");
		if($cbvalue == "Y"){
			(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownerid='$ownerid' and ownertable='$ownerTable' and filedefault='Y'")->setValue("filedefault", "N")->change();
			(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere(" filename='$filename' ")->setValue("description", $filedesc)->setValue("filedefault", $cbvalue)->change();
		}else{
			(new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere(" filename='$filename' ")->setValue("description", $filedesc)->setValue("filedefault", $cbvalue)->change();
		}
		
		
		
		$msg = new stdClass();
		$msg->msg = "filedefault_updated";
		Set::throws($msg);
	}
	public static function downloadFiles()

	{
		$servername = $_SERVER['SERVER_NAME'];
		$path = XboSet::getAttachFolder();
		$downloadFiles = $_GET["downloadFiles"];
		if (file_exists($path)) {
			$dl_file = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})", '', $downloadFiles); // simple file name validation
			$dl_file = filter_var($dl_file, FILTER_SANITIZE_URL); // Remove (more) invalid characters
			$fullPath = $path . $dl_file;
			if ($fd = fopen($fullPath, "r")) {
				$fsize = filesize($fullPath);
				$path_parts = pathinfo($fullPath);
				$ext = strtolower($path_parts["extension"]);
				switch ($ext) {
					/* image file format */
				case "jpg":
					header("Content-type: application/jpg");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\""); // use 'attachment' to force a file download
					break;

				case "png":
					header("Content-type: application/png");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "bmp":
					header("Content-type: application/bmp");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "gif":
					header("Content-type: application/gif");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;
					/* Movie File Format */
				case "mpg":
					header("Content-type: application/mpg");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\""); // use 'attachment' to force a file download
					break;

				case "mpeg":
					header("Content-type: application/mpeg");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "mov":
					header("Content-type: application/mov");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "avi":
					header("Content-type: application/avi");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "wmv":
					header("Content-type: application/wmv");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;
					/* office File Format */
				case "doc":
					header("Content-type: application/doc");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\""); // use 'attachment' to force a file download
					break;

				case "docx":
					header("Content-type: application/docx");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "xls":
					header("Content-type: application/xls");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "xlsx":
					header("Content-type: application/xlsx");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "ppt":
					header("Content-type: application/ppt");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				case "pptx":
					header("Content-type: application/pptx");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;
					/*PDF file format */
				case "pdf":
					header("Content-type: application/pdf");
					header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
					break;

				default;	
				header("Content-type: application/octet-stream");
				header("Content-Disposition: filename=\"" . $path_parts["basename"] . "\"");
				break;
			}
			header("Content-length: $fsize");
			header("Cache-control: private"); //use this to open files directly
			while (!feof($fd)) {
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}
		fclose($fd);
		exit;
	}
}
	public static function displayFiles()

	{
		$ownerParts = explode("^", $_GET['displayFiles']);
		$ownertable = $ownerParts[0];
		$ownerid = $ownerParts[1];
		$fsname = ContextPath::getFullHostName(); /* https://maxes.id/maxtest */
		$origPath = $fsname . "/attachments/";
		$thumbPath = $fsname . "/attachments/thumbs/";
		$iconPath = $fsname . "/icons/";
		$thumbName = "thumb_";
		
		
		$set = new Set();
		$fileinfoSet = $set->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownerid = '$ownerid' and ownertable = '$ownertable'")->MboRemote("filename")
		->MboRemote("description")->MboRemote("uploaddate")->MboRemote("type")->MboRemote("ownertable")->MboRemote("filesize")->MboRemote("sentby")
		->MboRemote("receivedby")->MboRemote("fileinfoid")->getMboSet();
		$numrows = $fileinfoSet->counts();
		$set2 = new Set();
		if ($numrows > 0) {
			echo "<div class='row'>";
			for ($i = 0; $i < $numrows; $i++) {
				$fileinfo = $fileinfoSet->getMbo();
				$date = date_create($fileinfo->getString("uploaddate"));
				$fileType = strtolower($fileinfo->getString("type"));
				$dateFormat = date_format($date, "d M Y");
				$imgid = $fileinfo->getString("filename");
				$description = $fileinfo->getString("description");
				$sentby = $fileinfo->getString("sentby");
				$receivedby = $fileinfo->getString("receivedby");
				$editbutton = "<img class=\"maxesfupd\" src=\"" . $iconPath . "/update_icon.png\"> ";
				$fsize = $fileinfo->getString("filesize");
				$origfilepath = XboSet::getAttachFolder() . $imgid;
				
				if ($fileType == "jpg" || $fileType == "png" || $fileType == "jpeg" || $fileType == "bmp" || $fileType == "gif") {
					echo "<div class=\"col-md-4 col-lg-4 col-md-4\">
								<table border=0 class=\"imgtable\">							
								<tr>
									<td>
										<div class=\"imgcontainer\">
										<div class=\"imgtag\"><a href='javascript:;' id=\"" . $imgid . "\">Download</a></div>
										<a href=\"$origPath$imgid\" target='_blank'><img class=\"maxesil\" src=" . "\"$thumbPath" . "thumb_" . $imgid . "\" data-filename=\"" . $imgid . "\"></a>
										</div>
									</td>
								</tr>
								<tr>
									<td><blockquote class=\"editarea\">" . Xbo::editButton($iconPath, $imgid) . "<div class=\"maxesattdesc\">" . $description . " </div> 
									<font style=\"font-family:'calibri'; color:#919191; font-size:9pt;\"> 
									<b>Uploaded on: </b><i>" . $dateFormat . "</i> 
									<br /><b>Size: </b>" . $fsize . " KB
									</blockquote>
									</td>
								</tr>
								</table>
							</div>";
				}
				if ($fileType == "mov" || $fileType == "mpg" || $fileType == "mpeg" || $fileType == "avi" || $fileType == "mp4") {
					echo "<div class=\"col-md-4 col-lg-4 col-md-4\">
								<table border=0>							
								<tr>
									<td>
										<div class=\"imgcontainer\">
										<div class=\"imgtag\"><a href='javascript:;' id=\"" . $imgid . "\">Download</a></div>
										<img class=\"maxesil\" src=" . "\"$iconPath" . "video_icon.png\">
										</div>
									</td>
								</tr>
								<tr>
									<td><blockquote class=\"editarea\">" . Xbo::editButton($iconPath, $imgid) . "<div class=\"maxesattdesc\">" . $description . " </div> 
									<font style=\"font-family:'calibri'; color:#919191; font-size:9pt;\"> 
									<b>Uploaded on: </b><i>" . $dateFormat . "</i> 
									<br /><b>Size: </b>" . $fsize . " KB
									</blockquote>
									</td>
								</tr>
								
								</table>
							</div>";
				}
				if ($fileType == "pdf") {
					echo "<div class=\"col-md-4 col-lg-4 col-md-4\">
								<table border=0>							
								<tr>
									<td>
										<div class=\"imgcontainer\">
										<div class=\"imgtag\"><a href='javascript:;' id=\"" . $imgid . "\">Download</a></div>
										<img class=\"maxesil\" src=" . "\"$iconPath" . "pdf_icon.png\">
										</div>
									</td>
								</tr>
								<tr>
									<td><blockquote class=\"editarea\">" . Xbo::editButton($iconPath, $imgid) . "<div class=\"maxesattdesc\">" . $description . " </div> 
									<br /> <font style=\"font-family:'calibri'; color:#919191; font-size:9pt;\"> 
									<b>Uploaded on: </b><i>" . $dateFormat . "</i> 
									<br /><b>Size: </b>" . $fsize . " KB
									</blockquote>
									</td>
								</tr>
								
								</table>
							</div>";
				}
				if ($fileType == "xls" || $fileType == "xlsx") {
					echo "<div class=\"col-md-4 col-lg-4 col-md-4\">
								<table border=0>							
								<tr>
									<td>
										<div class=\"imgcontainer\">
										<div class=\"imgtag\"><a href='javascript:;' id=\"" . $imgid . "\">Download</a></div>
										<img class=\"maxesil\" src=" . "\"$iconPath" . "excel_icon.png\">
										</div>
									</td>
								</tr>
								<tr>
									<td><blockquote class=\"editarea\">" . Xbo::editButton($iconPath, $imgid) . "<div class=\"maxesattdesc\">" . $description . " </div> 
									<font style=\"font-family:'calibri'; color:#919191; font-size:9pt;\"> 
									<b>Uploaded on: </b><i>" . $dateFormat . "</i> 
									<br /><b>Size: </b>" . $fsize . " KB
									</blockquote>
									</td>
								</tr>
								
								</table>
							</div>";
				}
				if ($fileType == "doc" || $fileType == "docx") {
					echo "<div class=\"col-md-4 col-lg-4 col-md-4\">
								<table border=0>							
								<tr>
									<td>
										<div class=\"imgcontainer\">
										<div class=\"imgtag\"><a href='javascript:;' id=\"" . $imgid . "\">Download</a></div>
										<img class=\"maxesil\" src=" . "\"$iconPath" . "msword_icon.png\">
										</div>
									</td>
								</tr>
								<tr>
									<td><blockquote class=\"editarea\">" . Xbo::editButton($iconPath, $imgid) . "<div class=\"maxesattdesc\">" . $description . " </div>
									<font style=\"font-family:'calibri'; color:#919191; font-size:9pt;\"> 
									<b>Uploaded on: </b><i>" . $dateFormat . "</i> 
									<br /><b>Size: </b>" . $fsize . " KB
									</blockquote>
									</td>
								</tr>
								
								</table>
							</div>";
				}
				if ($fileType == "ppt" || $fileType == "pptx") {
					echo "<div class=\"col-md-4 col-lg-4 col-md-4\">
								<table border=0>							
								<tr>
									<td>
										<div class=\"imgcontainer\">
										<div class=\"imgtag\"><a href='javascript:;' id=\"" . $imgid . "\">Download</a></div>
										<img class=\"maxesil\" src=" . "\"$iconPath" . "ppt_icon.png\">
										</div>
									</td>
								</tr>
								<tr>
									<td><blockquote class=\"editarea\">" . Xbo::editButton($iconPath, $imgid) . "<div class=\"maxesattdesc\">" . $description . " </div>
									<font style=\"font-family:'calibri'; color:#919191; font-size:9pt;\">
									<b>Uploaded on: </b><i>" . $dateFormat . "</i> 
									<br /><b>Size: </b>" . $fsize . " KB
									</blockquote>
									</td>
								</tr>
								</table>
							</div>";
				}
				if ($fileType == "ytb") {
					$youtubeurl2 = str_replace("embed/", "watch?v=", $imgid);
					echo "<div class=\"col-md-4 col-lg-4 col-md-4\">
								<table border=0>							
								<tr>
									<td>
										<div class=\"imgcontainer\">
										<iframe width='300' height='195'
											src='$imgid' frameborder='0' allowfullscreen>
										</iframe>
										</div>
									</td>
								</tr>
								<tr>
									<td><blockquote class=\"editarea\">" . Xbo::editButton($iconPath, $imgid) . "<div class=\"maxesattdesc\">" . $description . " </div>
									<font style=\"font-family:'calibri'; color:#919191; font-size:9pt;\">
									<b>Embedded on: </b><i>" . $dateFormat . "</i> 
									<br /><b>URL: </b><i>$youtubeurl2</i>
									</blockquote>
									</td>
								</tr>
								</table>
							</div>";
				}
			
			}
				echo "</div>";
		}
		else {
			echo "<center>
					<font class=\"maxesnoatt\">There are no attachments to display.
					</font>
					</center>";
		}
	}

	
	public static function attKey(){
		$attkey = $_POST['attkey'];
		$table = $_POST['set'];
		
		$set = new Set();
		$fileInfoSet = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownerid='$attkey' and ownertable='$table'")->getMboSet();
		$fcount = $fileInfoSet->counts();
		Set::throws(["fcount" =>$fcount]);
	}
	public static function deleteFileUpload()
	{
		error_reporting(0);
		$childPackage = $_GET['delFileUpload'];
		$super = new Super();
		$super->deleteFileUpload($childPackage);
	}

	public static function getAttachThumbsFolder()
	{
		return Super::getAttachThumbsFolder();
	}
	public static function getAttachFolder()
	{
		return Super::getAttachFolder();
	}
	


	public static function fileUpload()
	{
		$originalFname = isset($_FILES["fileToUpload"]["name"]) ? basename($_FILES["fileToUpload"]["name"]) : "";
		$msg           = null;
		$cause         = null;
		$uploadPath    = null;
		$absolutePath  = getcwd();

		// ── GUARD: pastikan file ada dan tidak error saat upload ──────────────
		if (!isset($_FILES["fileToUpload"]["name"]) || $_FILES["fileToUpload"]["error"] !== UPLOAD_ERR_OK) {
			Set::throws(["msg" => "nofile", "fname" => $originalFname, "cause" => null, "uploadpath" => null, "absolutePath" => $absolutePath]);
			return;
		}

		$tmpName                  = $_FILES["fileToUpload"]["tmp_name"];
		$rawFileName              = basename($_FILES["fileToUpload"]["name"]);
		$fileName_without_extension = pathinfo($rawFileName, PATHINFO_FILENAME);
		$fileExtension            = strtolower(pathinfo($rawFileName, PATHINFO_EXTENSION));

		// ── SECURITY LAYER 1: WHITELIST EXTENSION ────────────────────────────
		$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'xls', 'xlsx', "ppt", "pptx", "doc", "docx"];
		if (!in_array($fileExtension, $allowedExtensions)) {
			Set::throws(["msg" => "invalid_extension", "fname" => $originalFname, "cause" => "Extension '.$fileExtension' is not allowed.", "uploadpath" => null, "absolutePath" => $absolutePath]);
			return;
		}

		// ── SECURITY LAYER 2: FILE SIZE ───────────────────────────────────────
		if ($_FILES["fileToUpload"]["size"] > 700000000) {
			Set::throws(["msg" => "filetoobig", "fname" => $originalFname, "cause" => null, "uploadpath" => null, "absolutePath" => $absolutePath]);
			return;
		}

		$isloader = isset($_POST["isloader"]) ? $_POST["isloader"] : "";

		// ════════════════════════════════════════════════════════════════════════
		//  CASE 1 ── DATA LOADER  (isloader == "y")
		// ════════════════════════════════════════════════════════════════════════
		if ($isloader === "y") {

			$appname = $_POST["ownertable"] ?? "";
			$url     = (new Set())->useTransactionManager()
								->MboSetRemote("mxsapp")
								->where("appname=?", $appname)
								->getMboValue("url");

			// 1. Auto-create target directory
			$target_dir = Super::getExcelFolder() . $url . DIRECTORY_SEPARATOR;
			if (!is_dir($target_dir)) {
				mkdir($target_dir, 0755, true);
			}

			// 2. Cleanup old slave files
			$patternToDelete = $target_dir . $fileName_without_extension . "_import_*." . $fileExtension;
			foreach (glob($patternToDelete) as $file) {
				if (is_file($file)) unlink($file);
			}

			// 3. Define master & slave paths
			$masterFile     = $target_dir . $rawFileName;
			$safeImportName = $fileName_without_extension
							. "_import_" . round(microtime(true))
							. "_" . bin2hex(random_bytes(4))
							. "." . $fileExtension;
			$slaveFile = $target_dir . $safeImportName;

			// 4. Save slave file
			if (!move_uploaded_file($tmpName, $slaveFile)) {
				Set::throws(["msg" => "errortoupload", "fname" => $originalFname, "cause" => "Failed to move slave file.", "uploadpath" => null, "absolutePath" => $absolutePath]);
				return;
			}

			// 5. Seed master template jika belum ada
			if (!file_exists($masterFile)) {
				try {
					copy($slaveFile, $masterFile);

					$spreadsheet = IOFactory::load($masterFile);
					$sheet       = $spreadsheet->getActiveSheet();
					$highestRow  = $sheet->getHighestRow();

					if ($highestRow > 3) {
						$sheet->removeRow(4, $highestRow - 3);
					}

					$writer = IOFactory::createWriter($spreadsheet, ucfirst($fileExtension));
					$writer->save($masterFile);

					$msg   = "uploadsuccess";
					$cause = "Master template created and data queued for import.";
				} catch (Exception $e) {
					error_log("Failed to seed master: " . $e->getMessage());
					$msg   = "errortoupload";
					$cause = "Failed to seed master template.";
				}
			} else {
				$msg = "uploadsuccess";
			}

			Set::throws(["msg" => $msg, "fname" => $originalFname, "cause" => $cause, "uploadpath" => $slaveFile, "absolutePath" => $absolutePath]);
			return;
		}

		// ════════════════════════════════════════════════════════════════════════
		//  CASE 2 ── REGULAR ATTACHMENT UPLOAD
		// ════════════════════════════════════════════════════════════════════════

		$target_dir       = XboSet::getAttachFolder();
		$targetThumbs_dir = XboSet::getAttachThumbsFolder();

		$description = $_POST['description'] ?? "";
		$ownertable  = $_POST['ownertable']  ?? "";
		$ownerID     = $_POST['ownerid']     ?? "";
		$dateNow     = date("Y-m-d");

		// Buat nama file unik & aman
		$newfilename = $ownertable . "_" . $ownerID . "_" . round(microtime(true)) . "." . $fileExtension;
		$uploadPath  = $target_dir . $newfilename;

		// Pindahkan file dari tmp
		if (!move_uploaded_file($tmpName, $uploadPath)) {
			$errCode = $_FILES["fileToUpload"]["error"];
			Set::throws(["msg" => "errortoupload", "fname" => $originalFname, "cause" => "Upload failed (error #$errCode).", "uploadpath" => null, "absolutePath" => $absolutePath]);
			return;
		}

		$msg      = "uploadsuccess";
		$fileType = $fileExtension; // sudah lowercase dari atas

		// ── Thumbnail generation (hanya untuk image) ─────────────────────────
		$supportedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
		if (in_array($fileType, $supportedImageTypes)) {

			if (!extension_loaded('gd')) {
				Set::throws(["msg" => "gd_extension_missing", "fname" => $originalFname, "cause" => "GD Library not installed.", "uploadpath" => $uploadPath, "absolutePath" => $absolutePath]);
				return;
			}

			$originalImage = null;
			switch ($fileType) {
				case 'jpg':
				case 'jpeg': $originalImage = imagecreatefromjpeg($uploadPath); break;
				case 'png':  $originalImage = imagecreatefrompng($uploadPath);  break;
				case 'gif':  $originalImage = imagecreatefromgif($uploadPath);  break;
				case 'bmp':  $originalImage = imagecreatefrombmp($uploadPath);  break;
			}

			if ($originalImage === false || $originalImage === null) {
				Set::throws(["msg" => "failed_to_create_image", "fname" => $originalFname, "cause" => "Unable to create image resource.", "uploadpath" => $uploadPath, "absolutePath" => $absolutePath]);
				return;
			}

			$imgWidth   = imagesx($originalImage);
			$imgHeight  = imagesy($originalImage);
			$thumbWidth = 261;
			$thumbHeight = (int) round(($thumbWidth / $imgWidth) * $imgHeight);

			$imageThumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
			imagecopyresampled($imageThumb, $originalImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight);
			imagejpeg($imageThumb, $targetThumbs_dir . "thumb_" . $newfilename);

			imagedestroy($originalImage);
			imagedestroy($imageThumb);
		}
		// ── END thumbnail ─────────────────────────────────────────────────────

		$filesize = Super::filesizeformat(filesize($uploadPath));

		// ── Simpan ke DB (dengan atau tanpa childtable) ───────────────────────
		$hasChild  = isset($_POST["childtable"]) && isset($_POST["childid"]);
		$childtable = $hasChild ? $_POST["childtable"] : null;
		$childid    = $hasChild ? $_POST["childid"]    : null;

		// Handling khusus fleetusagecust
		if ($hasChild && $childtable === "fleetusagecust") {
			$set      = new Set();
			$fucust   = $set->useTransactionManager()
							->MboSetRemote("fleetusagecust")
							->setWhere("fleetusagecustid='$childid'")
							->MboRemote("fullname")
							->MboRemote("phone")
							->getMboSet()
							->getMbo();

			$fullname = $fucust->getString("fullname");
			$phone    = $fucust->getString("phone");

			$descr = array_filter([$description, $phone, $fullname], fn($v) => $v !== "");
			$description = implode(" - ", $descr);

			(new Set())->useTransactionManager()
					->MboSetRemote("fleetusagecust")
					->setWhere("fleetusagecustid='$childid'")
					->setValue("filetransfer", "Y")
					->change();
		}

		// Insert ke fileinfo
		$dbSet = (new Set())->useTransactionManager()
			->MboSetRemote("fileinfo")
			->setValue("filename",    $newfilename)
			->setValue("description", $description)
			->setValue("uploaddate",  $dateNow)
			->setValue("ownerid",     $ownerID)
			->setValue("ownertable",  $ownertable)
			->setValue("type",        $fileType)
			->setValue("hits",        0)
			->setValue("filesize",    $filesize);

		if ($hasChild) {
			$dbSet->setValue("childtable", $childtable)
				->setValue("childid",    $childid);
		}

		$dbSet->add();

		Set::throws(["msg" => $msg, "fname" => $originalFname, "cause" => $cause, "uploadpath" => $uploadPath, "absolutePath" => $absolutePath]);
	}


	public static function recalculateNextVal($value, $nextval){
		$set = new Set();
		$nextval = $nextval+1;
		(new Set())->useTransactionManager()->MboSetRemote("mxsnextval")->setWhere("app='$value' and isprimary='Y'")->setValue("nextval", $nextval)->change();
		XboSet::getNextVal();
	}
	public static function getNextVal()

	{
		$value = $_GET['getNextVal'];
		$db = new dbo();
		$dbtype = $db->dbtype();
		if ($dbtype == "oracle") $value = strtoupper($value);
	
		$mxsnextvalSet = (new Set())->useTransactionManager()->MboSetRemote("mxsnextval")->setWhere("app='$value' and isprimary='Y'")->MboRemote("keyattr")->MboRemote("prefix")->MboRemote("suffix")
		->MboRemote("pad")->MboRemote("nextval")->getMboSet();
		$mxsnextval = $mxsnextvalSet->getMbo();
		$keyattr = $mxsnextval->getString("keyattr");
		$prefix = $mxsnextval->getString("prefix");
		$nextval = $mxsnextval->getString("nextval");
		$suffix = $mxsnextval->getString("suffix");
		$pad = $mxsnextval->getString("pad");
		
		
		if ($keyattr != "") {
			if($pad!=null){
				$varnum = $nextval;
				$zerofill = $pad;
				$nextval = str_pad($varnum, $zerofill, '0', STR_PAD_LEFT);
			}
			
			
			$keyval = $prefix.$nextval.$suffix;
		
			$appSet = (new Set())->useTransactionManager()->MboSetRemote($value)->setWhere("$keyattr='$keyval'")->getMboSet();
			$cnt = $appSet->counts();
			
			if($cnt == 0){
				Set::throws(["keyattr"=>$keyattr, "prefix"=>$prefix, "nextval"=>$nextval, "suffix"=>$suffix]);
				$nextvalue = $nextval + 1;
				(new Set())->useTransactionManager()->MboSetRemote("mxsnextval")->setWhere("keyattr='$keyattr' and app='$value'")->setValue("nextval", $nextvalue)->change();
			}else{
				$RecalculateNextVal = XboSet::recalculateNextVal($value, $nextval);
			}
		}else{
			Set::throws(["keyattr"=>null, "prefix"=>null, "nextval"=>null, "suffix"=>null]);
		}
	}
	public static function getInitStatus()

	{
		$value = Super::getParam("getInitStatus");
	
		$keyValue = (new Set())->useTransactionManager()->MboSetRemote("mxsattrvalue")
		->setWhere("app='$value' and statuslevel=1 and type='status'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
		if(!empty($keyValue)){
			Super::sendJsonResponse("success", "Status Found", ["status"=>$keyValue]);
		}else{
			Super::sendJsonResponse("success", null);
		}
	}
	
	public static function chorg()

	{
		$ch = explode("|", $_GET['chorg']);
		$user = $ch[0];
		$set = $ch[1];
		if ($set == "chartofaccount" || $set == "organization" || $set == "currency") {
			echo "200";
		}
		else {
			$set = new Set();
			$comSet = (new Set())->useTransactionManager()->MboSetRemote("employees")->setRelationship("mxsusers", "mxsusers.personid = employees.personid")->setWhere("mxsusers.userid='$user'")->MboRemote("employees.orgcode")->getMboSet();
			$com = $comSet->getMbo();
			$o = $com->getString("orgcode");
			if (empty($o)) {
				echo "198";
			}
			else {
				echo "200";
			}
		}
	}
	public static function deleteParent()
	{
		Super::deleteParent();
	}
	public static function showNextRecord()
	{
		Super::showNextRecord();
	}
	public static function showPrevRecord()

	{
		Super::showPrevRecord();
	}
	public static function getLinenum()

	{
		
		$value = $_GET['getLinenum'];
		$parts = explode("|", $value);
		$ownerTable = $parts[0];
		$ownerCols = explode("`", $parts[1]);
		$oColname = $ownerCols[0];
		$oColVal = "'" . $ownerCols[1] . "'";
		$childTable = $parts[2];
		$childCols = $parts[3];
		$set = new Set();

		$where = "$oColname = $oColVal";
		if($childTable == "legaldoc"){
			$where = "ownernum = $oColVal AND ownertable = '$ownerTable'";
		}
		$tableSet = $set->MboSetRemote($childTable)->setWhere($where)->MboRemote("max($childCols)as value")->getMboSet();
		$table = $tableSet->getMbo();
		$value = $table->getString("value");
		
		if($value == null){
			$value = 10;
		}else{
			$value = $value+10;
		}
		echo $value;
		
	}
	public static function delSubChildPackage()
	{
		Super::delSubChildPackage();
	}
	public static function deleteChildren()
	{
		Super::deleteChildren();
	}
	public static function updateTableRowDetail()
	{
		Super::updateTableRowDetail();
	}
	public static function changes()
	{
		Super::changes();
	}
	public static function validate()
	{
		$xrt = Super::validate();
		$set = new Set();
		$count = $set->getMboSet("", $xrt)->counts();
		$result = new stdClass();
		$result->num_rows = false;
		if ($count > 0) {
			$result->num_rows = true;
		}
		else {
			$result->num_rows = false;
		}
		return $result;
	}

	public static function changeChildren()
	{
		Super::changeChildren();
	}
	
	public static function LoadProfile(){
		$set = new Set();
		$orgSet = (new Set())->useTransactionManager()->MboSetRemote("organization")->MboRemote("orgcode")->MboRemote("description")->MboRemote("npwp")->MboRemote("name1")->MboRemote("title1")->getMboSet();
		$org = $orgSet->getMbo();
		$orgcode = $org->getString("orgcode");
		$description = $org->getString("description");
		$npwp = $org->getString("npwp");
		$name1 = $org->getString("name1");
		$title1 = $org->getString("title1");
		
		$set2 = new Set();
		$addressSet = $set2->MboSetRemote("address")->MboRemote("addresscode")->MboRemote("description as addrdescription")->MboRemote("stateprovince")
		->MboRemote("zippostalcode")->MboRemote("officephone")->MboRemote("city")->MboRemote("country")
		->getMboSet();
		$address = $addressSet->getMbo();
		
		$addresscode = $address->getString("addresscode");
		$addrdescription = $address->getString("addrdescription");
		$stateprovince = $address->getString("stateprovince");
		$zippostalcode = $address->getString("zippostalcode");
		$officephone = $address->getString("officephone");
		$city = $address->getString("city");
		$country = $address->getString("country");
		
		
		
		$set2 = new Set();
		$whsSet = $set2->MboSetRemote("warehouse")->MboRemote("warehouse")->MboRemote("description")
		->getMboSet();
		$wh = $whsSet->getMbo();
		
		$warehouse = $wh->getString("warehouse");
		$whdesc = $wh->getString("description");
		
		
		if(empty($orgcode)) $orgcode= Xbo::RandomString(2)."-".Xbo::RandomString(3);
		if(empty($addresscode)) $addresscode=Xbo::RandomString(3)."-".Xbo::GetBatchNum();
		if(empty($country)) $country='INDONESIA';
		if(empty($whdesc)) $whdesc='Gudang Utama';
		if(empty($warehouse)) $warehouse= Xbo::RandomString(3)."-".Xbo::GetBatchNum();
		
		
		Set::throws(["orgcode"=>$orgcode, "description"=>$description, "npwp"=>$npwp, "name1"=>$name1, "title1"=>$title1,
		"addresscode"=>$addresscode, "addrdescription"=>$addrdescription, "stateprovince"=>$stateprovince, "zippostalcode"=>$zippostalcode, "officephone"=>$officephone
		, "city"=>$city, "country"=>$country, "warehouse"=>$warehouse, "whdesc"=>$whdesc
		]);
	}
	public static function ProfileUpdate(){
		$orgcode = $_POST["xhr_profile"];
		$description = $_POST["description"];
		$npwp = $_POST["npwp"];
		$name1 = $_POST["name1"];
		$title1 = $_POST["title1"];
		$addresscode = $_POST["addresscode"];
		$addrdescription = $_POST["addrdescription"];
		$stateprovince = $_POST["stateprovince"];
		$zippostalcode = $_POST["zippostalcode"];
		$officephone = $_POST["officephone"];
		$city = $_POST["city"];
		$country = $_POST["country"];
		
		$warehouse = $_POST["warehouse"];
		$whdesc = $_POST["whdesc"];
		
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		
		
		
		$set = new Set();
		$orgSet = (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("orgcode")->getMboSet();
		$cnt = $orgSet->counts();
		$set2 = new Set();
		
		
		
		$urlattach = ContextPath::getFullHostName();
		$urlattach = $urlattach . "/attachments/";
		
		if($cnt == 0){
			$set2->MboSetRemote("organization")->setValue("orgcode", $orgcode)->setValue("description", $description)->setValue("npwp", $npwp)->setValue("name1", $name1)
			->setValue("title1", $title1)->setValue("urlattach", $urlattach)->setValue("basecurrency", 'IDR')->setValue("costtype", 'AVG')
			->add();
		}else{
			$set2->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->setValue("orgcode", $orgcode)->setValue("description", $description)->setValue("npwp", $npwp)
			->setValue("name1", $name1)->setValue("title1", $title1)->setValue("costtype", 'AVG')
			->change();
		}
		
		
		$set2 = new Set();
		$addrSet = $set2->MboSetRemote("address")->setWhere("addresscode = '$addresscode'")->MboRemote("addresscode")->getMboSet();
		$cnt = $addrSet->counts();
		$set2 = new Set();
		
		if($cnt == 0){
			$set2->MboSetRemote("address")->setValue("addresscode", $addresscode)->setValue("description", $addrdescription)->setValue("stateprovince", $stateprovince)
			->setValue("zippostalcode", $zippostalcode)->setValue("officephone", $officephone)->setValue("city", $city)->setValue("country", $country)
			->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("orgcode", $orgcode)
			->add();
		}else{
			$set2->MboSetRemote("address")->setWhere("orgcode='$orgcode' and addresscode='$addresscode'")->setValue("description", $addrdescription)->setValue("stateprovince", $stateprovince)
			->setValue("zippostalcode", $zippostalcode)->setValue("officephone", $officephone)->setValue("city", $city)->setValue("country", $country)
			->setValue("changeby", $changeby)->setValue("changedate", $changedate)
			->change();
		}
		
		
		$set2 = new Set();
		$siteSet = $set2->MboSetRemote("site")->setWhere("orgcode = '$orgcode'")->MboRemote("sitecode")->getMboSet();
		$cnt = $siteSet->counts();
		$set2 = new Set();
		
		if($cnt == 0){
			$sitecode = $addresscode;
			$set2->MboSetRemote("site")->setValue("sitecode", $sitecode)->setValue("sitecontact", $enterby)->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)->setValue("orgcode", $orgcode)->setValue("active", 'Y')->setValue("description", $addrdescription)
			->add();
		}else{
			$site = $siteSet->getMbo();
			$sitecode=$site->getString("sitecode");
			$set2->MboSetRemote("site")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")
			->setValue("sitecontact", $enterby)->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->change();
		}
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orggldefaultid>=0")->setValue("orgcode", $orgcode)->setValue("sitecode", $addresscode)->change();
		
		$set2 = new Set();
		$siteaddressSet = $set2->MboSetRemote("siteaddress")->setWhere("orgcode = '$orgcode' and siteaddresscode='$addresscode' and sitecode='$sitecode'")
		->MboRemote("sitecode")->MboRemote("orgcode")->MboRemote("siteaddresscode")->getMboSet();
		$cnt = $siteaddressSet->counts();
		$set2 = new Set();
		if($cnt == 0){
			$sitecode = $addresscode;
			$siteaddresscode = $addresscode;
			$set2->MboSetRemote("siteaddress")
			->setValue("siteaddresscode", $siteaddresscode)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("addresscode", $addresscode)
			->setValue("billcontact", $enterby)->setValue("shipcontact", $enterby)->setValue("billtodefault", "Y")->setValue("shiptodefault", "Y")
			->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
			->add();
		}else{
			$siteaddress = $siteaddressSet->getMbo();
			$orgcode=$siteaddress->getString("orgcode");
			$sitecode=$siteaddress->getString("sitecode");
			$siteaddresscode=$siteaddress->getString("siteaddresscode");
			$set2->MboSetRemote("siteaddress")->setWhere("orgcode='$orgcode' and sitecode='$sitecode' and siteaddresscode='$siteaddresscode'")
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->change();
		}
		$set2 = new Set();
		$sitecode=$addresscode;
		$warehouseSet = $set2->MboSetRemote("warehouse")->setWhere("orgcode = '$orgcode' and sitecode='$sitecode' and warehouse='$warehouse'")
		->MboRemote("warehouse")->MboRemote("description")->getMboSet();
		$cnt = $warehouseSet->counts();
		$set2 = new Set();
		if($cnt == 0){
			$set2 = new Set();
			$set2->MboSetRemote("warehouse")
			->setValue("warehouse", $warehouse)->setValue("description", $whdesc)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
			->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
			->add();
		}else{
			$siteaddress = $siteaddressSet->getMbo();
			$orgcode=$siteaddress->getString("orgcode");
			$sitecode=$siteaddress->getString("sitecode");
			$warehouse=$siteaddress->getString("warehouse");
			$set2->MboSetRemote("warehouse")->setWhere("orgcode='".$_POST["xhr_profile"]."' and sitecode='$addresscode' and warehouse='$warehouse'")
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->change();
		}
		
		$oset = new Set();
		$orggldefaultSet = $oset->MboSetRemote("orggldefault")->setWhere("orgcode='".$_POST["xhr_profile"]."' and sitecode='$addresscode'")
		->MboRemote("whglaccount")->MboRemote("whshrinkacct")->MboRemote("whcostacct")->getMboSet();
		$orggldefault = $orggldefaultSet->getMbo();
		
		$whglaccount = $orggldefault->getString("whglaccount");
		$whcostacct = $orggldefault->getString("whcostacct");
		$whshrinkacct = $orggldefault->getString("whshrinkacct");
		
		$wset = new Set();
		$wset->MboSetRemote("warehouse")->setWhere("orgcode='".$_POST["xhr_profile"]."' and sitecode='$addresscode'")
		->setValue("controlacc", $whglaccount)->setValue("invcostadjacc", $whcostacct)
		->setValue("shrinkageacc", $whshrinkacct)->setValue("shippingaddress", $addresscode)
		->setValue("billingaddress", $addresscode)->change();
		
		$em = new Set();
		$em->MboSetRemote("employees")->setWhere("orgcode is null or sitecode is null")->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->change();
		
		$ca = new Set();
		$ca->MboSetRemote("chartofaccount")->setWhere("orgcode is null and sitecode is null")->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->change();
		
		$us = new Set();
		$us->MboSetRemote("mxsusers")->setWhere("userid='$enterby'")->setValue("personid", $enterby)->change();
		$data = array(
			'regcustomer' => $orgcode, 
			'description' => $description, 
			"npwp"=>$npwp, 
			"name1"=>$name1, 
			"title1"=>$title1,
			"addresscode"=>$addresscode,
			"addrdescription"=>$addrdescription,
			"stateprovince"=>$stateprovince,
			"zippostalcode"=>$zippostalcode,
			"officephone"=>$officephone,
			"city"=>$city,
			"country"=>$country
		);
		
		
		/*
		$isconn = Super::callInternet();
		if($isconn == "connected"){
			$response = XboSet::PostUrl(null, $data, "POST");
			$msg = "success";
			$resp = $response;
		}else{
			$msg = "success";
			$resp = "nt";
			XboSet::OrgInfo($data);
		}
		Set::throws(["msg"=>$msg,"resp"=>$resp]);
		*/
		
		$ss = new stdClass;
		
		$ss->msg = "added";
		Set::throws($ss);
		
	}
	
	public static function OrgInfo($data){
		if(count($data)>0){
			$set = new Set();
			if(empty($data["regcustomer"])) $data["regcustomer"] = $_POST["xhr_profile"];
			$orginfoSet = (new Set())->useTransactionManager()->MboSetRemote("orginfo")->setWhere("orgcode='".$data["regcustomer"]."' and addresscode='".$data["addresscode"]."'")
			->MboRemote("orgcode")->getMboSet();
			$orginfoCount = $orginfoSet->counts();
			if($orginfoCount ==0){
				$set = new Set();
				$orginfo = (new Set())->useTransactionManager()->MboSetRemote("orginfo")
				->setValue("orgcode", $data["regcustomer"])
				->setValue("description", $data["description"])
				->setValue("npwp", $data["npwp"])
				->setValue("name1", $data["name1"])
				->setValue("title1", $data["title1"])
				->setValue("addresscode", $data["addresscode"])
				->setValue("addrdescription", $data["addrdescription"])
				->setValue("stateprovince", $data["stateprovince"])
				->setValue("zippostalcode", $data["zippostalcode"])
				->setValue("officephone", $data["officephone"])
				->setValue("city", $data["city"])
				->setValue("country", $data["country"])
				->add();
			}
		}
	}
	public static function RenderAva(){
		$isconn = Super::callInternet();
		if($isconn == "connected"){
			$set = new Set();
			$orginfoSet = (new Set())->useTransactionManager()->MboSetRemote("orginfo")
			->MboRemote("orgcode")->MboRemote("description")->MboRemote("npwp")->MboRemote("name1")
			->MboRemote("title1")->MboRemote("addresscode")->MboRemote("addrdescription")
			->MboRemote("stateprovince")->MboRemote("zippostalcode")->MboRemote("officephone")
			->MboRemote("city")->MboRemote("country")->getMboSet();
			
			$ogcnt = $orginfoSet->counts();
			if($ogcnt>0){
				for($i=0; $i<$ogcnt; $i++){
					$og = $orginfoSet->getMbo();
					$orgcode = $og->getString("orgcode");
					$description = $og->getString("description");
					$npwp = $og->getString("npwp");
					$name1 = $og->getString("name1");
					$title1 = $og->getString("title1");
					$addresscode = $og->getString("addresscode");
					$addrdescription = $og->getString("addrdescription");
					$stateprovince = $og->getString("stateprovince");
					$zippostalcode = $og->getString("zippostalcode");
					$officephone = $og->getString("officephone");
					$city = $og->getString("city");
					$country = $og->getString("country");
					
					$data = array(
						'regcustomer' => $orgcode, 
						'description' => $description, 
						"npwp"=>$npwp, 
						"name1"=>$name1, 
						"title1"=>$title1,
						"addresscode"=>$addresscode,
						"addrdescription"=>$addrdescription,
						"stateprovince"=>$stateprovince,
						"zippostalcode"=>$zippostalcode,
						"officephone"=>$officephone,
						"city"=>$city,
						"country"=>$country
					);
					
					$response = XboSet::PostUrl(null, $data, "POST");
					$msg = "success";
					$resp = $response;
					
					$st = new Set();
					$st->MboSetRemote("orginfo")->setWhere("orgcode='$orgcode' and addresscode='$addresscode'")->remove();
				}
			}
		}else{
			$msg = "success";
			$resp = "nt";
		}
	}
	public static function PostUrl($url, $data, $type)
	{
		if($url == null) $url = "https://www.javarave.com/jvr/ui/common.php";
		else $url = $url;
		if($type == "POST"){
			$url = $url;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			curl_close ($ch);
			return $response;
		}else{
			if($type == "GET"){
				$postString = http_build_query($data, '', '&');
				$url = $url;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST, 0);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close ($ch);
				return $response;
			}
		}
	}
	public static function validateChildren()
	{
		Super::validateChildren();
	}
	public static function showTableRowDetail()
	{
		Super::showTableRowDetail();
	}
	public static function getOwner()

	{
		$clauseOwner = Super::getOwner();
		return $clauseOwner;
	}
	
	public static function signout(){
		Super::Signout();
	}
	
	public static function setObject($obj, $cl)
    {
        self::$objectname = $obj;
		self::$clause = $cl;
		
    }
	
	public static function getXboSet($objectname, $clause = null)
	{
		
		
		self::setObject($objectname, $clause);
		Super::getXboSet($objectname, $clause);
		
	}
	public static function getObject(){
		$ss = new stdClass;
		$ss->objectname = self::$objectname;
		$ss->clause = self::$clause;
		
	}
	public static function showDetail()
	{
		$ss = self::getObject();
		
		Super::showDetail();
	}
	
	public static function displayData($join = "", $distinct = "")
	{
		Super::displayData($join, $distinct);
	}
}
class Xbo{
	
	private static $startdateg;
	private static $enddateg;
	public $totalcost = 0;
	public $owncost = 0;
	public static $drilldownWos = [];
	public static $matproditem = [];
	public static $servproditem = [];
	public static $labproditem = [];
	public static $toolproditem = [];
	
	public static function updateLastRevisedRecord(){
		$payload = Super::getParam("updateLastRevisedRecord");
		$tableid = $payload["tableid"];
		$table = $payload["table"];

		$currentDate = date("Y-m-d h:i:s");
		
		(new Set())->useTransactionManager()->MboSetRemote($table)->where($table."id = ?", $tableid)
		->setValue("status", "REVISED")->setValue("statusdate", $currentDate)->change();
		Super::sendJsonResponse("success", "Record has been revised");
	}
	

	public static function deleteYoutubeVideo()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("deleteYoutubeVideo");
			$mxsvideosid = $payload['mxsvideosid'] ?? null;

			if (!$mxsvideosid) {
				throw new Exception("Video ID is required for deletion.");
			}

			// Eksekusi delete dengan library Set (Aman & Transaksional)
			(new Set())->useTransactionManager()
				->MboSetRemote("mxsvideos")
				->where("mxsvideosid = ?", $mxsvideosid)
				->delete();

			$tm->commit();
			Super::sendJsonResponse("success", "Video has been deleted.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}

	public static function updateYoutubeVideo()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("updateYoutubeVideo");
			$mxsvideosid = $payload['mxsvideosid'] ?? null;
			$title       = $payload['title'] ?? null;
			$description = $payload['description'] ?? null;
			$youtubeurl  = $payload['youtubeurl'] ?? null;

			if (!$mxsvideosid || !$title || !$youtubeurl) {
				throw new Exception("Mandatory fields (ID, Title, URL) are missing.");
			}

			// 1. Re-extract YouTube Video ID (in case URL changed)
			$youtubevideoid = null;
			if (preg_match('/v=([a-zA-Z0-9_-]+)/', $youtubeurl, $match)) {
				$youtubevideoid = $match[1];
			} elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $youtubeurl, $match)) {
				$youtubevideoid = $match[1];
			} elseif (preg_match('/shorts\/([a-zA-Z0-9_-]+)/', $youtubeurl, $match)) {
				$youtubevideoid = $match[1];
			}

			if ($youtubevideoid && strlen($youtubevideoid) > 11) {
				$youtubevideoid = substr($youtubevideoid, 0, 11);
			}

			$thumbnailurl = $youtubevideoid 
				? "https://img.youtube.com/vi/{$youtubevideoid}/hqdefault.jpg" 
				: null;

			// 2. Update DB menggunakan change()
			(new Set())->useTransactionManager()
				->MboSetRemote("mxsvideos")
				->where("mxsvideosid = ?", $mxsvideosid)
				->setValue("title", $title)
				->setValue("description", $description)
				->setValue("youtubeurl", $youtubeurl)
				->setValue("youtubevideoid", $youtubevideoid)
				->setValue("thumbnailurl", $thumbnailurl)
				->setValue("changeby", Xbo::getLoginUser())
				->setValue("changedate", Xbo::setEnterDate())
				->change();

			$tm->commit();
			Super::sendJsonResponse("success", "Video updated successfully.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function getSurveilanceVideos()
	{
		try {
			$payload = Super::getParam("getSurveilanceVideos");
			$ownertable = $payload['ownertable'] ?? null;
			$ownernum   = $payload['ownernum'] ?? null;

			if (!$ownertable || !$ownernum) {
				throw new InvalidArgumentException("Context table and number are required.");
			}

			// Ambil data dari tabel mxsvideos
			$videos = (new Set())->useTransactionManager()
				->MboSetRemote("mxsvideos")
				->where("ownertable = ? AND ownernum = ?", $ownertable, $ownernum)
				->order("mxsvideosid DESC") // Video terbaru di atas
				->getMboSet()
				->fetchData();

			Super::sendJsonResponse("success", count($videos) . " videos loaded.", $videos);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}

	public static function saveYoutubeVideos()
	{
		$payload = Super::getParam("saveYoutubeVideos");

		if (!$payload) {
			Super::sendJsonResponse("error", "Payload is empty.");
			return;
		}

		$ownertable  = $payload['ownertable'] ?? null;
		$ownernum    = $payload['ownernum'] ?? null;
		$youtubeurl  = $payload['youtubeurl'] ?? null;
		// Tangkap data baru dari payload
		$title       = $payload['title'] ?? null;
		$description = $payload['description'] ?? null;

		$enterby   = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();

		// VALIDATION
		if (!$ownertable || !$ownernum || !$youtubeurl || !$title) {
			Super::sendJsonResponse("error", "Mandatory fields (Title, URL) are missing.");
			return;
		}

		// Basic youtube validation
		if (
			strpos($youtubeurl, "youtube.com") === false &&
			strpos($youtubeurl, "youtu.be") === false
		) {
			Super::sendJsonResponse("error", "Invalid YouTube URL.");
			return;
		}

		// =========================
		// EXTRACT YOUTUBE VIDEO ID
		// =========================
		$youtubevideoid = null;

		if (preg_match('/v=([a-zA-Z0-9_-]+)/', $youtubeurl, $match)) {
			// Format: youtube.com/watch?v=ID
			$youtubevideoid = $match[1];
		} elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $youtubeurl, $match)) {
			// Format: youtu.be/ID
			$youtubevideoid = $match[1];
		} elseif (preg_match('/shorts\/([a-zA-Z0-9_-]+)/', $youtubeurl, $match)) {
			// Format Baru: youtube.com/shorts/ID
			$youtubevideoid = $match[1];
		}

		// Pastikan ID yang diambil tepat 11 karakter (standar YouTube)
		if ($youtubevideoid && strlen($youtubevideoid) > 11) {
			$youtubevideoid = substr($youtubevideoid, 0, 11);
		}


		// URL Thumbnail YouTube
		$thumbnailurl = $youtubevideoid
			? "https://img.youtube.com/vi/{$youtubevideoid}/hqdefault.jpg"
			: null;

		// =========================
		// INSERT DB 
		// =========================
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			(new Set())->useTransactionManager()
				->MboSetRemote("mxsvideos")
				->setValue("ownertable", $ownertable)
				->setValue("ownernum", $ownernum)
				->setValue("youtubeurl", $youtubeurl)
				->setValue("youtubevideoid", $youtubevideoid)
				->setValue("thumbnailurl", $thumbnailurl)
				// Masukkan data baru ke query insert
				->setValue("title", $title)              
				->setValue("description", $description)  
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->add(); 

			$tm->commit();

			// =========================
			// RESPONSE 
			// =========================
			Super::sendJsonResponse("success", "Video has been saved successfully.",[
				"youtubevideoid" => $youtubevideoid,
				"thumbnailurl" => $thumbnailurl,
				"title" => $title // Kembalikan title ke frontend jika diperlukan
			]);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to save video: " . $e->getMessage());
		}
	}
	public static function markNotifRead() {
		try {
			$notifId = Super::getParam("markNotifRead");
			if (empty($notifId)) throw new Exception("ID Notifikasi tidak ditemukan.");

			(new Set())->useTransactionManager()
				->MboSetRemote("mxsnotification")
				->where("mxsnotificationid = ?", $notifId)
				->setValue("isread", "Y")
				->change();

			Super::sendJsonResponse("success", "Notif marked as read");
		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}

	public static function markAllNotifRead() {
		try {
			$username = Super::getParam("markAllNotifRead");
			if (empty($username)) throw new Exception("Username tidak ditemukan.");

			(new Set())->useTransactionManager()
				->MboSetRemote("mxsnotification")
				->where("username = ? AND (isread = 'N' OR isread IS NULL)", $username)
				->setValue("isread", "Y")
				->change();

			Super::sendJsonResponse("success", "All notifs marked as read");
		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}

	public static function getUnreadNotificationCount() 
	{
		try {
		
			$username = Xbo::getLoginUser();
			
			if (empty($username)) {
				throw new Exception("User not logged in.");
			}

			$unreadCount = (new Set())->useTransactionManager()
				->MboSetRemote("mxsnotification")
				->where("username = ?", $username)
				->where("(isread = 'N' OR isread IS NULL)") // Cek N atau Null
				->countRecords();

			Super::sendJsonResponse("success", "Notification count retrieved", ["count" => $unreadCount]);

		} catch (Exception $e) {
			error_log("Error getting notification count: " . $e->getMessage());
			// Tetap kembalikan 0 jika terjadi error agar UI tidak error
			Super::sendJsonResponse("error", $e->getMessage(),["count" => 0]);
		}
	}

	public static function getAutoCompletion() 
	{
		try {
			$param = Super::getParam("getAutoCompletion");
			
			$table = $param['table'] ?? '';
			$attribute = $param['attribute'] ?? '';
			$columns = $param['columns'] ?? '';
			$keyword = $param['keyword'] ?? '';

			// 1. Validasi Panjang Keyword (Penting untuk Performa)
			if (strlen($keyword) < 2) {
				Super::sendJsonResponse("success", "Please enter at least 2 characters.", []);
				return;
			}

			// 2. BLACKLIST: Tables forbidden
			$forbiddenTables = ['mxsusers', 'organization', 'mxsapp', 'userlogin', 'system_config'];
			if (in_array(strtolower($table), $forbiddenTables)) {
				throw new Exception("Access to this object is denied.");
			}

			// 3. Sanitasi
			$safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
			$safeAttribute = preg_replace('/[^a-zA-Z0-9_]/', '', $attribute);
			
			$colsArray = explode(',', $columns);
			$safeCols = [];
			foreach ($colsArray as $col) {
				$cleaned = preg_replace('/[^a-zA-Z0-9_]/', '', trim($col));
				if (!empty($cleaned)) $safeCols[] = $cleaned;
			}

			if (empty($safeTable) || empty($safeAttribute) || empty($safeCols)) {
				throw new InvalidArgumentException("Invalid parameters provided.");
			}

			// 4. Cross-Platform (Penanganan LIMIT)
			$colList = implode(', ', $safeCols);
			$dbType = (new dbo())->dbtype();
			
			// SQL Server menggunakan TOP
			if ($dbType === 'sqlserver') {
				$sql = "SELECT TOP 20 $colList FROM $safeTable WHERE $safeAttribute LIKE ?";
			} else {
				// MySQL dan Oracle 12c+
				$sql = "SELECT $colList FROM $safeTable WHERE $safeAttribute LIKE ?";
			}

			// Tambahkan LIMIT untuk MySQL & Oracle (Oracle 12c+)
			if ($dbType === 'mysql') {
				$sql .= " LIMIT 50";
			} elseif ($dbType === 'oracle') {
				$sql .= " FETCH FIRST 50 ROWS ONLY";
			}
			
			// 5. Eksekusi Aman
			$results = Set::fetchAll($sql, ["%" . $keyword . "%"]);
			
			Super::sendJsonResponse("success", "Data found", $results);

		} catch (Exception $e) {
			error_log("Autocomplete Error: " . $e->getMessage());
			Super::sendJsonResponse("error", "Internal system error. Please contact support.");
		}
	}
	public static function getPersonid($userid){
		$set = new Set();
		$personid = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->where("userid='$userid'")->MboRemote("personid")->getMboSet()->getMbo()->getString("personid");
		return $personid;
	}
	public static function getUsername($personid){
		$set = new Set();
		$username = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->where("personid='$personid'")->MboRemote("username")->getMboSet()->getMbo()->getString("username");
		return $username;
		
	}
	public static function getTeamLeader($teamnum){
		$set = new Set();
		$empTeamSet = $set->useTransactionManager()->MboSetRemote("empteam")->setWhere("teamnum='$teamnum' and leader = 'Y'")->MboRemote("personid")->getMboSet();
		$empTeam = $empTeamSet->getMbo();
		$personid = $empTeam->getString("personid");
		
		$set = new Set();
		$employeesSet = $set->useTransactionManager()->MboSetRemote("employees")->setWhere("personid = '$personid'")->MboRemote("fullname")->getMboSet();
		$emp = $employeesSet->getMbo();
		$fullname = $emp->getString("fullname");
		
		$response = new stdClass();
		$response->fullname = $fullname;
		$response->personid = $personid;
		return $response;
	}
	public static function getOrgcode($enterby){
		$set = new Set();
		$mxsusersSet = $set->useTransactionManager()->MboSetRemote("mxsusers")->setWhere("userid='$enterby'")->MboRemote("personid")->getMboSet();
		$mxsusers = $mxsusersSet->getMbo();
		$personid = $mxsusers->getString("personid");
		
		$set = new Set();
		$empSet = $set->useTransactionManager()->MboSetRemote("employees")->setWhere("personid='$personid'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$emp = $empSet->getMbo();
		$orbj = new stdClass();
		$orbj->orgcode = $emp->getString("orgcode");
		$orbj->sitecode = $emp->getString("sitecode");
		return $orbj;
	}
	public static function getautonum()

	{
		$appname = Super::getParam("getautonum");
		$autonumber = new Super();
		$autonum = $autonumber->getAutoNumber($appname);
		$autonum = $autonum->autonumber;
		Super::sendJsonResponse("success", $autonum);
	}

	public static function getNextAutoNumberApplication($appname, $isUpdate)
	{
		
		if($isUpdate){
			$autonumber = new Super();
			$autonum = $autonumber->getAutoNumber($appname, $isUpdate);
			$x = $autonum->autonumber;
			return $x;
		}else{
			$autonumber = new Super();
			$autonum = $autonumber->getAutoNumber($appname);
			$x = $autonum->autonumber;
			return $x;
		}
		
	}

	public static function GetUnitCommission($itemnum, $warehouse, $pricecode=null, $conditiontype=null){
		$set = new Set();
		
		if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype= '')";
		else $condval = " and conditiontype = '$conditiontype'";
		
		if(empty($pricecode)) $priceval = " and defaultprice='Y'";
		else $priceval = " and pricecode = '$pricecode'";
		
		$where = "itemnum='$itemnum' and warehouse='$warehouse' $priceval $condval";
		
		$invpriceSet = (new Set())->useTransactionManager()->MboSetRemote("invprice")->setWhere($where)->MboRemote("commissionprice")->getMboSet();
		$invprice = $invpriceSet->getMbo();
		
		$commission = $invprice->getString("commissionprice");
		if(empty($commission)) $commission = 0;
		return $commission;
	}
	public static function gendescription($itemnum = null, $classes = null)
	{
		if ($itemnum == null) $itemnum = $_GET['gendescription'];
		else {
			$itemnum = $itemnum;
		}
		$set = new Set();
		if ($classes == null) {
			$tableclass = "itemclassification";
			$whereClause = "itemnum='$itemnum'";
			$table = "item";
		}
		else {
			if ($classes == "assets") {
				$tableclass = "assetclassification";
				$whereClause = "assetnum='$itemnum'";
				$table = "assets";
			}
		}
		$classtableSet = $set->MboSetRemote($tableclass)->setWhere($whereClause)->MboRemote("attributename")->MboRemote("value")->getMboSet();
		$numrows = $classtableSet->counts();
		$specTextArr = array();
		if ($numrows > 0) {
			for ($i = 0; $i < $numrows; $i++) {
				$classtable = $classtableSet->getMbo();
				$attributename = $classtable->getString("attributename");
				$value = $classtable->getString("value");
				if (!empty($value)) {
					$attrValue = $attributename . ": " . $value;
					array_push($specTextArr, $attrValue);
				}
			}
			$specText = implode("; ", $specTextArr);
			if ($specText != "") {
				$set->MboSetRemote($table)->setWhere($whereClause)->setValue("spectext", $specText)->change();
				echo $specText;
			}
			else {
				echo "valueAttributeIsNull";
			}
		}
	}
	public static function getTime()

	{
		$time = date("h:i:s");
		return $time;
	}
	
	public static function RandomString($length = 10, $chars = null) {
		if ($chars ==null) $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		else $characters = $chars;
		
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	

	public static function GetBatchNum($param = null)
	{
		// tahun 2 digit
		$year = date('y');

		// julian day (001-366)
		$day = str_pad(date('z') + 1, 3, '0', STR_PAD_LEFT);

		// random 3 digit base36
		$rand = strtoupper(str_pad(base_convert(random_int(0, 46655), 10, 36), 3, '0', STR_PAD_LEFT));

		return $year . $day . $rand;
	}
		
	
	public static function recl()
	{
		$xsa = explode("|", $_GET['recl']);
		$set = new Set();
		$sm = explode("`", $xsa[1]);
		$sonum = $sm[1];
		$solineSet = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='" . $sonum . "' and solinenum='" . $xsa[2] . "'")->MboRemote("orderunit")->getMboSet()->getMbo();
		$orderunit = $solineSet->getString("orderunit");
		Set::throws(["orderunit" => $orderunit]);
	}
	public static function getlanglat()

	{
		Set::throws(["msg" => "ok", "longitude" => "-7.55611", "latitude" => "110.83167"]);
	}
	
	public static function getConversionFactorBackend($itemnum, $orderunit, $issueunit)
	{
		
		if (empty($orderunit) || empty($issueunit)) {
			return null;
		}

		$set = new Set();
		$conversion = null;

		
		if (!empty($itemnum)) {
		
			$mbo = (new Set())->useTransactionManager()->MboSetRemote("conversion")
					  ->where("itemnum = ? AND frommeasureunit = ? AND tomeasureunit = ?", $itemnum, $orderunit, $issueunit)
					  ->getMboSet()
					  ->getMbo();
			
			
			if ($mbo) {
				$conversion = $mbo->getString("conversion");
			}
		}

		
		if ($conversion === null) {
			$mbo = (new Set())->useTransactionManager()->MboSetRemote("conversion")
					  ->where("(itemnum IS NULL OR itemnum = ?) AND frommeasureunit = ? AND tomeasureunit = ?", '', $orderunit, $issueunit)
					  ->getMboSet()
					  ->getMbo();

			if ($mbo) {
				$conversion = $mbo->getString("conversion");
			}
		}
		
		if($conversion === null){
			$conversion = 1;
		}
		
		return $conversion;
	}
		
	
	public static function getConversionFactor()
	{
		
		$orderunit = $_POST["getConversionFactor"] ?? '';
		$issueunit = $_POST["issueunit"] ?? '';
		$itemnum = $_POST["itemnum"] ?? '';
		$conversionValue = self::getConversionFactorBackend($itemnum, $orderunit, $issueunit);

		Set::throws(["conversion" => $conversionValue]);
	}
	
	
	public static function setSR($seedroot)

	{
		$arrRev = Super::setSR($seedroot);
		return $arrRev;
	}
	public static function checksum()

	{
		Super::checksum();
	}
	public static function setActid($seedroot)

	{
		$srStr = Super::setActid($seedroot);
		return $srStr;
	}
	public static function seedroot()

	{
		Super::seedroot();
	}
	public static function getPaper(){
		
		$ss = new Set();
		$appname = $_POST['getpapersetting'];
		$type = $_POST["type"];
		if($type == "barcode") $tableset = "papersettingbc";
		if($type == "qrcode") $tableset = "papersetting";
		$ppSet = $ss->MboSetRemote($tableset)->setWhere("isdefault='Y' ")
		->MboRemote("width")->MboRemote("height")
		->MboRemote("margintop")->MboRemote("marginleft")
		->MboRemote("secmargintop")->MboRemote("secmarginright")
		->MboRemote("padding")->MboRemote("type")
		->MboRemote("colsqty")->MboRemote("rowsqty")
		->getMboSet();
		$pp =$ppSet->getMbo();
		
		$width = $pp->getString("width");
		$height = $pp->getString("height");
		$margintop = $pp->getString("margintop");
		$marginleft = $pp->getString("marginleft");
		$secmarginright = $pp->getString("secmarginright");
		$secmargintop = $pp->getString("secmargintop");
		$padding = $pp->getString("padding");
		$type = $pp->getString("type");
		$colsqty = $pp->getString("colsqty");
		$rowsqty = $pp->getString("rowsqty");
		
		Set::throws([
		"type"=>$type, "width"=>$width, "height"=>$height, "margintop"=>$margintop, "padding"=>$padding,
		"marginleft"=>$marginleft, "secmargintop"=>$secmargintop, "secmarginright"=>$secmarginright,
		"rowsqty"=>$rowsqty, "colsqty"=>$colsqty
		]);
	}
	public static function getBaseCurrency($return = false){

		$enterby = Xbo::getLoginUser();
		$orgInfo = Xbo::getOrgcode($enterby);
		$orgcode = $orgInfo->orgcode;
		$sitecode = $orgInfo->sitecode;
		$basecurrency = (new Set())->useTransactionManager()->MboSetRemote("organization")->where("orgcode=?", $orgcode)->getMboValue("basecurrency");

		$shiptoaddress =  (new Set())->useTransactionManager()->MboSetRemote("siteaddress")
		->where("orgcode=? AND sitecode =? AND shiptodefault='Y'", $orgcode, $sitecode)
		->getMboValue("addresscode");


		$billtoaddress =  (new Set())->useTransactionManager()->MboSetRemote("siteaddress")
		->where("orgcode=? AND sitecode =? AND billtodefault='Y'", $orgcode, $sitecode)
		->getMboValue("addresscode");


		$data [] = [
			"basecurrency" => $basecurrency,
			"shiptoaddress" => $shiptoaddress,
			"billtoaddress" => $billtoaddress,
			"exchangerate" => 1,
		];
		
		if($return == true){
			return $data;
		}
		Super::sendJsonResponse("success", "Data Retrieved", $data);
	}
	public static function getExchangeRate($obj = null)
	{
		// 1. Ambil parameter

		if ($obj === null) {
			$orgcode         = $_POST["orgcode"] ?? null;
			$currencyFrom    = $_POST["getexchangerate"] ?? null;
			$transactionDate = $_POST["transactiondate"] ?? null;
		} else {
			$orgcode         = $obj->orgcode ?? null;
			$currencyFrom    = $obj->currencycodefrom ?? null;
			$transactionDate = $obj->transactiondate ?? null;
		}

		if (empty($orgcode) || empty($currencyFrom)) {
			Super::sendJsonResponse(
				"error",
				"Missing mandatory parameters (orgcode, currencycodefrom)"
			);
		}

		// 2. Fallback Transaction Date
		if (empty($transactionDate)) {
			$transactionDate = date('Y-m-d');
		}

		// set ke tengah hari biar aman
		$transactionDateTime = date(
			'Y-m-d 12:00:00',
			strtotime($transactionDate)
		);

		// 3. Ambil base currency (1 org = 1 base)
		$baseCurrency = (new Set())
			->useTransactionManager()
			->MboSetRemote("organization")
			->where("orgcode = ?", $orgcode)
			->getMboValue("basecurrency");

		if (empty($baseCurrency)) {
			Super::sendJsonResponse(
				"error",
				"Organization base currency not found"
			);
		}

		// 4. Currency sama → rate = 1
		if ($currencyFrom === $baseCurrency) {
			Super::sendJsonResponse(
				"success",
				"Exchange Rate will use: 1",
				["exchangerate" => 1]
			);
		}

		// 5. Cari exchange rate by periode
		$rateSet = (new Set())
			->useTransactionManager()
			->MboSetRemote("exchangerate")
			->where(
				"currencycodefrom = ?
				AND currencycodeto   = ?
				AND activedate      <= ?
				AND expireddate     >= ?",
				$currencyFrom,
				$baseCurrency,
				$transactionDateTime,
				$transactionDateTime
			)->getMboSet();
		

		if ($rateSet->isEmpty()) {
			Super::sendJsonResponse(
				"error",
				"No exchange rate found for {$currencyFrom} → {$baseCurrency} on {$transactionDate}"
			);
		}
		
		// 6. Validasi overlapping period
		if ($rateSet->counts() > 1) {
			Super::sendJsonResponse(
				"error",
				"Overlapping exchange rate period detected for {$currencyFrom} → {$baseCurrency}"
			);
		}

		// 7. Ambil & freeze rate
		$exchangeRate = round(
			(float) $rateSet->getMboValue("exchangerate"),
			2
		);

		if ($exchangeRate <= 0) {
			Super::sendJsonResponse(
				"error",
				"Invalid exchange rate value"
			);
		}

		// =====================================
		// 8. Return 
		// =====================================
		Super::sendJsonResponse(
			"success",
			"Exchange Rate will use: {$exchangeRate}",
			["exchangerate" => $exchangeRate]
		);
	}



	
	public static function getLocationGL($location){
		$set = new Set();
		$locSet = (new Set())->useTransactionManager()->MboSetRemote("locations")->setWhere("locationsid='$location'")->MboRemote("locnum")->MboRemote("glaccount")->getMboSet()->getMbo();
		$glaccount = $locSet->getString("glaccount");
		return $glaccount;
	}
	public static function getlocgl()

	{
		$p = explode("|", $_GET["getlocgl"]);
		$rowid = $p[0];
		$origin = $p[1];
		$set = new Set();
		if ($origin == "asset") {
			$assetSet = (new Set())->useTransactionManager()->MboSetRemote("assets")->setWhere("assetsid='$rowid'")->MboRemote("locnum")->MboRemote("glaccount")->getMboSet()->getMbo();
			$locnum = $assetSet->getString("locnum");
			$glaccount = $assetSet->getString("glaccount");
			Set::throws(["location" => $locnum, "glaccount" => $glaccount]);
		}
		if ($origin == "location") {
			$assetSet = (new Set())->useTransactionManager()->MboSetRemote("locations")->setWhere("locationsid='$rowid'")->MboRemote("locnum")->MboRemote("glaccount")->getMboSet()->getMbo();
			$glaccount = $assetSet->getString("glaccount");
			Set::throws(["glaccount" => $glaccount]);
		}
		
		if ($origin == "customer") {
			$assetSet = (new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customerid='$rowid'")->MboRemote("customercode")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("rbniacct")->getMboSet()->getMbo();
			$glaccount = $assetSet->getString("rbniacct");
			$orgcode = $assetSet->getString("orgcode");
			$sitecode = $assetSet->getString("sitecode");
			$customercode = $assetSet->getString("customercode");
			$glaccount = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode);
			Set::throws(["glaccount" => $glaccount]);
		}
		
	}
	
	public static function getCustomerName($customercode)

	{
		$set = new Set();
		return (new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$customercode'")->MboRemote("name")->getMboSet()->getMbo()->getString("name");
	}
	public static function AvaImage(){
		$mxid = $_POST["avaimage"];
		
		$set = new Set();
		$personid = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setWhere("userid='$mxid'")->MboRemote("personid")->getMboSet()->getMbo()->getString("personid");
		$set = new Set();
		$empSet = (new Set())->useTransactionManager()->MboSetRemote("employees")->setWhere("personid='$personid'")->MboRemote("employeesid")->MboRemote("fullname")->MboRemote("title")->getMboSet();
		$emp = $empSet->getMbo();
		$ownerid = $emp->getString("employeesid");
		$fullname = $emp->getString("fullname");
		$title = $emp->getString("title");
		
		$set = new Set();
		$fileinfoSet = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownertable='employees' and ownerid='$ownerid' and filedefault='Y'")->MboRemote("filename")->getMboSet();
		
		$fileinfo =$fileinfoSet->getMbo();
		$filethumb = $fileinfo->getString("filename");
		
		if(empty($filethumb)) $filethumb = "defaultprofile.jpg";
		$filename = "thumb_".$filethumb;
		$fsname = ContextPath::getFullHostName();
		$thumbPath = "/attachments/thumbs/";
		$thumbPath2 = $fsname.$thumbPath;
		$dir = $thumbPath2. $filename;
		
		Set::throws(["url"=>$dir, "filename"=>$filename, "thumbPath"=>$thumbPath, "fullname"=>$fullname, "title"=>$title]);
		XboSet::RenderAva();
	}
	public static function RackWhereClause($racknum){
		if(empty($racknum)) $rackval = " and (racknum is null or racknum='')"; 
		else $rackval = " and racknum='$racknum'";
		
		return $rackval;
	}
	public static function BatchWhereClause($batchnum){
		if(empty($batchnum)) $batchval = " and (batchnum is null or batchnum='')"; 
		else $batchval = " and batchnum='$batchnum'";
		return $batchval;
	}
	public static function CondTypeWhereClause($condtype){
		if (empty($condtype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$condtype'";
			
		return $condval;
		
	}
	public static function isCondType()

	{
		$x = $_GET["isCondType"];
		$set = new Set();
		echo (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->setWhere("itemnum = '$x'")->MboRemote("count(conditiontype) as isCondType")->getMboSet()->getMbo()->getString("isCondType");
	}
	public static function getwogl($wo = "")
	{
		if ($wo == "") $wonum = $_GET['getwogl'];
		else $wonum = $wo;
		$set = new Set();
		$gldebitacct = (new Set())->useTransactionManager()->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("glaccount")->getMboSet()->getMbo()->getString("glaccount");
		if ($wo == "") {
			Set::throws(["gldebitacct" => $gldebitacct]);
		}
		else {
			return $gldebitacct;
		}
	}
	public static function getwhgl($wh="", $noprint=null){
		
		if(isset($_GET["orgcode"]) && isset($_GET["sitecode"]) ) {
			$orgcode = $_GET["orgcode"];
			$sitecode = $_GET["sitecode"];
			$wc = " and orgcode = '$orgcode' and sitecode='$sitecode' ";
		}else{
			$wc = null;
		}
		
		if(isset($_GET["getwhgl"])) $wh = $_GET["getwhgl"];
		else  $wh = $wh;

		$set = new Set();
		$whSet = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->setWhere("warehouse='$wh' $wc")->MboRemote("controlacc")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$wh = $whSet->getMbo();
		
		$controlacc = $wh->getString("controlacc");
		$orgcode = $wh->getString("orgcode");
		$sitecode = $wh->getString("sitecode");
		
		if($controlacc == null){
			$set = new Set();
			$orgSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("whglaccount")->getMboSet();
			$org= $orgSet->getMbo();
			$controlacc = $org->getString("whglaccount");
		}
		if($noprint == null) {
			Set::throws(["gldebitacct"=>$controlacc]);
			return $controlacc;
		}
		return $controlacc;
		
	}
	public static function sumWOPlanCost($wonum){
		$set = new Set();
		$woPlanSet = (new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->setWhere("wonum='$wonum'")->MboRemote("sum(linecost) as sumplancost")->getMboSet();
		$woPlan = $woPlanSet->getMbo();
		$sumplancost = $woPlan->getString("sumplancost") * -1;
		return $sumplancost;
	}
	public static function sumWOActualCost($wonum){
		$set = new Set();
		$matuseSet = (new Set())->useTransactionManager()->MboSetRemote("matusetrans")->setWhere("wonum='$wonum'")->MboRemote("sum(linecost) as sumactualcost")->getMboSet();
		$matuse = $matuseSet->getMbo();
		$sumactualcost = $matuse->getString("sumactualcost") * -1;
		
		
		$set = new Set();
		$servrecSet = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->setWhere("wonum='$wonum'")->MboRemote("sum(linecost) as sumactualcost2")->getMboSet();
		$servrec = $servrecSet->getMbo();
		$sumactualcost2 = floatval($servrec->getString("sumactualcost2"));
		$sumactualcost = $sumactualcost + $sumactualcost2;
		
		return $sumactualcost;
	}
	public static function getAVGCost($itemnum, $warehouse, $conditiontype=null){
		
		if ($conditiontype == null) {
			$condval = " and (conditiontype is null or conditiontype='') ";
		}
		else {
			$condval = " and conditiontype = '$conditiontype' ";
		}
		
		$set = new Set();
		$invcostSet = (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum = '$itemnum' and warehouse='$warehouse' $condval")->MboRemote("avgcost")->getMboSet();
		
		$invcost = $invcostSet->getMbo();
		$cost = floatval($invcost->getString("avgcost"));
		return $cost;
	}
	public static function getUnitCost($orgcode, $itemnum, $warehouse, $conditiontype=null){
		if ($conditiontype == null) {
			$condval = " and (conditiontype is null or conditiontype='') ";
		}
		else {
			$condval = " and conditiontype = '$conditiontype' ";
		}
		$set = new Set();
		$costtype = Xbo::getCostType($orgcode);
		if($costtype == "AVG") $costtype = "avgcost";
		else $costtype = "avgcost";
		$invcostSet = (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum = '$itemnum' and warehouse='$warehouse' $condval")->MboRemote($costtype)->getMboSet();
		
		
		$invcost = $invcostSet->getMbo();
		$cost = $invcost->getString($costtype);
		return $cost;
	}
	
	public function getUnitPriceDiscount($orgcode, $itemnum, $warehouse, $conditiontype=null, $pricecode = null){
		$this->primarydiscount = 0;
		$this->secondarydiscount =  0;
		$this->discountprice = 0;
		$this->unitprice = 0;
		$this->unitpricediscount = 0;
		$this->useupdateprice = "N";
		$this->msg = "ok";
		
		if ($conditiontype == null) {
			$condval = " and (conditiontype is null or conditiontype='') ";
		}
		else {
			$condval = " and conditiontype = '$conditiontype' ";
		}
		
		if($pricecode == null){
			$priceval = " and defaultprice='Y' ";
		}else{
			$priceval = " and pricecode='$pricecode' ";
		}
		$set = new Set();
		$invpriceSet = (new Set())->useTransactionManager()->MboSetRemote("invprice")->setWhere("itemnum = '$itemnum' and warehouse='$warehouse' $priceval $condval")->MboRemote("unitprice")
		->MboRemote("primarydiscount")->MboRemote("secondarydiscount")
		->MboRemote("useupdateprice")->getMboSet();
		
		$cnt = $invpriceSet->counts();
		
		if($cnt == 0){
			$this->item = $itemnum;
			$this->warehouse = $warehouse;
			$this->conditiontype = $conditiontype;
			$this->msg = "cantfounddefaultprice";
			return $this;
		}else{
			$invprice = $invpriceSet->getMbo();
			$unitprice = $invprice->getString("unitprice");
			$useupdateprice = $invprice->getString("useupdateprice");
			
			if($useupdateprice == "Y") {
				$useupdateprice = "Y";
			}else{
				$useupdateprice = "N";
			}
			if($unitprice == null) $unitprice = 0;
			
			$primarydiscount = $invprice->getString("primarydiscount");
			if($primarydiscount == null) $primarydiscount = 0;
			$secondarydiscount = $invprice->getString("secondarydiscount");
			if($secondarydiscount == null) $secondarydiscount = 0;
			
			if(!empty($primarydiscount) || $primarydiscount !=0 ){
				$discountprice = ($primarydiscount*$unitprice)/100;
				$unitpricediscount = $unitprice-$discountprice;
				$discount1 = $discountprice;
				if(!empty($secondarydiscount) || $secondarydiscount !=0 ){
					$discountprice = $secondarydiscount*$unitpricediscount/100;
					$unitpricediscount = $unitpricediscount-$discountprice;
					$discount2 = $discountprice;
					$discountprice = $discount1+$discount2;
					
				}else{
					$discountprice = 0;
				}
			}else{
				if($primarydiscount == 0 && $secondarydiscount == 0){
					$unitpricediscount = $unitprice;
				}
				$discountprice = 0;
			}	

			$this->primarydiscount = $primarydiscount;
			$this->secondarydiscount = $secondarydiscount;
			$this->discountprice = $discountprice;
			$this->unitprice = $unitprice;
			$this->unitpricediscount = $unitpricediscount;
			$this->useupdateprice = $useupdateprice;
			$this->msg = "ok";
			return $this;
		}
	}
	public static function AdjustWTMaterialCost($itemnum, $warehouse, $newAvgCost)

	{
		(new Set())->useTransactionManager()->MboSetRemote("wtplanmaterial")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->setValue("unitcost", $newAvgCost)->change();
		$sql = "update wtplanmaterial set linecost=quantity*unitcost where itemnum='$itemnum' and warehouse='$warehouse'";
		DB::exec($sql);
	}
	public static function getLineNumber($table, $tableline, $where)

	{
		$set = new Set(); 
		
		$maxlinenum = $set->useTransactionManager()->MboSetRemote($table)->setWhere($where)->MboRemote("max($tableline)+10 as linenum")->getMboSet()->getMbo()->getString("linenum");
		if (empty($maxlinenum)) {
			$maxlinenum = 10;
		}
		return $maxlinenum;
	}
	
	public static function getLineNum(string $table, string $tableline, array $whereConditions)
	{
		$tm = TransactionManager::getInstance();
		$set = (new Set())->useTransactionManager();

		$safeTable = Super::sanitizeIdentifier($table);
		$safeTableLine = Super::sanitizeIdentifier($tableline);

		$conditions = [];
		$params = [];
		foreach ($whereConditions as $key => $value) {
			$conditions[] = Super::sanitizeIdentifier($key) . " = ?";
			$params[] = $value;
		}
		$whereClause = implode(' AND ', $conditions);

		$result = (new Set())->useTransactionManager()->MboSetRemote($safeTable)
					   ->where($whereClause, ...$params) 
					   ->MboRemote("MAX($safeTableLine) AS max_linenum")
					   ->getMboSet()
					   ->fetch(); 

		$maxlinenum = $result['max_linenum'] ?? 0;
		
		return (int)$maxlinenum + 10;
	}
	
	
	public function updateallwocost($wonum=null){
		

		$WOSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->MboRemote("actualcost, assetnum")->getMboSet();
		$WOs = clone $WOSet;
		
		$WO = $WOSet->getMbo();
		$sumactcost = $WO->getString("actualcost");
		
		if($sumactcost == null) $sumactcost=0;
	
		$assetsubSet2 = (new Set())->useTransactionManager()->MboSetRemote("wosubs")->where("wonum=?", $wonum)->MboRemote("subwonum")->getMboSet();
		$b=0;
		while($res = $assetsubSet2->fetch()){
			$subwonum =$res["subwonum"];
			$get = new Xbo();
			$a = $get->updateallwocost($subwonum)->totalcost;
			$b = $a+$b;	
		}

		$WOs = $WOs->MboRemote("wonum, assetnum")->where("wonum=?", $wonum)->getMboSet()->fetchData();
		
		self::$drilldownWos = array_merge(self::$drilldownWos, $WOs);

		$this->totalcost = $sumactcost+$b;
		$this->owncost = $sumactcost;
		return $this;
	}
	
	public function updateallassetscost($assetnum=null, $worktype=null, $startdate=null, $enddate=null, $subassetarr=[]){
		
		self::$startdateg = $startdate;
		self::$enddateg = $enddate;		
		
		if($assetnum == null && $startdate == null && $enddate == null){
			$AssetSet = (new Set())->useTransactionManager()->MboSetRemote("assets")->MboRemote("assetnum")->getMboSet();
			
			
			if(!$AssetSet->isEmpty()){
				while($res = $AssetSet->fetch()){
					$assetnum = $res["assetnum"];
					$WOSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->setWhere("assetnum='$assetnum'")->MboRemote("sum(actualcost) as sumactcost")->getMboSet();
					$WO = $WOSet->getMbo();
					$sumactcost = $WO->getString("sumactcost"); 
					(new Set())->useTransactionManager()->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->setValue("ytdcost", $sumactcost)->change();
				}
			}
			
		}else{			
			
			$mx = new Mxs();
			$startdate = Xbo::getDate($startdate);
			$enddate = Xbo::getDate($enddate);
			$startdate = $mx->dateFormat($startdate);
			$enddate = $mx->dateFormat($enddate);			
			$dateclause = " and reportdate >= $startdate and reportdate <= $enddate ";			
			$WOs = null;
			$WOSet = null;
			if($worktype == null){
				$WOSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->setWhere("assetnum='$assetnum' $dateclause");
				
			}else{
				$WOSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->setWhere("assetnum='$assetnum' $dateclause and worktype='$worktype'");
			}

			$WOs = clone $WOSet;
			$WOSet->MboRemote("sum(actualcost) as sumactcost")
			->getMboSet();

			
			$WO = $WOSet->getMbo();
			$sumactcost = floatval($WO->getString("sumactcost"));
			
			//Update Asset YTD
			(new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum=?", $assetnum)->setValue("ytdcost", $sumactcost)->change();
			


			$setr = new Set();
			$assetsubSet2 = $setr->MboSetRemote("assetsubs")->where("assetnum=?", $assetnum)->MboRemote("subassetnum")->getMboSet();
			$b=0;
			while($res = $assetsubSet2->fetch()){
				$assetsubname = $res["subassetnum"];
				$get = new Xbo();
				$a = $get->updateallassetscost($assetsubname, $worktype, self::$startdateg, self::$enddateg)->totalcost;
				$b = $a+$b;

			}

			/* Get All Production Resources */
			$WOs = $WOs->MboRemote("wonum, assetnum")->where("assetnum=?", $assetnum)->getMboSet()->fetchData();
			self::$drilldownWos = array_merge(self::$drilldownWos, $WOs);
			
			

			$this->totalcost = $sumactcost+$b;
			$this->owncost = $sumactcost;
			return $this;
			
		}
	}
	
	 public static function calculateForecastWOCost(string $wonum): ?stdClass
    {
        // 1. SQL Query
       /* $sql = "
            SELECT 
               
                (SELECT COALESCE(SUM(wtpm.linecost), 0) 
                 FROM pmjpline pjp
                 JOIN wtplanmaterial wtpm ON pjp.wtnum = wtpm.wtnum
                 WHERE pjp.pmwolineid = pwl.pmwolineid) AS planmaterialcost,

                (SELECT COALESCE(SUM(wtps.linecost), 0) 
                 FROM pmjpline pjp
                 JOIN wtplanservice wtps ON pjp.wtnum = wtps.wtnum
                 WHERE pjp.pmwolineid = pwl.pmwolineid) AS planservicecost,
                 
                (SELECT COALESCE(SUM(wtpt.linecost), 0) 
                 FROM pmjpline pjp
                 JOIN woplantool wtpt ON pjp.wtnum = wtpt.wtnum -- Asumsi tabel wtplanservice
                 WHERE pjp.pmwolineid = pwl.pmwolineid) AS plantoolcost,

                (SELECT COALESCE(SUM(wtpl.linecost), 0) 
                 FROM pmjpline pjp
                 JOIN woplanlabor wtpl ON pjp.wtnum = wtpl.wtnum -- Asumsi tabel wtplanservice
                 WHERE pjp.pmwolineid = pwl.pmwolineid) AS planlaborcost

            FROM pmwoline pwl
            WHERE pwl.pmwolineid = ?
        ";
		*/
        $sql = "
            SELECT 
               
                (SELECT COALESCE(SUM(wtpm.linecost), 0) 
                 FROM pmjpline pjp
                 JOIN wtplanmaterial wtpm ON pjp.wtnum = wtpm.wtnum
                 WHERE pjp.pmwolineid = pwl.pmwolineid) AS planmaterialcost
            FROM pmwoline pwl
            WHERE pwl.wonum = ?
        ";
        $set = new Set();
        $results = $set->fetchAll($sql, [$wonum]); 
		
        if (empty($results)) {
            return null; // Forecast WO tidak ditemukan.
        }
        $costs = (object)$results[0];
		//$costs->plannedcost = $costs->planmaterialcost + $costs->planservicecost + $costs->plantoolcost + $costs->planlaborcost;
        $costs->plannedcost =$costs->planmaterialcost;
		
       // $costs->variancecost = $costs->plannedcost - $costs->actualcost;

        return $costs;
    }

	public static function calculateWOCost(string $wonum): ?stdClass
	{
		$sql = "
			SELECT
				wo.assetnum,
				wo.location,
				(SELECT COALESCE(ABS(SUM(linecost)), 0) FROM matusetrans WHERE wonum = wo.wonum) AS actualmaterialcost,
				(SELECT COALESCE(ABS(SUM(linecost)), 0) FROM servrectrans WHERE wonum = wo.wonum) AS actualservicecost,
				(SELECT COALESCE(ABS(SUM(linecost)), 0) FROM labortrans WHERE wonum = wo.wonum) AS actuallaborcost,
				(SELECT COALESCE(ABS(SUM(linecost)), 0) FROM tooltrans WHERE wonum = wo.wonum) AS actualtoolcost,
				(SELECT COALESCE(SUM(linecost), 0) FROM woplanmaterial WHERE wonum = wo.wonum) AS planmaterialcost,
				(SELECT COALESCE(SUM(linecost), 0) FROM woplanservice WHERE wonum = wo.wonum) AS planservicecost2,
				(SELECT COALESCE(SUM(linecost), 0) FROM woplantool WHERE wonum = wo.wonum) AS plantoolcost,
				(SELECT COALESCE(SUM(linecost), 0) FROM woplanlabor WHERE wonum = wo.wonum) AS planlaborcost,
				(SELECT COALESCE(AVG(progress), 0) FROM wologs WHERE wonum = wo.wonum) AS progress
			FROM workorder wo
			WHERE wo.wonum = ? 
		";
		
		$set = new Set();
		$results = $set->fetchAll($sql, [$wonum]);

		if (empty($results)) {
			return null; // Work Order tidak ditemukan
		}

		$costs = (object)$results[0];
		// Lakukan kalkulasi akhir di PHP (tidak berubah)
		$costs->actualcost = $costs->actualmaterialcost + $costs->actualservicecost + $costs->actuallaborcost + $costs->actualtoolcost;
		$costs->plannedcost = $costs->planmaterialcost + $costs->planservicecost2 + $costs->plantoolcost + $costs->planlaborcost;
		$costs->variancecost = $costs->plannedcost - $costs->actualcost;
		$costs->progress = round($costs->progress, 2);

		(new Set())->useTransactionManager()->MboSetRemote("workorder")->setWhere("wonum = '$wonum'")
			->setValue("actualcost", $costs->actualcost)
			->setValue("plannedcost", $costs->plannedcost)
			->setValue("variancecost", $costs->variancecost)
			->setValue("progress", $costs->progress)
			->change();

		return $costs;
	}
	
	
	
	public static function getFinancialPeriod($date = null)

	{
		$enterdate = Xbo::setEnterDate();
		if($date!=null) $enterdate = $date;
		$finparts = explode("-", $enterdate);
		$financialperiod = $finparts[0] . $finparts[1];
		return $financialperiod;
	}
	
	public static function varallowance()
	{
		$financialperiod = Xbo::getFinancialPeriod();
		$whereclause = " and financialperiod='$financialperiod' and status='approved'";
		return $whereclause;
	}
	public static function setValue($colname, $val)

	{
		global $el;
		$concatenate = $colname . "`" . $val;
		$el[] = $concatenate;
		return $el;
	}
	public static function ChangeChildren($modch)

	{
		
		$ownerTable = $modch->ownerTable;
		$childTable = $modch->childTable;
		$childid = $modch->childid;
		if ($childTable == "invprice") {
			$set = new Set();
			$invpriceSet = (new Set())->useTransactionManager()->MboSetRemote("invprice")->setRelationship("pricegroup", "pricegroup.pricecode = invprice.pricecode")->setWhere("invpriceid=$childid")->MboRemote("invprice.pricecode")->MboRemote("unitprice")->MboRemote("pricegroup.pricerate")->MboRemote("itemnum")->MboRemote("conditiontype")->MboRemote("warehouse")->getMboSet();
			$invprice = $invpriceSet->getMbo();
			$itemnum = $invprice->getString("itemnum");
			$warehouse = $invprice->getString("warehouse");
			$pricecode = $invprice->getString("pricecode");
			$pricerate = $invprice->getString("pricerate");
			$unitprice = $invprice->getString("unitprice");
			$conditiontype = $invprice->getString("conditiontype");
			if (empty($conditiontype)) {
				$condval = " and (conditiontype is null or conditiontype='') ";
			}
			else {
				$condval = " and conditiontype='$conditiontype'";
			}
			if ($pricerate == 100) {
				$set2 = new Set();
				$set3 = new Set();
				$set4 = new Set();
				$invpriceSet2 = $set2->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("unitprice")->MboRemote("pricecode")->MboRemote("invpriceid")->getMboSet();
				if ($invpriceSet2->counts() > 1) {
					for ($i = 0; $i < $invpriceSet2->counts(); $i++) {
						$invprice2 = $invpriceSet2->getMbo();
						$aunitprice = $invprice2->getString("unitprice");
						$apricecode = $invprice2->getString("pricecode");
						$ainvpriceid = $invprice2->getString("invpriceid");
						$apricegroup = $set3->MboSetRemote("pricegroup")->setWhere("pricecode='$apricecode'")->MboRemote("pricerate")->MboRemote("islesser")->getMboSet()->getMbo();
						$apricerate = $apricegroup->getString("pricerate");
						$aislesser = $apricegroup->getString("islesser");
						if ($apricerate < 100) {
							if ($aislesser == "N") {
								$percentage = 100 + $apricerate;
							}
							else {
								$percentage = 100 - $apricerate;
							}
							$anewprice = $unitprice * ($percentage / 100);
							$set4->MboSetRemote("invprice")->setWhere("invpriceid='$ainvpriceid'")->setValue("unitprice", $anewprice)->change();
						}
					}
				}
			}
		}
	}
	
	public static function getLinkTableWhere($get)

	{ 
		$linktable = Super::InitLinkTable($get);
		return $linktable;
	}
	public static function getLinkTableName($get)

	{
		
		$mxtable = $get["tablename"];
		
		return $mxtable;
	}
	public static function getDate($str, $isDate = null)
	{
		
		if($isDate == null){
			$date = Xbo::getString($str);
			
		}else{
			if($isDate == "date"){
				$date = $str;
			}
		}

		if ($date == "") {
			$date = Xbo::getString($str);
		}

		return $date;
	}
	public static function editButton($iconPath, $imgid)

	{
		$editbutton = "<div class=\"editbutton\"><img class=\"maxesfupd\" src=\"" . $iconPath . "/update_icon.png\" data-filename=\"" . $imgid . "\" > </div>";
		return $editbutton;
	}
	public static function setActivity($remarks)

	{
		$userid = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$set = new Set();
		$fullname = (new Set())->useTransactionManager()->MboSetRemote("employees")->setRelationship("mxsusers", "employees.personid = mxsusers.personid ")->setWhere("userid='$userid'")->MboRemote("employees.fullname")->getMboSet()->getMbo()->getString("fullname");
		$remarks = "<b>" . $fullname . "</b> " . $remarks;
		(new Set())->useTransactionManager()->MboSetRemote("mxsactivity")->setValue("userid", $userid)->setValue("remarks", $remarks)->setValue("enterby", $userid)->setValue("enterdate", $enterdate)->add();
	}
	public static function getDefaultInsertSite()

	{
		$value = $_GET["getDefaultInsertSite"];
		$set = new Set();
		$mxsusers = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setRelationship("employees", "mxsusers.personid = employees.personid")->setWhere("mxsusers.userid='$value'")->MboRemote("employees.orgcode")->MboRemote("employees.sitecode")->getMboSet()->getMbo();
		Set::throws(["orgcode" => $mxsusers->getString("orgcode") , "sitecode" => $mxsusers->getString("sitecode") ]);
	}
	public static function getkpiot()

	{
		$parts = explode("^", $_GET['getkpiot']);
		$set = new Set();
		$table = $parts[0];
		$id = $parts[1];
		$tableid = $table . "id";
		$mxskpiset = (new Set())->useTransactionManager()->MboSetRemote("mxskpi")->setWhere("ownertable='$table'")->MboRemote("selectstatement")->MboRemote("whereclause")->MboRemote("groupby")->MboRemote("querytype")->MboRemote("queryname")->getMboSet();
		$countkpi = $mxskpiset->counts();
		$ckpi = array();
		for ($h = 0; $h < $countkpi; $h++) {
			$mxskpi = $mxskpiset->getMbo();
			$queryname = $mxskpi->getString("queryname");
			$querytype = $mxskpi->getString("querytype");
			$selectstatement = $mxskpi->getString("selectstatement");
			$groupby = $mxskpi->getString("groupby");
			$whereclause = $mxskpi->getString("whereclause");
			$whereclause = Super::ReplaceColumns($whereclause, $table, $tableid, $id, "y");
			$conn = DB::getMXConnection();
			$dbo = new dbo();
			$dbtype = $dbo->dbtype();
			$sql = $selectstatement . " " . $whereclause . " " . $groupby;
			$count = null;
			if ($dbtype == "oracle") {
				$mboset = oci_parse($conn, $sql);
				$mboset2 = oci_parse($conn, $sql);
				oci_execute($mboset);
				$count = oci_fetch_all($stid, $res);
			}
			if ($dbtype == "mysql") {
				$mboset = $conn->query($sql);
				$mboset2 = $conn->query($sql);
				$conn->close();
				$count = mysqli_num_rows($mboset);
			}
			$datacol = $mboset2->fetch_assoc();
			if($datacol!=null){
				$colnamer = array();
				if ($datacol >= 0) {
					foreach($datacol as $col => $val) {
						array_push($colnamer, $col);
					}
					$valuer = array();
					$labelr = array();
					$xtitler = array();
					$ccolnamer = count($colnamer);
					for ($i = 0; $i < $count; $i++) {
						if ($dbtype == "mysql") $data = $mboset->fetch_assoc();
						if ($dbtype == "oracle") $data = oci_fetch_assoc($mboset);
						for ($j = 0; $j < $ccolnamer; $j++) {
							$value = $data[$colnamer[$j]];
							if ($j == 0) array_push($valuer, $value);
							if ($j == 1) array_push($labelr, $value);
							if ($j == 2) array_push($xtitler, $value);
						}
					}
					$label = implode(",", $labelr);
					$value = implode(",", $valuer);
					$xtitle = implode(",", $xtitler);
					$arr = json_encode(["xtitle" => $xtitle, "label" => $label, "value" => $value, "querytype" => $querytype, "queryname" => $queryname]);
					array_push($ckpi, $arr);
					$ccolnamer = array();
					$valuer = array();
					$labelr = array();
					$xtitler = array();
				}
			}
		}
		Set::throws($ckpi);
	}
	
	public static function getLastChanged(string $childTable, string $primaryKeyName, $primaryKeyValue, bool $isNewRecord = false)
	{
	
		try {
			
			$schemaSet = new Set();
			$columnResult = $schemaSet->getColumnResult($childTable);
			
			$availableColumns = [];
			while ($row = $columnResult->fetch()) {
				if (isset($row['columnname'])) {
					$availableColumns[] = strtolower($row['columnname']);
				}
			}

			if (empty($availableColumns)) {
				error_log("getLastChanged: Tidak dapat menemukan kolom untuk tabel '$childTable'.");
				return;
			}
			
			$currentUser = Xbo::getLoginUser();
			$currentDate = date("Y-m-d H:i:s"); 

			$updateSet = new Set();
			$updateSet->MboSetRemote($childTable);
			
			$hasUpdate = false;

			if ($isNewRecord) {
				if (in_array('enterby', $availableColumns)) {
					$updateSet->setValue("enterby", $currentUser);
					$hasUpdate = true;
				}
				if (in_array('enterdate', $availableColumns)) {
					$updateSet->setValue("enterdate", $currentDate);
					$hasUpdate = true;
				}
			}
			if (in_array('changeby', $availableColumns)) {
				$updateSet->setValue("changeby", $currentUser);
				$hasUpdate = true;
			}
			if (in_array('changedate', $availableColumns)) {
				$updateSet->setValue("changedate", $currentDate);
				$hasUpdate = true;
			}
			
			if ($hasUpdate) {
		
				$updateSet->where(Super::sanitizeIdentifier($primaryKeyName) . " = ?", $primaryKeyValue)
						  ->change();
			}

		} catch (Exception $e) {
			error_log("Error di getLastChanged: " . $e->getMessage());
		}
	}
		public static function getEntryDate($childTable, $tableid, $maxtableid)

	{
		$currentUser = Xbo::getLoginUser();
		$currentDate = date("Y-m-d h:i:s");
		(new Set())->useTransactionManager()->MboSetRemote($childTable)->setWhere("$tableid = '$maxtableid'")->setValue("enterby", $currentUser)->setValue("changeby", $currentUser)->setValue("enterdate", $currentDate)->setValue("changedate", $currentDate)->change();
	}
	public static function setTableID($getorpost)

	{
		$tableid = $getorpost;
		if (strpos($tableid, "|") !== false) {
			$tableid = explode("|", $tableid);
			$tableid = $tableid[1];
		}
		return $tableid;
	}
	public static function getTableID()

	{
		$data = Super::getParam("tablename");
		return $data;
	}
	
	public static function getCustomerAcct($custcode, $orgcode=null, $sitecode=null)

	{
		$mboset = null;
		if($orgcode == null ){
			$mboset = (new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$custcode'");
		}else{
			$mboset = (new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$custcode' and orgcode='$orgcode' and sitecode='$sitecode'");
		}
		$rbniacct = $mboset->getMboValue("rbniacct");
		return $rbniacct;
	}
	public static function getVendorAcct($vendorcode, $orgcode, $sitecode)
	{
		$set = new Set();
		if($orgcode == null ){
			$mboset = (new Set())->useTransactionManager()->MboSetRemote("vendor")->setWhere("vendorcode='$vendorcode'");
		}else{
			$mboset =(new Set())->useTransactionManager()->MboSetRemote("vendor")->setWhere("vendorcode='$vendorcode' and orgcode='$orgcode' and sitecode='$sitecode'");
		}
		$mboset = $mboset->MboRemote("rbniacct")->getMboSet();
		
		$mbo = $mboset->getMbo();
		$rbniacct = $mbo->getString("rbniacct");
		if(empty($rbniacct)){
		
			$orgglSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("vrbniglaccount")->getMboSet();
			$orggl = $orgglSet->getMbo();
			$rbniacct = $orggl->getString("vrbniglaccount");
		}

		return $rbniacct;
	}
	
	public static function getVendorAPAcct($vendorcode, $orgcode, $sitecode)
	{
		$set = new Set();
		if($orgcode == null ){
			$mboset = (new Set())->useTransactionManager()->MboSetRemote("vendor")->setWhere("vendorcode='$vendorcode'");
		}else{
			$mboset = (new Set())->useTransactionManager()->MboSetRemote("vendor")->setWhere("vendorcode='$vendorcode' and orgcode='$orgcode' and sitecode='$sitecode'");
		}
		$mboset = $mboset->MboRemote("apacct")->getMboSet();
		
		$mbo = $mboset->getMbo();
		$apacct = $mbo->getString("apacct");
		if(empty($apacct)){
			$set = new Set();
			$orgglSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("vapglaccount")->getMboSet();
			$orggl = $orgglSet->getMbo();
			$apacct = $orggl->getString("vapglaccount");
		}
		return $apacct;
	}
	
	
	public static function getWOAccount($wonum, $orgcode, $sitecode)

	{
		
		$orgc = (empty($orgcode)) ? "orgcode is null" : "orgcode='$orgcode'";
		$sitec = (empty($sitecode)) ? "sitecode is null" : "sitecode='$sitecode'";
		$whereclause = "wonum='$wonum' and $orgc and $sitec";
		return (new Set())->useTransactionManager()->MboSetRemote("workorder")->setWhere($whereclause)->MboRemote("glaccount")->getMboSet()->getMbo()->getString("glaccount");
	}
	
	
	public static function getRevenueAcct($orgcode, $sitecode)

	{
		return $set = (new Set())->useTransactionManager()->MboSetRemote("site")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("revenueacct")->getMboSet()->getMbo()->getString("revenueacct");
	}
	public static function getCashOnBankAcct($orgcode, $sitecode)

	{
		$set = new Set();
		$siteSet = (new Set())->useTransactionManager()->MboSetRemote("site")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("cashonbankacct")->getMboSet();
		$site = $siteSet->getMbo();
		
		$cashonbank = $site->getString("cashonbankacct");
		
		if($cashonbank == null){
			$set = new Set();
			$orgSet = (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("cashonbankacct")->getMboSet();
			$org = $orgSet->getMbo();
			$cashonbank = $org->getString("cashonbankacct");
		}
		return $cashonbank;
	}
	
	public static function getCashOnHandAcct($orgcode, $sitecode)

	{
		$set = new Set();
		
		$siteSet = (new Set())->useTransactionManager()->MboSetRemote("site")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("cashonhandacct")->getMboSet();
		$site = $siteSet->getMbo();
		$cashonhandacct = $site->getString("cashonhandacct");
		
		if($cashonhandacct == null){
			$set = new Set();
			$orgSet = (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("cashonhandacct")->getMboSet();
			$org = $orgSet->getMbo();
			$cashonhandacct = $org->getString("cashonhandacct");
		}
		return $cashonhandacct;
	}
	public static function getAssetGLAccount($assetnum, $orgcode, $sitecode)

	{
		
		$assetSet = (new Set())->useTransactionManager()->MboSetRemote("assets")
		->setWhere("assetnum='$assetnum' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("assets.glaccount")->getMboSet();
		
		$asset = $assetSet->getMbo();
		$glaccount = $asset->getString("glaccount");
		return $glaccount;
		
	}
	public static function getGLAccount($itemnum, $warehouse=null, $orgcode = null, $sitecode = null)

	{
		$set = new Set();
		$itemgroupSet = (new Set())->useTransactionManager()->MboSetRemote("itemgroup")->setRelationship("item", "itemgroup.groupname = item.itemgroup and itemgroup.orgcode = item.orgcode and itemgroup.sitecode = item.sitecode")
		->setWhere("itemnum='$itemnum'")->MboRemote("itemgroup.glaccount")->getMboSet();
		
		$itemgroup = $itemgroupSet->getMbo();
		$glaccount = $itemgroup->getString("glaccount");
		if($glaccount == null){
			
			$glaccount = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
			
		}
		
		return $glaccount;
		
	}
	public static function getControlAcct($warehouse, $orgcode, $sitecode)

	{
		
		$orgc = (empty($orgcode)) ? "orgcode is null" : "orgcode='$orgcode'";
		$sitec = (empty($sitecode)) ? "sitecode is null" : "sitecode='$sitecode'";
		
		$whereclause = "$orgc and $sitec and warehouse='$warehouse'";
		
		$whSet = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->setWhere($whereclause)->MboRemote("controlacc")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		
		if($whSet->isEmpty()){
			$set = new Set();
			$whereclause = "warehouse='$warehouse'";
			$whSet = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->setWhere($whereclause)->MboRemote("controlacc")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		}
		$wh = $whSet->getMbo();
		$controlacc = $wh->getString("controlacc");
		
		if($orgcode==null) $orgcode = $wh->getString("orgcode");
		if($sitecode==null) $sitecode = $wh->getString("sitecode");
		
		
		if($controlacc == null){
			$set = new Set();
			$orgSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode'")->MboRemote("whglaccount")->getMboSet();
			$org = $orgSet->getMbo();
			$controlacc = $org->getString("whglaccount");
		}

		return $controlacc;
	}
	public static function getCustomerInvCostAdjAcct($customercode, $orgcode, $sitecode){
		$set = new Set();
		$custSet = (new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$customercode'")->MboRemote("costadjacct")->getMboSet();
		$cust = $custSet->getMbo();
		$costadjacc = $cust->getString("costadjacct");
		
		if($costadjacc == null){
			$set = new Set();
			$orggldefaultSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("custpriceacct")->getMboSet();
			$orggldefault = $orggldefaultSet->getMbo();
			$costadjacc = $orggldefault->getString("custpriceacct");
		}
		
		
		return $costadjacc;
	}
	public static function getCustomerShrinkageAcct($customercode, $orgcode, $sitecode){
		$set = new Set();
		$custSet = (new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$customercode'")->MboRemote("shrinkageacct")->getMboSet();
		$cust = $custSet->getMbo();
		$shrinkageacct = $cust->getString("shrinkageacct");
		
		if($shrinkageacct == null){
			$set = new Set();
			$orggldefaultSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("custshrinkacct")->getMboSet();
			$orggldefault = $orggldefaultSet->getMbo();
			
			$shrinkageacct = $orggldefault->getString("custshrinkacct");
		}
		
		
		return $shrinkageacct;
	}
	
	public static function getCustomerRBNIAcct($customercode, $orgcode, $sitecode){
		$set = new Set();
		$custSet = (new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$customercode'")->MboRemote("rbniacct")->getMboSet();
		$cust = $custSet->getMbo();
		$rbniacct = $cust->getString("rbniacct");
		
		if($rbniacct == null){
			$set = new Set();
			$orggldefaultSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("crbniglaccount")->getMboSet();
			$orggldefault = $orggldefaultSet->getMbo();
			
			$rbniacct = $orggldefault->getString("crbniglaccount");
		}
		return $rbniacct;
	}
	
	public static function getCustomerAPAcct($customercode, $orgcode, $sitecode){
		$set = new Set();
		$custSet = (new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$customercode'")->MboRemote("apaccount")->getMboSet();
		$cust = $custSet->getMbo();
		$apaccount = $cust->getString("apaccount");
		
		if($apaccount == null || $customercode == null){
			$set = new Set();
			$orggldefaultSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("capglacount")->getMboSet();
			$orggldefault = $orggldefaultSet->getMbo();
			$apaccount = $orggldefault->getString("capglacount");
		}
		return $apaccount;
	}
	
	
	
	
	public static function getShrinkageAcct($warehouse, $orgcode, $sitecode)

	{
		$set = new Set();
		
		$whSet = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->setWhere("orgcode='$orgcode' and sitecode='$sitecode' and warehouse='$warehouse'")->MboRemote("shrinkageacc")->getMboSet();
		$wh = $whSet->getMbo();
		$shrinkageacc = $wh->getString("shrinkageacc");
		
		if($shrinkageacc == null){
			$set = new Set();
			$orggldefaultSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("whshrinkacct")->getMboSet();
			$orggldefault = $orggldefaultSet->getMbo();
			$shrinkageacc = $orggldefault->getString("whshrinkacct");
		}
		
		return $shrinkageacc;
	}
	
	public static function getInvCostAdjAcct($warehouse, $orgcode, $sitecode)

	{
		$set = new Set();
		$whSet = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->setWhere("orgcode='$orgcode' and sitecode='$sitecode' and warehouse='$warehouse'")->MboRemote("invcostadjacc")->getMboSet();
		$wh = $whSet->getMbo();
		$invcostadjacc = $wh->getString("invcostadjacc");
		
		if($invcostadjacc == null){
			$set = new Set();
			$orggldefaultSet = (new Set())->useTransactionManager()->MboSetRemote("orggldefault")->setWhere("orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("whcostacct")->getMboSet();
			$orggldefault = $orggldefaultSet->getMbo();
			$invcostadjacc = $orggldefault->getString("whcostacct");
		}
		
		return $invcostadjacc;
	}

	public static function getConditionType($itemnum) {
		return (new Set())
			->useTransactionManager()
			->MboSetRemote("itemcondition")
			->where("itemnum=? and conditionrate=100")
			->MboRemote("conditiontype")
			->getMboSet()
			->getMbo()->getString("conditiontype") ?? null;
	}
	public static function getConditionRate($conditiontype, $itemcond = null, $itemnum = null, $warehouse = null)

	{
		$set = new Set();
		if ($itemcond == null) {
			$conditionrate = (new Set())->useTransactionManager()->MboSetRemote("conditiontype")->setWhere("conditiontype = '$conditiontype'")->MboRemote("conditionrate")->getMboSet()->getMbo()->getString("conditionrate");
		}
		else {
			if ($itemnum != null) {
				$conditionrate = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->setWhere("conditiontype = '$conditiontype'")->MboRemote("conditionrate")->getMboSet()->getMbo()->getString("conditionrate");
			}
			if ($itemnum == null) {
				$conditionrate = (new Set())->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and conditiontype='$conditiontype' and warehouse='$warehouse'")->MboRemote("conditionrate")->getMboSet()->getMbo()->getString("conditionrate");
			}
		}
		return $conditionrate;
	}
	public static function getIssueUnit($itemnum)

	{
		$set = new Set();
		return (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
	}
	public static function getCostType($orgcode)

	{
		$set = new Set();
		$costSet= (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("costtype")->getMboSet();
		$cost = $costSet->getMbo();
		$costtype = $cost->getString("costtype");
		if($costtype != "avgcost"){
			$costype = "avgcost";
		}
		return $costtype;
	}
	public static function InventoryCostType($itemnum, $warehouse, $orgcode){
		$set = new Set();
		$invSet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and orgcode='$orgcode'")->MboRemote("issuetype")->getMboSet();
		$inv = $invSet->getMbo();
		$issuetype = $inv->getString("issuetype");
	
		if(empty($issuetype)){
			$issuetype = Xbo::getCostType2($orgcode);
		}
		
		return $issuetype;
	}
	public static function getCostType2($orgcode)
	{
		
		$set = new Set();
		$costSet= (new Set())->useTransactionManager()->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("costtype")->getMboSet();
		$cost = $costSet->getMbo();
		$costtype = $cost->getString("costtype");
		return $costtype;
	}
	public static function getData($sql)

	{
		return Set::fetchAll($sql);
	}
	public static function getLoginUser() {
		try {
			$userData = Auth::getCurrentUserData(); 
			
			if ($userData && isset($userData->sub)) {
				return $userData->sub;
			}
			
			return null;
			
		} catch (Exception $e) {
			error_log("Error getting login user: " . $e->getMessage());
			return null;
		}
	}
	public static function getLoginUser_v1()

	{
		$mxsession = null;
		if (!isset($_SESSION)) {
			session_start();
		}
		if(isset($_SESSION['username'])){
			$mxsession = $_SESSION['username'];
		}
		
		return 'kulpang';
	}
	public static function getString($str)

	{
		
		if (isset($_POST["package"])) {
			
			$package = $_POST["mbovalues"];
			if (empty($package)) {
				return null;
			}
			else {
				return $package[$str];
			}
		}else{
			return null;
		}
		
		
	}
	public static function select($col, $val, $datatype = null)

	{
		Super::select($col, $val, $datatype);
	}
	public static function setEnterDate()
	{
		
		
		$strotimenow = strtotime("now");
		
		$enterdate = date("Y-m-d h:i:s",$strotimenow);
		
		return $enterdate;
	}
	
	public static function resetOwnerPassword(){
		$newpassword = $_POST['newpassword'];
		$confirmpassword = $_POST['confirmpassword'];
		$email =  $_POST['resetOwnerPassword'];
		
		$md5password = md5($newpassword);
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("owneremail")->setWhere("email='$email'")
		->setValue("password", $md5password)
		->setValue("origpassword", $newpassword)
		->change();
		
		$set = new Set();
		(new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setWhere("username = 'maxesadmin' and ownership = '$email'")
		->setValue("password", $md5password)
		->change();
		
		echo "ok";
	}
	public static function logininfo()
	{
		$payload = Super::getParam("logininfo");
		$username = $payload["user"];
		$md5password = md5($payload["pass"]);
		$set = new Set();
		$mxsusersSet = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")
						  ->where("username = ? AND password = ?", $username, $md5password)
						  ->getMboSet();
		
		
		$mxusers = $mxsusersSet->getMbo();
		$found = $mxsusersSet->counts();

		if ($found > 0) { 
			$y = $mxusers->getString("username");
			$x = $mxusers->getString("password");
			$z = $mxusers->getString("status");
			$lcs = ContextPath::getLcs();
			Super::invested($y, $x, $z, $lcs);
		}
		else {
			Super::sendJsonResponse("error", "Cannot Login");
		}
	
	}
	public static function getMaxTableID($XboSetName)
	{
		$maxtableid = 0;
		$tableid = strtolower($XboSetName . "id");
		$XboSetName = strtolower($XboSetName);
		$set = new Set();
		$XboSetSet = $set->MboSetRemote($XboSetName)->MboRemote("max($tableid)+1 as tableid")->getMboSet();
		
		
		if ($XboSetSet->counts() > 0) {
			$xboset = $XboSetSet->getMbo();
			$maxtableid = $xboset->getString("tableid");
			if (empty($maxtableid)) {
				$maxtableid = 1;
			}
		}
		else {
			$maxtableid = 1;
		}
		
		return $maxtableid;
	}
}


class UI{
	public static function crtbody()

	{
		Super::BodyChart();
	}
	public static function chartport()

	{
		Super::ChartPorts();
	}
	
	public static function isConnected()
	{
		$hello = Super::callInternet();
		return $hello;
	}
	public static function getactions(){
		Super::getApplicationActions();
	}
	public static function sapxhr()
	{
		Super::Auth();
	}
}
?>