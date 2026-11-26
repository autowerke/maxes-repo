<!DOCTYPE html>
<html>
	<title></title>
	<head>

		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="chartofaccount" data-appkeynum = "glaccount">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Account Code</a></li>
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
													<table id="mxtable_chartofaccount" class="display"></table>
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
														<td>Account Code</td>
														<td><input type="text" data-attribute="glaccount" data-input="required" data-key="value"></td>
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
													<td class="maxes_sct_tbl2">Utilization
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Company
													</td>
													<td><input type="text" data-attribute="orgcode" data-lookup="organization">
													</td>
												</tr>
												<tr>
													<td>Site
													</td>
													<td><input type="text" data-attribute="sitecode" data-lookup="site">
													</td>
												</tr>
											</table>
										
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Opposite Transactions
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addglaccount"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_chartofaccountline_child" class="display" data-child="chartofaccountline"></table>
												<div id="mxtable_chartofaccountline_child_detail" class="mxtable_detail">
													<table class="tableDetailRow">
														<tr>
															<td colspan=2>
																<div class="maxes_sct_tbl3">Opposite Transactions GL</div>
															</td>
														</tr>
														<tr>
															<td>Account Code</td>
															<td><input type="text" data-childfld="child" data-attribute="glaccount2" data-key="value" data-input="readonly" readonly data-childrentable="chartofaccount" data-childrencolumn="glaccount"></td>
														</tr>
														<tr>
															<td>Description</td>
															<td><input type="text" data-childfld="child" data-attribute="glaccount.description" data-input="readonly">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Registered Date
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Active Date
													</td>
													<td><input type="text" data-attribute="activedate" data-type="date">
													</td>
												</tr>
												<tr>
													<td>Expired Date
													</td>
													<td><input type="text" data-attribute="expireddate" data-type="date">
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
	</body>
</html>