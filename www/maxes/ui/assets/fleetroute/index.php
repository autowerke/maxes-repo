<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="fleetroute" data-appkeynum = "fleetroutenum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Fleet Route</a></li>
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
													<table id="mxtable_fleetroute" class="display"></table>
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
														<td>Code
														</td>
														<td><input type="text" data-attribute="fleetroutenum" data-input="required" data-key="value" data-type="upper">
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
													<td class="maxes_sct_tbl">Fleet Route
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>From 
														</td>
														<td><input type="text" data-attribute="fromlocation" data-input="required" data-lookup="locations">
														</td>
													</tr>
													<tr>
														<td>To
														</td>
														<td><input type="text" data-attribute="tolocation" data-input="required" data-lookup="locations">
														</td>
													</tr>
													<tr>
														<td>Remarks
														</td>
														<td><input type="text" data-attribute="description">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="subtab">
										<ul class="maxes02">
											<li class="maxes02_1"><a href="#fleetprice">Fleet and Price</a></li>
											<li class="maxes02_2"><a href="#relatedlocations">Locations passed on this route</a></li>
										</ul>
									</div>
									<div class="maxes04sub">
										<div id="fleetprice" class="maxes02_1">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Fleet and Price
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="row eatfix">
														<div class="col-md-12">
															<table width=100%>
																<tr>
																	<td class="maxes_sct_tbl">Fleet
																	</td>
																</tr>
															</table>
														</div>
													</div>
													<div class="row eatfix">
														<div class="row eatfix">
															<div class="col-md-6">
																<div class="addrow" id="addfleet"></div>
															</div>
														</div>
														<div class="col-md-12">
															<div class="tablelist">
																<table id="mxtable_fleetroute_child" class="display" data-child="fleetrouteline"></table>
																<div id="mxtable_fleetroute_child_detail" class="mxtable_detail">
																	<div class="row eatfix">
																		<div class="col-md-6">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Num.</td>
																					<td><input type="text" data-childfld="child" data-attribute="fleetroutelinenum" data-key="value" data-input="readonly"></td>
																				</tr>
																				<tr>
																					<td>Fleet</td>
																					<td><input type="text" data-childfld="child" data-attribute="assetnum"  data-childrentable="assets" data-childrencolumn="assetnum" data-input="readonly"></td>
																				</tr>
																			</table>
																		</div>
																		<div class="col-md-6">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Entered By</td>
																					<td><input type="text" data-childfld="child" data-attribute="enterby"></td>
																				</tr>
																				<tr>
																					<td>Entered Date</td>
																					<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" ></td>
																				</tr>
																				<tr>
																					<td>Changed By</td>
																					<td><input type="text" data-childfld="child" data-attribute="changeby"></td>
																				</tr>
																				<tr>
																					<td>Change By</td>
																					<td><input type="text" data-childfld="child" data-attribute="changedate" data-type="date" ></td>
																				</tr>
																				<tr>
																					<td>Change Date</td>
																					<td><input type="text" data-childfld="child" data-attribute="description"></td>
																				</tr>
																			</table>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														
													</div>
													<div class="row eatfix">
															<div class="col-md-6">
																<table width=100%>
																	<tr>
																		<td class="maxes_sct_tbl">Route Based Passenger Pricing
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-6">
																<table width=100%>
																	<tr>
																		<td class="maxes_sct_tbl">Vehicle-Based Passenger Pricing
																		</td>
																	</tr>
																</table>
															</div>
														</div>
														<div class="row eatfix">
															<div class="col-md-6">
																<div class="tablelist">
																<table id="mxtable_fleetprice_child" class="display" data-child="fleetprice"></table>
																	<div id="mxtable_fleetprice_child_detail" class="mxtable_detail">
																		<div class="row eatfix">
																			<div class="col-md-6">
																				<table class="tableDetailRow">
																					<tr>
																						<td>Class</td>
																						<td><input type="text" data-childfld="child" data-attribute="seattype" data-input="readonly" data-key="value"></td>
																					</tr>
																					<tr>
																						<td>Price</td>
																						<td><input type="text" data-childfld="child" data-attribute="price" data-type="decimal"></td>
																					</tr>
																				</table>
																			</div>
																		</div>
																	</div>	
																</div>
															</div>
															<div class="col-md-6">
															
																<div class="tablelist">
																	<table id="mxtable_fleetrouteprice_child" class="display" data-child="fleetrouteprice"></table>
																	<div id="mxtable_fleetrouteprice_child_detail" class="mxtable_detail">
																		<div class="row eatfix">
																			<div class="col-md-6">
																				<table class="tableDetailRow">
																					<tr>
																						<td>Num.</td>
																						<td><input type="text" data-childfld="child" data-attribute="fleetroutepricenum" data-key="value" data-input="readonly"></td>
																					</tr>
																					<tr>
																						<td>Fleet</td>
																						<td><input type="text" data-childfld="child" data-attribute="assetnum"  data-childrentable="assets" data-childrencolumn="assetnum" data-input="readonly"></td>
																					</tr>
																					<tr>
																						<td>Class</td>
																						<td><input type="text" data-childfld="child" data-attribute="seattype" data-input="readonly"></td>
																					</tr>
																					<tr>
																						<td>Price</td>
																						<td><input type="text" data-childfld="child" data-attribute="price" data-type="decimal"></td>
																					</tr>
																				</table>
																			</div>
																			<div class="col-md-6">
																				<table class="tableDetailRow">
																					<tr>
																						<td>Entered By</td>
																						<td><input type="text" data-childfld="child" data-attribute="enterby"></td>
																					</tr>
																					<tr>
																						<td>Entered Date</td>
																						<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" ></td>
																					</tr>
																					<tr>
																						<td>Changed By</td>
																						<td><input type="text" data-childfld="child" data-attribute="changeby"></td>
																					</tr>
																					<tr>
																						<td>Changed Date</td>
																						<td><input type="text" data-childfld="child" data-attribute="changedate" data-type="date" ></td>
																					</tr>
																				</table>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													<div class="row eatfix">
														<div class="col-md-6">
															<table width=100%>
																<tr>
																	<td class="maxes_sct_tbl">Weight Based Package Pricing
																	</td>
																</tr>
															</table>
															<div class="tablelist">
															Directly on  <b>Fleet Usage</b> application
															</div>
														</div>
														<div class="col-md-6">
															<table width=100%>
																<tr>
																	<td class="maxes_sct_tbl">Size Based Package Pricing
																	</td>
																</tr>
															</table>
															<div class="tablelist">
																<table id="mxtable_fleetrouteprice2_child" class="display" data-child="fleetrouteprice2"></table>
																<div id="mxtable_fleetrouteprice2_child_detail" class="mxtable_detail">
																	<div class="row eatfix">
																		<div class="col-md-6">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Num.</td>
																					<td><input type="text" data-childfld="child" data-attribute="fleetrouteprice2num" data-key="value" data-input="readonly"></td>
																				</tr>
																				<tr>
																					<td>Fleet</td>
																					<td><input type="text" data-childfld="child" data-attribute="assetnum"  data-childrentable="assets" data-childrencolumn="assetnum" data-input="readonly"></td>
																				</tr>
																				<tr>
																					<td>Packet Type</td>
																					<td><input type="text" data-childfld="child" data-attribute="packtype" data-input="readonly"></td>
																				</tr>
																				<tr>
																					<td>Price</td>
																					<td><input type="text" data-childfld="child" data-attribute="price" data-type="decimal"></td>
																				</tr>
																			</table>
																		</div>
																		<div class="col-md-6">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Entered By</td>
																					<td><input type="text" data-childfld="child" data-attribute="enterby"></td>
																				</tr>
																				<tr>
																					<td>Entered Date</td>
																					<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" ></td>
																				</tr>
																				<tr>
																					<td>Changed By</td>
																					<td><input type="text" data-childfld="child" data-attribute="changeby"></td>
																				</tr>
																				<tr>
																					<td>Changed Date</td>
																					<td><input type="text" data-childfld="child" data-attribute="changedate" data-type="date" ></td>
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
										<div id="relatedlocations" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												
										Passed location
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="addlocations"></div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="tablelist">
													<table id="mxtable_fleetroutelocations_child" class="display" data-child="fleetroutelocations"></table>
													<div id="mxtable_fleetroutelocations_child_detail" class="mxtable_detail">
														<div class="row eatfix">
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td>Location</td>
																		<td><input type="text" data-childfld="child" data-attribute="fleetroutelinenum" data-key="value" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Remarks</td>
																		<td><input type="text" data-childfld="child" data-attribute="description"  ></td>
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