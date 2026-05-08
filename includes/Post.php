<?php


class Post {
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }


    public function getHomePosts(){
        $query = "select p.*, c.name as cat_name
        from posts p
        join categories c on p.category_id = c.id
        where p.status = 'published'
        order by p.created_at desc
        limit 3";


        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function detailById($id) {
        $query = " 
        select p.*, c.name as cat_name
        from posts p
        join categories c on p.category_id = c.id
        where p.id = :id and p.status = 'published'
        limit 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':id' => $id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCategories() {
        $query = "select * from categories order by name asc";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
  





}