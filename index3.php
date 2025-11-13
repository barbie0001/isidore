<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style3.css">
    <title>Header с логотипом</title>
</head>

<body>
    <header class="header">
        <div class="logo-container">
            <a href="index1.php"><img src="logo.png" alt="Логотип"></a>
            <span class="izd">Издательство</span>
        </div>

        <nav class="nav-menu">
            <a href="index2.php" class="nav-item">Каталог</a>
            <a href="index3.php" class="nav-item">Контакты</a>
            <a href="index4.php" class="nav-item">Отзывы</a>
        </nav>

        <div class="contact-info">
            <a href="login_panel.php"><img src="znak.png" alt=""></a>
        </div>
    </header>
    <style>
        .auth-panel {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .auth-dropdown {
            position: relative;
            display: inline-block;
        }

        .auth-btn {
            background: #92816F;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .auth-btn:hover {
            background: #92816F;
        }

        .auth-content {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 1;
            margin-top: 5px;
        }

        .auth-content.show {
            display: block;
        }

        .auth-option {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s ease;
        }

        .auth-option:hover {
            background-color: #f5f5f5;
        }

        .auth-option:last-child {
            border-bottom: none;
        }

        .auth-option.admin {
            color: #D2C2B2;
            font-weight: bold;
        }

        .auth-option.user {
            color: #AFC1CC;
        }

        .user-info {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .user-info.show {
            display: block;
        }

        .user-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .user-role {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        .user-action {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }

        .user-action.profile {
            background: #92816F;
            color: white;
        }

        .user-action.logout {
            background: #f5f5f5;
            color: #333;
        }
    </style>
    </head>

    <body>
        <!-- Панель авторизации -->
        <div class="auth-panel">
            <div class="auth-dropdown">
                <button class="auth-btn" onclick="toggleAuthDropdown()">Войти / Регистрация</button>
                <div class="auth-content" id="authDropdown">
                    <a href="admin_login.php" class="auth-option admin">Администратор</a>
                    <a href="login.php" class="auth-option user">Пользователь</a>
                    <a href="admin_login.php?action=register" class="auth-option admin">Регистрация администратора</a>
                    <a href="register.php" class="auth-option user">Регистрация пользователя</a>
                </div>
            </div>
        </div>

        <!-- Информация о пользователе (будет показана после авторизации) -->
        <div class="user-info" id="userInfo">
            <div class="user-name" id="userName"></div>
            <div class="user-role" id="userRole"></div>
            <div class="user-actions">
                <a href="#" class="user-action profile" id="profileLink">Профиль</a>
                <a href="#" class="user-action logout" id="logoutLink">Выйти</a>
            </div>
        </div>
        <script src="script1.js"></script>
        <script>
            // Функция для переключения dropdown
            function toggleAuthDropdown() {
                document.getElementById('authDropdown').classList.toggle('show');
            }

            // Закрытие dropdown при клике вне его
            window.onclick = function (event) {
                if (!event.target.matches('.auth-btn')) {
                    var dropdowns = document.getElementsByClassName("auth-content");
                    for (var i = 0; i < dropdowns.length; i++) {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }

            // Проверка авторизации при загрузке страницы
            document.addEventListener('DOMContentLoaded', function () {
                checkAuthStatus();
            });

            // Функция проверки статуса авторизации
            function checkAuthStatus() {
                // Проверяем, авторизован ли пользователь (это можно сделать через PHP сессии или localStorage)
                // Здесь пример с localStorage для демонстрации
                const userData = localStorage.getItem('userData');
                const adminData = localStorage.getItem('adminData');

                if (userData) {
                    showUserInfo(JSON.parse(userData), 'user');
                } else if (adminData) {
                    showUserInfo(JSON.parse(adminData), 'admin');
                }
            }

            // Функция показа информации о пользователе
            function showUserInfo(userData, role) {
                const authPanel = document.querySelector('.auth-panel');
                const userInfo = document.getElementById('userInfo');
                const userName = document.getElementById('userName');
                const userRole = document.getElementById('userRole');
                const profileLink = document.getElementById('profileLink');
                const logoutLink = document.getElementById('logoutLink');

                // Скрываем панель авторизации
                authPanel.style.display = 'none';

                // Заполняем данные пользователя
                if (role === 'user') {
                    userName.textContent = userData.name || 'Пользователь';
                    userRole.textContent = 'Пользователь';
                    profileLink.href = 'profile.php';
                    logoutLink.href = 'logout.php';
                } else if (role === 'admin') {
                    userName.textContent = userData.name || 'Администратор';
                    userRole.textContent = 'Администратор';
                    profileLink.href = 'admin_panel.php';
                    logoutLink.href = 'admin_logout.php';
                }

                // Показываем панель пользователя
                userInfo.classList.add('show');
            }

            // Функция выхода
            function logout() {
                localStorage.removeItem('userData');
                localStorage.removeItem('adminData');
                location.reload();
            }

            // Назначаем обработчики для ссылок выхода
            document.getElementById('logoutLink')?.addEventListener('click', function (e) {
                e.preventDefault();
                logout();
            });
        </script>
    </body>
    <section class="fullscreen-hero">
        <div class="hero-image">
            <img src="fon1.jpg" alt="Отзывы">
        </div>
        <div class="hero-overlay">
            <h1 class="hero-title">Отзывы об Издательстве</h1>
        </div>
    </section>

    <!-- Секция отзывов -->
    <section class="reviews-section">
        <div class="reviews-container">
            <!-- Отзыв 1 -->
            <div class="review-card">
                <div class="review-header">
                    <h3 class="review-author">Виктория И.</h3>
                    <div class="review-rating">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <div class="review-features">
                        <span class="feature">Приятное обслуживание</span>
                        <span class="feature">Быстрая доставка</span>
                        <span class="feature">Топовые услуги</span>
                    </div>
                </div>
                <div class="review-content">
                    <p>Всё понравилось! Заказывал обложку для книги, сделали быстро и красиво. Цена хорошая. Спасибо!
                        Рекомендую.</p>
                </div>
            </div>

            <!-- Отзыв 2 -->
            <div class="review-card">
                <div class="review-header">
                    <h3 class="review-author">Александра Р.</h3>
                    <div class="review-rating">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <div class="review-features">
                        <span class="feature">Выстрая доставка</span>
                        <span class="feature">Большой выбор</span>
                        <span class="feature">Качественные услуги</span>
                    </div>
                </div>
                <div class="review-content">
                    <p>Большее спасибо за давайм обложки! Сделали несколько крутых вариантов на выбор. Получилось
                        современно и стильно. Качество печати отличное.</p>
                </div>
            </div>

            <!-- Отзыв 3 -->
            <div class="review-card">
                <div class="review-header">
                    <h3 class="review-author">Елена В.</h3>
                    <div class="review-rating">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <div class="review-features">
                        <span class="feature">Удобная оплата</span>
                        <span class="feature">Дисциплина</span>
                        <span class="feature">Быстрые ответы</span>
                    </div>
                </div>
                <div class="review-content">
                    <p>Обращался сюда впервые, чтобы напечатать свою книгу. Очень доволен! Обложку надоедали именно так,
                        как я хотел. Менеджер всё понятно объяснял. В следующий раз только сюда.</p>
                </div>
            </div>

            <!-- Отзыв 4 -->
            <div class="review-card">
                <div class="review-header">
                    <h3 class="review-author">Дмитрий К.</h3>
                    <div class="review-rating">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <div class="review-features">
                        <span class="feature">Профессионализм</span>
                        <span class="feature">Соблюдение сроков</span>
                        <span class="feature">Отличный результат</span>
                    </div>
                </div>
                <div class="review-content">
                    <p>Заказывал печать тиража журналов. Всё сделали качественно и в срок. Особенно порадовало внимание
                        к деталям и индивидуальный подход. Буду рекомендовать коллегам!</p>
                </div>
            </div>

            <!-- Отзыв 5 -->
            <div class="review-card">
                <div class="review-header">
                    <h3 class="review-author">Марина С.</h3>
                    <div class="review-rating">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <div class="review-features">
                        <span class="feature">Креативный подход</span>
                        <span class="feature">Высокое качество</span>
                        <span class="feature">Гибкие условия</span>
                    </div>
                </div>
                <div class="review-content">
                    <p>Разрабатывали дизайн обложки для моей первой книги. Результат превзошёл все ожидания! Очень
                        творческий подход, учли все пожелания. Книга выглядит профессионально.</p>
                </div>
            </div>

            <!-- Отзыв 6 -->
            <div class="review-card">
                <div class="review-header">
                    <h3 class="review-author">Сергей П.</h3>
                    <div class="review-rating">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <div class="review-features">
                        <span class="feature">Оперативность</span>
                        <span class="feature">Честные цены</span>
                        <span class="feature">Вежливый персонал</span>
                    </div>
                </div>
                <div class="review-content">
                    <p>Печатали корпоративные материалы для компании. Сделали быстро, качественно и по разумной цене.
                        Теперь все наши полиграфические заказы только здесь.</p>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-left">
                <div class="footer-logo">
                    <img src="logo.png" alt="Логотип">
                </div>
                <div class="footer-social">
                    <a href="#" class="social-link"><img src="inst.png" alt="inst"></a>
                    <a href="#" class="social-link"><img src="tg.png" alt="tg"></a>
                    <a href="#" class="social-link"><img src="tiktok.png" alt="tiktok"></a>
                </div>
            </div>

            <div class="footer-center">
                <p class="footer-address">
                    г.Москва, ул. Большая Дмитровка, д. 32с1<br>
                    +7 (966) 525-69-57
                </p>
            </div>

            <div class="footer-right">
                <p class="footer-hours">
                    Пн-Пт: 9:00-18:00<br>
                    Сб-Вс: выходной
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="copyright">&copy; 2025 Все права защищены</p>
        </div>
    </footer>
    <script src="script1.js"></script>
</body>

</html>