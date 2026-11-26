<?php
require_once ("../../../eat.php");

Class ActionSet {
	
	public static function showbuktitransfer(){
		$childtable = $_POST["showbuktitransfer"];
		$rowid = $_POST["rowid"];
		
		$set = new Set();
		$fileinfoSet = $set->MboSetRemote("fileinfo")->setWhere("childid='$rowid'")->MboRemote("filename")->getMboSet();
		$fileinfo = $fileinfoSet->getMbo();
		$imgid = $fileinfo->getString("filename");
		$fsname = ContextPath::getFullHostName();
		$origPath = $fsname . "/attachments/";
		$thumbPath = $fsname . "/attachments/thumbs/";
		$iconPath = $fsname . "/icons/";
		
		if($imgid != ""){
			$img = "<img class=\"maxesil\" src=" . "\"$thumbPath" . "thumb_" . $imgid . "\" data-filename=\"" . $imgid . "\">";
		}else{
			$img = "";
		}
		
		$ss = new stdClass();
		$ss->imgsrc = $img;
		
		
		Set::throws($ss);
	}
	public static function PrintExpense(){
		$fleetusagenum = $_POST["print_exp"];
		$tableid = "expense";
		
		$get = new ActionSet();
		$tableheader = $get->getTableElements($tableid)->tableheader;
		
		$set = new Set();
		$fleetusageCostSet = $set->MboSetRemote("fleetusagecost fc")->setRelationship("fleetusage fu ", "fc.fleetusagenum = fu.fleetusagenum")
		->setWhere("fc.fleetusagenum = '$fleetusagenum'")
		->MboRemote(" fc.fleetusagenum")->MboRemote("fc.fleetcostlinenum")->MboRemote("fc.description")->MboRemote("fc.cost")->MboRemote("fu.sitecode")
		->MboRemote("fu.usagedate")
		->getMboSet();
		$fcount = $fleetusageCostSet->counts();
		
		$tabledatar = [];
		$sumtotalexpense = 0;
		$no = 0;
		for($i=0; $i<$fcount; $i++){
			$fleetusageCost = $fleetusageCostSet->getMbo();
			$fleetusagenum = $fleetusageCost->getString("fleetusagenum");
			$fleetcostlinenum = $fleetusageCost->getString("fleetcostlinenum");
			$description = $fleetusageCost->getString("description");
			$cost = $fleetusageCost->getString("cost");
			$sitecode = $fleetusageCost->getString("sitecode");
			$usagedate = $fleetusageCost->getString("usagedate");
			$sumtotalexpense = $sumtotalexpense+$cost;
			$no = $no+1;
			$tabledata = "<tr>
					<td width=27 style='width:20pt; border: .5pt solid black;'>$no</td>
					<td width=220 style='width:165pt; border: .5pt solid black;'>$usagedate</td>
					<td width=92 style='width:69pt; border: .5pt solid black;'>$fleetusagenum </td>
					<td width=137 style='width:103pt; border: .5pt solid black;'>$description</td>
					<td width=67 style='width:50pt; border: .5pt solid black; text-align:right;'>$cost</td>
					<td width=101 style='width:76pt; border: .5pt solid black;'>$sitecode</td>
				</tr>";
			array_push($tabledatar, $tabledata);	
		}
		
		$tabledata = implode(" ", $tabledatar);
		$sumtotalexpense = $sumtotalexpense;
		$tablesumfooter = "<tr>
					<td colspan=4><b>TOTAL PENGELUARAN: </b> </td>
					<td colspan=1 width=101 style='width:76pt;border: .5pt solid black; text-align:right;'>$sumtotalexpense</td>
					<td> </td>
				</tr>";
				
		$tablefooter = $tablesumfooter."</table>";
		$ss = new stdClass();
		$ss->tableheader = $tableheader;
		$ss->tabledata = $tabledata;
		$ss->tablefooter = $tablefooter;
		$ss->tableid = $tableid;
		Set::throws($ss);
	}
	
	public function getTableElements($tableid, $startdate = null, $enddate = null){
		$tableheader = "<table class='printxls' id='".$tableid."' border=0 cellpadding=0 cellspacing=0 width=644 style='border-collapse:collapse;table-layout:fixed;width:883pt'>";
		$tableheader1 = "<tr height=28 valign=bottom style='height:21.0pt'>
					<td colspan=6 width=92 style='width:369pt; font-weight:bold;'><font size=5>PT SEMERUTRANSINDO</font></td>
				</tr>";
		$tableheader2 = "<tr height=28 valign=bottom style='height:21.0pt'>
					<td colspan=6 style='font-size:12; font-weight:bold;'>PENGELUARAN</td>
				</tr>";
		$tableheader3 = "<tr height=20 style='height:15.0pt'>
					<td height=20 style='height:15.0pt' colspan=2><b>PERIODE :</b> $startdate - $enddate </td>
					<td colspan=4></td>
				</tr>";
		$tableheader4 = "<tr height=20 valign=bottom style='height:15.0pt'>
					<td height=20  align=center style='height:15.0pt; border: .5pt solid black; font-weight:bold;'>NO</td>
					<td  align=center style='border: .5pt solid black; font-weight:bold;'>HARI/TANGGAL</td>
					<td  style=' border: .5pt solid black; font-weight:bold;'>KETERANGAN</td>
					<td  style=' border: .5pt solid black; font-weight:bold;'>JENIS PENGELUARAN</td>
					<td  style=' border: .5pt solid black; font-weight:bold;'>JUMLAH</span></td>
					<td  style=' border: .5pt solid black; font-weight:bold;'>SITE</td>
				</tr>";
				
		$ss = new stdClass();
		if($startdate == null && $enddate == null){
			$ss->tableheader = $tableheader.$tableheader1.$tableheader2.$tableheader4;
		}else{
			$ss->tableheader = $tableheader.$tableheader1.$tableheader2.$tableheader3.$tableheader4;
		}
		
		return $ss;
	}
	
	public static function getSiteCode(){
		$username = Xbo::getLoginUser();
		$set = new Set();
		$siteSet = $set->MboSetRemote("mxsusers us")->setRelationship(" employees em ", "us.personid = em.personid")->setWhere("us.personid='$username'")
		->MboRemote("em.sitecode")->getMboSet();
		$site = $siteSet->getMbo();
		$sitecode = $site->getString("sitecode");
		return $sitecode;
	}
	public static function PrintExpenseAll(){
		$fleetusagenum = $_POST["print_exp_all"];
		$tableid =  $_POST["tableid"];
		$startdate = $_POST["startdate"];
		$enddate = $_POST["enddate"];
		
		$get = new ActionSet();
		$tableheader = $get->getTableElements($tableid, $startdate, $enddate)->tableheader;
		
		$sitecode = ActionSet::getSiteCode();
		$set = new Set();
		$fleetusageCostSet = $set->MboSetRemote("fleetusagecost fc")->setRelationship("fleetusage fu ", "fc.fleetusagenum = fu.fleetusagenum")
		->setWhere("fu.usagedate >= '$startdate' and fu.usagedate <= '$enddate' and fu.sitecode='$sitecode'")
		->MboRemote(" fc.fleetusagenum")->MboRemote("fc.fleetcostlinenum")->MboRemote("fc.description")->MboRemote("fc.cost")->MboRemote("fu.sitecode")
		->MboRemote("fu.usagedate")
		->getMboSet();
		
		$fcount = $fleetusageCostSet->counts();
		
		$tabledatar = [];
		$sumtotalexpense = 0;
		$no = 0;
		for($i=0; $i<$fcount; $i++){
			$fleetusageCost = $fleetusageCostSet->getMbo();
			$fleetusagenum = $fleetusageCost->getString("fleetusagenum");
			$fleetcostlinenum = $fleetusageCost->getString("fleetcostlinenum");
			$description = $fleetusageCost->getString("description");
			$cost = $fleetusageCost->getString("cost");
			$sitecode = $fleetusageCost->getString("sitecode");
			$usagedate = $fleetusageCost->getString("usagedate");
			$sumtotalexpense = $sumtotalexpense+$cost;
			$no = $no+1;
			$tabledata = "<tr>
					<td width=27 style='width:20pt;border: .5pt solid black;'>$no</td>
					<td width=220 style='width:165pt; border: .5pt solid black;'>$usagedate</td>
					<td width=92 style='width:69pt;border: .5pt solid black;'>$fleetusagenum </td>
					<td width=137 style='width:103pt;border: .5pt solid black;'>$description</td>
					<td width=67 style='width:50pt;border: .5pt solid black; text-align:right;'>$cost</td>
					<td width=101 style='width:76pt;border: .5pt solid black;'>$sitecode</td>
				</tr>";
			array_push($tabledatar, $tabledata);	
		}
		
		
		$set = new Set();
		$invoicetransSet = $set->MboSetRemote("invoicetrans iv")->setWhere("iv.enterdate >= '$startdate' and iv.enterdate <= '$enddate' and wonum is not null ")
		->MboRemote('iv.invoicenum')->MboRemote('iv.description')->MboRemote('iv.linecost')->MboRemote('iv.sitecode')->MboRemote('iv.enterdate')
		->getMboSet();
		
		$ivCnt = $invoicetransSet->counts();
		
		$sumtotalinvoicetrans = 0;
		for($i=0; $i<$ivCnt; $i++){
			$invoicetrans = $invoicetransSet->getMbo();
			$invoicenum = $invoicetrans->getString("invoicenum");
			$description = $invoicetrans->getString("description");
			$linecost = $invoicetrans->getString("linecost");
			$linecost = $linecost*-1;
			$sitecode = $invoicetrans->getString("sitecode");
			$enterdate = $invoicetrans->getString("enterdate");
			$no = $no+1;
			$sumtotalinvoicetrans = $sumtotalinvoicetrans+$linecost;
			
			$tabledata = "<tr>
					<td width=27 style='width:20pt;border: .5pt solid black;'>$no</td>
					<td width=220 style='width:165pt; border: .5pt solid black;'>$enterdate</td>
					<td width=92 style='width:69pt;border: .5pt solid black;'>$invoicenum </td>
					<td width=137 style='width:103pt;border: .5pt solid black;'>$description</td>
					<td width=67 style='width:50pt;border: .5pt solid black; text-align:right;'>$linecost</td>
					<td width=101 style='width:76pt;border: .5pt solid black;'>$sitecode</td>
				</tr>";
			array_push($tabledatar, $tabledata);
			
		}
		
		$tabledata = implode(" ", $tabledatar);
		$sumtotalexpense = $sumtotalexpense + $sumtotalinvoicetrans;
		$tablesumfooter = "<tr>
					<td colspan=4><b>TOTAL PENGELUARAN: </b> </td>
					<td colspan=1 width=101 style='width:76pt;border: .5pt solid black; text-align:right;'>$sumtotalexpense</td>
					<td> </td>
				</tr>";
				
		$tablefooter = $tablesumfooter."</table>";
		$ss = new stdClass();
		$ss->tableheader = $tableheader;
		$ss->tabledata = $tabledata;
		$ss->tablefooter = $tablefooter;
		$ss->tableid = $tableid;
		Set::throws($ss);
	}
	public static function PrintManifest(){
		$fleetusagenum = $_POST["printmanifest"];
		
		$set = new Set();
		$fleetusageSet = $set->MboSetRemote("fleetusage")->setWhere("fleetusagenum = '$fleetusagenum'")
		->MboRemote("orgcode")->MboRemote("driver")->MboRemote("fleetroutenum")->MboRemote("description")
		->MboRemote("usagedate")->MboRemote("assetnum")->getMboSet();
		
		$fu = $fleetusageSet->getMbo();
		
		$orgcode = $fu->getString("orgcode");
		$driver = $fu->getString("driver");
		$fleetroutenum = $fu->getString("fleetroutenum");
		$description = $fu->getString("description");
		$usagedate = $fu->getString("usagedate");
		$assetnum = $fu->getString("assetnum");
		
		$set = new Set();
		$orgSet = $set->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("description")->getMboSet();
		$org = $orgSet->getMbo();
		$orgname = $org->getString("description");
		
		$set = new Set();
		$assetseatSet = $set->MboSetRemote("assetseat")->MboRemote("seattype")->getMboSet();
		$cnt = $assetseatSet->counts();
		
		$seats = [];
		for($i=0; $i<$cnt; $i++){
			$assetseat = $assetseatSet->getMbo();
			$seattype = $assetseat->getString("seattype");
			array_push($seats, $seattype);
		}
		
		$seattype = implode(", ", $seats);
		
		$set = new Set();
		$assetsSet = $set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->MboRemote("description")->getMboSet();
		$asset = $assetsSet->getMbo();
		
		$assetdesc = $asset->getString("description");
		 
		$set = new Set();
		$fleetrouteSet = $set->MboSetRemote("fleetroute")->setWhere("fleetroutenum='$fleetroutenum'")->MboRemote("fromlocation")->MboRemote("tolocation")->getMboSet();
		$fleetroute = $fleetrouteSet->getMbo();
		
		$fromlocation = $fleetroute->getString("fromlocation");
		$tolocation =  $fleetroute->getString("tolocation");
		$header = "<table id='manifest' border=0 cellpadding=0 cellspacing=0 width=1120 style='border-collapse: collapse;table-layout:fixed;width:841pt'> <tr height=20 valign=bottom style='height:15.0pt'> <td height=20 width=64 style='height:15.0pt;width:48pt'></td><td width=95 style='width:71pt'>PO</td><td colspan=5 width=355 style='width:267pt'>$orgname</td><td width=79 style='width:59pt'></td><td width=181 style='width:136pt'>Trayek</td><td colspan=4 width=346 style='width:260pt'>$fleetroutenum</td></tr><tr height=20 valign=bottom style='height:15.0pt'> <td height=20 style='height:15.0pt'></td><td>Kelas</td><td colspan=5>$seattype</td><td></td><td>Titik Awal Trayek</td><td colspan=4>$fromlocation</td></tr><tr height=20 valign=bottom style='height:15.0pt'> <td height=20 style='height:15.0pt'></td><td>No. Polisi</td><td colspan=5>$assetnum - $assetdesc</td><td></td><td>Titik Akhir Trayek</td><td colspan=4>$tolocation</td></tr><tr height=20 valign=bottom style='height:15.0pt'> <td height=20 style='height:15.0pt'></td><td>Driver</td><td colspan=5>$driver</td><td></td><td>No. Manifest</td><td colspan=4>$fleetusagenum</td></tr><tr height=20 valign=bottom style='height:15.0pt'> <td height=20 style='height:15.0pt'></td><td colspan=6></td><td></td><td>Catatan</td><td colspan=4>$description</td></tr>";	
		
		$set = new Set();
		$dataSet = $set->MboSetRemote("fleetusagecust fc")
		->setRelationship("fleetusage fu", "fc.fleetusagenum = fu.fleetusagenum")
		->setRelationship("customer cu", "fc.customercode = cu.customercode")
		->setWhere("fc.fleetusagenum='$fleetusagenum'")
		->MboRemote("fc.transnum")
		->MboRemote("fc.fullname")
		->MboRemote("fc.seatnum")
		->MboRemote("fc.seattype")
		->MboRemote("fc.phone")
		->MboRemote("fc.pickaddress")
		->MboRemote("fc.destaddress")
		->MboRemote("cu.ktp")
		->MboRemote("fu.usagedate")
		->getMboSet();
		
		$cnt = $dataSet->counts();
		$tdr = [];
		
		for($i=0; $i<$cnt; $i++){
			$data = $dataSet->getMbo();
			$nomor = $i+1;
			$transnum = $data->getString("transnum");
			$fullname = $data->getString("fullname");
			$seatnum = $data->getString("seatnum");
			$seattype = $data->getString("seattype");
			$phone = $data->getString("phone");
			$pickaddress = $data->getString("pickaddress");
			$destaddress = $data->getString("destaddress");
			$ktp = $data->getString("ktp");
			$usagedate = $data->getString("usagedate");
			
			$tr = "<tr>";
			$td_nomor = "<td style='border:.5pt solid black;'>$nomor</td>";
			$td_transnum = "<td style='border:.5pt solid black;'>$transnum</td>";
			$td_fullname = "<td style='border:.5pt solid black;'>$fullname</td>";
			$td_seatnum = "<td style='border:.5pt solid black;'>$seatnum</td>";
			$td_pak_kecil = "<td style='border:.5pt solid black;'></td>";
			$td_pak_sedang = "<td style='border:.5pt solid black;'></td>";
			$td_pak_besar = "<td style='border:.5pt solid black;'></td>";
			$td_seattype = "<td style='border:.5pt solid black;'>$seattype</td>";
			$td_ktp = "<td style='border:.5pt solid black;'>$ktp</td>";
			$td_phone = "<td style='border:.5pt solid black;'>$phone</td>";
			$td_pickaddress = "<td style='border:.5pt solid black;'>$pickaddress</td>";
			$td_usagedate = "<td style='border:.5pt solid black;'>$usagedate</td>";
			$td_destaddress = "<td style='border:.5pt solid black;'>$destaddress</td>";
			$endtr = "</tr>";
			
			$td = $tr.$td_nomor.$td_transnum.$td_fullname.$td_seatnum.$td_pak_kecil.$td_pak_sedang.$td_pak_besar.$td_seattype.$td_ktp.$td_phone.$td_pickaddress.$td_usagedate.$td_destaddress.$endtr;
			
			array_push($tdr, $td);
		}
		
		$data = implode(" ", $tdr);
		$ss = new stdClass();
		$ss->header = $header;
		$ss->data = $data;
		Set::throws($ss);
	}
	public static function PrintSelling_Pack(){
		$startdate = $_POST["startselling_pack"];
		$enddate = $_POST["endselling_pack"];
		$sitecode = $_POST["sitecode"];
		
		if($sitecode != "") $sitecode = " and sitecode = '$sitecode'";
		else $sitecode = "";
		
		$set = new Set();
		
		$fuSet = $set->MboSetRemote("fleetusagepack fp")
		->setRelationship("fleetusage fu", "fp.fleetusagenum = fu.fleetusagenum")
		->setWhere("fu.usagedate >= '$startdate' and fu.usagedate <= '$enddate' $sitecode")
		->MboRemote("fu.usagedate")
		->MboRemote("fp.packname")
		->MboRemote("fp.pickaddress")
		->MboRemote("fp.destaddress")
		->MboRemote("fp.senderphone")
		->MboRemote("fp.sendername")
		->MboRemote("fp.receivername")
		->MboRemote("fp.receiverphone")
		->MboRemote("fp.lineprice")
		->MboRemote("fp.paymenttype")
		->getMboSet();
		
		$fuCnt = $fuSet->counts();
		$tdr = [];
		$totallineprice = 0;
		for($i=0; $i<$fuCnt; $i++){
			$fu = $fuSet->getMbo();
			$nomor = $i+1;
			$tanggal = $fu->getString("usagedate");
			$packname = $fu->getString("packname");
			$pickaddress = $fu->getString("pickaddress");
			$destaddress =  $fu->getString("destaddress");
			$senderphone =  $fu->getString("senderphone");
			$sendername =  $fu->getString("sendername");
			$pengirim = $senderphone. " - ".$sendername;
			
			$receivername =  $fu->getString("receivername");
			$receiverphone =  $fu->getString("receiverphone");
			$penerima = $receivername. " - ".$receiverphone;
			$lineprice =  intval($fu->getString("lineprice"));
			$paymenttype =  $fu->getString("paymenttype");
			
			$totallineprice = $totallineprice+$lineprice;
			
			$tr = "<tr>";
			$td_nomor = "<td style='border:.5pt solid black;'>$nomor</td>";
			$td_tanggal = "<td style='border:.5pt solid black;'>$tanggal</td>";
			$td_namapaket = "<td style='border:.5pt solid black;'>$packname</td>";
			$td_alamatjemput = "<td style='border:.5pt solid black;'>$pickaddress</td>";
			$td_alamattujuan = "<td style='border:.5pt solid black;'>$destaddress</td>";
			$td_pengirim = "<td style='border:.5pt solid black;'>$pengirim</td>";
			$td_penerima = "<td style='border:.5pt solid black;'>$penerima</td>";
			$td_harga = "<td style='border:.5pt solid black;'>$lineprice</td>";
			$td_paymenttype = "<td style='border:.5pt solid black;'>$paymenttype</td>";
			$endtr = "</tr>";
			
			$td = $tr.$td_nomor.$td_tanggal.$td_namapaket.$td_alamatjemput.$td_alamattujuan.$td_pengirim.$td_penerima.$td_harga.$td_paymenttype.$endtr;
			array_push($tdr, $td);
		}
		
		$trf = "<tr>";
		$td_nomor = "<td style='border:.5pt solid black; border-right:none;'></td>";
		$td_tanggal = "<td style='border:.5pt solid black; border-right:none;'></td>";
		$td_namapaket = "<td style='border:.5pt solid black; border-right:none;'></td>";
		$td_alamatjemput = "<td style='border:.5pt solid black; border-right:none;'></td>";
		$td_alamattujuan = "<td style='border:.5pt solid black;border-right:none;'></td>";
		$td_pengirim = "<td style='border:.5pt solid black;border-right:none;'></td>";
		$td_penerima = "<td style='border:.5pt solid black;'><b>Total Paket</b></td>";
		$td_harga = "<td style='border:.5pt solid black;'>$totallineprice</td>";
		$td_paymenttype = "<td style='border:.5pt solid black;'></td>";
		$endtr = "</tr>";
		$endtrf =  "</tr>";
		
		$trf = $trf.$td_nomor.$td_tanggal.$td_namapaket.$td_alamatjemput.$td_alamattujuan.$td_pengirim.$td_penerima.$td_harga.$td_paymenttype.$endtrf;
		
		$tabledata = implode(" ", $tdr).$trf;
		
		$ob = new stdClass();
		$ob->msg = "ok";
		$ob->tabledata = $tabledata;
		Set::throws($ob);
	}
	public static function PrintSelling(){
		$startdate = $_POST["startselling"];
		$enddate = $_POST["endselling"];
		$np_sitecode = $_POST["sitecode"];
		$np_paymenttype = $_POST["paymenttype"];
		
		$enterby = Xbo::getLoginUser();
		$set = new Set();
		$mxsusersSet = $set->MboSetRemote("mxsusers")->setWhere("userid='$enterby'")->MboRemote("personid")->getMboSet();
		$mxsusers = $mxsusersSet->getMbo();
		$personid = $mxsusers->getString("personid");
		
		$set = new Set();
		$employeesSet = $set->MboSetRemote("employees")->setWhere("personid='$personid'")->MboRemote("sitecode")->getMboSet();
		$employees = $employeesSet->getMbo();
		$sitecode = $employees->getString("sitecode");
		
		if($np_sitecode != "") $sitecode = $np_sitecode;
		if($np_paymenttype != "" ) $paymenttype = "and paymenttype = '$np_paymenttype' ";
		else $paymenttype = "";
		
		
		$set = new Set();
		$fuSet = $set->MboSetRemote("fleetusage fu")
		->setRelationship("fleetusagecust fuc", "fu.fleetusagenum = fuc.fleetusagenum")
		->setRelationship("assets", "assets.assetnum = fu.assetnum")
		->setWhere("fu.usagedate >= '$startdate' and fu.usagedate <= '$enddate' and fu.sitecode='$sitecode' $paymenttype")
		->MboRemote("fu.usagedate as tanggal")
		->MboRemote("fuc.seatnum")
		->MboRemote("fuc.fullname")
		->MboRemote("fuc.phone")
		->MboRemote("fuc.seattype")
		->MboRemote("fuc.lineprice as seatprice")
		->MboRemote("(select lineprice from fleetusagepack where fleetusagepack.fleetusagenum = fuc.fleetusagenum and fleetusagepack.senderphone = fuc.phone ) packprice")
		->MboRemote("fu.assetnum")
		->MboRemote("assets.description as no_kendaraan")
		->MboRemote("fu.fleetroutenum as rute")
		->MboRemote("fuc.paymenttype")
		->MboRemote("fu.driver")
		->MboRemote("fu.totalprice")
		->MboRemote("fu.expense")
		->MboRemote("fu.margin")
		->getMboSet();
		
		$fuCnt = $fuSet->counts();
		$tdr = [];
		
		$seatprice_total = 0;
		$packprice_total = 0;
		$totalprice_total = 0;
		$expense_total = 0;
		$margin_total = 0; 
		
		for($i=0; $i<$fuCnt; $i++){
			$fu = $fuSet->getMbo();
			$nomor = $i+1;
			$tanggal = $fu->getString("tanggal");
			$nomorkursi = $fu->getString("seatnum");
			$fullname = $fu->getString("fullname");
			$phone = $fu->getString("phone");
			$pemesan = $fullname. " - ".$phone;
			$seattype =  $fu->getString("seattype");
			$seatprice =  intval($fu->getString("seatprice"));
			$packprice = intval($fu->getString("packprice"));
			$assetnum =  $fu->getString("assetnum");
			$no_kendaraan =  $fu->getString("no_kendaraan");
			$rute =  $fu->getString("rute");
			$paymenttype =  $fu->getString("paymenttype");
			$driver =  $fu->getString("driver");
			$totalprice =  intval($fu->getString("totalprice"));
			$expense =  intval($fu->getString("expense"));
			$margin =  intval($fu->getString("margin"));
			
			if($expense == null) $expense = 0;
			if($margin == null) $margin = 0;
			if($totalprice == null) $totalprice = 0;
			
			$seatprice_total = $seatprice_total+$seatprice;
			$packprice_total = $packprice_total+$packprice;
			$totalprice_total = $totalprice_total+$totalprice;
			$expense_total = $expense_total + $expense;
			$margin_total = $margin_total + $margin;
			
			$tr = "<tr>";
			$td_nomor = "<td style='border:.5pt solid black;'>$nomor</td>";
			$td_tanggal = "<td style='border:.5pt solid black;'>$tanggal</td>";
			$td_nomorkursi = "<td style='border:.5pt solid black;'>$nomorkursi</td>";
			$td_pemesan = "<td style='border:.5pt solid black;'>$pemesan</td>";
			$td_type = "<td style='border:.5pt solid black;'>$seattype</td>";
			$td_seatprice = "<td style='border:.5pt solid black;'>$seatprice</td>";
			$td_packprice = "<td style='border:.5pt solid black;'>$packprice</td>";
			$td_armada = "<td style='border:.5pt solid black;'>$assetnum - $no_kendaraan</td>";
			$td_rute = "<td style='border:.5pt solid black;'>$rute</td>";
			$td_paymenttype = "<td style='border:.5pt solid black;'>$paymenttype</td>";
			$td_totalprice = "<td style='border:.5pt solid black;'>$totalprice</td>";
			$td_expense = "<td style='border:.5pt solid black;'>$expense</td>";
			$td_margin = "<td style='border:.5pt solid black;'>$margin</td>";
			
			$td_user1 = "<td style='border:.5pt solid black;'></td>";
			$td_user2 = "<td style='border:.5pt solid black;'></td>";
			$td_user3 = "<td style='border:.5pt solid black;'></td>";
			$endtr = "</tr>";
			
			$td = $tr.$td_nomor.$td_tanggal.$td_nomorkursi.$td_pemesan.$td_type.$td_seatprice.$td_packprice.$td_armada.$td_rute.$td_paymenttype.$td_totalprice.$td_expense.$td_margin.$td_user1.$td_user2.$td_user3.$endtr;
			array_push($tdr, $td);
		}
		
		$trf = "<tr>";
			$td_nomor = "<td style='border:.5pt solid black;'></td>";
			$td_tanggal = "<td style='border:.5pt solid black;'></td>";
			$td_nomorkursi = "<td style='border:.5pt solid black;'></td>";
			$td_pemesan = "<td style='border:.5pt solid black;'></td>";
			$td_type = "<td style='border:.5pt solid black;'></td>";
			$td_seatprice = "<td style='border:.5pt solid black;'>$seatprice_total</td>";
			$td_packprice = "<td style='border:.5pt solid black;'>$packprice_total</td>";
			$td_armada = "<td style='border:.5pt solid black;'></td>";
			$td_rute = "<td style='border:.5pt solid black;'></td>";
			$td_paymenttype = "<td style='border:.5pt solid black;'></td>";
			$td_totalprice = "<td style='border:.5pt solid black;'>$totalprice_total</td>";
			$td_expense = "<td style='border:.5pt solid black;'>$expense_total</td>";
			$td_margin = "<td style='border:.5pt solid black;'>$margin_total</td>";
			
			$td_user1 = "<td style='border:.5pt solid black;'></td>";
			$td_user2 = "<td style='border:.5pt solid black;'></td>";
			$td_user3 = "<td style='border:.5pt solid black;'></td>";
		$endtrf = "</tr>";
			
		$trf = $trf.$td_nomor.$td_tanggal.$td_nomorkursi.$td_pemesan.$td_type.$td_seatprice.$td_packprice.$td_armada.$td_rute.$td_paymenttype.$td_totalprice.$td_expense.$td_margin.$td_user1.$td_user2.$td_user3.$endtrf;
		
		$tabledata = implode(" ", $tdr).$trf;
		
		$ob = new stdClass();
		$ob->msg = "ok";
		$ob->tabledata = $tabledata;
		Set::throws($ob);
		
	}
	public static function setfunum($fromlocation, $tolocation, $year, $month, $date, $hour){
		$fleetusagenum = $fromlocation."-".$tolocation.".".$date.".".$month.".".$year."-".$hour;
		return $fleetusagenum;
	}
	public static function GetFR(){
		$set = new Set();
		$fleetroutenum = $_POST["getfr"];
		$usagedate = $_POST["usagedate"];
		
		
		$fleetrouteSet = $set->MboSetRemote("fleetroute")->setWhere("fleetroutenum='$fleetroutenum'")
		->MboRemote("fromlocation")->MboRemote("tolocation")->getMboSet();
		
		$fleetroute = $fleetrouteSet->getMbo();
		$fromlocation = $fleetroute->getString("fromlocation");
		$tolocation =  $fleetroute->getString("tolocation");
		
		if($usagedate == null || $usagedate == ""){
			$fleetusagenum = $fromlocation."-".$tolocation;
		}else{
			$pts = explode(" ", $usagedate);
			$datepts = explode("-", $pts[0]);
			$hour = $pts[1];
			
			$year = $datepts[0];
			$month = $datepts[1];
			$date = $datepts[2];
			$fleetusagenum = ActionSet::setfunum($fromlocation, $tolocation, $year, $month, $date, $hour);
		}
		
		$set = new Set();
		$fleetusageSet = $set->MboSetRemote("fleetusage")->setWhere("fleetusagenum='$fleetusagenum'")->MboRemote("fleetusagenum")->getMboSet();
		$flcnt = $fleetusageSet->counts();
		
		$sd = new stdClass();
		if($flcnt == 0){
			$sd->fleetusagenum = $fleetusagenum;
			$sd->msg = "ok";
		}else{
			$sd->fleetusagenum = "";
			$sd->msg = "exist";
		}
		
		Set::throws($sd);
		
	}
	public static function AddPackagePerKG(){
		$packname = $_POST["packname"];
		$pickaddress = $_POST["pickaddress"];
		$sendername = $_POST["sendername"];
		$senderphone = $_POST["senderphone"];
		$pickprice = $_POST["pickprice"];
		$destaddress = $_POST["destaddress"];
		$receivername = $_POST["receivername"];
		$receiverphone = $_POST["receiverphone"];
		$destprice = $_POST["destprice"];
		$packweight = $_POST["packweight"];
		$unitprice1 = $_POST["unitprice1"];
		$unitprice = $_POST["unitprice"];
		$additionalprice = $_POST["additionalprice"];
		$lineprice = $_POST["lineprice"];
		$packdate = $_POST["packdate"];
		$description = $_POST["description"];
		$enterby = $_POST["enterby"];
		$enterdate = $_POST["enterdate"];
		$paymenttype = $_POST["paymenttype"];
		$frombank = $_POST["frombank"];
		$tobankaccount = $_POST["tobankaccount"];
		$transnum = $_POST["transnum"];
		$fleetusagenum = $_POST["addpackageperkg"];
		$pool = $_POST["pool"];
		$pooldest = $_POST["pooldest"];
		
		$packnum = Xbo::getLineNumber("fleetusagepack", "packnum", "fleetusagenum='$fleetusagenum'");
		$set = new Set();
		$set->MboSetRemote("fleetusagepack")
		->setValue("fleetusagenum", $fleetusagenum)
		->setValue("description", $description)
		->setValue("packnum", $packnum)
		->setValue("packname", $packname)
		->setValue("packtype", "PER-KG")
		->setValue("pickaddress", $pickaddress)
		->setValue("dropdate", $packdate)
		->setValue("pool", $pool)
		->setValue("destaddress", $destaddress)
		->setValue("pooldest", $pooldest)
		->setValue("sendername", $sendername)
		->setValue("senderphone", $senderphone)
		->setValue("receivername", $receivername)
		->setValue("receiverphone", $receiverphone)
		->setValue("paymenttype", $paymenttype)
		->setValue("transnum", $transnum)
		->setValue("frombank", $frombank)
		->setValue("tobankaccount", $tobankaccount)
		->setValue("unitprice1", $unitprice1)
		->setValue("unitprice", $unitprice)
		->setValue("additionalprice", $additionalprice)
		->setValue("pickprice", $pickprice)
		->setValue("destprice", $destprice)
		->setValue("lineprice", $lineprice)
		->setValue("enterby", $enterby)
		->setValue("enterdate", $enterdate)
		->add();
		
		$ss = new stdClass();
		$ss->msg = "added";
		Set::throws($ss);
		
	}
	public static function CheckUsage(){
		$set = new Set();
		$usagedate = $_POST["checkexistingusage"];
		$assetnum = $_POST["assetnum"];
		$fleetroutenum = $_POST["fleetroutenum"];
		$fleetusageSet = $set->MboSetRemote("fleetusage")->setWhere("usagedate='$usagedate' and assetnum='$assetnum' and fleetroutenum='$fleetroutenum'")
		->MboRemote("fleetusageid")->MboRemote("fleetusagenum")->getMboSet();
		$cnt = $fleetusageSet->counts();
		
		if($cnt > 0){
			$fleetusage = $fleetusageSet->getMbo();
			$fleetusageid = $fleetusage->getString("fleetusageid");
			$fleetusagenum =  $fleetusage->getString("fleetusagenum");
			$msg = "exist";
		}else{
			$fleetusageid = "";
			$fleetusagenum = "";
			$msg = "notexist";
		}
		
		
		$ss = new stdClass();
		$ss->fleetusageid = $fleetusageid;
		$ss->fleetusagenum = $fleetusagenum;
		$ss->msg = $msg;
		Set::throws($ss);
		
	}
	public static function SearchData(){
		$searchdata = $_POST["searchdata"];
		
		$ss = [];
		if($searchdata == "pessangers"){
			$tbname = "fleetusagecust";
			
			$transnum = $_POST["transnum"];
			if(empty($transnum) || $transnum == ""){
			}else{
				
				$transnum = "transnum = '$transnum'";
				array_push($ss, $transnum);
			}
			
			$fullname = $_POST["fullname"];
				
			if(empty($fullname) || $fullname == ""){
			}else{
				
				$fullname = "fullname like '%".$fullname."%'";
				array_push($ss, $fullname);
			}
	
			$phone = $_POST["phone"];
			if(empty($phone) || $phone == ""){
			}else{
				
				$phone = "phone like '%".$phone."%'";
				array_push($ss, $phone);
			}
			
			$fromlocation = $_POST["fromlocation"];
			
			if(empty($fromlocation) || $fromlocation == ""){
			}else{
				
				$fromlocation = "pickaddress like '%".$fromlocation."%'";
				array_push($ss, $fromlocation);
			}
			$tolocation = $_POST["tolocation"];
			if(empty($tolocation) || $tolocation == ""){
			}else{
				
				$tolocation = "destaddress like '%".$tolocation."%'";
				array_push($ss, $tolocation);
			}
			
			$usagedate = $_POST["usagedate"];
			if(empty($usagedate) || $usagedate == ""){
			}else{
				$usage = explode(" ", $usagedate);
				$fromdate = $usage[0]." 00:01:00";
				$todate = $usage[0]." 23:59:00";
				$usagedate = "usagedate >= '$fromdate' and usagedate <= '$todate'";
				array_push($ss, $usagedate);
			}
			
			$wc = implode(" and ", $ss);
	
		}else{
			$tbname = "fleetusagepack";
			if($searchdata == "package"){
				$transnum = $_POST["transnum"];
				if(empty($transnum) || $transnum == ""){
				}else{
					$transnum = "transnum = '$transnum'";
					array_push($ss, $transnum);
				}
			
				$packname = $_POST["packname"];
				if(empty($packname) || $packname == ""){
				}else{
					
					$packname = "packname like '%".$packname."%'";
					array_push($ss, $packname);
				}
				
				$dropdate = $_POST["dropdate"];
				if(empty($dropdate) || $dropdate == ""){
				
				}else{
					$drop = explode(" ", $dropdate);
					$fromdate = $drop[0]." 00:01:00";
					$todate = $drop[0]." 23:59:00";
					$dropdate = "dropdate >= '$fromdate' and dropdate <= '$todate'";
					array_push($ss, $dropdate);
				}
				
				$fromlocation = $_POST["fromlocation"];
				if(empty($fromlocation) || $fromlocation == ""){
				}else{
					
					$fromlocation = "fromlocation like '%".$fromlocation."%'";
					array_push($ss, $fromlocation);
				}
				
				$tolocation = $_POST["tolocation"];
				if(empty($tolocation) || $tolocation == ""){
				}else{
					
					$tolocation = "tolocation like '%".$tolocation."%'";
					array_push($ss, $tolocation);
				}
				
				$wc = implode(" and ", $ss);
			}
		}
			
		
		$set = new Set();
		$fleetusageSet = $set->MboSetRemote($tbname)->setWhere($wc)->MboRemote("fleetusagenum")->getMboSet();
		$fleetusage = $fleetusageSet->getMbo();
		$fleetusagenum = $fleetusage->getString("fleetusagenum");
		
		$set = new Set();
		$fleetusageSet = $set->MboSetRemote("fleetusage")->setWhere("fleetusagenum='$fleetusagenum'")->MboRemote("fleetusageid")->getMboSet();
		$fleetusage = $fleetusageSet->getMbo();
		$fleetusageid = $fleetusage->getString("fleetusageid");
		
		
	
		$ob = new stdClass();
		$ob->fleetusagenum = $fleetusagenum;
		$ob->fleetusageid = $fleetusageid;
		Set::throws($ob);
		
	}
	public static function PrintSeat(){
		$fleetusagenum = $_POST["printseat"];
		$seatnum = $_POST["seatnum"];
		$set = new Set();
		$fleetusageSet = $set->MboSetRemote("fleetusage")->setWhere("fleetusagenum='$fleetusagenum'")->MboRemote("orgcode")->MboRemote("fleetroutenum")->MboRemote("sitecode")->MboRemote("usagedate")->getMboSet();
		$fleetusage = $fleetusageSet->getMbo();
		$orgcode = $fleetusage->getString("orgcode");
		$sitecode = $fleetusage->getString("sitecode");
		$fleetroutenum = $fleetusage->getString("fleetroutenum");
		$usagedate =  $fleetusage->getString("usagedate");
		
		$set = new Set();
		$headerfooterSet = $set->MboSetRemote("headerfooter")->setWhere("appname='fleetusage' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("header")->MboRemote("footer")->getMboSet();
		$headerfooter = $headerfooterSet->getMbo();
		$setheader = $headerfooter->getString("header");
		$setfooter = $headerfooter->getString("footer");
		
		
		$set = new Set();
		$orgSet = $set->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("organizationid")->getMboSet();
		$org = $orgSet->getMbo();
		$ownerid = $org->getString("organizationid");
		$ownertable = "organization";
		
		$set = new Set();
		$fileinfoSet = $set->MboSetRemote("fileinfo")->setWhere("ownerid = '$ownerid' and ownertable = '$ownertable'")
		->MboRemote("filename")->getMboSet();
		$fileinfo = $fileinfoSet->getMbo();
		
		$filename = $fileinfo->getString("filename");
		$thumbfilename = "thumb_".$filename;
		
	
		$fsname = ContextPath::getFullHostName();
		$thumbPath = $fsname . "/attachments/thumbs/";
		$logo = $thumbPath . $thumbfilename;
		
		
		$set = new Set();
		$fleetrouteSet = $set->MboSetRemote("fleetroute")->setWhere("fleetroutenum='$fleetroutenum'")->MboRemote("fromlocation")->MboRemote("tolocation")->getMboSet();
		$fleetroute = $fleetrouteSet->getMbo();
		$fromlocation = $fleetroute->getString("fromlocation");
		$tolocation = $fleetroute->getString("tolocation");
		$fromloc_toloc = $fromlocation. " - ".$tolocation;
		
		$set = new Set();
		$fleetusageSet = $set->MboSetRemote("fleetusagecust")->setWhere("fleetusagenum='$fleetusagenum' and seatnum='$seatnum'")->MboRemote("phone")->MboRemote("fullname")->MboRemote("pickaddress")
		->MboRemote("destaddress")->MboRemote("paymenttype")->MboRemote("transnum")->MboRemote("seatnum")->MboRemote("seatposition")->MboRemote("seattype")
		->MboRemote("unitprice")->MboRemote("additionalprice")->MboRemote("lineprice")->MboRemote("enterby")->MboRemote("enterdate")
		->getMboSet();
		
		$fleetusage = $fleetusageSet->getMbo();
		$phone = $fleetusage->getString("phone");
		$fullname = $fleetusage->getString("fullname");
		$pickaddress = $fleetusage->getString("pickaddress");
		$destaddress = $fleetusage->getString("destaddress");
		$paymenttype = $fleetusage->getString("paymenttype");
		$transnum = $fleetusage->getString("transnum");
		$seatnum = $fleetusage->getString("seatnum");
		$seatposition = $fleetusage->getString("seatposition");
		$seattype = $fleetusage->getString("seattype");
		$unitprice = $fleetusage->getString("unitprice");
		$additionalprice = $fleetusage->getString("additionalprice");
		$lineprice = $fleetusage->getString("lineprice");
		$enterby = $fleetusage->getString("enterby");
		$enterdate = $fleetusage->getString("enterdate");
		
		$ss = new stdClass;
		$ss->logo = $logo;
		$ss->setheader = $setheader;
		$ss->setfooter = $setfooter;
		$ss->fromloc_toloc = $fromloc_toloc;
		$ss->phone = $phone;
		$ss->fullname = $fullname;
		$ss->pickaddress = $pickaddress;
		$ss->destaddress = $destaddress;
		$ss->paymenttype = $paymenttype;
		$ss->transnum = $transnum;
		$ss->seatnum = $seatnum;
		$ss->seatposition = $seatposition;
		$ss->seattype = $seattype;
		$ss->unitprice = number_format((float)$unitprice, 2, '.', '');
		$ss->additionalprice = number_format((float)$additionalprice, 2, '.', '');
		$ss->lineprice = number_format((float)$lineprice, 2, '.', '');
		$ss->enterby = $enterby;
		$ss->enterdate = $enterdate;
		$ss->usagedate = $usagedate;
		Set::throws($ss);
	}
	public static function PrintPacket(){
		$fleetusagenum = $_POST["printpacket"];
		$packnum = $_POST["packnum"];
		$set = new Set();
		$fleetusageSet = $set->MboSetRemote("fleetusage")->setWhere("fleetusagenum='$fleetusagenum'")->MboRemote("orgcode")->MboRemote("fleetroutenum")->MboRemote("sitecode")->getMboSet();
		$fleetusage = $fleetusageSet->getMbo();
		$orgcode = $fleetusage->getString("orgcode");
		$sitecode = $fleetusage->getString("sitecode");
		$fleetroutenum = $fleetusage->getString("fleetroutenum");
		
		$set = new Set();
		$headerfooterSet = $set->MboSetRemote("headerfooter")->setWhere("appname='fleetusage' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("header")->MboRemote("footer")->getMboSet();
		$headerfooter = $headerfooterSet->getMbo();
		$setheader = $headerfooter->getString("header");
		$setfooter = $headerfooter->getString("footer");
		
		
		$set = new Set();
		$orgSet = $set->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("organizationid")->getMboSet();
		$org = $orgSet->getMbo();
		$ownerid = $org->getString("organizationid");
		$ownertable = "organization";
		
		$set = new Set();
		$fileinfoSet = $set->MboSetRemote("fileinfo")->setWhere("ownerid = '$ownerid' and ownertable = '$ownertable'")
		->MboRemote("filename")->getMboSet();
		$fileinfo = $fileinfoSet->getMbo();
		
		$filename = $fileinfo->getString("filename");
		$thumbfilename = "thumb_".$filename;
		
	
		$fsname = ContextPath::getFullHostName();
		$thumbPath = $fsname . "/attachments/thumbs/";
		$logo = $thumbPath . $thumbfilename;
		
		
		$set = new Set();
		$fleetrouteSet = $set->MboSetRemote("fleetroute")->setWhere("fleetroutenum='$fleetroutenum'")->MboRemote("fromlocation")->MboRemote("tolocation")->getMboSet();
		$fleetroute = $fleetrouteSet->getMbo();
		$fromlocation = $fleetroute->getString("fromlocation");
		$tolocation = $fleetroute->getString("tolocation");
		$fromloc_toloc = $fromlocation. " - ".$tolocation;
		
		
		$set = new Set();
		$fleetusageSet = $set->MboSetRemote("fleetusagepack")->setWhere("fleetusagenum='$fleetusagenum' and packnum='$packnum'")->MboRemote("packname")->MboRemote("unitprice")->MboRemote("description")
		->MboRemote("sendername")->MboRemote("senderphone")->MboRemote("pickaddress")->MboRemote("receivername")->MboRemote("receiverphone")->MboRemote("destaddress")
		->MboRemote("additionalprice")->MboRemote("lineprice")->MboRemote("transnum")->MboRemote("enterdate")->MboRemote("enterby")->MboRemote("paymenttype")
		->MboRemote("pickprice")->MboRemote("destprice")->MboRemote("dropdate")
		->getMboSet();
		
		
		$fleetusage = $fleetusageSet->getMbo();
		$packname = $fleetusage->getString("packname");
		$unitprice = $fleetusage->getString("unitprice");
		$description = $fleetusage->getString("description");
		$sendername = $fleetusage->getString("sendername");
		$senderphone = $fleetusage->getString("senderphone");
		$pickaddress = $fleetusage->getString("pickaddress");
		$receivername = $fleetusage->getString("receivername");
		$receiverphone = $fleetusage->getString("receiverphone");
		$destaddress = $fleetusage->getString("destaddress");
		$additionalprice = $fleetusage->getString("additionalprice");
		$lineprice = $fleetusage->getString("lineprice");
		$transnum = $fleetusage->getString("transnum");
		$enterdate = $fleetusage->getString("enterdate");
		$enterby = $fleetusage->getString("enterby");
		$pickprice = $fleetusage->getString("pickprice");
		$destprice = $fleetusage->getString("destprice");
		$paymenttype = $fleetusage->getString("paymenttype");
		$dropdate = $fleetusage->getString("dropdate"); 
	
		$ss = new stdClass;
		$ss->setheader = $setheader;
		$ss->setfooter = $setfooter;
		$ss->orgcode = $orgcode;
		$ss->sitecode = $sitecode;
		$ss->fromloc_toloc = $fromloc_toloc;
		$ss->packname = $packname;
		$ss->unitprice = number_format((float)$unitprice, 2, '.', '');
		$ss->description = $description;
		$ss->sendername = $sendername;
		$ss->senderphone = $senderphone;
		$ss->pickaddress = $pickaddress;
		$ss->receivername = $receivername;
		$ss->receiverphone = $receiverphone;
		$ss->destaddress = $destaddress;
		$ss->additionalprice = number_format((float)$additionalprice, 2, '.', '');
		$ss->lineprice = number_format((float)$lineprice, 2, '.', '');
		$ss->transnum = $transnum;
		$ss->enterdate = $enterdate;
		$ss->enterby = $enterby;
		$ss->paymenttype = $paymenttype;
		$ss->pickprice = number_format((float)$pickprice, 2, '.', '');
		$ss->destprice = number_format((float)$destprice, 2, '.', '');
		$ss->dropdate = $dropdate;
		$ss->logo = $logo;
		Set::throws($ss);
	}
	public static function packtransnum(){
		$fleetusagenum = $_POST["packtransnum"];
		$datenum = Xbo::setEnterDate();
		$transnum = strtotime($datenum);
		$rand1 = rand(100,999);
		$rand2 = rand(100,999);
		
		//$transnum = $rand1.$transnum.$rand2;
		$transnum = Xbo::RandomString(3)."-".Xbo::RandomString(3)."-".Xbo::GetBatchNum(2);
		$ss = new stdClass();
		$ss->transnum = $transnum;
		
		Set::throws($ss);
	}
	public static function pooldestaddress(){
		$fleetusagenum = $_POST["pooldestaddress"];
		$fleetroutenum = $_POST["fleetroutenum"];
		
		$set = new Set();
		$fleetrouteSet = $set->MboSetRemote("fleetroute")->setWhere("fleetroutenum='$fleetroutenum'")->MboRemote("tolocation")->getMboSet();
		$fleetroute = $fleetrouteSet->getMbo();
		
		$ss = new stdClass();
		$ss->destaddress = "POOL ".$fleetroute->getString("tolocation");
		Set::throws($ss);
	}
	
	public static function poolpickaddress(){
		$fleetusagenum = $_POST["poolpickaddress"];
		$fleetroutenum = $_POST["fleetroutenum"];
		
		$set = new Set();
		$fleetrouteSet = $set->MboSetRemote("fleetroute")->setWhere("fleetroutenum='$fleetroutenum'")->MboRemote("fromlocation")->getMboSet();
		$fleetroute = $fleetrouteSet->getMbo();
		
		$ss = new stdClass();
		$ss->pickaddress = "POOL ".$fleetroute->getString("fromlocation");
		Set::throws($ss);
	}
	public static function sumfleetcost(){
		$fleetusagenum = $_POST["sumfleetcost"];
		$set = new Set();
		
		$psgrprice = $set->MboSetRemote("fleetusagecust")->setWhere("fleetusagenum='$fleetusagenum'")->MboRemote(" sum(lineprice) as psgrprice ")->getMboSet()->getMbo()->getString("psgrprice");
		if ($psgrprice == null) $psgrprice = 0;
		
		$packprice = $set->MboSetRemote("fleetusagepack")->setWhere("fleetusagenum='$fleetusagenum'")->MboRemote(" sum(lineprice) as packprice ")->getMboSet()->getMbo()->getString("packprice");
		if ($packprice == null) $packprice = 0;
		
		$totalprice = $psgrprice+$packprice;
		
		$expense = $set->MboSetRemote("fleetusagecost")->setWhere("fleetusagenum='$fleetusagenum'")->MboRemote(" sum(cost) as cost ")->getMboSet()->getMbo()->getString("cost");
		if ($expense == null) $expense = 0;
		
		$margin = $totalprice-$expense;
		
		Set::throws([
		"psgrprice" => $psgrprice, 
		"packprice" => $packprice, 
		"totalprice" => $totalprice,
		"expense" => $expense,
		"margin" => $margin
		]);
	}
	public static function PackTypePrice(){
		$ob = new stdClass();
		$fleetusagenum = $_POST["fleetusagenum"];
		$fleetroutenum = $_POST["fleetroutenum"];
		$packtype = $_POST["packtypeprice"];
		$assetnum = $_POST["assetnum"];
		
		$set = new Set();
		$fleetrouteprice2Set = $set->MboSetRemote("fleetrouteprice2")->setWhere("fleetroutenum = '$fleetroutenum' and packtype='$packtype' and assetnum='$assetnum' ")->MboRemote("price")->getMboSet();
		$fleetrouteprice2 = $fleetrouteprice2Set->getMbo();
		$price = $fleetrouteprice2->getString("price");
		
		$ob = new stdClass();
		$ob->price = $price;
		Set::throws($ob);
	}
	public static function InputPessangers(){
		$ob = new stdClass();
		$fleetusagenum = $_POST["inputpessangers"];
		$customercode = $_POST["customercode"]; //ktp
		$phone = $_POST["phone"];
		$fullname = $_POST["fullname"];
		$pickaddress = $_POST["pickaddress"];
		$destaddress = $_POST["destaddress"];
		$paymenttype = $_POST["paymenttype"];
		$transnum = $_POST["transnum"];
		$frombank = $_POST["frombank"];
		$tobankaccount = $_POST["tobankaccount"];
		$seatnum = $_POST["seatnum"];
		$seatposition = $_POST["seatposition"];
		$enterby = $_POST["enterby"];
		$enterdate = $_POST["enterdate"];
		$additionalprice = $_POST["additionalprice"];
		$fleetroutenum = $_POST["fleetroutenum"];
		$seattype = $_POST["seattype"];
		$assetnum = $_POST["assetnum"];
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$orgsite = Xbo::getOrgcode($enterby);
		$orgcode = $orgsite->orgcode;
		$sitecode = $orgsite->sitecode;
		$contactname = $fullname;
		if(empty($customercode)){
			$customercode = null;
		}else{
			$setc = new Set();
			$customerSet = $setc->MboSetRemote("customer")->setWhere("customercode='$customercode'")->MboRemote("customercode")->getMboSet();
			$cnt = $customerSet->counts();
			if($cnt == 0){
				$set = new Set();
				$set->MboSetRemote("customer")
				->setValue("customercode", $customercode)
				->setValue("name", $fullname)
				->setValue("distributor", 'N')
				->setValue("reseller", 'N')
				->setValue("balance", 0)
				->setValue("status", 'ACTIVE')
				->setValue("enterdate", $enterdate)
				->setValue("enterby", $enterby)
				->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)
				->setValue("ktp", $customercode)
				->add();
				
				$set = new Set();
				$customercontactSet = $set->MboSetRemote("customercontact")->setWhere("customercode='$customercode' and phone='$phone'")->MboRemote("customercode")->getMboSet();
				$cntcc = $customercontactSet->counts();
				
				if($cntcc == 0){
					$set = new Set();
					$set->MboSetRemote("customercontact")
					->setValue("customercode", $customercode)
					->setValue("contactname", $contactname)
					->setValue("phone", $phone)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();
				}
			}else{
				$set = new Set();
				$set->MboSetRemote("customer")
				->setWhere("customercode='$customercode'")
				->setValue("customercode", $customercode)
				->setValue("name", $fullname)
				->setValue("distributor", 'N')
				->setValue("reseller", 'N')
				->setValue("balance", 0)
				->setValue("status", 'ACTIVE')
				->setValue("enterdate", $enterdate)
				->setValue("enterby", $enterby)
				->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)
				->change();
				 
				$set = new Set();
				$customercontactSet = $set->MboSetRemote("customercontact")->setWhere("customercode='$customercode' and phone='$phone'")->MboRemote("customercode")->getMboSet();
				$cntcc = $customercontactSet->counts();
				
				if($cntcc == 0){
					$set = new Set();
					$set->MboSetRemote("customercontact")
					->setValue("customercode", $customercode)
					->setValue("contactname", $contactname)
					->setValue("phone", $phone)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();
				}
			}
		}
		
		if(empty($pickaddress)){
			$pickaddress = "POOL";
		}
		
		$set = new Set();
		$fleetRouteSet = $set->MboSetRemote("fleetroute")->setWhere("fleetroutenum='$fleetroutenum'")->MboRemote("fromlocation")->getMboSet();
		$fleetroute = $fleetRouteSet->getMbo();
		$fromlocation = $fleetroute->getString("fromlocation");
		
		$pickaddress = $pickaddress. " ".$fromlocation;
		
		$checkFl = new Set();
		$fleetusagecustSet = $checkFl->MboSetRemote("fleetusagecust")->setWhere("phone='$phone' and fleetusagenum='$fleetusagenum'")->MboRemote("fullname")->MboRemote("phone")->getMboSet();
		$cntfleet = $fleetusagecustSet->counts();
		
		$set = new Set();
		$fleetroutepriceSet = $set->MboSetRemote("fleetrouteprice")->setWhere("fleetroutenum='$fleetroutenum' and assetnum='$assetnum' and seattype='$seattype'")
		->MboRemote("price")->getMboSet();
		
		$fleetrouteprice = $fleetroutepriceSet->getMbo();
		$unitprice = $fleetrouteprice->getString("price");
		
		if($unitprice == null){
			$set2 = new Set();
			$fleetpriceSet = $set->MboSetRemote("fleetprice")->setWhere("fleetroutenum='$fleetroutenum' and seattype='$seattype'")->MboRemote("price")->getMboSet();
			$fleetprice = $fleetpriceSet->getMbo();
			$unitprice = $fleetprice->getString("price");
		}
		if($unitprice == null) $unitprice = 0;
		if($additionalprice == null) $additionalprice =0;
		
		$lineprice = $unitprice+$additionalprice;
		
		if($cntfleet == 0){
			$set = new Set();
			$set->MboSetRemote("fleetusagecust")
			->setValue("fleetusagenum", $fleetusagenum)
			->setValue("customercode", $customercode)
			->setValue("phone", $phone)
			->setValue("fullname", $fullname)
			->setValue("pickaddress", $pickaddress)
			->setValue("destaddress", $destaddress)
			->setValue("paymenttype", $paymenttype)
			->setValue("transnum", $transnum)
			->setValue("frombank", $frombank)
			->setValue("tobankaccount", $tobankaccount)
			->setValue("seatnum", $seatnum)
			->setValue("seattype", $seattype)
			->setValue("seatposition", $seatposition)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("unitprice", $unitprice)
			->setValue("additionalprice", $additionalprice)
			->setValue("lineprice", $lineprice)
			->add();
			$ob->msg = "ok";
		}else{
			$fleetusagecust = $fleetusagecustSet->getMbo();
			$ob->msg = "exist";
			$ob->fullname = $fleetusagecust->getString("fullname");
			$ob->phone = $fleetusagecust->getString("phone");
		}
		
		
		Set::throws($ob);
	}
	public static function getTransnum(){
		//$datenum = Xbo::setEnterDate();
		//$transnum = strtotime($datenum);
		$transnum = Xbo::RandomString(3)."-".Xbo::RandomString(3)."-".Xbo::GetBatchNum(2);		
		$o = new stdClass();
		$o->transnum = $transnum;
		Set::throws($o);
	}
	public static function SeatConfig(){
		$assetnum = $_POST["seatconfig"];
		$fleetusagenum = $_POST["fleetusagenum"];
		
		$obj = new stdClass();
		
		$set = new Set();
		$assetseatconfigSet = $set->MboSetRemote("assetseatconfig")->setWhere("assetnum='$assetnum'")->order("sort")
		->MboRemote("map")->MboRemote("sort")->getMboSet();
		
		$cnt = $assetseatconfigSet->counts();
		
		$mapr = [];
		$seat_temp = [];
		
		
		
		for($i=0; $i<$cnt; $i++){
			$assetseatconfig = $assetseatconfigSet->getMbo();
			$map = $assetseatconfig->getString("map");
			
			
			array_push($mapr, $map);
			$map_temp = str_split($map);
			$cntmaptemp = count($map_temp);
			for($j=0; $j<$cntmaptemp; $j++){
				$map_letter = $map_temp[$j];
				if($map_letter == "_"){
				}else{
					array_push($seat_temp, $map_letter);
				}
				
			}
			
			
		}
		$seat_temp = array_unique($seat_temp);
		$seat_temp = array_values($seat_temp);
		$seats = new stdClass();
		$legendobj = new stdClass();
		$legendobj->items = [];
		$seatmsg = "ok";
		for($i=0; $i<count($seat_temp); $i++){
			$legend = [];
			$prop =  $seat_temp[$i];	
			$set = new Set();
			$assetseatSet = $set->MboSetRemote("assetseat")->setWhere("seatcode='$prop'")->MboRemote("seattype")->MboRemote("description")->MboRemote("class")->getMboSet();
			$assetseatSetCount = $assetseatSet->counts();
			$assetseat = $assetseatSet->getMbo();
			$seats->{$prop} = new stdClass();
			if($assetseatSetCount == 0){
				$seatmsg = "notok";
				$seats->seattype = $prop;
				$seats->{$prop}->classes = null;
				$seats->{$prop}->category = null;
				array_push($legendobj->items, null);
				break;
			}else{
				$seatmsg = "ok";
				$seats->seattype = $prop;
				$seattype = $assetseat->getString("seattype");
				$description = $assetseat->getString("description");
				$class = $assetseat->getString("class");
				
				$seats->{$prop}->classes = $class;
				$seats->{$prop}->category = $seattype;
				
				array_push($legend, $prop);
				array_push($legend, "available");
				array_push($legend, $description);
				
				array_push($legendobj->items, $legend);
			}
		}
		$seatobj = new stdClass();
		$namingobj = new stdClass();
		
		$obj->seatmsg = $seatmsg;
		$obj->map = $mapr;
		$obj->seats = $seats;
		$obj->legend = $legendobj;
		
		$set = new Set();
		$fleetusagecustSet = $set->MboSetRemote("fleetusagecust")->setWhere("fleetusagenum='$fleetusagenum'")->MboRemote("seatposition")->MboRemote("seatnum")->getMboSet();
		$cnt = $fleetusagecustSet->counts();
		
		$seatr = [];
		for($i=0; $i<$cnt; $i++){
			$fleetusagecust = $fleetusagecustSet->getMbo();
			$seatposition = $fleetusagecust->getString("seatposition");
			array_push($seatr, $seatposition);
			
		}
		
		$obj->seatbooked = $seatr;
		Set::throws($obj);
	}
	
} 


class ValidateTables {
	
	public static function delchildrows(){
		
		$dl = explode("|", $_GET["delchildrow"]);
		$set = new Set();
		$appname = $dl[0];
		$vtable = $dl[2];
		
		if ($vtable == "fleetusagecust") {
			$colarr = explode("^", $dl[3]);
		
			$pathToFile = XboSet::getAttachFolder();
			$pathToThumbFile = XboSet::getAttachThumbsFolder();
			
			for ($i = 0; $i < count($colarr); $i++) {
				$tableid = explode("`", $colarr[$i])[1];
				$set = new Set();
				
				$fileinfoSet = $set->MboSetRemote("fileinfo")->setWhere("childtable='fleetusagecust' and childid='$tableid'")->MboRemote("filename")->getMboSet();
				$fileinfo = $fileinfoSet->getMbo();				
				$filename = $fileinfo->getString("filename");
				
				
				$set =  new Set();
				$fileinfoSet = $set->MboSetRemote("fileinfo")->setWhere("childtable='fleetusagecust' and childid='$tableid'")->remove();
				
				$thumbfilename = $pathToThumbFile . "thumb_".$filename;
				$pathfilename = $pathToFile . $filename;
				 
				if (file_exists($pathfilename)) {
					unlink($pathfilename);
					if (file_exists($thumbfilename)) {
						unlink($thumbfilename);
					}
					$msg = "deleteom";
				}else{
					$msg = "deleteom";
					unlink($pathfilename);
					unlink($thumbfilename);
					echo $thumbfilename;
				}
			}
			
			Set::throws(["msg" => "hhhh"]);
		}
	}
	
}

