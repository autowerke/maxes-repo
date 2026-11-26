<?php

class Response {

  
    public static function json(array $data, int $httpStatusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($httpStatusCode);
        echo json_encode($data);
        exit();
    }

 
    public static function success($data = [], string $message = 'Success') {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

   
    public static function error(string $message, int $httpStatusCode = 400, array $errors = []) {
        $payload = [
            'success' => false,
            'message' => $message
        ];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }
        self::json($payload, $httpStatusCode);
    }

 
    public static function notFound(string $message = 'Resource not found') {
        self::error($message, 404);
    }

  
    public static function unauthorized(string $message = 'Authentication failed') {
        self::error($message, 401);
    }
}