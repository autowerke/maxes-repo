<!DOCTYPE html>
<html>

<head>
	<title>SK Team</title>
	<?php
        include ("../../../applications/ui/ui.php");
    ?>
</head>
<!-- 
  Konfigurasi MAXES:
  - xboset: Terhubung ke tabel master "teamdecree"
  - data-appkeynum: Primary business key "teamdecreenum"
-->

<body id="page-top" xboset="teamdecree" data-appkeynum="teamdecreenum">
	<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Team Decree (SK)</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_3">Team Members</a></li>
						</ul>
						<div class="maxes04 shadow">
							
							<!-- TAB 1: DAFTAR SK TIM (LIST) -->
							<div id="maxes02_1_1" class="tab maxes02_1">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_teamdecree" class="display"></table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- TAB 2: DETAIL SK TIM -->
							<div id="maxes02_1_2" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>

								<!-- Bagian Identitas Utama Dokumen SK -->
								<div class="row eatfix">
									<div class="col-md-4 page-header">
										<table class="tbldata">
											<tr>
												<td>Decree Num.</td>
												<td>
													<input type="text" data-attribute="teamdecreenum" data-input="required" data-key="value" data-type="upper">
												</td>
											</tr>
											<tr>
												<td>Decree Title</td>
												<td>
													<input type="text" data-attribute="decreetitle" data-input="required" style="width: 100%;">
												</td>
											</tr>
											<tr>
												<td>Decree Type</td>
												<td>
													<input type="text" data-attribute="decreetype" data-lookup="doctype">
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Start Date</td>
												<td>
													<input type="text" data-attribute="startdate" data-type="date" data-input="required">
												</td>
											</tr>
											<tr>
												<td>End Date</td>
												<td>
													<input type="text" data-attribute="enddate" data-type="date" data-input="required">
												</td>
											</tr>
											
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Revised From</td>
												<td>
													<input type="text" data-attribute="oldteamdecreenum" data-type="upper" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Revision</td>
												<td>
													<input type="text" data-attribute="revision" data-type="integer" data-input="readonly">
												</td>
											</tr>
										</table>
									</div>
								</div>

								<!-- Bagian Deskripsi Ruang Lingkup dan Catatan (Remarks) -->
								<div class="row eatfix" style="border-top: 1px solid #e3e6f0; padding-top: 15px; margin-top: 15px;">
									<div class="col-md-6">
										<h6>Team Scope & Responsibility Description</h6>
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="vertical-align: top; width: 120px;">Description</td>
												<td>
													<textarea data-attribute="description" rows="5" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 6px;"></textarea>
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-6">
										<h6>Management Authority & Remarks</h6>
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="vertical-align: top; width: 120px;">Remarks</td>
												<td>
													<textarea data-attribute="remarks" rows="5" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 6px;"></textarea>
												</td>
											</tr>
										</table>
									</div>
								</div>

								<!-- Bagian Audit Trail & Status -->
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
													<input type="text" data-attribute="changeby" data-input="readonly">
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

							<!-- TAB 3: ANGGOTA SK TIM (TEAM MEMBERS) -->
							<div id="maxes02_1_3" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>

								<!-- Header Ringkasan SK Induk -->
								<div class="row eatfix page-header">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Decree Num.</td>
												<td>
													<input type="text" data-attribute="teamdecreenum" data-key="value" readonly>
												</td>
											</tr>
											<tr>
												<td>Decree Title</td>
												<td>
													<input type="text" data-attribute="decreetitle" readonly style="width: 100%;">
												</td>
											</tr>
										</table>
									</div>
								</div>

								<div class="row eatfix">
									<div class="col-md-12">
										<table width="100%">
											<tr>
												<td class="maxes_sct_tbl3">Assigned Team Personnel</td>
											</tr>
										</table>
									</div>
								</div>

								<!-- Tombol Tambah Anggota Tim -->
								<div class="row eatfix">
									<div class="col-md-3">
										<div class="addrow" id="addTeamMember"></div>
									</div>
								</div>

								<!-- Subform Input Child Member -->
								<div class="row eatfix">
									<div class="col-md-12">
										<div id="subform-teamdecreemember" class="subform" data-child="teamdecreemember" data-table="mxtable_teamdecreemember_child">
											<div class="row eatfix">
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td>Person ID</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="personid" data-lookup="employees" data-key="value" data-type="upper">
															</td>
														</tr>
														<tr>
															<td>Role / Position</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="memberrole" placeholder="e.g. Ketua Tim, Lead Engineer">
															</td>
														</tr>
														<tr>
															<td>Division / Unit</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="division" placeholder="e.g. Reverse Engineering">
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-4">
													<table class="tbldata">
														<tr>
															<td>Task Notes</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="notes" style="width: 100%;" placeholder="e.g. Penanggung jawab validasi CAD">
															</td>
														</tr>
														<tr>
															<td>Remarks</td>
															<td>
																<input type="text" data-childfld="child" data-attribute="remarks" style="width: 100%;">
															</td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Child Table Grid & Detail Drawer -->
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_teamdecreemember_child" class="display" data-child="teamdecreemember" data-subform="subform-teamdecreemember"></table>
											
											<!-- Drawer Detail Saat Baris Anggota Di-klik -->
											<div id="mxtable_teamdecreemember_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="row eatfix">
															
															<!-- Kolom Kiri: Detail Personil -->
															<div class="col-md-5">
																<table class="tableDetailRow">
																	<tr>
																		<td>Person ID</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="personid" data-lookup="resources" data-key="value" data-type="upper">
																		</td>
																	</tr>
																	<tr>
																		<td>Role / Position</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="memberrole">
																		</td>
																	</tr>
																	<tr>
																		<td>Division / Unit</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="division">
																		</td>
																	</tr>
																	<tr>
																		<td>Task Notes</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="notes">
																		</td>
																	</tr>
																	<tr>
																		<td style="vertical-align: top;">Description</td>
																		<td>
																			<textarea data-childfld="child" data-attribute="description" rows="2" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 4px;"></textarea>
																		</td>
																	</tr>
																	<tr>
																		<td style="vertical-align: top;">Remarks</td>
																		<td>
																			<textarea data-childfld="child" data-attribute="remarks" rows="2" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 4px;"></textarea>
																		</td>
																	</tr>
																</table>
															</div>

															<!-- Kolom Kanan: Audit Trail Anggota -->
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
											<!-- Akhir Drawer Detail -->

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