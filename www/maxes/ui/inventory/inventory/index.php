<!DOCTYPE html>
<html lang="en">

<head>

    <?php
			include ("../../../applications/ui/ui.php");
			?>

        <style>
            .ui-dialog .ui-dialog-titlebar-close {
            			  display: none; 
            			}
            			#replenishmentworkbench_display, #ropsafetyRecommendation_display{
            				display:none;
            			}
            			
            			button {
            				padding: 10px 8px;
            				margin: 0 4px;
            				border: none;
            				border-radius: 8px;
            				background-color: #d5e2fd;
            				color: black;
            				font-size: 14px;
            				font-weight: 500;
            				cursor: pointer;
            				transition: background-color 0.3s, transform 0.2s;
            			}
            
            			button:hover {
            				background-color: #a6bdec;
            				transform: translateY(-2px);
            			}
            
            			button:active {
            				transform: scale(0.97);
            			}
            
            			button.secondary {
            				background-color: #6c757d;
            			}
            
            			button.secondary:hover {
            				background-color: #5a6268;
            			}
        </style>
</head>

<body id="page-top" xboset="inventory" data-appkeynum="itemnum">
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
                        <button id="recomorderButton" class="general-button">
                            <i class="fas fa-calculator"></i>Forecasting Workbench
                        </button>
                        <button id="replenishmentworkbench" class="general-button">
                            <i class="fas fa-warehouse"></i>Replenishment Workbench
                        </button>

                        <button id="ropsafetyRecommendation" class="general-button">
                            <i class="fas fa-calculator"></i>Optimization Workbench
                        </button>

                    </div>
                    <div id="ropsafetyRecommendation_display" class="maxes01 graphdisplay">
                        <div class="row eatfix">
                            <div class="col-md-12 maxes04">
                                <br>
                                <h4>ROP & Safety Stock Recommendation</h4>
                                <hr>
                                <div class="row eatfix">
                                    <div class="col-md-3">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Start Date
                                                </td>
                                                <td>
                                                    <input type="date" name='startDateROP'>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>End Date
                                                </td>
                                                <td>
                                                    <input type="date" name='endDateROP'>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-3">
                                        <button id="submitBtnROP">Optimization Workbench</button>
                                    </div>
                                </div>
                                <div id="ropChart" style="width:100%; height:850px"></div>
                            </div>
                        </div>
                    </div>
                    <div id="replenishmentworkbench_display" class="maxes01 graphdisplay">
                        <div class="row eatfix">
                            <div class="col-md-12 maxes04">
                                <br>
                                <h4>Replenishment Workbench</h4>
                                <hr>
                                
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl3">Cart to Purchase Requisition

                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                                <td class="maxes_sct_tbl4">Summary
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-xl-12" id="sentinels_report">
                                        
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="tablelist">
                                        <table id="mxtable_invcart_child" class="display" data-child="invcart"></table>
                                        <div id="mxtable_invcart_child_detail" class="mxtable_detail">
                                            <div class="row eatfix">
                                                <div class="col-md-4">
                                                    <table class="tableDetailRow">

                                                        <tr>
                                                            <td>Condition Type</td>
                                                            <td>
                                                                <input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly" disabled>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Warehouse</td>
                                                            <td>
                                                                <input type="text" data-childfld="child" data-attribute="warehouse" data-lookup="warehouse">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Rack</td>
                                                            <td>
                                                                <input type="text" data-childfld="child" data-attribute="racknum" data-lookup="rack|warehouse">
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
                                                <td>Start Date
                                                </td>
                                                <td>
                                                    <input type="date" name='startDate'>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>End Date
                                                </td>
                                                <td>
                                                    <input type="date" name='endDate'>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-3">
                                        <button id="submitBtn">Generate ABC Analysis</button>
                                    </div>
                                </div>

                                <div id="ABCChart" style="width:100%; height:850px"></div>

                            </div>
                        </div>
                    </div>
                    <div id="default_display" class="maxes01">
                        <ul class="maxes02">
                            <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
                            <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Inventory</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_3">Repeat Order</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_4">Transaction History</a></li>
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
                                                <table id="mxtable_inventory" class="display"></table>
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
                                                    <td>Item Num.
                                                    </td>
                                                    <td>
                                                        <input type="text" data-attribute="itemnum" data-input="required" data-childrentable="item" data-childrencolumn="itemnum" data-key="value">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Description
                                                    </td>
                                                    <td>
                                                        <input type="text" data-childfld="child" data-attribute="itemnum.description" data-input="readonly">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Warehouse
                                                    </td>
                                                    <td>
                                                        <input type="text" data-attribute="warehouse" data-input="readonly" data-key="value">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Rack Num
                                                    </td>
                                                    <td>
                                                        <input type="text" data-attribute="racknum">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Room Name
                                                    </td>
                                                    <td>
                                                        <input type="text" data-attribute="roomnum">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Issue Type
                                                </td>
                                                <td>
                                                    <input type="text" data-attribute="issuetype" data-lookup="issuetype" data-lookupaction="y">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Item Group
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="itemnum.itemgroup" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Order Unit
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="itemnum.orderunit" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Issue Unit
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Site</td>
                                                <td colspan=2>
                                                    <input type="text" data-attribute="sitecode" data-input="readonly" data-key="value">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Current Stock
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="totalcurbal" data-input="readonly" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Expired Stock
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="expquantity" data-input="readonly" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Available Stock
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="availableqty" data-input="readonly" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Company</td>
                                                <td>
                                                    <input type="text" data-attribute="orgcode" data-input="readonly" data-key="value">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="maxes_sct_tbl2">Pictures/Videos</div>
                                        <div id="itemcat_attachments" style="width:100%; white-space: nowrap; overflow-y: hidden; overflow-x: auto;">
                                        </div>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="subtab col-md-12">
                                        <ul class="maxes02">
                                            <li class="maxes02_1"><a href="#maxes02_1_2_1">AVG & FIFO/LIFO Stock</a></li>
                                            <li class="maxes02_2"><a href="#maxes02_1_2_2">Average Cost</a></li>
                                            <li class="maxes02_2"><a href="#maxes02_1_2_3">Conversion Stock</a></li>
                                        </ul>
                                    </div>
                                    <div class="maxes04sub">
                                        <div id="maxes02_1_2_1" class="maxes02_1">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Stock (AVG)
                                            </div>

                                            <!--
										<div class="row eatfix">
											<div class="col-md-12">
												<div class="addrow" id="addInvbalances"></div>
											</div>
										</div>
										-->
                                            <div class="row eatfix">
                                                <div class="col-md-12">
                                                    <div id="subform-invBalances" class="subform" data-childfld="child" data-child="v_invbalance" data-table="mxtable_invbalances_child">
                                                        <div class="row eatfix">
                                                            <div class="col-md-6">
                                                                <table class="tbldata">
                                                                    <tr>
                                                                        <td>Condition Type</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="conditiontype" data-key="value" data-lookupaction="y">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Description</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="conditiontype.description" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Stock</td>
                                                                        <td colspan=2>
                                                                            <input type="text" data-childfld="child" data-type="decimal" data-attribute="curbal" data-input="required">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Rack</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="racknum" data-lookup="rack|warehouse" data-key="value">
                                                                        </td>
                                                                    </tr>
                                                                   
                                                                </table>
                                                                <br>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <table class="tbldata">

                                                                    <tr>
                                                                        <td>Expired Date</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-type="date" data-attribute="expdate">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Site</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="sitecode" data-input="readonly" data-key="value">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Company</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="orgcode" data-input="readonly" data-key="value">
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
                                                        <table id="mxtable_invbalances_child" class="display" data-subform="subform-invBalances" data-child="v_invbalance">
                                                        </table>
                                                        <div id="mxtable_invbalances_child_detail" class="mxtable_detail">
                                                            <div class="row eatfix">
                                                                <div class="col-md-4">
                                                                    <table class="tableDetailRow">
                                                                        <tr>
                                                                            <td>Quantity</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="curbal" data-type="decimal">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Condition Type</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Rack Num</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="racknum" data-lookup="rack|warehouse">
                                                                            </td>
                                                                        </tr>
                                                                       
                                                                    </table>
                                                                </div>
                                                               
                                                            </div>
                                                            <input type='hidden' data-childfld='child' data-attribute='v_invbalanceid' data-input='readonly'>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Stock (FIFO/LIFO)
                                            </div>
                                            <div class="row eatfix">
                                                <div class="col-md-12">
                                                    <div class="tablelist">
                                                        <table id="mxtable_invfifolifocost_child" class="display" data-child="invfifolifocost"></table>
                                                        </table>
                                                        <div id="mxtable_invfifolifocost_child_detail" class="mxtable_detail">
                                                            <div class="row eatfix">
                                                                <div class="col-md-3">
                                                                    <div class="sub_qrcoderegion">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <table class="tableDetailRow">
                                                                        <tr>
                                                                            <td>Item Num.</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="itemnum" data-key="value">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Batch Num.</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="batchnum">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Cost Date</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="costdate" data-type="date">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Expired Date</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="expdate" data-type="date">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Purchase Order#</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="ponum" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Vendor</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="vendorcode" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                         <tr>
                                                                            <td>Unit Cost</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <table class="tableDetailRow">
                                                                        <tr>
                                                                            <td>Condition Type</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly" disabled>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Quantity</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Physical Count</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="physqty" data-type="decimal" data-input="readonly">
                                                                            </td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td>FIFO/LIFO Cost</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Condition Rate</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="conditionrate" data-type="decimal" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Adjustment Type</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="transtype" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Adjustment Date</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="transdate" data-type="date" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                         <tr>
                                                                            <td>Physical Count Date</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="physcountdate" data-type="date" data-input="readonly">
                                                                            </td>
                                                                        </tr>

                                                                    </table>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <table class="tableDetailRow">
                                                                         <tr>
                                                                            <td>Financial Period</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="financialperiod" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Warehouse</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Rack</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="racknum" data-lookup="rack|warehouse">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Company</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="orgcode" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Site</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="sitecode" data-input="readonly">
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
                                        <div id="maxes02_1_2_2" class="maxes03">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Average Cost
                                            </div>
                                            <div class="row eatfix">
                                                <div class="col-md-12">
                                                    <div class="tablelist">
                                                        <table id="mxtable_invcost_child" class="display" data-subform="subform-invcost" data-child="invcost"></table>
                                                        </table>
                                                        <div id="mxtable_invcost_child_detail" class="mxtable_detail">
                                                            <div class="row eatfix">
                                                                <div class="col-md-3">
                                                                    <table class="tableDetailRow">
                                                                        <tr>
                                                                            <td>Condition Type</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly" data-childrentable="conditiontype" data-childrencolumn="conditiontype">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Description</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="conditiontype.description" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Cost Standar</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-type="decimal" data-attribute="stdcost" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <table class="tableDetailRow">
                                                                        <tr>
                                                                            <td>Average Cost</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-input="readonly" data-type="decimal" data-attribute="avgcost">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Last Receipt Cost</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-type="decimal" data-attribute="lastcost" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <table class="tableDetailRow">
                                                                        <tr>
                                                                            <td>Average Cost Prod.</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-input="readonly" data-type="decimal" data-attribute="avgprodcost">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Last Production Cost</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-input="readonly" data-type="decimal" data-attribute="lastprodcost">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Last WO Num.</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-input="readonly" data-attribute="wonum">
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <input type='hidden' data-childfld='child' data-attribute='invcostid' data-input='readonly'>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="maxes02_1_2_3" class="maxes03">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Conversion Stock
                                            </div>
                                            <div class="row eatfix">
                                                <div class="col-md-12">
                                                    <table width=100%>
                                                        <tr>
                                                            <td class="maxes_sct_tbl">Conversion Qty
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="addrow" id="addConversions"></div>
                                                </div>
                                                <div class="row eatfix">
                                                    <div class="col-md-12">
                                                        <div class="tablelist">
                                                            <table id="mxtable_invconversion_child" class="display" data-child="invconversion">
                                                            </table>
                                                            <div id="mxtable_invconversion_child_detail" class="mxtable_detail">
                                                                <div class="row eatfix">
                                                                    <div class="col-md-4">
                                                                        <table class="tableDetailRow">
                                                                            <tr>
                                                                                <td>To Conversion</td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="tomeasureunit" data-lookup="measureunit">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Value</td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="conversion">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Conversion Stock</td>
                                                                                <td>
                                                                                    <input type="text" data-childfld="child" data-attribute="curbalconversion" data-input="readonly">
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


                                        <div class="col-md-12">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between col-md-12">
                                                Selling Price
                                            </div>
                                        </div>
                                        <div class="row eatfix">
                                            <div class="col-md-4">
                                                <div class="addrow" id="addInvPrice"></div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div id="subform-invprice" class="subform" data-childfld="child" data-child="invprice" data-table="mxtable_invprice_child">
                                                <div class="row eatfix">
                                                    <div class="col-md-4">
                                                        <table class="tbldata">
                                                            <tr>
                                                                <td>Condition Type</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="conditiontype" data-key="value" data-childrentable="conditiontype" data-childrencolumn="conditiontype" data-lookup="itemcondition|itemnum" data-lookupaction="y">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Description</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="conditiontype.description" data-input="readonly">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Price Category</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="pricecode" data-key="value" data-childrentable="pricegroup" data-childrencolumn="pricecode" data-lookup="pricegroup" data-lookupactionlookupaction="y">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Unit Price</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="unitprice1" data-input="required" data-type="decimal">
                                                                </td>
                                                            </tr>


                                                        </table>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <table class="tbldata">
                                                            <tr>
                                                                <td>Commission per Unit(%)</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="commission" data-type="decimal">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Commission</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="commissionprice" data-type="decimal">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Price + Commission</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="unitprice" data-input="required" data-type="decimal">
                                                                </td>
                                                            </tr>


                                                        </table>
                                                        <br>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <table class="tbldata">
                                                            <tr>
                                                                <td>Description</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="pricecode.description" data-input="readonly">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Default Price</td>
                                                                <td>
                                                                    <input type="checkbox" data-childfld="child" data-attribute="defaultprice">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>The price is lower than the normal price?</td>
                                                                <td>
                                                                    <input type="checkbox" data-input="readonly" data-childfld="child" data-attribute="pricecode.islesser" data-input="readonly">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Site</td>
                                                                <td colspan=2>
                                                                    <input type="text" data-childfld="child" data-attribute="sitecode" data-input="readonly" data-key="value">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Company</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="orgcode" data-input="readonly" data-key="value">
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
                                                    <table id="mxtable_invprice_child" class="display" data-subform="subform-invprice"></table>
                                                    <div id="mxtable_invprice_child_detail" class="mxtable_detail">
                                                        <div class="row eatfix">
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Condition Type</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="conditiontype" data-key="value" data-childrentable="conditiontype" data-childrencolumn="conditiontype">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Description</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="conditiontype.description" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Price Category</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="pricecode" data-key="value" data-childrentable="pricegroup" data-childrencolumn="pricecode" data-lookup="pricegroup">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Unit Price</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="unitprice1" data-input="required" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Commission per Unit(%)</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="commission" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Commission</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="commissionprice" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Price + Commission</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="unitprice" data-input="required" data-type="decimal">
                                                                        </td>
                                                                    </tr>

                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Price Rate</td>
                                                                        <td>
                                                                            <input type="text" data-input="readonly" data-childfld="child" data-attribute="pricecode.pricerate">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Discount 1st(%)</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-type="decimal" data-attribute="primarydiscount">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Discount 2nd(%)</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-type="decimal" data-attribute="secondarydiscount">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Default Price</td>
                                                                        <td>
                                                                            <input type="checkbox" data-childfld="child" data-attribute="defaultprice">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Always use the last price?</td>
                                                                        <td>
                                                                            <input type="checkbox" data-childfld="child" data-attribute="useupdateprice">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Price lower than normal price?</td>
                                                                        <td>
                                                                            <input type="checkbox" data-input="readonly" data-childfld="child" data-attribute="pricecode.islesser">
                                                                        </td>
                                                                    </tr>

                                                                </table>
                                                            </div>
                                                        </div>
                                                        <input type='hidden' data-childfld='child' data-attribute='invpriceid' data-input='readonly'>
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
                                                    <td>Item Num.
                                                    </td>
                                                    <td>
                                                        <input type="text" data-attribute="itemnum" data-input="required" data-childrentable="item" data-childrencolumn="itemnum" data-key="value">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Description
                                                    </td>
                                                    <td>
                                                        <input type="text" data-childfld="child" data-attribute="itemnum.description" data-input="readonly">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Warehouse
                                                    </td>
                                                    <td>
                                                        <input type="text" data-attribute="warehouse" data-input="readonly" data-key="value">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <blockquote>
                                            <b>Internal time period:</b> How many days it takes from the first time a user requests an item, until the PO is sent to the Vendor. 
                                            <br>
                                            <b>External time period:</b> How many days the vendor spends from the time the PO is sent, until
                                            the goods arrive at the warehouse.
                                        </blockquote>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-4">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Min amount. Reorder
                                                </td>
                                                <td>
                                                    <input type="text" data-attribute="reorderqty" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Economic Order Qty
                                                </td>
                                                <td>
                                                    <input type="text" data-attribute="eoqqty" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Lead Time
                                                </td>
                                                <td>
                                                    <input type="text" data-attribute="leadtime" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Safety Stock
                                                </td>
                                                <td>
                                                    <input type="text" data-attribute="safetystock" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Apply Reorder
                                                </td>
                                                <td>
                                                    <input type="checkbox" data-attribute="reorder">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Main Vendor
                                                </td>
                                                <td>
                                                    <input type="text" data-attribute="vendorcode" data-lookup="vendor">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Model
                                                </td>
                                                <td>
                                                    <input type="text" data-attribute="model">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Catalog
                                                </td>
                                                <td>
                                                    <input type="text" data-attribute="catalog">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="page-header-list">
                                            <div class="tablelist">
                                                <table id="mxtable_invvendor_child" class="display" data-child="invvendor"></table>
                                                <div id="mxtable_invvendor_child_detail" class="mxtable_detail">
                                                    <div class="col-md-12">
                                                        <div class="col-md-4">
                                                            <table class="tableDetailRow">
                                                                <tr>
                                                                    <td>Vendor</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="vendorcode" data-input="readonly">
                                                                    </td>
                                                                </tr>
                                                                <td>Last order</td>
                                                                <td>
                                                                    <input type="text" data-childfld="child" data-attribute="lastorderdate" data-type="date" data-input="readonly">
                                                                </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Last Price</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="lastprice" data-input="readonly" data-type="decimal">
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
                            <div id="maxes02_1_4" class="maxes03">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>

                                </div>
                                <div class="card-body">
                                    <div class="row eatfix">
                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>Item Number
                                                    </td>
                                                    <td>
                                                        <input type="text" data-attribute="itemnum" data-input="required" data-childrentable="item" data-childrencolumn="itemnum" data-key="value">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Description
                                                    </td>
                                                    <td>
                                                        <input type="text" data-childfld="child" data-attribute="itemnum.description" data-input="readonly">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Warehouse
                                                    </td>
                                                    <td>
                                                        <input type="text" data-attribute="warehouse" data-input="readonly" data-key="value">
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
                                                <td class="maxes_sct_tbl">Detail Transactions
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-6">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Current Stock
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="totalcurbal" data-input="readonly" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Order Unit
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="itemnum.orderunit" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Issue Unit
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="itemnum.issueunit" data-input="readonly">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Item Group
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="itemnum.itemgroup" data-input="readonly">
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                                    <div class="col-md-6">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Net Revenue
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="nettrevenue" data-input="readonly" data-type="decimal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Gross Revenue
                                                </td>
                                                <td>
                                                    <input type="text" data-childfld="child" data-attribute="grossrevenue" data-input="readonly" data-type="decimal">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="subtab">
                                        <ul class="maxes02">
                                            <li class="maxes02_1"><a href="#maxes02_1_4_1">Purchasing</a></li>
                                            <li class="maxes02_2"><a href="#maxes02_1_4_2">Transfer Between Warehouses</a></li>
                                            <li class="maxes02_2"><a href="#maxes02_1_4_3">Sales</a></li>
                                            <li class="maxes02_2"><a href="#maxes02_1_4_4">Adjustment </a></li>
                                            <li class="maxes02_2"><a href="#maxes02_1_4_5">Issue</a></li>
                                        </ul>
                                    </div>
                                    <div class="maxes04sub">
                                        <div id="maxes02_1_4_1" class="maxes02_1">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Purchasing
                                            </div>
                                            <div class="row eatfix">
                                                <div class="tablelist">
                                                    <table id="mxtable_receipt_child" class="display" data-child="matrectrans"></table>
                                                    <div id="mxtable_receipt_child_detail" class="mxtable_detail">
                                                        <div class="row eatfix">
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>PO</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="ponum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Num.</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="polinenum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Quantity</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Unit Cost</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Total Cost</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Entered By</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <td>Entered Date</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                                    </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Remarks</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="remarks" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="maxes02_1_4_2" class="maxes03">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Transfer Between Warehouses
                                            </div>
                                            <div class="row eatfix">
                                                <div class="tablelist">
                                                    <table id="mxtable_transfer_child" class="display" data-child="matrectrans"></table>
                                                    <div id="mxtable_transfer_child_detail" class="mxtable_detail">
                                                        <div class="row eatfix">
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>From Warehouse</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>To Warehouse</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="towarehouse" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>From Rack/Bin</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="racknum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>To Rack</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="toracknum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Condition Type</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Quantity</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Unit Cost</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Total Cost</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>

                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Entered By</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Entered Date</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Actual Date</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="actualdate" data-type="date">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Remarks</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="remarks" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="maxes02_1_4_3" class="maxes03">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Sales
                                            </div>
                                            <div class="row eatfix">
                                                <div class="tablelist">
                                                    <table id="mxtable_matsaletrans_child" class="display" data-child="matsaletrans">
                                                    </table>
                                                    <div id="mxtable_matsaletrans_child_detail" class="mxtable_detail">
                                                        <div class="row eatfix">
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>SO Number</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="sonum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Num</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="solinenum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>RFQ num</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="rfqnum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>RFQ Line Num</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="rfqlinenum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Customer Code</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="customercode" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <tr>
                                                                            <td>From Warehouse</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="warehouse" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>From Rack/Bin</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="fromrack" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Price Code</td>
                                                                            <td>
                                                                                <input type="text" data-childfld="child" data-attribute="pricecode" data-input="readonly">
                                                                            </td>
                                                                        </tr>
                                                                        <td>Unit Price</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    </tr>
                                                                    <td>Quantity</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal">
                                                                    </td>
                                                                    </tr>
                                                                    </tr>
                                                                    <td>Total Price</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="unitprice" data-input="readonly" data-type="decimal">
                                                                    </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">

                                                                    <tr>
                                                                        <td>Entered By</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <td>Entered Date</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                                    </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Shipment Num.</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="trackingnum">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Remarks</td>
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
                                        <div id="maxes02_1_4_4" class="maxes03">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Adjustment
                                            </div>
                                            <div class="row eatfix">
                                                <div class="tablelist">
                                                    <table id="mxtable_invtrans_child" class="display" data-child="invtrans">
                                                    </table>
                                                    <div id="mxtable_invtrans_child_detail" class="mxtable_detail">
                                                        <div class="row eatfix">
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Transaction Type</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="transtype" data-input="readonly">
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>Qty. before</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="oldquantity" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Current qty</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Variance Qty</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="varianceqty" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">

                                                                    <tr>
                                                                        <td>Old Cost</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="oldcost" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Current Cost</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="cost" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Variance Cost</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="variancecost" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Work Order Num.</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Entered By</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <td>Entered Date</td>
                                                                    <td>
                                                                        <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                                    </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="maxes02_1_4_5" class="maxes03">
                                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                Issuing
                                            </div>
                                            <div class="row eatfix">
                                                <div class="tablelist">
                                                    <table id="mxtable_matusetrans_child" class="display" data-child="matusetrans">
                                                    </table>
                                                    <div id="mxtable_matusetrans_child_detail" class="mxtable_detail">
                                                        <div class="row eatfix">
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Transaction Type</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="transtype" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Work Order Num.</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Asset</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="assetnum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Location</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="location" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>MR Num.</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="mrnum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>Condition Type</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="conditiontype" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Qty</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="quantity" data-input="readonly" data-type="decimal">
                                                                        </td>
                                                                    </tr> 
                                                                    <tr>
                                                                        <td>Unit Cost</td>
                                                                        <td> <input type="text" data-childfld="child" data-attribute="unitcost" data-input="readonly" data-type="decimal"></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Total Cost</td>
                                                                            <td><input type="text" data-childfld="child" data-attribute="linecost" data-input="readonly" data-type="decimal">  </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <table class="tableDetailRow">
                                                                    <tr>
                                                                        <td>WO</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="wonum" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Entered Date</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Transaction Date</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Remarks</td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="remarks" data-input="readonly">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Requested by </td>
                                                                        <td>
                                                                            <input type="text" data-childfld="child" data-attribute="requestedby" data-input="readonly">
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
        </div>
    </div>
</body>

</html>