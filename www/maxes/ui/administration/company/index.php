<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id='page-top' xboset="organization" data-appkeynum = "orgcode">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Company</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Addresses</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Sites</a></li>
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
                                                <table id="mxtable_organization" class="display"></table>
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
														<td>Company</td>
														<td><input type="text" data-type="upper" data-attribute="orgcode" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Name</td>
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
													<td class="maxes_sct_tbl">Responsibilities</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Leader
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Name
													</td>
													<td><input type="text" data-attribute="name1" data-lookup="employees" data-childrentable = "employees" data-childrencolumn="personid">
													</td>
												</tr>
												<tr>
													<td>Title
													</td>
													<td><input type="text" data-attribute="name1.title" data-child="childfld" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Tax Responsibilities
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>NPWP
													</td>
													<td><input type="text" data-attribute="npwp2">
													</td>
												</tr>
												<tr>
													<td>Name
													</td>
													<td><input type="text" data-attribute="name2" data-lookup="employees" data-childrentable = "employees" data-childrencolumn="personid">
													</td>
												</tr>
												<tr>
													<td>Title
													</td>
													<td><input type="text" data-attribute="name2.title" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Tax Responsibilities #2
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>NPWP
													</td>
													<td><input type="text" data-attribute="npwp3">
													</td>
												</tr>
												<tr>
													<td>Name
													</td>
													<td><input type="text" data-attribute="name3" data-lookup="employees" data-childrentable = "employees" data-childrencolumn="personid">
													</td>
												</tr>
												<tr>
													<td>Title
													</td>
													<td><input type="text" data-attribute="name3.title" data-input="readonly">
													</td>
												</tr>
											</table>
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
										<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Account Code Default
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Owner's Capital Account
													</td>
													<td><input type="text" data-attribute="capitalacct" data-input="required" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td>Cash in Bank
													</td>
													<td><input type="text" data-attribute="cashonbankacct" data-input="required" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td>Cash on Hand
													</td>
													<td><input type="text" data-attribute="cashonhandacct" data-input="required" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td>Base Currency
													</td>
													<td><input type="text" data-attribute="basecurrency"  data-input="required" data-lookup="currency">
													</td>
												</tr>
												<tr>
													<td>Default Issue Type
													</td>
													<td><input type="text" data-attribute="costtype"  data-input="required" data-lookup="costtype">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Tax Service Office (KPP)
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>NPWP
													</td>
													<td><input type="text" data-attribute="npwp">
													</td>
												</tr>
												<tr>
													<td>Tax Service Office (KPP)
													</td>
													<td><input type="text" data-attribute="kppoffice">
													</td>
												</tr>
												<tr>
													<td>KLU
													</td>
													<td><input type="text" data-attribute="klu">
													</td>
												</tr>
												<tr>
													<td>Type of Taxpayer
													</td>
													<td><input type="text" data-attribute="wptype" data-lookup="wptype">
													</td>
												</tr>
												<tr>
												
													<td>Types of Financial Reports
													</td>
													<td><input type="text" data-attribute="finreporttype" data-lookup="finreporttype">
													</td>
												</tr>
											</table>
										</div>
										
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Other Settings
													</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Report URL Attachment
													</td>
													<td><input type="text" data-attribute="urlattach">
													</td>
												</tr>
												<tr>
													<td>VPortal Administrator
													</td>
													<td><input type="text" data-attribute="vportaladmin" data-lookup="employees">
													</td>
												</tr>
												
											</table>
										</div>		
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">List of Account Numbers</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-6">
											<div class="addrow" id="addBankAccount"></div>
										</div>
									</div>
									
															
									<div class="row eatfix">
									<div class="col-md-12">
										<div id="subform-bankaccount" class="subform" data-child="orgbankaccount" data-table="mxtable_bankaccount_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Account Number</td>
															<td><input type="text" data-childfld="child" data-attribute="bankaccount" data-input="required" data-key="value"></td>
														</tr>
														<tr>
															<td>Bank Name</td>
															<td><input data-childfld="child" type="text" data-attribute="bankname"></td>
														</tr>
														<tr>
															<td>Bank Account Owner</td>
															<td><input data-childfld="child" type="text" data-attribute="bankholder"></td>
														</tr>
														<tr>
															<td>Default Transactions?</td>
															<td><input data-childfld="child" type="checkbox" data-attribute="isdefault"></td>
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
												<table id="mxtable_bankaccount_child" class="display" data-subform="subform-bankaccount">					
												</table>
												<div id="mxtable_bankaccount_child_detail" class="mxtable_detail">
													
														<div class="row eatfix">
																<div class="col-md-8">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Account Number</td>
																			<td><input type="text" data-childfld="child" data-attribute="bankaccount" data-input="required" data-key="value"></td>
																		</tr>
																		<tr>
																			<td>Bank Name</td>
																			<td><input data-childfld="child" type="text" data-attribute="bankname"></td>
																		</tr>
																		<tr>
																			<td>Bank Account Owner</td>
																			<td><input data-childfld="child" type="text" data-attribute="bankholder"></td>
																		</tr>
																		<tr>
																			<td>Default Transactions?</td>
																			<td><input data-childfld="child" type="checkbox" data-attribute="isdefault"></td>
																		</tr>
																	</table>
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
														<td>Company</td>
														<td><input type="text" data-type="upper" data-key="value" data-type="upper" data-attribute="orgcode" data-input="required"></td>
													</tr>
													<tr>
														<td>Name</td>
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
													<td class="maxes_sct_tbl">Address</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addcompanyAddress"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-company-addresscode" class="subform" data-child="address" data-table="mxtable_address_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Address Code
															</td>
															<td><input type="text" data-childfld="child" data-attribute="addresscode" data-input="required" data-type="upper" data-childrentable = "address" data-childrencolumn = "orgcode" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Address
															</td>
															<td  colspan=2><input data-childfld="child" type="text" data-attribute="address">
															</td>
														</tr>
														<tr>
															<td>City
															</td>
															<td  colspan=2><input data-childfld="child" type="text" data-attribute="city">
															</td>
														</tr>
														<tr>
															<td>Province
															</td>
															<td  colspan=2><input data-childfld="child" type="text" data-attribute="stateprovince">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Postal Code
															</td>
															<td  colspan=2><input data-childfld="child" type="text" data-attribute="zippostalcode">
															</td>
														</tr>
														<tr>
															<td>Country
															</td>
															<td colspan=2><input data-childfld="child" type="text" data-attribute="country">
															</td>
														</tr>
														<tr>
															<td >Remarks </td>
															<td><input type="text" data-childfld="child" data-attribute="description">
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
												<table id="mxtable_address_child" class="display" data-subform="subform-company-addresscode">					
												</table>
												<div id="mxtable_address_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-5">
															<table class="tableDetailRow">
																<tbody>
																	<tr>
																		<td>Address Code</td>
																		<td><input type="text" data-childfld="child" data-type="upper" data-attribute="addresscode"  data-childrentable="address" data-childrencolumn="addresscode"  data-input="readonly" data-key="value"> </td>
																	</tr>
																	<tr>
																		<td>Office Tel.</td>
																		<td><input type="text" data-childfld="child" data-attribute="officephone"> </td>
																	</tr>
																	<tr>
																		<td>Fax</td>
																		<td><input type="text" data-childfld="child" data-attribute="fax"> </td>
																	</tr>
																	<tr>
																		<td>Address</td>
																		<td><input type="text" data-childfld="child" data-attribute="address"> </td>
																	</tr>
																	<tr>
																		<td>City</td>
																		<td><input type="text" data-childfld="child" data-attribute="city" ></td>
																	</tr>
																	<tr>
																		<td>Remarks</td>
																		<td><input type="text" data-childfld="child" data-attribute="description"> </td>
																	</tr>
															</table>
														</div>
														<div class="col-md-5">
															<table class="tableDetailRow">
																<tbody>
																	<tr>
																		<td>Province</td>
																		<td><input type="text" data-childfld="child" data-attribute="stateprovince"></td>
																	</tr>
																	<tr>
																		<td>Postal Code</td>
																		<td><input type="text" data-childfld="child"  data-attribute="zippostalcode" ></td>
																	</tr>
																	<tr>
																		<td>Country</td>
																		<td><input type="text" data-childfld="child"  data-attribute="country" ></td>
																	</tr>
																	<tr>
																		<td>KPP Pratama</td>
																		<td><input type="text" data-childfld="child"  data-attribute="kppoffice" ></td>
																	</tr>
																	<tr>
																		<td>NPWP</td>
																		<td><input type="text" data-childfld="child"  data-attribute="npwp" ></td>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Perusahaaan</td>
														<td><input type="text"  data-type="upper" data-key="value" data-type="upper" data-attribute="orgcode" data-input="required"></td>
													</tr>
													<tr>
														<td>Name</td>
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
													<td class="maxes_sct_tbl">Site
													</td>
												</tr>
											</table>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addSites"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div id="subform-company-sites" class="subform" data-child="site" data-table = "mxtable_site_child">
													<div class="row eatfix">
														<div class="col-md-12">
															<table class="tbldata">
																<tr>
																	<td>Site
																	</td>
																	<td><input type="text" data-key = "value" data-childfld="child" data-attribute="sitecode" data-input="required" data-type="upper">
																	</td>
																</tr>
																<tr>
																	<td>Cash on Hand
																	</td>
																	<td><input type="text" data-key = "value" data-childfld="child" data-attribute="cashonhandacct" data-lookup="chartofaccount">
																	</td>
																</tr>
																<tr>
																	<td>Cash in Bank
																	</td>
																	<td><input type="text" data-key = "value" data-childfld="child" data-attribute="cashonbankacct" data-lookup="chartofaccount">
																	</td>
																</tr>
																<tr>
																	<td>Remarks
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="description">
																	</td>
																</tr>
																<tr>
																	<td>Site Contact
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="sitecontact" data-lookup="employees">
																	</td>
																</tr>
																<tr>
																	<td>Active </td>
																	<td><input type="checkbox" data-childfld="child" data-attribute="active">	</td>
																</tr>
															</table>
															
														</div>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="tablelist">
													<table id="mxtable_site_child" class="display"  data-subform="subform-company-sites">
													</table>
												</div>
											</div>
											<div id="mxtable_site_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-5">
														<table class="tableDetailRow">
															<tbody>
																<tr>
																	<td>Site Code</td>
																	<td><input type="text" data-childfld="child" data-attribute="sitecode"  data-key="value" data-input="readonly" data-type="upper"> </td>
																</tr>
																<tr>
																	<td>Cash on Hand
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="cashonhandacct" data-lookup="chartofaccount">
																	</td>
																</tr>
																<tr>
																	<td>Cash in Bank
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="cashonbankacct" data-lookup="chartofaccount">
																	</td>
																</tr>
																<tr>
																	<td>Remarks</td>
																	<td><input type="text" data-childfld="child" data-attribute="description"> </td>
																</tr>
																<tr>
																	<td>Site Contact</td>
																	<td><input type="text" data-childfld="child" data-attribute="sitecontact" data-lookup="employees"></td>
																</tr>
																<tr>
																	<td>Active</td>
																	<td><input type="checkbox" data-childfld="child" data-attribute="active"></td>
																</tr>
														</table>
													</div>
													<div class="col-md-5">
														<table class="tableDetailRow">
															<tbody>
																<tr>
																	<td>Entered By</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterby" class="readonly"></td>
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td><input type="text" data-childfld="child"  data-attribute="enterdate" data-type="date" class="readonly"></td>
																</tr>
																<tr>
																	<td>Changed By</td>
																	<td><input type="text" data-childfld="child"  data-attribute="changeby"  class="readonly"></td>
																</tr>
																<tr>
																	<td>Changed Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="changedate" data-type="date" class="readonly" ></td>
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
													<td class="maxes_sct_tbl">Address Site <span id="mxtable_siteAddress_child_title"></span>
													</td>
												</tr>
											</table>
											<div class="row eatfix">
												<div class="col-md-4">
													<div class="addrow" id="addSiteAddress"></div>
												</div>
											</div>
											<div class="row eatfix">
												<div id="subform-company-siteaddress" class="subform" data-child="siteaddress" data-table="mxtable_siteAddress_child" data-pk="sitecode">
													<div class="col-md-4">
														<table class="tbldata">
															<tr>
																<td>Site Code</td>
																<td> <input type="text" size=6 data-childfld="child" data-attribute="sitecode" data-childrentable="site" data-childrencolumn="sitecode" data-key="value" data-lookup="site"> 
																	<input type="text" data-childfld="child" data-attribute="sitecode.description"  data-input="readonly"> 
																</td>
															</tr>
															<tr>
																<td>Address
																</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="siteaddresscode" data-childrentable="address" data-childrencolumn="addresscode" data-input="required" data-key="value" data-lookup="address">
																	<input type="text" data-childfld="child" data-attribute="siteaddresscode.description" data-input="readonly">
																</td>
															</tr>
															<tr>
																<td>Billing Contact
																</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="billcontact" data-lookup="employees" data-childrentable="employees" data-childrencolumn="personid">
																	<input type="text" data-childfld="child" data-attribute="billcontact.fullname" data-input="readonly">
																</td>
															</tr>
															<tr>
																<td>Shipping Contact
																</td>
																<td><input type="text" data-childfld="child" data-attribute="shipcontact" data-lookup="employees"  data-childrentable="employees"  data-childrencolumn="personid">
																	<input type="text" data-childfld="child" data-attribute="shipcontact.fullname" data-input="readonly">
																</td>
															</tr>
														</table>
													</div>
													<div class="col-md-2">
														<table class="tbldata">
															<tr>
																<td>Default Billing</td>
																<td><input type="checkbox" data-childfld="child" data-attribute="billtodefault">	</td>
															</tr>
															<tr>
																<td>Shipping Default </td>
																<td><input type="checkbox" data-childfld="child"  data-attribute="shiptodefault">	</td>
															</tr>
														</table>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="tablelist">
													<table id="mxtable_siteAddress_child" class="display" data-subform="subform-company-siteaddress" data-link="mxtable_site_child">
													</table>
												</div>
											</div>
											<div id="mxtable_siteAddress_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tbody>
																<tr>
																	<td>Site Code</td>
																	<td><input type="text" size=6 data-childfld="child" data-attribute="sitecode"   data-childrentable="site" data-childrencolumn="sitecode" data-key="value" data-input="readonly"> 
																		<input type="text" data-childfld="child" data-attribute="sitecode.description"  data-input="readonly"> 
																	</td>
																</tr>
																<tr>
																	<td>Site Address Code</td>
																	<td><input type="text" size=6 data-childfld="child" data-attribute="siteaddresscode"  data-childrentable="address" data-childrencolumn="addresscode" data-key="value" data-lookup="address"> 
																		<input type="text" data-childfld="child" data-attribute="siteaddresscode.address"  data-input="readonly"> 
																	</td>
																</tr>
														</table>
													</div>
													<div class="col-md-8">
														<table class="tableDetailRow">
															<tr>
																<td>Default Billing </td>
																<td><input type="checkbox" data-childfld="child" data-attribute="billtodefault"> </td>
															</tr>
															<tr>
																<td>Shipping Default</td>
																<td><input type="checkbox" data-childfld="child" data-attribute="shiptodefault" > </td>
															</tr>
														</table>
													</div>
												</div>
												<div class="row eatfix">
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Shipping Contact</td>
																<td><input type="text" size=6 data-childfld="child" data-attribute="shipcontact" data-lookup="employees"   data-childrentable="employees" data-childrencolumn="personid"> 
																	<input type="text" data-childfld="child" data-attribute="shipcontact.fullname"  data-input="readonly"  >
																</td>
															</tr>
															<tr>
																<td>Primary Phone</td>
																<td> <input type="text" data-childfld="child" data-attribute="shipcontact.primaryphone"  data-input="readonly"></td>
															</tr>
															<td>PIN BB </td>
															<td> <input type="text" data-childfld="child" data-attribute="shipcontact.blackberrypin"  data-input="readonly" > </td>
															<tr>
																<td>Whatsapp</td>
																<td> <input type="text" data-childfld="child" data-attribute="shipcontact.wanum"  data-input="readonly" > </td>
															</tr>
															<tr>
																<td>Line ID</td>
																<td> <input type="text" data-childfld="child" data-attribute="shipcontact.lineid"  data-input="readonly" > </td>
															</tr>
															<tr>
																<td></td>
																<td></td>
															</tr>
														</table>
													</div>
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td>Billing Contact</td>
																<td><input type="text" size=6 data-childfld="child" data-attribute="billcontact"  data-childrentable="employees" data-childrencolumn="personid" data-lookup="employees" > 
																	<input type="text" data-childfld="child" data-attribute="billcontact.fullname"  data-input="readonly"  >
																</td>
															</tr>
															<tr>
																<td>Primary Phone</td>
																<td> <input type="text" data-childfld="child" data-attribute="billcontact.primaryphone"  data-input="readonly"></td>
															</tr>
															<td>PIN BB </td>
															<td> <input type="text" data-childfld="child" data-attribute="billcontact.blackberrypin"  data-input="readonly" > </td>
															<tr>
																<td>Whatsapp</td>
																<td> <input type="text" data-childfld="child" data-attribute="billcontact.wanum"  data-input="readonly" > </td>
															</tr>
															<tr>
																<td>Line ID</td>
																<td> <input type="text" data-childfld="child" data-attribute="billcontact.lineid"  data-input="readonly" > </td>
															</tr>
															<tr>
																<td></td>
																<td></td>
															</tr>
														</table>
													</div>
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tbody>
																<tr>
																	<td>Entered By</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterby" class="readonly"></td>
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td><input type="text" data-childfld="child"  data-attribute="enterdate" data-type="date" class="readonly"></td>
																</tr>
																<tr>
																	<td>Changed By</td>
																	<td><input type="text" data-childfld="child"  data-attribute="changeby"  class="readonly"></td>
																</tr>
																<tr>
																	<td>Changed Date</td>
																	<td><input type="text" data-childfld="child" data-attribute="changedate" data-type="date" class="readonly" ></td>
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
	</body>
</html>