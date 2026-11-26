<!DOCTYPE html>
<html>
   <title></title>
   <head>
      <?php
         include ("../../../applications/ui/ui.php");
         ?>
   </head>
   <body id="page-top" xboset="assets" data-appkeynum="assetnum">
      <div id="wrapper">
         <div id='sidebarmenu'></div>
         <div id="content-wrapper">
            <div id="content">
               <div class="eat-toolbars"></div>
               <div class="container-fluid">
                  <div class="maxes01">
                     <ul class="maxes02">
                        <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
                        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Fixed Asset</a></li>
                        <li id="tab_spesifikasi" class="maxes02_2"><a href="#maxes02_1_3">Specification</a></li>
                        <li id="tab_history" class="maxes02_2"><a href="#maxes02_1_4">History</a></li>
                        <li id="tab_bom" class="maxes02_2"><a href="#maxes02_1_5">Bill of Materials</a></li>
                        <li id="tab_meter" class="maxes02_2"><a href="#maxes02_1_6">Meters</a></li>
                        <li id="tab_productivity" class="maxes02_2"><a href="#maxes02_1_7">Productivity</a></li>
                        <li id="tab_assetmodification" class="maxes02_2"><a href="#maxes02_1_8">Performance Modification</a></li>
                        <li id="tab_mapinfo" class="maxes02_2"><a href="#maxes02_1_9">Asset Tracking</a></li>
                     </ul>
                     <div class="maxes04 shadow">
                        <div id="maxes02_1_1" class="tab maxes02_1">
                           <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                              <div class='eat_apptitle'></div>
                           </div>
                           <div class="map-accordion-wrapper">
                              <div class="map-accordion-header">
                                 <span class="map-accordion-title">
                                 <i class="fas fa-map-marked-alt"></i>
                                 Fleet Map Overview
                                 </span>
                                 <span class="map-accordion-toggle-icon">▼</span>
                              </div>
                              <div class="map-accordion-content">
                                 <div id="fleet-overview-map-container" style="height: 75vh; width: 100%;"></div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div class="page-header-list">
                                    <div class="tablelist">
                                       <table id="mxtable_assets" class="display"></table>
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
                              <div class="col-md-2">
                                 <div id="qrcoderegion">
                                    <div id='qrcode'></div>
                                 </div>
                              </div>

                              <div class="col-md-4">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Asset
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="assetnum" data-input="required" data-key="value" data-type='upper'>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="description">
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Location
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="locnum" data-lookup="locations">
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Analyzed in ML?
                                          </td>
                                          <td>
                                             <input type="checkbox" data-attribute="autotrainml">
                                          </td>
                                       </tr>
                                        <tr>
                                              <td colspan=2 align=center><button id="train_ai" class="general-button"></button>
                                                <span id="training_status_text" style="margin-left: 10px;"></span> 
                                          </td>
                                          
                                       </tr>
                                    </table>
                                 </div>
                              </div>

                              <div class="col-md-2">
                                <div id="assetHealthWidget" class="asset-health-widget">
                                    <div class="label">Asset Health Rate</div>
                                    <div class="health-score" id="asset_health_rate" data-rate=""></div>
                                 </div>
                              </div>
                              <div class="col-md-4">   
                                 <div id="assetTTFWidget" class="asset-ttf-widget">
                                    <div class="label">Predicted Time to Failure</div>
                                    <table class="ttf_table">
                                       <tr>
                                          <td>Predicted TTF</td>
                                          <td><span id="predicted_ttf"></span></td>
                                       </tr>
                                       <tr>
                                          <td>Predicted Failure Date</td>
                                          <td><span id="predicted_failure_date"></span></td>
                                       </tr>
                                       <tr>
                                          <td>Last Updated</td>
                                          <td><span id="lastupdated"></span></td>
                                       </tr>
                                       <tr>
                                          <td>AI Confidence</td>
                                          <td><span id="ai_confident"></span></td>
                                       </tr>
                                       <tr>
                                          <td colspan=2>
                                             <button id="ai_recommendation" class="general-button">✨ AI Recommendation</button>
                                          </td>
                                       </tr>
                                    </table>
                                 </div>
                              </div>

                           </div>
                           <div class="col-md-12">
                              <table width=100%>
                                 <tr>
                                    <td class="maxes_sct_tbl4">Ownership
                                    </td>
                                 </tr>
                              </table>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <table class="tbldata">
                                    <tr>
                                       <td style="vertical-align: top; padding-top: 6px;">Responsible Team</td>
                                       <td>
                                          <div class="row">
                                             <div class="col-md-3">
                                                <input type="text" data-attribute="teamnum" data-lookup="team">
                                                <i id="help_teamnum"
                                                   class="fa fa-question-circle"
                                                   style="font-size:20px; color:#007bff; margin-left:6px; cursor:pointer; vertical-align:middle;"
                                                   title="Click for help">
                                                </i>
                                             </div>
                                             <div class="col-md-9">
                                                <button id="report_downtime" class="general-button">
                                                   Report Downtime
                                                </button>

                                                <button id="set_asset_health" class="general-button">
                                                   Set Asset Health
                                                </button>
                                             </div>
                                          </div>
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                              
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Detail
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">

                              <!-- COLUMN 1 -->
                              <div class="col-md-3">
                                 <table class="tbldata">
                                    <tr><td>Status</td><td><input type="text" data-attribute="status" data-input="readonly"></td></tr>
                                    <tr><td>Status Date</td><td><input type="text" data-attribute="statusdate" data-type="date" data-input="readonly"></td></tr>
                                    <tr><td>Health Rate</td><td><input type="text" data-attribute="healthrate" data-input="readonly"></td></tr>
                                    <tr><td>Manufacturer</td><td><input type="text" data-attribute="manufacturer" data-lookup="manufacturer"></td></tr>
                                    <tr><td>Model</td><td><input type="text" data-attribute="model"></td></tr>
                                 </table>
                              </div>

                              <!-- COLUMN 2 -->
                              <div class="col-md-3">
                                 <table class="tbldata">
                                    <tr><td>Model Series</td><td><input type="text" data-attribute="modelseries"></td></tr>
                                    <tr><td>Mfc. Serial Number</td><td><input type="text" data-attribute="serialnum"></td></tr>
                                    <tr><td>Design Life (Years)</td><td><input type="text" data-attribute="designlife"></td></tr>
                                    <tr><td>Asset Type</td><td><input type="text" data-attribute="assettype" data-lookup="assettype"></td></tr>
                                    <tr><td>Sub Type</td><td><input type="text" data-attribute="subtype" data-lookup="assetsubtype"></td></tr>
                                 </table>
                              </div>

                              <!-- COLUMN 3 -->
                              <div class="col-md-3">
                                 <table class="tbldata">
                                    <tr><td>Account Code</td><td><input type="text" data-attribute="glaccount" data-input="required" data-lookup="chartofaccount"></td></tr>
                                    <tr><td>Vendor</td><td><input type="text" data-attribute="vendorcode" data-lookup="vendor"></td></tr>
                                    <tr><td>Purchased Date</td><td><input type="text" data-attribute="purchasedate" data-input="required" data-type="date"></td></tr>
                                    <tr><td>Purchase Price</td><td><input type="text" data-attribute="purchaseprice" data-type="decimal"></td></tr>
                                    <tr><td>Installed Date</td><td><input type="text" data-attribute="installdate" data-type="date"></td></tr>
                                 </table>
                              </div>

                              <!-- COLUMN 4 -->
                              <div class="col-md-3">
                                 <table class="tbldata">
                                    <tr><td>Depreciation Cost</td><td><input type="text" data-attribute="depreccost" data-type="decimal" data-input="readonly"></td></tr>
                                    <tr><td>Depreciation Type</td><td><input type="text" data-attribute="depretype" data-lookup="depretype"></td></tr>
                                    <tr><td>Customer</td><td><input type="text" data-attribute="customercode" data-lookup="customer"></td></tr>
                                    <tr><td>Entered Date</td><td><input type="text" data-attribute="enterdate" data-type="date"></td></tr>
                                    <tr><td>Company</td><td><input type="text" data-attribute="orgcode" data-input="readonly"></td></tr>
                                    <tr><td>Site</td><td><input type="text" data-attribute="sitecode" data-input="readonly"></td></tr>
                                 </table>
                              </div>

                           </div>

                           <div class="row eatfix" id='biayadepresiasi_title'>
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Depreciation Cost
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="tablelist">
                                 <!-- Asset Depreciation -->
                                 <table id="mxtable_assetdepreciation_child" class="display" data-child="assetdepreciation"></table>
                              </div>
                           </div>
                        </div>
                        <div id="maxes02_1_3" class="maxes03">
                           <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                              <div class='eat_apptitle'></div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-5">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Asset
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="assetnum" data-input="required" data-key="value" data-type='upper'>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="description">
                                          </td>
                                       </tr>
                                    </table>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix" id="spc_tl">
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Specification
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix" id='spec_conf'>
                              <div class="col-md-6">
                                 <table class="tbldata">
                                    <tr>
                                       <td>Specification</td>
                                       <td>
                                          <input type="text" data-attribute="classificationcode" data-lookup="classification" data-lookupaction="y">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>Detail Spec.</td>
                                       <td>
                                          <input type="text" data-attribute="spectext">
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix" id='spc_config'>
                              <div class="col-md-12">
                                 <span class="addrow" style="width:180px;">
                                 <a href="javascript:;" id="gendescription">Create Specification Detail</a>
                                 </span>
                                 <span class="addrow" style="width:180px;">
                                 <a href="javascript:;" id="additionalattr">Add Attribute</a>
                                 </span>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div class="tablelist">
                                    <!-- Asset Classification -->
                                    <table id="mxtable_assetclassification_child" class="display" data-child="assetclassification"></table>
                                    <div id="mxtable_assetclassification_child_detail" class="mxtable_detail">
                                       <div class="row eatfix">
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Asset</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="assetnum" data-key="value" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Attribute Name</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="attributename" data-key="value" data-input="readonly" data-childrentable="classattribute" data-childrencolumn="attributename">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Description</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="attributename.description" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Type</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="type" data-input="readonly">
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Value</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="value">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Measurement Unit</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="unit" data-lookup="measureunit">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Num.tes</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="description">
                                                   </td>
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
                                       <td class="maxes_sct_tbl4">Seat Configuration for Transporter
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div class="addrow" id="addseatconfig"></div>
                              </div>
                           </div>
                           <div id="subform-assetseatconfig" class="subform" data-childfld="child" data-child="assetseatconfig" data-table="mxtable_assetseatconfig_child">
                              <div class="col-md-4">
                                 <table class="tbldata">
                                    <tr>
                                       <td>Order</td>
                                       <td>
                                          <input type="text" data-childfld="child" data-attribute="sort" data-key="value" data-type="integer">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>Map</td>
                                       <td>
                                          <input type="text" data-childfld="child" data-attribute="map">
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-6">
                                 <div class="row eatfix">
                                    <div class="tablelist">
                                       <table id="mxtable_assetseatconfig_child" class="display" data-subform="subform-assetseatconfig"></table>
                                       <div id="mxtable_assetseatconfig_child_detail" class="mxtable_detail">
                                          <div class="row eatfix">
                                             <div class="col-md-4">
                                                <table class="tableDetailRow">
                                                   <tr>
                                                      <td>Order</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="sort" data-key="value" data-type="integer">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Map</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="map">
                                                      </td>
                                                   </tr>
                                                </table>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="row eatfix">
                                    <div class="seatwrapper">
                                       <div class="seatcontainer">
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
                              <div class="col-md-5">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Asset
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="assetnum" data-input="required" data-key="value" data-type='upper'>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="description">
                                          </td>
                                       </tr>
                                    </table>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12" id='hist_title'>
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Maintenance History
                                       </td>
                                    </tr>
                                 </table>
                                 <table class="tbldata">
                                    <tr>
                                       <td>Due Date Cost
                                       </td>
                                       <td>
                                          <input type="text" data-attribute="ytdcost" data-type="decimal" data-input="readonly">
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix" id='hist_tbl'>
                              <div class="col-md-12">
                                 <div class="tablelist">
                                    <!-- Work Order Children Table -->
                                    <table id="mxtable_workorder_child" class="display" data-child="workorder"></table>
                                    <div id="mxtable_workorder_child_detail" class="mxtable_detail">
                                       <div class="row eatfix">
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Work Order</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="wonum" data-key="value" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Description</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="description" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Work Type</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="worktype" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Work Classification</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="workclass" data-input="readonly">
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Target Start Date</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="targstartdate" data-type="date" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Target Finish Date</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="targcompdate" data-type="date" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Actual Start</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="actstartdate" data-type="date" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Actual Finish</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="actfinishdate" data-type="date" data-input="readonly">
                                                   </td>
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
                                       <td class="maxes_sct_tbl4">Asset Life Cycle
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td>Health Rate
                                       </td>
                                       <td>
                                          <input type="text" data-attribute="healthrate" data-input="readonly">
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div id="dipfChartContainer"></div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Life Cycle Statement
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div id='life-cycle-statement'>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix" id="move_tl">
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">MTBF
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div id="mtbfchart"></div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div id="mttrchart"></div>
                              </div>
                           </div>
                           <div class="row eatfix" id="move_tl">
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Movement Asset History
                                       </td>
                                    </tr>
                                 </table>
                                 <div class="tablelist">
                                    <!-- Asset Transaction Table -->
                                    <table id="mxtable_assettrans_child" class="display" data-child="assettrans"></table>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div id="maxes02_1_5" class="maxes03">
                           <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                              <div class='eat_apptitle'></div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-5">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Asset
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="assetnum" data-input="required" data-key="value" data-type='upper'>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="description">
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Parent
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="parent" data-lookup="assets" data-input="readonly">
                                          </td>
                                       </tr>
                                    </table>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix" id='sub_sp_sect'>
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl43">Sub Asset
                                       </td>
                                    </tr>
                                 </table>
                                 <div class="row eatfix">
                                    <div class="col-md-12">
                                       <div class="addrow" id="addSubAssets"></div>
                                    </div>
                                 </div>
                                 <div class="row eatfix">
                                    <div id="subform-subassets" class="subform" data-table="mxtable_assetsubs_child" data-child="assetsubs">
                                       <div class="row eatfix">
                                          <div class="col-md-4">
                                             <table class="tbldata">
                                                <tr>
                                                   <td>Sub Asset</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="subassetnum" data-key="value" data-input="required" data-lookupaction="y" data-childrentable="assets" data-childrencolumn="assetnum"
                                                         data-lookup="assets">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Description</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="subassetnum.description" data-input="readonly">
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
                                             <td>Roll Up Cost
                                             </td>
                                             <td>
                                                <input type="text" data-attribute="rollupcost" data-input="readonly" data-type="decimal">
                                             </td>
                                          </tr>
                                       </table>
                                    </div>
                                    <div class="col-md-12">
                                       <div class="page-header-list">
                                          <div class="tablelist">
                                             <!-- Asset Subs Children Table -->
                                             <table id="mxtable_assetsubs_child" class="display" data-subform="subform-subassets"></table>
                                             <div id="mxtable_assetsubs_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                   <div class="col-md-4">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Asset</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="subassetnum" data-key="value" data-childrentable="assets" data-childrencolumn="assetnum" data-lookup="assets">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Description</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="subassetnum.description" data-input="readonly">
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
                              <div class="col-md-12" id='sp_sect'>
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Critical Spareparts
                                       </td>
                                    </tr>
                                 </table>
                                 <div class="row eatfix" id='sp_conf'>
                                    <div class="col-md-12">
                                       <div class="addrow" id="addSpareparts"></div>
                                    </div>
                                 </div>
                                 <div class="row eatfix">
                                    <div id="subform-spareparts" class="subform" data-childfld="child" data-child="assetsparepart" data-table="mxtable_assetsparepart_child">
                                       <div class="col-md-4">
                                          <table class="tbldata">
                                             <tr>
                                                <td>Item</td>
                                                <td>
                                                   <input type="text" data-childfld="child" data-attribute="itemnum" data-key="value" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="item">
                                                </td>
                                             </tr>
                                             <tr>
                                                <td>Description</td>
                                                <td>
                                                   <input type="text" data-childfld="child" data-attribute="itemnum.description" data-input="readonly">
                                                </td>
                                             </tr>
                                             <tr>
                                                <td>Quantity</td>
                                                <td>
                                                   <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
                                                </td>
                                             </tr>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row eatfix" id='sp_tl'>
                                    <div class="page-header-list">
                                       <div class="tablelist">
                                          <!-- Asset Sparepart Table -->
                                          <table id="mxtable_assetsparepart_child" class="display" data-subform="subform-spareparts"></table>
                                          <div id="mxtable_assetsparepart_child_detail" class="mxtable_detail">
                                             <div class="row eatfix">
                                                <div class="col-md-4">
                                                   <table class="tableDetailRow">
                                                      <tr>
                                                         <td>Item</td>
                                                         <td>
                                                            <input type="text" data-childfld="child" data-attribute="itemnum" data-key="value" data-childrentable="item" data-childrencolumn="itemnum" data-lookup="item">
                                                         </td>
                                                      </tr>
                                                      <tr>
                                                         <td>Description</td>
                                                         <td>
                                                            <input type="text" data-childfld="child" data-attribute="itemnum.description" data-input="readonly">
                                                         </td>
                                                      </tr>
                                                      <tr>
                                                         <td>Quantity</td>
                                                         <td>
                                                            <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
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
                        <div id="maxes02_1_6" class="maxes03">
                           <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                              <div class='eat_apptitle'></div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-5">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Asset
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="assetnum" data-input="required" data-key="value" data-type='upper'>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="description">
                                          </td>
                                       </tr>
                                    </table>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div class="hmr-accordion">
                                    <div class="guidance-header" onclick="this.parentElement.classList.toggle('active')">
                                       <span class="guidance-title">How to Configure Health Monitoring Rules</span>
                                       <span class="guidance-toggle-icon">▼</span>
                                    </div>
                                    <div class="guidance-content">
                                       <p>Define the operational zones for each meter to enable automatic health diagnosis. The system uses these zones to calculate a real-time health score.</p>
                                       <div class="zone-explanation">
                                          <h6><span class="zone-dot normal"></span>Normal Zone (Low / High)</h6>
                                          <p>The ideal operating range. Inside this zone, the meter's health is <strong>100% (Healthy)</strong>. No action is needed.</p>
                                          <p><strong>Example:</strong> For a pump bearing temperature, the normal range might be <strong>0</strong> to <strong>75</strong> °C.</p>
                                       </div>
                                       <div class="zone-explanation">
                                          <h6><span class="zone-dot warning"></span>Warning Zone (Low / High)</h6>
                                          <p>The "caution" or "yellow" zone. The asset is operating outside of optimal conditions, which can lead to accelerated wear. This is an early warning.</p>
                                          <p><strong>Example:</strong> A bearing temperature between <strong>76</strong> and <strong>85</strong> °C is a warning sign. It might trigger a medium-priority inspection WO.</p>
                                       </div>
                                       <div class="zone-explanation">
                                          <h6><span class="zone-dot limit"></span>Limit Zone (Low / High)</h6>
                                          <p>The "critical" or "red" zone. Operating here poses a significant risk of catastrophic failure or safety hazards. This requires immediate action.</p>
                                          <p><strong>Example:</strong> A temperature above <strong>95</strong> °C is a critical limit. It will trigger an urgent WO and may require an emergency shutdown.</p>
                                       </div>
                                       <hr>
                                       <h6>Health Impact Weight (Scale 1-10)</h6>
                                       <p>This setting tells the system how critical this meter is to the overall health of the asset. A higher weight means any deviation will have a larger impact on the final asset health score.</p>
                                       <ul>
                                          <li><strong>1-3 (Minor):</strong> Affects efficiency or convenience (e.g., fuel consumption).</li>
                                          <li><strong>4-7 (Medium):</strong> Indicates accelerated wear or degradation (e.g., general vibration).</li>
                                          <li><strong>8-10 (Critical):</strong> Poses a risk of catastrophic failure (e.g., lube oil pressure, boiler water level).</li>
                                       </ul>
                                       <p><em>Tip: Leave a limit field as 0 if it does not apply (e.g., there is no "low limit" for temperature).</em></p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix" id='meter_tl'>
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Reliability Health Parameter
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div class="page-section-wrapper">
                                    <!-- Atau nama lain yang sesuai -->
                                    <div id="gaugeChartContainer" style="display: flex; flex-wrap: wrap; gap: 20px;">
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-4">
                                 <div class="addrow" id="addHealthAdvisor"></div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="row eatfix" id='meter_tbl'>
                                 <div class="col-md-12">
                                    <div class="tablelist">
                                       <!-- Asset Meter Configuration -->
                                       <table id="mxtable_assetmeterconfig_child" class="display" data-child="assetmeterconfig"></table>
                                       <div id="mxtable_assetmeterconfig_child_detail" class="mxtable_detail">
                                          <div class="row eatfix">
                                             <div class="col-md-3">
                                                <table class="tableDetailRow">
                                                   <tr>
                                                      <td>Meters</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="meternum" data-key="value" data-input="readonly" data-childrentable="meter" data-childrencolumn="meternum">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Description</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="meternum.description" data-input="readonly">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Health Impact Weight</td>
                                                      <td>
                                                         <input type="number" min=1 max=10 data-childfld="child" data-attribute="health_impact_weight" data-type="decimal" data-input="required">
                                                      </td>
                                                   </tr>
                                                </table>
                                             </div>
                                             <div class="col-md-3">
                                                <table class="tableDetailRow">
                                                   <tr>
                                                      <td>Normal Low</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="normal_low" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Normal High</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="normal_high" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                </table>
                                             </div>
                                             <div class="col-md-3">
                                                <table class="tableDetailRow">
                                                   <tr>
                                                      <td>Warning Low</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="warning_low" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Warning High</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="warning_high" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                </table>
                                             </div>
                                             <div class="col-md-3">
                                                <table class="tableDetailRow">
                                                   <tr>
                                                      <td>Limit Low</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="limit_low" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Limit High</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="limit_high" data-type="decimal">
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
                           <div class="row eatfix" id='meter_tl'>
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl4">Meter Readings
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div id="meterchart"></div>
                              </div>
                           </div>
                           <div class="row eatfix" id='meter_conf'>
                              <div class="col-md-4">
                                 <table class="tbldata">
                                    <tr>
                                       <td>Meter Group</td>
                                       <td>
                                          <input type="text" data-attribute="metergroupnum" data-lookup="metergroup" data-lookupaction="y">
                                       </td>
                                       <td>
                                          <div class="addrow" id="addexistingmeter"></div>
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix" id='meter_tbl'>
                              <div class="col-md-12">
                                 <div class="tablelist">
                                    <!-- Asset Meter -->
                                    <table id="mxtable_assetmeter_child" class="display" data-child="assetmeter"></table>
                                    <div id="mxtable_assetmeter_child_detail" class="mxtable_detail">
                                       <div class="row eatfix">
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Meters</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="meternum" data-key="value" data-input="readonly" data-childrentable="meter" data-childrencolumn="meternum">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Description</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="meternum.description" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Type</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="meternum.metertype" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Is Analyzed in ML?</td>
                                                   <td>
                                                      <input type="checkbox" data-childfld="child" data-attribute="isanalyze">
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Last Work Order</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="lastwo" data-lookup='workorder'>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Measurement Unit</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="unit" data-lookup="measureunit">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Lookup</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="meterlookup">
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Last Reading</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="lastreading" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Last Reading Date</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="lastreadingdate" data-type="date" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Avg. Daily Usage</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="avgdailyusage" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Predicted Due Date</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="predictedduedate" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Day(s) until due</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="daysuntildue" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Units To Go</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="unitstogo" data-input="readonly">
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
                        <div id="maxes02_1_7" class="maxes03">
                           <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                              <div class='eat_apptitle'></div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-5">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Asset
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="assetnum" data-input="required" data-key="value" data-type='upper'>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="description">
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
                                       <td class="maxes_sct_tbl4">Productivity Parameter
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-5">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Calculate Productivity Since
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="productivitydate" data-type="date">
                                          </td>
                                       </tr>
                                    </table>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-6">
                                 <table class="tbldata">
                                    <tr>
                                       <td>
                                          <div class="addrow" id="addprodparamforAsset"></div>
                                          <div class="addrow" id="recalculateproductivity"></div>
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <div class="tablelist">
                                    <!-- Asset Productivity -->
                                    <table id="mxtable_assetproductivity_child" class="display" data-child="assetproductivity"></table>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div id="maxes02_1_8" class="maxes03">
                           <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                              <div class='eat_apptitle'></div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-5">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Asset
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="assetnum" data-input="required" data-key="value" data-type='upper'>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="description">
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
                                       <td class="maxes_sct_tbl4">Modification List
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-6">
                                 <table class="tbldata">
                                    <tr>
                                       <td>
                                          <div class="addrow" id="addModification"></div>
                                       </td>
                                       <td>
                                          <div class="addrow" id="createprassetmod"></div>
                                       </td>
                                       <td>
                                          <div class="addrow" id="createroi"></div>
                                       </td>
                                       <td>
                                          <div class="addrow" id="approvemodprop"></div>
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <table width=100%>
                                 <tr>
                                    <td class="maxes_sct_tbl42">Modification Header
                                    </td>
                                 </tr>
                              </table>
                           </div>
                           <div class="row eatfix">
                              <div id="subform-assetmod" class="subform" data-childfld="child" data-child="assetmod" data-table="mxtable_assetmod_child" data-pk="assetmodid">
                                 <div class="row eatfix">
                                    <div class="col-md-6">
                                       <table class="tbldata">
                                          <tr>
                                             <td>Modification Date</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="modificationdate" data-input="required" data-type="date">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Subject</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="subject" data-input="required">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Description</td>
                                             <td>
                                                <textarea data-childfld="child" data-attribute="description"></textarea>
                                             </td>
                                          </tr>
                                       </table>
                                    </div>
                                    <div class="col-md-6">
                                       <table class="tbldata">
                                          <tr>
                                             <td>Enter By</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Enter Date</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Objective</td>
                                             <td>
                                                <textarea data-childfld="child" data-attribute="objective"></textarea>
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
                                    <!-- Asset Modifification -->
                                    <table id="mxtable_assetmod_child" class="display" data-child="assetmod" data-pk="assetmodid"></table>
                                    <div id="mxtable_assetmod_child_detail" class="mxtable_detail">
                                       <div class="row eatfix">
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Modification Date</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="modificationdate" data-input="required" data-type="date">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Subject</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="subject" data-input="required">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Description</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="description" data-input="required">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Status</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="status" data-input="readonly">
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Total Cost</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="totalcost" data-type="decimal" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Enter By</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Enter Date</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="enterdate" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Objective</td>
                                                   <td>
                                                      <textarea data-childfld="child" data-attribute="objective"></textarea>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <div class="col-md-4">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Is Approved?</td>
                                                   <td>
                                                      <input type="checkbox" data-childfld="child" data-attribute="isapproved" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Approved By</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="approvedby" data-input='readonly'>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Approval Date</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="approveddate" data-input='readonly'>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>ROI Analysis</td>
                                                   <td>
                                                      <textarea data-childfld="child" data-attribute="roianalysis" data-input='readonly' disabled></textarea>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <table width=100%>
                                 <tr>
                                    <td class="maxes_sct_tbl42">Modification Lines
                                    </td>
                                 </tr>
                              </table>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-6">
                                 <table class="tbldata">
                                    <tr>
                                       <td>
                                          <div class="addrow" id="addModificationLine"></div>
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div id="subform-assetmodline" class="subform" data-child="assetmodline" data-table="mxtable_assetmodline_child" data-pk="assetmodid">
                                 <div class="row eatfix">
                                    <div class="col-md-6">
                                       <table class="tbldata">
                                          <tr>
                                             <td>Modification ID</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-key="value" data-attribute="assetmodid">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Line</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="assetmodlinenum" data-type="integer" data-input="required">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Line Type</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="linetype" data-input="required" data-lookup="assetmodlinetype">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Item</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="item">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Description</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="description" data-input="required">
                                             </td>
                                          </tr>
                                       </table>
                                    </div>
                                    <div class="col-md-6">
                                       <table class="tbldata">
                                          <tr>
                                             <td>Quantity</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Measure Unit</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="measureunit" data-lookup="measureunit">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Unit Cost</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>Line Cost</td>
                                             <td>
                                                <input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly">
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
                                    <!-- Asset Modification Line -->	
                                    <table id="mxtable_assetmodline_child" class="display" data-child="assetmodline" data-link="mxtable_assetmod_child" data-pk="assetmodid"></table>
                                    <div id="mxtable_assetmodline_child_detail" class="mxtable_detail">
                                       <div class="row eatfix">
                                          <div class="col-md-6">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Modification ID</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="assetmodid" data-input="readonly">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Line</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="assetmodlinenum" data-type="integer" data-input="required">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Line Type</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="linetype" data-input="required" data-lookup="assetmodlinetype">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Item</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="item">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Description</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="description" data-input="required">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Item Num.</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="itemnum" data-lookup="item">
                                                      <br>* Parts OR Consumable Line Type requires Item Number from Inventory 
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <div class="col-md-6">
                                             <table class="tableDetailRow">
                                                <tr>
                                                   <td>Quantity</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="quantity" data-type="decimal">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Measure Unit</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="measureunit" data-lookup="measureunit">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Unit Cost</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="unitcost" data-type="decimal">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>Line Cost</td>
                                                   <td>
                                                      <input type="text" data-childfld="child" data-attribute="linecost" data-type="decimal" data-input="readonly">
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <table width=100%>
                                 <tr>
                                    <td class="maxes_sct_tbl42">Modification Result Prediction
                                    </td>
                                 </tr>
                              </table>
                           </div>
                           <div class="row eatfix">
                              <div class="subtab">
                                 <ul class="maxes02">
                                    <li class="maxes02_1"><a href="#meter_reading">Meter Reading</a></li>
                                    <li class="maxes02_2"><a href="#downtime">Downtime</a></li>
                                    <li class="maxes02_2"><a href="#maintenance">Maintenance</a></li>
                                    <li class="maxes02_2"><a href="#productivity">Productivity</a></li>
                                 </ul>
                              </div>
                              <div class="maxes04sub">
                                 <div id="meter_reading" class="maxes02_1">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                       Meter Reading Prediction
                                    </div>
                                    <div class="row eatfix">
                                       <div class="col-md-6">
                                          <table class="tbldata">
                                             <tr>
                                                <td>
                                                   <div class="addrow" id="addMeterPrediction"></div>
                                                   <div class="addrow" id="addExistingMeterPrediction"></div>
                                                </td>
                                             </tr>
                                          </table>
                                       </div>
                                    </div>
                                    <div class="row eatfix">
                                       <div id="subform-assetmodmeter" class="subform" data-childfld="child" data-child="assetmodmeter" data-table="mxtable_assetmodmeter_child" data-pk="assetmodid">
                                          <div class="row eatfix">
                                             <div class="col-md-6">
                                                <table class="tbldata">
                                                   <tr>
                                                      <td>Modification ID</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-key="value" data-attribute="assetmodid">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Meter</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="meternum" data-lookup="meter">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Change Type</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="changetype" data-input="required" data-lookup="assetmodchange">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Change Value</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="changevalue" data-input="required" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Measure Unit</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="measureunit" data-input="required" data-lookup="measureunit">
                                                      </td>
                                                   </tr>
                                                </table>
                                             </div>
                                             <div class="col-md-6">
                                                <table class="tbldata">
                                                   <tr>
                                                      <td>Enter By</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Enter Date</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Change By</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Change Date</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="changedate" data-type="date" data-input="readonly">
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
                                             <!-- Asset Modification Meter -->
                                             <table id="mxtable_assetmodmeter_child" class="display" data-child="assetmodmeter" data-link="mxtable_assetmod_child" data-pk="assetmodid"></table>
                                             <div id="mxtable_assetmodmeter_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                   <div class="col-md-6">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Modification ID</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-key="value" data-attribute="assetmodid" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Meter</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="meternum" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Change Type</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="changetype" data-input="required" data-lookup="assetmodchange">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Change Value</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="changevalue" data-input="required" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Measure Unit</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="measureunit" data-input="required" data-lookup="measureunit">
                                                            </td>
                                                         </tr>
                                                      </table>
                                                   </div>
                                                   <div class="col-md-6">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Enter By</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Enter Date</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Change By</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Change Date</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="changedate" data-type="date" data-input="readonly">
                                                            </td>
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
                                          <div class="maxes_sct_tbl42">
                                             Chart Comparison
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <br>
                                          <select data-childfld='child' data-attribute='meter_select_assetmodid'>
                                             <option value="">Select Modification Proposal</option>
                                          </select>
                                          <br>
                                          <select data-childfld='child' data-attribute='selectmeter'>
                                             <option value="">Select Meter</option>
                                          </select>
                                          <br>
                                          <br>
                                       </div>
                                       <div class="col-md-12">
                                          <div id="metermodChart" style='width:100%; height:400px'></div>
                                       </div>
                                    </div>
                                 </div>
                                 <div id="downtime" class="maxes03">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                       Downtime Prediction
                                    </div>
                                    <div class="row eatfix">
                                       <div class="col-md-6">
                                          <table class="tbldata">
                                             <tr>
                                                <td>Downtime Start Date</td>
                                                <td>
                                                   <input type="text" data-attribute='downtimestartdate' data-type="date">
                                                </td>
                                                <td rowspan=2>
                                                   <div class="addrow" id="addDowntimePrediction"></div>
                                                </td>
                                             </tr>
                                             <tr>
                                                <td>Downtime End Date</td>
                                                <td>
                                                   <input type="text" data-attribute='downtimeenddate' data-type="date">
                                                </td>
                                             </tr>
                                          </table>
                                       </div>
                                    </div>
                                    <div class="row eatfix">
                                       <div class="col-md-12">
                                          <div class="tablelist">
                                             <!-- Asset Modification Downtime -->
                                             <table id="mxtable_assetmoddowntime_child" class="display" data-child="assetmoddowntime" data-link="mxtable_assetmod_child" data-pk="assetmodid"></table>
                                             <div id="mxtable_assetmoddowntime_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                   <div class="col-md-6">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Modification ID</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="assetmodid" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Failure Code</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="failurecode" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Duration Downtime History (minutes) </td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="histdurationmin" data-input="readonly" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Est. Reduction(%)</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="estreduction" data-input="required" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Duration Downtime Prediction</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="predictdurationmin" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Duration Downtime Prediction</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="predictdurationmin" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Minutes Saved</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="minsaved" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                      </table>
                                                   </div>
                                                   <div class="col-md-6">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Remarks</td>
                                                            <td>
                                                               <textarea data-childfld="child" data-attribute="remarks"></textarea>
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Year</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="year" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Est. Year</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="estyear">
                                                            </td>
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
                                          <div class="maxes_sct_tbl42">
                                             Chart Comparison
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <br>
                                          <select data-childfld='child' data-attribute='downtime_select_assetmodid'>
                                             <option value="">Select Modification Proposal</option>
                                          </select>
                                          <br>
                                          <br>
                                       </div>
                                       <div class="col-md-12">
                                          <div id="downtimeModChart" style='width:100%; height:600px'></div>
                                       </div>
                                    </div>
                                 </div>
                                 <div id="productivity" class="maxes03">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                       Productivity Prediction
                                    </div>
                                    <div class="row eatfix">
                                       <div class="col-md-6">
                                          <table class="tbldata">
                                             <tr>
                                                <td>
                                                   <div class="addrow" id="addProductivityPrediction"></div>
                                                   <div class="addrow" id="addExistingProductivityPrediction"></div>
                                                </td>
                                             </tr>
                                          </table>
                                       </div>
                                    </div>
                                    <div class="row eatfix">
                                       <div id="subform-assetmodproductivity" class="subform" data-childfld="child" data-child="assetmodproductivity" data-table="mxtable_assetmodproductivity_child" data-pk="assetmodid">
                                          <div class="row eatfix">
                                             <div class="col-md-4">
                                                <table class="tbldata">
                                                   <tr>
                                                      <td>Modification ID</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-key="value" data-attribute="assetmodid">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Parameter Productivity</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="assetparamnum" data-lookup="addprodparam">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Potential Value</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="value" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Actual Value</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="actualvalue" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Measure Unit</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="measureunit" data-input="readonly" data-lookup="measureunit">
                                                      </td>
                                                   </tr>
                                                </table>
                                             </div>
                                             <div class="col-md-4">
                                                <table class="tbldata">
                                                   <tr>
                                                      <td>Change Type</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="changetype" data-input="required" data-lookup="assetmodchange">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Change Value</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="changevalue" data-input="required" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Predictive Value</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="estimatedvalue" data-input="readonly" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Year</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="year">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Prediction Year</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="estyear">
                                                      </td>
                                                   </tr>
                                                </table>
                                             </div>
                                             <div class="col-md-4">
                                                <table class="tbldata">
                                                   <tr>
                                                      <td>Enter By</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Enter Date</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Change By</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Change Date</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="changedate" data-type="date" data-input="readonly">
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
                                             <!-- Asset Modification Productivity -->
                                             <table id="mxtable_assetmodproductivity_child" class="display" data-child="assetmodproductivity" data-link="mxtable_assetmod_child" data-pk="assetmodid"></table>
                                             <div id="mxtable_assetmodproductivity_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                   <div class="col-md-4">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Modification ID</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="assetmodid" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Parameter Productivity</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="assetparamnum" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Potential Value</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="value" data-input="readonly" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Actual Value</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="actualvalue" data-input="readonly" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Measure Unit</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="measureunit" data-input="readonly" data-lookup="measureunit">
                                                            </td>
                                                         </tr>
                                                      </table>
                                                   </div>
                                                   <div class="col-md-4">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Change Type</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="changetype" data-input="required" data-lookup="assetmodchange">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Change Value</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="changevalue" data-input="required" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Predictive Value</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="estimatedvalue" data-input="readonly" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Year</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="year">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Prediction Year</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="estyear">
                                                            </td>
                                                         </tr>
                                                      </table>
                                                   </div>
                                                   <div class="col-md-4">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Enter By</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Enter Date</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Change By</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Change Date</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="changedate" data-type="date" data-input="readonly">
                                                            </td>
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
                                          <div class="maxes_sct_tbl42">
                                             Chart Comparison
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <br>
                                          <select data-childfld='child' data-attribute='productivity_select_assetmodid'>
                                             <option value="">Select Modification Proposal</option>
                                          </select>
                                          <br>
                                          <br>
                                       </div>
                                       <div class="col-md-12">
                                          <div id="productivityModChart" style='width:100%; height:500px'></div>
                                       </div>
                                    </div>
                                 </div>
                                 <div id="maintenance" class="maxes03">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                       Maintenance Prediction
                                    </div>
                                    <blockquote class="blockquote">
                                       <p style="font-size:11pt">
                                          <b>Important Note:</b> Estimating the impact on future maintenance costs is a critical part of the modification analysis. While many modifications aim to reduce these costs, it's also possible for costs
                                          to increase.
                                       </p>
                                       <footer class="blockquote-footer">
                                          Consider factors such as increased complexity, specialized parts, or new training requirements. Accurate estimates, whether positive or negative, will ensure better decision-making and planning.
                                       </footer>
                                    </blockquote>
                                    <div class="row eatfix">
                                       <div class="col-md-6">
                                          <table class="tbldata">
                                             <tr>
                                                <td>
                                                   <div class="addrow" id="addMaintenancePrediction"></div>
                                                </td>
                                             </tr>
                                          </table>
                                       </div>
                                    </div>
                                    <div class="row eatfix">
                                       <div id="subform-assetmodmaintenance" class="subform" data-childfld="child" data-child="assetmodmaintenance" data-table="mxtable_assetmodmaintenance_child" data-pk="assetmodid">
                                          <div class="row eatfix">
                                             <div class="col-md-6">
                                                <table class="tbldata">
                                                   <tr>
                                                      <td>Modification ID</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-key="value" data-attribute="assetmodid">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Current YTD Cost</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="currentytdcost" data-input="readonly" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Change(%)</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="ytdcostchange" data-input="required" data-type="decimal">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Predicted YTD Cost</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="predictedytdcost" data-input="readonly" data-type="decimal">
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
                                             <div class="col-md-6">
                                                <table class="tbldata">
                                                   <tr>
                                                      <td>Year</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="year" data-input="required">
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td>Prediction Year</td>
                                                      <td>
                                                         <input type="text" data-childfld="child" data-attribute="estyear" data-input="required">
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
                                             <!-- Asset Modification Maintenance -->
                                             <table id="mxtable_assetmodmaintenance_child" class="display" data-child="assetmodmaintenance" data-link="mxtable_assetmod_child" data-pk="assetmodid"></table>
                                             <div id="mxtable_assetmodmaintenance_child_detail" class="mxtable_detail">
                                                <div class="row eatfix">
                                                   <div class="col-md-4">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Modification ID</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="assetmodid" data-input="readonly">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Current YTD Cost</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="currentytdcost" data-input="readonly" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Change(%)</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="ytdcostchange" data-input="required" data-type="decimal">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Predicted YTD Cost</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="predictedytdcost" data-input="readonly" data-type="decimal">
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
                                                   <div class="col-md-4">
                                                      <table class="tableDetailRow">
                                                         <tr>
                                                            <td>Year</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="year" data-input="required">
                                                            </td>
                                                         </tr>
                                                         <tr>
                                                            <td>Prediction Year</td>
                                                            <td>
                                                               <input type="text" data-childfld="child" data-attribute="estyear" data-input="required">
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
                        <div id="maxes02_1_9" class="maxes03">
                           <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                              <div class='eat_apptitle'></div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-5">
                                 <div class="page-header">
                                    <table class="tbldata">
                                       <tr>
                                          <td>Asset
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="assetnum" data-input="required" data-key="value" data-type='upper'>
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td>
                                             <input type="text" data-attribute="description">
                                          </td>
                                       </tr>
                                    </table>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-3">
                                 <div class="card shadow mb-4">
                                    <div class="card-header">
                                       <h6 class="m-0 font-weight-bold">History Playback</h6>
                                    </div>
                                    <div class="card-body">
                                       <div class="form-group">
                                          <label for="start-date">Start Date:</label>
                                          <input type="text" id="start-date" data-attribute="start-date" data-childfld="child" data-type="date">
                                       </div>
                                       <div class="form-group">
                                          <label for="end-date">End Date:</label>
                                          <input type="text" id="end-date" data-attribute="end-date" data-childfld="child" data-type="date">
                                       </div>
                                       <button id="show-history-btn" class="general-button">
                                       <i class="fas fa-search-location"></i> Show History
                                       </button>
                                       <button id="play-animation-btn" class="general-button" style="display:none;">
                                       <i class="fas fa-play"></i> Play Animation
                                       </button>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-9">
                                 <div class="card shadow mb-4">
                                    <div class="card-body" style="padding: 0;">
                                       <div id="assetTrackingMap" style="height: 70vh; width: 100%;"></div>
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