<!DOCTYPE html>
<html>

<head>
	<title>Procedures</title>
	<?php
        include ("../../../applications/ui/ui.php");
    ?>
</head>
<!-- 
  Konfigurasi MAXES:
  - xboset: Terhubung ke tabel master "procedures"
  - data-appkeynum: Primary business key "proceduresnum"
-->

<body id="page-top" xboset="procedures" data-appkeynum="proceduresnum">
	<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Procedure</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_3">Work Instructions</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_4">Doc. Form</a></li>
						</ul>
						<div class="maxes04 shadow">
							<!-- TAB 1: DAFTAR PROSEDUR (LIST) -->
							<div id="maxes02_1_1" class="tab maxes02_1">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_procedures" class="display"></table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- TAB 2: DETAIL PROSEDUR -->
							<div id="maxes02_1_2" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<!-- Bagian Identitas Utama Dokumen -->
								<div class="row eatfix">
									<div class="col-md-4 page-header">
										<table class="tbldata">
											<tr>
												<td>Procedure Num.</td>
												<td>
													<input type="text" data-attribute="proceduresnum" data-input="required" data-key="value" data-type="upper">
												</td>
											</tr>
											<tr>
												<td>Doc Identifier</td>
												<td>
													<input type="text" data-attribute="docidentifier" data-input="required">
												</td>
											</tr>
											<tr>
												<td>Document Name</td>
												<td>
													<input type="text" data-attribute="docname" data-input="required" style="width: 100%;">
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Revision</td>
												<td>
													<input type="text" data-attribute="revision" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Revised From</td>
												<td>
													<input type="text" data-attribute="oldproceduresnum"  data-type="upper" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Effective Date</td>
												<td>
													<input type="text" data-attribute="effectivedate" data-type="date">
												</td>
											</tr>
											<tr>
												<td>Expiry Date</td>
												<td>
													<input type="text" data-attribute="expirydate" data-type="date">
												</td>
											</tr>
											
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Doc Type</td>
												<td>
													<input type="text" data-attribute="doctype" data-lookup="doctype">
												</td>
											</tr>
											
											<tr>
												<td>Category</td>
												<td>
													<input type="text" data-attribute="category" data-lookup="doccategory">
												</td>
											</tr>
											<tr>
												<td>Parent</td>
												<td>
													<input type="text" data-attribute="integmanualnum" data-lookup="integmanual">
												</td>
											</tr>
										</table>
									</div>
								</div>
								<!-- Bagian File, Deskripsi, dan Catatan (Remarks) -->
								<div class="row eatfix" style="border-top: 1px solid #e3e6f0; padding-top: 15px; margin-top: 15px;">
									<div class="col-md-6">
										<h6>Document Description & Scope</h6>
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="vertical-align: top;">Description</td>
												<td>
													<textarea data-attribute="description" rows="4" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 6px;"></textarea>
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-6">
										
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="vertical-align: top; width: 120px;">Remarks</td>
												<td>
													<textarea data-attribute="remarks" rows="6" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 6px;"></textarea>
												</td>
											</tr>
										</table>
									</div>
								</div>
								
								<!-- Affected Team -->

								<!-- SECTION: TEAM OWNERSHIP & AFFECTED STAKEHOLDERS -->
								<div class="row eatfix" style="border-top: 1px solid #e3e6f0; padding-top: 15px; margin-top: 15px;">
									<div class="col-md-12">
										<h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-users mr-2"></i>Procedure Team Ownership & Affected Units</h6>
									</div>
									
									<!-- Kolom Kiri: Owner Team -->
									<div class="col-md-5">
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="width: 130px; font-weight: 600;">Owner Team</td>
												<td>
													<input type="text" data-attribute="ownerteam" data-lookup="team" data-type="upper" placeholder="Pilih Tim Penanggung Jawab...">
												</td>
											</tr>
										</table>
									</div>

									<!-- Kolom Kanan: Affected Teams (1 s/d 5) -->
									<div class="col-md-7">
										<div class="row">
											<div class="col-md-6">
												<table class="tbldata" style="width: 100%;">
													<tr>
														<td style="width: 120px;">Affected Team 1</td>
														<td><input type="text" data-attribute="affectedteam1" data-lookup="team" data-type="upper"></td>
													</tr>
													<tr>
														<td>Affected Team 2</td>
														<td><input type="text" data-attribute="affectedteam2" data-lookup="team" data-type="upper"></td>
													</tr>
													<tr>
														<td>Affected Team 3</td>
														<td><input type="text" data-attribute="affectedteam3" data-lookup="team" data-type="upper"></td>
													</tr>
												</table>
											</div>
											<div class="col-md-6">
												<table class="tbldata" style="width: 100%;">
													<tr>
														<td>Affected Team 4</td>
														<td><input type="text" data-attribute="affectedteam4" data-lookup="team" data-type="upper"></td>
													</tr>
													<tr>
														<td>Affected Team 5</td>
														<td><input type="text" data-attribute="affectedteam5" data-lookup="team" data-type="upper"></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>		

								<!-- END -->
								
								
								<div class="row eatfix" style="border-top: 1px solid #e3e6f0; padding-top: 15px; margin-top: 15px;">
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Enter By</td>
												<td>
													<input type="text" data-attribute="enterby" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Enter Date</td>
												<td>
													<input type="text" data-attribute="enterdate" data-type="date" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Change By</td>
												<td>
													<input type="text" data-attribute="changeby" data-type="date" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Change Date</td>
												<td>
													<input type="text" data-attribute="changedate" data-type="date" data-input="readonly">
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Status</td>
												<td>
													<input type="text" data-attribute="status" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Status Date</td>
												<td>
													<input type="text" data-attribute="statusdate" data-type="date" data-input="readonly">
												</td>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<!-- TAB 3: CHILD WORK INSTRUCTIONS (IK TURUNAN) -->
							<div id="maxes02_1_3" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<!-- Ringkasan Header Prosedur Induk -->
								<div class="row eatfix page-header">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Procedure Num</td>
												<td>
													<input type="text" data-attribute="proceduresnum" data-key="value" readonly>
												</td>
											</tr>
											<tr>
												<td>Document Name</td>
												<td>
													<input type="text" data-attribute="docname" readonly style="width: 100%;">
												</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width="100%">
											<tr>
												<td class="maxes_sct_tbl3">Related Work Instructions (IK)</td>
											</tr>
										</table>
									</div>
								</div>
								<!-- Child Grid: Instruksi Kerja yang Menginduk ke Prosedur Ini -->
								<div class="row eatfix">
									<div class="col-md-6">
										<div class="row eatfix">
											<div class="col-md-3">
												<div class="addrow" id="newWorkInstruction"></div>
											</div>
											
										</div>
									</div>
									<div class="col-md-12">
										<div id="subform-workinstructions" class="subform" data-child="workinstr" data-table="mxtable_workinstr_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Line Num. </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="workinstrlinenum" data-input="required" data-type="integer" data-key="value"> </td>
														</tr>
														<tr>
															<td>Work Instr. Num</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="workinstrnum" data-lookup="workinstr"  data-childrentable="workinstr" data-childrencolumn="workinstrnum"> </td>
														</tr>
														<tr>
															<td>Doc. Identifier </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="docidentifier"> </td>
														</tr>
														<tr>
															<td>Work Instr. Name </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="workinstrnum.workinstrname" data-input="readonly"> </td>
														</tr>
														<tr>
															<td>Category </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="category" data-lookup="doccategory"> </td>
														</tr>
														<tr>
															<td>Scope </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="scope"> </td>
														</tr>
													</table>
												</div>
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Enter By </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"> </td>
														</tr>
														<tr>
															<td>Enter Date </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"> </td>
														</tr>
														<tr>
															<td>Change By </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly"> </td>
														</tr>
														<tr>
															<td>Change Date </td>
															<td>
																<input type="text" data-childfld="child" data-attribute="changedate" data-input="readonly"> </td>
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
											<table id="mxtable_workinstr_child" class="display" data-child="workinstr" data-subform="subform-workinstructions"></table>
											<div id="mxtable_workinstr_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tableDetailRow">
																	
																	<tr>
																		<td>Work Instr. Num</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="workinstrnum" data-lookup="workinstr"  data-childrentable="workinstr" data-childrencolumn="workinstrnum"> </td>
																	</tr>
																	<tr>
																		<td>Doc. Identifier </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="docidentifier"> </td>
																	</tr>
																	<tr>
																		<td>Work Instr. Name </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="workinstrnum.workinstrname" data-input="readonly"> </td>
																	</tr>
																	<tr>
																		<td>Category </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="category" data-lookup="doccategory"> </td>
																	</tr>
																	<tr>
																		<td>Scope </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="scope"> </td>
																	</tr>
																</table>
															</div>
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td>Enter By </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="enterby" data-input="readonly"> </td>
																	</tr>
																	<tr>
																		<td>Enter Date </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="enterdate" data-type="date" data-input="readonly"> </td>
																	</tr>
																	<tr>
																		<td>Change By </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="changeby" data-input="readonly"> </td>
																	</tr>
																	<tr>
																		<td>Change Date </td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="changedate" data-input="readonly"> </td>
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
							
							<!-- TAB 4: CHILD DOC FORM (FORMULIR TERKAIT PROSEDUR) -->
							<div id="maxes02_1_4" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<!-- Ringkasan Header Prosedur Induk -->
								<div class="row eatfix page-header">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Procedure Num</td>
												<td>
													<input type="text" data-attribute="proceduresnum" data-key="value" readonly>
												</td>
											</tr>
											<tr>
												<td>Document Name</td>
												<td>
													<input type="text" data-attribute="docname" readonly style="width: 100%;">
												</td>
											</tr>
										</table>
									</div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<table width="100%">
											<tr>
												<td class="maxes_sct_tbl3">Related Document Forms (Doc. Form)</td>
											</tr>
										</table>
									</div>
								</div>
								
								<!-- Tombol Tambah Baris Doc Form -->
								<div class="row eatfix">
									<div class="col-md-6">
										<div class="row eatfix">
											<div class="col-md-3">
												<div class="addrow" id="newDocForm"></div>
											</div>
										</div>
									</div>
									<div class="col-md-12">
										<div id="subform-docform" class="subform" data-child="docform" data-table="mxtable_docform_child">
											<div class="row eatfix">
												<div class="col-md-6">
													<table class="tbldata">
														<tr>
															<td>Line Num.</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="docformlinenum" data-input="required" data-type="integer" data-key="value">
															</td>
														</tr>
														<tr>
															<td>Doc. Form Num</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="docformnum" data-lookup="docform" data-childrentable="docform" data-childrencolumn="docformnum">
															</td>
														</tr>
														<tr>
															<td>Doc. Identifier</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="docformidentifier">
															</td>
														</tr>
														<tr>
															<td>Doc. Form Name</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="docformname" data-input="readonly"  data-crossvalue="docformnum.docformname">
															</td>
														</tr>
														<tr>
															<td>Category</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="category" data-lookup="doccategory">
															</td>
														</tr>
														<tr>
															<td>Scope</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="scope">
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
																<input type="text" data-childfld="child" data-attribute="changedate" data-input="readonly">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Tabel Grid Child Doc Form & Detail Row -->
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_docform_child" class="display" data-child="docform" data-subform="subform-docform"></table>
											<div id="mxtable_docform_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="row eatfix">
															<div class="col-md-4">
																<table class="tableDetailRow">
																	<tr>
																		<td>Line Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="docformlinenum" data-type="integer" data-key="value">
																		</td>
																	</tr>
																	<tr>
																		<td>Doc. Form Num</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="docformnum" data-lookup="docform" data-childrentable="docform" data-childrencolumn="docformnum">
																		</td>
																	</tr>
																	<tr>
																		<td>Doc. Identifier</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="docformidentifier">
																		</td>
																	</tr>
																	<tr>
																		<td>Doc. Form Name</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="docformnum.docformname" data-input="readonly">
																		</td>
																	</tr>
																	<tr>
																		<td>Category</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="category" data-lookup="doccategory">
																		</td>
																	</tr>
																	<tr>
																		<td>Scope</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="scope">
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
																			<input type="text" data-childfld="child" data-attribute="changedate" data-input="readonly">
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