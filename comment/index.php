<?php
require_once "../RestRequest.php";
require_once "../database/Database.php";
require_once "Comment.php";

session_start();
$request = new RestRequest();
$response = array("error" => false, "msg" => "");
try {
    $reqVars = $request->getRequestVariables();

    $comment = new Comment();

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
                $queryResult = $comment->findByPostId($reqVars["post_id"]);

                $response["error"] = false;
                //$response["id"] = $queryResult["id"];
                //$response["user_id"] = $queryResult["user_id"];
                //$response["username"] = $queryResult["username"];
                //$response["post_id"] = $queryResult["post_id"];
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
        if (array_key_exists('user_id', $_SESSION) && $reqVars['user_id'] === $_SESSION['user_id']) {
            $response["error"] = false;
            $response["msg"] = "Post";
            if (array_key_exists("user_id", $reqVars)
                && array_key_exists("post_id", $reqVars) && array_key_exists("comment_text", $reqVars)) {
                try {
                    $comment->create(["user_id" => $reqVars["user_id"], "post_id" => $reqVars["post_id"],
                        "comment_text" => $reqVars["comment_text"]]);
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
    } elseif ($request->isPut()) {
        if (array_key_exists('user_id', $_SESSION)) {
            if (array_key_exists("id", $reqVars) && array_key_exists("comment_text", $reqVars)) {
                try {
                    $comment->update(["id" => $reqVars["id"], "comment_text" => $reqVars["comment_text"],
                        "session_userid" => $_SESSION['user_id']]);
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
            if (array_key_exists("id", $reqVars) && array_key_exists("user_id", $reqVars)) {
                try {
                    $comment->deleteById(["id" => $reqVars["id"], "session_userid" => $reqVars["user_id"]]);
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
    $response["msg"] = "Request variables not found";
    echo json_encode($response);
}
