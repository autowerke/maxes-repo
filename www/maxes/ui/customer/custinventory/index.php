<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="custinventory"  data-appkeynum = "itemnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Detail</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Transactions</a></li>
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
														<table id="mxtable_custinventory" class="display"></table>
														<div>
														</div>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Item</td>
														<td><input type="text" data-attribute="itemnum" data-input="readonly" data-key="value">  
														<td> - <input type="text" data-attribute="description" data-input="readonly"></td>
														</td>
													</tr>
													<tr>
														<td>Location</td>
														<td><input type="text" data-attribute="customercode" data-input="readonly" data-key="value">  
														<td> - <input type="text" data-attribute="name" data-input="readonly"></td>
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
													<td class="maxes_sct_tbl3">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Current Stock</td>
													<td><input type="text" data-attribute="curbal" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Safety Stock Average</td>
													<td><input type="text" data-attribute="avgstock" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Issue Unit</td>
													<td><input type="text" data-attribute="issueunit" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Specification</td>
													<td><textarea  data-attribute="spectext" style="width:230px; height:100px;"> </textarea>
													</td>
												</tr>
												<tr>
													<td>NPWP</td>
													<td><input type="text"  data-attribute="npwp" >
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl3">Shipment Cost</td>
													</tr>
												</table>
											</div>
											<div class="col-md-12">
												<table class="tbldata">
													<tr>
														<td>Address</td>
														<td><input type="text"  data-attribute="shipaddress" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>City</td>
														<td><input type="text"  data-attribute="shipcity" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Province</td>
														<td><input type="text"  data-attribute="shipstateprovince" data-input="readonly">
														</td>
													</tr>
													<tr>
														<td>Postal Code</td>
														<td><input type="text"  data-attribute="shipzippostalcode" data-input="readonly">
														</td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-4">
											<div class="col-md-12">
												<table width=100%>
													<tr>
														<td class="maxes_sct_tbl3">Billing Address</td>
													</tr>
												</table>
											</div>
											<table class="tbldata">
												<tr>
													<td>Address</td>
													<td><input type="text"  data-attribute="billaddress" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>City</td>
													<td><input type="text"  data-attribute="billcity" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Province</td>
													<td><input type="text"  data-attribute="billstateprovince" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Postal Code</td>
													<td><input type="text"  data-attribute="billzippostalcode" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Tel.</td>
													<td><input type="text"  data-attribute="billphone" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Consignment Stock</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="tablelist">
											<table id="mxtable_custbalance_child" class="display" data-child="custbalanceprice">
											</table>
											<div id="mxtable_custbalance_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Item</td>
																<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly" data-childrentable="item" data-childrencolumn="itemnum"></td>
															</tr>
															<tr>
																<td>Condition Type</td>
																<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Remaining Qty.</td>
																<td><input type="text" data-childfld="child" data-attribute="curbal" data-type="decimal" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Issue Unit</td>
																<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Discount #1</td>
																<td><input type="text" data-childfld="child" data-attribute="primarydiscount" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Discount #2</td>
																<td><input type="text" data-childfld="child" data-attribute="secondarydiscount" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Discount Unit Price</td>
																<td><input type="text" data-childfld="child" data-attribute="discountprice" data-input="readonly"></td>
															</tr>
														</table>
													</div>
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>WO</td>
																<td><input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Delivered Date</td>
																<td><input type="text" data-childfld="child" data-attribute="arriveddate" data-type="date"></td>
															</tr>
															<tr>
																<td>Entered By</td>
																<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Entered Date</td>
																<td><input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly"  data-type="date"></td>
															</tr>
															<tr>
																<td>Unit Cost</td>
																<td><input type="text" data-childfld="child" data-attribute="unitcost"  data-input="readonly" data-type="decimal"></td>
															</tr>
															<tr>
																<td>Unit Price</td>
																<td><input type="text" data-childfld="child" data-attribute="unitprice"  data-input="readonly" data-type="decimal"></td>
															</tr>
															<tr>
																<td>Unit Profit</td>
																<td><input type="text" data-childfld="child" data-attribute="unitprofit"  data-input="readonly" data-type="decimal"></td>
															</tr>
														</table>
													</div>
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Batch Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="batchnum" ></td>
															</tr>
															<tr>
																<td>Expired Date</td>
																<td><input type="text" data-childfld="child" data-attribute="expdate" data-input="readonly" data-type="date"></td>
															</tr>
															<tr>
																<td>FIFO/LIFO Date</td>
																<td><input type="text" data-childfld="child" data-attribute="costdate"  data-input="readonly" data-type="date"></td>
															</tr>
															<tr>
																<td>Discount Price per Unit</td>
																<td><input type="text" data-childfld="child" data-attribute="unitpricediscount"  data-input="readonly" data-type="decimal"></td>
															</tr>
															<tr>
																<td>Unit Profit Discount</td>
																<td><input type="text" data-childfld="child" data-attribute="unitprofitdiscount"  data-input="readonly" data-type="decimal"></td>
															</tr>
														</table>
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
														<td>Item</td>
														<td><input type="text" data-attribute="itemnum" data-input="readonly" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Customer Code</td>
														<td><input type="text" data-attribute="customercode" data-input="readonly" data-key="value"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="subtab">
											<ul class="maxes02">
												<li class="maxes02_1"><a href="#maxes02_1_3_1">Transfer Transactions </a></li>
												<li class="maxes02_2"><a href="#maxes02_1_3_2">Return Transactions </a></li>
												<li class="maxes02_2"><a href="#maxes02_1_3_3">Stock Adjustment</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_3_4">Cost Adjustment</a></li>
											</ul>
										</div>
										<div class="maxes04sub">
											<div id="maxes02_1_3_1" class="maxes02_1">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Transfer Transactions 
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_custinvtrans_child" class="display" data-child="custinvtrans"></table>
														</div>
													</div>
												</div>
											</div>
											<div id="maxes02_1_3_2" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Return Transactions 
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_custinvtransreturn_child" class="display" data-child="custinvtrans"></table>
														</div>
													</div>
												</div>
											</div>
											<div id="maxes02_1_3_3" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Stock Adjustment
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_custinvtransqtyadjust_child" class="display" data-child="custinvtrans"></table>
														</div>
													</div>
												</div>
											</div>
											<div id="maxes02_1_3_4" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Cost Adjustment
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_custinvtranscostadjust_child" class="display" data-child="custinvtrans"></table>
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