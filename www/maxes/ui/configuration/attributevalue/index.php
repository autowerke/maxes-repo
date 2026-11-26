<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="mxsattrvalue"  data-appkeynum = "mxsattrvalueid">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Status Value</a></li>
								<li class="maxes02_3"><a href="#maxes02_1_3">Option Value</a></li>
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
													<table id="mxtable_mxsattrvalue" class="display"></table>
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
														<td>ID
														</td>
														<td><input type="text" data-attribute="mxsattrvalueid"  data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Type</td>
														<td><input type="text" data-attribute="type" data-input='required' data-lookup='mxsattrvaluetype'></td>
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
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Name
													</td>
													<td><input type="text" data-attribute="name">
													</td>
												</tr>
												<tr>
													<td>Application
													</td>
													<td><input type="text" data-attribute="app" >
													</td>
												</tr>
												<tr>
													<td>Key Attribute
													</td>
													<td><input type="text" data-attribute="keyattr"  >
													</td>
												</tr>
												<tr>
													<td>Status Level
													</td>
													<td><input type="text" data-attribute="statuslevel" >
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Root Value
													</td>
													<td><input type="text" data-attribute="rootvalue" >
													</td>
												</tr>
												<tr>
													<td>Value 
													</td>
													<td><input type="text" data-attribute="value" >
													</td>
												</tr>
												<tr>
													<td>Value Text
													</td>
													<td><input type="text" data-attribute="valuetext">
													</td>
												</tr>
												<tr>
													<td>Remarks
													</td>
													<td><input type="text" data-attribute="remarks" size=70>
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								<div id="maxes02_1_3" class="maxes03">
									
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
											
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>ID
														</td>
														<td><input type="text" data-attribute="mxsattrvalueid"  data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Type</td>
														<td><input type="text" data-attribute="type" data-input='required' data-lookup='mxsattrvaluetype'></td>
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
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Name
													</td>
													<td><input type="text" data-attribute="name">
													</td>
												</tr>
												<tr>
													<td>Root Value
													</td>
													<td><input type="text" data-attribute="rootvalue" >
													</td>
												</tr>
												<tr>
													<td>Value 
													</td>
													<td><input type="text" data-attribute="value" >
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Value Text
													</td>
													<td><input type="text" data-attribute="valuetext">
													</td>
												</tr>
												<tr>
													<td>Remarks
													</td>
													<td><input type="text" data-attribute="remarks" size=70>
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