<?php
require_once '../RestRequest.php';
require_once "../database/Database.php";

/**
 * A class representing a post on a blog.
 */
class Post
{
    private PDO $db;

    public function __construct()
    {
        $db = new Database();
        $this->db = $db->connect();
    }

    /**
     * Adds a new post to the given user's blog.
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function create(array $data) {
        $sql = 'INSERT INTO post (id, user_id, post_date, post_text, extra) 
        VALUES(:id, :user_id, CURRENT_DATE, :post_text, :extra);';

        $id_sql = 'SELECT MAX(id) FROM post;';
        $this->db->beginTransaction();
        $id_query = $this->db->prepare($id_sql);
        $id_query->execute();
        $max_id = $id_query->fetch(PDO::FETCH_ASSOC);
        $id = $max_id["max"] + 1;

        $queryParams = [
            ':id' => $id,
            ':user_id' => $data['user_id'],
            //':post_date' => $data['post_date'],
            ':post_text' => $data['post_text'],
            ':extra' => $data['extra']
        ];

        $query = $this->db->prepare($sql);
        $success = $query->execute($queryParams);
        $this->db->commit();

        if (!$success) {
            throw new Exception('Failed to create post');
        }
    }


    /**
     * Finds the post with the given id.
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function findById(string $id): array {
        $sql = 'SELECT * FROM post WHERE id = ?';

        $query = $this->db->prepare($sql);
        $query->execute([$id]);

        $post = $query->fetch(PDO::FETCH_ASSOC);

        if ($post === false) {
            throw new Exception('blog-db\Post not found: ' . $id);

        }

        return ["id" => $post["id"], "user_id" => $post["user_id"], "username" => $post["username"], "post_date" => $post["post_date"],
                "post_text" => $post["post_text"], "extra" => $post["extra"]];
    }


    /**
     * Finds all posts by the user with the given user id.
     * @param string $user_id
     * @return array
     * @throws Exception
     */
    public function findByUserId(string $user_id): array {
        $sql = 'SELECT * FROM user NATURAL JOIN post WHERE user.id = ?';

        $query = $this->db->prepare($sql);
        $query->execute([$user_id]);

        $posts = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($posts === false) {
            throw new Exception('blog-db\Posts not found: user ' . $user_id);

        }

        //return ["id" => $posts["id"], "username" => $posts["username"], "user_id" => $posts["user_id"], "post_date" => $posts["post_date"],
        //    "post_text" => $posts["post_text"], "extra" => $posts["extra"]];
        return json_encode($posts);
    }


    /**
     * Updates the post text and extra of the post with the given id.
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function update(array $data) {
        $sql = 'UPDATE post SET post_text = :post_text, extra = :extra WHERE id = :id';

        $queryParams = [
            ':id' => $data['id'],
            ':post_text' => $data['post_text'],
            ':extra' => $data['extra']
        ];

        $query = $this->db->prepare($sql);
        $success = $query->execute($queryParams);

        if (!$success) {
            throw new Exception('Failed to update post');
        }
    }


    /**
     * Deletes the post with the given id.
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function deleteById(string $id) {
        $sql = 'DELETE FROM post WHERE id = ?';

        $query = $this->db->prepare($sql);
        $success = $query->execute([$id]);

        if (!$success) {
            throw new Exception('blog-db\Could not delete post: ' . $id);

        }
    }
}
