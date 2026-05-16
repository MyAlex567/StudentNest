<?php
namespace App\Controllers;

use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Helpers\FileStorageHelper;
use App\Models\ClassModel;

class ClassController{
    private $model;
    private $storage;

    public function __construct(ClassModel $model){
        $this->model = $model;
        $this->storage = new FileStorageHelper();
    }

    public function store($data){
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

    public function join($classCode){
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

    public function getClassRole($userClassInfo){
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

    public function getUserClasses($username){
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
    public function getClassData($classCode){
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
    public function selectAllClass($classCode){
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

    public function createActivity($postInfo){
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

    public function createPost($postInfo){
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

    public function deletePost($postId){
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

    public function getActivityPost($classCode){
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

    public function getTobeGraded($username){
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

    public function getPost($classCode){
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
     * Get submission file by id
     */
    public function getSubmissionFilePaths($subId){
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

    public function submitGrade($grade_details){
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

    public function getSubmissiondata($submissionId){
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
    
    public function submitActivity($submissionData){
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