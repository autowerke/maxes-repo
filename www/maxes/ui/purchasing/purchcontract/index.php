<!DOCTYPE html>
<html>
<title></title>

<head>
	<?php
	include("../../../applications/ui/ui.php");
	?>
</head>

<body id="page-top" xboset="purchcontract" data-appkeynum="contractnum">
	<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Contract</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_3">Contract Lines</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_4">Terms and Conditions</a></li>
						</ul>
						<div class="maxes04 shadow">
							
							<!-- TAB 1: LIST VIEW -->
							<div id="maxes02_1_1" class="tab maxes02_1">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_purchcontract" class="display"></table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- TAB 2: MAIN CONTRACT HEADER -->
							<div id="maxes02_1_2" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								
								<!-- Header Identifiers -->
								<div class="row eatfix">
									<div class="col-md-2">
										<div id="qrcoderegion">
											<div id='qrcode'></div>
										</div>
									</div>
									<div class="col-md-10">
										<div class="page-header">
											<table class="tbldata">
												<tr>
													<td>Contract Num</td>
													<td>
														<input type="text" data-attribute="contractnum" data-type="upper" data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description</td>
													<td>
														<input type="text" data-attribute="description" style="width:450px;">
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
												<td class="maxes_sct_tbl4">Contract Details</td>
											</tr>
										</table>
									</div>
								</div>

								<div class="row eatfix">
									<!-- Col 1: Vendor & General Info -->
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Vendor Information</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Vendor</td>
												<td>
													<input type="text" data-attribute="vendorcode" data-input="required" data-lookup="vendor" data-childrentable="vendor" data-childrencolumn='vendorcode' data-lookupaction="y"> 
												</td>
											</tr>
											<tr>
												<td>Name</td>
												<td>
													<input type="text" data-childfld="child" data-attribute="vendorcode.name" data-input="readonly"> 
												</td>
											</tr>
											<tr>
												<td>Type</td>
												<td>
													<input type="text" data-attribute="contracttype" data-lookup="contracttype" data-input="required" placeholder="PRICE / BLANKET"> 
												</td>
											</tr>
											<tr>
												<td>Buyer</td>
												<td>
													<input type="text" data-attribute="buyer" data-lookup="employees"> 
												</td>
											</tr>
											<tr>
												<td>Ref. RFQ</td>
												<td>
													<input type="text" data-attribute="rfqnum" data-input="readonly"> 
												</td>
											</tr>
											<tr>
												<td>Org / Site</td>
												<td>
													<input type="text" data-attribute="orgcode" data-input="readonly" size="5"> / 
													<input type="text" data-attribute="sitecode" data-input="readonly" size="5">
												</td>
											</tr>
										</table>
									</div>

									<!-- Col 2: Dates & Status -->
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Validity Period</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Status</td>
												<td>
													<input type="text" data-attribute="status" data-input="readonly"> 
												</td>
											</tr>
											<tr>
												<td>Status Date</td>
												<td>
													<input type="text" data-type="date" data-attribute="statusdate" data-input="readonly"> 
												</td>
											</tr>
											<tr>
												<td>Start Date</td>
												<td>
													<input type="text" data-type="date" data-attribute="startdate" data-input="required"> 
												</td>
											</tr>
											<tr>
												<td>End Date</td>
												<td>
													<input type="text" data-type="date" data-attribute="enddate" data-input="required"> 
												</td>
											</tr>
											<tr>
												<td>Revision</td>
												<td>
													<input type="text" data-attribute="revisionnum" data-input="readonly"> 
												</td>
											</tr>
											<tr>
												<td>Entered By</td>
												<td>
													<input type="text" data-attribute="enterby" data-input="readonly"> 
												</td>
											</tr>
										</table>
									</div>

									<!-- Col 3: Financials & Visualization -->
									<div class="col-md-4">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Values & Execution</td>
											</tr>
										</table>
										<table class="tbldata">
											<tr>
												<td>Currency</td>
												<td>
													<input type="text" data-attribute="currencycode" data-lookup="currency"> 
												</td>
											</tr>
											<tr>
												<td>Payment Terms</td>
												<td>
													<input type="text" data-attribute="paymentterms"> 
												</td>
											</tr>
											<tr>
												<td>Total Limit (Cost)</td>
												<td>
													<input type="text" data-type="decimal" data-attribute="totalcost" style="font-weight:bold;"> 
												</td>
											</tr>
											<tr>
												<td>Amount Spent</td>
												<td>
													<input type="text" data-type="decimal" data-attribute="amountspent" data-input="readonly" style="color:blue;"> 
												</td>
											</tr>
										</table>
										
										<!-- ECHART CONTAINER FOR BLUEPRINT REQUIREMENT -->
										<br>
										<div style="border:1px solid #ddd; padding:5px; background:#f9f9f9;">
											<small><b>Contract Utilization</b></small>
											<div id="echart_contract_execution" style="min-width: 300px; height: 200px;"></div>
										</div>

									</div>
								</div>
							</div>
							
							<!-- TAB 3: CONTRACT LINES -->
							<div id="maxes02_1_3" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<div class="page-header">
											<table class="tbldata">
												<tr>
													<td>Contract Num</td>
													<td>
														<input type="text" data-attribute="contractnum" data-type="upper" data-input="required" data-key="value">
													</td>
												</tr>
												<tr>
													<td>Description</td>
													<td>
														<input type="text" data-attribute="description" style="width:450px;">
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
								
								<div class="row eatfix">
									<div class="maxes_sct_tbl4">Contract Lines (Price Agreement)</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-4">
										<div class="addrow" id="addContractLines"></div>
									</div>
								</div>

								<!-- SUBFORM FOR CONTRACT LINES -->
								<div class="col-md-12">
									<div id="subform-contractlines" class="subform" data-table="mxtable_contractline_child" data-child="contractline">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Item Detail</td>
													</tr>
													<tr>
														<td>Line Num.</td>
														<td>
															<input type="text" size=3 data-childfld="child" data-attribute="contractlinenum" data-key="value" data-input="required" data-type="integer">
														</td>
													</tr>
													<tr>
														<td>Line Type</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype" placeholder="ITEM / SERVICE" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Item Num</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="item" data-lookupaction="y" data-childrentable="item" data-childrencolumn="itemnum">
														</td>
													</tr>
                                                    <tr>
														<td>Service Item</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" data-childrentable="item" data-childrencolumn="itemnum">
														</td>
													</tr>

													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Pricing & Unit</td>
													</tr>
													<tr>
														<td>Order Unit</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit" data-crossvalue="itemnum.orderunit">
														</td>
													</tr>
													<tr>
														<td>Agreed Unit Cost</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost" data-input="required">
														</td>
													</tr>
                                                   
													<tr>
														<td>Min Order Qty</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="minqty">
														</td>
													</tr>
													<tr>
														<td>Tax Code</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax">
														</td>
													</tr>
                                                   
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Additional Info</td>
													</tr>
													<tr>
														<td>Remark</td>
														<td>
															<textarea data-childfld="child" data-attribute="remark" rows="3"></textarea>
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>

								<!-- TABLE LIST FOR LINES -->
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_contractline_child" class="display" data-subform="subform-contractlines"></table>
												<!-- DETAIL VIEW FOR SELECTED ROW -->
												<div id="mxtable_contractline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2><div class="maxes_sct_tbl3">Line Detail</div></td>
																</tr>
																<tr>
                                                                    <td>Line Type</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype" placeholder="ITEM / SERVICE">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Item Num</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="item" data-lookupaction="y" data-childrentable="item" data-childrencolumn="itemnum">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Service Item</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" data-childrentable="item" data-childrencolumn="itemnum">
                                                                    </td>
                                                                </tr>
																<tr>
																	<td>Description</td>
																	<td><input type="text" data-childfld="child" data-attribute="description" readonly></td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2><div class="maxes_sct_tbl3">Cost & Terms</div></td>
																</tr>
																<tr>
																	<td>Unit Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal"></td>
																</tr>
                                                                
																<tr>
																	<td>Min Qty</td>
																	<td><input type="text" data-childfld="child" data-attribute="minqty"></td>
																</tr>
                                                                <tr>
                                                                    <td>Tax Code</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax">
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
								<div class="row eatfix">
									<div class="maxes_sct_tbl4">Pricing Scales</div>
								</div>
								
								<div class="row eatfix">
									<div class="col-md-6">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Pricing Scale <span id="mxtable_contractprice_child_title"></span>
												</td>
											</tr>
										</table>
										<div class="addrow" id="addPricingScales"></div>
									</div>
								</div>
								<!-- SUBFORM FOR CONTRACT PRICIING SCALE -->
								<div class="col-md-12">
									<div id="subform-contractprice" class="subform" data-table="mxtable_contractprice_child" data-child="contractprice" data-link="mxtable_contractline_child" >
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Line Detail</td>
													</tr>
													<tr>
														<td>Line Num.</td>
														<td>
															<input type="text" size=3 data-childfld="child" data-attribute="contractpricelinenum" data-key="value" data-input="required" data-type="integer">
														</td>
													</tr>
													<tr>
														<td>For Line Num.</td>
														<td>
															<input type="text" size=3 data-childfld="child" data-attribute="contractlinenum"  data-input="required" data-type="integer">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td>
															<input type="text" data-childfld="child" data-attribute="description" >
														</td>
													</tr>

												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td colspan=2 class="maxes_sct_tbl2">Pricing & Unit</td>
													</tr>
													<tr>
														<td>Min. Order Qty</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="minqty" data-input="required">
														</td>
													</tr>
	
													<tr>
														<td>Max Order Qty</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="maxqty">
														</td>
													</tr>
													
													<tr>
														<td>Unit Cost</td>
														<td>
															<input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost">
														</td>
													</tr>
													
												</table>
											</div>
										</div>
									</div>
								</div>

								
								<!-- TABLE LIST FOR CONTRACT INES -->
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_contractprice_child" class="display" data-subform="subform-contractprice" data-link="mxtable_contractline_child" data-pk="contractlinenum" ></table>
												<!-- DETAIL VIEW FOR SELECTED ROW -->
												<div id="mxtable_contractprice_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2 class="maxes_sct_tbl2">Line Detail</td>
																</tr>
																<tr>
																	<td>Line Num.</td>
																	<td>
																		<input type="text" size=3 data-childfld="child" data-attribute="contractpricelinenum" data-key="value" data-input="required" data-type="integer">
																	</td>
																</tr>
																<tr>
																	<td>For Line Num.</td>
																	<td>
																		<input type="text" size=3 data-childfld="child" data-attribute="contractlinenum" data-input="required" data-type="integer">
																	</td>
																</tr>
																<tr>
																	<td>Description</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" >
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2 class="maxes_sct_tbl2">Pricing & Unit</td>
																</tr>
																<tr>
																	<td>Min. Order Qty</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="minqty" data-input="required">
																	</td>
																</tr>
				
																<tr>
																	<td>Max Order Qty</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="maxqty">
																	</td>
																</tr>
																
																<tr>
																	<td>Unit Cost</td>
																	<td>
																		<input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost">
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

							<!-- TAB 4: TERMS AND CONDITIONS -->
							<div id="maxes02_1_4" class="maxes03">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-6">
                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>Contract</td>
                                                    <td><input type="text" data-attribute="contractnum" data-type="upper" data-input="required" data-key="value"></td>
                                                </tr>
                                                <tr>
                                                    <td>Description</td>
                                                    <td><input type="text" data-attribute="description" style="width:450px;"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="maxes_sct_tbl">Term of Payment</div>

                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-4">
                                        <div class="addrow" id="addTerms"></div>
                                    </div>
                                </div>

                                <div id="subform-contractterm" class="subform" data-child="contractterm" data-table="mxtable_contractterm_child">
                                    <div class="row eatfix">
                                        <div class="col-md-8">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>Num.</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-key="value" data-type="integer"></td>
                                                </tr>
                                                <tr>
                                                    <td>Code</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Remarks</td>
                                                    <td><input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="tablelist">
                                            <table id="mxtable_contractterm_child" class="display" data-subform="subform-contractterm"></table>
                                            <div id="mxtable_contractterm_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Term & Conditions</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Num.</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="termlinenum" data-key="value" data-type="integer" readonly></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Code</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Remarks</td>
                                                                <td><input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row eatfix">
                                    <div class="maxes_sct_tbl">Term & Conditions</div>

                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-4">
                                        <div class="addrow" id="addTerms2"></div>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div id="subform-contractterm2" class="subform" data-child="contractterm2" data-table="mxtable_contractterm2_child">

                                        <div class="row eatfix">
                                            <div class="col-md-8">

                                                <table class="tbldata">
                                                    <tr>
                                                        <td>Num.</td>
                                                        <td><input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" data-key="value" data-type="integer"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Code</td>
                                                        <td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Remarks</td>
                                                        <td><input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description"></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="tablelist">
                                            <table id="mxtable_contractterm2_child" class="display" data-subform="subform-contractterm2"></table>
                                            <div id="mxtable_contractterm2_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Term & Conditions</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Num.</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="termlinenum" data-key="value" data-type="integer" readonly></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Code</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Remarks</td>
                                                                <td><input style="width:350px;" type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description"></td>
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