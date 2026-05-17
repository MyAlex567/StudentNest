<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Handles input sanitization for user, class, post, and other form data.
 *
 * @author lisayAlex <202401-00307@dwc-legazpi.edu>
 */
class Sanitizer{

    /**
     * Sanitizes a class role.
     *
     * @param mixed $role The role to sanitize.
     * @return string Sanitized role.
     */
    public static function sanitizeRole($role): string {
        return strtolower(trim($role));
    }

    /**
     * Sanitizes checkbox value.
     *
     * @param mixed $value The checkbox value.
     * @return int Returns 1 if checked, otherwise 0.
     */
    public static function sanitizeCheckbox($value): int{
        return ($value == 1 || $value === '1') ? 1 : 0;
    }

    /**
     * Sanitizes post ID.
     *
     * @param mixed $id The post ID.
     * @return string Sanitized post ID.
     */
    public static function sanitizePostId($id): string {
        return trim((string) $id);
    }

    /**
     * Sanitizes username.
     *
     * @param mixed $username The username to sanitize.
     * @return string Sanitized username.
     */
    public static function sanitizeUsername($username): string{
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
     *
     * @param mixed $email The email to sanitize.
     * @return string Sanitized email.
     */
    public static function sanitizeEmail($email): string{
        if ($email === null) return '';

        $sanitized = trim($email);
        $sanitized = strip_tags($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        $sanitized = filter_var($sanitized, FILTER_SANITIZE_EMAIL);
        
        return $sanitized;
    }


    /**
     * Sanitize password (minimal sanitization as passwords should be hashed)
     *
     * @param mixed $password The password to sanitize.
     * @return string Sanitized password.
     */
    public static function sanitizePassword($password): string{
        if ($password === null) return '';
        
        // Just trim whitespace, don't modify password content
        return trim($password);
    }

    /**
     * Generic sanitize for string inputs
     *
     * @param mixed $input The string input to sanitize.
     * @return string Sanitized string.
     */
    public static function sanitizeString($input): string{
        if ($input === null) return '';
        
        $sanitized = trim($input);
        $sanitized = strip_tags($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        return $sanitized;
    }

    /**
     * Sanitize array of inputs based on field types
     *
     * @param array $data The array data to sanitize.
     * @param array $fieldtypes The field type rules.
     * @return array Sanitized array data.
     */
    public static function sanitizeArray($data, $fieldtypes = []): array{
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
                    case 'old_username':
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
     *
     * @param mixed $code The class code to sanitize.
     * @return string Sanitized class code.
     */
    public static function sanitizeClassCode($code): string{
        return trim(strtoupper($code));
    }

    /**
     * Sanitize Grade
     *
     * @param mixed $grade The grade to sanitize.
     * @return float Sanitized grade.
     */
    public static function sanitizeGrade($grade): float {
        $grade = trim($grade);
        return (float) $grade;
    }

    /**
     * Sanitize Id
     *
     * @param mixed $id The ID to sanitize.
     * @return int Sanitized ID.
     */
    public static function sanitizeId($id): int
    {
        return (int) $id;
    }    

}

?>