<!DOCTYPE html>
<html>
<title>
</title>
<head>
	<?php
	include ("../../../applications/ui/ui.php");
	?>
</head>
<body id="page-top" xboset="rfidmap"  data-appkeynum = "reader">
<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
		<div id="content">
		<div class="eat-toolbars"></div>
		<div class="container-fluid">
			<div class="maxes01">
    <ul class="maxes02">
        <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">RFID Map</a></li>
    </ul>
    <div class="maxes04">
        <div id="maxes02_1_1" class="tab maxes02_1">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<div class='eat_apptitle'></div>
						</div>
						<div class="row eatfix">
							<div class="col-md-12">
								<div class="page-header-list">
									<div class="tablelist">
										<table id="mxtable_rfidmap" class="display"></table>
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
					<table class="tbldata">
							<tr>
								<td>RFID Map
								</td>
								<td><input type="text" data-attribute="rfidmapnum" data-input="required" data-key="value" data-type="upper">
								</td>
							</tr>
							<tr>
								<td>Reader
								</td>
								<td><input type="text" data-attribute="reader" data-input="required" >
								</td>
							</tr>
							<tr>
								<td>Antenna
									</td>
									<td><input type="text" data-attribute="antenna" data-input="required" >
									</td>
							</tr>
							<tr>
								<td>Warehouse
									</td>
									<td><input type="text" data-attribute="warehouse" data-lookup="warehouse">
									</td>
							</tr>
							<tr>
								<td>Rack
									</td>
									<td><input type="text" data-attribute="racknum" data-lookup="racknum">
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
</body>
</html>
