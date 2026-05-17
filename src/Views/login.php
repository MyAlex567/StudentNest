<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
session_start();
require_once __DIR__ . '../../../vendor/autoload.php';

use App\Controllers\UserController;
use App\Models\UserModel;
use App\Helpers\Database;
use App\Controllers\AuthController;

$database = Database::getInstance();
$usermodel = new UserModel($database);
$UserController = new UserController($usermodel);
$AuthController = new AuthController($usermodel);
$message = '';
$messageType = '';

if(isset($_POST['Login']) && $_SERVER['REQUEST_METHOD'] == "POST"){

    $data = [
        'username' => $_POST['username'],
        'password' => $_POST['user_password']
    ];

    $result = $AuthController->login($data);

    if($result['success']){
        $_SESSION['userData'] = $result['data'];
        header('Location: ' . '../../index.php');
        exit;
    }else{
        $_SESSION['errors'] = implode(', ', $result['errors']);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }


}


if(isset($_SESSION['errors'])){
    $message = $_SESSION['errors'];
    $messageType = 'error';
    unset($_SESSION['errors']);
}





?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoginPage</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../Assets/CssDesign/Login.css">
</head>

<body>

    <div class="Login_form_container">

        <h3 class="login_title">LOG IN</h3>
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message ?>
            </div>
        <?php endif; ?>
        <h1>Hi! Welcome back to StudentNest 👋</h1>
        <h4 style="margin-bottom: 60px;">Sign in to continue your learning journey.</h4>
        
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

            <div class="user_info">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" placeholder="Username">
            </div>

            <div class="user_info">
                <label for="user_password">Password:</label>
                <input type="password" name="user_password" id="password" placeholder="Password">
            </div>

            <div class="login_button_container">
                <button type="submit" name="Login">Login</button>
            </div>


        </form>

        <p>Don't Have an account? <a style="color: hsl(207, 100%, 54%)" href="sign_up.php">Sign Up</a></p>
    </div>




    <script src="../../Assets/JavaScript/app.js"></script>
</body>
</html>