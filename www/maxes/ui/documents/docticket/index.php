<!DOCTYPE html>
<html>

<head>
	<title>Document Change Request (Doc Ticket)</title>
	<?php
        include ("../../../applications/ui/ui.php");
    ?>
</head>
<!-- 
  Konfigurasi MAXES:
  - xboset: Terhubung ke tabel master "docticket"
  - data-appkeynum: Primary business key "docticketnum"
-->

<body id="page-top" xboset="docticket" data-appkeynum="docticketnum">
	<div id="wrapper">
		<div id='sidebarmenu'></div>
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<div class="eat-toolbars"></div>
				<div class="container-fluid">
					<div class="maxes01">
						<ul class="maxes02">
							<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
							<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Doc Ticket</a></li>
						</ul>
						<div class="maxes04 shadow">
							
							<!-- TAB 1: DAFTAR DOCTICKETS (LIST) -->
							<div id="maxes02_1_1" class="tab maxes02_1">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>
								<div class="row eatfix">
									<div class="col-md-12">
										<div class="page-header-list">
											<div class="tablelist">
												<table id="mxtable_docticket" class="display"></table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- TAB 2: DETAIL DOCTICKET -->
							<div id="maxes02_1_2" class="maxes03">
								<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
									<div class='eat_apptitle'></div>
								</div>

								<!-- Bagian Identitas Utama Change Request -->
								<div class="row eatfix">
									<div class="col-md-4 page-header">
										<table class="tbldata">
											<tr>
												<td>Ticket Num.</td>
												<td>
													<input type="text" data-attribute="docticketnum" data-input="required" data-key="value" data-type="upper">
												</td>
											</tr>
											<tr>
												<td>Ticket Title</td>
												<td>
													<input type="text" data-attribute="doctickettitle" data-input="required" style="width: 100%;">
												</td>
											</tr>
											
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata">
											<tr>
												<td>Reported By</td>
												<td>
													<input type="text" data-attribute="reportedby" data-lookup="employees" data-input="required" placeholder="Nama / NIK Pelapor">
												</td>
											</tr>
											<tr>
												<td>Reported Date</td>
												<td>
													<input type="text" data-attribute="reporteddate" data-type="date">
												</td>
											</tr>
											<tr>
												<td>Priority</td>
												<td>
													<input type="text" data-attribute="priority" data-lookup="wopriority">
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-4">
										<table class="tbldata" id="parentticket">
											<tr>
												<td>Procedure</td>
												<td>
													<input type="text" data-attribute="proceduresnum" data-lookup="procedures" data-type="upper" data-lookupaction="y">
												</td>
											</tr>
											<tr>
												<td>Work Instr.</td>
												<td>
													<input type="text" data-attribute="workinstrnum" data-lookup="workinstr" data-type="upper" data-lookupaction="y">
												</td>
											</tr>
											<tr>
												<td>Doc. Form</td>
												<td>
													<input type="text" data-attribute="docformnum" data-lookup="docform" data-type="upper" data-lookupaction="y">
												</td>
											</tr>
										</table>
									</div>
								</div>

								<!-- Bagian Deskripsi Perubahan & Catatan (Remarks) -->
								<div class="row eatfix" style="border-top: 1px solid #e3e6f0; padding-top: 15px; margin-top: 15px;">
									<div class="col-md-6">
										<h6>Change Request Description & Justification</h6>
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="vertical-align: top; width: 120px;">Description</td>
												<td>
													<textarea data-attribute="description" rows="5" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 6px;" placeholder="Jelaskan alasan dan bagian prosedur yang perlu diubah..." ></textarea>
												</td>
											</tr>
										</table>
									</div>
									<div class="col-md-6">
										<h6>Reviewer Remarks & Notes</h6>
										<table class="tbldata" style="width: 100%;">
											<tr>
												<td style="vertical-align: top; width: 120px;">Remarks</td>
												<td>
													<textarea data-attribute="remarks" rows="5" style="width: 100%; border: 1px solid #d1d3e2; border-radius: 4px; padding: 6px;" placeholder="Catatan evaluasi atau tindak lanjut..."></textarea>
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

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>

</html>