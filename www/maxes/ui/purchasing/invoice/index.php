<!DOCTYPE html>
<html>
	<title> </title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="invoice" data-appkeynum="invoicenum">
		<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Invoice</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_3">Detail</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_5">Other Payment</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_4">Term of Conditions</a></li>
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
												<table id="mxtable_invoice" class="display"></table>
												<div>
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
								<div class="col-md-12">
									<div class="row eatfix">
										<div class="col-md-2">
											<div id="qrcoderegion">
												<div id='qrcode'></div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Invoice</td>
														<td><input type="text" data-attribute="invoicenum" data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description" style="width:450px;"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-4">
									
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Customer</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Customer</td>
												<td><input type="text" data-attribute="customercode" data-lookup="customer" data-lookupaction="y" data-childrentable="customer" data-childrencolumn="customercode"> </td>
											</tr>
											<tr>
												<td>NPWP </td>
												<td><input type="text" data-attribute="npwp"> </td>
											</tr>
											<tr>
												<td>Account Representative</td>
												<td><input type="text"  data-attribute="salesman" data-crossvalue="customercode.salesrep" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Donation</td>
												<td><input type="checkbox"  data-attribute="isdonation" data-input="readonly">
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Payment Method</td>
											</tr>
										</table>
										
										<table class="tbldata">
											<tr>
												<td>Payment Method</td>
												<td style="font-size:9pt; font-family:calibri"><br>
													<input type='radio' class='nonpersistent' name = 'paymenttype' value="kredit">  KREDIT/UTANG <br>
													<input type='radio' class='nonpersistent' name = 'paymenttype' value ="cashonbank"> Transfer Bank TUNAI <br>
													<input type='radio' class='nonpersistent' name = 'paymenttype' value ="cashonhand"> Cash TUNAI <br>
													<input type='radio' class='nonpersistent' name = 'paymenttype' value ="cashongiro" data-alias = "cashongiro"> Cash GIRO <br>
													
												</td>
											</tr>
											<tr>
												<td>Giro/Cheque Date</td>
												<td><input type="text" data-attribute="chequedate" data-type="date">
												</td>
											</tr>
											<tr>
												<td>Giro/Cheque Num. </td>
												<td><input type="text" data-attribute="chequenum" >
												</td>
											</tr>
										</table>			
									</div>
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Total Invoice</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Sub Total</td>
												<td><input type="text" data-attribute="subtotalcost" data-input="readonly" data-type="decimal"> </td>
											</tr>
											<tr>
												<td>Total Tax </td>
												<td><input type="text" data-attribute="totaltax" data-input="readonly" data-type="decimal"> </td>
											</tr>
											<tr>
												<td>Total</td>
												<td><input type="text" data-attribute="totalcost" data-input="readonly" data-type="decimal"> </td>
											</tr>
											<tr>
												<td>Term of Payment(%)</td>
												<td><input type="text" data-attribute="paypercent" data-type="decimal" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Company</td>
												<td><input type="text" data-attribute="orgcode" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Site</td>
												<td><input type="text" data-attribute="sitecode" data-input="readonly"> </td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
								
									<div class="col-md-4">
									
										<table class="tbldata">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Bank</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Bank Name </td>
												<td><input type="text" data-attribute="banktoname" data-lookup="customerbank|customercode" data-lookupaction='y'> </td>
											</tr>
											<tr>
												<td>Account Line Num.</td>
												<td><input type="text" data-attribute="banktoaccount" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Holder Name </td>
												<td><input type="text" data-attribute="banktoaccountname" data-input="readonly"> </td>
											</tr>
											
											
										</table>
									</div>
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Tanggal Invoice</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Invoice Date</td>
												<td><input type="text" data-attribute="invoicedate" data-type="date" data-input="required"> </td>
											</tr>
											<tr>
												<td>Scheduled Date </td>
												<td><input type="text" data-attribute="scheddate" data-type="date" data-input="required"> </td>
											</tr>
											<tr>
												<td>Forfeit Commission after Sched. Date Passed?</td>
												<td><input type="checkbox" data-attribute="voidcommission"> </td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Status</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Status </td>
												<td><input type="text" data-attribute="status" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Status Date </td>
												<td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"> </td>
											</tr>
											<tr>
												<td>Consignment</td>
												<td><input type="checkbox" data-attribute="isconsignment"> </td>
											</tr>
											
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Purchasing</td>
											</tr>
										</table>
										<div class="row eatfix">
											<div  class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>PO #</td>
														<td><input type="text" data-attribute="ponum" data-lookup="po" data-childrentable="po" data-childrencolumn="ponum" data-lookupaction="y"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.description"></td>
													</tr>
													<tr>
														<td>Vendor</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.vendor"></td>
													</tr>
													<tr>
														<td>Status</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.status"></td>
													</tr>
													<tr>
														<td>Order Date</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.orderdate" data-type="date"></td>
													</tr>
												</table>
											</div>
											<div  class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Currency</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.currencycode"></td>
													</tr>
													<tr>
														<td>Total Tax</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.totaltax" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Total Cost</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.totalcost" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Unpaid Cost</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.unpaidcost" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Total Base Cost</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="ponum.totalbasecost" data-type="decimal"></td>
													</tr>
													
												</table>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Sales</td>
											</tr>
										</table>
										<div class="row eatfix">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>SO #</td>
														<td><input type="text" data-attribute="sonum" data-lookup="salesorder" data-childrentable="so" data-childrencolumn="sonum" data-lookupaction="y"></td>
													</tr>
													<tr>
														<td>PKP</td>
														<td><input type="checkbox" data-attribute="pkp"> </td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.description"></td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.customercode"></td>
													</tr>
													<tr>
														<td>Contact</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.contactname"></td>
													</tr>
													<tr>
														<td>Status</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.status"></td>
													</tr>
													<tr>
														<td>Order Date</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.orderdate" data-type="date"></td>
													</tr>
												</table>
											</div>
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Total before Tax</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.pretaxtotal" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Total Tax</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.totaltax" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Total</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.totalprice" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Discount(%)</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.discountpercentage" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Total after discount</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.discountedtotalprofit" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Tax after discount</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.discountedtotaltax" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Total</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.discountedtotalprice" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Unpaid Cost</td>
														<td><input type="text" data-input="readonly" data-childfld="child" data-attribute="sonum.unpaidprice" data-type="decimal"></td>
													</tr>
												</table>
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
											<table class="tbldata">
												<tr>
													<td>Invoice</td>
													<td><input type="text" data-attribute="invoicenum" data-type="upper" data-input="required" data-key="value"></td>
												</tr>
												<tr>
													<td>Description</td>
													<td><input type="text" data-attribute="description" style="width:450px;"></td>
												</tr>
												<tr>
													<td>Sub Total Cost</td>
													<td><input type="text" data-attribute="subtotalcost" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Total Tax </td>
													<td><input type="text" data-attribute="totaltax" data-input="readonly" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Total Cost</td>
													<td><input type="text" data-attribute="totalcost" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Term of Payment(%)</td>
													<td><input type="text" data-attribute="paypercent" data-type="decimal" data-input="readonly"></td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Invoice Lines</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-2">
										<div class="addrow" id="selectinvoicelines"></div>
									</div>
									<div class="col-md-2">
										<div class="addrow" id="selectdelivereditems"></div>
									</div>
									<div class="col-md-2">
										<div class="addrow" id="selectdeliveredservices"></div>
									</div>
									<div class="col-md-2">
										<div class="addrow" id="addmatsaletrans"></div>
									</div>
									<div class="col-md-2">
										<div class="addrow" id="addbillings"></div>
									</div>
								</div>
								<div class="subtab col-md-12">
									<ul class="maxes02">
										<li class="maxes02_1"><a href="#invoiceline">Detail</a></li>
										<li class="maxes02_2"><a href="#invoicebilling">Billing Detail</a></li>
										<li class="maxes02_2"><a href="#invoicecost">Cost</a></li>
										
									</ul>
								</div>
								<div class="maxes04sub">
									<div id="invoiceline" class="maxes02_1">
										<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											Detail Invoice
										</div>
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>PO #</td>
														<td><input type="text" data-attribute="ponum" data-lookup="po" data-childrentable="po" data-childrencolumn="ponum" data-lookupaction="y"></td>
													</tr>
													<tr>
														<td>SO #</td>
														<td><input type="text" data-attribute="sonum" data-lookup="salesorder" data-childrentable="so" data-childrencolumn="sonum" data-lookupaction="y"></td>
													</tr>
													
													<tr>
														<td>Vendor</td>
														<td><input type="text" data-attribute="vendorcode" data-lookup="vendor" data-lookupaction="y"> </td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Customer Headquarter</td>
														<td><input type="text" data-attribute="customerhqcode" data-lookup="customer" data-lookupaction="y"> </td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-attribute="customercode" data-lookup="customer" data-lookupaction="y"> </td>
													</tr>
													<tr>
														<td>PKP</td>
														<td><input type="checkbox" data-attribute="pkp"> </td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_invoicelineview_child" class="display" data-child="invoicelineview"></table>
												<div id="mxtable_invoicelineview_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="invoicelinenum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Item</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly"> </td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Invoiced Qty.</td>
																	<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Order Unit </td>
																	<td><input type="text" data-childfld="child" data-attribute="orderunit" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Issue Unit</td>
																	<td><input type="text" data-childfld="child" data-attribute="saleunit" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Unit Price</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Total Price</td>
																	<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal"></td>
																</tr>
																
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Discount Price per Unit</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitpricediscount" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Total Price discount</td>
																	<td><input type="text" data-childfld="child" data-attribute="linepricediscount" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Tax Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="tax" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Tax Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="taxcost" data-input="readonly" data-type="decimal"></td>
																</tr>
															</table>
														</div>
													</div>
													<!-- DO NOT DELETE THIS HIDDEN INPUT, This input is to differentiate the record ID in matrectrans table--><input type='hidden' data-childfld='child' data-attribute='invoicelineviewid' data-input='readonly'> 
												</div>
											</div>
										</div>
									</div>
									<div id="invoicecost" class="maxes03">
										<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											Detail Item
										</div>
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>PO #</td>
														<td><input type="text" data-attribute="ponum" data-lookup="po" data-childrentable="po" data-childrencolumn="ponum" data-lookupaction="y"></td>
													</tr>
													<tr>
														<td>SO #</td>
														<td><input type="text" data-attribute="sonum" data-lookup="salesorder" data-childrentable="so" data-childrencolumn="sonum" data-lookupaction="y"></td>
													</tr>
													
													<tr>
														<td>Vendor</td>
														<td><input type="text" data-attribute="vendorcode" data-lookup="vendor" data-lookupaction="y"> </td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Customer Headquarter</td>
														<td><input type="text" data-attribute="customerhqcode" data-lookup="customer" data-lookupaction="y"> </td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-attribute="customercode" data-lookup="customer" data-lookupaction="y"> </td>
													</tr>
													<tr>
														<td>PKP</td>
														<td><input type="checkbox" data-attribute="pkp"> </td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_invoiceline_child" class="display" data-child="invoiceline"></table>
												<div id="mxtable_invoiceline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-3">
															<table class="tableDetailRow">
																<tr>
																	<td>Customer</td>
																	<td><input type="text" data-childfld="child" data-attribute="customercode" data-childrentable="customer" data-childrencolumn="customercode"></td>
																</tr>
																<tr>
																	<td>Name</td>
																	<td><input type="text" data-childfld="child" data-attribute="customercode.name" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Billing</td>
																	<td><input type="text" data-childfld="child" data-attribute="billingnum"  data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="billinglinenum"  data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Sold Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="solddate" data-type="date"  data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-3">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="invoicelinenum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Item</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly"> </td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Debit Account Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="glaccount|orgcode"></td>
																</tr>
																<tr>
																	<td>Credit Account Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="glaccount|orgcode"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-3">
															<table class="tableDetailRow">
																<tr>
																	<td>Unit Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcost2" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Total Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="linecost2" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>FIFO/LIFO Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="costdate" data-input="readonly" data-type="date"></td>
																</tr>
																<tr>
																	<td>Issue Type</td>
																	<td><input type="text" data-childfld="child" data-attribute="costtype" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>Quantity</td>
																	<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Order Unit </td>
																	<td><input type="text" data-childfld="child" data-attribute="orderunit" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Issue Unit</td>
																	<td><input type="text" data-childfld="child" data-attribute="saleunit" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-3">
															<table class="tableDetailRow">
																<tr>
																	<td>Unit Price</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Total Price</td>
																	<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Discount(%)</td>
																	<td><input type="text" data-childfld="child" data-attribute="discount" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Unit Price after Discount</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitpricediscount" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Discount</td>
																	<td><input type="text" data-childfld="child" data-attribute="discountprice" data-input="readonly" data-type="decimal"></td>
																</tr>
																
																<tr>
																	<td>Total Price after discount</td>
																	<td><input type="text" data-childfld="child" data-attribute="linepricediscount" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Commission per Unit</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcommission" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Total Commission</td>
																	<td><input type="text" data-childfld="child" data-attribute="linecommission" data-type="decimal"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-3">
															<table class="tableDetailRow">
																<tr>
																	<td>Tax Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="tax" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Tax Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="taxcost" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>WO</td>
																	<td><input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Entered By</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td><input type="text" data-childfld="child" data-attribute="remarks"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-3">
															<table class="tableDetailRow">
																<tr>
																	<td>PO</td>
																	<td><input type="text" data-childfld="child" data-attribute="ponum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Sales Order</td>
																	<td><input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Billing</td>
																	<td><input type="text" data-childfld="child" data-attribute="billingnum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Trans. No</td>
																	<td><input type="text" data-childfld="child" data-attribute="transnum" data-input="readonly"></td>
																</tr>
															</table>
														</div>
													</div>
													<!-- DO NOT DELETE THIS HIDDEN INPUT, This input is to differentiate the record ID in matrectrans table--><input type='hidden' data-childfld='child' data-attribute='invoicelineid' data-input='readonly'> 
												</div>
											</div>
										</div>
									</div>
									<div id="invoicebilling" class="maxes03">
										<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											Billing Detail
										</div>
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Customer Headquarter</td>
														<td><input type="text" data-attribute="customerhqcode" data-lookup="customer" data-lookupaction="y"> </td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-attribute="customercode" data-lookup="customer" data-lookupaction="y"> </td>
													</tr>
													<tr>
														<td>PKP</td>
														<td><input type="checkbox" data-attribute="pkp"> </td>
													</tr>
												</table>
											</div>
										</div>
										<div class="row eatfix">
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_invoicebilling_child" class="display" data-child="invoicebillingtax"></table>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Tax</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-2">
										<div class="addrow" id="selectchargelines"></div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_invoicecharge_child" class="display" data-child="invoicecharge"></table>
											<div id="mxtable_invoicecharge_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="invoicechargelinenum" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Line Num. E-FAKTUR</td>
																<td><input type="text" data-childfld="child" data-attribute="taxreceiptnum"></td>
															</tr>
															<tr>
																<td>Description</td>
																<td><input type="text" data-childfld="child" data-attribute="description"></td>
															</tr>
															<tr>
																<td>Origin</td>
																<td><input type="text" data-childfld="child" data-attribute="origin" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Quantity</td>
																<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-lookupaction="y"></td>
															</tr>
															<tr>
																<td>Unit Cost</td>
																<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="readonly"></td>
															</tr>
														</table>
													</div>
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Budget Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="budgetnum" data-lookup="budget"></td>
															</tr>
															<tr>
																<td>Budget Line Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="budgetlinenum" data-lookup="budgetline|budgetnum"></td>
															</tr>
															<tr>
																<td>Total Cost</td>
																<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-lookupaction="y"></td>
															</tr>
															<tr>
																<td>Debit Account Code</td>
																<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="glaccount|orgcode"></td>
															</tr>
															<tr>
																<td>Credit Account Code</td>
																<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="glaccount|orgcode"></td>
															</tr>
														</table>
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
												<td class="maxes_sct_tbl">Other Charges</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_invoicecharge2_child" class="display" data-child="invoicecharge2"></table>
											<div id="mxtable_invoicecharge2_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="invoicechargelinenum" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Line Num. Tagihan</td>
																<td><input type="text" data-childfld="child" data-attribute="taxreceiptnum"></td>
															</tr>
															<tr>
																<td>Description</td>
																<td><input type="text" data-childfld="child" data-attribute="description"></td>
															</tr>
															<tr>
																<td>Origin</td>
																<td><input type="text" data-childfld="child" data-attribute="origin" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Quantity</td>
																<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-lookupaction="y"></td>
															</tr>
															<tr>
																<td>Unit Cost</td>
																<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="readonly"></td>
															</tr>
														</table>
													</div>
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Budget Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="budgetnum" data-lookup="budget"></td>
															</tr>
															<tr>
																<td>Budget Line Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="budgetlinenum" data-lookup="budgetline|budgetnum"></td>
															</tr>
															<tr>
																<td>Total Cost</td>
																<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-lookupaction="y"></td>
															</tr>
															<tr>
																<td>Debit Account Code</td>
																<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="glaccount|orgcode"></td>
															</tr>
															<tr>
																<td>Credit Account Code</td>
																<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="glaccount|orgcode"></td>
															</tr>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
							</div>
							<div id="maxes02_1_4" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<div class="page-header">
											<table class="tbldata">
												<tr>
													<td>Invoice</td>
													<td><input type="text" data-attribute="invoicenum" data-type="upper" data-input="required" data-key="value"></td>
												</tr>
												<tr>
													<td>Description</td>
													<td><input type="text" data-attribute="description" style="width:450px;"></td>
												</tr>
												<tr>
													<td>Sub Total Cost</td>
													<td><input type="text" data-attribute="subtotalcost" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Total Tax </td>
													<td><input type="text" data-attribute="totaltax" data-input="readonly" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Total Cost</td>
													<td><input type="text" data-attribute="totalcost" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Term of Payment(%)</td>
													<td><input type="text" data-attribute="paypercent" data-type="decimal" data-input="readonly"></td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-4">
										<div class="addrow" id="addInvoiceTerms"></div>
									</div>
								</div>
								<div class="row eatfix">
									<div id="subform-invoiceterms" class="subform" data-child="invoiceterm" data-table="mxtable_invoiceterms_child">
										<div class="row eatfix"> </div>
										<div class="col-md-12">
											<div class="col-md-8">
												<table class="tbldata">
													<tr>
														<td>Num.</td>
														<td><input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-key="value" data-type="integer"></td>
													</tr>
													<tr>
														<td>Code</td>
														<td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode"> </td>
													</tr>
													<tr>
														<td>Remarks</td>
														<td><input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_invoiceterms_child" class="display" data-subform="subform-invoiceterms"></table>
											<div id="mxtable_invoiceterms_child_detail" class="mxtable_detail">
												<div class="col-md-4">
													<table class="tableDetailRow">
														<tr>
															<td colspan=2>
																<div class="maxes_sct_tbl3">DetailSyarat/Kondisi</div>
															</td>
														</tr>
														<tr>
															<td>Num.</td>
															<td><input type="text" data-childfld="child" data-attribute="termlinenum" data-key="value" data-type="integer" readonly></td>
														</tr>
														<tr>
															<td>Code</td>
															<td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode"> </td>
														</tr>
														<tr>
															<td>Remarks</td>
															<td><input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description"></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
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
													<td>Invoice</td>
													<td><input type="text" data-attribute="invoicenum" data-type="upper" data-input="required" data-key="value"></td>
												</tr>
												<tr>
													<td>Description</td>
													<td><input type="text" data-attribute="description" style="width:450px;"></td>
												</tr>
												<tr>
													<td>Sub Total Cost</td>
													<td><input type="text" data-attribute="subtotalcost" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Total Tax </td>
													<td><input type="text" data-attribute="totaltax" data-input="readonly" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Total Cost</td>
													<td><input type="text" data-attribute="totalcost" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Term of Payment(%)</td>
													<td><input type="text" data-attribute="paypercent" data-type="decimal" data-input="readonly"></td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Other Payment</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-8">
										<div class="col-md-6">
											<div class="addrow" id="addinvpaymentline"></div>
											<div class="addrow" id="selectWOServices"></div>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div id="subform-invpaymentline" class="subform" data-child="invpaymentline" data-table="mxtable_invpaymentline_child">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Num.</td>
														<td><input type="text" data-childfld="child" data-attribute="invpaymentlinenum" data-input="required" data-key="value" data-type="integer"></td>
													</tr>
													<tr>
														<td>Type </td>
														<td><input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype"> </td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-childfld="child" data-attribute="description"></td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Quantity</td>
														<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required"></td>
													</tr>
													<tr>
														<td>Order Unit </td>
														<td><input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit"></td>
													</tr>
													<tr>
														<td>Unit Cost</td>
														<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="required"> </td>
													</tr>
													<tr>
														<td>Total Cost</td>
														<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="required"> </td>
													</tr>
													<tr>
														<td>Paid Date</td>
														<td><input type="text" data-childfld="child" data-attribute="paiddate" data-type="date" data-input="required"> </td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>WO</td>
														<td><input type="text" data-childfld="child" data-attribute="wonum" data-input="required" data-lookup="workorder|orgcode" data-childrentable="workorder" data-childrencolumn="wonum"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-childfld="child" data-attribute="wonum.description" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Leader</td>
														<td><input type="text" data-childfld="child" data-attribute="wonum.leader" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Debit Account Code</td>
														<td><input type="text" data-childfld="child" data-attribute="wonum.glaccount" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Account Code</td>
														<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="chartofaccount"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_invpaymentline_child" class="display" data-subform="subform-invpaymentline"></table>
											<div id="mxtable_invpaymentline_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="invpaymentlinenum" data-input="required" data-key="value" data-type="integer"></td>
																</tr>
																<tr>
																	<td>Type </td>
																	<td><input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype"> </td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="description"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Quantity</td>
																	<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required"></td>
																</tr>
																<tr>
																	<td>Order Unit </td>
																	<td><input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit"></td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="required"> </td>
																</tr>
																<tr>
																	<td>Total Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="required"> </td>
																</tr>
																<tr>
																	<td>Paid Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="paiddate" data-type="date" data-input="required"> </td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>WO</td>
																	<td><input type="text" data-childfld="child" data-attribute="wonum" data-input="required" data-lookup="workorder|orgcode" data-childrentable="workorder" data-childrencolumn="wonum"></td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="wonum.description" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Leader</td>
																	<td><input type="text" data-childfld="child" data-attribute="wonum.leader" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>is Reimbursable?</td>
																	<td><input type="checkbox" data-childfld="child" data-attribute="isreimburse"> </td>
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
	</body>
</html>