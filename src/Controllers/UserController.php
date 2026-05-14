<?php
namespace App\Controllers;

use App\Helpers\Sanitizer;
use App\Helpers\Validator;

class UserController{
    private $user;

    public function __construct($user){
        $this->user = $user;
    }

    /**
     * Validate and sanitize user input for registration
     */
    public function validateAndProcessRegistration($input){
        Validator::clearErrors();

        // defined field types for sanitization
        $fieldtypes = [
            'sign_up_username' => 'sign_up_username',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'sex' => 'sex',
            'birthdate' => 'birthdate',
            'sign_up_password' => 'sign_up_password',
            'confirm_password' => 'confirm_password'
        ];

        // Sanitize all inputs
        $sanitized = Sanitizer::sanitizeArray($input, $fieldtypes);

        // Validate required fields
        $requiredFields = ['sign_up_username', 'first_name', 'last_name', 'email', 'birthdate', 'sign_up_password', 'confirm_password'];

        if(!Validator::validateRequired($sanitized, $requiredFields)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        // Validate username
        if(!Validator::validateUsername($sanitized['sign_up_username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        // Validate first and last name
        if(!Validator::validateName($sanitized['first_name'], 'first') || !Validator::validateName($sanitized['last_name'], 'last')){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        // Validate email
        if (!Validator::validateEmail($sanitized['email'])) {
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }

        // Validate sex
        if (!Validator::validateSex($sanitized['sex'])) {
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }

        // Validate birthdate
        if (!Validator::validateBirthDate($sanitized['birthdate'])) {
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }

        // Validate password
        if (!Validator::validatePassword($sanitized['sign_up_password'], $sanitized['confirm_password'])) {
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }

        // Check if username already exists
        if (!$this->user->check_username_availability($sanitized['sign_up_username'])) {
            return [
                'success' => false,
                'errors' => ['Username is already taken']
            ];
        }

        // Hash password before storing
        $sanitized['sign_up_password'] = password_hash($sanitized['sign_up_password'], PASSWORD_DEFAULT);

        // Remove confirm_pass from data to be inserted
        unset($sanitized['confirm_password']);

        $userId = $this->user->insert($sanitized);

        if($userId){
            return [
                'success' => true,
                'data' => [
                    'id' => $userId,
                    'username' => $sanitized['sign_up_username'],
                    'email' => $sanitized['email']
                ]
            ];
        } else {
            return [
                'success' => false,
                'errors' => ['Failed to create user. Please try again.']
            ];
        }
    }

    public function getUserInfo($username){
        if($username === null){
            return [
                'query_result' => false,
                'error' => 'error: no user found'
            ];
        }

        $user = $this->user->getUserInfo($username);

        if($user === false){
            return [
                'query_result' => false,
                'error' => 'error: no user found'
            ];
        }

        return [
            'query_result' => true,
            'data' => $user
        ];
    }

    /**
     * Validate email only (for API)
     */

    public static function validateEmailOnly($email){
        Validator::clearErrors();

        // Sanitize email
        $sanitized = Sanitizer::sanitizeEmail($email);

        if(!Validator::validateEmail($sanitized)){
            return [
                'valid' => false,
                'email' => $sanitized,
                'errors' => Validator::getErrors()
            ];
        }

        return [
            'valid' => true,
            'email' => $sanitized,
            'errors' => []
        ];
    }

    /**
     * Validate username only (for API)
     */
    public static function validateUsernameOnly($username) {
        Validator::clearErrors();
        
        // Sanitize username
        $sanitized = Sanitizer::sanitizeUsername($username);
        
        // Validate username
        if (!Validator::validateUsername($sanitized)) {
            return [
                'valid' => false,
                'username' => $sanitized,
                'errors' => Validator::getErrors()
            ];
        }
        
        return [
            'valid' => true,
            'username' => $sanitized,
            'errors' => []
        ];
    }


}



?>