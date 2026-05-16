<?php
namespace App\Models;

use App\Helpers\Database;

class ClassModel{
    private $conn;

    // Store database connection when model is created
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }

    public function storeMaterial($postID){
        $sql = "INSERT INTO material(post_id) VALUES (:post_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'post_id' => $postID
        ]);

        return $this->conn->lastInsertId();
    }

    public function storeActivity($duedate, $postID){
        $sql = "INSERT INTO activity(post_id, due_date) VALUES(:post_id, :due_date)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'post_id' => $postID,
            'due_date' => $duedate
        ]);

        return $this->conn->lastInsertId();
    }

    public function storeActivityPost($postInfo, $filePaths){
        try{
            $this->conn->beginTransaction();

            // get class id
            $sql = "SELECT class_id FROM class WHERE class_code = :class_code";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'class_code' => $postInfo['class_code']
            ]);
            $classId = $stmt->fetch(\PDO::FETCH_ASSOC);

            // get created by references
            $sql2 = "SELECT account_id FROM account WHERE username = :username";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([
                'username' => $postInfo['username']
            ]);
            $createdby = $stmt2->fetch(\PDO::FETCH_ASSOC);

            // Insert into post
            $sql = "INSERT INTO post(class_id, created_by, post_type, title, description)
                    VALUES (:class_id, :created_by, :post_type, :title, :description)";
            $stmt = $this->conn->prepare($sql);

            $stmt->execute([
                'class_id' => $classId['class_id'],
                'created_by' => $createdby['account_id'],
                'post_type' => $postInfo['post_type'] === 'post_activity' ? 'activity' : '',
                'title' => $postInfo['activity_title'],
                'description' => trim($postInfo['activity_description']) === '' ? 'No available Description For this Activity' : $postInfo['activity_description']
            ]);

            // get the last inserted id which is the post
            $lastinsertedId = $this->conn->lastInsertId();

            // decide where to insert the post

            $this->storeActivity($postInfo['due_date'], $lastinsertedId);


            // Loop for insertion in data base
            foreach($filePaths['path'] as $file){

                $sql3 = "INSERT INTO attachment(post_id, file_path, file_name)
                        VALUES (:post_id, :file_path, :file_name)";
                $stmt3 = $this->conn->prepare($sql3);
                $stmt3->execute([
                    "post_id" => $lastinsertedId,
                    'file_path' => $file['file_path'],
                    'file_name' => $file['file_name'],
                ]);
            }   

            $this->conn->commit();

        }catch(\PDOException $error){
            echo $error->getMessage();
            $this->conn->rollBack();
        }
    }

    public function storePost($postInfo, $filePaths){
        try{
            $this->conn->beginTransaction();

            // get class id
            $sql = "SELECT class_id FROM class WHERE class_code = :class_code";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'class_code' => $postInfo['class_code']
            ]);
            $classId = $stmt->fetch(\PDO::FETCH_ASSOC);

            // get created by references
            $sql2 = "SELECT account_id FROM account WHERE username = :username";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([
                'username' => $postInfo['username']
            ]);
            $createdby = $stmt2->fetch(\PDO::FETCH_ASSOC);

            // Insert into post
            $sql = "INSERT INTO post(class_id, created_by, post_type, title, description)
                    VALUES (:class_id, :created_by, :post_type, :title, :description)";
            $stmt = $this->conn->prepare($sql);

            $stmt->execute([
                'class_id' => $classId['class_id'],
                'created_by' => $createdby['account_id'],
                'post_type' => $postInfo['post_type'] === 'post_material' ? 'material' : '',
                'title' => $postInfo['post_title'],
                'description' => $postInfo['post_description']
            ]);

            // get the last inserted id which is the post
            $lastinsertedId = $this->conn->lastInsertId();

            // decide where to insert the post

            $this->storeMaterial($lastinsertedId);


            // Loop for insertion in data base
            foreach($filePaths['path'] as $file){

                $sql3 = "INSERT INTO attachment(post_id, file_path, file_name)
                        VALUES (:post_id, :file_path, :file_name)";
                $stmt3 = $this->conn->prepare($sql3);
                $stmt3->execute([
                    "post_id" => $lastinsertedId,
                    'file_path' => $file['file_path'],
                    'file_name' => $file['file_name'],
                ]);
            }   

            $this->conn->commit();

        }catch(\PDOException $error){
            echo $error->getMessage();
            $this->conn->rollBack();
        }

    }

    public function getSubmissionData($submissionId){
        $sql = "SELECT
                    s.submission_id,
                    s.activity_id,
                    s.submitted_by,
                    s.graded_by,
                    s.answer_text,
                    s.grade,
                    s.status,
                    s.submitted_at,
                    s.graded_at,
                    CONCAT(u.first_name, ' ', u.last_name) AS submitted_by_name
                FROM activity act
                INNER JOIN submission s ON act.activity_id = s.activity_id
                INNER JOIN user u ON u.account_id = s.submitted_by
                WHERE s.submission_id = :submission_id;";
    }

    public function getTobeGradedAt($username){
        $sql = "SELECT
                    s.submission_id,
                    s.activity_id,
                    s.submitted_by,
                    s.graded_by,
                    s.answer_text,
                    s.grade,
                    s.status,
                    s.submitted_at,
                    s.graded_at,
                    p.title,
                    p.description,
                    CONCAT(u.first_name, ' ', u.last_name) AS submitted_by_name
                FROM post p
                INNER JOIN activity act ON act.post_id = p.post_id
                INNER JOIN submission s ON s.activity_id = act.activity_id
                INNER JOIN user u ON u.account_id = s.submitted_by
                WHERE p.created_by = (SELECT account_id FROM account WHERE username = :username)";  
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'username' => $username
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getClassActivity($classCode){
        try{
            $sql = "SELECT
                        p.post_id,
                        act.activity_id,
                        p.created_by,
                        p.post_type,
                        p.post_date,
                        p.title,
                        p.description,
                        act.due_date,
                        CONCAT(u.first_name, ' ', u.last_name) AS author,

                        GROUP_CONCAT(
                            at.file_name
                            SEPARATOR '[[FILE_SEPARATOR]]'
                        ) AS file_name,
                        GROUP_CONCAT(
                            at.file_path
                            SEPARATOR '[[FILE_SEPARATOR]]'
                        ) AS file_paths

                    FROM post p
                    LEFT JOIN attachment at ON at.post_id = p.post_id
                    JOIN user u ON u.account_id = p.created_by
                    JOIN activity act ON act.post_id = p.post_id
                    WHERE p.class_id = (SELECT class_id FROM class WHERE class_code = :class_code)
                    AND p.post_type = 'activity'
                    GROUP BY p.post_id;";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'class_code' => $classCode
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        }catch(\PDOException $error){
            return [
                'error' => $error->getMessage()
            ];
        }
    }

    /**
     * Get all the post and file in the class
     */
    public function getClassPost($classCode){
        try{
            $sql = "SELECT
                        p.post_id,
                        p.created_by, 
                        p.post_type, 
                        p.post_date, 
                        p.title, 
                        p.description, 
                        CONCAT(u.first_name, ' ', u.last_name) AS author, 
                        GROUP_CONCAT(
                            at.file_name 
                            SEPARATOR '[[FILE_SEPARATOR]]'
                        ) AS file_names,

                        GROUP_CONCAT(
                            at.file_path 
                            SEPARATOR '[[FILE_SEPARATOR]]'
                        ) AS file_paths

                    FROM post p
                    LEFT JOIN attachment at ON at.post_id = p.post_id
                    JOIN user u ON u.account_id = p.created_by
                    WHERE p.class_id = (SELECT class_id FROM class WHERE class_code = :class_code)
                    GROUP BY p.post_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'class_code' => $classCode
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }catch(\PDOException $error){
            return [
                'error' => $error->getMessage()
            ];
        }
    }

    // get the role of the current user in class
    public function getClassRole($username, $classCode){
        $sql = "SELECT cu.role FROM class_user cu
                JOIN account a ON a.account_id = cu.account_id
                JOIN class c ON c.class_id = cu.class_id
                WHERE a.username = :username AND c.class_code = :class_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'class_code' => $classCode 
        ]);

        return $stmt->fetchColumn();

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

    public function deletePost($postID){
        try{

            $sql = "DELETE FROM post WHERE post_id = :post_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'post_id' => $postID
            ]);

            // check if anything was deleted
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Post deleted successfully!'
                ];
            }

            return [
                'success' => false,
                'message' => 'Post not found'
            ];

        }catch(\PDOException $error){
            return [
                'success' => false,
                'message' => 'Database error',
                'error' => $error->getMessage()
            ];
        }
    }

    public function getSubmissionFile($postID){
        $sql = "SELECT sf.file_path FROM activity act
                INNER JOIN submission s ON act.activity_id = s.activity_id
                INNER JOIN submission_file sf ON s.submission_id = sf.submission_id
                WHERE act.post_id = :post_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'post_id' => $postID
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getFilePaths($postID){
        try{
            $sql = "SELECT file_path FROM attachment WHERE post_id = :post_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'post_id' => $postID
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }catch(\PDOException $error){
            return false;
        }
    }

    public function getClassData($classCode){
        $sql = "SELECT created_by, class_name, subject, room, section, class_code, created_at FROM class WHERE class_code = :class_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['class_code' => $classCode]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getDueDate($activityId){
        $sql = "SELECT due_date FROM activity WHERE activity_id = :activity_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'activity_id' => $activityId
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function submission($submissionData, $status, $filePaths){
        try{
            $this->conn->beginTransaction();
            $sql = "INSERT INTO submission (activity_id, submitted_by, answer_text, status) VALUES
                    (:activity_id, (SELECT account_id FROM account WHERE username = :username), :answer_text, :status)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                "activity_id" => $submissionData['activity_id'],
                'username' => $submissionData['username'],
                'answer_text' => $submissionData['answer_text'],
                'status' => $status
            ]);            

            $lastinsertedId = $this->conn->lastInsertId();

            // Loop for insertion in data base
            foreach($filePaths['path'] as $file){

                $sql3 = "INSERT INTO submission_file(submission_id, file_path, file_name)
                        VALUES (:submission_id, :file_path, :file_name)";
                $stmt3 = $this->conn->prepare($sql3);
                $stmt3->execute([
                    "submission_id" => $lastinsertedId,
                    'file_path' => $file['file_path'],
                    'file_name' => $file['file_name'],
                ]);
            }   
            $this->conn->commit();
            return $lastinsertedId;
        }catch(\PDOException $error){
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
        
    }
}

?>