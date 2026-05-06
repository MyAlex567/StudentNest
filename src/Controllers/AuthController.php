<?php
namespace App\Controllers;

use App\Helpers\Sanitizer;
use App\Helpers\Validator;

class AuthController{
    private $model;

    public function __construct($model){
        $this->model = $model;
    }

    public function login($data){

        $fieldTypes = [
            'username' => 'username',
            'password' => 'password'
        ];

        $sanitizedData = Sanitizer::sanitizeArray($data, $fieldTypes);

        if(!Validator::validateRequired($sanitizedData, $fieldTypes)){
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }

        if(!Validator::validateUsername($sanitizedData['username'])){
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }     
        
        // Validate password
        if (!Validator::validatePassword($sanitizedData['password'])) {
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }

        $account = $this->model->findUser($sanitizedData['username']);




        if(!$account){
            return [
                'success' => false,
                'errors' => 'Error: user not found'
            ];
        }elseif(!password_verify($sanitizedData['password'], $account['password'])){
            return [
                'success' => false,
                'errors' => 'Error: Wrong Password'
            ];
        }else{
            unset($account['password']);
            return [
                'success' => true,
                'data' => $account
            ];
        }
    }
}

?>