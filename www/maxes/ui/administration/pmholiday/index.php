<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
		?>
	</head>
	<body id="page-top" xboset="pmholiday" data-appkeynum="holidaycode">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li class="maxes02_1" id="list"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Holiday Management</a></li>
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
													<table id="mxtable_pmholiday" class="display"></table>
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
														<td>Holiday Code</td>
														<td><input type="text" data-attribute="holidaycode" data-type="upper" data-key="value" data-input="required" ></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Holiday Type</td>
														<td>
															<select data-attribute="holidaytype" data-input="required">
																<option value="NATIONAL">National Holiday</option>
																<option value="SHUTDOWN">Company Shutdown</option>
																<option value="REGIONAL">Regional Holiday</option>
															</select>
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Start Date</td>
														<td><input type="text" data-type="date" data-attribute="startdate" data-input="required"></td>
													</tr>
													<tr>
														<td>End Date</td>
														<td><input type="text" data-type="date" data-attribute="enddate" data-input="required"></td>
													</tr>
													<tr>
														<td>Applicable Site</td>
														<td><input type="text" data-attribute="sitecode" data-lookup="site"></td>
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
	</body>
</html>