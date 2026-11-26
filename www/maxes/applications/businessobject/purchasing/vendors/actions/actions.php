<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");
Class ActionSet {
	public static function changeVendorPassword(){
		$payload = Super::getParam("changeVendorPassword");
		$vendorcontactid = $payload["vendorcontactid"];
		$newpasswod = $payload["new_password"]; 
		
		$hashedPassword = password_hash($newpasswod, PASSWORD_DEFAULT);
		
		(new Set())->MboSetRemote("vendorcontact")->where("vendorcontactid=?", $vendorcontactid)
		->setValue("password", $hashedPassword)->change();
		Super::sendJsonResponse("success", "Password has been changed");
	}

	public static function InitActionVendor()

	{
		$x = explode("^", $_GET["initActionVendor"]);
		$table = $x[0];
		$tableID = $table . "id";
		$tableid = $x[1];
		$set = new Set();
		$tableSet = $set->MboSetRemote($table)->setWhere("$tableID='$tableid'")->MboRemote("vendorcode")->getMboSet()->getMbo();
		$vendorcode = $tableSet->getString("vendorcode");
		$totalbasecost = $set->MboSetRemote("po")->setWhere("po.vendor = '$vendorcode'")->MboRemote("sum(totalbasecost) as totalbasecost")->getMboSet()->getMbo()->getString("totalbasecost");
		$matreccost = $set->MboSetRemote("matrectrans")->setRelationship("po", "matrectrans.ponum = po.ponum")->setWhere("po.vendor = '$vendorcode'")->MboRemote("sum(linecost) as totalmatreccost")->getMboSet()->getMbo()->getString("totalmatreccost");
		$servreccost = $set->MboSetRemote("servrectrans")->setRelationship("po", "servrectrans.ponum = po.ponum")->setWhere("po.vendor = '$vendorcode'")->MboRemote("sum(linecost) as totalservreccost")->getMboSet()->getMbo()->getString("totalservreccost");
		if ($totalbasecost == null) $totalbasecost = 0;
		if ($matreccost == null) $matreccost = 0;
		if ($servreccost == null) $servreccost = 0;
		echo json_encode(["totalbasecost" => $totalbasecost, "matreccost" => $matreccost, "servreccost" => $servreccost]);
	}
	public static function CreateCustomer()
	{
		$pt = explode("^", $_GET["createcustomer"]);
		$vendcode = $pt[0];
		$custcode = $pt[1];
		$custname = $pt[2];
		$rbniacct = $pt[3];
		$set = new Set();
		$vendorSet = $set->MboSetRemote("vendor")->setWhere("vendorcode='$vendcode'")->MboRemote("bankname1")->MboRemote("bankname2")->MboRemote("bankname3")->MboRemote("bankname4")->MboRemote("bankname5")->MboRemote("bankaccount1")->MboRemote("bankaccount2")->MboRemote("bankaccount3")->MboRemote("bankaccount4")->MboRemote("bankaccount5")->MboRemote("contact")->MboRemote("address")->MboRemote("city")->MboRemote("stateprovince")->MboRemote("zippostalcode")->MboRemote("phone")->MboRemote("vendorfax")->MboRemote("rcontact")->MboRemote("raddress")->MboRemote("rcity")->MboRemote("rstateprovince")->MboRemote("rzippostalcode")->getMboSet();
		$vendor = $vendorSet->getMbo();
		$bankname1 = $vendor->getString("bankname1");
		$bankaccount1 = $vendor->getString("bankaccount1");
		$bankname2 = $vendor->getString("bankname2");
		$bankaccount2 = $vendor->getString("bankaccount2");
		$bankname3 = $vendor->getString("bankname3");
		$bankaccount3 = $vendor->getString("bankaccount3");
		$bankname4 = $vendor->getString("bankname4");
		$bankaccount4 = $vendor->getString("bankaccount4");
		$bankname5 = $vendor->getString("bankname5");
		$bankaccount5 = $vendor->getString("bankaccount5");
		$contact = $vendor->getString("contact");
		$address = $vendor->getString("address");
		$city = $vendor->getString("city");
		$stateprovince = $vendor->getString("stateprovince");
		$zippostalcode = $vendor->getString("zippostalcode");
		$phone = $vendor->getString("phone");
		$vendorfax = $vendor->getString("vendorfax");
		$rcontact = $vendor->getString("rcontact");
		$raddress = $vendor->getString("raddress");
		$rcity = $vendor->getString("rcity");
		$rstateprovince = $vendor->getString("rstateprovince");
		$rzippostalcode = $vendor->getString("rzippostalcode");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$isExist = $set->MboSetRemote("customer")->setWhere("customercode='$custcode'")->MboRemote("customercode")->getMboSet()->counts();
		if ($isExist == 0) {
			$set->MboSetRemote("customer")->setValue("customercode", $custcode)->setValue("name", $custname)->setValue("distributor", "N")->setValue("reseller", "N")->setValue("rbniacct", $rbniacct)->setValue("bankname1", $bankname1)->setValue("bankname2", $bankname2)->setValue("bankname3", $bankname3)->setValue("bankname4", $bankname4)->setValue("bankname5", $bankname5)->setValue("bankaccount1", $bankaccount1)->setValue("bankaccount2", $bankaccount2)->setValue("bankaccount3", $bankaccount3)->setValue("bankaccount4", $bankaccount4)->setValue("bankaccount5", $bankaccount5)->setValue("billcontact", $rcontact)->setValue("billaddress", $raddress)->setValue("billzippostalcode", $rzippostalcode)->setValue("billcity", $rcity)->setValue("billstateprovince", $rstateprovince)->setValue("billphone", $phone)->setValue("billcustomerfax", $vendorfax)->setValue("shipcontact", $contact)->setValue("shipaddress", $address)->setValue("shipcity", $city)->setValue("shipstateprovince", $stateprovince)->setValue("shipzippostalcode", $zippostalcode)->setValue("shipphone", $phone)->setValue("shipcustomerfax", $vendorfax)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)->setValue("changeby", $enterby)->add();
			$set->MboSetRemote("customerstatus")->setValue("customercode", $custcode)->setValue("status", 'ACTIVE')->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("remarks", "Created from Vendor: $vendcode ")->add();
			$vendorcontactSet = $set->MboSetRemote("vendorcontact")->setWhere("vendorcode='$vendcode'")->MboRemote("contact")->MboRemote("position")->MboRemote("email")->MboRemote("phone")->MboRemote("officephone")->MboRemote("mobilephone")->MboRemote("fax")->MboRemote("blackberrypin")->MboRemote("wanum")->MboRemote("lineid")->getMboSet();
			$cnt = $vendorcontactSet->counts();
			if ($cnt > 0) {
				$set2 = new Set();
				for ($i = 0; $i < $cnt; $i++) {
					$vendorcontact = $vendorcontactSet->getMbo();
					$contact = $vendorcontact->getString("contact");
					$position = $vendorcontact->getString("position");
					$email = $vendorcontact->getString("email");
					$phone = $vendorcontact->getString("phone");
					$officephone = $vendorcontact->getString("officephone");
					$mobilephone = $vendorcontact->getString("mobilephone");
					$fax = $vendorcontact->getString("fax");
					$blackberrypin = $vendorcontact->getString("blackberrypin");
					$wanum = $vendorcontact->getString("wanum");
					$lineid = $vendorcontact->getString("lineid");
					$set2->MboSetRemote("customercontact")->setValue("customercode", $custcode)->setValue("contactname", $contact)->setValue("function", $position)->setValue("phone", $phone)->setValue("email", $email)->setValue("wanum", $wanum)->setValue("blackberrypin", $blackberrypin)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $changedate)->add();
				}
			}
			Set::throws(["msg" => "created"]);
		}
		else {
			Set::throws(["msg" => "customerexist"]);
		}
	}
}

Class ValidateTables {	
	public static function tbce(){
		$table = $_GET['tbce'];
		if ($table == "mxtable_vendorpolines_child" || $table == "mxtable_vendormatrectrans_child" || $table == "mxtable_vendorservrectrans_child" || $table == "mxtable_receipt_child" || $table == "mxtable_matsaletrans_child" || $table == "mxtable_servsaletrans_child" || $table == "mxtable_soshipment_child" || $table == "mxtable_sotracking_child") 
			echo "histable";
		if ($table == "mxtable_inventory_child" || $table == "mxtable_invvendor_child") echo "relatedtable";
	}
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		$appname = $dl[0];
		$vtable = $dl[2];
		
	}
}
	

?>