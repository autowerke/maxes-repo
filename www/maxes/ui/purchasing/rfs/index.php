
<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="rfs" data-appkeynum="rfsnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Goods Receipt Request</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Planning Storage</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Shipping & Inspections</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_5">Return</a></li>
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
													<table id="mxtable_rfs" class="display"></table>
													<div>
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
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>GRI</td>
														<td><input type="text" data-attribute="rfsnum" data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description" style="width:450px;"></td>
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
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Remarks
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Warehouse Staff
													</td>
													<td><input type="text" data-attribute="personid" data-lookup="employees" data-childrentable="employees" data-childrencolumn='personid'>
													</td>
												</tr>
												<tr>
													<td>PO</td>
													<td><input type="text" data-attribute="ponum" data-lookup="po" data-lookupaction='y' data-childrentable="po" data-childrencolumn="ponum"></td>
												</tr>
												<tr>
													<td>PO Order Date</td>
													<td><input type="text" data-attribute="ponum.orderdate" data-input="readonly"></td>
												</tr>
												<tr>
													<td>From Site
													</td>
													<td><input type="text" data-attribute="fromsitecode">
													</td>
												</tr>
												<tr>
													<td>GRI Date
													</td>
													<td><input type="text" data-attribute="rfsdate" data-type="date">
													</td>
												</tr>
												<tr>
													<td>Est. of Arrival
													</td>
													<td><input type="text" data-attribute="estdatearrival" data-type="date" data-input="rfsdate">
													</td>
												</tr>
												<tr>
													<td>Company
													</td>
													<td><input type="text" data-attribute="orgcode" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Site
													</td>
													<td><input type="text" data-attribute="sitecode" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Remarks
													</td>
												</tr>
											</table>
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
													<td><input type="text" data-attribute="statusdate" data-input="readonly" data-type="date">
													</td>
												</tr>
												<tr>
													<td>Changed By
													</td>
													<td><input type="text" data-attribute="changeby" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Changed Date
													</td>
													<td><input type="text" data-attribute="changedate" data-input="readonly" data-type="date">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Assignment</td>
												</tr>
											</table>
											<table class="tbldata">
												<table class="tbldata">
													<tr>
														<td>Process Line Num.
														</td>
														<td><input type="text" data-attribute="docprocessnum" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Status
														</td>
														<td><input type="text" data-attribute="docstatus" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Sent By
														</td>
														<td><input type="text" data-attribute="docsentby" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Remarks
														</td>
														<td><input type="text" data-attribute="docnotice" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Position
														</td>
														<td><input type="text" data-attribute="docposition" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Arrival
														</td>
														<td><input type="text" data-attribute="docarrival" data-input="readonly" data-type="date">
														</td>
													</tr>
												</table>
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
														<td>GRI</td>
														<td><input type="text" data-attribute="rfsnum" data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description" style="width:450px;"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Storage Planning</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
									<div class="col-md-4">
									<span class="addrow" id="addPOLines"></span>
									</div>
										
									</div>
								
									
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_rfsline_child" class="display" data-subform="subform-rfsline" data-child="rfsline"></table>
												<div id="mxtable_rfsline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Line Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Num.</td>
																	<td><input type="text" size=3 data-childfld="child" data-attribute="rfslinenum" data-key="value" data-input="required" data-type="integer"></td>
																</tr>
																<tr>
																	<td>Item</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="item|sitecode" data-lookupaction="y" data-childrentable="item" data-childrencolumn="itemnum"></td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description"></td>
																</tr>
																<tr>
																	<td>Specification</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.spectext" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Cost</div>
																	</td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Total Cost</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="linecost" data-input="readonly" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Tax</td>
																	<td><input type="text" data-childfld="child" data-attribute="tax" data-lookup="tax" data-lookupaction="y" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Tax Cost</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="taxcost" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Quantity</div>
																	</td>
																</tr>
																<tr>
																	<td>Quantity</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="orderqty" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Order Unit </td>
																	<td><input type="text" data-childfld="child" data-attribute="orderunit" data-input="readonly" data-lookup="measureunit" data-crossvalue="itemnum.orderunit" data-lookupaction="y"></td>
																</tr>
																<tr>
																	<td>Issue Unit</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Conversion Factor</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="conversionfactor" data-input="readonly"></td>
																</tr>
															</table>
														</div>
													</div>
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Model</div>
																	</td>
																</tr>
																<tr>
																	<td>Manufacturer</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.manufacturer" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Part Number</td>
																	<td><input type="text" data-childfld="child" data-attribute="partnum" data-lookup="itempartnum|itemnum"></td>
																</tr>
																<tr>
																	<td>Category</td>
																	<td><input type="text" data-childfld="child" data-attribute="category"></td>
																</tr>
																<tr>
																	<td>Catalog</td>
																	<td><input type="text" data-childfld="child" data-attribute="catalog"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Receiving Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Warehouse</td>
																	<td><input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse|sitecode" data-childrentable="warehouse" data-childrencolumn="warehouse" data-lookupaction="y"></td>
																</tr>
																<tr>
																	<td>Rack</td>
																	<td><input type="text" data-childfld="child" data-attribute="racknum" data-lookup="rack|warehouse"></td>
																</tr>
																<tr>
																	<td>Storage Duration (days)</td>
																	<td><input type="text" data-childfld="child" data-attribute="storagedur" data-type="decimal"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Transactions Account Code</div>
																	</td>
																</tr>
																<tr>
																	<td>Credit Account Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="glaccount|orgcode"></td>
																</tr>
																<tr>
																	<td>Debit Account Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="glaccount|orgcode" data-crossvalue="warehouse.controlacc"></td>
																</tr>
															</table>
														</div>
													</div>
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Dimension</div>
																	</td>
																</tr>
																<tr>
																	<td>Width (cm)</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.width" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Height (cm)</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.height" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Length (cm)</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.length" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Volume (cm<sup>3</sup>)
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.volume" data-type="decimal" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Weight (gram)</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.weight" data-input="readonly" data-type="decimal"></td>
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
																	<td>Entered By</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterby" data-type="date" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!--end tab-->
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>GRI</td>
														<td><input type="text" data-attribute="rfsnum" data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description" style="width:450px;"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Shipping</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addCourier"></div>
										</div>
									</div>
									<div id="subform-rfscourier" class="subform" data-table="mxtable_rfscourier_child" data-child="rfscourier">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Line Detail</td>
													</tr>
													<tr>
														<td>Num.</td>
														<td><input type="text" size=3 data-childfld="child" data-attribute="rfscourierlinenum" data-key="value" data-input="required" data-type="integer"></td>
													</tr>
													<tr>
														<td>Courier Name</td>
														<td><input type="text" data-childfld="child" data-attribute="couriername"></td>
													</tr>
													<tr>
														<td>Courier Tracking Num.</td>
														<td><input type="text" data-childfld="child" data-attribute="trackingcode"></td>
													</tr>
													<tr>
														<td>Courier Agent Name</td>
														<td><input type="text" data-childfld="child" data-attribute="agentname"></td>
													</tr>
													<tr>
														<td>Shipping Type</td>
														<td><input type="text" data-childfld="child" data-attribute="shippingtype"></td>
													</tr>
													<tr>
														<td>Service Type</td>
														<td><input type="text" data-childfld="child" data-attribute="servicetype"></td>
													</tr>
													<tr>
														<td>Special Instructions</td>
														<td><input type="text" data-childfld="child" data-attribute="specialinstruction"></td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Origin Address</td>
													</tr>
													<tr>
														<td>Address</td>
														<td><input type="text" data-childfld="child" data-attribute="address"></td>
													</tr>
													<tr>
														<td>City</td>
														<td><input type="text" data-childfld="child" data-attribute="city"></td>
													</tr>
													<tr>
														<td>Province</td>
														<td><input type="text" data-childfld="child" data-attribute="province"></td>
													</tr>
													<tr>
														<td>Postal Code</td>
														<td><input type="text" data-childfld="child" data-attribute="zipcode"></td>
													</tr>
													<tr>
														<td>Country</td>
														<td><input type="text" data-childfld="child" data-attribute="country"></td>
													</tr>
													<tr>
														<td>Tel.</td>
														<td><input type="text" data-childfld="child" data-attribute="phone"></td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Date Time</td>
													</tr>
													<tr>
														<td>Shipping Date</td>
														<td><input type="text" data-childfld="child" data-attribute="shipdate" data-type="date"></td>
													</tr>
													<tr>
														<td>Est. of Arrival</td>
														<td><input type="text" data-childfld="child" data-attribute="estdatearrival" data-type="date"></td>
													</tr>
													<tr>
														<td>Actual of Arrival</td>
														<td><input type="text" data-childfld="child" data-attribute="actdatearrival" data-type="date"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_rfscourier_child" class="display" data-subform="subform-rfscourier"></table>
												<div id="mxtable_rfscourier_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Shipment Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Num.</td>
																	<td><input type="text" size=3 data-childfld="child" data-attribute="rfscourierlinenum" data-key="value" data-input="required" data-type="integer"></td>
																</tr>
																<tr>
																	<td>Courier Name</td>
																	<td><input type="text" data-childfld="child" data-attribute="couriername"></td>
																</tr>
																<tr>
																	<td>Courier Tracking Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="trackingcode"></td>
																</tr>
																<tr>
																	<td>Courier Staff Name</td>
																	<td><input type="text" data-childfld="child" data-attribute="agentname"></td>
																</tr>
																<tr>
																	<td>Shipping Type</td>
																	<td><input type="text" data-childfld="child" data-attribute="shippingtype"></td>
																</tr>
																<tr>
																	<td>Service Type</td>
																	<td><input type="text" data-childfld="child" data-attribute="servicetype"></td>
																</tr>
																<tr>
																	<td>Special Instructions</td>
																	<td><input type="text" data-childfld="child" data-attribute="specialinstruction"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Origin</div>
																	</td>
																</tr>
																<tr>
																	<td>Address</td>
																	<td><input type="text" data-childfld="child" data-attribute="address"></td>
																</tr>
																<tr>
																	<td>City</td>
																	<td><input type="text" data-childfld="child" data-attribute="city"></td>
																</tr>
																<tr>
																	<td>Province</td>
																	<td><input type="text" data-childfld="child" data-attribute="province"></td>
																</tr>
																<tr>
																	<td>Postal Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="zipcode"></td>
																</tr>
																<tr>
																	<td>Country</td>
																	<td><input type="text" data-childfld="child" data-attribute="country"></td>
																</tr>
																<tr>
																	<td>Tel.</td>
																	<td><input type="text" data-childfld="child" data-attribute="phone"></td>
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
																	<td>Shipping Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="shipdate" data-type="date"></td>
																</tr>
																<tr>
																	<td>Est. of Arrival</td>
																	<td><input type="text" data-childfld="child" data-attribute="estdatearrival" data-type="date"></td>
																</tr>
																<tr>
																	<td>Actual Arrival Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="actdatearrival" data-type="date"></td>
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
													<td class="maxes_sct_tbl">Quality Control & Inspection</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="col-md-12">
										<div class="subtab">
											<ul class="maxes02">
												<li class="maxes02_1"><a href="#maxes02_1_4_1">Inspectors</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_4_2">Inspected Items</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_4_3">Inspection Documents</a></li>
											</ul>
										</div>
									</div>
									<div class="maxes04sub">
										<div id="maxes02_1_4_1" class="maxes02_1">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Inspectors
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addTeam"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_rfsinsteam_child" class="display" data-child="rfsinsteam"></table>
														<div id="mxtable_rfsinsteam_child_detail" class="mxtable_detail">
															<div class="col-md-12">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Role</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Acknowledge</td>
																			<td><input type="checkbox" data-childfld="child" data-attribute="acknowledge"></td>
																		</tr>
																		<tr>
																			<td>Approve</td>
																			<td><input  type="checkbox" data-childfld="child" data-attribute="approve"></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div id="maxes02_1_4_2" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Inspected Items <span id="mxtable_rfsinsitem_child_title"></span>
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addInspItem"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_rfsinsitem_child" class="display" data-subform="subform-rfsinsitem" data-link="mxtable_rfscourier_child" data-pk="rfscourierlinenum" data-child="rfsinsitem"></table>
														<div id="mxtable_rfsinsitem_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Inspection Detail</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Shipment Line Num.</td>
																			<td><input type="text" data-childfld="child" data-attribute="rfscourierlinenum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>RFS Line Num.</td>
																			<td><input type="text" data-childfld="child" data-attribute="rfsinslinenum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Item</td>
																			<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Quantity</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Inspect Qty</td>
																			<td><input type="text" data-childfld="child" data-attribute="inspectqty" data-type="decimal"></td>
																		</tr>
																		<tr>
																			<td>Unit Cost</td>
																			<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Total Cost</td>
																			<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Deliverables</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Debit Account Code</td>
																			<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-lookup="chartofaccount"></td>
																		</tr>
																		<tr>
																			<td>Account Code</td>
																			<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
																		</tr>
																		<tr>
																			<td>Warehouse</td>
																			<td><input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse"></td>
																		</tr>
																		<tr>
																			<td>Rack</td>
																			<td><input type="text" data-childfld="child" data-attribute="racknum" data-lookup="rack|warehouse"></td>
																		</tr>
																	</table>
																</div>
															</div>
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Inspector</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Inspected By</td>
																			<td><input type="text" data-childfld="child" data-attribute="inspectby" data-lookup="rfsinspectionteam|rfsnum"></td>
																		</tr>
																		<tr>
																			<td>Inspected Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="inspectdate" data-type="date"></td>
																		</tr>
																		<tr>
																			<td>Completed Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="inspcompdate" data-type="date"></td>
																		</tr>
																		<tr>
																			<td>Acknowledged?</td>
																			<td><input type="checkbox" data-childfld="child" data-attribute="isacknowledged"></td>
																		</tr>
																		<tr>
																			<td>Approved?</td>
																			<td><input type="checkbox" data-childfld="child" data-attribute="isapproved"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Justification</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Inspection Type</td>
																			<td><input type="text" data-childfld="child" data-attribute="inspectiontype" data-lookup="inspectiontype"></td>
																		</tr>
																		<tr>
																			<td>Comment</td>
																			<td><textarea data-childfld="child" data-attribute="inspectcomment"></textarea></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div id="maxes02_1_4_3" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Inspection Documents<span id="mxtable_rfsinsdoc_child_title"></span>
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addDoc"></div>
												</div>
											</div>
											<div class="col-md-12">
												<div id="subform-rfsinsdoc" class="subform" data-table="mxtable_rfsinsdoc_child" data-child="rfsinsdoc" data-pk="rfscourierlinenum">
													<div class="row eatfix">
															<div class="col-md-6">
																<table class="tbldata">
																	<tr>
																		<td>Shipment Line Num.</td>
																		<td><input type="text" data-childfld="child" data-attribute="rfscourierlinenum" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Num.</td>
																		<td><input type="text" data-childfld="child" data-attribute="rfsinslinenum" data-type="integer" data-key="value"></td>
																	</tr>
																	<tr>
																		<td>Document ID</td>
																		<td><input type="text" data-childfld="child" data-attribute="docnum"></td>
																	</tr>
																	<tr>
																		<td>Title</td>
																		<td><input type="text" data-childfld="child" data-attribute="doctitle"></td>
																	</tr>
																	<tr>
																		<td>Abstract</td>
																		<td><textarea data-childfld="child" data-attribute="abstract"></textarea></td>
																	</tr>
																</table>
															</div>
															<div class="col-md-6">
																<table class="tbldata">
																	<tr>
																		<td>Date Created</td>
																		<td><input type="text" data-childfld="child" data-attribute="createddate" data-type="date"></td>
																	</tr>
																	<tr>
																		<td>Date Issued</td>
																		<td><input type="text" data-childfld="child" data-attribute="issueddate" data-type="date"></td>
																	</tr>
																</table>
															</div>
														
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_rfsinsdoc_child" class="display" data-subform="subform-rfsinsdoc" data-link="mxtable_rfscourier_child"> </table>
														<div id="mxtable_rfsinsdoc_child_detail" class="mxtable_detail">
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Document Detail</div>
																		</td>
																	</tr>
																	<tr>
																		<td>Receipt Line Num. </td>
																		<td><input type="text" data-childfld="child" data-attribute="rfscourierlinenum" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Num.</td>
																		<td><input type="text" data-childfld="child" data-attribute="rfsinslinenum" data-type="integer"></td>
																	</tr>
																	<tr>
																		<td>Doc. Number</td>
																		<td><input type="text" data-childfld="child" data-attribute="docnum"></td>
																	</tr>
																	<tr>
																		<td>Doc. Title</td>
																		<td><input type="text" data-childfld="child" data-attribute="doctitle"></td>
																	</tr>
																	<tr>
																		<td>Abstract</td>
																		<td><textarea data-childfld="child" data-attribute="abstract"></textarea></td>
																	</tr>
																</table>
															</div>
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Date Time</div>
																		</td>
																	</tr>
																	<tr>
																		<td>Date Created</td>
																		<td><input type="text" data-childfld="child" data-attribute="createddate" data-type="date"></td>
																	</tr>
																	<tr>
																		<td>Date Issued</td>
																		<td><input type="text" data-childfld="child" data-attribute="issueddate" data-type="date"></td>
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
								<div id="maxes02_1_5" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>GRI</td>
														<td><input type="text" data-attribute="rfsnum" data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description" style="width:450px;"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Return Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="selectitemtoreturn"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_rfsreturn_child" class="display" data-child="rfsreturn"></table>
												<div id="mxtable_rfsreturn_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="rfsreturnlinenum" data-input="readonly" data-childrentable="poline" data-childrencolumn="polinenum,ponum"></td>
																</tr>
																<tr>
																	<td>Item</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly" data-childrentable="item" data-childrencolumn="itemnum">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Actual Return Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="returndate" data-type="date"></td>
																</tr>
																<tr>
																	<td>Return By</td>
																	<td><input type="text" data-childfld="child" data-attribute="returnby" data-lookup="employees"></td>
																</tr>
																<tr>
																	<td>Debit Account Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Credit Account Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>PO</td>
																	<td><input type="text" data-childfld="child" data-attribute="ponum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>PO Line</td>
																	<td><input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Return Qty.</td>
																	<td><input type="text" data-childfld="child" data-attribute="returnqty" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Return Unit </td>
																	<td><input type="text" data-childfld="child" data-attribute="returnunit" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Line Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Conversion Factor</td>
																	<td><input type="text" data-childfld="child" data-attribute="conversionfactor" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Condition Type</td>
																	<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Batch Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="batchnum"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
														<table class="tableDetailRow">
																<tr>
																	<td>Warehouse</td>
																	<td><input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Rack</td>
																	<td><input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td><textarea data-childfld="child" data-attribute="remarks"></textarea></td>
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Entered By</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
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
													<td class="maxes_sct_tbl">Transactions</td>
												</tr>
											</table>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_matrectrans_child" class="display" data-child="matrectrans"></table>
												<div id="mxtable_matrectrans_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly" data-childrentable="poline" data-childrencolumn="polinenum,ponum"></td>
																</tr>
																<tr>
																	<td>Item</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly" data-childrentable="item" data-childrencolumn="itemnum">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Actual Receipt Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="actualdate" data-type="date"></td>
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
																	<td>Receipt Qty.</td>
																	<td><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Order Unit </td>
																	<td><input type="text" data-childfld="child" data-attribute="polinenum.orderunit" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Issue Unit</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Total Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Warehouse</td>
																	<td><input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Rack</td>
																	<td><input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td><input type="text" data-childfld="child" data-attribute="remarks"></td>
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Entered By</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																</tr>
															</table>
														</div>
													</div>
													<!-- DO NOT DELETE THIS HIDDEN INPUT, This input is to differentiate the record ID in matrectrans table-->
													<input type='hidden' data-childfld='child' data-attribute='matrectransid' data-input='readonly'>
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