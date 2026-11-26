<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">
		<title>Inventory</title>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
<body id="page-top" xboset="mxsmodule" data-appkeynum = "modulename">


<div class="maxes01">
    <ul class="maxes02">
        <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Applications</a></li>
    </ul>
    <div class="maxes04 shadow">
        <div id="maxes02_1_1" class="tab maxes02_1">
            <div class="page-header-list">
				<div class="tablelist">
					<table id="mxtable_mxsmodule" class="display">					
					</table>
				</div>
			</div>
        </div>
 
        <div id="maxes02_1_2" class="maxes03">
			<div class="page-header">
					<table class="tbldata">
							<tr>
								<td>Module Name
								</td>
								<td><input type="text" data-attribute="modulename"  data-input="required" data-key="value">
								</td>
							</tr>
							<tr>
								<td>Description
								</td>
								<td><input type="text" data-attribute="description" data-input="required" >
								</td>
							</tr>
							<tr>
								<td>Sort
								</td>
								<td><input type="text" data-attribute="sort" data-type="integer"  data-input="required" >
								</td>
							</tr>								
						</table>				
			</div>
		
			<div class="row eatfix">  </div>
			<div class ="col-md-12">
				<table width=100%>
				<tr>
					<td class="maxes_sct_tbl">Applications</td>
				</tr>
				</table>
			</div>
			
			<div class="row eatfix">  </div>
				<div class="col-md-4">
					<div class="addrow" id="addapplication"></div>
				</div>
				<div class="row eatfix">  </div>
			<div id="subform-application" class="subform" data-child="mxsapp" data-table="mxtable_mxsapp_child">
				<div class="col-md-6">
					<table class="tbldata">
							<tr>
								<td>Application Name</td>
								<td><input type="text" data-childfld="child" data-attribute="appname" data-key="value"> </td>
								</tr>	
								<tr>
									<td>Application Deskripsi</td>
									<td><input type="text" data-childfld="child" data-attribute="description"> </td>
								</tr>
								
								<tr>
									<td>URL</td>
									<td><input type="text" data-childfld="child" data-attribute="url"></td>
								</tr>
								<tr>
									<td>Sort</td>
									<td><input type="text" data-childfld="child" data-attribute="sort"></td>
								</tr>
					</table>
				</div>
				
			</div>
			<div class="row eatfix">  </div>
				<div class="page-header-list">
				<div class="tablelist">
					<table id="mxtable_mxsapp_child" class="display"  data-subform="subform-application">
					</table>
					<div id="mxtable_mxsapp_child_detail" class="mxtable_detail">
						<div class="col-md-6">
							<table class="tableDetailRow">
								<tbody>
								<tr>
									<td>Application Name</td>
									<td><input type="text" data-childfld="child" data-attribute="appname"  data-input="readonly" data-key="value"> </td>
								</tr>	
								<tr>
									<td>Application Deskripsi</td>
									<td><input type="text" data-childfld="child" data-attribute="description"> </td>
								</tr>
								<tr>
									<td>URL</td>
									<td><input type="text" data-childfld="child" data-attribute="url"></td>
								</tr>
								<tr>
									<td>Sort</td>
									<td><input type="text" data-childfld="child" data-attribute="sort"></td>
								</tr>
							</table>	
						</div>	
					</div>
				</div>
			</div>	

						<div class="col-md-12">
							
							<div class="col-md-6">
								<table width=100%>
									<tr>
										<td class="maxes_sct_tbl">Actions <span id="mxtable_mxsaction_child_title"></span>
										</td>
									</tr>
								</table>
								<div class="addrow" id="addAppActions"></div>
							</div>
							<div class="col-md-6">
								<table width=100%>
									<tr>
										<td class="maxes_sct_tbl">Data Loader <span id="mxtable_mxsimportdata_child_title"></span>
										</td>
									</tr>
								</table>
								<div class="addrow" id="addAppDataImport"></div>
							</div>
							<div class="row eatfix">  </div>
							<div class="col-md-6">
								<div id="subform-mxsaction" class="subform" data-child="mxsaction" data-table="mxtable_mxsaction_child"  data-pk="appname">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Application</td>
												<td> <input type="text" data-childfld="child" data-attribute="appname" data-alias="actionname" data-key="value"> </td>
											</tr>
											<tr>
												<td>Action</td>
												<td> <input type="text" data-childfld="child" data-attribute="actionname" data-alias="actionname" data-key="value"> </td>
											</tr>
											<tr>
												<td>Description</td>
												<td> <input type="text" data-childfld="child" data-attribute="description" > </td>
											</tr>
											<tr>
												<td>Sort</td>
												<td> <input type="text" data-childfld="child" data-attribute="Sort" data-type="integer"> </td>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div id="subform-importdata" class="subform" data-child="mxsimportdata" data-table="mxtable_mxsimportdata_child"  data-pk="appname">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Application</td>
												<td> <input type="text" data-childfld="child" data-attribute="appname" data-key="value"> </td>
											</tr>
											<tr>
												<td>Object Name</td>
												<td> <input type="text" data-childfld="child" data-attribute="objectname"> </td>
											</tr>
											<tr>
												<td>File Name</td>
												<td> <input type="text" data-childfld="child" data-attribute="filename"> </td>
											</tr>
											<tr>
												<td>Action Name</td>
												<td> <input type="text" data-childfld="child" data-attribute="actionname"> </td>
											</tr>
											<tr>
												<td>Description</td>
												<td> <input type="text" data-childfld="child" data-attribute="description" > </td>
											</tr>
										</table>
									</div>
								</div>
							</div>
							
							
							
							
							<div class="row eatfix">  </div>
							<div class="col-md-6">
								<div class="page-header-list">
									<div class="tablelist">
										<table id="mxtable_mxsaction_child" class="display" data-subform="subform-mxsaction" data-link="mxtable_mxsapp_child">
										</table>
										<div id="mxtable_mxsaction_child_detail" class="mxtable_detail">
											<div class="col-md-6">
												<table class="tableDetailRow">
													<tr>
														<td>Application</td>
														<td> <input type="text" data-childfld="child" data-attribute="appname" data-alias="appname" data-key="value"> </td>
													</tr>
													<tr>
														<td>Action</td>
														<td> <input type="text" data-childfld="child" data-attribute="actionname" data-alias="actionname" data-key="value"> </td>
													</tr>
													<tr>
														<td>Description</td>
														<td> <input type="text" data-childfld="child" data-attribute="description" > </td>
													</tr>
													<tr>
														<td>Sort</td>
														<td> <input type="text" data-childfld="child" data-attribute="Sort" data-type="integer"> </td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>		
							<div class="col-md-6">
								<div class="page-header-list">
									<div class="tablelist">
										<table id="mxtable_mxsimportdata_child" class="display" data-subform="subform-importdata" data-link="mxtable_mxsapp_child">
										</table>
										<div id="mxtable_mxsimportdata_child_detail" class="mxtable_detail">
											<div class="col-md-6">
												<table class="tableDetailRow">
													<tr>
														<td>Application</td>
														<td> <input type="text" data-childfld="child" data-attribute="appname" data-alias="appname" data-key="value"> </td>
													</tr>
													<tr>
														<td>Object Name</td>
														<td> <input type="text" data-childfld="child" data-attribute="objectname" > </td>
													</tr>
													<tr>
														<td>File Name</td>
														<td> <input type="text" data-childfld="child" data-attribute="filename" > </td>
													</tr>
													<tr>
														<td>Action Name</td>
														<td> <input type="text" data-childfld="child" data-attribute="actionname"> </td>
													</tr>
													<tr>
														<td>Description</td>
														<td> <input type="text" data-childfld="child" data-attribute="description" > </td>
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
