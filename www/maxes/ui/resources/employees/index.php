<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="employees" data-appkeynum = "personid">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Staff</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Qualifications</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Financial</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_5">Transactions</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_6">Legal Documents</a></li>
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
													<table id="mxtable_employees" class="display"></table>
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
														<td>Person ID
														</td>
														<td><input type="text" data-attribute="personid" data-type="upper" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>First Name
														</td>
														<td><input type="text" data-attribute="firstname" data-input="required">
														</td>
													</tr>
													<tr>
														<td>Last Name
														</td>
														<td><input type="text" data-attribute="lastname">
														</td>
													</tr>
													<tr>
														<td>Full Name
														</td>
														<td><input type="text" data-attribute="fullname" data-input="readonly">
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
													<td>Phone
													</td>
													<td><input type="text" data-attribute="primaryphone">
													</td>
												</tr>
												<tr>
													<td>Blackberry PIN
													</td>
													<td><input type="text" data-attribute="blackberrypin">
													</td>
												</tr>
												<tr>
													<td>WA
													</td>
													<td><input type="text" data-attribute="wanum">
													</td>
												</tr>
												<tr>
													<td>Line ID
													</td>
													<td><input type="text" data-attribute="lineid">
													</td>
												</tr>
												<tr>
													<td>Email
													</td>
													<td><input type="text" data-attribute="primaryemail">
													</td>
												</tr>
												<tr>
													<td>Email Password
													</td>
													<td><input type="password" data-attribute="emailpassword">
													</td>
												</tr>
												<tr>
													<td>Email Protocol
													</td>
													<td><input type="text" data-attribute="emailprotocol" data-lookup="emailprotocol">
													</td>
												</tr>
												<tr>
													<td>Email Server
													</td>
													<td><input type="text" data-attribute="emailserver">
													</td>
												</tr>
												<tr>
													<td>Email Port
													</td>
													<td><input type="text" data-attribute="emailport" data-lookup="emailport">
													</td>
												</tr>
												<tr>
													<td>Email Limit
													</td>
													<td><input type="text" data-attribute="emaillimit" data-type="integer">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Employee ID
													</td>
													<td><input type="text" data-attribute="employeeid">
													</td>
												</tr>
												<tr>
													<td>Date of Birth
													</td>
													<td><input type="text" data-type="date" data-attribute="dateofbirth">
													</td>
												</tr>
												<tr>
													<td>Recruited Date
													</td>
													<td><input type="text" data-type="date" data-attribute="hiredate">
													</td>
												</tr>
												<tr>
													<td>Termination Date
													</td>
													<td><input type="text" data-type="date" data-attribute="terminationdate">
													</td>
												</tr>
												<tr>
													<td>Title
													</td>
													<td><input type="text" data-attribute="title">
													</td>
												</tr>
												<tr>
													<td>Departemen
													</td>
													<td><input type="text" data-attribute="department">
													</td>
												</tr>
												<tr>
													<td>Section
													</td>
													<td><input type="text" data-attribute="section">
													</td>
												</tr>
												<tr>
													<td valign=top>
														Supervisor
													</td>
													<td><input type="text" data-attribute="supervisor" data-lookup="employees" data-childrentable="employees" data-childrencolumn="personid">
														<input type="text" data-childfld="child" data-attribute="supervisor.fullname" data-input="readonly">
													</td>
												</tr>
											</table>
											
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>ID/KTP
													</td>
													<td><input type="text" data-attribute="citizenid">
													</td>
												</tr>
												<tr>
													<td>Address
													</td>
													<td><input type="text" data-attribute="address">
													</td>
												</tr>
												<tr>
													<td>City
													</td>
													<td><input type="text" data-attribute="city">
													</td>
												</tr>
												<tr>
													<td>Province
													</td>
													<td><input type="text" data-attribute="stateprovince">
													</td>
												</tr>
												<tr>
													<td>Postal Code
													</td>
													<td><input type="text" data-attribute="zippostalcode">
													</td>
												</tr>
												<tr>
													<td>Country
													</td>
													<td><input type="text" data-attribute="country">
													</td>
												</tr>
												<tr>
													<td>Company
													</td>
													<td><input type="text" data-attribute="orgcode" data-lookup="organization" data-input="required" data-childrentable="organization" data-childrencolumn="orgcode">
													</td>
												</tr>
												<tr>
													<td>Site
													</td>
													<td><input type="text" data-attribute="sitecode" data-input="required" data-lookup="site">
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Person ID
														</td>
														<td><input type="text" data-attribute="personid" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>First Name
														</td>
														<td><input type="text" data-attribute="firstname">
														</td>
													</tr>
													<tr>
														<td>Last Name
														</td>
														<td><input type="text" data-attribute="lastname">
														</td>
													</tr>
													<tr>
														<td>Full Name
														</td>
														<td><input type="text" data-attribute="fullname" data-input="readonly">
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
													<td class="maxes_sct_tbl">Qualifications</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addPersonQualification"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div id="subform-employees-qualification" class="subform" data-child="empqual" data-table="mxtable_employeesqualification_child">
											<div class="col-md-6">
												<table class="tbldata">
													<tr>
														<td>Qualification</td>
														<td colspan=2><input type="text" data-childfld="child" data-attribute="qualification" data-childrentable="qualification" data-childrencolumn="qualification" data-input="required" data-key="value" data-lookup="qualifications"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td colspan=2><input type="text" data-childfld="child" data-attribute="qualification.description" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Skill</td>
														<td colspan=2><input type="text" data-childfld="child" data-attribute="qualification.skill" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Certificate</td>
														<td colspan=2><input type="text" data-childfld="child" data-attribute="qualification.certificate" data-input="readonly"></td>
													</tr>
												</table>
											</div>
											<div class="info"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_employeesqualification_child" class="display" data-subform="subform-employees-qualification">
												</table>
												<div id="mxtable_employeesqualification_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tbody>
																	<tr>
																		<td>Qualifications</td>
																		<td><input type="text" data-childfld="child" data-attribute="qualification" data-childrentable="qualification" data-childrencolumn="qualification" data-input="readonly" data-lookup="qualifications" data-key="value"> </td>
																	</tr>
																	<tr>
																		<td>Description</td>
																		<td><input type="text" data-childfld="child" data-attribute="qualification.description" data-input="readonly"> </td>
																	</tr>
																	<tr>
																		<td>Skill</td>
																		<td><input type="text" data-childfld="child" data-attribute="qualification.skill" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Certificate</td>
																		<td><input type="text" data-childfld="child" data-attribute="qualification.certificate" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Remarks</td>
																		<td><input type="text" data-childfld="child" data-attribute="remarks"></td>
																	</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tbody>
																	<tr>
																		<td>Entered By</td>
																		<td><input type="text" data-childfld="child" data-attribute="enterby" data-childrentable="mxsusers" data-childrencolumn="userid" data-lookup="mxsusers" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Entered Date</td>
																		<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Changed By</td>
																		<td><input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly"></td>
																	</tr>
																	<tr>
																		<td>Changed Date</td>
																		<td><input type="text" data-childfld="child" data-attribute="changedate" data-type="date" data-input="readonly"></td>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Person ID
														</td>
														<td><input type="text" data-attribute="personid" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>First Name
														</td>
														<td><input type="text" data-attribute="firstname">
														</td>
													</tr>
													<tr>
														<td>Last Name
														</td>
														<td><input type="text" data-attribute="lastname">
														</td>
													</tr>
													<tr>
														<td>Full Name
														</td>
														<td><input type="text" data-attribute="fullname" data-input="readonly">
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
													<td>NPWP
													</td>
													<td><input type="text" data-attribute="npwp">
													</td>
												</tr>
												<tr>
													<td>Bank Name
													</td>
													<td><input type="text" data-attribute="bankname" >
													</td>
												</tr>
												<tr>
													<td>Rekening Bank
													</td>
													<td><input type="text" data-attribute="bankaccount" >
													</td>
												</tr>
											</table>
											
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Allowance</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Code PTKP
													</td>
													<td><input type="text" data-attribute="taxexemptcode" data-lookup="tax" data-lookupaction="y">
													</td>
												</tr>
												<tr>
													<td>Penghasilan Tidak Kena Pajak (PTKP)
													</td>
													<td><input type="text" data-attribute="taxexemptcost" data-type="decimal">
													</td>
												</tr>
												<tr>
													<td>Account Code
													</td>
													<td><input type="text" data-attribute="glaccount" data-lookup="chartofaccount">
													</td>
												</tr>
												<tr>
													<td>Base Salary</td>
													<td><input type="text" data-attribute="basesalary" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Allowance</td>
													<td><input type="text" data-attribute="allowance" data-type="decimal"></td>
												</tr>
												<tr>
													<td>BPJS</td>
													<td><input type="text" data-attribute="bpjsallowance" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Commission</td>
													<td><input type="text" data-attribute="commision" data-type="decimal"></td>
												</tr>
												<td>Potongan BPJS</td>
												<td><input type="text" data-attribute="potbpjs" data-type="decimal"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Gross Revenue</td>
													<td><input type="text" data-attribute="grossincome" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Net Revenue</td>
													<td><input type="text" data-attribute="nettincome" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Gross Revenue Yearly</td>
													<td><input type="text" data-attribute="yearlygrossincomecom" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Net Revenue Yearly</td>
													<td><input type="text" data-attribute="yearlynettincome" data-input="readonly" data-type="decimal"></td>
												</tr>
											
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Tax Monthly</td>
													<td><input type="text" data-attribute="monthlytaxcharge" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Tax Yearly</td>
													<td><input type="text" data-attribute="yearlytaxcharge" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Tax Monthly non NPWP</td>
													<td><input type="text" data-attribute="monthlytaxcharge2" data-input="readonly" data-type="decimal"></td>
												</tr>
												<tr>
													<td>Tax Yearly non NPWP</td>
													<td><input type="text" data-attribute="yearlytaxcharge2" data-input="readonly" data-type="decimal"></td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addallowance"></div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_empallowance_child" class="display" data-child="empallowance">
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Allowance Lainnya</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Allowance
													</td>
													<td><input type="text" data-attribute="otherallowance" data-type="decimal" data-input="readonly">
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_empallowance2_child" class="display" data-child="myallowance">
													</table>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Person ID
														</td>
														<td><input type="text" data-attribute="personid" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>First Name
														</td>
														<td><input type="text" data-attribute="firstname">
														</td>
													</tr>
													<tr>
														<td>Last Name
														</td>
														<td><input type="text" data-attribute="lastname">
														</td>
													</tr>
													<tr>
														<td>Full Name
														</td>
														<td><input type="text" data-attribute="fullname" data-input="readonly">
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
													<td class="maxes_sct_tbl">Default Customers</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="tablelist">
											<table id="mxtable_customer_child" class="display" data-child="customer">
											</table>
										</div>
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Transactions</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="subtab col-md-12">
											<ul class="maxes02">
												<li class="maxes02_1"><a href="#maxes02_1_5_1">Item/Service Requisition</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_5_2">Purchase Order</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_5_3">Work Order</a></li>
												<li class="maxes02_2"><a href="#maxes02_1_5_4">Sales Order</a></li>
											</ul>
										</div>
										<div class="maxes04sub">
											<div id="maxes02_1_5_1" class="maxes02_1">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Item/Service Requisition
												</div>
												<div class="row eatfix">
													<div class="tablelist">
														<table id="mxtable_pr_child" class="display" data-child="pr">
														</table>
													</div>
												</div>
											</div>
											<div id="maxes02_1_5_2" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Purchase Order
												</div>
												<div class="row eatfix">
													<div class="tablelist">
														<table id="mxtable_po_child" class="display" data-child="po">
														</table>
													</div>
												</div>
											</div>
											<div id="maxes02_1_5_3" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													SPK/Work Order
												</div>
												<div class="row eatfix">
													<div class="tablelist">
														<table id="mxtable_wo_child" class="display" data-child="workorder">
														</table>
													</div>
												</div>
											</div>
											<div id="maxes02_1_5_4" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													Sales Order
												</div>
												<div class="row eatfix">
													<div class="tablelist">
														<table id="mxtable_so_child" class="display" data-child="so">
														</table>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Person ID</td>
														<td>
															<input type="text" data-attribute="personid" data-input="required" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Full Name</td>
														<td>
															<input type="text" data-attribute="fullname">
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
													<td class="maxes_sct_tbl">Legal Documents</td>
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