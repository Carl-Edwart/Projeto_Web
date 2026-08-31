<?php
require __DIR__ . '/backend/helpers.php';

$pdo = db();
$usuario = usuarioAtual();
if ($usuario) {
    redirect((int) $usuario['primeiro_acesso'] === 1 ? 'backend/auth/senha.php?obrigatoria=1' : 'index.php');
}

$login = '';
$erros = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();
    $login = post('login');
    $senha = postSenha('senha');

    if ($login === '') {
        $erros[] = 'Informe o usuário.';
    } elseif (mb_strlen($login) > 80) {
        $erros[] = 'O usuário informado é inválido.';
    }

    if (!$erros) {
        $pdo->beginTransaction();
        try {
            $sql = 'SELECT id_usuario, login, nome, senha_hash, tentativas_falhas, bloqueado, primeiro_acesso
                    FROM usuarios WHERE login = ?';
            if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $sql .= ' FOR UPDATE';
            }

            $st = $pdo->prepare($sql);
            $st->execute([$login]);
            $usuario = $st->fetch();

            if (!$usuario) {
                registrarLog($pdo, null, 'login_invalido');
                $pdo->commit();
                $erros[] = 'Usuário ou senha inválidos.';
            } elseif ((int) $usuario['bloqueado'] === 1) {
                registrarLog($pdo, (int) $usuario['id_usuario'], 'login_bloqueado');
                $pdo->commit();
                $erros[] = 'Este usuário está bloqueado. Procure o responsável pelo sistema.';
            } elseif (password_verify($senha, $usuario['senha_hash'])) {
                $st = $pdo->prepare(
                    'UPDATE usuarios
                     SET tentativas_falhas = 0, bloqueado = 0, atualizado_em = CURRENT_TIMESTAMP
                     WHERE id_usuario = ?'
                );
                $st->execute([(int) $usuario['id_usuario']]);

                if (password_needs_rehash($usuario['senha_hash'], PASSWORD_DEFAULT)) {
                    $st = $pdo->prepare('UPDATE usuarios SET senha_hash = ?, atualizado_em = CURRENT_TIMESTAMP WHERE id_usuario = ?');
                    $st->execute([password_hash($senha, PASSWORD_DEFAULT), (int) $usuario['id_usuario']]);
                }

                registrarLog($pdo, (int) $usuario['id_usuario'], 'login_sucesso');
                $pdo->commit();

                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['usuario_id'] = (int) $usuario['id_usuario'];
                $_SESSION['usuario_login'] = $usuario['login'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                redirect((int) $usuario['primeiro_acesso'] === 1 ? 'backend/auth/senha.php?obrigatoria=1' : 'index.php');
            } else {
                $tentativas = min(3, (int) $usuario['tentativas_falhas'] + 1);
                $bloqueado = $tentativas >= 3 ? 1 : 0;
                $st = $pdo->prepare(
                    'UPDATE usuarios
                     SET tentativas_falhas = ?, bloqueado = ?, atualizado_em = CURRENT_TIMESTAMP
                     WHERE id_usuario = ?'
                );
                $st->execute([$tentativas, $bloqueado, (int) $usuario['id_usuario']]);
                registrarLog($pdo, (int) $usuario['id_usuario'], 'login_invalido');

                if ($bloqueado) {
                    registrarLog($pdo, (int) $usuario['id_usuario'], 'usuario_bloqueado');
                }

                $pdo->commit();
                $erros[] = $bloqueado
                    ? 'Usuário bloqueado após 3 tentativas de senha inválida.'
                    : 'Usuário ou senha inválidos. Tentativas restantes: ' . (3 - $tentativas) . '.';
            }
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }
}

$flashes = flashes();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar · CRUD Mundo</title>
    <meta name="description" content="Acesso ao sistema CRUD Mundo.">
    <link rel="stylesheet" href="<?= e(url('frontend/css/style.css')) ?>">
    <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
    <script defer src="<?= e(url('frontend/js/app.js')) ?>"></script>
</head>
<body class="auth-page">
<main class="auth-layout">
    <section class="auth-card" aria-labelledby="titulo-login">
        <a class="auth-marca" href="<?= e(url('login.php')) ?>">
            <span class="auth-marca-icone">🌍</span>
            <span><b>CRUD</b> Mundo</span>
        </a>

        <div class="auth-cabecalho">
            <p class="auth-kicker">Área restrita</p>
            <h1 id="titulo-login">Entrar no sistema</h1>
            <p>Use suas credenciais para acessar os dados geográficos.</p>
        </div>

        <?php if (isset($_GET['logout'])): ?>
            <div class="alerta alerta-sucesso"><span>Sessão encerrada com segurança.</span></div>
        <?php endif; ?>

        <?php foreach ($flashes as $f): ?>
            <div class="alerta alerta-<?= e($f['tipo']) ?>"><span><?= e($f['mensagem']) ?></span></div>
        <?php endforeach; ?>

        <?php if ($erros): ?>
            <div class="alerta alerta-erro" role="alert">
                <div>
                    <strong>Não foi possível entrar.</strong>
                    <ul><?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?></ul>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" class="auth-form" data-login>
            <?= campoCsrf() ?>
            <div class="campo">
                <label for="login">Usuário</label>
                <input type="text" id="login" name="login" required maxlength="80" autocomplete="username"
                       value="<?= e($login) ?>" placeholder="Digite seu usuário">
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required autocomplete="current-password"
                       placeholder="Digite sua senha">
            </div>

            <button type="submit" class="btn btn-primario btn-entrar">Entrar</button>
        </form>

        <p class="auth-rodape">O acesso é protegido por sessão e registro de eventos.</p>
    </section>
</main>
</body>
</html>
