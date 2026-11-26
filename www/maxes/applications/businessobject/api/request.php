<?php

class Request{
    public static function getParam($param = null) {
		
		if ($param === null) {
			
			return $_POST + $_GET;
		}

		return $_POST[$param] ?? $_GET[$param] ?? null;
	}


    public static function hasParam($param) {
		return isset($_POST[$param]) || isset($_GET[$param]);
	}

    public static function sendJsonResponse($status, $message, $data = []) {
		
		$response = ['status' => $status, 'message' => $message];
		if (!empty($data)) {
			$response['data'] = $data;
		}
		echo json_encode($response);
		
		exit();
	}
}

?>