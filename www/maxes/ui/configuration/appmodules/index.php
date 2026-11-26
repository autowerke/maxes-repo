<!DOCTYPE html>
<html lang="en">
	<head>
	
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="mxsmodule" data-appkeynum = "modulename">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Application</a></li>
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
													<table id="mxtable_mxsmodule" class="display"></table>
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
														<td>Module Name
														</td>
														<td><input type="text" data-attribute="modulename"  data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description" data-input="required" >
														</td>
													</tr>
													<tr>
														<td>Order</td>
														<td><input type="text" data-attribute="sort" data-type="integer"  data-input="required" ></td>
													</tr>
													<tr>
														<td>Icon</td>
														<td><input type="text" data-attribute="icon" ></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class ="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Application</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addapplication"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-application" class="subform" data-child="mxsapp" data-table="mxtable_mxsapp_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Application Name</td>
															<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value"> </td>
														</tr>
														<tr>
															<td>Description</td>
															<td><input type="text" data-childfld="child" data-attribute="description"> </td>
														</tr>
														<tr>
															<td>URL</td>
															<td><input type="text" data-childfld="child" data-attribute="url"></td>
														</tr>
														<tr>
															<td>Order</td>
															<td><input type="text" data-childfld="child" data-attribute="sort"></td>
														</tr>
														<tr>
															<td>Status Table</td>
															<td><input type="text" data-childfld="child" data-attribute="statustable"></td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Icon</td>
															<td><input type="text" data-childfld="child" data-attribute="icon"></td>
														</tr>
														<tr>
															<td>Divider</td>
															<td><input type="text" data-childfld="child" data-attribute="divider"></td>
														</tr>
														<tr>
															<td>Divider Title</td>
															<td><input type="text" data-childfld="child" data-attribute="divtitle"></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_mxsapp_child" class="display"  data-subform="subform-application">
												</table>
												<div id="mxtable_mxsapp_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Application Name</td>
																	<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value"> </td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="description"> </td>
																</tr>
																<tr>
																	<td>URL</td>
																	<td><input type="text" data-childfld="child" data-attribute="url"></td>
																</tr>
																<tr>
																	<td>Order</td>
																	<td><input type="text" data-childfld="child" data-attribute="sort"></td>
																</tr>
																<tr>
																	<td>Status Table</td>
																	<td><input type="text" data-childfld="child" data-attribute="statustable"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Icon</td>
																	<td><input type="text" data-childfld="child" data-attribute="icon"></td>
																</tr>
																<tr>
																	<td>Divider</td>
																	<td><input type="checkbox" data-childfld="child" data-attribute="divider"></td>
																</tr>
																<tr>
																	<td>Divider Title</td>
																	<td><input type="text" data-childfld="child" data-attribute="divtitle"></td>
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
											<div class="row eatfix">
												<div class="col-md-6">
													<table width=100%>
														<tr>
															<td class="maxes_sct_tbl">Action <span id="mxtable_mxsaction_child_title"></span>
															</td>
														</tr>
													</table>
													<div class="addrow" id="addAppActions"></div>
												</div>
												<div class="col-md-6">
													<table width=100%>
														<tr>
															<td class="maxes_sct_tbl">Data Loader <span id="mxtable_mxsimportdata_child_title"></span>
															</td>
														</tr>
													</table>
													<div class="addrow" id="addAppDataImport"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div id="subform-mxsaction" class="subform" data-child="mxsaction" data-table="mxtable_mxsaction_child"  data-pk="appname">
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>Application</td>
																	<td> <input type="text" data-childfld="child" data-attribute="appname" data-alias="actionname" data-key="value"> </td>
																</tr>
																<tr>
																	<td>Action</td>
																	<td> <input type="text" data-childfld="child" data-attribute="actionname" data-alias="actionname" data-key="value"> </td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td> <input type="text" data-childfld="child" data-attribute="description" > </td>
																</tr>
																<tr>
																	<td>Order</td>
																	<td> <input type="text" data-childfld="child" data-attribute="Sort" data-type="integer"> </td>
																</tr>
																
															</table>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div id="subform-importdata" class="subform" data-child="mxsimportdata" data-table="mxtable_mxsimportdata_child"  data-pk="appname">
														<div class="row eatfix">
															<!-- KOLOM KIRI -->
															<div class="col-md-6">
																<table class="tbldata">
																	<tr>
																		<td>Application</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="appname" data-key="value">
																		</td>
																	</tr>
																	<tr>
																		<td>Object Name</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="objectname">
																		</td>
																	</tr>
																	<tr>
																		<td>File Name</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="filename">
																		</td>
																	</tr>
																</table>
															</div>

															<!-- KOLOM KANAN -->
															<div class="col-md-6">
																<table class="tbldata">
																	<tr>
																		<td>Action</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="actionname">
																		</td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="description">
																		</td>
																	</tr>
																	<td>
																		<select data-childfld="child" data-attribute="isinsertupdate" required>
																			<option value="">-- Select Procedure --</option>
																			<option value="INSERT">INSERT</option>
																			<option value="INSERTUPDATE">INSERTUPDATE</option>
																		</select>
																	</td>
																</table>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_mxsaction_child" class="display" data-subform="subform-mxsaction" data-link="mxtable_mxsapp_child">
															</table>
															<div id="mxtable_mxsaction_child_detail" class="mxtable_detail">
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Application</td>
																			<td> <input type="text" data-childfld="child" data-attribute="appname" data-alias="appname" data-key="value"> </td>
																		</tr>
																		<tr>
																			<td>Action</td>
																			<td> <input type="text" data-childfld="child" data-attribute="actionname" data-alias="actionname" data-key="value"> </td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td> <input type="text" data-childfld="child" data-attribute="description" > </td>
																		</tr>
																		<tr>
																			<td>Order</td>
																			<td> <input type="text" data-childfld="child" data-attribute="Sort" data-type="integer"> </td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_mxsimportdata_child" class="display" data-subform="subform-importdata" data-link="mxtable_mxsapp_child">
															</table>
															<div id="mxtable_mxsimportdata_child_detail" class="mxtable_detail">
																
															<div class="row eatfix">
																<!-- KOLOM KIRI -->
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Application</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="appname" data-key="value">
																			</td>
																		</tr>
																		<tr>
																			<td>Object Name</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="objectname">
																			</td>
																		</tr>
																		<tr>
																			<td>File Name</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="filename">
																			</td>
																		</tr>
																	</table>
																</div>

																<!-- KOLOM KANAN -->
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Action</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="actionname">
																			</td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="description">
																			</td>
																		</tr>
																		<tr>
																			<td>Procedure</td>
																			<td>
																				<select data-childfld="child" data-attribute="isinsertupdate" required>
																					<option value="">-- Select Procedure --</option>
																					<option value="INSERT">INSERT</option>
																					<option value="INSERTUPDATE">INSERTUPDATE</option>
																				</select>
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
			</div>
		</div>
	</body>
</html>