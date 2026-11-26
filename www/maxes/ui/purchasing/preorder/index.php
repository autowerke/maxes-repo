<!DOCTYPE html>
<html>
	<title> Preorder </title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="preorder" data-appkeynum="preordernum">
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
								<li class="maxes02_2"><a href="#maxes02_1_3">Order Assignment</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Resources</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_5">Logs</a></li>
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
													<table id="mxtable_preorder" class="display"></table>
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
														<td>Pre-Assignment Num.</td>
														<td>
															<input type="text" data-attribute="preordernum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Customer </td>
														<td>
															<input type="text" data-attribute="customercode" data-lookup="customer"> 
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Status</td>
														<td>
															<input type="text" data-attribute="status" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Status Date </td>
														<td>
															<input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"> 
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
													<td class="maxes_sct_tbl">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Follow Up </td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Pre-Assignment Date</td>
													<td>
														<input type="text" data-attribute="preorderdate" data-type="date"> 
													</td>
												</tr>
												<tr>
													<td>Customer Contact </td>
													<td>
														<input type="text" data-attribute="customercontact"> 
													</td>
												</tr>
												<tr>
													<td>Sales Representative </td>
													<td>
														<input type="text" data-attribute="salesrep" data-lookup="employees"> 
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
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Cost </td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Total before Tax </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotal"> 
													</td>
												</tr>
												<tr>
													<td>Total Tax </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax"> 
													</td>
												</tr>
												<tr>
													<td>Total </td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="totalcost"> 
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Company</td>
													<td>
														<input type="text" data-attribute="orgcode" data-input="readonly"> 
													</td>
												</tr>
												<tr>
													<td>Site</td>
													<td>
														<input type="text" data-attribute="sitecode" data-input="readonly"> 
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix"> </div>
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
														<td>Pre-Assignment Num.</td>
														<td>
															<input type="text" data-attribute="preordernum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Customer </td>
														<td>
															<input type="text" data-attribute="customercode" data-lookup="customer"> 
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Status</td>
														<td>
															<input type="text" data-attribute="status" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Status Date </td>
														<td>
															<input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"> 
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="addrow" id="addproline"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div id="subform-addproline" class="subform" data-childfld="child" data-child="preorderline" data-table="mxtable_preorderline_child">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tbldata">
															<tr>
																<td>Num.</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="preorderlinenum" data-key="value" data-type="integer">
																</td>
															</tr>
															<tr>
																<td>Description</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="description" data-input="required">
																</td>
															</tr>
															<tr>
																<td>Remarks</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="remarks">
																</td>
															</tr>
														</table>
													</div>
													<div class="col-md-4">
														<table class="tbldata">
															<tr>
																<td>Quantity</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																</td>
															</tr>
															<tr>
																<td>Order Unit</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
																</td>
															</tr>
															<tr>
																<td>Unit Cost</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																</td>
															</tr>
															<tr>
																<td>Total Cost</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal">
																</td>
															</tr>
															<tr>
																<td>Tax Code</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax" data-lookupaction="y">
																</td>
															</tr>
															<tr>
																<td>Quantity Tax</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="taxcost" data-type="decimal">
																</td>
															</tr>
														</table>
													</div>
													<div class="col-md-3">
														
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_preorderline_child" class="display" data-subform="subform-addproline"></table>
													<div id="mxtable_preorderline_child_detail" class="mxtable_detail">
														<div class="row eatfix">
															<div class="col-md-3">
																<table class="tableDetailRow">
																	<tr>
																		<td>Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="preorderlinenum" data-key="value" data-type="integer">
																		</td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="description" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>Remarks</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="remarks">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-3">
																<table class="tableDetailRow">
																	<tr>
																		<td>Quantity</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Order Unit</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
																		</td>
																	</tr>
																	<tr>
																		<td>Unit Cost</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Total Cost</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly">
																		</td>
																	</tr>
																	<tr>
																		<td>Tax Code</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax"  data-lookupaction="y">
																		</td>
																	</tr>
																	<tr>
																		<td>Tax Cost</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="taxcost" data-type="decimal" data-input="readonly">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-3">
																<table class="tableDetailRow">
																	
																	<tr>
																		<td>Sales Quotation Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="salesquotenum" data-input="readonly">
																		</td>
																	</tr>
																	<tr>
																		<td>Sales Order Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly">
																		</td>
																	</tr>
																	<tr>
																		<td>WO #</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly">
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
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Pre-Assignment Num.</td>
														<td>
															<input type="text" data-attribute="preordernum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Customer </td>
														<td>
															<input type="text" data-attribute="customercode" data-lookup="customer"> 
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Status</td>
														<td>
															<input type="text" data-attribute="status">
														</td>
													</tr>
													<tr>
														<td>Status Date </td>
														<td>
															<input type="text" data-attribute="statusdate" data-type="date"> 
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
													<td class="maxes_sct_tbl">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Cost </td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Inventory Cost</td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="itemcost"> 
													</td>
												</tr>
												<tr>
													<td>Service Cost</td>
													<td>
														<input type="text" data-type="decimal" data-input="readonly" data-attribute="servicecost"> 
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="subtab col-md-12">
										<ul class="maxes02">
											<li class="maxes02_1"><a href="#proresource">Service Resource</a>
											
											</li>
											<li class="maxes02_2"><a href="#proitem">Item/Inventory</a></li>
										</ul>
									</div>
									<div class="maxes04sub">
										<div id="proresource" class="maxes02_1">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Resource for Service
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													Resource will be the work order task</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="addprores"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div id="subform-addprores" class="subform" data-childfld="child" data-child="proresource" data-table="mxtable_proresource_child">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="proreslinenum" data-key="value" data-type="integer">
																		</td>
																	</tr>
																	<tr>
																		<td>Type </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="description" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>For Pre-Assignment Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="forpreorderlinenum"  data-input="required">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Cost</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="cost" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Margin (%)</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="marginpercent" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Margin</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="margincost" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Cost Margin</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="costmargin" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																</table>
															</div>
															<div class="col-md-3">
																<table class="tbldata">
																	<tr>
																		<td>Start Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="targstartdate" data-type="date">
																		</td>
																	</tr>
																</table>
																<table class="tbldata">
																	<tr>
																		<td>End Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="targcompdate" data-type="date">
																		</td>
																	</tr>
																	<tr>
																		<td>Status</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="status">
																		</td>
																	</tr>
																	<tr>
																		<td>Status Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="statusdate"  data-type="date">
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
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_proresource_child" class="display" data-subform="subform-addprores"></table>
															<div id="mxtable_proresource_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Num.</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="proreslinenum" data-key="value" data-type="integer">
																				</td>
																			</tr>
																			<tr>
																				<td>For Pre-Assignment Num.</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="forpreorderlinenum"  data-input="required">
																				</td>
																			</tr>
																			<tr>
																				<td>Type </td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype" data-input="required">
																				</td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="description" data-input="required">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Cost</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="cost" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Margin (%)</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="marginpercent" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Margin</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="margincost" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>Harga Total</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="costmargin" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																		</table>
																	</div>
																	<div class="col-md-3">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Target Mulai</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="targstartdate" data-type="date">
																				</td>
																			</tr>
																		</table>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Target Selesai</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="targcompdate" data-type="date">
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
										
										<div id="proitem" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Item 
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="addproitem"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div id="subform-addproitem" class="subform" data-childfld="child" data-child="proitem" data-table="mxtable_proitem_child">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="proitemlinenum" data-key="value" data-type="integer">
																		</td>
																	</tr>
																	<tr>
																		<td>Item</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="invbalance" data-lookupaction="y" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="description" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>For Pre-Assignment Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="forpreorderlinenum"  data-input="required">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Jml. yang diperlukan </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="requiredqty" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Issue Unit</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="issueunit">
																		</td>
																	</tr>
																	<tr>
																		<td>Warehouse</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse">
																		</td>
																	</tr>
																	<tr>
																		<td>Rack</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="racknum" data-lookup="rack">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-3">
																<table class="tbldata">
																	<tr>
																		<td>Unit Cost</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Total Cost </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Cost Type</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="costtype" data-lookup="costtype" >
																		</td>
																	</tr>
																	<tr>
																		<td>Company</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="orgcode" data-lookup="organization">
																		</td>
																	</tr>
																	<tr>
																		<td>Site</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="sitecode" data-lookup="site">
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
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_proitem_child" class="display" data-subform="subform-addproitem"></table>
															<div id="mxtable_proitem_child_detail" class="mxtable_detail">
																<div class="col-md-12">
																	<div class="row eatfix">
																		<div class="col-md-4">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Num.</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="proitemlinenum" data-key="value" data-type="integer">
																					</td>
																				</tr>
																				<tr>
																					<td>Item</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="inventory" data-lookupaction="y" data-input="required">
																					</td>
																				</tr>
																				<tr>
																					<td>Description</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="description" data-input="required">
																					</td>
																				</tr>
																				<tr>
																					<td>For Pre-Assignment Num.</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="forpreorderlinenum" >
																					</td>
																				</tr>
																			</table>
																		</div>
																		<div class="col-md-4">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Required Quantity</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="requiredqty" data-type="decimal">
																					</td>
																				</tr>
																				<tr>
																					<td>Issue Unit</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="issueunit" data-lookup="measureunit">
																					</td>
																				</tr>
																				<tr>
																					<td>Warehouse</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse">
																					</td>
																				</tr>
																				<tr>
																					<td>Rack</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="racknum" data-lookup="rack">
																					</td>
																				</tr>
																			</table>
																		</div>
																		<div class="col-md-3">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Unit Cost</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																					</td>
																				</tr>
																				<tr>
																					<td>Total Cost</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal">
																					</td>
																				</tr>
																				<tr>
																					<td>Cost Type</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="costtype" data-lookup="costtype" >
																					</td>
																				</tr>
																				<tr>
																					<td>Company</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="orgcode" data-lookup="organization">
																					</td>
																				</tr>
																				<tr>
																					<td>Site</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="sitecode" data-lookup="site">
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
								<div id="maxes02_1_5" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Pre-Assignment Num.</td>
														<td>
															<input type="text" data-attribute="preordernum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Customer </td>
														<td>
															<input type="text" data-attribute="customercode" data-lookup="customer"> 
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Status</td>
														<td>
															<input type="text" data-attribute="status">
														</td>
													</tr>
													<tr>
														<td>Status Date </td>
														<td>
															<input type="text" data-attribute="statusdate" data-type="date"> 
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
													<td class="maxes_sct_tbl">Related Documents</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="addrow" id="addprodocs"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div id="subform-addprodocs" class="subform" data-childfld="child" data-child="preorderdocs" data-table="mxtable_prodocs_child">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tbldata">
															<tr>
																<td>Num.</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="preorderdoclinenum" data-key="value" data-type="integer">
																</td>
															</tr>
															<tr>
																<td>Description</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="description">
																</td>
															</tr>
															<tr>
																<td>Acknowledged By</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="acknowledgedby" data-lookup="employees">
																</td>
															</tr>
															<tr>
																<td>Approved By</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="approvedby" data-lookup="employees">
																</td>
															</tr>
														</table>
													</div>
													<div class="col-md-4">
														<table class="tbldata">
															<tr>
																<td>Submited Date</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="submitdate" data-type="date">
																</td>
															</tr>
															<tr>
																<td>Approval Date</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="approvaldate"  data-type="date">
																</td>
															</tr>
															<tr>
																<td>Status</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="status">
																</td>
															</tr>
															<tr>
																<td>Status Date</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="statusdate" data-type="date">
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
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_prodocs_child" class="display" data-subform="subform-addprodocs"></table>
													<div id="mxtable_prodocs_child_detail" class="mxtable_detail">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td>Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="preorderdoclinenum" data-key="value" data-type="integer">
																		</td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="description">
																		</td>
																	</tr>
																	<tr>
																		<td>Acknowledged By</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="acknowledgedby" data-lookup="employees">
																		</td>
																	</tr>
																	<tr>
																		<td>Approved By</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="approvedby" data-lookup="employees">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td>Submited Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="submitdate" data-type="date">
																		</td>
																	</tr>
																	<tr>
																		<td>Approval Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="approvaldate"  data-type="date">
																		</td>
																	</tr>
																	<tr>
																		<td>Status</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="status">
																		</td>
																	</tr>
																	<tr>
																		<td>Status Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="statusdate" data-type="date">
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