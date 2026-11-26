<?php

// Pastikan path ke library-mu sudah benar
require_once 'objects.php'; 
require_once 'TransactionManager.php';

// --- KONFIGURASI ---
const NEW_DEFAULT_PASSWORD = '123456';
// --------------------

echo "=============================================\n";
echo " memulai skrip Reset Password Massal\n";
echo "=============================================\n";

try {
  
    $userMboSet = (new Set())->MboSetRemote("mxsusers")
                         ->MboRemote("userid") 
                         ->getMboSet();

    if ($userMboSet->isEmpty()) {
        echo "Tidak ada user yang ditemukan di tabel mxsusers.\n";
        exit;
    }
   
    while ($user = $userMboSet->fetch()) {
        $userId = $user["userid"];
        if (empty($userId)) {
            continue; 
        }

        echo "-> Mereset password untuk user: " . $userId . "... <br>";

        $newPasswordHash = password_hash(NEW_DEFAULT_PASSWORD, PASSWORD_DEFAULT);

       (new Set())->useTransactionManager() 
                  ->MboSetRemote("mxsusers")
                  ->where("userid = ?", $userId) 
                  ->setValue("password_hash", $newPasswordHash)
                  ->change();
        
        echo "[OK]\n";
    }

    echo "\n=============================================\n";
    echo " SELESAI! Semua password user telah direset.\n";
    echo "=============================================\n";

} catch (Exception $e) {
    echo "\n\n!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
    echo " ERROR TERJADI: \n";
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
    echo "Pesan Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Baris: " . $e->getLine() . "\n";
}

?>