<?php
require_once ("../../../eat.php");


Class InvoiceActionSet {
	public static function importWO(){
		$payload = Super::getParam("importWO");
		$data = $payload["data"];
		$invoicenum = $payload["invoicenum"];

		$sqlData = [];
		foreach($data as $row){
			$servrectransid = $row["servrectransid"];
			$servrec = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->where("servrectransid=?", $servrectransid)->getMboSet()->getMbo();
			$invpaymentlinenum = Xbo::getLineNumber("invpaymentline", "invpaymentlinenum", "invoicenum='$invoicenum'");
			$sqlData [] = [
				"invoicenum" => $invoicenum,
				"invpaymentlinenum" => $invpaymentlinenum,
				"linetype" => $servrec->getString("linetype"),
				"description" => $servrec->getString("description"),
				"quantity" => $servrec->getString("quantity"),
				"orderunit" => $servrec->getString("orderunit"),
				"unitcost" => $servrec->getString("unitcost"),
				"linecost" => $servrec->getString("linecost"),
				"wonum" => $servrec->getString("wonum"),
				"gldebitacct" => $servrec->getString("gldebitacct"),
				"glcreditacct" => $servrec->getString("glcreditacct"),
				"transtype" => $servrec->getString("transtype"),
				"serviceitemnum" => $servrec->getString("serviceitemnum"),
				"contractnum" => $servrec->getString("contractnum"),
				"servrectransid" => $servrec->getString("servrectransid")
			];
		}

		(new Set())->MboSetRemote("invpaymentline")->addStream($sqlData);
		Super::sendJsonResponse("success", "WO Service has been imported");

	}
	public static function validateStatusChangeForInvoice(){
		$payload = Super::getParam("validateStatusChangeForInvoice");
		$new_status = strtolower($payload["new_status"]); //Change Invoice Status To:
		$invoicenum = $payload["invoicenum"];

		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->where("invoicenum=?", $invoicenum)->getMboSet();
		$inv = $invoiceSet->getMbo();

		$current_status = $inv->getString("status");
	
		if($current_status == "cancel" || $current_status == "canceled"){
			Super::sendJsonResponse("error", "You cannot modify or change the status of a canceled invoice.");
		}
		
		if($current_status == "paid"){
			Super::sendJsonResponse("error", "The invoice has been paid");
		}


		if($new_status == "new"){
			//check Invoice Trans 
			$invoiceTransSet = (new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->where("invoicenum=?", $invoicenum)->getMboSet();
			if($invoiceTransSet->isEmpty()){
				Super::sendJsonResponse("success", "Validate Passed");
			}
		}

		$description = $inv->getString("description");
		if(empty($description)) {
			Super::sendJsonResponse("error", "Description cannot be empty.");
		}

		
		// Ambil nilai PO dan SO untuk Validasi berikutnya
		$ponum = $inv->getString("ponum");
		$sonum = $inv->getString("sonum");	
		$isPKP = $inv->getString("pkp"); //Pengusaha Kena Pajak

		if($new_status != "cancel" || $new_status != "canceled"){
			$paymenttype =  $inv->getString("paymenttype");
			if(empty($paymenttype)){
				Super::sendJsonResponse("error", "Payment type cannot be empty. Please select a payment type.");
			}

			$invoicedate =  $inv->getString("invoicedate");
			$scheddate =  $inv->getString("scheddate");

			if(empty($invoicedate) || empty($scheddate)){
				Super::sendJsonResponse("error", "Invoice date and scheduled date are required.");
			}

			if($paymenttype == "cashongiro"){
				$chequenum =  $inv->getString("chequenum");
				$chequedate = $inv->getString("chequedate");

				if(empty($chequenum) && empty($chequedate)){
					Super::sendJsonResponse("error", "Please enter the cheque number and cheque date.");
				}
			}
			if(!empty($sonum)){
				$banktoname =   $inv->getString("banktoname");
				$banktoaccount =   $inv->getString("banktoaccount");
				$banktoaccountname =  $inv->getString("banktoaccountname");

				if(empty($banktoaccount) || empty($banktoname) || empty($banktoaccountname) ){
					Super::sendJsonResponse("error", "Please provide the customer's bank information: bank name, account number, and account holder name.");
				}
			}
			
			//Check jika PKP
			if($isPKP == "Y"){
				//check invoice Charge.
				$invoiceChargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->where("invoicenum=? and taxreceiptnum IS NULL", $invoicenum)->getMboSet();
				if(!$invoiceChargeSet->isEmpty()){
					//check nomor PKP: taxreceiptnum
					while($row = $invoiceChargeSet->fetch()){
						$invoicechargelinenum = $row["invoicechargelinenum"];
						Super::sendJsonResponse("error", "This invoice requires PKP (Taxable Entrepreneur) information. Please enter the E-Faktur (Tax Invoice Number) on line: $invoicechargelinenum.");
					}
				}
			}
		}
		
		//Check jika status = approved, paid
		if($new_status == "approved" || $new_status == "paid"){

			
			//check eligibility of InvoiceLine
			
			$missingFields = [];
			$invoiceLineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->where("invoicenum=?", $invoicenum)->getMboSet();
			if($invoiceLineSet->isEmpty()){
				
				//Check Invoice Payment Line 
				$invPaymentLineSet = (new Set())->useTransactionManager()->MboSetRemote("invpaymentline")->where("invoicenum=?", $invoicenum)->getMboSet();
				if($invPaymentLineSet->isEmpty()){
					Super::sendJsonResponse("error", "No need to Approve the invoice. Invoice Lines OR Other Payment is not registered yet.");
				}else{
					//check isi invpaymentline 
					$missingFields = [];
					while($result = $invPaymentLineSet->fetch()){
						$invpaymentlinenum = $result["invpaymentlinenum"];
				
						$gldebitacct = $result["gldebitacct"];
						if(empty($gldebitacct)){
							$missingFields[] = "Please enter the GL Debit Account for line number: $invpaymentlinenum.";
						}
						$glcreditacct = $result["gldebitacct"];
						if(empty($glcreditacct)){
							$missingFields[] = "Please enter the GL Credit Account for line number: $invpaymentlinenum.";
						}
					}

					//
					if (!empty($missingFields)) {
						$message = "Please fill in the following mandatory fields on your Other Payment: <br>" . implode("<br>", $missingFields) . ".";
						Super::sendJsonResponse("error", $message, array_unique($missingFields));
					}
				}
			}
		
			while($il = $invoiceLineSet->fetch()){
				$invoicelinenum = $il["invoicelinenum"];
				if(empty($invoicelinenum)) {
					$missingFields[] = "One of your invoice lines has a null invoice line number.";
				}

				$il_description = $il["description"];
				if(empty($il_description)) {
					$missingFields[] = "Please enter the invoice line description for line number: $invoicelinenum.";
				}

				$invoicedQty = $il["quantity"];
				if(empty($invoicedQty) || floatval($invoicedQty) == 0) {
					$missingFields[] = "Invoiced quantity cannot be zero (0) or empty for line number: $invoicelinenum.";
				}

				if(!empty($sonum)){ // Berarti invoice ini untuk Sales Order
					$saleunit = $il["saleunit"];
					if(empty($saleunit)){
						$missingFields[] = "Sales unit cannot be empty for line number: $invoicelinenum.";
					}
					
					$unitcost = $il["unitcost"];
					if(empty($unitcost) || floatval($unitcost) == 0) {
						$missingFields[] = "Unit Price cannot be zero (0) or empty on Line Number: $invoicelinenum.";
					}

					$linecost = $il["linecost"];
					if(empty($linecost) || floatval($linecost) == 0) {
						$missingFields[] = "Line price cannot be zero (0) or empty for line number: $invoicelinenum.";
					}

					$unitpricediscount = $il["unitpricediscount"];
					if(empty($unitpricediscount) || floatval($unitpricediscount) == 0) {
						$missingFields[] = "Unit price discount cannot be zero (0) or empty for line number: $invoicelinenum.";
					}
					$linepricediscount = $il["linepricediscount"];
					if(empty($linepricediscount) || floatval($linepricediscount) == 0) {
						$missingFields[] = "Line price discount cannot be zero (0) or empty for line number: $invoicelinenum.";
					}
	
				}

				if(!empty($ponum)){ // Berarti invoice ini untuk Purchase Order.
					$orderunit = $il["orderunit"];
					if(empty($orderunit)){
						$missingFields[] = "Order Unit cannot be empty on Line Number: $invoicelinenum.";
					}

					/*
					$unitcost2 = $il["unitcost2"];
					if(empty($unitcost2) || floatval($unitcost2) == 0) {
						$missingFields[] = "Unit Cost cannot be zero (0) or null on Line Number: $invoicelinenum.";
					}

					$linecost2 = $il["linecost2"];
					if(empty($unitcost) || floatval($unitcost) == 0) {
						$missingFields[] = "Line Cost cannot be zero (0) or null on Line Number: $invoicelinenum.";
					}
					*/
				}
				//Gl Debit dan GL Credit mutlak tidak bisa kosong
				$gldebitacct = $il["gldebitacct"];
				if(empty($gldebitacct)){
					$missingFields[] = "GL Debit Account cannot be empty on Line Number: $invoicelinenum.";
				}
				$glcreditacct = $il["glcreditacct"];
				if(empty($gldebitacct)){
					$missingFields[] = "GL Credit Account cannot be empty on Line Number: $invoicelinenum.";
				}

			}// END Scanning Invoice Line
			if (!empty($missingFields)) {
				$message = "Please fill in the following mandatory fields on your Invoice Lines: <br>" . implode("<br>", $missingFields) . ".";
				Super::sendJsonResponse("error", $message, array_unique($missingFields));
			}

			//Scanning The Invoice Charges.
			//Check Sum Invoice Line Tax.
			$sumTaxCost = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->where("invoicenum=?", $invoicenum)->MboRemote("sum(taxcost) as sumTaxCost")->getMboSet()->getMbo()->getString("sumTaxCost");
			
			if($sumTaxCost > 0 ){//Berarti ada Pajak..
				
				//maka, check Invoice Charge
				$invoiceChargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->where("invoicenum=?", $invoicenum)->getMboSet();
				if($invoiceChargeSet->isEmpty()){
					Super::sendJsonResponse("error", "One or more invoice lines include tax costs. You must enter the corresponding tax charge under the invoice charges. Please re-import the invoice lines or add them manually.");
				}

				$missingFields =[];
				while($row = $invoiceChargeSet->fetch()){
					$invoicechargelinenum = $row["invoicechargelinenum"];
					if(empty($invoicechargelinenum)){
						Super::sendJsonResponse("error", "One of your invoice charge lines has a null invoice charge line number. Please review the lines under the invoice charges.");

					}
					$tax_description = $row["description"];
					if(empty($tax_description)){
						Super::sendJsonResponse("error", "Tax description cannot be null.");
					
					}

					// Check apakah ada tax origin yang salah. 
					// kalau SO , tax-origin = "tax-out"
					// Kalau PO, tax-origin = "tax-in"
					$origin = $row["origin"];
					if(!empty($ponum)){ //berarti invoice ini untuk PO
						if($origin == "tax-out"){ // tapi ada tax-out (pajak keluaran)
							Super::sendJsonResponse("error", "Invoices for purchase orders cannot have a TAX-OUT origin.");
						} 
					}elseif(!empty($sonum)) { // berarti invoice ini untuk SO
						if($origin == "tax-in") { //tapi ada tax-in (pajak masukan)
							Super::sendJsonResponse("error", "Invoices for sales orders cannot have a TAX-OUT origin.");
						}
					}

					//Check apakah PO ini requires budget.
					if(!empty($ponum)){
						//check apakah PO ini menggunakan budget?
						$poset = (new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $ponum)->getMboSet();
						$po = $poset->getMbo();

						$budgetnum = $po->getString("budgetnum");
						//check apakah PO ini tidak boleh di approve kalau budget exceeds.
						$isExceeds = (new Set())->useTransactionManager()->MboSetRemote("budget")->where("budgetnum=?", $budgetnum)->getMboValue("isexceeds"); 
						if($isExceeds == "Y"){ //berarti harus ada budget number untuk pembayaran tax. 
							//check tax budget number 
							$budgetnum = $row["budgetnum"];
							if(empty($budgetnum)){
								$missingFields[] = "This purchase order requires a budget for tax payment. Please enter the budget number for line number: $invoicechargelinenum.";
							}
							$budgetlinenum = $row["budgetlinenum"];
							if(empty($budgetnum)){
								$missingFields[] = "The budget line is empty. Please enter the budget line number for line number: $invoicechargelinenum.";
							}
							$linecost = $row["linecost"];
							if($linecost <= 0){
								$missingFields[] = "Line cost cannot be zero (0) or empty. Please enter the tax line cost for line number: $invoicechargelinenum.";
							}

							$gldebitacct = $row["gldebitacct"];
							if(empty($gldebitacct)){
								$missingFields[] = "GL Debit Account cannot be empty for line number: $invoicechargelinenum.";
							}

							$glcreditacct = $row["glcreditacct"];
							if(empty($glcreditacct)){
								$missingFields[] = "GL Credit Account cannot be empty for line number: $invoicechargelinenum.";
							}
						}

					}

				}
				//Tampilkan error pada invoice charge
				if (!empty($missingFields)) {
					$message = "Please fill in the following mandatory fields on your Invoice Charges: <br>" . implode("<br>", $missingFields) . ".";
					Super::sendJsonResponse("error", $message, array_unique($missingFields));
				}
			}

			//Kalau semua validasi sukses. 

			// jika new_status == paid. maka invoice ini harus sudah di approve dulu sebelumnya.
			if($new_status == "paid"){
				$invoiceStatusSet = (new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->where("invoicenum=? and status in('approved', 'appr')", $invoicenum)->getMboSet();
				if($invoiceStatusSet->isEmpty()){
					Super::sendJsonResponse("error", "The invoice has not been approved yet. Please review and approve it.");
				}

			}


			Super::sendJsonResponse("success", "Validation Passed!");
		}

		
	}
	public static function RecalculatePaymentTerm(){
		$invoicenum = $_POST["recalculatePaymentTerm"];
		$set = new Set();
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->where("invoicenum=?", $invoicenum)->MboRemote("ponum")->MboRemote("sonum")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		
		$ponum = $invoice->getString("ponum");
		$sonum = $invoice->getString("sonum");
		$existingTotalPayPercent = 0;
		$maxTotalPayPercent =100;
		if($sonum != null){
			//CheckPayment Terms
			$sas = new Set();
			$checkExistingInvoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")
			->setWhere("sonum='$sonum' and invoicenum not in('$invoicenum')")->MboRemote("invoicenum")->MboRemote("paypercent")->getMboSet();
			$cntExistingInvoice = $checkExistingInvoiceSet->counts();
			$remarks = "";
			$rem = [];
			if($cntExistingInvoice == 0){
			}else{
				$tempExistingPayPercent = 0 ;
				for($a=0; $a<$cntExistingInvoice; $a++){
					$checkExistingInvoice = $checkExistingInvoiceSet->getMbo();
					$existinginvoicenum = $checkExistingInvoice->getString("invoicenum");
					$tempExistingPayPercent = $checkExistingInvoice->getString("paypercent");
					$existingTotalPayPercent = $tempExistingPayPercent+$existingTotalPayPercent;
				}
			}
			$remainingPayPercent = $maxTotalPayPercent-$existingTotalPayPercent;
			
			Set::throws(["remainingPayPercent"=>$remainingPayPercent]);
			//end Check Payment Terms
		}else{
			Set::throws(["remainingPayPercent"=>"100"]);
		}
		
	}
	public static function GetGLCreditAcct(){
		$ponum = $_POST["addgetglcreditacct"];
		
		$poSet = (new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("ponum='$ponum'")->MboRemote("vendor")->getMboSet();
		$po = $poSet->getMbo();
		$vendor = $po->getString("vendor");
		
		$set = new Set();
		$POSet = (new Set())->useTransactionManager()->MboSetRemote("vendor")->setWhere("vendorcode='$vendor'")->MboRemote("rbniacct")->getMboSet();
		$PO = $POSet->getMbo();
		
		$rbniacct = $PO->getString("rbniacct");
		Set::throws(["msg"=>$rbniacct]);
	}
	
	
	public static function addDonation(){
		$newdonation = $_POST["addDonation"];
		$customercode = $_POST["customercode"];
		$newcommission = $_POST["newcommission"];
		$invoicenum = $_POST["invoicenum"];
		$newdonationtype = $_POST["newdonationtype"];
		$newdescription = $_POST["newdescription"];
		$invoicelinenum = Xbo::getLineNumber("invoiceline", "invoicelinenum", "invoicenum='$invoicenum'");
		
		
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		$enterdate = $changedate;
		$enterby = $changeby;
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("salesman")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$invc = $invoiceSet->getMbo();
		$salesman = $invc->getString("salesman");
		$orgcode = $invc->getString("orgcode");
		$sitecode = $invc->getString("sitecode");
		
		$vendorcode = "XXXXXXXXXXX";
		$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
		$glcreditacct = Xbo::getVendorAcct($vendorcode, $orgcode, $sitecode);
		
		$set = new Set();
		$set->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("isdonation", "Y")->change();
		
		$set = new Set();
		$set->MboSetRemote("invoiceline")
		->setValue("invoicenum", $invoicenum)
		->setValue("invoicelinenum", $invoicelinenum)
		->setValue("customercode", $customercode)
		->setValue("linetype", "services")
		->setValue("description", $newdescription)
		->setValue("exchangerate", 1)
		->setValue("quantity", 1)
		->setValue("saleunit", "UNIT")
		->setValue("unitcost", $newdonation)
		->setValue("linecost", $newdonation)
		->setValue("unitcost2", 0)
		->setValue("linecost2", 0)
		->setValue("unitprofit", 0)
		->setValue("lineprofit", 0)
		->setValue("unitpricediscount", $newdonation)
		->setValue("linepricediscount", $newdonation)
		->setValue("unitprofitdiscount", 0)
		->setValue("lineprofitdiscount", 0)
		->setValue("unitcommission", $newcommission)
		->setValue("linecommission", $newcommission)
		->setValue("conversionfactor", 1)
		->setValue("taxcost", 0)
		->setValue("gldebitacct", $gldebitacct)
		->setValue("glcreditacct", $glcreditacct)
		->setValue("enterby", $enterby)
		->setValue("salesman", $salesman)
		->setValue("enterdate", $enterdate)
		->setValue("changeby", $changeby)
		->setValue("changedate", $changedate)
		->setValue("isdonation", "Y")
		->setValue("donationtype", $newdonationtype)
		->add();
		
		$ms = new stdClass();
		$ms->msg = "added";
		Set::throws($ms);
	}
	
	public static function changesalesman(){
		$invoicenum = $_POST["invoicenum"];
		$newsalesman = $_POST["changesalesman"];
		$newrequestedby = $_POST["requestedby"];
		$newremarks = $_POST["remarks"];
		$fromsalesman = $_POST["fromsalesman"];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		$set = new Set();
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("customercode")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$customercode = $invoice->getString("customercode");
		
		(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("salesman", $newsalesman)->change();
		(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->setValue("salesman", $newsalesman)->change();
		(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setWhere("invoicenum='$invoicenum'")->setValue("salesman", $newsalesman)->change();
		(new Set())->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$customercode'")->setValue("salesrep", $newsalesman)->change();
		$s = new stdClass();
		$s->msg = "salesman_updated";
		
		(new Set())->useTransactionManager()->MboSetRemote("salesrephistory")->setValue("fromsalesman", $fromsalesman)->setValue("tosalesman", $newsalesman)->setValue("changeby", $changeby)
		->setValue("changedate", $changedate)->setValue("customercode", $customercode)->setValue("remarks", $newremarks)->setValue("requestedby", $newrequestedby)->add();
		
		
		Set::throws($s);
	}
	public static function getCustomerBankAccount(){
		$bankaccount = $_POST["getCustomerBankAccount"];
		$invoicenum = $_POST["invoicenum"];
		$customercode = $_POST["customercode"];
		
		$custbankSet = (new Set())->useTransactionManager()->MboSetRemote("customerbank")
		->where("customercode=? AND bankaccount=?", $customercode, $bankaccount)->MboRemote("bankaccount")
		->MboRemote("bankname")->MboRemote("banktoaccountname")->getMboSet();
		$cust = $custbankSet->getMbo();
		$bankname = $cust->getString("bankname");
		$bankaccount = $cust->getString("bankaccount");
		$banktoaccountname = $cust->getString("banktoaccountname");
		
		$sa = new stdClass();
		$sa->banktoname = $bankname;
		$sa->banktoaccount = $bankaccount;
		$sa->banktoaccountname = $banktoaccountname;
		Set::throws($sa);
	}
	public static function getCustomerBank(){
		$invoicenum = $_POST["getCustomerBank"];
		$customercode = $_POST["customercode"];
		
		$customerSet = (new Set())->useTransactionManager()->MboSetRemote("customerbank")->setWhere("customercode='$customercode' and isdefault='Y'")->MboRemote("bankaccount")
		->MboRemote("bankname")->MboRemote("banktoaccountname")->MboRemote("isdefault")->getMboSet();
		
		$cust = $customerSet->getMbo();
		$bankname = $cust->getString("bankname");
		$bankaccount = $cust->getString("bankaccount");
		$banktoaccountname = $cust->getString("banktoaccountname");
		$isdefault = $cust->getString("isdefault");
		
		$sa = new stdClass();
		$sa->banktoname = $bankname;
		$sa->banktoaccount = $bankaccount;
		$sa->banktoaccountname = $banktoaccountname;
		$sa->isdefault = $isdefault;
		Set::throws($sa);
		
	}
	public static function AddOtherCharge(){
		$invoicenum = $_POST["invoicenum"];
		$newtax = $_POST["newtax"];
		$newtaxvalue = $_POST["newtaxvalue"];
		$addothercharge = $_POST["addothercharge"];
		
		$invclineSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge2")->setWhere("invoicenum='$invoicenum'")->MboRemote("max(invoicechargelinenum) as invoicechargelinenum")->getMboSet();
		$invcline = $invclineSet->getMbo();
		$invoicechargelinenum = $invcline->getString("invoicechargelinenum");
		if($invoicechargelinenum == null) {
			$invoicechargelinenum = 10;
			$newinvoicechargelinenum = $invoicechargelinenum;
		}else{
			$newinvoicechargelinenum = $invoicechargelinenum+10;
		}
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("ponum")->MboRemote("sonum")->MboRemote("paymenttype")
		->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$ponum = $invoice->getString("ponum");
		$sonum = $invoice->getString("sonum");
		$paymenttype = $invoice->getString("paymenttype");
		$orgcode = $invoice->getString("orgcode");
		$sitecode = $invoice->getString("sitecode");
		
		$customercode = null;
		$vendorcode = null;
		$glcreditacct = null;
		$gldebitacct = null;
		
		$invoiceline = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")
		->MboRemote("gldebitacct")->MboRemote("customercode")->MboRemote("linetype")->MboRemote("warehouse")->getMboSet("distinct")->getMbo();
		
		$gldebitacct_invoiceline = $invoiceline->getString("gldebitacct");
		$customercode = $invoiceline->getString("customercode");
		$linetype = $invoiceline->getString("linetype");
		$warehouse = $invoiceline->getString("warehouse");
		
		if(empty($ponum)){
			if(empty($sonum)){
				$customercode = $customercode;
			}else{
				$customercode = (new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->MboRemote("customercode")->getMboSet()->getMbo()->getString("customercode");
			}
			
			if($paymenttype == "kredit"){
				$glcreditacct = Xbo::getVendorAcct($vendorcode, $orgcode, $sitecode);
			}else{
				if($paymenttype == "cashonbank" || $paymenttype == "cashongiro"){
					$glcreditacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
					if($linetype == "item") $gldebitacct = Xbo::getwhgl($warehouse, "noprint");
					if($linetype == "services") {
						if(empty($gldebitacct_invoiceline)) $gldebitacct = Xbo::getVendorAPAcct($vendorcode, $orgcode, $sitecode);
						else $gldebitacct = $gldebitacct_invoiceline;
					}
				}else{
					if($paymenttype == "cashonhand"){
						$glcreditacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
						if($linetype == "item") $gldebitacct = Xbo::getwhgl($warehouse, "noprint");
						if($linetype == "services") {
							if(empty($gldebitacct_invoiceline)) $gldebitacct = Xbo::getVendorAPAcct($vendorcode, $orgcode, $sitecode);
							else $gldebitacct = $gldebitacct_invoiceline;
						}
					}
				}
			}
		}else{
		
			$vendorcode = (new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("ponum='$ponum'")->MboRemote("vendor")->getMboSet()->getMbo()->getString("vendor");
			
				if($paymenttype == "kredit"){
				$gldebitacct = Xbo::getVendorAcct($vendorcode, $orgcode, $sitecode);
			}else{
				if($paymenttype == "cashonbank" || $paymenttype == "cashongiro"){
					$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
					if($linetype == "item") $glcreditacct = Xbo::getwhgl($warehouse, "noprint");
					if($linetype == "services") {
						if(empty($gldebitacct_invoiceline)) $glcreditacct = Xbo::getVendorAPAcct($vendorcode, $orgcode, $sitecode);
						else $glcreditacct = $gldebitacct_invoiceline;
					}
				}else{
					if($paymenttype == "cashonhand"){
						$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
						if($linetype == "item") $glcreditacct = Xbo::getwhgl($warehouse, "noprint");
						if($linetype == "services") {
							if(empty($gldebitacct_invoiceline)) $glcreditacct = Xbo::getVendorAPAcct($vendorcode, $orgcode, $sitecode);
							else $glcreditacct = $gldebitacct_invoiceline;
						}
					}
				}
			}
		
		}
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		
		(new Set())->useTransactionManager()->MboSetRemote("invoicecharge2")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $newinvoicechargelinenum)
		->setValue("consignment", "N")->setValue("ponum", $ponum)->setValue("sonum", $sonum)->setValue("customercode", $customercode)->setValue("vendorcode", $vendorcode)
		->setValue("description", $newtax)->setValue("quantity", 1)->setValue("unitcost", $addothercharge)->setValue("linecost", $addothercharge)
		->setValue("taxcode", $newtax)->setValue("taxcost", 0)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("origin", "tax-etc")
		->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
		
		$ss = new stdClass();
		$ss->msg = "ok";
		Set::throws($ss);
	}
	public static function othercharges(){
		$othercharges = $_POST["othercharges"];
		$invoicenum = $_POST["invoicenum"];
		$chargename = $_POST["chargename"];
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("ponum")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$ponum = $invoice->getString("ponum");
		$pot = 0;
		if(empty($ponum)){
			
			$slpdSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote("sum(linepricediscount) as sumlinepricediscount")->getMboSet();
			$slpd = $slpdSet->getMbo();
			$sumlinepricediscount = $slpd->getString("sumlinepricediscount");
			$pot = ($othercharges/100)*$sumlinepricediscount;
		}else{
		
			$slcSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote("sum(linecost) as sumlinecost")->getMboSet();
			$slc = $slcSet->getMbo();
			$sumlinecost = $slc->getString("sumlinecost");
			$pot = ($othercharges/100)*$sumlinecost;
		}
		
		$s = new stdClass();
		$s->othercharges = $pot;
		Set::throws($s);
		
	}
	public static function ApplyCommissionPrice(){
		$m = new stdClass();
		$commissionprice = $_POST["applycommissionprice"];
		$commissionpercent = $_POST["applycommissionpercent"];
		$invoicenum = $_POST["invoicenum"];
		
		$invoiceLineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")
		->MboRemote("initunitcommission")->MboRemote("initlinecommission")
		->MboRemote("unitcommission")->MboRemote("linecommission")->MboRemote("invoicelinenum")
		->MboRemote("linepricediscount")->MboRemote("unitpricediscount")->MboRemote("quantity")
		->getMboSet();
		
		$cnt = $invoiceLineSet->counts();
		for($i=0; $i<$cnt; $i++){
			$il = $invoiceLineSet->getMbo();
			$initunitcommission  = $il->getString("initunitcommission");
			if(empty($initunitcommission)) $initunitcommission = 0;
			$initlinecommission  = $il->getString("initlinecommission");
			if(empty($initlinecommission)) $initlinecommission = 0;
			$unitcommission  = $il->getString("unitcommission");
			if(empty($unitcommission)) $unitcommission = 0;
			$linecommission  = $il->getString("linecommission");
			if(empty($linecommission)) $linecommission = 0;
			
			$quantity = $il->getString("quantity");
			if(empty($quantity)) $quantity = 0;
			
			$unitpricediscount = $il->getString("unitpricediscount");
			if(empty($unitpricediscount)) $unitpricediscount = 0;
			
			$linepricediscount = $il->getString("linepricediscount");
			if(empty($linepricediscount)) $linepricediscount = 0;
			
			$invoicelinenum = $il->getString("invoicelinenum");
			
			if($initunitcommission == 0 || empty($initunitcommission)) {
				if($unitcommission != 0){
					(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and invoicelinenum='$invoicelinenum'")
					->setValue("initunitcommission", $unitcommission)->change();
				}else{
					
					$unitcommission = 0;
				}
			}else{
				$unitcommission = $initunitcommission;
			}
			if($initlinecommission == 0 ){
				if($linecommission != 0){
					(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and invoicelinenum='$invoicelinenum'")
					->setValue("initlinecommission", $linecommission)->change();
				}
			}else{
				$linecommission = $initlinecommission;
			}
		
			$unitpricediscountAftrCommission = $unitpricediscount-$initunitcommission;
			$newunitcommission = ($commissionpercent/100)*$unitpricediscountAftrCommission;
			
			
			if($unitcommission == 0) $unitcommission = 0;
			
			$newunitcommission = $newunitcommission+$unitcommission;				
			$newlinecommission = $newunitcommission*$quantity;
			
			(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and invoicelinenum='$invoicelinenum'")
			->setValue("unitcommission", $newunitcommission)->setValue("linecommission", $newlinecommission)->change();
			
		}
		$m->msg = "ok";
		Set::throws($m);
	}
	public static function ChangeCommission(){
		$msg = null;
		
		$invoicenum = $_POST["invoicenum"];
	
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("subtotalcost")
		->getMboSet();
		
		$invoice = $invoiceSet->getMbo();
		$subtotalcost = $invoice->getString("subtotalcost");
		
		$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")
		->MboRemote("sonum")->MboRemote("transnum")->MboRemote("billingnum")->MboRemote("linetype")
		->MboRemote("invoicelinenum")->MboRemote("linepricediscount")->MboRemote("linecommission")
		->MboRemote("initlinecommission")
		->MboRemote("billinglinenum")
		->getMboSet();
		
		$invCnt = $invoicelineSet->counts();
		
		$commissionprice = 0;
		$newcommission = 0;
		$newinvoicecommission = 0; 
		$commissionprice_temp = 0;
		$newcommission_temp = 0;
		$newinvoicecommission_temp = 0;
		$linepricecommission_temp = 0;
		
		for($i=0; $i<$invCnt; $i++){
			$invoice = $invoicelineSet->getMbo();
			$sonum = $invoice->getString("sonum");
			$transnum = $invoice->getString("transnum");
			$billingnum = $invoice->getString("billingnum");
			$billinglinenum = $invoice->getString("billinglinenum");
			$linetype = $invoice->getString("linetype");
			$invoicelinenum = $invoice->getString("invoicelinenum");			
			$linepricediscount = $invoice->getString("linepricediscount");
			$linecommission = $invoice->getString("linecommission");
			$initlinecommission = $invoice->getString("initlinecommission");
			
			if(empty($initlinecommission))  {
				$linecommission = $linecommission;
			}
			else{
				$linecommission = $initlinecommission;
			}
				
			$linepricecommission = $linepricediscount-$linecommission;
			$linepricecommission_temp = $linepricecommission_temp+$linepricecommission;
			
			
			if(isset($_POST["changecommissionpercent"])){
				
				$commission = $_POST["changecommissionpercent"];
				if($commission >= 100){
					$msg = "above100";
				}else{
					
					if($commission <=0 ){
						$msg = "below0";
					}else{
						if(!empty($sonum)){
							$msg = "SONUM";
							if($linetype == "item"){
								
								$matsaletransSet = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("sonum='$sonum' and solinenum='$invoicelinenum'")
								->MboRemote("sum(linecommission) totallinecommission")->getMboSet();
								$recnt = $matsaletransSet->counts();
								if($recnt>0){
									$matsaletrans = $matsaletransSet->getMbo();
									$totallinecommission = $matsaletrans->getString("totallinecommission");
									if(empty($totallinecommission)) $totallinecommission = 0;
									$commissionprice = $linepricecommission*($commission/100);
									$newcommission = $commissionprice+$totallinecommission;
								}else{
									$msg = "receivingnotexist";
									break;
								}
							}else{
							
								$servsaletransSet = (new Set())->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("sonum='$sonum' and solinenum='$invoicelinenum'")
								->MboRemote("sum(linecommission) totallinecommission")->getMboSet();
								$recnt = $servsaletransSet->counts();
								if($recnt>0){
									$servsaletrans = $servsaletransSet->getMbo();
									$totallinecommission = $servsaletrans->getString("totallinecommission");
									if(empty($totallinecommission)) $totallinecommission = 0;
									$commissionprice = $linepricecommission*($commission/100);
									$newcommission = $commissionprice+$totallinecommission;
								}else{
									$msg = "receivingnotexist";
									break;
								}
								
							}
						}else{
							if(!empty($transnum)){
								$msg = "TRANSNUM";
								if($linetype == "item"){
								
									$matsaletransSet =(new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("transnum='$transnum'")
									->MboRemote("sum(linecommission) totallinecommission")->getMboSet();
									$recnt = $matsaletransSet->counts();
									if($recnt>0){
										$matsaletrans = $matsaletransSet->getMbo();
										$totallinecommission = $matsaletrans->getString("totallinecommission");
										if(empty($totallinecommission)) $totallinecommission = 0;
										$commissionprice = $linepricecommission*($commission/100);
										$newcommission = $commissionprice+$totallinecommission;
									
								}else{
									
								}
							}else{
								if(!empty($billingnum)){
									$msg = "billingnum";
								
									$billinglineSet = (new Set())->useTransactionManager()->MboSetRemote("billingline")->setWhere("billingnum='$billingnum' and billinglinenum='$billinglinenum'")
									->MboRemote("sum(linecommission) totallinecommission")->getMboSet();
									$recnt = $billinglineSet->counts();
										if($recnt>0){
											$billingline = $billinglineSet->getMbo();
											$totallinecommission = $billingline->getString("totallinecommission");
											if(empty($totallinecommission)) $totallinecommission = 0;										
											$commissionprice = $linepricecommission*($commission/100);
											$newcommission = $commissionprice+$totallinecommission;
										}else{
											$msg = "receivingnotexist";
											break;
										}
									}
								}
							}
						}
					}
				}
			}else{
		
				 if(isset($_POST["changecommissionprice"])){
					$commission = $_POST["changecommissionprice"];
				
					$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("subtotalcost")->getMboSet();
					$subtotalcost = $invoiceSet->getMbo();
					$subtotalcost = $invoiceSet->getString("subtotalcost");
					
					if($commission >= $subtotalcost){
						$msg = "abovesubtotalcost";
					}else{
						if($commission <=0 ){
							$msg = "below0";
						}else{
							$msg = "ok";
						}
					}
			
				 }
			}
			
			$commissionprice_temp = $commissionprice_temp+$commissionprice;
			$newcommission_temp = $newcommission_temp+$newcommission;
			$linepricecommission_temp = $linepricecommission_temp-$newcommission;
		
		
			
		}
		
		$ss= new stdClass();
		$ss->msg = $msg;
		$ss->commissionprice = $commissionprice_temp;
		$ss->newcommission = $newcommission_temp;
		$ss->newinvoicecommission = $linepricecommission_temp;
		Set::throws($ss);
	}
	public static function ViewCommission(){
		$invoicenum = $_POST["viewcommission"];
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote("sum(linecommission) totalcommission")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$oldcommission = $invoice->getString("totalcommission");
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("subtotalcost")->getMboSet();
		$subtotalcost = $invoiceSet->getMbo();
		
		$subtotalcost = $invoiceSet->getString("subtotalcost");
		
		$oldinvoicecommission = $subtotalcost-$oldcommission;
		
		$ss = new stdClass();
		$ss->oldcommission = $oldcommission;
		$ss->oldinvoicecommission = $oldinvoicecommission;
		Set::throws($ss);
	}
	public static function IsLate($invoicenum, $scheddate){
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("voidcommission")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$voidcommission = $invoice->getString("voidcommission");
		
		if($voidcommission=="Y"){
			$todaydate = Xbo::setEnterDate();
			$oscheddate = strtotime($scheddate);
			$otodaydate = strtotime($todaydate);
			
			echo "$oscheddate - $otodaydate";
			$odate = $oscheddate - $otodaydate;
			if ($odate < 0) $islate = "Y";
			else $islate = "N";
		}else{
			$islate = "N";
		}
		
		
		return $islate;
	}
	public static function SumSOCost($sonum, $discountpercentage)
	{
			
			$totaltax = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum'")->MboRemote("sum(taxprice) as totaltax")->getMboSet()->getMbo()->getString("totaltax");
			if($totaltax == null ) $totaltax=0.00;
			$pretaxtotal = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum'")->MboRemote("sum(lineprice) as pretaxtotal")->getMboSet()->getMbo()->getString("pretaxtotal");
			if($pretaxtotal == null )$pretaxtotal=0.00;
			
			$pretaxtotaldiscount = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum'")->MboRemote("sum(linepricediscount) as pretaxtotaldiscount")->getMboSet()->getMbo()->getString("pretaxtotaldiscount");
			if($pretaxtotaldiscount == null )$pretaxtotaldiscount=0.00;
			
			$paidprice = (new Set())->useTransactionManager()->MboSetRemote("sopayment")->setWhere("sonum='$sonum'")->MboRemote("sum(paidprice) as paidprice")->getMboSet()->getMbo()->getString("paidprice");
			if ($paidprice == null) $paidprice = 0.00;
			
			$invoicediscount = (new Set())->useTransactionManager()->MboSetRemote("sopayment")->setWhere("sonum='$sonum'")->MboRemote("sum(invoicediscount) as invoicediscount")->getMboSet()->getMbo()->getString("invoicediscount");
			if ($invoicediscount == null) $invoicediscount = 0.00;
			
			
			$totalprice = $totaltax + $pretaxtotal;
			
			
			$unpaidprice = $paidprice - $totalprice;
			$discountedtotalprice = $totalprice - ($totalprice * ($discountpercentage / 100)); 
			$discountedtotalprice = $discountedtotalprice;
			$unpaidprice2 = $paidprice - $discountedtotalprice;
			$unpaidprice2 = $unpaidprice2;
			
			
			$discountedtotaltax = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum'")->MboRemote("sum(taxpricediscount) as discountedtotaltax")->getMboSet()->getMbo()->getString("discountedtotaltax");
			if($discountedtotaltax == null )$discountedtotaltax=0.00;
			
			if($invoicediscount>0){
				$taxpercentage = round($invoicediscount)/round($discountedtotaltax)/10;
				$disctax = $discountedtotaltax*$taxpercentage;
				$discountedtotaltax = $discountedtotaltax-$disctax;
			}
			
			$discountedtotalprofit = $pretaxtotaldiscount-$invoicediscount;
			$discountedtotalprice = $discountedtotaltax+$discountedtotalprofit;
			
			$unpaidprice2 = $discountedtotalprice-$paidprice;
			if($unpaidprice2>0) $unpaidprice2 = $unpaidprice2*-1;
			(new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")
			->setValue("discountedtotalprice", $discountedtotalprice)
			->setValue("discountedtotalprofit", $discountedtotalprofit)
			->setValue("discountedtotaltax", $discountedtotaltax)
			->setValue("totalprice", $totalprice)
			->setValue("totaltax", $totaltax)
			->setValue("pretaxtotal", $pretaxtotal)
			->setValue("paidprice", $paidprice)
			->setValue("unpaidprice", round($unpaidprice2))
			->setValue("invoicediscount", $invoicediscount)
			->change();
		}
		
	public static function SumPOCost($ponum) {
		
		$whereClause = "ponum = '$ponum'";

		// Get exchange rate
		$exchangerate = (new Set())->useTransactionManager()->MboSetRemote("po")
						  ->setWhere($whereClause)
						  ->MboRemote("exchangerate")
						  ->getMboSet()
						  ->getMbo()
						  ->getString("exchangerate");
		$exchangerate = floatval($exchangerate);

		// Get total tax
		$totaltax = (new Set())->useTransactionManager()->MboSetRemote("poline")
					  ->setWhere($whereClause)
					  ->MboRemote("SUM(taxcost) AS totaltax")
					  ->getMboSet()
					  ->getMbo()
					  ->getString("totaltax");
		$totaltax = floatval($totaltax);

		// Get pre-tax total
		$pretaxtotal = (new Set())->useTransactionManager()->MboSetRemote("poline")
						 ->setWhere($whereClause)
						 ->MboRemote("SUM(linecost) AS pretaxtotal")
						 ->getMboSet()
						 ->getMbo()
						 ->getString("pretaxtotal");
		$pretaxtotal = floatval($pretaxtotal);

		// Calculate initial totals
		$totalcost = $totaltax + $pretaxtotal;
		$totalbasecost = $totalcost * $exchangerate;

		$set = new Set();
		$paidcost = (new Set())->useTransactionManager()->MboSetRemote("popayment")
					  ->setWhere($whereClause)
					  ->MboRemote("SUM(paidcost) AS paidcost")
					  ->getMboSet()
					  ->getMbo()
					  ->getString("paidcost");
		$paidcost = floatval($paidcost) * $exchangerate;

		$unpaidcost = $totalbasecost - $paidcost;

		$set = new Set();
		$poSet = (new Set())->useTransactionManager()->MboSetRemote("po")
					->setWhere($whereClause)
					->MboRemote("discountprice")
					->MboRemote("discountpercentage")
					->MboRemote("exchangerate")
					->getMboSet();

		if ($poSet->counts() > 0) {  
		  $po = $poSet->getMbo();

		  $discountprice = floatval($po->getString("discountprice"));
		  $discountpercentage = floatval($po->getString("discountpercentage"));
		  $exchangerate = floatval($po->getString("exchangerate"));

		  // Calculate totals with discount
		  $pretaxtotaldiscount = $pretaxtotal - $discountprice;
		  $totaltax2 = $totaltax * ($discountpercentage / 100);
		  $totaltax2 = $totaltax - $totaltax2;
		  $totalcost = $totaltax2 + $pretaxtotaldiscount;
		  $totalbasecost = $totalcost * $exchangerate;
		  $unpaidcost = $paidcost - $totalbasecost;  //harus minus

		}

		// Update PO record
		(new Set())->useTransactionManager()->MboSetRemote("po")
			->setWhere($whereClause)
			->setValue("totalcost", $totalcost)
			->setValue("totaltax", $totaltax)
			->setValue("totalbasecost", $totalbasecost)
			->setValue("pretaxtotal", $pretaxtotal)
			->setValue("paidcost", $paidcost)
			->setValue("unpaidcost", $unpaidcost)
			->setValue("pretaxtotaldiscount", $pretaxtotaldiscount)
			->setValue("totaltax2", $totaltax2)
			->change();
	}
	
	public static function ProcessImportBillings($value){
		$retval = new stdClass();
		$billinglineid = $value["billinglineid"];
		
		$billinglineSet = (new Set())->useTransactionManager()->MboSetRemote("billingline bl")->setRelationship("billing b", "bl.billingnum = b.billingnum")
		->setWhere("bl.billinglineid='$billinglineid'")
			->MboRemote("bl.billingnum")->MboRemote("bl.billinglinenum")->MboRemote("bl.itemnum")->MboRemote("bl.description")->MboRemote("bl.spectext")->MboRemote("bl.unitcost")
			->MboRemote("bl.linecost")->MboRemote("bl.unitprice")->MboRemote("bl.lineprice")->MboRemote("bl.unitprofit")->MboRemote("bl.lineprofit")
			->MboRemote("bl.unitpricediscount")->MboRemote("bl.linepricediscount")->MboRemote("bl.unitprofitdiscount")->MboRemote("bl.lineprofitdiscount")
			->MboRemote("bl.quantity")->MboRemote("bl.issueunit")->MboRemote("bl.primarydiscount")->MboRemote("bl.secondarydiscount")
			->MboRemote("bl.discountprice")->MboRemote("bl.conditiontype")->MboRemote("bl.wonum")->MboRemote("bl.fromwarehouse")
			->MboRemote("bl.fromracknum")->MboRemote("bl.costdate")->MboRemote("bl.expdate")->MboRemote("bl.billinglineid")
			->MboRemote("bl.tax")->MboRemote("bl.taxcost")->MboRemote("bl.remarks")
			->MboRemote("b.personid")->MboRemote("bl.gldebitacct")->MboRemote("bl.glcreditacct")->MboRemote("bl.actualdate")->MboRemote("b.customercode")
			->MboRemote("b.orgcode")->MboRemote("b.sitecode")->MboRemote("b.subtotalpricediscount")->MboRemote("bl.costtype")
			->MboRemote("bl.unitcommission")->MboRemote("bl.linecommission")
		->getMboSet();
	
		$bill = $billinglineSet->getMbo();
		
		$invoicenum = $value["invoicenum"];
		$invoicelinenum = Xbo::getLineNumber("invoiceline", "invoicelinenum", "invoicenum='$invoicenum'");
		$customercode = $bill->getString("customercode");
		$linetype = "item";
		$itemnum = $bill->getString("itemnum");
		$conditiontype = $bill->getString("conditiontype");
		$description = $bill->getString("description");
		$exchangerate = 1;
		$wonum = $bill->getString("wonum"); 
		$quantity = $bill->getString("quantity");
		$saleunit = $bill->getString("issueunit");
		$unitcost = $bill->getString("unitprice");
		$linecost = $bill->getString("lineprice");
		$unitcost2 = $bill->getString("unitcost");
		$linecost2 = $bill->getString("linecost");
		$unitprofit = $bill->getString("unitprofit");
		$lineprofit = $bill->getString("lineprofit");
		$unitpricediscount = $bill->getString("unitpricediscount");
		$linepricediscount = $bill->getString("linepricediscount");
		$unitprofitdiscount = $bill->getString("unitprofitdiscount");
		$lineprofitdiscount = $bill->getString("lineprofitdiscount");
		$glcreditacct = $bill->getString("glcreditacct");
		$gldebitacct = $bill->getString("gldebitacct");
		$actualdate = $bill->getString("actualdate");
		$costtype = $bill->getString("costtype");
		$subtotalpricediscount = $bill->getString("subtotalpricediscount");
		$unitcommission = $bill->getString("unitcommission");
		if(empty($unitcommission)) $unitcommission = 0;
		$linecommission = $bill->getString("linecommission");
		
		$discount = 0;
		$discountprice = 0;
		$conversionfactor = 1;
		
		
		$tax = $bill->getString("tax");
		$taxcost = $bill->getString("taxcost");
		$warehouse = $bill->getString("fromwarehouse");
		$racknum = $bill->getString("fromracknum");
		$consignment = "Y";
		$enterby = Xbo::getLoginUser();
		$salesman = $bill->getString("personid");
		$enterdate = Xbo::setEnterDate();
		$costdate = $bill->getString("costdate");
		$changeby = $enterby;
		$changedate = $enterdate;
		$remarks = $bill->getString("remarks");
		$billingnum = $bill->getString("billingnum");
		$billinglinenum = $bill->getString("billinglinenum");
		$glcreditacct = $bill->getString("glcreditacct");
		$gldebitacct = $bill->getString("gldebitacct");
		$solddate = $bill->getString("actualdate");
		$imported = "Y";
		$importkey = $bill->getString("billinglineid");
		$importobject = "billingline";
		$orgcode = $bill->getString("orgcode");
		$sitecode = $bill->getString("sitecode");		
		
		$ilSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("billingnum='$billingnum' and billinglinenum='$billinglinenum'")->MboRemote("sum(quantity) as ilquantity")
		->getMboSet();
		$il = $ilSet->getMbo();
		$ilquantity = $il->getString("ilquantity");
		if($ilquantity == null) {
			$paypercent = 100;
			$term =($paypercent/100);
			$termQty = $quantity*$term;
			$newqty = $termQty;
		}else{
			$prevpercent = ($ilquantity/$quantity)*100;
			$paypercent = 100-$prevpercent;
			$term = ($paypercent/100);
			$termQty = $quantity*$term;
			$newqty = $termQty;
		}
		$linepricediscount = $newqty*$unitpricediscount;
		
		$linecost2 = $newqty*$unitcost2;
		$linecost =$newqty*$unitcost;
		
		$discount = $newqty*$discount;
		$discountprice = $newqty*$discountprice;
		$lineprofit = $newqty*$unitprofit;
		$lineprofitdiscount = $newqty*$unitprofitdiscount;
		$taxcost =  $taxcost*$term;
		$linecommission = $newqty*$unitcommission;
		
		(new Set())->useTransactionManager()->MboSetRemote("invoiceline")
		->setValue("quantity", $newqty)
		->setValue("linecost2", $linecost2)
		->setValue("linecost", $linecost)
		->setValue("lineprofit", $lineprofit)
		->setValue("linepricediscount", $linepricediscount) 
		->setValue("lineprofitdiscount", $lineprofitdiscount)
		->setValue("discount", $discount)
		->setValue("discountprice", $discountprice)
		->setValue("taxcost", $taxcost)
		->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)->setValue("customercode", $customercode)->setValue("linetype", $linetype)
		->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("description", $description)->setValue("exchangerate", $exchangerate)
		->setValue("saleunit", $saleunit)->setValue("unitcost", $unitcost)->setValue("unitpricediscount", $unitpricediscount)
		->setValue("unitcost2", $unitcost2)->setValue("unitprofit", $unitprofit)->setValue("unitprofitdiscount", $unitprofitdiscount)		
		->setValue("conversionfactor", $conversionfactor)->setValue("tax", $tax)
		->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("consignment", $consignment)
		->setValue("enterby", $enterby)->setValue("salesman", $salesman)->setValue("enterdate", $enterdate)->setValue("costdate", $costdate)
		->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("remarks", $remarks)->setValue("billingnum", $billingnum)->setValue("wonum", $wonum)
		->setValue("billinglinenum", $billinglinenum)->setValue("imported", $imported)->setValue("importkey", $importkey)->setValue("importobject", $importobject)
		->setValue("solddate", $solddate)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("costtype", $costtype)
		->setValue("keylinenum", $billinglinenum)->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)
		->add();
		
		$set = new Set();
		$set->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("paypercent", $paypercent)->change();
		$retval->msg = "written";
		return $retval;
	}
	
	public static function SumTax($invoicenum){
	
		$invoiceSet= (new Set())->useTransactionManager()->MboSetRemote("invoice")->setRelationship("invoicecharge", "invoicecharge.invoicenum = invoice.invoicenum")
		->setWhere("invoice.invoicenum='$invoicenum'")->MboRemote("invoice.paymenttype")
		->MboRemote("invoice.orgcode")->MboRemote("invoice.sitecode")->MboRemote("invoice.ponum")->MboRemote("invoice.sonum")
		->MboRemote("invoicecharge.taxreceiptnum")->MboRemote("invoicecharge.enterby")->MboRemote("invoicecharge.enterdate")
		->MboRemote("invoicecharge.gldebitacct")->MboRemote("invoicecharge.glcreditacct")
		->getMboSet();
		$invoice = $invoiceSet->getMbo();
		
		$paymenttype = $invoice->getString("paymenttype");
		$orgcode =  $invoice->getString("orgcode");
		$sitecode =  $invoice->getString("sitecode");
		$ponum =  $invoice->getString("ponum");
		$sonum =  $invoice->getString("sonum");
		$taxreceiptnum =  $invoice->getString("taxreceiptnum");
		$enterby =  $invoice->getString("enterby");
		$enterdate =  $invoice->getString("enterdate");
		$gldebitacct  =  $invoice->getString("gldebitacct");
		$glcreditacct  =  $invoice->getString("glcreditacct");
		
		
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		
		
		(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->remove();
		
		$icSet= (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")
		->MboRemote("sum(taxcost) as sumtaxcost")
		->getMboSet();
		$ic = $icSet->getMbo();
		$sumtaxcost = $ic->getString("sumtaxcost");
		if(empty($sumtaxcost)) $sumtaxcost = 0;
		$invoicechargelinenum = Xbo::getLineNumber("invoicecharge", "invoicechargelinenum", "invoicenum='$invoicenum'");		
		
		
		$val = new stdClass();
		$val->customercode = null;
		$val->paymenttype = $paymenttype;
		$val->orgcode  = $orgcode;
		$val->sitecode  = $sitecode;
		$ss = new InvoiceActionSet();
		
		if($sumtaxcost != 0){
			if(!empty($ponum)){
				(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $invoicechargelinenum)
				->setValue("quantity", 1)->setValue("linecost", $sumtaxcost)->setValue("origin", "tax-in")->setValue("ponum", $ponum)
				->setValue("consignment", 'Y')->setValue("description", "Tax-In")->setValue("unitcost", $sumtaxcost)
				->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
				->setValue("taxreceiptnum", $taxreceiptnum)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
				->setValue("changedate", $changedate)->setValue("changeby", $changeby)
				->add();
			}else{
				if(!empty($sonum)){
					(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $invoicechargelinenum)
					->setValue("quantity", 1)->setValue("linecost", $sumtaxcost)->setValue("origin", "tax-out")->setValue("sonum", $sonum)
					->setValue("consignment", 'Y')->setValue("description", "Tax Out")->setValue("unitcost", $sumtaxcost)
					->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
					->setValue("taxreceiptnum", $taxreceiptnum)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
					->setValue("changedate", $changedate)->setValue("changeby", $changeby)
					->add();
				}else{
					(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $invoicechargelinenum)
					->setValue("quantity", 1)->setValue("linecost", $sumtaxcost)->setValue("origin", "tax-out")
					->setValue("consignment", 'Y')->setValue("description", "Tax Out")->setValue("unitcost", $sumtaxcost)
					->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
					->setValue("taxreceiptnum", $taxreceiptnum)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
					->setValue("changedate", $changedate)->setValue("changeby", $changeby)
					->add();
				}
				
			}
		}
		
		
	}
	public static function ImportBillings(){
		$value = $_POST["importbillings"];
		$invoicenum = $_POST["invoicenum"];
		
		$cnt = count($value);
		$newmsg = null;
		for($i=0; $i<$cnt; $i++){
			$value[$i]["invoicenum"] = $invoicenum;
			$get = InvoiceActionSet::ProcessImportBillings($value[$i]);
			$msg = $get->msg;
			if($msg == "notimported"){
				$newmsg = $msg;
				break;
			}else{
				$newmsg = "imported";
			}
		}
		
		InvoiceActionSet::SumTax($invoicenum);
		Set::throws(["msg"=>$newmsg]);
	}

	public static function PreviewDiscount(){
		
		$previewdiscount = $_POST["previewdiscount"];
		$discounttype = $_POST["discounttype"];
		$invoicenum = $_POST["invoicenum"];
		
	
		$ivcSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("subtotalcost")->MboRemote("totaltax")->getMboSet();
		$ivc = $ivcSet->getMbo();
		$subtotalcost = $ivc->getString("subtotalcost");
		$totaltax = $ivc->getString("totaltax");
		
		if($discounttype == "discountprice"){
			$discountpercentage = ($previewdiscount * 100) / $subtotalcost;
			$discountprice = $previewdiscount;
		}else{
			$discountpercentage = $previewdiscount;
			$discountprice = ($discountpercentage / 100) * $subtotalcost;
		}
		
		$totaltaxdiscount = $totaltax-(($discountpercentage/100)*$totaltax);
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoiceline.invoicenum='$invoicenum'")
		->MboRemote("invoiceline.unitcost2")->MboRemote("invoiceline.unitcost")->MboRemote("invoiceline.unitpricediscount")->MboRemote("invoiceline.quantity")
		->getMboSet();
		
		$ivc = $invoiceSet->counts();
		
		$subtotalcostdiscount =0;
		
		for($i=0; $i<$ivc; $i++){
			$invoice = $invoiceSet->getMbo();
			$unitcost =  $invoice->getString("unitcost2");
			$unitprice =  $invoice->getString("unitcost");
			$quantity =  $invoice->getString("quantity");
			$unitpricediscount =  $invoice->getString("unitpricediscount");
			$discountlagi = ($discountpercentage/100)*$unitpricediscount;
			$unitpricediscount2 = $unitpricediscount-$discountlagi;
			$linepricediscount = $unitpricediscount2*$quantity;
			$subtotalcostdiscount = $subtotalcostdiscount+$linepricediscount;
		}
		$totalcostdiscount = $subtotalcostdiscount+$totaltaxdiscount;
		Set::throws(["subtotalcostdiscount"=>$subtotalcostdiscount, "totaltaxdiscount"=> $totaltaxdiscount, "totalcostdiscount"=>$totalcostdiscount]);
	}
	public static function ApplyDiscount(){
		
		$invoicenum=$_POST['invoicenum'];
		$discount = $_POST['applydiscount'];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum' and (ponum is not null or vendorcode is not null)")->getMboSet();
		$co = $invoiceSet->counts();
		if($co > 0){
			$msg = "notallowedpovendor";
		}else{
			
			$otherPaymentSet = (new Set())->useTransactionManager()->MboSetRemote("invpaymentline")->setWhere("invoicenum='$invoicenum'")->getMboSet();
			$cop = $otherPaymentSet->counts();
			if($cop > 0){
				$msg = "notallowedpovendor";
			}else{
				
				$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")
				->MboRemote("quantity")->MboRemote("invoicelineid")
				->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("wonum")->MboRemote("conditiontype")->MboRemote("costdate")
				->MboRemote("unitcost2")->MboRemote("unitcost")->MboRemote("unitpricediscount")->MboRemote("tax")->MboRemote("linetype")->MboRemote("invoicelinenum")
				->MboRemote("billingnum")->MboRemote("billinglinenum")->MboRemote("sonum")->MboRemote("ponum")->MboRemote("customercode")->MboRemote("importkey")
				->getMboSet();
			
				$cnt = $invoicelineSet->counts();
				for($i=0; $i<$cnt; $i++){
					$invoice = $invoicelineSet->getMbo();
					$invoicelineid =  $invoice->getString("invoicelineid");
					
					$itemnum = $invoice->getString("itemnum");
					$warehouse = $invoice->getString("warehouse");
					$wonum  = $invoice->getString("wonum");					
					$quantity  = $invoice->getString("quantity");
					$costdate = $invoice->getString("costdate");
					$conditiontype = $invoice->getString("conditiontype");
					$racknum = $invoice->getString("racknum");
					$invoiceunitpricediscount = $invoice->getString("unitpricediscount");
					$invoiceunitprice = $invoice->getString("unitcost");
					$invoiceunitcost = $invoice->getString("unitcost2");
					$tax = $invoice->getString("tax");
					$invoicelinenum = $invoice->getString("invoicelinenum");
					$linetype = $invoice->getString("linetype");
					$billingnum = $invoice->getString("billingnum");
					$billinglinenum = $invoice->getString("billinglinenum");
					$ponum = $invoice->getString("ponum");
					$sonum = $invoice->getString("sonum");
					$customercode = $invoice->getString("customercode");
					$importkey = $invoice->getString("importkey");
					
					if(empty($racknum)) {
						$rackval = " and (racknum is null or racknum = '') ";
						$fromrack = " and (fromrack is null or fromrack = '') ";
					}else{
						$rackval = " and racknum = '$racknum' ";
						$fromrack = " and fromrack = '$racknum' ";
					}
					
					if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype = '') ";
					else $condval = " and conditiontype = '$conditiontype' ";
					
					if(empty($costdate)) $costdate = null;
					else $costdate = Super::whereDate("costdate", $costdate);
					
					
					if($ponum != null){
						//PO DISCOUNT NOT ALLOWED
					}else{
					
						if($sonum != null && $customercode != null){
							if($linetype == "services"){
								//PENJUALAN JASA
								
								$servsaletransSet = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$invoicelinenum' ")
								->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")
								->getMboSet();
								
								$servsale = $servsaletransSet->getMbo();
								$unitcost = $servsale->getString("unitcost");
								$unitprice = $servsale->getString("unitprice");
								$unitpricediscount = $servsale->getString("unitpricediscount");
								
							}else{
								if($linetype == "item"){
									
									$matsaleSet = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("itemnum='$itemnum' and warehouse = '$warehouse' and sonum='$sonum' $condval $fromrack $costdate")
									->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")
									->MboRemote("unitcommission")
									->getMboSet();
									$matsale = $matsaleSet->getMbo();
									$unitcost = $matsale->getString("unitcost");
									$unitprice = $matsale->getString("unitprice");
									$unitpricediscount = $matsale->getString("unitpricediscount");
									$unitcommission = $matsale->getString("unitcommission");
									//echo "$unitpricediscount <br>";
								}
							}
						}else{
							
							if($sonum == null && $customercode != null){
								if($billingnum == null){
									//INVENTORY SELLING
									$msg = "invsellnotallowed";
									Set::throws(["msg"=>$msg]);
									return $msg;
								}else{
									//CONSIGNMENT SELLING
									$billinglineSet = (new Set())->useTransactionManager()->MboSetRemote("billingline")->setWhere("billingnum = '$billingnum' and billinglinenum='$billinglinenum'")
									->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")->MboRemote("unitcommission")
									->getMboSet();
									
									$bill = $billinglineSet->getMbo();
									$unitcost = $bill->getString("unitcost");
									$unitprice = $bill->getString("unitprice");
									$unitpricediscount = $bill->getString("unitpricediscount");
									$unitcommission =  $bill->getString("unitcommission");
								}
							}else{
							}
						}
					}
					
					
					if($unitcost == null) $unitcost = 0;
					if($unitprice == null) $unitprice = 0;
					if($unitpricediscount == null) $unitpricediscount = 0;
					if(empty($unitcommission)) $unitcommission = 0;
					
					if($discount == 100)  $discountprice = $unitpricediscount;
					else $discountprice = $unitpricediscount*($discount/100);
											
					$newunitpricediscount = $unitpricediscount-$discountprice;
					$newlinepricediscount = $newunitpricediscount*$quantity;
					$newunitprofitdiscount = $newunitpricediscount-$unitcost;
					$newlineprofitdiscount = $newunitprofitdiscount*$quantity;	
					
					if($discount == 0) {
						$newlinecommission = ($unitcommission*$quantity);
					}else{
						
						$oldlinecommission = $unitcommission*$quantity;
						$discountcommission = $oldlinecommission *($discount/100);
						$newlinecommission = $oldlinecommission - $discountcommission;
					}
					
					$newunitpricediscount = round($newunitpricediscount,2);
					$newlinepricediscount = round($newlinepricediscount,2);
					$newunitprofitdiscount = round($newunitprofitdiscount,2);
					$newlineprofitdiscount = round($newlineprofitdiscount,2);
					$newlinecommission = round($newlinecommission,2);
					
					if(!empty($tax)){
						//create tax cost
						$taxcost = XboSet::getTaxValue($tax);
						$taxcost = ($taxcost/100)*$newlinepricediscount;
						//end tax cost
					}else{
						$taxcost  =0;
					}
					
					
					$newdiscountprice = $discountprice*$quantity;
					(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicelineid='$invoicelineid'")
					->setValue("unitpricediscount", $newunitpricediscount)
					->setValue("linepricediscount", $newlinepricediscount)->setValue("taxcost", $taxcost)
					->setValue("unitprofitdiscount", $newunitprofitdiscount)->setValue("lineprofitdiscount", round($newlineprofitdiscount,2))
					->setValue("discount", $discount)->setValue("discountprice", $newdiscountprice)
					->setValue("linecommission", $newlinecommission)
					->change();
					
					
					$setc = new Set();
					//INVOICECHARGE DISCOUNT - IMPOSIBBLE!
									
				}
				
				InvoiceActionSet::SumTax($invoicenum);
				
				$msg = "ok";
			}
		}
		Set::throws(["msg"=>$msg]);
	}
	public static function ProcessImportMaterialSales($mo){
		$imvalObj = new stdClass();
		$invoicenum = $_POST["invoicenum"];
		$oldinvoicelinenum = 0;
		$set = new Set();
		$invSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("sonum")->MboRemote("ponum")->getMboSet();
		$inv = $invSet->getMbo();
		$ponum = $inv->getString("ponum");
		$sonum =  $inv->getString("sonum");
		
		if(!empty($ponum) || !empty($sonum)){
			$msg = "posonotallowed";
		}else{
			
			$ilSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and ponum is not null")->MboRemote("ponum")->getMboSet();
			$countil = $ilSet->counts();
			if($countil >0){
				$msg = "posonotallowed";
			}else{
				$ilSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and sonum is not null")->MboRemote("ponum")->getMboSet();
				$countil = $ilSet->counts();
				if($countil >0){
					$msg = "posonotallowed";
				}else{
				
					$ipSet = (new Set())->useTransactionManager()->MboSetRemote("invpaymentline")->setWhere("invoicenum='$invoicenum'")->MboRemote("invoicenum")->getMboSet();
					$countip = $ipSet->counts();
					if($countip > 0){
						$msg = "posonotallowed";
					}else{
					
						$invlineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote("max(invoicelinenum) as invoicelinenum")->getMboSet();
						$invline = $invlineSet->getMbo();
						$invoicelinenum = $invline->getString("invoicelinenum");
						if($invoicelinenum == null) {
							$invoicelinenum = 10;
							$newinvoicelinenum = $invoicelinenum;
						}else{
							$newinvoicelinenum = $invoicelinenum+10;
						}
						
						
						
						$dr = $mo->datarecord;
						$matsaleimportid = $dr["matsaleimportid"];
						$hastaxed = $dr["hastaxed"];
						$taxcode = $dr["taxcode"];
					
						$matsaleimportSet = (new Set())->useTransactionManager()->MboSetRemote("matsaleimport")
						->setWhere("matsaleimportid='$matsaleimportid'")
						->MboRemote("matsaleimport.customercode")->MboRemote("matsaleimport.itemnum")->MboRemote("matsaleimport.linetype")->MboRemote("matsaleimport.quantity")
						->MboRemote("matsaleimport.unitcost")->MboRemote("matsaleimport.linecost")->MboRemote("matsaleimport.unitprice")->MboRemote("matsaleimport.lineprice")
						->MboRemote("matsaleimport.unitprofit")->MboRemote("matsaleimport.lineprofit")->MboRemote("matsaleimport.description")
						->MboRemote("matsaleimport.conditiontype")->MboRemote("matsaleimport.unitpricediscount")->MboRemote("matsaleimport.linepricediscount")
						->MboRemote("matsaleimport.unitprofitdiscount")->MboRemote("matsaleimport.lineprofitdiscount")
						->MboRemote("matsaleimport.costdate")->MboRemote("matsaleimport.warehouse")->MboRemote("matsaleimport.transnum")
						->MboRemote("matsaleimport.fromrack")->MboRemote("matsaleimport.gldebitacct")->MboRemote("matsaleimport.salesman")
						->MboRemote("matsaleimport.glcreditacct")->MboRemote("matsaleimport.costtype")
						->MboRemote("matsaleimport.unitcommission")->MboRemote("matsaleimport.linecommission")
						->getMboSet();
						
						$matsaleimport = $matsaleimportSet->getMbo();
						
						$customercode = $matsaleimport->getString("customercode");
						$itemnum = $matsaleimport->getString("itemnum");
						
						$saleunit = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
						
						
						$linetype = $matsaleimport->getString("linetype");
						$quantity = $matsaleimport->getString("quantity");
						if($quantity < 0) $quantity = $quantity*-1; 
						$unitcost = $matsaleimport->getString("unitcost");
						$linecost = $matsaleimport->getString("linecost");
						$unitprice = $matsaleimport->getString("unitprice");
						$lineprice = $matsaleimport->getString("lineprice");
						$unitprofit = $matsaleimport->getString("unitprofit");
						$lineprofit = $matsaleimport->getString("lineprofit");
						$description = $matsaleimport->getString("description");
						$conditiontype = $matsaleimport->getString("conditiontype");
						$unitpricediscount = $matsaleimport->getString("unitpricediscount");
						$unitpricediscount2 = $unitpricediscount;
						$linepricediscount = $matsaleimport->getString("linepricediscount");						
						$unitprofitdiscount = $matsaleimport->getString("unitprofitdiscount");
						$lineprofitdiscount = $matsaleimport->getString("lineprofitdiscount");
						$unitcommission = $matsaleimport->getString("unitcommission");
						$linecommission = $matsaleimport->getString("linecommission");
						$salesman = $matsaleimport->getString("salesman");
						$costdate = $matsaleimport->getString("costdate");
						$warehouse = $matsaleimport->getString("warehouse");
						$fromrack = $matsaleimport->getString("fromrack");
						$gldebitacct = $matsaleimport->getString("gldebitacct");
						$glcreditacct = $matsaleimport->getString("glcreditacct");
						$transnum = $matsaleimport->getString("transnum");
						$costtype = $matsaleimport->getString("costtype");
						$enterdate = Xbo::setEnterDate();
						$enterby = Xbo::getLoginUser();
						$changeby = $enterby;
						$changedate = $enterdate;
						$importkey = $matsaleimportid;
						$imported = 'Y';
						$importobject = "matsaletrans";
						
						
						if($taxcode != null){
							if($hastaxed == "false"){
								$taxcost = XboSet::getTaxValue($taxcode);
								$taxcost = ($taxcost/100)*$linepricediscount;
							}else{
								$taxvalue = XboSet::getTaxValue($taxcode);
								$taxvalue = $taxvalue/100;
								$tx = $taxvalue/$taxvalue;
								$tx2 = $tx+$taxvalue;
								$linepricediscount2 = $linepricediscount/$tx2;
								$taxcost = $linepricediscount2*$taxvalue;
								$linepricediscount = $linepricediscount2;
								$unitpricediscount = $linepricediscount/$quantity;
								
								$unitprofitdiscount = $unitpricediscount-$unitcost;
								$lineprofitdiscount = $unitprofitdiscount*$quantity;
								
							}
						}else{
							$taxcost = 0;
						}
						
						(new Set())->useTransactionManager()->MboSetRemote("invoiceline")
						->setValue("customercode", $customercode)->setValue("itemnum", $itemnum)->setValue("linetype", $linetype)->setValue("quantity", $quantity)->setValue("unitcost2", $unitcost)
						->setValue("linecost2", $linecost)->setValue("unitcost", $unitprice)->setValue("linecost", $lineprice)->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
						->setValue("description", $description)->setValue("conditiontype", $conditiontype)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)->setValue("changeby", $changeby)
						->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $newinvoicelinenum)->setValue("changedate", $changedate)
						->setValue("taxcost", $taxcost)->setValue("tax", $taxcode)->setValue("saleunit", $saleunit)
						->setValue("importkey", $importkey)->setValue("imported", $imported)->setValue("importobject", $importobject)
						->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
						->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
						->setValue("costdate", $costdate)->setValue("warehouse", $warehouse)->setValue("racknum", $fromrack)->setValue("costtype", $costtype)
						->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("salesman", $salesman)->setValue("transnum", $transnum)
						->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)
						->add();
						
						$invclineSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->MboRemote("max(invoicechargelinenum) as invoicechargelinenum")->getMboSet();
						$invcline = $invclineSet->getMbo();
						$invoicechargelinenum = $invcline->getString("invoicechargelinenum");
						if($invoicechargelinenum == null) {
							$invoicechargelinenum = 10;
							$newinvoicechargelinenum = $invoicechargelinenum;
						}else{
							$newinvoicechargelinenum = $invoicechargelinenum+10;
						}
						
						if($taxcost > 0){
							(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $newinvoicechargelinenum)->setValue("customercode", $customercode)
							->setValue("description", "Tax Out")->setValue("quantity", 1)->setValue("unitcost", $taxcost)->setValue("linecost", $taxcost)->setValue("origin", "tax-out")
							->setValue("chargekey", 9999)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)->setValue("changeby", $changeby)->setValue("changedate", $changedate)
							->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
							->add();
						}
						
						if($customercode == null){
							$custval = "and (customercode is null or customercode='')";
						}else{
							$custval = "and customercode = '$customercode'";
						}
						(new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("matsaletransid=$importkey")
						->setValue("invoiced", "Y")->setValue("invoicenum", $invoicenum)
						->setValue("invoicelinenum", $newinvoicelinenum)->change();
						
						$msg = "imported";
						
					}
				}
			}
		}
		
		$imvalObj->msg = $msg;
		
		return $imvalObj;
	}
	public static function ImportMaterialSales(){
		$postvalue = $_POST["matsaleimport"];
		$tbname = $_POST["tablename"];
		$cnt = count($postvalue);
		$msg = null;
		
		for($i=0; $i<$cnt; $i++){
			$mo = new stdClass();
			$mo->tablename = $tbname;
			$mo->datarecord = $postvalue[$i];
			
			$get = InvoiceActionSet::ProcessImportMaterialSales($mo);
			$msg = $get->msg;
			if($msg != "imported"){
				break;
			}
		}
		if($msg == "imported"){
			Set::throws(["msg"=>$msg]);
		}else{
			Set::throws(["msg"=>$msg]); 
		}
	}
	public static function WriteInvoiceSet(){
		$value = $_POST["writeinvline"];
		$countval = count($value);
		for($i=0; $i<$countval; $i++){
			$msg = InvoiceActionSet::WriteInvoice($value[$i]);
		}
		Set::throws(["msg" => "ok"]);
	}
	public static function WriteInvoice($value)

	{//matsaletrans
		
		$invoicenum = $value["invoicenum"];
		$table = $value["table"];
		$key = $value["key"];
		$keyline = $value["keyline"];
		$quantity = $value["quantity"];
		if(isset($value["costdate"])) $costdate = $value["costdate"];
		else $costdate = null;
		$qval = $quantity;
		$unitprice = $value["cost"];
		if (isset($value["linecost"])) $lineprice = $value["linecost"];
		if (isset($value["taxcost"])) $taxprice = $value["taxcost"];
		
		$set = new Set();
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		
		if(empty($taxprice)) $taxprice = 0;
		
		$invSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("refund")->MboRemote("ponum")->MboRemote("sonum")->MboRemote("customercode")
		->MboRemote("orgcode")->MboRemote("sitecode")
		->getMboSet();
		$ivc = $invSet->getMbo();
		$isrefund = $ivc->getString("refund");
		$isponum = $ivc->getString("ponum");
		$issonum = $ivc->getString("sonum");
		$customercode = $ivc->getString("customercode");
		$orgcode = $ivc->getString("orgcode");
		$sitecode = $ivc->getString("sitecode");
		$glrevenue = Xbo::getRevenueAcct($orgcode, $sitecode);
	
		$invoicechargelinenum = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->MboRemote("max(invoicechargelinenum)+1 as invoicechargelinenum")
		->getMboSet()->getMbo()->getString("invoicechargelinenum");
		
		if(empty($invoicechargelinenum)) $invoicechargelinenum=1;
		if ($table == "invoicecharge") {
			
			$sonum = $key;
			$unitcost = $value["cost"];
			$origin = $value["origin"];
			$invoicechargeid = $value["invoicechargeid"];
			
			if ($origin == "tax") {
			
				if($issonum != null && $isponum == null || !empty($customercode)){
					
					$sombo = (new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$key'")->MboRemote("customercode")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("employeesid")->getMboSet()->getMbo();
					$orgcode = $sombo->getString("orgcode");
					$sitecode = $sombo->getString("sitecode");
					if(empty($customercode)) $customercode = $sombo->getString("customercode");
					$salesman = $sombo->getString("employeesid");
					$glrevenue = Xbo::getRevenueAcct($orgcode, $sitecode);
				
					$totaltaxcost = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("taxcost>0 and sonum='$key'")->MboRemote("sum(taxcost)as tax")->getMboSet()->getMbo()->getString("tax");
					
					if ($isrefund == "N") {
						//$linecost = $unitcost * $quantity;
						(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("invoicechargelinenum", $invoicechargelinenum)
						->setValue("sonum", $sonum)->setValue("description", "Tax Out")->setValue("linecost", $totaltaxcost)->setValue("quantity", 1)
						->setValue("unitcost", $totaltaxcost)->setValue("gldebitacct", $glrevenue)->setValue("origin", "tax-out")->setValue("chargekey", $invoicechargeid)
						->setValue("customercode", $customercode)->add();
					}
				}
			}
			if($origin == "tax" || $origin == "return-tax"){
				if($issonum == null && $isponum != null){
					$ponum = $key;
					
					$pombo = (new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("ponum='$key'")->MboRemote("vendor")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
					$orgcode = $pombo->getString("orgcode");
					$sitecode = $pombo->getString("sitecode");
					$vendorcode = $pombo->getString("vendor");
					
					$totaltaxcost = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("taxcost>0 and ponum='$key'")->MboRemote("sum(taxcost)as tax")->getMboSet()->getMbo()->getString("tax");
					
					if($totaltaxcost == null) $totaltaxcost = 0;
					
					if ($isrefund == "N") {
						$quantity = 1;
						$linecost = $unitcost * $quantity;
						$set = new Set();
						$set->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("invoicechargelinenum", $invoicechargelinenum)->setValue("taxcost", 0)
						->setValue("ponum", $ponum)->setValue("description", "Total Masukkan")->setValue("linecost", $linecost)->setValue("quantity", $quantity)
						->setValue("unitcost", $totaltaxcost)->setValue("glcreditacct", $glrevenue)->setValue("origin", "tax-in")->setValue("chargekey", $invoicechargeid)
						->setValue("vendorcode", $vendorcode)
						->add();
					}
					if ($isrefund == "Y") {
						$totaltaxcostmin = $totaltaxcost;
						$linecost = $unitcost * $quantity;
						(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("invoicechargelinenum", $invoicechargelinenum)->setValue("taxcost", 0)
						->setValue("ponum", $ponum)->setValue("description", "Tax-In - refund")->setValue("linecost", $linecost)->setValue("quantity", $quantity)
						->setValue("unitcost", $totaltaxcostmin)->setValue("gldebitacct", $glrevenue)->setValue("origin", "return-tax")->setValue("chargekey", $invoicechargeid)
						->setValue("vendorcode", $vendorcode)
						->add();
					}
				}
				
			}
			
				return;
		}
		if ($table == "poline" || $table == "matrectrans" || $table == "servrectrans") {
			
	
			$ponum = $key;
			$pombo = (new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("ponum='$key'")->MboRemote("orgcode")->MboRemote("exchangerate")->MboRemote("sitecode")
			->MboRemote("discountpercentage")
			->getMboSet()->getMbo();
			$orgcode = $pombo->getString("orgcode");
			$sitecode = $pombo->getString("sitecode");
			$discountpercentage = $pombo->getString("discountpercentage");
			$exchangerate = $pombo->getString("exchangerate");
			
			$polineSet = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("ponum='$key' and polinenum='$keyline'")->MboRemote("linetype")->MboRemote("itemnum")->MboRemote("description")
			->MboRemote("tax")->MboRemote("taxcost")->MboRemote("orderqty")->MboRemote("unitcost")->MboRemote("orderunit")->MboRemote("linecost")->MboRemote("glcreditacct")
			->MboRemote("gldebitacct")->MboRemote("conversionfactor")->MboRemote("conditiontype")->MboRemote("warehouse")->MboRemote("polinenum")
			->getMboSet();
			
			$poline = $polineSet->getMbo();
			$glcreditacct = $poline->getString("glcreditacct");
			$gldebitacct = $poline->getString("gldebitacct");
			$conditiontype = $poline->getString("conditiontype");
			$taxcode = $poline->getString("tax");
			$conversionfactor = $poline->getString("conversionfactor");
			$itemdesc = $poline->getString("description");
			$itemnum = $poline->getString("itemnum");
			$linetype = $poline->getString("linetype");
			$warehouse = $poline->getString("warehouse");
			$polinenum = $poline->getString("polinenum");
			if($table == "matrectrans" || $table == "servrectrans"){
				$discountprice = ($discountpercentage*$unitprice)/100;
				$unitprice = $unitprice-$discountprice;
				$unitprice = $unitprice/$exchangerate;
				
				$discountprice = ($discountpercentage*$lineprice)/100;
				$lineprice = $lineprice-$discountprice;
				$lineprice = $lineprice/$exchangerate;
			}else{
				$discountprice = ($discountpercentage*$unitprice)/100;
				$unitprice = $unitprice-$discountprice;
				
				$discountprice = ($discountpercentage*$lineprice)/100;
				$lineprice = $lineprice-$discountprice;
			}
			if ($linetype == "services") {
				$orderunit = $poline->getString("orderunit");
			}
			if ($linetype == "item") {
				
				$orderunit = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='" . $poline->getString("itemnum") . "'")->MboRemote("orderunit")
				->getMboSet()->getMbo()->getString("orderunit");
			}
			if ($isrefund == "N") {
				(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $keyline)->setValue("ponum", $key)
				->setValue("linetype", $linetype)->setValue("itemnum", $itemnum)->setValue("description", $itemdesc)
				->setValue("quantity", $quantity)->setValue("orderunit", $orderunit)->setValue("unitcost", $unitprice)->setValue("linecost", $lineprice)
				->setValue("conversionfactor", $conversionfactor)->setValue("conditiontype", $conditiontype)
				->setValue("warehouse", $warehouse)->setValue("tax", $taxcode)->setValue("taxcost", $taxprice)
				->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
				->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("exchangerate", $exchangerate)
				->setValue("keylinenum", $polinenum)
				->add();
			}
			if($taxprice != 0) {
				
				$ponum = $key;
				$pombo =(new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("ponum='$key'")->MboRemote("vendor")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
				$orgcode = $pombo->getString("orgcode");
				$sitecode = $pombo->getString("sitecode");
				$vendorcode = $pombo->getString("vendor");
				
		
				$discounttax = ($discountpercentage*$taxprice)/100;
				$taxprice = $taxprice-$discounttax;
				(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $keyline)->setValue("consignment", "N")
				->setValue("ponum", $ponum)->setValue("vendorcode", $vendorcode)->setValue("description", "Total Tax-In")
				->setValue("unitcost", $taxprice)->setValue("quantity", 1)->setValue("linecost", $taxprice)->setValue("taxcode", $taxcode)
				->setValue("origin", "tax-in")->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
				->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)
				->add();
				
				$invoicechargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("ponum='$ponum' and invoicenum='$invoicenum'")->MboRemote("sum(linecost) as sumlinecost")
				->getMboSet();
				
				$invcharge = $invoicechargeSet->getMbo();
				$sumlinecost=$invcharge->getString("sumlinecost");
				if(empty($sumlinecost)) $sumlinecost = 0;
				
				(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("ponum='$ponum' and invoicenum='$invoicenum'")->remove();
				
				(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $keyline)->setValue("consignment", "N")
				->setValue("ponum", $ponum)->setValue("vendorcode", $vendorcode)->setValue("description", "Total Tax-In")
				->setValue("unitcost", $sumlinecost)->setValue("quantity", 1)->setValue("linecost", $sumlinecost)->setValue("taxcode", $taxcode)
				->setValue("origin", "tax-in")->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
				->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)
				->add();
			}
			
			
			return;
		}
		
		if ($table == "soline" || $table == "servsaletrans" ) {
			
			$sombo = (new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$key'")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("customercode")->MboRemote("discountpercentage")
			->MboRemote("employeesid")->getMboSet()->getMbo();
			$discount = $sombo->getString("discountpercentage");
			$customercode = $sombo->getString("customercode");
			$salesman = $sombo->getString("employeesid");
			$orgcode = $sombo->getString("orgcode");
			$sitecode = $sombo->getString("sitecode");
			
			$solineSet = (new Set())->useTransactionManager()->MboSetRemote("soline")->setRelationship("so", "so.sonum = soline.sonum")->setWhere("soline.sonum='$key' and soline.solinenum='$keyline'")
			->MboRemote("soline.linetype")->MboRemote("soline.itemnum")->MboRemote("soline.description")->MboRemote("soline.orderunit")
			->MboRemote("soline.lineprice")->MboRemote("soline.taxcode")->MboRemote("soline.glcreditacct")->MboRemote("soline.gldebitacct")->MboRemote("soline.conditiontype")
			->MboRemote("soline.warehouse")->MboRemote("soline.unitpricediscount")->MboRemote("soline.linepricediscount")
			->MboRemote("so.customercode")->MboRemote("soline.racknum")->MboRemote("soline.taxpricediscount")->MboRemote("soline.solinenum")
			->getMboSet();
			
			$soline = $solineSet->getMbo();
			$linetype = $soline->getString("linetype");
			$unitpricediscount = $soline->getString("unitpricediscount");
			if(empty($unitpricediscount)) $unitpricediscount = $unitprice;
			$linepricediscount = $soline->getString("linepricediscount");
			$customercode  = $soline->getString("customercode");
			$conditiontype = $soline->getString("conditiontype");
			$warehouse = $soline->getString("warehouse");
			$sodesc = $soline->getString("description");
			$itemnum = $soline->getString("itemnum");
			$gldebitacct = $soline->getString("gldebitacct");
			$glcreditacct = $soline->getString("glcreditacct");
			$taxcode = $soline->getString("taxcode");
			$racknum =  $soline->getString("racknum");
			$taxprice =  $soline->getString("taxpricediscount");
			$solinenum =  $soline->getString("solinenum");
			if ($linetype == "services") {
				$sellunit = $soline->getString("orderunit");
				
				$tbname = "servsaletrans";
				$transtableSet =  (new Set())->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("sonum='$key' and solinenum='$keyline'")->MboRemote("unitcost")->MboRemote("linecost")
				->MboRemote("unitprofit")->MboRemote("lineprofit")->MboRemote("unitprofitdiscount")->MboRemote("lineprofitdiscount")->MboRemote("unitcommission")
				->MboRemote("linecommission")
				->getMboSet();
			}else{
				if ($linetype == "item") {
		
					$sellunit = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='" . $itemnum . "' ")->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
					
					$tbname = "matsaletrans";
					$qval = $qval*-1;
					
					if(empty($costdate)){
						
					}else{
						
					}
				
					$wheredate = Super::whereDate("costdate", $costdate);
					$transtableSet =  (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("sonum='$key' and solinenum='$keyline' $wheredate")->MboRemote("unitcost")->MboRemote("linecost")
					->MboRemote("unitprofit")->MboRemote("lineprofit")->MboRemote("unitprofitdiscount")->MboRemote("lineprofitdiscount")
					->MboRemote("costdate")->MboRemote("costtype")->MboRemote("transnum")
					->MboRemote("unitcommission")->MboRemote("linecommission")
					->getMboSet();
					
				}
			}
			
			
			
			//get Unit profit
			$trans = $transtableSet->getMbo();
			$unitcost2 = $trans->getString("unitcost");
			//$linecost2 = $trans->getString("linecost");
			$linecost2 = $unitcost2*$quantity;
			$unitprofit = $trans->getString("unitprofit");
			$unitcommission = $trans->getString("unitcommission");
			$linecommission = $trans->getString("linecommission");
			
			if(empty($unitprofit)) $unitprofit = $unitprice;
			$lineprofit = $trans->getString("lineprofit");
			$unitprofitdiscount = $trans->getString("unitprofitdiscount");
			if(empty($unitprofitdiscount)) $unitprofitdiscount = $unitprofit;
			
			if(empty($unitcommission)) $unitcommission = 0;
			if(empty($linecommission)) $linecommission = 0;
			
			$lineprofitdiscount = $trans->getString("lineprofitdiscount");
			if($linetype == "item"){
				$costdate  = $trans->getString("costdate");
				$costtype  = $trans->getString("costtype");
				$transnum  = $trans->getString("transnum");
			}else{
				$costdate = null;
				$costtype = null;
				$transnum = null;
			}
			
			//end Unit Profit
		//	echo "Qty $quantity Unitpricedisc : $unitpricediscount Unitprofitdisc : $unitprofitdiscount ";
			$newlinepricediscount = $quantity*$unitpricediscount;
			$newlineprofitdiscount = $quantity*$unitprofitdiscount;
			$newlineprofit =  $quantity*$unitprofit;
			$newlinecommission = $quantity*$unitcommission;
			$taxvalue = XboSet::getTaxValue($taxcode);
			$newtaxprice = ($newlinepricediscount)*$taxvalue/100;
			
			if ($isrefund == "N") {
				(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $keyline)
				->setValue("sonum", $key)->setValue("customercode", $customercode)
				->setValue("linetype", $linetype)->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)
				->setValue("itemnum", $itemnum)->setValue("description", $sodesc)
				->setValue("quantity", $quantity)->setValue("saleunit", $sellunit)
				->setValue("unitcost", $unitpricediscount)
				->setValue("linecost", $newlinepricediscount) //get the last discount value from so, not the base cost
				->setValue("unitpricediscount", $unitpricediscount)
				->setValue("linepricediscount", $newlinepricediscount)
				->setValue("lineprofit", $newlineprofit)
				->setValue("lineprofitdiscount", $newlineprofitdiscount)
				->setValue("taxcost", $newtaxprice)
				->setValue("conversionfactor", 1)
				->setValue("tax", $taxcode)
				->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)
				->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
				->setValue("unitcost2", $unitcost2)->setValue("linecost2", $linecost2)
				->setValue("unitprofit", $unitprofit)
				->setValue("unitprofitdiscount", $unitprofitdiscount)
				->setValue("costdate", $costdate)->setValue("salesman", $salesman)->setValue("racknum", $racknum)
				->setValue("costtype", $costtype)->setValue("transnum", $transnum)
				->setValue("keylinenum", $solinenum)
				->setValue("unitcommission", $unitcommission)
				->setValue("linecommission", $newlinecommission)
				->add();
				
				$servsaleSet = (new Set())->useTransactionManager()->MboSetRemote($tbname)->setWhere("sonum='$key' and solinenum='$keyline'")->setValue("invoiced", "Y")->setValue("invoicenum", $invoicenum)
				->setValue("invoicelinenum", $keyline)->setValue("ispaid", "N")->setValue("isrefund", $isrefund)->change();
			
				if($newtaxprice != 0){
					(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $keyline)->setValue("consignment", "N")
					->setValue("sonum", $key)->setValue("customercode", $customercode)->setValue("description", "Total Tax Out")
					->setValue("unitcost", $newtaxprice)->setValue("quantity", 1)->setValue("linecost", $newtaxprice)->setValue("taxcode", $taxcode)
					->setValue("origin", "tax-in")->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
					->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)
					->add();
				
					$invoicechargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("sonum='$key' and invoicenum='$invoicenum'")->MboRemote("sum(linecost) as sumlinecost")
					->getMboSet();
					
					$invcharge = $invoicechargeSet->getMbo();
					$sumlinecost=$invcharge->getString("sumlinecost");
					
					(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("sonum='$key' and invoicenum='$invoicenum'")->remove();
				
					(new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setValue("invoicenum", $invoicenum)->setValue("invoicechargelinenum", $keyline)->setValue("consignment", "N")
					->setValue("sonum", $key)->setValue("customercode", $customercode)->setValue("description", "Tax Out")
					->setValue("unitcost", $sumlinecost)->setValue("quantity", 1)->setValue("linecost", $sumlinecost)->setValue("taxcode", $taxcode)
					->setValue("origin", "tax-out")->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
					->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)
					->add();
				}
			}
			
			return;
		}
		
	}
	public static function ApplyTermTables (){
		$ponum = $_POST["ponum"];
		$sonum = $_POST["sonum"];
		$invoicenum = $_POST["invoicenum"];
		$att = $_POST['applytermtable'];
		$paypercent = $_POST['paypercent'];
		
		$minTotalPayPercent = 0;
		$maxTotalPayPercent = 100;
		$existingTotalPayPercent = 0;
		
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setRelationship("invoiceline", "invoice.invoicenum = invoiceline.invoicenum")
		->setWhere("invoice.invoicenum='$invoicenum'")->MboRemote("invoice.status")->MboRemote("invoiceline.customercode")
		->MboRemote("invoiceline.tax")
		->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$status = $invoice->getString("status");
		$customercode = $invoice->getString("customercode");
		$tax = $invoice->getString("tax");
		$taxvalue = XboSet::getTaxValue($tax);
		
		if($status == "paid"){
			Set::throws(["msg"=>"invoicehasbeenpaid"]);
		}else{
			$cntatt = count($att);
			
			for($i=0; $i<$cntatt; $i++){
				$table = $att[$i];
				
				
				//invoicecharge
				if($table == "invoiceline"){
					if($sonum != null){
						//CheckPayment Terms
				
						$checkExistingInvoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("sonum='$sonum' and invoicenum not in('$invoicenum')")->MboRemote("invoicenum")->MboRemote("paypercent")->getMboSet();
						$cntExistingInvoice = $checkExistingInvoiceSet->counts();
						$remarks = "";
						$rem = [];
						
						if($cntExistingInvoice == 0){
							
						}else{
							$tempExistingPayPercent = 0 ;
							for($a=0; $a<$cntExistingInvoice; $a++){
								$checkExistingInvoice = $checkExistingInvoiceSet->getMbo();
								$existinginvoicenum = $checkExistingInvoice->getString("invoicenum");
								$tempExistingPayPercent = $checkExistingInvoice->getString("paypercent");
								$existingTotalPayPercent = $tempExistingPayPercent+$existingTotalPayPercent;
								$remarks = "Invoice: $existinginvoicenum , Payment Term: $tempExistingPayPercent %<br>";
								array_push($rem, $remarks);
							}
						}
						
						$msgr = implode("",$rem);
						$nextPayPercent = $existingTotalPayPercent + $paypercent;
						$remainingPayPercent = $maxTotalPayPercent-$existingTotalPayPercent;
						
						
						
						if($nextPayPercent> $maxTotalPayPercent){
							Set::throws(["msg"=>"termExceed", "remarks"=>$msgr, "paypercent"=>$paypercent, "remainingPayPercent" =>$remainingPayPercent, "existingTotalPayPercent"=>$existingTotalPayPercent, "nextPayPercent"=>$nextPayPercent ]);
							return null;
						}else{
							//okay
						}
						//end Check Payment Terms
					
						$sordSet = (new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->MboRemote("discountpercentage")->getMboSet();
						$sord = $sordSet->getMbo();
						$discountpercentage = $sord->getString("discountpercentage");
						
						$solineSet = (new Set())->useTransactionManager()->MboSetRemote("soline")
						->setRelationship("invoiceline", "invoiceline.sonum = soline.sonum and soline.solinenum = invoiceline.invoicelinenum")
						->setWhere("soline.sonum='$sonum'")
						->MboRemote("soline.solinenum")->MboRemote("soline.unitpricediscount")
						->MboRemote("soline.taxpricediscount")->MboRemote("soline.linetype")
						->MboRemote("invoiceline.costdate")->MboRemote("invoiceline.unitprofitdiscount")
						->MboRemote("invoiceline.unitcommission")
						->MboRemote("invoiceline.invoicelineid")
						->getMboSet();
						
						$countso = $solineSet->counts();
						
						for($j=0; $j<$countso; $j++){
							$so = $solineSet->getMbo();
							$solinenum = $so->getString("solinenum");
							$unitprice = $so->getString("unitpricediscount");
							if(empty($unitprice)) $unitprice=0;
							$unitprofitdiscount = $so->getString("unitprofitdiscount");
							if(empty($unitprofitdiscount)) $unitprofitdiscount=0;
							$unitcommission = $so->getString("unitcommission");
							if(empty($unitcommission)) $unitcommission=0;
							$costdate = $so->getString("costdate");
							$invoicelineid = $so->getString("invoicelineid");
							$wheredate = Super::whereDate("costdate", $costdate);
							$linetype = $so->getString("linetype");
							
							if(!empty($invoicelineid)){
								if($linetype == "item"){
								
									$matsaletransSet = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("sonum='$sonum' and solinenum='$solinenum' $wheredate")
									->MboRemote("sum(quantity) as quantity")->getMboSet();
									$matsale = $matsaletransSet->getMbo();
									$quantity = $matsale->getString("quantity");
									
									if(empty($quantity)){
									
										$solineSet2 = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$solinenum'")
										->MboRemote("sellqty as quantity")->getMboSet();
										$soline = $solineSet2->getMbo();
										$quantity = $soline->getString("quantity");
									
									}	
								}else{
									if($linetype == "services"){
									
										$servsaletransSet =(new Set())->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("sonum='$sonum' and solinenum='$solinenum'")
										->MboRemote("sum(quantity) as quantity")->getMboSet();
										$servsale = $servsaletransSet->getMbo();
										$quantity = $servsale->getString("quantity");
										
										if(empty($quantity)){
											
											$solineSet2 = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$solinenum'")
											->MboRemote("sellqty as quantity")->getMboSet();
											$soline = $solineSet2->getMbo();
											$quantity = $soline->getString("quantity");
										}
									}
								}
								if($quantity <0) $quantity = $quantity*-1;
								$unitprice2 = $unitprice;
								$unitprofit2 = $unitprofitdiscount;
								//echo "Quant : $quantity PayPercent : $paypercent ";
								$newqty = $quantity*($paypercent/100);
								$newlineprofit = $newqty*$unitprofit2;
								$newlineprice = $newqty*$unitprice2;
								$newtaxprice = $newlineprice*$taxvalue/100;
								$newlinecommission = $newqty*$unitcommission;
								
								$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicelineid=$invoicelineid")
								->setValue("quantity", $newqty)->setValue("linecost", $newlineprice)->setValue("lineprofit", $newlineprofit)->setValue("taxcost", $newtaxprice)
								->setValue("linecommission", $newlinecommission)
								->change();
							}
						}
					}else{
						if($ponum!=null){
							
							//CheckPayment Terms
							$checkExistingInvoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("ponum='$ponum' and invoicenum not in('$invoicenum')")->MboRemote("invoicenum")->MboRemote("paypercent")->getMboSet();
							$cntExistingInvoice = $checkExistingInvoiceSet->counts();
							$remarks = "";
							$rem = [];
							if($cntExistingInvoice == 0){
							}else{
								$tempExistingPayPercent = 0 ;
								for($a=0; $a<$cntExistingInvoice; $a++){
									$checkExistingInvoice = $checkExistingInvoiceSet->getMbo();
									$existinginvoicenum = $checkExistingInvoice->getString("invoicenum");
									$tempExistingPayPercent = $checkExistingInvoice->getString("paypercent");
									$existingTotalPayPercent = $tempExistingPayPercent+$existingTotalPayPercent;
									$remarks = "Invoice: $existinginvoicenum , Payment Term: $tempExistingPayPercent %<br>";
									array_push($rem, $remarks);
								}
							}
							$msgr = implode("",$rem);
							$nextPayPercent = $existingTotalPayPercent + $paypercent;
							$remainingPayPercent = $maxTotalPayPercent-$existingTotalPayPercent;
							
							if($nextPayPercent> $maxTotalPayPercent){
								Set::throws(["msg"=>"termExceed", "remarks"=>$msgr, "paypercent"=>$paypercent, "remainingPayPercent" =>$remainingPayPercent, "existingTotalPayPercent"=>$existingTotalPayPercent, "nextPayPercent"=>$nextPayPercent ]);
								return null;
							}else{
								//okay
							}
							//end Check Payment Terms
							
							$polineSet = (new Set())->useTransactionManager()->MboSetRemote("poline")
							->setRelationship("po", "po.ponum = poline.ponum")
							->setWhere("poline.ponum='$ponum'")
							->MboRemote("poline.polinenum")->MboRemote("poline.orderqty")->MboRemote("poline.unitcost")->MboRemote("poline.taxcost")
							->MboRemote("po.discountpercentage")
							->getMboSet();
							$countso = $polineSet->counts();
							for($j=0; $j<$countso; $j++){
								$po = $polineSet->getMbo();
								$polinenum = $po->getString("polinenum");
								$orderqty = $po->getString("orderqty");
								$unitcost = $po->getString("unitcost");
								$taxcost = $po->getString("taxcost");
								$discountpercentage = $po->getString("discountpercentage");
								
								$newqty = $orderqty*($paypercent/100);
								
								$discountprice = ($discountpercentage*$unitcost)/100;
								$unitcost = $unitcost-$discountprice;
								$newlinecost = $newqty*$unitcost;
								
								$discounttax = ($discountpercentage*$taxcost)/100;
								$taxcost = $taxcost-$discounttax;
								$newtaxcost = $taxcost*($paypercent/100);
								
								$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and ponum='$ponum' and invoicelinenum='$polinenum'")
								->setValue("quantity", $newqty)->setValue("linecost", $newlinecost)->setValue("lineprofit", 0)->setValue("taxcost", $newtaxcost)->change();
							}
						}else{
							if($ponum == null && $sonum == null){
									
								$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote("billingnum")->MboRemote("billinglinenum")
								->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("conditiontype")->MboRemote("costdate")->MboRemote("transnum")
								->MboRemote("linecost2")->MboRemote("unitcost2")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("discountprice")
								->MboRemote("unitcommission")->MboRemote("linecommission")
								->getMboSet();
							
								$countbil = $invoicelineSet->counts();
								
								
								for($j=0; $j<$countbil; $j++){
									$il = $invoicelineSet->getMbo();
									$billingnum = $il->getString("billingnum");
									$billinglinenum = $il->getString("billinglinenum");
									$itemnum = $il->getString("itemnum");
									$warehouse = $il->getString("warehouse");
									$fromrack = $il->getString("racknum");
									$conditiontype = $il->getString("conditiontype");
									$costdate = $il->getString("costdate");
									$costdate = Super::whereDate("costdate", $costdate);
									$transnum = $il->getString("transnum");
									$linecost2 = $il->getString("linecost2");
									$unitcost2 = $il->getString("unitcost2");
									$linecost = $il->getString("linecost");
									$unitcost = $il->getString("unitcost");
									$discountprice = $il->getString("discountprice");
									//$unitcommission = $il->getString("unitcommission");
									//$linecommission = $il->getString("linecommission");
									
									if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype = '') ";
									else $condval = " and conditiontype = '$conditiontype' ";
					
									if(empty($fromrack)) $fromrack = " and (fromrack is null or fromrack = '') ";
									else $fromrack = " and fromrack = '$fromrack' ";
									
										if($billingnum == null){
											
											$matsaleSet = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("itemnum='$itemnum' and transtype='SALE' and warehouse = '$warehouse' and transnum='$transnum' $condval $fromrack $costdate")
											->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")->MboRemote("quantity")
											->MboRemote("unitprofit")->MboRemote("unitprofitdiscount")->MboRemote("taxprice")->MboRemote("transnum")
											->MboRemote("unitcommission")
											->getMboSet();
											
											$matsale = $matsaleSet->getMbo();
											
											$unitprice = $matsale->getString("unitprice");
											$unitpricediscount = $matsale->getString("unitpricediscount");
											$unitprofitdiscount = $matsale->getString("unitprofitdiscount");
											$unitcommission = $matsale->getString("unitcommission");
											if(empty($unitcommission)) $unitcommission =0;
											$quantity = $matsale->getString("quantity");
											if($quantity <0) $quantity = $quantity*-1;
											
											$unitprofit = $matsale->getString("unitprofit");
											$taxprice = $matsale->getString("taxprice");
											
											$newqty = $quantity*($paypercent/100);
											$newlineprice = $newqty*$unitprice;
											$newlinepricediscount = $newqty*$unitpricediscount;
											$newlineprofit = $newqty*$unitprofit;
											$newlineprofitdiscount = $newqty*$unitprofitdiscount;
											$newtaxcost = $taxprice*($paypercent/100);
											$newlinecost2 = $newqty*$unitcost2;
											$newlinecommission = $newqty*$unitcommission;

											(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and transnum='$transnum' ")
											->setValue("quantity", $newqty)->setValue("linecost", $newlineprice)->setValue("lineprofit", $newlineprofit)
											->setValue("linepricediscount", $newlinepricediscount)->setValue("lineprofitdiscount", $newlineprofitdiscount)
											->setValue("taxcost", $newtaxcost)->setValue("linecost2", $newlinecost2)->setValue("linecommission", $newlinecommission)
											->change();
											
										}else{
											
											$billingSet =(new Set())->useTransactionManager()->MboSetRemote("billingline")
											->setWhere("billingnum='$billingnum' and billinglinenum='$billinglinenum'")
											->MboRemote("quantity")->MboRemote("unitprice")->MboRemote("unitpricediscount")->MboRemote("unitprofit")->MboRemote("unitprofitdiscount")
											->MboRemote("taxcost")->MboRemote("unitcommission")
											->getMboSet();
											
											$cntbil = $billingSet->counts();
											
											for($k=0; $k<$cntbil; $k++){
												$bill = $billingSet->getMbo();
												$quantity = $bill->getString("quantity");
												$unitprice = $bill->getString("unitprice");
												$unitpricediscount = $bill->getString("unitpricediscount");
												$unitprofit = $bill->getString("unitprofit");
												$unitprofitdiscount = $bill->getString("unitprofitdiscount");
												$taxcost = $bill->getString("taxcost");
												$unitcommission = $bill->getString("unitcommission");
												if(empty($unitcommission)) $unitcommission=0;
												$term = ($paypercent/100);
												
												$termQty = $quantity*$term;
												
												$newqty = $termQty;
												$newlineprice = $newqty*$unitcost;
												$newlinepricediscount = $newqty*$unitpricediscount;
												$newlineprofit = $newqty*$unitprofit;
												$newlineprofitdiscount = $newqty*$unitprofitdiscount;
												$newtaxcost =  $taxcost*$term;
												$newlinecost2 = $newqty*$unitcost2;
												$newlinecommission = $newqty*$unitcommission;
												
												(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and billingnum='$billingnum' and billinglinenum='$billinglinenum'")
												->setValue("quantity", $newqty)->setValue("linecost", $newlineprice)->setValue("lineprofit", $newlineprofit)
												->setValue("linepricediscount", $newlinepricediscount)->setValue("lineprofitdiscount", $newlineprofitdiscount)
												->setValue("taxcost", $newtaxcost)->setValue("linecost2", $newlinecost2)->setValue("linecommission", $newlinecommission)
												->change();
											}
									}
								}
							}
						}
					}
					InvoiceActionSet::SumTax($invoicenum);
				}else{
				}
			}
			
			(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("paypercent", $paypercent)->change();
			
			Set::throws(["msg"=>"appliedterm", "paypercent"=>$paypercent]);
		}
		
	}
	public static function ValidateInvoicedQuantity()

	{
		$pts = explode("|", $_GET["validateqtyinvoiced"]);
		$qtyinvoiced = $pts[0];
		$invoicelineid = $pts[1];
	
		$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicelineid='$invoicelineid'")->MboRemote("ponum")->MboRemote("sonum")->MboRemote("invoicelinenum")->MboRemote("invoicenum")->getMboSet();
		$invoiceline = $invoicelineSet->getMbo();
		$sonum = $invoiceline->getString("sonum");
		$ponum = $invoiceline->getString("ponum");
		$invoicenum = $invoiceline->getString("invoicenum");
		$invoicelinenum = $invoiceline->getString("invoicelinenum");
	
		$invoicebo = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("refund")->getMboSet()->getMbo();
		$isrefund = $invoicebo->getString("refund");
		if (empty($ponum) && !empty($sonum)) {
			$set = new Set();
			$sellqty = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$invoicelinenum'")->MboRemote("sellqty")->getMboSet()->getMbo()->getString("sellqty");
			if ($qtyinvoiced > 0) {
				if ($qtyinvoiced > $sellqty) {
					echo "greaterthansellqty";
				}
				else {
			
					$invoicetransSet = (new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setWhere("sonum='$sonum' and invoicelinenum='$invoicelinenum'")->MboRemote("sum(quantity) as totalinvoiced")->getMboSet();
					$invoicetrans = $invoicetransSet->getMbo();
					$totalinvoiced = $invoicetrans->getString("totalinvoiced"); 
					$newtotalinvoiced = $totalinvoiced+$qtyinvoiced;
					
					if($newtotalinvoiced > $sellqty) {
						echo "greaterthansellqty";
					}else{
						echo "ok";
					}
				}
			}
			if ($qtyinvoiced <= 0 && $isrefund == "N") {
				echo "lesserthansellqty";
			}
		}
		if (empty($sonum) && !empty($ponum)) {
			$orderqty = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("ponum='$ponum' and polinenum='$invoicelinenum'")->MboRemote("orderqty")->getMboSet()->getMbo()->getString("orderqty");
			if ($qtyinvoiced > 0) {
				if ($qtyinvoiced > $orderqty) {
					echo "greaterthansellqty";
				}
				else {
					echo "ok";
				}
			}
			if ($qtyinvoiced <= 0 && $isrefund == "N") {
				echo "lesserthansellqty";
			}
		}
	}
	public static function ValidateInvoicedCost()

	{
		$as = explode("|", $_GET["validatelncostinvoiced"]);
		$lncost = $as[0];
		$invoicelineid = $as[1];
		$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicelineid='$invoicelineid'")->MboRemote("ponum")->MboRemote("sonum")->MboRemote("invoicelinenum")->MboRemote("invoicenum")->getMboSet();
		
		$invoiceline = $invoicelineSet->getMbo();
		$sonum = $invoiceline->getString("sonum");
		$ponum = $invoiceline->getString("ponum");
		$invoicenum = $invoiceline->getString("invoicenum");
		$invoicelinenum = $invoiceline->getString("invoicelinenum");
	
		$invoicebo = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("refund")->getMboSet()->getMbo();
		$isrefund = $invoicebo->getString("refund");
	
		if (empty($ponum) && !empty($sonum)) {
		
			$SOlinecostSet = (new Set())->useTransactionManager()->MboSetRemote("soline")->setRelationship("so", "so.sonum = soline.sonum")
			->setWhere("soline.sonum='$sonum' and soline.solinenum='$invoicelinenum'")->MboRemote("soline.lineprice")->MboRemote("so.discountpercentage")->getMboSet();
			$SOlinecost = $SOlinecostSet->getMbo();
			$linecost = $SOlinecost->getString("lineprice");
			$discountpercentage = $SOlinecost->getString("discountpercentage");
			$linecost = $linecost-($linecost*$discountpercentage/100);
			
			if ($lncost > 0) {
				if ($lncost > $linecost) {
					echo "greaterthansellqty";
				}
				else {
					$invoicetransSet = (new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setWhere("sonum='$sonum' and invoicelinenum='$invoicelinenum'" )->MboRemote("sum(lineprice) as totalinvoiced")->getMboSet();
					$invoicetrans = $invoicetransSet->getMbo();
					$totalinvoiced = $invoicetrans->getString("totalinvoiced");
					
					$newtotalinvoiced = $lncost+$totalinvoiced;
					if($newtotalinvoiced>$linecost) {
						echo "greaterthansellqty";
					}else{
						echo "ok";
					}
				}
			}
			if ($lncost <= 0 && $isrefund == "N") {
				echo "lesserthansellqty";
			}
		}
		if (empty($sonum) && !empty($ponum)) {
			$linecost = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("ponum='$ponum' and polinenum='$invoicelinenum'")->MboRemote("linecost")->getMboSet()->getMbo()->getString("linecost");
			if ($lncost > 0) {
				if ($lncost > $linecost) {
					echo "greaterthansellqty";
				}
				else {
					echo "ok";
				}
			}
			if ($lncost <= 0 && $isrefund == "N") {
				echo "lesserthansellqty";
			}
		}
	}
	
	public function SynInvoiceCost($ob){
		
		$colname = $ob->colname; 
		$invoicenum = $ob->invoicenum;
		
		$invoicelinecost = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote(" sum($colname) as linecost ")->getMboSet()->getMbo()->getString("linecost");
		if($invoicelinecost == null) $invoicelinecost =0;
		
		$invoicechargecost = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
		if($invoicechargecost == null) $invoicechargecost =0;
		
		$invoicechargecostTax = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum' and origin not like '%tax%'")->MboRemote(" sum(taxcost) as taxcost ")->getMboSet()->getMbo()->getString("taxcost");
		if($invoicechargecostTax == null) $invoicechargecostTax =0;
		$invoicechargecost = $invoicechargecost+$invoicechargecostTax;
		
		$set = new Set();
		$invpaymentlinecost = (new Set())->useTransactionManager()->MboSetRemote("invpaymentline")->setWhere("invoicenum='$invoicenum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
		if($invpaymentlinecost == null) $invpaymentlinecost =0;
		$totalcost = $invoicelinecost + $invoicechargecost + $invpaymentlinecost;
		$subtotalcost = $invoicelinecost+$invpaymentlinecost;
		
		(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("totalcost", $totalcost)->change();
		
		$this->totalcost = $totalcost;
		$this->subtotalcost = $subtotalcost;
		$this->invoicechargecost = $invoicechargecost;
		return $this;
	}
	public static function SumInvoiceCost()
	{
		$invoicenum = $_GET["suminvoicecost"];
		$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")
		->setWhere("invoicenum='$invoicenum' and (ponum is not null or vendorcode is not null)")
		->MboRemote("ponum")->MboRemote("vendorcode")->MboRemote("paypercent")->getMboSet();
		
		$cntivc = $invoiceSet->counts();
		

		if($cntivc > 0 ){
			$colname = "linecost";
		}else{
			$colname = "linepricediscount";
		}
		
		$ob = new stdClass();
		$ob->invoicenum = $invoicenum;
		$ob->colname = $colname;
		
		$a = new InvoiceActionSet();
		$aa = $a->SynInvoiceCost($ob);
		
		$paypercent = (new Set())->MboSetRemote("invoice")->where("invoicenum=?", $invoicenum)->getMboValue("paypercent");
		$invoiceLineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setRelationship("invoice", "invoice.invoicenum = invoiceline.invoicenum")
		->setWhere("invoiceline.invoicenum='$invoicenum'")
		->MboRemote("invoiceline.customercode")->MboRemote("invoiceline.unitcost2")->MboRemote("invoiceline.unitcost")->MboRemote("invoiceline.unitpricediscount")
		->MboRemote("invoiceline.quantity")->MboRemote("invoiceline.invoicelineid")
		->MboRemote("invoiceline.sonum")
		->getMboSet();
		
		$cnt = $invoiceLineSet->counts();
		
		for($i=0; $i<$cnt; $i++){
			$invoice = $invoiceLineSet->getMbo();
			$customercode = $invoice->getString("customercode");
			$sonum = $invoice->getString("sonum");
			
			if(!empty($customercode) || !empty($sonum)){
				
				$unitcost = $invoice->getString("unitcost2");
				if($unitcost == null) $unitcost =0;
				
				$unitprice = $invoice->getString("unitcost");
				if($unitprice == null) $unitprice =0;
				$unitpricediscount = $invoice->getString("unitpricediscount");
				//discount 0% is not allowed
				if($unitpricediscount == null || $unitpricediscount == 0) $unitpricediscount = $unitprice;
				
				$quantity = $invoice->getString("quantity");
				if($quantity == null) $quantity =0;
				$invoicelineid =  $invoice->getString("invoicelineid");
				
				$lineprice = $unitprice*$quantity;
				$linepricediscount = $unitpricediscount*$quantity;
				
				$unitprofit = $unitprice-$unitcost;
				$unitprofitdiscount = $unitpricediscount-$unitcost;
				
				$lineprofit = $unitprofit*$quantity;
				$lineprofitdiscount = $unitprofitdiscount*$quantity;
				
				(new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicelineid='$invoicelineid'")->setValue("linecost", $lineprice)
				->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
				->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
				->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
				->change();
			}
		}
		
		$a = new InvoiceActionSet();
		$aa = $a->SynInvoiceCost($ob);
		
		$totalcost = $aa->totalcost;
		$subtotalcost = $aa->subtotalcost;
		$invoicechargecost = $aa->invoicechargecost;

		//check berapa jumlah record invoice line saat ini
		$count_records = (new Set())->useTransactionManager()
			->MboSetRemote("invoiceline")
			->where("invoicenum = ?", $ob->invoicenum )
			->countRecords();
		if($count_records == 0){
			$paypercent = 100;
		} 
		Set::throws(["totalcost" => $totalcost, "subtotalcost"=>$subtotalcost, "totaltax"=>$invoicechargecost, "paypercent" => $paypercent]);
	}
	
	public static function ValidateInvoiceLines()

	{
		$invoicenum = $_GET["validateinvoiceline"];
		
		$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote("invoicenum")->getMboSet();
		if ($invoicelineSet->counts() > 0) {
			Set::throws(["msg" => "relatedtable"]);
		}
		else {
			Set::throws(["msg" => "200"]);
		}
	}
	function GetCustomerGLAccount($val, $print=null){
		//UNTUK JASA
		$customercode = $val->customercode;
		$paymenttype = $val->paymenttype;
		$orgcode = $val->orgcode;
		$sitecode = $val->sitecode;
		$set = new Set();
		
		$glcreditacct = null;
		$gldebitacct = null;
		
		if($paymenttype == "kredit"){
			$gldebitacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode);
		}else{
			if($paymenttype == "cashonbank"){
				$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
			}else{
				if($paymenttype == "cashonhand"){
					$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
				}
			}
		}
		$glcreditacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode);
		
		$this->glcreditacct = $glcreditacct;
		$this->gldebitacct = $gldebitacct;
		
		if($print != null){
			Set::throws(["gldebitacct" =>$gldebitacct , "glcreditacct"=>$glcreditacct]);
		}
		
		return $this;
		
	}
	
	
	public static function advpay($invoicenum=null){
		
		$invoicebo = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("invoicedate")->MboRemote("isconsignment")->MboRemote("statusdate")
		->MboRemote("description as invoicedesc")->MboRemote("refund")->MboRemote("paymenttype")->MboRemote("chequedate")->MboRemote("orgcode")->MboRemote("sitecode")
		->MboRemote("voidcommission")->MboRemote("scheddate")
		->getMboSet()->getMbo();
		
		$invoicedate = $invoicebo->getString("invoicedate");
		$statusdate = $invoicebo->getString("statusdate");
		$isconsignment = $invoicebo->getString("isconsignment");
		$invoicedesc = $invoicebo->getString("invoicedesc");
		$isrefund = $invoicebo->getString("refund");
		$paymenttype = $invoicebo->getString("paymenttype");
		$chequedate = $invoicebo->getString("chequedate");
		$orgcode = $invoicebo->getString("orgcode");
		$sitecode = $invoicebo->getString("sitecode");
		$voidcommission = $invoicebo->getString("voidcommission");
		$scheddate = $invoicebo->getString("scheddate");
		$enterdate = Xbo::setEnterDate(); 
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		$islate = InvoiceActionSet::IsLate($invoicenum, $scheddate);
		
		///CHECK CHEQUEDATE
		$flag = true;
		
		if($paymenttype == "cashongiro"){
			
			if($chequedate != null ){
				$todaydate = Xbo::setEnterDate();
				$otodaydate = strtotime($todaydate);
				$oexpdate = strtotime($chequedate);
				$odate = $oexpdate - $otodaydate;
				if ($odate < 0) {
					
					$flag = false;
					
					(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='paid'")->remove();
					
					(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("status", "hold")->change();
					
					$content = "Cheque Date has been expired. Date: $chequedate";
			
					$ivcstatus = (new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='hold' and remarks like '%Cheque sudah expired. Tertanggal:%' ")
					->getMboSet();
					$cnt = $ivcstatus->counts();
					
					if($cnt == 0){
						(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setValue("invoicenum", "$invoicenum")->setValue("changeby", "$changeby")
						->setValue("changedate", "$changedate")->setValue("status", "hold")->setValue("remarks", $content)->add();
					}
					Set::throws(["msg"=>"chequeexpired", "content"=>$content]);
					return $flag;
				}
				else {
					$flag=true;
				}
			}else{
				$flag = true;
			}
		}else{
			$flag = true;
		}
		
		
		//END CHECK
		if($flag == true){
		
			$invpaymentlineSet = (new Set())->useTransactionManager()->MboSetRemote("invpaymentline")->setRelationship("invoice", "invoice.invoicenum = invpaymentline.invoicenum")
					->setWhere("invpaymentline.invoicenum='$invoicenum'")->MboRemote("invpaymentlinenum")
					->MboRemote("invoice.vendorcode")->MboRemote("linetype")->MboRemote("invpaymentlinenum")
					->MboRemote("invpaymentline.description")->MboRemote("quantity")->MboRemote("unitcost")->MboRemote("glcreditacct")
					->MboRemote("linecost")->MboRemote("wonum")->MboRemote("isreimburse")->MboRemote("orgcode")
					->MboRemote("sitecode")->getMboSet(); 
						
			$countipl = $invpaymentlineSet->counts();
			if($countipl > 0){
				
				//Purchase something from Vendor/Supplier
				for ($i = 0; $i < $countipl; $i++) {
					$invpaymentline = $invpaymentlineSet->getMbo();
					$linetype = $invpaymentline->getString("linetype");
					$description = $invpaymentline->getString("description");
					$quantity = $invpaymentline->getString("quantity") * -1;
					$unitcost = $invpaymentline->getString("unitcost");
					$linecost = $invpaymentline->getString("linecost") * -1;
					$wonum = $invpaymentline->getString("wonum");
					$isreimburse = $invpaymentline->getString("isreimburse");
					$vendorcode = $invpaymentline->getString("vendorcode");
					$invpaymentlinenum = $invpaymentline->getString("invpaymentlinenum");
					$sitecode = $invpaymentline->getString("sitecode");
					$orgcode = $invpaymentline->getString("orgcode");
					$glcreditacct = $invpaymentline->getString("glcreditacct");
					$serviceitemnum = $invpaymentline->getString("serviceitemnum");
					
					if(empty($glcreditacct)){
						
						$glcreditacct = (new Set())->useTransactionManager()->MboSetRemote("vendor")->setWhere("vendorcode='$vendorcode'")->MboRemote("rbniacct")->getMboSet()->getMbo()->getString("rbniacct");
					}
					
					$transtype = "advpay";
					$financialperiod = Xbo::getFinancialPeriod($statusdate);
									
					if($paymenttype == "kredit"){
						$gldebitacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode);
					}else{
						if($paymenttype == "cashonbank" || $paymenttype == "cashongiro"){
							$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
							
						}else{
							if($paymenttype == "cashonhand"){
								$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
							}else{
								$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
							}
						}
					}
					(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("linetype", $linetype)->setValue("invoicelinenum", $invpaymentlinenum)
						->setValue("invoicenum", $invoicenum)->setValue("description", $description)->setValue("unitcost", $unitcost)
						->setValue("linecost", $linecost)->setValue("quantity", $quantity)->setValue("invoicedate", $invoicedate)->setValue("consignment", 'N')
						->setValue("transtype", $transtype)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
						->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("financialperiod", $financialperiod)
						->setValue("wonum", $wonum)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
						->setValue("paymenttype", $paymenttype)->setValue("serviceitemnum", $serviceitemnum)
						->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
						->add();
					
					/*
					if($linetype == "item" || $linetype == "material"){
						(new Set())->useTransactionManager()->MboSetRemote("matusetrans")->setValue("wonum", $wonum)->setValue("description", $description)->setValue("quantity", $quantity)
						->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("transtype", $transtype)->setValue("gldebitacct", $gldebitacct)
						->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)
						->setValue("changedate", $enterdate)->setValue("sitecode", $sitecode)->setValue("orgcode", $orgcode)->setValue("invoicenum", $invoicenum)->add();
					}else{
						if($linetype == "services"){
							$financialperiod = Xbo::getFinancialPeriod();
							$quantity = $quantity*-1;
							$linecost = $linecost*-1;
							(new Set())->useTransactionManager()->MboSetRemote("servrectrans")->setValue("wonum", $wonum)->setValue("description", $description)->setValue("quantity", $quantity)
							->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("transtype", $transtype)->setValue("gldebitacct", $gldebitacct)
							->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
							->setValue("sitecode", $sitecode)->setValue("orgcode", $orgcode)->setValue("invoicenum", $invoicenum)
							->setValue("financialperiod", $financialperiod)->add();
						}
					}
					
					Xbo::calculateWOCost($wonum);
					*/
				}
			}else{
				//Customers pays the debt;
				$financialperiod = Xbo::getFinancialPeriod();
				$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setRelationship("invoice", "invoice.invoicenum = invoiceline.invoicenum")
				->setWhere("invoiceline.invoicenum='$invoicenum' and invoiceline.ponum is null and invoiceline.sonum is null")
				->MboRemote("invoiceline.invoicelinenum")->MboRemote("invoiceline.customercode")->MboRemote("invoiceline.linetype")->MboRemote("invoiceline.itemnum")
				->MboRemote("invoiceline.conditiontype")->MboRemote("invoiceline.description")->MboRemote("invoiceline.quantity")->MboRemote("invoiceline.saleunit")
				->MboRemote("invoiceline.unitcost")->MboRemote("invoiceline.linecost")->MboRemote("invoiceline.unitprofit")->MboRemote("invoiceline.lineprofit")
				->MboRemote("invoiceline.tax")->MboRemote("invoiceline.taxcost")->MboRemote("invoiceline.gldebitacct")->MboRemote("invoiceline.glcreditacct")
				->MboRemote("invoiceline.warehouse")->MboRemote("invoiceline.unitpricediscount")->MboRemote("invoiceline.linepricediscount")
				->MboRemote("invoiceline.unitprofitdiscount")->MboRemote("invoiceline.lineprofitdiscount")->MboRemote("invoice.invoicedate")
				->MboRemote("invoiceline.unitcost2")->MboRemote("invoiceline.linecost2")->MboRemote("invoiceline.costdate")->MboRemote("invoiceline.salesman")
				->MboRemote("invoiceline.billingnum")->MboRemote("invoiceline.billinglinenum")->MboRemote("invoiceline.invoicelineid")->MboRemote("invoiceline.remarks")
				->MboRemote("invoiceline.costtype")->MboRemote("invoiceline.importkey")->MboRemote("invoiceline.discountprice")->MboRemote("invoiceline.transnum")
				->MboRemote("invoiceline.keylinenum")->MboRemote("invoiceline.unitcommission")->MboRemote("invoiceline.linecommission")
				->getMboSet();
				
				$cn = $invoicelineSet->counts();
				
				if($cn == 0){
					(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='paid'")->remove();
					(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("status", "hold")->change();
					Set::throws(["msg"=>"line0", "content" => "No Item and Services found to be invoiced."]);
					return false;
					
				}else{
					for($i=0; $i<$cn; $i++){
						$invoiceline = $invoicelineSet->getMbo();
						$invoicelinenum = $invoiceline->getString("invoicelinenum");
						$linetype = $invoiceline->getString("linetype");
						$itemnum = $invoiceline->getString("itemnum");
						$conditiontype = $invoiceline->getString("conditiontype");
						$description = $invoiceline->getString("description");
						$quantity = $invoiceline->getString("quantity");
						$saleunit = $invoiceline->getString("saleunit");
						$unitprice = $invoiceline->getString("unitcost");
						$lineprice = $invoiceline->getString("linecost");
						$unitcost = $invoiceline->getString("unitcost2");
						$linecost = $invoiceline->getString("linecost2");
						$unitprofit = $invoiceline->getString("unitprofit");
						$lineprofit = $invoiceline->getString("lineprofit");
						$tax = $invoiceline->getString("tax");
						$taxprice = $invoiceline->getString("taxcost");
						$gldebitacct = $invoiceline->getString("gldebitacct");
						$glcreditacct = $invoiceline->getString("glcreditacct");
						$warehouse =  $invoiceline->getString("warehouse");
						$unitpricediscount =  $invoiceline->getString("unitpricediscount");
						$linepricediscount =  $invoiceline->getString("linepricediscount");
						$unitprofitdiscount =  $invoiceline->getString("unitprofitdiscount");
						$lineprofitdiscount =  $invoiceline->getString("lineprofitdiscount");
						$invoicedate =  $invoiceline->getString("invoicedate");
						$costdate =  $invoiceline->getString("costdate");
						$salesman =  $invoiceline->getString("salesman");
						$billingnum =  $invoiceline->getString("billingnum");
						$billinglinenum =  $invoiceline->getString("billinglinenum");
						$invoicelineid =  $invoiceline->getString("invoicelineid");
						$remarks =  $invoiceline->getString("remarks");
						$customercode = $invoiceline->getString("customercode");
						$costtype = $invoiceline->getString("costtype");
						$importkey = $invoiceline->getString("importkey");
						$discountprice = $invoiceline->getString("discountprice");
						$transnum = $invoiceline->getString("transnum");
						$keylinenum = $invoiceline->getString("keylinenum");
						$unitcommission = $invoiceline->getString("unitcommission");
						$linecommission = $invoiceline->getString("linecommission");
						
						
						
						if($paymenttype == "kredit"){
							$gldebitacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode);
						}else{
							if($paymenttype == "cashonbank" || $paymenttype == "cashongiro"){
								$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
							}else{
								if($paymenttype == "cashonhand"){
									$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
								}
							}
						}
						$exchangerate = 1;
						(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("exchangerate", $exchangerate)->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)
						->setValue("customercode", $customercode)->setValue("linetype", $linetype)
						->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("description", $description)->setValue("quantity", $quantity)->setValue("transtype", "sales")
						->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("consignment", $isconsignment) 
						->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)->setValue("tax", $tax)->setValue("taxprice", $taxprice)
						->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("financialperiod", $financialperiod)
						->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)
						->setValue("warehouse", $warehouse)->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
						->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)->setValue("invoicedate", $invoicedate)
						->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("costdate", $costdate)->setValue("salesman", $salesman)
						->setValue("billingnum", $billingnum)->setValue("billinglinenum", $billinglinenum)->setValue("paymenttype", $paymenttype)
						->setValue("costtype", $costtype)->setValue("transnum", $transnum)->setValue("keylinenum", $keylinenum)
						->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)->setValue("voidcommission", $islate)
						->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
						->add();
						
						if(!empty($billingnum)){
							
							$billpaySet =(new Set())->useTransactionManager()->MboSetRemote("billingpayment")->setWhere("billingpayment.invoicelineid='$invoicelineid'")->MboRemote("billingpayment.invoicenum")->getMboSet();
							$blcount = $billpaySet->counts();
							
							$paymentlinenum = Xbo::getLineNumber("billingpayment", "paymentlinenum", "billingnum='$billingnum'");
							$paidprice = $linepricediscount+$taxprice;
							
							if ( $blcount == 0) {
								(new Set())->useTransactionManager()->MboSetRemote("billingpayment")->setValue("billingnum", $billingnum)->setValue("paymentlinenum", $paymentlinenum)->setValue("invoicenum", $invoicenum)
								->setValue("invoicelinenum", $invoicelinenum)->setValue("invoicedate", $invoicedate)->setValue("description", $invoicedesc)
								->setValue("paidprice", $paidprice)->setValue("invoicelineid", $invoicelineid)->setValue("refund", $isrefund)->setValue("enterby", $enterby)
								->setValue("customercode", $customercode)->setValue("paymentdate", $enterdate)->setValue("remarks", $remarks)->setValue("changeby", $enterby)
								->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)->setValue("discountprice", $discountprice)
								->add();
							}
							else {
								(new Set())->useTransactionManager()->MboSetRemote("billingpayment")->setWhere("invoicelineid='$invoicelineid' and billingnum='$billingnum'")->setValue("invoicenum", $invoicenum)
								->setValue("invoicelinenum", $invoicelinenum)->setValue("invoicedate", $invoicedate)->setValue("paidprice", $paidprice)
								->setValue("invoicelineid", $invoicelineid)->setValue("refund", $isrefund)->setValue("enterby", $enterby)->setValue("paymentdate", $enterdate)
								->setValue("remarks", $remarks)->setValue("changeby", $enterby)->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)
								->setValue("discountprice", $discountprice)
								->change();
							}
							
							$sumdiscSet = (new Set())->useTransactionManager()->MboSetRemote("billingpayment")->setWhere("billingnum='$billingnum'")->MboRemote("sum(discountprice) as sumdiscountprice")->getMboSet();
							$sumdisc = $sumdiscSet->getMbo();
							$sumdiscountprice = $sumdisc->getString("sumdiscountprice");
							
							$sumbilSet =(new Set())->useTransactionManager()->MboSetRemote("billingpayment")->setWhere("billingnum='$billingnum'")->MboRemote("sum(paidprice) as paidprice")->getMboSet();
							$sumbil = $sumbilSet->getMbo();
							$paidprice = $sumbil->getString("paidprice");
							
							$billingSet = (new Set())->useTransactionManager()->MboSetRemote("billing")->setWhere("billingnum='$billingnum'")->MboRemote("totalprice")->getMboSet();
							$bil = $billingSet->getMbo();
							$totalprice = $bil->getString("totalprice");
							//$unpaidprice = $paidprice - $totalprice + $sumdiscountprice;
							$unpaidprice = $paidprice - $totalprice + $sumdiscountprice;

							(new Set())->useTransactionManager()->MboSetRemote("billing")->setWhere("billingnum='$billingnum'")->setValue("paidprice", $paidprice)
							->setValue("unpaidprice", $unpaidprice)
							->setValue("discountprice", $sumdiscountprice)
							->change();
							
							InvoiceActionSet::SumBillingCost($billingnum);
						}
					}
					
					$invoicechargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum' and ponum is null and sonum is null")->getMboSet();
					$countic = $invoicechargeSet->counts();
					
					if($countic > 0){
						for($i=0; $i<$countic; $i++){
							$invoicecharge = $invoicechargeSet->getMbo();
							$invoicechargelinenum = $invoicecharge->getString("invoicechargelinenum");
							$customercode = $invoicecharge->getString("customercode");
							$description = $invoicecharge->getString("description");
							$invoicechargelinenum = $invoicecharge->getString("invoicechargelinenum");
							$quantity = $invoicecharge->getString("quantity");
							$unitprice = $invoicecharge->getString("unitcost");
							$lineprice = $invoicecharge->getString("linecost");
							$glcreditacct = $invoicecharge->getString("glcreditacct");
							$gldebitacct = $invoicecharge->getString("gldebitacct");
							$origin = $invoicecharge->getString("origin");
							$linetype = $origin;
											
											
							if($paymenttype == "kredit"){
								$gldebitacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode);
							}else{
								if($paymenttype == "cashonbank" || $paymenttype == "cashongiro"){
									$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
									
								}else{
									if($paymenttype == "cashonhand"){
										$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
									}
								}
							}
							
							(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicechargelinenum)->setValue("customercode", $customercode)
							->setValue("description", $description)->setValue("quantity", $quantity)->setValue("transtype", "sales")->setValue("linetype", $linetype)
							->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("invoicedate", $invoicedate)->setValue("consignment", $isconsignment)
							->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("financialperiod", $financialperiod)->setValue("paymenttype", $paymenttype)
							->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)
							->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
							->add();
							
						}
					}
					
					
					$invoicechargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge2")->setWhere("invoicenum='$invoicenum' and ponum is null and sonum is null")->getMboSet();
					$countic = $invoicechargeSet->counts();
					
					if($countic > 0){
						for($i=0; $i<$countic; $i++){
							$invoicecharge = $invoicechargeSet->getMbo();
							$invoicechargelinenum = $invoicecharge->getString("invoicechargelinenum");
							$customercode = $invoicecharge->getString("customercode");
							$description = $invoicecharge->getString("description");
							$invoicechargelinenum = $invoicecharge->getString("invoicechargelinenum");
							$quantity = $invoicecharge->getString("quantity");
							$unitprice = $invoicecharge->getString("unitcost");
							$lineprice = $invoicecharge->getString("linecost");
							$glcreditacct = $invoicecharge->getString("glcreditacct");
							$gldebitacct = $invoicecharge->getString("gldebitacct");
							$origin = $invoicecharge->getString("origin");
							$linetype = $origin;
											
											
							if($paymenttype == "kredit"){
								$gldebitacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode);
							}else{
								if($paymenttype == "cashonbank" || $paymenttype == "cashongiro"){
									$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
									
								}else{
									if($paymenttype == "cashonhand"){
										$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
									}
								}
							}
							
							
							if($linecost>0) $linecost = $linecost*-1;
							if($quantity>0) $quantity = $quantity*-1;
							$transtype = "purchasing";

							(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicechargelinenum)->setValue("customercode", $customercode)
							->setValue("description", $description)->setValue("quantity", $quantity)->setValue("transtype", $transtype)->setValue("linetype", $linetype)
							->setValue("unitcost", $unitprice)->setValue("linecost", $lineprice)->setValue("invoicedate", $invoicedate)->setValue("consignment", $isconsignment)
							->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("financialperiod", $financialperiod)->setValue("paymenttype", $paymenttype)
							->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)
							->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
							->add();
							
						}
					}
				}
			}
		}
	}
	public static function SumBillingCost($billingnum){
		
		$billingSet = (new Set())->useTransactionManager()->MboSetRemote("billingline")->setWhere("billingnum='$billingnum'")
		->MboRemote("sum(lineprice) as subtotalprice")
		->MboRemote("sum(linepricediscount) as subtotalpricediscount")
		->MboRemote("sum(taxcost) as totaltax")
		->MboRemote("sum(linecost) as totalcost")
		->MboRemote("sum(discountprice) as bldiscountprice")
		->getMboSet();
		
		$billing = $billingSet->getMbo();
		$subtotalprice = $billingSet->getString("subtotalprice");
		if(empty($subtotalprice)) $subtotalprice = 0;
		$subtotalpricediscount = $billingSet->getString("subtotalpricediscount");
		if(empty($subtotalpricediscount) )$subtotalpricediscount = 0;
		$totaltax = $billingSet->getString("totaltax");
		if(empty($totaltax)) $totaltax = 0;
		$totalcost = $billingSet->getString("totalcost"); //totalbiaya
		if(empty($totalcost)) $totalcost = 0;
		$totalprice = $subtotalpricediscount+$totaltax;
		$bldiscountprice = $billingSet->getString("bldiscountprice");
		if(empty($bldiscountprice)) $bldiscountprice = 0;
		
		// BILLING PAYMENT
		$billpaySet = (new Set())->useTransactionManager()->MboSetRemote("billingpayment")->setWhere("billingnum='$billingnum'")
		->MboRemote("sum(paidprice) as paidprice")
		->MboRemote("sum(discountprice) as discountprice")
		->getMboSet();
		
		$billcount = $billpaySet->counts();
		
		$billpay = $billpaySet->getMbo();
		$paidprice = $billpay->getString("paidprice");
		if(empty($paidprice)) $paidprice = 0;
		
		$discountprice = $billpay->getString("discountprice");
		if(empty($discountprice)) $discountprice = 0;
		
		if($billcount > 0){
			$subtotalprice = $subtotalprice; //sub total
			$subtotalpricediscount = $subtotalpricediscount-$discountprice; // sub total diskon
			
			//getTaxCode
			$billinglineTaxSet = (new Set())->useTransactionManager()->MboSetRemote("billingline")->setWhere("billingnum='$billingnum'")->MboRemote("tax")->getMboSet();
			$billinglineTax = $billinglineTaxSet->getMbo();
			$taxcode = $billinglineTax->getString("tax");
			
			$taxSet = (new Set())->useTransactionManager()->MboSetRemote("tax")->setWhere("taxcode='$taxcode'")->MboRemote("value")->getMboSet();
			$tax = $taxSet->getMbo();
			$taxvalue = $tax->getString("value");
			
			$totaltax2 = $subtotalpricediscount*($taxvalue/100);
			if($totaltax == 0) $totaltax2 = 0;
			$totalprice = $subtotalpricediscount+$totaltax2; // total harga
			$paidprice = $paidprice;
		}
		
		$unpaidprice = $paidprice-$totalprice;
		
		(new Set())->useTransactionManager()->MboSetRemote("billing")->setWhere("billingnum='$billingnum'")
		->setValue("paidprice", $paidprice)->setValue("unpaidprice", $unpaidprice)->setValue("totalcost", $totalcost)->setValue("subtotalprice", $subtotalprice)
		->setValue("totalprice", $totalprice)->setValue("subtotalpricediscount", $subtotalpricediscount)->setValue("totaltax", $totaltax)->setValue("totaltax2", $totaltax2)
		->setValue("bldiscountprice", $bldiscountprice)->setValue("discountprice", $discountprice)->change();
		
	}
	public static function updatepayment()

	{
		//taxprice
		$pts = explode("|", $_POST['updatepayment']);
		$table = $pts[0];
		if (isset($pts[1])) $invoicenum = $pts[1];
		else $invoicenum = null;
		if (isset($pts[2])) $keynum = $pts[2];
		else $keynum = null;
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$invoicebo = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("isconsignment")
		->MboRemote("refund")->MboRemote("invoicedate")->MboRemote("statusdate")->MboRemote("customercode")->MboRemote("vendorcode")
		->MboRemote("voidcommission")->MboRemote("scheddate")->MboRemote("orgcode")->MboRemote("sitecode")		
		->getMboSet()->getMbo();

		$isconsignment = $invoicebo->getString("isconsignment");
		$isrefund = $invoicebo->getString("refund");
		$invoicedate = $invoicebo->getString("invoicedate");
		$statusdate = $invoicebo->getString("statusdate");
		$financialperiod = Xbo::getFinancialPeriod($statusdate);
		$customercode = $invoicebo->getString("customercode");
		$vendorcode = $invoicebo->getString("vendorcode");
		$voidcommission = $invoicebo->getString("voidcommission");
		$scheddate = $invoicebo->getString("scheddate");
		$islate = InvoiceActionSet::IsLate($invoicenum, $scheddate);
		$orgcode = $invoicebo->getString("orgcode");
		$sitecode = $invoicebo->getString("sitecode");
		
		if ($isrefund == "N") {
			$refund = "N";
			if ($table == "so") {
				$transtype="sales";
			}else{
				if ($table == "po") {
					$transtype="purchasing";
				}
				if(!empty($customercode)){
					$transtype="sales";
				}
			}
		}
		if ($isrefund == "Y") {
			$refund = "Y";
			if ($table == "so") {
				$transtype="return-sales";
			}else{
				if ($table == "po") {
					$transtype="return-purchasing";
				}
				if(!empty($customercode)){
					$transtype="sales";
				}
			}
		}
		
		
		if ($table == "invoicetrans") {
			// for consignment
			$invoicelineSet =(new Set())->useTransactionManager()->MboSetRemote("invoiceline")
			->setRelationship("invoice", "invoice.invoicenum=invoiceline.invoicenum")->setWhere("invoiceline.invoicenum='$invoicenum'")
			->MboRemote("invoiceline.invoicelinenum")->MboRemote("invoiceline.linecost")->MboRemote("invoice.description")->MboRemote("invoiceline.description as invldesc")
			->MboRemote("invoiceline.invoicelineid")->MboRemote("invoiceline.taxcost")->MboRemote("invoiceline.remarks")->MboRemote("invoiceline.linetype")->MboRemote("invoiceline.itemnum")
			->MboRemote("invoiceline.quantity")->MboRemote("invoiceline.saleunit")->MboRemote("invoiceline.unitcost")->MboRemote("invoiceline.tax")->MboRemote("invoiceline.warehouse")
			->MboRemote("invoiceline.gldebitacct")->MboRemote("invoiceline.glcreditacct")->MboRemote("invoiceline.conditiontype")->MboRemote("invoiceline.unitprofit")
			->MboRemote("invoiceline.lineprofit")->MboRemote("invoiceline.consignment")->MboRemote("invoiceline.importobject")
			->MboRemote("invoiceline.customercode")->MboRemote("invoiceline.unitcost2")->MboRemote("invoiceline.linecost2")
			->MboRemote("invoiceline.unitpricediscount")->MboRemote("invoiceline.linepricediscount")->MboRemote("invoiceline.unitprofitdiscount")->MboRemote("invoiceline.lineprofitdiscount")
			->MboRemote("invoiceline.wonum")->MboRemote("invoiceline.costdate")->MboRemote("invoiceline.salesman")
			->MboRemote("invoiceline.billingnum")->MboRemote("invoiceline.billinglinenum")->MboRemote("invoiceline.costtype")->MboRemote("invoiceline.keylinenum")
			->MboRemote("invoiceline.unitcommission")->MboRemote("invoiceline.linecommission")
			->getMboSet();
			
			for ($i = 0; $i < $invoicelineSet->counts(); $i++) {
				$invoiceline = $invoicelineSet->getMbo();
				$invoicedesc = $invoiceline->getString("description");
				$invoicelinenum = $invoiceline->getString("invoicelinenum");
				$lineprice = $invoiceline->getString("linecost");
				$invoicelineid = $invoiceline->getString("invoicelineid");
				$remarks = $invoiceline->getString("remarks");
				$taxprice = $invoiceline->getString("taxcost");
				$paidprice = $taxprice + $lineprice;
				$taxprice = 0;
				$consignment = $invoiceline->getString("consignment");
				$importobject = $invoiceline->getString("importobject");
				$customercode = $invoiceline->getString("customercode");
				$unitcommission = $invoiceline->getString("unitcommission");
				$linecommission = $invoiceline->getString("linecommission");
				
				if(empty($consignment)) $consignment = 'N';
				if(!empty($sonum)) {
					$sonum = null;
					$paymentlinenum = Xbo::getLineNumber("sopayment", "paymentlinenum", "sonum='$sonum'");
				}else{
					$sonum = null;
					$paymentlinenum = $i+10;
				}
				
				$linetype = $invoiceline->getString("linetype");
				$itemnum = $invoiceline->getString("itemnum");
				$itemdesc = $invoiceline->getString("invldesc");
				$conditiontype = $invoiceline->getString("conditiontype");
				$quantity = $invoiceline->getString("quantity");
				$saleunit = $invoiceline->getString("saleunit");
				$unitprice = $invoiceline->getString("unitcost");
				$tax = $invoiceline->getString("tax");
				$warehouse = $invoiceline->getString("warehouse");
				$gldebitacct = $invoiceline->getString("gldebitacct");
				$glcreditacct = $invoiceline->getString("glcreditacct");
				$unitprofit = $invoiceline->getString("unitprofit");
				$lineprofit = $invoiceline->getString("lineprofit");
				$unitcost2 = $invoiceline->getString("unitcost2");
				$linecost2 = $invoiceline->getString("linecost2");
				$unitpricediscount = $invoiceline->getString("unitpricediscount");
				$linepricediscount = $invoiceline->getString("linepricediscount");
				$unitprofitdiscount =  $invoiceline->getString("unitprofitdiscount");
				$lineprofitdiscount =  $invoiceline->getString("lineprofitdiscount");
				$wonum =  $invoiceline->getString("wonum");
				$costdate =  $invoiceline->getString("costdate");
				$salesman = $invoiceline->getString("salesman");
				$billingnum = $invoiceline->getString("billingnum");
				$billinglinenum = $invoiceline->getString("billinglinenum");
				$costtype = $invoiceline->getString("costtype");
				$keylinenum = $invoiceline->getString("keylinenum");
				
				
				if($linecost2 >0)$linecost2 = $linecost2*-1;
				(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("sonum", $sonum)->setValue("linetype", $linetype)
				->setValue("invoicelinenum", $invoicelinenum)->setValue("itemnum", $itemnum)->setValue("invoicenum", $invoicenum)
				->setValue("description", $itemdesc)->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)
				->setValue("taxprice", $taxprice)->setValue("quantity", $quantity)->setValue("tax", $tax)
				->setValue("invoicedate", $invoicedate)->setValue("consignment", $consignment)->setValue("conditiontype", $conditiontype)
				->setValue("warehouse", $warehouse)->setValue("transtype", $transtype)->setValue("gldebitacct", $gldebitacct)
				->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("lineprofit", $lineprofit)->setValue("unitprofit", $unitprofit)->setValue("financialperiod", $financialperiod)
				->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("customercode", $customercode)
				->setValue("unitcost", $unitcost2)->setValue("linecost", $linecost2)->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
				->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
				->setValue("wonum", $wonum)->setValue("costdate", $costdate)->setValue("salesman", $salesman)
				->setValue("billingnum", $billingnum)->setValue("billinglinenum", $billinglinenum)->setValue("costtype", $costtype)
				->setValue("keylinenum", $keylinenum)
				->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)
				->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
				->add();
				
			}
			
			$invoicechargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->MboRemote("invoicechargelinenum")
			->MboRemote("ponum")->MboRemote("sonum")->MboRemote("description")->MboRemote("quantity")->MboRemote("customercode")->MboRemote("vendorcode")
			->MboRemote("unitcost")	->MboRemote("linecost")->MboRemote("glcreditacct")->MboRemote("gldebitacct")->getMboSet();
			
			$countic = $invoicechargeSet->counts();
			if($countic > 0){ 
				$invoicecharge = $invoicechargeSet->getMbo();
				$invoicechargelinenum = $invoicecharge->getString("invoicechargelinenum");
				$customercode = $invoicecharge->getString("customercode");
				$ponum = $invoicecharge->getString("ponum");
				$sonum = $invoicecharge->getString("sonum");
				$description = $invoicecharge->getString("description");
				$quantity = $invoicecharge->getString("quantity"); 
				$unitcost = $invoicecharge->getString("unitcost");
				$linecost = $invoicecharge->getString("linecost");
				$glcreditacct = $invoicecharge->getString("glcreditacct");
				$gldebitacct = $invoicecharge->getString("gldebitacct");
				
				if(!empty($ponum)){
					if(!empty($vendorcode))	$transtype = "purchasing";
					else{
						if(!empty($customercode)) $transtype == "sales";
					}
				}else{
					if(!empty($sonum)) $transtype == "sales";
					else{
						if(!empty($vendorcode))	$transtype = "purchasing";
						else{
							if(!empty($customercode)) $transtype == "sales";
						}
					}
				}
				(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("sonum", $sonum)->setValue("linetype", "tax")
				->setValue("invoicelinenum", $invoicechargelinenum)->setValue("invoicenum", $invoicenum)
				->setValue("ponum", $ponum)->setValue("description", $description)->setValue("unitprice", $unitcost)->setValue("lineprice", $linecost)
				->setValue("taxprice", 0)->setValue("taxcost", 0)->setValue("unitcost", 0)->setValue("linecost", 0)
				->setValue("quantity", $quantity)->setValue("invoicedate", $invoicedate)->setValue("consignment", $consignment)
				->setValue("warehouse", $warehouse)->setValue("transtype", "sales")->setValue("gldebitacct", $gldebitacct)
				->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("financialperiod", $financialperiod)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
				->setValue("customercode", $customercode)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
				->add();
			}
			
			$invoicechargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->MboRemote("invoicechargelinenum")
			->MboRemote("ponum")->MboRemote("sonum")->MboRemote("description")->MboRemote("quantity")->MboRemote("customercode")->MboRemote("vendorcode")
			->MboRemote("unitcost")	->MboRemote("linecost")->MboRemote("glcreditacct")->MboRemote("gldebitacct")->getMboSet();
			
			$countic = $invoicechargeSet->counts();
			if($countic > 0){ 
				$invoicecharge = $invoicechargeSet->getMbo();
				$invoicechargelinenum = $invoicecharge->getString("invoicechargelinenum");
				$customercode = $invoicecharge->getString("customercode");
				$ponum = $invoicecharge->getString("ponum");
				$sonum = $invoicecharge->getString("sonum");
				$description = $invoicecharge->getString("description");
				$quantity = $invoicecharge->getString("quantity"); 
				$unitcost = $invoicecharge->getString("unitcost");
				$linecost = $invoicecharge->getString("linecost");
				$glcreditacct = $invoicecharge->getString("glcreditacct");
				$gldebitacct = $invoicecharge->getString("gldebitacct");
				
				if(!empty($ponum)){
					if(!empty($vendorcode))	$transtype = "purchasing";
					else{
						if(!empty($customercode)) $transtype == "sales";
					}
				}else{
					if(!empty($sonum)) $transtype == "sales";
					else{
						if(!empty($vendorcode))	$transtype = "purchasing";
						else{
							if(!empty($customercode)) $transtype == "sales";
						}
					}
				}
				(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("sonum", $sonum)->setValue("linetype", "tax")
				->setValue("invoicelinenum", $invoicechargelinenum)->setValue("invoicenum", $invoicenum)
				->setValue("ponum", $ponum)->setValue("description", $description)->setValue("unitprice", $unitcost)->setValue("lineprice", $linecost)
				->setValue("taxprice", 0)->setValue("taxcost", 0)->setValue("unitcost", 0)->setValue("linecost", 0)
				->setValue("quantity", $quantity)->setValue("invoicedate", $invoicedate)->setValue("consignment", $consignment)
				->setValue("warehouse", $warehouse)->setValue("transtype", "sales")->setValue("gldebitacct", $gldebitacct)
				->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("financialperiod", $financialperiod)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
				->setValue("customercode", $customercode)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
				->add();
			}
			

			return "paid";
		}
		//SALES ORDER
		if ($table == "so") {
			$sonum = $keynum;
			$so = (new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->MboRemote("customercode")->MboRemote("discountedtotalprice")->MboRemote("discountpercentage")->MboRemote("orgcode")
			->MboRemote("sitecode")->getMboSet()->getMbo();
			$customercode = $so->getString("customercode");
			$discountedtotalprice = $so->getString("discountedtotalprice");
			$discountpercentage = $so->getString("discountpercentage");
			$orgcode = $so->getString("orgcode");
			$sitecode = $so->getString("sitecode");

			$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setRelationship("invoice", "invoice.invoicenum=invoiceline.invoicenum")
			->setWhere("invoiceline.invoicenum='$invoicenum' and invoiceline.sonum='$sonum'")->MboRemote("invoiceline.invoicelinenum")
			->MboRemote("invoiceline.linecost")->MboRemote("invoice.description")->MboRemote("invoiceline.description as invldesc")->MboRemote("invoiceline.conditiontype")
			->MboRemote("invoiceline.invoicelineid")->MboRemote("invoiceline.taxcost")->MboRemote("invoiceline.remarks")->MboRemote("invoiceline.linetype")
			->MboRemote("invoiceline.itemnum")->MboRemote("invoiceline.quantity")->MboRemote("invoiceline.saleunit")->MboRemote("invoiceline.unitcost")
			->MboRemote("invoiceline.tax")->MboRemote("invoiceline.warehouse")->MboRemote("invoiceline.gldebitacct")->MboRemote("invoiceline.glcreditacct")
			->MboRemote("invoiceline.unitpricediscount")->MboRemote("invoiceline.linepricediscount")->MboRemote("invoiceline.unitprofitdiscount")->MboRemote("invoiceline.lineprofitdiscount")
			->MboRemote("invoiceline.unitcost2")->MboRemote("invoiceline.linecost2")->MboRemote("invoiceline.unitprofit")->MboRemote("invoiceline.lineprofit")->MboRemote("invoiceline.customercode")
			->MboRemote("invoiceline.costdate")->MboRemote("invoiceline.salesman")->MboRemote("invoiceline.costtype")->MboRemote("invoice.paymenttype")
			->MboRemote("invoiceline.billingnum")->MboRemote("invoiceline.billinglinenum")->MboRemote("invoiceline.discountprice")->MboRemote("invoice.chequedate")
			->MboRemote("invoiceline.keylinenum")->MboRemote("invoiceline.unitcommission")->MboRemote("invoiceline.linecommission")
			->getMboSet();
			
			$cn = $invoicelineSet->counts();
			if($cn == 0){
				(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='paid'")->remove();
				(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("status", "hold")->change();
				Set::throws(["msg"=>"line0", "content" => "Tidak ada Item/Jasa yang harus di-invoice."]);
				return false;
			}else{
				for ($i = 0; $i < $invoicelineSet->counts(); $i++) {
					$invoiceline = $invoicelineSet->getMbo();
					$invoicedesc = $invoiceline->getString("description");
					$invoicelinenum = $invoiceline->getString("invoicelinenum");
					$customercode = $invoiceline->getString("customercode");
					$unitprice = $invoiceline->getString("unitcost");
					$lineprice = $invoiceline->getString("linecost");
					$invoicelineid = $invoiceline->getString("invoicelineid");
					$remarks = $invoiceline->getString("remarks");
					$taxprice = 0;
					
					$paymentlinenum = Xbo::getLineNumber("sopayment", "paymentlinenum", "sonum='$sonum'");
					$linetype = $invoiceline->getString("linetype");
					$itemnum = $invoiceline->getString("itemnum");
					$itemdesc = $invoiceline->getString("invldesc");
					$conditiontype = $invoiceline->getString("conditiontype");
					$quantity = $invoiceline->getString("quantity");
					$saleunit = $invoiceline->getString("saleunit");
					$tax = $invoiceline->getString("tax");
					$warehouse = $invoiceline->getString("warehouse");
					$gldebitacct = $invoiceline->getString("gldebitacct");
					$glcreditacct = $invoiceline->getString("glcreditacct");
					$unitpricediscount = $invoiceline->getString("unitpricediscount");
					$linepricediscount = $invoiceline->getString("linepricediscount");
					$unitprofitdiscount = $invoiceline->getString("unitprofitdiscount");
					$lineprofitdiscount = $invoiceline->getString("lineprofitdiscount");
					$unitcost = $invoiceline->getString("unitcost2");
					$linecost = $invoiceline->getString("linecost2");
					$financialperiod = Xbo::getFinancialPeriod($statusdate);
					$paidprice = $linepricediscount;
					$unitprofit = $invoiceline->getString("unitprofit");
					$lineprofit = $invoiceline->getString("lineprofit");
					$costdate = $invoiceline->getString("costdate");
					$salesman = $invoiceline->getString("salesman");
					$billingnum = $invoiceline->getString("billingnum");
					$billinglinenum = $invoiceline->getString("billinglinenum");
					$costtype = $invoiceline->getString("costtype");
					$invoicediscount = $invoiceline->getString("discountprice");
					$paymenttype  = $invoiceline->getString("paymenttype");
					$chequedate  = $invoiceline->getString("chequedate");
					$keylinenum  = $invoiceline->getString("keylinenum");
					$unitcommission  = $invoiceline->getString("unitcommission");
					$linecommission  = $invoiceline->getString("linecommission");
					$flag = true;
					
					if($paymenttype == "cashongiro"){
						
						if($chequedate != null ){
							$todaydate = Xbo::setEnterDate();
							$otodaydate = strtotime($todaydate);
							
							$oexpdate = strtotime($chequedate);
							$odate = $oexpdate - $otodaydate;
							if ($odate < 0) {
								
								$flag = false;
								(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='paid'")->remove();
								
								(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("status", "hold")->change();
								
								$content = "Tanggal Giro/Cheque sudah expired. Tertanggal: $chequedate";
								
								$ivcstatus = (new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='hold' and remarks like '%Cheque sudah expired. Tertanggal:%' ")
								->getMboSet();
								$cnt = $ivcstatus->counts();
								if($cnt == 0){
									(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setValue("invoicenum", "$invoicenum")->setValue("changeby", "$changeby")
									->setValue("changedate", "$changedate")->setValue("status", "hold")->setValue("remarks", $content)->add();
								}
								Set::throws(["msg"=>"chequeexpired", "content"=>$content]);
								return $flag;
							}
							else {
								$flag=true;
							}
						}else{
							$flag = true;
						}
					}else{
						$flag = true;
					}
					
					
					
					if($paymenttype == "kredit"){
						$gldebitacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode);
					}else{
						if($paymenttype == "cashonbank" || $paymenttype == "cashongiro"){
							$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
							
						}else{
							if($paymenttype == "cashonhand"){
								$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
							}
						}
					}
					
					
					$sopaymentSet = (new Set())->useTransactionManager()->MboSetRemote("sopayment")->setWhere("sopayment.invoicelineid='$invoicelineid'")->MboRemote("sopayment.invoicenum")->getMboSet();
					$scount = $sopaymentSet->counts();
					if ( $scount == 0) {
						(new Set())->useTransactionManager()->MboSetRemote("sopayment")->setValue("sonum", $sonum)->setValue("paymentlinenum", $paymentlinenum)->setValue("invoicenum", $invoicenum)
						->setValue("invoicelinenum", $invoicelinenum)->setValue("invoicedate", $invoicedate)->setValue("description", $invoicedesc)
						->setValue("paidprice", $paidprice)->setValue("invoicelineid", $invoicelineid)->setValue("refund", $isrefund)->setValue("enterby", $enterby)
						->setValue("customercode", $customercode)->setValue("paymentdate", $enterdate)->setValue("remarks", $remarks)->setValue("changeby", $enterby)
						->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)
						->setValue("invoicediscount", $invoicediscount)
						->add();
					}
					else {
						(new Set())->useTransactionManager()->MboSetRemote("sopayment")->setWhere("invoicelineid='$invoicelineid' and sonum='$sonum'")->setValue("invoicenum", $invoicenum)
						->setValue("invoicelinenum", $invoicelinenum)->setValue("invoicedate", $invoicedate)->setValue("paidprice", $paidprice)
						->setValue("invoicelineid", $invoicelineid)->setValue("refund", $isrefund)->setValue("enterby", $enterby)->setValue("paymentdate", $enterdate)
						->setValue("remarks", $remarks)->setValue("changeby", $enterby)->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)
						->setValue("invoicediscount", $invoicediscount)
						->change();
					}
					
					//Check Invoice Advanced PAYMENT
					if($unitcost != 0 || $unitcost != null){
						if($linetype == "item"){
							
							$checkwc = "sonum='$sonum' and itemnum='$itemnum' and (unitcost=0 or unitcost is null or unitcost='')";
							$checkInvoiceSOSet = (new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setWhere($checkwc)
							->MboRemote("unitpricediscount")->MboRemote("quantity")->getMboSet();
							$countCheckSO = $checkInvoiceSOSet->counts();
							if($countCheckSO>0){
								for($aa=0; $aa<$countCheckSO; $aa++){
									$checkInvoiceSO = $checkInvoiceSOSet->getMbo();
									$check_unitprice = $checkInvoiceSO->getString("unitpricediscount");
									$check_quantity = $checkInvoiceSO->getString("quantity");
									$check_linecost = $unitcost*$check_quantity;
									$check_unitprofit = $check_unitprice-$unitcost;
									$check_lineprofit = $check_unitprofit*$check_quantity;
									
									$check_unitprofitdiscount = $check_unitprofit;
									$check_lineprofitdiscount = $check_lineprofit;
									(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setWhere($checkwc)
									->setValue("unitcost", $unitcost)
									->setValue("linecost", $check_linecost)
									->setValue("unitprofit", $check_unitprofit)
									->setValue("lineprofit", $check_lineprofit)
									->setValue("unitprofitdiscount", $check_unitprofitdiscount)
									->setValue("lineprofitdiscount", $check_lineprofitdiscount)
									->change();
								}
							}
						}else{
							//services
							$qw = new Set();
							$checkwc = "sonum='$sonum' and invoicelinenum='$invoicelinenum' and (unitcost=0 or unitcost is null or unitcost='')";
							//to be defined later
						}
						
					}
					
					//end Check Advanced Payment
					
					
					(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("sonum", $sonum)->setValue("linetype", $linetype)->setValue("invoicelinenum", $invoicelinenum)
					->setValue("itemnum", $itemnum)->setValue("invoicenum", $invoicenum)->setValue("description", $itemdesc)->setValue("unitprice", $unitprice)
					->setValue("lineprice", $lineprice)->setValue("taxprice", 0)->setValue("taxcost", 0)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
					->setValue("transtype", $transtype)->setValue("quantity", $quantity)->setValue("customercode", $customercode)
					->setValue("tax", $tax)->setValue("invoicedate", $invoicedate)->setValue("consignment", 'N')->setValue("conditiontype", $conditiontype)
					->setValue("warehouse", $warehouse)->setValue("financialperiod", $financialperiod)->setValue("gldebitacct", $gldebitacct)
					->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("lineprofit", $lineprofit)
					->setValue("unitprofit", $unitprofit)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
					->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)->setValue("unitprofitdiscount", $unitprofitdiscount)
					->setValue("lineprofitdiscount", $lineprofitdiscount)->setValue("costdate", $costdate)->setValue("salesman", $salesman)
					->setValue("billingnum", $billingnum)->setValue("billinglinenum", $billinglinenum)->setValue("costtype", $costtype) 
					->setValue("keylinenum", $keylinenum)->setValue("paymenttype", $paymenttype)
					->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)->setValue("voidcommission", $islate)
					->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
					->add();
					
					if($linetype == 'item'){
						$tbname = 'matsaletrans';
					}else{
						if($linetype == "services"){
							$tbname = 'servsaletrans';
						}
					}
					(new Set())->useTransactionManager()->MboSetRemote($tbname)->setWhere("sonum='$sonum' and solinenum='$invoicelinenum'")->setValue("invoiced", "Y")->setValue("invoicenum", $invoicenum)
					->setValue("invoicelinenum", $invoicelinenum)->setValue("ispaid", "Y")->setValue("isrefund", $isrefund)->change();
				}
				
				
				$invoiceChargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")->setWhere("sonum='$sonum' and invoicenum='$invoicenum'")
				->MboRemote("description")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("gldebitacct")->MboRemote("taxcost")->MboRemote("invoicechargelinenum")
				->MboRemote("glcreditacct")->MboRemote("origin")->MboRemote("quantity")->MboRemote("taxcode")
				->getMboSet();
				
				$iccount = $invoiceChargeSet->counts();
				
				if($iccount >0 ){
					for ($i = 0; $i < $iccount; $i++) {
						$invoiceCharge = $invoiceChargeSet->getMbo();
						$invchargeDesc = $invoiceCharge->getString("description");
						$invoicechargelinenum = $invoiceCharge->getString("invoicechargelinenum");
						$unitcost = $invoiceCharge->getString("unitcost");
						$linecost = $invoiceCharge->getString("linecost");
						$paymentlinenum = Xbo::getLineNumber("sopayment", "paymentlinenum", "sonum='$sonum'");
						$gldebitacct = $invoiceCharge->getString("gldebitacct");
						$glcreditacct = $invoiceCharge->getString("glcreditacct");
						$origin = $invoiceCharge->getString("origin");
						$quantity = $invoiceCharge->getString("quantity");
						$taxcost = $invoiceCharge->getString("taxcost");
						$taxcode = $invoiceCharge->getString("taxcode");
						$financialperiod = Xbo::getFinancialPeriod($statusdate);						
						$linecostandtax = $linecost+$taxcost;
						
						(new Set())->useTransactionManager()->MboSetRemote("sopayment")->setValue("paymentlinenum", $paymentlinenum)->setValue("sonum", $sonum)->setValue("invoicenum", $invoicenum)
						->setValue("invoicedate", $invoicedate)->setValue("refund", $refund)->setValue("enterby", $enterby)->setValue("changeby", $enterby)
						->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)->setValue("paidprice", $linecostandtax)
						->setValue("description", $invchargeDesc)->setValue("paymentdate", $enterdate)->setValue("customercode", $customercode)->add();
						
						if(strpos($origin, "tax") !== false){
							$taxcost = 0;
						}
						
						(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("sonum", $sonum)->setValue("linetype", $origin)
						->setValue("invoicelinenum", $invoicechargelinenum)->setValue("invoicenum", $invoicenum)->setValue("tax", $taxcode)
						->setValue("description", $invchargeDesc)->setValue("unitprice", $unitcost)->setValue("lineprice", $linecost)
						->setValue("taxprice", $taxcost)->setValue("taxcost", 0)->setValue("unitcost", 0)->setValue("linecost", 0)
						->setValue("transtype", $transtype)->setValue("quantity", $quantity)->setValue("invoicedate", $invoicedate)
						->setValue("consignment", 'N')->setValue("financialperiod", $financialperiod)->setValue("gldebitacct", $gldebitacct)
						->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("lineprofit", 0)->setValue("unitprofit", 0)->setValue("changeby", $enterby)->setValue("customercode", $customercode)
						->setValue("changedate", $enterdate)->setValue("paymenttype", $paymenttype)
						->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
						->add();
					}
				}
				
				
				$invoiceChargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge2")->setWhere("sonum='$sonum' and invoicenum='$invoicenum'")
				->MboRemote("description")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("gldebitacct")->MboRemote("taxcost")->MboRemote("invoicechargelinenum")
				->MboRemote("glcreditacct")->MboRemote("origin")->MboRemote("quantity")->MboRemote("taxcode")
				->getMboSet();
				
				$ic2count = $invoiceChargeSet->counts();
				if($ic2count >0){
					for ($i = 0; $i < $ic2count ; $i++) {
						$invoiceCharge = $invoiceChargeSet->getMbo();
						$invchargeDesc = $invoiceCharge->getString("description");
						$invoicechargelinenum = $invoiceCharge->getString("invoicechargelinenum");
						$unitcost = $invoiceCharge->getString("unitcost");
						$linecost = $invoiceCharge->getString("linecost");
						$paymentlinenum = Xbo::getLineNumber("sopayment", "paymentlinenum", "sonum='$sonum'");
						$gldebitacct = $invoiceCharge->getString("gldebitacct");
						$glcreditacct = $invoiceCharge->getString("glcreditacct");
						$origin = $invoiceCharge->getString("origin");
						$quantity = $invoiceCharge->getString("quantity");
						$taxcost = $invoiceCharge->getString("taxcost");
						$taxcode = $invoiceCharge->getString("taxcode");
						$financialperiod = Xbo::getFinancialPeriod($statusdate);
						if(strpos($origin, "tax") !== false){
							$taxcost = 0;
						}
						
						if($linecost>0) $linecost = $linecost*-1;
						if($quantity>0) $quantity = $quantity*-1;
						$transtype = "purchasing";
						(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("sonum", $sonum)->setValue("linetype", $origin)
						->setValue("invoicelinenum", $invoicechargelinenum)->setValue("invoicenum", $invoicenum)->setValue("tax", $taxcode)
						->setValue("description", $invchargeDesc)->setValue("unitprice", 0)->setValue("lineprice", 0)
						->setValue("taxprice", $taxcost)->setValue("taxcost", 0)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
						->setValue("transtype", $transtype)->setValue("quantity", $quantity)->setValue("invoicedate", $invoicedate)
						->setValue("consignment", 'N')->setValue("financialperiod", $financialperiod)->setValue("gldebitacct", $gldebitacct)
						->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("lineprofit", 0)->setValue("unitprofit", 0)->setValue("changeby", $enterby)->setValue("customercode", $customercode)
						->setValue("changedate", $enterdate)->setValue("paymenttype", $paymenttype)
						->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
						->add();
					}
				}
				
				if ($isconsignment != "Y") {
					
					$sumpaidprice = (new Set())->useTransactionManager()->MboSetRemote("sopayment")->setWhere("sonum='$sonum'")->MboRemote("sum(paidprice) as sumpaidprice")->getMboSet()->getMbo()
					->getString("sumpaidprice");
					$unpaidprice = $discountedtotalprice - $sumpaidprice;
					if ($isrefund == "Y") $unpaidprice = $unpaidprice * -1;
					
					if ($unpaidprice == 0) {
						(new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->setValue("status", "fullpaid")->change();
						(new Set())->useTransactionManager()->MboSetRemote("sostatus")->setValue("sonum", $sonum)->setValue("status", "fullpaid")->setValue("changeby", $enterby)
						->setValue("changedate", $enterdate)->setValue("remarks", "Last Invoice: $invoicenum")->add();
					}
					else {
						(new Set())->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->setValue("status", "partialpaid")->change();
						(new Set())->useTransactionManager()->MboSetRemote("sostatus")->setValue("sonum", $sonum)->setValue("status", "partialpaid")->setValue("changeby", $enterby)
						->setValue("changedate", $enterdate)->setValue("remarks", "Last Invoice: $invoicenum")->add();
					}
				}
				
				if(!empty($sonum)){
				}else{
					InvoiceActionSet::advpay($invoicenum);
				}
				InvoiceActionSet::SumSOCost($sonum, $discountpercentage);
				return "paid";
			}
			//END SALES ORDER
		}
			
		if ($table == "po") {
			
			// PURCHASE ORDER 
			$ponum = $keynum;
			$poset = (new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("ponum='$ponum'")->MboRemote("vendor")->MboRemote("totalcost")->MboRemote("exchangerate")
			->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("budgetnum")
			->getMboSet();
			
			$po = $poset->getMbo();
			$vendorcode = $po->getString("vendor");
			$totalcost = $po->getString("totalcost");
			$exchangerate = $po->getString("exchangerate");
			$orgcode = $po->getString("orgcode");
			$sitecode = $po->getString("sitecode");
			$budgetnum = $po->getString("budgetnum");
			
			$invoiceSet = (new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("paymenttype")->MboRemote("chequedate")->getMboSet();
			$invoice = $invoiceSet->getMbo();
			$paymenttype = $invoice->getString("paymenttype");
			$chequedate = $invoice->getString("chequedate");
			$changeby = $enterby;
			$changedate = $enterdate;
			
		
			$flag = true;
			if($paymenttype == "cashongiro"){
				if($chequedate != null ){
					$todaydate = Xbo::setEnterDate();
					$otodaydate = strtotime($todaydate);
					$oexpdate = strtotime($chequedate);
					$odate = $oexpdate - $otodaydate;
					if ($odate < 0) {
						$flag = false;
						(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='paid'")->remove();
						(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("status", "hold")->change();
						
						$content = "Expired Cheque Date. Dated on: $chequedate";
						
						$ivcstatus = (new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='hold' and remarks like '%Cheque sudah expired. Tertanggal:%' ")
						->getMboSet();
						$cnt = $ivcstatus->counts();
						
						if($cnt == 0){
							(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setValue("invoicenum", "$invoicenum")->setValue("changeby", "$changeby")
							->setValue("changedate", "$changedate")->setValue("status", "hold")->setValue("remarks", $content)->add();
						}
						Set::throws(["msg"=>"chequeexpired", "content"=>$content]);
						return $flag;
					}
					else {
						$flag=true;
					}
				}else{
					$flag = true;
				}
			}else{
				if(empty($paymenttype)){
					$flag = false;
					(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='paid'")->remove();
					(new Set())->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("status", "hold")->change();
					$content = "Payment Type cannot be null.";
					(new Set())->useTransactionManager()->MboSetRemote("invoicestatus")->setWhere("invoicenum='$invoicenum' and status='hold' and remarks like '%Tipe Pembayaran Kosong:%' ")
					->getMboSet();
					$cnt = $ivcstatus->counts();
					
					if($cnt == 0){
						$ss = new Set();
						$ss->useTransactionManager()->MboSetRemote("invoicestatus")->setValue("invoicenum", "$invoicenum")->setValue("changeby", "$changeby")
						->setValue("changedate", "$changedate")->setValue("status", "hold")->setValue("remarks", $content)->add();
					}
					Set::throws(["msg"=>"chequeexpired", "content"=>$content]);
					return $flag;
				}
				$flag = true;
			}
				
			//Grab Invoice Lines
			$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setRelationship("invoice", "invoice.invoicenum=invoiceline.invoicenum")
			->setWhere("invoiceline.invoicenum='$invoicenum' and invoiceline.ponum='$ponum'")->MboRemote("invoiceline.invoicelinenum")
			->MboRemote("invoiceline.linecost")->MboRemote("invoice.description")->MboRemote("invoiceline.description as invldesc")
			->MboRemote("invoiceline.invoicelineid")->MboRemote("invoiceline.taxcost")->MboRemote("invoiceline.remarks")->MboRemote("invoiceline.linetype")
			->MboRemote("invoiceline.itemnum")->MboRemote("invoiceline.quantity")->MboRemote("invoiceline.orderunit")->MboRemote("invoiceline.unitcost")
			->MboRemote("invoiceline.conversionfactor")->MboRemote("invoiceline.tax")->MboRemote("invoiceline.warehouse")->MboRemote("invoiceline.gldebitacct")
			->MboRemote("invoiceline.unitprofit")->MboRemote("invoiceline.lineprofit")->MboRemote("invoiceline.glcreditacct")->MboRemote("invoiceline.conditiontype")
			->MboRemote("invoiceline.billingnum")->MboRemote("invoiceline.billinglinenum")
			->MboRemote("invoice.paymenttype")->MboRemote("invoice.chequedate")->MboRemote("invoice.chequenum")
			->MboRemote("invoiceline.keylinenum")
			->getMboSet();

			
			
			for ($i = 0; $i < $invoicelineSet->counts(); $i++) {
				$invoiceline = $invoicelineSet->getMbo();
				$invoicedesc = $invoiceline->getString("description");
				$invoicelinenum = $invoiceline->getString("invoicelinenum");
				$linecost = $invoiceline->getString("linecost");
				$invoicelineid = $invoiceline->getString("invoicelineid");
				$remarks = $invoiceline->getString("remarks");
				$paymentlinenum = Xbo::getLineNumber("popayment", "paymentlinenum", "ponum='$ponum'");
				$linetype = $invoiceline->getString("linetype");
				$itemnum = $invoiceline->getString("itemnum");
				$itemdesc = $invoiceline->getString("invldesc");
				$conditiontype = $invoiceline->getString("conditiontype");
				$quantity = $invoiceline->getString("quantity");
				$orderunit = $invoiceline->getString("orderunit");
				$unitcost = $invoiceline->getString("unitcost");
				
				$conversionfactor = $invoiceline->getString("conversionfactor");
				$tax = $invoiceline->getString("tax");
				$warehouse = $invoiceline->getString("warehouse");
				$unitprofit = $invoiceline->getString("unitprofit");
				$lineprofit = $invoiceline->getString("lineprofit");
				$financialperiod = Xbo::getFinancialPeriod($statusdate);
				$billingnum = $invoiceline->getString("billingnum");
				$billinglinenum = $invoiceline->getString("billinglinenum");
				$taxcost = $invoiceline->getString("taxcost");
				$paymenttype = $invoiceline->getString("paymenttype");
				$chequedate = $invoiceline->getString("chequedate");
				$gldebitacct_invoiceline = $invoiceline->getString("gldebitacct");
				$keylinenum = $invoiceline->getString("keylinenum");
				
				if($paymenttype == "kredit" || $paymenttype == "credit"){
					$glcreditacct = Xbo::getVendorAcct($vendorcode, $orgcode, $sitecode);
				}else{
					if($paymenttype == "cashonbank" || $paymenttype == "cashongiro"){
						$glcreditacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
						if($linetype == "item") $gldebitacct = Xbo::getwhgl($warehouse, "noprint");
						if($linetype == "services") {
							if(empty($gldebitacct_invoiceline)) $gldebitacct = Xbo::getVendorAPAcct($vendorcode, $orgcode, $sitecode);
							else $gldebitacct = $gldebitacct_invoiceline;
						}
					}else{
						if($paymenttype == "cashonhand"){
							$glcreditacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
							if($linetype == "item") $gldebitacct = Xbo::getwhgl($warehouse, "noprint");
							if($linetype == "services") {
								if(empty($gldebitacct_invoiceline)) $gldebitacct = Xbo::getVendorAPAcct($vendorcode, $orgcode, $sitecode);
								else $gldebitacct = $gldebitacct_invoiceline;
							}
						}
					}
				}
				
				$paidcost = $linecost;
				
				$popaymentSet = (new Set())->useTransactionManager()->MboSetRemote("popayment")->setWhere("popayment.invoicelineid='$invoicelineid'")->MboRemote("popayment.invoicenum")->getMboSet();
				
				
				$paidcostExchangeRate  = $paidcost*$exchangerate;
				
				$countpp = $popaymentSet->counts();
				if ( $countpp == 0) {
					(new Set())->useTransactionManager()->MboSetRemote("popayment")->setValue("ponum", $ponum)->setValue("paymentlinenum", $paymentlinenum)->setValue("invoicenum", $invoicenum)
					->setValue("invoicelinenum", $invoicelinenum)
					->setValue("invoicedate", $invoicedate)->setValue("description", $invoicedesc)->setValue("paidcost", $paidcost )->setValue("paidbasecost", $paidcostExchangeRate)
					->setValue("invoicelineid", $invoicelineid)->setValue("refund", $refund)->setValue("enterby", $enterby)->setValue("vendor", $vendorcode)->setValue("paymentdate", $enterdate)
					->setValue("remarks", $remarks)->setValue("changeby", $enterby)->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)->add();
				}
				else {
					(new Set())->useTransactionManager()->MboSetRemote("popayment")->setWhere("invoicelineid='$invoicelineid' and ponum='$ponum'")->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)
					->setValue("invoicedate", $invoicedate)->setValue("paidcost", $paidcost)->setValue("paidbasecost", $paidcostExchangeRate)->setValue("invoicelineid", $invoicelineid)
					->setValue("refund", $refund)->setValue("enterby", $enterby)->setValue("paymentdate", $enterdate)->setValue("remarks", $remarks)->setValue("changeby", $enterby)
					->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)->change();
				}
				
				$quantity = $quantity*-1;
				$linecost = $linecost*-1;
				$unitcostExchange = $unitcost*$exchangerate;
				$linecostExchange = $linecost*$exchangerate;
				$taxcostExchange = $taxcost*$exchangerate;
				 
				/* get budgetline from POLINE */
			
				$polineSet = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("ponum='$ponum' and polinenum='$invoicelinenum'")->MboRemote("budgetlinenum")->getMboSet();
				$poline = $polineSet->getMbo();
				$budgetlinenum = $poline->getString("budgetlinenum");
			
				(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("ponum", $ponum)->setValue("linetype", $linetype)->setValue("invoicelinenum", $invoicelinenum)->setValue("itemnum", $itemnum)
				->setValue("exchangerate", $exchangerate)->setValue("invoicenum", $invoicenum)->setValue("description", $itemdesc)->setValue("unitcost", $unitcostExchange)->setValue("linecost", $linecostExchange)
				->setValue("transtype", $transtype)->setValue("taxcost", $taxcostExchange)->setValue("taxprice", 0)->setValue("quantity", $quantity)->setValue("tax", $tax)->setValue("invoicedate", $invoicedate)
				->setValue("consignment", 'N')->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)->setValue("financialperiod", $financialperiod)
				->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("lineprofit", 0)->setValue("unitprofit", 0)->setValue("unitprice", 0)->setValue("lineprice", 0)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
				->setValue("lineprofitdiscount", 0)->setValue("unitprofitdiscount", 0)->setValue("unitpricediscount", 0)->setValue("linepricediscount", 0)
				->setValue("paymenttype", $paymenttype)->setValue("keylinenum", $keylinenum)
				->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("budgetnum", $budgetnum)->setValue("budgetlinenum", $budgetlinenum)
				->add();
				
			}
			
			//Grab Invoicecharge
			$invoiceChargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")
			->setRelationship("po", "po.ponum = invoicecharge.ponum")
			->setWhere("invoicecharge.ponum='$ponum' and invoicecharge.invoicenum='$invoicenum'")
			->MboRemote("invoicecharge.description")->MboRemote("invoicecharge.unitcost")->MboRemote("invoicecharge.linecost")
			->MboRemote("invoicecharge.invoicechargelinenum")
			->MboRemote("invoicecharge.origin")
			->MboRemote("invoicecharge.quantity")->MboRemote("po.exchangerate")
			->MboRemote("invoicecharge.budgetnum")->MboRemote("invoicecharge.budgetlinenum")
			->getMboSet();
			
			for ($i = 0; $i < $invoiceChargeSet->counts(); $i++) {
				$invoiceCharge = $invoiceChargeSet->getMbo();
				$invchargeDesc = $invoiceCharge->getString("description");
				$invoicechargelinenum = $invoiceCharge->getString("invoicechargelinenum");
				$unitcost = $invoiceCharge->getString("unitcost");
				$linecost = $invoiceCharge->getString("linecost");
				$paymentlinenum = Xbo::getLineNumber("popayment", "paymentlinenum", "ponum='$ponum'");
				$origin = $invoiceCharge->getString("origin");
				$quantity = $invoiceCharge->getString("quantity");
				$exchangerate = $invoiceCharge->getString("exchangerate");
				$financialperiod = Xbo::getFinancialPeriod($statusdate);
				$tax_budgetnum =  $invoiceCharge->getString("budgetnum");
				$tax_budgetlinenum =  $invoiceCharge->getString("budgetlinenum");
				$paidbasecost = $linecost*$exchangerate;
				(new Set())->useTransactionManager()->MboSetRemote("popayment")->setValue("paymentlinenum", $paymentlinenum)->setValue("ponum", $ponum)->setValue("invoicenum", $invoicenum)
				->setValue("invoicedate", $invoicedate)->setValue("refund", $refund)->setValue("enterby", $enterby)->setValue("changeby", $enterby)
				->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)->setValue("paidcost", $linecost)->setValue("paidbasecost", $paidbasecost)
				->setValue("description", $invchargeDesc)->setValue("paymentdate", $enterdate)->add();
				
				if($quantity > 0) $quantity = $quantity*-1;
				if($linecost > 0) $linecost = $linecost*-1;
				
				$linecost = $linecost*$exchangerate;
				$unitcost = $unitcost*$exchangerate;
				
				(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("ponum", $ponum)->setValue("linetype", $origin)
				->setValue("invoicelinenum", $invoicechargelinenum)->setValue("invoicenum", $invoicenum)->setValue("exchangerate", $exchangerate)
				->setValue("description", $invchargeDesc)->setValue("unitprice", 0)->setValue("lineprice", 0)
				->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("taxprice", 0)->setValue("taxcost", 0)
				->setValue("transtype", $transtype)->setValue("quantity", $quantity)->setValue("invoicedate", $invoicedate)
				->setValue("consignment", 'N')->setValue("financialperiod", $financialperiod)->setValue("gldebitacct", $gldebitacct)
				->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("lineprofit", 0)->setValue("unitprofit", 0)->setValue("changeby", $enterby)->setValue("paymenttype", $paymenttype)
				->setValue("changedate", $enterdate)
				->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
				->setValue("budgetnum", $tax_budgetnum)->setValue("budgetlinenum", $tax_budgetlinenum)
				->add();
			}
			
			
			//Grab Invoicecharge2
			$invoiceChargeSet = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge2")
			->setRelationship("po", "po.ponum = invoicecharge2.ponum")
			->setWhere("invoicecharge2.ponum='$ponum' and invoicecharge2.invoicenum='$invoicenum'")
			->MboRemote("invoicecharge2.description")->MboRemote("invoicecharge2.unitcost")->MboRemote("invoicecharge2.linecost")
			->MboRemote("invoicecharge2.invoicechargelinenum")
			->MboRemote("invoicecharge2.origin")
			->MboRemote("invoicecharge2.quantity")->MboRemote("po.exchangerate")
			->MboRemote("invoicecharge2.budgetnum")->MboRemote("invoicecharge2.budgetlinenum")
			->getMboSet();
			
			for ($i = 0; $i < $invoiceChargeSet->counts(); $i++) {
				$invoiceCharge = $invoiceChargeSet->getMbo();
				$invchargeDesc = $invoiceCharge->getString("description");
				$invoicechargelinenum = $invoiceCharge->getString("invoicechargelinenum");
				$unitcost = $invoiceCharge->getString("unitcost");
				$linecost = $invoiceCharge->getString("linecost");
				$paymentlinenum = Xbo::getLineNumber("popayment", "paymentlinenum", "ponum='$ponum'");
				$origin = $invoiceCharge->getString("origin");
				$quantity = $invoiceCharge->getString("quantity");
				$exchangerate = $invoiceCharge->getString("exchangerate");
				$tax_budgetnum = $invoiceCharge->getString("budgetnum");
				$tax_budgetlinenum = $invoiceCharge->getString("budgetlinenum");
				$financialperiod = Xbo::getFinancialPeriod($statusdate);
				$paidbasecost = $linecost*$exchangerate;
			
				if($linecost<0) $linecost = $linecost*-1;
				if($quantity<0) $quantity = $quantity*-1;
				$transtype = "sales";
				$linecost = $linecost*$exchangerate;
				$unitcost = $unitcost*$exchangerate;
				
				(new Set())->useTransactionManager()->MboSetRemote("invoicetrans")->setValue("ponum", $ponum)->setValue("linetype", $origin)
				->setValue("invoicelinenum", $invoicechargelinenum)->setValue("invoicenum", $invoicenum)->setValue("exchangerate", $exchangerate)
				->setValue("description", $invchargeDesc)->setValue("unitprice", $unitcost)->setValue("lineprice", $linecost)
				->setValue("unitcost", 0)->setValue("linecost", 0)->setValue("taxprice", 0)->setValue("taxcost", 0)
				->setValue("transtype", $transtype)->setValue("quantity", $quantity)->setValue("invoicedate", $invoicedate)
				->setValue("consignment", 'N')->setValue("financialperiod", $financialperiod)->setValue("gldebitacct", $gldebitacct)
				->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("lineprofit", 0)->setValue("unitprofit", 0)->setValue("changeby", $enterby)->setValue("paymenttype", $paymenttype)
				->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
				->setValue("budgetnum", $tax_budgetnum)->setValue("budgetlinenum", $tax_budgetlinenum)
				->add();
			}
			
			
			if(!empty($ponum)){
			}else{
				InvoiceActionSet::advpay($invoicenum);
			}
			InvoiceActionSet::SumPOCost($ponum);
			return "paid";
		}
		if ($table == "invpaymentline") {
			
			InvoiceActionSet::advpay($invoicenum);
			return "paid";
		}

		
	}
	
	public static function IsConsignmentRecord()
	{
		$pts = explode("|", $_GET["isconsignrow"]);
		$childtable = $pts[0];
		$childtableid = $pts[2];
		$set = new Set();
		$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicelineid='$childtableid'")->MboRemote("invoicenum")->MboRemote("invoicelinenum")->getMboSet();
		$invoiceline = $invoicelineSet->getMbo();
		$invoicenum = $invoiceline->getString("invoicenum");
		$invoicelinenum = $invoiceline->getString("invoicelinenum");
		$set2 = new Set();
		$matsaletransSet = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("invoicenum='$invoicenum' and invoicelinenum='$invoicelinenum'")->MboRemote("invoicenum")->getMboSet();
		if ($matsaletransSet->counts() > 0) {
			Super::sendJsonResponse("success", "ro");
		}
	}
	

} 

Class InvoiceValidateTables {	
	
	public static function invoice_tbcx(){
		
		$payload = Super::getParam("invoice_tbcx");
		$dataPackage = $payload["dataPackage"];

		$childTable = $dataPackage["datasetChild"];
		$deleteBy = $dataPackage["ownership"]["userid"];
		$parent = $dataPackage["ownership"]["xboset"];
		$parentid = $dataPackage["ownership"]["xbosetid"];
		$selectedRows =  $dataPackage["selectedRows"];

		if ($childTable == "invoiceline") {
			
			foreach($selectedRows as $row){
				$invoicelineid = $row["invoicelineid"];
				$invoiceline = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")
				->where("invoicelineid=?" , $invoicelineid)
				->getMboSet();

				$invoice = $invoiceline->getMbo();
				$invoicenum = $invoice->getString("invoicenum");
				$invoicelinenum = $invoice->getString("invoicelinenum");
				$linetype = $invoice->getString("linetype");
				$ponum =  $invoice->getString("ponum");
				$sonum =  $invoice->getString("sonum");

				//check status 
				$invoice = (new Set())->useTransactionManager()->MboSetRemote("invoice")->where("invoicenum=?", $invoicenum)->getMboSet()->getMbo();
				$status = $invoice->getString("status");

				if($status == "paid" || $status == "canceled" || $status == "approved" || $status == "hold"){
					Super::sendJsonResponse("error", "Cannot Delete Invoice Line due to status : $status");
				}
				
				if($status == "new" ){
					if(!empty($sonum)){
						(new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("sonum='$sonum' and solinenum='$invoicelinenum'")->setValue("invoiced", "N")->setValue("invoicenum", null)
						->setValue("invoicelinenum", null)->setValue("ispaid", "N")->setValue("isrefund", "N")->change();
					}

					$_POST["delChildPackage"] = $dataPackage;
					$isDeleted = Super::deleteChildren(true);
					$isDeleted = true;
					if($isDeleted == true){
						// Sum Tax Cost
						$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->where("invoicenum=?", $invoicenum)
						->MboRemote("sum(taxcost) as sumTaxCost")
						->getMboSet();
						$invoiceline = $invoicelineSet->getMbo();
						$sumTaxCost = $invoiceline->getString("sumTaxCost");

						//update Tax cost baru
						//check dulu apakah ada record invoice charge
						$ichargeSet = (new Set())->MboSetRemote("invoicecharge")->where("invoicenum=?", $invoicenum)->getMboSet();
						//kalau ada update!
						if(!$ichargeSet->isEmpty()){
							(new Set())->MboSetRemote("invoicecharge")->where("invoicenum=?", $invoicenum)
							->setValue("unitcost", $sumTaxCost)
							->setValue("linecost", $sumTaxCost)
							->change();
							
						}

						//check berapa jumlah record invoice line saat ini
						$count_records = (new Set())->useTransactionManager()
                           ->MboSetRemote("invoiceline")
                           ->where("invoicenum = ?", $invoicenum)
                           ->countRecords();
						if($count_records == 0){
							(new Set())->MboSetRemote("invoicecharge")->where("invoicenum=?", $invoicenum)->remove();
							//kembalikan termin ke 100%
							(new Set())->useTransactionManager()->MboSetRemote("invoice")->where("invoicenum=?", $invoicenum)->setValue("paypercent",100)->change();
						} 
						Super::sendJsonResponse("success", "Data has been deleted and Invoice Charge has been re-calculated.");
					}
					
				}
			}
		
		}
		if ($childTable== "invoicecharge") {
			foreach($selectedRows as $row){
				$invoicechargeid = $row["invoicechargeid"];
				$invoicecharge = (new Set())->useTransactionManager()->MboSetRemote("invoicecharge")
				->where("invoicechargeid=?" , $invoicechargeid)
				->getMboSet()->getMbo();
				$invoicenum = $invoicecharge->getString("invoicenum");

				//check apakah ada data invoiceline? 
				$invoicelineSet = (new Set())->MboSetRemote("invoiceline")->where("invoicenum = ?", $invoicenum)->getMboSet();

				if($invoicelineSet->isEmpty()){
					$_POST["delChildPackage"] = $dataPackage;
					Super::deleteChildren();
				}
				Super::sendJsonResponse("error", "Cannot Delete Invoice Charge Records");
			}
		}
		if($childTable == "invpaymentline"){
			foreach($selectedRows as $row){
				$invpaymentlineid = $row["invpaymentlineid"];
				$invpay = (new Set())->MboSetRemote("invpaymentline")->where("invpaymentlineid =?", $invpaymentlineid)->getMboSet();
				$invoicenum = $invpay->getString("invoicenum");
				$wonum = $invpay->getString("wonum");
				$serviceitemnum = $invpay->getString("serviceitemnum");

				//Check apakah sudah ada di invoicetrans 
				$invoicetransSet = (new Set())->MboSetRemote("invoicetrans")->where("invoicenum=? AND wonum=? AND serviceitemnum=?", $invoicenum, $wonum, $serviceitemnum)->getMboSet();
				if($invoicetransSet->isEmpty()){
					$_POST["delChildPackage"] = $dataPackage;
					Super::deleteChildren();
				}else{
					Super::sendJsonResponse("error", "Cannot Delete Invoice Payment Line Records because it is linked to the invoice transactions.");
				}
			}
		}
		
		if($childTable == "invoicelineview"){
			Super::sendJsonResponse("error", "Cannot Delete Invoice Line View");
		}
		if($childTable == "invoicebilling"){
			Super::sendJsonResponse("error", "Cannot Delete Invoice Billing Records");
		}
		
	}
}
	

?>