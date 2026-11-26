<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
		?>
	</head>

	<body id="page-top" xboset="tools"  data-appkeynum="toolnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">				
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">						
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Tools</a></li>
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
												
													<table id="mxtable_tools" class="display"></table>
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
	<div class="col-md-12">
		<div class="page-header">
			<div class="row eatfix">

				<!-- KOLOM 1 -->
				<div class="col-md-3">
					<table class="tbldata">
						<tr><td>Tool #</td><td><input type="text" data-attribute="toolnum" data-input="required" data-key="value" data-type="upper"></td></tr>
						<tr><td>Name</td><td><input type="text" data-attribute="name" data-input="required"></td></tr>
						<tr><td>Description</td><td><textarea data-attribute="description"></textarea></td></tr>
					</table>
				</div>

				<!-- KOLOM 2 -->
				<div class="col-md-3">
					<div class="maxes_sct_tbl3">Classification</div>
					<table class="tbldata">
						<tr><td>Category</td><td><input type="text" data-attribute="category" data-type="upper"></td></tr>
						<tr><td>Brand</td><td><input type="text" data-attribute="brand" data-type="upper"></td></tr>
						<tr><td>Tool Type</td><td><input type="text" data-attribute="tooltype" data-type="upper"></td></tr>
					</table>
				</div>

				<!-- KOLOM 3 -->
				<div class="col-md-3">
					<div class="maxes_sct_tbl3">Identification & Status</div>
					<table class="tbldata">
						<tr><td>Serial Number</td><td><input type="text" data-attribute="serialnumber"></td></tr>
						<tr><td>Model Number</td><td><input type="text" data-attribute="modelnumber"></td></tr>
						<tr><td>Status</td><td><input type="text" data-attribute="status" data-type="upper" data-input="readonly"></td></tr>
						<tr><td>Active</td><td><input type="checkbox" data-attribute="isactive" checked></td></tr>
					</table>
				</div>

				<!-- KOLOM 4 -->
				<div class="col-md-3">
					<div class="maxes_sct_tbl3">Operational Assignment</div>
					<table class="tbldata">
						<tr><td>WO#</td><td><input type="text" data-attribute="wonum" data-input="readonly"></td></tr>
						<tr><td>Location</td><td><input type="text" data-attribute="locnum" data-type="upper" data-input="readonly"></td></tr>
						<tr><td>Assigned To</td><td><input type="text" data-attribute="personid" data-type="upper" data-input="readonly"></td></tr>
						<tr><td>Measure Unit</td><td><input type="text" data-attribute="measureunit" data-lookup="measureunit"></td></tr>
					</table>
				</div>

			</div>
		</div>
	</div>
</div>

<div class="row eatfix">

	<div class="col-md-3">
		<div class="maxes_sct_tbl3">Procurement</div>
		<table class="tbldata">
			<tr><td>Vendor</td><td><input type="text" data-attribute="vendorcode" data-lookup="vendor"></td></tr>
			<tr><td>Purchase Date</td><td><input type="text" data-attribute="purchasedate" data-type="date"></td></tr>
		</table>
	</div>

	<div class="col-md-3">
		<div class="maxes_sct_tbl3">Cost Information</div>
		<table class="tbldata">
			<tr><td>Unit Cost</td><td><input type="text" data-attribute="unitcost" data-type="decimal"></td></tr>
		</table>
	</div>

	<div class="col-md-3">
		<div class="maxes_sct_tbl3">Warranty</div>
		<table class="tbldata">
			<tr><td>Warranty Expiry</td><td><input type="text" data-attribute="warrantyexpirydate" data-type="date"></td></tr>
		</table>
	</div>

	<div class="col-md-3">
		<div class="maxes_sct_tbl3">Calibration Control</div>
		<table class="tbldata">
			<tr><td>Requires Calibration?</td><td><input type="checkbox" data-attribute="requirecalibration"></td></tr>
			<tr><td>Frequency (Days)</td><td><input type="number" data-attribute="calibrationfrequency"></td></tr>
			<tr><td>Last Calibration</td><td><input type="text" data-attribute="lastcalibrationdate" data-type="date"></td></tr>
			<tr><td>Next Calibration</td><td><input type="text" data-attribute="nextcalibrationdate" data-type="date"></td></tr>
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