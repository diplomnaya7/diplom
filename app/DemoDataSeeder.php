<?php
/**
 * Скрипт инициализации демо-контента
 */

class DemoDataSeeder
{
    private $db;
    private $postModel;
    private $categoryModel;
    private $userModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->postModel = new Post();
        $this->categoryModel = new Category();
        $this->userModel = new User();
    }

    public function seed()
    {
        try {
            Logger::info('Starting demo data seeding...');
            
            // Создаём категории
            $this->seedCategories();
            
            // Создаём автора
            $authorId = $this->seedAuthor();
            
            // Создаём посты
            $this->seedPosts($authorId);
            
            Logger::info('Demo data seeding completed successfully');
            return true;
        } catch (Exception $e) {
            Logger::error('Demo data seeding failed: ' . $e->getMessage());
            return false;
        }
    }

    private function seedCategories()
    {
        $categories = [
            ['name' => 'Технологии', 'slug' => 'technology'],
            ['name' => 'Бизнес', 'slug' => 'business'],
            ['name' => 'Полезные советы', 'slug' => 'tips'],
            ['name' => 'Новости', 'slug' => 'news'],
            ['name' => 'Обучение', 'slug' => 'tutorials'],
        ];

        foreach ($categories as $cat) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM categories WHERE slug = ? LIMIT 1",
                [$cat['slug']]
            );
            if (!$existing) {
                $this->categoryModel->create($cat);
                Logger::info("Category created: {$cat['name']}");
            }
        }
    }

    private function seedAuthor()
    {
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = 'author@example.com' LIMIT 1"
        );
        
        if ($existing) {
            return $existing['id'];
        }

        return $this->userModel->create([
            'name' => 'Главный автор',
            'email' => 'author@example.com',
            'password' => 'Password123',
            'role' => 'editor',
            'status' => 'active',
        ]);
    }

    private function seedPosts($authorId)
    {
        $categories = $this->db->fetchAll("SELECT * FROM categories");
        $categoryIds = array_column($categories, 'id');

        $posts = [
            [
                'title' => 'Как начать работать с современными фреймворками',
                'slug' => 'kak-nachat-s-frameworkami',
                'content' => '<p>В современной веб-разработке фреймворки стали неотъемлемой частью процесса создания приложений. Они предоставляют готовые решения для большинства задач, что значительно ускоряет разработку.</p>

<h3>Выбор правильного фреймворка</h3>
<p>При выборе фреймворка необходимо учитывать:</p>
<ul>
<li>Размер и сложность проекта</li>
<li>Опыт команды</li>
<li>Производительность</li>
<li>Экосистема и сообщество</li>
</ul>

<h3>Начало работы</h3>
<p>Первый шаг - это установка фреймворка. Большинство современных фреймворков поставляются с инструментами для быстрого создания проектов.</p>

<h3>Лучшие практики</h3>
<p>Следуйте рекомендациям фреймворка, используйте встроенные инструменты и придерживайтесь паттернов проектирования.</p>',
                'excerpt' => 'Руководство для начинающих разработчиков по выбору и использованию фреймворков.',
                'category_id' => $categoryIds[0] ?? 1,
                'author_id' => $authorId,
                'status' => 'published',
            ],
            [
                'title' => 'Стратегии масштабирования бизнеса в 2026 году',
                'slug' => 'strategii-masshtabirovaniya-biznesa',
                'content' => '<p>Масштабирование бизнеса - это ключевой момент в развитии компании. Правильная стратегия может увеличить доход и расширить рынок.</p>

<h3>Анализ рынка</h3>
<p>Перед масштабированием необходимо провести тщательный анализ рынка:</p>
<ul>
<li>Изучить конкурентов</li>
<li>Определить целевую аудиторию</li>
<li>Оценить спрос на услугу</li>
<li>Расчитать финансовые ресурсы</li>
</ul>

<h3>Методы масштабирования</h3>
<p>Существует несколько способов расширения бизнеса: открытие филиалов, запуск новых продуктов или выход на новые географические рынки.</p>',
                'excerpt' => 'Рассмотрим основные стратегии и методы успешного масштабирования компании.',
                'category_id' => $categoryIds[1] ?? 2,
                'author_id' => $authorId,
                'status' => 'published',
            ],
            [
                'title' => '10 полезных советов для повышения продуктивности',
                'slug' => '10-sovetov-produktivnosti',
                'content' => '<p>Продуктивность - это искусство максимально эффективно использовать своё время. Вот несколько проверенных советов.</p>

<h3>Совет 1: Планирование</h3>
<p>Начните день с планирования. Определите главные задачи, которые необходимо выполнить.</p>

<h3>Совет 2: Техника Pomodoro</h3>
<p>Работайте 25 минут, потом отдохните 5 минут. Это помогает поддерживать концентрацию.</p>

<h3>Совет 3: Устраняйте отвлечения</h3>
<p>Отключите уведомления, закройте ненужные вкладки браузера.</p>

<h3>Совет 4: Делегируйте</h3>
<p>Учитесь доверять работу другим. Это освобождает время для важных дел.</p>

<h3>Совет 5: Отдыхайте</h3>
<p>Регулярный отдых необходим для поддержания высокого уровня производительности.</p>',
                'excerpt' => 'Практические советы для увеличения продуктивности и эффективности работы.',
                'category_id' => $categoryIds[2] ?? 3,
                'author_id' => $authorId,
                'status' => 'published',
            ],
            [
                'title' => 'Последние тренды в веб-дизайне 2026',
                'slug' => 'trendy-veb-dizajna-2026',
                'content' => '<p>Веб-дизайн постоянно эволюционирует. Рассмотрим основные тренды, которые доминируют в 2026 году.</p>

<h3>Минимализм</h3>
<p>Чистый, простой дизайн с минимумом элементов остаётся популярным. Белое пространство становится полноценным элементом дизайна.</p>

<h3>Темная тема</h3>
<p>Dark mode становится стандартом. Многие пользователи предпочитают тёмные интерфейсы.</p>

<h3>Микро-взаимодействия</h3>
<p>Небольшие анимации и переходы улучшают пользовательский опыт.</p>

<h3>Адаптивный дизайн</h3>
<p>Mobile-first подход критически важен в современном вебе.</p>',
                'excerpt' => 'Обзор самых актуальных трендов в веб-дизайне и их применение.',
                'category_id' => $categoryIds[0] ?? 1,
                'author_id' => $authorId,
                'status' => 'published',
            ],
            [
                'title' => 'Как защитить свои данные в интернете',
                'slug' => 'kak-zashitit-dannie-internet',
                'content' => '<p>Безопасность в интернете - это критически важный аспект жизни в 2026 году. Вот основные принципы защиты.</p>

<h3>Сильные пароли</h3>
<p>Используйте пароли из 12+ символов, содержащие буквы, цифры и специальные символы.</p>

<h3>Двухфакторная аутентификация</h3>
<p>Включите 2FA где это возможно. Это добавляет дополнительный уровень защиты.</p>

<h3>Своевременные обновления</h3>
<p>Всегда обновляйте операционную систему и приложения.</p>

<h3>Антивирус и VPN</h3>
<p>Используйте качественный антивирус и VPN для защиты соединения.</p>

<h3>Проверка источников</h3>
<p>Не открывайте ссылки от неизвестных источников. Это главная входная точка для вирусов.</p>',
                'excerpt' => 'Практические советы для защиты ваших личных данных и конфиденциальности.',
                'category_id' => $categoryIds[4] ?? 5,
                'author_id' => $authorId,
                'status' => 'published',
            ],
            [
                'title' => 'Путеводитель по инструментам веб-разработчика',
                'slug' => 'putevoditel-instrumenty-web',
                'content' => '<p>Современный веб-разработчик использует множество инструментов. В этом материале мы рассмотрим самые полезные из них и как эффективно их применять.</p><h3>IDE и редакторы</h3><p>Выберите инструмент, который повышает вашу продуктивность — VS Code, PhpStorm или другие.</p>',
                'excerpt' => 'Обзор полезных инструментов и советов для веб-разработчиков.',
                'category_id' => $categoryIds[0] ?? 1,
                'author_id' => $authorId,
                'status' => 'published',
            ],
            [
                'title' => 'Планирование контент-стратегии для блога',
                'slug' => 'planirovanie-kontent-strategii',
                'content' => '<p>Контент-стратегия помогает систематизировать публикации и привлекать аудиторию. Разберём ключевые этапы планирования и примеры календаря публикаций.</p>',
                'excerpt' => 'Как спланировать контент для блога: темы, частота, метрики.',
                'category_id' => $categoryIds[1] ?? 2,
                'author_id' => $authorId,
                'status' => 'published',
            ],
        ];

        foreach ($posts as $post) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM posts WHERE slug = ? LIMIT 1",
                [$post['slug']]
            );
            if (!$existing) {
                $postId = $this->postModel->create($post);
                Logger::info("Post created: {$post['title']}");
            }
        }
    }
}
