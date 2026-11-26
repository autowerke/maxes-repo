<?php
$composer_maxesloader = __DIR__ . '/composers/maxesloader/autoload.php';
$composer_maxesworkflow = __DIR__ . '/composers/maxesworkflow/maxeswf.php';
require_once $composer_maxesworkflow;
require_once $composer_maxesloader;
use PhpOffice\PhpSpreadsheet\IOFactory;

include ("TransactionManager.php");
include ("objects.php");
include ("seed.php");

set_exception_handler('globalExceptionHandler');


class Super
{

	private static $tablekey = null;
	private static $tablekeyvalue = null;
	private static $currentSS = null;
	

	public static function getConfig(){
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		
		// Load XML (Asumsi Super::loadFile mengembalikan SimpleXMLElement)
		$xml = Super::loadFile($filePath);
		$docker_aiServer = (string)$xml->docker_aiServer;
		$aiServer = (string)$xml->docker_aiServer;

		return [
			"docker_aiServer" => $docker_aiServer,
			"aiServer" => $aiServer
		];
	}
	public static function loadFile($filePath){
		if(!file_exists($filePath)) {
			echo "Property File is not found: " . $filePath;
			return null;
		}
		$xml = simplexml_load_file($filePath);
		
		if($xml){
			return $xml;
		} else {
			echo "Error on XML Parsing: " . $filePath;
			return null;
		}
	}

	public static function getClientID(){
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		
		// Load XML (Asumsi Super::loadFile mengembalikan SimpleXMLElement)
		$xml = Super::loadFile($filePath);
		$client_id = (string)$xml->client_id;
		return $client_id;
	}

	public static function sendToML($endpoint, $payload, $curl_response = false){
		
		set_time_limit(0); 
		ini_set('max_execution_time', 3600); 

		$aiServer = Super::getAIServer();
		$ai_url = $aiServer.$endpoint;
		$ch = curl_init($ai_url);

		// 2. Set Timeout 10 Menit (600 Detik)
		// CURLOPT_TIMEOUT menggunakan satuan DETIK
		$timeout = 2400; // 40 menit

   		 $client_id = Super::getClientID();

    	$finalJsonPayload = "";

		if (is_string($payload)) {
			$tempArray = json_decode($payload, true);

			if (json_last_error() === JSON_ERROR_NONE && is_array($tempArray)) {
				$tempArray['client_id'] = $client_id;
				$finalJsonPayload = json_encode($tempArray);
			} else {
				$finalJsonPayload = json_encode([
					"raw_data" => $payload,
					"client_id" => $client_id
				]);
			}
		} 
		elseif (is_array($payload) || is_object($payload)) {
			$tempArray = (array)$payload;
			$tempArray['client_id'] = $client_id;
			$finalJsonPayload = json_encode($tempArray);
		}
		else {
			$finalJsonPayload = json_encode(["client_id" => $client_id]);
		}

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $finalJsonPayload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Content-Length: ' . strlen($finalJsonPayload)
		));

		// 3. Konfigurasi Timeout & Keep-Alive
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);       // Waktu tunggu response total (10 menit)
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);      // Waktu tunggu saat connect awal (30 detik cukup)
    
		// Tambahan: Keep-Alive (Penting untuk long polling 10 menit agar tidak diputus firewall)
		curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
		curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 120); 
		curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 60);

		// 4. Execute
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);

    
    	curl_close($ch); 

		if($curl_response == true){
			return $response;
		}

		if ($http_code == 200) {
			$response_data = json_decode($response, true);
			if (isset($response_data['error'])) {
				$narrative_result = "ML Engine Error: " . $response_data['error'];
			}
			elseif (isset($response_data['narrative_summary'])) {
				$narrative_result = $response_data['narrative_summary'];
			} else {
				// Fallback jika format JSON beda tapi sukses
				$narrative_result = isset($response_data['message']) ? $response_data['message'] : "AI response format is invalid.";
			}

		} else {
			if ($curl_error) {
				$narrative_result = "System Error: Failed to connect to AI Engine. (Timeout/Network) " . $curl_error;
			} else {
				$error_resp = json_decode($response, true);
				$detail = isset($error_resp['detail']) ? $error_resp['detail'] : "Unknown Error";
				$narrative_result = "AI Error ($http_code): " . $detail;
			}
		}

		return $narrative_result;
	}
	
	public static function sendNotification(array $data) {
		
		$username    = $data['username'] ?? null;
		$message     = $data['message'] ?? 'You have a new notification.';
		$objectname  = $data['objectname'] ?? null;
		$objectid    = $data['objectid'] ?? null;
		$objectnum   = $data['objectnum'] ?? null;
		$processname = $data['processname'] ?? 'GENERAL_NOTIFICATION';

		if (empty($username)) {
			error_log("Attempted to send notification with no username.");
			return false;
		}

		try {
			$enterdate = Xbo::setEnterDate();
		   
			$url = '#'; 
			$paramName = '';
			$appName = $objectname;
			
			
			switch (strtolower($objectname)) {
				case 'invreserve':
					$appName = 'socialreservation'; 
					$paramName = 'invreservenum';
					break;
			}
			
			
			
			if (!empty($appName) && !empty($objectnum)) {
				$baseUrl = Super::getApplicationURL($appName); 
				
			}

			// 4. INSERT data notifikasi ke dalam database
			$setNotification = new Set();
			$setNotification->MboSetRemote("mxsnotification")
				->setValue("username", $username)
				->setValue("message", $message)
				->setValue("url", $baseUrl)
				->setValue("isread", 'N')
				->setValue("enterdate", $enterdate)
				->setValue("object", $objectname)
				->setValue("objectid", $objectid)
				->setValue("objectnum", $objectnum)
				->setValue("processname", $processname)
				->add();
			
			return true; 

		} catch (Exception $e) {
			error_log("Failed to send notification: " . $e->getMessage());
			return false;
		}
	}
	
	public static function senddocuments(){
		// Ambil semua data dari POST
		$remarks = $_POST["documentremarks"];
		$documentstatus = $_POST["documentstatus"];
		$notify = $_POST["notify"];
		$ownerpts = explode("^", $_POST["owner"]);
		$person = explode("`", $_POST["senddocuments"])[1]; // personid dari penerima
		$ownertable = $ownerpts[0];
		$ownerid = $ownerpts[1];
		$tablename = $_POST["tablename"]; // Ini adalah tabel tracking, e.g., 'prdoctracking'
		$tablekey = $_POST["tablekey"];   // e.g., 'prnum'
		$ownertableid = $ownertable . "id"; // e.g., 'prid'

		$set = new Set(); // Inisialisasi Set object

		// Dapatkan userid pengirim saat ini
		$sentby = Xbo::getLoginUser();
		$accepteddate = Xbo::setEnterDate();
		$adSt = strtotime($accepteddate);

		// Dapatkan userid penerima berdasarkan personid
		// Pastikan untuk menginisialisasi ulang $set jika metode MboSetRemote atau lainnya mengubah state internal $set
		// atau jika Set dirancang untuk sekali pakai per query.
		$set = new Set(); 
		$userid = $set->MboSetRemote("mxsusers")->setWhere("personid='$person'")->MboRemote("userid")->getMboSet()->getMbo()->getString("userid");

		// --- Guard Clause 0: Periksa apakah penerima valid ---
		if (empty($userid)) {
			echo "invalidrecipient"; // Atau pesan error yang lebih spesifik
			return null;
			
		}

		// Dapatkan tablekeyvalue (e.g., nomor PR) dari ownertable
		$set = new Set();
		$tablekeyvalue = $set->MboSetRemote($ownertable)->setWhere("$ownertableid = '$ownerid'")->MboRemote($tablekey)->getMboSet()->getMbo()->getString($tablekey);
		
		// Dapatkan personid penerima terakhir dari tabel tracking dokumen
		$set = new Set();
		$lastpersonid = $set->MboSetRemote($tablename) // $tablename adalah tabel tracking seperti prdoctracking
						   ->setWhere("$tablekey ='$tablekeyvalue' AND accepteddate = (SELECT MAX(accepteddate) FROM $tablename WHERE $tablekey ='$tablekeyvalue')")
						   // Alternatif subquery yang mungkin lebih efisien di beberapa DB:
						   // ->setWhere("$tablekey ='$tablekeyvalue'")->addOrderBy("accepteddate", "DESC")->setMaxRows(1) 
						   ->MboRemote("receivedby")
						   ->getMboSet() // Hapus "showcommand" jika tidak diperlukan
						   ->getMbo()
						   ->getString("receivedby");
		
		$lastrecby = ""; // Inisialisasi $lastrecby
		if (!empty($lastpersonid)) {
			$set = new Set();
			$lastrecby = $set->MboSetRemote("mxsusers")->setWhere("personid='$lastpersonid'")->MboRemote("userid as lastrecby")->getMboSet()->getMbo()->getString("lastrecby");
		}

		// Debugging output (opsional, bisa dihapus di produksi)
		// echo "> lastrecby: " .$lastrecby . " > sentby: ".$sentby . " > userid: $userid";

		// --- Guard Clause 1: Periksa apakah pengirim mencoba mengirim ke dirinya sendiri ---
		if ($sentby == $userid) {
			echo "sameperson"; // Atau pesan yang lebih deskriptif: "cannotsendtoself"
			return null;
		}

		// --- Guard Clause 2: Jika dokumen sudah pernah diterima seseorang ($lastrecby tidak kosong),
		//     periksa apakah pengirim saat ini ($sentby) BUKAN orang tersebut ($lastrecby).
		//     Jika ya, berarti pengirim tidak berhak memindahkan dokumen.
		if (!empty($lastrecby) && $lastrecby != $sentby) {
			echo "cannotmove"; // Pesan error: Pengirim bukan pemegang dokumen saat ini.
			return null;
		}
		
		// --- Guard Clause 3: Jika dokumen sudah pernah diterima seseorang ($lastrecby tidak kosong),
		//     dan penerima terakhir ($lastrecby) adalah sama dengan penerima yang dituju ($userid).
		//     Ini berarti dokumen sudah ada pada orang yang dituju.
		//     Catatan: Kasus dimana $sentby == $lastrecby == $userid sudah ditangani oleh Guard Clause 1.
		//     Kondisi ini lebih untuk kasus dimana $lastrecby == $userid tetapi $sentby != $lastrecby (yang seharusnya sudah ditangani Guard Clause 2).
		//     Namun, untuk menjaga logika asli Anda jika ada skenario spesifik:
		if (!empty($lastrecby) && $lastrecby == $userid) {
			echo "sameperson"; // Pesan error: Dokumen sudah ada pada penerima yang dituju.
			return null;
		}

		// Jika semua pemeriksaan di atas lolos, maka dokumen bisa dipindahkan/dikirim.
		// echo "canmove"; // Echo ini bisa dihapus karena aksi pengiriman adalah buktinya.

		$hours = null;
		$minute = null;
		$manydays = null;
		$doctracktable = $ownertable . "doctracking"; // Nama tabel tracking yang konsisten

		$set = new Set();
		$processnum = $set->MboSetRemote($doctracktable)->setWhere("$tablekey = '$tablekeyvalue'")->MboRemote("max(processnum)+1 as processnum")->getMboSet()->getMbo()->getString("processnum");
		if (empty($processnum) || $processnum == 0) { // Pastikan processnum mulai dari 1
			$processnum = 1;
		}

		$set = new Set();
		$lastaccepteddateStr = $set->MboSetRemote($doctracktable)->setWhere("$tablekey = '$tablekeyvalue' AND processnum = ($processnum-1)") // Dapatkan tanggal dari proses sebelumnya
								   ->MboRemote("accepteddate as lastaccepteddate") // Ambil accepteddate terakhir yang valid
								   ->getMboSet()->getMbo()->getString("lastaccepteddate");
		
		if (!empty($lastaccepteddateStr)) {
			$lacdSt = strtotime($lastaccepteddateStr);
			$diff = $adSt - $lacdSt; // Perbedaan dalam detik
			// $days = floor($diff / (60 * 60 * 24)); // Tidak digunakan di setValue
			$hours = floor($diff / (60 * 60)); // Total jam pembulatan ke bawah
			$minute = floor($diff / 60); // Total menit pembulatan ke bawah
			
			// Konversi total menit ke format HH:MM atau DD HH:MM
			$totalMinutesForManyDays = abs(round($diff / 60));
			$manydays_d = floor($totalMinutesForManyDays / (24 * 60));
			$manydays_h = floor(($totalMinutesForManyDays % (24 * 60)) / 60);
			$manydays_m = $totalMinutesForManyDays % 60;
			if ($manydays_d > 0) {
				$manydays = sprintf("%d hari %02d:%02d", $manydays_d, $manydays_h, $manydays_m);
			} else {
				$manydays = sprintf("%02d:%02d", $manydays_h, $manydays_m);
			}
			// Jika Anda menggunakan Super::convertToHoursMins dan itu bekerja, Anda bisa tetap menggunakannya.
			// $manydays = Super::convertToHoursMins($minute, '%02d:%02d'); // Pastikan $minute di sini adalah total menit, bukan sisa menit.
		} else {
			$manydays = "00:00"; // Atau "0 hari 00:00" jika prosesnum = 1
			$hours = 0;
			$minute = 0;
		}
		
		$set = new Set(); // Pastikan $set bersih sebelum operasi add/change
		$set->MboSetRemote($doctracktable)
			->setValue($tablekey, $tablekeyvalue)
			->setValue("status", $documentstatus) // Menggunakan $documentstatus dari POST
			->setValue("sentby", $sentby)
			->setValue("remarks", $remarks)
			->setValue("notify", $notify)
			->setValue("receivedby", $userid)
			->setValue("processnum", $processnum)
			->setValue("accepteddate", $accepteddate)
			->setValue("delta", $manydays)
			->setValue("hours", $hours) // Menyimpan total jam (mungkin pembulatan)
			->setValue("minutes", $minute) // Menyimpan total menit
			->add();
			
		$remarks2 = "<b>" . $sentby . "</b> said, \"" . $remarks . "\"";
		
		$set = new Set();
		// Tandai semua notifikasi sebelumnya untuk dokumen ini sebagai sudah dibaca
		$set->MboSetRemote("mxsdoctracking")->setWhere("ownertable = '$ownertable' AND ownernum = '$tablekeyvalue'")->setValue("markasread", 'Y')->change();
		
		// Tambah notifikasi baru
		$set = new Set();
		$set->MboSetRemote("mxsdoctracking")
			->setValue("ownertable", $ownertable)
			->setValue("ownernum", $tablekeyvalue)
			->setValue("fromuserid", $sentby)
			->setValue("touserid", $userid)
			->setValue("remarks", $remarks2)
			->setValue("markasread", "N")
			->setValue("enterdate", $accepteddate)
			->setValue("ownerkey", $ownerid) // ownerid dari $ownerpts (misal, prid)
			->add();
		
		$set = new Set();
		$set->MboSetRemote($ownertable)->setWhere("$ownertableid = '$ownerid'")
			->setValue("docprocessnum", $processnum)
			->setValue("docstatus", $documentstatus) // Menggunakan $documentstatus dari POST
			->setValue("docsentby", $sentby)
			->setValue("docnotice", $remarks) // Menyimpan remarks utama
			->setValue("docposition", $userid) // Menyimpan userid penerima saat ini
			->setValue("docarrival", $accepteddate)
			->change();
			
		// Mungkin ada output JSON yang diharapkan oleh client, e.g., status sukses
		echo json_encode(["status" => "success", "message" => "Document sent successfully"]);
		//return null; // Atau return true/status object jika diperlukan
	}
		
	
	public static function ChartPorts(){
		$appname = $_GET['chartport'];
		$set = new Set();
		$mxschartSet = $set->MboSetRemote("mxschart")->setRelationship("mxschartline", "mxschart.chartnum = mxschartline.chartnum")->setWhere("mxschartline.appname='$appname'")->order("mxschartline.sortorder")->MboRemote("mxschart.chartnum")->MboRemote("mxschart.description")->MboRemote("mxschart.label")->MboRemote("mxschart.data")->getMboSet();
		if ($mxschartSet->counts() > 0) {
			$chartnumArr = array();
			for ($i = 0; $i < $mxschartSet->counts(); $i++) {
				$mxschart = $mxschartSet->getMbo();
				$chartname = $mxschart->getString("chartnum");
				array_push($chartnumArr, $chartname);
			}
			$chartnameI = implode("^", $chartnumArr);
			$datax = $mxschartSet->counts() . "|" . $chartnameI;
			echo $datax;
		}
		else echo "nocharts";
	}
	
	public static function BodyChart(){
		$x = explode("|", $_GET['crtbody']);
		$set = new Set();
		$mxschart = $set->MboSetRemote("mxschart")->setRelationship("mxschartline", "mxschart.chartnum = mxschartline.chartnum")->setWhere("mxschartline.appname='" . $x[1] . "' and mxschart.chartnum='" . $x[0] . "'")->MboRemote("mxschart.chartnum")->MboRemote("mxschart.description")->MboRemote("mxschart.type")->MboRemote("mxschart.label")->MboRemote("mxschart.data as cdata")->MboRemote("mxschart.title")->MboRemote("mxschart.xtitle")->getMboSet()->getMbo();
		$cname = $x[0];
		$ctype = $mxschart->getString("type");
		$clabel = ($mxschart->getString("label") == null) ? "" : $mxschart->getString("label");
		$cdata = ($mxschart->getString("cdata") == null) ? "" : $mxschart->getString("cdata");
		$ctitle = $mxschart->getString("title");
		$cxtitle = ($mxschart->getString("xtitle") == null) ? "" : $mxschart->getString("xtitle");
		$cdescription = $mxschart->getString("description");
		Set::throws(["cname" => $cname, "ctype" => $ctype, "clabel" => $clabel, "cdata" => $cdata, "ctitle" => $ctitle, "cxtitle" => $cxtitle, "cdescription" => $cdescription]);
	}
	public static function embedYoutube(){
		$youtubeurl = $_POST["youtubeurl"];
		$youtubedesc = $_POST["youtubedesc"];
		$ownertable = $_POST["ownertable"];
		$ownerid = $_POST["ownerid"];
		$youtubeurl2 = str_replace("watch?v=", "embed/", $youtubeurl);
		$set = new Set();
		$dateNow = date("Y-m-d");
		$set->MboSetRemote("fileinfo")->setValue("filename", $youtubeurl2)->setValue("filedefault", "N")->setValue("description", $youtubedesc)->setValue("uploaddate", $dateNow)->setValue("ownerid", $ownerid)->setValue("ownertable", $ownertable)->setValue("type", "ytb")->add();
	}
	
	public static function convertToHoursMins($time, $format = '%02d:%02d') {
		if ($time < 1) {
			return;
		}
		$hours = floor($time / 60);
		$minutes = ($time % 60);
		return sprintf($format, $hours, $minutes);
	}
	public static function seedroot(){
		Seed::SeedRoot();
	}
	public static function setActid($seedroot){
		$srStr = Seed::SetActid($seedroot);
		return $srStr;
	}
	public static function checksum(){
		Seed::CheckSum();
	}
	public static function setSR($seedroot){
		$arrRev = Seed::SetSR($seedroot);
		return $arrRev;
	}
	
	function getColumnValue($colarr, $appname, $key, $val, $nodom = null){
		/*
		item.itemnum.description, 
		item.itemnum.spectext, 
		invvendor.itemnum.vendorcode, 
		invvendor.itemnum.vendorcode.vendor.name
		*/
		
		$cnt = count($colarr);
		
		$info = new stdClass();
		for($i=0; $i< $cnt; $i++){
			$cols = $colarr[$i];
			
			if (strpos($cols, ':') !== false){
				$colx = explode(":", $cols);
				$cols = $colx[1];
				$label = "<span class='labeltitle'>".$colx[0].": </span>";
				if($nodom == "nodom"){
					$label = $colx[0];
				}
			}else{
				$label = "";
			}
			
			if (strpos($cols, '.') !== false) {
				$havedot = explode(".", $cols);
				$cnts = count($havedot);
				
				if($cnts == 2){
					$table = $appname;
					$keynum = $havedot[0];
					$keyval = $havedot[1];
					//echo "$appname, $key, $val, $table, $keynum, $keyval <hr>";
					$set = new Set();
					$tableSet = $set->MboSetRemote($appname)
					->setWhere("$table.$keynum = '$val'")
					->MboRemote($keyval)->getMboSet();
					
					$tsetcnt = $tableSet->counts();
					$vr = [];
					for($ic3=0; $ic3<$tsetcnt; $ic3++){
						$tb = $tableSet->getMbo();
						$getval = $tb->getString($keyval);
						if(is_numeric($getval)) {
							 $getval = number_format($getval,2);
							 $decimallabel = " label-decimal";
						}else{
							 $decimallabel="";
						}
						array_push($vr, $getval);
					}
					$vr = array_unique($vr);
					$vrs = implode("; ", $vr);
					$info->$keyval = $label."<span class='labelvalue$decimallabel'>".$vrs."</span>";
					if($nodom == "nodom"){
						$info->$keyval = $vrs;
					}
					
				}else{
					if($cnts == 3){
						$table = $havedot[0];
						$keynum = $havedot[1];
						$keyval = $havedot[2];
						
						$set = new Set();
						$tableSet = $set->MboSetRemote($appname)
						->setWhere("$table.$key = '$val'")
						->setRelationship($table, "$table.$keynum = $appname.$keynum")->MboRemote("$table.$keyval")->getMboSet();
						
						$tsetcnt = $tableSet->counts();
						$vr = [];
						if($tsetcnt == 0){
							$set = new Set();
							$tableSet = $set->MboSetRemote($appname)
							->setWhere("$appname.$key='$val'")
							->MboRemote("$keynum")->getMboSet();
							
							$tbl = $tableSet->getMbo();
							$fkeyval = $tbl->getString("$keynum");
							//echo "$appname $key $val $keynum $fkeyval $val $table";
							$set2 = new Set();
							$tableSet = $set->MboSetRemote($table)
							->setWhere("$table.$keynum='$fkeyval'")
							->MboRemote("$keyval")->getMboSet();
							
							$tsetcnt = $tableSet->counts();
							for($ic3=0; $ic3<$tsetcnt; $ic3++){
								$tb = $tableSet->getMbo();
								$getval = $tb->getString("$keyval");
								if(is_numeric($getval)) {
									 $getval = number_format($getval,2);
									 $decimallabel = " label-decimal";
								}else{
									 $decimallabel="";
								}
								array_push($vr, $getval);
							}
							$vr = array_unique($vr);
							$vrs = implode("; ", $vr);
							$info->$keyval = $label."<span class='labelvalue$decimallabel'>".$vrs."</span>";
							if($nodom == "nodom"){
								$info->$keyval = $vrs;
							}
						}else{
							for($ic3=0; $ic3<$tsetcnt; $ic3++){
								$tb = $tableSet->getMbo();
								$getval = $tb->getString("$keyval");
								if(is_numeric($getval)) {
									 $getval = number_format($getval,2);
									 $decimallabel = " label-decimal";
								}else{
									 $decimallabel="";
								}
								array_push($vr, $getval);
							}
							$vr = array_unique($vr);
							$vrs = implode("; ", $vr);
							$info->$keyval = $label."<span class='labelvalue$decimallabel'>".$vrs."</span>";
							if($nodom == "nodom"){
								$info->$keyval = $vrs;
							}
						}
					}else{
						if($cnts == 5){
							$table = $havedot[0];
							$keynum = $havedot[1];
							$fkeynum = $havedot[2];
							$reltable = $havedot[3];
							$keyval = $havedot[4];
							
							//"$table 2. $keynum 3. $fkeynum 4. $reltable 5. $keyval ";
							//invvendor 2. itemnum 3. vendorcode 4. vendor 5. name
							$ste = new Set();
							$tableSet = $ste->MboSetRemote($table)
							->setWhere("$table.$key = '$val'")
							->setRelationship($appname, "$table.$keynum = $appname.$keynum")
							->setRelationship($reltable, "$reltable.$fkeynum = $table.$fkeynum")
							->MboRemote("$reltable.$keyval")->getMboSet();
							
							$tsetcnt = $tableSet->counts();
							$vr = [];
							for($ic5=0; $ic5<$tsetcnt; $ic5++){
								$tb = $tableSet->getMbo();
								$getval = $tb->getString("$keyval");
								if(is_numeric($getval)) {
									 $getval = number_format($getval,2);
									 $decimallabel = " label-decimal";
								}else{
									 $decimallabel="";
								}
								array_push($vr, $getval);
							}
							$vr = array_unique($vr);
							$vrs = implode("; ", $vr);
							$info->$keyval = $label."<span class='labelvalue$decimallabel'>".$vrs."</span>";
							if($nodom == "nodom"){
								$info->$keyval = $vrs;
							}
						}
					}
				}
				
			}else{
				$set = new Set();
				$tableSet = $set->MboSetRemote($appname)->setWhere("$key = '$val'")->MboRemote($cols)->getMboSet();
				$tsetcnt = $tableSet->counts();
				
				if($tsetcnt == 0){
					$set = new Set();
					$tableSet = $set->MboSetRemote($appname)
					->setWhere("$appname.$key='$val'")
					->MboRemote("$cols")->getMboSet();
					
					$tscount = $tableSet->counts();
					$vr = [];
					for($t=0;$t<$tscount;$t++){
						$tb = $tableSet->getMbo();
						$getval = $tb->getString("$cols");
						if(is_numeric($getval)) {
							 $getval = number_format($getval,2);
							 $decimallabel = " label-decimal";
						}else{
							 $decimallabel="";
						}
						array_push($vr, $getval);
					}
					$vr = array_unique($vr);
					$vrs = implode("; ", $vr);
					$info->$cols = $label."<span class='labelvalue$decimallabel'>".$vrs."</span>";
					if($nodom == "nodom"){
						$info->$cols = $vrs;
					}
				}else{
					
					$table = $tableSet->getMbo();
					$getval = $table->getString($cols);
					if(is_numeric($getval)) {
						 $getval = number_format($getval,2);
						 $decimallabel = " label-decimal";
					}else{
						 $decimallabel="";
					}
					$info->$cols = $label."<span class='labelvalue$decimallabel'>".$getval."</span>";
					if($nodom == "nodom"){
						$info->$cols = $getval;
					}
				}
				
			}
		}
		
		$retval = $info;
		return $retval;
	}
	
	public static function renderQRData(){
		$appname = $_POST["renderqrdata"];
		$key = $_POST["key"];
		$val = (isset($_POST["val"])) ? $_POST["val"] : null;
		$content = "";
		$innertableleft = "<table id='innertableleft'><tr> <td id='top-left'></td> </tr>   <tr> <td id='bottom-left'></td> </tr> </table>";
		$innertableright= "<table id='innertableright'><tr> <td id='top-right'></td> </tr>   <tr> <td id='bottom-right'></td> </tr> </table>";
		$tbframe = "<table id='qrtblsection'><tr> <td>".$innertableleft."</td> <td>".$innertableright."</td> </tr> </table>";
		
		$set = new Set();
		$qrconfigSet = $set->MboSetRemote("qrcodeconfig")->setWhere("appname='$appname'")
		->MboRemote("leftattribute")->MboRemote("rightattribute")
		->MboRemote("qrformat")->MboRemote("qrsize")
		->MboRemote("qrcodeposition")->MboRemote("remarks")
		->getMboSet();
		
		$qrconcount = $qrconfigSet->counts();
		if($qrconcount >0){
			
			$sl = new Super();
			$sr = new Super();
			$sa = new Super();
			
			$qrconfig = $qrconfigSet->getMbo();
			$leftattribute = $qrconfig->getString("leftattribute");
			$rightattribute = $qrconfig->getString("rightattribute");
			$qrformat = $qrconfig->getString("qrformat");
			$qrsize = $qrconfig->getString("qrsize");
			$qrcodeposition = $qrconfig->getString("qrcodeposition");
			$remarks = $qrconfig->getString("remarks");
			
			
			if($leftattribute != "" || $leftattribute !=null){
				$leftexplode = explode(",", $leftattribute);
				$ss_left = $sl->getColumnValue($leftexplode, $appname, $key, $val);
			}else{
				$leftexplode = [];
				$ss_left = "";
			}
			
			
			if($rightattribute != "" || $rightattribute !=null){
				$rightexplode = explode(",", $rightattribute);
				$ss_right = $sr->getColumnValue($rightexplode, $appname, $key, $val);
				
			}else{
				$rightexplode = [];
				$ss_right = "";
			}
			
			
			
			$lrrr = array_merge($leftexplode, $rightexplode);
			$ss_all = $sa->getColumnValue($lrrr, $appname, $key, $val, "nodom");
			
			if (strpos($qrformat, '|') !== false) {
				$sx = explode("|", $qrformat);
				$sxcount = count($sx);
				$ssr = [];
				$qrobj = new stdClass();
				
				for($i=0; $i<$sxcount; $i++){
					$set = new Set();
					$qrformat = $sx[$i];
					if (strpos($qrformat, '.') !== false) {
						$havedot = explode(".", $qrformat);
						$table = $havedot[0];
						$keynum = $havedot[1];
						$keyval = $havedot[2];
						
						$set = new Set();
						$tableSet = $set->MboSetRemote($appname)
						->setWhere("$table.$key = '$val'")
						->setRelationship($table, "$table.$keynum = $appname.$keynum")->MboRemote("$table.$keyval")->getMboSet();
						
						$tsetcnt = $tableSet->counts();
						$vr = [];
						for($ic3=0; $ic3<$tsetcnt; $ic3++){
							$tb = $tableSet->getMbo();
							$getval = $tb->getString("$keyval");
							array_push($vr, $getval);
						}
						$asd = array_unique($vr);
						$qrformat = implode("|", $asd);
						
					}else{
						$set = new Set();
						$qrformat = $set->MboSetRemote($appname)->setWhere("$qrformat = '$val'")->MboRemote($qrformat)->getMboSet()->getMbo()->getstring($qrformat);
						
					}
					array_push($ssr, $qrformat);
				}
				$newqrformat = implode("|", $ssr);
			}else{
				$set = new Set();
				$newqrformat = $set->MboSetRemote($appname)->setWhere("$qrformat = '$val'")->MboRemote($qrformat)->getMboSet()->getMbo()->getstring($qrformat);
			}
			
			Set::throws([
			"qrsize"=>$qrsize, "info_right"=>$ss_right, "info_left"=>$ss_left, "info_all"=>$ss_all, "qrformat"=>$newqrformat, "qrcodeposition"=>$qrcodeposition, "remarks" => $remarks, 
			"tbframe" => $tbframe
			]);
		}else{
			Set::throws([
			"qrsize"=>null, "info_right"=>null, "info_left"=>null, "info_all"=>null, "qrformat"=>null, "info_all"=>null, "qrcodeposition"=>null, "remarks" => null, "tbframe" => $tbframe
			]);
		}
		
	}
	public static function UpdateQRConfig(){
		$appname = $_POST["updateqrconfig"];
		$leftattribute = $_POST["leftattribute"];
		$rightattribute = $_POST["rightattribute"];
		$qrcodeposition = $_POST["qrcodeposition"];
		$qrformat = $_POST["qrformat"];
		$remarks = $_POST["remarks"];
		$qrsize = $_POST["qrsize"];
		
	
		if(empty($appname)){
			Set::throws(["msg"=>"appnamenotset"]);
		}else{
			$ss = new Set();
			$appSet = $ss->MboSetRemote("qrcodeconfig")->setWhere("appname='$appname'")->MboRemote("appname")->getMboSet();
			$cnt = $appSet->counts();
				
			if($cnt == 0){
				$si = new Set();
				$si->MboSetRemote("qrcodeconfig")->setValue("appname", $appname)->setValue("leftattribute", $leftattribute)->setValue("rightattribute", $rightattribute)->setValue("qrformat", $qrformat)
				->setValue("qrsize", $qrsize)->setValue("qrcodeposition", $qrcodeposition)->setValue("remarks", $remarks)->add();
			}else{
				$si = new Set();
				$si->MboSetRemote("qrcodeconfig")->setWhere("appname='$appname'")->setValue("leftattribute", $leftattribute)->setValue("rightattribute", $rightattribute)->setValue("qrformat", $qrformat)
				->setValue("qrsize", $qrsize)->setValue("qrcodeposition", $qrcodeposition)->setValue("remarks", $remarks)->change();
			}
			Set::throws(["msg"=>"updated"]);
		}
		
	}
	public static function QRConfigInfo(){
		$appname = $_POST["qrconfiginfo"];
		
		$ss = new Set();
		$qrcodeconfigSet = $ss->MboSetRemote("qrcodeconfig")->setWhere("appname='$appname'")->MboRemote("leftattribute")->MboRemote("rightattribute")
		->MboRemote("qrcodeposition")->MboRemote("remarks")->MboRemote("qrsize")->MboRemote("qrformat")
		->getMboSet();
		$qr = $qrcodeconfigSet->getMbo();
		
		Set::throws([
		"leftattribute"=>$qr->getString("leftattribute"), 
		"rightattribute"=>$qr->getString("rightattribute"),
		"qrcodeposition"=>$qr->getString("qrcodeposition"),
		"remarks"=>$qr->getString("remarks"),
		"qrsize"=>$qr->getString("qrsize"),
		"qrformat"=>$qr->getString("qrformat")
		]);
	}
	
	public static function getExcelPath($value = null){
		if($value == null ) {
			$xboset = $_GET["importxls"];
			$set = new Set();
			$url = $set->MboSetRemote("mxsapp")->setWhere("appname='$xboset'")->MboRemote("url")->getMboSet()->getMbo()->getString("url");
		}else {
			$xboset = $value["objectname"];
			$modulename = $value["modulename"];
			$appname = $value["appname"];
			$displaymsg = $value["displaymsg"];
			$set = new Set();
			$url = $set->MboSetRemote("mxsapp")->setWhere("appname='$appname'")->MboRemote("url")->getMboSet()->getMbo()->getString("url");
			
		}
		
		if($value != null ) $filename = $value["filename"]; 
		else $filename = $xboset . ".xls";
		
		$dl_file = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})", '', $filename);
		$dl_file = filter_var($dl_file, FILTER_SANITIZE_URL);
		
		$fullpath = Super::getExcelFolder() . $url . $dl_file;
	
		$dataob = new stdClass();
		$dataob->data = $fullpath;
		$dataob->appurl = $url;
		$dataob->filename = $dl_file;
		$dataob->appfileurl = $url . $dl_file;
		return $dataob;
	}
	
	public static function resolveImportFilePath($val) {
		$fileName_without_extension = pathinfo($val['filename'], PATHINFO_FILENAME);
		$fileExtension = strtolower(pathinfo($val['filename'], PATHINFO_EXTENSION));
		
		$url = (new Set())->useTransactionManager()
			->MboSetRemote("mxsapp")
			->where("appname=?", $val['appname'])
			->getMboValue("url");

		$target_dir = Super::getExcelFolder() . $url . DIRECTORY_SEPARATOR;
		$searchPattern = $target_dir . $fileName_without_extension . "_import_*." . $fileExtension;
		$foundFiles = glob($searchPattern);

		if (empty($foundFiles)) {
			throw new Exception("The Excel file was not found on the server. Please reupload the file.");
		}

		// Ambil yang paling baru
		usort($foundFiles, function($a, $b) { return filemtime($b) - filemtime($a); });
		return $foundFiles[0];
	}

	public static function getExcelValue($val) {
	
		$filePath = $val['real_filepath']; 
		
		$result = new stdClass();
		$result->data = [];

		if (!file_exists($filePath)) {
			throw new Exception("Excel File is not found. Path: " . $filePath);
		}

		$spreadsheet = IOFactory::load($filePath);
		$worksheet = $spreadsheet->getSheet(0);
		// Ambil semua data sebagai array, baris 3 adalah nama kolom (Header)
		$allRows = $worksheet->toArray(null, true, true, true);

		if (count($allRows) < 3) return $result;

		$dbHeaders = $allRows[3]; // Ambil baris ke-3 sebagai nama atribut

		// Ambil data mulai dari baris ke-4
		$dataRows = array_slice($allRows, 3);

		foreach ($dataRows as $row) {
			// Cek baris hantu
			$checkContent = array_filter($row, function($cell) {
				return $cell !== null && trim($cell) !== '';
			});
			if (empty($checkContent)) continue;

			$obj = new stdClass();
			foreach ($dbHeaders as $colIndex => $colName) {
				$colName = trim($colName);
				if (empty($colName)) continue;
				// Masukkan data ke property object
				$obj->$colName = ($row[$colIndex] !== null) ? trim($row[$colIndex]) : null;
			}
			$result->data[] = $obj;
		}

		return $result;
	}

	public static function download_defDataLoader(){
		$value = $_POST["downloadloader"];
		$isarr = is_array($value);
		if($isarr == 1){
			$countloadfiles = count($value);
			for($i=0; $i<$countloadfiles; $i++){
				$val = $value[$i];
				$get = Super::getExcelPath($val);
				$fullPath = $get->data;
				$filename = $get->filename;
				$appurl = $get->appurl;
				if (file_exists($fullPath)) {
					$fullhost =  ContextPath::getFullHostName()."/applications/importdata/".$appurl.$filename;
					Set::throws(["msg"=>"downloaded", "errormsg"=>"success", "reviewmsg"=>$fullhost]);
					exit;
				}else{
					Set::throws(["msg"=>"none", "errormsg"=>"File does not exist in $fullPath", "reviewmsg"=>""]);
				}
			}
		}
	}
	public static function Load()
	{
		
		try {
			$loadDataPayload = Super::getParam("loaddata");

			if (!is_array($loadDataPayload) || empty($loadDataPayload)) {
				throw new InvalidArgumentException("Invalid 'loaddata' payload. Expected a non-empty array.");
			}

			// Kita akan proses satu per satu, jadi ambil elemen pertama
			$fileInfo = $loadDataPayload[0];

			// Validasi payload individu
			$appName = $fileInfo['appname'] ?? null;
			$fileName = $fileInfo['filename'] ?? null;
			$objectName = $fileInfo['objectname'] ?? null;
			$actionName = $fileInfo['actionname'] ?? null; 
			$isinsertupdate = $fileInfo['isinsertupdate'] ?? null; 


			if (empty($appName) || empty($fileName) || empty($objectName)) {
				throw new InvalidArgumentException("Payload is missing required fields: appname, filename, objectname.");
			}
			
			// LOGIKA KUNCI: MENEMUKAN NAMA FILE YANG SUDAH DI-RENAME
			// 1. Dapatkan nama file tanpa ekstensi dari frontend
			$fileName_without_extension = pathinfo($fileName, PATHINFO_FILENAME);
			$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

			// 2. Tentukan direktori tempat file seharusnya berada
			$url = (new Set())->useTransactionManager()->MboSetRemote("mxsapp")->where("appname=?", $appName)->getMboValue("url");
			if (empty($url)) {
				throw new Exception("Application '$appName' configuration not found.");
			}
			$target_dir = Super::getExcelFolder() . $url . DIRECTORY_SEPARATOR;

			// 3. Pencarian Pola: [nama_template]_import_*.xls
			$searchPattern = $target_dir . $fileName_without_extension . "_import_*." . $fileExtension;

			// 4. Cari file yang cocok.
			$foundFiles = glob($searchPattern);

			if (empty($foundFiles)) {
				throw new Exception("Import file for '$fileName' not found on server. Please upload the file first.");
			}
			
			// Ambil file yang paling baru (jika ada lebih dari satu)
			usort($foundFiles, function($a, $b) {
				return filemtime($b) - filemtime($a);
			});
			$realFilePath = $foundFiles[0];
			
			// END OF LOGIKA PENCARIAN FILE
			
			$fileInfo['real_filepath'] = $realFilePath;
			
			if ($actionName != null) {
				// dispatcher untuk memanggil fungsi spesifik berdasarkan actionname.
				
			} else {
				$result = Super::importxls($fileInfo);
			}
			
			if (!$result || !is_object($result) || !isset($result->msg)) {
				throw new Exception("Import function returned an invalid result.");
			}

			Set::throws([
				"msg" => $result->msg, 
				"errormsg" => $result->errormsg ?? null, 
				"reviewmsg" => $result->reviewmsg ?? null
			]);

		} catch (Exception $e) {
			Set::throws([
				"msg" => "ERROR", 
				"errormsg" => $e->getMessage(), 
				"reviewmsg" => null
			]);
		}
	}
	
	public static function importxls($value = null)
	{
		ini_set('max_execution_time', 3000);
		ini_set('memory_limit', '512M'); 

		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$filePath = $value['real_filepath'];
		$objectName = $value['objectname'];
		$isInsertUpdate = $value['isinsertupdate'] ?? 'INSERT'; 
		$isInsertUpdate = strtoupper(trim($isInsertUpdate));
		
		$result = new stdClass;
		$result->msg = "success";
		$result->errormsg = "";
		$result->reviewmsg = "";

		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			if (!file_exists($filePath)) {
				throw new Exception("File not found: " . $filePath);
			}
			
			$spreadsheet = IOFactory::load($filePath);
			$worksheet = $spreadsheet->getSheet(0);
			$allRows = $worksheet->toArray(null, true, true, true);

			if (count($allRows) < 4) {
				throw new Exception("The Excel format is invalid. There must be at least 4 rows.");
			}

			// 1. Identifikasi Struktur Header 
			$flagRow = $allRows[1] ??[];       // Baris 1: Flag (PRIMARYKEY)
			$constraints = $allRows[2] ??[];   // Baris 2: Labels / Constraints
			$dbHeaders = $allRows[3] ??[];     // Baris 3: Nama Kolom DB

			$primaryKeyColumns = []; 
			$mandatoryColumns =[];  
			
			// --- LOGIKA MENDETEKSI PRIMARY KEY ---
			foreach ($flagRow as $colIndex => $flagContent) {
				if ($flagContent !== null && strtoupper(trim((string)$flagContent)) === 'PRIMARYKEY') {
					$dbColName = trim($dbHeaders[$colIndex] ?? '');
					if (!empty($dbColName)) {
						$primaryKeyColumns[] = $dbColName;
					}
				}
			}

			// --- LOGIKA MENDETEKSI NOT_NULL ---
			foreach ($constraints as $colIndex => $content) {
				if ($content !== null && strtoupper(trim((string)$content)) === 'NOT_NULL') {
					$mandatoryColumns[] = $colIndex;
				}
			}

			if ($isInsertUpdate === 'INSERTUPDATE' && empty($primaryKeyColumns)) {
				throw new Exception("Update feature is enabled, but there is no 'PRIMARYKEY' label in Row 1.");
			}

			// 2. PROSES BACA DATA EXCEL KE ARRAY (ANTI PHANTOM RECORDS)
			// =========================================================================
			$dataToProcess =[];
			$totalError = 0;
			$errorLogs =[];
			$dataRows = array_slice($allRows, 3); 

			foreach ($dataRows as $rowIndex => $row) {
				$record =[];
				$isRowValid = true;
				$hasRealData = false; // Penanda apakah ada data di kolom DB yang dipetakan

				foreach ($dbHeaders as $colIndex => $dbColumnName) {
					$dbColumnName = trim((string)$dbColumnName);
					if (empty($dbColumnName)) continue;

					$cellValue = $row[$colIndex] ?? null;
					$cleanValue = ($cellValue !== null) ? trim((string)$cellValue) : null;
					if ($cleanValue === '') {
						$cleanValue = null;
					}

					if ($cleanValue !== null && stripos($dbColumnName, 'date') !== false) {
						if (is_numeric($cleanValue)) {
							try {
								$dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cleanValue);
								$cleanValue = $dateObj->format('Y-m-d H:i:s');
							} catch (Exception $e) {}
						} elseif (strpos($cleanValue, '/') !== false || strpos($cleanValue, '-') !== false) {
							$timestamp = strtotime($cleanValue);
							if ($timestamp !== false) {
								$cleanValue = date('Y-m-d H:i:s', $timestamp);
							}
						}
					}
					// Jika ada 1 sel saja yang terisi di kolom DB, berarti ini bukan baris phantom
					if ($cleanValue !== null && $cleanValue !== '') {
						$hasRealData = true;
					}

					// Validasi NOT_NULL
					if (is_array($mandatoryColumns) && in_array($colIndex, $mandatoryColumns)) {
						if ($cleanValue === null || $cleanValue === '') {
							$isRowValid = false;
							$errorLogs[] = "Row " . ($rowIndex + 4) . ": Column '$dbColumnName' cannot be empty.";
							break; 
						}
					}

					// Penanganan Tanggal
					if ($cleanValue !== null && $cleanValue !== '' && stripos($dbColumnName, 'date') !== false) {
						if (is_numeric($cleanValue)) {
							try {
								$dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cleanValue);
								$cleanValue = $dateObj->format('Y-m-d H:i:s');
							} catch (Exception $e) {}
						} elseif (strpos($cleanValue, '/') !== false || strpos($cleanValue, '-') !== false) {
							$timestamp = strtotime($cleanValue);
							if ($timestamp !== false) {
								$cleanValue = date('Y-m-d H:i:s', $timestamp);
							}
						}
					}

					$record[$dbColumnName] = $cleanValue;
				}

				// GUARD RAILS 1: Jika baris ini benar-benar kosong di area kolom DB, LEWATI!
				if (!$hasRealData) {
					continue; 
				}

				// GUARD RAILS 2: Pastikan Primary Key tidak kosong (Wajib untuk Upsert)
				if ($isInsertUpdate === 'INSERTUPDATE' && !empty($primaryKeyColumns)) {
					$mainPk = $primaryKeyColumns[0];
					if (empty($record[$mainPk])) {
						$totalError++;
						$errorLogs[] = "Row " . ($rowIndex + 4) . ": Primary Key '$mainPk' is required but empty.";
						continue; // Lewati baris cacat ini
					}
				}

				if ($isRowValid) {
					if (!array_key_exists('enterby', $record)) $record['enterby'] = $enterby;
					if (!array_key_exists('enterdate', $record)) $record['enterdate'] = $enterdate;
					$dataToProcess[] = $record;
				} else {
					$totalError++;
				}
			}

			// 3. EKSEKUSI DATABASE: Pisahkan logika Insert Only vs Insert & Update
			// =========================================================================
			$totalImported = 0;
			$totalUpdated = 0;

			if (!empty($dataToProcess)) {
					
				// SKENARIO A: INSERT AND UPDATE (UPSERT)
				// ==========================================================
				if ($isInsertUpdate === 'INSERTUPDATE') {
					
					$mainPk = $primaryKeyColumns[0]; 
					
					// --- NORMALISASI DAN HAPUS DUPLIKAT DI DALAM EXCEL ---
					// Buat array baru dengan Key berupa PK yang dinormalkan (Uppercase & Trim)
					// Jika ada duplikat di Excel, data yang terbaca paling akhir yang akan menimpa (diproses)
					$normalizedDataToProcess =[];
					foreach ($dataToProcess as $record) {
						$rawPkValue = $record[$mainPk];
						$normalizedPk = strtoupper(trim((string)$rawPkValue)); // Normalisasi Mutlak
						$normalizedDataToProcess[$normalizedPk] = $record;
					}

					// Ambil daftar nilai PK yang sudah unik dan ternormalisasi
					$pkValuesFromExcel = array_keys($normalizedDataToProcess);

					$existingRecordsMap =[];
					
					// BATCH CHECKING: Cek ke database (Hanya jalan jika ada list PK)
					if (!empty($pkValuesFromExcel)) {
						
						// Ambil data dari DB berdasarkan PK yang dikirim
						$dbRecords = (new Set())->useTransactionManager()
							->MboSetRemote($objectName)
							->whereIn($mainPk, $pkValuesFromExcel)
							->MboRemote($mainPk) // Kita cuma butuh ID-nya saja untuk pencocokan
							->getMboSet()
							->fetchData(); 
							
						// --- NORMALISASI KEY DARI DATABASE ---
						// Ubah hasil dari DB menjadi Map dengan Key yang dinormalkan juga
						// Ini mencegah PHP gagal mencocokkan karena perbedaan huruf besar/kecil atau spasi
						foreach ($dbRecords as $dbRec) {
							$dbPk = strtoupper(trim((string)$dbRec[$mainPk]));
							$existingRecordsMap[$dbPk] = true; // Kita cuma butuh penanda "TRUE" (data ada)
						}
					}

					$dataToInsert =[];
					$dataToUpdate =[];
					
					// Pisahkan keranjang Insert dan Update berdasarkan Key yang sudah dinormalkan
					foreach ($normalizedDataToProcess as $normPk => $record) {
						if (isset($existingRecordsMap[$normPk])) {
							$dataToUpdate[] = $record; // Data ditemukan di DB -> Update
						} else {
							$dataToInsert[] = $record; // Data tidak ditemukan -> Insert Baru
						}
					}

					// A1. Lakukan BULK INSERT untuk data baru (Sistem Chunk)
					if (!empty($dataToInsert)) {
						foreach (array_chunk($dataToInsert, 500) as $chunk) {
							(new Set())->useTransactionManager()->MboSetRemote($objectName)->addStream($chunk); 
							$totalImported += count($chunk);
						}
					}

					// A2. Lakukan UPDATE untuk data yang sudah ada
					if (!empty($dataToUpdate)) {
						foreach ($dataToUpdate as $record) {
							$updateSet = (new Set())->useTransactionManager()->MboSetRemote($objectName);
							
							// Set WHERE berdasarkan semua Primary Key yang ditandai
							foreach ($primaryKeyColumns as $pkCol) {
								$updateSet->where("$pkCol = ?", $record[$pkCol]);
							}

							// Set VALUE untuk kolom selain Primary Key
							foreach ($record as $colName => $val) {
								if (!in_array($colName, $primaryKeyColumns)) {
									// Update audit trail secara pintar
									if ($colName == 'enterby') { $updateSet->setValue('changeby', $val); continue; }
									if ($colName == 'enterdate') { $updateSet->setValue('changedate', $val); continue; }
									
									$updateSet->setValue($colName, $val);
								}
							}
							$updateSet->change();
							$totalUpdated++;
						}
					}

					$result->reviewmsg = "Successfully inserted $totalImported new rows and updated $totalUpdated existing rows in $objectName.";

				} 
				// SKENARIO B: INSERT ONLY (Skenario Default / Super Cepat)
				// ==========================================================
				else {
					// Lakukan Bulk Insert langsung tanpa pengecekan
					foreach (array_chunk($dataToProcess, 500) as $chunk) {
						(new Set())->useTransactionManager()->MboSetRemote($objectName)->addStream($chunk);
						$totalImported += count($chunk);
					}
					$result->reviewmsg = "Successfully inserted $totalImported rows into $objectName.";
				}

				// Jika semua proses Bulk Insert maupun Update berhasil, Commit Transaksi!
				$tm->commit();

			} else {
				// Tidak ada data valid yang bisa diproses
				$tm->rollback();
				$result->msg = "error";
				$result->errormsg = "No valid data found in the Excel file.";
			}

			// Rekap Laporan Error jika ada baris yang cacat/dilewati
			if ($totalError > 0) {
				$result->reviewmsg .= " Detected $totalError error rows.";
				$result->errormsg = implode(" | ", array_slice($errorLogs, 0, 10)); // Batasi log maks 10
				if (count($errorLogs) > 10) {
					$result->errormsg .= " ... and " . (count($errorLogs) - 10) . " more errors.";
				}
			}

		} catch (Exception $e) {
			// Batalkan semua perubahan jika terjadi Fatal Error
			$tm->rollback();
			$result->msg = "error";
			$result->errormsg = "System Error: " . $e->getMessage();
		}

		return $result;
	}

	
	public static function getExcelFolder()
	{
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$reportFolder = $xml->excelFolder;
		return $reportFolder;
	}

	function checkNewRecord($recordOwner)
	{
		$msg = null;
		if (!isset($_POST["table"])) {
			Set::throws(["msg" => "recordOwner_null"]);
			return;
		}

		$tablename = $_POST["table"];
		if ($tablename === "invbalance") return;

		$allData = array_merge($_POST["data"] ?? [], $_POST["recordOwner"] ?? []);
		$allKeys = array_merge($_POST["datakeys"] ?? [], $_POST["recordOwner"] ?? []);
		$clauseArr = [];
		foreach ($allKeys as $colname => $value) {
			if ($value === null) {
				$clauseArr[] = "$colname IS NULL";
			} else {
				$safeValue = addslashes($value);
				$clauseArr[] = "$colname = '$safeValue'";
			}
		}
		$clauseStr = implode(" AND ", $clauseArr);

		$set = new Set();
		$recordidname = $tablename . "id";
	
		$tablenameSet = $set->MboSetRemote($tablename)
							->setWhere($clauseStr)
							->MboRemote($recordidname)
							->getMboSet();

		$numrows = $tablenameSet->counts();
		$msg = ($numrows > 0) ? "recordExist" : "recordNotExist";

		Set::throws(["msg" => $msg]);
	}
	
	public static function whereDate2($colname, $colvalue, $sign=null){
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$where = null;
		if($sign == null){
			if($colvalue != null){
				if ($dbtype == "oracle") {
					$where = "$colname = TO_DATE('".$colvalue."', 'yyyy-mm-dd hh24:mi:ss')";
				}
				if ($dbtype == "mysql") {
					$where = "$colname = '$colvalue' ";
				}
			}else{
				$where = null;
			}
		}else{
			if($colvalue != null){
				if ($dbtype == "oracle") {
					$where = "$colname $sign TO_DATE('".$colvalue."', 'yyyy-mm-dd hh24:mi:ss')";
				}
				if ($dbtype == "mysql") {
					$where = "$colname $sign '$colvalue' ";
				}
			}else{
				$where = null;
			}
		}
		return $where;
	}
	
	public static function whereDate($colname, $colvalue, $sign=null){
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$where = null;
		if($sign == null){
			if($colvalue != null){
				if ($dbtype == "oracle") {
					$where = " and $colname = TO_DATE('".$colvalue."', 'yyyy-mm-dd hh24:mi:ss')";
				}
				if ($dbtype == "mysql") {
					$where = " and $colname = '$colvalue' ";
				}
			}else{
				$where = null;
			}
		}else{
			if($colvalue != null){
				if ($dbtype == "oracle") {
					$where = " and $colname $sign TO_DATE('".$colvalue."', 'yyyy-mm-dd hh24:mi:ss')";
				}
				if ($dbtype == "mysql") {
					$where = " and $colname $sign '$colvalue' ";
				}
			}else{
				$where = null;
			}
		}
		
		
		return $where;
	}
	public static function getReportFolder()
	{
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$reportFolder = $xml->reportFolder;
		return $reportFolder;
	}
	public static function getMaxesViewer()
	{
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$maxesViewer = $xml->reportURL;
		return $maxesViewer;
	}
	public static function runReportParam()
	{
		$parts = $_GET['runReportParam'];
		
		$maxesViewerURL = XboSet::getMaxesViewer();
		$parts = explode("|", $parts);
		$XboSet = "'" . $parts[0] . "'";
		$ReportDesc = "'" . $parts[1] . "'";
		$reportParam = $parts[2];
		$reportParam2 = str_replace("`", "=", $reportParam);
		$reportParam3 = str_replace("^", "&", $reportParam2);
		$clause = "appname = $XboSet and description=$ReportDesc";
		$set = new Set();
		$reportSet = $set->MboSetRemote("report")->setWhere($clause)->MboRemote("reportname")->getMboSet();
		$countResult = $reportSet->counts();
		if ($countResult > 0) {
			$reportfilename = $reportSet->getMbo()->getString("reportname");
			$dest = $maxesViewerURL . "frameset?__report=" . $reportfilename;
			$dest.= "&__title=" . $ReportDesc . "%20Report";
			$dest.= "&" . $reportParam3;
			echo $dest;
		}
	}
	public static function runReport()
	{
		$payload = Super::getParam("runReport");
		$appname = $payload["application_name"];
		$reportDesc = $payload["report_description"];
		$reportID = $payload["id"];

		$maxesViewerURL = XboSet::getMaxesViewer(); //Get WebApps BIRT Report Viewer

		$reportSet = (new Set())->useTransactionManager()->MboSetRemote("report")->where("appname=? AND reportid=?", $appname, $reportID)
		->MboRemote("reportname")->getMboSet();
	
		if ($reportSet->isEmpty()) {
			Super::sendJsonResponse("error", "No Report Found."); //Auto Exit PHP
		}
		$report = $reportSet->getMbo();

		$reportfilename = $report->getString("reportname");
		$url_destination = $maxesViewerURL . "frameset?__report=" . $reportfilename;

		//Check apakah ada report Parameter ? 
		
		$report_parameters = [];
		$reportparameterSet = (new Set())->useTransactionManager()->MboSetRemote("reportparameter")->order("sortparam")
		->where("reportname=?", $reportfilename)
		->MboRemote("parametername, lookupname, displayname, defaultvalue, datatype")->getMboSet();

		if(!$reportparameterSet->isEmpty()){

			while($result = $reportparameterSet->fetch()){
			
				$report_parameters[] =  [
					'parametername' => $result["parametername"], // <-- BENAR
					'lookupname' => $result["lookupname"],       // <-- BENAR
					'displayname' => $result["displayname"],
					'defaultvalue' => $result["defaultvalue"],
					'datatype' => $result["datatype"]
				];
			}
			
		}

		$url_destination.= "&__title=" . $reportDesc . "%20Report";
		Super::sendJsonResponse("success", "Report Found", [
			"url_destination" => $url_destination,
			"report_parameters" => $report_parameters
		]);
	}
	
	
	function getAutoNumber($appname, $update = null, $attrname = null)
    {
        $set = new Set();
        $db = new dbo();
        $dbtype = $db->dbtype();
        $value = ($dbtype == "oracle") ? strtoupper($appname) : $appname;
        $attrWhere = ($attrname != null) ? " and keyattr='$attrname' " : "";

        try {
            // Dapatkan data dari mxsnextval
            $mxsnextvalSet = $set->MboSetRemote("mxsnextval")
                ->setWhere("app='$value' $attrWhere")
                ->MboRemote("keyattr")
                ->MboRemote("prefix")
                ->MboRemote("suffix")
                ->MboRemote("nextval")
                ->MboRemote("pad")
                ->getMboSet();

            $mxsnextval = $mxsnextvalSet->getMbo();
            $keyattr = $mxsnextval->getString("keyattr");
            $prefix = $mxsnextval->getString("prefix");
            $nextval = $mxsnextval->getString("nextval");
            $suffix = $mxsnextval->getString("suffix");
            $pad = $mxsnextval->getString("pad");

            if ($nextval != null) {
                if ($pad != null) {
                    $nextval = str_pad($nextval, $pad, '0', STR_PAD_LEFT);
                }

                $this->autonumber = $prefix . $nextval . $suffix;
                $this->keyattr = $keyattr;

                if ($update == "update_next_value" || $update == "updateNextValue" || $update == "updateNextVal") {
                    $nextvalue = $nextval + 1;
					
                     // Tambahkan logging sebelum update
					$set2= new Set();
             
                    $set2->MboSetRemote("mxsnextval")
                        ->setWhere("keyattr='$keyattr' and app='$value'")
                        ->setValue("nextval", $nextvalue)
                        ->change();
                }

                return $this;
            } else {
                $this->autonumber = $prefix . $nextval . $suffix;
                return $this;
            }
        } catch (Exception $e) {
           
            error_log("Error in getAutoNumber: " . $e->getMessage());
            throw $e;
        }
    }
	
	public static function copyChildrenTable($tableCopy)
	{
		
		
		$opts = explode("^", $_POST["tablecopy"]);
		$ownerTable = $opts[0]; 
		$ownerKey = $opts[1];
		$oldrecnum = $_POST["oldrecnum"];
		$newrecnum = $_POST["newrecnum"];
		$keynum = $_POST["keynum"];
		$ownerTableID = $ownerTable."id";
		if ($ownerKey == "UDFND") $tables = "UDFND";
		else $tables = explode(",", $_POST["mxtables"]);
		
		$set = new Set();
		$isExistSet = $set->MboSetRemote($ownerTable)->setWhere("$keynum='$newrecnum'")->MboRemote($keynum)->getMboSet();
		
		$isExist = $isExistSet->getMbo()->getString($keynum);
		
		if (empty($isExist)) {
			$ownerTableSet = $set->getAllColumns($ownerTable);
			
			$attrSet = $ownerTableSet->result;
			$columnname = $ownerTableSet->columnname;
			$countResult = $attrSet->counts();
			$arrayAtt = array();
			$enterdate = Xbo::setEnterDate();
			$enterby = Xbo::getLoginUser();
			$changeby = $enterby;
			$changedate = $enterdate;
			$childrenTableID = $ownerTable . "id";
			for ($i = 0; $i < $countResult; $i++) {
				$attr = $attrSet->getMbo();
				$attname = $attr->getString($columnname);
				$attname = strtolower($attname);
				if ($attname !== $childrenTableID) {
					array_push($arrayAtt, $attname);
				}
			}
			
			$set2 = new Set();
			$ownerSet = $set2->MboSetRemote("$ownerTable")->setWhere("$childrenTableID='$ownerKey'");
			for ($i = 0; $i < count($arrayAtt); $i++) {
				$ownerSet = $ownerSet->MboRemote($arrayAtt[$i]);
			}
			$ownerSet = $ownerSet->getMboSet();
			
			$owner = $ownerSet->getMbo();
			$valarr = array();
			for ($i = 0; $i < count($arrayAtt); $i++) {
				$value = $owner->getString($arrayAtt[$i]);
				if ($arrayAtt[$i] == $keynum) {
					$value = $newrecnum;
				}
				if ($arrayAtt[$i] == "receipt" || $arrayAtt[$i] == "sentstatus") {
					$value = "NONE";
				}
				if ($arrayAtt[$i] == "status") {
					$setSt = new Set();
					$statusval = $setSt->MboSetRemote("mxsattrvalue")->setWhere("app='$ownerTable' and statuslevel=1")->MboRemote("rootvalue")->getMboSet()->getMbo()->getString("rootvalue");
					
					$value = $statusval;
					$setIn = new Set();
					$ownerTableStatus = $ownerTable . "status";
					$setIn->MboSetRemote("$ownerTableStatus")->setValue($keynum, $newrecnum)->setValue("status", $value)
					->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("remarks", "Duplicated from $ownerTable: $oldrecnum")->add();
				}
				if ($arrayAtt[$i] == "statusdate" || $arrayAtt[$i] == "enterdate" || $arrayAtt[$i] == "changedate") {
					$value = $enterdate;
				}
				if ($arrayAtt[$i] == "enterby" || $arrayAtt[$i] == "changeby") {
					$value = $enterby;
				}
				if ($arrayAtt[$i] == "docprocessnum" || $arrayAtt[$i] == "docstatus" || $arrayAtt[$i] == "docsentby" || $arrayAtt[$i] == "docnotice" || $arrayAtt[$i] == "docposition" || $arrayAtt[$i] == "docarrival") {
					$value = null;
				}
				else {
					$value = $value;
				}
				$str = $arrayAtt[$i] . "`" . $value;
				array_push($valarr, $str);
			}
			$set2 = new Set();
			$ownerIns = $set2->MboSetRemote($ownerTable);
			for ($i = 0; $i < count($valarr); $i++) {
				$pts = explode("`", $valarr[$i]);
				$col = $pts[0];
				$val = $pts[1];
				$ownerIns = $ownerIns->setValue($col, $val);
			}
			
			
			$tableidvalue = $ownerIns->add()->getTableID;
			$errormsg = $ownerIns->errormsg;
			
			if (empty($errormsg)) {
				
				if ($_POST["mxtables"] != "UDFND") {
					
					$setC = new Set();
					for ($i = 0; $i < count($tables); $i++) {
						$childtable = $tables[$i];
						$childcolid = $tables[$i] . "id";
						$childTableSet = $setC->getAllColumns($childtable);
						
						$attrSet2 = $childTableSet->result;
						$columnname2 = $childTableSet->columnname;
						$countC = $attrSet2->counts();
						
						$arrayAtt2 = array();
						for ($j = 0; $j < $countC; $j++) {
							$attr2 = $attrSet2->getMbo();
							$attname2 = $attr2->getString($columnname2);
							
							$attname2 = strtolower($attname2);
							$attname2 = strtolower($attname2);
							if ($attname2 !== $childcolid) {
								array_push($arrayAtt2, $attname2);
							}
						}
						$set = new Set();
						$childSet = $set->MboSetRemote("$childtable")->setWhere("$keynum='$oldrecnum'");
						for ($j = 0; $j < count($arrayAtt2); $j++) {
							$childSet = $childSet->MboRemote($arrayAtt2[$j]);
						}
						$childSet = $childSet->getMboSet();
						
					
						$childSetCount = $childSet->counts();
						for ($j = 0; $j < $childSetCount; $j++) {
							$valarr2 = array();
							$child = $childSet->getMbo();
							for ($k = 0; $k < $countC-1; $k++) {
								$columname = $arrayAtt2[$k];
								$valuek = $child->getString($columname);
								if ($arrayAtt2[$k] == $keynum) {
									$valuek = $newrecnum;
								}
								if ($arrayAtt2[$k] == "receiptstatus" || $arrayAtt2[$k] == "sentstatus") {
									$valuek = "NONE";
								}
								if ($arrayAtt2[$k] == "docprocessnum" || $arrayAtt2[$k] == "docstatus" || $arrayAtt2[$k] == "docsentby" || $arrayAtt2[$k] == "docnotice" || $arrayAtt2[$k] == "docposition" || $arrayAtt2[$k] == "docarrival") {
									$valuek = null;
								}
								if($childtable == "prline"){
									if ($arrayAtt2[$k] == "ponum" || $arrayAtt2[$k] == "polinenum") {
										$valuek = null;
									}
								}
								if (!empty($arrayAtt2[$k])) {
									array_push($valarr2, $arrayAtt2[$k] . "·" . $valuek);
								}
							}
							$set3 = new Set();
							$childIns = $set3->MboSetRemote($childtable);
							for ($l = 0; $l < count($valarr2); $l++) {
								$pts = explode("·", $valarr2[$l]);
								$col = $pts[0];
								$val = $pts[1];
								$childIns = $childIns->setValue($col, $val);
							}
							$childIns->add();
							
						}
					}
					$getAutoNum = new Super();
					$getAutoNum->getAutoNumber($ownerTable, "update_next_value");
					Set::throws(["msg"=>"duplicated", "duplicateid"=>$tableidvalue]);
				}
				else {
					if($tableidvalue!=null){
						Set::throws(["msg"=>"duplicated", "duplicateid"=>$tableidvalue]);
					}else{
						Set::throws(["msg"=>"UDFND"]);
					}
					
				}
			}
			else {
				Set::throws(["msg"=>$errormsg]);
			}
		}
		else {
			Set::throws(["msg"=>"recordExist"]);
		}
	}
	function changeStatusRecord($prop){
		$set = new Set();
		$table = $prop->table;
		$tablestatus = $prop->tablestatus;
		$newstatus = $prop->newstatus;
		$keynum = $prop->keynum;
		$statusdate = $prop->statusdate;
		$colname = $prop->colname;
		$colvalue = $prop->colvalue;
		$remarks = $prop->remarks;
		$changeby = Xbo::getLoginUser();
		$set->MboSetRemote($table)->setWhere("$colname = '$colvalue'")->setValue("status", $newstatus)->setValue("statusdate", $statusdate)->setValue("changeby", $changeby)
		->setValue("changedate", $statusdate)->change();
		
		$set = new Set();
		$set->MboSetRemote($tablestatus)->setValue($colname, $colvalue)->setValue("status", $newstatus)->setValue("changedate", $statusdate)->setValue("changeby", $changeby)->setValue("remarks", $remarks)->add();
		
	}
	function changeStatus()
	{
		
		$oldstatus = $_POST["oldstatus"];
		$keyid = $_POST["keyid"];
		$ownerCol = $_POST["keynum"];
		$ownerColValue = $_POST["keyval"];
		$cs = $_POST["changeStatus"];
		$ownerTable = $_POST["xboset"];
		$newstatus = $cs["newstatus"];
		$changedate = $cs["changedate"];
		$remarks = $cs["newremarks"];
		$notifcode = $cs["notifcode"];
		$statusTable = $ownerTable."status";
		$changeby = Xbo::getLoginUser();
		$setwhere = $ownerCol."='$ownerColValue'";
		
		$set = new Set();
		$statusTableSet = $set->MboSetRemote($statusTable)->setValue($ownerCol, $ownerColValue)->setValue("status", $newstatus)
		->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("remarks", $remarks)->setValue("notifcode", $notifcode)->add();
		
		$set = new Set();
		$set->MboSetRemote($ownerTable)->setWhere($setwhere)->setValue("status", $newstatus)
		->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("statusdate", $changedate)->change();
	
		$activity = "Change Status <b>$ownerCol: <font color=blue>$oldstatus</font></b> to <font color=blue><b>$newstatus</b></font> from $ownerTable.";
		
		$set = new Set();
		
		$whr ="changedate in (select max(changedate) from $statusTable  where $setwhere and status='$newstatus')";
		$stsTableSet = $set->MboSetRemote($statusTable)->SetWhere($whr)->MboRemote("notifcode")->MboRemote("status")->getMboSet();
		
		$stsTable = $stsTableSet->getMbo();
		$notifcode = $stsTable->getString("notifcode");
		$newstatus = $stsTable->getString("status");
		if (!empty($notifcode)) {
			Super::NotifyStatus($ownerTable, $ownerCol, $dataKeyParts[1], $notifcode, $newstatus);
		}
		else {}
		Set::throws(["msg"=>"ok"]);
	}
	public static function ReplaceColumns($messages, $ownerTable, $ownerCol, $ownerColValue, $isquery = null)
	{
		if (preg_match_all('/(?<!\w)#(\w+)/', $messages, $matches)) {
			$columns = $matches[1];
			$colarr = array();
			for ($i = 0; $i < count($columns); $i++) {
				$set = new Set();
				$colval = $set->MboSetRemote($ownerTable)->setWhere("$ownerCol='$ownerColValue'")->MboRemote($columns[$i])->getMboSet()->getMbo()->getString($columns[$i]);
				if ($isquery == "y") {
					$colval = "'" . $colval . "'";
				}
				else {
					$colval = $colval;
				}
				$msgtemp = str_replace("#" . $columns[$i], $colval, $messages);
				$messages = $msgtemp;
			}
		}
		return $messages;
	}
	public static function CheckInternet(){
		$connected = @fsockopen("www.google.com", 80); //80//443
		if ($connected) {
			$isConn = "connected";
			fclose($connected);
		}
		else {
			$isConn = "notConnected";
		}
		
		$a = new stdClass();
		$a->isconnected = $isConn;
		
		Set::throws($a);
	}
	public static function callInternet()
	{
		$connected = @fsockopen("www.google.com", 80); //80//443
		if ($connected) {
			$isConn = "connected";
			fclose($connected);
		}
		else {
			$isConn = "notConnected";
		}
		return $isConn;
	}
	public static function LandPage(){
		//session_start();
		$userid = $_POST["landpage"];
		$username = $_POST["username"];
		
		if($userid == null || $userid == "null") {
			$userid = $username;
		}
		$set = new Set();
		$landSet = $set->MboSetRemote("mxsusergroup")
			->setRelationship("mxsgroupline", "mxsusergroup.groupnum = mxsgroupline.groupnum")
			->setRelationship("mxsapp", "mxsgroupline.appname = mxsapp.appname")
			// 1. Ganti ke `where()` yang lebih aman
			->where("mxsusergroup.userid = ? AND mxsgroupline.homepage = ?", $userid, 'Y')
			->MboRemote("mxsgroupline.appname")->MboRemote("mxsapp.url")
			->getMboSet();
		
		// 2. Langsung panggil fetch() untuk mengambil baris pertama
		$landPageData = $landSet->fetch();
		
		// 3. Cek apakah fetch() berhasil mendapatkan data
		if ($landPageData) { 
			$appname = $landPageData['appname']; 
			$url = $landPageData['url'];
		} else { 
			$appname = "home";
			$url = "home/home/";
		}
		
		$dirurl = ContextPath::getFullHostName();
		$fsname = $dirurl."/ui/".$url;
		
		Set::throws(["appname"=>$appname, "fsname"=>$fsname, "dirurl"=>$dirurl, "url"=>$url]);
	}

	public static function GetSideBar()
	{
		$userid = $_POST["mxsidebar"];
		
		$set = new Set();
		
		$sidebarSet = $set->MboSetRemote("mxsusers")
			->setRelationship("mxsusergroup", "mxsusers.userid = mxsusergroup.userid")
			->setRelationship("mxsgroupline", "mxsusergroup.groupnum = mxsgroupline.groupnum")
			->setRelationship("mxsapp", "mxsgroupline.appname = mxsapp.appname")
			->setRelationship("mxsmodule", "mxsmodule.modulename = mxsapp.modulename")
			->where("mxsusers.userid = ? AND mxsmodule.modulename IS NOT NULL AND mxsapp.appname IS NOT NULL", $userid)
			->MboRemote("mxsmodule.modulename, mxsmodule.description as moduledesc, mxsmodule.icon as moduleicon, mxsmodule.sort as modulesort")
			->MboRemote("mxsapp.appname, mxsapp.description as appdesc, mxsapp.url, mxsapp.icon as appicon, mxsapp.divider, mxsapp.divtitle, mxsapp.sort as appsort") 
			->order("mxsmodule.sort, mxsapp.sort")
			->getMboSet("distinct"); 
			
		$sidebarModules =[];
		
		// LAPIS 2: Buat array tracker untuk memastikan tidak ada app yang masuk dua kali
		$appTracker =[]; 

		while ($row = $sidebarSet->fetch()) {
			$modulename = $row['modulename'];
			$appname = $row['appname'];

			// Jika modul ini belum pernah ada di array utama, buatkan wadahnya
			if (!isset($sidebarModules[$modulename])) {
				$sidebarModules[$modulename] =[
					'modulename'  => $modulename,
					'description' => $row['moduledesc'], 
					'icon'        => $row['moduleicon'],
					'sort'        => $row['modulesort'],
					'apps'        =>[] 
				];
			}
			
			// Cek apakah APP ini sudah pernah dimasukkan ke dalam MODUL ini.
			// Jika belum ada (!isset), maka masukkan.
			if (!isset($appTracker[$modulename][$appname])) {
				
				$sidebarModules[$modulename]['apps'][] =[
					'appname'     => $appname,
					'description' => $row['appdesc'],
					'url'         => $row['url'],
					'icon'        => $row['appicon'],
					'divider'     => $row['divider'],
					'divtitle'    => $row['divtitle']
				];
				
				// Tandai bahwa aplikasi ini SUDAH dimasukkan, jadi iterasi berikutnya akan diskip
				$appTracker[$modulename][$appname] = true;
			}
		}
		
		// Karena kita menggunakan $modulename sebagai kunci array (associative),
		// kita ubah kembali menjadi array index biasa (0, 1, 2) pakai array_values()
		// agar JSON output-nya berupa array [ ... ], bukan object { ... }
		$finalOutput = array_values($sidebarModules);
		
		Set::throws($finalOutput);
	}
		
	function linkTable($package)
	{
		$payload = Super::getParam("linktable");
		$childTableName = $payload["childTableName"];
		$parentObject = $payload["parentObject"];
		$parentObjectID = $payload["parentObjectID"];
		$childObject = $payload["childObject"];
		$foreign_key = $payload["foreign_key"];
		$foreignKeyID = $payload["foreign_key_id"];
		$ownerPackage = $payload["ownerPackage"];
		$clause_array = [];
		foreach($ownerPackage as $row){
			$key = $row["key"];
			$value = $row["value"];
			
			$clause = "$key = '$value'";
			$clause_array [] = $clause;
		}
		
		if(empty($foreignKeyID)){
			$clause = implode(" AND " , $clause_array);
		}else{
			$clause = implode(" AND " , $clause_array). " and $parentObjectID='$foreignKeyID'";
		}
		
		$selectedSet = (new Set())->MboSetRemote($parentObject)->setWhere($clause)->limit(1)->MboRemote($foreign_key)->getMboSet();
		$selected = $selectedSet->getMbo();
		$selectedValue = null; // Default value
		if ($selected) {
			$selectedValue = $selected->getString($foreign_key);
		}
		
			$selectedValue = $selected->getString($foreign_key);
			
			$ss = new stdclass;
			$fk = new stdClass;
			$fk->key = $foreign_key;
			$fk->value = $selectedValue;
			$ss->childTableName = $childTableName;
			$ss->child_fk = $fk;
			$ss->ownerPackage = $ownerPackage;
			$ss->parentObject = $parentObject;
			$ss->childObject = $childObject;
			
			echo json_encode($ss);
			exit();
	}
	
	public static function InitLinkTable($get)
	{
		$owner_clause = [];
		
		$parentObject = $get["parentObject"];
		$childObject = $get["childObject"];
		
		foreach($get["getOwner"] as $row){
			$key = $row["key"];
			$value = $row["value"];
			$id = $row["id"];
			
			$ss = "$childObject.$key = '$value'";
			$owner_clause [] = $ss;
		}
		$ownerClause = implode(" AND " , $owner_clause);
		$child_fk = $get["child_fk"];
		$child_fk_value = $get["child_value"];
		$ownerClause = "( $ownerClause ) AND $childObject.$child_fk = '$child_fk_value'";
		
		return $ownerClause;
	
	}
	function lookupentry($package)
	{
		$table = $package["lookupentry"];
		$set = new Set();
		if($table == "invbalance"){
			XboSet::AddToInventory($_POST);
		}else{
			if($table == "papersetting" || $table == "papersettingbc"){
				
				$addSet = $set->MboSetRemote($table);
				$isdefault = "notdefault";
				foreach($package["data"] as $colname=>$value) {
					if($colname == "isdefault" && $value == "Y"){
						$isdefault = "Y";
					}else{
						$isdefault = "N";
					}
					$addSet = $addSet->setValue($colname, $value);	
				}
				
				if($isdefault == "Y"){
					$sp = new Set();
					$papersettingSet = $sp->MboSetRemote($table)->setWhere("isdefault='Y' or isdefault='y'")->setValue("isdefault", "N")->change();
				}
				$addSet = $addSet->add();
				$msg = "added";
			}else{
				try {
				
					$payload = Super::getParam("data"); 
					if (empty($payload) || !is_array($payload)) {
						throw new InvalidArgumentException("Payload 'data' is required.");
					}

					$checkSet = (new Set())->useTransactionManager()
										->MboSetRemote($table);

					foreach ($payload as $colname => $value) {
						
						if ($value === null || $value === '') {
							$checkSet->whereNull($colname);
						} else {
							$checkSet->where("$colname = ?", $value);
						}
					}
					$primaryKeyName = $table . 'id';
					$existingRecord = $checkSet->MboRemote($primaryKeyName)
											->limit(1)
											->getMboSet()
											->fetch();

					// Eksekusi ADD atau CHANGE ---

					$actionSet = (new Set())->useTransactionManager()
											->MboSetRemote($table);

					// Set nilai-nilai yang akan disimpan (sama untuk add maupun change)
					foreach ($payload as $colname => $value) {
						$actionSet->setValue($colname, $value);
					}

					$msg = "";
					
					if ($existingRecord) {
						// === KASUS UPDATE (CHANGE) ===
						$recordId = $existingRecord[$primaryKeyName];
						
						$actionSet->where("$primaryKeyName = ?", $recordId)
								->change();
						$msg = "updated";
					} else {
						// === KASUS INSERT (ADD) ===
						$actionSet->add();
						$msg = "added";
					}
				
					//Super::sendJsonResponse("success", "Data has been $msg successfully.");
				} catch (Exception $e) {
					Super::sendJsonResponse("error", $e->getMessage());
				}

			}
		}
		
		Set::throws(["msg"=>$msg]);
	}
	function delLookupPackage($package)
	{
		$parts = explode("|", $package);
		$table = $parts[0];
		$parts2 = explode("^", $parts[1]);
		$whereArr = array();
		for ($i = 0; $i < count($parts2); $i++) {
			$parts3 = explode("`", $parts2[$i]);
			$where = $parts3[0] . "=" . $parts3[1];
			array_push($whereArr, $where);
		}
		$whereImp = "where " . implode(" or ", $whereArr);
		$stmt = "delete from $table $whereImp";
		DB::exec($stmt);
	}
	function lookupentrymod($package)
	{
		$table = $package["lookupentrymod"];
		$tableid = $package["tableid"];
		$tableidname = $table . "id";
		$set = new Set();
		
		if(!empty($tableid)){
			$changeSet = $set->MboSetRemote($table)->setWhere("$tableidname = '$tableid'");
				if(isset($package["data"])) {
					foreach($package["data"] as $colname=>$value) {
						$changeSet = $changeSet->setValue($colname, $value);	
					}
					if($table == "mxsquery"){
						$set2= new Set();
						$mxsquery = $set2->MboSetRemote("mxsquery")->setWhere("mxsqueryid='$tableid'")->MboRemote("appname")->MboRemote("userid")->getMboSet()->getMbo();
						$appname = $mxsquery->getString("appname");
						$userid = $mxsquery->getString("userid");
						$set3 = new Set();
						$set3->MboSetRemote("mxsquery")->setWhere("appname='$appname' and userid='$userid'")->setValue("isdefault", "N")->change();
					}
					if($table == "papersetting" || $table == "papersettingbc"){
						$set2= new Set();
						$tablid = $table."id";
						$papersetting = $set2->MboSetRemote($table)->setWhere("$tablid='$tableid'")->MboRemote("brand")->MboRemote("type")->getMboSet()->getMbo();
						$brand = $papersetting->getString("brand");
						$type = $papersetting->getString("type");
						
						$set3 = new Set();
						$set3->MboSetRemote($table)->setWhere("$tablid>0")->setValue("isdefault", "N")->change();
					}
					$changeSet = $changeSet->change();
					Set::throws(["msg"=>"added"]);
				}else{
					Set::throws(["msg"=>"emptyPackage[data]"]);
				}
			
		}else{
			Set::throws(["msg"=>"emptytableid"]);
		}
		
	}
	public static function entrylookup()
	{
		$super = new Super();
		$super->lookupentry($_POST);
	}
	public static function entrylookupmod()
	{
		$super = new Super();
		$super->lookupentrymod($_POST);;
	}
	
	public static function getApplicationActions()
	{
		$userid = $_POST['actionid'];
		$app = $_POST['app'];
		if($app == "invreserve" )$app = "socialreservation";
		$set = new Set();
		$mxsgroupactionSet = $set->MboSetRemote("mxsgroupaction")->setRelationship("mxsusergroup", "mxsgroupaction.groupnum = mxsusergroup.groupnum ")
		->setRelationship("mxsaction", "mxsgroupaction.actionname = mxsaction.actionname and mxsgroupaction.appname = mxsaction.appname")
		->setWhere("userid='$userid' and mxsgroupaction.appname='$app'")
		->order("mxsaction.sort")
		->MboRemote("mxsgroupaction.actionname")
		->MboRemote("mxsaction.description")
		->MboRemote("mxsaction.sort")
		->getMboSet("remove_duplicate");
		$count = $mxsgroupactionSet->counts();
		$actnameArr = array();
		for ($i = 0; $i < $count; $i++) {
			$mxsgroupaction = $mxsgroupactionSet->getMbo();
			$a = new stdclass();
			$a->actionname = $mxsgroupaction->getString("actionname");
			$a->description = $mxsgroupaction->getString("description");
			array_push($actnameArr, $a);
		}
		Set::throws(["actions"=>$actnameArr]);
		
	}
	function deleteFileUpload($childPackage)
	{
		$conn = DB::getMXConnection();
		$packageParts = explode("|", $childPackage);
		$table = $packageParts[2];
		$childCols = $packageParts[3];
		$parentStmt = "delete from $table ";
		$fileNameArr = array();
		$msg = null;
		$fname = null;
		$childColsArr = explode("^", $childCols);
		$childColslength = count($childColsArr);
		$statement = array();
		for ($i = 0; $i < $childColslength; $i++) {
			$stmt = explode("`", $childColsArr[$i]);
			$subdataLength = count($stmt);
			for ($j = 0; $j < $subdataLength; $j++) {
				if ($j % 2 != 0) {
					array_push($fileNameArr, $stmt[$j]);
					$stmt[$j] = "= '" . $stmt[$j] . "'^";
				}
				else {
					$stmt = $stmt;
				}
				array_push($statement, $stmt[$j]);
			}
		}
		$delStmtImp = implode(" ", $statement);
		$delStmtExp = explode("^", $delStmtImp);
		$delStmtCount = count($delStmtExp);
		$pathToFile = XboSet::getAttachFolder();
		$pathToThumbFile = XboSet::getAttachThumbsFolder();
		for ($i = 0; $i < $delStmtCount - 1; $i++) {
			$delStmt = $parentStmt . " where " . $delStmtExp[$i];
			$result = $conn->query($delStmt);
			$filename = $pathToFile . $fileNameArr[$i];
			$thumbfilename = $pathToThumbFile . "thumb_" . $fileNameArr[$i];
			if (file_exists($filename)) {
				unlink($filename);
				if (file_exists($thumbfilename)) {
					unlink($thumbfilename);
				}
				$msg = "deletesuccess";
				$fname = $fileNameArr[$i];
				
			}
			else {
				$msg = "deletefail";
				$fname = $filename;
			}
		}
		
		Set::throws(["msg"=>$msg, "fname" => $fname]);
		$conn->close();
	}
	public static function filesizeformat($bytes)
	{
		if ($bytes >= 1073741824) {
			return number_format($bytes / 1073741824, 3, '.', '');
		} 
		elseif ($bytes >= 1048576) {
			return number_format($bytes / 1048576, 3, '.', '');
		} 
		elseif ($bytes >= 1024) {
			return number_format($bytes / 1024, 3, '.', '');
		} 
		else {
			return number_format($bytes, 3, '.', '');
		}
	}
	
	
	public static function getAndroidSentLogFolder(){
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$androidsentfolder = $xml->androidsentfolder;
		return $androidsentfolder;
	}
	
	public static function getAndroidErrorLogFolder()

	{
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$androiderrorfolder = $xml->androiderrorfolder;
		return $androiderrorfolder;
	}
	
	public static function getBarcodeUrl($app=null){
		$contextPath = ContextPath::getContextPath();
		$servername = $_SERVER['SERVER_NAME'];
		$serverPort = ":" . $_SERVER['SERVER_PORT'];
		$serverPortVal = "";
		if (!empty($serverPort)) {
			$serverPortVal = $serverPort;
		}
		$fullServerName = ContextPath::getFullHostName() . "/attachments/barcodes/";
		if($app !=null){
			$fullServerName = $fullServerName.$app."/";
		}else{
			$fullServerName = $fullServerName;
		}
		return $fullServerName;
	}
	public static function getBarcodeFolder($app=null)
	{
		
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$folder = $xml->barcodeFolder;
		
		if($app !=null){
			$folder = $xml->barcodeFolder.$app."\\";
		}else{
			$folder = $xml->barcodeFolder."";
		}
		return $folder;
	}
	
	public static function getAttachThumbsFolder()
	{
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$thumbsfolder = $xml->thumbsfolder;
		return $thumbsfolder;
	}
	public static function isMaximoIntegrated(){
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$value = $xml->maximoIntegration;
		return $value;
	}
	public static function getApplicationURL($appname){
		$set= new Set();
		$url = $set->MboSetRemote("mxsapp")->setWhere("appname='$appname'")->MboRemote("url")->getMboSet()->getMbo()->getString("url");
		$fullServerName = ContextPath::getFullHostName() . "/ui/".$url;
		return $fullServerName;
	}

	public static function getAIServer(){
		
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		
        // Load XML (Asumsi Super::loadFile mengembalikan SimpleXMLElement)
		$xml = Super::loadFile($filePath);
		
        // AUTO-SWITCH
        // Cek apakah script berjalan di dalam container Docker?
        if (file_exists('/.dockerenv')) {
            // CASE: DOCKER
            // Mengambil URL: http://mlengine:8000
            $aiServer = (string)$xml->docker_aiServer;
        } else {
            // CASE: WINDOWS / XAMPP
            // Mengambil URL: http://localhost:8000
            $aiServer = (string)$xml->aiServer;
        }

		return $aiServer;
	}
	public static function getAttachFolder()
	{
		$appRoot = dirname(__DIR__, 2);
		$filePath = $appRoot . "/applications/properties/properties.xml";
		$xml = Super::loadFile($filePath);
		$attachFolder = $xml->attachFolder;
		return $attachFolder;
	}
	public static function FixedSearch(){
		$table = $_POST["fxstable"];
		$key = $_POST["fxskey"];
		$value = $_POST["fxsval"];
		$colid = $table."id";
		$set = new Set();
		$tbSet = $set->MboSetRemote($table)->where("$key = ?", $value)->MboRemote("$colid")->getMboSet();
		$tb = $tbSet->getMbo();
		
		$count = $tbSet->counts();
		if($count >0){
			$idval = $tb->getstring("$colid");
			Set::throws(["msg" => "recordfound", "idvalue"=>$idval]);
		}else{
			Set::throws(["msg" => "recordnotfound"]);
		}
		
	}
	public static function deleteParent()
	{		
		
		$parentTable = $_POST["deleteparent"];	
		$parentClauseArr = [];
		for($i=0; $i<count($_POST["keys"]); $i++){
			foreach($_POST["keys"][$i] as $colname=>$value) {
				$columns = "$colname = '$value' ";
				if(empty($value)) {
					$columns = "(" . $colname . $value . " or " . $colname . " is null)";
				}
				array_push($parentClauseArr, $columns);
			}
		}
		$parentClauseImp = implode(" and ", $parentClauseArr);
		
		(new Set())->useTransactionManager()->MboSetRemote($parentTable)->setWhere($parentClauseImp)->remove();
	

		if(isset($_POST["childtable"])){
			$childTables = $_POST["childtable"];
			
			for ($i = 0; $i < count($childTables); $i++) {
				if($childTables[$i] == "legaldoc"){
					$parentClauseImp = str_replace("personid", "ownernum", $parentClauseImp);
				}elseif(empty($childTables[$i])){
					continue;
				}else{
					(new Set())->useTransactionManager()->MboSetRemote($childTables[$i])->setWhere($parentClauseImp)->remove();
				}
				
			}
			
		}
	
		if(isset($_POST["childtable2"])){
			$childTables2 = $_POST["childtable2"];
			
			for ($i = 0; $i < count($childTables2); $i++) {
				if($childTables2[$i] == "legaldoc"){
					$parentClauseImp = str_replace("personid", "ownernum", $parentClauseImp);
				}elseif(empty($childTables2[$i])){
					continue;
				}else{
					(new Set())->useTransactionManager()->MboSetRemote($childTables2[$i])->setWhere($parentClauseImp)->remove();
				}
			}
		}
	
		$set = new Set();
		$mxsappSet = $set->MboSetRemote("mxsapp")->setWhere("appname='$parentTable'")->MboRemote("statustable")->getMboSet();	
		$cntapp = $mxsappSet->counts();
		
		if($cntapp >0){
			$mxsapp = $mxsappSet->getMbo();
			$statustable = $mxsapp->getstring("statustable");
			$set = new Set();
			if(!empty($statustable)) {
				$set->MboSetRemote($statustable)->setWhere($parentClauseImp)->remove();
			}
			
		}
		
		Set::throws(["msg" => "recordDeleted"]);
		
	}
	
	public static function updateTableRowDetail()
	{
		
		$ownership = Super::getParam("ownership");
			$ownerTable = $ownership["xboset"];
			$id = $ownership["xbosetid"];
			$userid = $ownership["userid"];
			
		$childTable = Super::getParam("childtable");
		$modified_data = Super::getParam("modChildPackage");
		$datakey = Super::getParam("datakey");
		
		$set = new Set();
		$tableSet = $set->MboSetRemote($childTable);
		$tableid = $childTable . "id";
			
		$maxtableid = null;
		foreach($modified_data as $key => $value){
			
			$tableSet = $tableSet->setValue($key, $value);
			if($key == $tableid){
				$tableSet->where("$key = ?",$value);
				$maxtableid = $value;
			}
		}
		
		
		
		if($childTable == "invprice"){
			
			$invPriceSet = (new Set())->MboSetRemote($childTable)->where("$tableid = ?", $maxtableid)
			->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("conditiontype")->getMboSet();
			$invPrice = $invPriceSet->getMbo();
			$itemnum = $invPrice->getString("itemnum");
			$warehouse = $invPrice->getString("warehouse");
			$conditiontype = $invPrice->getString("conditiontype");
			
			if(empty($conditiontype)){
				$condval = " and (conditiontype is null or conditiontype='')";
			}else{
				$condval = " and conditiontype ='$conditiontype'";
			}
			(new Set())->MboSetRemote("invprice")->setWhere("itemnum = '$itemnum' and warehouse='$warehouse' $condval")->setValue("defaultprice", "N")->change();
		}

		$tableSet->change();

		
			
		if($maxtableid != null ){
			Xbo::getLastChanged($childTable, $tableid, $maxtableid);
		}
		self::sendJsonResponse("success", "Data has been updated");
	}
	public static function showPrevRecord()
	{
		$showprevrec = $_GET['showprevrec'];
		$pts = explode("|", $showprevrec);
		$ids = explode("-", $pts[1]);
		$showprevrec2 = $pts[0];
		$PackagePart = explode("^", $showprevrec2);
		$XboSet = $PackagePart[0];
		$currentID = $PackagePart[1];
		$XboSetID = $XboSet . "id";
		$set = new Set();
		$res = $set->getAllColumns($XboSet);
		$attrResult = $res->result;
		$columnname = $res->columnname;
		$columnNameArray = array();
		$countResult = $attrResult->counts();
		for ($i = 0; $i < $countResult; $i++) {
			$data = $attrResult->getMbo();
			$column = $data->getString($columnname);
			array_push($columnNameArray, $column);
		}
		$allColumns = implode(",", $columnNameArray);
		rsort($ids);
		$prevval = null;
		for ($i = 0; $i < count($ids); $i++) {
			if ($ids[$i] < $currentID) {
				$prevval = $ids[$i];
				break;
			}
		}
		if (empty($prevval)) {
			Set::throws(["msg" => "stopprevrecord"]);
			return null;
		}
		else {
			$set = new Set();
			$xbosetSet = $set->MboSetRemote($XboSet)->setWhere("$XboSetID = " . $prevval);
			for ($i = 0; $i < count($columnNameArray); $i++) {
				$xbosetSet = $xbosetSet->MboRemote($columnNameArray[$i]);
			}
			$xbosetSet = $xbosetSet->getMboSet();
			$countResult = $xbosetSet->counts();
			$jsonArr = array();
			if ($countResult > 0) {
				for ($i = 0; $i < $countResult; $i++) {
					$prevData = $xbosetSet->getMbo();
					for ($j = 0; $j < count($columnNameArray); $j++) {
						$value = $prevData->getString($columnNameArray[$j]);
						$dQuote = preg_match('/"/', $prevData->getString($columnNameArray[$j]));
						if (strpos($dQuote, '1') !== false) {
							$jsonChar = "\"$columnNameArray[$j]\":\"" . addslashes($prevData->getString($columnNameArray[$j])) . "\"";
						}
						else {
							$jsonChar = "\"$columnNameArray[$j]\":\"" . $value . "\"";
						}
						array_push($jsonArr, $jsonChar);
					}
				}
				$jsonImp = implode(",", $jsonArr);
				$jsonEncode = "{" . $jsonImp . "}";
				echo $jsonEncode;
			}
			else {
				Set::throws(["msg" => "stopprevrecord"]);
			}
		}
	}
	public static function showNextRecord()
	{
		$shownextrec = $_GET['shownextrec'];
		$pts = explode("|", $shownextrec);
		$shownextrec2 = $pts[0];
		$ids = explode("-", $pts[1]);
		$PackagePart = explode("^", $shownextrec2);
		$XboSet = $PackagePart[0];
		$currentID = $PackagePart[1];
		$XboSetID = $XboSet . "id";
		$set = new Set();
		$res = $set->getAllColumns($XboSet);
		$attrResult = $res->result;
		$columnname = $res->columnname;
		$columnNameArray = array();
		$countResult = $attrResult->counts();
		$columnNameArray = array();
		for ($i = 0; $i < $countResult; $i++) {
			$data = $attrResult->getMbo();
			$column = $data->getString($columnname);
			array_push($columnNameArray, $column);
		}
		$allColumns = implode(",", $columnNameArray);
		asort($ids);
		$nextval = null;
		sort($ids);
		for ($i = 0; $i < count($ids); $i++) {
			if ($ids[$i] > $currentID) {
				$nextval = $ids[$i];
				break;
			}
		}
		if (empty($nextval)) {
			Set::throws(["msg" => "stopnextrecord"]);
			return null;
		}
		else {
			$set = new Set();
			$xbosetSet = $set->MboSetRemote($XboSet)->setWhere("$XboSetID = " . $nextval);
			for ($i = 0; $i < count($columnNameArray); $i++) {
				$xbosetSet = $xbosetSet->MboRemote($columnNameArray[$i]);
			}
			$xbosetSet = $xbosetSet->getMboSet();
			$countResult = $xbosetSet->counts();
			$jsonArr = array();
			$jsonChar = "";
			if ($countResult > 0) {
				for ($i = 0; $i < $countResult; $i++) {
					$nextData = $xbosetSet->getMbo();
					for ($j = 0; $j < count($columnNameArray); $j++) {
						$value = $nextData->getString($columnNameArray[$j]);
						$dQuote = preg_match('/"/', $value);
						if (strpos($dQuote, '1') !== false) {
							$jsonChar = "\"$columnNameArray[$j]\":\"" . addslashes($value) . "\"";
						}
						else {
							$jsonChar = "\"$columnNameArray[$j]\":\"" . $value . "\"";
						}
						array_push($jsonArr, $jsonChar);
					}
				}
				$jsonImp = implode(",", $jsonArr);
				$jsonEncode = "{" . $jsonImp . "}";
				echo $jsonEncode;
			}
			else {
				Set::throws(["msg" => "stopnextrecord"]);
			}
		}
	}
	
	public static function sendJsonResponse($status, $message, $data = []) {
		
		$response = ['status' => $status, 'message' => $message];
		if (!empty($data)) {
			$response['data'] = $data;
		}
		echo json_encode($response);
		
		exit();
	}
	
	
	public static function deleteChildren($isPrint = false, $param=null)
	{
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$data = Super::getParam();
			$delChildPackage = $data['delChildPackage'] ?? $param;
			
			if (empty($delChildPackage['selectedRows']) || empty($delChildPackage['ownership']['owner'])) {
				//Special case for invcart deletion
				if($delChildPackage["recOwner"] == "invcart" && $delChildPackage["datasetChild"] == "invcart"){
					$tablename = $delChildPackage["recOwner"];
					$selectedRows = $delChildPackage["selectedRows"];
					$tablenameid = $tablename."id";
					
					$ids= [];
					foreach($selectedRows as $key=>$row){

						$ids[] = $row[$tablenameid];
					}

					(new Set())->useTransactionManager()
						->MboSetRemote($tablename)
						->whereIn($tablenameid, $ids)
						->remove();

					$tm->commit();
					if($isPrint == false){
						self::sendJsonResponse("success", "The data has been deleted");
					}else{
						return true;
					}
				}else{
					throw new InvalidArgumentException("Invalid input data or no row/owner selected.");
				}
				
			}
			
			// Langkah 2: Ekstrak dan Sanitasi Informasi Kunci
			$ownership = $delChildPackage['ownership'];
			$childTable = self::sanitizeIdentifier($delChildPackage['datasetChild'] ?? '');
			
			if ($childTable == "legaldoc") {
				if (isset($ownership['owner'][0]['vendorcode'])) {
					$ownership['owner'][0]['ownernum'] = $ownership['owner'][0]['vendorcode'];
					unset($ownership['owner'][0]['vendorcode']);
				}

				if (isset($ownership['owner'][0]['ponum'])) {
					$ownership['owner'][0]['ownernum'] = $ownership['owner'][0]['ponum'];
					unset($ownership['owner'][0]['ponum']);
				}

				if (isset($ownership['owner'][0]['personid'])) {
					$ownership['owner'][0]['ownernum'] = $ownership['owner'][0]['personid'];
					unset($ownership['owner'][0]['personid']);
				}

			}
			if ($childTable == "mxsairelations") {
				if (isset($ownership['owner'][0]['domainname'])) {
					$ownership['owner'][0]['parentdomain'] = $ownership['owner'][0]['domainname'];
					unset($ownership['owner'][0]['domainname']);
				}
			}

			// Dapatkan klausa WHERE untuk OWNER secara dinamis dan aman
			list($ownerWhereClause, $ownerParams) = self::buildOwnerClause($ownership['owner']);
			
			// Dapatkan ID dari semua baris yang dipilih
			list($childPrimaryKey, $selectedIds) = self::extractSelectedIds($delChildPackage['selectedRows']);

			if (empty($childTable) || empty($ownerWhereClause) || empty($childPrimaryKey) || empty($selectedIds)) {
				throw new InvalidArgumentException("Required information (table/owner/primary key) is incomplete.");
			}

			// Langkah 3: Handle penghapusan Sub-Children 
			$delSubChildPackage = $data['delSubChildPackage'] ?? null;
			if (!empty($delSubChildPackage['subDataChild']) && !empty($delSubChildPackage['primary_key'])) {
				
				$subPrimaryKey = self::sanitizeIdentifier($delSubChildPackage['primary_key']);
				
				// SELECT untuk mendapatkan nilai foreign key (bagian ini sudah benar)
				$foreignKeyResults = (new Set())->useTransactionManager()
					->MboSetRemote($childTable)
					->where("$childPrimaryKey IN (" . rtrim(str_repeat('?,', count($selectedIds)), ',') . ") AND $ownerWhereClause", ...array_merge($selectedIds, $ownerParams))
					->MboRemote($subPrimaryKey)
					->getMboSet();
				
				$foreignKeyValues = [];
				while ($row = $foreignKeyResults->fetch()) {
					if (!empty($row[$subPrimaryKey])) {
						$foreignKeyValues[] = $row[$subPrimaryKey];
					}
				}

				if (!empty($foreignKeyValues)) {
					$uniqueForeignKeyValues = array_unique($foreignKeyValues);
					$placeholders = rtrim(str_repeat('?,', count($uniqueForeignKeyValues)), ',');

					// Gabungkan parameter untuk kueri DELETE sub-child
					$subDeleteParams = array_merge($uniqueForeignKeyValues, $ownerParams);

					foreach ($delSubChildPackage['subDataChild'] as $subTableName) {
						$subTable = self::sanitizeIdentifier($subTableName);
						
						// Hapus semua sub-children DENGAN menyertakan klausa owner
						(new Set())->useTransactionManager()
							->MboSetRemote($subTable)
							->where("$subPrimaryKey IN ($placeholders) AND $ownerWhereClause", ...$subDeleteParams)
							->remove();
					}
				}
			}

			// Langkah 4: Hapus data Children dalam SATU KALI kueri DELETE ... WHERE IN.
			$set = (new Set())->useTransactionManager();
			$placeholders = rtrim(str_repeat('?,', count($selectedIds)), ',');
			
			// Gabungkan semua parameter: ID terpilih + parameter owner
			$params = array_merge($selectedIds, $ownerParams);
			
			$set->MboSetRemote($childTable)
				->where("$childPrimaryKey IN ($placeholders) AND $ownerWhereClause", ...$params)
				->remove();
			
			$tm->commit();

			if($isPrint == false){
				self::sendJsonResponse("success", "The data has been deleted");
			}else{
				return true;
			}
			

		} catch (Exception $e) {
			$tm->rollback();
			self::sendJsonResponse("error", "Failed to delete the data: " . $e->getMessage());
		}
	}
	public static function sanitizeIdentifier(string $identifier): string
	{
		
		return preg_replace('/[^a-zA-Z0-9_]/', '', $identifier);
	}
	
	private static function buildOwnerClause(array $ownerData): array
	{
		if (empty($ownerData)) return ['', []];
		
		$conditions = [];
		$params = [];

		foreach ($ownerData[0] as $column => $value) {
			
			$conditions[] = self::sanitizeIdentifier($column) . " = ?";
			$params[] = $value;
		}
		return [implode(' AND ', $conditions), $params];
	}

	
	private static function extractSelectedIds(array $selectedRows): array
	{
		if (empty($selectedRows)) return ['', []];
		
		$firstRow = $selectedRows[0];
		$primaryKeyName = self::sanitizeIdentifier(array_key_first($firstRow));
		$ids = array_column($selectedRows, $primaryKeyName);
		
		return [$primaryKeyName, $ids];
	}
	
	private static function fetchRestrictions($tableName, $columnsToSelect, $mapToFields, $userId, $appName)
	{
		$set = new Set();
		$resultSet = $set->MboSetRemote($tableName)
			->setRelationship("mxsusergroup", "$tableName.groupnum=mxsusergroup.groupnum")
			->where("mxsusergroup.userid = ? AND $tableName.appname = ?", $userId, $appName)
			->MboRemote(implode(", ", $columnsToSelect))
			->getMboSet();

		$restrictions = [];
		while ($row = $resultSet->fetch()) {
			$restriction = new StdClass();
			$restriction->name = $row[$mapToFields['name']];
			$restriction->restricttype = $row[$mapToFields['type']];
			$restrictions[] = $restriction;
		}
		return $restrictions;
	}
	
	public static function Auth()
	{
		header('Content-Type: application/json');

		try {
			$payload = Super::getParam("sapxhr");
			
			if (empty($payload)) {
				throw new InvalidArgumentException("Authorization Payload Not Found");
			}
			
			$userId = $payload["username"];
			$appName = $payload["objectname"];

			$set = new Set();
			$mainAuthSet = $set->MboSetRemote("mxsgroupline")
				->setRelationship("mxsusergroup", "mxsgroupline.groupnum=mxsusergroup.groupnum")
				->where("mxsusergroup.userid = ? AND mxsgroupline.appname = ?", $userId, $appName)
				->MboRemote("MAX(CASE WHEN mxsgroupline.canread = 'Y' THEN 'Y' ELSE 'N' END) as can_read")
				->MboRemote("MAX(CASE WHEN mxsgroupline.cansave = 'Y' THEN 'Y' ELSE 'N' END) as can_save")
				->getMboSet();
			
			$authResult = $mainAuthSet->fetch();
			
			$canRead = $authResult['can_read'] ?? 'N';
			$canSave = $authResult['can_save'] ?? 'N';
			
			$columnRestrictions = self::fetchRestrictions(
				"mxsgroupcolres", 
				['attributename', 'restricttype'], 
				['name' => 'attributename', 'type' => 'restricttype'],
				$userId, 
				$appName
			);

			$layoutRestrictions = self::fetchRestrictions(
				"mxslayoutres", 
				['idname', 'restricttype'], 
				['name' => 'idname', 'type' => 'restricttype'],
				$userId, 
				$appName
			);

			// Langkah 5: Kirim respons JSON yang terstruktur.
			Set::throws([
				"cr" => $canRead, 
				"cs" => $canSave, 
				"restrict" => $columnRestrictions, 
				"restrict2" => $layoutRestrictions
			]);

		} catch (Exception $e) {
			http_response_code(500);
			Set::throws([
				'error' => true,
				'message' => 'Authorization Failed: ' . $e->getMessage()
			]);
		}
	}

	
	
	public static function validate()

	{
		//global $XboSetName;
		
		$conn = DB::getMXConnection();
		$data = self::getCurrentSS();
		$XboSet = $data->objectname;
		$clause = $data->clause;
		
		$stmt = "select * from " . $XboSet . " where " . $clause;
		
		
		return $stmt;
	}
	public static function changeChildren()

	{
		
		global $ownerTable;
		global $ownerCols;
		global $childTable;
		global $childCols;

		$ownerColsLength = count($ownerCols);
		$clauseOwnerArr = array();
		
		if (is_array($ownerCols)) {
			$ownerColsLength = count($ownerCols);
			for ($h = 0; $h < $ownerColsLength; $h++) {
				$ownerColsPart = explode("`", $ownerCols[$h]);
				if (count($ownerColsPart) >= 2) {
					$ownerColName = $ownerColsPart[0];
					$ownerColVal = $ownerColsPart[1];
					$clauseOwnerSQL = $ownerColName . "='" . $ownerColVal . "' ";
					array_push($clauseOwnerArr, $clauseOwnerSQL);
				} else {
					error_log("Invalid ownerCols format: " . $ownerCols[$h]);
					continue; 
				}
			}
		} else {
			$ownerColsPart = explode("`", $ownerCols);
			if (count($ownerColsPart) >= 2) {
					$ownerColName = $ownerColsPart[0];
					$ownerColVal = $ownerColsPart[1];
					$clauseOwnerSQL = $ownerColName . "='" . $ownerColVal . "' ";
					array_push($clauseOwnerArr, $clauseOwnerSQL);
				} else {
					error_log("Invalid ownerCols format: " . $ownerCols[$h]);
				}
		}
		
		$clauseChildrenArr = array();
		foreach($childCols as $colname=>$value) {
			$childrenColName = $colname;
			$childrenColVal = $value;
			$clauseChildrenSQL = $childrenColName . "='" . $childrenColVal . "' ";
			array_push($clauseChildrenArr, $clauseChildrenSQL);
		}
		
		
		$clauseOwner = implode(" and ", $clauseOwnerArr);
		
		$clauseChildren = implode(" and ", $clauseChildrenArr);
		$clause = $clauseOwner . " and " . $clauseChildren;
		$colname = [];
		$colvalue = [];
		foreach($childCols as $col=>$val) {
			array_push($colname, $col);
			array_push($colvalue, $val);
		}
		
		$set = new Set();
		
		$stmt = $set->MboSetRemote($childTable)->setWhere($clause);
		for ($i = 0; $i < count($colname); $i++) {
			$stmt = $stmt->setValue($colname[$i], $colvalue[$i]);
		}
		
		$stmt = $stmt->change();
	}
	public static function add()
	{
		
		$payload = Super::getParam();
		$ownerTable = $payload["package"];
		$data = $payload["mbovalues"];
		
		$set = new Set();
		$addSet = $set->MboSetRemote($ownerTable);
		foreach($data as $key=>$value){
			$addSet = $addSet->setValue($key, $value);
		}
		$addSet->add();
		
		
		$tableID = $addSet->getTableID;
		Super::addStatus($ownerTable, $tableID);
		
		self::sendJsonResponse("success", "Data has been added", ["tableid"=>$tableID]);
		
	}
	

	public static function changes()

	{
		
		$stmt = null;
		global $package;
		$package = "";
		if (isset($_POST["package"])) $package = $_POST["package"];

		$data = self::getCurrentSS();

		$XboSet = $data->objectname;
		$clause = $data->clause;
		
		$dataPackage = $_POST["mbovalues"];
		$value = null;
		$tempValue = null;
		$set = new Set();
		$updSet = $set->MboSetRemote($XboSet)->setWhere($clause);
		foreach($dataPackage as $colname=>$value) {
			$updSet = $updSet->setValue($colname, $value);
		}
		
		

		$updSet->change();
		Super::sendJsonResponse("success", "Data has been updated");
	}

	public static function addStatus($XboSetName, $maxtableid2=null, $keynum = null)

	{
		$set = new Set();
		$mxsattrvalueSet = $set->MboSetRemote("mxsattrvalue")->setWhere("app = '$XboSetName' and statuslevel=1 and type='status'")->MboRemote("value")->MboRemote("keyattr")->getMboSet();
		
		if($mxsattrvalueSet->isEmpty()) return;
		
		$mxsattrvalue = $mxsattrvalueSet->getMbo();
		$status = $mxsattrvalue->getString("value");
		$key = $mxsattrvalue->getString("keyattr");
	
		if (!empty($status)) {
			$status = $status;
			$set2 = new Set();

			(new Set())->useTransactionManager()
			->MboSetRemote($XboSetName)
			->where($XboSetName."id=?", $maxtableid2)
			->getMboValue("statusdate");

			
			if(empty($statusdate)){
				$statusdate = Xbo::setEnterDate();
			}
			
			$changedate = $statusdate;
			$ownerCol = $key;
			$statusTable = $XboSetName . "status";
			$currentUser = Xbo::getLoginUser();
			$keyValue = Xbo::getString($key);
			if(empty($keyValue)){
				$keyValue = $keynum;
			}
			$keyValue2 = "'".$keyValue."'";
			
			$set2 = new Set();
			$set2->MboSetRemote($statusTable)->setValue("status", $status)->setValue("changedate", $changedate)->setValue($key, $keyValue)->setValue("changeby", $currentUser)->add();
			$set2 = new Set();
			$set2->MboSetRemote($XboSetName)->setWhere($key . "=" . $keyValue2)->setValue("changeby", $currentUser)->setValue("changedate", $changedate)->setValue("status", $status)->setValue("statusdate", $changedate)->change();
		}
	}
	
	public static function addChildren($payload)

	{
		
		$payload = Super::getParam();
		
		$ownership = $payload["ownership"];
		$childrenTable = $payload["childrentable"];
		$ownerTable = $ownership["xboset"];
		$ownerColumns = $ownership["owner"];
		
		$set = new Set();
		$set->useTransactionManager()->MboSetRemote($childrenTable);
		
		foreach($ownerColumns as $row){
			foreach($row as $key => $value){
				if($childrenTable == "legaldoc"){
					if($key == "vendorcode" || $key == "personid" || $key == "ponum"){
						$key = "ownernum";
					}
				}
				if($childrenTable == "mxsairelations"){
					if($key == "domainname"){
						$key = "parentdomain";
					}
				}

				$set->setValue($key, $value);
			}
			
		}
		$data = $payload["childPackage"];
		foreach($data as $key=>$value){
			$set->setValue($key, $value);
		}
		if($childrenTable == "legaldoc"){
			$set->setValue("ownertable", $ownerTable);
		}
		$set->add();
		self::sendJsonResponse("success", "Data Has been added");
		
	}
	
	public static function validateChildren()
	{
		if (!isset($_POST["ownership"])) return;
		try {
			$payload = Super::getParam();
			if (empty($payload["ownership"]) || empty($payload["childPackage"]) || empty($payload["childrentable"])) {
				throw new InvalidArgumentException("Payload is not complete.");
			}

			$ownership = $payload["ownership"];
			$ownerTable = self::sanitizeIdentifier($ownership["xboset"]);
			$ownerCols = $ownership["owner"];
			
			$childData = $payload["childPackage"];
			$childTable = self::sanitizeIdentifier($payload["childrentable"]);
			
			$dbType = (new dbo())->dbtype();

			$conditions = [];
			$params = [];

			// 1. Ambil kondisi dari Owner (Domain)
			foreach ($ownerCols as $row) {
				foreach ($row as $key => $value) {
					$dataType = Set::getDataType($ownerTable, self::sanitizeIdentifier($key));
					if($childTable == "mxsairelations"){
						if($key == "domainname" ) $key = "parentdomain";
					}
				
					list($condition, $param) = self::buildCondition($key, $value, $dataType, $dbType);
					$conditions[] = $condition;
					$params[] = $param;
				}
			}

			// 2. Ambil kondisi dari Child (HANYA KOLOM IDENTIFIER)
			// Jangan masukkan sqltext atau description karena bisa mengandung karakter '?' 
			// yang merusak parser placeholder dan membuat kueri berat.
			foreach ($childData as $key => $value) {
				// SKIP kolom non-identifier untuk pengecekan duplikasi
				if (in_array($key, ['sqltext', 'logicinstruction', 'description', 'requiredparams'])) {
					continue;
				}

				// HANDLE jika value adalah array (seperti parammeta)
				if (is_array($value)) {
					$value = implode(',', $value); // Ubah ["a","b"] jadi "a,b"
				}

				
				$dataType = Set::getDataType($childTable, self::sanitizeIdentifier($key));
				list($condition, $param) = self::buildCondition($key, $value, $dataType, $dbType);
				$conditions[] = $condition;
				$params[] = $param;
			}

			if (empty($conditions)) {
				throw new LogicException("No Condition for validation");
			}

			$whereClause = implode(" AND ", $conditions);
			
			$set = new Set();
			$childTableSet = $set->MboSetRemote($childTable)
								->where($whereClause, ...$params)
								->getMboSet();
			
			// Pastikan $childTableSet adalah object sebelum panggil isEmpty
			if ($childTableSet instanceof Set) {
				if ($childTableSet->isEmpty()) {
					Super::addChildren($payload);
				} else {
					Super::changeChildren($payload);
				}
			} else {
				throw new Exception("Database query failed to return a valid result set.");
			}

		} catch (Exception $e) {
			self::sendJsonResponse("error", "Validation Failed: " . $e->getMessage());
		}
	}
	
	
	private static function buildCondition(string $column, $value, ?string $dataType, string $dbType): array
	{
		$sanitizedColumn = self::sanitizeIdentifier($column);

		// Jika tipe data adalah tanggal atau datetime
		if (in_array(strtolower($dataType), ['date', 'datetime', 'timestamp'])) {
			try {
				$dateObj = new DateTime($value);
			} catch (Exception $e) {
				return ["$sanitizedColumn = ?", $value];
			}

			if ($dbType === 'oracle') {
			
				if (strpos($value, ':') !== false) { 
					$condition = "TRUNC($sanitizedColumn) = TO_DATE(?, 'YYYY-MM-DD')";
					$param = $dateObj->format('Y-m-d');
				} else {
					$condition = "$sanitizedColumn = TO_DATE(?, 'YYYY-MM-DD HH24:MI:SS')";
					$param = $dateObj->format('Y-m-d H:i:s');
				}
				 return [$condition, $param];
			} 
			elseif ($dbType === 'sqlserver') {
				 // Untuk SQL Server, pakai CAST
				if (strpos($value, ':') !== false) {
					 $condition = "$sanitizedColumn = CAST(? AS DATETIME)";
				} else {
					 $condition = "CAST($sanitizedColumn AS DATE) = CAST(? AS DATE)";
				}
				return [$condition, $value];
			}
			
		}

		return ["$sanitizedColumn = ?", $value];
	}


	public static function showTableRowDetail()
	{
		
		header('Content-Type: application/json');

		try {
			
			//if (!isset($_POST['showChildPackage'], $_POST['tdID'], $_POST['detailDataAttr'])) {
			if (!isset($_POST['showChildPackage'], $_POST['tdID'])) {
				throw new InvalidArgumentException("Uncomplete Payload: showChildPackage or tdID is missing");
			}

		
			$tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['showChildPackage']);
			$tableId = $_POST['tdID']; 
			
			$requestedColumns = $_POST['detailDataAttr'];
			$sanitizedColumns = [];
			foreach ($requestedColumns as $column) {
				$sanitizedColumns[] = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
			}

			$idColumnName = $tableName . "id";
			if (!in_array($idColumnName, $sanitizedColumns)) {
				$sanitizedColumns[] = $idColumnName;
			}

			if (empty($tableName)) {
				throw new InvalidArgumentException("Table Name is Not Valid");
			}

			$set = new Set();
			$tableSet = $set->MboSetRemote($tableName)
						   ->where("$idColumnName = ?", $tableId)
						   ->MboRemote(implode(", ", $sanitizedColumns));
			$result = $tableSet->getMboSet();
			
			$data = $result->fetch();
			if ($data) {
				echo json_encode($data);
			} else {
				echo json_encode(['error' => true, 'message' => 'Data Not Found']);
			}

		} catch (Exception $e) {
			echo json_encode([
				'error' => true,
				'message' => 'Error occured : ' . $e->getMessage()
			]);
		}
	}
		
		
	
	public static function getOwner()
	{
		$clause_array = [];
		$parentObject = Super::getParam("xboset");
		if(Super::hasParam("childObject")){
			$childObject = Super::getParam("childObject");
		}else{
			$data = Super::getCurrentSS();
			if($data){
				$XboSet = $data->objectname;
				$childObject = $XboSet;
			}else{
				$childObject = Super::getParam("childObject");
			}
			
		}
		
		if (Super::hasParam("linktable")) {
			$linktable   = Super::getParam("linktable");
			$data        = $linktable["getOwner"] ?? [];
			$child_fk    = $linktable["child_fk"] ?? null;
			$child_value = $linktable["child_value"] ?? null;
		} else {
			$data        = Super::getParam("getOwner") ?? [];
			$child_fk    = Super::getParam("child_fk") ?? null;
			$child_value = Super::getParam("child_value") ?? null;
		}

		foreach ($data as $row) {
			$key = addslashes($row['key']);
			$val = addslashes($row['value']);
			$clause_array[] = "$childObject.$key = '$val'";
		}

		if (!empty($child_fk) && !empty($child_value)) {
			$clause_array[] = "$childObject.$child_fk = '$child_value'";
		}
			
		$clause = implode(" AND ", $clause_array);
		
		return $clause;
	}
	public static function hasParam($param) {
		return isset($_POST[$param]) || isset($_GET[$param]);
	}

	public static function getParam($param = null) {
		
		if ($param === null) {
			
			return $_POST + $_GET;
		}

		return $_POST[$param] ?? $_GET[$param] ?? null;
	}
	public static function childColumnsRelationship()
	{
		$cax = $_POST['childRelColumn'] ?? [];
		$cnoArr = [];

		foreach ($cax as $childRelColumnRaw) {
			$childRelColumn = urldecode($childRelColumnRaw);
			$parts = explode("|", $childRelColumn);

			if (count($parts) < 4) {
				continue; // Skip jika format tidak valid
			}

			[$colname, $table, $ownerRaw, $ownerTable] = $parts;
			$parts2 = explode("`", $ownerRaw);

			$colowner = $parts2[0];
			if ($colowner === "employeesid") $colowner = "personid";
			if ($colowner === "tonodeline")  $colowner = "nodeline";

			$colown = "{$ownerTable}.{$colname}";

			$criteria = [];
			if (strpos($ownerRaw, '^') !== false) {
				foreach (explode("^", $ownerRaw) as $filter) {
					[$key, $value] = explode('`', $filter, 2);
					$criteria[$key] = $value;
				}
			} else {
				$criteria[$colowner] = $parts2[1] ?? null;
			}

			// Ambil nilai kolom
			$set = new Set();
			$nilaiKolom = $set->fetchOneField($table, $colname, $criteria);

			// Simpan ke array hasil
			$cnoArr[] = (object)[
				'cno'   => $colown,
				'value' => $nilaiKolom
			];
		}

		Set::throws($cnoArr);
	}
	
	public static function setTableKeyValue($key, $value){
		self::$tablekey = $key;
		self::$tablekeyvalue = $value;
		
	}
	
	
	public static function showDetail()

	{
		$db = new DB();
		$set = new Set();
		$dbtype = $db->getDBProperties()->dbtype;
		
		$package = Super::getParam("package");
		$columnName = $package["key"];
		$columnValue = $package["value"];
		
		
		self::setTableKeyValue($columnName, $columnValue);
	
		$data = self::getCurrentSS();
		$XboSet = $data->objectname;
		$clause = $data->clause;
		
		if(empty($clause)){
			$id = $columnValue;
			$clause = $XboSet."id = '$columnValue' ";
		}
		
		
		$set = new Set();
		$columnNameArray = $set->getAllColumnNamesAsArray($XboSet);
		
		$set = new Set();

		$resultSet = $set->MboSetRemote($XboSet)
						 ->setWhere($clause)
						 ->limit(1) 
						 ->getMboSet();

		$dataRow = $resultSet->fetch();

		if ($dataRow) {
			$outputData = [];
			foreach ($dataRow as $colname => $value) {
				
				if (strpos($colname, "date") !== false && !empty($value)) {
					try {
						$date = new DateTime($value);
						$outputData[$colname] = $date->format("Y-m-d H:i");
					} catch (Exception $e) {
						$outputData[$colname] = $value;
					}
				} else {
					$outputData[$colname] = $value;
				}
			}

			header('Content-Type: application/json'); 
			echo json_encode($outputData, JSON_PRETTY_PRINT); 

		} else {
			header('Content-Type: application/json');
			self::sendJsonResponse("error", "ID Record Not Found while querying <b>$XboSet</b> object.<br> Check your showDetail Class.");
		}
	}
	
	public static function select($col, $val, $datatype=null){
		global $columnStack;
		if ($datatype !== null) {
			$datatype = "data-type=\"" . $datatype . "\"";
			$columnStack[] = $col . "`" . $val . "|" . $datatype;
		}
		else {
			$columnStack[] = $col . "`" . $val;
		}
		return $columnStack;
	}
	public static function displayData($join = "", $distinct = "")
	{
		
		
		
		global $columnStack;
		global $dtype;
		global $errorexec;
		global $getnewid;
		global $partslinktable;
		global $whereClauseGlobal;
		global $joinGlobal;	
		
		$customcol = null;
		$customcolas = null;
		$countdot3r = [];
		
		
		$data = self::getCurrentSS();

		
		$XboSet = $data->objectname;
		$clause = $data->clause;
		//curbal
		if($whereClauseGlobal != null){
			$clause = $whereClauseGlobal;
		}
		
		if (strpos($clause??"", "_PLS_") !== false) {
			$tempValue = str_replace("_PLS_", "+", $clause);
			$clause = $tempValue;
		}
		$cls = $clause;		
		$clsr = [];
		
		$sitecol_exist = false; 

		if ($XboSet != "site") {
			$resultSetObject = (new Set())->getAllColumns($XboSet);
			
			$allColumns = $resultSetObject->fetchFields();
			
			if (in_array('sitecode', array_map('strtolower', $allColumns))) {
				$sitecol_exist = true;
			}
		}
		
		
		if (!empty($partslinktable)) {
			$countplt = count($partslinktable);
			for ($i = 1; $i < $countplt; $i++) {
				$colvalue =null;
				$clauseplt = $XboSet . "." . $partslinktable[$i];
				$plts = explode("`", $clauseplt);
				$colname = $plts[0];
				$valname = "'" . $plts[1] . "'";
				if(!empty($plts[1])){
					$colvalue = $colname . "=" . $valname;
					array_push($clsr, $colvalue);
				}
			}
			$clause = implode(" and ", $clsr);
		}
		
		//----
		if ($sitecol_exist == true) {
			$username = Xbo::getLoginUser();
		
			$arrsite = [];

			$siteSet = (new Set())->useTransactionManager()->MboSetRemote("mxsusergroup")
				->setRelationship("mxsgroupsite", "mxsusergroup.groupnum = mxsgroupsite.groupnum")
				->where("mxsusergroup.userid = ?", $username)
				->MboRemote("mxsgroupsite.sitecode")
				->getMboSet("distinct"); 
				while ($row = $siteSet->fetch()) {
					if (!empty($row['sitecode'])) {
						$arrsite[] = $row['sitecode'];
					}
				}
			
			if (!empty($arrsite)) {
				
				$siteList = "'" . implode("', '", $arrsite) . "'";
				$joinsiteclause = "(" . $XboSet . ".sitecode IN (" . $siteList . "))";
				if (empty($clause)) {
					$clause = $joinsiteclause;
				} else {
					$clause .= " AND " . $joinsiteclause;
				}
			}
		}
		
		
		$XboSetID = $XboSet . "id";
		
		if ($distinct != "distinct") {
			Super::select($XboSetID, $XboSetID);
		}
		
		$countColumnStack = count($columnStack);
		
		$columnName = array();
		$joinColumn = array();
		$colstackarr = array();
		$inputr = array();
		for ($h = 0; $h < $countColumnStack; $h++) {
			$columnNameExplode = explode("`", $columnStack[$h]);
			$columnNameStack = $columnNameExplode[1];
			array_push($colstackarr, $columnNameStack);
			$findStack = "|";
			$dataType = "";
			if (strpos($columnNameStack, $findStack) !== false) {
				
				$columnNameStackSt = explode("|", $columnNameStack);
				$columnNameStack = $columnNameStackSt[0];
				$dataType = $columnNameStack[1];
			}
			$findDot = ".";
			$pos = strpos($columnNameStack, $findDot);
			if ($pos === false) {
				if(strpos($columnNameStack, "_input_")!== false){
					$columnNameStack = $columnNameStack;					
				}else{
					$columnNameStack = $XboSet . "." . $columnNameStack;
					if ($columnNameStack == "item.curbal") {
					
					$customcol = "(" . (new Set())->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum=item.itemnum")->MboRemote("sum(curbal)")->getMboSet("stacked")->sql . ")";
					$columnNameStack = $customcol . " as curbal";
					$customcolas = $columnNameStack;
					}
					if ($columnNameStack == "inventory.curbal") {
					
						$customcol = "(" . (new Set())->useTransactionManager()->MboSetRemote("v_invbalance")->setWhere("itemnum=inventory.itemnum and inventory.warehouse = v_invbalance.warehouse")
						->MboRemote("sum(curbal)")->getMboSet("stacked")->sql . ")";
						$columnNameStack = $customcol . " as curbal";
						$customcolas = $columnNameStack;
					}
					if ($columnNameStack == "customer.balance") {
					
						$customcol = "(" . (new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("customercode = customer.customercode and orgcode = customer.orgcode and sitecode = customer.sitecode")->MboRemote("sum(unpaidprice) as unpaidprice")->getMboSet("stacked")->sql . ")";
						$columnNameStack = $customcol . " as balance";
						$customcolas = $columnNameStack;
					}
					if ($columnNameStack == "vendor.balance") {
				
						$customcol = "(" . (new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("vendor = vendor.vendorcode")->MboRemote("sum(unpaidcost) as unpaidcost")->getMboSet("stacked")->sql . ")";
						$columnNameStack = $customcol . " as balance";
						$customcolas = $columnNameStack;
					}
					
				}
				
				array_push($columnName, $columnNameStack);
			}
			else {
				$dot3 = explode(".", $columnNameStack);
				$countdot3 = count($dot3);
				if ($countdot3 == 3) {
					array_push($countdot3r, $columnNameStack);
				}
				else {
					$columnNameStack = $columnNameStack;
				}
				array_push($joinColumn, $columnNameStack);
			}
		}
		
		$joinColumn2r = [];
		if (!empty($countdot3r)) {
			for ($i = 0; $i < count($countdot3r); $i++) {
				$coln = $countdot3r[$i];
				$parts3 = explode(".", $coln);
				$tabcol = $parts3[0] . "." . $parts3[2];
				array_push($joinColumn2r, $tabcol);
				deleteElement($coln, $joinColumn);
			}
		}
	
		$colmerge = array_merge($columnName, $joinColumn);
		$colmerge = array_merge($joinColumn2r, $colmerge);
		$jointbl = array();
		if(!empty($joinColumn)){
			for ($i = 0; $i < count($joinColumn); $i++) {
				$q = explode(".", $joinColumn[$i]);
				array_push($jointbl, $q[0]);
			}
			if(!empty($join)){
				if(is_array($join)==true){
					for($i=0; $i<count($join); $i++){
					$pts1 = explode("=", $join[$i]);
					for($j=0; $j<count($pts1); $j++){
						$pts2 = explode(".", $pts1[$j]);
						$tablejoinname = trim($pts2[0], " ");
						$XboSet = trim($XboSet, " ");					
						if($tablejoinname == $XboSet){
							
						}else{
							array_push($jointbl, $tablejoinname);
						}
					} 
					}	
				}
			}
			
		}else{
			
			$isArray = is_array($join);
			if($isArray == true){
				for($i=0; $i<count($join); $i++){
					$pts1 = explode("=", $join[$i]);
					for($j=0; $j<count($pts1); $j++){
						$pts2 = explode(".", $pts1[$j]);
						$tablejoinname = trim($pts2[0], " ");
						$XboSet = trim($XboSet, " ");					
						if($tablejoinname == $XboSet){
						}else{
							array_push($jointbl, $tablejoinname);
						}
					}
				}
			}
		}
		
		$jointable2 = array_unique($jointbl);
		$jointable = array_values($jointable2);
		
		$set = new Set();
		$set->useTransactionManager();
		for ($i = 0; $i < count($colmerge); $i++) {
			if(strpos($colmerge[$i], "_input_")!==false){
				array_push($inputr, $colmerge[$i]);
			}else{
				$set->MboRemote($colmerge[$i]);
			}
		}
		
		$set->MboSetRemote($XboSet);
		
		if ($join != "") { 
			$isarray = is_array($join);
			$countjointable = count($jointable);
			
			if($joinGlobal != null){
				$join = $joinGlobal;
			}
			for ($y=0; $y<$countjointable; $y++) {
				$jointablename = $jointable[$y];
				if ($isarray == 1) {
					$set->setRelationship($jointable[$y], $join[$y]);
				}
				else {
					$set->setRelationship($jointable[$y], $join);
				}
			}
		}
		
		$sourcecol = null;
		$sourcetbl = null;
		if (!empty($countdot3r)) {
		
			for ($i = 0; $i < count($countdot3r); $i++) {
				$coln = $countdot3r[$i];
				$parts3 = explode(".", $coln);
				$sourcetbl = $parts3[0];
				$parentcol = $parts3[1];
				
				$mxsrelationshipSet = (new Set())->useTransactionManager()->MboSetRemote("mxsrelationship")->setWhere("sourcetbl='$sourcetbl'")->MboRemote("sourcecol");
				$mxsrelationshipSet->getstring = "sourcecol";
				$mxsrelationshipSet = $mxsrelationshipSet->getMboSet();
				$mxsrelationship = $mxsrelationshipSet->getMbo();
				$sourcecol = $mxsrelationship->getString("sourcecol");
				$set = $set->setRelationship($sourcetbl, "$sourcetbl.$sourcecol = $XboSet.$sourcecol and  $sourcetbl.$parentcol = $XboSet.$parentcol");
			}
		}
		
		if($join != null){
			$setleftjoin = json_encode($join);
		}else{
			$setleftjoin = null;
		}
		
	
		if (empty($clause)) {
			
		}
		else {
			$set->setWhere($clause);
		}
		
		$mxtableSet = $set;
		
		$mxtableSet2 = clone $set;
		
		$mxtableSet_cnt = $mxtableSet2;
		
		$datacount = $mxtableSet_cnt->getMboSet()->counts();	
		$recordsTotal = $datacount;
		
		$draw = Super::getParam("draw"); 
		if (Super::hasParam('order') && Super::hasParam('columns')) {
			$order = Super::getParam('order');
			$columns = Super::getParam('columns');

			if (isset($order[0]['column']) && isset($columns[$order[0]['column']]['data'])) {
				$orderByColumnIndex = $order[0]['column'];
				$orderBy = $columns[$orderByColumnIndex]['data'];
			} else {
				// Default fallback jika struktur tidak sesuai
				$orderBy = $XboSetID; 
			}
		} else {
			// Fallback jika parameter tidak ada
			$orderBy = $XboSetID;
		}

		$orderType = 'ASC'; // default
		if (Super::hasParam('order')) {
			$orderParam = Super::getParam('order');

			if (is_array($orderParam) && isset($orderParam[0]['dir'])) {
				$orderType = $orderParam[0]['dir'];
			}
		}
		
		$start = Super::getParam("start"); //Paging first record indicator.
		if ($start == "") {
			$start = 0;
		}
		$length = Super::getParam('length'); //Number of records that the table can display in the current draw
		
	
		if ($length === "" || $length < 0) {

			$setl = new Set();
			if (empty($clause)) {
			
				$totallengthset = $mxtableSet2->getMboSet();
			}
			else {
				//BUG disini; mxtableSet2 tidak sepenuhnya mengclone mxtableSet.
				$totallengthset = $mxtableSet2->setWhere($clause)->getMboSet();
			}
			
			$length = $totallengthset->counts();
			if(empty($length)) $length =100;
		}
		

		
		
		for ($i = 0; $i < count($colmerge); $i++) {
			if (strpos($colmerge[$i]??"", $orderBy) !== false) {
				$orderBy = $colmerge[$i];
			}
		}
		
		if (!empty($customcolas) && strpos($orderBy ?? "", $customcolas) !== false) {
			$orderBy = $customcol;
		}
		
		if (strpos($orderBy ??"", ") as ") !== false) {
			$orderBy = $orderBy;
		}else{
			if (strpos($orderBy??"", " as ") !== false) {
			$parts = explode(" as ", $orderBy);
			$orderBy = $parts[1];
			}
		}
		$orderClause = "$orderBy $orderType";
		if(empty($orderBy)) {
			$orderClause = "";
		}
		
		$searchDataTable = Super::getParam('search');
		
		if (!empty($searchDataTable)) {
			
			$getArr = array();
			$clauser = array();
			$clauseStr = null;
			if (!empty(Super::getParam('search'))) {
				$countcolumns = count(Super::getParam('columns'));
				$columnarray = [];
				
				for ($s = 0; $s < $countcolumns; $s++) {
					$column = Super::getParam('columns')[$s]['data'];
					if (isset(Super::getParam('columns')[$s]['search']['value'])) {
						$value = Super::getParam('columns')[$s]['search']['value'];
						
						// Cek apakah value tidak kosong setelah dipastikan ada
						if ($value !== '' && $value !== null) {
							for ($g = 0; $g < count($colmerge); $g++) {
								if (strpos($colmerge[$g], $column) !== false) {
									if (strpos($colmerge[$g], ") as ") !== false) {
										continue;
									}
									else {
										if (strpos($colmerge[$g], " as ") !== false) {
											$parts = explode(" as ", $colmerge[$g]);
											$colmerge[$g] = $parts[0];
										}
										$column = $colmerge[$g];
									}
								}
							}
							$clauseSt = $column . " like '%" . $value . "%' ";
							
							array_push($clauser, $clauseSt);
							$clauseStr = implode(" and ", $clauser);
						}
					}
					if(strpos($column, "_input_")!== false){
					}else{
						array_push($columnarray, $column);
					}
				}
				$searchstr = Super::getParam('search')['value'];
				
				if ($searchstr != null) {
					$countcolumnarray = count($columnarray);
					$value = null;
					$valarr = [];
					for ($t = 0; $t < $countcolumnarray; $t++) {
						if (!empty($columnarray[$t])) {
							if (strpos($colmerge[$t], ") as ") !== false) $colmerge[$t] = $colmerge[$t]; 
							else{
								if (strpos($colmerge[$t], " as ") !== false) {
									$parts = explode(" as ", $colmerge[$t]);
									$colmerge[$t] = $parts[0];
								}
							}
							$value = $colmerge[$t] . " like '%" . $searchstr . "%'";
							if (strpos($colmerge[$t], ") as") !== false) {
								continue;
							}else{
								if (strpos($colmerge[$t], " as ") !== false) {
										$parts = explode(" as ", $colmerge[$t]);
										$colmerge[$t] = $parts[1];
								}else{
									if (strpos($colmerge[$t], "_input_") !== false) {
										continue;
									}
								}
							}array_push($valarr, $value);
						}
					}
					$searchstr = implode(" or ", $valarr);
					/*
					if($sitecol_exist == 1){
						$searchstr = $searchstr." and ".$clause;
					}
					*/
				}		
				
				if ($clauseStr != null) {
					$objWhere = $mxtableSet->where;
					if (!empty($objWhere)) {
						$clauseStr = "(".$mxtableSet->where . ") and " . $clauseStr;
					}
					else {
						$clauseStr = $clauseStr;
					}
					$findwhere = " where ";
					if (strpos($clauseStr, $findwhere) !== false) {
						$clauseStr = str_replace($findwhere, " ", $clauseStr);
					}
				
					$mxtableSet = $mxtableSet->setWhere($clauseStr)->order($orderClause)->limit($start, $length);
				}
				else {
					
					if ($searchstr != null) {
						$objWhere = $mxtableSet->where;
						if (!empty($objWhere)) {
							$searchstr = "(" . $searchstr . ")";
							$searchstr = $mxtableSet->where . " and " . $searchstr;
						}
						else {
							$searchstr = $searchstr;
						}
						$findwhere = " where ";
						if (strpos($searchstr, $findwhere) !== false) {
							$searchstr = str_replace($findwhere, " ", $searchstr);
						}
						
						$mxtableSet = $mxtableSet->setWhere($searchstr)->order($orderClause)->limit($start, $length);
					}
					else {
						if (Super::getParam('mxsdvsearch') != null) {
							$a = new Super();
							$b = $a->getWhere(Super::getParam('mxsdvsearch'), $XboSet);
							$jointable = $b->join;
							$searchstr = $b->where;
							$mxtableSet = $mxtableSet->setWhere($searchstr)->order($orderClause)->limit($start, $length);
						}
						else {
						
							if (!empty($orderClause)) {
								$mxtableSet = $mxtableSet->order($orderClause);
							}

							$mxtableSet = $mxtableSet->limit($start, $length);
						}
					}
				}
				
				$mxtableSet3 = clone $mxtableSet;
				$mxtableSet = $mxtableSet->getMboSet("remove_duplicate");
				$sql = $mxtableSet->sql;
				
				$data = Xbo::getData($sql);
				$dataCount = count($data);
				
				$selectst= $mxtableSet3->getstring;
				$fromst = $mxtableSet3->mbosetname;
				$where = $mxtableSet3->where;
				$joinst = $mxtableSet3->leftjoin;
				$totalRecordsSQL = "select $selectst from $fromst $joinst $where"; 
				
				
				$dataTotal = Xbo::getData($totalRecordsSQL);
				$dataTotalCount = count($dataTotal);
				$recordsFiltered = $dataTotalCount;
				
			}
			else {
			
				$selectst = $mxtableSet->sql;
				if (strpos($selectst, " where ") !== false) {
					$where = $mxtableSet->where;
					$where = str_replace(" where ", " and ", $where);
				}
				else {
					$where = $mxtableSet->where;
				}
				
				$order = $mxtableSet->order;
				$sql = $selectst . $where . $order;
				
				$data = Xbo::getData($sql);
				$dataCount = count($data);
				$recordsFiltered = $dataCount;
			}
			
			if(count($inputr) >0){
				
				$countdata = count($data);
				for($i=0; $i<$countdata; $i++ ){
					for($j=0; $j<count($inputr); $j++){
						$ipts = explode("_", $inputr[$j]);
						
						$lookupname = null;
						$input_lookup = null;
						$input_type = $ipts[2];
						$input_attr = $ipts[3];
						if(isset($ipts[4])){
							$lookupname = $ipts[4];
							$lookupname = str_replace("@", "|", $lookupname);
							$input_lookup = "data-lookup = '".$lookupname."'";
							
						}else{
							$lookupname="";
						}
						$input = "<input type='$input_type'  class='nonpersistent' style='width:80px' data-attribute='$input_attr' $input_lookup>";
					
						$data[$i][$inputr[$j]] =  $input;
					}
				}
			}
			if($searchstr == null || $searchstr == ""){
				$searchstr=null;
			}
			
			if($clause != null){
				$searchstr = $clause;
			}
			
			
			//get table ids; 
			$tableids = [];
			for($d=0; $d<count($data); $d++){
				$dt = $data[$d];
				$tableid = $dt[$XboSetID];
				array_push($tableids, $tableid);
			}
			$drawcount = intval($draw);
			$response = array(
				"draw" => $drawcount ,
				"recordsTotal" => $recordsTotal,
				"recordsFiltered" => $recordsFiltered,
				"data" => $data,
				"objectname"=>$XboSet,
				"whereclause" =>$searchstr,
				"tableids"=>$tableids,
				"order"=>"$orderClause limit $start,$length"
			);
			$joinGlobal = $setleftjoin;
			Set::throws($response);
			exit;
			//getData
			
		}
		
		if (empty(Super::getParam("search"))) {
			
			$colarray = [];
			for ($i = 0; $i < count($colstackarr); $i++) {
				$colname = null;
				$colobj = new stdClass();
				$columnNameExplode = explode("`", $columnStack[$i]);
				$title = $columnNameExplode[0];
				$columnNameStack = $columnNameExplode[1];
				$colobj->title = $title;
				if (strpos($columnNameStack, "|") !== false) {
					$columnNameStackSt = explode("|", $columnNameStack);
					$p = explode("=", $columnNameStackSt[1]);
					$dtype = $p[1];
					$dataType = str_replace('"', '', $dtype);
					$colobj->datatype = $dataType;
					$colname = $columnNameStackSt[0];
					if (strpos($colname, ".") !== false) {
						$parts = explode(".", $colname);
						$colname = $parts[1];
						$countdot3 = count($parts);
						if ($countdot3 == 3) {
							$colname = $parts[2];
						}
						else {
							$colname = $parts[1];
						}
					}
				}
				else {
					$colname = $columnNameStack;
					if (strpos($colname, ".") !== false) {
						$parts = explode(".", $colname);
						$colname = $parts[1];
						$countdot3 = count($parts);
						if ($countdot3 == 3) {
							$colname = $parts[2];
						}
						else {
							$colname = $parts[1];
						}
					}
				}
				
				if (strpos($colname, " as ") !== false) {
					$parts = explode(" as ", $colname);
					$colname = $parts[1];
				}
				$colobj->data = $colname;
				if ($colname == $XboSetID) {
					
					$colobj->sClass = "hide";
				}
				if($errorexec!=null){
					$colobj->errorexec = $errorexec;
				}
				if($getnewid != null){
					$colobj->getnewid = $getnewid;
				}
				array_push($colarray, $colobj);
			}
			Set::throws($colarray);
			
		}
	
	}
	
	

	// optional: setter jika mau set manual dari luar
	public static function setCurrentSS($ssObject)
    {
        self::$currentSS = $ssObject;
    }
	public static function getCurrentSS(){
		
		return self::$currentSS; 
	}
	public static function getXboSet($objectname, $clause = null){
		$ss = new stdClass();
        $ss->objectname = $objectname;
        $ss->clause = empty($clause) ? null : $clause;

		
        self::setCurrentSS($ss);
		
        return $ss;
	}
	public static function IDWhereClause($tableid=null, $origwhereclause=null){
		
		$orclause = null;
		if(isset($_POST["tableids"])){
			
			$tableids = $_POST["tableids"];
			if($tableids != null ){
				$cntids = count($tableids);
				$idsr = [];
				for($i=0; $i<$cntids; $i++){
					$orclause = "$tableid = ".$tableids[$i];
					array_push($idsr, $orclause);
				}
				$orclause = implode(" or ", $idsr);
			}else{
				$orclause = null;
			}
			$whereclause = $orclause;
		}else{
			if(isset($_GET["tableids"])){
				$tableids = json_decode($_GET["tableids"]);
				if($tableids != null ){
					$cntids = count($tableids);
					$idsr = [];
					for($i=0; $i<$cntids; $i++){
						$orclause = "$tableid = ".$tableids[$i];
						array_push($idsr, $orclause);
					}
					$orclause = implode(" or ", $idsr);
				}else{
					$orclause = null;
				}
				$whereclause = $orclause;
			}else{
				$whereclause = $origwhereclause;
			}
			
		}
		return null;
		//return $whereclause;	
	}
	function getWhere($get, $xboset)
	{
		$joinrtbl = array();
		$whrcol = array();
		$colval = "";
		global $whereClauseGlobal;
		global $joinGlobal;
		
		$_GET["mxsdvsearch"] = $get;
		if (strpos($_GET["mxsdvsearch"] , "qmngry") !== false) {
			$q = explode("|", $_GET["mxsdvsearch"]);
			$set = new Set();
			$mxsquery = $set->MboSetRemote("mxsquery")->setWhere("mxsqueryid=".$q[1])->MboRemote("jointable")->MboRemote("whereclause")->getMboSet()->getMbo();
			$this->join = $mxsquery->getString("jointable");
			$this->join = json_decode($this->join);
			$this->where = $mxsquery->getString("whereclause");
			$whereClauseGlobal = $this->where;
			$joinGlobal = $this->join;
			
		}else{
			
			$sdt = urldecode($_GET['mxsdvsearch']);
			$e = explode("$", $sdt);
			
			for ($i = 0; $i < count($e); $i++) {
				$q = explode("|", $e[$i]);
				$ww = explode("^", $q[0]);
				$joincol = $ww[0];
				$jointable = $ww[1];
				
				if ($jointable == $xboset) {
					$re = explode("^", $q[1]);
					for ($k = 0; $k < count($re); $k++) {
						$cv = explode("`", $re[$k]);
						if (isset($cv[1])) {
							if (strpos($cv[0], 'date') !== false) {
								if (strpos($cv[0], 'from_') !== false) {
									$cv[0] = str_replace("from_", "", $cv[0]);
									$cvtemp = Xbo::getDate($cv[1]);
									$colval = $cv[0] . ">='" . $cvtemp . "'";
								}
								if (strpos($cv[0], 'to_') !== false) {
									$cvtemp = Xbo::getDate($cv[1]);
									$cv[0] = str_replace("to_", "", $cv[0]);
									$colval = $cv[0] . "<='" . $cvtemp . "'";
								}
							}
							else {
								if (strpos($cv[0], '=') !== false) {
									$cv[0] = str_replace("=", "", $cv[0]);
								}
								if (strpos($cv[0], '>') !== false || strpos($cv[0], '<') !== false) {
									$colval = $cv[0] . "='" . $cv[1] . "'";
								}
								else {
									$colval = $cv[0] . " like '%" . $cv[1] . "%'";
								}
							}
						}
						else {
							$colval = $cv[0];
						}
						array_push($whrcol, $colval);
					}
					
					continue;
				}
				else {
				
					$joinclause = $jointable . "." . $joincol . "=" . $xboset . "." . $joincol;				
					$jointbl = $joinclause;	
					
					array_push($joinrtbl, $jointbl);
					
					$re = explode("^", $q[1]);
					for ($k = 0; $k < count($re); $k++) {
						$cv = explode("`", $re[$k]);
						if (isset($cv[1])) {
							if (strpos($cv[0], 'date') !== false) {
								if (strpos($cv[0], 'from_') !== false) {
									$cv[0] = str_replace("from_", "", $cv[0]);
									$cvtemp = Xbo::getDate($cv[1]);
									$colval = $cv[0] . ">='" . $cvtemp . "'";
								}
								if (strpos($cv[0], 'to_') !== false) {
									$cvtemp = Xbo::getDate($cv[1]);
									$cv[0] = str_replace("to_", "", $cv[0]);
									$colval = $cv[0] . "<='" . $cvtemp . "'";
								}
							}
							else {
								if (strpos($cv[0], '=') !== false) {
									$cv[0] = str_replace("=", "", $cv[0]);
								}
								if (strpos($cv[0], '>') !== false || strpos($cv[0], '<') !== false) {
									$colval = $cv[0] . "='" . $cv[1] . "'";
								}
								else {
									$colval = $cv[0] . " like '%" . $cv[1] . "%'";
								}
							}
						}
						else {
							$colval = $cv[0];
						}
						array_push($whrcol, $colval);
					}
				}
			}
			$this->join = $joinrtbl;
			$this->where = implode(" and ", $whrcol);
		}
		
		return $this;
	}
	
	public static function Signout()

	{
		$invested = $_POST['invested'];
		$set = new Set();
		$set->MboSetRemote("mxssession")->setWhere("invested='$invested'")->remove();
		session_start();
		session_destroy();
		echo "signout";
	}
	public static function Investor($id, $uq)

	{
		
		$ts = Xbo::setEnterDate();
		$ipaddr = new Super();
		$ip = $ipaddr->getIPAddress();
		
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		try {
			$set = new Set();
			$set->useTransactionManager();
			$set->MboSetRemote("mxssession")->setValue("username", $id)->setValue("logintime", $ts)->setValue("invested", $uq)->setValue("ipaddress", $ip)->add();
			
			$tm->commit();
		} catch (Exception $e) {
			$tm->rollback();
			throw $e;
		}
			
	}
	function XboSession($v, $x)
	{
		Seed::XboSession($v, $x);
	}
	function Xbor($inc = "")
	{
		Seed::Xbor($inc);
	}
	function getIPAddress()
	{
		if (getenv('HTTP_X_FORWARDED_FOR')) {
			$pipaddress = getenv('HTTP_X_FORWARDED_FOR');
			$ipaddress = getenv('REMOTE_ADDR');
		}
		else {
			$ipaddress = getenv('REMOTE_ADDR');
		}
		return $ipaddress;
	}
	public static function setSysPath()

	{
		$xsar = dirname(__FILE__);
		$path = str_replace("businessobject", "", $xsar) . "properties";
		return $path;
	}
	function breakapart($data)
	{
		
		$p1 = explode(".", $data);
		$p2 = base64_decode($p1[1]);
		$this->pl = json_decode($p2);
		return $this;
	}
	function registered()
	{
		Seed::Registered();
	}
	function encodeUpreg($jwtclaims)
	{
		$jwttemp = Seed::EncodeUpreg($jwtclaims);
		return $jwttemp;
		
	}
	function decodeUpregTracking($jwtclaims)
	{
		$jwttemp = Seed::DecodeUpregTracking($jwtclaims);
		return $jwttemp;
		
	}
	function decodeUpreg($jwtclaims)
	{
		$jwttemp = base64_decode($jwtclaims);
		return $jwttemp;
	}
	function XPreg()
	{
		Seed::XPreg();
	}
	public static function invested($y, $x, $z, $licenseType = null)
	{
		Seed::Invested($y, $x, $z, $licenseType);
	}
}

function android_renderData($defaultjoin=null, $jointable=null){
	$set = new Set();
	
	$XboSetPart = explode("^", $XboSetName);
	$XboSet = $_POST["android_xbosetname"];
	$clause = $_POST["android_clause"];
	$userid = $_POST["android_username"];
	

	$set = new Set();
	$mxsquerySet = $set->MboSetRemote("mxsquery")->setWhere("userid='$userid' and isdefault='Y' and appname = '$XboSet' ")->MboRemote("jointable")->MboRemote("whereclause")->getMboSet();
	$mxsquerySetCounts = $mxsquerySet->counts();
	$arr =[];
	if($mxsquerySetCounts == 1){
		$mxquery = $mxsquerySet->getMbo();
		$jointable = $mxquery->getString("jointable");
		$whereclause =  $mxquery->getString("whereclause");
		$joindecode = json_decode($jointable);
		if($clause == null || $clause == "null") $whereClauseGlobal = $whereclause;
		else $whereClauseGlobal = " $clause and $whereclause";
		
		XboSet::getXboSet("$XboSet", $whereClauseGlobal);
		$joinGlobal = $joindecode;
		XboSet::displayData($joindecode);	
	}else{
		if($defaultjoin == null){
			$joinGlobal = $jointable;
			XboSet::displayData($jointable);
		}else{
			if($jointable != null){
			$joinmerge = array_merge($defaultjoin, $jointable);
				$joinGlobal = $joinmerge;
				XboSet::displayData($joinmerge);	
			}else{
				$joinGlobal = $defaultjoin;
				XboSet::displayData($defaultjoin);	
			}
			$joinGlobal = $joinmerge;
		}
	}
}



function renderData($defaultjoin = null, $jointable = null) {
  
    global $whereClauseGlobal;
    global $joinGlobal;

    $data = Super::getCurrentSS();
    $XboSet = $data->objectname;
    $initialClause = $data->clause;
    $userid = Xbo::getLoginUser();

    $set = new Set();
    $querySet = $set->MboSetRemote("mxsquery")
                    ->where("userid = ? AND isdefault = 'Y' AND appname = ?", $userid, $XboSet)
                    ->MboRemote("jointable")
                    ->MboRemote("whereclause")
                    ->getMboSet();

    if ($querySet->counts() === 1) {
        // --- ALUR 1: Ditemukan query default yang tersimpan untuk user ---
        $savedQuery = $querySet->getMbo();
        
        $savedJoinTableJson = $savedQuery->getString("jointable");
        $savedWhereClause = $savedQuery->getString("whereclause");
        
        $finalJoin = json_decode($savedJoinTableJson);

        // Gabungkan klausa 'where' jika keduanya ada
        if (!empty($initialClause) && !empty($savedWhereClause)) {
            $whereClauseGlobal = "($initialClause) AND ($savedWhereClause)";
        } else {
            $whereClauseGlobal = !empty($initialClause) ? $initialClause : $savedWhereClause;
        }
        
        $joinGlobal = $finalJoin;
		
        XboSet::getXboSet($XboSet, $whereClauseGlobal); 
        XboSet::displayData($finalJoin);

    } else {
  
        $finalJoin = null;

        if ($defaultjoin !== null && $jointable !== null) {
            // Jika keduanya ada, gabungkan
            $finalJoin = array_merge($defaultjoin, $jointable);
        } else if ($defaultjoin !== null) {
            // Jika hanya defaultjoin yang ada
            $finalJoin = $defaultjoin;
        } else {
            // Jika hanya jointable yang ada (atau keduanya null)
            $finalJoin = $jointable;
        }
        
        $joinGlobal = $finalJoin;
        $whereClauseGlobal = $initialClause;
        XboSet::displayData($finalJoin);
    }
}
function deleteElement($element, &$array)
	{
		$index = array_search($element, $array);
		if ($index !== false) {
			unset($array[$index]);
		}
	}
	
?>
