<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="home">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div id="notification_container">
						<!-- will be loaded by the JS !-->
					</div>
					
					<!-- TABS -->
					<div class="row eatfix">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1">
									<a href="#maxes02_1_1">Budget</a>
								</li>
								<li class="maxes02_2">
									<a href="#maxes02_1_2">Inventory</a>
								</li>
								<li class="maxes02_2">
									<a href="#maxes02_1_3">Assets</a>
								</li>
								<li class="maxes02_2">
									<a href="#maxes02_1_4">Supply Chain</a>
								</li>
								<li class="maxes02_2">
									<a href="#maxes02_1_5">Productivity</a>
								</li>
							</ul>
							<div class="maxes04 shadow">
								<div id="maxes02_1_1" class="tab maxes02_1 mxcard_dashboard">
									<div class="internal_menu">
										<ul>
											<li id="budget_committed" >Budget Committed & Allocation</li>
											<li id="spending_analysis">Spending Analysis</li>
											<li id="total_spending">Total Spending Details</li>
										</ul>	
									</div>
									<!-- KPI Cards Row -->
									<div class="mxcard_grid mxcard_grid--dashboard">
										
										<!-- Kolom Kiri: 2 kartu vertikal -->
										<div class="mxcard_col mxcard_col--left">
											<div class="mxcard_card mxcard_card--sm">
												<div class="mxcard_header">
													<h4 class="mxcard_title">Total Budget</h4>
												</div>
												<div class="mxcard_body mxcard_kpi">
													<div class="mxcard_kpi_value" id="kpi_total_budget">$0</div>
													<div class="mxcard_kpi_label">Current Year</div>
												</div>
											</div>
											<div class="mxcard_card mxcard_card--sm">
												<div class="mxcard_header">
													<h4 class="mxcard_title">Usage</h4>
												</div>
												<div class="mxcard_body mxcard_kpi">
													<div class="mxcard_kpi_value" id="kpi_committed">$0</div>
													<div class="mxcard_kpi_change mxcard_kpi_change--positive" id="kpi_committed_change"></div>
												</div>
											</div>
										</div>

										<!-- Kolom Tengah: DIV baru untuk Pie Chart -->
										<div class="mxcard_col mxcard_col--center">
											<div class="mxcard_card mxcard_card--chart">
												<div class="mxcard_header">
													<h4 class="mxcard_title">Budget Distribution</h4>
												</div>
												<div class="mxcard_body">
													<div id="pie_chart_container"></div>
												</div>
											</div>
										</div>

										<!-- Kolom Kanan: 2 kartu vertikal -->
										<div class="mxcard_col mxcard_col--right">
											<div class="mxcard_card mxcard_card--sm">
												<div class="mxcard_header">
													<h4 class="mxcard_title">Variance</h4>
												</div>
												<div class="mxcard_body mxcard_kpi">
													<div class="mxcard_kpi_value" id="kpi_variance">$0</div>
													<span id="budget_flag"> </span>
												</div>
											</div>
											<div class="mxcard_card mxcard_card--sm">
												<div class="mxcard_header">
													<h4 class="mxcard_title">Utilization</h4>
												</div>
												<div class="mxcard_body mxcard_kpi">
													<div class="mxcard_kpi_value" id="kpi_utilization">0%</div>
													<div class="mxcard_kpi_sparkline" id="kpi_util_sparkline"></div>
												</div>
											</div>
										</div>

									</div>
									<!-- Main Charts Row 1 -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header" id="title_budget_committed">
												<div>
													<h4 class="mxcard_title">Budget vs. Committed Spending</h4>
													<div class="mxcard_subtitle">Year-over-year comparison</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Year</label>
														<input type="number" 
															class="mxcard_input" 
															data-attribute='budget_actual_spending_year' 
															min="2000" 
															max="2070"
															placeholder="2026"
															style="width: 100px;">
													</div>
													<button id="submitBtn_bas" class="mxcard_btn">Generate</button>
												</div>
												<div class="mxcard_chart" id="budgetActualSpending_chart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 5m ago</span>
												<span>Fiscal Year 2026</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Budget Allocation Breakdown</h4>
													<div class="mxcard_subtitle">By budget line category</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Budget Line</label>
														<select id="budgetline" class="mxcard_select" style="min-width: 200px;">
															<option value="" data-budgetnum="">Select Budget Line</option>
														</select>
													</div>
													<div class="mxcard_control_group">
														<label class="mxcard_label">Year</label>
														<input type="number" 
															class="mxcard_input" 
															data-attribute='budget_allocation_breakdown_year' 
															min="1900" 
															max="2100"
															style="width: 100px;">
													</div>
													<button id="submitBtn_bab" class="mxcard_btn">Generate</button>
												</div>
												<div class="mxcard_chart" id="budgetAllocationBreakdown_chart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 5m ago</span>
											</div>
										</div>
									</div>
									<!-- Spending Analysis Section -->
									<h3 class="mxcard_section_header" id="title_spending_analysis">
										Spending Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<!-- Spending Over Time - Full Width -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Spending by Category Over Time</h4>
													<div class="mxcard_subtitle">Track spending trends across categories</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Fullscreen">⛶</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Year</label>
														<input type="text" 
															class="mxcard_input" 
															data-attribute="prpo_analytics_year" 
															value="2026"
															style="width: 100px;">
													</div>
													<div class="mxcard_control_group">
														<label class="mxcard_label">Period</label>
														<label style="display: flex; align-items: center; gap: 4px; font-size: 13px;">
														<input type="radio" name="count_period" value="monthly" checked style="margin: 0;"> Monthly
														</label>
														<label style="display: flex; align-items: center; gap: 4px; font-size: 13px;">
														<input type="radio" name="count_period" value="quarterly" style="margin: 0;"> Quarterly
														</label>
													</div>
													<button id="submitBtn_generate" class="mxcard_btn">Generate</button>
												</div>
												<div class="mxcard_chart" id="spendingByCategoryOverTime_chart" style="min-height: 500px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 2m ago</span>
												<span>Showing monthly trends</span>
											</div>
										</div>
									</div>
									<!-- Total Count & Cost Row -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header" id="title_total_spending">
												<div>
													<h4 class="mxcard_title">Total Count Analysis</h4>
													<div class="mxcard_subtitle">Purchase order count by category</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="totalCountChart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 2m ago</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Total Cost Analysis</h4>
													<div class="mxcard_subtitle">Spending distribution by category</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="totalCostChart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 2m ago</span>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="internal_menu">
												<ul>
													<li id="budget_committed" >Budget Committed & Allocation</li>
													<li id="spending_analysis">Spending Analysis</li>
													<li id="total_spending">Total Spending Details</li>
												</ul>	
											</div>
										</div>
									</div>
									<div class="row eatfix"></div>
								</div>
								<div id="maxes02_1_2" class="maxes03 mxcard_dashboard">
									<div class="internal_menu">
										<ul>
											<li id="lead_time_analysis" >Lead Time Analysis</li>
											<li id="lead_time_details">Lead Time Details</li>
											<li id="abc_analysis">ABC Analysis</li>
											<li id="abc_classification">ABC Classification Details</li>
											<li id="inventory_movement">Inventory Movement</li>
											<li id="stock_forecasting">Stock & Forecasting</li>
											<li id="inventory_valuation">Inventory Valuation</li>
											
										</ul>	
									</div>
									<!-- KPI Cards Row -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Total Inventory Value</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_inv_value">$0</div>
												<div class="mxcard_kpi_label" id="kpi_total_items"></div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Usage</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_total_usage">0</div>
												<div class="mxcard_kpi_change mxcard_kpi_change--positive" id="kpi_items_change">
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Stock Turnover</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_turnover">0x</div>
												<span class="mxcard_badge mxcard_badge--success" id="kpi_turnover_badge">Healthy</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Low Stock Items</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_low_stock">0</div>
												<span id="kpi_low_stock_status"></span>
											</div>
										</div>
									</div>
									<!-- Lead Time Analysis Section -->
									<h3 class="mxcard_section_header" id="title_lead_time_analysis"> 
										Lead Time Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Average Lead Time per Item</h4>
													<div class="mxcard_subtitle">Internal vs External lead time comparison</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Download Data">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Year</label>
														<input type="number" 
															class="mxcard_input" 
															data-attribute='analytics_year' 
															min="1900" 
															max="2100"
															placeholder="2026"
															style="width: 100px;">
													</div>
													<button id="submitBtn_il" class="mxcard_btn">Find out Lead Time Analysis</button>
												</div>
												<div class="mxcard_chart" id="leadTimeChart_item" style="min-height: 450px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 10m ago</span>
												<span>Average lead time across all items</span>
											</div>
										</div>
									</div>
									<!-- Lead Time Table Card -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title" id="title_lead_time_details">Lead Time Details</h4>
													<div class="mxcard_subtitle">Detailed breakdown by item</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Export CSV">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body mxcard_body--no-padding">
												<div style="padding: 20px; overflow-x: auto;">
													<table id="itemLeadTimeTable" class="display" style="width:100% !important">
														<thead>
															<tr>
																<th>Item Number</th>
																<th>Description</th>
																<th>Average Internal Lead Time</th>
																<th>Average External Lead Time</th>
																<th>Average Total Lead Time</th>
															</tr>
														</thead>
														<tfoot>
															<tr>
																<th>Item Number</th>
																<th>Description</th>
																<th>Average Internal Lead Time</th>
																<th>Average External Lead Time</th>
																<th>Average Total Lead Time</th>
															</tr>
														</tfoot>
													</table>
												</div>
											</div>
										</div>
									</div>
									<!-- ABC Analysis Section -->
									<h3 class="mxcard_section_header" id="title_abc_analysis">
										ABC Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Activity Based Cost (ABC) Analysis</h4>
													<div class="mxcard_subtitle">Pareto analysis of inventory value</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Start Date</label>
														<input type="date" class="mxcard_input" data-attribute='startDate'>
													</div>
													<div class="mxcard_control_group">
														<label class="mxcard_label">End Date</label>
														<input type="date" class="mxcard_input" data-attribute='endDate'>
													</div>
													<button id="submitBtn" class="mxcard_btn">Generate ABC Analysis</button>
												</div>
												<div class="mxcard_chart" id="ABCChart" style="min-height: 550px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated just now</span>
											</div>
										</div>
									</div>
									<!-- ABC Insights Cards -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<h4 class="mxcard_title">ABC Analysis Summary</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 12px; background: rgba(45, 114, 210, 0.1); border-radius: 6px; border-left: 3px solid #2D72D2;">
													<div id="abc_statement" style="font-size: 14px; line-height: 1.6; margin-bottom: 12px;"></div>
													<div id="abc_explanation" style="font-size: 13px; line-height: 1.5;"></div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<h4 class="mxcard_title">ABC Distribution</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div style="display: flex; justify-content: space-around; width: 100%;">
													<div style="text-align: center;">
														<div class="mxcard_kpi_value" style="font-size: 24px; color: #3DCC91;">A</div>
														<div class="mxcard_kpi_label" id="abc_a_items">~20% Items</div>
														<div style="font-size: 12px; margin-top: 4px;" id="abc_a_value">80% Value</div>
													</div>
													<div style="text-align: center;">
														<div class="mxcard_kpi_value" style="font-size: 24px; color: #FFB366;">B</div>
														<div class="mxcard_kpi_label"  id="abc_b_items">~30% Items</div>
														<div style="font-size: 12px; margin-top: 4px;" id="abc_b_value">15% Value</div>
													</div>
													<div style="text-align: center;">
														<div class="mxcard_kpi_value" style="font-size: 24px; color: #FF7373;">C</div>
														<div class="mxcard_kpi_label" id="abc_c_items">~50% Items</div>
														<div style="font-size: 12px; margin-top: 4px;" id="abc_c_value">5% Value</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- ABC Table Card -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title" id="title_abc_classification" >ABC Classification Details</h4>
													<div class="mxcard_subtitle">Complete item ranking and contribution</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Export CSV">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body mxcard_body--no-padding">
												<div style="padding: 20px; overflow-x: auto;">
													<table id="abcTable" class="display" style="width:100%">
														<thead>
															<tr>
																<th>Item Number</th>
																<th>Description</th>
																<th>Contribution (%)</th>
																<th>Cumulative Value (%)</th>
																<th>Cumulative Items (%)</th>
																<th>ABC Rank</th>
															</tr>
														</thead>
														<tfoot>
															<tr>
																<th>Item Number</th>
																<th>Description</th>
																<th>Contribution (%)</th>
																<th>Cumulative Value (%)</th>
																<th>Cumulative Items (%)</th>
																<th>Rank</th>
															</tr>
														</tfoot>
													</table>
												</div>
											</div>
										</div>
									</div>
									<!-- Inventory Movement Section -->
									<h3 class="mxcard_section_header" id="title_inventory_movement">
										Inventory Movement
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Inventory Movement Status</h4>
													<div class="mxcard_subtitle">Fast, slow, and non-moving items</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Year</label>
														<input type="number" 
															class="mxcard_input" 
															data-attribute='movement_year' 
															min="1900" 
															max="2100"
															style="width: 100px;">
													</div>
													<button id="submitBtn_my" class="mxcard_btn">Generate Chart</button>
												</div>
												<div class="mxcard_chart" id="movementStatusChart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 15m ago</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Movement Summary</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
													<div id="invmov_statement"></div>
													<button id="btn_seedetail" 
														class="mxcard_btn" 
														data-toggle="modal" 
														data-target="#invmovDetailModal"
														style="width: 100%;">
													See Detailed Items
													</button>
												</div>
											</div>
										</div>
									</div>
									<!-- Stock Fluctuation & Safety Stock Row -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Stock Fluctuation</h4>
													<div class="mxcard_subtitle">Monthly stock levels by warehouse</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Year</label>
														<input type="number" 
															class="mxcard_input" 
															data-attribute='stockyear' 
															min="1900" 
															max="2100"
															style="width: 100px;">
													</div>
													<button id="submitBtn_aggregrate" class="mxcard_btn">Generate Chart</button>
												</div>
												<div class="mxcard_chart" id="stokBulananWarehouseChart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 8m ago</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Safety Stock Level</h4>
													<div class="mxcard_subtitle">Items with highest minimum stock</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="top20Chart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 12m ago</span>
											</div>
										</div>
									</div>
									<!-- Forecasting Section -->
									<h3 class="mxcard_section_header" id="title_stock_forecasting">
										Demand Forecasting
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Simple Average Forecasting (SAF)</h4>
													<div class="mxcard_subtitle">By item group</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Item Group</label>
														<select id="itemroupSelect" class="mxcard_select" style="min-width: 200px;"></select>
													</div>
												</div>
												<div class="mxcard_chart" id="forecastChart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 5m ago</span>
												<span class="mxcard_badge mxcard_badge--info">Forecast Model: SAF</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Moving Average Forecasting (MAF)</h4>
													<div class="mxcard_subtitle">By item group</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Item Group</label>
														<select id="itemroupSelect_ma" class="mxcard_select" style="min-width: 200px;"></select>
													</div>
												</div>
												<div class="mxcard_chart" id="forecastMovingChart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 5m ago</span>
												<span class="mxcard_badge mxcard_badge--info">Forecast Model: MAF</span>
											</div>
										</div>
									</div>
									<!-- Inventory Worth Section -->
									<h3 class="mxcard_section_header" id="title_inventory_valuation">
										Inventory Valuation
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Inventory Value by Category</h4>
													<div class="mxcard_subtitle">Total worth distribution</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="inventoryValueChart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 3m ago</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Inventory Quantity by Category</h4>
													<div class="mxcard_subtitle">Item count distribution</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="inventoryChart"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 3m ago</span>
											</div>
										</div>
									</div>

									<div class="row eatfix">
										<div class="col-md-12">
											<div class="internal_menu">
												<ul>
													<li id="lead_time_analysis" >Lead Time Analysis</li>
													<li id="lead_time_details">Lead Time Details</li>
													<li id="abc_analysis">ABC Analysis</li>
													<li id="abc_classification">ABC Classification Details</li>
													<li id="inventory_movement">Inventory Movement</li>
													<li id="stock_forecasting">Stock & Forecasting</li>
													<li id="inventory_valuation">Inventory Valuation</li>
												</ul>	
											</div>
										</div>
									</div>
									<div class="row eatfix"></div>
									<!-- MODAL: Inventory Movement Detail -->
									<div class="modal fade" id="invmovDetailModal" tabindex="-1" role="dialog" aria-labelledby="invmovDetailModalLabel" aria-hidden="true">
										<div class="modal-dialog modal-xl" role="document">
											<div class="modal-content">
												<div class="modal-header"">
													<h5 class="modal-title" id="invmovDetailModalLabel">Inventory Detail Items</h5>
													<button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">×</span>
													</button>
												</div>
												<div class="modal-body">
													<table id="invmovTable" class="display compact" style="width:100%">
														<thead>
															<tr>
																<th></th>
																<th>Item Number</th>
																<th>Condition Type</th>
																<th>Warehouse</th>
																<th>Rack</th>
																<th>Curbal</th>
																<th>Avg. Cost</th>
																<th>Line Cost</th>
																<th>Last Issue Date</th>
																<th>Total Issued</th>
																<th>Movement Status</th>
															</tr>
														</thead>
														<tfoot>
															<tr>
																<th></th>
																<th>Item Number</th>
																<th>Condition Type</th>
																<th>Warehouse</th>
																<th>Rack</th>
																<th>Curbal</th>
																<th>Avg. Cost</th>
																<th>Line Cost</th>
																<th>Last Issue Date</th>
																<th>Total Issued</th>
																<th>Movement Status</th>
															</tr>
														</tfoot>
													</table>
												</div>
												<div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1);">
													<button type="button" class="mxcard_btn mxcard_btn--secondary" data-dismiss="modal">Close</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_3" class="maxes03 mxcard_dashboard">
									<div class="internal_menu">
										<ul> 
											<li id="year2date_analysis" >Year to Date Cost Analysis</li>
											<li id="maint_forecasting">Maintenance Forecasting</li>
											<li id="cba">Cost Benefit Analysis</li>
											<li id="asset_model_ca">Asset Model Cost Analysis</li>
											<li id="asset_performance">Asset Performance Summary</li>
										</ul>	
									</div>
									<!-- KPI Cards Row -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Total Asset Value</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_asset_value">$0</div>
												<div class="mxcard_kpi_label">Current Book Value</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">YTD Maintenance Cost</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_ytd_cost">$0</div>
												<div class="mxcard_kpi_change mxcard_kpi_change--negative" id="kpi_cost_change">
													↑ 8.3% vs last year
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Incoming Downtime</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_incoming_downtime">0</div>
												<div class="mxcard_kpi_change mxcard_kpi_change--negative" id="kpi_incoming_downtime_date">
													2025-12-17
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">In Downtime</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_roi">0</div>
												<div class="mxcard_kpi_label" id="kpi_wo_downntime"></div>
											</div>
										</div>
									</div>
									<!-- YTD Cost Analysis Section -->
									<h3 class="mxcard_section_header" id="title_year2date_analysis">
										Year-to-Date Cost Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">YTD Cost (Actual & Roll Up) vs Investment</h4>
													<div class="mxcard_subtitle">Compare actual spending, rolled up costs, and investment values</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Fullscreen">⛶</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Start Date</label>
														<input type="date" class="mxcard_input" data-attribute='startDate_ytd'>
													</div>
													<div class="mxcard_control_group">
														<label class="mxcard_label">End Date</label>
														<input type="date" class="mxcard_input" data-attribute='endDate_ytd'>
													</div>
													<button id="submitBtn_ytd" class="mxcard_btn">Generate Chart</button>
												</div>
												<div class="mxcard_chart" id="assetYtdCostChart" style="min-height: 450px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 7m ago</span>
												<span>Comparing actual vs investment values</span>
											</div>
										</div>
									</div>
									<!-- Maintenance Forecasting Section -->
									<h3 class="mxcard_section_header" id="title_maint_forecasting">
										Maintenance Planning
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Maintenance Forecasting</h4>
													<div class="mxcard_subtitle">Demand vs supply timeline for maintenance activities</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="forecastDemand_supplyTimeLine" style="min-height: 400px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 12m ago</span>
												<span class="mxcard_badge mxcard_badge--info">Predictive Model</span>
											</div>
										</div>
									</div>
									<!-- Cost-Benefit Analysis Section -->
									<h3 class="mxcard_section_header" id="title_cba">
										Cost-Benefit Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Cost-Benefit Analysis by Location</h4>
													<div class="mxcard_subtitle">Asset performance and costs across locations</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Start Date</label>
														<input type="text" class="mxcard_input" data-attribute="al_startdate" data-type="date">
													</div>
													<div class="mxcard_control_group">
														<label class="mxcard_label">End Date</label>
														<input type="text" class="mxcard_input" data-attribute="al_enddate" data-type="date">
													</div>
													<button id="al_ok" class="mxcard_btn">OK</button>
												</div>
												<div class="mxcard_chart" id="assetLocationChart" style="min-height: 450px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 10m ago</span>
											</div>
										</div>
									</div>
									<!-- Asset Model Analysis Section -->
									<h3 class="mxcard_section_header" id="title_asset_model_ca">
										Asset Model Cost Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Asset Model Cost Analysis</h4>
													<div class="mxcard_subtitle">Compare maintenance costs across asset models</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Select Asset</label>
														<select id="assetSelect" class="mxcard_select" style="min-width: 200px;">
															<option value="">Display All</option>
														</select>
													</div>
													<button id="compareAssetButton" class="mxcard_btn">Compare Asset</button>
													<button id="clearComparisonButton" class="mxcard_btn mxcard_btn--secondary">Clear Comparison</button>
													<div style="width: 100%; border-top: 1px solid rgba(255,255,255,0.08); margin: 12px 0;"></div>
													<div class="mxcard_control_group">
														<label class="mxcard_label">Start Date</label>
														<input type="text" class="mxcard_input" data-attribute="am_startdate" data-type="date">
													</div>
													<div class="mxcard_control_group">
														<label class="mxcard_label">End Date</label>
														<input type="text" class="mxcard_input" data-attribute="am_enddate" data-type="date">
													</div>
													<button id="am_ok" class="mxcard_btn">OK</button>
												</div>
												<div class="mxcard_chart" id="assetModelChart" style="min-height: 450px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 6m ago</span>
												<span id="asset_comparison_count">Showing all assets</span>
											</div>
										</div>
									</div>
									<!-- Asset Comparison Chart -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Asset Comparison View</h4>
													<div class="mxcard_subtitle">Side-by-side comparison of selected assets</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Fullscreen">⛶</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="compareChartContainer" style="min-height: 450px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 6m ago</span>
												<span class="mxcard_badge mxcard_badge--info">Comparison Mode</span>
											</div>
										</div>
									</div>
									<!-- Asset Summary Cards -->
									<h3 class="mxcard_section_header" id="title_asset_performance">
										Asset Performance Summary
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Best Performing Asset</h4>
											</div>
											<div class="mxcard_body" style="display: flex; justify-content: center; align-items: center;">
												<div style="padding: 16px; text-align: center;">
													<div style="font-size: 18px; font-weight: 600; color: #3DCC91; margin-bottom: 8px;" id="best_asset_name">
														Asset #001
													</div>
													<div class="mxcard_subtitle" id="best_asset_description">
													</div>
													<div style="font-size: 13px; margin-bottom: 16px;">
														Lowest cost-to-value ratio
													</div>
													<div style="display: flex; justify-content: space-around; margin-top: 20px;">
														<div>
															<div id="best_maint_cost" style="font-size: 20px; font-weight: 600; ">$12.5K</div>
															<div style="font-size: 11px; margin-top: 4px;">Total Maintenance Cost</div>
														</div>
														<div>
															<div id="best_efficiency_ratio" style="font-size: 20px; font-weight: 600; color: #3DCC91;">95%</div>
															<div style="font-size: 11px; margin-top: 4px;">Efficiency</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Worst Performing Asset</h4>
											</div>
											<div class="mxcard_body" style="display: flex; justify-content: center; align-items: center;">
												<div style="padding: 16px; text-align: center;">
													<div style="font-size: 18px; font-weight: 600; color: #FF7373; margin-bottom: 8px;" id="worst_asset_name">
														Asset #042
													</div>
													<div class="mxcard_subtitle" id="worst_asset_description">
													</div>
													<div style="font-size: 13px; margin-bottom: 16px;">
														Highest cost-to-value ratio
													</div>
													<div style="display: flex; justify-content: space-around; margin-top: 20px;">
														<div>
															<div id="worst_maint_cost" style="font-size: 20px; font-weight: 600; ">$48.2K</div>
															<div style="font-size: 11px; margin-top: 4px;">Total Maintenance Cost</div>
														</div>
														<div>
															<div id="worst_efficiency_ratio" style="font-size: 20px; font-weight: 600; color: #FF7373;">62%</div>
															<div style="font-size: 11px; margin-top: 4px;">Efficiency</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">WO Status Distribution</h4>
													<div class="mxcard_subtitle">Work order breakdown by status</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" onclick="fetchWOPerformance()" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body" data-wo-card="body">
												<!-- Content will be loaded dynamically -->
												<div class="mxcard_loading">
													<div class="mxcard_spinner"></div>
													<div style="margin-top: 12px; ">Loading WO data...</div>
												</div>
											</div>
											<div class="mxcard_footer">
												<span>Total WOs: <strong data-wo-kpi="total">-</strong></span>
											</div>
										</div>
									
									
									
									</div>
									<!-- Key Insights Card -->
									<div class="mxcard_grid" style="display:none;">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<h4 class="mxcard_title">💡 Key Insights & Recommendations</h4>
											</div>
											<div class="mxcard_body">
												<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
													<div style="padding: 16px; background: rgba(45, 114, 210, 0.1); border-radius: 6px; border-left: 3px solid #2D72D2;">
														<div style="font-weight: 600; color: #48AFF0; margin-bottom: 8px; font-size: 14px;">📊 Cost Trend</div>
														<div style=" font-size: 13px; line-height: 1.5;">
															Maintenance costs have increased by 8.3% compared to last year. Consider reviewing preventive maintenance schedules.
														</div>
													</div>
													<div style="padding: 16px; background: rgba(15, 153, 96, 0.1); border-radius: 6px; border-left: 3px solid #0F9960;">
														<div style="font-weight: 600; color: #3DCC91; margin-bottom: 8px; font-size: 14px;">✓ Efficiency</div>
														<div style=" font-size: 13px; line-height: 1.5;">
															Top 20% of assets deliver 65% of total value. Focus optimization efforts on these high-performers.
														</div>
													</div>
													<div style="padding: 16px; background: rgba(217, 130, 43, 0.1); border-radius: 6px; border-left: 3px solid #D9822B;">
														<div style="font-weight: 600; color: #FFB366; margin-bottom: 8px; font-size: 14px;">⚠️ Action Required</div>
														<div style=" font-size: 13px; line-height: 1.5;">
															3 assets have overdue maintenance. Schedule immediate inspection to prevent downtime.
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="row eatfix">
										<div class="col-md-12">
											<div class="internal_menu">
												<ul> 
													<li id="year2date_analysis" >Year to Date Cost Analysis</li>
													<li id="maint_forecasting">Maintenance Forecasting</li>
													<li id="cba">Cost Benefit Analysis</li>
													<li id="asset_model_ca">Asset Model Cost Analysis</li>
													<li id="asset_performance">Asset Performance Summary</li>
												</ul>	
											</div>
										</div>
									</div>
									<div class="row eatfix"></div>

								</div>
								<div id="maxes02_1_4" class="maxes03 mxcard_dashboard">
									
									<div class="internal_menu">
										<ul> 
											<li id="sc_lead_time_analysis" >Lead Time Analysis</li>
											<li id="po_details">PO Details</li>
											<li id="gr_process">Good Receipt Process</li>
											<li id="gr_inspection_details">Good Receipt Inspection Details</li>
											<li id="inspection_trends">Inspection Trends</li>
										</ul>
									</div>
									
									<!-- KPI Cards Row -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Avg Lead Time</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_avg_leadtime">0</div>
												<div class="mxcard_kpi_label" id="kpi_avg_leadtime_unit">Days</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">On-Time Delivery</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_ontime">0%</div>
												<div class="mxcard_kpi_change mxcard_kpi_change--positive" id="kpi_ontime_change">
													↑ 3.2% vs last month
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Active POs</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_active_pos">0</div>
												<span class="mxcard_badge mxcard_badge--success">In Progress</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Inspection Pass Rate</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_pass_rate">0%</div>
												<div class="mxcard_kpi_label">Quality Score</div>
											</div>
										</div>
									</div>
									<!-- Lead Time Analysis Section -->
									<h3 class="mxcard_section_header" id="sc_lead_time_analysis">
										Lead Time Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Purchase Order Lead Time Analysis</h4>
													<div class="mxcard_subtitle">Internal vs External lead time breakdown by vendor</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Fullscreen">⛶</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="leadTimeChart" style="min-height: 450px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 5m ago</span>
												<span>Showing all active vendors</span>
											</div>
										</div>
									</div>
									<!-- Lead Time Statistics Cards -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Lead Time Breakdown</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 12px;">
													<div style="margin-bottom: 20px;">
														<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
															<span style="font-size: 13px; ">Internal Processing</span>
															<span style="font-size: 14px; font-weight: 600; color: #48AFF0;" id="stat_internal_days">0 days</span>
														</div>
														<div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
															<div id="stat_internal_bar" style="height: 100%; width: 40%; background: #48AFF0;"></div>
														</div>
													</div>
													<div style="margin-bottom: 20px;">
														<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
															<span style="font-size: 13px; ">External Transit</span>
															<span style="font-size: 14px; font-weight: 600; color: #3DCC91;" id="stat_external_days">0 days</span>
														</div>
														<div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
															<div id="stat_external_bar" style="height: 100%; width: 60%; background: #3DCC91;"></div>
														</div>
													</div>
													<div>
														<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
															<span style="font-size: 13px; ">Total Lead Time</span>
															<span style="font-size: 16px; font-weight: 700; " id="stat_total_days">0 days</span>
														</div>
														<div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
															<div id="stat_total_bar" style="height: 100%; width: 100%; background: linear-gradient(to right, #48AFF0 40%, #3DCC91 40%);"></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Vendor Performance</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 12px;">
													<div style="margin-bottom: 16px;">
														<div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Fastest Vendor</div>
														<div style="font-size: 16px; font-weight: 600; color: #3DCC91;" id="fastest_vendor">Vendor A</div>
														<div style="font-size: 13px; margin-top: 4px;" id="fastest_vendor_time">12 days avg</div>
													</div>
													<div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
														<div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Slowest Vendor</div>
														<div style="font-size: 16px; font-weight: 600; color: #FF7373;" id="slowest_vendor">Vendor B</div>
														<div style="font-size: 13px; margin-top: 4px;" id="slowest_vendor_time">45 days avg</div>
													</div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Lead Time Targets</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div style="display: flex; flex-direction: column; gap: 20px; width: 100%;">
													<div style="text-align: center;">
														<div class="mxcard_kpi_value" style="font-size: 28px; color: #3DCC91;" id='target_pct'></div>
														<div class="mxcard_kpi_label" id="target_label">Within Target</div>
														<span class="mxcard_badge mxcard_badge--success" style="margin-top: 8px;" id="target_badge"></span>
													</div>
													<div style="padding: 12px; background: rgba(255,255,255,0.05); border-radius: 6px; text-align: center;">
														<div style="font-size: 12px; margin-bottom: 4px;" id="target_val_goal"></div>
														<div style="font-size: 12px; " id="target_val_actual"></div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- PO Details Table -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title" id="title_po_details">Purchase Order Details</h4>
													<div class="mxcard_subtitle">Complete PO timeline and lead time breakdown</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Export CSV">⤓</button>
													<button class="mxcard_action_btn" title="Filter">🔍</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body mxcard_body--no-padding">
												<div style="padding: 20px; overflow-x: auto;">
													<table id="leadTimeTable" class="display" style="width:100%">
														<thead>
															<tr>
																<th>PO#</th>
																<th>Vendor</th>
																<th>Order Date</th>
																<th>Last Received Date</th>
																<th>Internal Lead Time</th>
																<th>External Lead Time</th>
																<th>Lead Time</th>
															</tr>
														</thead>
														<tfoot>
															<tr>
																<th>PO#</th>
																<th>Vendor</th>
																<th>Order Date</th>
																<th>Last Received Date</th>
																<th>Internal Lead Time</th>
																<th>External Lead Time</th>
																<th>Lead Time</th>
															</tr>
														</tfoot>
													</table>
												</div>
											</div>
										</div>
									</div>
									<!-- Good Receipt Process Section -->
									<h3 class="mxcard_section_header" id="title_gr_process">
										Good Receipt Process
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--xl">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Good Receipt Funnel</h4>
													<div class="mxcard_subtitle">Inspection process flow and conversion rates</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="good-receipt-funnel-chart" style="min-height: 400px;"></div>
											</div>
											<div class="mxcard_footer">
												<!--<span class="mxcard_timestamp">🕐 Updated 8m ago</span> -->
												<span>Overall conversion rate: <strong id="funnel_conversion">0%</strong></span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Process Metrics</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 12px;">
													<div style="margin-bottom: 20px;">
														<div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Pass Rate</div>
														<div style="display: flex; align-items: baseline; gap: 8px;">
															<div style="font-size: 32px; font-weight: 700; color: #3DCC91;" id = "passrate">94.2%</div>
															<span class="mxcard_badge mxcard_badge--success" id = "passrate_badge"></span>
														</div>
													</div>
													<div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px; margin-bottom: 16px;">
														<div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Avg Inspection Time</div>
														<div style="font-size: 24px; font-weight: 600; " id = "avginspect">2.3 days</div>
													</div>
													<div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
														<div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Rejection Rate</div>
														<div style="display: flex; align-items: baseline; gap: 8px;">
															<div style="font-size: 24px; font-weight: 600; color: #FFB366;" id = "rejectrate">5.8%</div>
															<span class="mxcard_badge mxcard_badge--warning" id = "rejectrate_badge"></span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- Funnel Description -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header" >
												<h4 class="mxcard_title" id="title_gr_inspection_details">Good Receipt Inspection Process Details</h4>
											</div>
											<div class="mxcard_body">
												<div id="funnel-description" style="padding: 12px;">
													<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
														<div style="padding: 16px; background: rgba(45, 114, 210, 0.1); border-radius: 6px; border-left: 3px solid #2D72D2;">
															<div style="font-weight: 600; color: #48AFF0; margin-bottom: 8px; font-size: 14px;">📦 Received</div>
															<div style=" font-size: 13px; line-height: 1.5;">
																Initial goods receipt from vendors. All items logged into system for tracking.
															</div>
														</div>
														<div style="padding: 16px; background: rgba(45, 114, 210, 0.1); border-radius: 6px; border-left: 3px solid #2D72D2;">
															<div style="font-weight: 600; color: #48AFF0; margin-bottom: 8px; font-size: 14px;">🔍 Inspected</div>
															<div style=" font-size: 13px; line-height: 1.5;">
																Quality inspection conducted. Items checked against specifications and PO requirements.
															</div>
														</div>
														<div style="padding: 16px; background: rgba(15, 153, 96, 0.1); border-radius: 6px; border-left: 3px solid #0F9960;">
															<div style="font-weight: 600; color: #3DCC91; margin-bottom: 8px; font-size: 14px;">✓ Approved</div>
															<div style=" font-size: 13px; line-height: 1.5;">
																Items passed inspection and approved for use. Ready for inventory placement.
															</div>
														</div>
														<div style="padding: 16px; background: rgba(217, 130, 43, 0.1); border-radius: 6px; border-left: 3px solid #D9822B;">
															<div style="font-weight: 600; color: #FFB366; margin-bottom: 8px; font-size: 14px;">⚠️ Rejected</div>
															<div style=" font-size: 13px; line-height: 1.5;">
																Items failed inspection. Vendor notified for return or replacement.
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- Monthly Inspection Trends -->
									<h3 class="mxcard_section_header" id="title_inspection_trends">
										Inspection Trends
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Monthly Inspection Volume</h4>
													<div class="mxcard_subtitle">Track inspection activity and trends over time</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="View Year">📅</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="rfs-per-month-chart" style="min-height: 400px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 4m ago</span>
												<span>Showing last 12 months</span>
											</div>
										</div>
									</div>
									<!-- Supply Chain Health Score -->
									<h3 class="mxcard_section_header" style="display:none;">
										Supply Chain Health
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid" style="display:none">
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Overall Health Score</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div style="position: relative; width: 120px; height: 120px; margin: 0 auto 16px;">
													<svg viewBox="0 0 120 120" style="transform: rotate(-90deg);">
														<circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="10"/>
														<circle cx="60" cy="60" r="50" fill="none" stroke="#3DCC91" stroke-width="10" 
															stroke-dasharray="314" stroke-dashoffset="63" stroke-linecap="round"/>
													</svg>
													<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
														<div style="font-size: 28px; font-weight: 700; color: #3DCC91;">85</div>
														<div style="font-size: 11px; ">/ 100</div>
													</div>
												</div>
												<span class="mxcard_badge mxcard_badge--success">Excellent</span>
												<div style="margin-top: 16px; font-size: 12px; text-align: center; line-height: 1.5;">
													Supply chain performing above target across all metrics
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Key Metrics</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 8px;">
													<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
														<span style="font-size: 13px; ">Lead Time Performance</span>
														<div style="display: flex; align-items: center; gap: 8px;">
															<span style="font-size: 14px; font-weight: 600; color: #3DCC91;">92%</span>
															<span style="color: #3DCC91; font-size: 12px;">▲</span>
														</div>
													</div>
													<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
														<span style="font-size: 13px; ">Quality Score</span>
														<div style="display: flex; align-items: center; gap: 8px;">
															<span style="font-size: 14px; font-weight: 600; color: #3DCC91;">94%</span>
															<span style="color: #3DCC91; font-size: 12px;">▲</span>
														</div>
													</div>
													<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
														<span style="font-size: 13px; ">Vendor Reliability</span>
														<div style="display: flex; align-items: center; gap: 8px;">
															<span style="font-size: 14px; font-weight: 600; color: #FFB366;">78%</span>
															<span style="color: #FFB366; font-size: 12px;">▼</span>
														</div>
													</div>
													<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0;">
														<span style="font-size: 13px; ">Process Efficiency</span>
														<div style="display: flex; align-items: center; gap: 8px;">
															<span style="font-size: 14px; font-weight: 600; color: #3DCC91;">88%</span>
															<span style="color: #3DCC91; font-size: 12px;">▲</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--md">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Action Items</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 8px;">
													<div style="padding: 12px; background: rgba(217, 130, 43, 0.1); border-radius: 6px; border-left: 3px solid #D9822B; margin-bottom: 12px;">
														<div style="font-size: 12px; font-weight: 600; color: #FFB366; margin-bottom: 4px;">⚠️ Vendor Reliability</div>
														<div style="font-size: 12px;  line-height: 1.4;">
															2 vendors below 75% threshold
														</div>
													</div>
													<div style="padding: 12px; background: rgba(45, 114, 210, 0.1); border-radius: 6px; border-left: 3px solid #2D72D2; margin-bottom: 12px;">
														<div style="font-size: 12px; font-weight: 600; color: #48AFF0; margin-bottom: 4px;">ℹ️ Review Required</div>
														<div style="font-size: 12px;  line-height: 1.4;">
															8 POs pending inspection >5 days
														</div>
													</div>
													<div style="padding: 12px; background: rgba(15, 153, 96, 0.1); border-radius: 6px; border-left: 3px solid #0F9960;">
														<div style="font-size: 12px; font-weight: 600; color: #3DCC91; margin-bottom: 4px;">✓ On Track</div>
														<div style="font-size: 12px;  line-height: 1.4;">
															Monthly targets being met
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="row eatfix">
										<div class="col-md-12">
											<div class="internal_menu">
												<ul> 
													<li id="lead_time_analysis" >Lead Time Analysis</li>
													<li id="po_details">PO Details</li>
													<li id="gr_process">Good Receipt Process</li>
													<li id="gr_inspection_details">Good Receipt Inspection Details</li>
													<li id="inspection_trends">Inspection Trends</li>
												</ul>	
											</div>
										</div>
									</div>
									<div class="row eatfix"></div>
								</div>
								<div id="maxes02_1_5" class="maxes03 mxcard_dashboard">
									<div class="internal_menu">
										<ul> 
											<li id="downtime_analysis" >Downtime Analysis</li>
											<li id="productivity_kpi">Productivity KPI</li>
										</ul> 
									</div>
									<!-- KPI Cards Row -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Total Uptime</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_uptime">0%</div>
												<div class="mxcard_kpi_label">Operational Efficiency</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Total Downtime</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_downtime">0</div>
												<div class="mxcard_kpi_label">Hours Lost</div>
												<div class="mxcard_kpi_change mxcard_kpi_change--negative" id="kpi_downtime_change">
													↑ 12% vs last month
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Avg Productivity</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_productivity">0%</div>
												<span class="mxcard_badge mxcard_badge--success">Above Target</span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Current Production</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div class="mxcard_kpi_value" id="kpi_production_count">0</div>
												<div class="mxcard_kpi_label">UNITS</div>
											</div>
										</div>
									</div>
									<!-- Downtime Analysis Section -->
									<h3 class="mxcard_section_header" id="title_downtime_analysis">
										Downtime Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Total Downtime Overview</h4>
													<div class="mxcard_subtitle">Monthly downtime hours by category</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Fullscreen">⛶</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_controls">
													<div class="mxcard_control_group">
														<label class="mxcard_label">Year</label>
														<input type="number" 
															class="mxcard_input" 
															data-attribute='totaldowntime_chart_year' 
															min="1900" 
															max="2100"
															style="width: 100px;">
															<label class="mxcard_label">| OR |</label>

														<label class="mxcard_label">Prod. Start Date </label> <input data-attribute="prodstartdate" data-type="date">
														<label class="mxcard_label">Prod. End Date </label>  <input data-attribute="prodenddate" data-type="date">
													</div>
													<button id="submitBtn_tdt" class="mxcard_btn">Generate Chart</button>
												</div>
												<div class="mxcard_chart" id="totaldowntime_chart" style="min-height: 350px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_timestamp">🕐 Updated 3m ago</span>
												<span>Year-to-date total: <strong id="ytd_downtime">0h</strong></span>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Downtime Detail by Asset/Equipment</h4>
													<div class="mxcard_subtitle">Granular breakdown of downtime incidents</div>
												</div>
												
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="totaldowntime_detail_chart" style="min-height: 450px;"></div>
											</div>
										</div>
									</div>
									
										<!-- Performance Metrics Grid -->
									<div class="mxcard_grid" id="title_productivity_kpi">
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Equipment Availability</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div style="position: relative; width: 140px; height: 140px; margin: 0 auto 16px;">
													<svg viewBox="0 0 140 140" style="transform: rotate(-90deg);">
														<circle cx="70" cy="70" r="60" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="12"/>
														<circle id="kpi_avail_circle" cx="70" cy="70" r="60" fill="none" stroke="#3DCC91" stroke-width="12" 
															stroke-dasharray="377" stroke-dashoffset="38" stroke-linecap="round"/>
													</svg>
													<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
														<div style="font-size: 20pt; font-weight: 700; color: #3DCC91;" id="stats_availability"></div>
														<div style="font-size: 11px; margin-top: 4px;">Available</div>
													</div>
												</div>
												<div style="text-align: center; padding: 0 16px;">
													<div style="font-size: 12px; line-height: 1.5;">
														Equipment ready and operational for production
													</div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Productivity Performance</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div style="position: relative; width: 140px; height: 140px; margin: 0 auto 16px;">
													<svg viewBox="0 0 140 140" style="transform: rotate(-90deg);">
														<circle cx="70" cy="70" r="60" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="12"/>
														<circle id="kpi_performance_circle" cx="70" cy="70" r="60" fill="none" stroke="#48AFF0" stroke-width="12" 
															stroke-dasharray="377" stroke-dashoffset="53" stroke-linecap="round"/>
													</svg>
													<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
														<div style="font-size: 20pt; font-weight: 700; color: #48AFF0;" id="stats_performance"></div>
														<div style="font-size: 11px; margin-top: 4px;">Efficiency</div>
													</div>
												</div>
												<div style="text-align: center; padding: 0 16px;">
													<div style="font-size: 12px; line-height: 1.5;">
														Actual vs theoretical maximum production speed
													</div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Quality Rate</h4>
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div style="position: relative; width: 140px; height: 140px; margin: 0 auto 16px;">
													<svg viewBox="0 0 140 140" style="transform: rotate(-90deg);">
														<circle cx="70" cy="70" r="60" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="12"/>
														<circle id="kpi_quality_circle" cx="70" cy="70" r="60" fill="none" stroke="#FFB366" stroke-width="12" 
															stroke-dasharray="377" stroke-dashoffset="19" stroke-linecap="round"/>
													</svg>
													<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
														<div style="font-size: 20pt; font-weight: 700; color: #FFB366;" id="stats_quality">95%</div>
														<div style="font-size: 11px; margin-top: 4px;">Good Units</div>
													</div>
												</div>
												<div style="text-align: center; padding: 0 16px;">
													<div style="font-size: 12px; line-height: 1.5;">
														Products meeting quality standards
													</div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--sm">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Overall Equipment Effectiveness (OEE)</h4>
												</div>												
											</div>
											<div class="mxcard_body mxcard_kpi">
												<div style="text-align: center;">
													
														<div style="font-size: 48px; font-weight: 700; color: #3DCC91; line-height: 1;" id="stats_oee">77%</div>
														<div style="margin-top: 12px;">
															<span class="mxcard_badge mxcard_badge--success">World Class Target: >85%</span>
														</div>
													
												</div>
											</div>
											
											<div class="mxcard_footer">
												A × P × Q = 
												<span style=""><span id="num_availability">0.92</span> × <span id="num_performance">0.88</span> × <span id="num_quality">0.95</span> = <span id="num_oee">0.77</span></span>
											</div>
										</div>
									</div>
									<!-- MTBF | MTTR Calculation Card -->
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Unplanned Downtime & MTTR</h4>
													<div class="mxcard_subtitle">Unplanned Downtime and Mean Time to Repair</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="AI Analysis & Recommendation" id="ai_unplanned_downtime">✨ Analysis & Recommendation</button>
												</div>										
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="unplannedDowntimeChart" style="min-height: 350px;"></div>
											</div>

											
										</div>

										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">MTBF & MTTR</h4>
													<div class="mxcard_subtitle">Mean Time Between Failures and Mean Time To Repair</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="AI Analysis & Recommendation" id="ai_mtbfmttr">✨ PM Recommendation</button>
												</div>											
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="mtbfmttrChart" style="min-height: 350px;"></div>
											</div>
										</div>
									</div>

									
									<!-- Productivity Analysis Section -->
									<h3 class="mxcard_section_header">
										Productivity Analysis
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<div>
													<h4 class="mxcard_title">Overall Productivity Performance</h4>
													<div class="mxcard_subtitle">Uptime vs downtime ratio and efficiency metrics</div>
												</div>
												<div class="mxcard_actions">
													<button class="mxcard_action_btn" title="Refresh">↻</button>
													<button class="mxcard_action_btn" title="Fullscreen">⛶</button>
													<button class="mxcard_action_btn" title="Export">⤓</button>
													<div class="mxcard_menu">
														<button class="mxcard_menu_icon">⋯</button>
													</div>
												</div>
											</div>
											<div class="mxcard_body">
												<div class="mxcard_chart" id="productivity_chart" style="min-height: 350px;"></div>
											</div>
											<div class="mxcard_footer">
												<span class="mxcard_badge mxcard_badge--success">Target Uptime: >90%</span>
											</div>
										</div>
									</div>
								
									<!-- Key Insights & Recommendations -->
									<h3 class="mxcard_section_header" style="display:none;">
										Insights & Recommendations
										<div class="mxcard_section_divider"></div>
									</h3>
									<div class="mxcard_grid" style="display:none">
										<div class="mxcard_card mxcard_card--full">
											<div class="mxcard_header">
												<h4 class="mxcard_title">💡 Productivity Improvement Opportunities</h4>
											</div>
											<div class="mxcard_body">
												<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
													<div style="padding: 16px; background: rgba(255, 115, 115, 0.1); border-radius: 6px; border-left: 3px solid #FF7373;">
														<div style="font-weight: 600; color: #FF7373; margin-bottom: 8px; font-size: 14px;">🔴 Critical: Equipment Failure</div>
														<div style=" font-size: 13px; line-height: 1.5; margin-bottom: 12px;">
															32 hours lost due to equipment failure this month. Consider implementing predictive maintenance.
														</div>
														<div style="font-size: 12px; ">
															Impact: -4% OEE | Priority: High
														</div>
													</div>
													<div style="padding: 16px; background: rgba(217, 130, 43, 0.1); border-radius: 6px; border-left: 3px solid #D9822B;">
														<div style="font-weight: 600; color: #FFB366; margin-bottom: 8px; font-size: 14px;">🟠 Maintenance Optimization</div>
														<div style=" font-size: 13px; line-height: 1.5; margin-bottom: 12px;">
															45 hours scheduled maintenance. Review maintenance windows to minimize production impact.
														</div>
														<div style="font-size: 12px; ">
															Impact: -5% OEE | Priority: Medium
														</div>
													</div>
													<div style="padding: 16px; background: rgba(45, 114, 210, 0.1); border-radius: 6px; border-left: 3px solid #2D72D2;">
														<div style="font-weight: 600; color: #48AFF0; margin-bottom: 8px; font-size: 14px;">🔵 Performance Gap</div>
														<div style=" font-size: 13px; line-height: 1.5; margin-bottom: 12px;">
															88% performance vs 100% theoretical. Identify bottlenecks and optimize cycle times.
														</div>
														<div style="font-size: 12px; ">
															Potential Gain: +8% OEE | Priority: Medium
														</div>
													</div>
													<div style="padding: 16px; background: rgba(15, 153, 96, 0.1); border-radius: 6px; border-left: 3px solid #0F9960;">
														<div style="font-weight: 600; color: #3DCC91; margin-bottom: 8px; font-size: 14px;">🟢 Quality Excellence</div>
														<div style=" font-size: 13px; line-height: 1.5; margin-bottom: 12px;">
															95% quality rate is strong. Continue current quality control processes and share best practices.
														</div>
														<div style="font-size: 12px; ">
															Status: Excellent | Action: Maintain
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- Trend Analysis Summary -->
									<div class="mxcard_grid" style="display:none">
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Month-over-Month Trends</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 12px;">
													<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
														<span style="font-size: 13px; ">Downtime Hours</span>
														<div style="display: flex; align-items: center; gap: 8px;">
															<span style="font-size: 14px; font-weight: 600; color: #FF7373;">+12%</span>
															<span style="color: #FF7373; font-size: 12px;">▲</span>
														</div>
													</div>
													<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
														<span style="font-size: 13px; ">OEE Score</span>
														<div style="display: flex; align-items: center; gap: 8px;">
															<span style="font-size: 14px; font-weight: 600; color: #FFB366;">-3%</span>
															<span style="color: #FFB366; font-size: 12px;">▼</span>
														</div>
													</div>
													<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
														<span style="font-size: 13px; ">Equipment Availability</span>
														<div style="display: flex; align-items: center; gap: 8px;">
															<span style="font-size: 14px; font-weight: 600; color: #3DCC91;">+2%</span>
															<span style="color: #3DCC91; font-size: 12px;">▲</span>
														</div>
													</div>
													<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0;">
														<span style="font-size: 13px; ">MTTR (Hours)</span>
														<div style="display: flex; align-items: center; gap: 8px;">
															<span style="font-size: 14px; font-weight: 600; color: #3DCC91;">-15%</span>
															<span style="color: #3DCC91; font-size: 12px;">▼</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="mxcard_card mxcard_card--lg">
											<div class="mxcard_header">
												<h4 class="mxcard_title">Target Achievement</h4>
											</div>
											<div class="mxcard_body">
												<div style="padding: 12px;">
													<div style="margin-bottom: 20px;">
														<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
															<span style="font-size: 13px; ">OEE Target (85%)</span>
															<span style="font-size: 14px; font-weight: 600; color: #FFB366;">77%</span>
														</div>
														<div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; position: relative;">
															<div style="height: 100%; width: 77%; background: #FFB366;"></div>
															<div style="position: absolute; left: 85%; top: -8px; height: 24px; width: 2px; background: #F5F8FA;"></div>
														</div>
														<div style="font-size: 11px; margin-top: 4px; text-align: right;">Target →</div>
													</div>
													<div style="margin-bottom: 20px;">
														<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
															<span style="font-size: 13px; ">Uptime Target (90%)</span>
															<span style="font-size: 14px; font-weight: 600; color: #3DCC91;">92%</span>
														</div>
														<div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; position: relative;">
															<div style="height: 100%; width: 92%; background: #3DCC91;"></div>
															<div style="position: absolute; left: 90%; top: -8px; height: 24px; width: 2px; background: #F5F8FA;"></div>
														</div>
														<div style="font-size: 11px; color: #3DCC91; margin-top: 4px; text-align: right;">✓ Target met</div>
													</div>
													<div>
														<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
															<span style="font-size: 13px; ">Downtime Target (<100h)</span>
															<span style="font-size: 14px; font-weight: 600; color: #3DCC91;">85h</span>
														</div>
														<div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; position: relative;">
															<div style="height: 100%; width: 85%; background: #3DCC91;"></div>
															<div style="position: absolute; left: 100%; top: -8px; height: 24px; width: 2px; background: #F5F8FA;"></div>
														</div>
														<div style="font-size: 11px; color: #3DCC91; margin-top: 4px; text-align: right;">✓ Below target</div>
													</div>
												</div>
											</div>
										</div>

										
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="internal_menu">
												<ul> 
													<li id="downtime_analysis" >Downtime Analysis</li>
													<li id="productivity_kpi">Productivity KPI</li>
												</ul> 
											</div>
										</div>
									</div>
									<div class="row eatfix"></div>
								</div>
							</div>
						</div>
					</div>
				
				</div>
			</div>
		</div>
	</body>
</html>