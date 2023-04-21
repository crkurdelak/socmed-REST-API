<?php

class Database
{
    function connect(): PDO
    {
        try {
            //$db = new PDO("pgsql:dbname=blog_db;host=localhost;password=314dev;user=dev");
            $db = new PDO("pgsql:dbname=blog_db;host=localhost;password=ckurdelak20;user=ckurdelak20");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            exit('blog-db\database\Database Connection Error! ' . $e->getMessage());
        }

        return $db;
    }
}