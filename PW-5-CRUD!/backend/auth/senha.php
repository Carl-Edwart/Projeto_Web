<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';

$usuario = exigirAutenticacao(true);
$pdo = db();
$erros = [];
$obrigatoria = (int) $usuario['primeiro_acesso'] === 1;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();

    $senhaAtual = postSenha('senha_atual');
    $novaSenha = postSenha('nova_senha');
    $confirmacao = postSenha('confirmacao_senha');

    $st = $pdo->prepare('SELECT senha_hash FROM usuarios WHERE id_usuario = ? AND bloqueado = 0');
    $st->execute([(int) $usuario['id_usuario']]);
    $conta = $st->fetch();

    if (!$conta || !password_verify($senhaAtual, $conta['senha_hash'])) {
        $erros[] = 'A senha atual está incorreta.';
    }

    validarNovaSenha($novaSenha, $confirmacao, $senhaAtual, $erros);

    if (!$erros) {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare(
                'UPDATE usuarios
                 SET senha_hash = ?, tentativas_falhas = 0, bloqueado = 0, primeiro_acesso = 0,
                     atualizado_em = CURRENT_TIMESTAMP
                 WHERE id_usuario = ?'
            );
            $st->execute([$hash, (int) $usuario['id_usuario']]);
            registrarLog($pdo, (int) $usuario['id_usuario'], 'senha_alterada');
            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }

        flash('sucesso', 'Senha alterada com sucesso.');
        redirect('index.php');
    }
}

$titulo = $obrigatoria ? 'Troca obrigatória de senha' : 'Alterar senha';
$ativo = '';
$somenteTrocaSenha = $obrigatoria;
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <p class="auth-kicker">Conta de <?= e($usuario['nome']) ?></p>
        <h1><?= $obrigatoria ? '🔐 Troca obrigatória de senha' : '🔐 Alterar senha' ?></h1>
        <p class="subtitulo">
            <?= $obrigatoria
                ? 'Por segurança, defina uma senha pessoal antes de continuar.'
                : 'Mantenha sua conta protegida atualizando sua senha periodicamente.' ?>
        </p>
    </div>
</div>

<?php if ($erros): ?>
    <div class="alerta alerta-erro" role="alert">
        <div>
            <strong>Verifique os dados informados:</strong>
            <ul><?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?></ul>
        </div>
    </div>
<?php endif; ?>

<form method="post" class="cartao formulario senha-form" data-senha>
    <?= campoCsrf() ?>
    <div class="campo campo-total">
        <label for="senha_atual">Senha atual</label>
        <input type="password" id="senha_atual" name="senha_atual" required autocomplete="current-password">
    </div>

    <div class="campo">
        <label for="nova_senha">Nova senha <small>(mínimo de <?= SENHA_MINIMA ?> caracteres)</small></label>
        <input type="password" id="nova_senha" name="nova_senha" required minlength="<?= SENHA_MINIMA ?>" maxlength="72"
               autocomplete="new-password">
    </div>

    <div class="campo">
        <label for="confirmacao_senha">Confirmar nova senha</label>
        <input type="password" id="confirmacao_senha" name="confirmacao_senha" required minlength="<?= SENHA_MINIMA ?>" maxlength="72"
               autocomplete="new-password">
    </div>

    <div class="senha-requisitos campo-total">
        <strong>Requisitos:</strong> pelo menos <?= SENHA_MINIMA ?> caracteres, confirmação idêntica e diferente da senha atual.
    </div>

    <div class="form-acoes">
        <button type="submit" class="btn btn-primario">Alterar senha</button>
        <?php if (!$obrigatoria): ?><a class="btn btn-neutro" href="<?= e(url('index.php')) ?>">Cancelar</a><?php endif; ?>
    </div>
</form>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
