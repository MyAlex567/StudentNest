<?php
namespace App\Helpers;

class Sanitizer{

    public static function sanitizePostId($id) {
        return trim((string) $id);
    }

    public static function sanitizeUsername($username){
        if ($username === null) return '';

        // trim whitespaces
        $sanitized = trim($username);

        // Remove html tags
        $sanitized = strip_tags($sanitized);

        // Convert special characters to HTML entities
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');

        // Allow only letters, numbers, underscores, and dots
        $sanitized = preg_replace('/[^a-zA-Z0-9_.]/', '', $sanitized);

        return $sanitized;
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail($email){
        if ($email === null) return '';

        $sanitized = trim($email);
        $sanitized = strip_tags($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        $sanitized = filter_var($sanitized, FILTER_SANITIZE_EMAIL);
        
        return $sanitized;
    }


    /**
     * Sanitize password (minimal sanitization as passwords should be hashed)
     */
    public static function sanitizePassword($password){
        if ($password === null) return '';
        
        // Just trim whitespace, don't modify password content
        return trim($password);
    }

    /**
     * Generic sanitize for string inputs
     */
    public static function sanitizeString($input){
        if ($input === null) return '';
        
        $sanitized = trim($input);
        $sanitized = strip_tags($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        return $sanitized;
    }

    /**
     * Sanitize array of inputs based on field types
     */
    public static function sanitizeArray($data, $fieldtypes = []){
        $sanitized = [];

        foreach($data as $field => $value){
            if(isset($fieldtypes[$field])){
                switch($fieldtypes[$field]){
                    case 'sign_up_username':
                        $sanitized[$field] = self::sanitizeUsername($value);
                        break;
                    case 'username':
                        $sanitized[$field] = self::sanitizeUsername($value);
                        break;
                    case 'email':
                        $sanitized[$field] = self::sanitizeEmail($value);
                        break;
                    case 'sex':
                        $sanitized[$field] = $value;
                        break;
                    case 'birthdate':
                        $sanitized[$field] = $value;
                        break;
                    case 'password':
                        $sanitized[$field] = self::sanitizePassword($value);
                        break;
                    default:
                        $sanitized[$field] = self::sanitizeString($value);
                }
            }else{
                $sanitized[$field] = self::sanitizeString($value);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize Class code
     */
    public static function sanitizeClassCode($code){
        return trim(strtoupper($code));
    }

    /**
     * Sanitize Grade
     */
    public static function sanitizeGrade($grade) {
        $grade = trim($grade);
        return (float) $grade;
    }

    /**
     * Sanitize Id
     */
    public static function sanitizeId($id)
    {
        return (int) $id;
    }    

}

?>