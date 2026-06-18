<?php
$pageTitle = 'Блог';
ob_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape($pageTitle) ?> - CMS</title>
    <link rel="stylesheet" href="/css/modern.css">
</head>
<body>
    <!-- НАВИГАЦИЯ -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="/" class="navbar-brand">📝 CMS Blog</a>
                <ul class="navbar-nav">
                    <li><a href="/" class="active">Главная</a></li>
                    <li><a href="/?page=1">Все посты</a></li>
                    <li><a href="/search">Поиск</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/admin/dashboard">Панель</a></li>
                        <li><a href="/logout">Выход</a></li>
                    <?php else: ?>
                        <li><a href="/login">Вход</a></li>
                        <li><a href="/register">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- БАННЕР -->
    <div style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 4rem 0; text-align: center;">
        <div class="container">
            <h1 style="color: white; margin-bottom: 1rem;">Добро пожаловать в наш блог</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Откройте для себя интересные статьи, новости и полезные советы от наших авторов</p>
        </div>
    </div>

    <!-- ОСНОВНОЙ КОНТЕНТ -->
    <div class="container" style="margin: 3rem auto;">
        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem;">
            <!-- СЕТКА ПОСТОВ -->
            <main>
                <h2 style="margin-bottom: 2rem;">Последние статьи</h2>
                <?php
                    $categoryPlaceholders = [
                        'tekhnologii' => ['image' => '/images/categories/technology.svg', 'label' => 'Технологии', 'color' => '#2563eb'],
                        'biznes' => ['image' => '/images/categories/business.svg', 'label' => 'Бизнес', 'color' => '#0f766e'],
                        'poleznye-sovety' => ['image' => '/images/categories/tips.svg', 'label' => 'Полезные советы', 'color' => '#7c3aed'],
                        'novosti' => ['image' => '/images/categories/news.svg', 'label' => 'Новости', 'color' => '#dc2626'],
                        'obuchenie' => ['image' => '/images/categories/education.svg', 'label' => 'Обучение', 'color' => '#f59e0b'],
                    ];
                ?>
                <?php if (empty($posts)): ?>
                    <div class="alert alert-info">
                        Статей пока не опубликовано
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                        <?php foreach ($posts as $post): ?>
                            <article class="card">
                                <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1.5rem;">
                                    <?php
                                        $placeholder = $categoryPlaceholders[$post['category_slug'] ?? ''] ?? null;
                                    ?>
                                    <div style="background: <?= $placeholder['color'] ?? '#2563eb' ?>; border-radius: 0.75rem; height: 150px; display: flex; align-items: center; justify-content: center; color: white;">
                                        <?php if ($placeholder): ?>
                                            <img src="<?= Security::escape($placeholder['image']) ?>" alt="<?= Security::escape($placeholder['label']) ?>" style="width: 70px; height: 70px; object-fit: contain;">
                                        <?php else: ?>
                                            <span style="font-size: 3rem;">📄</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 style="margin-bottom: 0.5rem;">
                                            <a href="/post/<?= Security::escape($post['slug']) ?>">
                                                <?= Security::escape($post['title']) ?>
                                            </a>
                                        </h3>
                                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.875rem; color: #64748b;">
                                            <span>✍️ <?= Security::escape($post['author_name'] ?? 'Автор') ?></span>
                                            <span>📅 <?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                                            <?php if (!empty($post['category_name']) && !empty($post['category_slug'])): ?>
                                                <span><a href="/category/<?= Security::escape($post['category_slug']) ?>"><?= Security::escape($post['category_name']) ?></a></span>
                                            <?php elseif (!empty($post['category_name'])): ?>
                                                <span><?= Security::escape($post['category_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <p style="color: #64748b; margin-bottom: 1rem;">
                                            <?= Security::escape(substr(strip_tags($post['content']), 0, 200)) ?>...
                                        </p>
                                        <a href="/post/<?= Security::escape($post['slug']) ?>" class="btn btn-primary btn-sm">Читать</a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>

            <!-- САЙДБАР -->
            <aside>
                <!-- ПОИСК -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <h5 style="margin-bottom: 1rem;">🔍 Поиск</h5>
                    <form action="/search" method="get">
                        <div class="form-group" style="margin-bottom: 0;">
                            <input type="search" name="q" placeholder="Найти статью..." required>
                        </div>
                    </form>
                </div>

                <!-- КАТЕГОРИИ -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <h5 style="margin-bottom: 1rem;">📂 Категории</h5>
                    <ul style="list-style: none;">
                        <?php foreach ($categories as $cat): ?>
                            <li style="margin-bottom: 0.5rem;">
                                <a href="/category/<?= Security::escape($cat['slug']) ?>" style="display: block; padding: 0.5rem; border-radius: 0.375rem; transition: background 0.3s; color: #2563eb;">
                                    <?= Security::escape($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- ИНФОРМАЦИЯ -->
                <div class="card" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; border: none;">
                    <h5 style="margin-bottom: 1rem; color: white;">ℹ️ О сайте</h5>
                    <p style="font-size: 0.9rem; color: rgba(255,255,255,0.9);">
                        Современная система управления контентом для публикации и организации статей, новостей и информационного материала.
                    </p>
                </div>
            </aside>
        </div>
    </div>

    <!-- ФУТЕР -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h5>О сайте</h5>
                    <ul>
                        <li><a href="/">Главная</a></li>
                        <li><a href="/?page=1">Все статьи</a></li>
                        <li><a href="/search">Поиск</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h5>Категории</h5>
                    <ul>
                        <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                            <li><a href="/category/<?= Security::escape($cat['slug']) ?>"><?= Security::escape($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h5>Учётная запись</h5>
                    <ul>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="/admin/profile">Профиль</a></li>
                            <li><a href="/logout">Выход</a></li>
                        <?php else: ?>
                            <li><a href="/login">Вход</a></li>
                            <li><a href="/register">Регистрация</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 CMS Blog. Все права защищены.</p>
            </div>
        </div>
    </footer>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>
