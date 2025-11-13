<?php
// register.php
session_start();
require_once 'user_functions.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$message = '';
$messageType = '';

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Валидация
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $message = "Все обязательные поля должны быть заполнены";
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = "Пароли не совпадают";
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = "Пароль должен содержать минимум 6 символов";
        $messageType = 'error';
    } else {
        $result = registerUser($firstName, $lastName, $email, $phone, $password);
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            // Перенаправляем на страницу авторизации
            header("Refresh: 2; URL=login.php");
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Издательство</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .auth-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .auth-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #92816F;
        }

        .submit-btn {
            width: 100%;
            background-color: #92816F;
            color: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
        }

        .submit-btn:hover {
            background-color: #7a6b5c;
        }

        .home-btn {
            width: 100%;
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
            text-decoration: none;
            display: block;
            text-align: center;
            box-sizing: border-box;
        }

        .home-btn:hover {
            background-color: #5a6268;
        }

        .auth-link {
            text-align: center;
            margin-top: 20px;
        }

        .auth-link a {
            color: #92816F;
            text-decoration: none;
        }

        .auth-link a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Регистрация</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="first_name">Имя *</label>
                <input type="text" id="first_name" name="first_name" class="form-input" 
                       value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="last_name">Фамилия *</label>
                <input type="text" id="last_name" name="last_name" class="form-input"
                       value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-input"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" class="form-input"
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Пароль *</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Подтверждение пароля *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>

            <div class="button-group">
                <button type="submit" class="submit-btn">Зарегистрироваться</button>
                <a href="index1.php" class="home-btn">Вернуться на главную</a>
            </div>
        </form>

        <div class="auth-link">
            Уже есть аккаунт? <a href="login.php">Войдите</a>
        </div>
    </div>
</body>
</html>