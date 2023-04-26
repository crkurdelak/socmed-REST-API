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

/**
 * Verifies the given password and logs the user in if it is correct.
 * @param string $id the user's ID number
 * @param string $pw the password input by the user
 * @return void
 */
function verifyPassword(string $id, string $pw): void
{
    try {
        $password = (new User)->getPw($id)["password"];
        if ($password) {
            if (password_verify($pw, $password)) {
                // register username with session
                $_SESSION['user_id'] = $id;

                // build response
                sendStatus($id . " logged in");
                //echo "Welcome, " .$reqVars['user'];
            } else {
                sendStatus("Incorrect password");
            }
        } else {
            sendStatus("No password given");
        }
    } catch (Exception $e) {
        sendStatus("User not found (verifyPassword)");
    }
}

if ($request->isPost()) {
    // create session or join existing session
    session_start();
    $id = $reqVars['id'];
    $username = $reqVars['username'];

    // if already logged in
    if(array_key_exists('user_id', $_SESSION)){
        $user_id = $_SESSION["user_id"];

        // if logging in with a username instead of an id, get the corresponding id
        if (array_key_exists('username', $reqVars)) {
            try {
                $id = (new User)->getId($username)["id"];
            }
            catch (Exception $e) {
                sendStatus("User not found");
            }
        }
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
            verifyPassword($id, $reqVars["password"]);
        }
        else if (array_key_exists('username', $reqVars)) {
            try {
                $id = (new User)->getId($username)["id"];
            }
            catch (Exception $e) {
                sendStatus("User not found");
            }
            if ($id) {
                verifyPassword($id, $reqVars["password"]);
            }
        }
        else {
            sendStatus("No user given");
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