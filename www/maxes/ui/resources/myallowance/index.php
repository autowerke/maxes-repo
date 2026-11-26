<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="myallowance" data-appkeynum = "myallowancenum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Allowance</a></li>
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
													<table id="mxtable_myallowance" class="display"></table>
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
												<table class="tbldata">
													<tr>
														<td>Allowance Num.
														</td>
														<td><input type="text" data-attribute="myallowancenum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Staff
														</td>
														<td><input type="text" data-attribute="personid" data-input="required" data-lookup="employees" data-childrentable="employees" data-childrencolumn="personid">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="Deskripsi">
														</td>
													</tr>
													<tr>
														<td>Full Name
														</td>
														<td><input type="text" data-attribute="personid.fullname" data-input="readonly">
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
											<table class="tbldata">
												<tr>
													<td>
														Financial Period
													</td>
													<td><input type="text" data-input="readonly" data-attribute="financialperiod">
													</td>
												</tr>
												<tr>
													<td>
														Total Cost
													</td>
													<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalcost">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Allowance</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addmyallowanceline"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-addmyallowanceline" class="subform" data-child="myallowanceline" data-table="mxtable_myallowance_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Num.</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="myallowancelinenum" data-input="required" data-key="value" data-type="integer"></td>
														</tr>
														<tr>
															<td>Description</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="Deskripsi"></td>
														</tr>
														<tr>
															<td>Debit Account Code</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="gldebitacct"  data-lookup="chartofaccount"></td>
														</tr>
														<tr>
															<td>Account Code  Credit</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Unit Cost</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="unitcost" data-input="required"  data-type="decimal"></td>
														</tr>
														<tr>
															<td>Quantity</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-input="required"  data-type="decimal"></td>
														</tr>
														<tr>
															<td>Measure Unit</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="measureunit" data-input="required"  data-lookup="measureunit"></td>
														</tr>
														<tr>
															<td>Total Cost</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly"  data-type="decimal"></td>
														</tr>
														<tr>
															<td>Remarks</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="remarks"></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_myallowance_child" class="display" data-subform="subform-addmyallowanceline">
												</table>
												<div id="mxtable_myallowance_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="myallowancelinenum" data-input="required" data-key="value" data-type="integer"></td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
																</tr>
																<tr>
																	<td>GL Debit</td>
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
																	<td>Unit Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="unitcost" data-input="required"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Quantity</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="quantity" data-input="required"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Measure Unit</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="measureunit" data-input="required"  data-lookup="measureunit"></td>
																</tr>
																<tr>
																	<td>Total Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="linecost" data-input="required"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="remarks"></td>
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