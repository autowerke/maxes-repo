<!DOCTYPE html>
<html>
<title>
</title>
<head>
	<?php
	include ("../../../applications/ui/ui.php");
	?>
	
</head>
<body id="page-top" xboset="report" data-appkeynum = "reportname">
<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
<div class="maxes01">
    <ul class="maxes02">
        <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Report</a></li>
    </ul>
    <div class="maxes04">
        <div id="maxes02_1_1" class="tab maxes02_1">
            <div class="row">
										<div class="col-xl-12 col-lg-12">
											<div clh">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													<div class='eat_apptitle'></div>
												</div>
												<div class="card-body">
													<div class="row eatfix">
														<div class="page-header-list">
															<div class="tablelist">
																<div class="tablelist">
																	<table id="mxtable_report" class="display">
																	</table>
																</div>
															</div>
														</div>
													</div>
													<hr>
												</div>
											</div>
										</div>
									</div>
        </div>
 
        <div id="maxes02_1_2" class="maxes03">
		<div class="row">
										<div class="col-xl-12 col-lg-12">
											<div class="mb-4">
												<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
													<div class='eat_apptitle'></div>
												</div>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
										
			<div class="page-header">
					<table class="tbldata">
							<tr>
								<td>Report File Name
								</td>
								<td><input type="text" data-attribute="reportname"  data-input="required" data-key="value">
								</td>
							</tr>
							<tr>
								<td>Description
								</td>
								<td><input type="text" data-attribute="description">
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
						<td class="maxes_sct_tbl">Details
						</td>
					</tr>		
				</table>
				</div>
				</div>
			<div class="row eatfix">
				<div class="col-md-4">
				<table class="tbldata">
							<tr>
								<td>Application
									</td>
									<td><input type="text" data-attribute="appname" data-lookup="mxsapp">
									</td>
							</tr>
							<tr>
								<td>Imported Date
								</td>
								<td><input type="text" data-attribute="importeddate" data-type="date" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Imported By
								</td>
								<td><input type="text" data-attribute="importedby" data-input="readonly">
								</td>
							</tr>
									
				</table>
				</div>
			</div>
			<div class="row eatfix">
			<div class ="col-md-12">
				<table width=100%>
				<tr>
					<td class="maxes_sct_tbl">Parameters</td>
				</tr>
				</table>
			</div>
			</div>
			<div class="row eatfix">
				<div class="col-md-4">
					<div class="addrow" id="addReportParameter"></div>
				</div>
			</div>	
				<div class="row eatfix col-md-12">
			<div id="subform-reportadmin-reportparameters"  class="subform" data-child="reportparameter" data-table="mxtable_reportparameter_child">
				
				<div class="col-md-6">
					<table class="tbldata">
							<tr>
								<td>Parameter Name</td>
								<td  colspan=2><input type="text" data-childfld="child" data-attribute="parametername"  data-childrentable="reportparameter" data-input="required"  data-key="value"></td>
								<td> <font color=red>*</font> BIRT Parameter Name</td>
							</tr>
							<tr>
									<td>Data Type</td>
									<td><input type="text" data-childfld="child" data-attribute="datatype" data-lookup="datatype"></td>
								</tr>
							<tr>
								<td>Lookup Name</td>
								<td  colspan=2><input type="text" data-childfld="child"  data-attribute="lookupname"></td>
							</tr>
							<tr>
								<td>Display Name</td>
								<td  colspan=2><input type="text" data-childfld="child"  data-attribute="displayname"></td>
							</tr>
							<tr>
								<td>Default Value</td>
								<td  colspan=2><input type="text" data-childfld="child"  data-attribute="defaultvalue"></td>
							</tr>
							<tr>
								<td>Sort</td>
								<td><input type="text" data-childfld="child" data-type="integer" data-attribute="sortparam" ></td>
							</tr>
					</table>
				</div>
			</div>
			</div>
			<div class="row eatfix"></div>
				<div class="col-md-12">
				<div class="tablelist">
					<table id="mxtable_reportparameter_child" class="display"  data-subform="subform-reportadmin-reportparameters">
					</table>
					<div id="mxtable_reportparameter_child_detail" class="mxtable_detail">
						<div class="row eatfix"></div>
						<div class="col-md-6">
							<table class="tableDetailRow">
								<tbody>
								<tr>
									<td>Parameter Name</td>
									<td><input type="text" data-childfld="child" data-attribute="parametername"  data-input="readonly" data-key="value" data-childrentable="reportparameter" data-childrencolumn="reportname" readonly> </td>
									<td> <font color=red>*</font> BIRT Parameter Name</td>
								</tr>	
								<tr>
									<td>Lookup Name</td>
									<td><input type="text" data-childfld="child" data-attribute="lookupname"> </td>
								</tr>
								<tr>
									<td>Data Type</td>
									<td><input type="text" data-childfld="child" data-attribute="datatype"  data-lookup="datatype" ></td>
								</tr>
								
							</table>	
						</div>	

						<div class="col-md-6">
							<table class="tableDetailRow">
								<tr>
									<td>Display Name</td>
									<td><input type="text" data-childfld="child" data-attribute="displayname" ></td>
								</tr>
								<tr>
									<td>Default Value</td>
									<td><input type="text" data-childfld="child" data-attribute="defaultvalue" ></td>
								</tr>
								<tr>
									<td>Sort</td>
									<td><input type="text" data-childfld="child" data-type="integer" data-attribute="sortparam" ></td>
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
</body>
</html>
