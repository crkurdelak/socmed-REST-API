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
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function findById(array $data): array {
        $sql = 'SELECT * FROM blog_user WHERE id = ?';

        if ($data["id"] == $data["session_userid"]) {

            $query = $this->db->prepare($sql);
            $query->execute([$data["id"]]);

            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user === false) {
                throw new Exception('blog-db\User not found: ' . $data["id"]);

            }

            return ["id" => $user["id"], "username" => $user["username"], "password" => $user["password"]];
        }
        else {
            throw new Exception('Can\'t access that user. your id: '.$data["session_userid"].'
             this user\'s id:'.$data["id"]);
        }
    }


    /**
     * Finds the user with the given username.
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function findByUsername(array $data): array {
        $id_array = $this->getId($data["username"]);

        if (!$id_array || $id_array["id"] === null) {
            throw new Exception('blog-db\User not found: ' . $data["id"]);

        }
        return $this->findById(["id" => $id_array["id"], "session_userid" => $data["session_userid"]]);
    }

    /**
     * Gets the id of the user with the given username.
     * @param string $username the user's username
     * @return array an array containing the user's id
     * @throws Exception
     */
    public function getId(string $username): array {
        $sql = 'SELECT id FROM blog_user WHERE username = ?';

        $query = $this->db->prepare($sql);
        $query->execute([$username]);

        $id_array = $query->fetch(PDO::FETCH_ASSOC);
        $num_rows = $query->rowCount();

        if ($id_array === false || $num_rows < 1 || $id_array["id"] === null) {
            throw new Exception('blog-db\User not found: ' . $username);
        }
        else {
            return ["id" => $id_array["id"]];
        }
    }

    /**
     * Returns the password of the user with the given id
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function getPw(string $id): array
    {
        $sql = 'SELECT password FROM blog_user WHERE id = ?';
        $query = $this->db->prepare($sql);
        $query->execute([$id]);

        $pw_array = $query->fetch(PDO::FETCH_ASSOC);

        if ($pw_array === false || $pw_array["password"] === null) {
            throw new Exception('blog-db\Password not found: ' . $id);

        }

        return ["password" => $pw_array["password"]];
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

        $password = (new User)->getPw($data["session_userid"])["password"];
        echo $password."\n";
        echo $data["old_password"];
        // TODO why are the hashes different

        if (password_verify($password, $data["old_password"])) {
            $queryParams = [
                ':id' => $data['session_userid'],
                ':username' => $data['username'],
                ':new_password' => $data['new_password'],
            ];

            $query = $this->db->prepare($sql);
            $success = $query->execute($queryParams);
            $num_rows = $query->rowCount();

            if (!$success || $num_rows < 1) {
                throw new Exception('Failed to update user');
            }
        }
        else {
            throw new Exception('Incorrect password for user '.$data["session_userid"]);
        }
    }


    /**
     * Deletes the user with the given id.
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function deleteById(array $data) {
        $sql = 'DELETE FROM blog_user WHERE id = ?';
        $post_sql = 'DELETE FROM post WHERE user_id = ?';
        $comment_sql = 'DELETE from blog_comment WHERE post_id IN (SELECT id FROM post WHERE post.user_id = ?)';

        $this->db->beginTransaction();
        // first delete all the comments on the user's posts
        $comment_query = $this->db->prepare($comment_sql);
        $comment_query->execute([$data["session_userid"]]);
        // then delete all the user's posts
        $post_query = $this->db->prepare($post_sql);
        $post_query->execute([$data["session_userid"]]);

        $query = $this->db->prepare($sql);
        $success = $query->execute([$data["session_userid"]]);

        if (!$success) {
            throw new Exception('blog-db\Could not delete user: ' . $data["session_userid"]);

        } else {
            $this->db->commit();
        }
    }
}
