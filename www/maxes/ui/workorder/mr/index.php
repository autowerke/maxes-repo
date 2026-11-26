<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="mr" data-appkeynum = "mrnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Additional Material Requisiition</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Detail</a></li>
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
													<table id="mxtable_mr" class="display"></table>
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
														<td>MR</td>
														<td><input type="text" data-attribute="mrnum" data-input="required" data-key="value"></td>
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
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Detail</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Status</td>
												<td><input type="text" data-attribute="status" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Status Date</td>
												<td><input type="text" data-attribute="statusdate" data-input="readonly" data-type="date"></td>
											</tr>
											<tr>
												<td>Requested Date</td>
												<td><input type="text" data-attribute="requesteddate" data-type="date"></td>
											</tr>
											<tr>
												<td>Requested by</td>
												<td><input type="text" data-attribute="requestedby" data-lookup="employees"></td>
											</tr>
											<tr>
												<td>Required Date</td>
												<td><input type="text" data-attribute="requireddate" data-type="date"></td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>WO</td>
												<td><input type="text" data-attribute="wonum" data-lookup="workorder" data-childrentable="workorder" data-childrencolumn="wonum"></td>
											</tr>
											<tr>
												<td>Description</td>
												<td><input type="text"  data-childfld="child"  data-attribute="wonum.description" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Asset</td>
												<td><input type="text" data-childfld="child" data-attribute="wonum.assetnum" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Location</td>
												<td><input type="text" data-childfld="child" data-attribute="wonum.location" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Priority</td>
												<td><input type="text" data-childfld="child" data-attribute="wonum.priority" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Work Type</td>
												<td><input type="text" data-childfld="child" data-attribute="wonum.worktype" data-input="readonly"></td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Work Class</td>
												<td><input type="text" data-childfld="child" data-attribute="wonum.workclass" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Leader</td>
												<td><input type="text" data-childfld="child" data-attribute="wonum.leader" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Owner</td>
												<td><input type="text" data-childfld="child" data-attribute="wonum.owner" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Account Code</td>
												<td><input type="text" data-childfld="child" data-attribute="wonum.glaccount" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Company</td>
												<td><input type="text" data-attribute="orgcode" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Site</td>
												<td><input type="text" data-attribute="sitecode" data-input="readonly"></td>
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
														<td>MR</td>
														<td><input type="text" data-attribute="mrnum" data-input="required" data-key="value"></td>
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
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addmrline"></div>
										</div>
									</div>
									<div class="col-md-12">
										<div id="subform-mrline" class="subform" data-child="mrline" data-table="mxtable_mrline_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Line 
															</td>
															<td><input type="text" data-childfld="child" data-attribute="mrlinenum" data-input="required" data-type="integer" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Item</td>
															<td><input type="text" data-childfld="child" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="invbalance|orgcode" data-lookupaction="y" data-input="required" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Description </td>
															<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.description">
															</td>
														</tr>
														<tr>
															<td>Warehouse
															</td>
															<td><input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>From Rack/Bin</td>
															<td><input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>Debit Account Code
															</td>
															<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required"  data-lookup="chartofaccount|orgcode">
															</td>
														</tr>
														<tr>
															<td>Credit Account Code
															</td>
															<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="chartofaccount|orgcode">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Condition Type
															</td>
															<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="itemcondition|itemnum,warehouse">
															</td>
														</tr>
														<tr>
															<td>Quantity
															</td>
															<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required">
															</td>
														</tr>
														<tr>
															<td>Issue Unit
															</td>
															<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>Unit Cost
															</td>
															<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="required">
															</td>
														</tr>
														<tr>
															<td>Total Cost
															</td>
															<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="required">
															</td>
														</tr>
														<tr>
															<td>Entered By
															</td>
															<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>Entered Date
															</td>
															<td><input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly">
															</td>
														</tr>
													</table>
												</div>
												<div class="row eatfix">
												</div>
											</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_mrline_child" class="display" data-subform="subform-mrline"></table>
														<div id="mxtable_mrline_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Line 
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="mrlinenum" data-input="required" data-type="integer" data-key="value">
																			</td>
																		</tr>
																		<tr>
																			<td>Item</td>
																			<td><input type="text" data-childfld="child" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="invbalance|orgcode" data-lookupaction="y" data-input="required" data-key="value">
																			</td>
																		</tr>
																		<tr>
																			<td>Description </td>
																			<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.description">
																			</td>
																		</tr>
																		<tr>
																			<td>Warehouse
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>From Rack/Bin</td>
																			<td><input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Debit Account Code
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required"  data-lookup="chartofaccount|orgcode">
																			</td>
																		</tr>
																		<tr>
																			<td>Credit Account Code
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="chartofaccount|orgcode">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Condition Type
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="itemcondition|itemnum,warehouse">
																			</td>
																		</tr>
																		<tr>
																			<td>Quantity
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required">
																			</td>
																		</tr>
																		<tr>
																			<td>Issue Unit
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Cost
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="required">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="required">
																			</td>
																		</tr>
																		<tr>
																			<td>Entered By
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Entered Date
																			</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly">
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
															<td class="maxes_sct_tbl">Issued Items</td>
														</tr>
													</table>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_matusetrans_child" class="display" data-child="matusetrans"></table>
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