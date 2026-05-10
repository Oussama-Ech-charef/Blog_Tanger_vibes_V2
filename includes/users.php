<?php


class User {


private $conn;


public function __construct($db){
    $this->conn = $db;
    }



public function register($full_name, $email, $password){
    $query = "select id from users where email = :email limit 1";

    $stmt = $this->conn->prepare($query);
    $stmt->execute([
        ':email' => $email
    ]);

    if ($stmt->fetch()) {
        return ['status' => false, 'message' => 'Email already exists.'];
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $query = "insert into users (full_name, email, password) values (:name, :email, :password)";
    $stmt = $this->conn->prepare($query);
   

    $stmt->execute([
        ':name' => $full_name,
        ':email' => $email,
        ':password' => $hashed
    ]);

    if ($stmt) {
         return ['status' => true, 'message' => 'Registration successful!'];
    }

        return ['status' => false, 'message' => 'Something went wrong.'];
}




}