<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
$usuario = exigirAutenticacao();
$pdo = db();

$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$erros = [];
$val   = [
    'nome' => '', 'id_pais' => '', 'id_governante' => '', 'populacao' => '',
    'area_km2' => '', 'clima' => '', 'data_fundacao' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();
    $val['nome']           = obrigatorio('nome', 'Nome', $erros);
    $val['id_pais']        = post('id_pais');
    $val['id_governante']  = post('id_governante');
    $val['populacao']      = post('populacao');
    $val['area_km2']       = post('area_km2');
    $val['clima']          = post('clima');
    $val['data_fundacao']  = post('data_fundacao');

    /* a cidade PRECISA pertencer a um país existente */
    $idPais = postInt('id_pais');
    if (!$idPais) {
        $erros[] = 'Selecione o <strong>País</strong> ao qual a cidade pertence.';
    } else {
        $st = $pdo->prepare('SELECT COUNT(*) FROM paises WHERE id_pais = ?');
        $st->execute([$idPais]);
        if (!$st->fetchColumn()) {
            $erros[] = 'O país selecionado não existe.';
        }
    }

    $idGovernante = postInt('id_governante'); // opcional
    if ($idGovernante) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM governantes WHERE id_governante = ?');
        $st->execute([$idGovernante]);
        if (!$st->fetchColumn()) {
            $erros[] = 'O governante selecionado não existe.';
        }
    }

    $populacao = inteiro('populacao', 'População', $erros);
    $area      = numero('area_km2', 'Área (km²)', $erros);
    $fundacao  = dataOpcional('data_fundacao', 'Data de fundação', $erros);

    if ($fundacao && $fundacao > date('Y-m-d')) {
        $erros[] = 'A <strong>Data de fundação</strong> não pode estar no futuro.';
    }

    if (!$erros) {
        if ($id) {
            $st = $pdo->prepare(
                'UPDATE cidades SET nome = ?, id_pais = ?, id_governante = ?, populacao = ?, area_km2 = ?, clima = ?, data_fundacao = ?
                 WHERE id_cidade = ?'
            );
            $st->execute([
                $val['nome'], $idPais, $idGovernante ?: null, $populacao, $area,
                $val['clima'] ?: null, $fundacao, $id,
            ]);
            flash('sucesso', 'Cidade atualizada com sucesso. ✅');
        } else {
            $st = $pdo->prepare(
                'INSERT INTO cidades (nome, id_pais, id_governante, populacao, area_km2, clima, data_fundacao)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                $val['nome'], $idPais, $idGovernante ?: null, $populacao, $area,
                $val['clima'] ?: null, $fundacao,
            ]);
            flash('sucesso', 'Cidade cadastrada com sucesso. 🎉');
        }
        redirect('backend/cidades/index.php');
    }
} elseif ($id) {
    $st = $pdo->prepare('SELECT * FROM cidades WHERE id_cidade = ?');
    $st->execute([$id]);
    $val = $st->fetch();
    if (!$val) {
        flash('erro', 'Cidade não encontrada.');
        redirect('backend/cidades/index.php');
    }
}

$titulo = $id ? 'Editar cidade' : 'Nova cidade';
$ativo  = 'cidades';
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <h1><?= $id ? '✏️ Editar cidade' : '＋ Nova cidade' ?></h1>
        <p class="subtitulo">Toda cidade precisa estar vinculada a um país existente.</p>
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
        <input type="text" id="nome" name="nome" required maxlength="100"
               value="<?= e($val['nome']) ?>" placeholder="Ex.: São Paulo">
    </div>

    <div class="campo">
        <label for="id_pais">País *</label>
        <select id="id_pais" name="id_pais" required>
            <option value="">— Selecione…</option>
            <?= opcoes($pdo, 'SELECT id_pais AS id, nome FROM paises ORDER BY nome', $val['id_pais']) ?>
        </select>
    </div>

    <div class="campo">
        <label for="id_governante">Governante <small>(opcional)</small></label>
        <select id="id_governante" name="id_governante">
            <option value="">— Sem vínculo —</option>
            <?= opcoes($pdo, 'SELECT id_governante AS id, nome FROM governantes ORDER BY nome', $val['id_governante']) ?>
        </select>
    </div>

    <div class="campo">
        <label for="populacao">População *</label>
        <input type="number" id="populacao" name="populacao" data-rotulo="População" required min="0" step="1"
               value="<?= e($val['populacao']) ?>" placeholder="Ex.: 11451245">
    </div>

    <div class="campo">
        <label for="area_km2">Área (km²) *</label>
        <input type="number" id="area_km2" name="area_km2" data-rotulo="Área (km²)" required min="0" step="0.01"
               value="<?= e($val['area_km2']) ?>" placeholder="Ex.: 1521">
    </div>

    <div class="campo">
        <label for="clima">Clima</label>
        <input type="text" id="clima" name="clima" list="dl-climas" maxlength="80"
               value="<?= e($val['clima']) ?>" placeholder="Ex.: Tropical de altitude">
    </div>

    <div class="campo">
        <label for="data_fundacao">Data de fundação</label>
        <input type="date" id="data_fundacao" name="data_fundacao" data-max-hoje
               value="<?= e($val['data_fundacao']) ?>">
    </div>

    <div class="form-acoes">
        <button type="submit" class="btn btn-primario">💾 Salvar</button>
        <a class="btn btn-neutro" href="index.php">Cancelar</a>
    </div>
</form>

<datalist id="dl-climas">
    <option value="Tropical"></option><option value="Tropical de altitude"></option>
    <option value="Equatorial"></option><option value="Árido"></option>
    <option value="Semiárido"></option><option value="Mediterrânico"></option>
    <option value="Temperado"></option><option value="Subtropical"></option>
    <option value="Continental"></option><option value="Oceânico"></option>
</datalist>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
