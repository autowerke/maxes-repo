<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="warehouse"  data-appkeynum = "warehouse">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Warehouse</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Related Items</a></li>
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
													<table id="mxtable_warehouse" class="display"></table>
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
														<td>Warehouse
														</td>
														<td><input type="text"  data-attribute="warehouse" data-type="upper" data-input="required" data-key="value">
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
													<td class="maxes_sct_tbl">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Inspection Warehouse?
													</td>
													<td><input type="checkbox" data-attribute="isinspection">
													</td>
												</tr>
												<tr>
													<td>Warehouse Account Code
													</td>
													<td><input type="text" data-attribute="controlacc" data-input="required"  data-lookup="glaccount|orgcode">
													</td>
												</tr>
												<tr>
													<td>Cost Adjustment Account Code 
													</td>
													<td><input type="text" data-attribute="invcostadjacc" data-input="required"  data-lookup="glaccount|orgcode">
													</td>
												</tr>
												<tr>
													<td> Stock Adjustment Account Code
													</td>
													<td><input type="text" data-attribute="shrinkageacc" data-input="required"  data-lookup="glaccount|orgcode">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Shipment Cost
													</td>
													<td><input type="text" data-attribute="shippingaddress" data-lookup="siteaddress|sitecode" data-childrentable="address" data-childrencolumn="addresscode">
														<input type="text" data-childfld="child" data-attribute="shippingaddress.address" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Billing Address
													</td>
													<td><input type="text" data-attribute="billingaddress" data-lookup="siteaddress|sitecode" data-childrentable="address" data-childrencolumn="addresscode">
														<input type="text" data-childfld="child" data-attribute="billingaddress.address" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Company
													</td>
													<td><input type="text" data-attribute="orgcode" data-lookup="organization" data-input="required">
													</td>
												</tr>
												<tr>
													<td>Site
													</td>
													<td><input type="text" data-attribute="sitecode" data-lookup="site" data-input="required">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Total Volume of Warehouse</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Total Volume(cm<sup>3</sup>)</td>
												<td><input type="text" data-attribute="totalspacevolume" data-type="decimal" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Used Space (cm<sup>3</sup>)</td>
												<td><input type="text" data-attribute="usedspacevolume" data-type="decimal"  data-input="readonly"></td>
											</tr>
											<tr>
												<td>Remaining Space (cm<sup>3</sup>)</td>
												<td><input type="text" data-attribute="remainingvolume" data-type="decimal"  data-input="readonly"></td>
											</tr>
										</table>
									</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Rack</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addRack"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-racks" class="subform" data-table="mxtable_warehouseline_child" data-child="warehouseline">
											<div class="row eatfix">
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td colspan=2>
																<div class="maxes_sct_tbl3">Detail</div>
															</td>
														</tr>
														<tr>
															<td>Num.</td>
															<td><input type="text" data-childfld="child" data-attribute="warehouselinenum"  data-type="integer" data-input="readonly"></td>
														</tr>
														<tr>
															<td>Rack Num</td>
															<td><input type="text" data-childfld="child" data-attribute="racknum" data-key="value"></td>
														</tr>
														<tr>
															<td>Rack Type</td>
															<td><input type="text" data-childfld="child" data-attribute="racktype" data-lookup="racktype"></td>
														</tr>
													</table>
												</div>
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td colspan=2>
																<div class="maxes_sct_tbl3">Dimension (cm)</div>
															</td>
														</tr>
														<tr>
															<td>Length</td>
															<td><input type="text" data-childfld="child" data-attribute="width"  data-type="decimal"></td>
														</tr>
														<tr>
															<td>Width</td>
															<td><input type="text" data-childfld="child" data-attribute="length" data-type="decimal"></td>
														</tr>
														<tr>
															<td>Height</td>
															<td><input type="text" data-childfld="child" data-attribute="height" data-type="decimal"></td>
														</tr>
														<tr>
															<td>Volume</td>
															<td><input type="text" data-childfld="child" data-attribute="volume" data-type="decimal"></td>
														</tr>
													</table>
												</div>
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td colspan=2>
																<div class="maxes_sct_tbl3">Date Time</div>
															</td>
														</tr>
														<tr>
															<td>Entered Date</td>
															<td><input type="text" data-childfld="child" data-attribute="enterdate"  data-type="date" data-input="readonly"></td>
														</tr>
														<tr>
															<td>Entered By</td>
															<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
														</tr>
													</table>
												</div>
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>Description</td>
																<td><textarea data-childfld="child" data-attribute="description" ></textarea>
																<td>
															</tr>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_warehouseline_child" class="display" data-child="warehouseline"></table>
												<div id="mxtable_warehouseline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Line</td>
																	<td><input type="text" data-childfld="child" data-attribute="warehouselinenum" data-key="value" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Rack Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="racknum" data-key="value"></td>
																</tr>
																<tr>
																	<td>Rack Type</td>
																	<td><input type="text" data-childfld="child" data-attribute="racktype" data-lookup="racktype"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Dimensionon (in Centimeters)</div>
																	</td>
																</tr>
																<tr>
																	<td>Width</td>
																	<td><input type="text" data-childfld="child" data-attribute="width"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Length</td>
																	<td><input type="text" data-childfld="child" data-attribute="length" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Height</td>
																	<td><input type="text" data-childfld="child" data-attribute="height" data-type="decimal"></td>
																</tr>
																
																<tr>
																	<td>Volume </td>
																	<td><input type="text" data-childfld="child" data-attribute="volume" data-type="decimal" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Date Time</div>
																	</td>
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterdate"  data-type="date" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Entered By</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="row eatfix">
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td>Description</td>
																		<td><textarea data-childfld="child" data-attribute="description" ></textarea>
																		<td>
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
								<div id="maxes02_1_3" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Warehouse
														</td>
														<td><input type="text"  data-attribute="warehouse" data-type="upper" data-input="required" data-key="value">
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
													<td class="maxes_sct_tbl2">Total Volume of Warehouse</td>
												</tr>
											</table>
										</div>
									</div>
									
									<div class ="row eatfix">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Total Volume (cm<sup>3</sup>)</td>
												<td><input type="text" data-attribute="totalspacevolume" data-type="decimal" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Used Space Volume(cm<sup>3</sup>)</td>
												<td><input type="text" data-attribute="usedspacevolume" data-type="decimal"  data-input="readonly"></td>
											</tr>
											<tr>
												<td>Remaining Space Volume(cm<sup>3</sup>)</td>
												<td><input type="text" data-attribute="remainingvolume" data-type="decimal"  data-input="readonly"></td>
											</tr>
										</table>
									</div>
									
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Related items</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_storeroomitems_child" class="display" data-child="v_invbalance"></table>
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