<!DOCTYPE html>
<html lang="en">
   <head>
      <?php
         include ("../../../applications/ui/ui.php");
         ?>
   </head>
   <body id="page-top" xboset = "teams" data-appkeynum='teamnum'>
      <div id="wrapper">
         <div id='sidebarmenu'></div>
         <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
               <div class="eat-toolbars"></div>
               <div class="container-fluid">
                  <!-- Content TAB PANEL -->
                  <div class="maxes01">
                     <ul class="maxes02">
                        <li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
                        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Team</a></li>
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
													<table id="mxtable_teams" class="display"></table>
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
                                          <td>Team ID
                                          </td>
                                          <td><input type="text" data-attribute="teamnum" data-type="upper" data-input="required" data-key="value">
                                          </td>
                                       </tr>
                                       <tr>
                                          <td>Description
                                          </td>
                                          <td><input type="text" data-attribute="description">
                                          </td>
                                    </table>
                                 </div>
                              </div>
                           </div>
                           <div class="row eatfix">
                              <div class="col-md-12">
                                 <table width=100%>
                                    <tr>
                                       <td class="maxes_sct_tbl">Team Member</td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
						   <div class="row eatfix">
							<div class="col-md-4">
								<div class="addrow" id="addTeamMember"></div>
							</div>
						   </div>
						   <div class="row eatfix">
								<div id="subform-teammember" class="subform" data-child="empteam" data-table="mxtable_empteam_child">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Num.</td>
												<td colspan=2><input type="text" data-childfld="child" data-attribute="empteamlinenum" data-input="required" data-type="integer"></td>
											</tr>
											<tr>
												<td>Person ID</td>
												<td colspan=2><input type="text" data-childfld="child" data-attribute="personid" data-lookup="employees" data-key="value"></td>
											</tr>
											<tr>
												<td>Is Leader?</td>
												<td colspan=2><input type="checkbox" data-childfld="child" data-attribute="leader"></td>
											</tr>
										</table>
									</div>
								</div>
						   </div>
						   
						   
						   <div class="row eatfix">
							<div class="page-header-list">
								<div class="tablelist">
									<table id="mxtable_empteam_child" class="display" data-subform="subform-teammember"></table>
										<div id="mxtable_empteam_child_detail" class="mxtable_detail">
										
										<div class="col-md-6">
											<table class="tableDetailRow">
												<tr>
													<td>Num.</td>
													<td colspan=2><input type="text" data-childfld="child" data-attribute="empteamlinenum" data-input="required" data-key="value" data-type="integer"></td>
												</tr>
												<tr>
													<td>Person ID</td>
													<td colspan=2><input type="text" data-childfld="child" data-attribute="personid" data-lookup="employees"></td>
												</tr>
												<tr>
													<td>Is Leader?</td>
													<td colspan=2><input type="checkbox" data-childfld="child" data-attribute="leader"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-6">
											<table class="tableDetailRow">
												<tr>
													<td>Skill Level</td>
													<td colspan=2><input type="text" data-childfld="child" data-attribute="skilllevel" ></td>
												</tr>
												<tr>
													<td>Priority</td>
													<td colspan=2><input type="text" data-childfld="child" data-attribute="priority" ></td>
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
                  <!-- END TAB PANEL -->
				  
               </div>
            </div>
			
         </div>
		
      </div>
    
   </body>
</html>