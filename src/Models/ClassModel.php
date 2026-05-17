<?php
declare(strict_types=1);

/**
 * ClassModel file.
 *
 * Handles database operations for classes, posts, announcements, materials,
 * activities, submissions, grading, and related file paths.
 *
 * @package App\Models
 * @author lisayAlex <202401-00307@dwc-legazpi.edu>
 * @since 2026-05-17
 */

namespace App\Models;

use App\Helpers\Database;

/**
 * Handles class-related database queries and transactions.
 *
 * This model manages class creation, joining, posts, activities,
 * announcements, materials, submissions, grading, and uploaded file paths.
 *
 * @package App\Models
 * @author lisayAlex <202401-00307@dwc-legazpi.edu>
 * @since 2026-05-17
 */
class ClassModel{
    /**
     * Database connection instance.
     *
     * @var \PDO
     */
    private $conn;

    // Store database connection when model is created
    /**
     * Create a new ClassModel instance.
     *
     * Stores the database connection from the provided Database helper.
     *
     * @param Database $database Database helper instance.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }

    /**
     * Store an announcement record linked to a post.
     *
     * @param mixed $postID ID of the post connected to the announcement.
     * @return string|false Last inserted announcement ID, or false on failure.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function storeAnnouncement($postID): string|false{
        $sql = "INSERT INTO announcement(post_id) VALUES (:post_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'post_id' => $postID
        ]);

        return $this->conn->lastInsertId();
    }    

    /**
     * Update if students can still submit an activity.
     *
     * @param array $details Activity submit status details.
     * @return bool True if updated, false otherwise.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function updateCanSubmit(array $details): bool
    {
        try{
            $sql = "SELECT 1 FROM post WHERE created_by = (SELECT account_id FROM account WHERE username = :username) AND post_id = :post_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'username' => $details['username'],
                'post_id' => $details['post_id']
            ]);

            $result = $stmt->fetchColumn();

            if($result){
                $sql = "UPDATE activity SET can_submit = :can_submit WHERE activity_id = :activity_id";
                $stmt = $this->conn->prepare($sql);
                $result = $stmt->execute([
                    'activity_id' => $details['activity_id'],
                    'can_submit' => $details['can_submit']
                ]);

                if($result){
                    return true;
                }
                return false;
            }
            return false;
        }catch(\PDOException){
            return false;
        }
    }

    /**
     * Store a material record linked to a post.
     *
     * @param mixed $postID ID of the post connected to the materia
     * @return string|false Last inserted material ID, or false on failure.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function storeMaterial($postID): string|false{
        $sql = "INSERT INTO material(post_id) VALUES (:post_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'post_id' => $postID
        ]);

        return $this->conn->lastInsertId();
    }

    /**
     * Store an activity record linked to a post.
     *
     * @param mixed $duedate Due date of the activity.
     * @param mixed $postID ID of the post connected to the activity.
     * @return string|false Last inserted activity ID, or false on failure.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function storeActivity($duedate, $postID): string|false{
        $sql = "INSERT INTO activity(post_id, due_date) VALUES(:post_id, :due_date)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'post_id' => $postID,
            'due_date' => $duedate
        ]);

        return $this->conn->lastInsertId();
    }

    /**
     * Store a new activity post with attachments.
     *
     * Creates a post, stores its activity details, and saves uploaded file paths
     * inside a database transaction.
     *
     * @param array $postInfo Activity post information.
     * @param array $filePaths Uploaded file path information.
     * @return void
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function storeActivityPost($postInfo, $filePaths): void{
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

    /**
     * Store a new class post with attachments.
     *
     * Creates either a material or announcement post and saves uploaded file paths
     * inside a database transaction.
     *
     * @param array $postInfo Post information.
     * @param array $filePaths Uploaded file path information.
     * @return void
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function storePost($postInfo, $filePaths): void{
        try{
            $this->conn->beginTransaction();

            if($postInfo['post_type'] === 'post_material'){
                $postInfo['post_type'] = 'material';
            }elseif($postInfo['post_type'] === 'post_announcement'){
                $postInfo['post_type'] = 'announcement';
            }

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
                'post_type' => $postInfo['post_type'],
                'title' => $postInfo['post_title'],
                'description' => $postInfo['post_description']
            ]);

            // get the last inserted id which is the post
            $lastinsertedId = $this->conn->lastInsertId();

            // decide where to insert the post

            $postInfo['post_type'] === 'material' ? $this->storeMaterial($lastinsertedId) : $this->storeAnnouncement($lastinsertedId);


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

    /**
     * Get submission details by submission ID.
     *
     * @param mixed $submissionId ID of the submission to retrieve.
     * @return array|false Submission details, or false on failure.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getSubmissionData($submissionId): array|false{
        try{
            $sql = "SELECT
                        s.submission_id,
                        s.activity_id,
                        s.submitted_by,
                        s.graded_by,
                        s.answer_text,
                        s.grade,
                        p.title,
                        s.status,
                        s.submitted_at,
                        s.graded_at,
                        CONCAT(u.first_name, ' ', u.last_name) AS submitted_by_name
                    FROM activity act
                    INNER JOIN submission s ON act.activity_id = s.activity_id
                    INNER JOIN post p ON p.post_id = act.post_id
                    INNER JOIN user u ON u.account_id = s.submitted_by
                    WHERE s.submission_id = :submission_id;";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'submission_id' => $submissionId
            ]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }catch(\PDOException $error){
            return false;
        }
    }

    /**
     * Get submissions that need grading for a teacher.
     *
     * @param mixed $username Username of the teacher.
     * @return array List of submissions to be graded.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getTobeGradedAt($username): array{
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

    /**
     * Get all activity posts for a class.
     *
     * @param mixed $classCode Class code used to find activities.
     * @return array List of class activities or error information.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getClassActivity($classCode): array{
        try{
            $sql = "SELECT
                        p.post_id,
                        act.activity_id,
                        act.can_submit,
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
    /**
     * Get all posts and attached files in a class.
     *
     * @param mixed $classCode Class code used to find posts.
     * @return array List of class posts or error information.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getClassPost($classCode): array{
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
    /**
     * Get the current user's role in a class.
     *
     * @param mixed $username Username of the current user.
     * @param mixed $classCode Class code to check.
     * @return mixed User role in the class.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getClassRole($username, $classCode): mixed{
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

    /**
     * Get all users enrolled in a class.
     *
     * @param mixed $classCode Class code to search.
     * @return array List of users and their roles.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function selectAllClass($classCode): array{
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
    /**
     * Generate a unique class code.
     *
     * @return string Unique generated class code.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    private function generateClassCode(): string{

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
    /**
     * Create a new class and assign the creator as teacher.
     *
     * Inserts records into the class and class_user tables inside a transaction.
     *
     * @param array $classData Class information.
     * @return string|false Last inserted class ID, or false on failure.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function createClass($classData): string|false{
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

    /**
     * Check if a user is already joined in a class.
     *
     * @param mixed $username Username of the user.
     * @param mixed $Code Class code to check.
     * @return bool True if already joined, otherwise false.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function isAlreadyJoined($username, $Code): bool{
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

    /**
     * Join the current user to a class as a student.
     *
     * @param mixed $Code Class code to join.
     * @return bool True if joining succeeds, otherwise false.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function joinClass($Code): bool{

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
    /**
     * Get all classes joined by a user.
     *
     * @param string $username Username of the user.
     * @return array List of classes joined by the user.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getUserClasses(string $username): array{
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

    /**
     * Delete a post by post ID.
     *
     * @param mixed $postID ID of the post to delete.
     * @return array Result message of the delete operation.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function deletePost($postID): array{
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

    /**
     * Use Submission Id to get the file paths
     */
    /**
     * Get submitted file paths by submission ID.
     *
     * @param mixed $subId ID of the submission.
     * @return array|false List of submitted files, or false on failure.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getSubmissionFilePath($subId): array|false{
        try{
            $sql = "SELECT file_name, file_path FROM submission_file WHERE submission_id = :submission_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'submission_id' =>  $subId
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }catch(\PDOException $error){
            return false;
        }
    }

    /**
     * Get submitted file paths for an activity post.
     *
     * @param mixed $postID ID of the activity post.
     * @return array List of submitted file paths.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getSubmissionFile($postID): array{
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

    /**
     * Get attachment file paths by post ID.
     *
     * @param mixed $postID ID of the post.
     * @return array|false List of attachment paths, or false on failure.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getFilePaths($postID): array|false{
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

    /**
     * Submit a grade for a student submission.
     *
     * Updates grade details, grading teacher, graded timestamp, and status.
     *
     * @param array $grade_details Grade details including username, grade, and submission ID.
     * @return bool True if a row was updated, otherwise false.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function submitGrade($grade_details): bool{
        try{
            $sql = "UPDATE submission
                    SET
                        graded_by = (SELECT account_id FROM account WHERE username = :username),
                        grade = :grade,
                        graded_at = NOW(),
                        status = 'graded'
                    WHERE submission_id = :submission_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'username' => $grade_details['username'],
                'grade'=> $grade_details['grade'],
                'submission_id' => $grade_details['submission_id']
            ]);
            return $stmt->rowCount() > 0;
        }catch(\PDOException){
            return false;
        }

    }

    /**
     * Get class data by class code.
     *
     * @param mixed $classCode Class code to search.
     * @return array|false Class data, or false if not found.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getClassData($classCode): array|false{
        $sql = "SELECT created_by, class_name, subject, room, section, class_code, created_at FROM class WHERE class_code = :class_code";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['class_code' => $classCode]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get the due date of an activity.
     *
     * @param mixed $activityId ID of the activity.
     * @return array|false Activity due date, or false if not found.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function getDueDate($activityId): array|false{
        $sql = "SELECT due_date FROM activity WHERE activity_id = :activity_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'activity_id' => $activityId
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Store a student submission and submitted files.
     *
     * Creates a submission record and saves its uploaded files inside a transaction.
     *
     * @param array $submissionData Submission information.
     * @param mixed $status Submission status.
     * @param array $filePaths Uploaded submission file path information.
     * @return string|false Last inserted submission ID, or false on failure.
     *
     * @author lisayAlex <202401-00307@dwc-legazpi.edu>
     * @since 2026-05-17
     */
    public function submission($submissionData, $status, $filePaths): string|false{
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

    /**
     * Get all submissions made by a specific user.
     *
     * This method retrieves all records from the `submission` table
     * where the `submitted_by` field matches the account ID of the
     * given username.
     *
     * @param string $username The username of the account whose submissions will be retrieved.
     *
     * @return array|false Returns an array of submissions if successful, or false if a database error occurs.
     */
    public function getSubmitted(string $username): array|false
    {
        try {
            $sql = "SELECT
                        p.title,
                        s.*
                    FROM submission s
                    INNER JOIN activity a ON s.activity_id = a.activity_id
                    INNER JOIN post p ON p.post_id = a.post_id
                    WHERE s.submitted_by = (SELECT account_id FROM account WHERE username = :username)";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'username' => $username
            ]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException) {
            return false;
        }
    }

    /**
     * Removes a user from a class.
     *
     * @param array $userData Contains class_code and username.
     * @return bool True if the user left the class, false otherwise.
     */
    public function leaveClass(array $userData): bool{
        try{
            $sql = "DELETE FROM class_user 
                    WHERE class_id = (SELECT class_id FROM class WHERE class_code = :class_code)
                    AND account_id = (SELECT account_id FROM account WHERE username = :username)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'class_code' => $userData['class_code'],
                'username' => $userData['username']
            ]);

            return $stmt->rowCount() > 0;
        }catch(\PDOException){
            return false;
        }
    }

    /**
     * Deletes a class created by a user.
     *
     * @param array $userData Contains class_code and username.
     * @return bool True if the class was deleted, false otherwise.
     */
    public function deleteClass(array $userData): bool{
        try{
            $sql = "DELETE FROM class
                    WHERE class_code = :class_code
                    AND created_by = (SELECT account_id FROM account WHERE username = :username)";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'class_code' => $userData['class_code'],
                'username' => $userData['username']
            ]);

            return $stmt->rowCount() > 0;

        }catch(\PDOException){
            return false;
        }
    }
}

?>