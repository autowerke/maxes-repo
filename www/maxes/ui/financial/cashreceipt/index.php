<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="cashreceipt"  data-appkeynum = "cashreceiptnum">
		<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Cash Receiving</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_3">Balance</a></li>
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
													<table id="mxtable_cashreceipt" class="display"></table>
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
													<td>Line Num. Cash Receiving
													</td>
													<td><input type="text" data-attribute="cashreceiptnum" data-type="upper" data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description
													</td>
													<td><input type="text" data-attribute="description" >
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
											</table>
										</div>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Detail
												</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-3">
										<table class="tbldata">
											<tr>
												<td>Total Cash In
												</td>
												<td><input type="text" data-attribute="totalcashreceipt" data-type="decimal" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Tax Out
												</td>
												<td><input type="text" data-attribute="totaltaxreceipt" data-type="decimal" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Total Cash Out
												</td>
												<td><input type="text" data-attribute="totalcashout"  data-type="decimal"  data-input="readonly"> 
												</td>
											</tr>
											<tr>
												<td>Tax In
												</td>
												<td><input type="text" data-attribute="taxin" data-type="decimal"  data-input="readonly">
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-3">
										<table class="tbldata">
											<tr>
												<td>Financial Period
												</td>
												<td><input type="text" data-attribute="financialperiod">
												</td>
											</tr>
											<tr>
												<td>Opening Balance
												</td>
												<td><input type="text" data-attribute="openbalance" data-type="decimal">
												</td>
											</tr>
											<tr>
												<td><font color="blue">Cash In + Tax Out</font>
												</td>
												<td><input type="text" data-attribute="closebalancetax" data-type="decimal" data-input="readonly">
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-3">
										<table class="tbldata">
											<tr>
												<td>
													<font color="blue">Total Additional Cost</font>
												</td>
												<td><input type="text" data-input="readonly" data-attribute="additionalcost" data-type="decimal">
												</td>
											</tr>
											<tr>
												<td>
													<font color="blue">Total Cash in Bank</font>
												</td>
												<td><input type="text" data-input="readonly" data-attribute="totalcashinbank" data-type="decimal">
												</td>
											</tr>
											<tr>
												<td>
													<font color="blue">Variance Cost</font>
												</td>
												<td><input type="text" data-input="readonly" data-attribute="variancecost" data-type="decimal">
												</td>
											</tr>
											<tr>
												<td>
													<font color="red">Variance Cost - Non Allocated</font>
												</td>
												<td><input type="text" data-input="readonly" data-attribute="variancecost2" data-type="decimal">
												</td>
											</tr>
											<tr>
												<td>
													Entered Date
												</td>
												<td><input type="text" data-input="readonly" data-attribute="enterdate" data-type="date">
												</td>
											</tr>
											<tr>
												<td>
													Entered By
												</td>
												<td><input type="text" data-input="readonly" data-attribute="enterby">
												</td>
											</tr>
										</table>
										
									</div>
									<div class="col-md-3">
										<table class="tbldata">
											<tr>
												<td>
													Bank Cost
												</td>
												<td><input type="text" data-input="readonly" data-attribute="bankcost" data-type="decimal" >
												</td>
											</tr>
											<tr>
												<td>
													Paid Tax
												</td>
												<td><input type="text" data-input="readonly" data-attribute="paidlineprice" data-type="decimal" >
												</td>
											</tr>
											<tr>
												<td>Close Balance
												</td>
												<td><input type="text" data-attribute="closebalance" data-type="decimal" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Close Balance - UVC
												</td>
												<td><input type="text" data-attribute="closebalance2" data-type="decimal" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Company
												</td>
												<td><input type="text" data-input="readonly" data-attribute="orgcode" data-lookup="organization">
												</td>
											</tr>
											<tr>
												<td>Site
												</td>
												<td><input type="text" data-input="readonly" data-attribute="sitecode" data-lookup="site">
												</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="subtab">
										<ul class="maxes02">
											<li class="maxes02_1"><a href="#maxes02_1_2_1">Cash In</a></li>
											<li class="maxes02_2"><a href="#maxes02_1_2_2">Cash Out</a></li>
											<li class="maxes02_2"><a href="#maxes02_1_2_3">Cash in Bank</a></li>
											<li class="maxes02_2"><a href="#maxes02_1_2_4">Additional Cost</a></li>
										</ul>
										<div class="maxes04sub">
											<div id="maxes02_1_2_1" class="maxes02_1">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Cash In
												</div>
												<div class="row eatfix">
													<div class="col-md-2">
														<div class="addrow" id="importcashin"></div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_cashreceiptline_child" class="display" data-child="cashreceiptline">
															</table>
															<div id="mxtable_cashreceiptline_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Invoice</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum" data-input="readonly" data-key="value" data-type="integer"></td>
																			</tr>
																			<tr>
																				<td>Num.</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicelinenum" data-input="readonly" data-key="value" data-type="integer"></td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Debit Account Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct"  data-lookup="chartofaccount"></td>
																			</tr>
																			<tr>
																				<td>Credit Account Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Unit Price</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Quantity</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly"  data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Measurement Unit</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="saleunit" data-input="readonly" data-lookup="measureunit"></td>
																			</tr>
																			<tr>
																				<td>Total Price</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="lineprice" data-input="readonly"  data-type="decimal"></td>
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
																<td class="maxes_sct_tbl">Tax Out</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="tablelist">
														<table id="mxtable_taxreceipt_child" class="display" data-child="taxreceipt">
														</table>
														<div id="mxtable_taxreceipt_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Invoice</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum" data-input="readonly" data-key="value" data-type="integer"></td>
																		</tr>
																		<tr>
																			<td>Num.</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicelinenum" data-input="readonly" data-key="value" data-type="integer"></td>
																		</tr>
																		<tr>
																			<td>Billing Code</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="taxreceiptnum"  ></td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
																		</tr>
																		<tr>
																			<td>Debit Account Code</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct"  data-lookup="chartofaccount"></td>
																		</tr>
																		<tr>
																			<td>Credit Account Code</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Unit Price</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																		</tr>
																		<tr>
																			<td>Quantity</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
																		</tr>
																		<tr>
																			<td>Total Price</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="lineprice" data-input="readonly"  data-type="decimal"></td>
																		</tr>
																		<tr>
																			<td>Payment Schedule</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="scheddate"  data-type="date"></td>
																		</tr>
																		<tr>
																			<td>Transfered Date</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="statusdate" data-input="readonly"  data-type="date"></td>
																		</tr>
																		<tr>
																			<td>Tax Type</td>
																			<td colspan=2><input type="text" data-childfld="child" data-attribute="transtype" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="maxes02_1_2_2" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Cash Out
												</div>
												<div class="row eatfix">
													<div class="col-md-2">
														<div class="addrow" id="importcashout"></div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_cashoutline_child" class="display" data-child="cashoutline">
															</table>
															<div id="mxtable_cashoutline_child_detail" class="mxtable_detail">
																<div class="col-md-5">
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Invoice</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum" data-input="readonly" data-key="value" data-type="integer"></td>
																			</tr>
																			<tr>
																				<td>Num.</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicelinenum" data-input="readonly" data-key="value" data-type="integer"></td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Debit Account Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct"  data-lookup="chartofaccount"></td>
																			</tr>
																			<tr>
																				<td>Account Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Unit Cost</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Quantity</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly"  data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Measurement Unit</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="orderunit" data-input="readonly" data-lookup="measureunit"></td>
																			</tr>
																			<tr>
																				<td>Total Price</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly"  data-type="decimal"></td>
																			</tr>
																		</table>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12">
															<table width=100%>
																<tr>
																	<td class="maxes_sct_tbl">Tax In</td>
																</tr>
															</table>
														</div>
														<div class="row eatfix"></div>
														<div class="tablelist">
															<table id="mxtable_taxreceipt2_child" class="display" data-child="taxreceipt">
															</table>
															<div id="mxtable_taxreceipt2_child_detail" class="mxtable_detail">

																<div class="row eatfix">
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Invoice</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum" data-input="readonly" data-key="value" data-type="integer"></td>
																			</tr>
																			<tr>
																				<td>Invoice Line Num.</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicelinenum" data-input="readonly" data-key="value" data-type="integer"></td>
																			</tr>
																			<tr>
																				<td>Billing Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="taxreceiptnum" ></td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
																			</tr>
																			<tr>
																				<td>Debit Account Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct"  data-lookup="chartofaccount"></td>
																			</tr>
																			<tr>
																				<td>Account Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Unit Cost</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Quantity</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Total Cost</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="lineprice" data-input="readonly"  data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Payment Schedule</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="scheddate"  data-type="date"></td>
																			</tr>
																			<tr>
																				<td>Transfered Date</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="statusdate" data-input="readonly"  data-type="date"></td>
																			</tr>
																			<tr>
																				<td>Tax Type</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="transtype" data-input="readonly"></td>
																			</tr>
																		</table>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="maxes02_1_2_3" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Cash in Bank
												</div>
												<div class="row eatfix">  
													<div class="col-md-4">
													<div class="addrow" id="addcashinbank"></div>
												</div>
												
												</div>

												<div class="row eatfix">  
												<div id="subform-cashinbank" class="subform" data-child="cashinbank" data-table="mxtable_cashinbank_child">
													
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>Num.</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="cashinbanklinenum" data-input="required" data-key="value" data-type="integer"></td>
															</tr>
															<tr>
																<td>Description</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
															</tr>
															<tr>
																<td>Bank Notes</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="banknotes"></td>
															</tr>
															<tr>
																<td>Financial Period</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="financialperiod" ></td>
															</tr>
															<tr>
																<td>Customer</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="customercode" data-lookup="customer" data-childrentable="customer" data-childrencolumn="customercode"></td>
															</tr>
															<tr>
																<td>Customer Name</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="customercode.name" data-input="readonly"></td>
															</tr>
														</table>
													</div>
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>Total Amount</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="totaltransfer" data-type="decimal"></td>
															</tr>
															<tr>
																<td>Transfered Date</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="transferdate" data-type="date"></td>
															</tr>
															<tr>
																<td>Debit Account Code</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount"></td>
															</tr>
															<tr>
																<td>Account Code</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
															</tr>
														</table>
													</div>
												</div>
												<div class="page-header-list">
													<div class="tablelist">
														<table id="mxtable_cashinbank_child" class="display" data-subform="subform-cashinbank"></table>
														<div id="mxtable_cashinbank_child_detail" class="mxtable_detail">
															<div class="row eatfix">
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td>Num.</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="cashinbanklinenum" data-input="readonly" data-key="value" data-type="integer"></td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
																	</tr>
																	<tr>
																		<td>Bank Notes</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="banknotes"></td>
																	</tr>
																	<tr>
																		<td>Financial Period</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="financialperiod" ></td>
																	</tr>
																	<tr>
																		<td>Customer</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="customercode" data-lookup="customer" data-childrentable="customer" data-childrencolumn="customercode"></td>
																	</tr>
																	<tr>
																		<td>Customer Code</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="customercode.name" data-input="readonly"></td>
																	</tr>
																</table>
															</div>
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td>Total Amount</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="totaltransfer" data-type="decimal"></td>
																	</tr>
																	<tr>
																		<td>Transfered Date</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="transferdate" data-type="date"></td>
																	</tr>
																	<tr>
																		<td>Include Tax?</td>
																		<td colspan=2><input type="checkbox" data-childfld="child" data-attribute="includetax"></td>
																	</tr>
																	<tr>
																		<td>Debit Account Code</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount"></td>
																	</tr>
																	<tr>
																		<td>Account Code</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
																	</tr>
																</table>
															</div>
															</div>
															
														</div>
													</div>
												</div>
												
												</div>
												
												
											</div>
											<div id="maxes02_1_2_4" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Additional Cost
												</div>
												<div class="row eatfix">
													<div class="col-md-4">
														<div class="addrow" id="addcost"></div>
													</div>
												</div>
												<div class="row eatfix">
													<div id="subform-addcost" class="subform" data-child="cashrecaddcost" data-table="mxtable_cashrecaddcost_child">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Num.</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="cashrecaddcostlinenum" data-input="required" data-key="value" data-type="integer" ></td>
																	</tr>
																	<tr>
																		<td>Cash in Bank Line Num.</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="cashinbanklinenum" data-lookup="cashinbank|cashreceiptnum"></td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
																	</tr>
																	<tr>
																		<td>Num.tes</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="notes"></td>
																	</tr>
																	<tr>
																		<td>Financial Period</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="financialperiod" data-input="readonly" ></td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Invoice</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum" data-lookup="cashreceiptinvoice|cashreceiptnum" data-childrentable="invoice" data-childrencolumn="invoicenum"></td>
																	</tr>
																	<tr>
																		<td>Invoice Description</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum.description" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Quantity</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal"></td>
																	</tr>
																	<tr>
																		<td>Unit Cost</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-crossvalue="invoicenum.subtotalcost"></td>
																	</tr>
																	<tr>
																		<td>Total Cost</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal"></td>
																	</tr>
																	<tr>
																		<td>Cost Type</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="costtype" data-lookup="costtype2"></td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td> Transactions Date</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="transdate" data-type="date"></td>
																	</tr>
																	<tr>
																		<td>Debit Account Code</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount"></td>
																	</tr>
																	<tr>
																		<td>Account Code</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct"  data-lookup="chartofaccount"></td>
																	</tr>
																</table>
															</div>
														</div>
													</div>
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_cashrecaddcost_child" class="display" data-subform="subform-addcost"></table>
															<div id="mxtable_cashrecaddcost_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Num.</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="cashrecaddcostlinenum" data-input="required" data-key="value" data-type="integer"></td>
																			</tr>
																			<tr>
																				<td>Cash in Bank Line Num.</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="cashinbanklinenum" data-lookup="cashinbank|cashreceiptnum"></td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
																			</tr>
																			<tr>
																				<td>Num.tes</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="notes"></td>
																			</tr>
																			<tr>
																				<td>Financial Period</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="financialperiod" ></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Invoice</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum" data-lookup="cashreceiptinvoice|cashreceiptnum" data-childrentable="invoice" data-childrencolumn="invoicenum"></td>
																			</tr>
																			<tr>
																				<td>Invoice Description</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="invoicenum.description" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Quantity</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Unit Cost</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-crossvalue="invoicenum.subtotalcost"></td>
																			</tr>
																			<tr>
																				<td>Total Cost</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Cost Type</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="costtype" data-lookup="costtype2"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<table class="tableDetailRow">
																			<tr>
																				<td> Transactions Date</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="transdate" data-type="date"></td>
																			</tr>
																			<tr>
																				<td>Debit Account Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount"></td>
																			</tr>
																			<tr>
																				<td>Account Code</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct"  data-lookup="chartofaccount"></td>
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
								<div id="maxes02_1_3" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Cash Receiving Num.
														</td>
														<td><input type="text" data-attribute="cashreceiptnum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description" >
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
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Profit & Loss
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>
														Total Expense
													</td>
													<td><input type="text" data-input="readonly" data-attribute="totalexpense" data-type="decimal" >
													</td>
												</tr>
												<tr>
													<td>Close Balance
													</td>
													<td><input type="text" data-attribute="closebalance" data-type="decimal" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table>
												<tr>
													<td>
														<div class="addrow" id="importexbypettycash"></div>
													</td>
													<td>
														<div class="addrow" id="importexpbyline"></div>
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_cashrecbalance_child" class="display" data-child="cashrecbalance"></table>
												<div id="mxtable_cashrecbalance_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>ID</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="cashrecbalanceid" data-key="value" data-type="integer"></td>
																</tr>
																<tr>
																	<td>Petty Cash</td>
																	<td colspan=2><input type="text" data-childfld="child" data-input="readonly" data-attribute="pettycashnum"></td>
																</tr>
																<tr>
																	<td>Invoice</td>
																	<td colspan=2><input type="text" data-childfld="child" data-input="readonly" data-attribute="invoicenum"></td>
																</tr>
																<tr>
																	<td>Invoice Line Num.</td>
																	<td colspan=2><input type="text" data-childfld="child" data-input="readonly" data-attribute="invoicelinenum"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Quantity</td>
																	<td colspan=2><input type="text" data-childfld="child" data-input="readonly" data-attribute="quantity" data-type="decimal" ></td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-input="readonly" data-attribute="unitcost" data-type="decimal" ></td>
																</tr>
																<tr>
																	<td>Total Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-input="readonly" data-attribute="linecost" data-type="decimal" ></td>
																</tr>
																<tr>
																	<td>Financial Period</td>
																	<td colspan=2><input type="text" data-childfld="child" data-input="readonly" data-attribute="financialperiod" data-type="decimal" ></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Debit Account Code</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount" ></td>
																</tr>
																<tr>
																	<td>Account Code</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
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
	</body>
</html>