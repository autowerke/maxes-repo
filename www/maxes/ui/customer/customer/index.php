<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="customer"  data-appkeynum = "customercode">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Customer</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Contact</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Address</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_5">Site</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_6">Transactions History</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_7">Map</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_8">Warehouse</a></li>
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
														<table id="mxtable_customer" class="display"></table>
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
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Customer Code</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-key="value" ></td>
													</tr>
													<tr>
														<td>Customer Name</td>
														<td><input type="text" data-attribute="name" data-input="required" ></td>
													</tr>
													<tr>
														<td>NPWP</td>
														<td><input type="text"  data-attribute="npwp" >
														</td>
													</tr>
													<tr>
														<td>ID/KTP</td>
														<td><input type="text"  data-attribute="ktp" >
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
													<td class="maxes_sct_tbl">Selling Information</td>
												</tr>
											</table>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Registered Bank Account</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<div class="addrow" id="addCustomerBank"></div>
										</div>
									</div>
									
									
									
									<div class="row eatfix">
									
									
									
										<div id="subform-customerbank" class="subform" data-child="customerbank" data-table="mxtable_customerbank_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
																<td>Bank Name</td>
																<td><input type="text" data-childfld="child" data-attribute="bankname">
																</td>
															</tr>
															<tr>
																<td>Bank Account
																</td>
																<td><input type="text" data-childfld="child" data-attribute="bankaccount">
																</td>
															</tr>
															
															<tr>
																<td>Holder Name
																</td>
																<td><input type="text" data-childfld="child" data-attribute="banktoaccountname">
																</td>
															</tr>
															<tr>
																<td>Remarks
																</td>
																<td><input type="text" data-childfld="child" data-attribute="remarks">
																</td>
															</tr>
															<tr>
																<td>Default Transactions?
																</td>
																<td><input type="checkbox" data-childfld="child" data-attribute="isdefault">
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
												<table id="mxtable_customerbank_child" class="display" data-subform="subform-customerbank"></table>
												<div id="mxtable_customerbank_child_detail" class="mxtable_detail">
													<div class="col-md-6">
														<table class="tableDetailRow">
															<tr>
																<td>Bank Name</td>
																<td><input type="text" data-childfld="child" data-attribute="bankname">
																</td>
															</tr>
															<tr>
																<td>Bank Account
																</td>
																<td><input type="text" data-childfld="child" data-attribute="bankaccount">
																</td>
															</tr>
															
															<tr>
																<td>Holder Name
																</td>
																<td><input type="text" data-childfld="child" data-attribute="banktoaccountname">
																</td>
															</tr>
															<tr>
																<td>Remarks
																</td>
																<td><input type="text" data-childfld="child" data-attribute="remarks">
																</td>
															</tr>
															<tr>
																<td>Default Transactions?
																</td>
																<td><input type="checkbox" data-childfld="child" data-attribute="isdefault">
																</td>
															</tr>
														</table>
													</div>
													
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-3">
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
													<td>Membership Code</td>
													<td><input type="text" data-attribute="membercode"></td>
												</tr>
												<tr>
													<td>Membership Type</td>
													<td><input type="text" data-attribute="membertype"></td>
												</tr>
												</tr>
												
											</table>
										</div>
										<div class="col-md-3">
											<table class="tbldata">
												<tr>
													<td>Credit Card Type</td>
													<td><input type="text" data-attribute="creditcardtype"></td>
												</tr>
												<tr>
													<td>Credit Card Number</td>
													<td><input type="text" data-attribute="creditcardnumber"></td>
												</tr>
												<tr>
													<td>Expired Date</td>
													<td><input type="text" data-attribute="expireddate" data-type="date"></td>
												</tr>
												<tr>
													<td>Card Security Code</td>
													<td><input type="text" data-attribute="cardsecuritycode"></td>
												</tr>
											
											</table>
										</div>
										<div class="col-md-3">
											<table class="tbldata">
												
												<tr>
													<td>Accounts Receivable</td>
													<td><input type="text"  data-attribute="rbniacct" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td>Sales Account</td>
													<td><input type="text"  data-attribute="apaccount" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td>Stock Adjustment Account</td>
													<td><input type="text"  data-attribute="shrinkageacct" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td> Cost Adjustment Account</td>
													<td><input type="text"  data-attribute="costadjacct" data-lookup="chartofaccount">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-3">
											<table class="tbldata">
											<tr>
													
												<tr>
													<td>Salesman Default</td>
													<td><input type="text" data-attribute="salesrep" data-lookup="employees"></td>
												</tr>
												<tr>
													<td>Balance</td>
													<td><input type="text" data-attribute="balance" data-type="decimal" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Distributor</td>
													<td><input type="checkbox" data-attribute="distributor"></td>
												</tr>
												<tr>
													<td>Reseller</td>
													<td><input type="checkbox" data-attribute="reseller"></td>
											</table>
										</div>
									</div>
									
									
								</div>
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Customer Code</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Customer Name</td>
														<td><input type="text" data-attribute="name" data-input="required"></td>
													</tr>
													<tr>
														<td>Latitude, Longitude</td>
														<td><input type="text" data-attribute="longlat"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Customer Address</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<td class="maxes_sct_tbl2">Shipping Information</td>
												<tr>
													<td>
														<table class="tbldata">
															<tr>
																<td>Contact</td>
																<td colspan=2>
																	<input type="text" data-attribute="shipcontact" data-lookup="customercontact|customercode">
																</td>
															</tr>
															<tr>
																<td>Address</td>
																<td colspan=2>
																	<input type="text" data-attribute="shipaddress">
																</td>
															</tr>
															<tr>
																<td>City</td>
																<td colspan=2>
																	<input type="text" data-attribute="shipcity">
																</td>
															</tr>
															<tr>
																<td>Province</td>
																<td colspan=2>
																	<input type="text" data-attribute="shipstateprovince">
																</td>
															</tr>
															<tr>
																<td>Postal Code</td>
																<td colspan=2>
																	<input type="text" data-attribute="shipzippostalcode">
																</td>
															</tr>
															<tr>
																<td>Tel.</td>
																<td colspan=2>
																	<input type="text" data-attribute="shipphone">
																</td>
															</tr>
															<tr>
																<td>Fax</td>
																<td colspan=2>
																	<input type="text" data-attribute="shipcustomerfax">
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<td class="maxes_sct_tbl2">Informasi Tagihan</td>
												<tr>
													<td>
														<table class="tbldata">
															<tr>
																<td>Contact</td>
																<td colspan=2>
																	<input type="text" data-attribute="billcontact" data-lookup="customercontact|customercode">
																</td>
															</tr>
															<tr>
																<td>Address</td>
																<td colspan=2>
																	<input type="text" data-attribute="billaddress">
																</td>
															</tr>
															<tr>
																<td>City</td>
																<td colspan=2>
																	<input type="text" data-attribute="billcity">
																</td>
															</tr>
															<tr>
																<td>Province</td>
																<td colspan=2>
																	<input type="text" data-attribute="billstateprovince">
																</td>
															</tr>
															<tr>
																<td>Postal Code</td>
																<td colspan=2>
																	<input type="text" data-attribute="billzippostalcode">
																</td>
															</tr>
															<tr>
																<td>Tel.</td>
																<td colspan=2>
																	<input type="text" data-attribute="billphone">
																</td>
															</tr>
															<tr>
																<td>Fax</td>
																<td colspan=2>
																	<input type="text" data-attribute="billcustomerfax">
																</td>
															</tr>
														</table>
													</td>
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
														<td>Customer Code</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Customer Name</td>
														<td><input type="text" data-attribute="name" data-input="required"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Contact</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td>Email </td>
													<td><input type="text"  data-attribute="primaryemail" >
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addCustomerContacts"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-customer-contacts" class="subform" data-child="customercontact" data-table="mxtable_customercontacts_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Contact
															</td>
															<td><input type="text" data-childfld="child" data-attribute="contactname" data-input="required" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Title </td>
															<td><input type="text" data-childfld="child" data-attribute="function">
															</td>
														</tr>
														<tr>
															<td>Tel.
															</td>
															<td><input type="text" data-childfld="child" data-attribute="phone">
															</td>
														</tr>
														<tr>
															<td>NPWP
															</td>
															<td><input type="text" data-childfld="child" data-attribute="npwp">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Email
															</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="email">
															</td>
														</tr>
														<tr>
															<td>Whatsapp
															</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="wanum">
															</td>
														</tr>
														<tr>
															<td>Blackberry PIN
															</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="blackberrypin">
															</td>
														</tr>
														<tr>
															<td>Remarks
															</td>
															<td colspan=2><input type="text" data-childfld="child" data-attribute="remarks">
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
												<table id="mxtable_customercontacts_child" class="display" data-subform="subform-customer-contacts"></table>
												<div id="mxtable_customercontacts_child_detail" class="mxtable_detail">
													<div class="col-md-6">
														<table class="tableDetailRow">
															<tr>
																<td>Contact
																</td>
																<td><input type="text" data-childfld="child" data-attribute="contactname" data-input="readonly" data-key="value">
																</td>
															</tr>
															<tr>
																<td>Title </td>
																<td><input type="text" data-childfld="child" data-attribute="function">
																</td>
															</tr>
															<tr>
																<td>Tel.
																</td>
																<td><input type="text" data-childfld="child" data-attribute="phone">
																</td>
															</tr>
															<tr>
																<td>NPWP
																</td>
																<td><input type="text" data-childfld="child" data-attribute="npwp">
																</td>
															</tr>
														</table>
													</div>
													<div class="col-md-6">
														<table class="tableDetailRow">
															<tr>
																<td>Email
																</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="email">
																</td>
															</tr>
															<tr>
																<td>Whatsapp
																</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="wanum">
																</td>
															</tr>
															<tr>
																<td>Blackberry PIN
																</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="blackberrypin">
																</td>
															</tr>
															<tr>
																<td>Remarks
																</td>
																<td colspan=2><input type="text" data-childfld="child" data-attribute="remarks">
																</td>
															</tr>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_5" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Customer Code</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Customer Name</td>
														<td><input type="text" data-attribute="name" data-input="required"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Site Customer</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addRelatedCustomer"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-relatedcustomer" class="subform" data-child="relatedcustomer" data-table="mxtable_relatedcustomer_child">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Customer Code
														</td>
														<td><input type="text" data-childfld="child" data-attribute="relatedcustomercode" data-input="required" data-lookup="customer" data-childrentable="customer" data-childrencolumn="customercode" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Customer Name </td>
														<td><input type="text" data-childfld="child" data-attribute="relatedcustomercode.name" data-input="readonly">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_relatedcustomer_child" class="display" data-subform="subform-relatedcustomer"></table>
												<div id="mxtable_relatedcustomer_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Customer Code
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="relatedcustomercode" data-childrentable="customer" data-childrencolumn="customercode" data-input="required" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Customer Name </td>
																	<td><input type="text" data-childfld="child" data-attribute="relatedcustomercode.name" data-input="readonly">
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
								<div id="maxes02_1_6" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Customer Code</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Customer Name</td>
														<td><input type="text" data-attribute="name" data-input="required"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="subtab">
											<ul class="maxes02">
												<li class="maxes02_1"><a href="#maxes02_1_5_1">Order History</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_5_2">Shipment History</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_5_3">Tracking History</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_5_4">Work Order History</a></li>
											</ul>
										</div>
										<div class="maxes04sub">
											<div id="maxes02_1_5_1" class="maxes0_1">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Transactions Barang
												</div>
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>Year To Date(YTD) Profit
																</td>
																<td><input type="text" data-childfld="child" data-attribute="ytdprofit" data-input="readonly" data-type="decimal">
																</td>
															</tr>
														</table>
													</div>
													<div class="col-md-6">
														<table class="tbldata">
															<tr>
																<td>Net profit
																</td>
																<td><input type="text" data-childfld="child" data-attribute="netgoodsprofit" data-input="readonly" data-type="decimal">
																</td>
															</tr>
														</table>
													</div>
												</div>
												<div class="col-md-12">
													<div class="row eatfix">
														<div class="tablelist">
															<table id="mxtable_matsaletrans_child" class="display" data-child="matsaletrans"></table>
															<div id="mxtable_matsaletrans_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>SO Number</td>
																				<td><input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Num.</td>
																				<td><input type="text" data-childfld="child" data-attribute="solinenum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Item</td>
																				<td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly" data-childrentable="item" data-childrencolumn="itemnum"></td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Issue Unit</td>
																				<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Price Code</td>
																				<td><input type="text" data-childfld="child" data-attribute="pricecode" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Unit Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Quantity</td>
																				<td><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Total Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Debit Account Code</td>
																				<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td> Credit Account Code</td>
																				<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="readonly"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Entered By</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Entered Date</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Remarks</td>
																				<td><input type="text" data-childfld="child" data-attribute="remarks" data-input="readonly"></td>
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
														<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
															Service Transactions
														</div>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>Net profit
																</td>
																<td><input type="text" data-childfld="child" data-attribute="netservprofit" data-input="readonly" data-type="decimal">
																</td>
															</tr>
														</table>
													</div>
													<div class="col-md-12">
														<div class="tablelist">
															<table id="mxtable_servsaletrans_child" class="display" data-child="servsaletrans">
															</table>
															<div id="mxtable_servsaletrans_child_detail" class="mxtable_detail">
																<div class="col-md-12">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>SO Number</td>
																				<td><input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Num.</td>
																				<td><input type="text" data-childfld="child" data-attribute="solinenum" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Issue Unit</td>
																				<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Unit Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Quantity</td>
																				<td><input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Total Price</td>
																				<td><input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Debit Account Code</td>
																				<td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Credit Account Code</td>
																				<td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="readonly"></td>
																			</tr>
																		</table>
																	</div>
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td>Entered By</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Entered Date</td>
																				<td><input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly"></td>
																			</tr>
																			<tr>
																				<td>Remarks</td>
																				<td><input type="text" data-childfld="child" data-attribute="remarks" data-input="readonly"></td>
																			</tr>
																		</table>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="maxes02_1_5_2" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Shipment Transactions 
												</div>
												<div class="row eatfix">  </div>
												<div class="page-header-list">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>Shipment Cost
																</td>
																<td><input type="text" data-childfld="child" data-attribute="shippingcost" data-input="readonly" data-type="decimal">
																</td>
															</tr>
														</table>
													</div>
													<div class="row eatfix">  </div>
													<div class="tablelist">
														<table id="mxtable_soshipment_child" class="display" data-child="soshipment">
														</table>
														<div id="mxtable_soshipment_child_detail" class="mxtable_detail">
															<div class="col-md-12">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>SO Number</td>
																			<td><input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Courier Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="couriername" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Courier Tracking Num.</td>
																			<td><input type="text" data-childfld="child" data-attribute="trackingnum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Shipping Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="shippingdate" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Shipping Cost</td>
																			<td><input type="text" data-childfld="child" data-attribute="shippingcost" data-input="readonly" data-type="decimal"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Address</td>
																			<td><input type="text" data-childfld="child" data-attribute="address" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Received By</td>
																			<td><input type="text" data-childfld="child" data-attribute="receivedby" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Tel.</td>
																			<td><input type="text" data-childfld="child" data-attribute="phone" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Received Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="delivereddate" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Remarks</td>
																			<td><input type="text" data-childfld="child" data-attribute="remarks" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Entered By</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Entered Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="maxes02_1_5_3" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Transactions Tracking
												</div>
												<div class="row eatfix">  </div>
												<div class="page-header-list">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>Tracking Cost
																</td>
																<td><input type="text" data-childfld="child" data-attribute="trackingcost" data-input="readonly" data-type="decimal">
																</td>
															</tr>
														</table>
													</div>
													<div class="row eatfix">  </div>
													<div class="tablelist">
														<table id="mxtable_sotracking_child" class="display" data-child="sotracking">
														</table>
														<div id="mxtable_sotracking_child_detail" class="mxtable_detail">
															<div class="col-md-12">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>SO Number</td>
																			<td><input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Tracking Num.</td>
																			<td><input type="text" data-childfld="child" data-attribute="tracklinenum" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Type </td>
																			<td><input type="text" data-childfld="child" data-attribute="tracktype" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Remarks</td>
																			<td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Cost</td>
																			<td><input type="text" data-childfld="child" data-attribute="cost" data-input="readonly" data-type="decimal"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Entered By</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Entered Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly"></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="maxes02_1_5_4" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Transactions Issue Return
												</div>
												<div class="row eatfix">  </div>
												<div class="page-header-list">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>Actual Cost
																</td>
																<td><input type="text" data-childfld="child" data-attribute="woactualcost" data-input="readonly" data-type="decimal">
																</td>
															</tr>
														</table>
													</div>
													<div class="row eatfix">  </div>
													<div class="tablelist">
														<table id="mxtable_matusetrans_child" class="display" data-child="matusetrans">
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_7" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Customer Code</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Customer Name</td>
														<td><input type="text" data-attribute="name" data-input="required"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Map
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table class="tbldata">
												<tr>
													<td>Latitude, Longitude</td>
													<td><input type="text" data-attribute="longlat" ></td>
												</tr>
											</table>
											<div id="map">
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_8" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Customer Code</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Customer Name</td>
														<td><input type="text" data-attribute="name" data-input="required"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Maximum Transfered</td>
														<td><input type="text" data-attribute="maxcost" data-type="decimal"></td>
													</tr>
													
												</table>
											</div>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Customer Stock
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="tablelist">
											<table id="mxtable_custbalance_child" class="display" data-child="custbalance">
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
																<td>Dropped Qty</td>
																<td><input type="text" data-childfld="child" data-attribute="droppedqty" data-type="decimal" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Sold Qty</td>
																<td><input type="text" data-childfld="child" data-attribute="issuedqty" data-type="decimal"></td>
															</tr>
															<tr>
																<td>Remaining Qty.</td>
																<td><input type="text" data-childfld="child" data-attribute="curbal" data-type="decimal" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Issue Unit</td>
																<td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
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
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">In Shipping...
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_custshipment_child" class="display" data-child="custbalance">
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
	</body>
</html>