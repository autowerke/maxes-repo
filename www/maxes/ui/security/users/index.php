<!DOCTYPE html>
<html>
<title>
</title>

<head>
	<?php
	include ("../../../applications/ui/ui.php");
	?>
</head>
<body id="page-top" xboset="mxsusers" data-appkeynum = "userid">
<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
<div class="maxes01">
    <ul class="maxes02">
        <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">User Detail</a></li>
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
													<table id="mxtable_mxsusers" class="display"></table>
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
								<td>User ID
								</td>
								<td><input type="text" data-attribute="userid"  data-input="required" data-key="value">
								</td>
							</tr>
							<tr>
								<td>User Name
								</td>
								<td><input type="text" data-attribute="username">
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
				<table class="tbldata">
							<tr>
								<td>Person
									</td>
									<td><input type="text"  data-attribute="personid"  data-childrentable="employees" data-childrencolumn="personid"  data-lookup="employees" data-input="required">
									</td>
							</tr>
							<tr>
								<td>First Name
								</td>
								<td><input type="text" data-childfld="child" data-attribute="personid.firstname" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Last Name
								</td>
								<td><input type="text" data-childfld="child" data-attribute="personid.lastname" data-input="readonly">
								</td>
							</tr>
									
				</table>
				
			</div>
			<div class="col-md-4">
				<table class="tbldata">
							<tr>
								<td>Full Name
									</td>
									<td><input type="text" data-childfld="child" data-attribute="personid.fullname"  data-input="readonly">
									</td>
							</tr>
							<tr>
								<td>Primary Phone
								</td>
								<td><input type="text" data-childfld="child" data-attribute="personid.primaryphone" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Last Name
								</td>
								<td><input type="text" data-childfld="child" data-attribute="personid.lastname" data-input="readonly">
								</td>
							</tr>
									
				</table>
				
			</div>
			<div class="col-md-4">
				<table class="tbldata">
							<tr>
								<td>Address
									</td>
									<td><input type="text" data-childfld="child" data-attribute="personid.address"  data-input="readonly">
									</td>
							</tr>
							<tr>
								<td>City
								</td>
								<td><input type="text" data-childfld="child" data-attribute="personid.city" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Province
								</td>
								<td><input type="text" data-childfld="child" data-attribute="personid.stateprovince" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Postal Code
								</td>
								<td><input type="text" data-childfld="child" data-attribute="personid.zippostalcode" data-input="readonly">
								</td>
							</tr>
							<tr>
								<td>Country
								</td>
								<td><input type="text" data-childfld="child" data-attribute="personid.country" data-input="readonly">
								</td>
							</tr>		
				</table>
			</div>
			</div>
			<div class="row eatfix">  
					<div class="col-md-12">
					<div class="row eatfix">
						<div class="maxes_sct_tbl">Group Authorization</div>
					</div>
				</div>
				
				<div class="row eatfix">  
				<div class="col-md-4">
						<div class="addrow" id="addGroup"></div>
						</div>
					</div>	
						
			</div>
			<div class="row eatfix">  
				<div id="subform-mxsusergroup"  class="subform" data-table="mxtable_mxsusergroup_child" data-child="mxsusergroup">
					
					<div class="col-md-12">
						<table class="tbldata">
								<tr>
									<td>Group</td>
									<td><input type="text" data-childfld="child" data-attribute="groupnum" data-key="value" data-lookup="mxsgroup" data-childrentable="mxsgroups" data-childrencolumn="groupnum"></td>
								</tr>
								<tr>
									<td>Description</td>
									<td><input type="text" data-childfld="child" data-attribute="groupnum.description" data-input="readonly"></td>
								</tr>
						</table>		
					</div>
						</div>
				</div>
			
			<div class="row eatfix">
					<div class="col-md-12">
					<div class="tablelist">
						<table id="mxtable_mxsusergroup_child" class="display"  data-subform="subform-mxsusergroup">
						</table>
						<div id="mxtable_mxsusergroup_child_detail" class="mxtable_detail">
							<div class="row eatfix">
							<div class="col-md-3">
							<table class="tableDetailRow">
								<tr>
									<td>Group</td>
									<td><input type="text" data-childfld="child" data-attribute="groupnum" data-key="value" data-lookup="mxsgroup" data-childrentable="mxsgroups" data-childrencolumn="groupnum"></td>
								</tr>
								<tr>
									<td>Description</td>
									<td><input type="text" data-childfld="child" data-attribute="groupnum.description" data-input="readonly"></td>
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
