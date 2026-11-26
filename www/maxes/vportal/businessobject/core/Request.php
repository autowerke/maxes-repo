<?php

class Request {
    private $params;
    private $user = null;
    public function __construct() {
        // Cek jika content type adalah JSON (buat lebih robust dengan strtolower)
        $contentType = strtolower($_SERVER["CONTENT_TYPE"] ?? '');

        if (strpos($contentType, "application/json") !== false) {
            
            // --- TRACE POINT #1: Apa isi raw body? ---
            $jsonInput = file_get_contents('php://input');
            error_log("TRACE (Request.php): Raw Body Input => " . $jsonInput);

            // --- TRACE POINT #2: Apa hasil decode? ---
            $decoded = json_decode($jsonInput, true);
            $this->params = is_array($decoded) ? $decoded : [];
            error_log("TRACE (Request.php): Decoded Params => " . print_r($this->params, true));

        } else {
            error_log("TRACE (Request.php): Content-Type BUKAN JSON => " . $contentType);
            $this->params = $_REQUEST;
        }
    }

    public function all() {
        return $this->params;
    }

    public function has(string $key): bool {
        return isset($this->params[$key]);
    }

    public function get(string $key = null, $default = null) {
        if ($key === null) {
            return $this->all();
        }
        return $this->params[$key] ?? $default;
    }
   
    public function getString(string $key): ?string {
        $value = $this->get($key);
        return $value !== null ? trim(filter_var($value, FILTER_SANITIZE_STRING)) : null;
    }

    public function getInt(string $key): ?int {
        $value = $this->get($key);
        return $value !== null ? (int) $value : null;
    }

    /**
     * Menyimpan data user yang sudah diautentikasi ke dalam request.
     * @param object $user Payload dari token JWT.
     */
    public function setUser(object $user) {
        $this->user = $user;
    }

    /**
     * Mengambil data user yang sudah diautentikasi.
     * @return object|null
     */
    public function getUser(): ?object {
        return $this->user;
    }
}