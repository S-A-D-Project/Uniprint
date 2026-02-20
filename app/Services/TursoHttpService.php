<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TursoHttpService
{
    private string $url;
    private string $authToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->url = env('TURSO_URL', 'libsql://uniprint-bragas002.aws-us-east-1.turso.io');
        $this->authToken = env('TURSO_AUTH_TOKEN', 'eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJhIjoicnciLCJpYXQiOjE3NzE1OTAwNDIsImlkIjoiYWFkNzE1ZDMtZWRiMi00NjQzLWI0ZDgtMGQxY2FiMmRlMGYzIiwicmlkIjoiNDg4OTNlNDctZDU5Ny00N2ZiLWIwYzgtMmFiN2NmYmVjYWU3In0.Sao0pGNjSMqTe7FtwAn6GKxCtTQxt8l74SbP0ALJlCFOnGA-68TdF8-Mtf9fYFGsn0XjVu68xyFKbwElJXa4DA');
        
        // Convert libsql:// to https:// for HTTP API
        $this->baseUrl = str_replace('libsql://', 'https://', $this->url);
    }

    public function execute(string $sql, array $params = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v2/pipeline', [
            'requests' => [
                [
                    'type' => 'execute',
                    'stmt' => [
                        'sql' => $sql,
                        'args' => $params
                    ]
                ]
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Turso query failed: ' . $response->body());
        }

        $data = $response->json();
        $result = $data['results'][0]['response']['result'] ?? [];
        
        // Transform the result to a more usable format
        if (isset($result['rows']) && isset($result['cols'])) {
            $rows = [];
            foreach ($result['rows'] as $row) {
                $formattedRow = [];
                foreach ($result['cols'] as $index => $col) {
                    $formattedRow[$col['name']] = $row[$index];
                }
                $rows[] = $formattedRow;
            }
            return $rows;
        }
        
        return $result;
    }

    public function query(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params);
    }

    public function select(string $table, array $where = [], string $select = '*'): array
    {
        $sql = "SELECT $select FROM $table";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        return $this->execute($sql, $params);
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $this->execute($sql, array_values($data));
        
        // Get last insert ID
        $result = $this->execute("SELECT last_insert_rowid() as id");
        return $result[0]['id'] ?? 0;
    }

    public function update(string $table, array $data, array $where): int
    {
        $setClause = [];
        foreach ($data as $column => $value) {
            $setClause[] = "$column = ?";
        }
        
        $whereClause = [];
        foreach ($where as $column => $value) {
            $whereClause[] = "$column = ?";
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);
        
        $params = array_merge(array_values($data), array_values($where));
        $this->execute($sql, $params);
        
        // Return affected rows (simplified)
        return count($where) > 0 ? 1 : 0;
    }

    public function delete(string $table, array $where): int
    {
        $whereClause = [];
        foreach ($where as $column => $value) {
            $whereClause[] = "$column = ?";
        }
        
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereClause);
        
        $this->execute($sql, array_values($where));
        
        // Return affected rows (simplified)
        return count($where) > 0 ? 1 : 0;
    }
}
