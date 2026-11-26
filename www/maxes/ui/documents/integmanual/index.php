<!DOCTYPE html>
<html>

<head>
	<title>Manual Integration</title>
	<?php
        include ("../../../applications/ui/ui.php");
    ?>
</head>
<!-- 
  Konfigurasi MAXES:
  - xboset: Terhubung ke tabel master "integmanual"
  - data-appkeynum: Primary business key "integmanualnum"
-->

<body id="page-top" xboset="integmanual" data-appkeynum="integmanualnum">
	<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Manual Integration</a></li>
							<li class="maxes02_2"><a href="#maxes02_1_3">Supporting Documents</a></li>
						</ul>
						<div class="maxes04 shadow">
							<!-- TAB 1: DAFTAR MANUAL TERINTEGRASI (LIST) -->
							<div id="maxes02_1_1" class="tab maxes02_1">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_integmanual" class="display"></table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- TAB 2: DETAIL MANUAL TERINTEGRASI -->
							<div id="maxes02_1_2" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>

								<!-- Bagian Identitas Utama Dokumen MT -->
								<div class="row eatfix">
									<div class="col-md-4 page-header">
										<table class="tbldata">
											<tr>
												<td>Manual Num.</td>
												<td>
													<input type="text" data-attribute="integmanualnum" data-input="required" data-key="value" data-type="upper">
												</td>
											</tr>
											<tr>
												<td>Doc. Identifier</td>
												<td>
													<input type="text" data-attribute="docidentifier" data-input="required">
												</td>
											</tr>
											<tr>
												<td>Manual Title</td>
												<td>
													<input type="text" data-attribute="manualtitle" data-input="required" style="width: 100%;">
												</td>
											</tr>
											
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Edition</td>
												<td>
													<input type="text" data-attribute="edition" data-type="upper">
												</td>
											</tr>
											<tr>
												<td>Period (Year)</td>
												<td>
													<input type="text" data-attribute="period" placeholder="e.g. 2026">
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
												<td>Approval Date</td>
												<td>
													<input type="text" data-attribute="approvaldate" data-type="date">
												</td>
											</tr>
											<tr>
												<td>Previous Manual</td>
												<td>
													<input type="text" data-attribute="oldintegmanualnum" data-type="upper" data-input="readonly">
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

								<!-- Bagian Deskripsi dan Remarks -->
								<div class="row eatfix" style="border-top: 1px solid #e3e6f0; padding-top: 15px; margin-top: 15px;">
									<div class="col-md-6">
										<h6>Manual Scope & Governance Policy</h6>
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
										<h6>Management Review Remarks</h6>
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

							<!-- TAB 3: DOKUMEN PENDUKUNG (CURATED SUPPORTING DOCUMENTS) -->
							<div id="maxes02_1_3" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>

								<!-- Header Ringkasan Manual Induk -->
								<div class="row eatfix page-header">
									<div class="col-md-6">
										<table class="tbldata">
											<tr>
												<td>Manual Num</td>
												<td>
													<input type="text" data-attribute="integmanualnum" data-key="value" readonly>
												</td>
											</tr>
											<tr>
												<td>Manual Title</td>
												<td>
													<input type="text" data-attribute="manualtitle" readonly style="width: 100%;">
												</td>
											</tr>
										</table>
									</div>
								</div>

								<div class="row eatfix">
									<div class="col-md-12">
										<table width="100%">
											<tr>
												<td class="maxes_sct_tbl3">Curated Supporting Documents (SOP & IK)</td>
											</tr>
										</table>
									</div>
								</div>
								
								
								<!-- Subform Input Child Document -->
								<div class="row eatfix">
									<div class="col-md-3">
										<div class="addrow" id="addProcedures"></div>
									</div>
								</div>

								<!-- Child Table Grid -->
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="tablelist">
											<table id="mxtable_integmanualitem_child" class="display" data-child="integmanualitem" data-subform="subform-integmanualitem"></table>
											<div id="mxtable_integmanualitem_child_detail" class="mxtable_detail">
												<div class="row eatfix">
													<div class="col-md-12">
														<div class="row eatfix">
															
															<!-- Kolom Kiri: Atribut Utama Dokumen Kurasi -->
															<div class="col-md-5">
																<table class="tableDetailRow">
																	<tr>
																		<td>Procedure Num.</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="proceduresnum" data-lookup="procedures" data-key="value" data-type="upper">
																		</td>
																	</tr>
																	<tr>
																		<td>ISO Clause / Notes</td>
																		<td>
																			<input type="text" data-childfld="child" data-attribute="notes" placeholder="e.g. Klausul 8.3 Desain & Rekayasa">
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

															<!-- Kolom Kanan: Audit Trail -->
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