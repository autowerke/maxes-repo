<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="exchangerate" data-appkeynum = "currencycodefrom">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Exchange Rates</a></li>
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
													<table id="mxtable_exchangerate" class="display"></table>
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
														<td>Convert From Currency
														</td>
														<td><input type="text" data-attribute="currencycodefrom" data-input="required" data-lookup="currency">
														</td>
													</tr>
													<tr>
														<td>Convert To Currency
														</td>
														<td><input type="text" data-attribute="currencycodeto" data-input="required" data-lookup="currency">
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
													<td class="maxes_sct_tbl">Detail
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Exchange Rates
													</td>
													<td><input type="text" data-attribute="exchangerate" data-input="required" data-type="decimal">
													</td>
												</tr>
												<tr>
													<td>Active Date
													</td>
													<td><input type="text" data-type="date" data-attribute="activedate" data-input="required">
													</td>
												</tr>
												<tr>
													<td>Expired Date
													</td>
													<td><input type="text" data-type="date" data-attribute="expireddate" data-input="required">
													</td>
												</tr>
												<tr>
													<td>Memo
													</td>
													<td><input type="text" data-attribute="memo">
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