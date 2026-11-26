<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
			<style>
			.recommended-order-button {
				background-color: #FFFFFF;
				border: 1px solid #3498db; 
				color: #3498db; 
				padding: 10px 20px;
				border-radius: 5px;
				font-size: 14px;
				cursor: pointer;
				transition: background-color 0.3s ease; /* Efek transisi */
				font-family: sans-serif; 
				margin-top: 10px; 
			}

			.recommended-order-button:hover {
				background-color: #3498db;
				color: white;
			}

			.recommended-order-button i {
				margin-right: 5px; 
			}

			
			.ui-dialog .ui-dialog-titlebar-close {
			  display: none; 
			}
			</style>
	</head>
	<body id="page-top" xboset="item" data-appkeynum = "itemnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
						<button id="requestitemButton" class="general-button">
							<i class="fas fa-info"></i>Request Item Number
						</button>
						</div>
						<div class="maxes01">
							
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Item</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_3">Warehouse</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_4">Vendor/Supplier</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_5">Specification</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_6">Item Assembly</a></li>
								<li class="maxes02_2"><a href="#maxes02_1_7">Hazardz & Precautions</a></li>
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
													<table id="mxtable_item" class="display"></table>
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
										<div class="col-md-5">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Item Num.</td>
														<td><input type="text" data-attribute="itemnum" data-key="value" data-autofillobject="item" data-columns="itemnum,description,alias" data-input="required"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-attribute="description" data-autofillobject="item" data-columns = "description, stocktype, manufacturer, brand" ></td>
													</tr>
													<tr>
														<td>Alias</td>
														<td><input type="text" data-attribute="alias"></td>
													</tr>
												</table>
											</div>
										</div>
										<div class="col-md-4">
											
													<table class="tbldata">
														<tr>
															<td>Criticality
															</td>
															<td><input type="text" data-attribute="criticalityranking">
															</td>
														</tr>
														<tr>
															<td>Market Rarity
															</td>
															<td><input type="text" data-attribute="marketrarityranking">
															</td>
														</tr>
														<tr>
															<td>ABC Ranking
															</td>
															<td><input type="text" data-attribute="usageranking">
															</td>
														</tr>
													</table>
											
										</div>
									</div>
								
									
									
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Status
													</td>
													<td><input type="text" data-attribute="status" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Status Date
													</td>
													<td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly">
													</td>
												</tr>
												<tr>
													<td>Item Group
													</td>
													<td><input type="text" data-attribute="itemgroup" data-input="required" data-lookup="itemgroup">
													</td>
												</tr>
												<tr>
													<td>Sub Item Group
													</td>
													<td><input type="text" data-attribute="itemsubgroup"  data-lookup="itemsubgroup|itemgroup">
													</td>
												</tr>
												<tr>
													<td>Order Unit
													</td>
													<td><input type="text" data-attribute="orderunit" data-input="required" data-lookup="measureunit">
													</td>
												</tr>
												<tr>
													<td>Issue Unit
													</td>
													<td><input type="text" name="issueunit" data-attribute="issueunit" data-input="required" data-lookup="measureunit">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Manufacturer
													</td>
													<td><input type="text" data-attribute="manufacturer" data-type="upper">
													</td>
												</tr>
												<tr>
													<td>Brand
													</td>
													<td><input type="text" data-attribute="brand" data-type="upper">
													</td>
												</tr>
												<tr>
													<td>Specification Detail 
													</td>
													<td><textarea type="text" data-attribute="spectext" style="width:230px; height:100px;"> </textarea>
													</td>
												</tr>
												
												<tr>
													<td> Stock Type
													</td>
													<td><input type="text" data-attribute="stocktype" data-input="required" data-lookup="stocktype">
													</td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Remarks</td>
													<td><textarea type="text" data-attribute="notes" style="width:300px"></textarea></td>
												</tr>
												<tr>
													<td>Company</td>
													<td><input type="text" data-attribute="orgcode" data-input="readonly"></td>
												</tr>
												<tr>
													<td>Site</td>
													<td><input type="text" data-attribute="sitecode" data-input="readonly"></td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Item Conditions
													</td>
												</tr>
											</table>
											<div class="row eatfix">
											<div class="col-md-12">
											
													<div class="addrow" id="addItemConditions"></div>
												
											</div>
											
											</div>
											
											<div class="row eatfix">
												<div id="subforms-itemcondition" class="subform" data-table="mxtable_itemcondition_child" data-child="itemcondition">
													<div class="col-md-12">
														<table class="tbldata">
															<tr>
																<td>Condition Type
																</td>
																<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-key="value" data-childrentable="conditiontype" data-childrencolumn="conditiontype" data-lookup="conditiontype">
																</td>
															</tr>
															<tr>
																<td>Rate (%)</td>
																<td><input type="text" data-childfld="child" data-attribute="conditionrate" data-type="decimal" data-crossvalue="conditiontype.conditionrate">
																</td>
															</tr>
														</table>
													</div>
												</div>
											</div>
											<div class="col-md-12">
													<div class="page-header-list">
														<div class="tablelist">
															<table id="mxtable_itemcondition_child" class="display" data-subform="subforms-itemcondition">
															</table>
															<div id="mxtable_itemcondition_child_detail" class="mxtable_detail">
																<div class="row eatfix">
																	<div class="col-md-4">
																		<table class="tableDetailRow">
																			<tr>
																				<td colspan=2>
																					<div class="maxes_sct_tbl3">Details</div>
																				</td>
																			</tr>
																			<tr>
																				<td>Condition Type</td>
																				<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-childrentable="conditiontype" data-childrencolumn="conditiontype" data-input="readonly"></td>
																			</tr>
																			<td>Condition Rate (%)</td>
																			<td><input type="text" data-childfld="child" data-attribute="conditionrate" data-crossvalue="conditiontype.conditionrate" data-type="decimal"></td>
																			</tr>
																			<tr>
																				<td>Description</td>
																				<td><input type="text" data-childfld="child" data-attribute="description"></td>
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
									
										<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Barcode Info</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
									
										<div class="col-md-3">
											<div class="row eatfix">
												<center>
												<div class="col-md-12">
													<div id="barcoderegion">
														<svg id="barcode"></svg>
													</div>
													<div id="barcodebutton">
													</div>
												</div>
												</center>
											</div>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Barcode</td>
													<td><input type="text" data-attribute="barcode"></td>
												</tr>
												<tr>
													<td>Barcode Height</td>
													<td><input type="text" data-attribute="barcodeheight"></td>
												</tr>
												<tr>
													<td>Barcode Width</td>
													<td><input type="text" data-attribute="barcodewidth"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Barcode Format (upc, CODE128,..)</td>
													<td><input type="text" data-attribute="barcodeformat" data-lookup="barcodetype" data-lookupaction="y"></td>
												</tr>
												<tr>
													<td>Text Size</td>
													<td><input type="text" data-attribute="barcodefontsize"></td>
												</tr>
												<tr>
													<td>Last Character</td>
													<td><input type="text" data-attribute="barcodelastchar"></td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Barcode List</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_itembarcode_child" class="display" data-child="itembarcode">
												</table>
												<div id="mxtable_itembarcode_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-12">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Barcode</td>
																			<td><input type="text" data-childfld="child" data-attribute="barcode"></td>
																		</tr>
																		<tr>
																			<td>Barcode Height</td>
																			<td><input type="text" data-childfld="child" data-attribute="barcodeheight"></td>
																		</tr>
																		<tr>
																			<td>Barcode Width</td>
																			<td><input type="text" data-childfld="child" data-attribute="barcodewidth"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Barcode Format (upc, CODE128,..)</td>
																			<td><input type="text" data-childfld="child" data-attribute="barcodeformat" data-lookup="barcodetype" data-lookupaction="y"></td>
																		</tr>
																		<tr>
																			<td>Text Size</td>
																			<td><input type="text" data-childfld="child" data-attribute="barcodefontsize"></td>
																		</tr>
																		<tr>
																			<td>Last Character</td>
																			<td><input type="text" data-childfld="child" data-attribute="barcodelastchar"></td>
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
																			<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Waiting Approval? </td>
																			<td><input type="checkbox" data-childfld="child" data-attribute="await" ></td>
																		</tr>
																		<tr>
																			<td>Public Registered?</td>
																			<td><input type="checkbox" data-childfld="child" data-attribute="registered" ></td>
																		</tr>
																		<tr>
																			<td>Registered Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="regdate" data-type="date"></td>
																		</tr>
																		<tr>
																			<td>Expired?</td>
																			<td><input type="checkbox" data-childfld="child" data-attribute="expired" data-input="readonly" disabled></td>
																		</tr>
																		<tr>
																			<td>Expired Date</td>
																			<td><input type="text" data-childfld="child" data-attribute="expdate" data-type="date" data-input="readonly" disabled></td>
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
									
									
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Alternate Item
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
										
										<div class="row eatfix">
											<div class="addrow" id="addItemAlternate"></div>
										</div>
										</div>
									</div>
										
									<div class="row eatfix">
										<div id="subforms-itemalternate" class="subform" data-table="mxtable_itemalternate_child" data-child="itemalternate">
											<div class="col-md-12">
												<table class="tbldata">
													<tr>
														<td>Alternate Item
														</td>
														<td><input type="text" data-childfld="child" data-attribute="relateditem" data-key="value" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="item">
														</td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" data-childfld="child" data-attribute="relateditem.description" data-input="readonly">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_itemalternate_child" class="display" data-subform="subforms-itemalternate">
												</table>
												<div id="mxtable_itemalternate_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td colspan=2>
																		<div class="maxes_sct_tbl3">Detail</div>
																	</td>
																</tr>
																<tr>
																	<td>Alternate Item</td>
																	<td><input type="text" data-childfld="child" data-attribute="relateditem" data-childrentable="item" data-childrencolumn="relateditem" data-key="value"></td>
																</tr>
																<td>Description</td>
																<td><input type="text" data-childfld="child" data-attribute="relateditem.description" data-input="readonly"></td>
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
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Item Num.</td>
														<td><input type="text" name="itemnum" data-attribute="itemnum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" name="description" data-attribute="description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Item Location
													</td>
												</tr>
											</table>
										</div>	
									</div>	
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_inventory_child" class="display" data-child="invbalance"></table>
												<div id="mxtable_inventory_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Warehouse</td>
																	<td><input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Default Racknum</td>
																	<td><input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Condition Type</td>
																	<td><input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Stock</td>
																	<td><input type="text" data-childfld="child" data-attribute="curbal" data-input="readonly"></td>
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
														<td>Item Num.</td>
														<td><input type="text" name="itemnum" data-attribute="itemnum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" name="description" data-attribute="description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="addrow" id="addVendor"></div>
										</div>
									</div>
									<div class="col-md-12">
										<div id="subform-invvendor" class="subform" data-childfld="child" data-child="invvendor" data-table="mxtable_invvendor_child">
											<div class="row eatfix">
                                                <div class="col-md-4">
                                                    <table class="tbldata">
                                                        <tr>
                                                            <td>Vendor</td>
                                                            <td>
                                                                <input type="text" data-childfld="child" data-attribute="vendorcode" data-key="value" data-childrentable="vendor" data-childrencolumn="vendorcode" data-lookup="vendor">
                                                            </td>
                                                        </tr>
														 <tr>
                                                            <td>Name</td>
                                                            <td>
                                                                <input type="text" data-childfld="child" data-attribute="vendorcode.Name" >
                                                            </td>
                                                        </tr>
													</table>
												</div>
											</div>	
										</div>
									</div>	
										
									<div class="row eatfix">
										<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl4">Vendor/Supplier
												</td>
											</tr>
										</table>
										<div class="row eatfix"></div>
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_invvendor_child" class="display" data-child="invvendor"></table>
												<div id="mxtable_invvendor_child_detail" class="mxtable_detail">
													<div class="col-md-12">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Vendor</td>
																	<td><input type="text" data-childfld="child" data-attribute="vendorcode" data-input="readonly"></td>
																</tr>
																<td>Last order</td>
																<td><input type="text" data-childfld="child" data-attribute="lastorderdate" data-type="date" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Last Price</td>
																	<td><input type="text" data-childfld="child" data-attribute="lastprice" data-input="readonly" data-type="decimal"></td>
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
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl4">Part Number
												</td>
											</tr>
										</table>
										<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
												<table id="mxtable_itempartnum_child" class="display" data-child="itempartnum"></table>
												<div id="mxtable_itempartnum_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Part Number</td>
																	<td><input type="text" data-childfld="child" data-attribute="partnum" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Vendor</td>
																	<td><input type="text" data-childfld="child" data-attribute="vendor" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Country</td>
																	<td><input type="text" data-childfld="child" data-attribute="country" data-input="readonly"></td>
																</tr>
															</table>
														</div>
														<div class="col-md-6">
															<table class="tableDetailRow">
																<tr>
																	<td>Manufacturer</td>
																	<td><input type="text" data-childfld="child" data-attribute="manufacturer" data-input="readonly"></td>
																</tr>
																<tr>
																<td>OEM</td>
																<td><input type="text" data-childfld="child" data-attribute="oem" data-input="readonly"></td>
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
								<div id="maxes02_1_5" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Item Num.</td>
														<td><input type="text" name="itemnum" data-attribute="itemnum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" name="description" data-attribute="description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											
											<div class="row eatfix">
												<div class="col-md-6">
													<table width=100%>
														<tr>
															<td class="maxes_sct_tbl2">Dimension
															</td>
														</tr>
													</table>
													<table class="tbldata">
														<tr>
															<td>Length (cm)
															</td>
															<td><input type="text" data-attribute="width" data-type="decimal">
															</td>
														</tr>
														<tr>
															<td>Width (cm)
															</td>
															<td><input type="text" data-attribute="length" data-type="decimal">
															</td>
														</tr>
														<tr>
															<td>Height (cm)
															</td>
															<td><input type="text" data-attribute="height" data-type="decimal">
															</td>
														</tr>
														<tr>
															<td>Volume (cm<sup>3</sup>)
															</td>
															<td><input type="text" data-attribute="volume" data-type="decimal" data-input="readonly">
															</td>
														</tr>
														<tr>
															<td>Weight (grams)
															</td>
															<td><input type="text" data-attribute="weight" data-type="decimal">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table width=100%>
														<tr>
															<td class="maxes_sct_tbl2">Specification
															</td>
														</tr>
													</table>
													<table class="tbldata">
														<tr>
															<td>Specifications</td>
															<td><input type="text" data-lookup="classification" data-attribute="classificationcode" data-lookupaction="y"></td>
														</tr>
														<tr>
															<td>Specification Detail </td>
															<td><input type="text" data-attribute="spectext"></td>
														</tr>
													</table>
													<div class="col-md-12">
														<div class="addrow" id="gendescription"></div>
														<div class="addrow" id="additionalattr"></div>
													</div>
												</div>
											</div>
											<div class="row eatfix">
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_itemsclassifications_child" class="display" data-child="itemclassification">
														</table>
														<div id="mxtable_itemsclassifications_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Item</td>
																			<td><input type="text" data-childfld="child" data-attribute="itemnum" data-key="value" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Attribute Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="attributename" data-key="value" data-input="readonly" data-childrentable="classattribute" data-childrencolumn="attributename"></td>
																		</tr>
																		<tr>
																			<td>Attribute Description</td>
																			<td><input type="text" data-childfld="child" data-attribute="attributename.description" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Type </td>
																			<td><input type="text" data-childfld="child" data-attribute="type" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Value</td>
																			<td><input type="text" data-childfld="child" data-attribute="value"></td>
																		</tr>
																		<tr>
																			<td>Unit</td>
																			<td><input type="text" data-childfld="child" data-attribute="unit" data-lookup="measureunit"></td>
																		</tr>
																		<tr>
																			<td>Remarks</td>
																			<td><input type="text" data-childfld="child" data-attribute="description"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-4">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Entered By</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																		</tr>
																		<td>Entered Date</td>
																		<td><input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Changed By</td>
																			<td><input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"></td>
																		</tr>
																		<td>Changed Date</td>
																		<td><input type="text" data-childfld="child" data-attribute="changedate" data-type="date" data-input="readonly"></td>
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
								<div id="maxes02_1_6" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Item Num.</td>
														<td><input type="text" name="itemnum" data-attribute="itemnum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" name="description" data-attribute="description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Item Assembly
													</td>
												</tr>
											</table>
										</div>
									</div>	
									<div class="row eatfix">	
									<div class="col-md-4">
										<div class="addrow" id="addItemStruct"></div>
									</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
										<div id="subform-itemstruct" class="subform" data-child="itemstruct" data-table="mxtable_itemstructure_child">
											<div class="row eatfix">
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td>Item</td>
															<td><input type="text" data-childfld="child" data-attribute="subitemnum" data-key="value" data-lookup="item"></td>
														</tr>
														<tr>
															<td>Quantity</td>
															<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" ></td>
														</tr>
													</table>
												</div>
												
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td>Unit Cost</td>
															<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal"></td>
														</tr>
														<tr>
															<td>Line Cost</td>
															<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" ></td>
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
												<table id="mxtable_itemstructure_child" class="display" data-child="itemstruct">
												</table>
												<div id="mxtable_itemstructure_child_detail" class="mxtable_detail">
													<div class="row eatfix">
														<div class="col-md-4">
															<table class="tableDetailRow">
																<tr>
																	<td>Item</td>
																	<td><input type="text" data-childfld="child" data-attribute="subitemnum" data-key="value" data-input="readonly"></td>
																</tr>
																<tr>
																	<td>Quantity</td>
																	<td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal" ></td>
																</tr>
															</table>
														</div>
														<div class="col-md-4">
															<table class="tbldata">
																<tr>
																	<td>Unit Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal"></td>
																</tr>
																<tr>
																	<td>Line Cost</td>
																	<td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" ></td>
																</tr>
															</table>
														</div>

													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- START LOG PRODUCCTION !-->
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Actual Production Resources
												</tr>
											</table>
										</div>
									</div>

									<div class="prodlog_container">
										
										<!-- PAGE HEADER -->
										<div class="prodlog_page_header">
											<h1 class="prodlog_page_title">Production Cost Analytics</h1>
											<p class="prodlog_page_subtitle">Comprehensive batch cost analysis and breakdown</p>
										</div>
										
										<!-- HEADER SECTION -->
										<div class="prodlog_card">
											<div class="prodlog_title">
												<div>
													<span class="prodlog_title_text">Batch Cost Overview</span>
													<span class="prodlog_badge">Click batch to view details</span>
												</div>
												<button id="btn_download_header" class="prodlog_download_btn">
													<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
														<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
													</svg>
													Export
												</button>
											</div>
											<div id="prodlog_header_chart"></div>
										</div>

										<!-- KPI PANELS -->
										<div class="prodlog_kpi_grid">
											<div class="prodlog_kpi_box prodlog_kpi_mat">
												<div class="prodlog_kpi_label">Material Cost</div>
												<div class="prodlog_kpi_value" id="kpi_mat">Rp 0</div>
											</div>
											<div class="prodlog_kpi_box prodlog_kpi_svc">
												<div class="prodlog_kpi_label">Services Cost</div>
												<div class="prodlog_kpi_value" id="kpi_svc">Rp 0</div>
											</div>
											<div class="prodlog_kpi_box prodlog_kpi_lab">
												<div class="prodlog_kpi_label">Labor Cost</div>
												<div class="prodlog_kpi_value" id="kpi_lab">Rp 0</div>
											</div>
											<div class="prodlog_kpi_box prodlog_kpi_tool">
												<div class="prodlog_kpi_label">Tools Cost</div>
												<div class="prodlog_kpi_value" id="kpi_tool">Rp 0</div>
											</div>
										</div>

										<!-- DETAIL CHARTS -->
										<div class="prodlog_charts_grid">
											<div class="prodlog_card">
												<div class="prodlog_title">
													<span class="prodlog_title_text">Material Breakdown</span>
													<button id="btn_download_mat" class="prodlog_download_btn">
														<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
														</svg>
														Export
													</button>
												</div>
												<div id="prodlog_chart_mat" class="prodlog_sub_chart"></div>
											</div>
											
											<div class="prodlog_card">
												<div class="prodlog_title">
													<span class="prodlog_title_text">Services Breakdown</span>
													<button id="btn_download_svc" class="prodlog_download_btn">
														<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
														</svg>
														Export
													</button>
												</div>
												<div id="prodlog_chart_svc" class="prodlog_sub_chart"></div>
											</div>
											
											<div class="prodlog_card">
												<div class="prodlog_title">
													<span class="prodlog_title_text">Labor Analysis</span>
													<button id="btn_download_lab" class="prodlog_download_btn">
														<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
														</svg>
														Export
													</button>
												</div>
												<div id="prodlog_chart_lab" class="prodlog_sub_chart"></div>
											</div>
											
											<div class="prodlog_card">
												<div class="prodlog_title">
													<span class="prodlog_title_text">Tools Breakdown</span>
													<button id="btn_download_tool" class="prodlog_download_btn">
														<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
														</svg>
														Export
													</button>
												</div>
												<div id="prodlog_chart_tool" class="prodlog_sub_chart"></div>
											</div>
										</div>

									</div>

									<div class="row eatfix">
										<div class="subtab">
											<ul class="maxes02">
												<li class="maxes02_1"><a href="#matprodtrans">Materials</a></li>
												<li class="maxes02_2"><a href="#servprodtrans">Services</a></li>
												<li class="maxes02_2"><a href="#toolprodtrans">Tools</a></li>
											</ul>
										</div>


										 <div class="maxes04sub">
											<!-- Material Production History !-->
											<div id="matprodtrans" class="maxes02_1">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                               		Material Production History
                                            	</div>
												<div class="row eatfix">
													<div class="tablelist">
														<table id="mxtable_matprodtrans_child" class="display" data-child="matprodtrans"></table>
													</div>
												</div>
											</div>

											<!-- Services Production History !-->
											<div id="servprodtrans" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                               		Service Production History
                                            	</div>
												<div class="row eatfix">
													<div class="tablelist">
														<table id="mxtable_servprodtrans_child" class="display" data-child="servprodtrans"></table>
													</div>
												</div>
											</div>
											
											<!-- Tools Production History !-->
											<div id="toolprodtrans" class="maxes03">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                               		Tool Production History
                                            	</div>
												<div class="row eatfix">
													<div class="tablelist">
														<table id="mxtable_toolprodtrans_child" class="display" data-child="toolprodtrans"></table>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- END LOG PRODUCTION !-->

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
														<td>Item Num.</td>
														<td><input type="text" name="itemnum" data-attribute="itemnum" data-input="required" data-key="value"></td>
													</tr>
													<tr>
														<td>Description</td>
														<td><input type="text" name="description" data-attribute="description"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Hazards 
													</td>
												</tr>
											</table>
											
										</div>
									</div>
									
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="col-md-12">
												<div class="addrow" id="addHazards"></div>
											</div>
										</div>
											
												<div class="col-md-12">
													<div class="tablelist">
														<table id="mxtable_itemhazard_child" class="display" data-child="itemhazard">
														</table>
														<div id="mxtable_itemhazard_child_detail" class="mxtable_detail">
															<div class="row eatfix">
																<div class="col-md-6">
																	<table class="tableDetailRow">
																		<tr>
																			<td>Hazards</td>
																			<td><input type="text" data-childfld="child" data-attribute="hazardnum" data-key="value" data-input="readonly"></td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td><input type="text" data-childfld="child" data-attribute="description"  ></td>
																		</tr>
																		<tr>
																			<td>Qualifications</td>
																			<td><input type="text" data-childfld="child" data-attribute="qualification"  ></td>
																		</tr>
																		<tr>
																			<td>Classification</td>
																			<td><input type="text" data-childfld="child" data-attribute="classification"  ></td>
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
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl4">Precautions 
													</td>
												</tr>
											</table>
											
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="tablelist">
											<table id="mxtable_itemprecautions_child" class="display" data-child="itemprecaution">
											</table>
											<div id="mxtable_itemprecautions_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-6">
														<table class="tableDetailRow">
															<tr>
																<td>Precautions</td>
																<td><input type="text" data-childfld="child" data-attribute="precaution" data-key="value" data-input="readonly"></td>
															</tr>
															<tr>
																<td>Description</td>
																<td><input type="text" data-childfld="child" data-attribute="description"  ></td>
															</tr>
															<tr>
																<td>Remarks</td>
																<td><input type="text" data-childfld="child" data-attribute="remarks"  ></td>
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