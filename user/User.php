<?php
require_once '../RestRequest.php';
require_once "../database/Database.php";

/**
 * A class representing a user.
 */
class User
{
    private PDO $db;

    public function __construct()
    {
        $db = new Database();
        $this->db = $db->connect();
    }

    /**
     * Adds a new user to the database.
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function create(array $data) {
        $sql = 'INSERT INTO blog_user (id, username, password) VALUES(:id, :username, :password);';

        $id_sql = 'SELECT MAX(id) FROM blog_user;';
        $this->db->beginTransaction();
        $id_query = $this->db->prepare($id_sql);
        $id_query->execute();
        $max_id = $id_query->fetch(PDO::FETCH_ASSOC);
        $id = $max_id["max"] + 1;

        $queryParams = [
            ':id' => $id,
            ':username' => $data['username'],
            // password is already encrypted
            ':password' => $data['password']
        ];

        $query = $this->db->prepare($sql);
        $success = $query->execute($queryParams);
        $this->db->commit();

        if (!$success) {
            throw new Exception('Failed to create user');
        }
    }


    /**
     * Finds the user with the given id.
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function findById(string $id): array {
        $sql = 'SELECT * FROM blog_user WHERE id = ?';

        $query = $this->db->prepare($sql);
        $query->execute([$id]);

        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            throw new Exception('blog-db\User not found: ' . $id);

        }

        return ["id" => $user["id"], "username" => $user["username"], "password" => $user["password"]];
    }


    /**
     * Finds the user with the given username.
     * @param string $username
     * @return array
     * @throws Exception
     */
    public function findByUsername(string $username): array {
        $sql = 'SELECT * FROM blog_user WHERE username = ?';

        $query = $this->db->prepare($sql);
        $query->execute([$username]);

        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            throw new Exception('blog-db\User not found: ' . $username);

        }

        return ["id" => $user["id"], "username" => $user["username"], "password" => $user["password"]];
    }


    /**
     * Updates the username and password of the user with the given id.
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function update(array $data) {
        $sql = 'UPDATE blog_user SET username = :username, password = :new_password 
                WHERE id = :id';

        $queryParams = [
            ':id' => $data['id'],
            ':username' => $data['username'],
            ':new_password' => $data['new_password'],
            //':old_password' => $data['old_password']
        ];

        $query = $this->db->prepare($sql);
        $success = $query->execute($queryParams);
        $num_rows = $query->rowCount();

        if (!$success || $num_rows < 1) {
            throw new Exception('Failed to update user');
        }
    }


    /**
     * Deletes the user with the given id.
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function deleteById(string $id) {
        $sql = 'DELETE FROM blog_user WHERE id = ?';
        $post_sql = 'DELETE FROM post WHERE user_id = ?';
        $comment_sql = 'DELETE FROM blog_comment JOIN post WHERE post.user_id = ?';

        $this->db->beginTransaction();
        // first delete all the comments on the user's posts
        $comment_query = $this->db->prepare($comment_sql);
        $comment_query->execute($id);
        // then delete all the user's posts
        $post_query = $this->db->prepare($post_sql);
        $post_query->execute($id);

        $query = $this->db->prepare($sql);
        $success = $query->execute([$id]);

        if (!$success) {
            throw new Exception('blog-db\Could not delete user: ' . $id);

        }
        else {
            $this->db->commit();
        }
    }
}
