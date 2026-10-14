<?php
declare(strict_types=1);
function database_connect(): PDO
{
    return new PDO(
        sprintf(
            "pgsql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST") ?: "db",
            getenv("DB_PORT") ?: "5432",
            getenv("DB_NAME") ?: "taskflow",
        ),
        getenv("DB_USER") ?: "taskflow",
        getenv("DB_PASSWORD") ?: "taskflow_local",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );
}
