<!DOCTYPE html>
<html>
	<title></title>
	<head> <?php
		include ("../../../applications/ui/ui.php");
		?> </head>
	<body data-appkeynum="budgetnum" id="page-top" xboset="budget">
		<div id="wrapper">
			<div id="sidebarmenu"></div>
			<div class="d-flex flex-column" id="content-wrapper">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div id="default_display" class="maxes01">
							<ul class="maxes02">
								<li class="maxes02_1" id="list">
									<a href="#maxes02_1_1">List</a>
								</li>
								<li class="maxes02_2" id="detailrec">
									<a href="#maxes02_1_2">Budget Detail</a>
								</li>
								<li class="maxes02_2" >
									<a href="#maxes02_1_3">Budget Insights</a>
								</li>
							</ul>
							<div class="maxes04 shadow">
								<div class="maxes02_1 tab" id="maxes02_1_1">
									<div class="d-flex align-items-center card-header flex-row justify-content-between py-3">
										<div class="eat_apptitle"></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header-list">
												<div class="tablelist">
													<table class="display" id="mxtable_budget"></table>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="maxes03" id="maxes02_1_2">
									<div class="d-flex align-items-center card-header flex-row justify-content-between py-3">
										<div class="eat_apptitle"></div>
									</div>
									<div class="row eatfix">
										<!-- Kolom 1 -->
										<div class="col-md-3">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Budget Group</td>
														<td>
															<input type="text" data-attribute="budgetnum" data-input="required" data-type="upper" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Budget Period (year)</td>
														<td>
															<input type="text" data-attribute="budgetperiod" data-type="integer" data-input="required">
														</td>
													</tr>
													<tr>
														<td>Budget Version</td>
														<td>
															<input type="text" data-attribute="budgetversion">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													
												</table>
											</div>
										</div>
										<!-- Kolom 2 -->
										<div class="col-md-3">
											<div>
												<table class="tbldata">
													<tr>
														<td>Cannot Approve when Budget Exceeds?</td>
														<td>
															<input type="checkbox" data-attribute="isexceeds">
														</td>
													</tr>
													<tr>
														<td>Status</td>
														<td>
															<input type="text" data-attribute="status" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Status Date</td>
														<td>
															<input type="text" data-attribute="statusdate" data-type="date" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Entered By</td>
														<td>
															<input type="text" data-attribute="enterby" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Entered Date</td>
														<td>
															<input type="text" data-attribute="enterdate" data-input="readonly" data-type="date">
														</td>
													</tr>
													
												</table>
											</div>
										</div>
										<!-- Kolom 3 -->
										<div class="col-md-3">
											<div>
												<table class="tbldata">
													<tr>
														<td>Change By</td>
														<td>
															<input type="text" data-attribute="changeby" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Change Date</td>
														<td>
															<input type="text" data-attribute="changedate" data-input="readonly" data-type="date">
														</td>
													</tr>
													<tr>
														<td>Total Budget Amount</td>
														<td>
															<input type="text" data-attribute="totalbudgetamount" data-type="decimal" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Total Commited Amount</td>
														<td>
															<input type="text" data-attribute="totalcommittedamount" data-type="decimal" data-input="readonly">
														</td>
													</tr>
												</table>
											</div>
										</div>
										<!-- Kolom 4 -->
										<div class="col-md-3">
											<div>
												<table class="tbldata">
													<tr>
														<td>Total Allocated Amount</td>
														<td>
															<input type="text" data-attribute="totalallocatedamount" data-type="decimal" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Total Remaining Amount</td>
														<td>
															<input type="text" data-attribute="totalremainingamount" data-type="decimal" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Total Actual Expense</td>
														<td>
															<input type="text" data-attribute="totalactualexpense" data-type="decimal" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Total Actual Amount</td>
														<td>
															<input type="text" data-attribute="totalactualamount" data-type="decimal" data-input="readonly">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width="100%">
												<tr>
													<td class="maxes_sct_tbl2">Budget Lines</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addBudgetLine"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="subform" id="subform-budget-budgetline" data-child="budgetline" data-table="mxtable_budgetline_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Budget Line Number</td>
															<td>
																<input type="text" data-attribute="budgetlinenum" data-input="required" data-type="integer"  data-childfld="child">
															</td>
														</tr>
														<tr>
															<td>Budget Code</td>
															<td>
																<input type="text" data-attribute="budgetlinecode" data-childfld="child" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Description</td>
															<td>
																<input type="text" data-attribute="description" data-childfld="child">
															</td>
														</tr>
														<tr>
															<td>Created Date</td>
															<td>
																<input type="text" data-attribute="createddate" data-childfld="child" data-type="date">
															</td>
														</tr>
														<tr>
															<td>Closing Date</td>
															<td>
																<input type="text" data-attribute="closingdate" data-childfld="child" data-type="date">
															</td>
														</tr>
														
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Funding Source</td>
															<td colspan="2">
																<input type="text" data-attribute="fundingsource" data-childfld="child">
															</td>
														</tr>
														<tr>
															<td>Budget Amount</td>
															<td colspan="2">
																<input type="text" data-attribute="budgetamount" data-type="decimal" data-childfld="child">
															</td>
														</tr>
														<tr>
															<td>WBS Code</td>
															<td>
																<input type="text" data-attribute="wbscode">
															</td>
														</tr>
														<tr>
															<td>Budget Status</td>
															<td>
																<select name="status" data-attribute="status" data-childfld="child" style="width:250px;">
																	<option value="">-- Select Budget Status --</option>
																	<option value="ADMINISTRASI">Proses Administrasi</option>
																	<option value="PEKERJAAN">Proses Pekerjaan</option>
																	<option value="BAPP">Proses BAPP/TUG 3-4</option>
																	<option value="NOTABUKU">Proses Nota Buku</option>
																	<option value="SELESAI">Selesai</option>
																	<option value="DROP">Drop</option>
																	<option value="PEN_EXTERNAL">Penugasan External</option>
																	<option value="BATAL">Batal</option>
																</select>
															</td>
														</tr>
													</table>
													</table>
												</div>
											</div>
										</div>
										<div class="info"></div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table class="display" id="mxtable_budgetline_child" data-subform="subform-budget-budgetline"></table>
												<div class="mxtable_detail" id="mxtable_budgetline_child_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Budget Line Number</td>
																	<td>
																		<input type="text" data-attribute="budgetlinenum" data-input="readonly" data-type="integer" data-childfld="child">
																	</td>
																</tr>
																<tr>
																	<td>Budget Line Code</td>
																	<td>
																		<input type="text" data-attribute="budgetlinecode" data-childfld="child" data-key="value" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-attribute="description" data-childfld="child">
																	</td>
																</tr>
																<tr>
																	<td>Created Date</td>
																	<td>
																		<input type="text" data-attribute="createddate" data-childfld="child" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Closing Date</td>
																	<td>
																		<input type="text" data-attribute="closingdate" data-childfld="child" data-type="date">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Funding Source</td>
																	<td>
																		<input type="text" data-attribute="fundingsource" data-childfld="child">
																	</td>
																</tr>
																<tr>
																	<td>Budget Amount</td>
																	<td>
																		<input type="text" data-attribute="budgetamount"  data-type="decimal" data-childfld="child" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>WBS Code</td>
																	<td>
																		<input type="text" data-attribute="wbscode">
																	</td>
																</tr>
																<tr>
																	<td>Budget Status</td>
																	<td>
																		<select name="status" data-attribute="status" data-childfld="child" style="width:250px;">
																			<option value="">-- Select Budget Status --</option>
																			<option value="ADMINISTRASI">Proses Administrasi</option>
																			<option value="PEKERJAAN">Proses Pekerjaan</option>
																			<option value="BAPP">Proses BAPP/TUG 3-4</option>
																			<option value="NOTABUKU">Proses Nota Buku</option>
																			<option value="SELESAI">Selesai</option>
																			<option value="DROP">Drop</option>
																			<option value="PEN_EXTERNAL">Penugasan External</option>
																			<option value="BATAL">Batal</option>
																		</select>
																	</td>
																</tr>
																<tr>
																	<td>Change By</td>
																	<td>
																		<input type="text" data-attribute="changeby" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Change Date</td>
																	<td>
																		<input type="text" data-attribute="changedate"  data-input="readonly">
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
								<div class="maxes03" id="maxes02_1_3">
									<div class="d-flex align-items-center card-header flex-row justify-content-between py-3">
										<div class="eat_apptitle"></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-3">
											<div>
												<table class="tbldata">
													<tr>
														<td>Budget Number</td>
														<td>
															<input type="text" data-attribute="budgetnum" data-input="required" data-type="upper" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Budget Period (year)</td>
														<td>
															<input type="text" data-attribute="budgetperiod" data-type="integer" data-input="required">
														</td>
													</tr>
													<tr>
														<td>Budget Version</td>
														<td>
															<input type="text" data-attribute="budgetversion">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>WBS Code</td>
														<td>
															<input type="text" data-attribute="wbscode">
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
													<td class="maxes_sct_tbl4">Budget Insights
												</tr>
											</table>
										</div>
									</div>
									<div class = "row eatfix">
										<div class="col-md-12">
											<div class="budgetInsight_layout">
												<!-- HEADER -->
												<div class="budgetInsight_header">
													<h1 class="budgetInsight_title">Budget Insights Monitoring</h1>
													<div class="budgetInsight_badge">Fiscal Year 2026</div>
												</div>
												<!-- KPI ROW (4 Cards) -->
												<div class="budgetInsight_kpi_grid">
													<div class="budgetInsight_card budgetInsight_kpi_card">
														<div class="budgetInsight_kpi_label">Total Budget Amount</div>
														<div class="budgetInsight_kpi_value" id="bi_kpi_total">Rp 0</div>
													</div>
													<div class="budgetInsight_card budgetInsight_kpi_card">
														<div class="budgetInsight_kpi_label">Total Committed (PO)</div>
														<div class="budgetInsight_kpi_value budgetInsight_text_warn" id="bi_kpi_commit">Rp 0</div>
													</div>
													<div class="budgetInsight_card budgetInsight_kpi_card">
														<div class="budgetInsight_kpi_label">Total Actual Expense</div>
														<div class="budgetInsight_kpi_value budgetInsight_text_danger" id="bi_kpi_actual">Rp 0</div>
													</div>
													<div class="budgetInsight_card budgetInsight_kpi_card">
														<div class="budgetInsight_kpi_label">Total Remaining</div>
														<div class="budgetInsight_kpi_value budgetInsight_text_success" id="bi_kpi_remain">Rp 0</div>
													</div>
												</div>
												<!-- ═══════════════════════════════════════════════════════
													ROW 1: Master Chart (kiri) + PO Type Donut (kanan)
													2 kolom — ratio 60:40
													════════════════════════════════════════════════════════ -->
												<div class="budgetInsight_row_2col budgetInsight_mt_0" style="display:grid;grid-template-columns:3fr 2fr;gap:10px;margin-bottom:20px;">
													<!-- Kolom Kiri: Master Bar Chart -->
													<div class="budgetInsight_card">
														<div class="budgetInsight_card_header">
															<h2 class="budgetInsight_card_title">Department Budget Allocation & Absorption</h2>
															<span class="budgetInsight_hint">Click on a budget bar to view breakdown</span>
														</div>
														<div id="budgetInsight_chart_master" class="budgetInsight_chart_container" style="height:350px;"></div>
													</div>
													<!-- Kolom Kanan: PO Type Doughnut -->
													<div class="budgetInsight_card">
														<div class="budgetInsight_card_header">
															<h2 class="budgetInsight_card_title" id="bi_title_potype">Committed Breakdown by PO Type</h2>
														</div>
														<div id="budgetInsight_chart_potype" class="budgetInsight_chart_container" style="height:350px;"></div>
													</div>
												</div>

												<!-- 
													═══════════════════════════════════════════════════════
													ROW 2: Work Orders
													═══════════════════════════════════════════════════════
													
												-->
													<div class="budgetInsight_card" style="margin-bottom:24px;">

														<!-- Card Header -->
														<div class="budgetInsight_card_header">
															<h2 class="budgetInsight_card_title" id="bi_title_wo">Work Order Overview</h2>
															<div style="display:flex;align-items:center;gap:8px;">
																<span id="bi_wo_count" class="budgetInsight_badge"
																	style="background-color:var(--bi-text-muted);">0 WO</span>
																<span class="budgetInsight_hint">Click Gantt bar for detail</span>
															</div>
														</div>

														<!-- Mini KPI — 7 angka -->
														<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:12px;margin-bottom:16px;">
															<div class="budgetInsight_card" style="padding:12px;box-shadow:none;border-color:#F0F4F8;">
																<div class="budgetInsight_kpi_label">Total WO</div>
																<div class="budgetInsight_kpi_value_small" id="bi_wo_total">—</div>
															</div>
															<div class="budgetInsight_card" style="padding:12px;box-shadow:none;border-color:#F0F4F8;">
																<div class="budgetInsight_kpi_label">Completed</div>
																<div class="budgetInsight_kpi_value_small budgetInsight_text_success" id="bi_wo_completed">—</div>
															</div>
															<div class="budgetInsight_card" style="padding:12px;box-shadow:none;border-color:#F0F4F8;">
																<div class="budgetInsight_kpi_label">On Track</div>
																<div class="budgetInsight_kpi_value_small budgetInsight_text_primary" id="bi_wo_on_track"
																	style="color:var(--bi-color-primary);">—</div>
															</div>
															<div class="budgetInsight_card" style="padding:12px;box-shadow:none;border-color:#F0F4F8;">
																<div class="budgetInsight_kpi_label">Overdue</div>
																<div class="budgetInsight_kpi_value_small budgetInsight_text_danger" id="bi_wo_overdue">—</div>
															</div>
															<div class="budgetInsight_card" style="padding:12px;box-shadow:none;border-color:#F0F4F8;">
																<div class="budgetInsight_kpi_label">Not Started</div>
																<div class="budgetInsight_kpi_value_small budgetInsight_text_warn" id="bi_wo_not_started">—</div>
															</div>
															<div class="budgetInsight_card" style="padding:12px;box-shadow:none;border-color:#F0F4F8;">
																<div class="budgetInsight_kpi_label">Avg Progress</div>
																<div class="budgetInsight_kpi_value_small" id="bi_wo_avg_progress">—</div>
															</div>
															<div class="budgetInsight_card" style="padding:12px;box-shadow:none;border-color:#F0F4F8;">
																<div class="budgetInsight_kpi_label">Avg Health</div>
																<div class="budgetInsight_kpi_value_small" id="bi_wo_avg_health">—</div>
															</div>
														</div>

														<!-- Gantt Chart -->
														<div id="budgetInsight_chart_wo_gantt"
															class="budgetInsight_chart_container"
															style="height:380px;">
														</div>

														<!-- Divider -->
														<div style="border-top:1px solid #E1E8ED;margin:20px 0 12px 0;"></div>

														<!-- Sub-header tabel -->
														<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
															<span style="font-size:13px;font-weight:600;color:#5C7080;
																		text-transform:uppercase;letter-spacing:0.4px;">
																Work Order Detail
															</span>
															<span class="budgetInsight_hint">Sorted by urgency (countdown)</span>
														</div>

														<!-- DataTable WO -->
														<div style="overflow-x:auto;">
															<table id="bi_table_workorder" class="budgetInsight_table" style="width:100%;">
																<thead>
																	<tr>
																		<th>WO Number</th>
																		<th>Description</th>
																		<th>Status</th>
																		<th>Work Class</th>
																		<th>Owner</th>
																		<th>Leader</th>
																		<th>Asset</th>
																		<th>Health</th>
																		<th>Customer</th>
																		<th>Targ. Start</th>
																		<th>Targ. Finish</th>
																		<th>Act. Start</th>
																		<th>Act. Finish</th>
																		<th>Progress</th>
																		<th>Countdown</th>
																		<th>Budget Line</th>
																		<th>Budget Line 2</th>
																		<th>Failure Code</th>

																	</tr>
																</thead>
																<tfoot>
																	<tr>
																		<th></th> <!-- text:     WO Number    -->
																		<th></th> <!-- text:     Description  -->
																		<th></th> <!-- dropdown: Status       -->
																		<th></th> <!-- dropdown: Work Class   -->
																		<th></th> <!-- dropdown: Owner        -->
																		<th></th> <!-- dropdown: Leader       -->
																		<th></th> <!-- text:     Asset        -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- dropdown: Customer     -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- -                      -->
																		<th></th> <!-- text:     Failure Code -->
																	</tr>
																</tfoot>
																<tbody></tbody>
															</table>
														</div>

													</div>
												<!-- ═══════════════════════════════════════════════════════
													ROW 3: Execution MLST (kiri) + Production Log (kanan)
													2 kolom — ratio 1:1
													════════════════════════════════════════════════════════ -->
												<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
													<!-- Kolom Kiri: Execution MLST -->
													<div class="budgetInsight_card">
														<div class="budgetInsight_card_header">
															<h2 class="budgetInsight_card_title" id="bi_title_execution">Execution Trace (MLST)</h2>
														</div>
														<div id="budgetInsight_chart_execution" class="budgetInsight_chart_container" style="height:300px;"></div>
													
														<div style="border-top:1px solid #E1E8ED;margin:16px 0 12px 0;"></div>
														<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
															<span style="font-size:13px;font-weight:600;color:#5C7080;text-transform:uppercase;letter-spacing:0.4px;">
																Material Usage Detail
															</span>
															<span class="budgetInsight_hint">Stock balance &amp; reservation status</span>
														</div>
														<div class="bi_mlst_table_wrap" style="overflow-x:auto;">
														<table id="bi_table_mlst_detail" class="budgetInsight_table" style="width:100%;">
															<thead>
																<tr>
																	<th>WO</th>
																	<th>Item</th>
																	<th>Iss. Qty</th>
																	<th>Resvd Qty</th>
																	<th>Resvd Status</th>
																	<th>Stock</th>
																	<th>Unit Cost</th>
																	<th>Line Cost</th>
																	<th>Batch</th>
																	<th>Reqs By</th>
																</tr>
															</thead>
															<tfoot>
																<tr>
																	<th></th> <!-- dropdown: WO Number     -->
																	<th></th> <!-- text:     Item No.       -->
																	<th></th> <!-- -                        -->
																	<th></th> <!-- -                        -->
																	<th></th> <!-- dropdown: Reserve Status -->
																	<th></th> <!-- -                        -->
																	<th></th> <!-- -                        -->
																	<th></th> <!-- -                        -->
																	<th></th> <!-- text:     Batch No.      -->
																	<th></th> <!-- text:     Requested By   -->
																</tr>
															</tfoot>
															<tbody></tbody>
														</table>
													</div>
													</div>



													<!-- Kolom Kanan: Production Log (card baru) -->
													<div class="budgetInsight_card">
														<div class="budgetInsight_card_header">
															<h2 class="budgetInsight_card_title" id="bi_title_prodlog">Production Log</h2>
															<span class="budgetInsight_hint">Work order activity</span>
														</div>
														<div id="budgetInsight_chart_prodlog" class="budgetInsight_chart_container" style="height:260px;"></div>
														<div style="border-top:1px solid #E1E8ED;margin:16px 0 12px 0;"></div>
														<div style="overflow-x:auto;margin-top:16px;">
															<table id="bi_table_prodlog" class="budgetInsight_table" style="width:100%;">
																<thead>
																	<tr>
																		<th>WO Number</th>
																		<th>Item No.</th>
																		<th>Production Date</th>
																		<th>Qty</th>
																		<th>Unit Cost</th>
																		<th>Line Cost</th>
																		<th>Enter Date</th>
																		<th>Enter By</th>
																	</tr>
																</thead>
																<tbody></tbody>
															</table>
														</div>
													</div>
												</div>
												<!-- ═══════════════════════════════════════════════════════
													ROW 3: Sales Alignment (kiri) + SO Payment Status (kanan)
													════════════════════════════════════════════════════════ -->
												<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
													<!-- Sales Alignment -->
													<div class="budgetInsight_card">
														<div class="budgetInsight_card_header">
															<h2 class="budgetInsight_card_title" id="bi_title_sales">Sales Alignment (SQ vs SO)</h2>
														</div>
														<div id="budgetInsight_chart_sales" class="budgetInsight_chart_container" style="height:300px;"></div>
													</div>
													<!-- SO Payment Status -->
													<div class="budgetInsight_card">
														<div class="budgetInsight_card_header">
															<h2 class="budgetInsight_card_title" id="bi_title_sostatus">SO Payment Status</h2>
														</div>
														<div class="budgetInsight_mini_kpi">
															<div>
																<span class="budgetInsight_kpi_label">Total SO</span>
																<div class="budgetInsight_kpi_value_small" id="bi_so_total">Rp 0</div>
															</div>
															<div>
																<span class="budgetInsight_kpi_label">Unpaid</span>
																<div class="budgetInsight_kpi_value_small budgetInsight_text_warn" id="bi_so_unpaid">Rp 0</div>
															</div>
														</div>
														<div id="budgetInsight_chart_payment" class="budgetInsight_chart_container" style="height:200px;"></div>
													</div>
												</div>
												<!-- ═══════════════════════════════════════════════════════
													ROW 4: Pipeline Table (full width)
													════════════════════════════════════════════════════════ -->
												<div class="budgetInsight_card" style="margin-bottom:20px;">
													<div class="budgetInsight_card_header">
														<h2 class="budgetInsight_card_title">Procurement Pipeline (Encumbered PR & Committed PO)</h2>
														<div style="display:flex;align-items:center;gap:8px;">
															<span id="bi_pipeline_count" class="budgetInsight_badge" style="background-color:var(--bi-text-muted);">0 rows</span>
															<div class="budgetInsight_badge" style="background-color:var(--bi-text-muted);">Details</div>
														</div>
													</div>
													
													<div style="overflow-x:auto;">
														<table id="bi_table_procurement" class="budgetInsight_table" style="width:100%;">
															<thead>
																<tr>
																	<th>Doc Type</th>
																	<th>Doc Number</th>
																	<th>PO Type</th>
																	<th>Description</th>
																	<th>Status</th>
																	<th>Actual Status</th>
																	<th>Vendor</th>
																	<th>ETA</th>
																	<th>Req. By/Buyer</th>
																	<th>Amount</th>
																</tr>
															</thead>
															<tfoot>
																<tr>
																	<th></th>
																	<!-- dropdown: Doc Type      -->
																	<th></th>
																	<!-- text:     Doc Number     -->
																	<th></th>
																	<!-- dropdown: PO Type        -->
																	<th></th>
																	<!-- text:     Description    -->
																	<th></th>
																	<!-- dropdown: Status         -->
																	<th></th>
																	<!-- dropdown: Actual Status  -->
																	<th></th>
																	<!-- text:     Vendor         -->
																	<th></th>
																	<!-- text:     ETA            -->
																	<th></th>
																	<!-- text:     Req. By/Buyer  -->
																	<th></th>
																	<!-- range:    Min/Max Amount -->
																</tr>
															</tfoot>
															<tbody></tbody>
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
	</body>
</html>