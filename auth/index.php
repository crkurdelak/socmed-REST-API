<?php

require_once "../RestRequest.php";
require_once "../user/User.php";

function sendStatus($msg) {
    $resp = array("status" => $msg);
    echo json_encode($resp);
}

$request = new RestRequest();
$reqVars = $request->getRequestVariables();
$response = array("error" => false, "msg" => "");

if ($request->isPost()) {
    // create session or join existing session
    session_start();
    $id = $reqVars['id'];

    // if already logged in
    if(array_key_exists('user_id', $_SESSION)){
        $user_id = $_SESSION["user_id"];

        if ($id === $user_id) {
            sendStatus($user_id . " is already logged in");
            //echo "Already logged in! ".$_SESSION['username'];
        }
        else {
            sendStatus("Cannot log $id in, $user_id already logged in");
        }
    }
    // if new login
    else {
        if (array_key_exists('id', $reqVars)) {
            try {
                $password = (new User)->findById($id)["password"];
            } catch (Exception $e) {
                sendStatus("User not found");
            }
            if ($password) {
                if (password_verify($reqVars["password"], $password)) {
                    // register username with session
                    $_SESSION['user_id'] = $id;

                    // build response
                    sendStatus($id . " logged in");
                    //echo "Welcome, " .$reqVars['user'];
                }
            }
            else {
                sendStatus("Incorrect password");
            }
        }
        else {
            echo "No user given";
        }
    }

}
else if ($request->isDelete()) {
    session_start();
    if (array_key_exists('user_id', $_SESSION)) {
        $user_id = $_SESSION['user_id'];
        sendStatus("Session for $user_id ended.");
    }
    else {
        sendStatus("Session ended for no one");
    }
    session_destroy();
}
else if ($request->isGet()) {
    // create or join session
    session_start();

    // check if logged in
    if (array_key_exists('user_id', $_SESSION)) {
        $user_id = $_SESSION['user_id'];
        sendStatus("$user_id logged in");
    }
    else {
        sendStatus("no one logged in");
    }
}
else {
    // operation not supported
    sendStatus("operation not supported");
}