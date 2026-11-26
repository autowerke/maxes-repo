<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="mxsgroups" data-appkeynum = "groupnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Access Group</a></li>
								<li class="maxes02_3"><a href="#maxes02_1_3">User</a></li>
								<li class="maxes02_3"><a href="#maxes02_1_4">Site Authorization</a></li>
								<li class="maxes02_3"><a href="#maxes02_1_5">Field Authorization</a></li>
								<li class="maxes02_3"><a href="#maxes02_1_6">UI Restriction</a></li>
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
													<table id="mxtable_mxsgroups" class="display"></table>
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
														<td>Grup
														</td>
														<td><input type="text" data-attribute="groupnum"  data-input="required" data-key="value" data-type='upper'>
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
									<div class="col-md-12">
										<div class="maxes_sct_tbl">Application Authorization</div>
										<div class="row eatfix">
											<div class="col-md-4">
												<div class="addrow" id="addApp"></div>
											</div>
										</div>
									</div>
									<div id="subform-app"  class="subform" data-child="mxsgroupline" data-table="mxtable_mxsgroupline_child" >
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Application</td>
														<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value" data-lookup="mxsapp" data-childrentable="mxsapp" data-childrencolumn="appname"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-childfld="child" data-attribute="appname.description" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Read</td>
														<td><input type="checkbox" data-childfld="child" data-attribute="canread" ></td>
													</tr>
													<tr>
														<td>Save/Update</td>
														<td><input type="checkbox" data-childfld="child" data-attribute="cansave"></td>
													</tr>
													<tr>
														<td>Home Page?</td>
														<td><input type="checkbox" data-childfld="child" data-attribute="homepage"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_mxsgroupline_child" class="display"  data-subform="subform-app">
												</table>
												<div id="mxtable_mxsgroupline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-3">
															<table class="tableDetailRow">
																<tr>
																	<td>Application</td>
																	<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value" data-lookup="mxsapp" data-childrentable="mxsapp" data-childrencolumn="appname"></td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="appname.description" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-5">
															<table class="tableDetailRow">
																<tr>
																	<td>Read</td>
																	<td><input type="checkbox" data-childfld="child" data-attribute="canread" ></td>
																</tr>
																<tr>
																	<td>Save/Update</td>
																	<td><input type="checkbox" data-childfld="child" data-attribute="cansave"></td>
																</tr>
																<tr>
																	<td>Home Page?</td>
																	<td><input type="checkbox" data-childfld="child" data-attribute="homepage"></td>
																</tr>
															</table>
														</div>
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
													<td class="maxes_sct_tbl">Actions For : <span id="mxtable_groupaction_child_title"></span>
													</td>
												</tr>
											</table>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addAction"></div>
												</div>
											</div>
											<div id="subform-appactions" class="subform" data-child="mxsgroupaction" data-table="mxtable_groupaction_child" data-pk="appname">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tbldata">
															<tr>
																<td>Application</td>
																<td> <input type="text" data-childfld="child" data-attribute="appname" data-alias="appname" data-key="value" data-lookup="mxsapp"> </td>
															</tr>
															<tr>
																<td>Action</td>
																<td> <input type="text" data-childfld="child" data-attribute="actionname" data-key="value" data-lookup="mxsaction|appname"> </td>
															</tr>
														</table>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="page-header-list">
													<div class="tablelist">
														<table id="mxtable_groupaction_child" class="display" data-subform="subform-appactions" data-link="mxtable_mxsgroupline_child">
														</table>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Grup
														</td>
														<td><input type="text" data-attribute="groupnum"  data-input="required" data-key="value" data-type='upper'>
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
									<div class="col-md-12">
										<div class="row eatfix">
											<div class="maxes_sct_tbl">Users Group</div>
										</div>
									</div>
									<div id="subform-mxsusergroup"  class="subform" data-table="mxtable_mxsusergroup_child" data-child="mxsusergroup">
										<div class="row eatfix">
											<div class="col-md-12">
												<table class="tbldata">
													<tr>
														<td>Grup</td>
														<td><input type="text" data-childfld="child" data-attribute="groupnum" data-key="value" data-lookup="mxsgroup" data-childrentable="mxsgroups" data-childrencolumn="groupnum"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-childfld="child" data-attribute="groupnum.description" data-input="readonly"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="row eatfix">
												<div class="page-header-list">
													<div class="tablelist">
														<table id="mxtable_mxsusergroup_child" class="display"  data-subform="subform-mxsusergroup">
														</table>
														<div id="mxtable_mxsusergroup_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td>User ID</td>
																			<td><input type="text" data-childfld="child" data-attribute="userid" data-key="value" data-lookup="mxsgroup"></td>
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
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Grup
														</td>
														<td><input type="text" data-attribute="groupnum"  data-input="required" data-key="value" data-type='upper'>
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
											<div class="maxes_sct_tbl">Site Authorization</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addgroupsite"></div>
												</div>
											</div>
											<div id="subform-mxsgroupsite" class="subform" data-table="mxtable_mxsgroupsite_child" data-child="mxsgroupsite">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tbldata">
															<tr>
																<td>Site</td>
																<td><input type="text" data-childfld="child" data-attribute="sitecode" data-key="value" data-lookup="site" data-childrentable="site" data-childrencolumn="sitecode"></td>
															</tr>
															<tr>
																<td>Description</td>
																<td><input type="text" data-childfld="child" data-attribute="sitecode.description" data-input="readonly"></td>
															</tr>
														</table>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="row eatfix">
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_mxsgroupsite_child" class="display"  data-subform="subform-mxsgroupsite">
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Grup
														</td>
														<td><input type="text" data-attribute="groupnum"  data-input="required" data-key="value" data-type='upper'>
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
									<div class="row eatfix">  </div>
									<div class="col-md-12">
										<div class="row eatfix">
											<div class="maxes_sct_tbl">Field Authorization</div>
										</div>
										<div class="row eatfix">
											<div class="col-md-4">
												<div class="addrow" id="addcolrestriction"></div>
											</div>
										</div>
										<div id="subform-mxsgroupcolres" class="subform" data-table="mxtable_mxsgroupcolres_child" data-child="mxsgroupcolres">
											<div class="row eatfix">
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td>Application</td>
															<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value" data-lookup='mxsapp'></td>
														</tr>
														<tr>
															<td>Field Name</td>
															<td><input type="text" data-childfld="child" data-attribute="attributename" data-key="value"></td>
														</tr>
														<tr>
															<td>Restriction</td>
															<td><input type="text" data-childfld="child" data-attribute="restricttype" data-lookup="restricttype"></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="row eatfix">
												<div class="page-header-list">
													<div class="tablelist">
														<table id="mxtable_mxsgroupcolres_child" class="display"  data-subform="subform-mxsgroupcolres">
														</table>
														<div id="mxtable_mxsgroupcolres_child_detail" class="mxtable_detail">
															<div class="col-md-3">
																<table class="tableDetailRow">
																	<tr>
																		<td>Application</td>
																		<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value" data-lookup='mxsapp'></td>
																	</tr>
																</table>
															</div>
															<div class="col-md-5">
																<table class="tableDetailRow">
																	<tr>
																		<td>Field Name</td>
																		<td><input type="text" data-childfld="child" data-attribute="attributename" ></td>
																	</tr>
																	<tr>
																		<td>Restriction</td>
																		<td><input type="text" data-childfld="child" data-attribute="restricttype" data-lookup="restricttype"></td>
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
								<div id="maxes02_1_6" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Grup
														</td>
														<td><input type="text" data-attribute="groupnum"  data-input="required" data-key="value" data-type='upper'>
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
											<div class="row eatfix">
												<div class="maxes_sct_tbl">Layout Restriction</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addLayoutRes"></div>
												</div>
											</div>
											<div id="subform-mxslayoutres" class="subform" data-table="mxtable_mxslayoutres_child" data-child="mxslayoutres">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tbldata">
															<tr>
																<td>Application</td>
																<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value" data-lookup='mxsapp'></td>
															</tr>
															<tr>
																<td>ID Name</td>
																<td><input type="text" data-childfld="child" data-attribute="idname" data-key="value"><font size=1>Use comma separator; ex: #idname1, #idname2, ...</font></td>
															</tr>
															<tr>
																<td>Restriction</td>
																<td><input type="text" data-childfld="child" data-attribute="restricttype" data-lookup="restricttype"></td>
															</tr>
														</table>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_mxslayoutres_child" class="display"  data-subform="subform-mxslayoutres">
															</table>
															<div id="mxtable_mxslayoutres_child_detail" class="mxtable_detail">
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Application</td>
																			<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value" data-lookup='mxsapp'></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-5">
																	<table class="tableDetailRow">
																		<tr>
																			<td>ID Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="idname"><font size=1>Use comma separator; ex: #idname1, #idname2, ...</font></td>
																		</tr>
																		<tr>
																			<td>Restriction</td>
																			<td><input type="text" data-childfld="child" data-attribute="restricttype" data-lookup="restricttype"></td>
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
	</body>
</html>