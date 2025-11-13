<?php
// order.php
session_start();
require_once 'user_functions.php';
require_once 'config.php';

// Если пользователь не авторизован, перенаправляем на страницу авторизации
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';

// Получаем услуги из базы данных
$services = [];
try {
    $stmt = $pdo->query("SELECT * FROM services WHERE is_available = TRUE");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Ошибка при загрузке услуг: " . $e->getMessage();
    $messageType = 'error';
}

// Получаем книги из базы данных
$books = [];
try {
    $stmt = $pdo->query("SELECT book_id, title, price FROM books WHERE stock_quantity > 0");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Ошибка при загрузке книг: " . $e->getMessage();
    $messageType = 'error';
}

// Обработка формы заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Преобразуем пустые строки в NULL для числовых полей
    $serviceIdBook = !empty($_POST['service_id_book']) ? intval($_POST['service_id_book']) : null;
    $serviceIdDesign = !empty($_POST['service_id_design']) ? intval($_POST['service_id_design']) : null;
    $bookId = !empty($_POST['book_id']) ? intval($_POST['book_id']) : null;
    $serviceType = $_POST['service_type'];
    $shippingAddress = trim($_POST['shipping_address']);
    $recipientName = trim($_POST['recipient_name']);
    $recipientPhone = trim($_POST['recipient_phone']);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    // Объединяем service_id из разных секций
    $serviceId = $serviceIdBook ?? $serviceIdDesign;
    
    // Валидация
    if (empty($serviceType) || empty($shippingAddress) || empty($recipientName) || empty($recipientPhone)) {
        $message = "Все обязательные поля должны быть заполнены";
        $messageType = 'error';
    } elseif ($quantity < 1) {
        $message = "Количество должно быть не менее 1";
        $messageType = 'error';
    } else {
        try {
            // Определяем цену в зависимости от типа услуги
            $unitPrice = 0;
            
            if ($serviceType === 'book' && $bookId) {
                // Если выбрана конкретная книга
                $stmt = $pdo->prepare("SELECT price FROM books WHERE book_id = ?");
                $stmt->execute([$bookId]);
                $book = $stmt->fetch(PDO::FETCH_ASSOC);
                $unitPrice = $book['price'] ?? 0;
            } elseif ($serviceType === 'cover_design' && $serviceId) {
                // Если выбрана услуга дизайна
                $stmt = $pdo->prepare("SELECT base_price FROM services WHERE service_id = ?");
                $stmt->execute([$serviceId]);
                $service = $stmt->fetch(PDO::FETCH_ASSOC);
                $unitPrice = $service['base_price'] ?? 0;
            } elseif ($serviceType === 'book' && $serviceId) {
                // Если выбрана услуга книги (индивидуальное издание)
                $stmt = $pdo->prepare("SELECT base_price FROM services WHERE service_id = ?");
                $stmt->execute([$serviceId]);
                $service = $stmt->fetch(PDO::FETCH_ASSOC);
                $unitPrice = $service['base_price'] ?? 0;
            }
            
            $totalAmount = $unitPrice * $quantity;
            
            // Вставляем заказ в базу данных со ВСЕМИ обязательными полями
            $stmt = $pdo->prepare("INSERT INTO user_orders (user_id, service_id, book_id, service_type, shipping_address, recipient_name, recipient_phone, unit_price, quantity, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $serviceId,
                $bookId,
                $serviceType,
                $shippingAddress,
                $recipientName,
                $recipientPhone,
                $unitPrice,
                $quantity,
                $totalAmount
            ]);
            
            $orderId = $pdo->lastInsertId();
            $message = "Заказ успешно оформлен! Номер заказа: " . $orderId;
            $messageType = 'success';
            
            // Очищаем форму после успешного оформления
            $_POST = [];
            
        } catch (PDOException $e) {
            $message = "Ошибка при оформлении заказа: " . $e->getMessage();
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
    <title>Оформление заказа - Издательство</title>
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
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
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

        .form-label.required::after {
            content: " *";
            color: #ff6b6b;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            font-family: Arial, sans-serif;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #92816F;
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
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
            text-decoration: none;
            display: block;
            text-align: center;
            margin-bottom: 10px;
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
            padding: 15px;
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

        .service-type-section {
            display: none;
            margin-top: 10px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #92816F;
        }

        .price-display {
            background-color: #e8f5e8;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
            color: #2d5016;
            display: none;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>
    <script>
        function toggleServiceSections() {
            const serviceType = document.getElementById('service_type').value;
            const bookSection = document.getElementById('book-section');
            const designSection = document.getElementById('design-section');
            const priceDisplay = document.getElementById('price-display');
            
            // Скрываем все секции
            bookSection.style.display = 'none';
            designSection.style.display = 'none';
            priceDisplay.style.display = 'none';
            
            // Показываем нужную секцию
            if (serviceType === 'book') {
                bookSection.style.display = 'block';
            } else if (serviceType === 'cover_design') {
                designSection.style.display = 'block';
            }
            
            updatePrice();
        }
        
        function updatePrice() {
            const serviceType = document.getElementById('service_type').value;
            const bookSelect = document.getElementById('book_id');
            const serviceBookSelect = document.getElementById('service_id_book');
            const serviceDesignSelect = document.getElementById('service_id_design');
            const quantity = document.getElementById('quantity').value;
            const priceDisplay = document.getElementById('price-display');
            
            let unitPrice = 0;
            
            if (serviceType === 'book' && bookSelect.value) {
                unitPrice = parseFloat(bookSelect.options[bookSelect.selectedIndex].getAttribute('data-price'));
            } else if (serviceType === 'book' && serviceBookSelect.value) {
                unitPrice = parseFloat(serviceBookSelect.options[serviceBookSelect.selectedIndex].getAttribute('data-price'));
            } else if (serviceType === 'cover_design' && serviceDesignSelect.value) {
                unitPrice = parseFloat(serviceDesignSelect.options[serviceDesignSelect.selectedIndex].getAttribute('data-price'));
            }
            
            const totalPrice = unitPrice * quantity;
            
            if (unitPrice > 0) {
                priceDisplay.innerHTML = `Стоимость за единицу: ${unitPrice} руб.<br>Общая стоимость: ${totalPrice} руб.`;
                priceDisplay.style.display = 'block';
            } else {
                priceDisplay.style.display = 'none';
            }
        }
        
        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            toggleServiceSections();
        });
    </script>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Оформление заказа</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label required" for="service_type">Тип услуги</label>
                <select id="service_type" name="service_type" class="form-select" onchange="toggleServiceSections()" required>
                    <option value="">-- Выберите тип услуги --</option>
                    <option value="book" <?php echo isset($_POST['service_type']) && $_POST['service_type'] === 'book' ? 'selected' : ''; ?>>Книга</option>
                    <option value="cover_design" <?php echo isset($_POST['service_type']) && $_POST['service_type'] === 'cover_design' ? 'selected' : ''; ?>>Дизайн обложки</option>
                </select>
            </div>

            <!-- Секция для заказа книги -->
            <div id="book-section" class="service-type-section">
                <div class="form-group">
                    <label class="form-label" for="book_id">Выберите книгу</label>
                    <select id="book_id" name="book_id" class="form-select" onchange="updatePrice()">
                        <option value="">-- Выберите книгу --</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?php echo $book['book_id']; ?>" 
                                    data-price="<?php echo $book['price']; ?>"
                                    <?php echo isset($_POST['book_id']) && $_POST['book_id'] == $book['book_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($book['title']); ?> (<?php echo $book['price']; ?> руб.)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Услуги для книг -->
                <div class="form-group">
                    <label class="form-label" for="service_id_book">Или выберите услугу</label>
                    <select id="service_id_book" name="service_id_book" class="form-select" onchange="updatePrice()">
                        <option value="">-- Выберите услугу --</option>
                        <?php foreach ($services as $service): ?>
                            <?php if ($service['service_type'] === 'book'): ?>
                                <option value="<?php echo $service['service_id']; ?>" 
                                        data-price="<?php echo $service['base_price']; ?>"
                                        <?php echo isset($_POST['service_id_book']) && $_POST['service_id_book'] == $service['service_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service['service_name']); ?> (<?php echo $service['base_price']; ?> руб.)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Секция для дизайна обложки -->
            <div id="design-section" class="service-type-section">
                <div class="form-group">
                    <label class="form-label" for="service_id_design">Выберите тип дизайна</label>
                    <select id="service_id_design" name="service_id_design" class="form-select" onchange="updatePrice()">
                        <option value="">-- Выберите тип дизайна --</option>
                        <?php foreach ($services as $service): ?>
                            <?php if ($service['service_type'] === 'cover_design'): ?>
                                <option value="<?php echo $service['service_id']; ?>" 
                                        data-price="<?php echo $service['base_price']; ?>"
                                        <?php echo isset($_POST['service_id_design']) && $_POST['service_id_design'] == $service['service_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service['service_name']); ?> (<?php echo $service['base_price']; ?> руб.)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label required" for="quantity">Количество</label>
                <input type="number" id="quantity" name="quantity" class="form-input" 
                       min="1" value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '1'; ?>" 
                       onchange="updatePrice()" required>
            </div>

            <div class="form-group">
                <label class="form-label required" for="shipping_address">Адрес доставки</label>
                <textarea id="shipping_address" name="shipping_address" class="form-textarea" required><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label required" for="recipient_name">Имя получателя</label>
                <input type="text" id="recipient_name" name="recipient_name" class="form-input"
                       value="<?php echo isset($_POST['recipient_name']) ? htmlspecialchars($_POST['recipient_name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label required" for="recipient_phone">Телефон получателя</label>
                <input type="tel" id="recipient_phone" name="recipient_phone" class="form-input"
                       value="<?php echo isset($_POST['recipient_phone']) ? htmlspecialchars($_POST['recipient_phone']) : ''; ?>" required>
            </div>

            <div id="price-display" class="price-display"></div>

            <div class="button-group">
                <button type="submit" class="submit-btn">Оформить заказ</button>
                <a href="index1.php" class="home-btn">Вернуться на главную</a>
            </div>
        </form>

        <div class="auth-link">
            <a href="login.php">← Вернуться в личный кабинет</a>
        </div>
    </div>
</body>
</html>