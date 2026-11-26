<!DOCTYPE html>
<html>
<title> </title>

<head>
	<?php
			include ("../../../applications/ui/ui.php");
			?>
</head>

<body id="page-top" xboset="po" data-appkeynum="ponum">
	<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">PO</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_3">Order Detail</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_4">Shipping and Billing</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_5">Terms and Conditions</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_6">Paid</a></li>
						</ul>
						<div class="maxes04 shadow">
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
							<div id="maxes02_1_2" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-3">
										<div class="page-header">
											<table class="tbldata">
												<tr>
													<td>PO</td>
													<td>
														<input type="text" data-attribute="ponum" data-type="upper" data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description</td>
													<td>
														<input type="text" data-attribute="description" style="width:250px;">
													</td>
												</tr>
												<tr>
													<td>PO Type</td>
													<td>
														<select name="potype" data-attribute="potype" style="width:250px;">
															<option value="">-- Select PO Type --</option>
															<option value="SPBJ">Surat Pemesanan Barang dan Jasa (SPBJ)</option>
															<option value="SPBL">Surat Pemesanan Barang Langsung (SPBL)</option>
															<option value="KHS">Kontrak Harga Satuan (KHS)</option>
															<option value="SPK">Surat Perintah Kerja (SPK)</option>
															<option value="PJ">Perjanjian (PJ)</option>
															<option value="OVERHEAD">Overhead</option>
														</select>
													</td>
												</tr>

											</table>
										</div>
										<div class="row eatfix">
											<div class="general-button2" id="sendwado">
												<img src="https://cdn.freebiesupply.com/images/large/2x/whatsapp-logo-transparent.png" width="20" height="20">
												Send DO from Whatsapp
											</div>
										</div>
									</div>
								
									<!-- Kolom 4 : Cost Summary -->
									<div class="col-md-9">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Cost Summary</td>
											</tr>
										</table>
										<div class="row eatfix">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Currency </td>
														<td>
															<input type="text" data-attribute="currencycode" data-input="required" data-lookup="currency" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Exchange Rates </td>
														<td>
															<input type="text" data-type="decimal" data-attribute="exchangerate">
														</td>
													</tr>
													<tr>
														<td>Total before Tax </td>
														<td>
															<input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotal">
														</td>
													</tr>
													<tr>
														<td>Total Tax before Discount </td>
														<td>
															<input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax">
														</td>
													</tr>
													<tr>
														<td>Discount(%) </td>
														<td>
															<input type="text" data-type="decimal" data-attribute="discountpercentage">
														</td>
													</tr>
													<tr>
														<td>Discount </td>
														<td>
															<input type="text" data-type="decimal" data-attribute="discountprice">
														</td>
													</tr>
													
												</table>
											</div>
											<div class="col-md-6">
												<table class="tbldata">
													
													<tr>
														<td>Total after Discount </td>
														<td>
															<input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotaldiscount">
														</td>
													</tr>
													<tr>
														<td>Total Tax after Discount </td>
														<td>
															<input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax2">
														</td>
													</tr>
													<tr>
														<td>Total </td>
														<td>
															<input type="text" data-type="decimal" data-input="readonly" data-attribute="totalcost">
														</td>
													</tr>
													<tr>
														<td>Total Base Cost </td>
														<td>
															<input type="text" data-type="decimal" data-input="readonly" data-attribute="totalbasecost">
														</td>
													</tr>
													<tr>
														<td>Paid </td>
														<td>
															<input type="text" data-type="decimal" data-input="readonly" data-attribute="paidcost">
														</td>
													</tr>
													<tr>
														<td>Unpaid </td>
														<td>
															<input type="text" data-type="decimal" data-input="readonly" data-attribute="unpaidcost">
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
												<td class="maxes_sct_tbl5">Detail</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">

									<!-- Kolom 1 : Buyer Information -->
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Buyer Information</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Buyer </td>
												<td>
													<input type="text" data-attribute="buyer" data-lookup="employees" data-childrentable="employees" data-childrencolumn='personid'>
												</td>
											</tr>
											<tr>
												<td>Full Name </td>
												<td>
													<input type="text" data-attribute="buyer.fullname" data-childfld="child" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Priority </td>
												<td>
													<input type="text" data-attribute="priority" data-lookup="popriority">
												</td>
											</tr>
											<tr>
												<td>Tax ID </td>
												<td>
													<input type="text" data-attribute="npwp">
												</td>
											</tr>
											<tr>
												<td>Receipt Status </td>
												<td>
													<input type="text" data-attribute="receipt" data-input="readonly">
												</td>
											</tr>
										</table>
									</div>

									<!-- Kolom 2 : Budget & Remarks -->
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Budget & Remarks</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Budget Group</td>
												<td>
													<input type="text" data-attribute="budgetnum" data-lookup="budget">
												</td>
											</tr>
											<tr>
												<td>Budget Span (Year)</td>
												<td>
													<input type="text" data-attribute="budgetspan" data-type="decimal">
												</td>
											</tr>
											<tr>
												<td>Contract</td>
												<td>
													<input type="text" data-attribute="contractnum" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Remarks </td>
												<td>
													<input type="text" data-attribute="remarks">
												</td>
											</tr>
										</table>
									</div>

									<!-- Kolom 3 : Schedule & Status -->
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Schedule & Status</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Status </td>
												<td>
													<input type="text" data-attribute="status" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Status Date </td>
												<td>
													<input type="text" data-type="date" data-attribute="statusdate" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Order Date </td>
												<td>
													<input type="text" data-type="date" data-attribute="orderdate" data-input="required">
												</td>
											</tr>
											<tr>
												<td>Required Date </td>
												<td>
													<input type="text" data-type="date" data-attribute="requireddate">
												</td>
											</tr>
											<tr>
												<td>Changed Date </td>
												<td>
													<input type="text" data-type="date" data-attribute="changedate" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Follow Up date </td>
												<td>
													<input type="text" data-type="date" data-attribute="followupdate">
												</td>
											</tr>
											<tr>
												<td>Vendor Promised Date </td>
												<td colspan=2>
													<input type="text" data-type="date" data-attribute="vendordate">
												</td>
											</tr>
										</table>
									</div>

									
								</div>



								<div class="row eatfix col-md-12">
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Vendor </td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Vendor </td>
												<td>
													<input type="text" data-navigation="vendor" data-attribute="vendor" data-input="required" data-lookup="vendor" data-childrentable="vendor" data-childrencolumn='vendorcode' data-lookupaction="y"> </td>
											</tr>
											<tr>
												<td>Name </td>
												<td colspan=3>
													<input type="text" data-childfld="child" data-attribute="vendor.name" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Address </td>
												<td colspan=3>
													<input type="text" data-childfld="child" data-attribute="vendor.address" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>City </td>
												<td>
													<input type="text" data-childfld="child" data-attribute="vendor.city" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Province </td>
												<td>
													<input type="text" data-childfld="child" data-attribute="vendor.stateprovince" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Postal Code </td>
												<td>
													<input type="text" data-childfld="child" data-attribute="vendor.zippostalcode" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Contact </td>
												<td colspan=2>
													<input type="text" data-childfld="child" data-attribute="vendor.contact" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Tel. </td>
												<td colspan=2>
													<input type="text" data-childfld="child" data-attribute="vendor.phone" data-input="readonly"> </td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Document Status </td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>
													
												</td>
											</tr>		
											<tr>
												<td>Line Num. Proses </td>
												<td>
													<input type="text" data-attribute="docprocessnum" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Document Status </td>
												<td>
													<input type="text" data-attribute="docstatus" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Remarks </td>
												<td>
													<input type="text" data-attribute="docnotice" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Sent By </td>
												<td>
													<input type="text" data-attribute="docsentby" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Document on Person </td>
												<td>
													<input type="text" data-attribute="docposition" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Date of Arrival </td>
												<td>
													<input type="text" data-attribute="docarrival" data-type="date" data-input="readonly"> </td>
											</tr>
											<tr>
												<td colspan=2>
													<div class='btncontainer2'>
														<ul>
															<a href='javascript:;' id='btn_comments'>
																<li><i class='fa fa-comment'></i></a>
															<ul>
													</div>
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Receiving </td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Term of Shipment </td>
												<td>
													<input type="text" data-attribute="freightterms"> </td>
											</tr>
											<tr>
												<td>Term of Payment </td>
												<td>
													<input type="text" data-attribute="paymentterms"> </td>
											</tr>
											<tr>
												<td>Est. of Arrival </td>
												<td>
													<input type="text" data-attribute="estdatearrival" data-type="date"> </td>
											</tr>
											<tr>
												<td>FOB </td>
												<td>
													<input type="text" data-attribute="fobpoints"> </td>
											</tr>
											<tr>
												<td>Company</td>
												<td>
													<input type="text" data-attribute="orgcode" data-lookup="organization" data-input="required"> </td>
											</tr>
											<tr>
												<td>Site</td>
												<td>
													<input type="text" data-attribute="sitecode" data-lookup="site" data-input="required"> </td>
											</tr>
										</table>
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
											<table class="tbldata">
												<tr>
													<td>PO</td>
													<td>
														<input type="text" data-attribute="ponum" data-type="upper" data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description</td>
													<td>
														<input type="text" data-attribute="description" style="width:450px;">
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="maxes_sct_tbl">Order Detail</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-4">
										<div class="addrow" id="addPOLines"></div>
										<div class="addrow" id="addContractLines"></div>
									</div>
								</div>
								<div class="col-md-12">
									<div id="subform-polines" class="subform" data-table="mxtable_polines_child" data-child="poline">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Order Detail</td>
													</tr>
													<tr>
														<td>Num.</td>
														<td>
															<input type="text" size=3 data-childfld="child" data-attribute="polinenum" data-key="value" data-input="required" data-type="integer">
														</td>
													</tr>
													<tr>
														<td>Type </td>
														<td>
															<input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype" data-lookupaction="y" data-input="required">
														</td>
													</tr>
													<tr>
														<td>Item</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="itemnum" data-navigation="item" data-lookup="item|sitecode" data-lookupaction="y" data-childrentable="item" data-childrencolumn="itemnum">
														</td>
													</tr>
													<tr>
														<td>Service Item</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems"  data-lookupaction="y" >
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description">
														</td>
													</tr>
													<tr>
														<td>Remarks</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="remarks">
														</td>
													</tr>
													<tr>
														<td>Specification</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="itemnum.spectext" data-input="readonly">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Cost</td>
													</tr>
													<tr>
														<td>Unit Cost</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost">
														</td>
													</tr>
													<tr>
														<td>Total Cost</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="linecost" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Tax</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="tax" data-lookup="taxpurchasing" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Tax Cost</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="taxcost">
														</td>
													</tr>
													<tr>
														<td>Budget Line</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="budgetlinenum" data-lookup="budgetline|budgetnum">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Quantity</td>
													</tr>
													<tr>
														<td>Quantity</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="orderqty">
														</td>
													</tr>
													<tr>
														<td>Order Unit </td>
														<td>
															<input type="text" data-childfld="child" data-attribute="orderunit" data-input="required" data-lookup="measureunit" data-crossvalue="itemnum.orderunit" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Issue Unit</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Conversion Factor</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="conversionfactor" data-input="required" data-lookupaction="y">
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="row eatfix col-md-12">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Model</td>
													</tr>
													<tr>
														<td>Manufacturer</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="itemnum.manufacturer" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Part Number</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="partnum" data-lookup="itempartnum|itemnum">
														</td>
													</tr>
													<tr>
														<td>Category</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="category">
														</td>
													</tr>
													<tr>
														<td>Catalog</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="catalog">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Receiving Detail</td>
													</tr>
													<tr>
														<td>Warehouse</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse|sitecode" data-childrentable="warehouse" data-childrencolumn="warehouse" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Work Order</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="wonum" data-lookup="workorder|orgcode" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Required Date</td>
														<td>
															<input type="text" data-childfld="child" data-type="date" data-attribute="requireddate">
														</td>
													</tr>
													
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Transactions Account Code</td>
													</tr>
													<tr>
														<td>Credit Account Code</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="glaccount|orgcode">
														</td>
													</tr>
													<tr>
														<td>Debit Account Code</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="glaccount|orgcode" data-crossvalue="warehouse.controlacc">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
								<!--end form-->
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_polines_child" class="display" data-subform="subform-polines"></table>
												<div id="mxtable_polines_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Num.</td>
																	<td>
																		<input type="text" size=3 data-childfld="child" data-attribute="polinenum" data-key="value" readonly>
																	</td>
																</tr>
																<tr>
																	<td>Type </td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype" data-lookupaction="y" data-input="required">
																	</td>
																</tr>
																<tr>
																	<td>Item</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="itemnum" data-navigation="item" data-lookup="item|warehouse" data-childrentable="item" data-childrencolumn="itemnum">
																	</td>
																</tr>
																<tr>
																	<td>Service Item</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems"  data-lookupaction="y" >
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description">
																	</td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="remarks">
																	</td>
																</tr>
																<tr>
																	<td>Specification</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="itemnum.spectext" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Cost</div>
																	</td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost">
																	</td>
																</tr>
																<tr>
																	<td>Total Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="linecost" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Tax</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="tax" data-lookup="tax" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Tax Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="taxcost">
																	</td>
																</tr>
																<tr>
																	<td>Budget Line</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="budgetlinenum" data-lookup="budgetline|budgetnum">
																	</td>
																</tr>
																<tr>
																	<td>Credit Account Code</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="glaccount|orgcode">
																	</td>
																</tr>
																<tr>
																	<td>Debit Account Code</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="glaccount|orgcode" data-crossvalue="warehouse.controlacc">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Quantity</div>
																	</td>
																</tr>
																<tr>
																	<td>Quantity</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="orderqty">
																	</td>
																</tr>
																<tr>
																	<td>Order Unit </td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderunit" data-input="required" data-lookup="measureunit" data-crossvalue="itemnum.orderunit" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Issue Unit</td>
																	<td>
																		<input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.issueunit">
																	</td>
																</tr>
																<tr>
																	<td>Conversion Factor</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="conversionfactor" data-input="required">
																	</td>
																</tr>
															</table>
														</div>
													</div>
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Model</div>
																	</td>
																</tr>
																<tr>
																	<td>Manufacturer</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="itemnum.manufacturer" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Part Number</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="partnum" data-lookup="itempartnum|itemnum">
																	</td>
																</tr>
																<tr>
																	<td>Category</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="category">
																	</td>
																</tr>
																<tr>
																	<td>Catalog</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="catalog">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Receiving Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Warehouse</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse|sitecode" data-childrentable="warehouse" data-childrencolumn="warehouse" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Work Order</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum" data-lookup="workorder|orgcode" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Required Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="date" data-attribute="requireddate">
																	</td>
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="date" data-attribute="enterdate" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Entered By</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Last Changed</div>
																	</td>
																</tr>
																
																
																<tr>
																	<td>Requested by</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="requestedby" data-lookup="employees">
																	</td>
																</tr>
																<tr>
																	<td>Requested Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="date" data-attribute="requesteddate">
																	</td>
																</tr>
																<tr>
																	<td>Changed Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="date" data-attribute="changedate" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Changed By</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Related PR Record</div>
																	</td>
																</tr>
																<tr>
																	<td>PR</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="prnum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>PR Line</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="prlinenum" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Related RFQ Record</div>
																	</td>
																</tr>
																<tr>
																	<td>RFQ</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="rfqnum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>RFQ Line</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="rfqlinenum" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Related Sales Order Record</div>
																	</td>
																</tr>
																<tr>
																	<td>SO</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>SO Line</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="solinenum" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Total Cost </td>
											</tr>
										</table>
									</div>
									<div class="row eatfix col-md-12">
										<div class='col-md-4'>
											<table class="tbldata">
												<tr>
													<td>Currency </td>
													<td>
														<input type="text" data-attribute="currencycode" data-input="required" data-lookup="currency" data-lookupaction="y"> </td>
												</tr>
												<tr>
													<td>Exchange Rates </td>
													<td>
														<input type="text" data-type="decimal" data-attribute="exchangerate"> </td>
												</tr>
												</tr>
												<tr>
													<td>Total before Tax </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotal"> </td>
												</tr>
												<tr>
													<td>Total Tax before Discount </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax"> </td>
												</tr>
											</table>
										</div>
										<div class='col-md-4'>
											<table class="tbldata">
												<tr>
													<td>Discount(%) </td>
													<td>
														<input type="text" data-type="decimal" data-attribute="discountpercentage"> </td>
												</tr>
												<tr>
													<td>Discount </td>
													<td>
														<input type="text" data-type="decimal" data-attribute="discountprice"> </td>
												</tr>
												<tr>
													<td>Total after Discount </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotaldiscount"> </td>
												</tr>
												<tr>
													<td>Total Tax after Discount </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax2"> </td>
												</tr>
											</table>
										</div>
										<div class='col-md-4'>
											<table class="tbldata">
												<tr>
													<td>Total </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="totalcost"> </td>
												</tr>
												<tr>
													<td>Total Base Cost </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="totalbasecost"> </td>
												</tr>
												<tr>
													<td>Paid </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="paidcost"> </td>
												</tr>
												<tr>
													<td>Unpaid </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="unpaidcost"> </td>
												</tr>
											</table>
										</div>
									</div>
								</div>
							</div>
							<!--end tab-->
							<div id="maxes02_1_4" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<div class="page-header">
											<table class="tbldata">
												<tr>
													<td>PO</td>
													<td>
														<input type="text" data-attribute="ponum" data-type="upper" data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description</td>
													<td>
														<input type="text" data-attribute="description" style="width:450px;">
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Shipping & Billing</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-4">
										<table class="tbldata">
											<td class="maxes_sct_tbl2">Shipping</td>
											<tr>
												<td>
													<table class="tbldata">
														<tr>
															<td>Address Code</td>
															<td>
																<input type="text" data-attribute="shiptoaddress" data-input="required" data-lookup="address|sitecode" data-childrentable="address" data-childrencolumn="addresscode">
															</td>
														</tr>
														<tr>
															<td>Address</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="shiptoaddress.address" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>City</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="shiptoaddress.city" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>Province</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="shiptoaddress.stateprovince" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>Postal Code</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="shiptoaddress.zippostalcode" data-input="readonly">
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td class="maxes_sct_tbl2">Billing</td>
											</tr>
											<tr>
												<td>
													<table class="tbldata">
														<tr>
															<td>Address Code</td>
															<td>
																<input type="text" data-attribute="billtoaddress" data-input="required" data-lookup="address|sitecode" data-childrentable="address" data-childrencolumn="addresscode">
															</td>
														</tr>
														<tr>
															<td>Address</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="billtoaddress.address" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>City</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="billtoaddress.city" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>Province</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="billtoaddress.stateprovince" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>Postal Code</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="billtoaddress.zippostalcode" data-input="readonly">
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<div id="maxes02_1_5" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<div class="page-header">
											<table class="tbldata">
												<tr>
													<td>PO</td>
													<td>
														<input type="text" data-attribute="ponum" data-type="upper" data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description</td>
													<td>
														<input type="text" data-attribute="description" style="width:450px;">
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="maxes_sct_tbl">Term of Payment</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-4">
										<div class="addrow" id="addTerms"></div>
									</div>
								</div>
								<div class="row eatfix">
									<div id="subform-poterms" class="subform" data-child="poterm" data-table="mxtable_poterm_child">
										<div class="row eatfix">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Num.</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-key="value" data-type="integer">
														</td>
													</tr>
													<tr>
														<td>Code</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode"> </td>
													</tr>
													<tr>
														<td>Remarks</td>
														<td>
															<input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_poterm_child" class="display" data-subform="subform-poterms"></table>
												<div id="mxtable_poterm_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Terms & Conditions</div>
																	</td>
																</tr>
																<tr>
																	<td>Num.</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="termlinenum" data-key="value" data-type="integer" readonly>
																	</td>
																</tr>
																<tr>
																	<td>Code</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode"> </td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td>
																		<input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="maxes_sct_tbl">Terms and Conditions</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-4">
										<div class="addrow" id="addTerms2"></div>
									</div>
								</div>
								<div id="subform-poterms2" class="subform" data-child="poterm2" data-table="mxtable_poterm2_child">
									<div class="row eatfix">
										<div class="col-md-8">
											<table class="tbldata">
												<tr>
													<td>Num.</td>
													<td>
														<input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-key="value" data-type="integer">
													</td>
												</tr>
												<tr>
													<td>Code</td>
													<td>
														<input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode"> </td>
												</tr>
												<tr>
													<td>Remarks</td>
													<td>
														<input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description">
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_poterm2_child" class="display" data-subform="subform-poterms2"></table>
												<div id="mxtable_poterm2_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Term & Condition</div>
																	</td>
																</tr>
																<tr>
																	<td>Num.</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="termlinenum" data-key="value" data-type="integer" readonly>
																	</td>
																</tr>
																<tr>
																	<td>Code</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode"> </td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td>
																		<input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div id="maxes02_1_6" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<div class="page-header">
											<table class="tbldata">
												<tr>
													<td>PO</td>
													<td>
														<input type="text" data-attribute="ponum" data-type="upper" data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description</td>
													<td>
														<input type="text" data-attribute="description" style="width:450px;">
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="maxes_sct_tbl">Cost</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Currency </td>
												<td>
													<input type="text" data-attribute="currencycode" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Exchange Rates </td>
												<td>
													<input type="text" data-type="decimal" data-attribute="exchangerate" data-input="readonly"> </td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Total Cost </td>
												<td>
													<input type="text" data-type="decimal" data-attribute="totalcost" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Total Base Cost </td>
												<td>
													<input type="text" data-type="decimal" data-attribute="totalbasecost" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Unpaid </td>
												<td>
													<input type="text" data-type="decimal" data-attribute="unpaidcost" data-input="readonly"> </td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="maxes_sct_tbl">Paid</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_popayment_child" class="display" data-subform="subform-popayment" data-child="popayment"></table>
												<div id="mxtable_popayment_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="paymentlinenum" data-input="required" data-key="value" data-type="integer">
																	</td>
																</tr>
																<tr>
																	<td>Payment Number</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="paymentnum" data-input="required">
																	</td>
																</tr>
																<tr>
																	<td>Payment Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="paymentdate" data-type="date"> </td>
																</tr>
																<tr>
																	<td>Payment Via</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="paymentvia">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Vendor Refund?</td>
																	<td>
																		<input type="checkbox" data-childfld="child" data-attribute="refund">
																	</td>
																</tr>
																<tr>
																	<td>Paid</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="paidcost" data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Paid Basis Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="paidbasecost" data-type="decimal" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Invoice</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="invoicenum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Payment Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="invoicedate" data-type="date" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td>
																		<input style="width:350px;" type="text" data-childfld="child" data-attribute="remarks">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>