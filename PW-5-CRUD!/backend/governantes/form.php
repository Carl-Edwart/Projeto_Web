<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
$pdo = db();

$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$erros = [];
$val   = [
    'nome' => '', 'partido_politico' => '', 'data_nascimento' => '',
    'idade' => '', 'inicio_mandato' => '', 'fim_mandato' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $val['nome']             = obrigatorio('nome', 'Nome', $erros);
    $val['partido_politico'] = post('partido_politico');
    $val['data_nascimento']  = post('data_nascimento');
    $val['idade']            = post('idade');
    $val['inicio_mandato']   = post('inicio_mandato');
    $val['fim_mandato']      = post('fim_mandato');

    $nascimento = dataOpcional('data_nascimento', 'Data de nascimento', $erros);
    $inicio     = dataOpcional('inicio_mandato', 'Início do mandato', $erros);
    $fim        = dataOpcional('fim_mandato', 'Fim do mandato', $erros);

    $idade = null;
    if (post('idade') !== '') {
        $idade = inteiro('idade', 'Idade', $erros);
        if ($idade !== null && $idade > 130) {
            $erros[] = 'A <strong>Idade</strong> informada parece incorreta.';
            $idade = null;
        }
    }

    if ($inicio && $fim && $fim < $inicio) {
        $erros[] = 'O <strong>fim do mandato</strong> não pode ser anterior ao início.';
    }

    if (!$erros) {
        if ($id) {
            $st = $pdo->prepare(
                'UPDATE governantes SET nome = ?, partido_politico = ?, data_nascimento = ?, idade = ?, inicio_mandato = ?, fim_mandato = ?
                 WHERE id_governante = ?'
            );
            $st->execute([$val['nome'], $val['partido_politico'] ?: null, $nascimento, $idade, $inicio, $fim, $id]);
            flash('sucesso', 'Governante atualizado com sucesso. ✅');
        } else {
            $st = $pdo->prepare(
                'INSERT INTO governantes (nome, partido_politico, data_nascimento, idade, inicio_mandato, fim_mandato)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $st->execute([$val['nome'], $val['partido_politico'] ?: null, $nascimento, $idade, $inicio, $fim]);
            flash('sucesso', 'Governante cadastrado com sucesso. 🎉');
        }
        redirect('backend/governantes/index.php');
    }
} elseif ($id) {
    $st = $pdo->prepare('SELECT * FROM governantes WHERE id_governante = ?');
    $st->execute([$id]);
    $val = $st->fetch();
    if (!$val) {
        flash('erro', 'Governante não encontrado.');
        redirect('backend/governantes/index.php');
    }
}

$titulo = $id ? 'Editar governante' : 'Novo governante';
$ativo  = 'governantes';
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <h1><?= $id ? '✏️ Editar governante' : '＋ Novo governante' ?></h1>
        <p class="subtitulo">Depois de cadastrar, vincule-o a um país ou cidade nos respectivos formulários.</p>
    </div>
</div>

<?php if ($erros): ?>
    <div class="alerta alerta-erro">
        <div>
            <strong>Verifique o formulário:</strong>
            <ul><?php foreach ($erros as $erro): ?><li><?= $erro ?></li><?php endforeach; ?></ul>
        </div>
    </div>
<?php endif; ?>

<form method="post" class="cartao formulario" data-validar>
    <div class="campo campo-total">
        <label for="nome">Nome *</label>
        <input type="text" id="nome" name="nome" required maxlength="120"
               value="<?= e($val['nome']) ?>" placeholder="Ex.: Luiz Inácio Lula da Silva">
    </div>

    <div class="campo">
        <label for="partido_politico">Partido político</label>
        <input type="text" id="partido_politico" name="partido_politico" maxlength="80"
               value="<?= e($val['partido_politico']) ?>" placeholder="Ex.: PT">
    </div>

    <div class="campo">
        <label for="data_nascimento">Data de nascimento <small>(a idade é calculada automaticamente)</small></label>
        <input type="date" id="data_nascimento" name="data_nascimento" data-max-hoje
               value="<?= e($val['data_nascimento']) ?>">
    </div>

    <div class="campo">
        <label for="idade">Idade</label>
        <input type="number" id="idade" name="idade" data-rotulo="Idade" min="0" max="130" step="1"
               value="<?= e($val['idade']) ?>" placeholder="Ex.: 78">
    </div>

    <div class="campo">
        <label for="inicio_mandato">Início do mandato</label>
        <input type="date" id="inicio_mandato" name="inicio_mandato"
               value="<?= e($val['inicio_mandato']) ?>">
    </div>

    <div class="campo">
        <label for="fim_mandato">Fim do mandato</label>
        <input type="date" id="fim_mandato" name="fim_mandato"
               value="<?= e($val['fim_mandato']) ?>">
    </div>

    <div class="form-acoes">
        <button type="submit" class="btn btn-primario">💾 Salvar</button>
        <a class="btn btn-neutro" href="index.php">Cancelar</a>
    </div>
</form>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
