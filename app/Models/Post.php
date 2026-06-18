<?php
/**
 * Модель Post
 */

class Post
{
    protected $db;
    protected $table = 'posts';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create($data)
    {
        $data['author_id'] = $_SESSION['user_id'] ?? null;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update($this->table, $data, 'id = ?', [$id]);
    }

    public function delete($id)
    {
        $this->db->delete($this->table, 'id = ?', [$id]);
    }

    public function publish($id)
    {
        $this->update($id, [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function unpublish($id)
    {
        $this->update($id, ['status' => 'draft']);
    }

    public function findById($id)
    {
        return $this->db->fetchOne(
            "SELECT p.*, u.name as author_name 
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             WHERE p.id = ? LIMIT 1",
            [$id]
        );
    }

    public function getPublished($limit = 10, $offset = 0)
    {
        return $this->db->fetchAll(
            "SELECT p.*, u.name as author_name, c.slug as category_slug, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'published'
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function getByCategory($categoryId, $limit = 10, $offset = 0)
    {
        return $this->db->fetchAll(
            "SELECT p.*, u.name as author_name
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             WHERE p.category_id = ? AND p.status = 'published'
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?",
            [$categoryId, $limit, $offset]
        );
    }

    public function getByAuthor($authorId, $includeUnpublished = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE author_id = ?";
        $params = [$authorId];

        if (!$includeUnpublished) {
            $sql .= " AND status = 'published'";
        }

        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function search($query, $limit = 10, $offset = 0)
    {
        return $this->db->fetchAll(
            "SELECT p.*, u.name as author_name
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             WHERE (p.title LIKE ? OR p.content LIKE ?) AND p.status = 'published'
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?",
            ['%' . $query . '%', '%' . $query . '%', $limit, $offset]
        );
    }

    public function getDrafts($limit = 10, $offset = 0)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE status = 'draft'
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT p.*, u.name as author_name, c.name as category_name
                FROM {$this->table} p
                LEFT JOIN users u ON p.author_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }
        return $this->db->fetchAll($sql);
    }

    public function count($status = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if ($status) {
            $sql .= " WHERE status = ?";
            $params = [$status];
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] ?? 0;
    }

    public function countPublished()
    {
        return $this->count('published');
    }
}
