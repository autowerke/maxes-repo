// Menunggu seluruh halaman siap sebelum menjalankan script

let token = null;
const API_URL = '../businessobject/router.php'; // Path ke router




const uploadDocument = {
    vendorcode: null,
    init: function(vendor){
        this.vendorcode = vendor
        console.log(" this.vendorcode",  this.vendorcode)
    },
    dialogContentHtml:`
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
            `,
    dialogFooterHtml: `
            <button id="upload-submit-btn" class="general-button">Upload File</button>
            <button id="dialog-close-btn" class="general-button">Cancel</button>
        `,
    show: function(){
         UI.setDialog(`Upload Document`, this.dialogContentHtml, this.dialogFooterHtml);
         var vendorcode = this.vendorcode
         console.log("vendorcode show", vendorcode)

          $('#upload-submit-btn').on('click', function() {
            console.log("click upload ")
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
            formData.append('actionname', 'uploadVendorDoc');
            formData.append('vendorcode', vendorcode);
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
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                }
            })
            .done(function(response) {
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
            .fail(function(jqXHR) {
                const errorMsg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'A server error occurred.';
                uploadStatus.text('Error: ' + errorMsg).css('color', 'red');
                uploadButton.text('Upload File').prop('disabled', false); // Aktifkan lagi tombolnya
            });
        });


    }
}

$(document).ready(function () {
  
	

	// Cek di halaman mana kita berada
	const currentPage = window.location.pathname.split("/").pop();

	/**
	 * Helper function untuk menampilkan pesan
	 */
	function showMessage(type, message) {
		const container = $('#message-container');
		container.removeClass('success error').addClass(type).text(message).show();
	}

	// --- LOGIKA UNTUK HALAMAN REGISTRASI ---
	$('#register-form').on('submit', function (event) {
        event.preventDefault(); // hentikan submit default

        // Ambil nilai password dan confirm password
        let password = $('#password').val();
        let confirmPassword = $('#confirmpassword').val();

        // Validasi apakah sama
        if (password !== confirmPassword) {
            alert('Password dan Confirm Password harus sama!');
            return; // hentikan proses submit
        }

        // Jika valid, serialize form data
        let formData = $(this).serializeArray();
        let dataToSend = {
            actionname: 'register_vendor'
        };

		formData.forEach(item => {
			dataToSend[item.name] = item.value;
		});
       
		$.ajax({
				url: API_URL,
				type: 'POST',
				data: dataToSend,
				dataType: 'json'
			})
			.done(function (response) {
				if (response.success) {
					showMessage('success', response.message + ' You will be redirected to Login Page');
					setTimeout(() => {
						window.location.href = 'login.php';
					}, 3000);
				} else {
					showMessage('error', response.message);
				}
			})
			.fail(function (jqXHR) {
				const errorMsg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Something wrong on the server.';
				showMessage('error', errorMsg);
			});
            
	});

	// --- LOGIKA UNTUK HALAMAN LOGIN ---
	$('#login-form').on('submit', function (event) {
		event.preventDefault();
		let formData = $(this).serializeArray();
		let dataToSend = {
			actionname: 'login'
		};
		formData.forEach(item => {
			dataToSend[item.name] = item.value;
		});

		console.log('Login form submitted with data:', dataToSend);

		$.ajax({
				url: API_URL,
				type: 'POST',
				data: dataToSend,
				dataType: 'json'
			})
			.done(function (response) {
				console.log('=== LOGIN RESPONSE ===');
				console.log('Full response:', response);
				console.log('response.success:', response.success);
				console.log('response.data:', response.data);
				console.log('response.data.token:', response.data ? response.data.token : 'TIDAK ADA');
				console.log('response.data.message:', response.data ? response.data.message : 'TIDAK ADA');

				// CEK STRUKTUR RESPONSE
				if (response.success) {
					console.log('✓ Login success = true');

					// DEBUG: Cek berbagai kemungkinan lokasi token
					

					// Kemungkinan 1: Token di response.data.token
					if (response.data && response.data.token) {
						token = response.data.token;
						console.log('✓ Token ditemukan di response.data.token');
					}
					// Kemungkinan 2: Token di response.token (struktur flat)
					else if (response.token) {
						token = response.token;
						console.log('✓ Token ditemukan di response.token');
					}
					// Kemungkinan 3: Token di response.data.data.token (nested)
					else if (response.data && response.data.data && response.data.data.token) {
						token = response.data.data.token;
						console.log('✓ Token ditemukan di response.data.data.token');
					}
					// Kemungkinan 4: Cek apakah response.data sendiri adalah token (string)
					else if (typeof response.data === 'string') {
						token = response.data;
						console.log('✓ Token ditemukan di response.data (string)');
					}

					if (token) {
						console.log('✓ Token berhasil ditemukan:', token.substring(0, 20) + '...');
						localStorage.setItem('vendor_token', token);
						console.log('✓ Token tersimpan di localStorage');
						console.log('✓ Verifikasi:', localStorage.getItem('vendor_token').substring(0, 20) + '...');
						showMessage('success', 'Login Success! Redirecting to the dashboard...');
						setTimeout(() => {
							window.location.href = 'dashboard.php';
						}, 1000);

					} else {
						console.error('✗ Token TIDAK ditemukan di mana pun dalam response');
						showMessage('error', 'Token not found. Contact Administrator');
					}
				} else {
					console.log('✗ Login success = false');
					showMessage('error', response.message || 'Login gagal');
				}
			})
			.fail(function (jqXHR) {
				console.error('=== AJAX FAIL ===');
				console.error('Status:', jqXHR.status);
				console.error('Response:', jqXHR.responseJSON);
				console.error('Response Text:', jqXHR.responseText);

				const errorMsg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Username or password is invalid.';
				showMessage('error', errorMsg);
			});
	});

	// --- LOGIKA UNTUK HALAMAN DASHBOARD ---
	if (currentPage === 'dashboard.php') {
		const token = localStorage.getItem('vendor_token');

		console.log('Dashboard loaded. Token:', token ? 'Ada' : 'TIDAK ADA');

		if (!token) {
			console.log('Tidak ada token, redirect ke login');
			window.location.href = 'login.php';
			return;
		}

		// Validasi token dengan memanggil check_token
		console.log('Sending check_token request with Authorization header');
		console.log('Token to send:', token.substring(0, 30) + '...');

		$.ajax({
				url: API_URL,
				type: 'POST',
				data: {
					actionname: 'check_token'
				},
				dataType: 'json',

				// PENTING: Kirim token di header Authorization dengan format "Bearer <token>"
				headers: {
					'Authorization': 'Bearer ' + token
				},

				// Debug: Log request yang dikirim
				beforeSend: function (xhr) {
					console.log('beforeSend: Setting Authorization header');
					console.log('Header value:', xhr.getResponseHeader('Authorization'));
				}
			})
			.done(function (response) {
				console.log('=== TOKEN VALIDATION SUCCESS ===');
				console.log('Response:', response);

				if (response.success) {
					const userData = response.data;
                    localStorage.setItem("vendor_token", token)
					vendorInfo(API_URL, userData)
				}


			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				console.error('=== TOKEN VALIDATION FAILED ===');
				console.error('Status:', jqXHR.status);
				console.error('Status Text:', jqXHR.statusText);
				console.error('Response JSON:', jqXHR.responseJSON);
				console.error('Text Status:', textStatus);
				console.error('Error Thrown:', errorThrown);

				// Lihat request headers yang dikirim
				console.error('Request headers:', jqXHR.getAllResponseHeaders());

				if (jqXHR.status === 401) {
					console.error('✗ Token tidak valid atau sudah expired');
					alert('Your session is over. Please login again.');
					localStorage.removeItem('vendor_token');
					window.location.href = 'login.php';
				} else {
					alert('Terjadi kesalahan: ' + (jqXHR.responseJSON ? jqXHR.responseJSON.message : errorThrown));
				}
			});

		// Logika untuk tombol logout
		$('#logout-button').on('click', function () {
			console.log('Logout clicked');
			localStorage.removeItem('vendor_token');
			window.location.href = 'login.php';
		});
	}
});

function vendorInfo(API_URL, userData){
    if (userData && userData.data) {
            const userInfoHtml = `
                <p><strong>Contact Name:</strong> ${userData.data.contact_name}</p>
                <p><strong>Username:</strong> ${userData.data.username} | <a id='user_changepassword' style='cursor:pointer; color:blue;'>  Change Password </a></p>
                <p><strong>Company:</strong> <a id='company_info' style='cursor:pointer; color:blue;'> ${userData.data.company_name} (${userData.data.vendor_code}) </a></p>
            `;
            $('#user-info').html(userInfoHtml);
            
            $("#user_changepassword").click(function(){
                var context = `
                    <table border="0" cellspacing="0" cellpadding="6" class="ven_inf_table">
                        <tr>
                            <th class="ven_inf_label">New Password</th>
                            <td>
                                <input type="password" class="ven_inf_input" id="newPassword" placeholder="8 Characters Minimum">
                            </td>
                        </tr>
                        <tr>
                            <th class="ven_inf_label">Confirm Password</th>
                            <td>
                                <input type="password" class="ven_inf_input" id="confirmPassword" placeholder="Confirm Password">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" id="ven_inf_error" style="color:red; text-align:center;"></td>
                        </tr>
                    </table>
                    `;
                UI.setDialog("Change Password", context, "<button id='change_password' class='view-details-btn'>Change Password</button> ")
                $("button[id='cancel']").click(function(){
                    console.log("cancel")
                    UI.destroyDialog();
                })
                $("#change_password").click(function(){
                    var newPassword = $('#newPassword').val().trim();
                    var confirmPassword = $('#confirmPassword').val().trim();
                    var errorMsg = '';

                    if(newPassword.length < 8) {
                        errorMsg = 'Password minimum 8 Characters.';
                    } else if(newPassword !== confirmPassword) {
                        errorMsg = 'Password and Confirmation Password is not equal.';
                    }

                    if(errorMsg) {
                        $('#ven_inf_error').text(errorMsg);
                        return;
                    }

                    $('#ven_inf_error').text(''); // clear error

                    var payload = {
                        actionname: "changePassword", 
                        newpassword: newPassword,
                        vendorcode: userData.data.vendor_code,
                        username: userData.data.username

                    };
                    token = localStorage.getItem("vendor_token");
                    $.ajax({
                        url: API_URL,
                        type: 'POST',
                        data: payload,
                        dataType: 'json',
                        headers: {
                            'Authorization': 'Bearer ' + token
                        },
                    })
                    .done(function (response) {
                        UI.setDialog("Info", response.message);
                    })
                })
            })
            $("#company_info").click(function () {
                var context = `<table class="ven_inf_table" border="1" cellspacing="0" cellpadding="6">
                    <tr class="ven_inf_row ven_inf_header_row">
                        <th class="ven_inf_header_cell">Label</th>
                        <th class="ven_inf_header_cell">Input</th>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Primary Bank Name</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="bankname1">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Account Number</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="bankaccount1">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Secondary Bank Name</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="bankname2">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Account Number</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="bankaccount2">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Third Bank Name</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="bankname3">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Account Number</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="bankaccount3">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Homepage / Website</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="website">
                        </td>
                    </tr>


                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Contact Person</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="contact">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Position / Title</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="position">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Email</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="email" readonly>
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Phone</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="phone">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Office Phone</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="officephone">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Address</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="address">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">City</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="city">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">State / Province</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="stateprovince">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Zip / Postal Code</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="zippostalcode">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Vendor Fax</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="vendorfax">
                        </td>
                    </tr>

                    <tr class="ven_inf_row">
                        <td class="ven_inf_label_cell">Country</td>
                        <td class="ven_inf_input_cell">
                            <input class="ven_inf_input" type="text" data-attribute="country">
                        </td>
                    </tr>
                </table>
                `
            userData.data.vendor_code

            UI.setDialog("Your Company Information", context, "<button id='uploadCompDoc' class='view-details-btn'>Upload Documents</button> <button id='savecompany' class='view-details-btn'>Save</button>")
            
            const dataToSend = {
                actionname: "getVendorProfile",
                vendorcode: userData.data.vendor_code
            }
           
            token = localStorage.getItem("vendor_token");
            $.ajax({
				url: API_URL,
				type: 'POST',
				data: dataToSend,
				dataType: 'json',
                headers: {
					'Authorization': 'Bearer ' + token
				},
			})
			.done(function (response) {
                var vendor  = response.data 
                bankaccount1 = vendor.bankaccount1
                bankaccount2 = vendor.bankaccount2
                bankaccount3 = vendor.bankaccount3
                bankname1 = vendor.bankname1
                bankname2 = vendor.bankname2
                bankname3 = vendor.bankname3
                city = vendor.city
                contact = vendor.contact
                country = vendor.country
                email = vendor.email
                officephone = vendor.officephone
                phone = vendor.phone
                position = vendor.position
                address = vendor.address
                stateprovince = vendor.stateprovince
                website = vendor.website
                zippostalcode = vendor.zippostalcode 

                var vendor = response.data;

                $('[data-attribute="bankaccount1"]').val(vendor.bankaccount1);
                $('[data-attribute="bankaccount2"]').val(vendor.bankaccount2);
                $('[data-attribute="bankaccount3"]').val(vendor.bankaccount3);

                $('[data-attribute="bankname1"]').val(vendor.bankname1);
                $('[data-attribute="bankname2"]').val(vendor.bankname2);
                $('[data-attribute="bankname3"]').val(vendor.bankname3);

                $('[data-attribute="city"]').val(vendor.city);
                $('[data-attribute="contact"]').val(vendor.contact);
                $('[data-attribute="country"]').val(vendor.country);

                $('[data-attribute="email"]').val(vendor.email);
                $('[data-attribute="officephone"]').val(vendor.officephone);
                $('[data-attribute="phone"]').val(vendor.phone);
                $('[data-attribute="position"]').val(vendor.position);

                $('[data-attribute="address"]').val(vendor.address);
                $('[data-attribute="stateprovince"]').val(vendor.stateprovince);
                $('[data-attribute="website"]').val(vendor.website);
                $('[data-attribute="zippostalcode"]').val(vendor.zippostalcode);

                $("#uploadCompDoc").click(function(){
                    console.log("userData.data.vendor_code", userData.data.vendor_code)
                    uploadDocument.init(userData.data.vendor_code)
                    uploadDocument.show();
                })


                $("#savecompany").click(function(){
                    var vendorPayload = {};
                    $('input[data-attribute]').each(function () {
                        var key = $(this).data('attribute');
                        var value = $(this).val().trim();
                        vendorPayload[key] = value;
                    });

                    const vendData = {
                        actionname:"updateVendorInformation",
                        vendorcode: userData.data.vendor_code,
                        vendorData: vendorPayload
                    }
                     $.ajax({
                        url: API_URL,
                        type: 'POST',
                        data: vendData,
                        dataType: 'json',
                        headers: {
                            'Authorization': 'Bearer ' + token
                        },
                    })
                    .done(function (response) {
                        UI.setDialog("Info", response.message)
                    })
                })
            })
            .fail(function (jqXHR) {
				const errorMsg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Something wrong on the server.';
				UI.setDialog('error', errorMsg);
			});
        
        })

    }
}