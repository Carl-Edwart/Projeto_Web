<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
$usuario = exigirAutenticacao();
$pdo = db();

$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$erros = [];
$val   = ['nome' => '', 'populacao' => '', 'area_km2' => '', 'total_paises' => ''];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();
    /* ------- recepção + validação server-side ------- */
    $val['nome']         = obrigatorio('nome', 'Nome', $erros);
    $val['populacao']    = post('populacao');
    $val['area_km2']     = post('area_km2');
    $val['total_paises'] = post('total_paises');

    $populacao = inteiro('populacao', 'População', $erros);
    $area      = numero('area_km2', 'Área (km²)', $erros);
    $total     = inteiro('total_paises', 'Total de países', $erros);

    /* nome deve ser único (além do UNIQUE no banco, validamos aqui para dar mensagem amigável) */
    if ($val['nome'] !== '') {
        $st = $pdo->prepare('SELECT COUNT(*) FROM continentes WHERE nome = ? AND id_continente <> ?');
        $st->execute([$val['nome'], $id]);
        if ($st->fetchColumn() > 0) {
            $erros[] = 'Já existe um continente cadastrado com este nome.';
        }
    }

    /* ------- gravação (INSERT ou UPDATE) ------- */
    if (!$erros) {
        if ($id) {
            $st = $pdo->prepare('UPDATE continentes SET nome = ?, populacao = ?, area_km2 = ?, total_paises = ? WHERE id_continente = ?');
            $st->execute([$val['nome'], $populacao, $area, $total, $id]);
            flash('sucesso', 'Continente atualizado com sucesso. ✅');
        } else {
            $st = $pdo->prepare('INSERT INTO continentes (nome, populacao, area_km2, total_paises) VALUES (?, ?, ?, ?)');
            $st->execute([$val['nome'], $populacao, $area, $total]);
            flash('sucesso', 'Continente cadastrado com sucesso. 🎉');
        }
        redirect('backend/continentes/index.php');
    }
} elseif ($id) {
    /* edição: carrega os dados atuais */
    $st = $pdo->prepare('SELECT * FROM continentes WHERE id_continente = ?');
    $st->execute([$id]);
    $val = $st->fetch();
    if (!$val) {
        flash('erro', 'Continente não encontrado.');
        redirect('backend/continentes/index.php');
    }
}

$titulo = $id ? 'Editar continente' : 'Novo continente';
$ativo  = 'continentes';
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <h1><?= $id ? '✏️ Editar continente' : '＋ Novo continente' ?></h1>
        <p class="subtitulo">Preencha os campos abaixo. Itens com * são obrigatórios.</p>
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
    <?= campoCsrf() ?>
    <div class="campo campo-total">
        <label for="nome">Nome *</label>
        <input type="text" id="nome" name="nome" required maxlength="80"
               value="<?= e($val['nome']) ?>" placeholder="Ex.: América do Sul">
    </div>

    <div class="campo">
        <label for="populacao">População *</label>
        <input type="number" id="populacao" name="populacao" data-rotulo="População" required min="0" step="1"
               value="<?= e($val['populacao']) ?>" placeholder="Ex.: 434000000">
    </div>

    <div class="campo">
        <label for="area_km2">Área (km²) *</label>
        <input type="number" id="area_km2" name="area_km2" data-rotulo="Área (km²)" required min="0" step="0.01"
               value="<?= e($val['area_km2']) ?>" placeholder="Ex.: 17840000">
    </div>

    <div class="campo">
        <label for="total_paises">Total de países *</label>
        <input type="number" id="total_paises" name="total_paises" data-rotulo="Total de países" required min="0" step="1"
               value="<?= e($val['total_paises']) ?>" placeholder="Ex.: 12">
    </div>

    <div class="form-acoes">
        <button type="submit" class="btn btn-primario">💾 Salvar</button>
        <a class="btn btn-neutro" href="index.php">Cancelar</a>
    </div>
</form>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
