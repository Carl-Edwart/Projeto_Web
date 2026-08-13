<?php
/**
 * backend/config/database.php — Conexão com o banco de dados
 *
 * Uso normal (entrega): MySQL (XAMPP/WAMP) com o banco `bd_mundo`.
 * Demonstração: se o MySQL não estiver disponível, cria/usar um SQLite
 * local (database/demo.sqlite) populado automaticamente — serve apenas
 * para pré-visualizar o sistema sem configurar nada.
 */

date_default_timezone_set('America/Sao_Paulo');

// --- Ajuste conforme o seu ambiente MySQL (padrão XAMPP) ---
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'bd_mundo';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
    } catch (PDOException $e) {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            http_response_code(500);
            exit('Não foi possível conectar ao MySQL. Verifique o XAMPP e o arquivo backend/config/database.php.');
        }
        $pdo = banco_demo($opcoes); // fallback para pré-visualização
    }

    return $pdo;
}

/** Banco de demonstração (SQLite) — mesmo esquema e mesmos dados do bd_mundo.sql */
function banco_demo(array $opcoes): PDO
{
    $arquivo = __DIR__ . '/../../database/demo.sqlite';
    $novo    = !file_exists($arquivo);

    $pdo = new PDO('sqlite:' . $arquivo, null, null, $opcoes);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($novo) {
        $pdo->exec("
            CREATE TABLE continentes (
                id_continente INTEGER PRIMARY KEY AUTOINCREMENT,
                nome          TEXT    NOT NULL UNIQUE,
                populacao     INTEGER NOT NULL,
                area_km2      REAL    NOT NULL,
                total_paises  INTEGER NOT NULL DEFAULT 0
            );
            CREATE TABLE governantes (
                id_governante   INTEGER PRIMARY KEY AUTOINCREMENT,
                nome            TEXT NOT NULL,
                partido_politico TEXT,
                data_nascimento TEXT,
                idade           INTEGER,
                inicio_mandato  TEXT,
                fim_mandato     TEXT
            );
            CREATE TABLE paises (
                id_pais       INTEGER PRIMARY KEY AUTOINCREMENT,
                id_continente INTEGER NOT NULL,
                id_governante INTEGER,
                nome          TEXT    NOT NULL UNIQUE,
                populacao     INTEGER NOT NULL,
                area_km2      REAL    NOT NULL,
                idioma        TEXT,
                clima         TEXT,
                regime_politico TEXT,
                moeda         TEXT,
                FOREIGN KEY (id_continente) REFERENCES continentes(id_continente) ON DELETE RESTRICT,
                FOREIGN KEY (id_governante) REFERENCES governantes(id_governante) ON DELETE SET NULL
            );
            CREATE TABLE cidades (
                id_cidade     INTEGER PRIMARY KEY AUTOINCREMENT,
                id_pais       INTEGER NOT NULL,
                id_governante INTEGER,
                nome          TEXT    NOT NULL,
                populacao     INTEGER NOT NULL,
                area_km2      REAL    NOT NULL,
                clima         TEXT,
                data_fundacao TEXT,
                FOREIGN KEY (id_pais)       REFERENCES paises(id_pais)           ON DELETE RESTRICT,
                FOREIGN KEY (id_governante) REFERENCES governantes(id_governante) ON DELETE SET NULL
            );
        ");
        require __DIR__ . '/semente.php';
        semear($pdo);
    }

    if (!defined('MODO_DEMO')) {
        define('MODO_DEMO', true);
    }
    return $pdo;
}
