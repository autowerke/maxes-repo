<!DOCTYPE html>
<html>
<head>
    <title>Social Reservation</title>
    <?php include ("../../../applications/ui/ui.php"); ?>
	<style>
			#resourceconflict_display{
				display:none;
			}
	</style>
</head>
<body id="page-top" xboset="invreserve" data-appkeynum="invreservenum">
    <div id="wrapper">
        <div id='sidebarmenu'></div>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="eat-toolbars"></div>
                <div class="container-fluid">
				
				
					<div class="maxes01">
						<div class="row eatfix">
							<button id="resourceconflict" class="general-button">
								<i class="fas fa-balance-scale-left"></i> Resource Conflict Management
							</button>
						</div>
					</div>
					
					<div id="resourceconflict_display" class="maxes01 graphdisplay">
						<div class="row eatfix">
							<div class="col-md-12 maxes04">
							<br>
								<h4>Resource Conflict Management</h4>
								<hr>
								<div id="resourceconflict_chart" style="width:100%; height:850px"></div>
							</div>
							
						</div>	
					</div>
					
					
                    <div id="default_display" class="maxes01">
                        <ul class="maxes02">
                            <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
                            <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Reservation Detail</a></li>
                            
                        </ul>
                        <div class="maxes04 shadow">
                            <!-- Tab: Daftar Reservasi (List) -->
                            <div id="maxes02_1_1" class="tab maxes02_1">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-12">
                                        <div class="page-header-list">
                                            <div class="tablelist">
                                                <table id="mxtable_invreserve" class="display"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Detail Reservasi (Header) -->
                            <div id="maxes02_1_2" class="maxes03">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class='eat_apptitle'></div>
                                </div>
                                <div class="row eatfix">
                                    <div class="col-md-6">
                                        <div class="page-header">
                                            <table class="tbldata">
                                                <tr>
                                                    <td>Reservation #</td>
                                                    <td><input type="text" data-attribute="invreservenum" data-input="required" data-key="value"></td>
                                                </tr>
                                                <tr>
                                                    <td>Work Order</td>
                                                    <td><input type="text" data-attribute="wonum" data-input="readonly"></td>
                                                </tr>
                                                <tr>
                                                    <td>Item Number</td>
                                                    <td><input type="text" data-attribute="itemnum"  data-input="readonly" data-childrentable="item" data-childrencolumn="itemnum"></td>
                                                </tr>
												 <tr>
                                                    <td>Item Number</td>
                                                    <td><input type="text" data-attribute="itemnum.description"  data-input="readonly"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan=2><div class="addrow" id="replaceItem"></div><div class="addrow" id="releaseReservation"></div></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl2">Details</td>
											</tr>
										</table>
									</div>
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Quantity</td>
												<td><input type="text" data-attribute="quantity"  data-input="readonly"></td>
											</tr>
											<tr>
												<td>Warehouse</td>
												<td><input type="text" data-attribute="warehouse"  data-input="readonly"></td>
											</tr>
											<tr>
												<td>Rack</td>
												<td><input type="text" data-attribute="racknum"  data-input="readonly"></td>
											</tr>
											<tr>
												<td>Status</td>
												<td><input type="text" data-attribute="status" data-input="readonly"></td>
											</tr>
											<tr>
												<td>Reserved For</td>
												<td><input type="text" data-attribute="reservedfor" data-input="readonly"></td>
											</tr>
										</table>
									</div>
									<div class="col-md-6">	
									<table class="tbldata">
											<tr>
												<td>Negotiation Status</td>
												<td><input type="text" data-attribute="negotiationstatus"  data-input="readonly"></td>
											</tr>
											<tr>
												<td>Reference ID</td>
												<td><input type="text" data-attribute="negotiationrefid"  data-input="readonly"></td>
											</tr>
											
										</table>	
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width=100%>
											<tr>
												<td class="maxes_sct_tbl">Negotiations</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-6">
										<div class="addrow" id="negotiateThisReservation"></div>
										<div class="addrow" id="replyThisNegotiation"></div>
										<div class="addrow" id="changeStatusThisNegotiation"></div>
										
									</div>
								
								</div>
								<div class="row eatfix">
									
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_reservationswap_child" class="display" data-child="reservationswap"></table>
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