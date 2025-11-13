<?php
// login.php
session_start();
require_once 'user_functions.php';

$message = '';
$messageType = '';
$isLoggedIn = isLoggedIn();

// Обработка формы авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Если пользователь уже авторизован, обрабатываем заказ
    if ($isLoggedIn) {
        header("Location: order.php");
        exit;
    }
    
    // Если не авторизован, обрабатываем вход
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $message = "Все поля должны быть заполнены";
        $messageType = 'error';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            $message = "Авторизация успешна! Теперь вы можете оформить заказ.";
            $messageType = 'success';
            $isLoggedIn = true;
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
    <title>Авторизация - Издательство</title>
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

        .welcome-message {
            text-align: center;
            color: #92816F;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 18px;
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

        .order-btn {
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

        .order-btn:hover {
            background-color: #92816F;
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
            color: #92816F;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .user-info {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .logout-link {
            text-align: center;
            margin-top: 15px;
        }

        .logout-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-link a:hover {
            color: #ff6b6b;
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
        <h1 class="auth-title">
            <?php echo $isLoggedIn ? 'Добро пожаловать!' : 'Авторизация'; ?>
        </h1>
        
        <?php if ($isLoggedIn): ?>
            <div class="user-info">
                <strong>Вы вошли как:</strong><br>
                <?php echo htmlspecialchars($_SESSION['user_name']); ?><br>
                <?php echo htmlspecialchars($_SESSION['user_email']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php if (!$isLoggedIn): ?>
                <!-- Форма авторизации -->
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-btn">Войти</button>
                    <a href="index1.php" class="home-btn">Вернуться на главную</a>
                </div>
                
                <div class="auth-link">
                    Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a>
                </div>
            <?php else: ?>
                <!-- Кнопка оформления заказа для авторизованного пользователя -->
                <div class="welcome-message">
                    Готовы оформить заказ?
                </div>
                
                <div class="button-group">
                    <button type="submit" class="order-btn">Оформить заказ</button>
                    <a href="index1.php" class="home-btn">Вернуться на главную</a>
                </div>
                
                <div class="logout-link">
                    <a href="logout.php">Выйти из аккаунта</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>