<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
		<link href="style.css" rel="stylesheet">
	</head>
	<body id="page-top" xboset="mxsworkflow"  data-appkeynum = "wfname">
		<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
		<div id="content">
		<div class="eat-toolbars"></div>
		<div class="container-fluid">
			<div class="maxes01">
				<ul class="maxes02">
					<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
					<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Workflow Canvas</a></li>
					<li class="maxes02_3"><a href="#maxes02_1_3">Workflow Procedures</a></li>
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
										<table id="mxtable_mxsworkflow" class="display"></table>
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
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Workflow Name
													</td>
													<td><input type="text" data-attribute="wfname"  data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description
													</td>
													<td><input type="text" data-attribute="description" data-input="required" >
													</td>
												</tr>
												<tr>
													<td>Active ?
													</td>
													<td><input type="checkbox" data-attribute="isactive" data-input="required" >
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Application</td>
													<td><input type="text" data-attribute="appname" data-lookup = "mxsapp" data-input="required" ></td>
												</tr>
											</table>
											<div class="row eatfix"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row eatfix">
							<div class="col-md-12">
								<div class="maxes_sct_tbl4">Workflow Canvas</div>
							</div>
						</div>
						<!--
							WORKFLOW BUILDER HTML SNIPPET
							Ganti bagian ini di index.php lo:
							
							BEFORE:
							<div class="row eatfix">
								<div class="col-md-12">
									<div id="workflow-canvas"></div>
								</div>
							</div>
							
							AFTER: (di bawah ini)
							-->
						<div class="row eatfix">
							<div class="col-md-12">
								<!-- TOOLBAR -->
								<div id="wf-toolbar">
									<button id="btn_wf_apply"       class="wf-btn wf-btn-primary">&#128190; Apply</button>
									<div class="wf-toolbar-sep"></div>
									<button id="btn_wf_download"    class="wf-btn" title="Download canvas sebagai PNG">&#128247; Download PNG</button>
									<div class="wf-toolbar-sep"></div>
									<button id="btn_wf_layout"      class="wf-btn" title="Auto-layout nodes">&#9633; Re-Layout</button>
									<span id="wf-dirty-badge">● Unsaved</span>
									<span class="wf-toolbar-label">
									Drag node dari palette. Klik node untuk edit. Tahan &amp; geser untuk pindah.
									</span>
								</div>
								<!-- PALETTE -->
								<div id="wf-palette">
									<span id="wf-palette-label">Nodes:</span>
									<!-- diisi oleh wfbuilder.js _initPalette() -->
								</div>
								<!-- CANVAS WRAPPER -->
								<div id="wf-canvas-container">
									<div id="workflow-canvas"></div>
								</div>
							</div>
						</div>
						
					</div>

					<div id="maxes02_1_3" class="maxes03">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<div class='eat_apptitle'></div>
						</div>
						<div class="container-fluid">
							<!-- Process Nodes Header -->
							<div class="row eatfix">
								<div class="col-md-12">
									<div class="maxes_sct_tbl">Process Nodes</div>
								</div>
							</div>
							<!-- Add Button -->
							<div class="row eatfix mb-2">
								<div class="col-md-4">
									<div class="addrow" id="addProcessNode"></div>
								</div>
							</div>
							<!-- Subform Process Nodes -->
							<div class="row eatfix">
								<div class="col-md-12">
									<div id="subform-mxswfnode" class="subform" data-child="mxswfnode" data-table="mxtable_mxswfnode_child">
										<table class="tbldata compact-form">
											<tr>
												<td>Node Line</td>
												<td><input type="text" data-childfld="child" data-attribute="nodeline" data-key="value" data-type="integer"></td>
											</tr>
											<tr>
												<td>Title</td>
												<td><input type="text" data-childfld="child" data-attribute="title"></td>
											</tr>
											<tr>
												<td>Description</td>
												<td><input type="text" data-childfld="child" data-attribute="description"></td>
											</tr>
											<tr>
												<td>Node Type</td>
												<td><input type="text" data-childfld="child" data-attribute="nodetype" data-lookup="wfnodetype"></td>
											</tr>
											<tr>
												<td>Where Clause</td>
												<td><input type="text" data-childfld="child" data-attribute="whereclause"></td>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<!-- Table List -->
							<div class="row eatfix mt-3">
								<div class="col-md-12">
									<div class="tablelist">
										<table id="mxtable_mxswfnode_child" class="display" data-subform="subform-mxswfnode"></table>
										<div id="mxtable_mxswfnode_child_detail" class="mxtable_detail">
											<div class="col-md-3">
												<table class="tableDetailRow compact-form">
													<tr>
														<td>Node Line</td>
														<td><input type="text" data-childfld="child" data-attribute="nodeline" data-key="value" data-type="integer"></td>
													</tr>
													<tr>
														<td>Title</td>
														<td><input type="text" data-childfld="child" data-attribute="title" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-childfld="child" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Node Type</td>
														<td><input type="text" data-childfld="child" data-attribute="nodetype" data-lookup="wfnodetype"></td>
													</tr>
													<tr>
														<td>Where Clause</td>
														<td><input type="text" data-childfld="child" data-attribute="whereclause"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- Node Actions Header -->
							<div class="row eatfix mt-4">
								<div class="col-md-12">
									<div class="maxes_sct_tbl">
										Node Actions <span id="mxtable_mxswfaction_child_title"></span>
									</div>
								</div>
							</div>
							<!-- Add Action Button -->
							<div class="row eatfix mb-2">
								<div class="col-md-4">
									<div class="addrow" id="addwfaction"></div>
								</div>
							</div>
							<!-- Subform Node Actions -->
							<div class="row eatfix">
								<div class="col-md-12">
									<div id="subform-mxswfaction" class="subform" data-child="mxswfaction" data-table="mxtable_mxswfaction_child" data-pk="nodeline">
										<div class="row">
											<!-- Left Column -->
											<div class="col-md-6">
												<table class="tbldata compact-form">
													<tr>
														<td>Line</td>
														<td><input type="text" data-childfld="child" data-attribute="nodeline" data-alias="nodeline" data-type="integer"></td>
													</tr>
													<tr>
														<td>To Line</td>
														<td><input type="text" data-childfld="child" data-attribute="tonodeline" data-type="integer" data-lookup="mxswfnode|wfname" data-childrentable="mxswfnode" data-childrencolumn="nodeline,wfname"></td>
													</tr>
													<tr>
														<td>Instruction</td>
														<td><input type="text" data-childfld="child" data-attribute="instruction" data-crossvalue="tonodeline.description"></td>
													</tr>
												</table>
											</div>
											<!-- Right Column -->
											<div class="col-md-6">
												<table class="tbldata compact-form">
													<tr>
														<td>Action</td>
														<td><input type="text" data-childfld="child" data-attribute="action" data-lookup="mxswfattribute"></td>
													</tr>
													<tr>
														<td>Is Negative?</td>
														<td><input type="checkbox" data-childfld="child" data-attribute="isnegative"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- Node Actions Table -->
							<div class="row eatfix mt-3">
								<div class="col-md-12">
									<div class="tablelist">
										<table id="mxtable_mxswfaction_child" class="display" data-subform="subform-mxswfaction" data-link="mxtable_mxswfnode_child"></table>
										<div id="mxtable_mxswfaction_child_detail" class="mxtable_detail">
											<div class="row">
												<div class="col-md-6">
													<table class="tableDetailRow compact-form">
														<tr>
															<td>Node Line</td>
															<td><input type="text" data-childfld="child" data-attribute="nodeline" data-alias="nodeline" data-type="integer"></td>
														</tr>
														<tr>
															<td>To Line</td>
															<td><input type="text" data-childfld="child" data-attribute="tonodeline" data-type="integer" data-lookup="mxswfnode|wfname" data-childrentable="mxswfnode" data-childrencolumn="wfname"></td>
														</tr>
														<tr>
															<td>Instruction</td>
															<td><input type="text" data-childfld="child" data-attribute="instruction" data-crossvalue="tonodeline.description"></td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tableDetailRow compact-form">
														<tr>
															<td>Action</td>
															<td><input type="text" data-childfld="child" data-attribute="action" data-lookup="mxswfattribute"></td>
														</tr>
														<tr>
															<td>Is Negative?</td>
															<td><input type="checkbox" data-childfld="child" data-attribute="isnegative"></td>
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