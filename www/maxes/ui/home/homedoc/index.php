<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SMT Executive Dashboard</title>
	<?php include ("../../../applications/ui/ui.php"); ?>

<body id="page-top" class="foundry-light-theme" xboset = "homedoc">
	<div id="wrapper">
		<!-- Sidebar Menu MAXES -->
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<!-- Main Container Dashboard -->
				<div class="container-fluid foundry-container px-4 py-3">
					<!-- HEADER / TOP CONTROLS -->
					<div class="row mb-3 align-items-center">
						<div class="col-md-6">
							<h4 class="foundry-title mb-0">PLN Pusharlis Enterprise Asset Management Dashboard</h4>
							<p class="foundry-subtitle text-muted mb-0"> IMS Document Intelligence & Knowledge Hub</p>
						</div>
						
					</div>
					<!-- ROW 1: KPI METRIC CARDS (DENSE GRID) -->
					<!-- ========================================================================== -->
					<!-- ROW 1: KNOWLEDGE GRAPH & DOCUMENT RECORDS STACKED BAR SIDE-BY-SIDE          -->
					<!-- ========================================================================== -->
					<div class="row eatfix">
						<div class="col-md-12">
							<!-- ROW 1: KPI METRIC CARDS (DENSE GRID) -->
							<div class="row foundry-grid-kpi mb-3">
								<!-- 1. Active Procedures -->
								<div class="col-xl-3 col-md-6 mb-2">
									<div class="card foundry-kpi-card h-100 py-2" data-kpi="procedures">
										<div class="card-body">
											<div class="row no-gutters align-items-center">
												<div class="col mr-2">
													<div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">Active Procedures (SOP)</div>
													<!-- ID DI SINI -->
													<div class="h5 mb-0 font-weight-bold text-dark" id="kpiProcCount">0</div>
												</div>
												<div class="col-auto"> <i class="fas fa-file-alt fa-2x text-secondary opacity-50"></i> </div>
											</div>
										</div>
									</div>
								</div>
								<!-- 2. Work Instructions -->
								<div class="col-xl-3 col-md-6 mb-2">
									<div class="card foundry-kpi-card h-100 py-2" data-kpi="workinstr">
										<div class="card-body">
											<div class="row no-gutters align-items-center">
												<div class="col mr-2">
													<div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">Work Instructions (IK)</div>
													<!-- ID DI SINI -->
													<div class="h5 mb-0 font-weight-bold text-dark" id="kpiWiCount">0</div>
												</div>
												<div class="col-auto"> <i class="fas fa-tasks fa-2x text-secondary opacity-50"></i> </div>
											</div>
										</div>
									</div>
								</div>
								<!-- 3. Active Team Decrees -->
								<div class="col-xl-3 col-md-6 mb-2">
									<div class="card foundry-kpi-card h-100 py-2" data-kpi="teamdecree">
										<div class="card-body">
											<div class="row no-gutters align-items-center">
												<div class="col mr-2">
													<div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">Active Team Decrees (SK)</div>
													<!-- ID DI SINI -->
													<div class="h5 mb-0 font-weight-bold text-dark" id="kpiSkCount">0</div>
												</div>
												<div class="col-auto"> <i class="fas fa-users fa-2x text-secondary opacity-50"></i> </div>
											</div>
										</div>
									</div>
								</div>
								<!-- 4. Open Change Requests -->
								<div class="col-xl-3 col-md-6 mb-2">
									<div class="card foundry-kpi-card h-100 py-2" data-kpi="docticket">
										<div class="card-body">
											<div class="row no-gutters align-items-center">
												<div class="col mr-2">
													<div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">Open Change Requests</div>
													<!-- ID DI SINI -->
													<div class="h5 mb-0 font-weight-bold text-danger" id="kpiTicketCount">0</div>
												</div>
												<div class="col-auto"> <i class="fas fa-ticket-alt fa-2x text-danger opacity-50"></i> </div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row eatfix">
						<div class="col-md-12">
							<div class="row mb-3">
								<!-- Left: Knowledge Graph ECharts -->
								<div class="col-xl-6 col-lg-6 mb-3 mb-lg-0">
									<div class="card foundry-card h-100">
										<div class="card-header foundry-card-header d-flex flex-row align-items-center justify-content-between py-2">
											<div class="d-flex align-items-center">
												<h6 class="m-0 font-weight-bold text-dark mr-3">Document Knowledge Graph & Lineage</h6>
												<!-- Dropdown Selector Manual Terintegrasi -->
												<div class="input-group input-group-sm" style="width: 250px;">
													<div class="input-group-prepend"> <span class="input-group-text bg-light text-muted" style="font-size: 11px;">Manual</span> </div>
													<select id="globalManualFilter" class="form-control form-control-sm" style="font-size: 11px;">
													</select>
												</div>
											</div>
											<div class="dropdown no-arrow">
												<button class="btn btn-sm btn-link text-muted dropdown-toggle" type="button" data-toggle="dropdown"> <i class="fas fa-ellipsis-v fa-sm fa-fw"></i> </button>
												<div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"> <a class="dropdown-item" href="#" id="toggleGraphLayout">Toggle Graph Physics</a> <a class="dropdown-item" href="#" id="resetGraphZoom">Reset Zoom</a> </div>
											</div>
										</div>
										<div class="card-body p-2">
											<div id="knowledgeGraphChart" style="width: 100%; height: 380px;"></div>
										</div>
									</div>
								</div>
								<!-- Right: Document Records by Document Type (Stacked Bar Chart + KPI Summary) -->
								<div class="col-xl-6 col-lg-6">
									<div class="card foundry-card h-100">
										<div class="card-header foundry-card-header d-flex flex-row align-items-center justify-content-between py-2">
											<div class="d-flex align-items-center">
												<div class="foundry-header-icon mr-2 text-primary"> <i class="fas fa-layer-group fa-sm"></i> </div>
												<div>
													<h6 class="m-0 font-weight-bold text-dark">Document Records by Document Type</h6> </div>
											</div> </div>
										<div class="card-body px-3 py-2">
											<!-- Mini Metric Summary row inside card --> 
											<div class="row mb-2">
												<div class="col-4">
													<div class="p-2 border rounded bg-light text-center"> <span class="text-uppercase text-muted font-weight-bold" style="font-size: 9px;">Total</span>
														<div class="h6 mb-0 font-weight-bold text-dark" id="summaryTotalRecords">3,190</div>
													</div>
												</div>
												<div class="col-4">
													<div class="p-2 border rounded bg-green-light-soft text-center"> <span class="text-uppercase text-success font-weight-bold" style="font-size: 9px;">Active</span>
														<div class="h6 mb-0 font-weight-bold text-success" id="summaryActiveRecords">2,349</div>
													</div>
												</div>
												<div class="col-4">
													<div class="p-2 border rounded bg-yellow-light-soft text-center"> <span class="text-uppercase text-warning font-weight-bold text-dark-warning" style="font-size: 9px;">In Progress</span>
														<div class="h6 mb-0 font-weight-bold text-dark" id="summaryInProgressRecords">492</div>
													</div>
												</div>
											</div>
											<!-- ECharts Stacked Bar Chart Container -->
											<div id="documentTypeStackedBarChart" style="width: 100%; height: 230px;"></div>
											<!-- Compact Legend -->
											<div class="d-flex justify-content-around align-items-center text-center mt-1 pt-1 border-top border-light" style="font-size: 10px;">
												<div><span class="rounded-circle mr-1" style="width:8px; height:8px; background-color:#36b37e; display:inline-block;">%</span> Active</div>
												<div><span class="rounded-circle mr-1" style="width:8px; height:8px; background-color:#0065ff; display:inline-block;"></span> Inactive</div>
												<div><span class="rounded-circle mr-1" style="width:8px; height:8px; background-color:#ffab00; display:inline-block;"></span> In Progress</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- ========================================================================== -->
					<!-- ROW 2: CATEGORY & STATUS DISTRIBUTION SIDE-BY-SIDE WITH DATATABLE           -->
					<!-- ========================================================================== -->
					<div class="row eatfix">
						<div class="col-md-12">
							<div class="row mb-3">
								<!-- Left: Category & Status Distribution Charts -->
								<div class="col-xl-4 col-lg-5 mb-3 mb-lg-0">
									<div class="card foundry-card h-100">
										<div class="card-header foundry-card-header py-2">
											<h6 class="m-0 font-weight-bold text-dark">Category & Status Distribution</h6> </div>
										<div class="card-body p-2 d-flex flex-column justify-content-between">
											<div style="height: 165px; width: 100%;">
												<div id="categoryBarChart" style="width: 100%; height: 100%;"></div>
											</div>
											<hr class="my-1 border-light">
											<div style="height: 165px; width: 100%;">
												<div id="statusDonutChart" style="width: 100%; height: 100%;"></div>
											</div>
										</div>
									</div>
								</div>
								<!-- Right: Interactive DataTable Explorer -->
								<div class="col-xl-8 col-lg-7">
									<div class="card foundry-card h-100">
										<div class="card-header foundry-card-header d-flex flex-row align-items-center justify-content-between py-2">
											<div>
												<h6 class="m-0 font-weight-bold text-dark d-inline-block mr-2">Filtered Documents Explorer</h6> <span id="currentTableContext" class="badge badge-secondary font-weight-normal">Showing: All Records</span> </div>
											<div class="foundry-table-actions">
												<!-- Datatables buttons injected here -->
											</div>
										</div>
										<div class="card-body p-2">
											<div class="table-responsive">
												<table id="foundryDataTable" class="table table-sm table-hover table-bordered display" width="100%"></table>
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