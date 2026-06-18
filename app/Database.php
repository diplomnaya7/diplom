<?php
/**
 * Класс для работы с базой данных
 */

class Database
{
    private static $instance = null;
    private $pdo;
    private $config;

    private function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    public static function getInstance($config = null)
    {
        if (self::$instance === null) {
            if ($config === null) {
                $config = require __DIR__ . '/../config/database.php';
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    private function connect()
    {
        try {
            // Различная синтаксис DSN для разных СУБД
            if ($this->config['driver'] === 'pgsql') {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s',
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['database']
                );
            } else {
                // MySQL
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['database'],
                    $this->config['charset']
                );
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 20,
                PDO::ATTR_PERSISTENT => false,
            ];

            if ($this->config['driver'] === 'mysql') {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4';
                if (!empty(getenv('DB_SSL_CA'))) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA');
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }
                $options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
            }
            
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );
        } catch (PDOException $e) {
            $error = 'DB Connection Error: ' . $e->getMessage();
            Logger::error($error);
            // Выводим точную ошибку для отладки
            die($error . "\n\nПараметры: Host=" . $this->config['host'] . 
                ", Port=" . $this->config['port'] . 
                ", DB=" . $this->config['database'] . 
                ", User=" . $this->config['username']);
        }
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error('Query error: ' . $e->getMessage() . ' | Query: ' . $sql);
            throw new Exception('Ошибка выполнения запроса к базе данных');
        }
    }

    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert($table, $data)
    {
        $keys = array_keys($data);
        $placeholders = array_map(function($k) { return '?'; }, $keys);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(',', $keys),
            implode(',', $placeholders)
        );

        $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $params = [])
    {
        $set = [];
        $values = [];

        foreach ($data as $key => $value) {
            $set[] = "$key = ?";
            $values[] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $set),
            $where
        );

        $this->query($sql, array_merge($values, $params));
    }

    public function delete($table, $where, $params = [])
    {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
        $this->query($sql, $params);
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}
