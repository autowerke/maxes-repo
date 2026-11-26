<!DOCTYPE html>
<html>
	<title>Service Receipt Inspection</title>
	<head> <?php
			include ("../../../applications/ui/ui.php");
			?> </head>
	<body id="page-top" xboset="sri" data-appkeynum="srinum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1">
									<a href="#maxes02_1_1">List</a>
								</li>
								<li id="detailrec" class="maxes02_2">
									<a href="#maxes02_1_2">Service Receipt Inspection</a>
								</li>
								<li class="maxes02_2">
									<a href="#maxes02_1_3">Planning & Detail</a>
								</li>
								<li class="maxes02_2">
									<a href="#maxes02_1_4">Inspection & Documents</a>
								</li>
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
													<table id="mxtable_sri" class="display"></table>
													<div></div>
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
														<td>SRI Number</td>
														<td>
															<input type="text" data-attribute="srinum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description" style="width:450px;">
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
													<td class="maxes_sct_tbl">Detail </td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">References </td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Created By </td>
													<td>
														<input type="text" data-attribute="enterby" data-lookup="employees" data-childrentable="employees" data-childrencolumn='personid'>
													</td>
												</tr>
												<tr>
													<td>PO Number </td>
													<td>
														<input type="text" data-attribute="ponum" data-lookup="po" data-lookupaction='y'>
													</td>
												</tr>
												<tr>
													<td>PO Date </td>
													<td>
														<input type="text" data-attribute="podate" data-type="date" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>SRI Date
													</td>
													<td><input type="text" data-attribute="sridate" data-type="date">
													</td>
												</tr>
												<tr>
													<td>Company </td>
													<td>
														<input type="text" data-attribute="orgcode" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Site </td>
													<td>
														<input type="text" data-attribute="sitecode" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Status </td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Status </td>
													<td>
														<input type="text" data-attribute="status" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Status Date </td>
													<td>
														<input type="text" data-attribute="statusdate" data-input="readonly" data-type="date">
													</td>
												</tr>
												<tr>
													<td>Changed By </td>
													<td>
														<input type="text" data-attribute="changeby" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Changed Date </td>
													<td>
														<input type="text" data-attribute="changedate" data-input="readonly" data-type="date">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Assignment</td>
												</tr>
											</table>
											<table class="tbldata">
												<table class="tbldata">
													<tr>
														<td>Process Line Num. </td>
														<td>
															<input type="text" data-attribute="docprocessnum" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Status </td>
														<td>
															<input type="text" data-attribute="docstatus" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Sent By </td>
														<td>
															<input type="text" data-attribute="docsentby" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Remarks </td>
														<td>
															<input type="text" data-attribute="docnotice" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Position </td>
														<td>
															<input type="text" data-attribute="docposition" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Arrival </td>
														<td>
															<input type="text" data-attribute="docarrival" data-input="readonly" data-type="date">
														</td>
													</tr>
												</table>
											</table>
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
														<td>SRI Number</td>
														<td>
															<input type="text" data-attribute="srinum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description" style="width:450px;">
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
													<td class="maxes_sct_tbl">Vendor Staff</td>
												</tr>
											</table>
										</div>
										</div>	
										<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addVendorStaff"></div>
										</div>
										<div class="col-md-12">
											<div class="row eatfix">
												<div id="subform-srivendor" class="subform" data-table="mxtable_srivendor_child" data-child="srivendor">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>Line Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="srivendorlinenum" data-type="integer" data-input="required"></td>
																</tr>
																
																<tr>
																	<td>Sfaff Name</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="staffname">
																	</td>
																</tr>
																<tr>
																	<td>Sfaff Position</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="staffposition">
																	</td>
																</tr>
																<tr>
																	<td>Job description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="jobdesc">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>Phone Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="phonenum" ></td>
																</tr>
																
																<tr>
																	<td>Email</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="email">
																	</td>
																</tr>
																<tr>
																	<td>Enter By</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="enterby">
																	</td>
																</tr>
																<tr>
																	<td>Enter Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="enterdate">
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
											<div class="tablelist">
												<table id="mxtable_srivendor_child" class="display" data-child="srivendor"></table>
												<div id="mxtable_srivendor_child_detail" class="mxtable_detail">
													<div class="row eatfix">
													<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Line Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="srivendorlinenum" data-type="integer" data-input="required"></td>
																</tr>
																
																<tr>
																	<td>Sfaff Name</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="staffname">
																	</td>
																</tr>
																<tr>
																	<td>Sfaff Position</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="staffposition">
																	</td>
																</tr>
																<tr>
																	<td>Job description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="jobdesc">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Phone Num.</td>
																	<td><input type="text" data-childfld="child" data-attribute="phonenum" ></td>
																</tr>
																
																<tr>
																	<td>Email</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="email">
																	</td>
																</tr>
																<tr>
																	<td>Enter By</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="enterby">
																	</td>
																</tr>
																<tr>
																	<td>Enter Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="enterdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Remarks</td>
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
									
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">SRI Lines</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<span class="addrow" id="addPOLines"></span>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_sriline_child" class="display" data-child="sriline"></table>
												<div id="mxtable_sriline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Line Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Line Number</td>
																	<td>
																		<input type="text" size=3 data-childfld="child" data-attribute="srilinenum" data-key="value" data-input="required" data-type="integer" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>PO Line Number</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Cost</div>
																	</td>
																</tr>
																<tr>
																	<td>Quantity</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="orderqty" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Order Unit</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderunit" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Tax Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="taxcost" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Line Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="linecost" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Work Order</div>
																	</td>
																</tr>
																<tr>
																	<td>Work Order Number</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Target Start</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="targetstartdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Target Finish</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="targetfinishdate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Technician</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="srivendorlinenum" data-lookup="vendorstaff|srinum">
																	</td>
																</tr>
																<tr>
																	<td>Remarks</td>
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
								<!--end tab-->
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>SRI</td>
														<td>
															<input type="text" data-attribute="srinum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-attribute="description" style="width:450px;">
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
													<td class="maxes_sct_tbl">User Acceptance Test</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addInspItem"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_sriinspection_child" class="display" data-child="sriinspection"></table>
												<div id="mxtable_sriinspection_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Line Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Line Number</td>
																	<td>
																		<input type="text" size=3 data-childfld="child" data-attribute="srilinenum" data-key="value" data-input="required" data-type="integer" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>PO </td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="ponum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>PO Line Number</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>	
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Vendor Staff</div>
																	</td>
																</tr>
																<tr>
																	<td>WO Replacement</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="wonum" data-lookup="workorder">
																	</td>
																</tr>
																<tr>
																	<td>Actual Vendor Staff</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="srivendorlinenum" data-lookup="vendorstaff|srinum">
																	</td>
																</tr>
																<tr>
																	<td>Actual Start</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="actualstartdate"  data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Actual Finish</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="actualfinishdate" data-type="date">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Delay Reason</div>
																	</td>
																</tr>
																<tr>
																	<td>Remarks<br>(Delay Reason, if any)</td>
																	<td>
																		<textarea data-childfld="child" data-attribute="delayreason"></textarea>
																	</td>
																</tr>
															</table>	
														</div>
													</div>
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Inspection Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>SRI Line Number</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="sriinslinenum" >
																	</td>
																</tr>
																<tr>
																	<td>Inspection Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="inspectiondate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Progress (%)</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="progress" data-type="decimal" >
																	</td>
																</tr>
																<tr>
																	<td>Status</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="status" data-lookup="sriinspectionstatus">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Receiving Cost Based On Progress</div>
																	</td>
																</tr>
																<tr>
																	<td>Order Qty</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderqty" data-type="decimal" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Line Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>Tax Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="taxcost" data-type="decimal" data-input="readonly">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Notes</div>
																	</td>
																</tr>
																<tr>
																	<td>Notes</td>
																	<td>
																		<textarea data-childfld="child" data-attribute="notes"></textarea>
																	</td>
																</tr>
															</table>
														</div>
													</div>
													<div class = "row eatfix">
														<div class="col-md-12">
																
															<div class="maxes_sct_tbl">Vendor SLA</div>
																
														</div>
													</div>
													<div class = "row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Quality</div>
																	</td>
																</tr>
																<tr>
																	<td>Quality Rating</td>
																	<td>
																		<select data-childfld="child" data-attribute="qualityrating">
																			 <option value="">Select Rating</option>
																				<option value="1">1 - Very Poor</option>
																				<option value="2">2 - Poor</option>
																				<option value="3">3 - Average</option>
																				<option value="4">4 - Good</option>
																				<option value="5">5 - Excellent</option>
																		</select>
																	</td>
																</tr>
																<tr>
																	<td>Quality Notes</td>
																	<td>
																		<textarea data-childfld="child" data-attribute="qualitynotes"></textarea>
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Safety & Incident</div>
																	</td>
																</tr>
																
																<tr>
																	<td><input type="checkbox"  data-childfld="child" data-attribute="hsecompliance"> HSE Compliance</td>
																</tr>
																<tr>
																	<td><input type="checkbox" data-childfld="child" data-attribute="apdcompliance"> APD Compliance</td>
																</tr>	
																<tr>
																	<td>Incident Date </td>
																	<td><input type="text" data-childfld="child"  data-attribute="incidentdate" data-type="date"></td>
																</tr>
																<tr>
																	<td>Incident Date</td>
																	<td><input type="text" data-childfld="child"  data-attribute="incidentdescription"></td>
																</tr>
															</table>
														</div>
														
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Communications</div>
																	</td>
																</tr>
																<tr>
																	<td>Communication Rating</td>
																	<td>
																		<select data-childfld="child" data-attribute="communicationrating">
																			<option value="">Select Rating</option>
																			<option value="1">1 - Very Slow</option>
																			<option value="2">2 - Slow</option>
																			<option value="3">3 - Average</option>
																			<option value="4">4 - Fast</option>
																			<option value="5">5 - Very Fast</option>
																		</select>
																	</td>
																</tr>
																<tr>
																	<td>Responsiveness Rating</td>
																	<td>
																		<select data-childfld="child" data-attribute="responsivenesrating">
																			<option value="">Select Rating</option>
																			<option value="1">1 - Very Slow</option>
																			<option value="2">2 - Slow</option>
																			<option value="3">3 - Average</option>
																			<option value="4">4 - Fast</option>
																			<option value="5">5 - Very Fast</option>
																		</select>
																	</td>
																</tr>
																<tr>
																	<td>Communication Notes</td>
																	<td><textarea data-childfld="child" data-attribute="communicationnotes"></textarea></td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								
									<div class="col-md-12">
										<div class="subtab">
											<ul class="maxes02">
												<li class="maxes02_1">
													<a href="#maxes02_1_4_1">Inspectors</a>
												</li>
												
												<li class="maxes02_2">
													<a href="#maxes02_1_4_2">Inspection Documents</a>
												</li>
												<li class="maxes02_2">
													<a href="#maxes02_1_4_3">Scope Changes</a>
												</li>
											</ul>
										</div>
									</div>
									<div class="maxes04sub">
										<div id="maxes02_1_4_1" class="maxes02_1">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"> Inspectors </div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addTeam"></div>
												</div>
											</div>
											<div class="col-md-12">
												<div id="subform-sriinspector" class="subform" data-table="mxtable_sriinspector_child" data-child="sriinspector">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>Inspector</td>
																	<td><input type="text" data-childfld="child" data-attribute="inspector" data-lookup="employees"></td>
																</tr>
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Role</div>
																	</td>
																</tr>
																<tr>
																	<td>Approval</td>
																	<td>
																		<input type="checkbox" data-childfld="child" data-attribute="isapprovalby">
																	</td>
																</tr>
																<tr>
																	<td>Acknowledge</td>
																	<td>
																		<input type="checkbox" data-childfld="child" data-attribute="isacknowledgeby">
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
														<table id="mxtable_sriinspector_child" class="display" data-child="sriinspector"></table>
														<div id="mxtable_sriinspector_child_detail" class="mxtable_detail">
															<div class="col-md-12">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Inspector</td>
																			<td><input type="text" data-childfld="child" data-attribute="inspector"  data-lookup="employees" data-childrentable="employees" data-childrencolumn="personid"></td>
																		</tr>
																		<tr>
																			<td>Inspector Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="inspector.fullname" data-input="readonly"></td>
																		</tr>
																		
																		<tr>
																			<td colspan=2>
																				<div class="maxes_sct_tbl3">Role</div>
																			</td>
																		</tr>
																		<tr>
																			<td>Role</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="role">
																			</td>
																		</tr>
																		<tr>
																			<td>Approval</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="isapprovalby">
																			</td>
																		</tr>
																		<tr>
																			<td>Acknowledge</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="isacknowledgeby">
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
										
										<div id="maxes02_1_4_2" class="maxes03">
											<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"> Inspection Documents <span id="mxtable_sriinspection_child_title"></span>
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addDocuments"></div>
												</div>
											</div>
											<div class="col-md-12">
												<div id="subform-sridocument" class="subform" data-table="mxtable_sridocument_child" data-child="sridocument">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>For Inspection Line Num.</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="sriinslinenum" >
																	</td>
																</tr>
																<tr>
																	<td>Title</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="title">
																	</td>
																</tr>
																<tr>
																	<td>Abstract</td>
																	<td>
																		<textarea data-childfld="child" data-attribute="abstract"></textarea>
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>Issue Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="issuedate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Approval Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="approvaldate" data-type="date">
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
														<table id="mxtable_sridocument_child" class="display" data-subform="subform-sridocument"></table>
														<div id="mxtable_sridocument_child_detail" class="mxtable_detail">
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Document Detail</div>
																		</td>
																	</tr>
																	<tr>
																		<td>For Inspection Line Num. </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="sriinslinenum" >
																		</td>
																	</tr>
																	<tr>
																		<td>Title</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="title">
																		</td>
																	</tr>
																	<tr>
																		<td>Abstract</td>
																		<td>
																			<textarea data-childfld="child" data-attribute="abstract"></textarea>
																		</td>
																	</tr>
																</table>
															</div>
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Date Time</div>
																		</td>
																	</tr>
																	<tr>
																		<td>Issue Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="issuedate" data-type="date">
																		</td>
																	</tr>
																	<tr>
																		<td>Approval Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="approvaldate" data-type="date">
																		</td>
																	</tr>
																</table>
															</div>
														</div>
													</div>
												</div>
											</div>
										
										
										</div>
									
									
									
										<div id="maxes02_1_4_3" class="maxes03">
										<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"> Scope Changes <span id="mxtable_sriinspection_child_title"></span>
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addScopeChanges"></div>
												</div>
											</div>
											
											<div class="row eatfix">
											<div class="col-md-12">
												<div id="subform-sriscopechange" class="subform" data-table="mxtable_sriscopechange_child" data-child="sriscopechange">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>For Inspection Line Num.</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="sriinslinenum" >
																	</td>
																</tr>
																
																<tr>
																	<td>Scope Title</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="scopetitle">
																	</td>
																</tr>
																<tr>
																	<td>Scope Description</td>
																	<td>
																		<textarea data-childfld="child" data-attribute="scopedescription"></textarea>
																		
																	</td>
																</tr>
																<tr>
																	<td>Change Impact</td>
																	<td>
																		<textarea data-childfld="child" data-attribute="changeimpact"></textarea>
																		
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tbldata">
																<tr>
																	<td>Order Qty</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderqty" data-type="decimal" data-input="required">
																	</td>
																</tr>
																<tr>
																	<td>Order Unit</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderunit"data-input="required" data-lookup="measureunit">
																	</td>
																</tr>
																
																<tr>
																	<td>Is Approved</td>
																	<td>
																		<input type="checkbox" data-childfld="child" data-attribute="isapproved">
																	</td>
																</tr>
																<tr>
																	<td>Approved By</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="approvedby" data-lookup="employees">
																	</td>
																</tr>
																<tr>
																	<td>Approval Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="approvaldate" data-type="date">
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
														<table id="mxtable_sriscopechange_child" class="display" data-subform="subform-sriscopechange"></table>
														<div id="mxtable_sriscopechange_child_detail" class="mxtable_detail">
														<div class="row eatfix">
															<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>For Inspection Line Num.</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="sriinslinenum" >
																	</td>
																</tr>
																
																<tr>
																	<td>Scope Title</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="scopetitle">
																	</td>
																</tr>
																<tr>
																	<td>Scope Description</td>
																	<td>
																		<textarea data-childfld="child" data-attribute="scopedescription"></textarea>
																		
																	</td>
																</tr>
																<tr>
																	<td>Change Impact</td>
																	<td>
																		<textarea data-childfld="child" data-attribute="changeimpact"></textarea>
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Order Qty</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderqty" data-type="decimal" data-input="required">
																	</td>
																</tr>
																<tr>
																	<td>Order Unit</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderunit"data-input="required" data-lookup="measureunit">
																	</td>
																</tr>
																
																<tr>
																	<td>Is Approved</td>
																	<td>
																		<input type="checkbox" data-childfld="child" data-attribute="isapproved">
																	</td>
																</tr>
																<tr>
																	<td>Approved By</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="approvedby" data-lookup="employees">
																	</td>
																</tr>
																<tr>
																	<td>Approval Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="approvaldate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>PR</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="prnum" data-input="readonly">
																	</td>
																</tr>
																<tr>
																	<td>PR Line Num</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="prlinenum" data-input="readonly">
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
			</div>
		</div>
	</body>
</html>