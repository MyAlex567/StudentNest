<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
session_start();
require_once __DIR__ . '../../../vendor/autoload.php';

use App\Controllers\UserController;
use App\Models\UserModel;
use App\Helpers\Database;


$database = Database::getInstance();
$usermodel = new UserModel($database);
$UserController = new UserController($usermodel);
$message = '';
$messageType = '';

if(isset($_POST['sign_up']) && $_SERVER['REQUEST_METHOD'] == "POST"){

    $data = [
        'sign_up_username' => $_POST['sign_up_username'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'sex' => $_POST['sex'] ?? 'none',
        'birthdate' => $_POST['birthdate'],
        'sign_up_password' => $_POST['sign_up_password'],
        'confirm_password' => $_POST['confirm_password']
    ];

    $result = $UserController->validateAndProcessRegistration($data);

    if ($result['success']) {
        $_SESSION['success'] = "User registered successfully!";
        $_SESSION['user_data'] = $result['data'];
    } else {
        $_SESSION['error'] = implode('<br>', $result['message']);
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;

}

// Get session messages
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']);
    unset($_SESSION['user_data']);
} elseif (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $messageType = 'error';
    unset($_SESSION['error']);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="../../Assets/CssDesign/Sign_up.css">
</head>

<body>

    <!-- Sign Up Form part -->
    <div class="sign_up_form1">

        <h3 class="login_title">SIGN UP</h3>

        <h1>Hi! Come Join us with StudentNest 👋</h1>
        <h4 style="margin-bottom: 40px;">Sign up to level up your learning journey.</h4>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

            <input type="checkbox" id="toggle_parts">
            <input type="checkbox" name="" id="go_back_toggle">

            <div class="first_part_info">
                <div class="sign_up_info">
                    <label for="sign_up_username">Username: </label>
                    <input type="text" name="sign_up_username" id="sign_up_username" placeholder="username">
                    <p id="usernameStatus" class="username-status"></p>
                </div>

                <div class="sign_up_info">
                    <label for="first_name">First name: </label>
                    <input type="text" name="first_name" id="first_name" placeholder="First Name">
                </div>

                <div class="sign_up_info">
                    <label for="last_name">Last name: </label>
                    <input type="text" name="last_name" id="last_name" placeholder="last Name">
                </div>

                <div class="sign_up_info">
                    <label for="sign_uo_email">Email: </label>
                    <input type="Email" name="email" id="email" placeholder="Email">
                    <p id="emailStatus" class="emails-status"></p>
                </div>


                <div class="Next_button_container">
                    <label for="toggle_parts">Next<i class="fa-solid fa-arrow-right"></i></label>
                </div>
                <a href="./login.php">Log in</a>
            </div>

            <div class="second_part">
                <div class="sign_up_info_sex">

                    <label for="">Sex:</label>

                    <label for="male">Male</label>
                    <input type="radio" id="male" name="sex" value="male">

                    <label for="female">Female</label>
                    <input type="radio" id="female" name="sex" value="female">

                </div>

                <div class="sign_up_info">
                    <label for="birthdate">Birthdate:</label>
                    <input type="date" id="birthdate" name="birthdate">
                </div>

                <div class="sign_up_info">
                    <label for="sign_up_password">Create Password:</label>
                    <input type="password" id="sign_up_password" name="sign_up_password" placeholder="create password">
                </div>

                <div class="sign_up_info">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="confirm your password">
                </div>

                <div class="go_back-container">
                    <button for="go_back_toggle" type="reset">Back</button>
                </div>

                <div class="sign_up_button_container">
                    <button type="submit" name="sign_up">Sign Up</button>
                </div>
            </div>

        </form>

    </div>


    <script src="../../Assets/JavaScript/app.js"></script>
</body>
</html>