<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Sanitizer;
use App\Helpers\Validator;

/**
 * Handles user authentication processes.
 *
 * This controller is responsible for validating login input,
 * sanitizing user credentials, checking the account record,
 * and verifying the user's password.
 *
 * @package App\Controllers
 */
class AuthController{
    private $model;

    /**
     * Creates an AuthController instance.
     *
     * @param mixed $model The model used for authentication queries.
     *
     * @return void
     */
    public function __construct($model){
        $this->model = $model;
    }

    /**
     * Processes user login.
     *
     * This method sanitizes and validates the submitted login data,
     * checks if the user exists, verifies the password, and returns
     * the login result.
     *
     * @param mixed $data The submitted login data.
     *
     * @return array Returns an array containing the login status,
     *               user data if successful, or errors if failed.
     */
    public function login($data): array{

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
                'errors' => ['Error: user not found']
            ];
        }elseif(!password_verify($sanitizedData['password'], $account['password'])){
            return [
                'success' => false,
                'errors' => ['Error: Wrong Password']
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