<?php
AuthMiddleware::checkAuth();

$pageTitle = 'Управление пользователями';
$users = (new User())->getAll(50);

ob_start();
?>

<h1>👥 Управление пользователями</h1>

<a href="/admin/users/create" class="btn btn-primary" style="margin-bottom: 1.5rem;">➕ Добавить пользователя</a>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Имя</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Дата регистрации</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><strong><?= Security::escape($user['name']) ?></strong></td>
                    <td><?= Security::escape($user['email']) ?></td>
                    <td>
                        <span style="display: inline-block; padding: 0.25rem 0.75rem; background: <?= $user['role'] === 'admin' ? '#dbeafe' : '#dcfce7' ?>; color: <?= $user['role'] === 'admin' ? '#0c4a6e' : '#166534' ?>; border-radius: var(--radius-sm); font-size: 0.875rem;">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-primary">Редактировать</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="/admin/users/<?= $user['id'] ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?');">Удалить</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>

