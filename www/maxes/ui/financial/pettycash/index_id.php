<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="pettycash"  data-appkeynum = "pettycashnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Petty Cash</a></li>
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
													<table id="mxtable_pettycash" class="display"></table>
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
														<td>Petty Cash
														</td>
														<td><input type="text" data-attribute="pettycashnum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description" >
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
										<div class="col-md-4">
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
													<td>Total Pengeluaran Invoice
													</td>
													<td><input type="text" data-attribute="totalcost" data-type="decimal">
													</td>
												</tr>
												<tr>
													<td>Total Pengeluaran Lainnya
													</td>
													<td><input type="text" data-attribute="totalothercost" data-type="decimal">
													</td>
												</tr>
												
												<tr>
													<td>Closing Balance
													</td>
													<td><input type="text" data-attribute="closebalance" data-type="decimal">
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
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>
														Entered Date
													</td>
													<td><input type="text" data-input="readonly" data-attribute="enterdate">
													</td>
												</tr>
												<tr>
													<td>
														Entered By
													</td>
													<td><input type="text" data-input="readonly" data-attribute="enterby">
													</td>
												</tr>
												<tr>
													<td>Company</td>
													<td><input type="text" data-attribute="orgcode" data-lookup="organization">
													</td>
												</tr>
												<tr>
													<td>Site
													</td>
													<td><input type="text" data-attribute="sitecode" data-lookup="site">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Petty Cash</td>
												</tr>
											</table>
										</div>
									</div>
									
									
									
									<div class="row eatfix">
										<div class="col-md-2">
											<div class="addrow" id="addexpenses"></div>
										</div>
									</div>
									<div id="subform-addexpenses" class="subform" data-child="pettycashline2" data-table="mxtable_pettycashline2_child">
									 <div class="row eatfix">
											
												 <div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Num.</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="pettycashline2num" data-input="readonly" data-key="value" data-type="integer"></td>
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
													<table class="tbldata">
														
														<tr>
															<td>Quantity</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity"  data-type="decimal"></td>
														</tr>
														<tr>
															<td>Unit Cost</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal"></td>
														</tr>
														
														<tr>
															<td>Measurement Unit</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit"></td>
														</tr>
														<tr>
															<td>Total Cost</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly"  data-type="decimal"></td>
														</tr>
													</table>
												</div>
												</div>
														
											</div>
									   </div>
						   
						   
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												
												<table id="mxtable_pettycashline2_child" class="display" data-subform="subform-addexpenses" data-child="pettycashline2">
												</table>
												<div id="mxtable_pettycashline2_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																
																<tr>
																	<td>Num.</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="pettycashline2num" data-input="readonly" data-key="value" data-type="integer"></td>
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
																	<td>Quantity</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="unitcost"  data-type="decimal"></td>
																</tr>
																
																<tr>
																	<td>Measurement Unit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit"></td>
																</tr>
																<tr>
																	<td>Total Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly"  data-type="decimal"></td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									
									
									<div class="row eatfix">
										<div class="col-md-2">
											<div class="addrow" id="importexpenses"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_pettycashline_child" class="display" data-child="pettycashline">
												</table>
												<div id="mxtable_pettycashline_child_detail" class="mxtable_detail">
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
																	<td>Total Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly"  data-type="decimal"></td>
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