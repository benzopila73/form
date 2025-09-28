<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Обработка текстовых данных
    $food = htmlspecialchars(trim($_POST['food']));
    $name = htmlspecialchars(trim($_POST['name']));
    $auth_msg = htmlspecialchars(trim($_POST['auth_msg']));
    $password = htmlspecialchars(trim($_POST['password']));
    $cachestva = htmlspecialchars(trim($_POST['cachestva']));
    $zp = htmlspecialchars(trim($_POST['zp']));
    
    // Валидация обязательных полей
    $errors = [];
    
    if (empty($name)) $errors[] = "Имя обязательно для заполнения";
    if (empty($auth_msg)) $errors[] = "Сообщение авторизации обязательно";
    if (empty($password)) $errors[] = "Пароль обязателен";
    
    // Обработка загруженного файла
    $img_info = "Файл не загружен";
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['img']['name'];
        $file_size = $_FILES['img']['size'];
        $file_type = $_FILES['img']['type'];
        
        // Проверка типа файла
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($file_type, $allowed_types)) {
            // Создаем папку для загрузок, если её нет
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            // Генерируем уникальное имя файла
            $new_file_name = uniqid() . '_' . $file_name;
            $upload_path = 'uploads/' . $new_file_name;
            
            if (move_uploaded_file($_FILES['img']['tmp_name'], $upload_path)) {
                $img_info = "Файл успешно загружен: " . $file_name . " (" . round($file_size/1024, 2) . " KB)";
            } else {
                $img_info = "Ошибка при загрузке файла";
            }
        } else {
            $img_info = "Недопустимый тип файла. Разрешены только JPEG, PNG, GIF";
        }
    } elseif (isset($_FILES['img'])) {
        $img_info = "Ошибка загрузки: " . getUploadError($_FILES['img']['error']);
    }
    
    // Если есть ошибки - показываем их
    if (!empty($errors)) {
        echo "<h2>Ошибки:</h2>";
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
        echo "<a href='javascript:history.back()'>Вернуться назад</a>";
        exit;
    }
    
    // Формируем данные для email
    $to = "kirillpolkovnikov314@gmail.com"; // Замените на ваш email
    $subject = "Новые данные от $name";
    
    $body = "
    Новые данные из формы:
    
    Имя: $name
    Любимая еда: $food
    Сообщение авторизации: $auth_msg
    Пароль: $password
    Качества: $cachestva
    Желаемая зарплата: $zp руб.
    Изображение: $img_info
    
    Дата отправки: " . date('Y-m-d H:i:s') . "
    IP адрес: " . $_SERVER['REMOTE_ADDR'] . "
    ";
    
    $headers = "From: web-form@yoursite.com\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    // Отправка email
    if (mail($to, $subject, $body, $headers)) {
        // Сохранение в файл (дополнительно)
        saveToFile($name, $food, $auth_msg, $password, $cachestva, $zp, $img_info);
        
        // Перенаправление на страницу успеха
        header("Location: success.html");
        exit;
    } else {
        echo "Ошибка при отправке email. Но данные сохранены в файл.";
        saveToFile($name, $food, $auth_msg, $password, $cachestva, $zp, $img_info);
    }
    
} else {
    header("Location: index.html");
    exit;
}

// Функция для получения текста ошибки загрузки файла
function getUploadError($error_code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер',
        UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер формы',
        UPLOAD_ERR_PARTIAL => 'Файл загружен частично',
        UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
        UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
        UPLOAD_ERR_EXTENSION => 'Расширение PHP остановил
