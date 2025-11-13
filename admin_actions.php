<?php
// admin_actions.php
session_start();
require_once 'admin_functions.php';
require_once 'config.php';

if (!isAdminLoggedIn()) {
    header("Location: admin_login.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $table = $_POST['table'];
    
    try {
        if ($action === 'add') {
            // Добавление записи
            $columns = [];
            $values = [];
            $placeholders = [];
            
            foreach ($_POST as $key => $value) {
                if ($key !== 'action' && $key !== 'table') {
                    $columns[] = $key;
                    $values[] = $value;
                    $placeholders[] = '?';
                }
            }
            
            $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            
            $_SESSION['message'] = "Запись успешно добавлена";
            $_SESSION['message_type'] = 'success';
            
        } elseif ($action === 'edit') {
            // Редактирование записи
            $editId = $_POST['edit_id'];
            
            // Получаем первичный ключ таблицы
            $stmt = $pdo->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
            $primaryKey = $stmt->fetch(PDO::FETCH_ASSOC);
            $pkColumn = $primaryKey['Column_name'];
            
            $setParts = [];
            $values = [];
            
            foreach ($_POST as $key => $value) {
                if ($key !== 'action' && $key !== 'table' && $key !== 'edit_id' && $key !== $pkColumn) {
                    $setParts[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            $values[] = $editId;
            
            $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE $pkColumn = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            
            $_SESSION['message'] = "Запись успешно обновлена";
            $_SESSION['message_type'] = 'success';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Ошибка: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: admin_panel.php?table=$table");
    exit;
}

// Обработка GET запросов (удаление)
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $table = $_GET['table'];
    $id = $_GET['id'];
    
    try {
        // Получаем первичный ключ таблицы
        $stmt = $pdo->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
        $primaryKey = $stmt->fetch(PDO::FETCH_ASSOC);
        $pkColumn = $primaryKey['Column_name'];
        
        $sql = "DELETE FROM $table WHERE $pkColumn = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        $_SESSION['message'] = "Запись успешно удалена";
        $_SESSION['message_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = "Ошибка при удалении: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: admin_panel.php?table=$table");
    exit;
}