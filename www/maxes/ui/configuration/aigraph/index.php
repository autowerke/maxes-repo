<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
		<style>
			
			.kg-container {
				padding: 12px 16px;
				border-bottom: 1px solid #e5e7eb;
				background: #f9fafb;
			}

			.kg-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
			}

			.kg-title {
				font-size: 14px;
				font-weight: 600;
				color: #111827;
				letter-spacing: 0.5px;
				text-transform: uppercase;
			}

			.kg-actions {
				display: flex;
				align-items: center;
				gap: 10px;
			}

			.kg-search {
				display: flex;
				align-items: center;
				gap: 6px;
			}

			.kg-input {
				width: 200px;
				height: 32px;
				font-size: 13px;
			}


			/* Wrapper */
.kg-wrapper {
    display: flex;
    flex-direction: column;
    height: 100%;
	margin: 0 !important;
}

/* Sticky header */
.kg-sticky {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

/* Content area */
.kg-content {
    overflow: none;
    height: 100%;
}

/* Graph container */
#kgraph_container {
    width: 100%;
    min-height: 1000px;
}

/* Optional: biar lebih "panel feel" */
.kg-wrapper {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
	display: flex;
}


#knowledge_graph_container.col-md-7 {
  margin: 0;
  padding: 0 10px 0 0;
}

		</style>
	</head>
	<body id="page-top" xboset="mxsaidomain" data-appkeynum = "domainname">
		<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Detail</a></li>
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
												<table id="mxtable_mxsaidomain" class="display"></table>
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
									<div id="knowledge_graph_properties" class="col-md-5">
										<div class="page-header">
											<table class="tbldata">
												<tr>
													<td>Domain Name</td>
													<td><input type="text" data-attribute="domainname"  data-input="required" data-key="value" data-type="upper"></td>
												</tr>
												<tr>
													<td>Description</td>
													<td><input type="text" data-attribute="description"></td>
												</tr>
											</table>
										</div>
										<div class="row eatfix">
											<div class="row eatfix">
												<div class="subtab" id="tab_ceb">
													<ul class="maxes02">
														<li class="maxes02_1"><a href="#domaintables">Tables</a></li>
														<li class="maxes02_2"><a href="#domaincolumns">Columns</a></li>
														<li class="maxes02_2"><a href="#domainrelations">KG Relations</a></li>
														<li class="maxes02_2"><a href="#domaintools">Tools</a></li>
														<li class="maxes02_2"><a href="#domainddq">SQL Definitions</a></li>
													</ul>
												</div>
												<div class="maxes04sub">
													<div id="domaintables" class="maxes02_1">
														<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
															Tables
														</div>
														<!-- Domain Tables -->
														<div class="row eatfix">
															<div class="col-md-4">
																<div class="addrow" id="adddomaintables"></div>
															</div>
														</div>
														<div class="row eatfix">
															<div class="col-md-12">
																<div id="subform-mxsaitabledomains" class="subform" data-child="mxsaitabledomains" data-table="mxtable_mxsaitabledomains_child">
																	<div class="row eatfix">
																		<div class="col-md-12">
																			<table class="tbldata">
																				<tr>
																					<td>Table Name
																					</td>
																					<td>
																						<input type="text" data-childfld="child" data-attribute="tablename" data-input="required">
																					</td>
																				</tr>
																			</table>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="row eatfix">
															<div class="col-md-12 ">
																<table width=100%>
																	<tr>
																		<td class="maxes_sct_tbl4">Tables</td>
																	</tr>
																</table>
																<div class="tablelist">
																	<table id="mxtable_mxsaitabledomains_child" class="display" data-child="mxsaitabledomains"></table>
																	<div id="mxtable_mxsaitabledomains_child_detail" class="mxtable_detail">
																		<div class="row eatfix">
																			<div class="col-md-12">
																				<table class="tbldata">
																					<tr>
																						<td>Delete this value to redefined associated tables
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
													<div id="domaincolumns" class="maxes03">
														<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
															Columns
														</div>
														<!-- Domain Columns -->
														<div class="row eatfix">
															<div class="col-md-4">
																<div class="addrow" id="adddomaincolumns"></div>
															</div>
														</div>
														<div id="subform-mxsaicolumns" class="subform" data-child="mxsaicolumns" data-table="mxtable_mxsaicolumns_child">
															<div class="row eatfix">
																<div class="col-md-6">
																	<table class="tbldata">
																		<tr>
																			<td>Table Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="tablename" data-input="required"></td>
																		</tr>
																		<tr>
																			<td>Column Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="columnname" data-input="required"></td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td><input type="text" data-childfld="child" data-attribute="description"></td>
																		</tr>
																	</table>
																</div>
																<div class="col-md-6">
																	<table class="tbldata">
																		<tr>
																			<td>Data Type</td>
																			<td>
																				<select data-childfld="child" data-attribute="datatype">
																					<option value="string">STRING</option>
																					<option value="decimal">DECIMAL</option>
																					<option value="integer">INTEGER</option>
																					<option value="date">DATE</option>
																					<option value="datetime">DATETIME</option>
																				</select>
																			</td>
																		</tr>
																		
																		<tr>
																			<td>Allow Filter?</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="isfilter">
																			</td>
																		</tr>
																		<tr>
																			<td>Is Visible?</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="isvisible">
																			</td>
																		</tr>
																		<tr>
																			<td>Is PK?</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="isprimarykey">
																			</td>
																		</tr>

																		<tr>
																			<td>Always Positive?</td>
																			<td>
																				<input type="checkbox" data-childfld="child" data-attribute="alwayspositive">
																			</td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
														<div class="row eatfix">
															<div class="col-md-12 ">
																<table width=100%>
																	<tr>
																		<td class="maxes_sct_tbl4">Columns</td>
																	</tr>
																</table>
																<div class="tablelist">
																	<table id="mxtable_mxsaicolumns_child" class="display" data-child="mxsaicolumns"></table>
																	<div id="mxtable_mxsaicolumns_child_detail" class="mxtable_detail">
																		<!-- MODIFY mxsaicolumns --> 
																		<div class="row eatfix">
																			<div class="col-sm-3">
																				<table class="tableDetailRow">
																					<tr>
																						<td>Table Name</td>
																						<td><input type="text" data-childfld="child" data-attribute="tablename" data-key="value"></td>
																					</tr>
																					<tr>
																						<td>Column Name</td>
																						<td><input type="text" data-childfld="child" data-attribute="columnname" data-key="value"></td>
																					</tr>
																					<tr>
																						<td>Description</td>
																						<td><input type="text" data-childfld="child" data-attribute="description"></td>
																					</tr>
																				</table>
																			</div>
																			<div class="col-sm-3">
																				<table class="tableDetailRow">
																					<tr>
																						<td>Attr. Value Name</td>
																						<td><input type="text" data-childfld="child" data-attribute="lookupattribute"></td>
																					</tr>
																					<tr>
																						<td>Lookup Table</td>
																						<td><input type="text" data-childfld="child" data-attribute="lookuptable"></td>
																					</tr>
																					<tr>
																						<td>Lookup Columns</td>
																						<td><input type="text" data-childfld="child" data-attribute="lookupcolumns"></td>
																					</tr>
																					<tr>
																						<td>Lookup Value (JSON)</td>
																						<td><input type="text" data-childfld="child" data-attribute="lookupvalues"></td>
																					</tr>

																				</table>
																			</div>
																			<div class="col-sm-3">
																				<table class="tableDetailRow">
																					<tr>
																						<td>Data Type</td>
																						<td>
																							<select data-childfld="child" data-attribute="datatype">
																								<option value="string">STRING</option>
																								<option value="decimal">DECIMAL</option>
																								<option value="integer">INTEGER</option>
																								<option value="date">DATE</option>
																								<option value="datetime">DATETIME</option>
																							</select>
																						</td>
																					</tr>
																					<tr>
																						<td>Allow Filter?</td>
																						<td>
																							<input type="checkbox" data-childfld="child" data-attribute="isfilter">
																						</td>
																					</tr>
																					
																					<tr>
																						<td>Is Visible?</td>
																						<td>
																							<input type="checkbox" data-childfld="child" data-attribute="isvisible">
																						</td>
																					</tr>
																					<tr>
																						<td>Is Primary Key?</td>
																						<td>
																							<input type="checkbox" data-childfld="child" data-attribute="isprimarykey">
																						</td>
																					</tr>
																					<tr>
																						<td>Always Positive?</td>
																						<td>
																							<input type="checkbox" data-childfld="child" data-attribute="alwayspositive">
																						</td>
																					</tr>

																				</table>
																			</div>
																		</div>
																		<!--END MODIFY mxaicolumns -->
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div id="domainrelations" class="maxes03">
														<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
															Relations
														</div>
														<!-- Domain Relations -->
														<div class="row eatfix">
															<div class="col-md-4">
																<div class="addrow" id="adddomainrelations"></div>
															</div>
														</div>
														<div id="subform-mxsairelations" class="subform" data-child="mxsairelations" data-table="mxtable_mxsairelations_child">
															<div class="row eatfix">
																<div class="col-md-12">
																	<table class="tbldata">
																		<tr>
																			<td>Parent Table</td>
																			<td><input type="text" data-childfld="child" data-attribute="parenttable" data-input="required"></td>
																		</tr>
																		<tr>
																			<td>Child Table</td>
																			<td><input type="text" data-childfld="child" data-attribute="childtable" data-input="required"></td>
																		</tr>
																		<tr>
																			<td>Relation Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="relationname" data-input="required" placeholder="e.g., HASMETER"></td>
																		</tr>
																		<tr>
																			<td>Join Condition</td>
																			<td><input type="text" data-childfld="child" data-attribute="joincondition" data-input="required" placeholder="workorder.wonum = poline.wonum"></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
														<div class="row eatfix">
															<div class="col-md-12 ">
																<table width=100%>
																	<tr>
																		<td class="maxes_sct_tbl4">Relations</td>
																	</tr>
																</table>
																<div class="tablelist">
																	<table id="mxtable_mxsairelations_child" class="display" data-child="mxsairelations"></table>
																	<div id="mxtable_mxsairelations_child_detail" class="mxtable_detail">
																		<div class="row eatfix">
																			<div class="col-md-12">
																				<table class="tbldata">
																					<tr>
																						<td>Parent Table</td>
																						<td><input type="text" data-childfld="child" data-attribute="parenttable" data-key="value"></td>
																					</tr>
																					<tr>
																						<td>Child Table</td>
																						<td><input type="text" data-childfld="child" data-attribute="childtable" data-key="value"></td>
																					</tr>
																					<tr>
																						<td>Relation Name</td>
																						<td><input type="text" data-childfld="child" data-attribute="relationname" data-input="required" placeholder="e.g., HASMETER"></td>
																					</tr>
																					<tr>
																						<td>Join Condition</td>
																						<td><input type="text" data-childfld="child" data-attribute="joincondition" data-input="required" placeholder="workorder.wonum = poline.wonum"></td>
																					</tr>
																					<tr>
																						<td>Child Domain</td>
																						<td><input type="text" data-childfld="child" data-attribute="childdomain"></td>
																					</tr>
																				</table>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div id="domaintools" class="maxes03">
														<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
															Tools
														</div>
														<!-- Domain Tools -->
														<div class="row eatfix">
															<div class="col-md-4">
																<div class="addrow" id="adddomaintools"></div>
															</div>
														</div>
														<div id="subform-mxsaitools" class="subform" data-child="mxsaitools" data-table="mxtable_mxsaitools_child">
															<div class="row eatfix">
																<div class="col-md-12">
																	<table class="tbldata">
																		<tr>
																			<td>Function Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="toolname" data-input="required"></td>
																		</tr>
																		<tr>
																			<td>URL Endpoint</td>
																			<td><input type="text" data-childfld="child" data-attribute="urlendpoint" data-input="required"></td>
																		</tr>
																		<tr>
																			<td>Required Params</td>
																			<td><input type="text" data-childfld="child" data-attribute="requiredparams" placeholder="JSON format param meta"></td>
																		</tr>
																		<tr>
																			<td>Description (Instruction for AI)</td>
																			<td><textarea data-childfld="child" data-attribute="description" rows="3"></textarea></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
														<div class="row eatfix">
															<div class="col-md-12 ">
																<table width=100%>
																	<tr>
																		<td class="maxes_sct_tbl4">Tools</td>
																	</tr>
																</table>
																<div class="tablelist">
																	<table id="mxtable_mxsaitools_child" class="display" data-child="mxsaitools"></table>
																	<div id="mxtable_mxsaitools_child_detail" class="mxtable_detail">
																		<div class="row eatfix">
																			<div class="col-md-12">
																				<table class="tableDetailRow">
																					<tr>
																						<td>Function Name</td>
																						<td><input type="text" data-childfld="child" data-attribute="toolname" data-key="value"></td>
																					</tr>
																					<tr>
																						<td>URL Endpoint</td>
																						<td><input type="text" data-childfld="child" data-attribute="urlendpoint" data-key="value"></td>
																					</tr>
																					<tr>
																						<td>Required Params</td>
																						<td><input type="text" data-childfld="child" data-attribute="requiredparams" placeholder="JSON format param meta"></td>
																					</tr>
																					<tr>
																						<td>Description (Instruction for AI)</td>
																						<td><textarea data-childfld="child" data-attribute="description" rows="3"></textarea></td>
																					</tr>
																				</table>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												
													<div id="domainddq" class="maxes03">
														<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
															SQL Query Definitions
														</div>
														<!-- SQL Query Definition -->
														<div class="row eatfix">
															<div class="col-md-4">
																<div class="addrow" id="adddomainddq"></div>
															</div>
														</div>
														<div id="subform-mxsaiddq" class="subform" data-child="mxsaiddq" data-table="mxtable_mxsaiddq_child">
															<div class="row eatfix">
																<div class="col-md-12">
																	<table class="tbldata">
																		<tr>
																			<td>Query Name</td>
																			<td><input type="text" data-childfld="child" data-attribute="queryname" data-input="required"></td>
																		</tr>
																		<tr>
																			<td>SQL Template</td>
																			<td><textarea data-childfld="child" data-attribute="sqltext"  rows="5" placeholder="SELECT ... WHERE col = ?"></textarea></td>
																		</tr>
																		<tr>
																			<td>Meta Parameter</td>
																			<td><input type="text" data-childfld="child" data-attribute="parammeta" placeholder="e.g., wonum, siteid"></td>
																		</tr>
																		<tr>
																			<td>Description</td>
																			<td><input type="text" data-childfld="child" data-attribute="description"></td>
																		</tr>
																	</table>
																</div>
															</div>
														</div>
														<div class="row eatfix">
															<div class="col-md-12 ">
																<table width=100%>
																	<tr>
																		<td class="maxes_sct_tbl4">Query Definitions</td>
																	</tr>
																</table>
																<div class="tablelist">
																	<table id="mxtable_mxsaiddq_child" class="display" data-child="mxsaiddq"></table>
																	<div id="mxtable_mxsaiddq_child_detail" class="mxtable_detail">
																		<div class="row eatfix">
																			<div class="col-md-12">
																				<table class="tableDetailRow">
																					<tr>
																						<td>Query Name</td>
																						<td><input type="text" data-childfld="child" data-attribute="queryname" data-key="value"></td>
																					</tr>
																					<tr>
																						<td>SQL Template</td>
																						<td><textarea data-childfld="child" data-attribute="sqltext"  rows="5" placeholder="SELECT ... WHERE col = ?"></textarea></td>
																					</tr>
																					<tr>
																						<td>Meta Parameter</td>
																						<td><input type="text" data-childfld="child" data-attribute="parammeta" placeholder="e.g., wonum, siteid"></td>
																					</tr>
																					<tr>
																						<td>Description</td>
																						<td><input type="text" data-childfld="child" data-attribute="description"></td>
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
									<div id="knowledge_graph_container" class="col-md-7">
										<div id="knowledge_graph" class="kg-wrapper">

										<!-- HEADER (sticky) -->
										<div class="kg-container kg-sticky">
											<div class="kg-header">
												<div class="kg-title">Knowledge Graph</div>

												<div class="kg-actions">
													<div class="kg-search">
														<input 
															type="text" 
															id="kg_search_input" 
															class="form-input kg-input" 
															placeholder="Search..." 
														/>
														<button id="kg_search_btn" class="kg-btn general-button2">Search</button>
														<button id="kg_reset_btn" class="kg-btn general-button2">Reset</button>
													</div>

													<button type="button" id="mkg_btn_toggle" class="kg-btn general-button2">
														<span id="mkg_text_toggle">Maximize</span>
													</button>
												</div>
											</div>
										</div>

										<!-- GRAPH CONTENT -->
										<div class="kg-content">
											<div id="kgraph_container"></div>
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