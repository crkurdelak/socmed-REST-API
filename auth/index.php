<?php

require_once "RestRequest.php";
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
    $name = $reqVars['user'];

    // if already logged in
    if(array_key_exists('username', $_SESSION)){
        $username = $_SESSION["username"];

        if ($name === $username) {
            sendStatus($username . " is already logged in");
            //echo "Already logged in! ".$_SESSION['username'];
        }
        else {
            sendStatus("Cannot log $name in, $username already logged in");
        }
    }
    // if new login
    else {
        if (array_key_exists('user', $reqVars)) {
            try {
                $password = (new User)->findByUsername($name)["password"];
            } catch (Exception $e) {

            }
            if ($password) {
                // pretend this password was retrieved from db
                if (password_verify($reqVars["password"], $password)) {
                    // register username with session
                    $_SESSION['username'] = $name;

                    // build response
                    sendStatus($name . " logged in");
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
    if (array_key_exists("username", $_SESSION)) {
        $username = $_SESSION["username"];
        sendStatus("Session for $username ended.");
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
    if (array_key_exists("username", $_SESSION)) {
        $username = $_SESSION["username"];
        sendStatus("$username logged in");
    }
    else {
        sendStatus("no one logged in");
    }
}
else {
    // operation not supported
    sendStatus("operation not supported");
}