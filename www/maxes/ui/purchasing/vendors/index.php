<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="vendor" data-appkeynum = "vendorcode">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a>
								</li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Vendor/Supplier</a>
								</li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Contact</a>
								</li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Addresses</a>
								</li>
								<li class="maxes02_2"><a href="#maxes02_1_5">Site</a>
								</li>
								<li class="maxes02_2"><a href="#maxes02_1_6">Category</a>
								</li>
								<li class="maxes02_2"><a href="#maxes02_1_7">Legal Documents</a>
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
													<table id="mxtable_vendor" class="display"></table>
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
														<td>Vendor Code
														</td>
														<td>
															<input type="text" data-attribute="vendorcode" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Name
														</td>
														<td>
															<input type="text" data-attribute="name" data-autofillobject="vendor" data-columns="name, address, stateprovince, phone">
														</td>
													</tr>
													<tr>
														<td>Currency</td>
														<td>
															<input type="text" data-attribute="currencycode" data-lookup="currency">
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
													<td class="maxes_sct_tbl2">Vendor Bank Details
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<!-- LEFT -->
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Bank Address</td>
													<td>
														<input type="text" data-attribute="bankaddress">
													</td>
												</tr>
												<tr>
													<td>Account Holder</td>
													<td>
														<input type="text" data-attribute="accountholder">
													</td>
												</tr>
											</table>
										</div>
										<!-- MIDDLE -->
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Bank Name #1</td>
													<td>
														<input type="text" data-attribute="bankname1" data-input="required">
													</td>
												</tr>
												<tr>
													<td>Account Number</td>
													<td>
														<input type="text" data-attribute="bankaccount1" data-input="required">
													</td>
												</tr>
											</table>
										</div>
										<!-- RIGHT -->
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Bank Name #2</td>
													<td>
														<input type="text" data-attribute="bankname2">
													</td>
												</tr>
												<tr>
													<td>Account Number</td>
													<td>
														<input type="text" data-attribute="bankaccount2">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Bank Name #3</td>
													<td>
														<input type="text" data-attribute="bankname3">
													</td>
												</tr>
												<tr>
													<td>Account Number</td>
													<td>
														<input type="text" data-attribute="bankaccount3">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Bank Name #4</td>
													<td>
														<input type="text" data-attribute="bankname4">
													</td>
												</tr>
												<tr>
													<td>Account Number</td>
													<td>
														<input type="text" data-attribute="bankaccount4">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Bank Name #5</td>
													<td>
														<input type="text" data-attribute="bankname5">
													</td>
												</tr>
												<tr>
													<td>Account Number</td>
													<td>
														<input type="text" data-attribute="bankaccount5">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Purchasing Information</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Homepage/Website
													</td>
													<td>
														<input type="text" data-attribute="website">
													</td>
												</tr>
												<tr>
													<td>Account Code Payable
													</td>
													<td colspan=2>
														<input type="text" data-attribute="apacct" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td>Account Code Payable (Hutang)
													</td>
													<td colspan=2>
														<input type="text" data-attribute="rbniacct" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td>Registered By</td>
													<td colspan=2><input type="text" data-attribute="enterby" data-childrentable="employees" data-childrencolumn='personid'> <input type="text" data-attribute="enterby.fullname" data-childfld="child" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Date Registered</td>
													<td colspan=2><input type="text" data-attribute="enterdate" data-type="date"></td>
												</tr>
												<tr>
													<td>Disqualified?
													</td>
													<td colspan=2>
														<input type="checkbox" name="disqualified" data-attribute="disqualified">
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
														<td>Vendor Code</td>
														<td>
															<input type="text" data-attribute="vendorcode" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Name</td>
														<td>
															<input type="text" data-attribute="name">
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
													<td class="maxes_sct_tbl2">Contact Information</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addContactRow"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-vendor-contact" class="subform" data-child="vendorcontact" data-table="mxtable_vendorcontact_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Contact Person</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="contact" data-input="required" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Position</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="position">
															</td>
														</tr>
														<tr>
															<td>Email</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="email">
															</td>
														</tr>
														<tr>
															<td>Phone</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="phone">
															</td>
														</tr>
														<tr>
															<td>Office Phone</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="officephone">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Mobile Phone</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="mobilephone">
															</td>
														</tr>
														<tr>
															<td>Fax</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="fax">
															</td>
														</tr>
														<tr>
															<td>Whatsapp Number</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="wanum">
															</td>
														</tr>
														<tr>
															<td>Blackberry PIN</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="blackberrypin">
															</td>
														</tr>
														<tr>
															<td>Line ID</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="lineid">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_vendorcontact_child" class="display" data-subform="subform-vendor-contact">
												</table>
												<div id="mxtable_vendorcontact_child_detail" class="mxtable_detail">
													<div class = "row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Contact Person</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="contact" data-input="required" data-key="value" readonly>
																	</td>
																</tr>
																<tr>
																	<td>Position</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="position">
																	</td>
																</tr>
																<tr>
																	<td>Email</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="email">
																	</td>
																</tr>
																<tr>
																	<td>Home Phone</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="phone">
																	</td>
																</tr>
																<tr>
																	<td>Office Phone</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="officephone">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Mobile Phone</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="mobilephone">
																	</td>
																</tr>
																<tr>
																	<td>Fax</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="fax">
																	</td>
																</tr>
																<tr>
																	<td>Whatsapp Number</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="wanum">
																	</td>
																</tr>
																<tr>
																	<td>Blackberry PIN</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="blackberrypin">
																	</td>
																</tr>
																<tr>
																	<td>Line ID</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="lineid">
																	</td>
																</tr>
																<tr>
																	<td>VPortal Username</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="username">
																	</td>
																</tr>
																<tr>
																	<td>VPortal Password</td>
																	<td colspan=2>
																		<button type="button" class="general-button" id="changepass">Change Password</button>
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
								<div id="maxes02_1_4" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Vendor Code</td>
														<td>
															<input type="text" data-attribute="vendorcode" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Name</td>
														<td>
															<input type="text" data-attribute="name">
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
													<td class="maxes_sct_tbl2">Vendor Addresses</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<td class="maxes_sct_tbl22">Contact Address</td>
												<tr>
													<td>
														<table class="tbldata">
															<tr>
																<td>Contact Person</td>
																<td colspan=2>
																	<input type="text" data-attribute="contact" data-lookup="vendorcontact|vendorcode">
																</td>
															</tr>
															<tr>
																<td>Address</td>
																<td colspan=2>
																	<input type="text" data-attribute="address">
																</td>
															</tr>
															<tr>
																<td>City</td>
																<td colspan=2>
																	<input type="text" data-attribute="city">
																</td>
															</tr>
															<tr>
																<td>Province</td>
																<td colspan=2>
																	<input type="text" data-attribute="stateprovince">
																</td>
															</tr>
															<tr>
																<td>Postal Code</td>
																<td colspan=2>
																	<input type="text" data-attribute="zippostalcode">
																</td>
															</tr>
															<tr>
																<td>Phone Number</td>
																<td colspan=2>
																	<input type="text" data-attribute="phone">
																</td>
															</tr>
															<tr>
																<td>Vendor Fax</td>
																<td colspan=2>
																	<input type="text" data-attribute="vendorfax">
																</td>
															</tr>
															<tr>
																<td>Country</td>
																<td colspan=2>
																	<input type="text" data-attribute="country">
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td class="maxes_sct_tbl22">Remit/Invoice To</td>
												</tr>
												<tr>
													<td>
														<table class="tbldata">
															<tr>
																<td>Contact Person</td>
																<td colspan=2>
																	<input type="text" data-attribute="rcontact" data-lookup="vendorcontact|vendorcode">
																</td>
															</tr>
															<tr>
																<td>Address</td>
																<td colspan=2>
																	<input type="text" data-attribute="raddress">
																</td>
															</tr>
															<tr>
																<td>City</td>
																<td colspan=2>
																	<input type="text" data-attribute="rcity">
																</td>
															</tr>
															<tr>
																<td>Province</td>
																<td colspan=2>
																	<input type="text" data-attribute="rstateprovince">
																</td>
															</tr>
															<tr>
																<td>Postal Code</td>
																<td colspan=2>
																	<input type="text" data-attribute="rzippostalcode">
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
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
														<td>Vendor Code
														</td>
														<td>
															<input type="text" name="vendor" data-attribute="vendorcode" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Name
														</td>
														<td>
															<input type="text" name="name" data-attribute="name">
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
													<td class="maxes_sct_tbl2">Sites</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addBranchRow"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-vendor-branch" class="subform" data-child="relatedvendor" data-table="mxtable_vendorbranches_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Vendor Branch ID</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="relatedvendorcode" data-key="value" data-childrentable="vendor" data-childrencolumn="vendorcode" data-lookup="vendor|vendorcode">
															</td>
														</tr>
														<tr>
															<td>Name</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="relatedvendorcode.name" data-input="readonly">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_vendorbranches_child" class="display" data-subform="subform-vendor-branch"></table>
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
														<td>Vendor Code</td>
														<td>
															<input type="text" data-attribute="vendorcode" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Name</td>
														<td>
															<input type="text" data-attribute="name">
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
													<td class="maxes_sct_tbl2">Vendor Categories</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="col-md-2">
												<div class="addrow" id="addVendorCategory"></div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<table class="tbldata">
												<tr>
													<td>Keyword</td>
													<td>
														<textarea data-attribute="keyword"></textarea>
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-2">
											<table class="tbldata">
												<tr>
													<td>Manufacturer</td>
													<td colspan=2>
														<input type="checkbox" data-attribute="manufacturer">
													</td>
												</tr>
												<tr>
													<td>Authorized Agent</td>
													<td colspan=2>
														<input type="checkbox" data-attribute="authorizedagent">
													</td>
												</tr>
												<tr>
													<td>General Trader</td>
													<td colspan=2>
														<input type="checkbox" data-attribute="generaltrading">
													</td>
												</tr>
												<tr>
													<td>Local Community</td>
													<td colspan=2>
														<input type="checkbox" data-attribute="localcommunity">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-vendor-category" class="subform" data-child="vendorcategory" data-table="mxtable_vendorCategory_child">
											<div class="col-md-12">
												<table class="tbldata">
													<tr>
														<td>Category</td>
														<td colspan=2>
															<input type="text" data-childfld="child" data-attribute="categorycode" data-input="required" data-key="value">
															<input type="text" data-childfld="child" data-attribute="description">
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
													<table id="mxtable_vendorCategory_child" class="display" data-subform="subform-vendor-category">
													</table>
													<div id="mxtable_vendorCategory_child_detail" class="mxtable_detail">
														<div class="row eatfix">
															<div class="col-md-6">
																<table class="tableDetailRow">
																	<tbody>
																		<tr>
																			<td>Category Code</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="categorycode" data-key="value" readonly>
																			</td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="description">
																			</td>
																		</tr>
																	</tbody>
																</table>
															</div>
														</div>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Vendor Code</td>
														<td>
															<input type="text" data-attribute="vendorcode" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Name</td>
														<td>
															<input type="text" data-attribute="name">
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
													<td class="maxes_sct_tbl2">Legal Documents</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addLegalDocuments"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-legaldoc" class="subform" data-child="legaldoc" data-table="mxtable_legaldoc_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Legal Doc. Num</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="legaldocnum" data-input="required" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Legal Doc. Type</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="legaldoctype">
															</td>
														</tr>
														<tr>
															<td>Description</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="description">
															</td>
														</tr>
														<tr>
															<td>Issue Date</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="issuedate" data-type="date">
															</td>
														</tr>
														<tr>
															<td>Expiry Date</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="expirydate" data-type="date">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Status</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="status">
															</td>
														</tr>
														<tr>
															<td>Created By</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="createdby">
															</td>
														</tr>
														<tr>
															<td>Created Date</td>
															<td colspan=2>
																<input type="text" data-childfld="child" data-attribute="createddate" data-type="date">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_legaldoc_child" class="display" data-subform="subform-legaldoc">
												</table>
												<div id="mxtable_legaldoc_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Legal Doc. Num</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="legaldocnum" data-input="required" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Legal Doc. Type</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="legaldoctype">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="description">
																	</td>
																</tr>
																<tr>
																	<td>Strength Level</td>
																	<td colspan="2">
																		<input 
																			type="number" 
																			data-childfld="child" 
																			data-attribute="level" 
																			data-type="integer"
																			step="1"
																			min="0"
																			>
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Status</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="status">
																	</td>
																</tr>
																<tr>
																	<td>Issue Date</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="issuedate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Expiry Date</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="expirydate" data-type="date">
																	</td>
																</tr>
																<tr>
																	<td>Created By</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="createdby">
																	</td>
																</tr>
																<tr>
																	<td>Created Date</td>
																	<td colspan=2>
																		<input type="text" data-childfld="child" data-attribute="createddate" data-type="date">
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
	</body>
</html>