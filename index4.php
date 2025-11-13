<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style4.css">
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
            <img src="fon2.jpg" alt="Контакты">
        </div>
        <div class="hero-overlay">
            <h1 class="hero-title">Контакты</h1>
        </div>
    </section>

    <div class="container">
        <h1 class="main-title">Уважаемые гости полиграфического комплекса!</h1>

        <div class="content-wrapper">
            <!-- Левая колонка - Форма связи -->
            <div class="form-card">
                <h2>Свяжитесь с нами</h2>
                <form>
                    <input type="text" placeholder="Ваше имя...">
                    <input type="tel" placeholder="Ваш номер телефона...">
                    <button type="submit">Отправить!</button>
                </form>
            </div>

            <!-- Правая колонка - Информация и карта -->
            <div class="info-section">
                <ul>
                    <li>Для самовывоза необходимо за сутки заказать пропуск у нашего менеджера.</li>
                    <li>Визит в типографию в нерабочее время (после 19.00 часов и в сб, вс) возможен по согласованию с
                        менеджером.</li>
                </ul>

                <p class="address-title">Мы находимся на улице Большая Дмитровка, 32c1</p>

                <div class="yandex-map-container">
                    <a href="ВАША_ССЫЛКА_НА_ЯНДЕКС.КАРТЫ" target="_blank">
                        <iframe
                            src="https://yandex.ru/map-widget/v1/?um=constructor%3A004e5646b31cd434775e642d176fac68b38730c5b863f3b77c62cd735c062241&amp;source=constructor"
                            width="544" height="301" frameborder="0"></iframe> alt="Яндекс Карта">
                    </a>
                </div>
            </div>
        </div>
    </div>

</html>
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