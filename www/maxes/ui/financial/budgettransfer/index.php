<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="budgettransfer" data-appkeynum = "budgettransfernum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Transfer Details</a></li>
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
													<table id="mxtable_budgettransfer" class="display"></table>
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
														<td>Transfer Number</td>
														<td><input type="text" data-attribute="budgettransfernum" data-input="required" data-key="value" data-type="upper"></td>
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
										<div class="maxes_sct_tbl">Detail</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
													<tr>
														<td>Requested Date</td>
														<td><input type="text" data-type='date' data-attribute="requesteddate"></td>
													</tr>
													<tr>
														<td>Transfer Date</td>
														<td><input type="text" data-type='date' data-attribute="transferdate"></td>
													</tr>
													<tr>
														<td>From Budget</td>
														<td><input type="text" data-attribute="frombudgetnum" data-lookup="budget"></td>
													</tr>
													<tr>
														<td>From Budget Line</td>
														<td><input type="text" data-attribute="frombudgetlinenum" data-lookup="budgetline|frombudgetnum"></td>
													</tr>
													<tr>
														<td>To Budget</td>
														<td><input type="text" data-attribute="tobudgetnum" data-lookup="budget"></td>
													</tr>
													<tr>
														<td>To Budget Line</td>
														<td><input type="text" data-attribute="tobudgetlinenum" data-lookup="budgetline|tobudgetnum"></td>
													</tr>
													<tr>
														<td>Transfer Amount</td>
														<td><input type="text" data-attribute="transferamount" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Reason</td>
														<td><input type="text" data-attribute="transferreason"></td>
													</tr>
												</table>
										</div>
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Requestor</td>
														<td><input type="text" data-attribute="requestor"></td>
													</tr>
													<tr>
														<td>Status</td>
														<td><input type="text" data-attribute="status" data-input='readonly'></td>
													</tr>
													<tr>
														<td>Status Date</td>
														<td><input type="text" data-attribute="statusdate" data-input='readonly'></td>
													</tr>
													<tr>
														<td>Approval Date</td>
														<td><input type="text" data-attribute="approvaldate" data-input='readonly'></td>
													</tr>
													<tr>
														<td>Approver</td>
														<td><input type="text" data-attribute="approver"></td>
													</tr>
													<tr>
														<td>Comments</td>
														<td><textarea data-attribute="comments" data-input='readonly'></textarea></td>
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