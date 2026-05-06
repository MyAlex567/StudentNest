<?php
namespace App\Models;

class UserModel{
    private $conn;

    // Store database connection when model is created
    public function __construct($database){
        $this->conn = $database->getConnection();
    }

    // Get full user info based on username
    public function getUserinfo($username){
        $sql = "SELECT u.first_name, u.last_name, u.sex, u.birthdate, a.username, a.email, a.created_at
                FROM user u
                JOIN account a ON a.account_id = u.account_id
                WHERE a.username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(["username"=>$username]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Find a user by username (used for login or checking user existence)
    public function findUser($username){
        $sql = "SELECT username, password FROM account WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }

    // Check if username is available (returns true if not found in DB)
    public function check_username_availability($username){
        $sql = "SELECT account_id FROM account WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result === false;
    }

    // Get all accounts ordered by latest first
    public function selectAll(){
        $sql = "SELECT * FROM account ORDER BY id DESC";
        $result = $this->conn->query($sql);
        $users = [];

        if($result){
            while($row = $result->fetch(\PDO::FETCH_ASSOC)){
                $users[] = $row;
            }
        }

        return $users;
    }

    // Insert new account and related user profile (transactional)
    public function insert($data){

        try{
            // Start transaction to ensure both inserts succeed or fail together
            $this->conn->beginTransaction();

            // Insert into account table
            $stmt = $this->conn->prepare("INSERT INTO account (username, email, password) 
                                    VALUES (:username, :email, :password)");
            $stmt->execute([
                'username' => $data['sign_up_username'],
                'email' => $data['email'],
                'password' => $data['sign_up_password']
            ]);

            // Get last inserted account ID
            $last_id = $this->conn->lastInsertId();

            // Insert related user profile data
            $stmt2 = $this->conn->prepare("INSERT INTO user(account_id, first_name, last_name, sex, birthdate) 
                                    VALUES (:account_id, :first_name, :last_name, :sex, :birthdate)");
            $stmt2->execute([
                'account_id' => $last_id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'sex' => $data['sex'],
                'birthdate' => $data['birthdate']
            ]);

            // Commit transaction if both inserts succeed
            $this->conn->commit();

            return $last_id;

        }catch(\PDOException $e){
            // Rollback if anything fails
            if($this->conn->inTransaction()){
                $this->conn->rollBack();
            }

            return false;
        }

    }


}

?>