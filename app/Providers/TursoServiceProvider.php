<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Libsql\Database;

class TursoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('turso', function ($app) {
            return new Database(
                url: env('TURSO_URL', 'libsql://uniprint-bragas002.aws-us-east-1.turso.io'),
                authToken: env('TURSO_AUTH_TOKEN', 'eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJhIjoicnciLCJpYXQiOjE3NzE1OTAwNDIsImlkIjoiYWFkNzE1ZDMtZWRiMi00NjQzLWI0ZDgtMGQxY2FiMmRlMGYzIiwicmlkIjoiNDg4OTNlNDctZDU5Ny00N2ZiLWIwYzgtMmFiN2NmYmVjYWU3In0.Sao0pGNjSMqTe7FtwAn6GKxCtTQxt8l74SbP0ALJlCFOnGA-68TdF8-Mtf9fYFGsn0XjVu68xyFKbwElJXa4DA')
            );
        });
    }
}
