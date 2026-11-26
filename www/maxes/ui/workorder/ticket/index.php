<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="ticket" data-appkeynum = "ticketnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Service Request</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Related Data</a></li>
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
													<table id="mxtable_ticket" class="display"></table>
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
														<td>Tiket</td>
														<td><input type="text" data-attribute="ticketnum" data-input="required" data-key="value"></td>
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
													<td class="maxes_sct_tbl">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Asset</td>
													<td><input type="text" data-attribute="assetnum" data-lookup="assets"></td>
												</tr>
												<tr>
													<td>Location</td>
													<td><input type="text" data-attribute="locnum" data-lookup="locations"></td>
												</tr>
												<tr>
													<td>Customer</td>
													<td><input type="text" data-attribute="customercode" data-lookup="customer"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Status</td>
													<td><input type="text" data-attribute="status" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Status Date</td>
													<td><input type="text" data-attribute="statusdate" data-input="readonly" data-type="date"></td>
												</tr>
												<tr>
													<td>Reported By</td>
													<td><input type="text" data-attribute="reportedby" data-lookup="employees"></td>
												</tr>
												<tr>
													<td>Reported Date</td>
													<td><input type="text" data-attribute="reportdate" data-type="date"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Priority</td>
													<td><input type="text" data-attribute="priority" data-lookup="wopriority"></td>
												</tr>
												<tr>
													<td>Ticket Type</td>
													<td>
														<select data-attribute="type">
															<option value="service" selected>Service</option>
															<option value="problem">Problem</option>
															<option value="incident">Incident</option>
														</select>
													</td>
												</tr>
												<tr>
													<td>Company</td>
													<td><input type="text" data-attribute="orgcode" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Site</td>
													<td><input type="text" data-attribute="sitecode" data-input="readonly"></td>
												</tr>
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
														<td>Ticket</td>
														<td><input type="text" data-attribute="ticketnum" data-input="required" data-key="value"></td>
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
													<td class="maxes_sct_tbl">Related Work Orders</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_relatedwoticket_child" class="display" data-child="relatedwoticket"></table>
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