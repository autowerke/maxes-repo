<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="pricegroup" data-appkeynum = "pricecode">
		<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
		<div id="content">
		<div class="eat-toolbars"></div>
		<div class="container-fluid">
			<div class="maxes01">
				<ul class="maxes02">
					<li class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
					<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Price Category</a></li>
				</ul>
				<div class="maxes04 shadow">
					<div id="maxes02_1_1" class="tab maxes02_1">
						<div class="row eatfix">
							<div class="col-xl-12 col-lg-12">
								<div class="mb-4">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="card-body">
										<div class="row eatfix">
											<div class="page-header-list">
												<div class="tablelist">
													<div class="tablelist">
														<table id="mxtable_pricegroup" class="display">
														</table>
													</div>
												</div>
											</div>
										</div>
										<hr>
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
											<td>Code
											</td>
											<td><input type="text" name = "pricecategory" data-attribute="pricecategory" data-input="required">
											</td>
										</tr>
										<tr>
											<td>Description
											</td>
											<td><input type="text" name = "description" data-attribute="description">
											</td>
										</tr>
										<tr>
											<td>Rate (%)</td>
											<td><input type="text" data-attribute="pricerate" data-input="required" data-type="decimal"> </td>
										</tr>
										<tr>
											<td>Lower than Normal Price?</td>
											<td><input type="checkbox" data-attribute="islesser"></td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>