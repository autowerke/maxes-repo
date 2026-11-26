<!DOCTYPE html>
<html>
<head>
    <title>Service Items</title>
    <?php
        include ("../../../applications/ui/ui.php");
    ?>
</head>
<!-- 
  Perubahan Kunci:
  - xboset: Diubah menjadi "serviceitems" agar terhubung dengan tabel yang benar.
  - data-appkeynum: Diubah menjadi "serviceitemnum" sebagai kunci unik.
-->
<body id="page-top" xboset="serviceitems" data-appkeynum="serviceitemnum">
    <div id="wrapper">
        <div id='sidebarmenu'></div>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="eat-toolbars"></div>
                <div class="container-fluid">
                    <div class="maxes01">
                        <ul class="maxes02">
                            <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
                            <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Service Item</a></li>
                            <li class="maxes02_2"><a href="#maxes02_1_3">Transactions</a></li>
                        </ul>
                        <div class="maxes04 shadow">
                            <!-- TAB DAFTAR -->
                            <div id="maxes02_1_1" class="tab maxes02_1">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="page-header-list">
                                            <div class="tablelist">
                                                <!-- ID Tabel disesuaikan -->
                                                <table id="mxtable_serviceitems" class="display"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB DETAIL -->
                            <div id="maxes02_1_2" class="maxes03">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>

                                <!-- Bagian Detail Utama -->
                                <div class="row eatfix">
                                    <div class="col-md-4">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Service #</td>
                                                <td><input type="text" data-attribute="serviceitemnum" data-input="required" data-key="value" data-type="upper"></td>
                                            </tr>
                                            <tr>
                                                <td>Description</td>
                                                <td><input type="text" data-attribute="description" data-input="required" style="width: 100%;"></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Service Group</td>
                                                <td><input type="text" data-attribute="servicegroup" data-lookup="servicegroup"></td>
                                            </tr>
                                            <tr>
                                                <td>Status</td>
                                                <td>
                                                    <input type="text" data-attribute="status">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
									<div class="col-md-4">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Cost Volatility Risk (%)</td>
                                                <td><input type="text" data-attribute="costvolatilityrisk"  data-type="decimal"></td>
                                            </tr>
                                            <tr>
                                                <td>Lead Time Risk (%)</td>
                                                <td>
                                                    <input type="text" data-attribute="leadtimerisk" data-type="decimal">
                                                </td>
                                            </tr>
											<tr>
                                                <td>Complexity Risk (%)</td>
                                                <td>
                                                    <input type="text" data-attribute="complexityrisk" data-type="decimal">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
								
                                <!-- Bagian Atribut & Biaya -->
                                <div class="row eatfix" style="border-top: 1px solid #e3e6f0; padding-top: 15px; margin-top: 15px;">
                                    <div class="col-md-6">
                                        <h6>Attributes & Costing</h6>
										<br>
                                        <table class="tbldata">
                                            <tr>
                                                <td>Service Type</td>
                                                <td>
												<input type="text" data-attribute="servicetype" data-lookup="servicetype">
                                                   
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Service Unit</td>
                                                <td>
                                                    <input type="text" data-attribute="serviceunit" data-lookup="serviceunit">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Standard Cost</td>
                                                <td><input type="text" data-attribute="stdcost" data-type="decimal"></td>
                                            </tr>
                                            <tr>
                                                <td>Standard Duration (Hours)</td>
                                                <td><input type="text" data-attribute="stdduration" data-type="decimal"></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Resources & Financials</h6>
										<br>
                                        <table class="tbldata">
                                            <tr>
                                                <td>Preferred Vendor</td>
                                                <td><input type="text" data-attribute="vendorcode" data-lookup="vendor"></td>
                                            </tr>
                                            <tr>
                                                <td>Required Skill</td>
                                                <td><input type="text" data-attribute="skillnum" data-lookup="qualifications"></td>
                                            </tr>
                                            <tr>
                                                <td>GL Account</td>
                                                <td><input type="text" data-attribute="glaccount" data-lookup="chartofaccount"></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                            </div>

                            <!-- Tab Transaction -->
                            <div id="maxes02_1_3" class="maxes03">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>

                                <!-- Bagian Detail Utama -->
                                <div class="row eatfix">
                                    <div class="col-md-4">
                                        <table class="tbldata">
                                            <tr>
                                                <td>Service #</td>
                                                <td><input type="text" data-attribute="serviceitemnum" data-input="required" data-key="value" data-type="upper"></td>
                                            </tr>
                                            <tr>
                                                <td>Description</td>
                                                <td><input type="text" data-attribute="description" data-input="required" style="width: 100%;"></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div> 
                                 <div class="row eatfix">
                                    <div class="col-md-12">
                                        <table width=100%>
                                            <tr>
                                            <td class="maxes_sct_tbl3">Historical Transactions</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="tablelist">
                                            <table id="mxtable_servrectrans_child" class="display" data-child="servrectrans"></table>
                                            <div id="mxtable_servrectrans_child_detail" class="mxtable_detail">
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