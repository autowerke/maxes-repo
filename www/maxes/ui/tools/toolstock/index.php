<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php			
			include ("../../../applications/ui/ui.php");
		?>
	</head>
	<body id="page-top" xboset="toolstock"  data-appkeynum="toolstockid">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Tool Stock</a></li>
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
													<table id="mxtable_toolstock" class="display"></table>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_2" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'>Tool Stock</div> <!-- Set App Title -->
									</div>
									<div class="row eatfix">							
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Tool Type</td>
														<td><input type="text" data-attribute="tooltype" data-input="required" data-key="value" data-type="upper" ></td>
													</tr>
													<tr>
														<td>Location</td>
														<td><input type="text" data-attribute="locnum" data-input="required" data-key="value" data-type="upper"></td>
													</tr>
													<tr>
														<td colspan="2"><hr></td>
													</tr>
													<tr>
														<td>Current Balance</td>
														<td><input type="text" data-attribute="curbal" data-type="decimal" readonly></td>
													</tr>
													<tr>
														<td>Quantity Available</td>
														<td><input type="text" data-attribute="qtyavailable" data-type="decimal" readonly ></td>
													</tr>
													<tr>
														<td>Average Cost</td>
														<td><input type="text" data-attribute="averagecost" data-type="decimal"  readonly ></td>
													</tr>
													<tr>
														<td colspan="2"><hr></td>
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