<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="timeliner"  data-appkeynum = "timelinernum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Penjadwalan</a></li>
								<li  class="maxes02_2"><a href="#maxes02_1_3">Work Order</a></li>
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
													<table id="mxtable_timeliner" class="display"></table>
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
														<td>Penjadwalan
														</td>
														<td><input type="text" data-attribute="timelinernum" data-input="required" data-key="value" data-type="upper">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Dibuat Oleh
														</td>
														<td><input type="text" data-attribute="createdby" data-input="readonly">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Filters</td>
											</tr>
										</table>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Target Mulai
													</td>
													<td><input type="text" data-type="date" data-attribute="targstartdate">
													</td>
												</tr>
												<tr>
													<td>Target Selesai
													</td>
													<td><input type="text" data-type="date" data-attribute="targcompdate">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Parent WO
													</td>
													<td><input type="text" data-attribute="parent" data-lookup="workorder">
													</td>
												</tr>
												<tr>
													<td>Pembuat/Pemilik 
													</td>
													<td><input type="text" data-attribute="owner" data-lookup="employees">
													</td>
												</tr>
												<tr>
													<td>Leader
													</td>
													<td><input type="text" data-attribute="leader"  data-lookup="employees">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Description
													</td>
													<td><input type="text" data-attribute="wodesc">
													</td>
												</tr>
												<tr>
													<td>Type Pekerjaan
													</td>
													<td><input type="text" data-attribute="worktype" data-lookup="worktype">
													</td>
												</tr>
												<tr>
													<td>Kelas Pekerjaan
													</td>
													<td><input type="text" data-attribute="workclass" data-lookup="workclass">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="col-md-12">
										<div class="gantt-toolbars">
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td class="maxes_sct_tbl">Penjadwalan</td>
															</tr>
														</table>
													</div>
													<div class="col-md-12">
														<table>
															<tr>
																<td style="vertical-align:middle; background:rgb(245,245,245); border:1px solid rgb(189,189,189); padding:5px;" rowspan=3><span class="addrow" id="importwo"></span></td>
																<td style="vertical-align:middle; padding-left:10px;">Lihat Jadwal dalam bentuk : </td>
															</tr>
															<tr>
																<td style="padding-left:10px;">
																	<span class="addrow" id="quarterday"></span>
																	<span class="addrow" id="halfday"></span>
																	<span class="addrow" id="day"></span>
																	<span class="addrow" id="week"></span>
																	<span class="addrow" id="month"></span>
																	<div class="addrow" id="hidetoolbars"></div>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="col-md-6">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td class="maxes_sct_tbl">DetailWO</td>
															</tr>
														</table>
													</div>
													<div class="col-md-12">
														<table>
															<tr>
																<td>
																	<span class="addrow" id="setwopriority"></span>
																	<span class="addrow" id="setactivity"></span>
																	<span class="addrow" id="setwologs"></span>
																	<span class="addrow" id="setwoterms"></span>
																</td>
															</tr>
														</table>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl">Person ID</td>
													</tr>
												</table>
											</div>
											<div class="col-md-12">
												<div class="row eatfix">
													<div class="col-md-6">
														<div class="col-md-12">
															<table width=100%>
																<tr>
																	<td class="maxes_sct_tbl2">Planning</td>
																</tr>
															</table>
														</div>
														<div class="col-md-12">
															<table>
																<tr>
																	<td>Total Planning Material</td>
																	<td><input type="text" data-attribute="totalplannedcost" data-type="decimal" data-input="readonly"></td>
																</tr>
																<tr>
																	<td colspan=2>
																		<span class="addrow" id="planmaterialres"></span>
																		<span class="addrow" id="planserviceres"></span>
																		<span class="addrow" id="planlaborres"></span>
																		<span class="addrow" id="plantoolsres"></span>
																	</td>
																</tr>
															</table>
														</div>
													</div>
													<div class="col-md-6">
														<div class="row eatfix">
															<div class="col-md-12">
																<table width=100%>
																	<tr>
																		<td class="maxes_sct_tbl2">Actual</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-12">
																<table>
																	<tr>
																		<td>Total Actual Cost </td>
																		<td><input type="text" data-attribute="totalactualcost"  data-type="decimal" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td colspan=2>
																			<span class="addrow" id="materialres"></span>
																			<span class="addrow" id="serviceres"></span>
																			<span class="addrow" id="laborres"></span>
																			<span class="addrow" id="toolsres"></span>
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
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="col-md-12">
												<div class="addrow" id="showtoolbars"></div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="mxsgantt-canvas">
												<div class="mxsgantt-container">
													<div class="timeliner"></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_3" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Scheduler ID
														</td>
														<td><input type="text" data-attribute="timelinernum" data-input="required" data-key="value" data-type="upper">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description">
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
													<td class="maxes_sct_tbl">Daftar Work Order</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_timelinerline_child" class="display" data-child="timelinerline"></table>
												<div id="mxtable_timelinerline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>WO
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum" data-childrentable="workorder" data-childrencolumn = "wonum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Targ. Mulai
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.targstartdate" data-type = "date" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Targ. Selesai
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.targcompdate" data-type = "date" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Progress (%)
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.progress" data-type = "decimal" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Status
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.status" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Description
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.description" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Tiket
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.origrecordid" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Pembuat WO
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.owner"  data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Leader
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.leader"  data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Priority
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.priority"  data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Cost Roll Up 
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.rollupcost" data-input="readonly"  data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Actual Cost
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.actualcost" data-input="readonly"  data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Cost Planning 
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.plannedcost"  data-input="readonly" data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Variance Cost
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum.variancecost"  data-input="readonly"  data-type="decimal">
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
			</div>
		</div>
	</body>
</html>