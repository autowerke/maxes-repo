<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;


class AuthActions {

    private $config;

    public function __construct() {
        // Muat file konfigurasi saat class dibuat
        $this->config = require __DIR__ . '/../config.php';
    }

    public static function forgotpassword(){
        $org = (new Set())->useTransactionManager()->MboSetRemote("organization")->limit(0,1)->order("organizationid asc")->getMboSet()->getMbo();
        $vportaladmin = $org->getString("vportaladmin");

        $person = (new Set())->useTransactionManager()->MboSetRemote("employees")->where("personid=?", $vportaladmin)->getMboSet()->getMbo();

        $firstname = $person->getString("firstname");
        $lastname =  $person->getString("lastname");
        $primaryphone = $person->getString("primaryphone");
        $primaryemail = $person->getString("primaryemail");

        $admin = [
            "firstname" => $firstname, 
            "lastname" => $lastname,
            "primaryphone"=> $primaryphone,
            "primaryemail" => $primaryemail
        ];

        return $admin;
    }

    public function getForgotPasswordInstruction(Request $request) {
        $admin = self::forgotpassword();
        $firstname = $admin["firstname"];
        $lastname = $admin["lastname"];
        $primaryphone = $admin["primaryphone"];
        $primaryemail = $admin["primaryemail"];

       
        // Siapkan konten HTML yang akan ditampilkan di dialog
        $instructionsHtml = '<div>
                <p>To reset your password or retrieve your username, please contact our procurement team directly via one of the methods below.</p>
                <hr>
                <p><strong>Email:</strong><br><a href="mailto:'.$primaryemail.'">'.$primaryemail.'</a></p>
                <p><strong>WhatsApp:</strong><br><a href="https://wa.me/'.$primaryphone.'" target="_blank">'.$primaryphone.'</a></p>
                <hr>
                <p><small>Please provide your company name and registered email address for verification purposes.</small></p>
            </div>
        ';

        Response::success(['instructions' => $instructionsHtml]);
    }

    /**
     * Menangani permintaan registrasi vendor baru.
     * @param Request $request Objek request yang berisi data dari form.
     */
    public function register(Request $request) {
        // 1. Ambil semua data yang dibutuhkan dari request
        $companyName = $request->get('company_name');
        $vendorCode = $request->get('vendor_code'); 
        $contactName = $request->get('contact_name');
        $email = $request->get('email');
        $username = $request->get('username');
        $password = $request->get('password');

        // 2. Validasi Input (Contoh sederhana, bisa diperluas)
        if (empty($companyName) || empty($vendorCode) || empty($contactName) || empty($email) || empty($username) || empty($password)) {
            Response::error('All Fields are required.', 400);
        }

        // --- Mulai proses ke database ---
        $tm = TransactionManager::getInstance();
        $set = new Set();
        $set->useTransactionManager(); // Gunakan koneksi dari TransactionManager
      
        try {
            $tm->beginTransaction();
            // 3. Cek apakah Vendor Code, Username, atau Email sudah ada
            $existingVendor =  (new Set())->useTransactionManager()->MboSetRemote("vendor")->where("vendorcode = ?", $vendorCode)->getMboSet()->fetch();
            if ($existingVendor) {
                (new Set())->useTransactionManager()->MboSetRemote("vendor")->where("vendorcode = ?", $vendorCode)
                ->setValue("name", $companyName)
                ->setValue("enterby", $username)
                ->setValue("enterdate", date('Y-m-d H:i:s'))
                ->change();
                //Response::error("Vendor Code {$vendorCode} - {$companyName} has been registered.", 409); // 409 Conflict

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $checkUserName =  (new Set())->useTransactionManager()->MboSetRemote("vendorcontact")->where("vendorcode=? AND username=?", $vendorCode, $username)->getMboSet();
                if($checkUserName->isEmpty()){
                    // 2. Insert ke tabel vendorcontact
                    (new Set())->useTransactionManager()->MboSetRemote("vendorcontact")
                    ->setValue("vendorcode", $vendorCode)
                    ->setValue("contact", $contactName)
                    ->setValue("email", $email)
                    ->setValue("username", $username)
                    ->setValue("password", $hashedPassword)
                    ->setValue("enterby", $username)
                    ->setValue("enterdate", date('Y-m-d H:i:s'))
                    ->add();
                }else{
                    (new Set())->useTransactionManager()->MboSetRemote("vendorcontact")
                    ->where("vendorcode=? AND username=?", $vendorCode, $username)
                    ->setValue("contact", $contactName)
                    ->setValue("email", $email)
                    ->setValue("username", $username)
                    ->setValue("password", $hashedPassword)
                    ->setValue("enterby", $username)
                    ->setValue("enterdate", date('Y-m-d H:i:s'))
                    ->change();
                }
                
                $tm->commit();
                Response::success([], 'Registration Success. Please Login.', 201); // 201 Created

                exit;

            }
            $existingUser =  (new Set())->useTransactionManager()->MboSetRemote("vendorcontact")->where("username = ? OR email = ?", $username, $email)->getMboSet()->fetch();
            if ($existingUser) {
                Response::error("Username or email has been used.", 409);
            }

            // 4. Mulai Transaksi Database (Penting!)
            

            // 5. Insert ke tabel VENDOR
            (new Set())->useTransactionManager()->MboSetRemote("vendor")
                ->setValue("vendorcode", $vendorCode)
                ->setValue("name", $companyName)
                ->setValue("enterby", $username)
                ->setValue("enterdate", date('Y-m-d H:i:s'))
                ->setValue("disqualified", 'N') // Default tidak didiskualifikasi
                ->add();

            // 6. HASH PASSWORD! Jangan pernah simpan password sebagai plain text.
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // 7. Insert ke tabel vendorcontact
            (new Set())->useTransactionManager()->MboSetRemote("vendorcontact")
                ->setValue("vendorcode", $vendorCode)
                ->setValue("contact", $contactName)
                ->setValue("email", $email)
                ->setValue("username", $username)
                ->setValue("password", $hashedPassword)
                ->setValue("enterby", $username)
                ->setValue("enterdate", date('Y-m-d H:i:s'))
                ->add();

            // 8. Jika semua berhasil, commit transaksi
            $tm->commit();

            // 9. Kirim respons sukses
            Response::success([], 'Registration Success. Please Login.', 201); // 201 Created

        } catch (Exception $e) {
            // Jika terjadi error di salah satu langkah, rollback semua perubahan
            $tm->rollback();
            Response::error('Failed to Registration: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Menangani permintaan login.
     * @param Request $request Objek request yang berisi username & password.
     */
    public function login(Request $request) {
        $username = $request->get('username');
        $password = $request->get('password');

        if (empty($username) || empty($password)) {
            Response::error('Username dan password wajib diisi.', 400);
        }

        $set = new Set();
        // 1. Cari user di vendorcontact dan join ke vendor untuk cek status 'disqualified'
        $user = $set->MboSetRemote("vendorcontact vc")
                    ->setRelationship("vendor v", "v.vendorcode = vc.vendorcode")
                    ->where("vc.username = ?", $username)
                    ->MboRemote("vc.vendorcontactid, vc.username, vc.contact, vc.vendorcode, vc.password, v.name as company_name, v.disqualified")
                    ->getMboSet()
                    ->fetch();
        if (!$user) {
            Response::unauthorized('Wrong username and Password');
        }
        
        // 2. Verifikasi Password
        if (!password_verify($password, $user['password'])) {
            Response::unauthorized('Invalid Username or Password');
        }

        // 3. Cek apakah vendor di-diskualifikasi
        if ($user['disqualified'] === 'Y' || $user['disqualified'] === 1) {
            Response::error('Your account has been disqualified.', 403); // 403 Forbidden
        }

         // --- PEMBUATAN TOKEN JWT ---
        $secretKey = $this->config['jwt']['secret_key'];
        $issuedAt = time();
        $expire = $issuedAt + (60 * 60 * 8); // Token berlaku 8 jam

        $payload = [
            'iss' => "maxtest_vportal",  // Issuer
            'iat' => $issuedAt,         // Issued at
            'exp' => $expire,           // Expiration time
            'sub' => $user['vendorcontactid'], // Subject (Unique ID User)
            'data' => [                 // Data custom yang ingin kita simpan
                'vendor_code' => $user['vendorcode'],
                'username' => $user['username'],
                'contact_name' => $user['contact'],
                'company_name' => $user['company_name']
            ]
        ];

      
        $jwt = JWT::encode($payload, $secretKey, $this->config['jwt']['algorithm']);

        /*
        Response::success([
            'message' => 'Login berhasil!',
            'token' => $jwt
        ]);
        */
        Response::success([
            'token' => $jwt
        ]);
       
    }

  /**
     * Memvalidasi token dari header Authorization.
     * Digunakan oleh router.php sebagai middleware.
     * @param Request $request
     * @return object Payload yang sudah di-decode jika valid.
     */
    public function validateTokenFromRequest(Request $request) {
        // DEBUG
        error_log('=== validateTokenFromRequest START ===');
        error_log('HTTP_AUTHORIZATION: ' . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'KOSONG'));
        
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            error_log('ERROR: Authorization header tidak ditemukan');
            Response::unauthorized('Token tidak ditemukan.');
        }

        error_log('Authorization header received: ' . substr($authHeader, 0, 50) . '...');

        // Token diharapkan dalam format "Bearer <token>"
        $matches = [];
        if (preg_match('/Bearer\s+(.+?)$/i', $authHeader, $matches)) {
            $jwt = $matches[1];
            error_log('Token extracted: ' . substr($jwt, 0, 30) . '...');
        } else {
            error_log('ERROR: Token tidak bisa di-extract dari header');
            Response::unauthorized('Format token tidak valid. Expected: Bearer <token>');
        }

        try {
            $secretKey = $this->config['jwt']['secret_key'];
            $algorithm = $this->config['jwt']['algorithm'];

            error_log('Decoding token dengan algorithm: ' . $algorithm);

            // Decode token
            $decoded = JWT::decode($jwt, new Key($secretKey, $algorithm));

            error_log('Token decoded successfully');
            error_log('Token payload: ' . json_encode($decoded));

            return $decoded;

        } catch (ExpiredException $e) {
            error_log('ERROR: Token expired - ' . $e->getMessage());
            Response::unauthorized('Token has been expired.');
        } catch (Exception $e) {
            error_log('ERROR: Token validation failed - ' . $e->getMessage());
            Response::unauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }

    public function validateToken(Request $request) {
        try {
            $decoded = $this->validateTokenFromRequest($request);
            
            // Jika sampai sini, token valid
            Response::success($decoded, 'Token valid');
            
        } catch (Exception $e) {
            // Error handling sudah dilakukan di validateTokenFromRequest()
            // jadi tidak perlu di-catch di sini
        }
    }


}