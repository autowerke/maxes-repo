<!DOCTYPE html>
<html>
<title></title>

<head>
    <?php  include("../../../applications/ui/ui.php");?>
        <link rel="stylesheet" href="style.css">
		
		<style>
			.dataTables_wrapper .dataTables_filter,
			.dataTables_wrapper .dt-buttons {
				display: inline-flex;
				align-items: center;
			}

			.dataTables_wrapper .dataTables_filter {
				margin-right: 15px; 
			}
			.dt-button {
				background: transparent !important; 
				color: #2C3E50 !important;  
				border: 1px solid #2C3E50 !important;
				border-radius: 6px;
				padding: 6px 12px;
				font-size: 13px;
			}

			.dt-button:hover {
				background-color: #fff !important;
			}
		</style>
</head>

<body id="page-top" xboset="pmcampaign" data-appkeynum="pmcampaignnum">
    <div id="wrapper">
        <div id='sidebarmenu'></div>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="eat-toolbars"></div>
                <div class="container-fluid">
                    <div class="maxes01">
                        <ul class="maxes02">
                            <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
                            <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Resource Smoothing</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_3">Material Workbench</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_4">Service Workbench</a></li>
                        </ul>
                        <div class="maxes04 shadow">
                            <div id="maxes02_1_1" class="tab maxes02_1">
                                <div id="maxes02_1_1" class="tab maxes02_1">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <div class='eat_apptitle'></div>
                                    </div>
                                    <div class="row eatfix">
                                        <div class="col-md-12">
                                            <div class="page-header-list">
                                                <div class="tablelist">
                                                    <table id="mxtable_pmcampaign" class="display"></table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="maxes02_1_2" class="maxes03">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                    <div class="internal_menu">
                                        <ul>
                                            <li id="campaign_health">Campaign Health</li>
                                            <li id="campaign_scheduler">Campaign Scheduler</li>
                                            <li id="resource_load">Resource Load</li>
                                            <li>
                                                <button class="mds-cart-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">
                                                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9-397.472L4.77 9.5H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                                                        />
                                                    </svg>
                                                    <span>Cart</span>
                                                    <span class="mds-cart-badge"></span>
                                                </button>
                                             </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-6">
                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>PM Campaign</td>
                                                    <td>
                                                        <input type="text" data-attribute="pmcampaignnum" data-input="required" data-key="value" data-type="upper">
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
                                </div>
								<div>
                                <div id="title_campaign_health" class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl4">Campaign Health Indicators</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="kpi-container">
                                            <!-- KPI Card 1:Total Managed Cost -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon blue">
                                                        <!-- Gunakan ikon SVG atau FontAwesome untuk hasil terbaik -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M4 1.25a.25.25 0 0 0-.25.25v2.5a.25.25 0 0 0 .5 0V1.5a.25.25 0 0 0-.25-.25zM7.25 12a.25.25 0 0 0-.25.25v2.5a.25.25 0 0 0 .5 0v-2.5a.25.25 0 0 0-.25-.25zM12 1.25a.25.25 0 0 0-.25.25v2.5a.25.25 0 0 0 .5 0V1.5a.25.25 0 0 0-.25-.25z" />
                                                            <path d="M2.5 7.25a.25.25 0 0 0-.25.25v5.5a.25.25 0 0 0 .5 0v-5.5a.25.25 0 0 0-.25-.25z" />
                                                            <path d="M8.25 5.5a.25.25 0 0 0-.25.25v8a.25.25 0 0 0 .5 0v-8a.25.25 0 0 0-.25-.25zm3.5 1.5a.25.25 0 0 0-.25.25v6.5a.25.25 0 0 0 .5 0v-6.5a.25.25 0 0 0-.25-.25z" />
                                                            <path d="M1 15.25a.25.25 0 0 0 .25.25h13.5a.25.25 0 0 0 .25-.25v-12a.25.25 0 0 0-.25-.25H1.25a.25.25 0 0 0-.25.25v12zM1.5 3.5a.5.5 0 0 1 .5-.5h12a.5.5 0 0 1 .5.5v11.5a.5.5 0 0 1-.5.5h-12a.5.5 0 0 1-.5-.5V3.5z" />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Total Managed Cost</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-total-cost">- <span class="unit"></span></div>
                                                <div class="kpi-comparison">For <strong><span id="kpi-total-cost-wo-count"></span></strong> Work Orders</div>
                                            </div>
                                            <!-- KPI Card 2:Total Required Hours -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon teal">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z" />
                                                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z" />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Total Required Hours</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-total-hours">864 <span class="unit">Hrs</span></div>
                                                <div class="kpi-comparison">From <strong><span id="kpi-total-hours-jp-count">18</span></strong> unique Job Plans</div>
                                            </div>
                                            <!-- KPI Card 3:Resource Utilization -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon orange">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                                            <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z" />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Resource Utilization</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-utilization-rate"><span class="unit">%</span></div>
                                                <div class="kpi-comparison"><span class="change positive" id="kpi-utilization-change">+5%</span> vs previous period</div>
                                            </div>
                                            <!-- KPI Card 4:Overloaded Weeks -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon red">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Overloaded Weeks</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-overloaded-weeks">3<span class="unit"> Weeks</span></div>
                                                <div class="kpi-comparison"><span id="kpi-overloaded-teams">2</span> Teams over capacity</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								</div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl4">
                                                    Import Work Orders
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-3">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Start Date</td>
                                                <td>
                                                    <input type="text" data-attribute="startdate" data-input="required" data-type="date">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>End Date</td>
                                                <td>
                                                    <input type="text" data-attribute="enddate" data-input="required" data-type="date">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-3">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Status</td>
                                                <td>
                                                    <input type="text" data-attribute="status" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Status Date</td>
                                                <td>
                                                    <input type="text" data-attribute="statusdate" data-input="readonly">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                    </div>
                                </div>
                                <div id="title_campaign_scheduler" class="workbench-container">
                                    <div class="workbench-panel" id="wo-list-panel">
                                        <div class="panel-header">
                                            <h2 class="panel-title">Work Order Snapshot</h2>
                                            <p class="panel-subtitle">Select a task to view on the timeline</p>
                                        </div>
                                        <div class="panel-header">
                                            <div class="addrow" id="importWOs"></div>
                                            <div class="addrow" id="addCorrenctiveWos"></div>
                                            <div class="addrow" id="addPMsWO"></div>
                                        </div>
                                        <div class="tablelist">
                                            <table id="mxtable_pmcampaignline_child" class="display" data-child="pmcampaignline"></table>
                                        </div>
                                    </div>
                                    <div class="workbench-panel" id="gantt-chart-panel">
                                        <div class="panel-header">
                                               <table width="100%">
                                                <tr>
                                                    <td>
                                                         <h2 class="panel-title">Campaign Scheduler</h2>
                                                        <span class="panel-subtitle">
                                                            Resource smoothing within a campaign applies only to work orders assigned to the same team.
                                                        </span>

                                                    </td>
                                                    <td> 
                                                        <div id="pmcampaign_ai_recommendation" style="float:right; position:relative; height:59px;">
                                                        </div>
                                                        
                                                    </td>
                                                </tr>
                                               </table>
                                        </div>
                                       
                                        <div class="zoom-controls">
                                            <button class="zoom-btn" data-view="Quarter Day">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                                                    <path d="M12 6v6l4 2" />
                                                </svg>
                                                Quarter
                                            </button>
                                            <button class="zoom-btn" data-view="Half Day">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20V4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z" />
                                                </svg>
                                                Half Day
                                            </button>
                                            <button class="zoom-btn" data-view="Day">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.89-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z" />
                                                </svg>
                                                Day
                                            </button>
                                            <button class="zoom-btn" data-view="Week">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.89-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 16H5V8h14v11z" />
                                                    <path d="M7 10h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z" />
                                                </svg>
                                                Week
                                            </button>
                                            <button class="zoom-btn" data-view="Month">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.89-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 16H5V8h14v11z" />
                                                    <rect x="7" y="10" width="2" height="2" />
                                                    <rect x="11" y="10" width="2" height="2" />
                                                    <rect x="15" y="10" width="2" height="2" />
                                                    <rect x="7" y="14" width="2" height="2" />
                                                    <rect x="11" y="14" width="2" height="2" />
                                                    <rect x="15" y="14" width="2" height="2" />
                                                </svg>
                                                Month
                                            </button>
                                            <button class="zoom-btn" data-view="Year">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.89-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.11-.9-2-2-2zm0 16H5V8h14v11z" />
                                                    <path d="M9 10v1h2v4h1v-5zm4 0v5h1v-2h1.5v-1H15v-1h2v-1z" />
                                                </svg>
                                                Year
                                            </button>
                                            <button class="zoom-btn action-btn" id="setallprogress">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                                </svg>
                                                Set All Progress
                                            </button>
                                            <button class="zoom-btn action-btn" id="setallduration">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" />
                                                    <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
                                                </svg>
                                                Set All Duration
                                            </button>
                                            <button class="zoom-btn action-btn" id="setallpriority">
                                                <svg viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                Set All Priority
                                            </button>
                                        </div>
                                        <div id="gantt-chart"></div>
                                    </div>
                                </div>
                                <div id="title_resource_load" class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl4">Resource Load Histogram - Weekly Capacity Hours & Team Burnout</td>
                                            </tr>
                                        </table>
                                        <div class="row eatfix">
                                            <div class="col-md-6">
                                                <div id="title_simulation" class="row eatfix">
                                                    <table width=100%>
                                                        <tr>
                                                            <td class="maxes_sct_tbl4">Before</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <p class="panel-subtitle">Initial plan showing periods of resource overload</p>
                                                <table class="tbldata">
                                                    <tr>
                                                        <td>Play with WO Dummies
                                                            <input type="number" data-attribute="wocount" data-childfld="child">
                                                            <button class='general-button' id="btn_simulate"><i class='fa fa-play'></i>Play</button>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <div id="title_configchanges" class="row eatfix">
                                                    <table width=100%>
                                                        <tr>
                                                            <td class="maxes_sct_tbl4">After</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <p class="panel-subtitle">Leveled plan with smoothed resource utilization</p>
                                                <div class="addrow" id="savepresets"></div>
                                                <div class="addrow" id="loadpresets"></div>
                                                <div class="addrow" id="deletepresets"></div>
                                                <div class="addrow" id="resetResSmoothing"></div>
                                                <div class="addrow" id="applyResSmoothing"></div>
                                            </div>
                                        </div>
                                        <div class="histogram-container">
                                            <div class="dashboard-grid">
                                                <!-- Left Panel-->
                                                <div class="left-panel">
                                                    <div class="panel-header">
                                                        <h2 class="panel-title"></h2>
                                                    </div>
                                                    <div id="histogram-chart-before"></div>
                                                </div>
                                                <!-- Right Panel -->
                                                <div class="right-panel">
                                                    <div id="histogram-chart"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								
							
							</div>
                            <div id="maxes02_1_3" class="maxes03">
                                <!-- sinibang -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                    <div class="internal_menu">
                                        <ul>
                                            <li>
                                                <button class="mds-cart-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">
                                                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9-397.472L4.77 9.5H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                                                        />
                                                    </svg>
                                                    <span>Cart</span>
                                                    <span class="mds-cart-badge"></span>
                                                </button>
                                             </li>
                                        </ul>
                                    </div>

                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-6">
                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>PM Campaign</td>
                                                    <td>
                                                        <input type="text" data-attribute="pmcampaignnum" data-input="required" data-key="value" data-type="upper">
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
                                    <div class="col-md-3">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Start Date</td>
                                                <td>
                                                    <input type="text" data-attribute="startdate" data-input="required" data-type="date">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>End Date</td>
                                                <td>
                                                    <input type="text" data-attribute="enddate" data-input="required" data-type="date">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-3">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Status</td>
                                                <td>
                                                    <input type="text" data-attribute="status" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Status Date</td>
                                                <td>
                                                    <input type="text" data-attribute="statusdate" data-input="readonly">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>


                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl4">Material Demand Summary</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="kpi-container">
                                            <!-- KPI Card 1:Total Material Cost -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon blue">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <!-- Money/Currency symbol with materials -->
                                                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Total Material Cost</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-total-material-cost"> 2.85 Billion IDR</div>
                                            </div>
                                            <!-- KPI Card 2:Total Shortage Items -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon teal">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <!-- Warning/Alert triangle with exclamation -->
                                                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Total Shortage Items</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-total-shortage-item">3 Items</div>
                                            </div>
                                            <!-- KPI Card 3: Highest Risk Item -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon orange">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <!-- Shield with X mark for high risk -->
                                                            <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"
                                                            />
                                                            <path d="M6.564 5.146a.5.5 0 0 1 .708 0L8 5.793l.728-.647a.5.5 0 1 1 .664.748L8.664 6.5l.728.646a.5.5 0 0 1-.664.748L8 7.207l-.728.647a.5.5 0 1 1-.664-.748L7.336 6.5l-.728-.646a.5.5 0 0 1-.044-.708z" />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Highest Risk Item</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-highest-risk-item">SEAL-C</div>
                                                <div class="kpi-comparison">Risk Score: <span class="change positive" id="kpi-risk-score">95</span> </div>
                                            </div>
                                            <!-- KPI Card 4: Potential Internal Savings -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon red">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <!-- Piggy bank for savings -->
                                                            <path d="M5 6.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0zm1.138-1.496A6.613 6.613 0 0 1 7.964 4.5c.666 0 1.303.097 1.893.273a.5.5 0 0 0 .286-.958A7.602 7.602 0 0 0 7.964 3.5c-.734 0-1.441.103-2.102.292a.5.5 0 1 0 .276.962z" />
                                                            <path fill-rule="evenodd" d="M7.964 1.527c-2.977 0-5.571 1.704-6.32 4.125h-.55A1 1 0 0 0 .11 6.824l.254 1.46a1.5 1.5 0 0 0 1.478 1.243h.263c.3.513.688.978 1.145 1.382l-.729 2.477a.5.5 0 0 0 .48.641h2a.5.5 0 0 0 .471-.332L5.94 11.4c.5.08 1.016.121 1.537.121.52 0 1.035-.041 1.535-.121l.468 2.195a.5.5 0 0 0 .471.332h2a.5.5 0 0 0 .48-.641l-.728-2.477c.457-.404.845-.869 1.145-1.382h.263a1.5 1.5 0 0 0 1.478-1.243l.254-1.46a1 1 0 0 0-.983-1.172h-.55c-.749-2.421-3.343-4.125-6.32-4.125zM2.78 3.116c.6-1.8 2.6-3.089 5.184-3.089 2.585 0 4.584 1.289 5.184 3.089.451 1.356-.238 2.884-1.707 3.384-.25.085-.518.155-.793.207a6.986 6.986 0 0 1-2.684 0 7.043 7.043 0 0 1-.793-.207c-1.469-.5-2.158-2.028-1.707-3.384z"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Potential Internal Savings</div>
                                                </div>
                                                <div class="kpi-value" id="potential-internal-savings">150 Million IDR</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="maxes_sct_tbl4">
                                            <div>
                                                Material Charges  & Readiness Summary
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <table class="tbldata">
                                        <tr>
                                            <td>Default Vendor Code</td>
                                            <td>
                                                <input type="text" data-attribute="defvendorcode" data-lookup="vendor">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Default Customer Code</td>
                                            <td>
                                                <input type="text" data-attribute="defcustomercode" data-lookup="customer">
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="mds-container">
                                            <div class="mds-table-wrapper">
                                                <table id="materialSummaryTable" class="display" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Item</th>
                                                            <th>Description</th>
                                                            <th>Total Need</th>
                                                            <th>Shortage</th>
                                                            <th>Earliest WO Date</th>
                                                            <th>Readiness Gap</th>
                                                            <th>Source Options</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Biarkan kosong, akan diisi oleh JavaScript -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="idsc-container" id="deepDiveContainer">
                                            <!-- Header Bagian -->
                                            <div class="idsc-header">
                                                <h4 class="idsc-title">Deep Dive: <span id="ddItemNum"></span> - <span id="ddItemDesc"></span></h4>
                                                <div class="idsc-shortage-info">
                                                    Shortage: <span id="ddShortageQty">8</span> units
                                                </div>
                                                <button class="idsc-close-button" id="btn_hideDeepDive">×</button>
                                            </div>
                                            <!-- Layout 2 Kolom -->
                                            <div class="idsc-grid">
                                                <!-- Kolom Kiri: Konteks -->
                                                <div class="idsc-panel">
                                                    <div class="idsc-panel-header">
                                                        <h6>Work Orders Requiring this Item</h6>
                                                    </div>
                                                    <div class="idsc-panel-body">
                                                        <div class="idsc-table-wrapper">
                                                            <table class="idsc-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>
                                                                            <input type="checkbox">
                                                                        </th>
                                                                        <th>WO#</th>
                                                                        <th>Required Date</th>
                                                                        <th>Qty</th>
                                                                        <th>Priority</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="ddWorkOrderTable">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Kolom Kanan: Solusi -->
                                                <div class="idsc-panel">
                                                    <div class="idsc-panel-header">
                                                        <h6>Sourcing & Solution Options</h6>
                                                        <!-- Navigasi Tab -->
                                                        <div class="idsc-tabs">
                                                            <button class="idsc-tab-button active">Internal</button>
                                                            <button class="idsc-tab-button">Vendors</button>
                                                        </div>
                                                    </div>
                                                    <div class="idsc-panel-body">
                                                        <!-- Konten Tab 1: Solusi Internal -->
                                                        <div id="tab-internal" class="idsc-tab-content active">
                                                            <p><strong>Harvest from Kits:</strong></p>
                                                            <div class="idsc-solution-item">

                                                                <button class="idsc-action-button-small">Initiate Disassembly</button>
                                                            </div>
                                                            <hr class="idsc-divider">
                                                            <p><strong>Available Alternates:</strong></p>
                                                            <div class="idsc-solution-item">

                                                                <button class="idsc-action-button-small">Swap in Selected WOs</button>
                                                            </div>
                                                        </div>
                                                        <!-- Konten Tab 2: Vendor -->
                                                        <div id="tab-vendors" class="idsc-tab-content" style="display: none;">
                                                            <div class="idsc-table-wrapper">
                                                                <table class="idsc-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Vendor</th>
                                                                            <th>Lead Time</th>
                                                                            <th>Est. Arrival</th>
                                                                            <th>Unit Cost</th>
                                                                            <th>Gap</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="ddVendorTable">

                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Panel Bawah: Simulasi -->
                                            <div class="idsc-simulation-panel">
                                                <div class="idsc-panel-header">
                                                    <h6>Impact Simulation</h6>
                                                </div>
                                                <div class="idsc-panel-body" id="ddSimulationPanel">
                                                    <p class="idsc-simulation-text">Select one or more Work Orders from the left to simulate postponement.</p>
                                                    <!-- Konten simulasi akan di-generate oleh JS saat WO dipilih -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>




                            <div id="maxes02_1_4" class="maxes03">
                                <!-- sinibang -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                    <div class="internal_menu">
                                        <ul>
                                            <li>
                                                <button class="mds-cart-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">
                                                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9-397.472L4.77 9.5H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                                                        />
                                                    </svg>
                                                    <span>Cart</span>
                                                    <span class="mds-cart-badge"></span>
                                                </button>
                                             </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-6">
                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>PM Campaign</td>
                                                    <td>
                                                        <input type="text" data-attribute="pmcampaignnum" data-input="required" data-key="value" data-type="upper">
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
                                    <div class="col-md-3">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Start Date</td>
                                                <td>
                                                    <input type="text" data-attribute="startdate" data-input="required" data-type="date">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>End Date</td>
                                                <td>
                                                    <input type="text" data-attribute="enddate" data-input="required" data-type="date">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-3">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Status</td>
                                                <td>
                                                    <input type="text" data-attribute="status" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Status Date</td>
                                                <td>
                                                    <input type="text" data-attribute="statusdate" data-input="readonly">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl4">Service Demand & Budget Summary</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="kpi-container">
                                            <!-- KPI Card 1: Total Service Cost -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon blue">

                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Total Service Cost</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-total-service-cost">_</div>
                                            </div>

                                            <!-- KPI Card 2: Pending Procurement -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon teal">
                                                        <!-- Ikon: Jam pasir atau kalender dengan tanda seru -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5V5.6z" />
                                                            <path d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64a.715.715 0 0 1 .012-.013l.354-.354.353.354a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.707l.354-.353a.849.849 0 0 0-.01-.012A5.992 5.992 0 0 0 8 2.5a6 6 0 1 0 0 12 6 6 0 0 0 0-12zM8 3a5 5 0 1 1 0 10A5 5 0 0 1 8 3z"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Pending Procurement Actions</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-pending-procurement">_</div>
												<div class="kpi-comparison">External Vendor Requirement</div>
                                            </div>

                                            <!-- KPI Card 3: Highest Procurement Risk -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon orange">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.777 11.777 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7.159 7.159 0 0 0 1.048-.625 11.775 11.775 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.541 1.541 0 0 0-1.044-1.263 62.467 62.467 0 0 0-2.887-.87C9.843.266 8.69 0 8 0zm0 5a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 5zm0 6a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1z"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Highest Risk</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-highest-risk-service">_</div>
                                                <div class="kpi-comparison"><span class="change negative" id="kpi-risk-value">_</span> </div>
                                            </div>

                                            <!-- KPI Card 4: Total Internal Hours -->
                                            <div class="kpi-card">
                                                <div class="kpi-header">
                                                    <div class="kpi-icon red">
                                                        <!-- Ikon: Orang/Tim -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                                            <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
                                                        </svg>
                                                    </div>
                                                    <div class="kpi-title">Total Internal Hours</div>
                                                </div>
                                                <div class="kpi-value" id="kpi-total-internal-hours">_</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- === SERVICE DEMAND SUMMARY === -->
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="maxes_sct_tbl4">
                                            <div>
                                                Service Charges Summary
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                 <div class="col-md-3">
                                    <table class="tbldata">
                                        <tr>
                                            <td>Default Vendor Code</td>
                                            <td>
                                                <input type="text" data-attribute="defvendorcode" data-lookup="vendor">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Default Customer Code</td>
                                            <td>
                                                <input type="text" data-attribute="defcustomercode" data-lookup="customer">
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="mds-container">
                                            <div class="mds-table-wrapper">
                                                <!-- ID Tabel diubah agar unik -->
                                                <table id="serviceSummaryTable" class="display" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <!-- Kolom-kolom disesuaikan untuk Jasa -->
                                                            <th style="text-align:left">Service #</th>
                                                            <th style="text-align:left">Description</th>
                                                            <th style="text-align:center">Total Need</th>
                                                            <th style="text-align:right">Total Cost</th>
                                                            <th>Earliest WO Date</th>
                                                            <th>Overall Risk (%)</th>
                                                            <th style="text-align:right">Recom. Total Cost</th>
                                                            <th style="text-align:center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
														
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END TAB : Service Workbench -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>

</html>