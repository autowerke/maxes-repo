<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="workorder" data-appkeynum="wonum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<button id="oprassignment" class="general-button">
							<i class="fas fa-info"></i>Operational Assignments
							</button>
							<button id="resworkbench" class="general-button">
							<i class="fas fa-info"></i>Resource Workbench
							</button>
							<div id="oprassignment_display" class="maxes01 graphdisplay">
								<div class="row eatfix">
									<div class="col-md-12 maxes04">
										<br>
										<h5 style="font-weight: 600; color: #182026;">Operational Assignments</h5>
										<hr style="border-top: 1px solid #E1E8ED;">
										<!-- START OF OPRASG LAYOUT -->
										<div class="oprasg_layout">
											<!-- HEADER & KPI ROW (Card 1) -->
											<div class="oprasg_header_row">
												<div class="oprasg_welcome">
													<span class="oprasg_text_muted">Current Shift:</span>
													<h2 class="oprasg_user_name" id="oprasg_user_info">Hello, -</h2>
												</div>
												<div class="oprasg_kpi_panel">
													<div class="oprasg_kpi_box">
														<span class="oprasg_kpi_label">Tasks</span>
														<span class="oprasg_kpi_value" id="oprasg_kpi_tasks">0</span>
													</div>
													<div class="oprasg_kpi_box">
														<span class="oprasg_kpi_label">Target</span>
														<span class="oprasg_kpi_value" id="oprasg_kpi_target">0h</span>
													</div>
													<div class="oprasg_kpi_box oprasg_kpi_box_logged" id="oprasg_kpi_box_logged" style="border-right: none; min-width: 160px;">
														<span class="oprasg_kpi_label" id="oprasg_kpi_logged_label">Logged</span>
														<span class="oprasg_kpi_value" id="oprasg_kpi_logged">0h</span>
														<!-- Mini progress bar -->
														<div class="oprasg_kpi_progress_track">
															<div class="oprasg_kpi_bar_fill" id="oprasg_kpi_progress_bar" style="width: 0%;"></div>
														</div>
														<span class="oprasg_kpi_progress_pct" id="oprasg_kpi_progress_pct">0% of target</span>
													</div>
												</div>
											</div>
											<!-- 3 COLUMN GRID -->
											<div class="oprasg_three_column_grid">
												<!-- LEFT: TASK QUEUE -->
												<div class="oprasg_col_left">
													<div class="oprasg_card oprasg_full_height">
														<div class="oprasg_card_header">
															<h3 class="oprasg_card_title">Task Queue</h3>
															<button class="oprasg_btn_icon" onclick="operationalAssignment()">&#x21bb;</button>
														</div>
														<div class="oprasg_task_list" id="oprasg_task_queue"></div>
														<div id="oprasg_pagination_container"></div>
													</div>
												</div>
												<!-- MIDDLE: EXECUTION CONSOLE -->
												<div class="oprasg_col_middle">
													<div class="oprasg_card oprasg_full_height">
														<div class="oprasg_card_header">
															<h3 class="oprasg_card_title" id="oprasg_exec_title">Execution: -</h3>
															<span class="oprasg_badge oprasg_badge_primary" id="oprasg_exec_status">-</span>
														</div>
														<div class="oprasg_exec_body">
															<div class="oprasg_task_steps" id="oprasg_task_steps"></div>
															<div id="oprasg_steps_pagination"></div>
															<div class="oprasg_action_group" style="margin-top: 20px;">
																<button id="btn_start_work" class="oprasg_btn oprasg_btn_primary oprasg_btn_grow">▶ START WORK</button>
																<button id="btn_update_progress" class="oprasg_btn oprasg_btn_outline oprasg_btn_fixed">✎ Update Progress</button>
															</div>
														</div>
													</div>
													<!-- TABBED CARD: Menggabungkan Card 4 & 5 -->
													<div class="oprasg_card" style="flex-grow: 1;">
														<div class="oprasg_tabs_header">
															<button class="oprasg_tab_btn active" data-target="tab_staging">Resources Staging</button>
															<button class="oprasg_tab_btn" data-target="tab_pipeline">Incoming Pipeline</button>
														</div>
														<!-- Tab Content 1: Staging Readiness -->
														<div id="tab_staging" class="oprasg_tab_content active">
															<div class="oprasg_table_wrapper" style="padding-top: 15px;">
																<table id="oprasg_table_staging" class="oprasg_dt_table" style="width:100%">
																	<thead>
																		<tr>
																			<th>Item / Description</th>
																			<th>Qty</th>
																			<th>Location</th>
																			<th style="text-align: right;">Status</th>
																		</tr>
																	</thead>
																	<tbody></tbody>
																</table>
															</div>
														</div>
														<!-- Tab Content 2: Incoming Pipeline -->
														<div id="tab_pipeline" class="oprasg_tab_content">
															<div class="oprasg_table_wrapper" style="padding-top: 15px;">
																<table id="oprasg_table_pipeline" class="oprasg_dt_table" style="width:100%">
																	<thead>
																		<tr>
																			<th>Doc Ref</th>
																			<th>Item / Desc</th>
																			<th>Qty</th>
																			<th style="text-align: right;">Target Date</th>
																		</tr>
																	</thead>
																	<tbody></tbody>
																</table>
															</div>
														</div>
													</div>
												</div>
												<!-- RIGHT: ECHARTS INSIGHTS -->
												<div class="oprasg_col_right">
													<div class="oprasg_card oprasg_chart_card">
														<h3 class="oprasg_card_title">Resource Readiness</h3>
														<div id="bi_chart_readiness"></div>
													</div>
													<div class="oprasg_card oprasg_chart_card">
														<h3 class="oprasg_card_title">Task Progress</h3>
														<div id="bi_chart_progress"></div>
													</div>
													<div class="oprasg_card oprasg_chart_card">
														<h3 class="oprasg_card_title">Pipeline Timeline</h3>
														<div id="bi_chart_pipeline"></div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div id="resworkbench_display" class="maxes01 graphdisplay">
								<div class="row eatfix">
									<div class="col-md-12 maxes04">
										<br>
										<h5 style="font-weight: 600; color: #182026;">Resources Workbench</h5>
										<hr style="border-top: 1px solid #E1E8ED;">
										<!-- START LAYOUT -->
										<div class="reswb_layout" data-layout="benchmark_filter">
											<div class="reswb_filter_bar">
												<!-- KOLOM 1: DATE RANGE -->
												<div class="reswb_col_filter">
													<div class="reswb_filter_group">
														<label>Reported Date From</label>
														<input type="date" id="reswb_date_from" class="reswb_input_lg">
													</div>
													<div class="reswb_filter_group">
														<label>Reported Date To</label>
														<input type="date" id="reswb_date_to" class="reswb_input_lg">
													</div>
												</div>
												<!-- KOLOM 2: WORK TYPE & CLASS -->
												<div class="reswb_col_filter">
													<div class="reswb_filter_group">
														<label>Work Type</label>
														<select id="reswb_worktype" class="reswb_select_lg">
															<option value="">All Work Type</option>
														</select>
													</div>
													<div class="reswb_filter_group">
														<label>Work Class</label>
														<select id="reswb_workclass" class="reswb_select_lg">
															<option value="">All Work Class</option>
														</select>
													</div>
												</div>
												<!-- KOLOM 3: OWNER & LEADER -->
												<div class="reswb_col_filter">
													<div class="reswb_filter_group">
														<label>Owner</label>
														<input type="text" id="reswb_owner" data-lookup="employees" class="reswb_input_lg" >
													</div>
													<div class="reswb_filter_group">
														<label>Leader</label>
														<input type="text" id="reswb_leader" data-lookup="employees"  class="reswb_input_lg" >
													</div>
												</div>
												<!-- KOLOM 4: STATUS & BUTTON -->
												<div class="reswb_col_filter">
													<div class="reswb_filter_group">
														<label>WO Status</label>
														<select id="reswb_status_filter" class="reswb_select_lg" multiple size="2">
															<option value="waitappr">Waiting Approval</option>
															<option value="approved">Approved</option>
															<option value="inprogress">In Progress</option>
														</select>
													</div>
													<button class="reswb_btn reswb_btn_primary" id="btn_reswb_applyfilters">Apply Filters</button>
												</div>
											</div>
											<!-- CAPACITY BAR (Global Load) -->
											<div class="row" style="margin-bottom: 20px;">
												<!-- KIRI: CAPACITY CARD (60% / col-md-8) -->
												<div class="col-md-8">
													<div class="reswb_capacity_card" style="height: 100%; margin-bottom: 0;">
														<div class="reswb_card_header">
															<h3 class="reswb_card_title">Global Team Capacity (Man-Hours)</h3>
															<span class="reswb_badge" id="reswb_capacity_pct">0% Utilization</span>
														</div>
														<!-- Progress Bar: Planned -->
														<div style="margin-bottom: 4px; font-size: 11pt; color: var(--reswb-text-muted);">Planned</div>
														<div class="reswb_progress_container">
															<div class="reswb_progress_bar" id="reswb_capacity_bar" style="width: 0%;"></div>
														</div>
														<!-- Progress Bar: Actual -->
														<div style="margin-top: 8px; margin-bottom: 4px; font-size: 11pt; color: var(--reswb-text-muted);">Actual</div>
														<div class="reswb_progress_container">
															<div class="reswb_progress_bar" id="reswb_actual_bar" style="width: 0%; background: var(--reswb-primary);"></div>
														</div>
														<!-- Labels -->
														<div class="reswb_capacity_labels" style="margin-top: 10px;">
															<span id="reswb_planned_hours">Planned: 0h / 0h (0%)</span>
															<span id="reswb_actual_hours" style="color: var(--reswb-primary); font-weight: bold;">
															Actual: 0h / 0h (0%)
															</span>
															<span id="reswb_available_hours">Available: 0h / 0h (0%)</span>
														</div>
														<!-- Variance -->
														<div style="margin-top: 8px; font-size: 11pt;">
															<strong>Variance: </strong>
															<span id="reswb_variance">0h</span>
															<span id="reswb_variance_label" style="color: var(--reswb-text-muted); margin-left: 4px;"></span>
														</div>
														<!-- Actual Cost -->
														<div style="margin-top: 6px; font-size: 11pt; color: var(--reswb-text-main);">
															<strong>Actual Labor Cost: </strong>
															<span id="reswb_actual_cost">Rp 0</span>
														</div>
													</div>
												</div>
												<!-- KANAN: HOLIDAYS & LEAVES (40% / col-md-4) -->
												<div class="col-md-4">
													<div class="reswb_card" style="height: 100%; margin-bottom: 0; display: flex; flex-direction: column; padding: 10px 20px;">
														<!-- TAB HEADERS -->
														<div class="reswb_tabs_header" style="margin-bottom: 5px;">
															<button class="reswb_tab_btn active" data-target="tab_holidays">Holidays</button>
															<button class="reswb_tab_btn" data-target="tab_leaves">Leaves</button>
														</div>
														<!-- TAB 1: HOLIDAYS -->
														<div id="tab_holidays" class="reswb_tab_content active reswb_scrollable_list">
															<div id="reswb_holidays_list" style="width: 100%;">
																<!-- Data JS masuk sini -->
															</div>
														</div>
														<!-- TAB 2: RESOURCE LEAVES -->
														<div id="tab_leaves" class="reswb_tab_content reswb_scrollable_list">
															<div id="reswb_leaves_list" style="width: 100%;">
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row" >
												<!-- LEFT: 60% Width - The Master Chart -->
												<div class="col-md-8">
													<div class="reswb_card">
														<div class="reswb_card_header">
															<h3 class="reswb_card_title">Work Order Cost Analysis (Plan vs Actual)</h3>
														</div>
														<div id="reswb_chart_master" style="width: 100%; height: 460px;"></div>
													</div>
												</div>
												<!-- RIGHT: 40% Width - Employee Workload -->
												<div class="col-md-4">
													<div class="reswb_card" style="display: flex; flex-direction: column;">
														<div class="reswb_card_header" style="border-bottom: none; padding-bottom: 0;">
															<h3 class="reswb_card_title">Employee's Work Load</h3>
														</div>
														<!-- TAB HEADERS -->
														<div class="reswb_tabs_header">
															<button class="reswb_tab_btn active" data-target="tab_load_planned">Planned</button>
															<button class="reswb_tab_btn" data-target="tab_load_actual">Actual</button>
														</div>
														<!-- TAB CONTENT 1: PLANNED (Tampilan Saat Ini) -->
														<div id="tab_load_planned" class="reswb_tab_content active" style="flex-grow: 1; display: flex; flex-direction: column;">
															<div id="reswb_employee_list" class="reswb_emp_list" style="flex-grow: 1;">
																<!-- Data RenderEmployeeLoad akan masuk ke sini -->
															</div>
															<div id="reswb_emp_pagination"></div>
														</div>
														<!-- TAB CONTENT 2: ACTUAL (Placeholder untuk Next Step) -->
														<div id="tab_load_actual" class="reswb_tab_content" style="flex-grow: 1; display: none; flex-direction: column;">
															<div id="reswb_employee_actual_list" class="reswb_emp_list" style="flex-grow: 1;">
																<div style="padding: 20px; text-align: center; color: #5C7080; font-size: 11pt;">
																	<br><br>
																	<i>Actual workload data will be rendered here...</i>
																</div>
															</div>
															<div id="reswb_emp_actual_pagination"></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<!-- END LAYOUT -->
									</div>
								</div>
							</div>
						</div>
						<div id="default_display" class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Work Order</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Tasks</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Planning</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_5">Actual</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_6">Scope of Works</a></li>
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
													<table id="mxtable_workorder" class="display"></table>
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
										<!-- Kolom 1 -->
										<div class="col-md-3">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>WO</td>
														<td>
															<input type="text" data-attribute="wonum" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description" data-autofillobject="workorder" data-columns="description, wonum, leader, owner, reportdate">
														</td>
													</tr>
												</table>
											</div>
										</div>
										<!-- Kolom 2 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Budget Group</td>
													<td>
														<input type="text" data-attribute="budgetnum" data-lookup="budget" data-lookupaction="y">
													</td>
												</tr>
												<tr>
													<td>Budget Line</td>
													<td>
														<input type="text" data-attribute="budgetlinenum" data-lookup="budgetline|budgetnum"   data-childrentable="budgetline" data-childrencolumn="wbscode">
													</td>
												</tr>
												<tr>
													<td>Alternate Budget Line</td>
													<td>
														<input type="text" data-attribute="budgetlinenum2" data-lookup="budgetline|budgetnum" data-childrentable="budgetline" data-childrencolumn="wbscode">
													</td>
												</tr>
											</table>
										</div>
										<!-- Kolom 3 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>PM</td>
													<td>
														<input type="text" data-attribute="pmnum" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>PM Deviation</td>
													<td>
														<input type="text" data-attribute="pmdeviation" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Work Template</td>
													<td>
														<input type="text" data-navigation="worktemplates" data-attribute="wtnum" data-lookup="worktemplates" data-lookupaction="y">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-sm-3">
											<!-- unused section -->
										</div>
									</div>
                           <div class="row eatfix">
                              <!-- CARD DESIGN -->
                              <div id="woqualityalert_container" class="row eatfix" style="display:none; margin-bottom: 15pt; width: 100%; max-width: 1000pt; margin: 0 auto; background: #ffffff; border: 1pt solid #e1e6ef; border-radius: 4pt;">
                                 
                                 <!-- Bagian Kiri: Quality Logic (Density: High) -->
                                 <div style="flex: 2; padding: 10pt 15pt; border-right: 1pt solid #edf2f7;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8pt;">
                                          <div id="wo_alert_title" style="font-size: 11pt; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em;"></div>
                                          <div id="wo_alert_score_badge" style="font-size: 10pt; font-weight: 700; padding: 2pt 8pt; border-radius: 2pt;"></div>
                                    </div>
                                    
                                    <div id="wo_alert_status_bar_line" style="height: 2pt; width: 100%; margin-bottom: 10pt;"></div>

                                    <div id="wo_alert_list" style="font-size: 10pt; color: #4a5568; line-height: 1.4;">
                                          <!-- Content via JS -->
                                    </div>
                                 </div>

                                 <!-- Bagian Kanan: Responsible Information (Foundry Sidebar Style) -->
                                 <div style="flex: 1.2; padding: 10pt 15pt; background: #f9fafb;">
                                    <div style="font-size: 10pt; font-weight: 700; color: #718096; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10pt; border-bottom: 1pt solid #e2e8f0; padding-bottom: 4pt;">
                                          Responsible Matrix
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15pt;">
                                          <!-- Owner Column -->
                                          <div>
                                             <div style="font-size: 10pt; font-weight: 600; color: #a0aec0; margin-bottom: 2pt;">OWNER</div>
                                             <div id="resp_owner_name" style="font-size: 10pt; font-weight: 700; color: #2d3748;"></div>
                                             <div id="resp_owner_details" style="font-size: 10pt; color: #718096; line-height: 1.3; margin-top: 4pt;"></div>
                                          </div>

                                          <!-- Leader Column -->
                                          <div>
                                             <div style="font-size: 10pt; font-weight: 600; color: #a0aec0; margin-bottom: 2pt;">LEADER</div>
                                             <div id="resp_leader_name" style="font-size: 10pt; font-weight: 700; color: #2d3748;"></div>
                                             <div id="resp_leader_details" style="font-size: 10pt; color: #718096; line-height: 1.3; margin-top: 4pt;"></div>
                                          </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Charge To </td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<!-- KOLOM 1 -->
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Customer</td>
													<td>
														<input type="text" data-attribute="customercode" data-lookup="customer" data-lookupaction="y" >
													</td>
												</tr>
												<tr>
													<td>SO</td>
													<td>
														<input type="text" data-attribute="sonum" data-lookup="so" data-lookupaction="y">
													</td>
												</tr>
												<tr>
													<td>Account Code</td>
													<td>
														<input type="text" data-attribute="glaccount" data-lookup="chartofaccount" data-input="required">
													</td>
												</tr>
												<tr>
													<td>Commission</td>
													<td>
														<input type="text" data-attribute="comission" data-type="decimal">
													</td>
												</tr>
											</table>
										</div>
										<!-- KOLOM 2  -->
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Location</td>
													<td>
														<input type="text" data-attribute="location" data-lookup="locations" data-lookupaction="y" data-childrentable="locations" data-childrencolumn="locnum">
													</td>
												</tr>
												<tr>
													<td>Location Name</td>
													<td>
														<input type="text" data-attribute="location.description" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<!-- KOLOM 3  -->
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Asset</td>
													<td>
														<input type="text" data-attribute="assetnum" data-lookup="assets" data-lookupaction="y" data-navigation="assets" data-childrentable="assets" data-childrencolumn="assetnum">
													</td>
												</tr>
												<tr>
													<td>Asset Description</td>
													<td>
														<input type="text" data-attribute="assetnum.description" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td colspan=2>
														<div class="row eatfix maxes_sct_tbl4">
															Asset Health & Life Cycle
															<i id="help_assethealth"
																class="fa fa-question-circle"
																style="font-size:14pt; color:#007bff; margin-left:6px; cursor:pointer; vertical-align:middle;"
																title="Click for help">
															</i>
														</div>
														<div class="row eatfix">
															<div class="col-md-12">
																<div class="addrow" id="setAssetCondition"></div>
																<div class="addrow" id="reportDowntime"></div>
															</div>
														</div>
													</td>
												</tr>
											</table>
										</div>
									</div>
									<!-- ===================== SECTION 1 ===================== -->
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Work & Schedule</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<!-- KOLOM 1 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Status</td>
													<td><input type="text" data-attribute="status" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Status Date</td>
													<td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Reported Date</td>
													<td><input type="text" data-attribute="reportdate" data-type="date"></td>
												</tr>
												<tr>
													<td>Duration (hours)</td>
													<td><input type="text" data-attribute="duration" data-type="decimal"></td>
												</tr>
											</table>
										</div>
										<!-- KOLOM 2 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Target Start Date</td>
													<td><input type="text" data-attribute="targstartdate" data-type="date"></td>
												</tr>
												<tr>
													<td>Target Finish</td>
													<td><input type="text" data-attribute="targcompdate" data-type="date"></td>
												</tr>
												<tr>
													<td>Actual Start</td>
													<td><input type="text" data-attribute="actstartdate" data-type="date"></td>
												</tr>
												<tr>
													<td>Actual Finish</td>
													<td><input type="text" data-attribute="actfinishdate" data-type="date"></td>
												</tr>
											</table>
										</div>
										<!-- KOLOM 3 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Progress (%)</td>
													<td><input type="text" data-attribute="progress" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Target Completion (%)</td>
													<td><input type="text" data-attribute="targetcompletion" data-type="decimal"></td>
												</tr>
												<tr>
													<td>GAP (%)</td>
													<td><input type="text" data-attribute="gapprogress" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Work Class</td>
													<td><input type="text" data-attribute="workclass" data-lookup="workclass"></td>
												</tr>
											</table>
										</div>
										<!-- KOLOM 4 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Origin</td>
													<td><input type="text" data-attribute="origrecordid" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Origin Classification</td>
													<td><input type="text" data-attribute="origrecordclass" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Work Type</td>
													<td><input type="text" data-attribute="worktype" data-lookup="worktype"></td>
												</tr>
												<tr>
													<td>Failure Code</td>
													<td>
														<input type="text" data-attribute="failurecode" data-lookup="failures">
														<button class="ai-btn" title="Ask AI for Failure Code" id="askai_failurecode">✨</button>
													</td>
												</tr>
											</table>
										</div>
									</div>
									<!-- ===================== SECTION 2 ===================== -->
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Assignment & Cost</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<!-- KOLOM 1 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Owner</td>
													<td><input type="text" data-attribute="owner" data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees" data-navigation="employees"></td>
												</tr>
												<tr>
													<td>Owner Name</td>
													<td><input type="text" data-attribute="owner.fullname" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Leader</td>
													<td><input type="text" data-attribute="leader" data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees" data-navigation="employees"></td>
												</tr>
												<!-- <tr>
													<td>Team</td>
													<td><input type="text" data-attribute="teamnum" data-lookup="team"></td>
													</tr>
													-->
												<tr>
													<td>Leader Name</td>
													<td><input type="text" data-attribute="leader.fullname" data-input="readonly"></td>
												</tr>
											</table>
										</div>
										<!-- KOLOM 2 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Reported By</td>
													<td><input type="text" data-attribute="reportedby" data-lookup="employees"></td>
												</tr>
												<tr>
													<td>Priority</td>
													<td><input type="text" data-attribute="priority" data-lookup="wopriority"></td>
												</tr>
											</table>
										</div>
										<!-- KOLOM 3 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Planning Cost</td>
													<td><input type="text" data-attribute="plannedcost" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Actual Cost</td>
													<td><input type="text" data-attribute="actualcost" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Variance Cost</td>
													<td><input type="text" data-attribute="variancecost" data-type="decimal" data-input="readonly"></td>
												</tr>
											</table>
										</div>
										<!-- KOLOM 4 -->
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Company</td>
													<td><input type="text" data-attribute="orgcode" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Site</td>
													<td><input type="text" data-attribute="sitecode" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Remarks</td>
													<td><input type="text" data-attribute="remarks"></td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Root Cause Failure Analysis</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addfailurecode"></div>
											<div class="addrow" id="failurehierarchy"></div>
											<div class="addrow" id="setfailurezone"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_rcfa_child" class="display" data-child="worootcause"></table>
												<div id="mxtable_rcfa_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Failure Code</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="failurecode" data-input="required" data-childrentable="assetfailure" data-childrencolumn="failurecode" data-type="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="failurecode.description">
																	</td>
																</tr>
																<tr>
																	<td>Zone
																	</td>
																	<td>
																		<input type="zone" data-childfld="child" data-attribute="zone" data-lookup="zone">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Failure Level</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="failurelevel">
																	</td>
																</tr>
																<tr>
																	<td>Notes
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="notes">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Sub Work Orders</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Parent</td>
													<td>
														<input type="text" data-attribute="parent" data-lookup="workorder" data-lookupaction="y">
													</td>
												</tr>
												<tr>
													<td>Roll Up Cost </td>
													<td>
														<input type="text" data-attribute="rollupcost" data-input="readonly" data-type="decimal">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addwosubs"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-wosubs" class="subform" data-child="wosubs" data-table="mxtable_wosubs_child">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Work Order
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="subwonum" data-input="required" data-key="value" data-childrentable="workorder" data-childrencolumn="wonum" data-lookup="workorderall" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="subwonum.description" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Status
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="subwonum.status" data-input="readonly">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_wosubs_child" class="display" data-subform="subform-wosubs"></table>
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
														<td>WO</td>
														<td>
															<input type="text" data-attribute="wonum" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Work Template</td>
														<td>
															<input type="text" data-attribute="wtnum" data-lookup="worktemplates" data-lookupaction="y">
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
													<td class="maxes_sct_tbl2">Charge To </td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Customer</td>
													<td>
														<input type="text" data-attribute="customercode" data-lookup="customer" data-lookupaction="y" data-childrentable="customer" data-childrencolumn="customercode">
													</td>
												</tr>
												<tr>
													<td>Customer Name</td>
													<td>
														<input type="text" data-attribute="customercode.name" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Asset</td>
													<td>
														<input type="text" data-navigation="assets" data-attribute="assetnum" data-lookup="assets" data-lookupaction="y" data-childrentable="assets" data-childrencolumn="assetnum">
													</td>
												</tr>
												<tr>
													<td>Asset</td>
													<td>
														<input type="text" data-attribute="assetnum.description" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Location</td>
													<td>
														<input type="text" data-attribute="location" data-lookup="locations" data-lookupaction="y" data-childrentable="locations" data-childrencolumn="locnum">
													</td>
												</tr>
												<tr>
													<td>Location Name</td>
													<td>
														<input type="text" data-attribute="location.description" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Account Code</td>
													<td>
														<input type="text" data-attribute="glaccount" data-lookup="chartofaccount" data-input="required">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Tasks</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addwotask"></div>
											<button class="ai-btn" title="Ask AI for Job Tasks" id="askai_tasks">✨</button>
										</div>
									</div>
									<div id="subform-wotask" class="subform" data-child="wotask" data-table="mxtable_wotask_child">
										<div class="row eatfix">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Line Num.
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="tasklinenum" data-input="required" data-type="integer" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Status
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="status" data-lookup="wotaskstatus">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Target Start Date
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="targstartdate" data-type="date">
														</td>
													</tr>
													<tr>
														<td>Target Finish Date</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="targcompdate" data-type="date">
														</td>
													</tr>
													<tr>
														<td>Actual Start
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="actstartdate" data-type="date">
														</td>
													</tr>
													<tr>
														<td>Actual Finish
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="actfinishdate" data-type="date">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									


									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_wotask_child" class="display" data-subform="subform-wotask"></table>
												<div id="mxtable_wotask_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Line Num.
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="tasklinenum" data-input="readonly" data-input="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Description </td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description">
																	</td>
																</tr>
																<tr>
																	<td>Status
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="status" data-lookup="wotaskstatus">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Target Start Date
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="targstartdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Target Finish Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="targcompdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Actual Start
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="actstartdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Actual Finish
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="actfinishdate" data-type="date">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Actual Progress (%)
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="progress" data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Duration
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="duration" data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Target Completion (%)
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="targetcompletion" data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>GAP (%)
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="gapprogress" data-type="decimal">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Production Line</td>
												</tr>
											</table>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addwotaskoperation"></div>
										</div>
									</div>
									<div id="subform-wotaskoperation" class="subform" data-child="wotaskoperation"  data-table="mxtable_wotaskoperation_child">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Line Num.
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="tasklinenum" data-type="integer" data-lookup="wotask|wonum">
														</td>
													</tr>
													<tr>
														<td>Sequence Num.
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="sequencenum" data-type="integer">
														</td>
													</tr>
													<tr>
														<td>Operation Name </td>
														<td>
															<input type="text" data-childfld="child" data-attribute="operationname">
														</td>
													</tr>
													<tr>
														<td>Asset
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="assetnum" data-lookup="assets">
														</td>
													</tr>
													
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Actual Start Date
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="actualstartdate" data-type="date">
														</td>
													</tr>
													<tr>
														<td>Actual Finish Date</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="actualfinishdate" data-type="date">
														</td>
													</tr>
													<tr>
														<td>Est. Hours
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="estimatedhours" data-type="decimal">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Status</td>
														<td>
															<select id="status" data-attribute="status" data-childfld="child">
																<option value="pending">Pending</option>
																<option value="ready">Ready</option>
																<option value="inprogress">In Progress</option>
																<option value="completed">Completed</option>
																<option value="cancel">Canceled</option>
															</select>
														</td>
													</tr>
													<tr>
														<td>Remarks
														</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="description">
														</td>
													</tr>
													
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_wotaskoperation_child" class="display" data-child="wotaskoperation" data-subform="subform-wotaskoperation"></table>
												<div id="mxtable_wotaskoperation_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Line Num.
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="tasklinenum" data-type="integer" data-key="value" data-lookup="wotask|wonum">
																	</td>
																</tr>
																<tr>
																	<td>Sequence Num.
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="sequencenum" data-type="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Operation Name </td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="operationname">
																	</td>
																</tr>
																<tr>
																	<td>Asset
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="assetnum" data-lookup="assets">
																	</td>
																</tr>
																
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Actual Start Date
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="actualstartdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Actual Finish Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="actualfinishdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Est. Hours
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="estimatedhours" data-type="decimal">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Status</td>
																	<td>
																		<td>
																			<select id="status" data-attribute="status" data-childfld="child">
																				<option value="pending">Pending</option>
																				<option value="ready">Ready</option>
																				<option value="inprogress">In Progress</option>
																				<option value="completed">Completed</option>
																				<option value="cancel">Canceled</option>
																			</select>
																		</td>
																	</td>
																</tr>
																<tr>
																	<td>Remarks
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description">
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
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>WO</td>
														<td>
															<input type="text" data-attribute="wonum" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Planning Cost</td>
														<td>
															<input type="text" data-attribute="plannedcost" data-type="decimal" data-input="readonly">
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
													<td class="maxes_sct_tbl2">Charge To </td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Customer</td>
													<td>
														<input type="text" data-attribute="customercode" data-lookup="customer" data-lookupaction="y" data-childrentable="customer" data-childrencolumn="customercode">
													</td>
												</tr>
												<tr>
													<td>Customer Name</td>
													<td>
														<input type="text" data-attribute="customercode.name" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Asset</td>
													<td>
														<input type="text" data-navigation="assets" data-attribute="assetnum" data-lookup="assets" data-lookupaction="y" data-childrentable="assets" data-childrencolumn="assetnum" >
													</td>
												</tr>
												<tr>
													<td>Asset</td>
													<td>
														<input type="text" data-attribute="assetnum.description" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Location</td>
													<td>
														<input type="text" data-attribute="location" data-lookup="locations" data-lookupaction="y" data-childrentable="locations" data-childrencolumn="locnum">
													</td>
												</tr>
												<tr>
													<td>Location Name</td>
													<td>
														<input type="text" data-attribute="location.description" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Account Code</td>
													<td>
														<input type="text" data-attribute="glaccount" data-lookup="chartofaccount" data-input="required">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="subtab">
										<ul class="maxes02">
											<li class="maxes02_1"><a href="#maxes_planmat">Materials</a></li>
											<li class="maxes02_2"><a href="#maxes_planserv">Services</a></li>
											<li class="maxes02_2"><a href="#maxes_planlab">Labors</a></li>
											<li class="maxes02_2"><a href="#maxes_plantools">Tools</a></li>
										</ul>
									</div>
									<div class="maxes04sub">
										<div id="maxes_planmat" class="maxes02_1">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Planning Materials
											</div>
											<div class="row eatfix">
												<div class="col-md-8">
													<div class="addrow" id="quickplanning"></div>
													<div class="addrow" id="addwoplanmaterial"></div>
													<div class="addrow" id="prodordermultiplier"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div id="subform-woplanmaterial" class="subform" data-child="woplanmaterial" data-table="mxtable_woplanmaterial_child">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Line Num.
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
																		</td>
																	</tr>
																	<tr>
																		<td>Item</td>
																		<td>
																			<input type="text" data-childfld="child" data-navigation="item" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="invbalance|orgcode" data-lookupaction="y" data-input="required" data-key="value">
																		</td>
																	</tr>
																	<tr>
																		<td>Description </td>
																		<td>
																			<input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.description">
																		</td>
																	</tr>
																	<tr>
																		<td>Warehouse
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly">
																		</td>
																	</tr>
																	<tr>
																		<td>From Rack/Bin</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Condition Type
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="invcost|itemnum,warehouse">
																		</td>
																	</tr>
																	<tr>
																		<td>Quantity
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>Unit Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>Total Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="required">
																		</td>
																	</tr>
																	<tr>
																		<td>Entered By
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
																		</td>
																	</tr>
																	<tr>
																		<td>Entered Date
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly" data-type="date">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata">
																	<tr>
																		<td>Doc. Num.
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="docnum">
																		</td>
																	</tr>
																	<tr>
																		<td>Debit Account Code
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="chartofaccount|orgcode">
																		</td>
																	</tr>
																	<tr>
																		<td>Credit Account Code
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="chartofaccount|orgcode">
																		</td>
																	</tr>
																	<tr>
																		<td>Remarks
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="remarks">
																		</td>
																	</tr>
																</table>
															</div>
														</div>
													</div>
													<div class="row eatfix">
														<div class="col-md-12">
															<div class="tablelist">
																<table id="mxtable_woplanmaterial_child" class="display" data-subform="subform-woplanmaterial"></table>
																<div id="mxtable_woplanmaterial_child_detail" class="mxtable_detail">
																	<div class="row eatfix">
																		<div class="col-md-4">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Line Num.
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
																					</td>
																				</tr>
																				<tr>
																					<td>Item</td>
																					<td>
																						<input type="text" data-childfld="child" data-navigation="item" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="invbalance|orgcode" data-lookupaction="y" data-input="required" data-key="value">
																					</td>
																				</tr>
																				<tr>
																					<td>Description </td>
																					<td>
																						<input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.description">
																					</td>
																				</tr>
																				<tr>
																					<td>Warehouse
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly" data-lookup = "warehouse" data-lookupaction="y">
																					</td>
																				</tr>
																				<tr>
																					<td>From Rack/Bin</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly" >
																					</td>
																				</tr>
																			</table>
																		</div>
																		<div class="col-md-4">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Quantity
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required">
																					</td>
																				</tr>
																				<tr>
																					<td>Condition Type
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="itemcondition|itemnum,warehouse">
																					</td>
																				</tr>
																				<tr>
																					<td>Unit Cost
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="unitcost" data-input="required" data-type="decimal">
																					</td>
																				</tr>
																				<tr>
																					<td>Total Cost
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="linecost" data-input="required" data-type="decimal">
																					</td>
																				</tr>
																				<tr>
																					<td>Entered By
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
																					</td>
																				</tr>
																				<tr>
																					<td>Entered Date
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly" data-type="date">
																					</td>
																				</tr>
																			</table>
																		</div>
																		<div class="col-md-4">
																			<table class="tableDetailRow">
																				<tr>
																					<td>Doc. Number
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="docnum">
																					</td>
																				</tr>
																				<tr>
																					<td>Debit Account Code
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="chartofaccount|orgcode">
																					</td>
																				</tr>
																				<tr>
																					<td>Credit Account Code
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="chartofaccount|orgcode">
																					</td>
																				</tr>
																				<tr>
																					<td>Remarks
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="remarks">
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
										<div id="maxes_planserv" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Service Planning
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addwoplanservice"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div id="subform-woplanservice" class="subform" data-child="woplanservice" data-table="mxtable_woplanservice_child">
													<div class="row eatfix">
														<div class="col-md-6">
															<div class="maxes_sct_tbl2"> Additional Vendor Service </div>
															<table class="tbldata">
																<tr>
																	<td>Line Num.
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Service Item
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" data-childrentable="serviceitems" data-childrencolumn="serviceitemnum">
																	</td>
																</tr>
																<tr>
																	<td>Description
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" data-input="required" data-crossvalue="serviceitemnum.description">
																	</td>
																</tr>
																<tr>
																	<td>Quantity
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Order Unit
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit" data-crossvalue="serviceitemnum.serviceunit">
																	</td>
																</tr>
																<tr>
																	<td>Unit Cost
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-crossvalue="serviceitemnum.stdcost">
																	</td>
																</tr>
																<tr>
																	<td>Line Cost
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<div class="maxes_sct_tbl2"> Existing Vendor Service </div>
															<table class="tbldata">
																<tr>
																	<td>Existing PR
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="prnum" data-lookup="pr|orgcode">
																	</td>
																</tr>
																<tr>
																	<td>Existing PR Line Num.
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="prlinenum" data-lookup="prlinewo|prnum">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_woplanservice_child" class="display" data-subform="subform-woplanservice"></table>
														<div id="mxtable_woplanservice_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-6">
																	<div class="maxes_sct_tbl2"> Additional Vendor Service </div>
																	<table class="tableDetailRow">
																		<tr>
																			<td>Line Num.
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
																			</td>
																		</tr>
																		<tr>
																			<td>Service Item
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" data-childrentable="serviceitems" data-childrencolumn="serviceitemnum">
																			</td>
																		</tr>
																		<tr>
																			<td>Description
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="description" data-input="required">
																			</td>
																		</tr>
																		<tr>
																			<td>Quantity
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Order Unit
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Line Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-6">
																	<div class="maxes_sct_tbl2"> Existing Vendor Service </div>
																	<table class="tableDetailRow">
																		<tr>
																			<td>Existing PR
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="prnum" data-lookup="pr|orgcode">
																			</td>
																		</tr>
																		<tr>
																			<td>Existing PR Line Num.
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="prlinenum" data-lookup="prlinewo|prnum">
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
										<div id="maxes_planlab" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Labor Planning
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addwoplanlabor"></div>
													<div class="addrow" id="checkavailablelabors"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div id="subform-woplanlabor" class="subform" data-child="woplanlabor" data-table="mxtable_woplanlabor_child">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tbldata  ">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Work Detail</div>
																		</td>
																	</tr>
																	<tr>
																		<td>Team
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team" data-lookupaction="y">
																		</td>
																	</tr>
																	<tr>
																		<td>Person ID
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="personid" data-navigation="employees"  data-lookup="employees" data-lookupaction="y">
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata  ">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Work Hours</div>
																		</td>
																	</tr>
																	<tr>
																		<td>Start Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="startdate" data-type="date">
																		</td>
																	</tr>
																	<tr>
																		<td>Finish Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="enddate" data-type="date">
																		</td>
																	</tr>
																	<tr>
																		<td>Regular Hours</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="reghours" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Total Hours</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="totalhours" data-type="decimal" data-input="readonly">
																		</td>
																	</tr>
																	<tr>
																		<td>Max. Overtime Hours</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="maxhours" data-type="decimal" >
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tbldata  ">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Labor Cost</div>
																		</td>
																	</tr>
																	<tr>
																		<td>Overtime?
																		</td>
																		<td>
																			<input type="checkbox" data-childfld="child" data-attribute="applyovertime">
																		</td>
																	</tr>
																	<tr>
																		<td>Unit Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Overtime Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="overtimecost" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Total Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="prelinecost" data-type="decimal" data-input="readonly" data-input="readonly">
																		</td>
																	</tr>
																</table>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_woplanlabor_child" class="display" data-subform="subform-woplanlabor"></table>
														<div id="mxtable_woplanlabor_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Detail</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Team
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team" data-lookupaction="y">
																			</td>
																		</tr>
																		<tr>
																			<td>Person ID
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="personid" data-navigation="employees" data-lookup="employees" data-lookupaction="y">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Hours</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Start Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="startdate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Finish Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="enddate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Regular Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="reghours" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="totalhours" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Max. Overtime Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="maxhours" data-type="decimal" >
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Labor Cost</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Overtime?
																			</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="applyovertime">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Overtime Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="overtimecost" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="prelinecost" data-type="decimal" data-input="readonly" data-input="readonly">
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
										<div id="maxes_plantools" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Planning Material
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addwoplantool"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div id="subform-woplantool" class="subform" data-child="woplantool" data-table="mxtable_woplantool_child">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>Line Num.
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="planlinenum" data-input="required" data-type="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Tools Number
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="toolnum" data-lookup='tools' data-childrentable="tools" data-childrencolumn="toolnum" data-input="required">
																	</td>
																</tr>
																<tr>
																	<td>Description
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" data-crossvalue="toolnum.description">
																	</td>
																</tr>
																<tr>
																	<td>For Task
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="tasklinenum" data-lookup="wotask|wonum">
																	</td>
																</tr>
																<tr>
																	<td>From Location </td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="locnum" data-crossvalue="toolnum.locnum">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>Quantity
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Order Unit
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit" data-crossvalue="toolnum.measureunit">
																	</td>
																</tr>
																<tr>
																	<td>Unit Cost
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-crossvalue="toolnum.unitcost">
																	</td>
																</tr>
																<tr>
																	<td>Total Cost
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_woplantool_child" class="display" data-subform="subform-woplantool"></table>
														<div id="mxtable_woplantool_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Line Num.
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="planlinenum" data-type="integer" data-key="value">
																			</td>
																		</tr>
																		<tr>
																			<td>For Task
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="tasklinenum" data-lookup="wotask|wo">
																			</td>
																		</tr>
																		<tr>
																			<td>Tool Number
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="toolnum" data-lookup="tools" data-childrentable="tools" data-childrencolumn="toolnum">
																			</td>
																		</tr>
																		<tr>
																			<td>Description </td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="description" data-crossvalue="toolnum.description">
																			</td>
																		</tr>
																		<tr>
																			<td>From Location </td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="locnum" data-crossvalue="toolnum.locnum">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Quantity
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Order Unit
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost </td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="decimal">
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
								<div id="maxes02_1_5" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>WO</td>
														<td>
															<input type="text" data-attribute="wonum" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Actual Cost</td>
														<td>
															<input type="text" data-attribute="actualcost" data-type="decimal" data-input="readonly">
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
													<td class="maxes_sct_tbl2">Charge To </td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Customer</td>
													<td>
														<input type="text" data-attribute="customercode" data-lookup="customer" data-childrentable="customer" data-childrencolumn="customercode">
													</td>
												</tr>
												<tr>
													<td>Customer Name</td>
													<td>
														<input type="text" data-attribute="customercode.name" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Asset</td>
													<td>
														<input type="text" data-navigation="assets" data-attribute="assetnum" data-lookup="assets" data-lookupaction="y" data-childrentable="assets" data-childrencolumn="assetnum">
													</td>
												</tr>
												<tr>
													<td>Asset</td>
													<td>
														<input type="text" data-attribute="assetnum.description" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Location</td>
													<td>
														<input type="text" data-attribute="location" data-lookup="locations" data-lookupaction="y" data-childrentable="locations" data-childrencolumn="locnum">
													</td>
												</tr>
												<tr>
													<td>Location Name</td>
													<td>
														<input type="text" data-attribute="location.description" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Account Code</td>
													<td>
														<input type="text" data-attribute="glaccount" data-lookup="chartofaccount" data-input="required">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="subtab">
										<ul class="maxes02">
											<li class="maxes02_1"><a href="#maxes_actmat">Materials</a></li>
											<li class="maxes02_2"><a href="#maxes_actserv">Services</a></li>
											<li class="maxes02_2"><a href="#maxes_actlab">Labors</a></li>
											<li class="maxes02_2"><a href="#maxes_acttools">Tools</a></li>
										</ul>
									</div>
									<div class="maxes04sub">
										<div id="maxes_actmat" class="maxes02_1">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Material Usage
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_matusetrans_child" class="display" data-child="matusetrans"></table>
														<div id="mxtable_matusetrans_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<!-- KOLOM 1 -->
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Item</td>
																			<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Issue Unit</td>
																			<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Condition Type</td>
																			<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
																<!-- KOLOM 2 -->
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Warehouse</td>
																			<td><input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Rack</td>
																			<td><input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Expired Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="expireddate" data-input="readonly" data-type="date"></td>
																		</tr>
																		<tr>
																			<td>FIFO/LIFO Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="costdate" data-input="readonly" data-type="date"></td>
																		</tr>
																	</table>
																</div>
																<!-- KOLOM 3 -->
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Quantity</td>
																			<td><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
																		</tr>
																		<tr>
																			<td>Unit Cost</td>
																			<td><input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
																		</tr>
																		<tr>
																			<td>Total Cost</td>
																			<td><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal"></td>
																		</tr>
																		<tr>
																			<td>Remarks</td>
																			<td><input type="text" data-childfld="child" data-attribute="remarks"></td>
																		</tr>
																	</table>
																</div>
																<!-- KOLOM 4 -->
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Do Not Calculate Resource?</td>
																			<td><input type="checkbox" data-childfld="child" data-attribute="donotcalculateres"></td>
																		</tr>
																		<tr>
																			<td>Entered Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly" data-type="date"></td>
																		</tr>
																		<tr>
																			<td>Date Issue</td>
																			<td><input type="text" data-childfld="child" data-attribute="actualdate" data-input="readonly" data-type="date"></td>
																		</tr>
																		<tr>
																			<td>Issue Type</td>
																			<td><input type="text" data-childfld="child" data-attribute="issuetype" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
														Shipment
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_matuseship_child" class="display" data-child="matusetrans"></table>
														<div id="mxtable_matuseship_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-3">
																	<div class="col-md-12 maxes_sct_tbl3">
																		Item
																	</div>
																	<table class="tableDetailRow">
																		<tr>
																			<td>Item
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Description
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="description" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Shipping Date
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="shipdate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Longitude, Latitude
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="longlat">
																			</td>
																		</tr>
																		<tr>
																			<td>Est. of Arrival
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="estdatearrival" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>FIFO/LIFO Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="costdate" data-type="date" data-input="readonly">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-3">
																	<div class="col-md-12 maxes_sct_tbl3">
																		Pengiriman
																	</div>
																	<table class="tableDetailRow">
																		<tr>
																			<td>Ship Via
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="shipvia">
																			</td>
																		</tr>
																		<tr>
																			<td>Agen Pengiriman (RESI)</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="shipagent">
																			</td>
																		</tr>
																		<tr>
																			<td>Shipment Num.</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="shipmentnum">
																			</td>
																		</tr>
																		<tr>
																			<td>Shipping Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="shipdate" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Shipment Status
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="shipstatus" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Remarks
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="shipremarks">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-3">
																	<div class="col-md-12 maxes_sct_tbl3">
																		Total Kirim
																	</div>
																	<table class="tableDetailRow">
																		<tr>
																			<td>Quantity</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Selling Price</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitprice" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Discount 1(%)</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="primarydiscount" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Discount 2(%)</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="secondarydiscount" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Discount</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="discountprice" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Price Discount
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitpricediscount" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Line Price Discount
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="linepricediscount" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-3">
																	<div class="col-md-12 maxes_sct_tbl3">
																		Receiving Materials
																	</div>
																	<table class="tableDetailRow">
																		<tr>
																			<td>Arrival Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="arrivaldate" data-type="date" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Remarks</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="remarks" data-input="readonly">
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
										<div id="maxes_actserv" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Actual Services
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="dispatchservices"></div>
													<div class="addrow" id="addvendorServices"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_servrectrans_child" class="display" data-child="servrectrans"></table>
														<div id="mxtable_servrectrans_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>PO
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="ponum" data-childrentable="po" data-childrencolumn="ponum" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Description PO
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="ponum.description" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>PO Line Num.
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Description
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="itemnum.description" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Order Unit
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Quantity
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Cost</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Line Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Entered Date
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly" data-type="date">
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
										<div id="maxes_actlab" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Actual Labors
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="dispatchlabor"></div>
													<div class="addrow" id="modifyworkhours"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_labortrans_child" class="display" data-child="labortrans"></table>
														<div id="mxtable_labortrans_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Detail</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Labor Line Num.
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="laborlinenum" data-input="required" data-type="integer" data-key="value">
																			</td>
																		</tr>
																		<tr>
																			<td>Task Line Num.
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="tasklinenum" data-lookup="wotask|wonum">
																			</td>
																		</tr>
																		<tr>
																			<td>Person ID
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="personid" data-lookup="employees">
																			</td>
																		</tr>
																		<tr>
																			<td>Team
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team">
																			</td>
																		</tr>
																		<tr>
																			<td>Permit to Work?
																			</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="permitted">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Hours</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Start Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="startdate" data-type="date" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Finish Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="enddate" data-type="date" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Days</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="regdays" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Work Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="reghours" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Overtime Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="extendedhours" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Max. Overtime Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="maxhours" data-type="decimal">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Labor Cost</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Overtime?
																			</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="applyovertime">
																			</td>
																		</tr>
																		<tr>
																			<td>Overtime Total Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="totalovertimecost" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="prelinecost" data-type="decimal" data-input="readonly" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost + Overtime
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly" data-input="readonly">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-3">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Achieved Target</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Start Delay (hrs)</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="startdelay_hrs" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Finish Delay (hrs)</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="finishdelay_hrs" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Is Target Achieved?</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="istargetachieved" data-input="readonly">
																			</td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<table width=100%>
														<tr>
															<td class="maxes_sct_tbl3">Labor Members</td>
														</tr>
													</table>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="recalculateLaborCost"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_labortransline_child" class="display" data-child="labortransline"></table>
														<div id="mxtable_labortransline_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Detail</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Team
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="teamnum" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Person ID
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="personid" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>WO
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Work Hours</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Start Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="startdate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Finish Date</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="enddate" data-type="date">
																			</td>
																		</tr>
																		<tr>
																			<td>Regular Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="reghours" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Extended Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="extendedhours" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Hours</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="totalhours" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Labor Cost</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Overtime?
																			</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="applyovertime">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Overtime Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="overtimecost" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Overtime Total Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="totalovertimecost" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="prelinecost" data-type="decimal" data-input="readonly" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost + Overtime
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly" data-input="readonly">
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
										<div id="maxes_acttools" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
												Tools Utilization
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="dispatchtools"></div>
													<div class="addrow" id="lendtools"></div>
													<div class="addrow" id="transfertools"></div>
												</div>
											</div>
											<div class="row eatfix"> 
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_tooltrans_child" class="display" data-child="tooltrans"></table>
														<div id="mxtable_tooltrans_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Plan
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="planlinenum" data-type="integer" data-key="value">
																			</td>
																		</tr>
																		<tr>
																			<td>For Task
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="tasklinenum" data-lookup="wotask|wo">
																			</td>
																		</tr>
																		<tr>
																			<td>Tool Line Num.
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="toolnum" data-lookup="wotask|wo">
																			</td>
																		</tr>
																		<tr>
																			<td>Description </td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="description">
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Quantity
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																			</td>
																		</tr>
																		<tr>
																			<td>Order Unit
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
																			</td>
																		</tr>
																		<tr>
																			<td>Unit Cost
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="readonly">
																			</td>
																		</tr>
																		<tr>
																			<td>Total Cost </td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="decimal">
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
								<div id="maxes02_1_6" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>WO</td>
														<td>
															<input type="text" data-attribute="wonum" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Asset</td>
														<td>
															<input type="text" data-navigation="assets" data-attribute="assetnum" data-lookup="assets" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Location</td>
														<td>
															<input type="text" data-attribute="location" data-lookup="locations" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Actual Cost</td>
														<td>
															<input type="text" data-attribute="actualcost" data-type="decimal" data-input="readonly">
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
													<td class="maxes_sct_tbl2">Scope of Works</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addwoterm"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-woterm" class="subform" data-child="woterm" data-table="mxtable_woterm_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Num.
															</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-type="integer" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Description</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="description">
															</td>
														</tr>
														<tr>
															<td>Meet Condition?
															</td>
															<td>
																<input type="checkbox" data-childfld="child" data-attribute="meetcondition">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_woterm_child" class="display" data-subform="subform-woterm"></table>
												<div id="mxtable_woterm_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-type="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description">
																	</td>
																</tr>
																<tr>
																	<td>Meet Condition?
																	</td>
																	<td>
																		<input type="checkbox" data-childfld="child" data-attribute="meetcondition">
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Progress & Log</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Progress (%)</td>
													<td>
														<input type="text" data-attribute="progress" data-input="readonly" data-type="decimal">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-2">
											<div class="addrow col-md-3" id="addwologs"></div>
										</div>
										<div class="col-md-2">
											<div class="addrow col-md-3" id="addactualtask"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div id="subform-wologs" class="subform" data-child="wologs" data-table="mxtable_wologs_child">
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>Num. Log
																</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="loglinenum" data-input="required" data-type="integer" data-key="value">
																</td>
															</tr>
															<tr>
																<td>Description</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="description">
																</td>
															</tr>
															<tr>
																<td>Reported By
																</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="reportedby" data-lookup="employees">
																</td>
															</tr>
															<tr>
																<td>Reported Date
																</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="reporteddate" data-type="date">
																</td>
															</tr>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_wologs_child" class="display" data-subform="subform-wologs"></table>
												<div id="mxtable_wologs_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-3">
															<div class="col-md-12 maxes_sct_tbl3">
																Detail
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Num. Log
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="loglinenum" data-input="required" data-type="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description">
																	</td>
																</tr>
																<tr>
																	<td>Reported By
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="reportedby" data-lookup="employees">
																	</td>
																</tr>
																<tr>
																	<td>Reported Date
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="reporteddate" data-type="date">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-3">
															<div class="col-md-12 maxes_sct_tbl3">
																Actual (Actual) Progress
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Task Line
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="tasklinenum" data-input="readonly" data-type="integer" data-childrentable="wotask" data-childrencolumn="tasklinenum,wonum">
																	</td>
																</tr>
																<tr>
																	<td>Target Start Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="tasklinenum.targstartdate" data-type="date" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Target Completed Date
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="tasklinenum.targcompdate" data-type="date" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Actual Start</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="actstartdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Actual Finish
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="actcompdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Progress (%)
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="progress" data-type="decimal">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-3">
															<div class="col-md-12 maxes_sct_tbl3">
																Status
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Status
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="Status" data-lookup="wotaskstatus">
																	</td>
																</tr>
																<tr>
																	<td>Status Date </td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="statusdate" data-type="date">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-3">
															<div class="col-md-12 maxes_sct_tbl3">
																Labors
															</div>
															<table class="tableDetailRow">
																<tr>
																	<td>Staff
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="labornum" data-lookup="employees">
																	</td>
																</tr>
																<tr>
																	<td>Team</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team">
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