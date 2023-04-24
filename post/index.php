<?php
require_once "../RestRequest.php";
require_once "../database/Database.php";
require_once "Post.php";

session_start();
$request = new RestRequest();
$response = array("error" => false, "msg" => "");
try {
    $reqVars = $request->getRequestVariables();

    $post = new Post();

// check which type of request was made
    if ($request->isGet()) {
        $response["error"] = false;
        $response["msg"] = "Get";
        if (array_key_exists("id", $reqVars)) {
            try {
                $queryResult = $post->findById($reqVars["id"]);

                $response["error"] = false;
                $response["id"] = $queryResult["id"];
                $response["user_id"] = $queryResult["user_id"];
                $response["username"] = $queryResult["username"];
                $response["post_date"] = $queryResult["post_date"];
                $response["post_text"] = $queryResult["post_text"];
                $response["extra"] = $queryResult["extra"];
            } catch (Exception $e) {
                $response["error"] = true;
                $response["msg"] = $e->getMessage();
            }
        } elseif (array_key_exists("user_id", $reqVars)) {
            try {
                $queryResult = $post->findByUserId($reqVars["user_id"]);

                $response["error"] = false;
                //$response["id"] = $queryResult["id"];
                //$response["user_id"] = $queryResult["user_id"];
                //$response["username"] = $queryResult["username"];
                //$response["post_date"] = $queryResult["post_date"];
                //$response["post_text"] = $queryResult["post_text"];
                //$response["extra"] = $queryResult["extra"];
                $response["posts"] = $queryResult;
            } catch (Exception $e) {
                $response["error"] = true;
                $response["msg"] = $e->getMessage();
            }
        } else {
            $response["error"] = true;
            $response["msg"] = "No post id or user id given";
        }
    } elseif ($request->isPost()) {
        if (array_key_exists('user_id', $_SESSION)) {
            $response["error"] = false;
            $response["msg"] = "Post";
            if (array_key_exists("user_id", $reqVars)
                && array_key_exists("post_text", $reqVars)
                && array_key_exists("extra", $reqVars)) {
                try {
                    $new_id = $post->create(["user_id" => $reqVars["user_id"], "post_text" => $reqVars["post_text"],
                        "extra" => json_encode($reqVars["extra"]), "session_userid" => $reqVars["user_id"]]);
                    $response["error"] = false;
                    $response["msg"] = $new_id;
                } catch (Exception $e) {
                    $response["error"] = true;
                    $response["msg"] = $e->getMessage();
                }
            } else {
                $response["error"] = true;
                $response["msg"] = "Missing parameter".print_r($reqVars, true);
            }
        }
        else {
            $response["error"] = true;
            $response["msg"] = "Not logged in";
        }
    } elseif ($request->isPut()) {
        if (array_key_exists('user_id', $_SESSION)) {
            if (array_key_exists("id", $reqVars) && array_key_exists("post_text", $reqVars)
                && array_key_exists("extra", $reqVars)) {
                try {
                    $post->update(["id" => $reqVars["id"], "post_text" => $reqVars["post_text"],
                        "extra" => $reqVars["extra"], "session_userid" => $_SESSION['user_id']]);
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
            if (array_key_exists("id", $reqVars) && array_key_exists("user_id", $reqVars)) { // TODO fix
                try {
                    $post->deleteById(["id" => $reqVars["id"], "session_userid" => $_SESSION["user_id"]]);
                    $response["error"] = false;
                    $response["msg"] = "Success";
                } catch (Exception $e) {
                    $response["error"] = true;
                    $response["msg"] = $e->getMessage();
                }
            } else {
                $response["error"] = true;
                $response["msg"] = "No id given";
            }
        } else {
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
    $response["msg"] = "Request variables not found";
    echo json_encode($response);
}
