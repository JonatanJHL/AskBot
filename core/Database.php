<?php
/**
 * AskBot Database - Conexión a base de datos
 */

class Database {
    private $host;
    private $user;
    private $pass;
    private $name;
    private $conn;
    private $stmt;
    private $lastSql;

    public function __construct($config = []) {
        if (!empty($config)) {
            $this->host = $config['host'] ?? 'localhost';
            $this->user = $config['user'] ?? 'root';
            $this->pass = $config['password'] ?? '';
            $this->name = $config['database'] ?? 'askbot';
        } else {
            $this->host = 'localhost';
            $this->user = 'root';
            $this->pass = '';
            $this->name = 'askbot';
        }
    }

    public function connect() {
        if ($this->conn) return $this->conn;

        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);
        
        if ($this->conn->connect_error) {
            throw new Exception("Conexión fallida: " . $this->conn->connect_error);
        }

        $this->conn->set_charset('utf8mb4');
        return $this->conn;
    }

    public function query($sql, $params = []) {
        $this->connect();
        $this->lastSql = $sql;
        
        $this->stmt = $this->conn->prepare($sql);
        
        if (!$this->stmt) {
            throw new Exception("Error en prepare: " . $this->conn->error);
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $this->stmt->bind_param($types, ...$params);
        }

        $this->stmt->execute();
        
        return new Result($this->stmt);
    }

    public function select_db($database) {
        $this->connect();
        return $this->conn->select_db($database);
    }

    public function multi_query($sql) {
        $this->connect();
        return $this->conn->multi_query($sql);
    }

    public function getLastInsertId() {
        return $this->conn->insert_id;
    }

    public function getError() {
        return $this->conn->error;
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

class Result {
    private $stmt;
    private $data = [];
    private $fetched = false;

    public function __construct($stmt) {
        $this->stmt = $stmt;
        $this->data = $stmt->get_result();
    }

    public function fetch() {
        if (!$this->data) {
            return $this->stmt->fetch() ? $this->getParams() : null;
        }
        return $this->data->fetch_assoc();
    }

    public function fetchAll() {
        if (!$this->data) {
            $rows = [];
            while ($row = $this->stmt->fetch()) {
                $rows[] = $this->getParams();
            }
            return $rows;
        }
        
        $rows = [];
        while ($row = $this->data->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    private function getParams() {
        $meta = $this->stmt->result_metadata();
        $params = [];
        $meta_data = $meta->fetch_fields();
        
        foreach ($meta_data as $field) {
            $params[$field->name] = null;
        }
        
        $this->stmt->bind_result(...array_keys($params));
        
        if ($this->stmt->fetch()) {
            return array_values($params);
        }
        
        return null;
    }

    public function num_rows() {
        return $this->data ? $this->data->num_rows : $this->stmt->num_rows;
    }
}