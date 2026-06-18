<?php
/**
 * Контроллер фронтенда
 */

class FrontendController
{
    private $postModel;
    private $categoryModel;
    private $commentModel;
    private $settingsModel;
    private $db;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->categoryModel = new Category();
        $this->commentModel = new Comment();
        $this->settingsModel = new Settings();
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $config = require __DIR__ . '/../../config/app.php';
        $perPage = $config['pagination']['per_page'];
        $offset = ($page - 1) * $perPage;

        $posts = $this->postModel->getPublished($perPage, $offset);
        $total = $this->postModel->count('published');
        $totalPages = ceil($total / $perPage);

        $categories = $this->categoryModel->getWithPostCount();
        $recentPosts = array_slice($posts, 0, 5);

        require __DIR__ . '/../Views/frontend/index.php';
    }

    public function post($slug)
    {
        $post = $this->db->fetchOne(
            "SELECT p.*, u.name as author_name, c.slug as category_slug, c.name as category_name
             FROM posts p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ? AND p.status = 'published'
             LIMIT 1",
            [$slug]
        );

        if (!$post) {
            http_response_code(404);
            require __DIR__ . '/../Views/404.php';
            exit;
        }

        $comments = $this->commentModel->getByPost($post['id']);
        $commentCount = $this->commentModel->countByPost($post['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->addComment($post['id'], $slug);
        }

        require __DIR__ . '/../Views/frontend/post.php';
    }

    public function category($slug)
    {
        $category = $this->db->fetchOne(
            "SELECT * FROM categories WHERE slug = ? LIMIT 1",
            [$slug]
        );

        if (!$category) {
            http_response_code(404);
            require __DIR__ . '/../Views/404.php';
            exit;
        }

        $page = $_GET['page'] ?? 1;
        $config = require __DIR__ . '/../../config/app.php';
        $perPage = $config['pagination']['per_page'];
        $offset = ($page - 1) * $perPage;

        $posts = $this->postModel->getByCategory($category['id'], $perPage, $offset);
        $categories = $this->categoryModel->getWithPostCount();

        require __DIR__ . '/../Views/frontend/category.php';
    }

    public function search()
    {
        $query = $_GET['q'] ?? '';

        if (strlen($query) < 3) {
            $error = 'Поисковой запрос должен содержать минимум 3 символа';
            require __DIR__ . '/../Views/frontend/search.php';
            exit;
        }

        $page = $_GET['page'] ?? 1;
        $config = require __DIR__ . '/../../config/app.php';
        $perPage = $config['pagination']['per_page'];
        $offset = ($page - 1) * $perPage;

        $posts = $this->postModel->search($query, $perPage, $offset);
        $categories = $this->categoryModel->getWithPostCount();

        require __DIR__ . '/../Views/frontend/search.php';
    }

    private function addComment($postId, $slug)
    {
        if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
            die('CSRF токен недействителен');
        }

        $validator = new Validator($_POST);
        if (!$validator->validate([
            'author_name' => 'required|min:2',
            'author_email' => 'required|email',
            'content' => 'required|min:10',
        ])) {
            // Обработка ошибок валидации
            return;
        }

        $data = [
            'post_id' => $postId,
            'author_name' => $_POST['author_name'],
            'author_email' => $_POST['author_email'],
            'content' => $_POST['content'],
            'status' => 'pending',
        ];

        if (isset($_SESSION['user_id'])) {
            $data['author_id'] = $_SESSION['user_id'];
        }

        $this->commentModel->create($data);
        Logger::info("Comment added to post: $postId");

        header("Location: /post/{$slug}");
        exit;
    }
}
