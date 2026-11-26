<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="metergroup" data-appkeynum = "metergroupnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2"> Meter Group</a></li>
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
													<table id="mxtable_metergroup" class="display"></table>
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
														<td>Meter Group
														</td>
														<td><input type="text" data-attribute="metergroupnum" data-type="upper" data-input="required" data-key="value">
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
													<td class="maxes_sct_tbl">Meters</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addMeter"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-meter" class="subform" data-child="relatedmeter" data-table="mxtable_relatedmeter_child">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Num.</td>
														<td colspan=2><input type="text" data-childfld="child" data-attribute="meterlinenum" data-input="required" data-key="value" data-type="integer"></td>
													</tr>
													<tr>
														<td>Meters</td>
														<td colspan=2><input type="text" data-childfld="child" data-attribute="meternum" data-lookup="meter"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td colspan=2><input type="text" data-childfld="child" data-attribute="description" ></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_relatedmeter_child" class="display" data-subform="subform-meter">
												</table>
												<div id="mxtable_relatedmeter_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-5">
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td>Num.</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="meterlinenum" data-input="required" data-key="value" data-type="integer"></td>
																	</tr>
																	<tr>
																		<td>Meters</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="meternum" data-lookup="meter"></td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td colspan=2><input type="text" data-childfld="child" data-attribute="description"></td>
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