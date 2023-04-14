<?php
require_once "../RestRequest.php";
require_once "../database/Database.php";
require_once "Post.php";
require_once "../auth/index.php";

$request = new RestRequest();
$response = array("error" => false, "msg" => "");
try {
    $reqVars = $request->getRequestVariables();

    $post = new Post();

    if (session_id()) {

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
                    $response["user_id"] = $queryResult["user_id"];
                    $response["username"] = $queryResult["username"];
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
            $response["error"] = false;
            $response["msg"] = "Post";
            if (array_key_exists("id", $reqVars) && array_key_exists("user_id", $reqVars)
                && array_key_exists("post_text", $reqVars)
                && array_key_exists("extra", $reqVars)) {
                try {
                    $post->create([$reqVars["id"], $reqVars["user_id"], $reqVars["post_text"],
                        $reqVars["extra"]]);
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
            if (array_key_exists("id", $reqVars) && array_key_exists("username", $reqVars)
                && array_key_exists("password", $reqVars)) {
                try {
                    $post->update([$reqVars["id"], $reqVars["post_text"], $reqVars["extra"]]);
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
        } elseif ($request->isDelete()) {
            $response["error"] = false;
            $response["msg"] = "Delete";
            if (array_key_exists("id", $reqVars)) {
                try {
                    $post->deleteById($reqVars["id"]);
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
            $response["msg"] = "Wrong Request Type";
        }
        echo json_encode($response);
    }
}
catch (Exception $e) {
    $response['error'] = true;
    $response["msg"] = "Request variables not found";
    echo json_encode($response);
}
