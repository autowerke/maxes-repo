<!DOCTYPE html>
<html>
	<title>
		Laundry
	</title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="laundry" data-appkeynum = "laundrynum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Laundry</a></li>
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
													<table id="mxtable_laundry" class="display"></table>
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
														<td>Line Num. Nota</td>
														<td><input type="text" data-attribute="laundrynum" data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<table class="tbldata">
													<tr>
														<td>Total KG</td>
														<td><input type="text" data-attribute="totalkg" data-type="decimal" data-input="required"></td>
													</tr>
													<tr>
														<td>Jarak</td>
														<td><input type="text" data-attribute="distance" data-type="decimal" data-input="required"></td>
													</tr>
												</table>
												</table>
											</div>
										</div>	
										<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Status</td>
														<td><input type="text" data-attribute="status"  data-input="readonly"></td>
													</tr>
													<tr>
														<td>Status Date</td>
														<td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"></td>
													</tr>
												</table>
										
										</div>	
									</div>
									
									
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Remarks </td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Name Customer Code </td>
													<td>
														<input type="text" data-attribute="customercode" data-lookup="employees" data-childrentable="employees" data-childrencolumn='personid'> </td>
												</tr>
												<tr>
													<td>Full Name </td>
													<td>
														<input type="text" data-attribute="customercode.fullname" data-childfld="child" data-input="readonly"> </td>
												</tr>
												<tr>
													<td>Address</td>
													<td>
														<input type="text" data-attribute="address"> </td>
												</tr>
												<tr>
													<td>Layanan</td>
													<td>
														<input type="text" data-attribute="service" data-lookup="laundrytype" data-lookupaction = "y"> </td>
												</tr>
												
												<tr>
													<td>Remarks </td>
													<td>
														<input type="text" data-attribute="description"> </td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Tanggal Proses </td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Cost Date</td>
													<td>
														<input type="text" data-attribute="orderdate" data-type="date"> </td>
												</tr>
												<tr>
													<td>Tanggal Mulai </td>
													<td>
														<input type="text" data-attribute="startdate" data-type="date"> </td>
												</tr>
												<tr>
													<td>Tanggal Selesai </td>
													<td>
														<input type="text" data-attribute="enddate" data-type="date"> </td>
												</tr>
											</table>
										</div>
										
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Price</td>
												</tr>
											</table>
											<table class="tbldata">
											<tr>
													<td>Ongkir</td>
													<td>
														<input type="text" data-attribute="distanceprice" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Discount(Rp)</td>
													<td>
														<input type="text" data-attribute="discountprice" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Discount(%) </td>
													<td>
														<input type="text" data-attribute="discountpercentage" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Harga Total</td>
													<td>
														<input type="text" data-attribute="totalprice" data-type="decimal" data-input="readonly"> </td>
												</tr>
											</table>
										</div>
										
									</div>
									
									
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Daftar Laundry-an</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
									<div class="col-md-4">
										<div class="addrow" id="addLaundryLine"></div>
									</div>
								</div>
									<div class="row eatfix">
										<div class="col-md-12">
										<div id="subform-laundryline" class="subform" data-table="mxtable_laundryline_child" data-child="laundryline">
											<div class="row eatfix">
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td colspan=2 class="maxes_sct_tbl2">Orderan</td>
														</tr>
														<tr>
															<td>Num.</td>
															<td>
																<input type="text" size=3 data-childfld="child" data-attribute="laundrylinenum" data-key="value" data-input="required" data-type="integer">
															</td>
														</tr>
														<tr>
															<td>Laundry</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="laundryname" data-lookup="laundrytype2" data-lookupaction="y" data-input="required">
															</td>
														</tr>
														
														<tr>
															<td>Description</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="description" data-input="readonly">
															</td>
														</tr>
														
													<tr>
																	<td>Quantity</td>
																<td>
																	<input type="text" data-childfld="child" data-type="quantity" data-attribute="quantity">
																</td>
															</tr>
															<tr>
																	<td>Unit Price</td>
																<td>
																	<input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice">
																</td>
															</tr>
															
															<tr>
																	<td>Harga total</td>
																<td>
																	<input type="text" data-childfld="child" data-type="decimal" data-attribute="lineprice" data-input="readonly">
																</td>
															</tr>
													
													</table>
												</div>
											</div>
										</div>
										
										
										
										
										<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_laundryline_child" class="display" data-subform="subform-laundryline"></table>
												<div id="mxtable_laundryline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																<td colspan=2 class="maxes_sct_tbl2">Orderan</td>
																</tr>
																<tr>
																	<td>Num.</td>
																	<td>
																		<input type="text" size=3 data-childfld="child" data-attribute="laundrylinenum" data-key="value" data-input="required" data-type="integer">
																	</td>
																</tr>
																<tr>
																	<td>Laundry</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="laundryname" data-lookup="item" data-lookupaction="y" data-input="required">
																	</td>
																</tr>
																
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" >
																	</td>
																</tr>
																
															<tr>
																	<td>Quantity</td>
																<td>
																	<input type="text" data-childfld="child" data-type="quantity" data-attribute="quantity">
																</td>
															</tr>
															<tr>
																	<td>Unit Price</td>
																<td>
																	<input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice">
																</td>
															</tr>
															
															<tr>
																	<td>Harga total</td>
																<td>
																	<input type="text" data-childfld="child" data-type="decimal" data-attribute="lineprice" data-input="readonly">
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
		</div>
	</body>
</html>