<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="taxgroup" data-appkeynum = "taxgroupnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Tax Group</a></li>
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
													<table id="mxtable_taxgroup" class="display"></table>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Group Code
														</td>
														<td><input type="text" data-attribute="taxgroupnum" data-input="required" data-key="value" data-type="upper">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description">
														</td>
													</tr>
													<tr>
														<td>Financial Period
														</td>
														<td><input type="text" data-attribute="financialperiod" data-input="required"> (yyyymm)
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
											<div class="row eatfix">
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td>Status</td>
															<td><input type="text" data-attribute="status" data-input="readonly"> </td>
														</tr>
														<tr>
															<td>Status Date</td>
															<td><input type="text" data-attribute="statusdate" data-input="readonly" data-type="date"> </td>
														</tr>
													</table>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-4">
													<table width=100%>
														<tr>
															<td class="maxes_sct_tbl3">Payment </td>
														</tr>
													</table>
													<table class="tbldata">
														<tr>
															<td>Payment Via</td>
															<td><input type="text" data-attribute="paymentvia"> </td>
														</tr>
														<tr>
															<td>ID Pembayaran</td>
															<td><input type="text" data-attribute="paymentid" > </td>
														</tr>
														<tr>
															<td>ID Billing</td>
															<td><input type="text" data-attribute="taxnum" > </td>
														</tr>
														<tr>
															<td>Tax Type</td>
															<td><input type="text" data-attribute="taxtype" data-lookup="taxtype"> </td>
														</tr>
														<tr>
															<td>Payment Type</td>
															<td><input type="text" data-attribute="taxpaymenttype" data-lookup="taxpaymenttype"> </td>
														</tr>
														<tr>
															<td>Periode</td>
															<td><input type="text" data-attribute="taxperiod"></td>
														</tr>
														<tr>
															<td>Tax Year</td>
															<td><input type="text" data-attribute="taxyear"></td>
														</tr>
													</table>
												</div>
												<div class="col-md-4">
													<table width=100%>
														<tr>
															<td class="maxes_sct_tbl3">Billing</td>
														</tr>
													</table>
													<table class="tbldata">
														<tr>
															<td>ID Billing</td>
															<td><input type="text" data-attribute="idbilling" > </td>
														</tr>
														<tr>
															<td>Active Until</td>
															<td><input type="text" data-attribute="activedate" data-type="date"> </td>
														</tr>
														<tr>
															<td>Total Amount</td>
															<td><input type="text" data-attribute="totalamount" data-type="decimal" data-input="readonly"></td>
														</tr>
														<tr>
															<td>Company</td>
															<td><input type="text" data-attribute="orgcode"  data-input="readonly" ></td>
														</tr>
														<tr>
															<td>Status</td>
															<td><input type="text" data-attribute="sitecode"  data-input="readonly"></td>
														</tr>
													</table>
												</div>
												<div class="col-md-4">
													<table width=100%>
														<tr>
															<td class="maxes_sct_tbl3">Tax Receipt</td>
														</tr>
													</table>
													<table class="tbldata">
														<tr>
															<td>Penyedia Tax</td>
															<td><input type="text" data-attribute="taxprovider" data-lookup="taxprovider" > </td>
														</tr>
														<tr>
															<td>Code Bank</td>
															<td><input type="text" data-attribute="bankcode"> </td>
														</tr>
														<tr>
															<td>NTB</td>
															<td><input type="text" data-attribute="ntb"> </td>
														</tr>
														<tr>
															<td>NTPN</td>
															<td><input type="text" data-attribute="ntpn"> </td>
														</tr>
														<tr>
															<td>STAN</td>
															<td><input type="text" data-attribute="stan"> </td>
														</tr>
													</table>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_taxgroupline_child" class="display" data-child="taxgroupline"></table>
															<div id="mxtable_taxgroupline_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Num.</td>
																				<td colspan=2><input type="text" data-childfld="child" data-attribute="taxgrouplinenum" data-key="value" data-type="integer"></td>
																			</tr>
																			<tr>
																				<td>Num. Faktur</td>
																				<td colspan=2><input type="text" data-childfld="child" data-input="readonly" data-attribute="taxreceiptnum" ></td>
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
		</div>
		</div>
	</body>
</html>