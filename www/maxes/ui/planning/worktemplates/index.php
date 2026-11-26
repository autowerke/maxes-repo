<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="worktemplates" data-appkeynum = "wtnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Work Template</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Tasks</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Planning</a></li>
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
													<table id="mxtable_worktemplates" class="display"></table>
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
														<td>Work Template</td>
														<td><input type="text" data-attribute="wtnum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Status</td>
														<td><input type="text" data-attribute="status" data-input="readonly"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Asset</td>
													<td><input type="text" data-attribute="assetnum" data-lookup="assets" data-lookupaction="y"></td>
												</tr>
												<tr>
													<td>Location</td>
													<td><input type="text" data-attribute="location" data-lookup="locations"  data-lookupaction="y"></td>
												</tr>
												<tr>
													<td>Priority</td>
													<td><input type="text" data-attribute="priority" data-lookup="wopriority" data-input="required"></td>
												</tr>
												<tr>
													<td>Work Type</td>
													<td><input type="text" data-attribute="worktype" data-lookup="worktype" ></td>
												</tr>
												<tr>
													<td>Work Class</td>
													<td><input type="text" data-attribute="workclass" data-lookup="workclass" ></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Team</td>
													<td><input type="text" data-attribute="teamnum" data-lookup="team" data-lookupaction="y"></td>
												</tr>
												<tr>
													<td>Leader</td>
													<td><input type="text" data-attribute="leader" data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees"></td>
												</tr>
												<tr>
													<td>Leader Name</td>
													<td><input type="text" data-attribute="leader.fullname" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Title</td>
													<td><input type="text" data-attribute="leader.title" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Owner</td>
													<td><input type="text" data-attribute="owner" data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees"></td>
												</tr>
												<tr>
													<td>Owner Name</td>
													<td><input type="text" data-attribute="owner.fullname" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Title</td>
													<td><input type="text" data-attribute="owner.title" data-input="readonly"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
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
														<td>Work Template</td>
														<td><input type="text" data-attribute="wtnum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Status</td>
														<td><input type="text" data-attribute="status" data-input="readonly"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Tasks</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addwttask"></div>
										</div>
									</div>
									<div class="col-md-12">
										<div id="subform-wttask" class="subform" data-child="wttask" data-table="mxtable_wttask_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Line Num.
															</td>
															<td><input type="text" data-childfld="child" data-attribute="tasklinenum" data-input="required" data-type="integer" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Description</td>
															<td><input type="text" data-childfld="child" data-attribute="description">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_wttask_child" class="display" data-subform="subform-wttask"></table>
												<div id="mxtable_wttask_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Line Num.
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="tasklinenum" data-input="readonly" data-input="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Description </td>
																	<td><input type="text" data-childfld="child" data-attribute="description">
																	</td>
																</tr>
															</table>
														</div>
													</div>
													<div class="col-md-6">
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
														<td>Work Template</td>
														<td><input type="text" data-attribute="wtnum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>



									<div class="subtab">
										<ul class="maxes02">
											<li class="maxes02_1"><a href="#maxes_planmat">Materials</a></li>
											<li class="maxes02_2"><a href="#maxes_planserv">Services</a></li>
											<li class="maxes02_2"><a href="#maxes_planlab">Labors</a></li>
											<li class="maxes02_2"><a href="#maxes_plantools">Tools</a></li>
										</ul>
									</div>
									<div class="col-md-12">
										<div class="maxes04sub">
											<!-- Planning Material Sub Tab -->
											<div id="maxes_planmat" class="maxes02_1">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Material Planning
												</div>
												<div class="row eatfix">
													<div class="col-md-4">
														<div class="addrow" id="addwtplanmaterial"></div>
													</div>
												</div>
												<div class="col-md-12">
													<div id="subform-wtplanmaterial" class="subform" data-child="wtplanmaterial" data-table="mxtable_wtplanmaterial_child">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Num.
																		</td>
																		<td><input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
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
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Issue Unit </td>
																		<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.issueunit">
																		</td>
																	</tr>
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
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata">
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
														</div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_wtplanmaterial_child" class="display" data-subform="subform-wtplanmaterial"></table>
															<div id="mxtable_wtplanmaterial_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Line Num.
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
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
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Issue Unit </td>
																				<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.issueunit">
																				</td>
																			</tr>
																			<tr>
																				<td>Quantity
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required">
																				</td>
																			</tr>
																			<tr>
																				<td>Condition Type
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="itemcondition|itemnum,warehouse">
																				</td>
																			</tr>
																			<tr>
																				<td>Unit Cost
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitcost" data-input="required" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Total Cost
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="linecost" data-input="required" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Entered By
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
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
																</div>
															</div>
														</div>
													</div>
												</div>

											</div>

											<!-- Planning Services Sub Tab --> 
											<div id="maxes_planserv" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Service Planning
												</div>
												<div class="row eatfix">
													<div class="col-md-4">
														<div class="addrow" id="addwtplanservice"></div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
													<div id="subform-wtplanservice" class="subform" data-child="wtplanservice" data-table="mxtable_wtplanservice_child">
														<div class="row eatfix">
															<div class="col-md-12">
																<div class="maxes_sct_tbl2"> Additional Vendor Service </div>
																<table class="tbldata">
																	<tr>
																		<td>Line Num.
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
																		</td>
																	</tr>
																	<tr>
																		<td>Service Item
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" data-childrentable="serviceitems" data-childrencolumn="serviceitemnum">
																		</td>
																	</tr>
																	<tr>
																		<td>Description
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="description" data-input="required" data-crossvalue="serviceitemnum.description">
																		</td>
																	</tr>
																	<tr>
																		<td>Quantity
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Order Unit
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit" data-crossvalue="serviceitemnum.serviceunit">
																		</td>
																	</tr>
																	<tr>
																		<td>Unit Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-crossvalue="serviceitemnum.stdcost">
																		</td>
																	</tr>
																	<tr>
																		<td>Line Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-6">
																<!-- Blank -->
															</div>
														</div>
													</div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_wtplanservice_child" class="display" data-subform="subform-wtplanservice"></table>
															<div id="mxtable_wtplanservice_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-6">
																		<div class="maxes_sct_tbl2"> Additional Vendor Service </div>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Line Num.
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
																				</td>
																			</tr>
																			<tr>
																				<td>Service Item
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" data-childrentable="serviceitems" data-childrencolumn="serviceitemnum">
																				</td>
																			</tr>
																			<tr>
																				<td>Description
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="description" data-input="required">
																				</td>
																			</tr>
																			<tr>
																				<td>Quantity
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Order Unit
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
																				</td>
																			</tr>
																			<tr>
																				<td>Unit Cost
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Line Cost
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<div class="maxes_sct_tbl2"> Existing Vendor Service </div>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Existing PR
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="prnum" data-lookup="pr|orgcode">
																				</td>
																			</tr>
																			<tr>
																				<td>Existing PR Line Num.
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="prlinenum" data-lookup="prlinewo|prnum">
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

											<!-- Planning Labor Sub Tab-->
											<div id="maxes_planlab" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Labor Planning
												</div>
												<div class="row eatfix">
													<div class="col-md-4">
														<div class="addrow" id="addwtplanlabor"></div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div id="subform-wtplanlabor" class="subform" data-child="wtplanlabor" data-table="mxtable_wtplanlabor_child">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tbldata  ">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Detail</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Team
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team" data-lookupaction="y">
																			</td>
																		</tr>
																		<tr>
																			<td>Person ID
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="personid" data-lookup="employees" data-lookupaction="y">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tbldata  ">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Hours</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Start Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="startdate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Finish Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="enddate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Regular Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="reghours" data-type="decimal">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tbldata  ">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Labor Cost</div>
																			</td>
																		</tr>
																		
																		<tr>
																			<td>Unit Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																			</td>
																		</tr>
																		
																		<tr>
																			<td>Total Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="prelinecost" data-type="decimal" data-input="readonly" data-input="readonly">
																			</td>
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
														<table id="mxtable_wtplanlabor_child" class="display" data-subform="subform-wtplanlabor"></table>
														<div id="mxtable_wtplanlabor_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Detail</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Team
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team" data-lookupaction="y">
																			</td>
																		</tr>
																		<tr>
																			<td>Person ID
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="personid" data-lookup="employees" data-lookupaction="y">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Hours</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Start Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="startdate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Finish Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="enddate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Regular Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="reghours" data-type="decimal">
																			</td>
																		</tr>
																		
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Labor Cost</div>
																			</td>
																		</tr>
																		
																		<tr>
																			<td>Unit Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="prelinecost" data-type="decimal" data-input="readonly" data-input="readonly">
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

											<!-- Planning Tools Sub Tab -->
											<div id="maxes_plantools" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Planning Material
												</div>
												<div class="row eatfix">
													<div class="col-md-4">
														<div class="addrow" id="addwtplantool"></div>
													</div>
												</div>
												<div class="row eatfix">
													<div id="subform-wtplantool" class="subform" data-child="wtplantool" data-table="mxtable_wtplantool_child">
														<div class="row eatfix">
															<div class="col-md-6">
																<table class="tbldata">
																	<tr>
																		<td>Line Num.
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
																		</td>
																	</tr>
																	<tr>
																		<td>Tools Number
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="toolnum" data-lookup='tools' data-childrentable="tools" data-childrencolumn="toolnum" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>Description
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="description" data-crossvalue="toolnum.description">
																		</td>
																	</tr>
																	<tr>
																		<td>For Task
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="tasklinenum" data-lookup="wotask|wonum">
																		</td>
																	</tr>
																	<tr>
																		<td>From Location </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="locnum" data-crossvalue="toolnum.locnum">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-6">
																<table class="tbldata">
																	<tr>
																		<td>Quantity
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Order Unit
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit" data-crossvalue="toolnum.measureunit">
																		</td>
																	</tr>
																	<tr>
																		<td>Unit Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-crossvalue="toolnum.unitcost">
																		</td>
																	</tr>
																	<tr>
																		<td>Total Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal">
																		</td>
																	</tr>
																</table>
															</div>
														</div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_wtplantool_child" class="display" data-subform="subform-wtplantool"></table>
															<div id="mxtable_wtplantool_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Line Num.
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="planlinenum" data-type="integer" data-key="value">
																				</td>
																			</tr>
																			<tr>
																				<td>For Task
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="tasklinenum" data-lookup="wotask|wo">
																				</td>
																			</tr>
																			<tr>
																				<td>Tool Number
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="toolnum" data-lookup="tools" data-childrentable="tools" data-childrencolumn="toolnum">
																				</td>
																			</tr>
																			<tr>
																				<td>Description </td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="description" data-crossvalue="toolnum.description">
																				</td>
																			</tr>
																			<tr>
																				<td>From Location </td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="locnum" data-crossvalue="toolnum.locnum">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Quantity
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Order Unit
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
																				</td>
																			</tr>
																			<tr>
																				<td>Unit Cost
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Total Cost </td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="decimal">
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
									<br>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>