<?php
// admin_functions.php
require_once 'config.php';

// Авторизация администратора
function loginAdmin($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT admin_id, username, email, full_name, password_hash FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && $password === $admin['password_hash']) {
        // Успешная авторизация
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['full_name'];
        return ["success" => true, "message" => "Авторизация администратора успешна!"];
    } else {
        return ["success" => false, "message" => "Неверный логин или пароль"];
    }
}

// Проверка авторизации администратора
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Выход администратора
function logoutAdmin() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_email']);
    unset($_SESSION['admin_name']);
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

// Получение информации о текущем администраторе
function getCurrentAdmin() {
    if (isAdminLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT admin_id, username, email, full_name FROM admins WHERE admin_id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

// Регистрация нового администратора
function registerAdmin($username, $password, $email, $fullName) {
    global $pdo;
    
    // Проверяем, существует ли username
    $stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        return ["success" => false, "message" => "Администратор с таким логином уже существует"];
    }
    
    // Проверяем, существует ли email
    $stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        return ["success" => false, "message" => "Администратор с таким email уже существует"];
    }
    
    // Сохраняем пароль в открытом виде (без хеширования)
    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, email, full_name) VALUES (?, ?, ?, ?)");
    
    try {
        $stmt->execute([$username, $password, $email, $fullName]);
        return ["success" => true, "message" => "Администратор успешно зарегистрирован!"];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Ошибка при регистрации: " . $e->getMessage()];
    }
}

// Получение всех администраторов
function getAllAdmins() {
    global $pdo;
    $stmt = $pdo->query("SELECT admin_id, username, email, full_name FROM admins ORDER BY admin_id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>