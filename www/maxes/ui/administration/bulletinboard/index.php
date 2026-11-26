<!DOCTYPE html>
<html>
<head>
	 <?php
         include ("../../../applications/ui/ui.php");
         ?>
</head>
<body id="page-top" xboset="bboard" data-appkeynum = "bboardcode">



<div id="wrapper">
<div id='sidebarmenu'></div>
<div id="content-wrapper" class="d-flex flex-column">
	<div id="content">
		
<div class="eat-toolbars"></div>
<div class="container-fluid">

<div class="maxes01">
    <ul class="maxes02">
        <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Bulletin Board</a></li>
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
                                                <table id="mxtable_bboard" class="display"></table>
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
								<td>Code</td>
								<td><input type="text" data-attribute="bboardcode" data-type="upper" data-input="required" data-key="value"></td>
							</tr>
							<tr>
								<td>Judul</td>
								<td><input type="text" data-attribute="subject"></td>
							</tr>
						</table>
					</div>	
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
				<table class="tbldata">
							<tr>
								<td>Posted Date
								</td>
								<td><input type="text" data-type="date" data-attribute="enterdate">
								</td>
							</tr>
							<tr>
								<td>Entered By
								</td>
								<td><input type="text" data-type="date" data-attribute="enterby" data-lookup = "employees">
								</td>
							</tr>
							<tr>
								<td>From Date
								</td>
								<td><input type="text" data-type="date" data-attribute="fromdate">
								</td>
							</tr>
							<tr>
								<td>S/d To Date
								</td>
								<td><input type="text" data-type="date" data-attribute="todate">
								</td>
							</tr>
							<tr>
								<td>Tgl Expire
									</td>
									<td><input type="text" data-type="date" data-attribute="expireddate" data-input="required">
									</td>
							</tr>
							<tr>
								<td>Message
								</td>
								<td><textarea type="text" data-attribute="message" width=350 height=250></textarea>
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
