<?php
// generate_passwords.php
// Скрипт для генерации правильных хешей паролей

$passwords = [
    'ivan@mail.ru' => 'password',
    'petr@mail.ru' => '123456',
    'admin' => 'admin123',
    'manager' => 'manager123'
];

echo "Хеши паролей для вставки в БД:\n\n";

foreach ($passwords as $email => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Email: {$email}\n";
    echo "Пароль: {$password}\n";
    echo "Хеш: {$hash}\n";
    echo "---\n";
}

// Проверка хешей
echo "\nПроверка хешей:\n";
foreach ($passwords as $email => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $verify = password_verify($password, $hash) ? '✓ Совпадает' : '✗ Не совпадает';
    echo "{$email}: {$verify}\n";
}
?>