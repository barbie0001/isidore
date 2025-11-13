<?php
// admin_login.php
session_start();
require_once 'config.php';
require_once 'admin_functions.php';

// Если администратор уже авторизован, перенаправляем в админ-панель
if (isAdminLoggedIn()) {
    header("Location: admin_panel.php");
    exit;
}

$message = '';
$messageType = '';
$showRegistration = false;

// Обработка переключения между формами
if (isset($_GET['action']) && $_GET['action'] === 'register') {
    $showRegistration = true;
}

// Обработка формы авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Обработка регистрации
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $email = trim($_POST['email']);
        $fullName = trim($_POST['full_name']);
        $confirmPassword = $_POST['confirm_password'];
        
        // Валидация
        if (empty($username) || empty($password) || empty($email) || empty($fullName)) {
            $message = "Все поля должны быть заполнены";
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Некорректный формат email";
            $messageType = 'error';
        } elseif ($password !== $confirmPassword) {
            $message = "Пароли не совпадают";
            $messageType = 'error';
        } elseif (strlen($password) < 6) {
            $message = "Пароль должен содержать минимум 6 символов";
            $messageType = 'error';
        } else {
            // Регистрируем администратора
            $result = registerAdmin($username, $password, $email, $fullName);
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
                $showRegistration = false;
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    } else {
        // Обработка авторизации
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $message = "Все поля должны быть заполнены";
            $messageType = 'error';
        } else {
            $result = loginAdmin($username, $password);
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
                // Перенаправляем в админ-панель
                header("Location: admin_panel.php");
                exit;
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style1.css">
    <title>Авторизация администратора - Издательство</title>
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
            padding: 20px;
        }

        .auth-container {
            background: white;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }

        .auth-title {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .admin-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
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

        .form-label.required::after {
            content: " *";
            color: #ff6b6b;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
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
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 15px;
        }

        .submit-btn:hover {
            background-color: #7a6b5c;
        }

        .register-btn {
            width: 100%;
            background-color: #92816F;
            color: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 15px;
        }

        .register-btn:hover {
            background-color: #92816F;
        }

        .switch-form {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .switch-form a {
            color: #92816F;
            text-decoration: none;
            font-weight: bold;
        }

        .switch-form a:hover {
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #92816F;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
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

        .admin-badge {
            background: #92816F;
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }

        .registration-note {
            background-color: #e3f2fd;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1565c0;
            border-left: 4px solid #2196f3;
        }

        .password-requirements {
            background-color: #fff3cd;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 12px;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">
            <?php echo $showRegistration ? 'Регистрация администратора' : 'Панель администратора'; ?>
            <span class="admin-badge">ADMIN</span>
        </h1>
        <p class="admin-subtitle">
            <?php echo $showRegistration ? 'Создание новой учетной записи администратора' : 'Доступ только для авторизованного персонала'; ?>
        </p>
        
        <?php if ($showRegistration): ?>
            <div class="registration-note">
            </div>
            
            <div class="password-requirements">
                <strong>Требования к паролю:</strong> минимум 6 символов
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!$showRegistration): ?>
            <!-- Форма авторизации -->
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label required" for="username">Логин администратора</label>
                    <input type="text" id="username" name="username" class="form-input"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           placeholder="Введите ваш логин" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Введите ваш пароль" required>
                </div>

                <button type="submit" class="submit-btn">Войти как администратор</button>
            </form>

            <div class="switch-form">
                Нет учетной записи? <a href="?action=register">Зарегистрировать администратора</a>
            </div>

        <?php else: ?>
            <!-- Форма регистрации -->
            <form method="POST" action="">
                <input type="hidden" name="register" value="1">
                
                <div class="form-group">
                    <label class="form-label required" for="full_name">ФИО администратора</label>
                    <input type="text" id="full_name" name="full_name" class="form-input"
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                           placeholder="Иванов Иван Иванович" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           placeholder="admin@example.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="username">Логин</label>
                    <input type="text" id="username" name="username" class="form-input"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           placeholder="Придумайте логин" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Минимум 6 символов" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="confirm_password">Подтверждение пароля</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                           placeholder="Повторите пароль" required>
                </div>

                <button type="submit" class="register-btn">Зарегистрировать администратора</button>
            </form>

            <div class="switch-form">
                Уже есть учетная запись? <a href="admin_login.php">Войти в систему</a>
            </div>
        <?php endif; ?>

        <div class="back-link">
            <a href="index1.php">← Вернуться на главную</a>
        </div>
    </div>
</body>
</html>