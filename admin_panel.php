<?php
// admin_panel.php
session_start();
require_once 'admin_functions.php';
require_once 'config.php';

// Проверка авторизации администратора
if (!isAdminLoggedIn()) {
    header("Location: admin_login.php");
    exit;
}

// Получаем список всех таблиц
$tables = [];
$views = [];
try {
    // Получаем таблицы
    $stmt = $pdo->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($allTables as $table) {
        // Получаем информацию о таблице
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE '$table'");
        $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tableInfo['Comment'] == 'VIEW') {
            $views[] = $table;
        } else {
            $tables[] = $table;
        }
    }
} catch (PDOException $e) {
    $error = "Ошибка при получении списка таблиц: " . $e->getMessage();
}

// Обработка действий
$action = $_GET['action'] ?? '';
$table = $_GET['table'] ?? '';
$view = $_GET['view'] ?? '';
$edit_id = $_GET['edit_id'] ?? '';
$show_add_form = isset($_GET['show_add']);

// Определяем активную вкладку
$active_tab = $_GET['tab'] ?? 'tables';

// Получение данных таблицы
$tableData = [];
$tableColumns = [];
$currentTable = '';
$editData = [];

if ($table && in_array($table, $tables)) {
    $currentTable = $table;
    try {
        // Получаем структуру таблицы
        $stmt = $pdo->query("DESCRIBE $table");
        $tableColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Получаем данные таблицы
        $stmt = $pdo->query("SELECT * FROM $table");
        $tableData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Если редактируем, получаем данные записи
        if ($edit_id) {
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE " . $tableColumns[0]['Field'] . " = ?");
            $stmt->execute([$edit_id]);
            $editData = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error = "Ошибка при получении данных таблицы: " . $e->getMessage();
    }
}

// Получение данных представления
$viewData = [];
$viewColumns = [];
$currentView = '';

if ($view && in_array($view, $views)) {
    $currentView = $view;
    try {
        // Получаем структуру представления
        $stmt = $pdo->query("DESCRIBE $view");
        $viewColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Получаем данные представления
        $stmt = $pdo->query("SELECT * FROM $view");
        $viewData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Ошибка при получении данных представления: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Издательство</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .admin-header {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
            color: #333;
            font-size: 24px;
        }

        .logout-btn {
            background-color: #92816F;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background-color: #92816F;
        }

        .admin-container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #92816F;
        }

        .tab {
            padding: 10px 20px;
            background: #f5f5f5;
            border: none;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
            text-decoration: none;
            color: #333;
            display: inline-block;
        }

        .tab.active {
            background: #92816F;
            color: white;
        }

        .tab-content {
            display: <?php echo $active_tab === 'tables' ? 'block' : 'none'; ?>;
        }

        #views-tab {
            display: <?php echo $active_tab === 'views' ? 'block' : 'none'; ?>;
        }

        .table-list, .view-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .table-item, .view-item {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            text-decoration: none;
            color: #333;
            display: block;
        }

        .table-item:hover, .view-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .table-item.active, .view-item.active {
            border-left: 4px solid #92816F;
        }

        .data-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .data-table th {
            background-color: #92816F;
            color: white;
            font-weight: bold;
        }

        .data-table tr:hover {
            background-color: #f9f9f9;
        }

        .action-btn {
            padding: 5px 10px;
            margin: 0 2px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            color: white;
        }

        .edit-btn {
            background-color: #92816F;
        }

        .delete-btn {
            background-color: #92816F;
        }

        .add-btn {
            background-color: #92816F;
            color: white;
            padding: 10px 15px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }

        .submit-btn {
            background-color: #92816F;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .error {
            background-color: #f8d7da;
            color: #92816F;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .info {
            background-color: #ffffffff;
            color: #000000ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1 class="admin-title">Панель администратора</h1>
        <a href="admin_logout.php" class="logout-btn">Выйти</a>
    </div>

    <div class="admin-container">
        <div class="sidebar">
            <h3>Навигация</h3>
            <div class="tabs">
                <a href="?tab=tables" class="tab <?php echo $active_tab === 'tables' ? 'active' : ''; ?>">Таблицы</a>
                <a href="?tab=views" class="tab <?php echo $active_tab === 'views' ? 'active' : ''; ?>">Представления</a>
            </div>
        </div>

        <div class="main-content">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Вкладка таблиц -->
            <div id="tables-tab" class="tab-content">
                <h2>Таблицы базы данных</h2>
                
                <div class="table-list">
                    <?php foreach ($tables as $tableName): ?>
                        <a href="?tab=tables&table=<?php echo urlencode($tableName); ?>" 
                           class="table-item <?php echo $currentTable === $tableName ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($tableName); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($currentTable): ?>
                    <div class="info">
                        Просмотр таблицы: <strong><?php echo htmlspecialchars($currentTable); ?></strong>
                    </div>

                    <a href="?tab=tables&table=<?php echo urlencode($currentTable); ?>&show_add=1" class="add-btn">+ Добавить запись</a>

                    <?php if ($show_add_form): ?>
                        <!-- Форма добавления -->
                        <div class="form-container">
                            <h3>Добавить запись в <?php echo htmlspecialchars($currentTable); ?></h3>
                            <form method="POST" action="admin_actions.php">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="table" value="<?php echo htmlspecialchars($currentTable); ?>">
                                
                                <?php foreach ($tableColumns as $column): ?>
                                    <?php if ($column['Extra'] !== 'auto_increment'): ?>
                                        <div class="form-group">
                                            <label class="form-label"><?php echo htmlspecialchars($column['Field']); ?></label>
                                            <input type="text" name="<?php echo htmlspecialchars($column['Field']); ?>" 
                                                   class="form-input" 
                                                   placeholder="<?php echo htmlspecialchars($column['Type']); ?>"
                                                   value="">
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <button type="submit" class="submit-btn">Добавить</button>
                                <a href="?tab=tables&table=<?php echo urlencode($currentTable); ?>" class="cancel-btn">Отмена</a>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($edit_id && !empty($editData)): ?>
                        <!-- Форма редактирования -->
                        <div class="form-container">
                            <h3>Редактировать запись в <?php echo htmlspecialchars($currentTable); ?></h3>
                            <form method="POST" action="admin_actions.php">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="table" value="<?php echo htmlspecialchars($currentTable); ?>">
                                <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_id); ?>">
                                
                                <?php foreach ($tableColumns as $column): ?>
                                    <div class="form-group">
                                        <label class="form-label"><?php echo htmlspecialchars($column['Field']); ?></label>
                                        <input type="text" 
                                               name="<?php echo htmlspecialchars($column['Field']); ?>" 
                                               class="form-input" 
                                               placeholder="<?php echo htmlspecialchars($column['Type']); ?>"
                                               value="<?php echo htmlspecialchars($editData[$column['Field']] ?? ''); ?>"
                                               <?php echo $column['Extra'] === 'auto_increment' ? 'readonly' : ''; ?>>
                                    </div>
                                <?php endforeach; ?>
                                
                                <button type="submit" class="submit-btn">Сохранить</button>
                                <a href="?tab=tables&table=<?php echo urlencode($currentTable); ?>" class="cancel-btn">Отмена</a>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (!$show_add_form && !$edit_id): ?>
                        <?php if (!empty($tableData)): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <?php foreach ($tableColumns as $column): ?>
                                            <th><?php echo htmlspecialchars($column['Field']); ?></th>
                                        <?php endforeach; ?>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tableData as $row): ?>
                                        <tr>
                                            <?php foreach ($tableColumns as $column): ?>
                                                <td><?php echo htmlspecialchars($row[$column['Field']] ?? ''); ?></td>
                                            <?php endforeach; ?>
                                            <td>
                                                <?php 
                                                $primaryKey = $tableColumns[0]['Field'];
                                                $primaryValue = $row[$primaryKey];
                                                ?>
                                                <a href="?tab=tables&table=<?php echo urlencode($currentTable); ?>&edit_id=<?php echo urlencode($primaryValue); ?>" 
                                                   class="action-btn edit-btn">Изменить</a>
                                                <a href="admin_actions.php?action=delete&table=<?php echo urlencode($currentTable); ?>&id=<?php echo urlencode($primaryValue); ?>" 
                                                   class="action-btn delete-btn" 
                                                   onclick="return confirm('Вы уверены, что хотите удалить эту запись?')">Удалить</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="info">Таблица пуста</div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="info">Выберите таблицу для просмотра данных</div>
                <?php endif; ?>
            </div>

            <!-- Вкладка представлений -->
            <div id="views-tab" class="tab-content">
                <h2>Представления базы данных</h2>
                
                <div class="view-list">
                    <?php foreach ($views as $viewName): ?>
                        <a href="?tab=views&view=<?php echo urlencode($viewName); ?>" 
                           class="view-item <?php echo $currentView === $viewName ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($viewName); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($currentView): ?>
                    <div class="info">
                        Просмотр представления: <strong><?php echo htmlspecialchars($currentView); ?></strong>
                    </div>

                    <?php if (!empty($viewData)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <?php foreach ($viewColumns as $column): ?>
                                        <th><?php echo htmlspecialchars($column['Field']); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($viewData as $row): ?>
                                    <tr>
                                        <?php foreach ($viewColumns as $column): ?>
                                            <td><?php echo htmlspecialchars($row[$column['Field']] ?? ''); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="info">Представление пусто</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="info">Выберите представление для просмотра данных</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>