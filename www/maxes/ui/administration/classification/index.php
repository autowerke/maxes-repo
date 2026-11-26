<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="classification" data-appkeynum = "classificationcode">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Classification</a></li>
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
													<table id="mxtable_classification" class="display"></table>
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
														<td>Classification</td>
														<td><input type="text" data-attribute="classificationcode"  data-type="upper" data-input="required" data-key="value"></td>
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
													<td class="maxes_sct_tbl">Attributes
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addClassAttributes"></div>
											<span class="addrow" style="width:180px;">
											<a href="javascript:;" id="additionalattr">Add Attribute</a>
											</span>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-classification-attribute" class="subform" data-child="classattribute" data-table="mxtable_classattribute_child">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Attribute Name
														</td>
														<td><input type="text" data-childfld="child"  data-type="upper" data-attribute="attributename"  data-key="value" data-input="required">
														</td>
													</tr>
													<tr>
														<td>Descriptions</td>
														<td><input type="text" data-childfld="child"  data-attribute="description">
														</td>
													</tr>
												</table>
												
											</div>
											
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_classattribute_child" class="display" data-subform="subform-classification-attribute"></table>
												<div id="mxtable_classattribute_child_detail" class="mxtable_detail">
													<table class="tableDetailRow">
														<tr>
															<td colspan=2>
																<div class="maxes_sct_tbl3">Attribute Detail</div>
															</td>
														</tr>
														<tr>
															<td>Attribute Name</td>
															<td><input type="text" data-childfld="child" data-attribute="attributename" data-key="value" data-input="readonly" readonly data-childrentable="attribute" data-childrencolumn="attributename"></td>
														</tr>
														<tr>
															<td>Description</td>
															<td><input type="text" data-childfld="child" data-attribute="attributename.description" data-input="readonly">
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
			</div>
		</div>
	</body>
</html>