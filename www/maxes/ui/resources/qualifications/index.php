<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<! -- xboset adalah nama tabel, data-appkeynum adalah nomor kode/nama  --!> 
	<body id="page-top" xboset="qualification" data-appkeynum = "qualification">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Qualifications</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Related Person</a></li>
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
													<table id="mxtable_qualification" class="display"></table>
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
												<table class="tbldata" >
													<tr>
														<td>Qualification ID
														</td>
														<td><input type="text" name = "qualification" data-attribute="qualification" data-input="required"  data-key="value"  data-type="upper" >
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" name = "description" data-attribute="description">
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
										<div class="col-md-8">
											<table class="tbldata">
												<tr>
													<td>Skill
													</td>
													<td><input type="text" name = "skill" data-attribute="skill">
													</td>
												</tr>
												<tr>
													<td>Certificate
													</td>
													<td><input type="text" name = "certificate" data-attribute="certificate">
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
														<td>Qualification ID
														</td>
														<td><input type="text" name = "qualification" data-attribute="qualification"  data-key="value" data-input="required">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" name = "description" data-attribute="description">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_associatedEmployee_child" class="display">					 
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
		</div>
	</body>
</html>