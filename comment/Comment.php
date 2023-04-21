<?php
require_once '../RestRequest.php';
require_once "../database/Database.php";

/**
 * A class representing a comment on a post.
 */
class Comment
{
    private PDO $db;

    public function __construct()
    {
        $db = new Database();
        $this->db = $db->connect();
    }

    /**
     * Adds a new comment to the given post.
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function create(array $data) {
        $sql = 'INSERT INTO blog_comment (id, user_id, post_id, comment_date, comment_text) 
        VALUES(:id, :user_id, :post_id, CURRENT_DATE, :comment_text);';

        $id_sql = 'SELECT MAX(id) FROM blog_comment;';
        $this->db->beginTransaction();
        $id_query = $this->db->prepare($id_sql);
        $id_query->execute();
        $max_id = $id_query->fetch(PDO::FETCH_ASSOC);
        $id = $max_id["max"] + 1;

        $queryParams = [
            ':id' => $id,
            ':user_id' => $data['user_id'],
            ':post_id' => $data['post_id'],
            //':comment_date' => $data['comment_date'],
            ':comment_text' => $data['comment_text']
        ];

        $query = $this->db->prepare($sql);
        $success = $query->execute($queryParams);
        $this->db->commit();

        if (!$success) {
            throw new Exception('Failed to create comment');
        }
    }


    /**
     * Finds the comment with the given id.
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function findById(string $id): array {
        $sql = 'SELECT * FROM blog_comment WHERE id = ?';

        $query = $this->db->prepare($sql);
        $query->execute([$id]);

        $comment = $query->fetch(PDO::FETCH_ASSOC);

        if ($comment === false) {
            throw new Exception('blog-db\Comment not found: ' . $id);

        }

        return ["id" => $comment["id"], "user_id" => $comment["user_id"], "post_id" => $comment["post_id"],
            "comment_date" => $comment["comment_date"], "comment_text" => $comment["comment_text"]];
    }


    /**
     * Finds all comments on the post with the given post id.
     * @param string $post_id
     * @return array
     * @throws Exception
     */
    public function findByPostId(string $post_id): array {
        //$sql = 'SELECT * FROM blog_comment NATURAL JOIN post WHERE post.id = ?';
        $sql = 'SELECT blog_comment.*, blog_user.username FROM blog_user RIGHT JOIN blog_comment 
                ON blog_user.id = blog_comment.user_id RIGHT JOIN post ON post.id = blog_comment.post_id 
                                          WHERE post.id = ?';

        $query = $this->db->prepare($sql);
        $query->execute([$post_id]);

        $comments = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($comments === false || count($comments) === 0) {
            throw new Exception('blog-db\Comments not found: post ' . $post_id);

        }

        //return ["id" => $comments["id"], "user_id" => $comments["user_id"], "post_id" => $comments["post_id"],
        //        "comment_text" => $comments["comment_text"], "comment_date" => $comments["comment_date"]];
        return $comments;
    }


    /**
     * Updates the comment text of the comment with the given id.
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function update(array $data) {
        $sql = 'UPDATE blog_comment SET comment_text = :comment_text WHERE id = :id';

        $userid_sql = 'SELECT user_id FROM post WHERE id = ?';
        $userid_query = $this->db->prepare($userid_sql);
        $user_id = $userid_query->execute([$data["id"]]);

        if ($user_id == $data["session_userid"]) {
            $queryParams = [
                ':id' => $data['id'],
                ':comment_text' => $data['comment_text']
            ];

            $query = $this->db->prepare($sql);
            $success = $query->execute($queryParams);

            if (!$success) {
                throw new Exception('Failed to update comment');
            }
        }
        else {
            throw new Exception('That is someone else\'s comment. your id: '.$data["session_userid"].'
             comment user id:'.$data["id"]);
        }
    }


    /**
     * Deletes the comment with the given id.
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function deleteById(array $data) {
        $sql = 'DELETE FROM blog_comment WHERE id = ?';
        $userid_sql = 'SELECT user_id FROM post WHERE id = ?';

        $userid_query = $this->db->prepare($userid_sql);
        $user_id = $userid_query->execute([$data["id"]]);

        if ($user_id == $data["session_userid"]) {
            $query = $this->db->prepare($sql);
            $success = $query->execute($data["id"]);

            if (!$success) {
                throw new Exception('blog-db\Could not delete comment: ' . $data["id"]);

            }
        }
        else {
            throw new Exception('That is someone else\'s comment. your id: '.$data["session_userid"].'
             comment user id:'.$data["id"]);
        }
    }
}