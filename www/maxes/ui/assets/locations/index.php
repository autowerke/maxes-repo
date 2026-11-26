<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
		 <link href='style.css' rel='stylesheet'>
	</head>
	<body id="page-top"  xboset="locations" data-appkeynum = "locnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Location</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Asset</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Meter Reading</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_5">Transaction History</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_6">Map</a></li>
							</ul>
							<div class="maxes04 shadow">
								<div id="maxes02_1_1" class="tab maxes02_1">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									
									
									 <div class="map-accordion-wrapper">
										<div class="map-accordion-header">
											<span class="map-accordion-title">
												<i class="fas fa-map-marked-alt"></i>
												Location Map Overview
											</span>
											<span class="map-accordion-toggle-icon">▼</span>
										</div>
										<div class="map-accordion-content">
											<div id="location-overview-map-container" style="height: 75vh; width: 100%;"></div>
										</div>
									</div>
									
									
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_locations" class="display"></table>
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
										<div class="col-md-2">
											<div id="qrcoderegion">
												<div id='qrcode'></div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Location
														</td>
														<td><input type="text" data-attribute="locnum"  data-type="upper" data-input="required" data-key="value">
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
									<div class="row eatfix ">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Detail
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
													<td>Account Code
													</td>
													<td><input type="text" data-attribute="glaccount" data-lookup="chartofaccount" >
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Type </td>
													<td><input type="text" data-attribute="type" data-lookup="locationtype"></td>
												</tr>
												<tr>
													<td>Company</td>
													<td><input type="text" data-attribute="orgcode" data-input="readonly" ></td>
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
														<td>Location
														</td>
														<td><input type="text" data-attribute="locnum"  data-type="upper" data-input="required" data-key="value">
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
													<td class="maxes_sct_tbl4">Asset
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_assets_child" class="display" data-child="assets"></table>
												<div id="mxtable_assets_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Asset</td>
																	<td><input type="text" data-childfld="child" data-attribute="assetnum" data-key="value" data-input="readonly"></td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_4" class="maxes03">
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Location
														</td>
														<td><input type="text" data-attribute="locnum"  data-type="upper" data-input="required" data-key="value">
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
													<td class="maxes_sct_tbl44">Meter Reading
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>
														<div class="addrow" id="addLocationMeter"></div>
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix" id='meter_tbl'>
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_locationmeter_child" class="display" data-child="locationmeter"></table>
												<div id="mxtable_locationmeter_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Meters</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="meternum" data-key="value" data-input="readonly" data-childrentable="meter" data-childrencolumn="meternum">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="meternum.description" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Type</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="meternum.metertype" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Last Work Order</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="lastwo" data-lookup='workorder'>
																	</td>
																</tr>
																<tr>
																	<td>Measurement Unit</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="unit" data-lookup="measureunit">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Last Reading</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="lastreading" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Last Reading Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="lastreadingdate" data-type="date" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="remarks">
																	</td>
																</tr>
																<tr>
																	<td>Health</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="meterhealthscore">
																	</td>
																</tr>
																<tr>
																	<td>Status</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="meterstatus">
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
								<div id="maxes02_1_5" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Location
														</td>
														<td><input type="text" data-attribute="locnum"  data-type="upper" data-input="required" data-key="value">
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
													<td class="maxes_sct_tbl4"> Transactions
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="tablelist">
											<table id="mxtable_matusetrans_child" class="display" data-child="matusetrans"></table>
											<div id="mxtable_matusetrans_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Item</td>
																<td><input type="text" data-childfld="child" data-attribute="itemnum" data-key="value" data-input="readonly" data-childrentable="item" data-childrencolumn="itemnum"></td>
															</tr>
															<tr>
																<td>Description</td>
																<td><input type="text" data-childfld="child" data-attribute="itemnum.description" data-input="readonly"></td>
															</tr>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_6" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Location
														</td>
														<td><input type="text" data-attribute="locnum"  data-type="upper" data-input="required" data-key="value">
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
													<td class="maxes_sct_tbl4">Map
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Latitude</td>
													<td><input type="text" data-attribute="latitude" ></td>
												</tr>
												<tr>
													<td>Longitute</td>
													<td><input type="text" data-attribute="longitude" ></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
										<table class="tbldata">
												<tr>
													<td>Shape Coordinates</td>
													<td><input type="text" data-attribute="shape_coordinates" ></td>
												</tr>
											</table>
										</div>
									
									</div>
									<div class="row eatfix">
										<div class="col-md-12">

											<div id="map-container">
												<div id="map"></div>
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