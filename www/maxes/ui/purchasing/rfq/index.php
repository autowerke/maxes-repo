<!DOCTYPE html>
<html>
<title>
</title>

<head>
    <?php
	include ("../../../applications/ui/ui.php");
	?>
</head>

<body id="page-top" xboset="rfq" data-appkeynum="rfqnum">
    <div id="wrapper">
        <div id='sidebarmenu'></div>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="eat-toolbars"></div>
                <div class="container-fluid">
                    <div class="maxes01">
                        <ul class="maxes02">
                            <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
                            <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Tender</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_3">Detail</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_4">Vendor</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_5">Term of Conditions</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_6">Quotation</a></li>
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
                                                    <table id="mxtable_rfq" class="display"></table>
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
                                                    <td>RFQ</td>
                                                    <td><input type="text" data-attribute="rfqnum" data-type="upper" data-input="required" data-key="value"></td>
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
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl2">Detail
                                                </td>
                                            </tr>
                                        </table>
                                        <table class="tbldata">
                                            <tr>
                                                <td>RFQ Type
                                                </td>
                                                <td><input type="text" data-attribute="rfqtype" data-lookup="rfqtype">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Requested by
                                                </td>
                                                <td><input type="text" data-attribute="requestedby" data-lookup="employees">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Buyer
                                                </td>
                                                <td><input type="text" data-attribute="buyer" data-lookup="employees">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Priority
                                                </td>
                                                <td><input type="text" data-attribute="priority" data-lookup="popriority">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Entered By
                                                </td>
                                                <td><input type="text" data-attribute="enterby" data-input="readonly">
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Status
                                                </td>
                                                <td><input type="text" data-attribute="status" data-input="readonly">
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                                    <div class="col-md-4">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl2">Date Time
                                                </td>
                                            </tr>
                                        </table>
                                        <table class="tbldata">
                                            <tr>
                                                <td>Date Tender
                                                </td>
                                                <td><input type="text" data-attribute="rfqdate" data-type="date">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Required Date</td>
                                                <td><input type="text" data-attribute="requireddate" data-type="date"></td>
                                            </tr>
                                            <tr>
                                                <td>Reply Date</td>
                                                <td><input type="text" data-attribute="replydate" data-type="date"></td>
                                            </tr>
                                            <tr>
                                                <td>Printed Date</td>
                                                <td><input type="text" data-attribute="printeddate" data-type="date"></td>
                                            </tr>
                                            <tr>
                                                <td>Closed Date</td>
                                                <td><input type="text" data-attribute="closedate" data-type="date"></td>
                                            </tr>
                                            <tr>
                                                <td>Status Date</td>
                                                <td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl2">Cost
                                                </td>
                                            </tr>
                                        </table>
                                        <table class="tbldata">
                                            <tr>
                                                <td>Currency
                                                </td>
                                                <td><input type="text" data-attribute="currencycode" data-input="required" data-lookup="currency" data-lookupaction="y">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Exchange Rates
                                                </td>
                                                <td><input type="text" data-type="decimal" data-attribute="exchangerate">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Total before Tax
                                                </td>
                                                <td><input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Total Tax
                                                </td>
                                                <td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Total Cost
                                                </td>
                                                <td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalcost" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Total Base Cost
                                                </td>
                                                <td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalbasecost">
                                                </td>
                                            </tr>

                                        </table>

                                    </div>
                                </div>
                                <div class="row eatfix">

                                    <div class="col-md-4">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl2">Document Status
                                                </td>
                                            </tr>
                                        </table>
                                        <table class="tbldata">
                                            <tr>
                                                <td>Line Num. Proses
                                                </td>
                                                <td><input type="text" data-attribute="docprocessnum" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Document Status
                                                </td>
                                                <td><input type="text" data-attribute="docstatus" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Remarks
                                                </td>
                                                <td><input type="text" data-attribute="docnotice" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Sent By
                                                </td>
                                                <td><input type="text" data-attribute="docsentby" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Document on Person
                                                </td>
                                                <td><input type="text" data-attribute="docposition" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Date of Arrival
                                                </td>
                                                <td><input type="text" data-attribute="docarrival" data-type="date" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2>
                                                    <div class='btncontainer2'>
                                                        <ul>
                                                            <a href='javascript:;' id='btn_comments'>
                                                                <li><i class='fa fa-comment'></i></a>
                                                            <ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                                    <div class="col-md-4">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl2">Receiving
                                                </td>
                                            </tr>
                                        </table>
                                        <table class="tbldata">
                                            <tr>
                                                <td>Term of Shipment
                                                </td>
                                                <td><input type="text" data-attribute="freightterms">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Term of Payment
                                                </td>
                                                <td><input type="text" data-attribute="paymentterms">
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>FOB points
                                                </td>
                                                <td><input type="text" data-attribute="fobpoints">
                                                </td>
                                            </tr>
                                            <tr>
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
                                                    <td>RFQ</td>
                                                    <td><input type="text" data-attribute="rfqnum" data-type="upper" data-input="required" data-key="value"></td>
                                                </tr>
                                                <tr>
                                                    <td>Description</td>
                                                    <td><input type="text" data-attribute="description" style="width:450px;"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row eatfix"> </div>
                                <div class="col-md-12">
                                    <table width=100%>
                                        <tr>
                                            <td class="maxes_sct_tbl3">Total Cost
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="tbldata">
                                        <tr>
                                            <td>Currency
                                            </td>
                                            <td><input type="text" data-attribute="currencycode" data-input="required" data-lookup="currency" data-lookupaction="y">
                                            </td>

                                            <td>Exchange Rates
                                            </td>
                                            <td><input type="text" data-type="decimal" data-attribute="exchangerate" data-input="readonly">
                                            </td>
                                            <td>Total Cost
                                            </td>
                                            <td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalcost" data-input="readonly">
                                            </td>

                                        </tr>
                                        <tr>
                                            <td>Total before Tax
                                            </td>
                                            <td><input type="text" data-type="decimal" data-input="readonly" data-attribute="pretaxtotal">
                                            </td>

                                            <td>Total Tax
                                            </td>
                                            <td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totaltax">
                                            </td>



                                            <td>Total Base Cost
                                            </td>
                                            <td><input type="text" data-type="decimal" data-input="readonly" data-attribute="totalbasecost">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="row eatfix">
                                    <div class="maxes_sct_tbl">Detail - Owner Estimation</div>

                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-4">
                                        <div class="addrow" id="addRFQLines"></div>
										  <div class="addrow" id="copyPR"></div>
                                    </div>
                                </div>

                                <div id="subform-RFQLines" class="subform" data-table="mxtable_rfqlines_child" data-child="rfqline">
                                    <div class="row eatfix">
                                        <div class="col-md-4">
                                            <table class="tbldata">
                                                <tr>
                                                    <td colspan=2 class="maxes_sct_tbl2">Detail</td>
                                                </tr>
                                                <tr>
                                                    <td>Num.</td>
                                                    <td><input type="text" size=3 data-childfld="child" data-attribute="rfqlinenum" data-key="value" data-input="required" data-type="integer"></td>
                                                </tr>
                                                <tr>
                                                    <td>Type </td>
                                                    <td><input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype" data-lookupaction="y" data-input="required"></td>
                                                </tr>
                                                <tr>
                                                    <td>Item</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="item|sitecode" data-lookupaction="y" data-childrentable="item" data-childrencolumn="itemnum"></td>
                                                </tr>
												<tr>
													<td>Service Item</td>
													<td>
														<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" >
													</td>
												</tr>
                                                <tr>
                                                    <td>Description</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description"></td>
                                                </tr>
                                                <tr>
                                                    <td>Specification</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="itemnum.spectext" data-input="readonly"></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <table class="tbldata">
                                                <tr>
                                                    <td colspan=2 class="maxes_sct_tbl2">Cost</td>
                                                </tr>
                                                <tr>
                                                    <td>Unit Cost</td>
                                                    <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost"></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Cost</td>
                                                    <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="linecost" data-input="readonly"></td>
                                                </tr>
                                                <tr>
                                                    <td>Tax</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax" data-lookupaction="y"></td>
                                                </tr>
                                                <tr>
                                                    <td>Tax Cost</td>
                                                    <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="taxcost"></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <table class="tbldata">
                                                <tr>
                                                    <td colspan=2 class="maxes_sct_tbl2">Quantity</td>
                                                </tr>
                                                <tr>
                                                    <td>Quantity</td>
                                                    <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="quantity"></td>
                                                </tr>
                                                <tr>
                                                    <td>Order Unit </td>
                                                    <td><input type="text" data-childfld="child" data-attribute="orderunit" data-input="required" data-lookup="measureunit" data-crossvalue="itemnum.orderunit" data-lookupaction="y"></td>
                                                </tr>
                                                <tr>
                                                    <td>Issue Unit</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
                                                </tr>
                                                <tr>
                                                    <td>Conversion Factor</td>
                                                    <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="conversionfactor" data-input="required"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row eatfix">
                                        <div class="col-md-4">
                                            <table class="tbldata">
                                                <tr>
                                                    <td colspan=2 class="maxes_sct_tbl2">Model</td>
                                                </tr>
                                                <tr>
                                                    <td>Manufacturer</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="itemnum.manufacturer" data-input="readonly"></td>
                                                </tr>
                                                <tr>
                                                    <td>Part Number</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="partnum" data-lookup="itempartnum|itemnum"></td>
                                                </tr>
                                                <tr>
                                                    <td>Category</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="category"></td>
                                                </tr>
                                                <tr>
                                                    <td>Catalog</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="catalog"></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <table class="tbldata">
                                                <tr>
                                                    <td colspan=2 class="maxes_sct_tbl2">Receiving Detail</td>
                                                </tr>
                                                <tr>
                                                    <td>Warehouse</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse|sitecode" data-childrentable="warehouse" data-childrencolumn="warehouse" data-lookupaction="y"></td>
                                                </tr>
                                                <tr>
                                                    <td>Rack</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="racknum" data-lookup="racknum|warehouse"></td>
                                                </tr>
                                                <tr>
                                                    <td>Work Order</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="wonum" data-lookup="workorder|orgcode" data-lookupaction="y"></td>
                                                </tr>
                                                <tr>
                                                    <td>Required Date</td>
                                                    <td><input type="text" data-childfld="child" data-type="date" data-attribute="requireddate"></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <table class="tbldata">
                                                <tr>
                                                    <td colspan=2 class="maxes_sct_tbl2">Transactions Account Code</td>
                                                </tr>
                                                <tr>
                                                    <td>Credit Account Code</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="glaccount|orgcode"></td>
                                                </tr>
                                                <tr>
                                                    <td>Debit Account Code</td>
                                                    <td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="glaccount|orgcode" data-crossvalue="warehouse.controlacc"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!--end form-->
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="tablelist">
                                            <table id="mxtable_rfqlines_child" class="display" data-subform="subform-RFQLines"></table>
                                            <div id="mxtable_rfqlines_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Line Detail</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Num.</td>
                                                                <td><input type="text" size=3 data-childfld="child" data-attribute="rfqlinenum" data-key="value" data-input="required" data-type="integer"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Type </td>
                                                                <td><input type="text" data-childfld="child" data-attribute="linetype" data-lookup="linetype" data-lookupaction="y" data-input="required"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Item</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="item|sitecode" data-lookupaction="y" data-childrentable="item" data-childrencolumn="itemnum"></td>
                                                            </tr>
															<tr>
																<td>Service Item</td>
																<td>
																	<input type="text" data-childfld="child" data-attribute="serviceitemnum" data-lookup="serviceitems" data-lookupaction="y" >
																</td>
															</tr>
                                                            <tr>
                                                                <td>Description</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="description" data-crossvalue="itemnum.description"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Specification</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="itemnum.spectext" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>PR</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="prnum" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>PR Line Num.</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="prlinenum" data-input="readonly"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Cost</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Unit Cost</td>
                                                                <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="unitcost"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Cost</td>
                                                                <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="linecost" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tax</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax" data-lookupaction="y"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tax Cost</td>
                                                                <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="taxcost"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Quantity</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Quantity</td>
                                                                <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="quantity"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Order Unit </td>
                                                                <td><input type="text" data-childfld="child" data-attribute="orderunit" data-input="required" data-lookup="measureunit" data-crossvalue="itemnum.orderunit" data-lookupaction="y"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Issue Unit</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Conversion Factor</td>
                                                                <td><input type="text" data-childfld="child" data-type="decimal" data-attribute="conversionfactor" data-input="required"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="row eatfix">
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Model</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Manufacturer</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="itemnum.manufacturer" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Part Number</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="partnum" data-lookup="itempartnum|itemnum"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Category</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="category"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Catalog</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="catalog"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Receiving Detail</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Warehouse</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse|sitecode" data-childrentable="warehouse" data-childrencolumn="warehouse" data-lookupaction="y"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Rack</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="racknum" data-lookup="racknum|warehouse"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Work Order</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="wonum" data-lookup="workorder|orgcode" data-lookupaction="y"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Required Date</td>
                                                                <td><input type="text" data-childfld="child" data-type="date" data-attribute="requireddate"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Transactions Account Code</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Credit Account Code</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="glcreditacct" data-input="required" data-lookup="glaccount|orgcode"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Debit Account Code</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="gldebitacct" data-input="required" data-lookup="glaccount|orgcode" data-crossvalue="warehouse.controlacc"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end tab-->
                            <div id="maxes02_1_4" class="maxes03">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-6">

                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>RFQ</td>
                                                    <td><input type="text" data-attribute="rfqnum" data-type="upper" data-input="required" data-key="value"></td>
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
                                    <div class="maxes_sct_tbl">Vendor/Supplier</div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-4">
                                        <div class="addrow" id="addRFQVendor"></div>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div id="subform-RFQVendors" class="subform" data-table="mxtable_rfqvendor_child" data-child="rfqvendor">
                                        <div class="row eatfix">
                                            <div class="col-md-6">
                                                <table class="tbldata">
                                                    <tr>
                                                        <td colspan=2 class="maxes_sct_tbl2">Line Detail</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Num.</td>
                                                        <td><input type="text" size=3 data-childfld="child" data-attribute="rfqvendorlinenum" data-input="required" data-type="integer"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Vendor</td>
                                                        <td><input type="text" data-childfld="child" data-attribute="vendor" data-input="required" data-key="value" data-lookup="vendor"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Procurement/User Remarks</td>
                                                        <td><input type="text" data-childfld="child" data-attribute="remarks"></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="tablelist">
                                            <table id="mxtable_rfqvendor_child" class="display" data-subform="subform-RFQVendors"></table>
                                            <div id="mxtable_rfqvendor_child_detail" class="mxtable_detail">
                                                <div class="col-md-4">
                                                    <table class="tableDetailRow">
                                                        <tr>
                                                            <td colspan=2>
                                                                <div class="maxes_sct_tbl3">Vendor Detail</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Num.</td>
                                                            <td><input type="text" size=3 data-childfld="child" data-attribute="rfqvendorlinenum" data-key="value" data-input="required" data-type="integer"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Vendor</td>
                                                            <td><input type="text" data-childfld="child" data-attribute="vendor" data-input="required" data-lookup="vendor"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Procurement/User Remarks</td>
                                                            <td><input type="text" data-childfld="child" data-attribute="remarks"></td>
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
                                                <td class="maxes_sct_tbl">Shipping & Billing</td>
                                            </tr>
                                        </table>
                                    </div>
                                
                                    <div class="col-md-12">
                                        <div class="row eatfix">
                                            <div class="col-md-6">
                                                <table class="tbldata">
                                                    <td class="maxes_sct_tbl2">Shipping</td>
                                                    <tr>
                                                        <td>
                                                            <table class="tbldata">
                                                                <tr>
                                                                    <td>Address Code</td>
                                                                    <td><input type="text" data-attribute="shiptoaddress" data-input="required" data-lookup="address|sitecode" data-childrentable="address" data-childrencolumn="addresscode"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Address</td>
                                                                    <td><input type="text" data-childfld="child" data-attribute="shiptoaddress.address" data-input="readonly"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>City</td>
                                                                    <td><input type="text" data-childfld="child" data-attribute="shiptoaddress.city" data-input="readonly"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Province</td>
                                                                    <td><input type="text" data-childfld="child" data-attribute="shiptoaddress.stateprovince" data-input="readonly"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Postal Code</td>
                                                                    <td><input type="text" data-childfld="child" data-attribute="shiptoaddress.zippostalcode" data-input="readonly"></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </div>
                                            <div class="col-md-6">
                                                <table class="tbldata">
                                                    <tr>
                                                        <td class="maxes_sct_tbl2">Billing</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <table class="tbldata">
                                                                <tr>
                                                                    <td>Address Code</td>
                                                                    <td><input type="text" data-attribute="billtoaddress" data-input="required" data-lookup="address|sitecode" data-childrentable="address" data-childrencolumn="addresscode"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Address</td>
                                                                    <td><input type="text" data-childfld="child" data-attribute="billtoaddress.address" data-input="readonly"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>City</td>
                                                                    <td><input type="text" data-childfld="child" data-attribute="billtoaddress.city" data-input="readonly"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Province</td>
                                                                    <td><input type="text" data-childfld="child" data-attribute="billtoaddress.stateprovince" data-input="readonly"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Postal Code</td>
                                                                    <td><input type="text" data-childfld="child" data-attribute="billtoaddress.zippostalcode" data-input="readonly"></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
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
                                    <div class="col-md-6">
                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>RFQ</td>
                                                    <td><input type="text" data-attribute="rfqnum" data-type="upper" data-input="required" data-key="value"></td>
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

                                <div id="subform-rfqterm" class="subform" data-child="rfqterm" data-table="mxtable_rfqterm_child">
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
                                            <table id="mxtable_rfqterm_child" class="display" data-subform="subform-rfqterm"></table>
                                            <div id="mxtable_rfqterm_child_detail" class="mxtable_detail">
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
                                    <div id="subform-rfqterm2" class="subform" data-child="rfqterm2" data-table="mxtable_rfqterm2_child">

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
                                            <table id="mxtable_rfqterm2_child" class="display" data-subform="subform-rfqterm2"></table>
                                            <div id="mxtable_rfqterm2_child_detail" class="mxtable_detail">
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
                            <div id="maxes02_1_6" class="maxes03">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-6">
                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>RFQ</td>
                                                    <td><input type="text" data-attribute="rfqnum" data-type="upper" data-input="required" data-key="value"></td>
                                                </tr>
                                                <tr>
                                                    <td>Description</td>
                                                    <td><input type="text" data-attribute="description" style="width:450px;"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
								<br><br>
								<div class = "row eatfix">
								 <div class="col-md-12">
									<div id="vendorpriceChart" style="width:100%; height:500px"></div>
								 </div>
								</div>
								 <div class="row eatfix">
                                    <div class="col-md-4" id="rfq_ai_recommendation">
                                    </div>
                                </div>
                               <div class="row eatfix">
                                   <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl">Invited Vendors
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                 <div class="row eatfix">

                                    <div class="col-md-2">
                                        <div class="addrow" id="sendnotestovendor"></div>
                                    </div>

                                </div>

                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="tablelist">
                                            <table id="mxtable_rfqvendorlist_child" class="display" data-child="rfqvendor"></table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl">Quotation <span id="mxtable_quotation_child_title"></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">

                                    <div class="col-md-6">
                                        <div class="addrow" id="selectrfqlines"></div>
                                        <div class="addrow" id="selectwinner"></div>
                                    </div>

                                </div>

                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="tablelist">
                                            <table id="mxtable_quotation_child" class="display" data-link="mxtable_rfqvendorlist_child" data-pk="vendor" data-child="rfqquotation"></table>
                                            <div id="mxtable_quotation_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Quotation Detail</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Num.</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="rfqlinenum" data-type="integer" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Vendor</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="vendor" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Item</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="itemnum" data-input="readonly"></td>
                                                            </tr>
                                                             <tr>
                                                                <td>Service Item</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="serviceitemnum" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Description</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="description" data-input="readonly"></td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>User Remarks</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="remarks" data-input="readonly"></td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>Vendor Remarks</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="remarks2" data-input="readonly"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td colspan=2>
                                                                    <div class="maxes_sct_tbl3">Cost</div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Unit Cost</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Quantity</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Cost</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tax</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="taxcode" data-lookup="tax" data-lookupaction="y"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tax Cost</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="taxcost" data-type="decimal"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <table class="tableDetailRow">
                                                            <tr>
                                                                <td>Awarded</td>
                                                                <td><input type="checkbox" data-childfld="child" data-attribute="awarded"></td>
                                                            </tr>
															 <tr>
                                                                <td>PO</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="ponum" data-input="readonly"></td>
                                                            </tr>
                                                             <tr>
                                                                <td>Awarded</td>
                                                                <td><input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly"></td>
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