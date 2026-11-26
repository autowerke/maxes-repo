<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id='page-top' xboset="notification" data-appkeynum = "notifcode">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Notification</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Logs</a></li>
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
													<table id="mxtable_notification" class="display"></table>
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
														<td>Num.tification</td>
														<td><input type="text" data-attribute="notifcode"  data-type="upper" data-input="required" data-key="value"></td>
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
													<td class="maxes_sct_tbl">Messages
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Email Sender</td>
													<td><input type="text" data-attribute="sender"  data-input="required" ></td>
												</tr>
												<tr>
													<td>Title</td>
													<td><input type="text" data-attribute="subject"  data-input="required" ></td>
												</tr>
												<tr>
													<td>Message</td>
													<td><textarea data-attribute="messages" data-input="required" ></textarea></td>
												</tr>
											</table>
										</div>
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Application</td>
													<td><input type="text" data-attribute="appname"  data-lookup="appname"  data-input="required" ></td>
												</tr>
												<tr>
													<td>Organization</td>
													<td><input type="text" data-attribute="orgcode"  data-input="readonly" ></td>
												</tr>
												<tr>
													<td>Site</td>
													<td><input type="text" data-attribute="sitecode" data-input="readonly" ></td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Staff
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addnotifiedperson"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-notificationline" class="subform" data-child="notificationline" data-table="mxtable_notificationline_child">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Line Num.
														</td>
														<td><input type="text" data-childfld="child"  data-type="integer" data-attribute="notiflinenum"  data-input="required">
														</td>
													</tr>
													<tr>
														<td>Employee </td>
														<td><input type="text" data-childfld="child"  data-attribute="personid" data-lookup="employees|orgcode" data-key="value" >
														</td>
													</tr>
													<tr>
														<td>TO? </td>
														<td><input type="checkbox" data-childfld="child"  data-attribute="nto">
														</td>
													</tr>
													<tr>
														<td>CC? </td>
														<td><input type="checkbox" data-childfld="child"  data-attribute="ncc">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_notificationline_child" class="display" data-subform="subform-notificationline"></table>
												<div id="mxtable_notificationline_child_detail" class="mxtable_detail">
													<table class="tableDetailRow">
														<tr>
															<td>Line
															</td>
															<td><input type="text" data-childfld="child"  data-type="integer" data-attribute="notiflinenum"  data-input="required">
															</td>
														</tr>
														<tr>
															<td>Employee </td>
															<td><input type="text" data-childfld="child"  data-attribute="personid" data-lookup="employees|orgcode" data-key="value" >
															</td>
														</tr>
														<tr>
															<td>TO? </td>
															<td><input type="checkbox" data-childfld="child"  data-attribute="nto">
															</td>
														</tr>
														<tr>
															<td>CC? </td>
															<td><input type="checkbox" data-childfld="child"  data-attribute="ncc">
															</td>
														</tr>
													</table>
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
														<td>Num.tification</td>
														<td><input type="text" data-attribute="notifcode"  data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">  </div>
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Log
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_emaillogs_child" class="display" data-child="emaillogs"></table>
											<div id="mxtable_emaillogs_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tableDetailRow">
															<tr>
																<td>Message</td>
																<td><textarea data-childfld="child" data-attribute="messages" data-input="readonly"></textarea></td>
															</tr>
														</table>
													</div>
													<div class="col-md-6">
														<table class="tableDetailRow">
															<tr>
																<td>Status</td>
																<td><input type="text" data-childfld="child" data-attribute="status" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Error </td>
																<td><input type="text" data-childfld="child" data-attribute="errormsg" style="width:300px;" data-input="readonly"></td>
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
	</body>
</html>