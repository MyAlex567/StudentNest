<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Helpers\FileStorageHelper;
use App\Models\ClassModel;

/**
 * Handles class-related actions such as creating classes, joining classes,
 * creating posts and activities, submitting activities, and grading submissions.
 *
 * @package App\Controllers
 * @author lisayAlex <202401-00307@dwc-legazpi.edu>
 * @since 2026-05-17
 */
class ClassController{
    /**
     * Class model instance used for database operations.
     *
     * @var ClassModel
     */
    private $model;

    /**
     * File storage helper instance used for storing and deleting uploaded files.
     *
     * @var FileStorageHelper
     */
    private $storage;

    /**
     * Initialize the class controller with the class model and file storage helper.
     *
     * @param ClassModel $model The class model instance.
     */
    public function __construct(ClassModel $model){
        $this->model = $model;
        $this->storage = new FileStorageHelper();
    }

    /**
     * Update whether a student can submit an activity.
     *
     * @param array $details The submission permission details.
     *
     * @return array Returns the update result message.
     */
    public function updateCanSubmit($details): array{
        Validator::clearErrors();

        if(!Validator::validateUsername($details['username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateId($details['activity_id']) || !Validator::validateId($details['post_id'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateCheckbox($details['can_submit'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];            
        }

        $details = [
            'username' => Sanitizer::sanitizeUsername($details['username']),
            'activity_id' => Sanitizer::sanitizeId($details['activity_id']),
            'post_id' => Sanitizer::sanitizeId($details['post_id']),
            'can_submit' => Sanitizer::sanitizeCheckbox($details['can_submit'])
        ];

        $result = $this->model->updateCanSubmit($details);

        if($result){
            return [
                'success' => true,
                'message' => 'Update success'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update'
        ];
    }

    /**
     * Store a new class after validating and sanitizing class information.
     *
     * @param array $data The class information.
     *
     * @return array Returns the class creation result message.
     */
    public function store($data): array{
        Validator::clearErrors();

        $fieldtypes = [
            'class_name' => 'class_name',
            'class_section' => 'class_section',
            'class_room' => 'class_room',
            'class_subject' => 'class_subject'
        ];

        if(!Validator::validateRequired($data, ['class_name'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateClassName($data['class_name'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateClassSection($data['class_section'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateClassRoom($data['class_room'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateClassSubject($data['class_subject'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizeArray($data, $fieldtypes);

        $classID = $this->model->createClass($sanitized);

        if($classID){
            return [
                'success' => true,
                'message' => 'Class Created Successfully'
            ];
        }else{
            return [
                'success' => false,
                'message' => 'Error: failed to create class'
            ];
        }


    }

    /**
     * Join a class using a class code.
     *
     * @param string $classCode The class code to join.
     *
     * @return array Returns the join class result message.
     */
    public function join($classCode): array{
        Validator::clearErrors();

        if(!Validator::validateClassCode($classCode)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizeClassCode($classCode);

        $result = $this->model->joinClass($sanitized);

        if($result){
            return [
                'success' => true,
                'message' => 'You have now Joined the class'
            ];
        }else{
            return [
                'success' => false,
                'message' => 'Error: failed to Join Class'
            ];
        }
    }

    /**
     * Get the role of a user inside a specific class.
     *
     * @param array $userClassInfo The user and class code information.
     *
     * @return array Returns the class role result.
     */
    public function getClassRole($userClassInfo): array{
        Validator::clearErrors();

        if(!Validator::validateUsername($userClassInfo['username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateClassCode($userClassInfo['class_code'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $userInfo['username'] = Sanitizer::sanitizeUsername($userClassInfo['username']);
        $userInfo['class_code'] = Sanitizer::sanitizeClassCode($userClassInfo['class_code']);
    
        $result = $this->model->getClassRole($userInfo['username'], $userClassInfo['class_code']);

        if($result){
            return [
                'success' => true,
                'role' => $result
            ];
        }else{
            return [
                'success' => false
            ];
        }

    }

    /**
     * Get all classes connected to a specific user.
     *
     * @param string $username The username of the user.
     *
     * @return array|false Returns the user classes, validation error, or false from the model.
     */
    public function getUserClasses($username): array|false{
        Validator::clearErrors();

        if(!Validator::validateUsername($username)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizeUsername($username);

        $userClasses = $this->model->getUserClasses($sanitized);
        
        return $userClasses;
    }

    // Get class data by class code
    /**
     * Get class data by class code.
     *
     * @param string $classCode The class code.
     *
     * @return array Returns the class data result.
     */
    public function getClassData($classCode): array{
        Validator::clearErrors();

        if(!Validator::validateClassCode($classCode)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizeClassCode($classCode);

        $classData = $this->model->getClassData($sanitized);

        if($classData){
            return [
                'success' => true,
                'data' => $classData
            ];
        }else{
            return [
                'success' => false,
                'message' => 'Class Not Found'
            ];
        }
    }

    // Select All members of the class
    /**
     * Select all members of a class using the class code.
     *
     * @param string $classCode The class code.
     *
     * @return array Returns the class members result.
     */
    public function selectAllClass($classCode): array{
        Validator::clearErrors();

        if(!Validator::validateClassCode($classCode)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizeClassCode($classCode);

        $classData = $this->model->selectAllClass($sanitized);

        if($classData){
            return [
                'success' => true,
                'class' => $classData
            ];
        }else{
            return [
                'success' => false,
                'message' => 'Class Not Found'
            ];
        }
    }

    /**
     * Create an activity post with optional uploaded activity files.
     *
     * @param array $postInfo The activity post information.
     *
     * @return array Returns the activity creation result message.
     */
    public function createActivity($postInfo): array{
        Validator::clearErrors();

        if(!Validator::validateUsername($postInfo['username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateClassCode($postInfo['class_code'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validatePostType($postInfo['post_type'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];  
        }

        if(!Validator::validateDueDate($postInfo['due_date'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];  
        }

        if(!Validator::validateTitle($postInfo['activity_title'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateDescription($postInfo['activity_description'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $postInfo['username'] = Sanitizer::sanitizeUsername($postInfo['username']);
        $postInfo['class_code'] = Sanitizer::sanitizeClassCode($postInfo['class_code']);

        $folderUpload = [];

        if(!empty($postInfo['activity_file'])){
            $folderUpload = $this->storage->store($_SESSION['userData']['username'], $postInfo['post_type'], $postInfo['activity_file']);
        }

        $uploadActivity = $this->model->storeActivityPost($postInfo, $folderUpload);

        return [
            'success' => true,
            'message' => 'Failed To create Activity' 
        ];

    }

    /**
     * Create a regular class post with optional uploaded files.
     *
     * @param array $postInfo The class post information.
     *
     * @return array Returns the post creation result and upload result.
     */
    public function createPost($postInfo): array{
        Validator::clearErrors();

        if(!Validator::validateClassCode($postInfo['class_code'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateUsername($postInfo['username'])){
            return[
                'success' => false,
                'message' => Validator::getErrors()            
            ];
        }

        if(!Validator::validatePostType($postInfo['post_type'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateTitle($postInfo['post_title'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateDescription($postInfo['post_description'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $postInfo['username'] = Sanitizer::sanitizeUsername($postInfo['username']);
        $postInfo['class_code'] = Sanitizer::sanitizeClassCode($postInfo['class_code']);

        $folderUpload = [];

        if(!empty($postInfo['file'])){
           $folderUpload = $this->storage->store($_SESSION['userData']['username'], $postInfo['post_type'], $postInfo['file']);
        }
        $this->model->storePost($postInfo, $folderUpload); 

        return[
            'success' => true,
            'uploadResult' => $folderUpload
        ];
        
    }

    /**
     * Delete a class post and its related uploaded files.
     *
     * @param int|string $postId The post ID to delete.
     *
     * @return array Returns the delete result message.
     */
    public function deletePost($postId): array{
        Validator::clearErrors();

        if(!Validator::validatePostId($postId)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizePostId($postId);
        $deleteFile = [];

        // Deleting file in folder
        $paths = $this->model->getFilePaths($sanitized);
        $paths2 = $this->model->getSubmissionFile($sanitized);

        if($paths2){
            $this->storage->deleteFile($paths2);
        }

        if($paths){
            $deleteFile = $this->storage->deleteFile($paths);
        }
        $result = $this->model->deletePost($sanitized);
        // Deleting data in database
        if($result['success']){
            return [
                'success' => true,
                'message' => $result['message'] ?? 'Post deleted successfully!',
                'deletedFile' => $deleteFile
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Failed to delete post'
        ];
    }

    /**
     * Get all activity posts for a class.
     *
     * @param string $classCode The class code.
     *
     * @return array Returns the activity posts result.
     */
    public function getActivityPost($classCode): array{
        Validator::clearErrors();

        if(!Validator::validateClassCode($classCode)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizeClassCode($classCode);

        $result = $this->model->getClassActivity($sanitized);

        if($result){
            return [
                'success' => true,
                'class_post' => $result
            ];
        }else{
            return [
                'success' => false,
                'message' => 'No post found'
            ];
        }
    
    }

    /**
     * Get submissions that need to be graded for a specific user.
     *
     * @param string $username The username of the user.
     *
     * @return array Returns the to-be-graded submissions result.
     */
    public function getTobeGraded($username): array{
        Validator::clearErrors();

        if(!Validator::validateUsername($username)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $username = Sanitizer::sanitizeUsername($username);

        $result = $this->model->getTobeGradedAt($username);

        if($result){
            return [
                'success' => true,
                'message' => 'Success Ngani',
                'data' => $result
            ];
        }else{
            return [
                'success' => false,
                'message' => 'Nag fail'
            ];
        }
    }

    /**
     * Get all regular class posts for a class.
     *
     * @param string $classCode The class code.
     *
     * @return array Returns the class posts result.
     */
    public function getPost($classCode): array{
        Validator::clearErrors();

        if(!Validator::validateClassCode($classCode)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizeClassCode($classCode);

        $result = $this->model->getClassPost($sanitized);

        if($result){
            return [
                'success' => true,
                'class_post' => $result
            ];
        }else{
            return [
                'success' => false,
                'message' => 'No post found'
            ];
        }
    }


    /**
     * Get submission file paths by submission ID.
     *
     * @param int|string $subId The submission ID.
     *
     * @return array Returns the submission file paths result.
     */
    public function getSubmissionFilePaths($subId): array{
        Validator::clearErrors();

        if(!Validator::validateId($subId)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $submissionId = Sanitizer::sanitizeId($subId);

        $result = $this->model->getSubmissionFilePath($submissionId);

        if($result){
            return [
                'success' => true,
                'data' => $result
            ];
        }

        return [
            'success' => false
        ];
    }

    /**
     * Submit a grade for a student submission.
     *
     * @param array $grade_details The grade details.
     *
     * @return array Returns the grading result message.
     */
    public function submitGrade($grade_details): array{
        Validator::clearErrors();

        if(!Validator::validateId($grade_details['submission_id'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }        

        if(!Validator::validateGrade($grade_details['grade'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];            
        }

        if(!Validator::validateUsername($grade_details['username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];            
        }

        $grade_details = [
            'username' => Sanitizer::sanitizeUsername($grade_details['username']),
            'grade' => Sanitizer::sanitizeGrade($grade_details['grade']),
            'submission_id' => Sanitizer::sanitizeId($grade_details['submission_id'])
        ];

        $result = $this->model->submitGrade($grade_details);

        if($result){
            return [
                'success' => true,
                'message' => 'Graded Success'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed To graded'
        ];
    }

    /**
     * Get submission data by submission ID.
     *
     * @param int|string $submissionId The submission ID.
     *
     * @return array Returns the submission data result.
     */
    public function getSubmissiondata($submissionId): array{
        Validator::clearErrors();

        if(!Validator::validateId($submissionId)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $submissionId = Sanitizer::sanitizeId($submissionId);

        $getData = $this->model->getSubmissionData($submissionId);

        if($getData){
            return [
                'success' => true,
                'data' => $getData  
            ];
        }

        return [
            'success' => false
        ];
    }

    /**
     * Allows a student to leave a class.
     *
     * @param array $userData Contains username, class_role, and class_code.
     * @return array Result status and message.
     */
    public function leaveClass($userData): array{
        Validator::clearErrors();

        if(!Validator::validateUsername($userData['username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::isStudent($userData['class_role'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateClassCode($userData['class_code'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $userData = [
            'username' => Sanitizer::sanitizeUsername($userData['username']),
            'class_role' => Sanitizer::sanitizeRole($userData['class_role']),
            'class_code' => Sanitizer::sanitizeClassCode($userData['class_code'])
        ];

        $result = $this->model->leaveClass($userData);

        if($result){
            return [
                'success' => true,
                'message' => 'You leave the class'
            ];
        }

        return [
            'success' => false,
            'message' => 'failed to leave'
        ];
    }

    /**
     * Deletes a class by a teacher.
     *
     * @param array $userData Contains username, class_role, and class_code.
     * @return array Result status and message.
     */
    public function deleteClass(array $userData): array{
        Validator::clearErrors();

        if(!Validator::validateUsername($userData['username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::isTeacher($userData['class_role'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateClassCode($userData['class_code'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $userData = [
            'username' => Sanitizer::sanitizeUsername($userData['username']),
            'class_role' => Sanitizer::sanitizeRole($userData['class_role']),
            'class_code' => Sanitizer::sanitizeClassCode($userData['class_code'])
        ];

        $result = $this->model->deleteClass($userData);

        if($result){
            return [
                'success' => true,
                'message' => 'You leave the class'
            ];
        }

        return [
            'success' => false,
            'message' => 'failed to leave'
        ];        
    }


    /**
     * Get submitted activities for a specific user.
     *
     * @param string $username The username of the user.
     *
     * @return array|false Returns submitted activity data, validation errors, or false if no result is found.
     */
    public function getSubmitted($username): array|false{
        Validator::clearErrors();

        if(!Validator::validateUsername($username)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $username = Sanitizer::sanitizeUsername($username);

        $result = $this->model->getSubmitted($username);

        if($result){
            return [
                'success' => true,
                'data' => $result
            ];
        }

        return false;
    }
    
    /**
     * Submit an activity answer with optional uploaded submission files.
     *
     * @param array $submissionData The activity submission data.
     *
     * @return array Returns the activity submission result message.
     */
    public function submitActivity($submissionData): array{
        Validator::clearErrors();

        if(!Validator::validateUsername($submissionData['username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateDescription($submissionData['answer_text'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $submissionData['username'] = Sanitizer::sanitizeUsername($submissionData['username']);

        $due =  $this->model->getDueDate($submissionData['activity_id']);
        $status = '';

        if($due){
            $status = ( strtotime($submissionData['submitted_at']) > strtotime($due['due_date']) ) ? 'late' : 'submitted';
        }

        $folderUpload = [];
        if(!empty($submissionData['submission_file'])){
            $folderUpload = $this->storage->store($submissionData['username'], $submissionData['post_type'], $submissionData['submission_file']);
        }

        $result = $this->model->submission($submissionData, $status, $folderUpload);
        
        if($result){
            return [
                'success' => true,
                'message' => $status === 'late' ? 'Submitted late': 'Submitted Complete'
            ];
        }else{
            return [
                'success' => false,
                'message' => 'Error bOi'
            ];
        }
        
    }
}


?>