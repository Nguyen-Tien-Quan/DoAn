
<?php

function getDB() {
    static $conn = null;

    if ($conn === null) {

        $host = 'localhost';
        $dbname = 'qlbthucan';
        $username = 'root';
        $password = '110805';


        try {
            $conn = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $username,
                $password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Lỗi DB: " . $e->getMessage());
        }
    }

    return $conn;
}
