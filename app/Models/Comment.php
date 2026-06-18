<?php
/**
 * Модель Comment
 */

class Comment
{
    protected $db;
    protected $table = 'comments';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function approve($id)
    {
        $this->db->update($this->table, ['status' => 'approved'], 'id = ?', [$id]);
    }

    public function reject($id)
    {
        $this->db->update($this->table, ['status' => 'rejected'], 'id = ?', [$id]);
    }

    public function delete($id)
    {
        $this->db->delete($this->table, 'id = ?', [$id]);
    }

    public function findById($id)
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    public function getByPost($postId, $approved = true)
    {
        $sql = "SELECT c.*, u.name as author_name
                FROM {$this->table} c
                LEFT JOIN users u ON c.author_id = u.id
                WHERE c.post_id = ?";
        $params = [$postId];

        if ($approved) {
            $sql .= " AND c.status = 'approved'";
        }

        $sql .= " ORDER BY c.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function countByPost($postId, $status = 'approved')
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} 
             WHERE post_id = ? AND status = ?",
            [$postId, $status]
        );
        return $result['count'] ?? 0;
    }

    public function getPending()
    {
        return $this->db->fetchAll(
            "SELECT c.*, p.title as post_title
             FROM {$this->table} c
             LEFT JOIN posts p ON c.post_id = p.id
             WHERE c.status = 'pending'
             ORDER BY c.created_at DESC"
        );
    }

    public function countPending()
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'pending'"
        );
        return $result['count'] ?? 0;
    }
    public function count()
{
    $result = $this->db->fetchOne(
        "SELECT COUNT(*) as count FROM {$this->table}"
    );

    return $result['count'] ?? 0;
}
}
