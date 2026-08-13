<?php
/**
 * backend/helpers.php — funções utilitárias compartilhadas por todas as páginas.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

/* ------------------------------------------------------------------
 * BASE_URL — caminho do projeto no servidor.
 * No XAMPP fica "/crud-mundo"; com `php -S` na pasta do projeto fica "".
 * ----------------------------------------------------------------- */
$docroot  = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$raiz     = str_replace('\\', '/', dirname(__DIR__));
$basePath = '';
if ($docroot !== '') {
    $docroot = rtrim(str_replace('\\', '/', $docroot), '/');
    if (strpos($raiz, $docroot) === 0) {
        $basePath = substr($raiz, strlen($docroot));
    }
}
define('BASE_URL', rtrim($basePath, '/'));

/** Escapa texto para saída segura em HTML (previne XSS) */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Monta URL a partir da raiz do projeto */
function url(string $caminho): string
{
    return BASE_URL . '/' . ltrim($caminho, '/');
}

/** Redireciona (padrão Post/Redirect/Get) e encerra o script */
function redirect(string $caminho): void
{
    header('Location: ' . url($caminho));
    exit;
}

/* --------------------- Mensagens de feedback (flash) --------------------- */
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function flashes(): array
{
    $lista = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $lista;
}

/* --------------------------- Leitura do formulário ----------------------- */
function post(string $campo, string $padrao = ''): string
{
    return isset($_POST[$campo]) ? trim((string) $_POST[$campo]) : $padrao;
}

function postInt(string $campo): ?int
{
    $v = post($campo);
    return ($v === '' || !is_numeric($v)) ? null : (int) $v;
}

/** Garante que a requisição é POST (ex.: exclusões) */
function exigirPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Método não permitido.');
    }
}

/* ------------------------------- Validações ----------------------------- */
function obrigatorio(string $campo, string $rotulo, array &$erros): string
{
    $valor = post($campo);
    if ($valor === '') {
        $erros[] = "O campo <strong>$rotulo</strong> é obrigatório.";
    }
    return $valor;
}

/** Número ≥ 0; aceita "1234.56" e também "1.234,56" */
function numero(string $campo, string $rotulo, array &$erros): ?float
{
    $valor = post($campo);
    if (strpos($valor, ',') !== false) {
        $valor = str_replace(['.', ','], ['', '.'], $valor);
    }
    if ($valor === '') {
        $erros[] = "O campo <strong>$rotulo</strong> é obrigatório.";
        return null;
    }
    if (!is_numeric($valor) || (float) $valor < 0) {
        $erros[] = "O campo <strong>$rotulo</strong> deve ser um número maior ou igual a zero.";
        return null;
    }
    return (float) $valor;
}

function inteiro(string $campo, string $rotulo, array &$erros): ?int
{
    $v = numero($campo, $rotulo, $erros);
    if ($v === null) {
        return null;
    }
    if (floor($v) !== $v) {
        $erros[] = "O campo <strong>$rotulo</strong> deve ser um número inteiro.";
        return null;
    }
    return (int) $v;
}

/** Data opcional no formato ISO (input type="date"); null quando vazia */
function dataOpcional(string $campo, string $rotulo, array &$erros): ?string
{
    $valor = post($campo);
    if ($valor === '') {
        return null;
    }
    $d = DateTime::createFromFormat('Y-m-d', $valor);
    if (!$d || $d->format('Y-m-d') !== $valor) {
        $erros[] = "O campo <strong>$rotulo</strong> contém uma data inválida.";
        return null;
    }
    return $valor;
}

/* -------------------------------- Diversos ------------------------------- */
/** Gera <option>s a partir de consulta com colunas (id, nome) */
function opcoes(PDO $pdo, string $sql, $selecionado = null): string
{
    $html = '';
    try {
        $linhas = $pdo->query($sql);
    } catch (PDOException $ex) {
        return '';
    }
    foreach ($linhas as $l) {
        $sel = ($selecionado !== null && (string) $selecionado !== '' && (int) $selecionado === (int) $l['id']) ? ' selected' : '';
        $html .= '<option value="' . (int) $l['id'] . '"' . $sel . '>' . e($l['nome']) . '</option>';
    }
    return $html;
}

/** Formata número no padrão brasileiro (1.234.567) */
function nfmt($valor): string
{
    return number_format((float) $valor, 0, ',', '.');
}

/** Converte data ISO (Y-m-d) para o padrão brasileiro */
function fmtData(?string $iso): string
{
    if (!$iso) {
        return '—';
    }
    $d = DateTime::createFromFormat('Y-m-d', $iso);
    return $d ? $d->format('d/m/Y') : '—';
}
