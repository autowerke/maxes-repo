<?php
require_once ("../../../../eat.php");
header("Content-type:application/json");
Class ActionSet {
	public static function SearchExpList(){
		$ss = $_POST["searchexplist"]; 
		$sd = json_decode($ss);
		$jenispengeluaran = $sd->jenispengeluaran;
		$fromcostdate = $sd->fromcostdate;
		$tocostdate = $sd->tocostdate;
		$caripengeluaran = $sd->caripengeluaran;
		
		$arrClause = [];
		
		if($jenispengeluaran == "") $clause = "";
		else {
			$clause = "laundrycosttype like '%$jenispengeluaran%'";
			array_push($arrClause, $clause);
		}
		
		if($caripengeluaran == "") $clause = "";
		else {
			$clause = "laundrycosttype like '%$caripengeluaran%'";
			array_push($arrClause, $clause);
		}
		
		if($fromcostdate == "") $fromcostdate = "";
		else  {
			$clause = "costdate>='$fromcostdate 00:00:00'";
			array_push($arrClause, $clause);
		}
		
		if($tocostdate == "") $tocostdate = "";
		else  {
			$clause = "costdate<='$tocostdate 23:59:00'";
			array_push($arrClause, $clause);
		}
		
		$whereclause = implode($arrClause, " and ");
		
		$ss= new stdClass;
		$ss->whereclause = $whereclause;
		Set::throws($ss);
	}
	
	public static function SearchNotaList(){
		$ss = $_POST["searchnotalist"]; 
		$sd = json_decode($ss);
		
		$telpon = $sd->telpon;
		$nama = $sd->nama;
		$alamat = $sd->alamat;
		$fromorderdate = $sd->fromorderdate;
		$toorderdate = $sd->toorderdate;
		$laundrynum = $sd->laundrynum;
		
		$arrClause = [];
		if($laundrynum == "") $clause = "";
		else {
			$clause = "laundrynum like '%$laundrynum%'";
			array_push($arrClause, $clause);
		}
		
		
		if($nama == "") $clause = "";
		else {
			$clause = "customercode like '%$nama%'";
			array_push($arrClause, $clause);
		}
		
		if($telpon == "") $telpon = "";
		else  {
			$clause = "phone like '%$telpon%'";
			array_push($arrClause, $clause);
		}	
		
		if($fromorderdate == "") $fromorderdate = "";
		else  {
			$clause = "orderdate>='$fromorderdate 00:00:00'";
			array_push($arrClause, $clause);
		}
		
		if($toorderdate == "") $toorderdate = "";
		else  {
			$clause = "orderdate<='$toorderdate 23:59:00'";
			array_push($arrClause, $clause);
		}
		
		if($alamat == "") $alamat = "";
		else  {
			$clause = "address like '%$alamat%'";
			array_push($arrClause, $clause);
		}
		
		$whereclause = implode($arrClause, " and ");
		
		$ss= new stdClass;
		$ss->whereclause = $whereclause;
		Set::throws($ss);
	}
	
	public static function DeleteRecordLaundryCost(){
		$del = $_POST["dellaundrycost"];
		$set = new Set();
		$set->MboSetRemote("laundrycost")->setWhere("laundrycostid='$del'")->remove();
		
		$ms = new stdClass;
		$ms->msg = "deleted";
		Set::throws($ms);
		
		
		
	}
	public static function DeleteNota(){
		$delnota = $_POST["delNota"];
		$set = new Set();
		$set->MboSetRemote("laundry")->setWhere("laundrynum='$delnota'")->remove();
		
		$set = new Set();
		$set->MboSetRemote("laundryline")->setWhere("laundrynum='$delnota'")->remove();
		$ms = new stdClass;
		$ms->msg = "deleted";
		Set::throws($ms);
	}	
	public static function ModifyNota(){
		$nomorNota = $_POST["modNota"];
		
		$set = new Set();
		$laundrySet = $set->MboSetRemote("laundry")->setWhere("laundrynum='$nomorNota'")
		->MboRemote("laundrynum")->MboRemote("description")->MboRemote("customercode")->MboRemote("phone")
		->MboRemote("orderdate")->MboRemote("startdate")->MboRemote("enddate")->MboRemote("totalkg")
		->MboRemote("distanceprice")->MboRemote("discountpercentage")->MboRemote("discountprice")->MboRemote("totalprice")
		->MboRemote("address")->MboRemote("service")->MboRemote("status")->MboRemote("statusdate")->MboRemote("enterby")
		->MboRemote("enterdate")->MboRemote("changeby")->MboRemote("changedate")->MboRemote("deliverydate")->MboRemote("dp")
		->MboRemote("distance")->MboRemote("subtotalprice")->MboRemote("sisa")
		->getMboSet();
		$count = $laundrySet->counts();
		if($count == 0){
			$msg = "notexist";
		}else{
			$msg = "exist";
		}
		
		$ob = new stdClass;		
		$laundry = $laundrySet->getMbo();
		$ob->laundrynum = $laundry->getString("laundrynum");
		$ob->description = $laundry->getString("description");
		$ob->customercode = $laundry->getString("customercode");
		$ob->phone = $laundry->getString("phone");
		if($ob->phone == "" || $ob->phone == null){
			$set2 = new Set();
			$customerSet = $set2->MboSetRemote("customer")->setWhere("customercode='".$ob->customercode."'")->MboRemote("billphone")->getMboSet();
			$cust = $customerSet->getMbo();
			$billphone = $cust->getString("billphone");
			$ob->phone = $billphone;
		}
		$ob->orderdate = $laundry->getString("orderdate");
		$ob->startdate = $laundry->getString("startdate");
		$ob->enddate = $laundry->getString("enddate");
		$ob->totalkg = $laundry->getString("totalkg");
		$ob->distanceprice = $laundry->getString("distanceprice");
		$ob->discountpercentage = $laundry->getString("discountpercentage");
		$ob->discountprice = $laundry->getString("discountprice");
		$ob->totalprice = $laundry->getString("totalprice");
		$ob->address = $laundry->getString("address");
		if($ob->address == "" || $ob->address == null){
			$set2 = new Set();
				$customerSet = $set2->MboSetRemote("customer")->setWhere("customercode='".$ob->customercode."'")->MboRemote("billaddress")->getMboSet();
				$cust = $customerSet->getMbo();
				$billaddress = $cust->getString("billaddress");
				$ob->address = $billaddress;
		}
		$ob->service = $laundry->getString("service");
		$ss = new Set();
		$LaundryTypeSet = $ss->MboSetRemote("laundrytype")->setWhere("laundrytype like '%".$ob->service."%'")
		->MboRemote("description")->MboRemote("price")->MboRemote("length")
		->MboRemote("laundrytypeid")
		->getMboSet();
		$LaundryType = $LaundryTypeSet->getMbo();
		$ob->ltype_description = $LaundryType->getString("description");
		$ob->ltype_price = $LaundryType->getString("price");
		$ob->ltype_length = $LaundryType->getString("length");
		$ob->status = $laundry->getString("status");
		$ob->statusdate = $laundry->getString("statusdate");
		$ob->enterby = $laundry->getString("enterby");
		$ob->enterdate = $laundry->getString("enterdate");
		$ob->changeby = $laundry->getString("changeby");
		$ob->changedate = $laundry->getString("changedate");
		$ob->deliverydate = $laundry->getString("deliverydate");
		$ob->dp = $laundry->getString("dp");
		$ob->sisa = $laundry->getString("sisa");
		$ob->subtotalprice = $laundry->getString("subtotalprice");
		$ob->distance = $laundry->getString("distance");
		$ob->msg = $msg;
		
		
		$laundrylineArr = [];
		$set = new Set();
		$laundrylineSet = $set->MboSetRemote("laundryline")->setWhere("laundrynum='$nomorNota'")
		->MboRemote("laundrylinenum")
		->MboRemote("description")
		->MboRemote("unitprice")
		->MboRemote("quantity")
		->MboRemote("laundryname")
		->getMboSet();
		
		$count = $laundrylineSet->counts();
		if($count == 0){
			$laundrylineArr = [];
		}
		else{
			
			for($i=0; $i<$count; $i++){
				$laundryline = $laundrylineSet->getMbo();
				$ll=  new stdClass;
				$ll->laundrylinenum = $laundryline->getString("laundrylinenum");
				$ll->description = $laundryline->getString("description");
				$ll->unitprice = $laundryline->getString("unitprice");
				$ll->quantity = $laundryline->getString("quantity");
				$ll->laundryname = $laundryline->getString("laundryname");
				array_push($laundrylineArr, $ll);
			}
		}
		
		$set = new Set();
		$laundryLineSet = $set->MboSetRemote("laundryline")->setWhere("laundrynum='$nomorNota'")->MboRemote("sum(lineprice) as lineprice")->getMboSet();
		$laundryline = $laundryLineSet->getMbo();
		$totallaundrysatuan = $laundryline->getString("lineprice");
		if($totallaundrysatuan==null) $totallaundrysatuan=0;
		
		$od = new stdClass;
		$od->result = $ob;
		$od->laundryline = $laundrylineArr;
		$od->laundrylineCount = $count;
		$od->totallaundrysatuan = $totallaundrysatuan;
		
		Set::throws($od);
	}
	
	public static function EntryExpense(){
		$en = json_decode($_POST["entryexpenses"]);
		$exp = $en->entryexpenses;
		
		$tglpengeluaran = $exp->tglpengeluaran;
		$jenispengeluaran = $exp->jenispengeluaran;
		$jumlahpengeluaran = $exp->jumlahpengeluaran;
		$catatan = $exp->catatan;
		$laundrycostid = $exp->laundrycostid;
		
		if($laundrycostid != "" || $laundrycostid != null){
			$set = new Set();
			$laundryCostSet = $set->MboSetRemote("laundrycost")->setWhere("laundrycostid='$laundrycostid'")->setValue("description", $catatan)
			->setValue("cost", $jumlahpengeluaran)->setValue("costdate", $tglpengeluaran)->change();
		
		}else{
			$set = new Set();
			$laundryCostSet = $set->MboSetRemote("laundrycost")->setValue("laundrycosttype", $jenispengeluaran)->setValue("description", $catatan)
			->setValue("cost", $jumlahpengeluaran)->setValue("costdate", $tglpengeluaran)->add();
		
		}
		
		
		$ss = new stdClass;
		$ss->msg = "added";
		Set::throws($ss);
	}
	
	public static function EntryNota(){
		$en = json_decode($_POST["entrynota"]);
		$ls = json_decode($_POST["laundrysatuan"]);
		
		$inputoredit = $en->inputoredit;
		$enddate = $en->tglselesai;
		$service = $en->layanan;
		$distanceprice = $en->ongkir;
		$discountpercentage = $en->diskonpersen;
		$description = $en->catatan;
		$tgldelivery = $en->tgldelivery;
		$dp = $en->en;
		$laundrynum = $en->laundrynum;
		$totalkg = $en->totalkg;
		$sisa = $en->sisa;
		$discountprice = $en->diskonharga;
		$subtotalprice = $en->subtotal;
		$totalprice = $en->hargatotal;
		$startdate = $en->tgldiproses;
		$customercode = $en->customer;
		$orderdate = $en->tglmasuk;
		$phone = $en->phone;
		$address = $en->alamat;
		$distance = "0";
		$status = "IN";
		$statusdate = $orderdate;
		$enterby = "maxesadmin";
		$changeby = $enterby;
		$changedate = $statusdate;
		
		if($inputoredit=="modifyitem"){
			$set = new Set();
			$set->MboSetRemote("laundry")->setWhere("laundrynum='$laundrynum'")->setValue("enddate",$enddate )->setValue("service", $service)->setValue("distanceprice", $distanceprice)
				->setValue("discountpercentage", $discountpercentage)->setValue("description", $description )
				->setValue("deliverydate", $tgldelivery)->setValue("dp", $dp)->setValue("laundrynum", $laundrynum)->setValue("totalkg", $totalkg)
				->setValue("sisa", $sisa )->setValue("discountprice", $discountprice)->setValue("subtotalprice", $subtotalprice )->setValue("totalprice",$totalprice )
				->setValue("startdate", $startdate )->setValue("customercode", $customercode )->setValue("orderdate", $orderdate )
				->setValue("distance", $distance)->setValue("phone", $phone)->setValue("status",$status )->setValue("address", $address)
				->setValue("statusdate", $statusdate)->setValue("enterby", $enterby)->setValue("changeby", $changeby )->setValue("changedate", $changedate)
				->change();
				$msg = "updated";
		}else{
			$set = new Set();
			$laundrySet = $set->MboSetRemote("laundry")->setWhere("laundrynum='$laundrynum'")->MboRemote("laundryid")->getMboSet();
			$counts = $laundrySet->counts();
			if($counts > 0){
				$msg = "exist";
			}else{
				$set = new Set();
				$set->MboSetRemote("laundry")
				->setValue("enddate",$enddate )->setValue("service", $service)->setValue("distanceprice", $distanceprice)
				->setValue("discountpercentage", $discountpercentage)->setValue("description", $description )
				->setValue("deliverydate", $tgldelivery)->setValue("dp", $dp)->setValue("laundrynum", $laundrynum)->setValue("totalkg", $totalkg)
				->setValue("sisa", $sisa )->setValue("discountprice", $discountprice)->setValue("subtotalprice", $subtotalprice )->setValue("totalprice",$totalprice )
				->setValue("startdate", $startdate )->setValue("customercode", $customercode )->setValue("orderdate", $orderdate )
				->setValue("distance", $distance)->setValue("phone", $phone)->setValue("status",$status )->setValue("address", $address)
				->setValue("statusdate", $statusdate)->setValue("enterby", $enterby)->setValue("changeby", $changeby )->setValue("changedate", $changedate)
				->add();
				$msg = "newitem";
				
			}
			
		}
		
		
		$ll = new Set();
		$laundrylineSet = $ll->MboSetRemote("laundryline")->setWhere("laundrynum='$laundrynum'")->getMboSet();
		$countLaundryLine = $laundrylineSet->counts();
		
		if($countLaundryLine == 0){
			$laundrysatuan = $ls->laundrysatuan;
			$count = count($laundrysatuan);
			$$laundrylinenum = 0;
			
			if($count>0){
				$laundrylinenum = 0;
				for($i=0; $i<$count; $i++){
					$laundrylinenum = $$laundrylinenum+10;
					$unitprice = $laundrysatuan[$i]->harga;
					$description = $laundrysatuan[$i]->description;
					$orderqty = $laundrysatuan[$i]->orderqty;
					$laundryname = $laundrysatuan[$i]->jenislaundry;
					$lineprice = $orderqty*$unitprice;
					
					$set = new Set();
					$set->MboSetRemote("laundryline")
					->setValue("laundrylinenum", $laundrylinenum)
					->setValue("laundrynum", $laundrynum)
					->setValue("unitprice", $unitprice)
					->setValue("description", $description)
					->setValue("quantity", $orderqty)
					->setValue("laundryname", $laundryname)
					->setValue("lineprice", $lineprice)
					->add();
				}
			}
			
		}else{
			$set = new Set();
			$set->MboSetRemote("laundryline")->setWhere("laundrynum='$laundrynum'")->remove();
			$laundrysatuan = $ls->laundrysatuan;
			$count = count($laundrysatuan);
			if($count>0){
				$laundrylinenum = 0;
				for($i=0; $i<$count; $i++){
					$laundrylinenum = $$laundrylinenum+10;
					$unitprice = $laundrysatuan[$i]->harga;
					$description = $laundrysatuan[$i]->description;
					$orderqty = $laundrysatuan[$i]->orderqty;
					$laundryname = $laundrysatuan[$i]->jenislaundry;
					$lineprice = $orderqty*$unitprice;
					
					$set = new Set();
					$set->MboSetRemote("laundryline")
					->setValue("laundrylinenum", $laundrylinenum)
					->setValue("laundrynum", $laundrynum)
					->setValue("unitprice", $unitprice)
					->setValue("description", $description)
					->setValue("quantity", $orderqty)
					->setValue("laundryname", $laundryname)
					->setValue("lineprice", $lineprice)
					->add();
				}
			}
		}
		$ss = new stdClass();
		$ss->msg = $msg;
		Set::throws($ss);
	
	}
	
	public static function getNota($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
	
		$searchstr = $_POST["getNota"];
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
			//$whereclause = " customercode like '%".$searchstr."%' or phone like '%".$searchstr."%' or address like '%".$searchstr."%' or laundrynum like '%".$searchstr."%' or service like '%".$searchstr."%'";
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
		
		$orderby = "laundry.laundrynum asc limit $minid,$length";
		if($searchstr == "all"){
			$LaundryTypeSet = $set->MboSetRemote("laundry")
			->order("$orderby")
			->MboRemote("laundry.laundrynum")->MboRemote("laundry.description")->MboRemote("laundry.customercode")->MboRemote("laundry.phone")
			->MboRemote("laundry.orderdate")->MboRemote("laundry.startdate")->MboRemote("laundry.enddate")->MboRemote("laundry.totalkg")
			->MboRemote("laundry.subtotalprice")->MboRemote("laundry.totalprice")->MboRemote("laundry.service")->MboRemote("laundry.status")
			->MboRemote("laundry.address")->MboRemote("laundry.subtotalprice")
			->getMboSet();
		}else{
			$LaundryTypeSet = $set->MboSetRemote("laundry")
			->setWhere($whereclause)->order("$orderby")
			->MboRemote("laundry.laundrynum")->MboRemote("laundry.description")->MboRemote("laundry.customercode")->MboRemote("laundry.phone")
			->MboRemote("laundry.orderdate")->MboRemote("laundry.startdate")->MboRemote("laundry.enddate")->MboRemote("laundry.totalkg")
			->MboRemote("laundry.subtotalprice")->MboRemote("laundry.totalprice")->MboRemote("laundry.service")->MboRemote("laundry.status")
			->MboRemote("laundry.address")->MboRemote("laundry.subtotalprice")
			->getMboSet();
		}
		
		$count = $LaundryTypeSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$laundryType = $LaundryTypeSet->getMbo();
			$obj_row->laundrynum = $laundryType->getString("laundrynum");
			$obj_row->description = $laundryType->getString("description");
			$obj_row->customercode = $laundryType->getString("customercode");
			$obj_row->phone = $laundryType->getString("phone");
			
			if($obj_row->phone == null || $obj_row->phone == ""){
				$set2 = new Set();
				$customerSet = $set2->MboSetRemote("customer")->setWhere("customercode='".$obj_row->customercode."'")->MboRemote("billphone")->getMboSet();
				$cust = $customerSet->getMbo();
				$billphone = $cust->getString("billphone");
				$obj_row->phone = $billphone;
			}
			$obj_row->orderdate = $laundryType->getString("orderdate");
			$obj_row->startdate = $laundryType->getString("startdate");
			$obj_row->enddate = $laundryType->getString("enddate");
			$obj_row->totalkg = $laundryType->getString("totalkg");
			$obj_row->subtotalprice = $laundryType->getString("subtotalprice");
			$obj_row->totalprice = $laundryType->getString("totalprice");
			$obj_row->service = $laundryType->getString("service");
			$obj_row->status = $laundryType->getString("status");
			$obj_row->billaddress = $laundryType->getString("address");
			
			if($obj_row->billaddress == null || $obj_row->billaddress==""){
				$set2 = new Set();
				$customerSet = $set2->MboSetRemote("customer")->setWhere("customercode='".$obj_row->customercode."'")->MboRemote("billaddress")->getMboSet();
				$cust = $customerSet->getMbo();
				$billaddress = $cust->getString("billaddress");
				$obj_row->billaddress = $billaddress;		
			}
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundry")->MboRemote("count(laundryid) totaldata")->getMboSet();
			$laundryType = $LaundryTypeSet->getMbo();
			$totaldata = $laundryType->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundry")->MboRemote("sum(totalkg) totalkg")->getMboSet();
			$laundryType = $LaundryTypeSet->getMbo();
			$sumtotalkg = $laundryType->getString("totalkg");
			if($sumtotalkg == "") $sumtotalkg = 0;
			
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundry")->MboRemote("sum(totalprice) totalprice")->getMboSet();
			$laundryType = $LaundryTypeSet->getMbo();
			$sumtotalprice = $laundryType->getString("totalprice");
			if($sumtotalprice == "") $sumtotalprice = 0;
			
			
		}else{
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundry")->setWhere($whereclause)->MboRemote("count(laundryid) totaldata")->getMboSet();
			$laundryType = $LaundryTypeSet->getMbo();
			$totaldata = $laundryType->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundry")->setWhere($whereclause)->MboRemote("sum(totalkg) totalkg")->getMboSet();
			$laundryType = $LaundryTypeSet->getMbo();
			$sumtotalkg = $laundryType->getString("totalkg");
			if($sumtotalkg == "") $sumtotalkg = 0;
			
			
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundry")->setWhere($whereclause)->MboRemote("sum(totalprice) totalprice")->getMboSet();
			$laundryType = $LaundryTypeSet->getMbo();
			$sumtotalprice = $laundryType->getString("totalprice");
			if($sumtotalprice == "") $sumtotalprice = 0;
		}
		
		
		
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->totalkg = $sumtotalkg;
		$data->totalrupiah = $sumtotalprice;
		$data->searchstr = $whereclause;
		Set::throws($data);
		
	}
	
	public static function DeleteRecordPelanggan(){
		$kodecustomer = $_POST["delpel"];
		$set = new Set();
		$set->MboSetRemote("customer")->setWhere("customercode = '$kodecustomer'")->remove();
		$msg = "deleted";
		
		$ss = new stdClass();
		$ss->msg = $msg;
		Set::throws($ss);
	}
	
	public static function DeleteRecordJenisPengeluaran(){
		$jenpeng = $_POST["deljenpeng"];
		$set = new Set();
		$set->MboSetRemote("laundrycosttype")->setWhere("laundrycosttype = '$jenpeng'")->remove();
		$msg = "deleted";
		
		$ss = new stdClass();
		$ss->msg = $msg;
		Set::throws($ss);
	}
	public static function DeleteRecordJenisLaundry(){
		$jenislaundry = $_POST["deljenlau"];
		$set = new Set();
		$set->MboSetRemote("laundrytype2")->setWhere("laundrytype = '$jenislaundry'")->remove();
		$msg = "deleted";
		
		$ss = new stdClass();
		$ss->msg = $msg;
		Set::throws($ss);
	}
	
	
	public static function DeleteRecordJenisLayanan(){
		$tipelayanan = $_POST["deljenlay"];
		$set = new Set();
		$set->MboSetRemote("laundrytype")->setWhere("laundrytype = '$tipelayanan'")->remove();
		$msg = "deleted";
		
		$ss = new stdClass();
		$ss->msg = $msg;
		Set::throws($ss);
	}
	
	public static function ModifyRecordPelanggan(){
		$modjenlay = $_POST["modpel"];
		$kodecustomer = $_POST["kodecustomer"];
		$nama = $_POST["nama"]; //name 
		$alamat = $_POST["alamat"]; //billaddress
		$telpon = $_POST["telpon"]; //billphone
		
		
		$msg = null;
		$set = new Set();
		if($modjenlay == "modifyitem"){
			$set->MboSetRemote("customer")->setWhere("customercode = '$kodecustomer'")
			->setValue("name", $nama)->setValue("billaddress", $alamat)->setValue("billphone", $telpon)->change();
			$msg = "updated";
		}else{
			if($modjenlay == "newitem"){
				$laundrytypeSet = $set->MboSetRemote("customer")->setWhere("customercode = '$kodecustomer'")->MboRemote("customercode")->getMboSet();
				$count = $laundrytypeSet->counts();
				if($count == 0){
					$set->MboSetRemote("customer")
					->setValue("customercode", $kodecustomer)
					->setValue("name", $nama)
					->setValue("billaddress", $alamat)
					->setValue("billphone", $telpon)
					->add();
					$msg = "added";
				}else{
					$msg = "exist";
				}
			}
		}
		
		$aa = new stdClass();
		$aa->msg = $msg;
		Set::throws($aa);
		
	}
	
	
	public static function ModifyRecordJenisLaundry(){
		$modjenlay = $_POST["modjenlau"];
		$jenislaundry = $_POST["jenislaundry"];
		$deskripsi = $_POST["deskripsi"];
		$harga = $_POST["harga"];
		
		
		$msg = null;
		$set = new Set();
		if($modjenlay == "modifyitem"){
			$set->MboSetRemote("laundrytype2")->setWhere("laundrytype = '$jenislaundry'")
			->setValue("description", $deskripsi)->setValue("price", $harga)->change();
			$msg = "updated";
		}else{
			if($modjenlay == "newitem"){
				$laundrytypeSet = $set->MboSetRemote("laundrytype2")->setWhere("laundrytype = '$jenislaundry'")->MboRemote("laundrytype")->getMboSet();
				$count = $laundrytypeSet->counts();
				if($count == 0){
					$set->MboSetRemote("laundrytype2")->setValue("laundrytype", $jenislaundry)
					->setValue("description", $deskripsi)->setValue("price", $harga)->add();
					$msg = "added";
				}else{
					$msg = "exist";
				}
			}
		}
		
		$aa = new stdClass();
		$aa->msg = $msg;
		Set::throws($aa);
		
	}
	
	public static function ModifyRecordJenisLayanan(){
		$modjenlay = $_POST["modjenlay"];
		$tipelayanan = $_POST["tipelayanan"];
		$deskripsi = $_POST["deskripsi"];
		$harga = $_POST["harga"];
		$hari = $_POST["hari"];
		
		$msg = null;
		$set = new Set();
		if($modjenlay == "modifyitem"){
			$set->MboSetRemote("laundrytype")->setWhere("laundrytype = '$tipelayanan'")
			->setValue("description", $deskripsi)->setValue("price", $harga)->setValue("length", $hari)->change();
			$msg = "updated";
		}else{
			if($modjenlay == "newitem"){
				
				$laundrytypeSet = $set->MboSetRemote("laundrytype")->setWhere("laundrytype = '$tipelayanan'")->MboRemote("laundrytype")->getMboSet();
				$count = $laundrytypeSet->counts();
				if($count == 0){
					$set->MboSetRemote("laundrytype")->setValue("laundrytype", $tipelayanan)
					->setValue("description", $deskripsi)->setValue("price", $harga)->setValue("length", $hari)->add();
					$msg = "added";
				}else{
					$msg = "exist";
				}
			}
		}
		$aa = new stdClass();
		$aa->msg = $msg;
		Set::throws($aa);
		
	}
	
	
	
	public static function getDataPelanggan($minid=null, $length=null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$getdp = $_POST["getDataPelanggan"];
		if($getdp == "all"){
			$whereclause = "";
		}else{
			$whereclause = " customercode like '%".$getdp."%' or billphone like '%".$getdp."%' or billaddress like '%".$getdp."%' or name like '%".$getdp."%'";
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
		
		$orderby = "name asc limit $minid,$length";
		if($getdp == "all"){
			$customerSet = $set->MboSetRemote("customer")->order("$orderby")
			->MboRemote("customercode")->MboRemote("name")->MboRemote("billaddress")->MboRemote("billphone")->MboRemote("customerid")
			->getMboSet();
		}else{
			$customerSet = $set->MboSetRemote("customer")->setWhere($whereclause)
			->MboRemote("customercode")->MboRemote("name")->MboRemote("billaddress")->MboRemote("billphone")->MboRemote("customerid")
			->getMboSet();
		}
		
		
		$count = $customerSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$cust = $customerSet->getMbo();
			$obj_row->kodecustomer = $cust->getString("customercode");
			$obj_row->nama = $cust->getString("name");
			$obj_row->alamat = $cust->getString("billaddress");
			$obj_row->telpon = $cust->getString("billphone");
			$obj_row->id = $cust->getString("customerid");
			if($obj_row->id >= $lastdata) $lastdata = $obj_row->id;
			array_push($trr, $obj_row);
		}
		
		if($getdp == "all"){
		
			
			$set = new Set();
			$customerSet  =  $set->MboSetRemote("customer")->MboRemote("count(customerid) totaldata")->getMboSet();
			$customer = $customerSet->getMbo();
			$totaldata = $customer->getString("totaldata");
		}else{
			$set = new Set();
			$customerSet  =  $set->MboSetRemote("customer")->setWhere($whereclause)->MboRemote("max(customerid) maxid")->getMboSet();
			$customer = $customerSet->getMbo();
			$maxid = $customer->getString("maxid");
			
			$set = new Set();
			$customerSet  =  $set->MboSetRemote("customer")->setWhere($whereclause)->MboRemote("count(customerid) totaldata")->getMboSet();
			$customer = $customerSet->getMbo();
			$totaldata = $customer->getString("totaldata");
		}
		
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->tshoot = $orderby;
		Set::throws($data);
	}
	
	public static function getJenisLaundry($minid=null, $length=null){
		
		$defaultlength = 10;
		$defaultminid = 1;
		$searchstr = $_POST["getJenisLaundry"];
		
		
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
		$orderby = "laundrytype asc limit $minid,$length";
		if($searchstr == "all"){
			$laundryType2Set = $set->MboSetRemote("laundrytype2")->order("$orderby")
			->MboRemote("laundrytype")->MboRemote("description")->MboRemote("price")->MboRemote("laundrytype2id")
			->getMboSet();
		}else{
			$laundryType2Set = $set->MboSetRemote("laundrytype2")->setWhere("laundrytype like '%".$searchstr."%'")
			->MboRemote("laundrytype")->MboRemote("description")->MboRemote("price")->MboRemote("laundrytype2id")
			->getMboSet();
		}
		
		
		$count = $laundryType2Set->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$laundryType2 = $laundryType2Set->getMbo();
			$obj_row->laundrytype = $laundryType2->getString("laundrytype");
			$obj_row->description = $laundryType2->getString("description");
			$obj_row->price = $laundryType2->getString("price");
			$obj_row->id = $laundryType2->getString("laundrytype2id");
			if($obj_row->id >= $lastdata) $lastdata = $obj_row->id;
			array_push($trr, $obj_row);
		}
		$set = new Set();
		if($searchstr == "all"){
			$set = new Set();
			$laundryType2Set  =  $set->MboSetRemote("laundrytype2")->MboRemote("count(laundrytype2id) totaldata")->getMboSet();
			$laundryType2 = $laundryType2Set->getMbo();
			$totaldata = $laundryType2->getString("totaldata");
		}else{
			$laundryType2Set  =  $set->MboSetRemote("laundrytype2")->setWhere("laundrytype like '%".$searchstr."%'")->MboRemote("max(laundrytype2id) maxid")->getMboSet();
			$laundryType2 = $laundryType2Set->getMbo();
			$maxid = $laundryType2->getString("maxid");
			
			$set = new Set();
			$laundryType2Set  =  $set->MboSetRemote("laundrytype2")->setWhere("laundrytype like '%".$searchstr."%'")->MboRemote("count(laundrytype2id) totaldata")->getMboSet();
			$laundryType2 = $laundryType2Set->getMbo();
			$totaldata = $laundryType2->getString("totaldata");
		}
		
		//lastdata menjadi next minimalid;
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		Set::throws($data);
	}
	
	public static function getJenisLayanan($minid=0, $length=0){
		$defaultlength = 4;
		$defaultminid = 0;
		$searchstr = $_POST["getJenisLayanan"];
		
			
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
		
		$orderby = "laundrytype asc limit $minid,$length";
		if($searchstr == "all"){
			$LaundryTypeSet = $set->MboSetRemote("laundrytype")->order("$orderby")
			->MboRemote("laundrytype")->MboRemote("description")->MboRemote("price")->MboRemote("length")
			->MboRemote("laundrytypeid")
			->getMboSet();
		}else{
			$LaundryTypeSet = $set->MboSetRemote("laundrytype")->setWhere("laundrytype like '%".$searchstr."%'")
			->MboRemote("laundrytype")->MboRemote("description")->MboRemote("price")->MboRemote("length")
			->MboRemote("laundrytypeid")
			->getMboSet();
		}
		
		$count = $LaundryTypeSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$laundryType = $LaundryTypeSet->getMbo();
			$obj_row->laundrytype = $laundryType->getString("laundrytype");
			$obj_row->description = $laundryType->getString("description");
			$obj_row->price = $laundryType->getString("price");
			$obj_row->hari = $laundryType->getString("length");
			$obj_row->id = $laundryType->getString("laundrytypeid");
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundrytype")->MboRemote("count(laundrytypeid) totaldata")->getMboSet();
			$laundryType = $LaundryTypeSet->getMbo();
			$totaldata = $laundryType->getString("totaldata");
		}else{
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundrytype")->setWhere("laundrytype like '%".$searchstr."%'")->MboRemote("max(laundrytypeid) maxid")->getMboSet();
			$LaundryType = $LaundryTypeSet->getMbo();
			$maxid = $LaundryType->getString("maxid");
			
			$set = new Set();
			$LaundryTypeSet  =  $set->MboSetRemote("laundrytype")->setWhere("laundrytype like '%".$searchstr."%'")->MboRemote("count(laundrytypeid) totaldata")->getMboSet();
			$laundryType = $LaundryTypeSet->getMbo();
			$totaldata = $laundryType->getString("totaldata");
		}
		
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		Set::throws($data);
	
	}
	public static function ModifyExpense(){
		$modexp = $_POST["modexp"];
		
		$set = new Set();
		$laundrycostSet = $set->MboSetRemote("laundrycost")->setWhere("laundrycostid='$modexp'")
		->MboRemote("laundrycosttype")->MboRemote("description")->MboRemote("costdate")->MboRemote("cost")->MboRemote("laundrycostid")
		->getMboSet();
		
		$ss = new stdClass;
		$laundrycost = $laundrycostSet->getMbo();
		$ss->laundrycosttype = $laundrycost->getString("laundrycosttype");
		$ss->description = $laundrycost->getString("description");
		$ss->costdate = $laundrycost->getString("costdate");
		$ss->cost = $laundrycost->getString("cost");
		$ss->laundrycostid = $laundrycost->getString("laundrycostid");
		
		Set::throws($ss);
		
		
		
		
		
	}
	public static function ModifyRecordPengeluaran(){
		$modpeng = $_POST["modpeng"];
		$tipepengeluaran = $_POST["tipepengeluaran"];
		$deskripsi = $_POST["deskripsi"];
		
		
		$msg = null;
		$set = new Set();
		if($modpeng == "modifyitem"){
			$set->MboSetRemote("laundrycosttype")->setWhere("laundrycosttype = '$tipepengeluaran'")
			->setValue("description", $deskripsi)->change();
			$msg = "updated";
		}else{
			if($modpeng == "newitem"){
				
				$laundrytypeSet = $set->MboSetRemote("laundrycosttype")->setWhere("laundrycosttype = '$tipepengeluaran'")->MboRemote("laundrycosttype")->getMboSet();
				$count = $laundrytypeSet->counts();
				if($count == 0){
					$set->MboSetRemote("laundrycosttype")->setValue("laundrycosttype", $tipepengeluaran)
					->setValue("description", $deskripsi)->add();
					$msg = "added";
				}else{
					$msg = "exist";
				}
			}
		}
		$aa = new stdClass();
		$aa->msg = $msg;
		Set::throws($aa);
		
	}
	
	
	public static function getLaundryCost($minid=0, $length=0){
		$defaultlength = 4;
		$defaultminid = 0;
		$searchstr = $_POST["getLaundryCost"];
			
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
		
		$orderby = "laundrycosttype asc limit $minid,$length";
		if($searchstr == "all"){
			$laundrycosttypeSet = $set->MboSetRemote("laundrycost")->order("$orderby")
			->MboRemote("laundrycosttype")->MboRemote("description")->MboRemote("costdate")->MboRemote("cost")
			->MboRemote("laundrycostid")
			->getMboSet();
		}else{
			$laundrycosttypeSet = $set->MboSetRemote("laundrycost")->setWhere($searchstr)
			->MboRemote("laundrycosttype")->MboRemote("description")->MboRemote("costdate")->MboRemote("cost")
			->MboRemote("laundrycostid")
			->getMboSet();
		}
		
		$count = $laundrycosttypeSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$lcosttype = $laundrycosttypeSet->getMbo();
			$obj_row->laundrycosttype = $lcosttype->getString("laundrycosttype");
			$obj_row->description = $lcosttype->getString("description");
			$obj_row->costdate = $lcosttype->getString("costdate");
			$obj_row->cost = $lcosttype->getString("cost");
			$obj_row->id = $lcosttype->getString("laundrycostid");
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$laundrycosttypeSet  =  $set->MboSetRemote("laundrycost")->MboRemote("count(laundrycostid) totaldata")->getMboSet();
			$laundrycost = $laundrycosttypeSet->getMbo();
			$totaldata = $laundrycost->getString("totaldata");
		}else{
			$set = new Set();
			$laundrycosttypeSet  =  $set->MboSetRemote("laundrycost")->setWhere($searchstr)->MboRemote("count(laundrycostid) totaldata")->getMboSet();
			$laundrycost = $laundrycosttypeSet->getMbo();
			$totaldata = $laundrycost->getString("totaldata");
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$laundrycosttypeSet  =  $set->MboSetRemote("laundrycost")->MboRemote("sum(cost) totalcost")->getMboSet();
			$laundrycost = $laundrycosttypeSet->getMbo();
			$totalcost = $laundrycost->getString("totalcost");
		}else{
			$set = new Set();
			$laundrycosttypeSet  =  $set->MboSetRemote("laundrycost")->setWhere($searchstr)->MboRemote("sum(cost) totalcost")->getMboSet();
			$laundrycost = $laundrycosttypeSet->getMbo();
			$totalcost = $laundrycost->getString("totalcost");
		}
		
		
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->totalcost = $totalcost;
		$data->countdata = $count;
		Set::throws($data);
	
	}
	
	
	public static function getLaundryCostType($minid=0, $length=0){
		$defaultlength = 4;
		$defaultminid = 0;
		$searchstr = $_POST["getLaundryCostType"];
		
			
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
		
		$orderby = "laundrycosttype asc limit $minid,$length";
		if($searchstr == "all"){
			$laundrycosttypeSet = $set->MboSetRemote("laundrycosttype")->order("$orderby")
			->MboRemote("laundrycosttype")->MboRemote("description")
			->MboRemote("laundrycosttypeid")
			->getMboSet();
		}else{
			$laundrycosttypeSet = $set->MboSetRemote("laundrycosttype")->setWhere("laundrycosttype like '%".$searchstr."%'")
			->MboRemote("laundrycosttype")->MboRemote("description")
			->MboRemote("laundrycosttypeid")
			->getMboSet();
		}
		
		$count = $laundrycosttypeSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$lcosttype = $laundrycosttypeSet->getMbo();
			$obj_row->laundrycosttype = $lcosttype->getString("laundrycosttype");
			$obj_row->description = $lcosttype->getString("description");
			$obj_row->id = $lcosttype->getString("laundrycosttypeid");
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$laundrycosttypeSet  =  $set->MboSetRemote("laundrycosttype")->MboRemote("count(laundrycosttypeid) totaldata")->getMboSet();
			$laundrycost = $laundrycosttypeSet->getMbo();
			$totaldata = $laundrycost->getString("totaldata");
		}else{
			$set = new Set();
			$laundrycosttypeSet  =  $set->MboSetRemote("laundrycosttype")->setWhere("laundrycosttype like '%".$searchstr."%'")->MboRemote("max(laundrycosttypeid) maxid")->getMboSet();
			$laundrycost = $laundrycosttypeSet->getMbo();
			$maxid = $laundrycost->getString("maxid");
			
			$set = new Set();
			$laundrycosttypeSet  =  $set->MboSetRemote("laundrycosttype")->setWhere("laundrycosttype like '%".$searchstr."%'")->MboRemote("count(laundrycosttypeid) totaldata")->getMboSet();
			$laundrycost = $laundrycosttypeSet->getMbo();
			$totaldata = $laundrycost->getString("totaldata");
		}
		
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		Set::throws($data);
	
	}
	
} 

	

?>