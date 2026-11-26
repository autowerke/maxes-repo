<!DOCTYPE html>
<html>

<head>
	<title>Document Forms</title>
	<?php
        include ("../../../applications/ui/ui.php");
    ?>
</head>
<!-- 
  Konfigurasi MAXES:
  - xboset: Terhubung ke tabel master "docform"
  - data-appkeynum: Primary business key "docformnum"
-->

<body id="page-top" xboset="docform" data-appkeynum="docformnum">
	<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Doc Form</a></li>
						</ul>
						<div class="maxes04 shadow">
							
							<!-- TAB 1: DAFTAR DOCUMENT FORMS (LIST) -->
							<div id="maxes02_1_1" class="tab maxes02_1">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_docform" class="display"></table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- TAB 2: DETAIL DOCUMENT FORM -->
							<div id="maxes02_1_2" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>

								<!-- Bagian Identitas Utama Dokumen Form -->
								<div class="row eatfix">
									<div class="col-md-4 page-header">
										<table class="tbldata">
											<tr>
												<td>Doc. Form Num.</td>
												<td>
													<input type="text" data-attribute="docformnum" data-input="required" data-key="value" data-type="upper">
												</td>
											</tr>
											<tr>
												<td>Doc. Identifier</td>
												<td>
													<input type="text" data-attribute="docformidentifier" data-input="required">
												</td>
											</tr>
											<tr>
												<td>Doc. Form Name</td>
												<td>
													<input type="text" data-attribute="docformname" data-input="required" style="width: 100%;">
												</td>
											</tr>
										</table>
									</div>
									
									<!-- Bagian Revisi, Kategori & Ruang Lingkup -->
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
													<input type="text" data-attribute="olddocformnum" data-type="upper" data-input="readonly">
												</td>
											</tr>
											<tr>
												<td>Category</td>
												<td>
													<input type="text" data-attribute="category" data-lookup="doccategory">
												</td>
											</tr>
											<tr>
												<td>Scope</td>
												<td>
													<input type="text" data-attribute="scope" placeholder="e.g. Lab NDT, Workshop CNC">
												</td>
											</tr>
										</table>
									</div>

									<!-- Bagian Induk Parent (Pilih Salah Satu: Prosedur ATAU Instruksi Kerja) -->
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Parent Procedure</td>
												<td>
													<input type="text" data-attribute="proceduresnum" data-lookup="procedures" data-lookupaction="y" data-type="upper" placeholder="Isi jika menginduk SOP">
												</td>
											</tr>
											<tr>
												<td>Parent Work Instr.</td>
												<td>
													<input type="text" data-attribute="workinstrnum" data-lookup="workinstr" data-lookupaction="y" data-type="upper" placeholder="Isi jika menginduk IK">
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
								</div>

								<!-- Bagian Deskripsi dan Catatan (Remarks) -->
								<div class="row eatfix" style="border-top: 1px solid #e3e6f0; padding-top: 15px; margin-top: 15px;">
									<div class="col-md-6">
										<h6>Form Description & Usage Purpose</h6>
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="vertical-align: top; width: 120px;">Description</td>
												<td>
													<textarea data-attribute="description" rows="5" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 6px;" placeholder="Tujuan penggunaan formulir / checklist..."></textarea>
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-6">
										<h6>Quality & Governance Remarks</h6>
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="vertical-align: top; width: 120px;">Remarks</td>
												<td>
													<textarea data-attribute="remarks" rows="5" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 6px;" placeholder="Petunjuk pengisian atau standar toleransi..."></textarea>
												</td>
											</tr>
										</table>
									</div>
								</div>
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
													<input type="text" data-attribute="statusdate" data-type="statusdate" data-input="readonly">
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