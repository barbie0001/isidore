<?php
// user_functions.php
require_once 'config.php';

// Регистрация нового пользователя
function registerUser($firstName, $lastName, $email, $phone, $password) {
    global $pdo;
    
    // Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Некорректный формат email"];
    }
    
    // Проверяем, существует ли email
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        return ["success" => false, "message" => "Пользователь с таким email уже существует"];
    }
    
    // Валидация пароля
    if (strlen($password) < 6) {
        return ["success" => false, "message" => "Пароль должен содержать минимум 6 символов"];
    }
    
    // Сохраняем пароль в открытом виде (ИСПРАВЛЕНО: password_hash вместо password)
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([$firstName, $lastName, $email, $phone, $password]);
        return ["success" => true, "message" => "Регистрация прошла успешно!"];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Ошибка при регистрации: " . $e->getMessage()];
    }
}

// Авторизация пользователя
function loginUser($email, $password) {
    global $pdo;
    
    // ИСПРАВЛЕНО: password_hash вместо password
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, phone, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Простое сравнение паролей (ИСПРАВЛЕНО: проверяем password_hash)
    if ($user && $password === $user['password_hash']) {
        // Успешная авторизация
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        return ["success" => true, "message" => "Авторизация успешна!"];
    } else {
        return ["success" => false, "message" => "Неверный email или пароль"];
    }
}

// Проверка авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Выход пользователя
function logoutUser() {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Получение информации о текущем пользователе
function getCurrentUser() {
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, phone FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

// Смена пароля
function changePassword($userId, $currentPassword, $newPassword) {
    global $pdo;
    
    // Получаем текущий пароль (ИСПРАВЛЕНО: password_hash вместо password)
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || $currentPassword !== $user['password_hash']) {
        return ["success" => false, "message" => "Текущий пароль неверен"];
    }
    
    if (strlen($newPassword) < 6) {
        return ["success" => false, "message" => "Новый пароль должен содержать минимум 6 символов"];
    }
    
    // ИСПРАВЛЕНО: password_hash вместо password
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    try {
        $stmt->execute([$newPassword, $userId]);
        return ["success" => true, "message" => "Пароль успешно изменен"];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Ошибка при смене пароля: " . $e->getMessage()];
    }
}
?>