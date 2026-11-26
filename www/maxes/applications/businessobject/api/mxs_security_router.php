<?php
include_once __DIR__ . '/mxs_login.php';
require_once  __DIR__ . '/request.php';

if (Request::hasParam("action")) {
    $action = Request::getParam("action");

    switch ($action) {
        case 'logout': 
             case 'logout':
            Logout::logoutUser();
            break;

        case 'login':
            Login::validateLogin();
            break;
        
        case 'getUserProfile':
            $userData = Auth::validateToken();
            $userid = $userData->sub;
            $userSet = (new Set())->MboSetRemote("mxsusers")
                        ->where("userid = ?", $userid) 
                        ->MboRemote("userid") 
                        ->getMboSet();
            
            if ($userSet->isEmpty()) {
                Request::sendJsonResponse("error", "User associated with token not found.");
            }

            $userProfile = $userSet->fetch(); // Ambil data sebagai array
            
            Request::sendJsonResponse("success", "User profile retrieved.", $userProfile);
            break;

        default:
            Request::sendJsonResponse("error", "Invalid action specified.");
            break;
    }

} else {
    Request::sendJsonResponse("error", "No action specified.");
}

?>