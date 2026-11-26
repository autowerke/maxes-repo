<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="billing" data-appkeynum = "billingnum">
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
								<li class="maxes02_2"><a href="#maxes02_1_3">Payment</a></li>
							</ul>
							<div class="maxes04 shadow">
								<div id="maxes02_1_1" class="tab maxes02_1">
									<div id="maxes02_1_1" class="tab maxes02_1">
										<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											<div class='eat_apptitle'></div>
											<div class="addrow" id="downloadwebdata"></div>
										</div>
										
										<div class="row eatfix">
											<div class="col-md-12">
												
												<div class="page-header-list">
												
													<div class="tablelist">
														<table id="mxtable_billing" class="display"></table>
														<div>
														</div>
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
										<div class="col-md-2">
											<div id="qrcoderegion">
												<div id='qrcode'></div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Billing Num.
														</td>
														<td><input type="text" data-attribute="billingnum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Salesman
														</td>
														<td><input type="text" data-attribute="personid" data-input="required" data-lookup="employees" data-childrentable="employees" data-childrencolumn="personid"> - 
															<input type="text" data-attribute="personid.fullname" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description">
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
													<td class="maxes_sct_tbl">Distributor  
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Customer
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Customer
													</td>
													<td><input type="text" data-attribute="customercode" data-lookup="customer" data-childrentable="customer" data-childrencolumn="customercode"> - <input type="text" data-attribute="customercode.name" data-input="readonly" style="width:200px;">
													</td>
												</tr>
												<tr>
													<td>Status
													</td>
													<td><input type="text" data-attribute="status" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Status Date
													</td>
													<td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Entered By
													</td>
													<td><input type="text" data-attribute="enterby" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Entered Date
													</td>
													<td><input type="text" data-attribute="enterdate" data-input="readonly" data-type="date">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Company
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<table class="tbldata">
													
													<tr>
														<td>Company
														</td>
														<td><input type="text" data-input="readonly" data-attribute="orgcode">
														</td>
													</tr>
													<tr>
														<td>Site
														</td>
														<td><input type="text" data-input="readonly" data-attribute="sitecode">
														</td>
													</tr>
												</table>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Price
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<table class="tbldata">
												
													<tr>
														<td>Sub Total</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="subtotalprice">
														</td>
													</tr>
													<tr>
														<td>Discount 1st & 2nd</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="bldiscountprice"></td>
													</tr>
													<tr>
														<td>Total Tax</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax"></td>
													</tr>
													<tr>
														<td>Invoice Discount</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="discountprice"></td>
													</tr>
													
													<tr>
														<td>
															Discount Sub Total 
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="subtotalpricediscount">
														</td>
													</tr>
													<tr>
														<td>Total Tax after Discount</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax2"></td>
													</tr>
													
													<tr>
														<td>
															Total Price
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalprice">
														</td>
													</tr>
													
													<tr>
														<td>
															Paid
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="paidprice">
														</td>
													</tr>
													<tr>
														<td>
															Unpaid
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="unpaidprice">
														</td>
													</tr>
												</table>
											</table>
										</div>
									</div>
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Detail</td>
											</tr>
										</table>
									</div>
									<div class="row eatfix">
										<div class="col-md-2">
											<div class="addrow" id="selectitems"></div>
										</div>
										<div class="col-md-2">
											<div class="addrow" id="selectitems2"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_billingline_child" class="display" data-child="billingline">
												</table>
												<div id="mxtable_billingline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Detail
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="billinglinenum" data-input="required" data-key="value" data-type="integer"></td>
																</tr>
																<tr>
																	<td>Item</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="itemnum"></td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
																</tr>
																<tr>
																	<td>Specification</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="spectext" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Sold Date</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="actualdate" data-type = "date"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Selling Price
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Unit Price</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="unitprice" data-input="required"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Quantity</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-input="required"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Measure Unit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="issueunit" data-input="readonly"  data-lookup="measureunit"></td>
																</tr>
																<tr>
																	<td>Total Price</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="lineprice" data-input="readonly"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="remarks"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Discount
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Discount #1</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="primarydiscount" data-input="readonly"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Discount #2</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="secondarydiscount" data-input="readonly"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Discount Price</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="discountprice" data-input="readonly"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Discount Price per Unit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="unitpricediscount" data-input="required"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Total Price Discount</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="linepricediscount" data-input="readonly"  data-type="decimal"></td>
																</tr>
															</table>
														</div>
													</div>
													<div class="row eatfix">
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Ship From
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>From Warehouse</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="fromwarehouse" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>From Rack/Bin</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="fromracknum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>WO</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Enter Date FIFO/LIFO</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="costdate" data-input="readonly" data-type="date" ></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Tax
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Tax Code</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="tax" data-lookup="taxpurchasing" data-lookupaction="y"></td>
																</tr>
																<tr>
																	<td>Tax Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="taxcost" data-input="readonly" data-type = "decimal"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Account Code
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Debit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount"></td>
																</tr>
																<tr>
																	<td>Credit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Terjual</td>
											</tr>
										</table>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_custusetrans_child" class="display" data-child="custusetrans">
												</table>
												<div id="mxtable_custusetrans_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Detail
															</div>
															<table class="tableDetailRow">
																
																<tr>
																	<td>Item</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="itemnum"></td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
																</tr>
																<tr>
																	<td>WO</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Sold Date</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="actualdate" data-type = "date"></td>
																</tr>
															</table>
														</div>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Billing Num
														</td>
														<td><input type="text" data-attribute="billingnum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Salesman
														</td>
														<td><input type="text" data-attribute="personid" data-input="required" data-lookup="employees" data-childrentable="employees" data-childrencolumn="personid"> - 
															<input type="text" data-attribute="personid.fullname" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description">
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
													<td class="maxes_sct_tbl">Payment</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												
													<tr>
														<td>
															Sub Total
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="subtotalprice">
														</td>
													</tr>
													<tr>
														<td>Discount 1st & 2nd</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="bldiscountprice" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Total Tax</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax"></td>
													</tr>
													<tr>
														<td>Invoice Discount</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="discountprice"></td>
													</tr>
													
													<tr>
														<td>
															Discount Sub Total 
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="subtotalpricediscount">
														</td>
													</tr>
													
												</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												
													<tr>
														<td>Total Tax after Discount</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax2"></td>
													</tr>
													
													<tr>
														<td>
															Total Price
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalprice">
														</td>
													</tr>
													
													<tr>
														<td>
															Paid
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="paidprice">
														</td>
													</tr>
													<tr>
														<td>
															Unpaid
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="unpaidprice">
														</td>
													</tr>
												</table>
										</div>
									</div>
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_billingpayment_child" class="display" data-child="billingpayment">
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
	</body>
</html>