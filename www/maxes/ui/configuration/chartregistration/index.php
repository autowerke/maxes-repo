<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="mxschart" data-appkeynum = "chartnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Chart</a></li>
							</ul>
							<div class="maxes04">
								<div id="maxes02_1_1" class="tab maxes02_1">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_mxschart" class="display"></table>
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
														<td>Chart ID</td>
														<td><input type="text" data-attribute="chartnum"  data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description" ></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">SQL Chart
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Type</td>
													<td><input type="text" data-attribute="type" data-lookup="charttype"></td>
												</tr>
												<tr>
													<td>Title</td>
													<td><input type="text" data-attribute="title" ></td>
												</tr>
												<tr>
													<td>X-Title</td>
													<td><input type="text" data-attribute="xtitle"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Select</td>
													<td><input type="text" data-attribute="stselect"></td>
												</tr>
												<tr>
													<td>From</td>
													<td><input type="text" data-attribute="stfrom" ></td>
												</tr>
												<tr>
													<td>Where</td>
													<td><input type="text" data-attribute="stwhere"></td>
												</tr>
												<tr>
													<td>Group By</td>
													<td><input type="text" data-attribute="stgroupby"></td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="maxes_sct_tbl2">Chart Preview</div>
											<div class="defchart">
												<canvas id="maxeschart"></canvas>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="maxes_sct_tbl2">Apply to Application</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addApp"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div id="subform-app"  class="subform" data-table="mxtable_mxschartline_child" data-child="mxschartline">
												<div class="row eatfix">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>Line</td>
																<td><input type="text" size=3 data-childfld="child" data-attribute="chartlinenum" data-key="value" data-type="integer"></td>
															</tr>
															<tr>
																<td>Application</td>
																<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value" data-lookup="mxsapp" data-childrentable="mxsapp" data-childrencolumn="appname"></td>
															</tr>
															<tr>
																<td>Description</td>
																<td><input type="text" data-childfld="child" data-attribute="appname.description" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Sort Order</td>
																<td><input type="text" data-childfld="child" data-attribute="sortorder" data-type="integer"></td>
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
												<table id="mxtable_mxschartline_child" class="display" data-subform="subform-app"></table>
												<div id="mxtable_mxschartline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-12">
															<table class="tableDetailRow">
																<tr>
																	<td>Line</td>
																	<td><input type="text" size=3 data-childfld="child" data-attribute="chartlinenum" data-key="value" readonly></td>
																</tr>
																<tr>
																	<td>Application</td>
																	<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value" data-lookup="mxsapp" data-childrentable="mxsapp" data-childrencolumn="appname"></td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="appname.description" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Sort Order</td>
																	<td><input type="text" data-childfld="child" data-attribute="sortorder" data-type="integer"></td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Chart
												</td>
											</tr>
										</table>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Data Label</td>
													<td><input type="text" data-attribute="label" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Data Value</td>
													<td><input type="text" data-attribute="data" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Reverse Axis</td>
													<td><input type="checkbox" data-attribute="revaxis"></td>
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
	</body>
</html>