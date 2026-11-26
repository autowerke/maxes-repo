<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
		<link href="style.css" rel="stylesheet">
	</head>
	<body id="page-top" xboset="pm"  data-appkeynum = "pmnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Dynamic Work Scoping & Dispatching Resource</a></li>
								<li  class="maxes02_2"><a href="#maxes02_1_3">Material Solution Workbench</a></li>
							</ul>
							<div class="maxes04 shadow
							">
								<div id="maxes02_1_1" class="tab maxes02_1">
									<div id="maxes02_1_1" class="tab maxes02_1">
										<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
											<div class='eat_apptitle'></div> 
										</div>
										<div class="row eatfix">
											<div class="col-md-12">
												<div class="page-header-list">
													<div class="tablelist">
														<table id="mxtable_pm" class="display"></table>
													</div>
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
														<td>PM
														</td>
														<td><input type="text" data-attribute="pmnum" data-input="required" data-key="value" data-type="upper">
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
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Organization
														</td>
														<td><input type="text" data-attribute="orgcode" data-input="readonly" data-type="upper">
														</td>
													</tr>
													<tr>
														<td>Site 
														</td>
														<td><input type="text" data-attribute="sitecode" data-input="readonly" data-type="upper">
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
													<td class="maxes_sct_tbl4">Asset Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class ="row eatfix">
										<div class= "col-md-6">
											<table class="tbldata">
												<tr>
													<td>Asset</td>
													<td>
														<input type="text" data-attribute="assetnum"  data-lookup="assets" data-lookupaction='y'>
													</td>
												</tr>
												<tr>
													<td>Location</td>
													<td>
														<input type="text" data-attribute="location"  data-lookup="locations" data-lookupaction='y'>
													</td>
												</tr>
												<tr>
													<td>Priority</td>
													<td>
														<input type="text" data-attribute="priority" data-lookup="wopriority" >
													</td>
												</tr>
												<tr>
													<td>GL Account</td>
													<td>
														<input type="text" data-attribute="glaccount" data-lookup="chartofaccount" >
													</td>
												</tr>
												
											</table>
										</div>
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Team</td>
													<td>
														<input type="text" data-attribute="teamnum" data-lookup="team" data-lookupaction="y">
													</td>
												</tr>
												<tr>
													<td>Leader</td>
													<td>
														<input type="text" data-attribute="leader" data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees">
													</td>
												</tr>
												<tr>
													<td>Leader Name </td>
													<td>
														<input type="text" data-attribute="leader.fullname" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Automated Problem Resolution & Logistics</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Work Order Generation Logic</td>
													<td>
														<select data-attribute="generationlogic">
															<option value="AND">Generate WO for <b>ALL</b> due frequencies (AND)</option>
															<option value="OR"> Generate WO for the <b>FIRST</b> due frequency (OR)</option>
														</select>
													</td>
												</tr>
												
											</table>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="subtab">
											<ul class="maxes02">
												<li class="maxes02_1"><a href="#pm_time_based">Time Based</a></li>
												<li class="maxes02_2"><a href="#pm_condition_based">Condition Based</a></li>
												<li class="maxes02_2"><a href="#pm_frequency_based">Frequency Based</a></li>
												<li class="maxes02_2"><a href="#pm_health_based">Health Based</a></li>
											</ul>
										</div>
										<div class="maxes04sub">
											<!-- Time Based PM -->
											<div id="pm_time_based" class="maxes02_1">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Time Frequency Maintenance
													<div class="internal_menu">
														<ul>
															<li id="time_interval" >Time Interval</li>
															<li id="frequency_rules">Frequency Rules</li>
															<li id="workorder_forecasting">Work Order Forecasting</li>
															<li id="jobplan_forecasting">Job Plans Forecasting</li>
														</ul>	
													</div>
												</div>
												
												<div id="title_time_interval" class="row eatfix">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td class="maxes_sct_tbl4">Time Interval</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
													<table class="tbldata">
														<tr>
															<td class="maxes_sct_tbl3">Parameter</td>
														</tr>
													</table>
													</div>
													<div class="col-md-12">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tbldata">
																
																<tr>
																	<td>Start Date</td>
																	<td>
																		<input type="text" data-attribute="startdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Last Started Date</td>
																	<td>
																		<input type="text" data-attribute="laststarteddate" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Last Completed Date</td>
																	<td>
																		<input type="text" data-attribute="lastcompleteddate" data-input="readonly">
																	</td>
																</tr>
																
															</table>
														</div>
														<div class="col-md-4">
															<table class="tbldata">
																<tr>
																	<td>Inteval Value</td>
																	<td>
																		<input type="text" data-attribute="intervalvalue">
																	</td>
																</tr>
																<tr>
																	<td>Interval Unit</td>
																	<td>
																		<input type="text" data-attribute="intervalunit"  data-lookup="durationunit">
																	</td>
																</tr>
															</table>
														</div>
														
														<div class="col-md-4">
															<table class="tbldata">
																<tr>
																	<td>Duration Value</td>
																	<td>
																		<input type="text" data-attribute="durationvalue">
																	</td>
																</tr>
																<tr>
																	<td>Duration Unit</td>
																	<td>
																		<input type="text" data-attribute="durationunit" data-lookup="durationunit">
																	</td>
																</tr>
																<tr>
																	<td>Notify me in</td>
																	<td>
																		<input type="number" data-attribute="alertleaddays" style="width:70px !important"> 
																		<small> days Before WO due date </small>
																	</td>
																</tr>
															</table>
														
														</div>
													</div>
													</div>
													
													<div id="title_frequency_rules" class="col-md-12">
														<table class="tbldata">
															<tr>
																<td class="maxes_sct_tbl3">Frequency Rules</td>
															</tr>
														</table>
														<div class="row eatfix">
															<div class="col-md-12">
																<div class="row eatfix">
																	<table class="tbldata">
																		<tr>
																			<td>
																				<div class="addrow" id="addwt"></div>
																			</td>
																		</tr>
																	</table>
																</div>
																<div class="row eatfix">
																	<div class="col-md-12">
																		<div class="tablelist">
																			<table id="mxtable_pmjprule_child" class="display" data-child="pmjprule"></table>
																			<div id="mxtable_pmjprule_child_detail" class="mxtable_detail">
																				<div class="row eatfix">
																					<div class="col-md-6">
																						<table class="tableDetailRow">
																							<tr>
																								<td>Job Plan#
																								</td>
																								<td>
																									<input type="text" data-childfld="child" data-attribute="wtnum" data-input="readonly" >
																								</td>
																							</tr>
																							<tr>
																								<td>Rule Type</td>
																								<td>
																									<select data-childfld="child" data-attribute="ruletype">
																										<option value="FREQUENCY">Frequency</option>
																										<option value="SPECIFIC_DATE">Specific Date</option>
																									</select>
																								</td>
																							</tr>
																							<tr>
																								<td>Frequency Value
																								</td>
																								<td>
																									<input type="number" data-childfld="child" data-attribute="frequencyvalue" min = 1 >
																								</td>
																							</tr>
																							<tr>
																								<td>Specific Date
																								</td>
																								<td>
																									<input type="text" data-childfld="child" data-attribute="specificdate" data-type="date">
																								</td>
																							</tr>
																						</table>
																					</div>
																					<div class="col-md-6">
																						<div class="maxes_sct_tbl3">
																							JP Sequences
																						</div>
																						<table class="tableDetailRow">
																							<tr>
																								<td>Sequence
																								</td>
																								<td>
																									<input type="number" data-childfld="child" data-attribute="sequence" min = 1 >
																								</td>
																							</tr>
																							<tr>
																								<td>Behaviour
																								</td>
																								<td>
																									<select data-childfld="child" data-attribute="behaviour">
																										<option value="STACKED" selected>Stacked</option>
																										<option value="OVERRIDE">Override</option>
																									</select>
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
												<div class="row eatfix">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td class="maxes_sct_tbl3">Scheduling Rules
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>If PM falls on a Holiday:</td>
																<td>
																	<!-- Dropdown ini akan menyimpan nilainya ke kolom pm.holidayaction -->
																	<select data-attribute="holidayaction">
																		<option value="AFTER">Reschedule AFTER Holiday (Default)</option>
																		<option value="BEFORE">Reschedule BEFORE Holiday</option>
																		<option value="IGNORE">Ignore Holiday (Perform on Holiday)</option>
																	</select>
																</td>
															</tr>
														</table>
													</div>
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>Working Days:</td>
																<td>
																	<!-- Checkbox ini akan menyimpan nilainya ke kolom pm.allowsaturday & pm.allowsunday -->
																	<label style="margin-right: 15px;">
																	<input type="checkbox" data-attribute="allowsaturday"> Allow Saturday
																	</label>
																	<label>
																	<input type="checkbox" data-attribute="allowsunday"> Allow Sunday
																	</label>
																</td>
															</tr>
															<tr>
																<td>Scheduling Method:</td>
																<td>
																	<select data-attribute="pmtype">
																		<option value="FIXED">Fixed Interval (Based on Start Date)</option>
																		<option value="FLOATING_START">Floating Interval (Based on Last WO Start Date)</option>
																		<option value="FLOATING_COMP">Floating Interval (Based on Last WO Completion Date)</option>
																	</select>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td id="title_workorder_forecasting" class="maxes_sct_tbl3">WO Forecasting
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>Owner</td>
																<td>
																	<input type="text" data-attribute="owner" data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees" >
																</td>
															</tr>
															<tr>
																<td>Owner Name</td>
																<td>
																	<input type="text" data-attribute="owner.fullname" data-input="readonly">
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>
																	<div class="addrow" id="generatepm"></div>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_pmwoline_child" class="display" data-child="pmwoline"></table>
															<div id="mxtable_pmwoline_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<div class="maxes_sct_tbl3">
																			Forecasted Planning
																		</div>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Line</td>
																				<td><input type="text" data-childfld="child" data-type="integer" data-input="readonly" data-key="value" data-attribute="pmwolinenum">
																				</td>
																			</tr>
																			<tr>
																				<td>Description
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="description">
																				</td>
																			</tr>
																			<tr>
																				<td>Target Start Date
																				</td>
																				<td><input type="text" data-childfld="child" data-type = "date" data-attribute="targstartdate">
																				</td>
																			</tr>
																			<tr>
																				<td>Target Completion Date
																				</td>
																				<td><input type="text" data-childfld="child" data-type = "date" data-attribute="targenddate">
																				</td>
																			</tr>
																			<tr>
																				<td>GL Account
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="glaccount" data-lookup="chartofaccount">
																				</td>
																			</tr>
																			<tr>
																				<td>Remarks
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="remarks">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<div class="maxes_sct_tbl3">
																			Resources
																		</div>
																		<table class="tableDetailRow">
																			<tr>
																				<td>Owner</td>
																				<td>
																					<input type="text" data-attribute="owner" data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees" >
																				</td>
																			</tr>
																			<tr>
																				<td>Owner Name</td>
																				<td>
																					<input type="text" data-attribute="owner.fullname" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Leader</td>
																				<td>
																					<input type="text" data-attribute="leader" data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees">
																				</td>
																			</tr>
																			<tr>
																				<td>Leader Name </td>
																				<td>
																					<input type="text" data-attribute="leader.fullname" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Team
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team">
																				</td>
																			</tr>
																			<tr>
																				<td>Resource Remarks 
																				</td>
																				<td><input type="text" data-childfld="child" data-attribute="remarks2">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																	
																		<div class="maxes_sct_tbl3">
																			PM Campaign
																		</div>
																	
																		<table class="tableDetailRow">
																			<tr>
																				<td>Is Schedulable?
																				</td>
																				<td>
																					<input type="checkbox" data-childfld="child" data-attribute="isschedulable">
																				</td>
																			</tr>
																			<tr>
																				<td>Earliest Start
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="earlieststart" data-type="date">
																				</td>
																			</tr>
																			<tr>
																				<td>Latest Finish
																				</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="latestfinish" data-type="date">
																				</td>
																			</tr>
																		</table>
																	</div>
																					
																</div>
															</div>
														</div>
													</div>
												</div>
												<div id="title_jobplan_forecasting" class="row eatfix">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td class="maxes_sct_tbl3">Job Plan Forecasting
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>
																	<div class="addrow" id="addadditionalJobplan"></div>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_pmjpline_child" class="display" data-child="pmjpline"></table>
														</div>
													</div>
												</div>
											</div>
											<!-- Condition Based PM -->
											<div id="pm_condition_based" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Condition Based Meter Reading
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td class="maxes_sct_tbl4">Rules for Condition Monitoring</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>
																	<div class="addrow" id="addpmmeterrule"></div>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_pmmeterrule_child" class="display" data-child="pmmeterrule" data-pk = "pmmeterruleid" ></table>
															<div id="mxtable_pmmeterrule_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Meter Name</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="meternum" data-input="required" data-key="value">
																				</td>
																			</tr>
																			<tr>
																				<td>Auto-Reserve Materials?</td>
																				<td>
																					<input type="checkbox" data-childfld="child" data-attribute="autoreserve">
																					<br><small class="form-text text-muted">Attempt to reserve required materials when WO is created.</small>
																				</td>
																			</tr>
																			
																			<tr>
																				<td>Trigger Condition</td>
																				<td>
																					<div style="display: flex; align-items: center; gap: 10px;">
																						<select data-childfld="child" data-attribute="operator" style="width: 150px;">
																							<option value=">">More Than</option>
																							<option value=">=">More Than / Equals</option>
																							<option value="<">Less Than</option>
																							<option value="<=">Less Than / Equals</option>
																							<option value="==">Equals</option>
																							<option value="IN_RANGE">In Range</option>
																							<option value="OUT_RANGE">Out Range</option>
																						</select>
																					</div>
																				</td>
																			</tr>
																			
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Trigger Point</td>
																				<td><input type="text" data-childfld="child" data-attribute="triggerpoint" data-type="decimal" placeholder="e.g., 500" data-input="required"></td>
																			</tr>
																			<tr>
																				<td>Extended Trigger Point</td>
																				<td><input type="text" data-childfld="child" data-attribute="triggerpoint2" data-type="decimal" placeholder="e.g., 700" >
																					<br><small class="form-text text-muted">When specified, the system monitors the range between the trigger point and the extended trigger point.</small>
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Predict Next Meter Reading?</td>
																				<td><input type="checkbox" data-childfld="child" data-attribute="predictive">
																				</td>
																			</tr>
																			<tr>
																				<td>Predicted Next Due Date</td>
																				<td><input type="text" data-childfld="child" data-attribute="predictedduedate" data-type="date" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Average Rate/Day</td>
																				<td><input type="text" data-childfld="child" data-attribute="avgreliabilityrate" data-type="decimal" data-input="readonly"></td>
																			</tr>
																			
																			<tr>
																				<td>Last WO</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="lastwonum" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Last WO Date</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="lastwodate" data-input="readonly" data-type="date">
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
																<td class="maxes_sct_tbl4">Assigned Job Plans For Condition Based Monitoring</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>
																	<div class="addrow" id="addpmmeterrulejp"></div>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_pmmeterrulejp_child" class="display" data-child="pmmeterrulejp" data-pk = "pmmeterruleid" data-link="mxtable_pmmeterrule_child"></table>
															<!-- PM Meter Rule CBM Job Plans !-->
															
															<div id="mxtable_pmmeterrulejp_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-6">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Meter ID</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="pmmeterruleid" data-input="readonly" data-key="value">
																				</td>
																			</tr>
																			<tr>
																				<td>Job Plan</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="wtnum" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Sequence</td>
																				<td>
																					<input type="number" data-childfld="child" data-attribute="sequence" min="1">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Team</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team">
																				</td>
																			</tr>
																			<tr>
																				<td>Leader</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="leader" data-lookup="employees">
																				</td>
																			</tr>
																			<tr>
																				<td>GL Account</td>
																				<td>
																					<input type="number" data-childfld="child" data-attribute="glaccount" data-lookup="chartofaccount">
																				</td>
																			</tr>
																		</table>
																	</div>
																</div>	
															</div>
															
															
															<!-- END !-->
														</div>
													</div>
												</div>
											</div>
											
											<div id="pm_frequency_based" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Frequency Based Meter Reading
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td class="maxes_sct_tbl4">Rules for Meter Frequency Monitoring</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>
																	<div class="addrow" id="addpmfrequencyrule"></div> 
																</td>
															</tr>
														</table>
													</div>
												</div>
												
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_pmmeterfreq_child" class="display" data-child="pmmeterfreq" data-pk = "pmmeterfreqid" ></table>
															<div id="mxtable_pmmeterfreq_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Meter Name</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="meternum" data-input="required" data-key="value">
																				</td>
																			</tr>
																			<tr>
																				<td>Frequency</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="frequency" data-input="required" data-type="decimal">
																				</td>
																			</tr>
																			<tr>
																				<td>GL Account</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="glaccount" data-lookup="chartofaccount">
																				</td>
																			</tr>
																			
																			
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Alert Lead (days)</td>
																				<td>
																					<input type="number" data-childfld="child" data-attribute="alertlead" min="1" max="180">
																				</td>
																			</tr>
																			<tr>
																				<td>Generate WO Ahead By</td>
																				<td>
																					<input type="number" data-childfld="child" data-attribute="generatewoahead" min="1" >
																				</td>
																			</tr>
																			<tr>
																				<td>Last Reading</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="lastreading" data-input="readonly" data-type="decimal" >
																				</td>
																			</tr>
																			<tr>
																				<td>Last Reading Date</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="lastreadingdate" data-input="readonly" data-type="decimal">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Is Active?</td>
																				<td>
																					<input type="checkbox" data-childfld="child" data-attribute="isactive">
																					<br><small class="form-text text-muted">Cron Task will monitor the meter in background</small>
																				</td>
																			</tr>
																			<tr>
																				<td>Auto-Reserve Materials?</td>
																				<td>
																					<input type="checkbox" data-childfld="child" data-attribute="autoreserve">
																					<br><small class="form-text text-muted">Attempt to reserve required materials when WO is created.</small>
																				</td>
																			</tr>
																			<tr>
																				<td>Last WO</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="lastwonum" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Last WO Date</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="lastwodate" data-input="readonly" data-type="date">
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
																<td class="maxes_sct_tbl4">Assigned Job Plans For Meter Frequency</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>
																	<div class="addrow" id="addmeterfreqjp"></div>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_pmmeterfreqjp_child" class="display" data-child="pmmeterfreqjp" data-pk = "pmmeterfreqid" data-link="mxtable_pmmeterfreq_child"></table>
															<!-- PM FREQUENCY JOB PLAN !-->
															<div id="mxtable_pmmeterfreqjp_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-6">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Frequency ID</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="pmmeterfreqid" data-input="readonly" data-key="value">
																				</td>
																			</tr>
																			<tr>
																				<td>Job Plan</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="wtnum" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Sequence</td>
																				<td>
																					<input type="number" data-childfld="child" data-attribute="sequence" min="1">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Team</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team">
																				</td>
																			</tr>
																			<tr>
																				<td>Leader</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="leader" data-lookup="employees">
																				</td>
																			</tr>
																			<tr>
																				<td>GL Account</td>
																				<td>
																					<input type="number" data-childfld="child" data-attribute="glaccount" data-lookup="chartofaccount">
																				</td>
																			</tr>
																		</table>
																	</div>
																	
																</div>	
															</div>
															<!-- END !-->
														</div>
													</div>	
												</div>	
											</div>
											
											
											<!-- Health Based PM -->
											<div id="pm_health_based" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Health Reading 
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table width=100%>
															<tr>
																<td class="maxes_sct_tbl4">Rules for Health Reading</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>
																	<div class="addrow" id="addhealthrule"></div>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div id="subform-pmhealthrule" class="subform" data-child="pmhealthrule" data-table="mxtable_pmhealthrule_child">
														<div class="row eatfix">
															<div class="col-md-12">
																<table class="tbldata" style="width:100%;">
																	<tr>
																		<td>Trigger Condition</td>
																		<td>
																			<div style="display: flex; align-items: center; gap: 10px;">
																				<span>If Asset Health is</span>
																				<select data-childfld="child" data-attribute="operator" style="width: 150px;">
																					<option value="<">Less Than</option>
																					<option value="<=">Less Than / Equals</option>
																					<option value=">">More Than</option>
																					<option value=">=">More Than / Equals</option>
																					<option value="==">Equals</option>
																				</select>
																				<input type="text" data-childfld="child" data-attribute="triggerpoint" data-type="decimal" placeholder="e.g., 70" data-input="required">
																				<span>%</span>
																			</div>
																		</td>
																	</tr>
																	<!-- Bagian Hysteresis (Deadband) - Opsional -->
																	<tr>
																		<td>Reset Point (Optional)</td>
																		<td>
																			<div style="display: flex; align-items: center; gap: 10px;">
																				<span>Rule resets when health is above:</span>
																				<input type="text" data-childfld="child" data-attribute="upperreset" data-type="decimal" placeholder="e.g., 90">
																				<span>%</span>
																			</div>
																			<small class="form-text text-muted">Prevents repeated triggers until asset is repaired.</small>
																		</td>
																	</tr>
																	<tr>
																		<td>Auto-Reserve Materials?</td>
																		<td>
																			<input type="checkbox" data-childfld="child" data-attribute="autoreserve" checked>
																			<label>Attempt to reserve required materials when WO is created.</label>
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
															<table id="mxtable_pmhealthrule_child" class="display" data-child="pmhealthrule" ></table>
															<div id="mxtable_pmhealthrule_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Trigger Condition</td>
																				<td>
																					<div style="display: flex; align-items: center; gap: 10px;">
																						<span>If Asset Health is</span>
																						<select data-childfld="child" data-attribute="operator" style="width: 150px;">
																							<option value="<">Less Than</option>
																							<option value="<=">Less Than / Equals</option>
																							<option value=">">More Than</option>
																							<option value=">=">More Than / Equals</option>
																							<option value="==">Equals</option>
																						</select>
																						<input type="text" data-childfld="child" data-attribute="triggerpoint" data-type="decimal" placeholder="e.g., 70" data-input="required">
																						<span>%</span>
																					</div>
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Reset Point (Optional)</td>
																				<td>
																					<div style="display: flex; align-items: center; gap: 10px;">
																						<span>Rule resets when health is above:</span>
																						<input type="text" data-childfld="child" data-attribute="upperreset" data-type="decimal" placeholder="e.g., 90">
																						<span>%</span>
																					</div>
																					<small class="form-text text-muted">Prevents repeated triggers until asset is repaired.</small>
																				</td>
																			</tr>
																			<tr>
																				<td>Auto-Reserve Materials?</td>
																				<td>
																					<input type="checkbox" data-childfld="child" data-attribute="autoreserve" checked>
																					<label>Attempt to reserve required materials when WO is created.</label>
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Last WO</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="lastwonum" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Last WO Date</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="lastwodate" data-input="readonly" data-type="date">
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
																<td class="maxes_sct_tbl4">Assigned Job Plans For Health Monitoring</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>
																	<div class="addrow" id="addhealthrulejp"></div>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_pmhealthrulejp_child" class="display" data-child="pmhealthrulejp" data-pk = "pmhealthruleid" data-link="mxtable_pmhealthrule_child"></table>
															<div id="mxtable_pmhealthrulejp_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-6">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Health ID</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="pmhealthruleid" data-input="readonly" data-key="value">
																				</td>
																			</tr>
																			<tr>
																				<td>Job Plan</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="wtnum" data-input="readonly">
																				</td>
																			</tr>
																			<tr>
																				<td>Sequence</td>
																				<td>
																					<input type="number" data-childfld="child" data-attribute="sequence" min="1">
																				</td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-6">
																		<table class="tableDetailRow" style="width:100%;">
																			<tr>
																				<td>Team</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team">
																				</td>
																			</tr>
																			<tr>
																				<td>Leader</td>
																				<td>
																					<input type="text" data-childfld="child" data-attribute="leader" data-lookup="employees">
																				</td>
																			</tr>
																			<tr>
																				<td>GL Account</td>
																				<td>
																					<input type="number" data-childfld="child" data-attribute="glaccount" data-lookup="chartofaccount">
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
								<div id="maxes02_1_3" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>PM
														</td>
														<td><input type="text" data-attribute="pmnum" data-input="required" data-key="value" data-type="upper">
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
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Job Plans
														</td>
														<td><input type="text" data-childfld="child" data-attribute="wtnum" data-lookup="worktemplates" >
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
													<td class="maxes_sct_tbl3">Potential Material Usage
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div id = "workbench_layout" class="row eatfix">
									</div>
								</div>
								<div id="maxes02_1_4" class="maxes03">
									<hr>
									<center>
										<p>This feature is under Development</p>
									</center>
									</hr>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>