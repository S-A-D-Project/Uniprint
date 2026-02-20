<?php

namespace App\Services;

use Libsql\Database;

class TursoService
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database(
            url: env('TURSO_URL', 'libsql://uniprint-bragas002.aws-us-east-1.turso.io'),
            authToken: env('TURSO_AUTH_TOKEN', 'eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJhIjoicnciLCJpYXQiOjE3NzE1OTAwNDIsImlkIjoiYWFkNzE1ZDMtZWRiMi00NjQzLWI0ZDgtMGQxY2FiMmRlMGYzIiwicmlkIjoiNDg4OTNlNDctZDU5Ny00N2ZiLWIwYzgtMmFiN2NmYmVjYWU3In0.Sao0pGNjSMqTe7FtwAn6GKxCtTQxt8l74SbP0ALJlCFOnGA-68TdF8-Mtf9fYFGsn0XjVu68xyFKbwElJXa4DA')
        );
    }

    public function execute(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function query(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params);
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $this->execute($sql, array_values($data));
        return $this->db->lastInsertRowId();
    }

    public function update(string $table, array $data, array $where): int
    {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $whereClause = implode(' = ? AND ', array_keys($where)) . ' = ?';
        $sql = "UPDATE $table SET $setClause WHERE $whereClause";
        
        $params = array_merge(array_values($data), array_values($where));
        $result = $this->execute($sql, $params);
        return $result['rowsAffected'] ?? 0;
    }

    public function delete(string $table, array $where): int
    {
        $whereClause = implode(' = ? AND ', array_keys($where)) . ' = ?';
        $sql = "DELETE FROM $table WHERE $whereClause";
        
        $result = $this->execute($sql, array_values($where));
        return $result['rowsAffected'] ?? 0;
    }

    public function select(string $table, array $where = [], string $select = '*'): array
    {
        $sql = "SELECT $select FROM $table";
        $params = [];
        
        if (!empty($where)) {
            $whereClause = implode(' = ? AND ', array_keys($where)) . ' = ?';
            $sql .= " WHERE $whereClause";
            $params = array_values($where);
        }
        
        return $this->execute($sql, $params);
    }
}
