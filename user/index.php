<?php
require_once "../RestRequest.php";
require_once "../database/Database.php";
require_once "User.php";

session_start();
$request = new RestRequest();
$response = array("error" => false, "msg" => "");
try {
    $reqVars = $request->getRequestVariables();

    $user = new User();

// check which type of request was made
    if ($request->isGet()) {
        if (array_key_exists('user_id', $_SESSION)) {
            $response["error"] = false;
            $response["msg"] = "Get";
            if (array_key_exists("username", $reqVars)) {
                try {
                    $queryResult = $user->findByUsername(["username" => $reqVars["username"],
                        "session_userid" => $_SESSION['user_id']]);

                    $response["error"] = false;
                    $response["id"] = $queryResult["id"];
                    $response["username"] = $queryResult["username"];
                    $response["password"] = $queryResult["password"];
                } catch (Exception $e) {
                    $response["error"] = true;
                    $response["msg"] = $e->getMessage();
                }
            } elseif (array_key_exists("id", $reqVars)) {
                try {
                    $queryResult = $user->findById(["id" => $reqVars["id"], "session_userid" => $_SESSION['user_id']]);

                    $response["error"] = false;
                    $response["id"] = $queryResult["id"];
                    $response["username"] = $queryResult["username"];
                    $response["password"] = $queryResult["password"];
                } catch (Exception $e) {
                    $response["error"] = true;
                    $response["msg"] = $e->getMessage();
                }
            } else {
                $response["error"] = true;
                $response["msg"] = "No username or id given";
            }
        }
        else {
            $response["error"] = true;
            $response["msg"] = "Not logged in";
        }
    } elseif ($request->isPost()) {
        $response["error"] = false;
        $response["msg"] = "Post";
        if (array_key_exists("username", $reqVars) && array_key_exists("password", $reqVars)) {
            try {
                // encrypt user password
                $cipherPass = password_hash($reqVars["password"], CRYPT_BLOWFISH);
                $user->create(['username' => $reqVars["username"], 'password' => $cipherPass]);
                $response["error"] = false;
                $response["msg"] = "Success";
            } catch (Exception $e) {
                $response["error"] = true;
                $response["msg"] = $e->getMessage();
            }
        } else {
            $response["error"] = true;
            $response["msg"] = "Missing parameter";
        }
    } elseif ($request->isPut()) {
        if (array_key_exists('user_id', $_SESSION)) {
            if (array_key_exists("username", $reqVars)
                && array_key_exists("old_password", $reqVars) && array_key_exists("new_password", $reqVars)) {
                try {
                    // encrypt new password
                    $cipherPass = password_hash($reqVars["new_password"], CRYPT_BLOWFISH);
                    $user->update(["username" => $reqVars['username'],
                        "new_password" => $cipherPass, "old_password" => $reqVars["old_password"],
                        "session_userid" => $_SESSION["user_id"]]);
                    $response["error"] = false;
                    $response["msg"] = "Success";
                } catch (Exception $e) {
                    $response["error"] = true;
                    $response["msg"] = $e->getMessage();
                }
            } else {
                $response["error"] = true;
                $response["msg"] = "Missing parameter";
            }
        }
        else {
            $response["error"] = true;
            $response["msg"] = "Not logged in";
        }
    } elseif ($request->isDelete()) {
        if (array_key_exists('user_id', $_SESSION)) {
            $response["error"] = false;
            $response["msg"] = "Delete";
            try {
                $user->deleteById(["session_userid" => $_SESSION["user_id"]]);
                $response["error"] = false;
                $response["msg"] = "Success";
            } catch (Exception $e) {
                $response["error"] = true;
                $response["msg"] = $e->getMessage();
            }
        }
        else {
            $response["error"] = true;
            $response["msg"] = "Not logged in";
        }
    } else {
        $response["error"] = true;
        $response["msg"] = "Wrong Request Type";
    }
    echo json_encode($response);
}
catch (Exception $e) {
    $response['error'] = true;
    $response["msg"] = $e->getMessage();
    echo json_encode($response);
}
