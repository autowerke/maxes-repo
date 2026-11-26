<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="fleetusage" data-appkeynum = "fleetusagenum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Penumpang</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Paket</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Pengeluaran</a></li>
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
													<table id="mxtable_fleetusage" class="display"></table>
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
														<td>Line Num.
														</td>
														<td><input type="text" data-attribute="fleetusagenum" data-input="required" data-key="value" data-type="upper">
														</td>
													</tr>
													<tr>
														<td>Rute 
														</td>
														<td><input type="text" data-attribute="fleetroutenum" data-lookup='fleetroute'  data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Asset</td>
														<td><input type="text" data-attribute="assetnum" data-lookup="fleetrouteline|fleetroutenum" data-lookupaction="y"></td>
													</tr>
													<tr>
														<td>Tanggal</td>
														<td><input type="text" data-attribute="usagedate" data-type="date" data-lookupaction="y"></td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Status
														</td>
														<td><input type="text" data-attribute="status" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Tanggal Ubah Status
														</td>
														<td><input type="text" data-attribute="statusdate" data-input="readonly" data-type="date">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Jam</td>
													<td>
														<table class="radiotime">
															<tr>
																<td>
																	<input type="radio" name="departtime" data-childfld="child" value="06:00"> 06:00 <br>
																	<input type="radio" name="departtime" data-childfld="child" value="06:30"> 06:30<br>
																	<input type="radio" name="departtime" data-childfld="child" value="08:00"> 08:00<br>
																	<input type="radio" name="departtime" data-childfld="child" value="08:30"> 08:30<br>
																	<input type="radio" name="departtime" data-childfld="child" value="10:00"> 10:00<br>
																</td>
																<td>
																	<input type="radio" name="departtime" data-childfld="child"  value="10:30"> 10:30<br>
																	<input type="radio" name="departtime" data-childfld="child" value="12:00"> 12:00<br>
																	<input type="radio" name="departtime" data-childfld="child" value="12:30"> 12:30<br>
																	<input type="radio" name="departtime" data-childfld="child" value="14:00"> 14:00<br>
																	<input type="radio" name="departtime" data-childfld="child" value="14:30"> 14:30<br>
																</td>
																<td>
																	<input type="radio" name="departtime" data-childfld="child" value="16:00"> 16:00<br>
																	<input type="radio" name="departtime" data-childfld="child" value="16:30"> 16:30<br>
																	<input type="radio" name="departtime" data-childfld="child" value="18:00"> 18:00<br>
																	<input type="radio" name="departtime" data-childfld="child" value="18:30"> 18:30<br>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td>Driver
													</td>
													<td><input type="text" data-attribute="driver" data-lookup="employees">
													</td>
												</tr>
												<tr>
													<td>Remarks
													</td>
													<td><input type="text" data-attribute="description">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Total Price Penumpang
													</td>
													<td><input type="text" data-attribute="psgrprice" data-input="readonly" data-type="decimal">
													</td>
												</tr>
												<tr>
													<td>Total Price Paket 
													</td>
													<td><input type="text" data-attribute="packprice" data-input="readonly" data-type="decimal">
													</td>
												</tr>
												<tr>
													<td>Total 
													</td>
													<td><input type="text" data-attribute="totalprice" data-input="readonly" data-type="decimal">
													</td>
												</tr>
												<tr>
													<td>Company
													</td>
													<td><input type="text" data-attribute="orgcode" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Site
													</td>
													<td><input type="text" data-attribute="sitecode" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Longitude, Latitude
													</td>
													<td><input type="text" data-attribute="longlat">
													</td>
												</tr>
												<tr>
													<td>Location Name
													</td>
													<td><input type="text" data-attribute="locname">
													</td>
												</tr>
												<tr>
													<td>Terakhir lokasi di update</td>
													<td><input type="text" data-attribute="loc_lastupdate" data-type='date' data-input='readonly'></td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl2">Booking Kursi
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-6">
												<div class="row eatfix">
													<div class="seatwrapper">
														<div class="seatcontainer">													
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-3">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl2">Cetak Kursi
														</td>
													</tr>
												</table>
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="printseat"></div>
													<div><input type="text" data-childfld="child" id="nomorkursi" ></div>
												</div>
											</div>
										</div>
										<div class="col-md-3">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl2">Cetak Laporan
														</td>
													</tr>
												</table>
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="printreport_tiket"></div>
													<div class="addrow" id="printreport_manifest"></div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl2">Data Penumpang
														</td>
													</tr>
												</table>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="addrow" id="uploadbuktitransfer"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="row eatfix">
														<div class="tablelist">
															<table id="mxtable_fleetusagecust_child" class="display" data-child="fleetusagecust"></table>
															<div id="mxtable_fleetusagecust_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>ID Penumpang</td>
																				<td><input type="text" data-childfld="child" data-attribute="customercode" data-key="value" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Name</td>
																				<td><input type="text" data-childfld="child" data-attribute="fullname" data-key="value" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Transactions Num.</td>
																				<td><input type="text" data-childfld="child" data-attribute="transnum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Address Jemput</td>
																				<td><input type="text" data-childfld="child" data-attribute="pickaddress" ></td>
																			</tr>
																			<tr>
																				<td>Address Tujuan</td>
																				<td><input type="text" data-childfld="child" data-attribute="destaddress" ></td>
																			</tr>
																			<tr>
																				<td>Payment Type</td>
																				<td><input type="text" data-childfld="child" data-attribute="paymenttype" ></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Line Num. Kursi</td>
																				<td><input type="text" data-childfld="child" data-attribute="seatnum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Position</td>
																				<td><input type="text" data-childfld="child" data-attribute="seatposition" data-input="readonly" ></td>
																			</tr>
																			<tr>
																				<td>Type Kursi</td>
																				<td><input type="text" data-childfld="child" data-attribute="seattype" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Di entry oleh</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterby" ></td>
																			</tr>
																			<tr>
																				<td>Entered Date</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterdate" ></td>
																			</tr>
																			<tr>
																				<td>Di ubah oleh</td>
																				<td><input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly" ></td>
																			</tr>
																			<tr>
																				<td>Date ubah</td>
																				<td><input type="text" data-childfld="child" data-attribute="changedate" data-input="readonly"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Harga</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprice" data-type="decimal"  data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Harga Tambahan</td>
																				<td><input type="text" data-childfld="child" data-attribute="additionalprice" data-type="decimal"  data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Total Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="lineprice" data-type="decimal" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td colspan=2>
																					<div class='loadimage_child'></div>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Line Num.
														</td>
														<td><input type="text" data-attribute="fleetusagenum" data-input="required" data-key="value" data-type="upper">
														</td>
													</tr>
													<tr>
														<td>Rute 
														</td>
														<td><input type="text" data-attribute="fleetroutenum" data-lookup='fleetroute'  data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Asset
														</td>
														<td><input type="text" data-attribute="assetnum" data-lookup="fleetrouteline|fleetroutenum" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Tanggal
														</td>
														<td><input type="text" data-attribute="usagedate" data-type="date" data-lookupaction="y">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Paket
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-3">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl2">Cetak Paket
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-3">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl2">Cetak Laporan
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-3">
											<div class="addrow" id="addpackage"></div>
										</div>
										<div class="col-md-3">
											<div class="addrow" id="calculateperkg"></div>
										</div>
										<div class="col-md-3">
											<div class="addrow" id="printpacket"></div>
											<div><input type="text" data-childfld="child" id="nomorpaket" ></div>
										</div>
										<div class="col-md-3">
											<div class="addrow" id="printreport_paket"></div>
										</div>
									</div>
									<div id="subform-addpackage" class="subform" data-childfld="child" data-child="fleetusagepack" data-table="mxtable_fleetusagepack_child">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Line Num. Paket</td>
														<td><input type="text" data-childfld="child" data-attribute="packnum" data-key="value" data-type="integer"></td>
													</tr>
													<tr>
														<td>Name Paket</td>
														<td><input type="text" data-childfld="child" data-attribute="packname" data-input="required" ></td>
													</tr>
													<tr>
														<td>Type Paket</td>
														<td><input type="text" data-childfld="child" data-attribute="packtype"  data-lookup="packtype" data-input="required" data-lookupaction="y"></td>
													</tr>
													<tr>
														<td>Harga Paket</td>
														<td><input type="text" data-childfld="child" data-attribute="unitprice"  data-type="decimal" ></td>
													</tr>
													<tr>
														<td>Tanggal Drop/Ambil Paket</td>
														<td><input type="text" data-childfld="child" data-attribute="dropdate"  data-type="date" ></td>
													</tr>
													<tr>
														<td>Remarks</td>
														<td><input type="text" data-childfld="child" data-attribute="description"  ></td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Name Pengirim</td>
														<td><input type="text" data-childfld="child" data-attribute="sendername" ></td>
													</tr>
													<tr>
														<td>Telp Pengirim</td>
														<td><input type="text" data-childfld="child" data-attribute="senderphone" ></td>
													</tr>
													<tr>
														<td>Address Jemput</td>
														<td><input type="text" data-childfld="child" data-attribute="pickaddress" ></td>
													</tr>
													<tr>
														<td>Pool?</td>
														<td><input type="checkbox" data-childfld="child" data-attribute="pool" ></td>
													</tr>
													<tr>
														<td>Name Penerima</td>
														<td><input type="text" data-childfld="child" data-attribute="receivername" ></td>
													</tr>
													<tr>
														<td>Telp Penerima</td>
														<td><input type="text" data-childfld="child" data-attribute="receiverphone" ></td>
													</tr>
													<tr>
														<td>Address Tujuan</td>
														<td><input type="text" data-childfld="child" data-attribute="destaddress"  data-input="required" ></td>
													</tr>
													<tr>
														<td>Pool?</td>
														<td><input type="checkbox" data-childfld="child" data-attribute="pooldest" ></td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Cost Jemput</td>
														<td><input type="text" data-childfld="child" data-attribute="pickprice" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Cost Antar</td>
														<td><input type="text" data-childfld="child" data-attribute="destprice" data-type="decimal"></td>
													</tr>
													<tr>
														<td>Tambahan Harga</td>
														<td><input type="text" data-childfld="child" data-attribute="additionalprice" data-type="decimal" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Total Price</td>
														<td><input type="text" data-childfld="child" data-attribute="lineprice" data-type="decimal" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Payment Type</td>
														<td> <input type='text' class='nonpersistent' data-attribute='paymenttype' data-lookup="paymenttype" >  </td>
													</tr>
													<tr>
														<td>Transaction Num./Resi</td>
														<td> <input type='text' class='nonpersistent' data-attribute='transnum' >  </td>
													</tr>
													<tr>
														<td>Dari Bank</td>
														<td> <input type='text' class='nonpersistent' data-attribute='frombank'>  </td>
													</tr>
													<tr>
														<td>Ke Bank</td>
														<td> <input type='text' class='nonpersistent' data-attribute='tobankaccount' data-lookup='orgbankaccount'>  </td>
													</tr>
													<tr>
														<td>Entered By</td>
														<td><input type="text" data-childfld="child" data-attribute="enterby" data-lookup="employees"></td>
													</tr>
													<tr>
														<td>Entered Date</td>
														<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="row eatfix">
												<div class="tablelist">
													<table id="mxtable_fleetusagepack_child" class="display" data-child="fleetusagepack"></table>
													<div id="mxtable_fleetusagepack_child_detail" class="mxtable_detail">
														<div class="row eatfix">
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tr>
																		<td>Line Num. Paket</td>
																		<td><input type="text" data-childfld="child" data-attribute="packnum" data-key="value" data-type="integer"></td>
																	</tr>
																	<tr>
																		<td>Name Paket</td>
																		<td><input type="text" data-childfld="child" data-attribute="packname" data-input="required" ></td>
																	</tr>
																	<tr>
																		<td>Type Paket</td>
																		<td><input type="text" data-childfld="child" data-attribute="packtype"  data-lookup="packtype" data-input="required" data-lookupaction="y"></td>
																	</tr>
																	<tr>
																		<td>Harga Paket</td>
																		<td><input type="text" data-childfld="child" data-attribute="unitprice"  data-type="decimal" ></td>
																	</tr>
																	<tr>
																		<td>Tanggal Kirim</td>
																		<td><input type="text" data-childfld="child" data-attribute="dropdate"  data-type="date" ></td>
																	</tr>
																	<tr>
																		<td>Remarks</td>
																		<td><input type="text" data-childfld="child" data-attribute="description"  ></td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td>Name Pengirim</td>
																		<td><input type="text" data-childfld="child" data-attribute="sendername" ></td>
																	</tr>
																	<tr>
																		<td>Telp Pengirim</td>
																		<td><input type="text" data-childfld="child" data-attribute="senderphone" ></td>
																	</tr>
																	<tr>
																		<td>Address Jemput</td>
																		<td><input type="text" data-childfld="child" data-attribute="pickaddress" ></td>
																	</tr>
																	<tr>
																		<td>Pool?</td>
																		<td><input type="checkbox" data-childfld="child" data-attribute="pool" ></td>
																	</tr>
																	<tr>
																		<td>Name Penerima</td>
																		<td><input type="text" data-childfld="child" data-attribute="receivername" ></td>
																	</tr>
																	<tr>
																		<td>Telp Penerima</td>
																		<td><input type="text" data-childfld="child" data-attribute="receiverphone" ></td>
																	</tr>
																	<tr>
																		<td>Address Tujuan</td>
																		<td><input type="text" data-childfld="child" data-attribute="destaddress"  data-input="required" ></td>
																	</tr>
																	<tr>
																		<td>Pool?</td>
																		<td><input type="checkbox" data-childfld="child" data-attribute="pooldest" ></td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td>Cost Jemput</td>
																		<td><input type="text" data-childfld="child" data-attribute="pickprice" data-type="decimal"></td>
																	</tr>
																	<tr>
																		<td>Cost Antar</td>
																		<td><input type="text" data-childfld="child" data-attribute="destprice" data-type="decimal"></td>
																	</tr>
																	<tr>
																		<td>Tambahan Harga</td>
																		<td><input type="text" data-childfld="child" data-attribute="additionalprice" data-type="decimal" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Total Price</td>
																		<td><input type="text" data-childfld="child" data-attribute="lineprice" data-type="decimal" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Payment Type</td>
																		<td> <input type='text' class='nonpersistent' data-attribute='paymenttype' data-lookup="paymenttype" >  </td>
																	</tr>
																	<tr>
																		<td>Transaction Num./Resi</td>
																		<td> <input type='text' class='nonpersistent' data-attribute='transnum' >  </td>
																	</tr>
																	<tr>
																		<td>Dari Bank</td>
																		<td> <input type='text' class='nonpersistent' data-attribute='frombank'>  </td>
																	</tr>
																	<tr>
																		<td>Ke Bank</td>
																		<td> <input type='text' class='nonpersistent' data-attribute='tobankaccount' data-lookup='orgbankaccount'>  </td>
																	</tr>
																	<tr>
																		<td>Entered By</td>
																		<td><input type="text" data-childfld="child" data-attribute="enterby" data-lookup="employees"></td>
																	</tr>
																	<tr>
																		<td>Entered Date</td>
																		<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date"></td>
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
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Line Num.
														</td>
														<td><input type="text" data-attribute="fleetusagenum" data-input="required" data-key="value" data-type="upper">
														</td>
													</tr>
													<tr>
														<td>Rute 
														</td>
														<td><input type="text" data-attribute="fleetroutenum" data-lookup='fleetroute'  data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Asset
														</td>
														<td><input type="text" data-attribute="assetnum" data-lookup="fleetrouteline|fleetroutenum" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Tanggal
														</td>
														<td><input type="text" data-attribute="usagedate" data-type="date" data-lookupaction="y">
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Total Pengeluaran
													</td>
													<td><input type="text" data-attribute="expense" data-input="readonly" data-type="decimal">
													</td>
												</tr>
												<tr>
													<td>Margin
													</td>
													<td><input type="text" data-attribute="margin" data-input="readonly" data-type="decimal">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl2">Add Pengeluaran
														</td>
													</tr>
												</table>
											</div>
											
											<div class="col-md-12">
											<div class="addrow" id="addcost"></div>
											</div>
											
										</div>
										<div class="col-md-3">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl2">Cetak Laporan
														</td>
													</tr>
												</table>
											</div>
											<div class="row eatfix">
												<div class="col-md-6">
													<div class="addrow" id="printreport_expense"></div>
													<div class="addrow" id="printreport_expense_all"></div>
												</div>
											</div>
										</div>
									
									</div>
									
									
										
										
									<div id="subform-addcost" class="subform" data-childfld="child" data-child="fleetusagecost" data-table="mxtable_fleetusagecost_child">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Num.</td>
														<td><input type="text" data-childfld="child" data-attribute="fleetcostlinenum" data-key="value" data-type="integer"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-childfld="child" data-attribute="description" data-input="required" ></td>
													</tr>
													<tr>
														<td>Cost</td>
														<td><input type="text" data-childfld="child" data-attribute="cost" data-input="required" data-type="decimal"></td>
													</tr>
													
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_fleetusagecost_child" class="display" data-child="fleetusagecost"></table>
												<div id="mxtable_fleetusagecost_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
															<tr>
																<td>Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="fleetcostlinenum" data-key="value" data-type="integer"></td>
															</tr>
															<tr>
																<td>Description</td>
																<td><input type="text" data-childfld="child" data-attribute="description" data-input="required"  ></td>
															</tr>
															<tr>
																<td>Cost</td>
																<td><input type="text" data-childfld="child" data-attribute="cost" data-input="required"  data-type="decimal"></td>
															</tr>
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