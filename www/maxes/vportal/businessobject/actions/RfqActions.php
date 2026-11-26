<?php

class RfqActions {

    public static function get_ntp(Request $request){
        $userPayload = $request->getUser();
        $vendorCode = $userPayload->data->vendor_code;
        $enterBy = $userPayload->data->username;

        $rfqnum = $request->get('rfqnum');
        $ntpnum = $request->get('ntpnum');

        $rfqntp = (new Set())->MboSetRemote("rfqntp")->where("rfqnum=? AND ntpnum=?", $rfqnum, $ntpnum)->getMboSet()->fetchData();
        Response::success($rfqntp, $ntpnum);    
    }
    public static function uploadVendorDoc(Request $request){
        $userPayload = $request->getUser();
        $vendorCode = $userPayload->data->vendor_code;
        $enterBy = $userPayload->data->username;
        $description = $request->get('description', 'Vendor Document'); // Ambil deskripsi, beri default

        if (empty($vendorCode)) {
            Response::error('Parameter vendorCode is required.', 400);
        }

        // === Langkah 1: Validasi File Upload ===
        if (!isset($_FILES['attachment_file']) || !is_uploaded_file($_FILES['attachment_file']['tmp_name'])) {
            Response::error('No file was uploaded. Please use the "attachment_file" field.', 400);
        }
        $file = $_FILES['attachment_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('Upload error. Code: ' . $file['error'], 400);
        }

        // === Langkah 2: Keamanan & Validasi File ===
        $rawName = basename($file['name']);
        $fileExtension = strtolower(pathinfo($rawName, PATHINFO_EXTENSION));

        // 2a. Whitelist Ekstensi
        $allowedExtensions = ['pdf', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            Response::error('Invalid file type. Allowed types are: ' . implode(', ', $allowedExtensions), 415);
        }

        // 2b. Validasi Ukuran (Max 10MB)
        if ($file['size'] > 10000000) { 
            Response::error("File is too large (max 10MB).", 413); // 413 Payload Too Large
        }
        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();
       try {
            // Langkah 3: Persiapan & Validasi Data Database
           
            // 3a. Dapatkan ID yang relevan dari DB
            $vendorData = (new Set())->useTransactionManager()->MboSetRemote("vendor")
                        ->where("vendorcode = ?", $vendorCode)
                        ->MboRemote("vendorid")->getMboSet()->fetch();
            if (!$vendorData) {
                // Kita tidak perlu rollback di sini karena belum ada perubahan data
                Response::error("Invalid Vendor Code : {$vendorCode}", 404);
            }
            $ownerid = $vendorData['vendorid'];
            // 3b. Buat nama file yang unik (tidak berubah)
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $uniqueFileName = 'vendor_' . time() . '.' . $fileExtension;
            
            $targetDir = Properties::getAttachFolder();
            $targetFile = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $uniqueFileName;

            // Langkah 4: Pindahkan File. Ini masih di luar DB, jadi jika gagal, kita bisa keluar.
            if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
                // Tidak perlu rollback. Cukup lemparkan Exception untuk ditangani oleh catch block.
                throw new Exception("Could not move uploaded file. Check server permissions.");
            }

            // Langkah 5: Simpan ke Database
            (new Set())->useTransactionManager()->MboSetRemote("fileinfo")
                ->setValue("filename", $uniqueFileName)
                ->setValue("description", $description)
                ->setValue("uploaddate", date("Y-m-d H:i:s"))
                ->setValue("ownertable", "vendor")
                ->setValue("ownerid", $ownerid)
                ->setValue("type", $fileExtension)
                ->setValue("filesize", $file['size'])
                ->setValue("sentby", $enterBy)
                ->add();
            
            // Jika sampai sini tanpa error, berarti semua operasi DB berhasil.
            $tm->commit();
            Response::success(['filename' => $uniqueFileName], "File has been uploaded successfully.");    

        } catch (Exception $e) {
            // CATCH SEMUA JENIS ERROR (DB, pemindahan file, dll.)
            
            // Hanya rollback jika transaksi masih aktif
            if ($tm->isTransactionActive()) {
                $tm->rollback();
            }
            
            // Jika file sudah sempat ter-upload tapi DB gagal, hapus file "yatim" tersebut
            if (isset($targetFile) && file_exists($targetFile)) {
                unlink($targetFile);
            }

            error_log("Upload Attachment Error: " . $e->getMessage()); 
            Response::error("A server error occurred: " . $e->getMessage(), 500);
        }
    }
    public static function changePassword(Request $request){
        $vendorcode = $request->get('vendorcode');
        $username = $request->get('username');
        $vendorcode = $request->get('vendorcode');
        $newpassword = $request->get('newpassword');

        $hashedPassword = password_hash($newpassword, PASSWORD_DEFAULT);
		//echo "newpasswod : $newpassword hashedPassword: $hashedPassword ";
		
		(new Set())->MboSetRemote("vendorcontact")->where("vendorcode=? AND username=?", $vendorcode, $username)
		->setValue("password", $hashedPassword)->change();

        Response::success([], "Password has been changed!");
    }
    public static function updateVendorInformation(Request $request){
        $vendorcode = $request->get('vendorcode');
        $data = $request->get('vendorData');
         // Ambil semua data vendorData
        $vendorData = $_POST['vendorData'] ?? [];
        // Bank Information
        $bankaccount1   = $vendorData['bankaccount1']   ?? "";
        $bankaccount2   = $vendorData['bankaccount2']   ?? "";
        $bankaccount3   = $vendorData['bankaccount3']   ?? "";
        $bankname1      = $vendorData['bankname1']      ?? "";
        $bankname2      = $vendorData['bankname2']      ?? "";
        $bankname3      = $vendorData['bankname3']      ?? "";
        // Contact & Company
        $city           = $vendorData['city']           ?? "";
        $contact        = $vendorData['contact']        ?? "";
        $country        = $vendorData['country']        ?? "";
        $email          = $vendorData['email']          ?? "";
        $officephone    = $vendorData['officephone']    ?? "";
        $phone          = $vendorData['phone']          ?? "";
        $position       = $vendorData['position']       ?? "";
        $address        = $vendorData['address']        ?? "";
        $stateprovince  = $vendorData['stateprovince']  ?? "";
        $website        = $vendorData['website']        ?? "";
        $zippostalcode  = $vendorData['zippostalcode']  ?? "";
        $vendorfax      = $vendorData['vendorfax']      ?? "";

       (new Set())
        ->useTransactionManager()
        ->MboSetRemote("vendor")
        ->where("vendorcode=?", $vendorcode)
        ->setValue("bankaccount1", $bankaccount1)
        ->setValue("bankaccount2", $bankaccount2)
        ->setValue("bankaccount3", $bankaccount3)
        ->setValue("bankname1", $bankname1)
        ->setValue("bankname2", $bankname2)
        ->setValue("bankname3", $bankname3)
        ->setValue("city", $city)
        ->setValue("contact", $contact)
        ->setValue("country", $country)
        ->setValue("stateprovince", $stateprovince)
        ->setValue("website", $website)
        ->setValue("zippostalcode", $zippostalcode)
        ->setValue("vendorfax", $vendorfax)
        ->setValue("address", $address)
        ->change();

        (new Set())
        ->useTransactionManager()
        ->MboSetRemote("vendorcontact")->where("vendorcode=? AND email=?", $vendorcode, $email)
        ->setValue("email", $email)
        ->setValue("officephone", $officephone)
        ->setValue("phone", $phone)
        ->setValue("position", $position)
        ->setValue("contact", $contact)
        ->change();

        Response::success([], "Your information has been updated!");
    }
    public static function getVendorProfile(Request $request){
        $vendorcode = $request->get('vendorcode');

        $vendorContactSet = (new Set())->useTransactionManager()->MboSetRemote("vendorcontact")->where("vendorcode=? AND username is not null", $vendorcode)->getMboSet()->getMbo();

        $contact = $vendorContactSet->getString("contact");
        $email = $vendorContactSet->getString("email");
        $position = $vendorContactSet->getString("position");
        $phone = $vendorContactSet->getString("phone");
        $officephone= $vendorContactSet->getString("officephone");

        $vendor = (new Set())->useTransactionManager()->MboSetRemote("vendor")->where("vendorcode=?", $vendorcode)->getMboSet()->getMbo();
        $bankname1 = $vendor->getString("bankname1");
        $bankaccount1 = $vendor->getString("bankaccount1");
        $bankname2 = $vendor->getString("bankname2");
        $bankaccount2 = $vendor->getString("bankaccount2");
        $bankname3 = $vendor->getString("bankname3");
        $bankaccount3 = $vendor->getString("bankaccount3");
        $website = $vendor->getString("website");
        $address = $vendor->getString("address");
        $city = $vendor->getString("city");
        $stateprovince = $vendor->getString("stateprovince");
        $zippostalcode = $vendor->getString("zippostalcode");
        $country = $vendor->getString("country");

        Response::success([
            'bankname1' => $bankname1,
            'bankaccount1' => $bankaccount1,
            'bankname2' => $bankname2,
            'bankaccount2' => $bankaccount2,
            'bankname3' => $bankname3,
            'bankaccount3' => $bankaccount3,
            'website' => $website,
            'address' => $address,
            'city' => $city,
            'stateprovince' => $stateprovince,
            'zippostalcode' => $zippostalcode,
            'contact' => $contact,
            'country' => $country,
            'email' => $email,
            'position' => $position,
            'phone' => $phone,
            'officephone' => $officephone
         ], "File has been uploaded successfully."); 


    }
    /**
     * Menangani upload attachment dari vendor untuk sebuah RFQ.
     * Method ini harus berada di dalam class RfqActions.
     */
    public function upload_attachment(Request $request)
    {
        // === Langkah 0: Dapatkan data dari Request & Token ===
        $userPayload = $request->getUser();
        $vendorCode = $userPayload->data->vendor_code;
        $enterBy = $userPayload->data->username;

        $rfqnum = $request->get('rfqnum');
        $description = $request->get('description', 'Vendor Attachment'); // Ambil deskripsi, beri default

        if (empty($rfqnum)) {
            Response::error('Parameter rfqnum is required.', 400);
        }

        // === Langkah 1: Validasi File Upload ===
        if (!isset($_FILES['attachment_file']) || !is_uploaded_file($_FILES['attachment_file']['tmp_name'])) {
            Response::error('No file was uploaded. Please use the "attachment_file" field.', 400);
        }
        $file = $_FILES['attachment_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('Upload error. Code: ' . $file['error'], 400);
        }

        // === Langkah 2: Keamanan & Validasi File ===
        $rawName = basename($file['name']);
        $fileExtension = strtolower(pathinfo($rawName, PATHINFO_EXTENSION));

        // 2a. Whitelist Ekstensi
        $allowedExtensions = ['pdf', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            Response::error('Invalid file type. Allowed types are: ' . implode(', ', $allowedExtensions), 415);
        }

        // 2b. Validasi Ukuran (Max 10MB)
        if ($file['size'] > 10000000) { 
            Response::error("File is too large (max 10MB).", 413); // 413 Payload Too Large
        }
        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();
       try {
            // Langkah 3: Persiapan & Validasi Data Database
           
            // 3a. Dapatkan ID yang relevan dari DB
            $rfqData = (new Set())->useTransactionManager()->MboSetRemote("rfq")
                        ->where("rfqnum = ?", $rfqnum)
                        ->MboRemote("rfqid")->getMboSet()->fetch();
            if (!$rfqData) {
                // Kita tidak perlu rollback di sini karena belum ada perubahan data
                Response::error("Invalid RFQ Number: {$rfqnum}", 404);
            }
            $ownerid = $rfqData['rfqid'];

            // Gunakan objek Set yang sama untuk kueri berikutnya
            $rfqvendorid = (new Set())->useTransactionManager()->MboSetRemote("rfqvendor")
                            ->where("rfqnum = ? AND vendor = ?", $rfqnum, $vendorCode)
                            ->getMboValue("rfqvendorid"); // Lebih efisien
            if (!$rfqvendorid) {
                Response::error("You are not an invited vendor for this RFQ.", 403);
            }
            
            // 3b. Buat nama file yang unik (tidak berubah)
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $uniqueFileName = 'rfq_' . $ownerid . '_vendor_' . $rfqvendorid . '_' . time() . '.' . $fileExtension;
            
            $targetDir = Properties::getAttachFolder();
            $targetFile = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $uniqueFileName;

            // Langkah 4: Pindahkan File. Ini masih di luar DB, jadi jika gagal, kita bisa keluar.
            if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
                // Tidak perlu rollback. Cukup lemparkan Exception untuk ditangani oleh catch block.
                throw new Exception("Could not move uploaded file. Check server permissions.");
            }

            // Langkah 5: Simpan ke Database (SEKARANG BARU KITA MELAKUKAN PERUBAHAN)
            // Gunakan lagi objek Set yang sama.
            (new Set())->useTransactionManager()->MboSetRemote("fileinfo")
                ->setValue("filename", $uniqueFileName)
                ->setValue("description", $description)
                ->setValue("uploaddate", date("Y-m-d H:i:s"))
                ->setValue("ownertable", "rfq")
                ->setValue("ownerid", $ownerid)
                ->setValue("childtable", "rfqvendor")
                ->setValue("childid", $rfqvendorid) 
                ->setValue("type", $fileExtension)
                ->setValue("filesize", $file['size'])
                ->setValue("sentby", $enterBy)
                ->add();
            
            // Jika sampai sini tanpa error, berarti semua operasi DB berhasil.
            $tm->commit();
            Response::success(['filename' => $uniqueFileName], "File has been uploaded successfully.");    

        } catch (Exception $e) {
            // CATCH SEMUA JENIS ERROR (DB, pemindahan file, dll.)
            
            // Hanya rollback jika transaksi masih aktif
            if ($tm->isTransactionActive()) {
                $tm->rollback();
            }
            
            // Jika file sudah sempat ter-upload tapi DB gagal, hapus file "yatim" tersebut
            if (isset($targetFile) && file_exists($targetFile)) {
                unlink($targetFile);
            }

            error_log("Upload Attachment Error: " . $e->getMessage()); 
            Response::error("A server error occurred: " . $e->getMessage(), 500);
        }
    }

    public static function getRFQAttachments(Request $request){
        $rfqnum = $request->get('rfqnum');
       

        $attachment_folder = Properties::getAttachFolder();
       
        //output: "View Attachment for : RFQ-001-2023 on Location: /var/www/html/maxtest/attachments/

        //1. Dapatkan nomor ID dari rfqnum untuk dicari di tabel fileinfo
        $ownerid = (new Set())->useTransactionManager()->MboSetRemote("rfq")->where("rfqnum=?", $rfqnum)->getMboValue("rfqid");
        //cari data di fileinfo
        $data = [];
        $attachments  = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")->where("ownertable='rfq' AND ownerid=? AND childid IS NULL", $ownerid)->getMboSet()->fetchData();
        Response::success($attachments); 
       
    }
    public function saveQuotation(Request $request) {
      
        $userPayload = $request->getUser();
        $vendorCode = $userPayload->data->vendor_code;
        $enterBy = $userPayload->data->username;

        $rfqNum = $request->get('rfqnum');
        $linesData = $request->get('quotation_lines');  // Ini adalah array dari baris item
        if (empty($rfqNum) || !is_array($linesData)) {
            error_log('Received rfqnum: ' . json_encode($rfqNum));
            error_log('Received quotation_lines: ' . json_encode($linesData));
            Response::error('Data RFQ Number atau Quotation Lines tidak valid.', 400);
        }

        // Dapatkan instance TransactionManager untuk mengontrol transaksi
        $tm = TransactionManager::getInstance();

        try {
            // 3. MULAI TRANSAKSI DATABASE. Ini sangat penting!
            // Semua baris harus berhasil disimpan, atau tidak sama sekali.
            $tm->beginTransaction();

            // 4. Loop melalui setiap baris item yang dikirim oleh vendor
            foreach ($linesData as $line) {
                $rfqLineNum = $line['rfqlinenum'];
                $unitCost = (float) $line['unitcost'];
                $remarks = $line['remarks'];
                // 5. Ambil data asli dari RFQLINE untuk keamanan & kelengkapan data
                // Ini untuk mencegah manipulasi data dari sisi klien (misal: quantity)
                $originalLine = (new Set())->useTransactionManager()
                                            ->MboSetRemote("RFQLINE")
                                            ->where("rfqnum = ? AND rfqlinenum = ?", $rfqNum, $rfqLineNum)
                                            ->getMboSet()
                                            ->fetch();
                
                if (!$originalLine) {
                    // Jika vendor mencoba submit baris yang tidak ada, gagalkan seluruh transaksi
                    throw new Exception("RFQ Line #{$rfqLineNum} is not found.");
                }

                // 6. Cek apakah vendor sudah pernah submit untuk baris ini (untuk menentukan INSERT atau UPDATE)
                $existingQuote = (new Set())->useTransactionManager()
                                             ->MboSetRemote("RFQQUOTATION")
                                             ->where("rfqnum = ? AND rfqlinenum = ? AND vendor = ?", $rfqNum, $rfqLineNum, $vendorCode)
                                             ->getMboSet()
                                             ->fetch();

                // 7. Siapkan data yang akan disimpan
                $quantity = (float) $originalLine['quantity'];
                $taxcode = $originalLine["taxcode"];
                $serviceitemnum = $originalLine["serviceitemnum"];
                $prnum = $originalLine["prnum"];
                $prlinenum = $originalLine["prlinenum"];
                $lineCost = $unitCost * $quantity;
                if(!empty($taxcode)){
                    $taxvalue = (new Set())->MboSetRemote("tax")->where("taxcode=?", $taxcode)->getMboValue("value");
                    $taxcost = $lineCost*($taxvalue/100);
                }else{
                    $taxcost = 0.00;
                }


                // Buat instance Set untuk operasi database
                $dbOperation = (new Set())->useTransactionManager()
                                          ->MboSetRemote("RFQQUOTATION")
                                          ->setValue("linetype", $originalLine['linetype'])
                                          ->setValue("itemnum", $originalLine['itemnum'])
                                          ->setValue("prnum", $originalLine['prnum'])
                                          ->setValue("prlinenum", $originalLine['prlinenum'])
                                          ->setValue("serviceitemnum", $originalLine['serviceitemnum'])
                                          ->setValue("requestedby", $originalLine['requestedby'])
                                          ->setValue("description", $originalLine['description'])
                                          ->setValue("quantity", $quantity)
                                          ->setValue("conversionfactor", 1) // Sesuai permintaan
                                          ->setValue("orderunit", $originalLine['orderunit'])
                                          ->setValue("partnum", $originalLine['partnum'])
                                          ->setValue("category", $originalLine['category'])
                                          ->setValue("catalog", $originalLine['catalog'])
                                          ->setValue("unitcost", $unitCost)
                                          ->setValue("remarks2", $remarks)
                                          ->setValue("linecost", $lineCost)
                                          ->setValue("taxcost", $taxcost)
                                          ->setValue("taxcode", $taxcode)
                                          ->setValue("changeby", $enterBy)
                                          ->setValue("changedate", date('Y-m-d H:i:s'));

                // 8. Logika INSERT atau UPDATE
                if ($existingQuote) {
                    // --- UPDATE ---
                    $dbOperation->where("rfqnum = ? AND rfqlinenum = ? AND vendor = ?", $rfqNum, $rfqLineNum, $vendorCode)
                                ->change();
                } else {
                    // --- INSERT ---
                    $dbOperation->setValue("rfqnum", $rfqNum)
                                ->setValue("rfqlinenum", $rfqLineNum)
                                ->setValue("vendor", $vendorCode)
                                ->setValue("enterby", $enterBy)
                                ->setValue("enterdate", date('Y-m-d H:i:s'))
                                ->add();
                }
            }

            $tm->commit();

            // 10. Kirim respons sukses
            Response::success([], 'Quotation has been saved successfully.');

        } catch (Exception $e) {
            // Jika terjadi error di manapun di dalam blok try, rollback semua perubahan
            $tm->rollback();
            Response::error('Failed to save quotation: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Mengambil daftar RFQ yang ditujukan untuk vendor yang sedang login.
     * @param Request $request
     */
    public function getList(Request $request) {
        // 1. Dapatkan data user dari token yang sudah divalidasi oleh router
        $userPayload = $request->getUser();
        $vendorCode = $userPayload->data->vendor_code;

        if (!$vendorCode) {
            Response::error('Vendor code tidak ditemukan di dalam token.', 400);
        }

        // 2. Query ke database untuk mendapatkan daftar RFQ
        
        $rfqList = (new Set())->useTransactionManager()->MboSetRemote("RFQVENDOR rv")
                        ->setRelationship("RFQ r", "r.rfqnum = rv.rfqnum")
                        ->where("rv.vendor = ?", $vendorCode)
                        ->MboRemote("r.rfqnum, r.description, r.status, r.replydate")
                        ->order("r.rfqdate DESC")
                        ->getMboSet()
                        ->fetchData();



        // 3. Kirim data sebagai respons sukses
        // Kita membungkusnya dalam key 'data' agar sesuai dengan format JQuery DataTables
        Response::success(['data' => $rfqList]);
    }


    /**
     * Mengambil detail item dari sebuah RFQ spesifik.
     * @param Request $request
     */
    public function getDetail(Request $request) {
        // 1. Dapatkan data user dan parameter rfqnum
        $userPayload = $request->getUser();
        $vendorCode = $userPayload->data->vendor_code;
        $rfqNum = $request->get('rfqnum');

        if (!$rfqNum) {
            Response::error('Parameter "rfqnum" wajib diisi.', 400);
        }

        // 2. (PENTING) Keamanan: Pastikan vendor ini memang diundang ke RFQ tersebut
     
        $isInvited = (new Set())->useTransactionManager()->MboSetRemote("RFQVENDOR")
                         ->where("rfqnum = ? AND vendor = ?", $rfqNum, $vendorCode)
                         ->getMboSet()
                         ->fetch();

        if (!$isInvited) {
            Response::error("You don't have access to this RFQ" , 403); // 403 Forbidden
        }

        // 3. Jika aman, ambil semua detail item dari RFQ Line
        $rfqLines = (new Set())->useTransactionManager()->MboSetRemote("RFQLINE")
                        ->where("rfqnum = ?", $rfqNum)
                        ->MboRemote("rfqlinenum, linetype, itemnum, description, quantity, orderunit, taxcode")
                        ->order("rfqlinenum ASC")
                        ->getMboSet()
                        ->fetchData();
        

        foreach ($rfqLines as &$row) { //Pakai reference untuk menambahkan key baru
            $rfqlinenum = $row["rfqlinenum"];
            $taxcode = $row["taxcode"];

            $rfqQuotation = (new Set())
                ->MboSetRemote("rfqquotation")
                ->where("rfqnum=? AND rfqlinenum=? AND vendor=?", $rfqNum, $rfqlinenum, $vendorCode)
                ->MboRemote("unitcost")
                ->MboRemote("linecost")
                ->MboRemote("taxcost")
                ->MboRemote("remarks2")
                ->getMboSet()
                ->getMbo();
            
            

            $unitcost = $rfqQuotation->getString("unitcost");
            $linecost = $rfqQuotation->getString("linecost");
            $taxcost  = $rfqQuotation->getString("taxcost");
            $remarks2  = $rfqQuotation->getString("remarks2");
            if(!empty($taxcode)){
                $taxvalue = (new Set())->MboSetRemote("tax")->where("taxcode=?", $taxcode)->getMboValue("value");
                $taxcost = $linecost*($taxvalue/100);
            }else{
                $taxcost =0.00;
            }

            // tambahkan key baru langsung ke array
            $row["unitcost"] = $unitcost;
            $row["linecost"] = $linecost;
            $row["taxcost"]  = $taxcost;
            $row["remarks2"]  = $remarks2;
        }


        $ntpData = [];

        $ntp_data =  (new Set())->useTransactionManager()->MboSetRemote("rfqntp")->where("rfqnum = ?", $rfqNum)->getMboSet()->fetchData();

        
        $data = [
            "ntp_data" => $ntp_data,
            "rfqlines"=> $rfqLines
        ];


        Response::success($data);
    }
}