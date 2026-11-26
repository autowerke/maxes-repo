<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="so" data-appkeynum = "sonum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Detail</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Items & Services</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Term of Conditions</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_7">Paid</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_8">Transactions</a></li>
								
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
													<table id="mxtable_so" class="display"></table>
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
										<div class="col-md-12">
											<div class="col-md-2">
												<div id="qrcoderegion">
													<div id='qrcode'></div>
												</div>
											</div>
											<div class="col-md-10">
												<div class="page-header">
													<table class="tbldata">
														<tr>
															<td>SO Number</td>
															<td><input type="text" data-attribute="sonum" data-input="required" data-key="value"></td>
														</tr>
														<tr>
															<td>Description</td>
															<td><input type="text" data-attribute="description"></td>
														</tr>
														<tr>
															<td>Customer</td>
															<td><input type="text" data-attribute="customercode" data-input="required" data-lookup="customer|orgcode" data-lookupaction="y" data-childrentable="customer" data-childrencolumn="customercode"></td>
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
													<td class="maxes_sct_tbl2">Detail</td>
												</tr>
											</table>
											<div class="row eatfix">
												<table class="tbldata">
													<tr>
														<td>Status</td>
														<td><input type="text" data-attribute="status" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Status Date</td>
														<td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Company</td>
														<td><input type="text" data-attribute="orgcode" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Site</td>
														<td><input type="text" data-attribute="sitecode" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Delivery Status</td>
														<td><input type="text" data-attribute="sentstatus" data-input="readonly"></td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-8">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Price</td>
												</tr>
											</table>
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Total before Tax
															</td>
															<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotal">
															</td>
														</tr>
														<tr>
															<td>Tax
															</td>
															<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax">
															</td>
														</tr>
														<tr>
															<td>Total 
															</td>
															<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalprice">
															</td>
														</tr>
														<tr>
															<td>Discount(persen %)
															</td>
															<td><input type="text" data-type="decimal" data-attribute="discountpercentage">
															</td>
														</tr>
														<tr>
															<td>Discount
															</td>
															<td><input type="text" data-type="decimal" data-attribute="discountprice">
															</td>
														</tr>
														
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Discount Invoice</td>
															<td><input type="text" data-type="decimal" data-attribute="invoicediscount" data-input="readonly"></td>
														</tr>
														<tr>
															<td>Sub Total after Discount</td>
															<td><input type="text" data-type="decimal" data-attribute="discountedtotalprofit" data-input="readonly"></td>
														</tr>
														
														<tr>
															<td>Tax after Discount</td>
															<td><input type="text" data-type="decimal" data-attribute="discountedtotaltax" data-input="readonly"></td>
														</tr>
														<tr>
															<td>Grand Total</td>
															<td><input type="text" data-type="decimal" data-attribute="discountedtotalprice" data-input="readonly"></td>
														</tr>
														
														<tr>
															<td>Paid
															</td>
															<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="paidprice">
															</td>
														</tr>
														<tr>
															<td>Unpaid Cost
															</td>
															<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="unpaidprice">
															</td>
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
													<td class="maxes_sct_tbl2">Date Time</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Date Booking</td>
													<td><input type="text" data-type="date" data-attribute="bookingdate"></td>
												</tr>
												<tr>
													<td>Order Date</td>
													<td><input type="text" data-type="date" data-attribute="orderdate" data-input="required"></td>
												</tr>
												<tr>
													<td>Shipping Date</td>
													<td><input type="text" data-type="date" data-attribute="shippingdate"></td>
												</tr>
												<tr>
													<td>Estimated Received Date</td>
													<td><input type="text" data-type="date" data-attribute="expecteddeldate"></td>
												</tr>
												<tr>
													<td>Received Date</td>
													<td><input type="text" data-type="date" data-attribute="deliverydate"></td>
												</tr>
												<tr>
													<td>Warranty Date s/d</td>
													<td><input type="text" data-type="date" data-attribute="warrantydate"></td>
												</tr>
												<tr>
													<td>Invoice Date</td>
													<td><input type="text" data-type="date" data-attribute="invoicedate"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Customer Detail</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Contact
													</td>
													<td><input type="text" data-attribute="contactname" data-lookup="customercontact|customercode" data-childrentable="customercontact" data-childrencolumn="contactname">
													</td>
												</tr>
												<tr>
													<td>Full Name
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.contactname">
													</td>
												</tr>
												<tr>
													<td>Title
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.function">
													</td>
												</tr>
												<tr>
													<td>Tel.
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.phone">
													</td>
												</tr>
												</tr>
												<tr>
													<td>Email
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.email">
													</td>
												</tr>
												<tr>
													<td>Whatsapp
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.wanum">
													</td>
												</tr>
												<tr>
													<td>Blackberry PIN
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.blackberrypin">
													</td>
												</tr>
												<tr>
													<td>Address
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.address">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Salesman</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Contact
													</td>
													<td><input type="text" data-attribute="employeesid" data-lookup="employees|orgcode" data-childrentable="employees" data-childrencolumn="employeesid">
													</td>
												</tr>
												<tr>
													<td>Full Name
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.fullname">
													</td>
												</tr>
												<tr>
													<td>Title
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.title">
													</td>
												</tr>
												<tr>
													<td>Departemen
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.department">
													</td>
												</tr>
												<tr>
													<td>Tel.
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.primaryphone">
													</td>
												</tr>
												<tr>
													<td>Email
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.primaryemail">
													</td>
												</tr>
												<tr>
													<td>Whatsapp
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.wanum">
													</td>
												</tr>
												<tr>
													<td>Blackberry PIN
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.blackberrypin">
													</td>
												</tr>
												<tr>
													<td>Address
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.address">
													</td>
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
														<td>SO Number</td>
														<td><input type="text" data-attribute="sonum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-lookup="customer|orgcode" data-lookupaction="y"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Price</td>
											</tr>
										</table>
										<div class="row eatfix col-md-12">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Total before Tax
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotal">
														</td>
													</tr>
													<tr>
														<td>Tax
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax">
														</td>
													</tr>
													<tr>
														<td>Total
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalprice">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Discount (%)
														</td>
														<td><input type="text" data-type="decimal" data-attribute="discountpercentage">
														</td>
													</tr>
													<tr>
														<td>Discount
														</td>
														<td><input type="text" data-type="decimal" data-attribute="discountprice">
														</td>
													</tr>
													
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Sub Total after Discount</td>
														<td><input type="text" data-type="decimal" data-attribute="discountedtotalprofit" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Tax after Discount</td>
														<td><input type="text" data-type="decimal" data-attribute="discountedtotaltax" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Grand Total</td>
														<td><input type="text" data-type="decimal" data-attribute="discountedtotalprice" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Paid
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="paidprice">
														</td>
													</tr>
													<tr>
														<td>Unpaid Cost
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="unpaidprice">
														</td>
													</tr>
													<tr>
														<td>Payment Method</td>
														<td style="font-size:9pt; font-family:calibri"><br><input type='radio' class='nonpersistent' name = 'paymenttype' value="kredit">  Debt <br>
															<input type='radio' class='nonpersistent' name = 'paymenttype' value ="cashonbank"> Cash Bank Transfer <br>
															<input type='radio' class='nonpersistent' name = 'paymenttype' value ="cashonhand"> Cash on Hand
															<br>
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Items / Services
												</td>
											</tr>
										</table>
									</div>
									<div class="row eatfix">
										<div class="col-md-2">
											<div class="addrow" id="addSalesOrderLines"></div>
										</div>
										<div class="col-md-2">
											<div class="addrow" id="addWorkorderLines"></div>
										</div>

									</div>
									<div id="subform-salesorder-solines" class="subform" data-child="soline" data-table="mxtable_solines_child">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Line
														</td>
														<td><input type="text" data-childfld="child" data-attribute="solinenum" data-key="value" data-input="required" data-type="integer">
														</td>
													</tr>
													<tr>
														<td>Line Type
														</td>
														<td><input type="text" data-childfld="child" data-attribute="linetype" data-input="required" data-lookup="linetype" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Item
														</td>
														<td><input type="text" data-childfld="child" data-attribute="itemnum"  data-lookup="itemsellable|orgcode" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Service Item</td>
														<td><input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Description </td>
														<td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description">
														</td>
													</tr>
													<tr>
														<td>Num. Part </td>
														<td><input type="text" data-childfld="child" data-attribute="partnum" data-lookup="itempartnum|itemnum">
														</td>
													</tr>
													<tr>
														<td>Specification</td>
														<td><input type="text" data-childfld="child" data-attribute="itemnum.spectext" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Condition Type </td>
														<td><input type="text" data-childfld="child" data-attribute="conditiontype">
														</td>
													</tr>
													<tr>
														<td>From Warehouse</td>
														<td><input type="text" data-childfld="child" data-attribute="warehouse">
														</td>
													</tr>
													<tr>
														<td>From Rack/Bin</td>
														<td><input type="text" data-childfld="child" data-attribute="racknum">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Quantity
														</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="sellqty" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Order Unit
														</td>
														<td><input type="text" data-childfld="child" data-attribute="orderunit" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Unit Price
														</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice">
														<input type="hidden" data-childfld="child" data-type="decimal" data-attribute="commission">
														<div data-attribute='remarks' style = "display:none; padding:3px; background: ##FFB8B8"></div>
														</td>
													</tr>
													<tr>
														<td>Total Price
														</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="lineprice">
														</td>
													</tr>
													<tr>
														<td>Tax
														</td>
														<td><input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Sales Tax</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="taxprice"></td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Cost Type
														</td>
														<td><input type="text" data-childfld="child" data-attribute="costtype" data-lookup="costtype" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Price Code</td>
														<td><input type="text" data-childfld="child" data-attribute="pricecode" data-lookup="invprice|itemnum,warehouse,conditiontype" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Work Order</td>
														<td><input type="text" data-childfld="child" data-attribute="wonum" data-lookup="workorder" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Debit Account Code</td>
														<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="chartofaccount">
														</td>
													</tr>
													<tr>
														<td>Credit Account Code</td>
														<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="chartofaccount">
														</td>
													</tr>
													<tr>
														<td>Entered By</td>
														<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
													</tr>
													<tr>
														<td>Entered Date</td>
														<td><input type="text" data-childfld="child" data-type="date" data-attribute="enterdate" data-input="readonly"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_solines_child" class="display" data-subform="subform-salesorder-solines"></table>
												<div id="mxtable_solines_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Line
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="solinenum" data-input="required" data-type="integer">
																	</td>
																</tr>
																<tr>
																	<td>Line Type
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="linetype" data-input="required" data-lookup="linetype" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Item
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="itemsellable|orgcode" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Service Item</td>
																	<td><input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Description </td>
																	<td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description">
																	</td>
																</tr>
																<tr>
																	<td>Condition Type </td>
																	<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Part Number </td>
																	<td><input type="text" data-childfld="child" data-attribute="partnum" data-lookup="itempartnum|itemnum">
																	</td>
																</tr>
																<tr>
																	<td>From Warehouse</td>
																	<td><input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse">
																	</td>
																</tr>
																<tr>
																	<td>From Rack/Bin</td>
																	<td><input type="text" data-childfld="child" data-attribute="racknum" data-lookup="racknum|warehouse">
																	</td>
																</tr>
																<tr>
																	<td>Sales Quotation</td>
																	<td><input type="text" data-childfld="child" data-attribute="salesquotenum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Sales Quotation Line</td>
																	<td><input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-8">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Price</div>
																	</td>
																</tr>
															</table>
															<div class="row eatfix">
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Quantity
																			</td>
																			<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="sellqty" data-lookupaction="y">
																			</td>
																		</tr>
																		<tr>
																			<td>Order Unit
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="orderunit" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Price
																			</td>
																			<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice">
																			<input type="hidden" data-childfld="child" data-type="decimal" data-attribute="commission">
																			<div data-attribute='remarks' style = "display:none; padding:3px; background: #FFF6CC; border-radius:10px"></div>
																			</td>
																		</tr>
																		<tr>
																			<td>Total Price
																			</td>
																			<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="lineprice">
																			</td>
																		</tr>
																		<tr>
																			<td>Tax
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax" data-lookupaction="y">
																			</td>
																		</tr>
																		<tr>
																			<td>Sales Tax</td>
																			<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="taxprice" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		
																		<tr>
																			<td>Cost Type
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="costtype" data-lookup="costtype" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Price Code</td>
																			<td><input type="text" data-childfld="child" data-attribute="pricecode" data-lookup="invprice|itemnum,warehouse,conditiontype" data-lookupaction="y">
																			</td>
																		</tr>
																		<tr>
																			<td>Work Order</td>
																			<td><input type="text" data-childfld="child" data-attribute="wonum" data-lookup="workorder" data-lookupaction="y">
																			</td>
																		</tr>
																		<tr>
																			<td>Debit Account Code</td>
																			<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount">
																			</td>
																		</tr>
																		<tr>
																			<td>Credit Account Code</td>
																			<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount">
																			</td>
																		</tr>
																		<tr>
																			<td>Entered By</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
																		</tr>
																		<tr>
																			<td>Entered Date</td>
																			<td><input type="text" data-childfld="child" data-type="date" data-attribute="enterdate" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Shipment Date</td>
																			<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="sentstatus"></td>
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
									
									<div class="row eatfix">
										<div class="subtab">
											<ul class="maxes02">
												<li class="maxes02_1"><a href="#maxes_woplandistribution">Planning Distribution</a></li>
												<li class="maxes02_2"><a href="#maxes_woactualdistribution">Actual Distribution</a></li>
											</ul>
										</div>
										<div class="maxes04sub">
											<div id="maxes_woplandistribution" class="maxes02_1">
												
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_solinewo_child" class="display"  data-child="solinewo"></table>
															<div id="mxtable_solinewo_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Line
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="sodistlinenum" data-input="required" data-type="integer">
																				</td>
																			</tr>
																			<tr>
																				<td>For SO Line Num.
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="solinenum" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Budget</td>
																				<td><input type="text" data-childfld="child" data-attribute="budgetnum" data-lookup="budget"> </td>
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
																		<table class="tableDetailRow">
																			<tr>
																				<td>Description
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="description" >
																				</td>
																			</tr>
																			<tr>
																				<td>Remarks
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="remarks" >
																				</td>
																			</tr>
																			<tr>
																				<td>Quantity
																				</td>
																				<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="quantity" data-lookupaction="y" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Order Unit
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="orderunit" data-input="readonly">
																				</td>
																			</tr>
																			
																			
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Unit Cost
																				</td>
																				<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Line Cost
																				</td>
																				<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="linecost" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Unit Price
																				</td>
																				<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Line Price
																				</td>
																				<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="lineprice" data-input="readonly">
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
											<div id="maxes_woactualdistribution" class="maxes03">
												<div class="row eatfix" id ='hist_tbl'>
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_workorder_child" class="display" data-child="workorder"></table>
															<div id="mxtable_workorder_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Work Order</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="wonum" data-key="value" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="description" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Work Type</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="worktype" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Work Classification</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="workclass" data-input="readonly">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Target Start</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="targstartdate" data-type= "date" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Target Completed</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="targcompdate" data-type= "date" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Actual Start</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="actstartdate" data-type= "date" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Actual Finish</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="actfinishdate" data-type= "date" data-input="readonly">
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
								
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>SO Number</td>
														<td><input type="text" data-attribute="sonum" data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
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
									<div id="subform-soterm" class="subform" data-child="soterm" data-table="mxtable_soterm_child">
										<div class="row eatfix">
											<div class="col-md-12">
												<table class="tbldata">
													<tr>
														<td>Num.</td>
														<td><input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-type="integer"></td>
													</tr>
													<tr>
														<td>Code</td>
														<td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
														</td>
													</tr>
													<tr>
														<td>Remarks</td>
														<td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_soterm_child" class="display" data-subform="subform-soterm"></table>
												<div id="mxtable_soterm_child_detail" class="mxtable_detail">
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td colspan=2>
																	<div class="maxes_sct_tbl3">DetailSyarat/Kondisi</div>
																</td>
															</tr>
															<tr>
																<td>Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="termlinenum"  data-type="integer" readonly></td>
															</tr>
															<tr>
																<td>Code</td>
																<td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
																</td>
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
									<div class="row eatfix">
										<div class="maxes_sct_tbl">Syarat Kondisi</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addConditions"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-soterm2" class="subform" data-child="soterm2" data-table="mxtable_soterm2_child">
											<div class="row eatfix">
												<div class="col-md-12">
													<table class="tbldata">
														<tr>
															<td>Num.</td>
															<td><input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-key="value" data-type="integer"></td>
														</tr>
														<tr>
															<td>Code</td>
															<td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
															</td>
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
												<table id="mxtable_soterm2_child" class="display" data-subform="subform-soterm2"></table>
												<div id="mxtable_soterm2_child_detail" class="mxtable_detail">
													<div class="row eatfix">
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
																	<td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
																	</td>
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
								</div>
								
								<div id="maxes02_1_7" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>SO Number</td>
														<td><input type="text" data-attribute="sonum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-lookup="customer|orgcode" data-lookupaction="y"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="maxes_sct_tbl">Price</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Total after Discount
													</td>
													<td><input type="text" data-type="decimal" data-attribute="discountedtotalprice" data-input="readonly" >
													</td>
												</tr>
												<tr>
													<td>Paid
													</td>
													<td><input type="text" data-type="decimal" data-attribute="paidprice" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Unpaid Cost
													</td>
													<td><input type="text" data-type="decimal" data-attribute="unpaidprice" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="maxes_sct_tbl">Paid</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="addrow" id="addPaymentLines"></div>
										</div>
									</div>
									<div class="col-md-12">
										<div id="subform-salesorders-paymentlines" class="subform" data-child="sopayment" data-table="mxtable_paymentlines_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Payment Line
															</td>
															<td><input type="text" data-childfld="child" data-attribute="paymentlinenum" data-type="integer" data-key="value" data-input="required">
															</td>
														</tr>
														<tr>
															<td>Payment Number</td>
															<td><input type="text" data-childfld="child" data-attribute="paymentnum" data-input="required"></td>
														</tr>
														<tr>
															<td>Payment Date
															</td>
															<td><input type="text" data-childfld="child" data-attribute="paymentdate" data-type="date" data-input="required">
															</td>
														</tr>
														<tr>
															<td>Payment Through</td>
															<td><input type="text" data-childfld="child" data-attribute="paymentvia"></td>
														</tr>
														<tr>
															<td>Remarks</td>
															<td><input type="text" data-childfld="child" data-attribute="remarks">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Paid</td>
															<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="paidprice" data-input="required"></td>
														</tr>
														<tr>
															<td>Refund ke Customer?</td>
															<td><input type="checkbox" data-childfld="child" data-attribute="refund"></td>
														</tr>
														<tr>
															<td>Invoice Number</td>
															<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="invoicenum"></td>
														</tr>
														<tr>
															<td>Invoice Date</td>
															<td><input type="text" data-type="date" data-childfld="child" data-input="readonly" data-attribute="invoicedate"></td>
														</tr>
														<tr>
															<td>Invoice Description</td>
															<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="description"></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_paymentlines_child" class="display" data-subform="subform-salesorders-paymentlines"></table>
												<div id="mxtable_paymentlines_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Line Num.
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="paymentlinenum" data-type="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Payment Number</td>
																	<td><input type="text" data-childfld="child" data-attribute="paymentnum" data-input="required"></td>
																</tr>
																<tr>
																	<td>Payment Date
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="paymentdate" data-type="date" data-input="required">
																	</td>
																</tr>
																<tr>
																	<td>Payment Through</td>
																	<td><input type="text" data-childfld="child" data-attribute="paymentvia"></td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td><input type="text" data-childfld="child" data-attribute="remarks">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Refund ke Customer?</td>
																	<td><input type="checkbox" data-childfld="child" data-attribute="refund">
																	</td>
																</tr>
																<tr>
																	<td>Paid
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="paidprice">
																	</td>
																</tr>
																<tr>
																	<td>Invoice Number</td>
																	<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="invoicenum"></td>
																</tr>
																<tr>
																	<td>Invoice Date</td>
																	<td><input type="text" data-type="date" data-input="readonly" data-childfld="child" data-attribute="invoicedate"></td>
																</tr>
																<tr>
																	<td>Invoice Description</td>
																	<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="description">
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
								<div id="maxes02_1_8" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>SO Number</td>
														<td><input type="text" data-attribute="sonum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-lookup="customer|orgcode" data-lookupaction="y"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="subtab">
											<ul class="maxes02">
												<li class="maxes02_1"><a href="#maxes02_1_7_1"> Transactions</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_7_2">Cost of Services</a></li>
											</ul>
										</div>
										<div class="maxes04sub">
											<div id="maxes02_1_7_1" class="maxes0_1">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Items
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="addrow" id="selectitemtosend"></div>
														<div class="addrow" id="selectitemtoreturn"></div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_matsaletrans_child" class="display" data-child="matsaletrans"></table>
															<div id="mxtable_matsaletrans_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-3">
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Item</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>SO Number</td>
																				<td><input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly" ></td>
																			</tr>
																			<tr>
																				<td>Num.</td>
																				<td><input type="text" data-childfld="child" data-attribute="solinenum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Customer Code</td>
																				<td><input type="text" data-childfld="child" data-attribute="customercode" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Item</td>
																				<td><input type="text" data-childfld="child" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td><input type="text" data-childfld="child" data-attribute="itemnum.description" data-childrentable="item" data-childrencolumn="itemnum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>FIFO/LIFO Date Masuk Warehouse</td>
																				<td><input type="text" data-childfld="child" data-attribute="costdate" data-type="date" data-input="readonly"></td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Origin</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			
																			<tr>
																			<tr>
																				<td>From Warehouse</td>
																				<td><input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly" ></td>
																			</tr>
																			<tr>
																				<td>From Rack/Bin</td>
																				<td><input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Issue Type/Cost</td>
																				<td><input type="text" data-childfld="child" data-attribute="issuetype" data-input="readonly" ></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-3">
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Quantity</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Quantity</td>
																				<td><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
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
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Return To Warehouse</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Warehouse</td>
																				<td><input type="text" data-childfld="child" data-attribute="towarehouse" data-input="readonly" ></td>
																			</tr>
																			<tr>
																				<td>Rack</td>
																				<td><input type="text" data-childfld="child" data-attribute="torack" data-input="readonly"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-3">
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Gross Price</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Unit Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Total Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="lineprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Unit Profit</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprofit" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Line Profit</td>
																				<td><input type="text" data-childfld="child" data-attribute="lineprofit" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Courier Courier Tracking Num.</td>
																				<td><input type="text" data-childfld="child" data-attribute="trackingnum" data-lookup="soshipment|sonum"></td>
																			</tr>
																			
																		</table>
																		
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Transaction Type</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Transaction Type</td>
																				<td><input type="text" data-childfld="child" data-attribute="transtype" data-input="readonly" style="color:blue, font-weight:bold;"></td>
																			</tr>
																			<tr>
																				<td>Actual Date</td>
																				<td><input type="text" data-childfld="child" data-attribute="actualdate"  data-type="date"></td>
																			</tr>
																			<tr>
																				<td>Entered Date</td>
																				<td><input type="text" data-childfld="child" data-attribute="transdate" data-input="readonly" data-type="date"></td>
																			</tr>
																			<tr>
																				<td>Entered By</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly" ></td>
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
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Nett Price & Profit</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Unit Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitpricediscount" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Total Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="linepricediscount" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Unit Profit</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprofitdiscount" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Line Profit</td>
																				<td><input type="text" data-childfld="child" data-attribute="lineprofitdiscount" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Price Category</td>
																				<td><input type="text" data-childfld="child" data-attribute="pricecode" data-input="readonly"></td>
																			</tr>
																			
																		</table>
																	</div>
																</div>
																<input type='hidden' data-childfld='child' data-attribute='matsaletransid' data-input='readonly'>
															</div>
														</div>

													</div>
												</div>

												<div class="row eatfix">
													<div class="col-md-12 maxes_sct_tbl4">Shipping</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_soshipment_child" class="display" data-child="soshipment"></table>
															<div id="mxtable_soshipment_child_detail" class="mxtable_detail">
																<div class="row eatfix">

																	<!-- Kolom 1 : Shipment Core -->
																	<div class="col-md-3">
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Shipment Core</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Line Num.</td>
																				<td><input type="text" data-childfld="child" data-attribute="shiplinenum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Related PO</td>
																				<td><input type="text" data-childfld="child" data-attribute="ponum" data-lookup="po" data-childrentable="po" data-childrencolumn="ponum"></td>
																			</tr>
																			<tr>
																				<td>Courier Name</td>
																				<td><input type="text" data-childfld="child" data-attribute="couriername"></td>
																			</tr>
																			<tr>
																				<td>Tracking Num</td>
																				<td><input type="text" data-childfld="child" data-attribute="trackingnum"></td>
																			</tr>
																		</table>
																	</div>

																	<!-- Kolom 2 : Tracking & Schedule -->
																	<div class="col-md-3">
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Tracking & Schedule</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Shiping Date</td>
																				<td><input type="text" data-childfld="child" data-attribute="shippingdate" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Delivered Date</td>
																				<td><input type="text" data-childfld="child" data-attribute="delivereddate" data-type="date"></td>
																			</tr>
																			<tr>
																				<td>Shiping Cost</td>
																				<td><input type="text" data-childfld="child" data-attribute="shippingcost" data-input="readonly" data-crossvalue="ponum.totalbasecost" data-type="decimal"></td>
																			</tr>
																		</table>
																	</div>

																	<!-- Kolom 3 : Charges Info -->
																	<div class="col-md-3">
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Charges Info</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Address</td>
																				<td><input type="text" data-childfld="child" data-attribute="address"></td>
																			</tr>
																			<tr>
																				<td>Phone</td>
																				<td><input type="text" data-childfld="child" data-attribute="phone"></td>
																			</tr>
																			<tr>
																				<td>Email</td>
																				<td><input type="text" data-childfld="child" data-attribute="email"></td>
																			</tr>
																			<tr>
																				<td>Remarks</td>
																				<td><input type="text" data-childfld="child" data-attribute="remarks"></td>
																			</tr>
																		</table>
																	</div>

																	<!-- Kolom 4 : Accounting -->
																	<div class="col-md-3">
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Accounting</div>
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>GL Debit Account</td>
																				<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount"></td>
																			</tr>
																			<tr>
																				<td>GL Credit Account</td>
																				<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
																			</tr>
																		</table>
																	</div>

																</div>	
															</div>
														</div>	
													</div>	
												</div>	
											</div>
											<div id="maxes02_1_7_2" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Services
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="addrow" id="selectservicetodeliver" data-table="mxtable_servsaletrans_child"></div>
														<div class="addrow" id="selectservicetoreturn" data-table="mxtable_servsaletrans_child"></div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_servsaletrans_child" class="display" data-child="servsaletrans"></table>
															<div id="mxtable_servsaletrans_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>SO Number</td>
																				<td><input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Num.</td>
																				<td><input type="text" data-childfld="child" data-attribute="solinenum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Customer Code</td>
																				<td><input type="text" data-childfld="child" data-attribute="customercode" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Actual Date</td>
																				<td><input type="text" data-childfld="child" data-type="date" data-attribute="actualdate"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Unit Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Quantity</td>
																				<td><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Total Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="lineprice" data-input="readonly" data-type="decimal"></td>
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
																				<td>Entered By</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Entered Date</td>
																				<td><input type="text" data-childfld="child" data-type="date" data-input='readonly' data-attribute="enterdate"></td>
																			</tr>
																			<tr>
																				<td>Remarks</td>
																				<td><input type="text" data-childfld="child" data-attribute="remarks" ></td>
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
								
								
								<div id="maxes02_1_9" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>SO Number</td>
														<td><input type="text" data-attribute="sonum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-lookup="customer|orgcode" data-lookupaction="y"></td>
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
	</body>
</html>