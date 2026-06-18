<?php
/**
 * Главный файл приложения
 */

// Загружаем переменные окружения из файла .env или .env.local
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    $envPath = __DIR__ . '/.env.local';
}
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match('/^\'(.*)\'$/', $value, $matches)) {
            $value = $matches[1];
        }

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
        if (getenv($key) === false) {
            putenv("$key=$value");
        }
    }
}

// Запуск сессии
session_start();

// Устанавливаем UTF-8 для всего контента
header('Content-Type: text/html; charset=utf-8');

// Показываем ошибки в разработке
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Подключаем основные классы
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Logger.php';
require_once __DIR__ . '/app/Validator.php';
require_once __DIR__ . '/app/Security.php';
require_once __DIR__ . '/app/Router.php';
require_once __DIR__ . '/app/DatabaseMigration.php';
require_once __DIR__ . '/app/DemoDataSeeder.php';

// Подключаем модели
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Models/Post.php';
require_once __DIR__ . '/app/Models/Category.php';
require_once __DIR__ . '/app/Models/Media.php';
require_once __DIR__ . '/app/Models/Comment.php';
require_once __DIR__ . '/app/Models/Settings.php';

// Выполняем миграции при запуске
try {
    (new DatabaseMigration())->migrate();
} catch (Exception $e) {
    Logger::error('Database migration failed: ' . $e->getMessage());
}

// Создаём дефолтного администратора, если ещё нет
try {
    (new User())->ensureDefaultAdmin();
} catch (Exception $e) {
    Logger::error('Admin seeding failed: ' . $e->getMessage());
}

// Создаём демо-контент, если его ещё нет
try {
    if (getenv('SEED_DEMO_DATA') !== 'false') {
        (new DemoDataSeeder())->seed();
    }
} catch (Exception $e) {
    Logger::error('Demo data seeding failed: ' . $e->getMessage());
}

// Подключаем middleware
require_once __DIR__ . '/app/Middleware/AuthMiddleware.php';

// Подключаем контроллеры
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Controllers/PostController.php';
require_once __DIR__ . '/app/Controllers/CategoryController.php';
require_once __DIR__ . '/app/Controllers/MediaController.php';
require_once __DIR__ . '/app/Controllers/UserController.php';
require_once __DIR__ . '/app/Controllers/CommentController.php';
require_once __DIR__ . '/app/Controllers/SettingsController.php';
require_once __DIR__ . '/app/Controllers/FrontendController.php';

// Определяем маршруты
// Аутентификация
Router::get('/login', function() {
    (new AuthController())->loginPage();
});

Router::post('/login', function() {
    (new AuthController())->login();
});

Router::get('/register', function() {
    (new AuthController())->registerPage();
});

Router::post('/register', function() {
    (new AuthController())->register();
});

Router::get('/logout', function() {
    (new AuthController())->logout();
});

Router::get('/forgot-password', function() {
    (new AuthController())->forgotPassword();
});

Router::post('/forgot-password', function() {
    (new AuthController())->sendResetLink();
});

Router::get('/debug', function() {
    $config = require __DIR__ . '/config/database.php';
    $db = new User();
    $admin = $db->findByEmail('admin@example.com');

    header('Content-Type: text/plain; charset=utf-8');
    echo "APP_DEBUG=" . (getenv('APP_DEBUG') ?: 'false') . "\n";
    echo "DB_DRIVER=" . $config['driver'] . "\n";
    echo "DB_HOST=" . $config['host'] . "\n";
    echo "DB_NAME=" . $config['database'] . "\n";
    echo "DB_USER=" . $config['username'] . "\n";
    echo "ADMIN_EXISTS=" . ($admin ? 'yes' : 'no') . "\n";
    if ($admin) {
        echo "ADMIN_ID=" . $admin['id'] . "\n";
        echo "ADMIN_ROLE=" . $admin['role'] . "\n";
    }
});

// Админ-панель
Router::get('/admin/dashboard', function() {
    (new DashboardController())->index();
});

// Посты
Router::get('/admin/posts', function() {
    (new PostController())->index();
});

Router::get('/admin/posts/create', function() {
    (new PostController())->create();
});

Router::post('/admin/posts', function() {
    (new PostController())->store();
});

Router::get('/admin/posts/{id}/edit', function($id) {
    (new PostController())->edit($id);
});

Router::post('/admin/posts/{id}', function($id) {
    (new PostController())->update($id);
});

Router::get('/admin/posts/{id}/delete', function($id) {
    (new PostController())->delete($id);
});

Router::get('/admin/posts/{id}/publish', function($id) {
    (new PostController())->publish($id);
});

// Категории
Router::get('/admin/categories', function() {
    (new CategoryController())->index();
});

Router::get('/admin/categories/create', function() {
    (new CategoryController())->create();
});

Router::post('/admin/categories', function() {
    (new CategoryController())->store();
});

Router::get('/admin/categories/{id}/edit', function($id) {
    (new CategoryController())->edit($id);
});

Router::post('/admin/categories/{id}', function($id) {
    (new CategoryController())->update($id);
});

Router::get('/admin/categories/{id}/delete', function($id) {
    (new CategoryController())->delete($id);
});

// Медиабиблиотека
Router::get('/admin/media', function() {
    (new MediaController())->index();
});

Router::post('/admin/media/upload', function() {
    (new MediaController())->upload();
});

Router::post('/admin/media/{id}/delete', function($id) {
    (new MediaController())->delete($id);
});

Router::get('/api/media', function() {
    (new MediaController())->api();
});

Router::get('/api/media/{id}', function($id) {
    (new MediaController())->api($id);
});

// Пользователи
Router::get('/admin/users', function() {
    (new UserController())->index();
});

Router::get('/admin/users/create', function() {
    (new UserController())->create();
});

Router::post('/admin/users', function() {
    (new UserController())->store();
});

Router::get('/admin/users/{id}/edit', function($id) {
    (new UserController())->edit($id);
});

Router::post('/admin/users/{id}', function($id) {
    (new UserController())->update($id);
});

Router::get('/admin/users/{id}/delete', function($id) {
    (new UserController())->delete($id);
});

// Профиль пользователя
Router::get('/admin/profile', function() {
    (new UserController())->profile();
});

Router::post('/admin/profile', function() {
    (new UserController())->updateProfile($_SESSION['user_id']);
});

// Комментарии
Router::get('/admin/comments', function() {
    (new CommentController())->index();
});

Router::get('/admin/comments/{id}/approve', function($id) {
    (new CommentController())->approve($id);
});

Router::get('/admin/comments/{id}/reject', function($id) {
    (new CommentController())->reject($id);
});

Router::get('/admin/comments/{id}/delete', function($id) {
    (new CommentController())->delete($id);
});

// Настройки
Router::get('/admin/settings', function() {
    (new SettingsController())->index();
});

Router::post('/admin/settings', function() {
    (new SettingsController())->update();
});

// Фронтенд
Router::get('/', function() {
    (new FrontendController())->index();
});

Router::get('/post/{slug}', function($slug) {
    (new FrontendController())->post($slug);
});

Router::post('/post/{slug}', function($slug) {
    (new FrontendController())->post($slug);
});

Router::get('/category/{slug}', function($slug) {
    (new FrontendController())->category($slug);
});

Router::get('/search', function() {
    (new FrontendController())->search();
});

// Обработка запроса
Router::dispatch();
