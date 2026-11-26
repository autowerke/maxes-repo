<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="salesquotation" data-appkeynum = "salesquotenum">
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
								<li class="maxes02_2"><a href="#maxes02_1_3">Items & Services</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Term of Conditions</a></li>
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
													<table id="mxtable_salesquotation" class="display"></table>
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
										<div class="col-md-12">
											<div class="col-md-2">
												<div id="qrcoderegion">
													<div id='qrcode'></div>
												</div>
											</div>
											<div class="col-md-10">
												<div class="page-header">
													<table class="tbldata">
														<tr>
															<td>Sales Quotation Num</td>
															<td><input type="text" data-attribute="salesquotenum" data-input="required" data-key="value"></td>
														</tr>
														<tr>
															<td>Description</td>
															<td><input type="text" data-attribute="description"></td>
														</tr>
														<tr>
															<td>Customer</td>
															<td><input type="text" data-attribute="customercode" data-input="required" data-lookup="customer|orgcode" data-lookupaction="y" data-childrentable="customer" data-childrencolumn="customercode"></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Detail</td>
												</tr>
											</table>
											<div class="row eatfix">
												<table class="tbldata">
													<tr>
														<td>Status</td>
														<td><input type="text" data-attribute="status" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Status Date</td>
														<td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Company</td>
														<td><input type="text" data-attribute="orgcode" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Site</td>
														<td><input type="text" data-attribute="sitecode" data-input="readonly"></td>
													</tr>
													<tr>
														<td>Delivery Status</td>
														<td><input type="text" data-attribute="sentstatus" data-input="readonly"></td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-8">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Price</td>
												</tr>
											</table>
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Total before Tax
															</td>
															<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotal">
															</td>
														</tr>
														<tr>
															<td>Tax
															</td>
															<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Total 
															</td>
															<td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalprice">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Date Time</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Booking Date</td>
													<td><input type="text" data-type="date" data-attribute="bookingdate"></td>
												</tr>
												<tr>
													<td>Order Date</td>
													<td><input type="text" data-type="date" data-attribute="orderdate" data-input="required"></td>
												</tr>
												<tr>
													<td>Estimated Received Date</td>
													<td><input type="text" data-type="date" data-attribute="expecteddeldate"></td>
												</tr>
												<tr>
													<td>Received Date</td>
													<td><input type="text" data-type="date" data-attribute="deliverydate"></td>
												</tr>
												<tr>
													<td>Warranty Date s/d</td>
													<td><input type="text" data-type="date" data-attribute="warrantydate"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Customer Detail</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Contact
													</td>
													<td><input type="text" data-attribute="contactname" data-lookup="customercontact|customercode" data-childrentable="customercontact" data-childrencolumn="contactname">
													</td>
												</tr>
												<tr>
													<td>Full Name
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.contactname">
													</td>
												</tr>
												<tr>
													<td>Title
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.function">
													</td>
												</tr>
												<tr>
													<td>Tel.
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.phone">
													</td>
												</tr>
												</tr>
												<tr>
													<td>Email
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.email">
													</td>
												</tr>
												<tr>
													<td>Whatsapp
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.wanum">
													</td>
												</tr>
												<tr>
													<td>Address
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="contactname.address">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl2">Sales Representative</td>
												</tr>
											</table>
											<table class="tbldata">
												<tr>
													<td>Contact
													</td>
													<td><input type="text" data-attribute="employeesid" data-lookup="employees|orgcode" data-childrentable="employees" data-childrencolumn="employeesid">
													</td>
												</tr>
												<tr>
													<td>Full Name
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.fullname">
													</td>
												</tr>
												<tr>
													<td>Title
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.title">
													</td>
												</tr>
												<tr>
													<td>Departemen
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.department">
													</td>
												</tr>
												<tr>
													<td>Tel.
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.primaryphone">
													</td>
												</tr>
												<tr>
													<td>Email
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.primaryemail">
													</td>
												</tr>
												<tr>
													<td>Whatsapp
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.wanum">
													</td>
												</tr>
												<tr>
													<td>Address
													</td>
													<td><input type="text" data-childfld="child" data-input="readonly" data-attribute="employeesid.address">
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
														<td>Sales Quotation Number</td>
														<td><input type="text" data-attribute="salesquotenum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
													</tr>
													<tr>
														<td>Customer</td>
														<td><input type="text" data-attribute="customercode" data-input="required" data-lookup="customer|orgcode" data-lookupaction="y"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Price</td>
											</tr>
										</table>
										<div class="row eatfix col-md-12">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Total</td>
														<td><input type="text" data-type="decimal" data-attribute="totalprice" data-input="readonly"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Items / Services
												</td>
											</tr>
										</table>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addSalesQuotationLine"></div>
                                            <div class="addrow" id="adjustMargin"></div>
										</div>
									</div>
									<div id="subform-salesquotationline" class="subform" data-child="salesquotationline" data-table="mxtable_salesquotationline_child">
										<div class="row eatfix">
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Line
														</td>
														<td><input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-key="value" data-input="required"  data-type="integer" data-key="value">
														</td>
													</tr>
													<tr>
														<td>Line Type
														</td>
														<td><input type="text" data-childfld="child" data-attribute="linetype" data-input="required" data-lookup="linetype" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Item
														</td>
														<td><input type="text" data-childfld="child" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="item|orgcode" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Service Item
														</td>
														<td><input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Description </td>
														<td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description">
														</td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													<tr>
														<td>Quantity
														</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="sellqty" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Measurement Unit
														</td>
														<td><input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
														</td>
													</tr>
													<tr>
														<td>Unit Price
														</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice">
														</td>
													</tr>
													<tr>
														<td>Line  Price
														</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="lineprice">
														</td>
													</tr>
													<tr>
														<td>Tax
														</td>
														<td><input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax" data-lookupaction="y">
														</td>
													</tr>
													<tr>
														<td>Sales Tax</td>
														<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="taxprice"></td>
													</tr>
												</table>
											</div>
											<div class="col-md-4">
												<table class="tbldata">
													
													<tr>
														<td>Entered By</td>
														<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
													</tr>
													<tr>
														<td>Entered Date</td>
														<td><input type="text" data-childfld="child" data-type="date" data-attribute="enterdate" data-input="readonly"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_salesquotationline_child" class="display" data-subform="subform-salesquotationline"></table>
												<div id="mxtable_salesquotationline_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Line
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-key="value" data-input="required"  data-type="integer" data-key="value">
																	</td>
																</tr>
																<tr>
																	<td>Line Type
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="linetype" data-input="required" data-lookup="linetype" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Item
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="itemsellable|orgcode" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Service Item
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Description </td>
																	<td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description">
																	</td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Quantity
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="sellqty" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Measurement Unit
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit" >
																	</td>
																</tr>
																<tr>
																	<td>Unit Price
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitprice">
																	</td>
																</tr>
																<tr>
																	<td>Total Price
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="lineprice">
																	</td>
																</tr>
																<tr>
																	<td>Tax
																	</td>
																	<td><input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax" data-lookupaction="y">
																	</td>
																</tr>
																<tr>
																	<td>Sales Tax</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-attribute="taxprice"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Unit Cost
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly"  data-attribute="unitcost">
																	</td>
																</tr>
																<tr>
																	<td>Line Cost
																	</td>
																	<td><input type="text" data-childfld="child" data-type="decimal" data-input="readonly" data-attribute="linecost">
																	</td>
																</tr>
																<tr>
																	<td>Entered By</td>
																	<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
																</tr>
																<tr>
																	<td>Entered Date</td>
																	<td><input type="text" data-childfld="child" data-type="date" data-attribute="enterdate" data-input="readonly"></td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12" id="title_ceb">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl3">Cost Estimation Builder for Presales
												</td>
											</tr>
										</table>
									</div>



									<!-- COST ESTIMATION BUILDER --> 
 								<div class="subtab" id="tab_ceb">
                                    <ul class="maxes02">
                                        <li class="maxes02_1"><a href="#maxes_planmat">Materials</a></li>
                                        <li class="maxes02_2"><a href="#maxes_planserv">Services</a></li>
                                        <li class="maxes02_2"><a href="#maxes_planlab">Labors</a></li>
                                        <li class="maxes02_2"><a href="#maxes_plantools">Tools</a></li>
                                    </ul>
                                </div>
                                <div class="maxes04sub" id="content_ceb">
                                    <div id="maxes_planmat" class="maxes02_1">
                                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                            Materials Estimation 
                                        </div>
                                        <div class="row eatfix">
                                            <div class="col-md-4">
                                                <div class="addrow" id="addsqestmaterial"></div>
                                            </div>
                                        </div>
                                        <div class="row eatfix">
                                            <div class="col-md-12">
                                                <div id="subform-sqestmaterial" class="subform" data-child="sqestmaterial" data-table="mxtable_sqestmaterial_child">
                                                    <div class="row eatfix">
                                                        <div class="col-md-4">
                                                            <table class="tbldata">
                                                                <tr>
                                                                    <td>For SQ Line Num.
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-input="required" data-lookup="salesquotationline|salesquotenum">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Item</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="invbalance|orgcode" data-lookupaction="y" data-input="required">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Description </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.description">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Warehouse
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>From Rack/Bin</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <table class="tbldata">
                                                                <tr>
                                                                    <td>Condition Type
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="invcost|itemnum,warehouse">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Quantity
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Unit Cost
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="required">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Total Cost
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="required">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Entered By
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Entered Date
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly" data-type="date">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <table class="tbldata">
                                                                
                                                                <tr>
                                                                    <td>Debit Account Code
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="chartofaccount|orgcode">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Credit Account Code
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="chartofaccount|orgcode">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Remarks
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="remarks">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row eatfix">
                                                    <div class="col-md-12">
                                                        <div class="tablelist">
                                                            <table id="mxtable_sqestmaterial_child" class="display" data-subform="subform-sqestmaterial"></table>
                                                            <div id="mxtable_sqestmaterial_child_detail" class="mxtable_detail">
                                                                <div class="row eatfix">
                                                                    <div class="col-md-4">
                                                                        <table class="tableDetailRow">
                                                                            <tr>
                                                                                <td>For SQ Line Num.
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-input="required"  data-lookup="salesquotationline|salesquotenum">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Item</td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="itemnum" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="invbalance|orgcode" data-lookupaction="y" data-input="required">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Description </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-input="readonly" data-attribute="itemnum.description">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Warehouse
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>From Rack/Bin</td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly">
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <table class="tableDetailRow">
                                                                            <tr>
                                                                                <td>Quantity
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" data-input="required">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Condition Type
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="conditiontype" data-lookup="itemcondition|itemnum,warehouse">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Unit Cost
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="unitcost" data-input="required" data-type="decimal">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Total Cost
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="linecost" data-input="required" data-type="decimal">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Entered By
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Entered Date
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly" data-type="date">
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <table class="tableDetailRow">
                                                                            
                                                                            <tr>
                                                                                <td>Debit Account Code
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="chartofaccount|orgcode">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Credit Account Code
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="chartofaccount|orgcode">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Remarks
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="remarks">
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
                                    <div id="maxes_planserv" class="maxes03">
                                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                            Service Estimation
                                        </div>
                                        <div class="row eatfix">
                                            <div class="col-md-4">
                                                <div class="addrow" id="addsqestservice"></div>
                                            </div>
                                        </div>
                                        <div class="row eatfix">
											 <div class="col-md-12">
												<div id="subform-sqestservice" class="subform" data-child="sqestservice" data-table="mxtable_sqestservice_child">
													<div class="row eatfix">
														<div class="col-md-12">
															<div class="maxes_sct_tbl2"> Additional Vendor Service </div>
															<table class="tbldata">
																<tr>
																	<td>For SQ Line Num.
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-input="required"  data-lookup="salesquotationline|salesquotenum">
																	</td>
																</tr>
																<tr>
																	<td>Service Item
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" data-childrentable="serviceitems" data-childrencolumn="serviceitemnum">
																	</td>
																</tr>

																<tr>
																	<td>Description
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="description" data-input="required" data-crossvalue="serviceitemnum.description">
																	</td>
																</tr>
																<tr>
																	<td>Quantity
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
																	</td>
																</tr>
																<tr>
																	<td>Order Unit
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit" data-crossvalue="serviceitemnum.serviceunit">
																	</td>
																</tr>
																<tr>
																	<td>Unit Cost
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-crossvalue="serviceitemnum.stdcost">
																	</td>
																</tr>
																<tr>
																	<td>Line Cost
																	</td>
																	<td>
																		<input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
																	</td>
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
                                                    <table id="mxtable_sqestservice_child" class="display" data-subform="subform-sqestservice"></table>
                                                    <div id="mxtable_sqestservice_child_detail" class="mxtable_detail">
                                                        <div class="row eatfix">

                                                            <div class="col-md-6">
                                                                <div class="maxes_sct_tbl2"> Additional Vendor Service </div>
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>For SQ Line Num.
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-input="required"  data-lookup="salesquotationline|salesquotenum">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Service Item
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" data-childrentable="serviceitems" data-childrencolumn="serviceitemnum">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Description
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="description" data-input="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Quantity
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Order Unit
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Unit Cost
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Line Cost
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
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
                                    <div id="maxes_planlab" class="maxes03">
                                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                            Labor Estimation
                                        </div>
                                        <div class="row eatfix">
                                            <div class="col-md-4">
                                                <div class="addrow" id="addsqestlabor"></div>
                                            </div>
                                        </div>
                                        <div class="row eatfix">
                                            <div class="col-md-12">
                                                <div id="subform-sqestlabor" class="subform" data-child="sqestlabor" data-table="mxtable_sqestlabor_child">
                                                     <div class="row eatfix">
                                                        <div class="col-md-4">
                                                            <table class="tbldata  ">
                                                                <tr>
                                                                    <td colspan=2>
                                                                        <div class="maxes_sct_tbl3">Work Detail</div>
                                                                    </td>
                                                                </tr>
																<tr>
                                                                        <td>For SQ Line Num.
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-input="required"  data-lookup="salesquotationline|salesquotenum">
                                                                        </td>
                                                                    </tr>
                                                                <tr>
                                                                    <td>Team
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="team" data-lookupaction="y">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Person ID
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="personid" data-lookup="employees" data-lookupaction="y">
                                                                    </td>
                                                                </tr>
                                                                

                                                            </table>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <table class="tbldata  ">
                                                                <tr>
                                                                    <td colspan=2>
                                                                        <div class="maxes_sct_tbl3">Work Hours</div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Start Date</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="startdate" data-type="date">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Finish Date</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="enddate" data-type="date">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Regular Hours</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="reghours" data-type="decimal">
                                                                    </td>
                                                                </tr>
                                                               
                                                            </table>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <table class="tbldata  ">
                                                                <tr>
                                                                    <td colspan=2>
                                                                        <div class="maxes_sct_tbl3">Labor Cost</div>
                                                                    </td>
                                                                </tr>
                                                               
                                                                <tr>
                                                                    <td>Unit Cost
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Line Cost
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly" data-input="readonly">
                                                                    </td>
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
													<table id="mxtable_sqestlabor_child" class="display" data-subform="subform-sqestlabor"></table>
													<div id="mxtable_sqestlabor_child_detail" class="mxtable_detail">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Work Detail</div>
																		</td>
																	</tr>
																	<tr>
																			<td>For SQ Line Num.
																			</td>
																			<td>
																				<input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-input="required" data-lookup="salesquotationline|salesquotenum">
																			</td>
																		</tr>
																	<tr>
																		<td>Team
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="teamnum" data-lookup="teamnum" data-lookupaction="y">
																		</td>
																	</tr>
																	<tr>
																		<td>Person ID
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="personid" data-lookup="employees" data-lookupaction="y">
																		</td>
																	</tr>
																	
																</table>
															</div>
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Work Hours</div>
																		</td>
																	</tr>
																	<tr>
																		<td>Start Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="startdate" data-type="date">
																		</td>
																	</tr>
																	<tr>
																		<td>Finish Date</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="enddate" data-type="date">
																		</td>
																	</tr>
																	<tr>
																		<td>Regular Hours</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="reghours" data-type="decimal">
																		</td>
																	</tr>
																
																</table>
															</div>
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td colspan=2>
																			<div class="maxes_sct_tbl3">Labor Cost</div>
																		</td>
																	</tr>
																	
																	<tr>
																		<td>Unit Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
																		</td>
																	</tr>
																	<tr>
																		<td>Line Cost
																		</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly" data-input="readonly">
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
                                    <div id="maxes_plantools" class="maxes03">
                                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                            Tools Material
                                        </div>
                                        <div class="row eatfix">
                                            <div class="col-md-4">
                                                <div class="addrow" id="addsqesttool"></div>
                                            </div>
                                        </div>
                                        <div class="row eatfix">
                                            <div id="subform-sqesttool" class="subform" data-child="sqesttool" data-table="mxtable_sqesttool_child">
                                                <div class="row eatfix">
                                                    <div class="col-md-6">
                                                        <table class="tbldata">
                                                            <tr>
                                                                <td>For SQ Line Num.
                                                                </td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="salesquotationlinenum" data-input="required"  data-lookup="salesquotationline|salesquotenum">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tools Number
                                                                </td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="toolnum" data-lookup='tools' data-childrentable="tools" data-childrencolumn="toolnum" data-input="required">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Description
                                                                </td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="description" data-crossvalue="toolnum.description">
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>From Location </td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="locnum" data-crossvalue="toolnum.locnum">
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <table class="tbldata">
                                                            <tr>
                                                                <td>Quantity
                                                                </td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Order Unit
                                                                </td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit" data-crossvalue="toolnum.measureunit">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Unit Cost
                                                                </td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-crossvalue="toolnum.unitcost">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Line Cost
                                                                </td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal">
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
                                                    <table id="mxtable_sqesttool_child" class="display" data-subform="subform-sqesttool"></table>
                                                    <div id="mxtable_sqesttool_child_detail" class="mxtable_detail">
                                                        <div class="row eatfix">
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Line Num.
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="salesquotationlinenum"  data-lookup="salesquotationline|salesquotenum">
                                                                        </td>
                                                                    </tr>
                                                                    
                                                                    <tr>
                                                                        <td>Tool Number
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="toolnum" data-lookup="tools" data-childrentable="tools" data-childrencolumn="toolnum">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Description </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="description" data-crossvalue="toolnum.description">
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>From Location </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="locnum" data-crossvalue="toolnum.locnum">
                                                                        </td>
                                                                    </tr>

                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Quantity
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Order Unit
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="orderunit" data-lookup="measureunit">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Unit Cost
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Total Cost </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="decimal">
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


									<!-- END COST ESTIMATION BUILDER --> 

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
														<td>Sales Quotation Number</td>
														<td><input type="text" data-attribute="salesquotenum" data-type="upper" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description"></td>
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
									<div id="subform-salesquotationterm" class="subform" data-child="salesquotationterm" data-table="mxtable_salesquotationterm_child">
										<div class="row eatfix">
											<div class="col-md-12">
												<table class="tbldata">
													<tr>
														<td>Num.</td>
														<td><input type="text" data-childfld="child" data-attribute="termlinenum" data-input="required" ></td>
													</tr>
													<tr>
														<td>Code</td>
														<td><input type="text" data-childfld="child" data-attribute="termcode" data-lookup="term|orgcode" data-childrentable="term" data-childrencolumn="termcode">
														</td>
													</tr>
													<tr>
														<td>Remarks</td>
														<td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="termcode.description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_salesquotationterm_child" class="display" data-subform="subform-salesquotationterm"></table>
												<div id="mxtable_salesquotationterm_child_detail" class="mxtable_detail">
													<div class="col-md-4">
														<table class="tableDetailRow">
															<tr>
																<td colspan=2>
																	<div class="maxes_sct_tbl3">Term of Payments</div>
																</td>
															</tr>
															<tr>
																<td>Num.</td>
																<td><input type="text" data-childfld="child" data-attribute="termlinenum"   readonly></td>
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
	</body>
</html>