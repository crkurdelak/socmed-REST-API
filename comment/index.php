<?php
require_once "../RestRequest.php";
require_once "../database/Database.php";
require_once "Comment.php";
require_once "../auth/index.php";

session_start();
$request = new RestRequest();
$response = array("error" => false, "msg" => "");
try {
    $reqVars = $request->getRequestVariables();

    $comment = new Comment();

    if (session_id()) { // TODO check for session in methods that edit

// check which type of request was made
        if ($request->isGet()) {
            $response["error"] = false;
            $response["msg"] = "Get";
            if (array_key_exists("id", $reqVars)) {
                try {
                    $queryResult = $comment->findById($reqVars["id"]);

                    $response["error"] = false;
                    $response["id"] = $queryResult["id"];
                    $response["user_id"] = $queryResult["user_id"];
                    $response["post_id"] = $queryResult["post_id"];
                    $response["comment_date"] = $queryResult["comment_date"];
                    $response["comment_text"] = $queryResult["comment_text"];
                } catch (Exception $e) {
                    $response["error"] = true;
                    $response["msg"] = $e->getMessage();
                }
            } elseif (array_key_exists("post_id", $reqVars)) {
                try {
                    $queryResult = $comment->findByPostId($reqVars["user_id"]);

                    $response["error"] = false;
                    //$response["id"] = $queryResult["id"];
                    $response["user_id"] = $queryResult["user_id"];
                    $response["username"] = $queryResult["username"];
                    $response["post_id"] = $queryResult["post_id"];
                    //$response["comment_date"] = $queryResult["comment_date"];
                    //$response["comment_text"] = $queryResult["comment_text"];
                    $response["comments"] = $queryResult;
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
                && array_key_exists("post_id", $reqVars) && array_key_exists("comment_text", $reqVars)) {
                try {
                    $comment->create([$reqVars["id"], $reqVars["user_id"], $reqVars["post_id"], $reqVars["comment_text"]]);
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
                    $comment->update([$reqVars["id"], $reqVars["comment_text"]]);
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
                    $comment->deleteById($reqVars["id"]);
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
