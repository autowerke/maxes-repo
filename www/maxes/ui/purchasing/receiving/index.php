<!DOCTYPE html>
<html>
<title>
</title>
<head>
	<?php
	include ("../../../applications/ui/ui.php");
	?>
	
</head>

<body id="page-top" xboset="po" data-appkeynum = "ponum">
	 <div id="wrapper">
        <div id='sidebarmenu'></div>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="eat-toolbars"></div>
                <div class="container-fluid">
		<div class="maxes01">
			<ul class="maxes02">
				<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
				<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Material Receiving</a></li>
				<li class="maxes02_2"><a href="#maxes02_1_3">Services Receiving</a></li>
			</ul>
			<div class="maxes04 shadow">
				<div id="maxes02_1_1" class="tab maxes02_1">
					 <div id="maxes02_1_1" class="tab maxes02_1">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="page-header-list">
                                            <div class="tablelist">
                                                <table id="mxtable_po" class="display"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>	
				</div>
				<div id="maxes02_1_2" class="maxes03">
					<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
						<div class='eat_apptitle'></div>
					</div>
					
					<div class="row eatfix">
					<div class="col-md-6">		
					<div class="page-header">
						<div class="col-md-6">
							<table class="tbldata">
							<tr>
								<td>Purchase Order
								</td>
								<td><input type="text" data-attribute="ponum" data-input="readonly" data-key="value">
								</td>
							</tr>
							<tr>
								<td>Description
								</td>
								<td><input type="text" data-attribute="description" data-input="readonly">
								</td>
							</tr>
						</table>
						</div>
					</div>
					</div>
					</div>
					
					<div class="row eatfix">  
					<div class="col-md-12">
						<table width=100%>
							<tr>
								<td class="maxes_sct_tbl">Nilai PO
								</td>
							</tr>
						</table>
					</div>
					
					
					</div>
					<div class="row eatfix">
						<div class="col-md-4">
						<table class="tbldata">
							<tr>
								<td>Currency</td>
								<td><input type="text" data-attribute="currencycode" data-lookup="currency"  data-input="readonly"></td>
							</tr>
							<tr>
								<td>Exchange Rates</td>
								<td><input type="text" data-type="decimal" data-attribute="exchangerate"  data-input="readonly"></td>
							</tr>
							<tr>
								<td>Total before Tax</td>
								<td><input type="text" data-type="decimal" data-attribute="pretaxtotal"  data-input="readonly"></td>
							</tr>
							
						</table>
						</div>
						<div class="col-md-4">
							<table class="tbldata">
								<tr>
									<td>Total Tax</td>
									<td><input type="text" data-type="decimal" data-attribute="totaltax"  data-input="readonly"></td>
								</tr>
								<tr>
									<td>Total Cost</td>
									<td><input type="text" data-type="decimal" data-attribute="totalcost"  data-input="readonly"></td>
								</tr>
								<tr>
									<td>Total Base Cost</td>
									<td><input type="text" data-type="decimal" data-attribute="totalbasecost"  data-input="readonly"></td>
								</tr>
								
								
							</table>	
						</div>
						<div class="col-md-4">
							<table class="tbldata">
								<tr>
									<td>Unpaid </td>
									<td><input type="text" data-type="decimal" data-attribute="unpaidcost"  data-input="readonly"></td>
								</tr>
								<tr>
									<td>Paid </td>
									<td><input type="text" data-type="decimal" data-attribute="paidcost" data-input="readonly"></td>
								</tr>
							</table>
						</div>
					</div>	
					<div class="row eatfix">  
					<div class="col-md-12">
						<table width=100%>
							<tr>
								<td class="maxes_sct_tbl">Receiving Detail Barang
								</td>
							</tr>
						</table>
					</div>
					
					
					
					</div>
					
					<div class="row eatfix">  
					
					<div class="col-md-4">
						<table class="tbldata">
							<tr>
								<td>PO Status
								</td>
								<td><input type="text" data-attribute="status" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Receipt Status
								</td>
								<td><input type="text" data-attribute="receipt" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Company
								</td>
								<td><input type="text" data-attribute="orgcode" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Site
								</td>
								<td><input type="text" data-attribute="sitecode" data-input="readonly">
								</td>
							</tr>
						</table>
					</div>
					
					</div>
					
					
					
					
					<div class="row eatfix">  
					<div class="col-md-2">
						<div class="addrow" id="selectordereditems"></div>
					</div>
					<div class="col-md-2">
						<div class="addrow" id="selectitemtoreturn"></div>
					</div>
					
					
					
					</div>
					<div class="row eatfix">
					<div class="col-md-12">  
					
					<div class="page-header-list">
						<div class="tablelist">
							<table id="mxtable_matrectrans_child" class="display" data-child="matrectrans"></table>
							<div id="mxtable_matrectrans_child_detail" class="mxtable_detail">
								<div class="row eatfix">
									<div class="col-md-4">
										<table class="tableDetailRow">
											<tr>
												<td>Num.</td>
												<td><input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly" data-childrentable="poline" data-childrencolumn="polinenum,ponum"></td>
											</tr>
											<tr>
												<td>Item</td>
												<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly" data-childrentable="item" data-childrencolumn="itemnum">
												</td>
											</tr>
											<tr>
												<td>Description</td>
												<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Tgl Diterima Actual</td>
												<td><input type="text" data-childfld="child" data-attribute="actualdate" data-type="date"></td>
											</tr>
											<tr>
												<td>Tgl Expired Date</td>
												<td><input type="text" data-childfld="child" data-attribute="expdate" data-type="date"></td>
											</tr>
											
											<tr>
												<td>Debit Account Code</td>
												<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Credit Account Code</td>
												<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="readonly"></td>
											</tr>
											
											<tr>
												<td>FIFO/LIFO Date</td>
												<td><input type="text" data-childfld="child" data-attribute="costdate" data-input="readonly" data-type="date"></td>
											</tr>
											<tr>
												<td>Batch Num.</td>
												<td><input type="text" data-childfld="child" data-attribute="batchnum" data-input="readonly"></td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tableDetailRow">
											<tr>
												<td>Received Quantity</td>
												<td><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Order Unit </td>
												<td><input type="text" data-childfld="child" data-attribute="polinenum.orderunit" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Issue Unit</td>
												<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Discount (%)</td>
												<td><input type="text" data-childfld="child" data-attribute="discountpercentage" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Discount</td>
												<td><input type="text" data-childfld="child" data-attribute="discountprice" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Unit Cost</td>
												<td><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Total Cost</td>
												<td><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Warehouse</td>
												<td><input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly" ></td>
											</tr>
											<tr>
												<td>Rack</td>
												<td><input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly" ></td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tableDetailRow">
											<tr>
												<td>Remarks</td>
												<td><input type="text" data-childfld="child" data-attribute="remarks"></td>
											</tr>
											<tr>
												<td>Notice of Deliverable</td>
												<td><input type="text" data-childfld="child" data-attribute="receiptnotes"></td>
											</tr>
											<tr>
												<td>Shipping Date</td>
												<td><input type="text" data-childfld="child" data-attribute="shipdate" data-type="date"></td>
											</tr>
											<tr>
												<td>Shipped By</td>
												<td><input type="text" data-childfld="child" data-attribute="shipby" data-lookup="employees"></td>
											</tr>
											<tr>
												<td>Entered Date</td>
												<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Entered By</td>
												<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
											</tr>
										</table>
									</div>
								</div>
								<!-- DO NOT DELETE THIS HIDDEN INPUT, This input is to differentiate the record ID in matrectrans table-->
								<input type='hidden' data-childfld='child' data-attribute='matrectransid' data-input='readonly'>
							</div>
						</div>
					</div>
					</div>
					
					</div>
					
				</div>
				<div id="maxes02_1_3" class="maxes03">
				
				<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
						<div class='eat_apptitle'></div>
					</div>
					<div class="row eatfix">
					<div class="col-md-6">		
					<div class="page-header">
						<div class="col-md-6">
							<table class="tbldata">
							<tr>
								<td>Purchase Order
								</td>
								<td><input type="text" data-attribute="ponum" data-input="readonly" data-key="value">
								</td>
							</tr>
							<tr>
								<td>Description
								</td>
								<td><input type="text" data-attribute="description" data-input="readonly">
								</td>
							</tr>
						</table>
						</div>
					</div>
					</div>
					</div>
					<div class="row eatfix">  
					
					<div class="col-md-12">
						<table width=100%>
							<tr>
								<td class="maxes_sct_tbl">Nilai PO
								</td>
							</tr>
						</table>
					</div>
					
					</div>
					<div class="row eatfix">
						<div class="col-md-4">
						<table class="tbldata">
							<tr>
								<td>Currency</td>
								<td><input type="text" data-attribute="currencycode" data-lookup="currency"  data-input="readonly"></td>
							</tr>
							<tr>
								<td>Exchange Rates</td>
								<td><input type="text" data-type="decimal" data-attribute="exchangerate"  data-input="readonly"></td>
							</tr>
							<tr>
								<td>Total before Tax</td>
								<td><input type="text" data-type="decimal" data-attribute="pretaxtotal"  data-input="readonly"></td>
							</tr>
							
						</table>
						</div>
						<div class="col-md-4">
							<table class="tbldata">
								<tr>
									<td>Total Tax</td>
									<td><input type="text" data-type="decimal" data-attribute="totaltax"  data-input="readonly"></td>
								</tr>
								<tr>
									<td>Total Cost</td>
									<td><input type="text" data-type="decimal" data-attribute="totalcost"  data-input="readonly"></td>
								</tr>
								<tr>
									<td>Total Base Cost</td>
									<td><input type="text" data-type="decimal" data-attribute="totalbasecost"  data-input="readonly"></td>
								</tr>
								
								
							</table>	
						</div>
						<div class="col-md-4">
							<table class="tbldata">
								<tr>
									<td>Unpaid </td>
									<td><input type="text" data-type="decimal" data-attribute="unpaidcost"  data-input="readonly"></td>
								</tr>
								<tr>
									<td>Paid </td>
									<td><input type="text" data-type="decimal" data-attribute="paidcost" data-input="readonly"></td>
								</tr>
							</table>
						</div>
					</div>	
					<div class="row eatfix">  
					<div class="col-md-12">
						<table width=100%>
							<tr>
								<td class="maxes_sct_tbl">Receiving Detail Jasa
								</td>
							</tr>
						</table>
					</div>
					
					
					</div>
					
					<div class="row eatfix">  
					
					<div class="col-md-4">
						<table class="tbldata">
							<tr>
								<td>Status PO
								</td>
								<td><input type="text" data-attribute="status" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Receipt Status
								</td>
								<td><input type="text" data-attribute="receipt" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Company
								</td>
								<td><input type="text" data-attribute="orgcode" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Site
								</td>
								<td><input type="text" data-attribute="sitecode" data-input="readonly">
								</td>
							</tr>
						</table>
					</div>
					
					</div>
					
					<div class="row eatfix">  
					<div class="col-md-2">
						<div class="addrow" id="selectorderedservices"></div>
					</div>
					<div class="col-md-2">
						<div class="addrow" id="selectservicetoreturn"></div>
					</div>
					
					</div>
					
					
					
					<div class="row eatfix">
					<div class="col-md-12">  
					
					<div class="page-header-list">
						<div class="tablelist">
							<table id="mxtable_servrectrans_child" class="display" data-child="servrectrans"></table>
							<div id="mxtable_servrectrans_child_detail" class="mxtable_detail">
								<div class="row eatfix">
									<div class="col-md-4">
										<table class="tableDetailRow">
											<tr>
												<td>Num.</td>
												<td><input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly" data-childrentable="poline" data-childrencolumn="polinenum"></td>
											</tr>

											<tr>
												<td>Description</td>
												<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Actual Receipt Date</td>
												<td><input type="text" data-childfld="child" data-attribute="actualdate" data-type="date"></td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tableDetailRow">
											<tr>
												<td>Receipt Qty.</td>
												<td><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Order Unit </td>
												<td><input type="text" data-childfld="child" data-attribute="polinenum.orderunit" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Discount (%)</td>
												<td><input type="text" data-childfld="child" data-attribute="discountpercentage" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Discount</td>
												<td><input type="text" data-childfld="child" data-attribute="discountprice" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Unit Cost</td>
												<td><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Total Cost</td>
												<td><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal"></td>
											</tr>
											<tr>
												<td>Debit Account Code</td>
												<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Credit Account Code</td>
												<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="readonly"></td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tableDetailRow">
											<tr>
												<td>Remarks</td>
												<td><input type="text" data-childfld="child" data-attribute="remarks"></td>
											</tr>
											<tr>
												<td>Line Num. Bukti Services Receiving</td>
												<td><input type="text" data-childfld="child" data-attribute="receiptnotes"></td>
											</tr>
											<tr>
												<td>Entered Date</td>
												<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Entered By</td>
												<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
											</tr>
										</table>
									</div>
								</div>
								<!-- DO NOT DELETE THIS HIDDEN INPUT, This input is to differentiate the record ID in servrectrans table-->
								<input type='hidden' data-childfld='child' data-attribute='servrectransid'>
							</div>

						</div>
					</div>
					
					</div>
					</div>
					
				</div>
			</div>
		</div>
	</div></div>
		</div>
	</div>
</body>

</html>