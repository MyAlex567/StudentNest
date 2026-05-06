<?php
namespace App\Models;

class ClassModel{
    private $conn;

    // Store database connection when model is created
    public function __construct($database){
        $this->conn = $database->getConnection();
    }

    public function storePost($postInfo){
        
    }

    public function selectAllClass($classCode){
        $sql = "SELECT CONCAT(u.first_name, ' ', u.last_name) as full_name, cu.role FROM class_user cu
                JOIN user u ON u.account_id = cu.account_id
                JOIN class c ON c.class_id = cu.class_id
                WHERE c.class_code = :class_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'class_code' => $classCode
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Generate a random shit classCode
    private function generateClassCode(){

        // check if the generated code is already exist in the class table
        do{

            // this will generate random shit
            $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

            $sql = "SELECT 1 FROM class WHERE class_code = :class_code";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'class_code' => $code
            ]);

            $exists = $stmt->fetch(\PDO::FETCH_ASSOC);

        }while($exists);

        // return the code after confirming it's unique
        return $code;
    }

    // Insert new class and related class_user (transactional)
    public function createClass($classData){
        try{
            // Start transaction to ensure both inserts succeed or fail together
            $this->conn->beginTransaction(); 

            // Insert into class table
            $stmt = $this->conn->prepare("INSERT INTO class(created_by, class_name, subject, room, section, class_code) 
                                        VALUES ((SELECT account_id FROM account WHERE username = :username), :class_name, :class_subject, :class_room, :class_section, :class_code)");
            $stmt->execute([
                'username' => $_SESSION['userData']['username'],
                'class_name' => $classData['class_name'],
                'class_subject' => $classData['class_subject'],
                'class_room' => $classData['class_room'],
                'class_section' => $classData['class_section'],
                'class_code' => $this->generateClassCode()
            ]);

            // Get last inserted class ID
            $last_id = $this->conn->lastInsertId();

            $stmt2 = $this->conn->prepare("INSERT INTO class_user(class_id, account_id, role) 
                                        VALUES(:class_id, (SELECT account_id FROM account WHERE username = :username), :role)");
            $stmt2->execute([
                'class_id' => $last_id,
                'username' => $_SESSION['userData']['username'],
                'role' => 'teacher'
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

    public function isAlreadyJoined($username, $Code){
        try{

            $sql = "SELECT 1 FROM class_user cu
                    JOIN class c ON c.class_id = cu.class_id
                    JOIN account a ON a.account_id = cu.account_id
                    WHERE c.class_code = :class_code
                    AND a.username = :username";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'class_code' => $Code,
                'username' => $username
            ]);

            return $stmt->fetch(\PDO::FETCH_ASSOC) ? true : false;

        }catch(\PDOException $e){
            return false;
        }
    }

    public function joinClass($Code){

        try{
            $sql = "SELECT class_id FROM class WHERE class_code = :class_code";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['class_code' => $Code]);

            $classID = $stmt->fetch(\PDO::FETCH_ASSOC);

            if(!$classID){
                return false;
            }

            if($this->isAlreadyJoined($_SESSION['userData']['username'], $Code)){
                return false;
            }

            $stmt2 = $this->conn->prepare("INSERT INTO class_user(class_id, account_id, role) 
                                        VALUES(:class_id, (SELECT account_id FROM account WHERE username = :username), :role)");
            $stmt2->execute([
                'class_id' => $classID['class_id'],
                'username' => $_SESSION['userData']['username'],
                'role' => 'student'
            ]);

            return true;
        }catch(\PDOException $e){
            return false;
        }
    }

    // use username of the user to get the current class of the user
    public function getUserClasses(string $username){
        $sql = "
            SELECT CONCAT(creator.first_name, ' ', creator.last_name) AS creator, c.class_name, c.subject, c.room, c.section, c.class_code FROM class c
            JOIN class_user cu ON cu.class_id = c.class_id
            JOIN user creator ON creator.account_id = c.created_by
            WHERE cu.account_id = (SELECT account_id FROM account WHERE username = :username);
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getClassData($classCode){
        $sql = "SELECT created_by, class_name, subject, room, section, class_code, created_at FROM class WHERE class_code = :class_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['class_code' => $classCode]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}

?>