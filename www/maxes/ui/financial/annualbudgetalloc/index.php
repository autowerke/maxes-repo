<!DOCTYPE html>
<html>
	<title>Annual Budget Allocation</title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="annualbudgetalloc" data-appkeynum = "annualbudgetallocnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Allocation Details</a></li>
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
													<table id="mxtable_annualbudgetalloc" class="display"></table>
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
														<td>Allocation Number</td>
														<td><input type="text" data-attribute="annualbudgetallocnum" data-input="required" data-key="value" data-type="upper"></td>
													</tr>
													<tr>
														<td>PO Number</td>
														<td><input type="text" data-attribute="ponum"></td>
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
														<td>Budget Year</td>
														<td><input type="text" data-type='number' data-attribute="budgetyear"></td>
													</tr>
													
													<tr>
														<td>Allocated Amount</td>
														<td><input type="text" data-attribute="allocatedamount" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Actual Expense</td>
														<td><input type="text" data-attribute="actualexpense" data-input='readonly' data-type="decimal"></td>
													</tr>
													<tr>
														<td>Remaining Allocation</td>
														<td><input type="text" data-attribute="remainingallocation" data-input='readonly' data-type="decimal"></td>
													</tr>
												</table>
										</div>
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Budget</td>
														<td><input type="text" data-attribute="budgetnum" data-lookup="budget"></td>
													</tr>
													<tr>
														<td>Budget Line Num</td>
														<td><input type="text" data-attribute="budgetlinenum" data-lookup="budgetline|budgetnum"></td>
													</tr>
													<tr>
														<td>Status</td>
														<td><input type="text" data-attribute="status"  data-input='readonly'></td>
													</tr>
													<tr>
														<td>Status Date</td>
														<td><input type="text" data-type='date' data-attribute="statusdate"  data-input='readonly'></td>
													</tr>
													<tr>
														<td>Enter Date</td>
														<td><input type="text" data-type='date' data-attribute="enterdate" data-input='readonly'></td>
													</tr>
													<tr>
														<td>Enter By</td>
														<td><input type="text" data-attribute="enterby" data-input='readonly'></td>
													</tr>
													<tr>
														<td>Change By</td>
														<td><input type="text" data-attribute="changeby" data-input='readonly'></td>
													</tr>
													<tr>
														<td>Change Date</td>
														<td><input type="text" data-type='date' data-attribute="changedate" data-input='readonly'></td>
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