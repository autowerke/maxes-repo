<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="ledger" data-appkeynum = "ledgernum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Jurnal</a></li>
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
													<table id="mxtable_ledger" class="display"></table>
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
														<td>Ledger Num
														</td>
														<td><input type="text" data-attribute="ledgernum" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Start Date
														</td>
														<td><input type="text" data-attribute="startdate" data-type="date">
														</td>
													</tr>
													<tr>
														<td>End Date
														</td>
														<td><input type="text" data-attribute="enddate" data-type="date">
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
													<td class="maxes_sct_tbl">Detail
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Status
													</td>
													<td><input type="text" data-attribute="status" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Status Date
													</td>
													<td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Entered By
													</td>
													<td><input type="text" data-attribute="enterby" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Entered Date
													</td>
													<td><input type="text" data-attribute="enterdate" data-input="readonly" data-type="date">
													</td>
												</tr>
											</table>
											
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>
														Debit 
													</td>
													<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="debitcost">
													</td>
												</tr>
												<tr>
													<td>
														Credit 
													</td>
													<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="creditcost">
													</td>
												</tr>
											</table>
											
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Ledger Lines</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_ledgerline_child" class="display" data-child="ledgerline">
												</table>
												<div id="mxtable_ledgerline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
													
													<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Num.</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="ledgerlinenum" data-input="required" data-key="value" data-type="integer"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="cost" data-input="readonly"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Cost</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="cost" data-input="readonly"  data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Financial Period</td>
																	<td colspan=2><input type="text" data-childfld="child" data-attribute="financialperiod" data-input="readonly" ></td>
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