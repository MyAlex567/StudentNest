<?php
namespace App\Controllers;

use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Helpers\FileStorageHelper;

class ClassController{
    private $model;
    private $storage;

    public function __construct($model){
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

    public function createPost($postInfo){
        Validator::clearErrors();

        if(!Validator::validatePostType($postInfo['post_type'])){
            echo'1';
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateTitle($postInfo['post_title'])){
            echo '2';
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateDescription($postInfo['post_description'])){
            echo '3';
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        $folderUpload = $this->storage->store($_SESSION['userData']['username'], $postInfo['post_type'], $postInfo['file']);

        var_dump($folderUpload);
        
    }
}


?>