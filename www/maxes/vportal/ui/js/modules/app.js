function formatDecimal(value) {
    if (!value || isNaN(value)) return '';
    
    const num = parseFloat(value);
    
    // Split integer dan desimal
    const parts = num.toFixed(2).split('.');
    
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    return parts.join(',');
}

function unformatDecimal(value) {
    if (!value) return 0;
    
    const cleaned = value.toString()
        .replace(/\./g, '')
        .replace(/,/g, '.');  
    
    return parseFloat(cleaned) || 0;
}


const UI = {

	setDialog: function (title, contentHtml, footerHtml = null, dialogId = 'dynamic-dialog') {
		// Hancurkan dialog lama jika ada, untuk mencegah duplikasi
		this.destroyDialog(dialogId);

		// Buat elemen-elemen dialog
		const dialogBackdrop = $('<div>', {
			'id': dialogId,
			'class': 'dialog-backdrop'
		});

		const dialogBox = $('<div>', {
			'class': 'dialog-box'
		});

		// Header
		const dialogHeader = $('<div>', {
			'class': 'dialog-header'
		});
		dialogHeader.append($('<h2>').text(title));
		dialogHeader.append($('<button>', {
			'id': 'dialog-close-btn'
		}).html('×'));

		// Content
		const dialogContent = $('<div>', {
			'class': 'dialog-content'
		}).html(contentHtml);

		// Gabungkan semua bagian
		dialogBox.append(dialogHeader, dialogContent);

		// Footer (jika ada)
		if (footerHtml) {
			const dialogFooter = $('<div>', {
				'class': 'dialog-footer'
			}).html(footerHtml);
			dialogBox.append(dialogFooter);
		}

		dialogBackdrop.append(dialogBox);

		// Tambahkan dialog ke body
		$('body').append(dialogBackdrop);

		// Trigger reflow untuk memastikan transisi CSS berjalan
		dialogBackdrop[0].offsetHeight;

		// Tampilkan dialog dengan menambahkan class 'show'
		setTimeout(() => {
			dialogBackdrop.addClass('show').css('opacity', '1');
		}, 10);

		// Event listener untuk tombol close dan klik di luar area
		dialogBackdrop.on('click', '#dialog-close-btn', () => this.destroyDialog(dialogId));
		dialogBackdrop.on('click', function (event) {
			if (event.target === this) {
				UI.destroyDialog(dialogId);
			}
		});
	},

	destroyDialog: function (dialogId = 'dynamic-dialog') {
		const dialog = $('#' + dialogId);
		dialog.removeClass('show').css('opacity', '0');
		setTimeout(() => {
			dialog.remove();
		}, 200);
	}
};


$(document).ready(function () {


	const API_URL = '../businessobject/router.php';
	const token = localStorage.getItem('vendor_token');

	// Jika tidak ada token, jangan lanjutkan, biarkan auth.js yang handle redirect
	if (!token) {
		return;
	}


	// Inisialisasi DataTable untuk menampilkan daftar RFQ
	const rfqTable = $('#rfq-table').DataTable({
		"ajax": {
			"url": API_URL,
			"type": "POST",
			"data": {
				actionname: 'get_rfq_list'
			},
			"beforeSend": function (xhr) {
				// Kirim token untuk otentikasi
				xhr.setRequestHeader('Authorization', 'Bearer ' + token);
			},
			"dataSrc": "data.data"
		},
		"columns": [{
				"data": "rfqnum",
				"title": "RFQ NUMBER"
			},
			{
				"data": "description",
				"title": "DESCRIPTION"
			},
			{
				"data": "status",
				"title": "STATUS"
			},
			{
				"data": "replydate",
				"title": "DUE DATE"
			},
			{
				"data": null,
				"title": "ACTIONS",
				"orderable": false,
				"render": function (data, type, row) {
					let viewDetailsBtn = `<button class="btn btn-primary btn-sm view-details-btn">View Details</button>`;

					// TOMBOL "VIEW ATTACHMENT" BARU DI PANEL KIRI
					let viewAttachmentsBtn = `<button class="btn btn-info btn-sm view-attachments-btn">Attachments</button>`;

					return viewDetailsBtn + viewAttachmentsBtn;
				}
			}
		]
	});
	$('#rfq-table tbody').on('click', '.view-attachments-btn', function () {
		const rowData = rfqTable.row($(this).parents('tr')).data();
		const rfqNum = rowData.rfqnum;
		$.ajax({
				url: API_URL,
				type: 'POST',
				data: {
					actionname: 'get_rfq_attachments',
					rfqnum: rfqNum
				},
				dataType: 'json',
				beforeSend: function (xhr) {
					xhr.setRequestHeader('Authorization', 'Bearer ' + token);
				}
			})
			.done(function (response) {
				if (response.success && response.data.length > 0) {
					// 1. Buat konten HTML untuk tabel attachment
					let tableHtml = '<table class="attachment-table">';
					tableHtml += '<thead><tr><th>Filename</th><th>Description</th><th>Upload Date</th><th>Size</th><th>Type</th><th>Download</th></tr></thead>';
					tableHtml += '<tbody>';

					response.data.forEach(file => {
						// Kita bisa buat link download di sini
						const downloadUrl = `/maxes/attachments/${file.filename}`; 
						tableHtml += `
                        <tr>
                            <td>${file.filename}</td>
                            <td>${file.description}</td>
                            <td>${file.uploaddate}</td>
                            <td>${(file.filesize / 1024).toFixed(2)} KB</td>
                            <td>${file.type.toUpperCase()}</td>
                            <td><a href="${downloadUrl}" target="_blank" class="btn btn-success btn-sm">Download</a></td>
                        </tr>
                    `;
					});

					tableHtml += '</tbody></table>';
					// 2. Panggil helper untuk membuat dialog
					UI.setDialog(`Attachments for RFQ: ${rfqNum}`, tableHtml);

				} else {
					// Tampilkan dialog dengan pesan 'tidak ada attachment'
					UI.setDialog(`Attachments for RFQ: ${rfqNum}`, '<p>No attachments found for this RFQ.</p>');
				}

			})
			.fail(function () {
				alert('Failed to display attachment(s) for ' + rfqNum);
			});

	});


	// Event listener untuk tombol "View Details"
	$('#rfq-table tbody').on('click', '.view-details-btn', function () {
		// Dapatkan data dari baris yang diklik
		const rowData = rfqTable.row($(this).parents('tr')).data();
		const rfqNum = rowData.rfqnum;
		refreshRfqDetail(rfqNum);
	});


	function refreshRfqDetail(rfqNum){
		

		// Tampilkan loading message
		$('#rfq-detail-panel').html('<p>Loading details for ' + rfqNum + '...</p>').show();

		// Panggil API untuk mendapatkan detail RFQ
		$.ajax({
				url: API_URL,
				type: 'POST',
				data: {
					actionname: 'get_rfq_detail',
					rfqnum: rfqNum
				},
				dataType: 'json',
				beforeSend: function (xhr) {
					xhr.setRequestHeader('Authorization', 'Bearer ' + token);
				}
			})
			.done(function (response) {
				if (response.success) {
					renderRfqDetails(rfqNum, response.data, response);
				} else {
					$('#rfq-detail-panel').html('<p class="error">' + response.message + '</p>');
				}
			})
			.fail(function () {
				$('#rfq-detail-panel').html('<p class="error">Failed to load RFQ details.</p>');
			});
	}
	/**
	 * Fungsi untuk merender detail item RFQ ke panel kanan
	 */
	function renderRfqDetails(rfqNum, data, metadata) {
		let html = '<div class="detail-content">';
		var items = data.rfqlines
		
		let rfq_data = metadata.data.rfq_data[0];
		let status = (rfq_data && rfq_data.status) ? rfq_data.status : '';

		function setUnitCostReadonly(status) {
            let readonly = (status === 'closed');
            $('.field-input#price-input').prop('readonly', readonly);
        }

		html += '<div class="detail-header">';
		html += `<h3>Quotation for RFQ ${rfqNum}</h3>`;

		if (data.ntp_data && data.ntp_data.length > 0) {
			var ntp_data = data.ntp_data[0]
			html += `<h4>Notice to Proceed (NTP Number): <a id='ntpnum' style='cursor:pointer; color:blue;'> ${ntp_data.ntpnum} </a></h3>`;
		} else {
			console.log("NTP NULL");
		}


		html += '<div class="rfq-meta">';

		if (metadata) {
			html += `<strong>Description:</strong> ${metadata.description || 'N/A'} | `;
			html += `<strong>Due Date:</strong> ${metadata.replydate || 'N/A'}`;
		}

		html += '</div>';
		html += '</div>';

		// Table
		html += '<table class="detail-table">';
		html += '<thead><tr>';
		html += '<th>Line</th><th>Line Type</th><th>Item</th><th>Description</th>';
		html += '<th>Qty</th><th>Unit</th><th>Unit Cost</th><th>Line Cost</th>';
		html += '<th>Tax Cost</th><th>Remarks</th>';
		html += '</tr></thead>';
		html += '<tbody>';

		if (items && items.length > 0) {
			items.forEach(function (item) {

				// --- VALIDASI NILAI KOSONG / NULL / UNDEFINED ---
				var unitcost = item.unitcost ? item.unitcost : "0.00";
				var linecost = item.linecost ? item.linecost : "0.00";
				var taxcost  = item.taxcost  ? item.taxcost  : "0.00";
				var remarks  = item.remarks2 ? item.remarks2 : "";

				unitcost = formatDecimal(unitcost);
				linecost = formatDecimal(linecost);
				taxcost = formatDecimal(taxcost);
				

				html += '<tr>';
				html += `<td>${item.rfqlinenum || ''}</td>`;
				html += `<td>${item.linetype || ''}</td>`;
				html += `<td>${item.itemnum || ''}</td>`;
				html += `<td>${item.description || ''}</td>`;
				html += `<td>${item.quantity || ''}</td>`;
				html += `<td>${item.orderunit || ''}</td>`;
				html += `<td><input type="text" class="field-input" id="price-input" data-line="${item.rfqlinenum}" placeholder="0.00" value="${unitcost}" /></td>`;
				html += `<td><input type="text" class="field-input readonly" id="linecost-input" data-line="${item.rfqlinenum}" placeholder="0.00" value="${linecost}" readonly/></td>`;
				html += `<td><input type="text" class="field-input readonly" id="taxcost-input" data-line="${item.rfqlinenum}" placeholder="0.00" value="${taxcost}" readonly/></td>`;
				html += `<td><input type="text" class="field-input" id="remarks-input" data-line="${item.rfqlinenum}" placeholder="Add remarks" value="${remarks}" /></td>`;
				html += '</tr>';
			});
		} else {
			html += '<tr><td colspan="9" style="text-align:center;">No items found for this RFQ.</td></tr>';
		}


		html += '</tbody></table>';

		// Button
		html += '<button id="save-quotation-btn" class="general-button">Save Quotation</button>';
		html += '<button id="add-attachment-btn"  class="general-button">Add Attachment</button>';

		html += '</div>';

		$('#rfq-detail-panel').html(html);
		setUnitCostReadonly(status); 

		//event click untuk ntpnum 
		$("#ntpnum").click(function () {
			
			const token = localStorage.getItem('vendor_token');

			$.ajax({
					url: API_URL,
					type: 'POST',
					data: {
						actionname: 'get_ntp',
						rfqnum: rfqNum,
						ntpnum: ntp_data.ntpnum
					},
					dataType: 'json',
					beforeSend: function (xhr) {
						xhr.setRequestHeader('Authorization', 'Bearer ' + token);
					}
				})
				.done(function (response) {
					var ntpData = response.data[0]


					// Buka window baru
					var newWin = window.open("", "_blank", "width=1000,height=800,scrollbars=yes,resizable=yes");

					// HTML + CSS + toolbar
					var html = `
                        <html>
                        <head>
                            <title>NTP Details</title>
                            <style>
                                body {
                                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                                    background-color: #f8f9fa;
                                    color: #333;
                                    padding: 20px;
                                }
                                h1 {
                                    color: #0052cc;
                                    border-bottom: 2px solid #0052cc;
                                    padding-bottom: 10px;
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin-top: 20px;
                                }
                                th, td {
                                    text-align: left;
                                    padding: 10px;
                                    border-bottom: 1px solid #ccc;
                                }
                                th {
                                    background-color: #e6f0ff;
                                    color: #0052cc;
                                    width: 200px;
                                }
                                td {
                                    background-color: #fff;
                                }
                                /* Toolbar */
                                .toolbar {
                                    margin-bottom: 20px;
                                }
                                .toolbar button {
                                    background-color: #0052cc;
                                    color: #fff;
                                    border: none;
                                    padding: 8px 15px;
                                    margin-right: 10px;
                                    font-size: 14px;
                                    border-radius: 4px;
                                    cursor: pointer;
                                }
                                .toolbar button:hover {
                                    background-color: #003d99;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="toolbar">
                                <button onclick="window.print()">Print</button>
                                <button onclick="savePage()">Save Page</button>
                            </div>

                            <h1>NTP Details</h1>
                            <table>
                                <tr><th>RFQ Number</th><td>${ntpData.rfqnum}</td></tr>
                                <tr><th>NTP Number</th><td>${ntpData.ntpnum}</td></tr>
                                <tr><th>Issue Date</th><td>${ntpData.issuedate}</td></tr>
                                <tr><th>Vendor</th><td>${ntpData.vendor}</td></tr>
                                <tr><th>Work Title</th><td>${ntpData.worktitle}</td></tr>
                                <tr><th>Contract Number</th><td>${ntpData.contractnumber}</td></tr>
                                <tr><th>Contract Value</th><td>${parseFloat(ntpData.contractvalue).toLocaleString()}</td></tr>
                                <tr><th>Start Date</th><td>${ntpData.startdate}</td></tr>
                                <tr><th>End Date</th><td>${ntpData.enddate}</td></tr>
                                <tr><th>Project Location</th><td>${ntpData.projectlocation}</td></tr>
                                <tr><th>Owner PIC</th><td>${ntpData.ownerpic}</td></tr>
                                <tr><th>Owner Contact</th><td>${ntpData.ownercontact}</td></tr>
                                <tr><th>Notes</th><td>${ntpData.notes}</td></tr>
                                <tr><th>Created At</th><td>${ntpData.created_at}</td></tr>
                                <tr><th>Updated At</th><td>${ntpData.updated_at}</td></tr>
                            </table>

                            <script>
                                function savePage() {
                                    // Buat blob HTML
                                    var blob = new Blob([document.documentElement.outerHTML], { type: 'text/html' });
                                    var a = document.createElement('a');
                                    a.href = URL.createObjectURL(blob);
                                    a.download = 'ntp_details.html';
                                    a.click();
                                    URL.revokeObjectURL(a.href);
                                }
                            </script>
                        </body>
                        </html>
                        `;

					newWin.document.write(html);
					newWin.document.close();
				})
				.fail(function () {
					alert('Failed to save quotation. Please try again.');
				});


		})


		$('#add-attachment-btn').on('click', function () {
			//const rfqNum = $('#rfq-detail-title').text().replace('Quotation for RFQ ', '');
			//alert('Fungsi untuk upload attachment untuk RFQ ' + rfqNum + ' akan dibuat di sini.');
			// TODO: Buat modal/popup dengan form upload file.
			// Saat upload, data akan disimpan ke `fileinfo` dengan
			// ownertable = 'rfq', ownerid = [rfqid]
			// childtable = 'rfqquotation', childid = [rfqquotationid]

			const rfqNum = $('#rfq-detail-title').text().replace('Quotation for RFQ ', '');

			// Buat konten HTML untuk form upload di dalam dialog
			const dialogContentHtml = `
            <form id="attachment-upload-form" enctype="multipart/form--data">
                <div class="form-group">
                    <label for="attachment-file-input">Select File:</label>
                    <input type="file" id="attachment-file-input" name="attachment_file" required>
                    <small>Allowed types: PDF, ZIP, RAR, JPG, PNG, DOC, XLS. Max size: 10MB.</small>
                </div>
                <div class="form-group">
                    <label for="attachment-description-input">Description:</label>
                    <input type="text" id="attachment-description-input" name="description" placeholder="e.g., Company Profile" required>
                </div>
                <div id="upload-status" style="margin-top: 10px;"></div>
            </form>
        `;

			// Buat konten HTML untuk footer dialog yang berisi tombol Upload
			const dialogFooterHtml = `
            <button id="upload-submit-btn" class="general-button">Upload File</button>
            <button id="dialog-close-btn" class="general-button">Cancel</button>
        `;

			UI.setDialog(`Upload Attachment for RFQ: ${rfqNum}`, dialogContentHtml, dialogFooterHtml);


			$('#upload-submit-btn').on('click', function () {
				const rfqNum = $('.detail-header h3').text().replace('Quotation for RFQ ', '');
				const uploadForm = $('#attachment-upload-form')[0];
				const fileInput = $('#attachment-file-input')[0];
				const descriptionInput = $('#attachment-description-input').val();
				const uploadStatus = $('#upload-status');
				const uploadButton = $(this);

				// Validasi sederhana di sisi klien
				if (fileInput.files.length === 0) {
					uploadStatus.text('Please select a file to upload.').css('color', 'red');
					return;
				}
				if (!descriptionInput.trim()) {
					uploadStatus.text('Please provide a description.').css('color', 'red');
					return;
				}

				// Gunakan FormData untuk mengirim file dan data teks bersamaan
				const formData = new FormData();
				formData.append('actionname', 'upload_attachment');
				formData.append('rfqnum', rfqNum);
				formData.append('description', descriptionInput);

				// PENTING: Gunakan 'attachment_file' sesuai dengan yang dibaca di backend ($_FILES['attachment_file'])
				formData.append('attachment_file', fileInput.files[0]);

				// Beri feedback ke user
				uploadButton.text('Uploading...').prop('disabled', true);
				uploadStatus.text('Uploading, please wait...').css('color', 'blue');

				$.ajax({
						url: API_URL,
						type: 'POST',
						data: formData,
						// PENTING: Opsi ini wajib untuk upload file dengan FormData
						processData: false,
						contentType: false,
						dataType: 'json',
						beforeSend: function (xhr) {
							xhr.setRequestHeader('Authorization', 'Bearer ' + token);
						}
					})
					.done(function (response) {
						if (response.success) {
							uploadStatus.text(response.message).css('color', 'green');
							// Tutup dialog setelah 2 detik
							setTimeout(() => {
								UI.destroyDialog();
							}, 2000);
						} else {
							uploadStatus.text('Error: ' + response.message).css('color', 'red');
							uploadButton.text('Upload File').prop('disabled', false); // Aktifkan lagi tombolnya
						}
					})
					.fail(function (jqXHR) {
						const errorMsg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'A server error occurred.';
						uploadStatus.text('Error: ' + errorMsg).css('color', 'red');
						uploadButton.text('Upload File').prop('disabled', false); // Aktifkan lagi tombolnya
					});
			});
		});

		$(document).off('click', '#save-quotation-btn').on('click', '#save-quotation-btn', function () {
			const rfqNum = $('.detail-header h3').text().replace('Quotation for RFQ ', '');
			const quotationData = [];

			$('.detail-table tbody tr').each(function () {
				const line = $(this).find('#price-input').data('line');
				var unitcost = unformatDecimal($(this).find('#price-input').val());
				const remarks = $(this).find('#remarks-input').val();

				// VALIDASI: Unit cost tidak boleh kosong (""), tapi boleh bernilai 0
				if (line && unitcost !== "" && !isNaN(unitcost)) {
					var parsedUnitCost = parseFloat(unitcost);
					
					// Validasi: Unit cost tidak boleh 0 atau negatif
					if (parsedUnitCost <= 0) {
						alert(`Unit Cost of 0 or below is not allowed on RFQ Line Num: ${line}`);
						return false; // Stop eksekusi
					}
					
					quotationData.push({
						rfqlinenum: line,
						unitcost: parsedUnitCost,
						remarks: remarks || ''
					});
				}
			});

			if (quotationData.length === 0) {
				alert('Please enter at least one Unit Cost before saving.');
				return;
			}

			// Kirim data ke API
			const token = localStorage.getItem('vendor_token');

			$.ajax({
					url: API_URL,
					type: 'POST',
					data: {
						actionname: 'save_quotation',
						rfqnum: rfqNum,
						quotation_lines: quotationData
					},
					dataType: 'json',
					beforeSend: function (xhr) {
						xhr.setRequestHeader('Authorization', 'Bearer ' + token);
					}
				})
				.done(function (response) {
					if (response.success) {
						alert('Quotation saved successfully!');
						// Reload RFQ table
						//$('#rfq-table').DataTable().ajax.reload();
						refreshRfqDetail(rfqNum)
					} else {
						alert('Error: ' + (response.message || 'Failed to save quotation'));
					}
				})
				.fail(function () {
					alert('Failed to save quotation. Please try again.');
				});
		});
	}


});