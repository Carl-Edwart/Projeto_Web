<?php
/**
 * backend/helpers.php — funções utilitárias compartilhadas por todas as páginas.
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('crud_mundo_session');

    $https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/config/database.php';

const SENHA_MINIMA = 8;

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

/* --------------------------- Autenticação ----------------------------- */
function usuarioIdSessao(): ?int
{
    $id = filter_var($_SESSION['usuario_id'] ?? null, FILTER_VALIDATE_INT);
    return $id !== false && $id !== null && $id > 0 ? (int) $id : null;
}

function limparDadosAutenticacao(): void
{
    unset($_SESSION['usuario_id'], $_SESSION['usuario_login'], $_SESSION['usuario_nome']);
}

/** Busca a conta da sessão sem expor o hash da senha para as páginas. */
function usuarioAtual(): ?array
{
    $id = usuarioIdSessao();
    if ($id === null) {
        return null;
    }

    $st = db()->prepare(
        'SELECT id_usuario, login, nome, tentativas_falhas, bloqueado, primeiro_acesso, criado_em, atualizado_em
         FROM usuarios WHERE id_usuario = ?'
    );
    $st->execute([$id]);
    $usuario = $st->fetch();

    if (!$usuario || (int) $usuario['bloqueado'] === 1) {
        limparDadosAutenticacao();
        return null;
    }

    return $usuario;
}

/** Protege páginas do sistema e impede o bypass do primeiro acesso pela URL. */
function exigirAutenticacao(bool $permitirPrimeiroAcesso = false): array
{
    $usuario = usuarioAtual();
    if (!$usuario) {
        flash('erro', 'Faça login para acessar o sistema.');
        redirect('login.php');
    }

    if (!$permitirPrimeiroAcesso && (int) $usuario['primeiro_acesso'] === 1) {
        flash('info', 'Troque sua senha para continuar.');
        redirect('backend/auth/senha.php?obrigatoria=1');
    }

    return $usuario;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function exigirCsrf(): void
{
    $enviado  = $_POST['csrf_token'] ?? '';
    $esperado = $_SESSION['csrf_token'] ?? '';

    if (!is_string($enviado) || !is_string($esperado) || $enviado === '' || $esperado === '' || !hash_equals($esperado, $enviado)) {
        http_response_code(403);
        exit('Token de segurança inválido. Recarregue a página e tente novamente.');
    }
}

function registrarLog(PDO $pdo, ?int $usuarioId, string $evento): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = null;
    }

    $st = $pdo->prepare('INSERT INTO logs (id_usuario, evento, ip) VALUES (?, ?, ?)');
    $st->execute([$usuarioId, $evento, $ip]);
}

function destruirSessao(): void
{
    $_SESSION = [];
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], (bool) $parametros['secure'], (bool) $parametros['httponly']);
    session_destroy();
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
    return isset($_POST[$campo]) && is_scalar($_POST[$campo]) ? trim((string) $_POST[$campo]) : $padrao;
}

/** Lê senha sem trim: espaços podem fazer parte intencionalmente da senha. */
function postSenha(string $campo): string
{
    return isset($_POST[$campo]) && is_string($_POST[$campo]) ? $_POST[$campo] : '';
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
    exigirCsrf();
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

function validarNovaSenha(string $nova, string $confirmacao, string $atual, array &$erros): void
{
    if ($nova === '') {
        $erros[] = 'Informe a nova senha.';
    } elseif (mb_strlen($nova) < SENHA_MINIMA) {
        $erros[] = 'A nova senha deve ter pelo menos ' . SENHA_MINIMA . ' caracteres.';
    } elseif (mb_strlen($nova) > 72) {
        $erros[] = 'A nova senha deve ter no máximo 72 caracteres.';
    }

    if ($confirmacao === '') {
        $erros[] = 'Confirme a nova senha.';
    } elseif ($nova !== $confirmacao) {
        $erros[] = 'A confirmação não coincide com a nova senha.';
    }

    if ($nova !== '' && $nova === $atual) {
        $erros[] = 'A nova senha deve ser diferente da senha atual.';
    }
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
