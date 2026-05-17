<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Helpers\FileStorageHelper;
use App\Models\UserModel;

/**
 * Handles user registration, user information retrieval, and user validation.
 *
 * @author lisayAlex <202401-00307@dwc-legazpi.edu>
 */
class UserController{
    private $user;

    /**
     * File storage helper instance used for storing and deleting uploaded files.
     *
     * @var FileStorageHelper
     */
    private $storage;    

    /**
     * Creates a UserController instance.
     *
     * @param mixed $user The user model instance.
     */
    public function __construct(UserModel $user){
        $this->user = $user;
        $this->storage = new FileStorageHelper();
    }

    public function deleteAccount(string $username){
        Validator::clearErrors();

        if(!Validator::validateUsername($username)){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }
        $username = Sanitizer::sanitizeUsername($username);

        $this->storage->deleteUserFolder($username);

        $removeAccount = $this->user->deleteAccount($username);

        if($removeAccount){
            return [
                'success' => true
            ];
        }

        return [
            'success' => false
        ];
    }

    /**
     * Updates the user's account and personal information.
     *
     * This method validates the submitted user information, sanitizes the input,
     * and sends the sanitized data to the user model for updating.
     *
     * @param array $input The user information to update.
     *
     * @return array Returns an array containing the update status, message,
     *               and the new username when the update is successful.
     */
    public function updateUserInfo(array $input): array{
        Validator::clearErrors();

        // defined field types for sanitization
        $fieldtypes = [
            'username' => 'username',
            'old_username' => 'old_username',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'sex' => 'sex',
            'birthdate' => 'birthdate'
        ];

        // Validate username
        if(!Validator::validateUsername($input['username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        if(!Validator::validateUsername($input['old_username'])){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        // Validate first and last name
        if(!Validator::validateName($input['first_name'], 'first') || !Validator::validateName($input['last_name'], 'last')){
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }        

        // Validate email
        if (!Validator::validateEmail($input['email'])) {
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }        

        // Validate sex
        if (!Validator::validateSex($input['sex'])) {
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }

        // Validate birthdate
        if (!Validator::validateBirthDate($input['birthdate'])) {
            return [
                'success' => false,
                'errors' => Validator::getErrors()
            ];
        }

        $sanitized = Sanitizer::sanitizeArray($input, $fieldtypes);

        $result = $this->user->updateAccount($sanitized);

        if($result){
            return [
                'success' => true,
                'message' => 'Update info success',
                'newUsername' => $sanitized['username']
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to change'
        ];
    }

    

    /**
     * Validate and sanitize user input for registration.
     *
     * @param array $input The registration input data.
     * @return array Registration result status and data or errors.
     */
    public function validateAndProcessRegistration($input): array{
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
                'message' => Validator::getErrors()
            ];
        }

        // Validate sex
        if (!Validator::validateSex($sanitized['sex'])) {
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        // Validate birthdate
        if (!Validator::validateBirthDate($sanitized['birthdate'])) {
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        // Validate password
        if (!Validator::validatePassword($sanitized['sign_up_password'], $sanitized['confirm_password'])) {
            return [
                'success' => false,
                'message' => Validator::getErrors()
            ];
        }

        // Check if username already exists
        if (!$this->user->check_username_availability($sanitized['sign_up_username'])) {
            return [
                'success' => false,
                'message' => ['Username is already taken']
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
                'message' => ['Failed to create user. Please try again.']
            ];
        }
    }

    /**
     * Gets user information by username.
     *
     * @param mixed $username The username to search for.
     * @return array User query result and user data or error message.
     */
    public function getUserInfo($username): array{
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
     * Validate email only (for API).
     *
     * @param mixed $email The email to validate.
     * @return array Email validation result.
     */

    public static function validateEmailOnly($email): array{
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
     * Validate username only (for API).
     *
     * @param mixed $username The username to validate.
     * @return array Username validation result.
     */
    public static function validateUsernameOnly($username): array {
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