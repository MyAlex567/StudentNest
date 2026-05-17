<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Handles validation for user, class, post, grade, and form data.
 *
 * @author lisayAlex <202401-00307@dwc-legazpi.edu>
 */
class Validator{
    private static $errors = [];

    /**
     * Validate the input grade.
     *
     * @param mixed $grade The grade to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateGrade($grade): bool {
        if($grade === '' || $grade === null) {
            self::$errors[] = "Grade is required.";
            return false;
        }

        if(!is_numeric($grade)) {
            self::$errors[] = "Grade must be a number.";
            return false;
        }

        $grade = (float) $grade;

        if($grade < 0 || $grade > 100) {
            self::$errors[] = "Grade must be between 0 and 100.";
            return false;
        }

        return true;
    }

    /**
     * Validate Post Id.
     *
     * @param mixed $id The post ID to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validatePostId($id): bool {

        if ($id === '' || !ctype_digit($id)) {
            self::$errors[] = "Invalid ID format.";
            return false;
        }

        $id = (int) $id;

        if ($id <= 0) {
            self::$errors[] = "ID must be a positive number.";
            return false;
        }

        return true;
    }

    /**
     * Validate checkbox can submit.
     *
     * @param mixed $value The checkbox value to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateCheckbox($value): bool{
        if($value !== 1 && $value !== 0 && $value !== '1' && $value !== '0'){
            self::$errors[] = "Invalid checkbox value";
            return false;
        }

        return true;
    }    

    /**
     * Validate description.
     *
     * @param mixed $description The description to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateDescription($description): bool{
        if(strlen($description) > 1000){
            self::$errors[] = "Description too long";
            return false;
        }

        if(preg_match("/<script\b/i", $description)) {
            self::$errors[] = "Invalid content detected";
            return false;
        }

        return true;
    }

    /**
     * Validate title.
     *
     * @param mixed $title The title to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateTitle($title): bool{
        if(strlen($title) < 3){
            self::$errors[] = "Title must be at least 3 characters";
            return false;
        }

        if (strlen($title) > 100) {
            self::$errors[] = "Title must not exceed 100 characters";
            return false;
        }

        if (!preg_match("/^[a-zA-Z0-9\s.,'-]+$/", $title)) {
            self::$errors[] = "Title contains invalid characters";
            return false;
        }

        return true;
    }

    /**
     * Validate post type.
     *
     * @param mixed $postType The post type to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validatePostType($postType): bool{
        $allowedTypes = ['post_announcement', 'post_material', 'post_activity'];

        if(!in_array($postType, $allowedTypes)){
            self::$errors[] = 'Error: Invalid Post Type';
            return false;
        }

        return true;
    }

    /**
     * Validate class code.
     *
     * @param mixed $classCode The class code to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateClassCode($classCode): bool{
        if(empty($classCode)){
            self::$errors[] = 'Class Code is required';
            return false;
        }

        if(!preg_match('/^[A-Z0-9]{6,10}$/', $classCode)){
            self::$errors[] = 'Error: Invalid Class Code format';
            return false;
        }

        return true;
    }

    /**
     * Validate class name.
     *
     * @param mixed $classname The class name to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateClassName($classname): bool{
        if(strlen($classname) < 3 || strlen($classname) > 100){
            self::$errors[] = 'Class name must be 3–100 characters';
            return false;
        }

        if (!preg_match("/^[a-zA-Z0-9\s\-&]+$/", $classname)) {
            self::$errors[] = "Class name contains invalid characters";
            return false;
        }

        return true;
    }

    /**
     * Validate class section.
     *
     * @param mixed $section The section to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateClassSection($section): bool{
        if (empty($section)) {
            return true;
        }

        if(strlen($section) > 20){
            self::$errors[] = "Section must not exceed 20 characters";
            return false;
        }

        if (!preg_match("/^[a-zA-Z0-9\s\-]+$/", $section)) {
            self::$errors[] = "Section contains invalid characters";
            return false;
        }

        return true;
    }

    /**
     * Validate class room.
     *
     * @param mixed $room The room to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateClassRoom($room): bool{
        if (empty($room)) {
            return true;
        }

        if(strlen($room) > 50){
            self::$errors[] = "Room must not exceed 50 characters";
            return false;
        }

        if (!preg_match("/^[a-zA-Z0-9\s\-]+$/", $room)) {
            self::$errors[] = "Room contains invalid characters";
            return false;
        }

        return true;
    }

    /**
     * Validate class subject.
     *
     * @param mixed $subject The subject to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateClassSubject($subject): bool{
        if (empty($subject)) {
            return true;
        }

        if(strlen($subject) > 100){
            self::$errors[] = "Subject must not exceed 100 characters";
            return false;
        }

        if (!preg_match("/^[a-zA-Z0-9\s\-]+$/", $subject)) {
            self::$errors[] = "Subject contains invalid characters";
            return false;
        }

        return true;
    }

    /**
     * Validate Submission Id.
     *
     * @param mixed $id The ID to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateId($id): bool
    {
        if (empty($id)) {
            self::$errors[] = "ID is required";
            return false;
        }

        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            self::$errors[] = "ID must be a valid number";
            return false;
        }

        if ((int)$id <= 0) {
            self::$errors[] = "ID must be greater than 0";
            return false;
        }

        return true;
    }

    /**
     * Validate username.
     *
     * @param mixed $name The name to validate.
     * @param mixed $type The type of name.
     * @return bool True if valid, false otherwise.
     */
    public static function validateName($name, $type): bool{
        if(empty($name)){
            self::$errors[] = "{$type} name is required";
            return false;
        }

        if (strlen($name) > 50) {
            self::$errors[] = "{$type} name cannot exceed 50 characters";
            return false;
        }

        // Check for SQL injection patterns
        $sqlPatterns = ['/\bSELECT\b/i', '/\bINSERT\b/i', '/\bUPDATE\b/i', '/\bDELETE\b/i', '/\bDROP\b/i', '/--/', '/;\s*$/'];
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                self::$errors[] = "{$type} contains invalid characters or patterns";
                return false;
            }
        }

        if(!preg_match("/^[a-zA-Z\s\-']{2,50}$/", $name)){
            self::$errors[] = "Invalid {$type} name input";
            return false;
        }

        return true;

    }

    /**
     * Validate username.
     *
     * @param mixed $username The username to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateUsername($username): bool{
        if(empty($username)){
            self::$errors[] = 'Username is required';
            return false;
        }

        if(strlen($username) < 3){
            self::$errors[] = 'Username must be at least 3 characters long';
            return false;
        }

        if(strlen($username) > 50){
            self::$errors[] = "Username cannot exceed 50 characters";
            return false;           
        }

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_.]*$/', $username)) {
            self::$errors[] = "Username must start with a letter and can only contain letters, numbers, underscores and dots";
            return false;
        }

        // Check for SQL injection patterns
        $sqlPatterns = ['/\bSELECT\b/i', '/\bINSERT\b/i', '/\bUPDATE\b/i', '/\bDELETE\b/i', '/\bDROP\b/i', '/--/', '/;\s*$/'];
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $username)) {
                self::$errors[] = "Username contains invalid characters or patterns";
                return false;
            }
        }

        return true;

    }

    /**
     * Validate sex.
     *
     * @param mixed $sex The sex value to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateSex($sex): bool{
        $allowed = ['male', 'female'];

        if(!in_array($sex, $allowed, true)){
            return false;
        }

        return true;
    }

    /**
     * Validate due date.
     *
     * @param mixed $dueDate The due date to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateDueDate($dueDate): bool{
        if (empty($dueDate)) {
            self::$errors[] = 'Due date is required';
            return false;
        }

        // expects: 2026-05-19T15:30
        $date = \DateTime::createFromFormat('Y-m-d\TH:i', $dueDate);

        if (!$date || $date->format('Y-m-d\TH:i') !== $dueDate) {
            self::$errors[] = 'Invalid date format';
            return false;
        }

        if ($date < new \DateTime()) {
            self::$errors[] = 'Due date should not be in the past';
            return false;
        }

        return true;

    }

    /**
     * Validate email.
     *
     * @param mixed $birthdate The birthdate to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateBirthDate($birthdate): bool{
        $date = \DateTime::createFromFormat('Y-m-d', $birthdate);

        if(empty($birthdate)){
            self::$errors[] = 'Birthdate is required';
            return false;
        }

        if($date->format('Y-m-d') !== $birthdate){
            self::$errors[] = 'Invalid date format';
            return false;
        }

        if($date > new \DateTime()){
            self::$errors[] = 'Date should not be in the future';
            return false;
        }

        return true;
    }

    /**
     * Validate email.
     *
     * @param mixed $email The email to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateEmail($email): bool {
        if (empty($email)) {
            self::$errors[] = "Email is required {$email}";
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::$errors[] = "Invalid email format";
            return false;
        }
        
        if (strlen($email) > 100) {
            self::$errors[] = "Email cannot exceed 100 characters";
            return false;
        }
        
        return true;
    }

    /**
     * Validate Role.
     *
     * @param mixed $role The role to validate.
     * @return bool True if role is student, false otherwise.
     */
    public static function isStudent($role): bool {
        if (empty($role)) {
            self::$errors[] = "Role is required";
            return false;
        }

        $allowedRoles = ['student'];

        if (!in_array($role, $allowedRoles, true)) {
            self::$errors[] = "Invalid role selected";
            return false;
        }

        return true;
    }

    /**
     * Validate teacher role.
     *
     * @param mixed $role The role to validate.
     * @return bool True if role is teacher, false otherwise.
     */
    public static function isTeacher($role): bool {
        if (empty($role)) {
            self::$errors[] = "Role is required";
            return false;
        }

        $allowedRoles = ['teacher'];

        if (!in_array($role, $allowedRoles, true)) {
            self::$errors[] = "Invalid role selected";
            return false;
        }

        return true;
    }    
    
    /**
     * Validate password.
     *
     * @param mixed $password The password to validate.
     * @param mixed|null $confirmPassword The confirmation password.
     * @return bool True if valid, false otherwise.
     */
    public static function validatePassword($password, $confirmPassword = null): bool {
        if (empty($password)) {
            self::$errors[] = "Password is required";
            return false;
        }
        
        if (strlen($password) < 8) {
            self::$errors[] = "Password must be at least 8 characters long";
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            self::$errors[] = "Password must contain at least one uppercase letter";
            return false;
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            self::$errors[] = "Password must contain at least one lowercase letter";
            return false;
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            self::$errors[] = "Password must contain at least one number";
            return false;
        }
        
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            self::$errors[] = "Password must contain at least one special character";
            return false;
        }
        
        if ($confirmPassword !== null && $password !== $confirmPassword) {
            self::$errors[] = "Passwords do not match";
            return false;
        }
        
        return true;
    }

    /**
     * Validate required fields.
     *
     * @param array $data The data to validate.
     * @param array $requiredFields The required field names.
     * @return bool True if valid, false otherwise.
     */ 
    public static function validateRequired($data, $requiredFields): bool {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            self::$errors[] = "Required fields missing: " . implode(', ', $missing);
            return false;
        }
        
        return true;
    }

    /**
     * Get all validation errors.
     *
     * @return array List of validation errors.
     */
    public static function getErrors(): array {
        return self::$errors;
    }

    /**
     * Clear errors.
     *
     * @return void
     */
    public static function clearErrors(): void {
        self::$errors = [];
    }

    /**
     * Check if there are any errors.
     *
     * @return bool True if errors exist, false otherwise.
     */
    public static function hasErrors(): bool {
        return !empty(self::$errors);
    }

}

?>