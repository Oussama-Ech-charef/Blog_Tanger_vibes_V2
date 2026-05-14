<?php


class Post {
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }


    public function getHomePosts(){
        $query = "select p.*
        from posts p
        where p.status = 'published'
        order by p.created_at desc
        limit 3";


        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function detailById($id) {
        $query = " 
        select p.*
        from posts p
        where p.id = :id and p.status = 'published'
        limit 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':id' => $id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

       public function allposts() {
        $sql = "select p.* from posts p
                where p.status = 'published'
                order by p.created_at desc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
  





}
