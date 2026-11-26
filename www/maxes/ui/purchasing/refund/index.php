<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="refund" data-appkeynum = "refundnum">
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
														<table id="mxtable_refund" class="display"></table>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Num. Refund
														</td>
														<td><input type="text" data-attribute="refundnum" data-type="upper" data-input="required" data-key="value">
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
													<td class="maxes_sct_tbl">Purchasing/Sales Refund 
													</td>
												</tr>
											</table>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
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
											</table>	
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Purchasing
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>PO
													</td>
													<td><input type="text" data-attribute="ponum" data-lookup="invoicetranspo" data-lookupaction='y' data-childrentable="po" data-childrencolumn = "ponum">
													</td>
												</tr>
												<tr>
													<td>Description
													</td>
													<td><input type="text" data-child="childfld" data-attribute="ponum.description" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Order Date
													</td>
													<td><input type="text" data-child="childfld" data-attribute="ponum.orderdate" data-input="readonly" data-type="date">
													</td>
												</tr>
												<tr>
													<td>Total 
													</td>
													<td><input type="text" data-child="childfld" data-attribute="ponum.pretaxtotal" data-input="readonly" data-type="decimal">
													</td>
												</tr>
												
													
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Sales
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<table class="tbldata">
													<tr>
														<td>SO
														</td>
														<td><input type="text"  data-attribute="sonum" data-lookup="invoicetransso" data-lookupaction='y'  data-childrentable="so" data-childrencolumn = "sonum">
														</td>
													</tr>
													
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-child="childfld" data-input="readonly" data-attribute="sonum.description">
														</td>
													</tr>
													<tr>
														<td>Order Date
														</td>
														<td><input type="text" data-child="childfld" data-input="readonly" data-attribute="sonum.orderdate">
														</td>
													</tr>
													<tr>
														<td>Total
														</td>
														<td><input type="text" data-child="childfld" data-input="readonly" data-type="decimal" data-attribute="sonum.discountedtotalprofit">
														</td>
													</tr>
													
												</table>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Lainnya
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<table class="tbldata">
													<tr>
														<td>Billing Num.
														</td>
														<td><input type="text"  data-attribute="billingnum" data-lookup="invoicetransbilling" data-lookupaction='y'>
														</td>
													</tr>
													<tr>
														<td>Transaction Num.
														</td>
														<td><input type="text"  data-attribute="transnum" data-lookup="invoicetranstransnum" data-lookupaction='y'>
														</td>
													</tr>
													<tr>
														<td>Other Payment
														</td>
														<td><input type="text" data-attribute="invoicenum" data-lookup="invoicetransadv" data-lookupaction='y'>
														</td>
													</tr>
													<tr>
														<td>Refunded By</td>
														<td><input type="text" data-input="required" data-attribute="refundby" data-lookup="employees">
														</td>
													</tr>
													<tr>
														<td>Date Refund</td>
														<td><input type="text" data-input="required" data-attribute="actualdate" data-type="date">
														</td>
													</tr>
												</table>
											</table>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<table class="tbldata">
													<tr>
														<td>Company
														</td>
														<td><input type="text"  data-attribute="orgcode" data-input='readonly'>
														</td>
													</tr>
													<tr>
														<td>Site
														</td>
														<td><input type="text"  data-attribute="sitecode" data-input='readonly'>
														</td>
													</tr>
													
												</table>
											</table>
										</div>
									</div>
									
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Refund Barang & Jasa</td>
											</tr>
										</table>
									</div>
									<div class="row eatfix">
										<div class="col-md-2">
											<div class="addrow" id="selectitems"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_refundline_child" class="display" data-child="refundline">
												</table>
												<div id="mxtable_refundline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Detail
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="refundlinenum" data-input="required" data-key="value" data-type="integer"></td>
																</tr>
																<tr>
																	<td>Type </td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="linetype" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>Item</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																</tr>
																
															</table>
														</div>
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Detail
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Invoice</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>Num. Baris Invoice</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicelinenum" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>PO</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="ponum" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>SO</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>WO</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>Bill. No</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="billingnum" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>Trans.No</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="transnum" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>Trans.ID</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="transid" data-input="readonly" ></td>
																</tr>
																<tr>
																	<td>Payment Type</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="paymenttype" data-input="readonly" ></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Harga
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Quantity</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Condition Type</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="itemcondition|itemnum"></td>
																</tr>
																<tr>
																	<td>Harga</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Total</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Unit Profit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="unitprofit" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Line Profit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="lineprofit" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Discount(%)</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="discount" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Discount</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="discountprice" data-input="readonly" data-type="decimal"></td>
																</tr>
															</table>
														</div>
													</div>
													<div class="row eatfix">
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Diambil Dari
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>From Warehouse</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse"></td>
																</tr>
																<tr>
																	<td>From Rack/Bin</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="fromrack" data-lookup="racknum"></td>
																</tr>
																<tr>
																	<td>Issue Type</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="costtype" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>FIFO/LIFO Date</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="costdate" data-input="readonly" data-type="date"></td>
																</tr>
																
															</table>
														</div>
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																Dikembalikan Ke
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>To Warehouse</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="towarehouse" data-lookup="warehouse"></td>
																</tr>
																<tr>
																	<td>To Rack</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="torack" data-lookup="rack"></td>
																</tr>
															</table>
														</div>
														
														<div class="col-md-4">
															<div class="col-md-12 maxes_sct_tbl3">
																GL
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>GL Debit </td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="readonly" data-lookup="chartofaccounts"></td>
																</tr>
																<tr>
																	<td>GL Kredit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="readonly" data-lookup="chartofaccounts"></td>
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
													<td class="maxes_sct_tbl">Penggantian Barang & Jasa</td>
												</tr>
											</table>
										</div>
									</div>
									
									
									<div class="row eatfix">
										<div class="col-md-2">
											<div class="addrow" id="selectitems2"></div>
										</div>
										<div class="col-md-2">
											<div class="addrow" id="selectitems3"></div>
										</div>
									</div>
									
									<div class="row eatfix">
									<div class="col-md-12">
									<div id="subform-refundline2" class="subform" data-child="refundline2" data-table="mxtable_refundline2_child">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Line
														</td>
														<td><input type="text" data-childfld="child" data-attribute="refundlinenum" data-key="value" data-input="required" data-type="integer">
														</td>
													</tr>
													<tr>
														<td>Line Type
														</td>
														<td><input type="text" data-childfld="child" data-attribute="linetype" value='services' data-input="readonly">
														</td>
													</tr>
													
													<tr>
														<td>Description </td>
														<td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description">
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
														<td>Measurement Unit
														</td>
														<td><input type="text" data-childfld="child" data-attribute="issueunit" data-input="measureunit" data-lookup='measureunit'>
														</td>
													</tr>
													<tr>
														<td>Harga per Unit
														</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice">
														</td>
													</tr>
													<tr>
														<td>Total Price
														</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="lineprice">
														</td>
													</tr>
												
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													
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
									</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_refundline2_child" class="display" data-child="refundline2" data-subform='subform-refundline2'>
												</table>
												<div id="mxtable_refundline2_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="refundlinenum" data-key="value" data-input="required" data-type="integer">
																	</td>
																</tr>
																<tr>
																	<td>Refund untuk Baris Ke
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="torefundlinenum" data-type="integer" data-lookup="refundline|refundnum" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Type
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="linetype" data-input="readonly" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Item
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="itemsellable|orgcode" data-lookupaction="y">
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
																	<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="conditiontype">
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
															<table class="tableDetailRow">
																<tr>
																	<td>Quantity
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="sellqty" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Measurement Unit
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Harga per Unit
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice">
																	</td>
																</tr>
																<tr>
																	<td>Harga Discount per Unit
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitpricediscount">
																	</td>
																</tr>
																<tr>
																	<td>Total Price
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="lineprice">
																	</td>
																</tr>
																<tr>
																	<td>Total Price Discount
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="linepricediscount">
																	</td>
																</tr>
																<tr>
																	<td>Tax 
																	</td>
																	<td><input type="text" data-childfld="child"  data-attribute="taxcode" data-lookup="taxpurchasing" data-lookupaction="y">
																	</td>
																</tr>
																
																<tr>
																	<td>Total Tax
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="taxprice">
																	</td>
																</tr>
																<tr>
																	<td>Total Tax Stlh Discount
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="taxpricediscount">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Selisih Harga per Unit
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="varunitprice" >
																	</td>
																</tr>
																<tr>
																	<td>Total Selisih Harga
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="varlineprice" data-input='readonly'>
																	</td>
																</tr>
																<tr>
																	<td>Cost Type
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="costtype" data-lookup="costtype" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Price Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="pricecode" data-lookup="invprice|itemnum,warehouse,conditiontype" data-lookupaction="y">
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
													<td class="maxes_sct_tbl">Total Refund 
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Refund Item
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<table class="tbldata">
													
													<tr>
														<td>
															Total
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="total">
														</td>
													</tr>
												
												</table>
											</table>
										
										</div>
										<div class="col-md-6">
											
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Total Pengganti Item/Jasa
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<table class="tbldata">
													
													<tr>
														<td>
															Total
														</td>
														<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalrefund">
														</td>
													</tr>
												
												</table>
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