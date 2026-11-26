<?php
$path = "/../../../../eat.php";
//require_once ($path);

require_once(realpath(dirname(__FILE__) . $path ));


//require_once(__DIR__.$path);
class UserActions {
	public static function submitMessage(){
		$subject = $_POST["submitSubject"];
		$message = $_POST["submitMessage"];
		$username = $_POST["fromUsername"];
		$ownership = $_POST["ownership"];
		$to_email = "maxesqriven@gmail.com";
		
		$emailSubject = "A Maxes WMS Message:[$username]-$ownership - $subject" ;
		$emailbody = "An email from $username, the ownership is $ownership , the message as stated as below following : <br><br>".nl2br($message);;
		$altbody = "An email from $username, the ownership is $ownership , the message as stated as below following : ".$message;
		XboSet::sendEmail($to_email, $subject, $emailbody, $altbody);
		$ss = new stdClass;
		$ss->msg = "ok";
		$ss->emailbody = $altbody;
		$ss->body = $emailbody;
		Set::throws($ss);
		
	}
	public static function setSoftwareToExpired(){
		$email = $_POST["setSoftwareToExpired"];
		$set = new Set();
		$set->MboSetRemote("owneremail")->setWhere("email='$email'")->setValue("expired", "Y")->setValue("trial", "N")->change();
		$ss = new stdClass;
		$ss->msg = "software_expired";
		Set::throws($ss);
		 
	}
	public static function checkNotification(){
		$email = $_POST["checkNotification"];
		$todaysdate = $_POST["todaysdate"];
		$set = new Set();
		$wc = "email='$email'";
		$ownerEmailSet = $set->MboSetRemote("owneremail")->setWhere($wc)->MboRemote("notiftype")
		->MboRemote("expireddate")->MboRemote("remarks")->MboRemote("enterdate")->MboRemote("trial")
		->MboRemote("notiftext")->getMboSet();
		$owneremail = $ownerEmailSet->getMbo();
		
		$notiftype = $owneremail->getString("notiftype");
		$notiftext = $owneremail->getString("notiftext");
		$expireddate = $owneremail->getString("expireddate");
		$enterdate = $owneremail->getString("enterdate");
		$remarks = $owneremail->getString("remarks");
		$trial = $owneremail->getString("trial");
		
		if($expireddate == null || $expireddate == ""){
			
			$set = new Set();
			$ownerEmailSet = $set->MboSetRemote("owneremail")->setWhere($wc)->MboRemote("timelength")->getMboSet();
			$ownerEmail = $ownerEmailSet->getMbo();
			$timelength = $ownerEmail->getString("timelength");
			
			if($timelength == "onemonth") $expireddate = TimeLength::getDateAhead($enterdate, TimeLength::oneMonth()); 
			if($timelength == "threemonths") $expireddate = TimeLength::getDateAhead($enterdate, TimeLength::threeMonths);
			if($timelength == "sixmonths") $expireddate = TimeLength::getDateAhead($enterdate, TimeLength::sixmonths());
			$set2 = new Set();
			$set2->MboSetRemote("owneremail")->setWhere("email='$email'")->setValue("expireddate", $expireddate)->change();
			
		}
		
		$future = strtotime($expireddate); //Future date.
		$timefromdb = strtotime($todaysdate);
		$timeleft = $future-$timefromdb;
		$daysleft = round((($timeleft/24)/60)/60); 
		$daysleft = $daysleft-1;
			
		$notiftype = "RENEWAL";
		$notiftext = "Maxes WMS will be expired on $expireddate ($daysleft days remaining).";
		
		if($trial == "Y"){
			$trialtext = "Hello Thank you for downloading Maxes WMS";
			$trialtext2 = "This is Trial Version, you can use the trial version for $daysleft days remaining.";
		}else {
			$trialtext = "";
			$trialtext2 = "";
		}
		
		$ss = new stdClass;
		$ss->msg = "ok";
		$ss->notiftype = $notiftype;
		$ss->notiftext = $notiftext;
		$ss->daysleft = $daysleft;
		$ss->expireddate = $expireddate;
		$ss->trial = $trial;
		$ss->trialtext = $trialtext;
		$ss->trialtext2 = $trialtext2;
		Set::throws($ss);
	}
	public static function midtransCheckOut($ss){
		$orderid = $ss->orderid;
		$name = $ss->name;
		$email = $ss->email;
		$phone = $ss->phone;
		$grossamount = $ss->grossamount;
		$ordertype = $ss->ordertype;
		$enterdate = Xbo::setEnterDate();
		$timelength = TimeLength::oneMonth();
		
		if($ordertype == "SOFTWARE"){
			$set = new Set();
			$tbl = "owneremailtrans";
			$remarks = "CHECKOUT";
			$owneremailtransSet = $set->MboSetRemote($tbl)
			->setValue("orderid", $orderid)
			->setValue("grossamount", $grossamount)
			->setValue("phone", $phone)
			->setValue("name", $name)
			->setValue("remarks", $remarks)
			->setValue("enterdate", $enterdate)
			->setValue("timelength", $timelength)
			->add();
			
			$tb2 = "owneremail";
			$set = new Set();
			$set->MboSetRemote($tb2)
			->setValue("orderid", $orderid)
			->setValue("timelength", $timelength)
			->change();
		}else if($ordertype == "USER-LICENSE"){
			$set = new Set();
			$tbl = "owneruserstrans";
			$remarks = "CHECKOUT";
			$orderid = $ss->orderid;
			$name = $ss->name;
			$email = $ss->email;
			$phone = $ss->phone;
			$grossamount = $ss->grossamount;
			$itemdetails = $ss->itemdetails;
			
			$countItems =  count($itemdetails);
			
			for($i=0; $i<$countItems; $i++){
				$item = $itemdetails[$i];
				$price = $item->price;
				$quantity = $item->quantity;
				$username = explode("|", $item->name)[1];
				$tableid = $item->id;
				$set = new Set();
				$set->MboSetRemote($tbl)
				->setValue("orderid", $orderid)
				->setValue("amount", $price)
				->setValue("username", $username)
				->setValue("quantity", $quantity)
				->setValue("id", $tableid)
				->setValue("remarks", $remarks)
				->setValue("enterdate", $enterdate)
				->add();
			}
		}
		
		
		
	}
	
	public static function midtransRemarks($transaction_status, $payment_type, $fraud_status, $order_id ){
		$remarks = "";
		if ($transaction_status == 'capture') {
			if ($payment_type == 'credit_card'){
				if($fraud_status == 'accept'){
					// TODO set payment status in merchant's database to 'Success'
					$remarks =  "Transaction order_id: " . $order_id ." successfully captured using " . $payment_type;
				}
			}
		}
		else if ($transaction_status == 'settlement'){
			// TODO set payment status in merchant's database to 'Settlement'
		  $remarks =  "Transaction order_id: " . $order_id ." successfully transfered using " . $payment_type;
		}
		else if($transaction_status == 'pending'){
			// TODO set payment status in merchant's database to 'Pending'
			$remarks =  "Waiting customer to finish transaction order_id: " . $order_id . " using " . $payment_type;
		  }
		else if ($transaction_status == 'deny') {
			// TODO set payment status in merchant's database to 'Denied'
			$remarks =  "Payment using " . $payment_type . " for transaction order_id: " . $order_id . " is denied.";
		  }
		else if ($transaction_status == 'expire') {
			// TODO set payment status in merchant's database to 'expire'
		   $remarks =  "Payment using " . $payment_type . " for transaction order_id: " . $order_id . " is expired.";
		  }
		else if ($transaction_status == 'cancel') {
			// TODO set payment status in merchant's database to 'Denied'
		   $remarks =  "Payment using " . $payment_type . " for transaction order_id: " . $order_id . " is canceled.";
		}
		return $remarks;
		
	}
	public static function BankTransfer($notif){
		
		$tb = "owneremailtrans";
		$tb2 = "owneremail";
		$tb3 = "owneruserstrans";
		
		$transaction_time = $notif->transaction_time;
		$transaction_status = $notif->transaction_status;
		$transaction_id = $notif->transaction_id;
		$status_message = $notif->status_message;
		$signature_key = $notif->signature_key;
		$order_id = $notif->order_id;
		$fraud_status = $notif->fraud_status;
		$settlement_time = $notif->settlement_time; //paiddate
		$merchant_id = $notif->merchant_id;
		$grossamount = $notif->gross_amount;
		$currency = $notif->currency;
		$payment_type = $notif->payment_type;
		$expiry_time = $notif->expiry_time; //payexpirydate
		$remarks = UserActions::midtransRemarks($transaction_status, $payment_type, $fraud_status, $order_id );
		
		if ($payment_type == 'bank_transfer') {
			$va_numbers = $notif->va_numbers;
			$va = $va_numbers[0];
			$vanumber = $va->va_number;
			$bankname = $va->bank;
		}else{
			$vanumber = null;
			$bankname = "sibob";
		}
		$set = new Set();
		$wc = "orderid = '$order_id'";
		$oetrans = $set->MboSetRemote($tb)->setWhere($wc)->getMboSet();
		$counts = $oetrans->counts();
		if($counts >0){
			
			$set = new Set();
			$ownerEmailSet = $set->MboSetRemote($tb2)->setWhere($wc)->MboRemote("expireddate")->MboRemote("timelength")->getMboSet();
			$ownerEmail = $ownerEmailSet->getMbo();
			$timelength = $ownerEmail->getString("timelength");
			$lastexpireddate = $ownerEmail->getString("expireddate");
			
			if($timelength == "onemonth") $timelength = TimeLength::oneMonth();
			if($timelength == "threemonths") $timelength = TimeLength::threeMonths();
			if($timelength == "sixmonths") $timelength = TimeLength::sixMonths();
			if($timelength == null) $timelength = TimeLength::threeMonths();
			
			//$paiddate_otime = strtotime($settlement_time);
			$paiddate_otime = strtotime($lastexpireddate);
			$nextdate = $paiddate_otime + $timelength;
			$expireddate = date("Y-m-d H:i", $nextdate);
			
			$set = new Set();
			$set->MboSetRemote($tb)
			->setWhere($wc)
			->setValue("remarks", $remarks)
			->setValue("vanumber", $vanumber)
			->setValue("bankname", $bankname)
			->setValue("payexpirydate", $expiry_time)
			->setValue("transactionstatus", $transaction_status)
			->setValue("paiddate", $settlement_time)
			->setValue("currency", $currency)
			->setValue("paymenttype", $payment_type)
			->change();
			
			
			if($transaction_status == "settlement"){
				$ordertype = "SOFTWARE";
				$set2 = new Set();
				$expired = "N";
				$set2->MboSetRemote($tb2)
				->setWhere($wc)
				->setValue("expired", $expired)
				->setValue("remarks", $remarks)
				->setValue("paymenttype", $payment_type)
				->setValue("grossamount", $grossamount)
				->setValue("paiddate", $settlement_time)
				->setValue("payexpirydate", $expiry_time)
				->setValue("expireddate", $expireddate)
				->setValue("lastexpireddate", $lastexpireddate)
				->setValue("transactionstatus", $transaction_status)
				->setValue("ordertype", $ordertype)
				->setValue("currency", $currency)
				->change();
			}
			
		}
		
		$oetrans = $set->MboSetRemote($tb3)->setWhere($wc)->getMboSet();
		$counts = $oetrans->counts();
		if($counts >0){
			$set = new Set();
			$set->MboSetRemote($tb3)
			->setWhere($wc)
			->setValue("remarks", $remarks)
			->setValue("vanumber", $vanumber)
			->setValue("bank", $bankname)
			->setValue("payexpirydate", $expiry_time)
			->setValue("transactionstatus", $transaction_status)
			->setValue("paiddate", $settlement_time)
			->setValue("currency", $currency)
			->change();
			
			
			
			if($transaction_status == "settlement"){
				$status = "ACTIVE";
			}else {
				$status = "INACTIVE";
			}
			
			
			$set = new Set();
			$tb3Set = $set->MboSetRemote($tb3)->setWhere($wc)
			->MboRemote("username")->MboRemote("ownership")
			->getMboSet();
			
			$tb3Counts = $tb3Set->counts();
			$tb4 = "mxsusers";
			for($i=0; $i<$tb3Counts; $i++){
				$tb3mbo = $tb3Set->getMbo();
				$username = $tb3mbo->getString("username");
				$ownership = $tb3mbo->getString("ownership");
				
				$set2 = new Set();
				$set2->MboSetRemote($tb4)->setWhere("userid = '$username' and ownership='$ownership'")
				->setValue("status", $status)
				->setValue("statusdate", $settlement_time)
				->setValue("changedate", $settlement_time)
				->setValue("remarks", $remarks)
				->change();
			}
		}
	}
	
	public static function CreditCard($notif){
		
		/*
			{
		"transaction_time": "2024-02-14 10:34:40", //paiddate
		"transaction_status": "capture",
			  "transaction_id": "3d6052eb-7436-4608-b333-8ae265e3ad85",
			  "three_ds_version": "2",
			  "status_message": "midtrans payment notification",
			  "status_code": "200",
			  "signature_key": "8ee1a2f8256d62572b52692c537048a9b183d110fea3ee0b2b78da3435c63f5b2eccebca13aa2132af6b201f6f5b1295133e2f9b80539df6d54322af5f031775",
		"payment_type": "credit_card",
		"order_id": "maxeswms_1321786729",
			  "metadata": {
			  },
		"merchant_id": "G811105954",
		"masked_card": "48111111-1114",
		"gross_amount": "987654.00",
		"fraud_status": "accept",
		 "expiry_time": "2024-02-22 10:34:39",
			  "eci": "05",
		"currency": "IDR",
		"channel_response_message": "Approved",
			  "channel_response_code": "00",
		"card_type": "credit",
		"bank": "cimb",
			"approval_code": "1707881690133"
			}
		*/
		$transaction_time = $notif->transaction_time;
		$transaction_status = $notif->transaction_status;
		$transaction_id = $notif->transaction_id;
		
		$three_ds_version = $notif->three_ds_version;
		$status_message = $notif->status_message;
		$status_code = $notif->status_code;
		
		$signature_key = $notif->signature_key;
		$payment_type = $notif->payment_type;
		$order_id = $notif->order_id;
		
		$merchant_id = $notif->merchant_id;
		$masked_card = $notif->masked_card;
		$gross_amount = $notif->gross_amount;
		
		$fraud_status = $notif->fraud_status;
		$expiry_time = $notif->expiry_time;
		$eci = $notif->eci;
		
		$currency = $notif->currency;
		$channel_response_message = $notif->channel_response_message;
		$channel_response_code = $notif->channel_response_code;
		
		$card_type = $notif->card_type;
		$bank = $notif->bank;
		$approval_code = $notif->approval_code;
		$remarks = UserActions::midtransRemarks($transaction_status, $payment_type, $fraud_status, $order_id );
		
		$wc = "orderid = '$order_id'";
		$set = new Set();
		$tb = "owneremailtrans";
		$tb2 = "owneremail";
		$tb3 = "owneruserstrans";
		$oetrans = $set->MboSetRemote($tb)->setWhere($wc)->getMboSet();
		$counts = $oetrans->counts();
		if($counts >0){
			
			$set = new Set();
			$ownerEmailSet = $set->MboSetRemote($tb2)->setWhere($wc)->MboRemote("expireddate")->MboRemote("timelength")->getMboSet();
			$ownerEmail = $ownerEmailSet->getMbo();
			$timelength = $ownerEmail->getString("timelength");
			$lastexpireddate = $ownerEmail->getString("expireddate");
			
			if($timelength == "onemonth") $timelength = TimeLength::oneMonth();
			if($timelength == "threemonths") $timelength = TimeLength::threeMonths();
			if($timelength == "sixmonths") $timelength = TimeLength::sixMonths();
			if($timelength == null) $timelength = TimeLength::threeMonths();
			
			
			//$paiddate_otime = strtotime($transaction_time);
			$paiddate_otime = strtotime($lastexpireddate);
			$nextdate = $paiddate_otime + $timelength;
			$expireddate = date("Y-m-d H:i", $nextdate);
			
			$set = new Set();
			$set->MboSetRemote($tb)
			->setWhere($wc)
			->setValue("remarks", $remarks)
			->setValue("maskedcard", $masked_card)
			->setValue("bankname", $bank)
			->setValue("cardtype", $card_type)
			->setValue("transactionstatus", $transaction_status)
			->setValue("paiddate", $transaction_time)
			->setValue("currency", $currency)
			->setValue("paymenttype", $payment_type)
			->setValue("fraudstatus", $fraud_status)
			->setValue("channelresponsemsg", $channel_response_message)
			->setValue("payexpirydate", $expiry_time)
			->change();
			
			
			if($transaction_status == "deny"){
				$expired = "Y";
				$trial = "Y";
			}else if($transaction_status == "capture"){
				$expired = "N";
				$trial = "N";
			}
			
			$ordertype = "SOFTWARE";
			$set2 = new Set();
			$set2->MboSetRemote($tb2)
			->setWhere($wc)
			->setValue("expired", $expired)
			->setValue("remarks", $remarks)
			->setValue("paymenttype", $payment_type)
			->setValue("grossamount", $gross_amount)
			->setValue("paiddate", $transaction_time)
			->setValue("cardtype", $card_type)
			->setValue("expireddate", $expireddate)
			->setValue("lastexpireddate", $lastexpireddate)
			->setValue("transactionstatus", $transaction_status)
			->setValue("ordertype", $ordertype)
			->setValue("currency", $currency)
			->setValue("trial", $trial)
			->change();
		}
		
		
		$oetrans = $set->MboSetRemote($tb3)->setWhere($wc)->getMboSet();
		$counts = $oetrans->counts();
		if($counts >0){
			$set = new Set();
			$set->MboSetRemote($tb3)
			->setWhere($wc)
			->setValue("remarks", $remarks)
			->setValue("maskedcard", $masked_card)
			->setValue("bank", $bank)
			->setValue("cardtype", $card_type)
			->setValue("transactionstatus", $transaction_status)
			->setValue("paiddate", $transaction_time)
			->setValue("currency", $currency)
			->setValue("paymenttype", $payment_type)
			->setValue("fraudstatus", $fraud_status)
			->setValue("channelresponsemsg", $channel_response_message)
			->setValue("payexpirydate", $expiry_time)
			->change();
						
			if($transaction_status == "deny"){
				$status = "INACTIVE";
			}else if($transaction_status == "capture"){
				$status = "ACTIVE";
			}
			
			
			$set = new Set();
			$tb3Set = $set->MboSetRemote($tb3)->setWhere($wc)
			->MboRemote("username")->MboRemote("ownership")
			->getMboSet();
			
			$tb3Counts = $tb3Set->counts();
			$tb4 = "mxsusers";
			for($i=0; $i<$tb3Counts; $i++){
				$tb3mbo = $tb3Set->getMbo();
				$username = $tb3mbo->getString("username");
				$ownership = $tb3mbo->getString("ownership");
				
				$set2 = new Set();
				$set2->MboSetRemote($tb4)->setWhere("userid = '$username' and ownership='$ownership'")
				->setValue("status", $status)
				->setValue("statusdate", $transaction_time)
				->setValue("changedate", $transaction_time)
				->setValue("remarks", $remarks)
				->change();
			}
				
		}
		
	}
	public static function midtransAfterPayment($notif){
		
		$tb = "owneremailtrans";
		$tb2 = "owneremail";
		$tb3 = "owneruserstrans";
		
		$payment_type = $notif->payment_type;
		if($payment_type == "bank_transfer"){
			UserActions::BankTransfer($notif);
		}else if($payment_type == "credit_card"){
			UserActions::CreditCard($notif);
		}
		
	}
	public static function changePassword(){
		
		$id = $_POST["id"];
		$username = $_POST["changePassword"];
		$password = md5($_POST["password"]);
		$ss = new stdClass;
		if($id == "" || $id == null){
			$wc = "username = '$username'";
		}else{
			$wc = "mxsusersid='$id' and username = '$username'";
		}
		
		
		$set = new Set();
		$mxsusersSet = $set->MboSetRemote("mxsusers")->setWhere($wc)->MboRemote("status")->getMboSet();
		$counts = $mxsusersSet->counts();
		if($counts == 0 ){
			$status = "INACTIVE";
			$set = new Set();
			$set->MboSetRemote("mxsusers")
			->setValue("userid", $username)
			->setValue("username", $username)
			->setValue("password", $password)
			->setValue("status", $status)
			->add();
			
			$set = new Set();
			$mxsusersSet2 = $set->MboSetRemote("mxsusers")->setWhere($wc)->MboRemote("mxsusersid")->getMboSet();
			$mxsusers = $mxsusersSet2->getMbo();
			$id = $mxsusersSet2->getString("mxsusersid");
			$ss->msg = "added";
			$ss->status = $status;
			$ss->id = $id;
			$ss->username = $username;
		}else{
			$mxsusers = $mxsusersSet->getMbo();
			$status = $mxsusers->getString("status");
			$set = new Set();
			$set->MboSetRemote("mxsusers")->setWhere($wc)
			->setValue("password", $password)->change();
			$ss->msg = "changed";
			$ss->status = $status;
			$ss->id = $id;
			$ss->username = $username;
		}
		
		
		
		
		Set::throws($ss);
	}
	public static function deleteUsers(){
		$id = $_POST["deleteUsers"];
		$set = new Set();
		$set->MboSetRemote("mxsusers")->setWhere("mxsusersid='$id'")->remove();
		
		$ss = new stdClass;
		$ss->msg = "deleted";
		Set::throws($ss);
	}

	
	public static function getUsers($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
	
		$searchstr = $_POST["getUsers"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$orderby = "mxsusers.userid asc limit $minid,$length";
		
		
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("mxsusers")
			->order("$orderby")
			->MboRemote("mxsusers.userid")->MboRemote("mxsusers.status")
			->MboRemote("mxsusers.mxsusersid")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("mxsusers")
			->order("$orderby")
			->setWhere($whereclause)
			->MboRemote("mxsusers.userid")->MboRemote("mxsusers.status")
			->MboRemote("mxsusers.mxsusersid")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		
		
		
		
		$fsname = ContextPath::getFullHostName();
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->userid = $table->getString("userid");
			$obj_row->status = $table->getString("status");
			$obj_row->mxsusersid = $table->getString("mxsusersid");
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("mxsusers")
			->MboRemote("count(mxsusersid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("mxsusers")
			->setWhere($whereclause)
			->MboRemote("count(mxsusersid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->searchstr = $whereclause;
		$data->countdata = $count;
		Set::throws($data);
		
	}
}


?>