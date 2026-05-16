<?php
namespace App\APIs;
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Helpers\Database;
use App\Models\UserModel;
use App\Models\ClassModel;
use App\Controllers\ClassController;
use App\Controllers\UserController;

class UserApi{
    private $usermodel;
    private $classModel;
    private $userController;
    private $classController;

    public function __construct(){
        $database = Database::getInstance();
        $this->usermodel = new UserModel($database);
        $this->classModel = new ClassModel($database);
        $this->userController = new UserController($this->usermodel);
        $this->classController = new ClassController($this->classModel);
    }

    public function handleRequest(){
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
            http_response_code(200);
            exit();
        }

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            http_response_code(405);
            echo json_encode(['error' => 'Method not Allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        

        switch($action){
            case 'get-To-be-graded':
                $this->getToBeGraded();
                break;
            case 'check-username':
                $this->check_username_availability();
                break;
            case 'check-email':
                $this->check_email_availability();
                break;
            case 'get-class':
                $this->getUserClass();
                break;
            case 'getUser-info':
                $this->getUserInfo();
                break;
            case 'delete-post':
                $this->deletePost();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
        }
    }

    private function getUserClass(){
        $userClasses = $this->classController->getUserClasses($_SESSION['userData']['username']);

        echo json_encode([
            'success' => true,
            'classes' => $userClasses
        ]);
    }

    private function getUserInfo(){

        if(empty($_SESSION['userData'])){
            echo json_encode([
                'success' => false,
                'error' => 'error: No user data found'
            ]);
            return;
        }

        $account_username = $_SESSION['userData'];

        // Find user info by username
        $userdata = $this->userController->getUserInfo($account_username['username']);

        if(!$userdata['query_result']){
            echo json_encode([
                'result' => false,
                'error' => $userdata['error']
            ]);
            return;
        }

        echo json_encode([
            'result' => true,
            'userData' => $userdata
        ]);
    }

    private function check_username_availability(){
        $input = json_decode(file_get_contents('php://input'), true); 
        $username = $input['username'] ?? '';

        $validationResult = UserController::validateUsernameOnly($username);

        if(!$validationResult['valid']){
            echo json_encode([
                'available' => false,
                'valid' => false,
                'errors' => $validationResult['errors']
            ]);
            return;

        }

        $isAvailable = $this->usermodel->check_username_availability($username);


        echo json_encode([
            'available' => $isAvailable,
            'valid' => true,
            'username' => $validationResult['username'],
            'message' => $isAvailable ? 'Username available' : 'Username is already taken'
        ]);

    }

    private function check_email_availability(){
        $input = json_decode(file_get_contents('php://input'), true); 
        $email = $input['email'] ?? '';

        $validationResult = UserController::validateEmailOnly($email);

        if(!$validationResult['valid']){
            echo json_encode([
                'available' => false,
                'valid' => false,
                'errors' => $validationResult['errors']
            ]);
            return;
        }

        $isAvailable = $this->usermodel->check_Email_Availability($email);

        echo json_encode([
            'available' => $isAvailable,
            'valid' => true,
            'username' => $validationResult['email'],
            'message' => $isAvailable ? 'Email available' : 'Email is already taken'
        ]);

    }
    
    private function deletePost(){
        $input = json_decode(file_get_contents('php://input'), true); 
        $post_id = $input['postId'] ?? '';

        $isSuccessDeleting = $this->classController->deletePost($post_id);

        if(!$isSuccessDeleting['success']){
            echo json_encode([
                'available' => false,
                'valid' => false,
                'errors' => $isSuccessDeleting['message']
            ]);
            return;
        }

        echo json_encode([
            'success' => $isSuccessDeleting['success'],
            'message' => $isSuccessDeleting['message']
        ]);
    }

    private function getToBeGraded(){
        $username = $_SESSION['userData']['username'];

        $result = $this->classController->getTobeGraded($username);

        if(!$result['success']){
            echo json_encode([
                'success' => false,
                'message' => 'No Data Found',
                'error' => $result['message']
            ]);
            return;   
        }

        echo json_encode([
            'success' => true,
            'data' => $result['data']
        ]);
    }

}

$UserApi = new UserApi();
$UserApi->handleRequest();

?>