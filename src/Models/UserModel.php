<?php
declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

/**
 * Handles database operations related to user accounts and user profile information.
 *
 * This model is responsible for retrieving user information, checking account
 * availability, inserting new users, selecting all accounts, and updating account details.
 *
 * @package App\Models
 */
class UserModel{
    private $conn;

    /**
     * Store database connection when model is created.
     *
     * @param Database $database The database helper instance.
     *
     * @return void
     */
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }

    /**
     * Gets full user information based on username.
     *
     * @param mixed $username The username used to find the user.
     *
     * @return array|false Returns user information as an associative array if found, otherwise false.
     */
    public function getUserinfo($username): array|false{
        $sql = "SELECT u.first_name, u.last_name, u.sex, u.birthdate, a.username, a.email, a.created_at
                FROM user u
                JOIN account a ON a.account_id = u.account_id
                WHERE a.username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(["username"=>$username]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Finds a user account by username.
     *
     * This method is commonly used for login or checking if a user exists.
     *
     * @param mixed $username The username to search for.
     *
     * @return array|false Returns the account data as an associative array if found, otherwise false.
     */
    public function findUser($username): array|false{
        $sql = "SELECT username, password FROM account WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Checks if an email address is available.
     *
     * @param mixed $email The email address to check.
     *
     * @return bool Returns true if the email is not found in the database, otherwise false.
     */
    public function check_Email_Availability($email): bool{
        $sql = "SELECT email FROM account WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(["email" => $email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result === false;
    }

    /**
     * Checks if a username is available.
     *
     * @param mixed $username The username to check.
     *
     * @return bool Returns true if the username is not found in the database, otherwise false.
     */
    public function check_username_availability($username): bool{
        $sql = "SELECT account_id FROM account WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result === false;
    }

    /**
     * Gets all accounts ordered by latest first.
     *
     * @return array Returns an array containing all account records.
     */
    public function selectAll(): array{
        $sql = "SELECT * FROM account ORDER BY id DESC";
        $result = $this->conn->query($sql);
        $users = [];

        if($result){
            while($row = $result->fetch(\PDO::FETCH_ASSOC)){
                $users[] = $row;
            }
        }

        return $users;
    }

    /**
     * Inserts a new account and related user profile information.
     *
     * This method uses a transaction to make sure both the account and user
     * profile records are inserted successfully.
     *
     * @param mixed $data The user registration data.
     *
     * @return string|false Returns the last inserted account ID if successful, otherwise false.
     */
    public function insert($data): string|false{

        try{
            // Start transaction to ensure both inserts succeed or fail together
            $this->conn->beginTransaction();

            // Insert into account table
            $stmt = $this->conn->prepare("INSERT INTO account (username, email, password) 
                                    VALUES (:username, :email, :password)");
            $stmt->execute([
                'username' => $data['sign_up_username'],
                'email' => $data['email'],
                'password' => $data['sign_up_password']
            ]);

            // Get last inserted account ID
            $last_id = $this->conn->lastInsertId();

            // Insert related user profile data
            $stmt2 = $this->conn->prepare("INSERT INTO user(account_id, first_name, last_name, sex, birthdate) 
                                    VALUES (:account_id, :first_name, :last_name, :sex, :birthdate)");
            $stmt2->execute([
                'account_id' => $last_id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'sex' => $data['sex'],
                'birthdate' => $data['birthdate']
            ]);

            // Commit transaction if both inserts succeed
            $this->conn->commit();

            return $last_id;

        }catch(\PDOException $e){
            // Rollback if anything fails
            if($this->conn->inTransaction()){
                $this->conn->rollBack();
            }

            return false;
        }

    }

    /**
     * Updates account and user profile information.
     *
     * This method updates the account table and user table using the old username
     * to find the existing account record.
     *
     * @param array $userInfo The updated user account and profile information.
     *
     * @return bool Returns true if at least one row was updated, otherwise false.
     */
    public function updateAccount(array $userInfo): bool{
        try{
            $sql = "UPDATE account a
                    INNER JOIN user u ON a.account_id = u.account_id
                    SET
                        a.username = :username,
                        a.email = :email,
                        u.first_name = :first_name,
                        u.last_name = :last_name,
                        u.sex = :sex,
                        u.birthdate = :birthdate
                    WHERE a.username = :old_username";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'username' => $userInfo['username'],
                'email' => $userInfo['email'],
                'first_name' => $userInfo['first_name'],
                'last_name' => $userInfo['last_name'],
                'sex' => $userInfo['sex'],
                'birthdate' => $userInfo['birthdate'],
                'old_username' => $userInfo['old_username']
            ]);

            return $stmt->rowCount() > 0;

        }catch(\PDOException $error){
            return false;
        }
    }    

    /**
     * Deletes an account based on the given username.
     *
     * This method removes the account record from the database using the provided
     * username. It returns true when at least one row was deleted, otherwise false.
     *
     * @param string $username The username of the account to delete.
     *
     * @return bool Returns true if the account was deleted successfully, otherwise false.
     */
    public function deleteAccount(string $username): bool{
        try{
            $sql = "DELETE FROM account WHERE username = :username";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'username' => $username
            ]);

            return $stmt->rowCount() > 0; 
        }catch(\PDOException){
            return false;
        }
    }


}

?>