<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
$usuario = exigirAutenticacao();
$pdo = db();

$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$erros = [];
$val   = [
    'nome' => '', 'id_continente' => '', 'id_governante' => '', 'populacao' => '',
    'area_km2' => '', 'idioma' => '', 'clima' => '', 'regime_politico' => '', 'moeda' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();
    $val['nome']           = obrigatorio('nome', 'Nome', $erros);
    $val['id_continente']  = post('id_continente');
    $val['id_governante']  = post('id_governante');
    $val['populacao']      = post('populacao');
    $val['area_km2']       = post('area_km2');
    $val['idioma']         = post('idioma');
    $val['clima']          = post('clima');
    $val['regime_politico'] = post('regime_politico');
    $val['moeda']          = post('moeda');

    $idContinente = postInt('id_continente');
    if (!$idContinente) {
        $erros[] = 'Selecione o <strong>Continente</strong>.';
    } else {
        $st = $pdo->prepare('SELECT COUNT(*) FROM continentes WHERE id_continente = ?');
        $st->execute([$idContinente]);
        if (!$st->fetchColumn()) {
            $erros[] = 'O continente selecionado não existe.';
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

    if ($val['nome'] !== '') {
        $st = $pdo->prepare('SELECT COUNT(*) FROM paises WHERE nome = ? AND id_pais <> ?');
        $st->execute([$val['nome'], $id]);
        if ($st->fetchColumn() > 0) {
            $erros[] = 'Já existe um país cadastrado com este nome.';
        }
    }

    if (!$erros) {
        if ($id) {
            $st = $pdo->prepare(
                'UPDATE paises SET nome = ?, id_continente = ?, id_governante = ?, populacao = ?, area_km2 = ?, idioma = ?, clima = ?, regime_politico = ?, moeda = ?
                 WHERE id_pais = ?'
            );
            $st->execute([
                $val['nome'], $idContinente, $idGovernante ?: null, $populacao, $area,
                $val['idioma'] ?: null, $val['clima'] ?: null, $val['regime_politico'] ?: null, $val['moeda'] ?: null, $id,
            ]);
            flash('sucesso', 'País atualizado com sucesso. ✅');
        } else {
            $st = $pdo->prepare(
                'INSERT INTO paises (nome, id_continente, id_governante, populacao, area_km2, idioma, clima, regime_politico, moeda)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                $val['nome'], $idContinente, $idGovernante ?: null, $populacao, $area,
                $val['idioma'] ?: null, $val['clima'] ?: null, $val['regime_politico'] ?: null, $val['moeda'] ?: null,
            ]);
            flash('sucesso', 'País cadastrado com sucesso. 🎉');
        }
        redirect('backend/paises/index.php');
    }
} elseif ($id) {
    $st = $pdo->prepare('SELECT * FROM paises WHERE id_pais = ?');
    $st->execute([$id]);
    $val = $st->fetch();
    if (!$val) {
        flash('erro', 'País não encontrado.');
        redirect('backend/paises/index.php');
    }
}

$titulo = $id ? 'Editar país' : 'Novo país';
$ativo  = 'paises';
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <h1><?= $id ? '✏️ Editar país' : '＋ Novo país' ?></h1>
        <p class="subtitulo">Vincule o país a um continente e, se quiser, a um governante.</p>
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
               value="<?= e($val['nome']) ?>" placeholder="Ex.: Brasil">
    </div>

    <div class="campo">
        <label for="id_continente">Continente *</label>
        <select id="id_continente" name="id_continente" required>
            <option value="">— Selecione…</option>
            <?= opcoes($pdo, 'SELECT id_continente AS id, nome FROM continentes ORDER BY nome', $val['id_continente']) ?>
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
               value="<?= e($val['populacao']) ?>" placeholder="Ex.: 203100000">
    </div>

    <div class="campo">
        <label for="area_km2">Área (km²) *</label>
        <input type="number" id="area_km2" name="area_km2" data-rotulo="Área (km²)" required min="0" step="0.01"
               value="<?= e($val['area_km2']) ?>" placeholder="Ex.: 8510416">
    </div>

    <div class="campo">
        <label for="idioma">Idioma</label>
        <input type="text" id="idioma" name="idioma" maxlength="80"
               value="<?= e($val['idioma']) ?>" placeholder="Ex.: Português">
    </div>

    <div class="campo">
        <label for="clima">Clima</label>
        <input type="text" id="clima" name="clima" list="dl-climas" maxlength="80"
               value="<?= e($val['clima']) ?>" placeholder="Ex.: Tropical">
    </div>

    <div class="campo">
        <label for="regime_politico">Regime político</label>
        <input type="text" id="regime_politico" name="regime_politico" list="dl-regimes" maxlength="80"
               value="<?= e($val['regime_politico']) ?>" placeholder="Ex.: República Presidencialista">
    </div>

    <div class="campo">
        <label for="moeda">Moeda</label>
        <input type="text" id="moeda" name="moeda" list="dl-moedas" maxlength="60"
               value="<?= e($val['moeda']) ?>" placeholder="Ex.: Real (BRL)">
    </div>

    <div class="form-acoes">
        <button type="submit" class="btn btn-primario">💾 Salvar</button>
        <a class="btn btn-neutro" href="index.php">Cancelar</a>
    </div>
</form>

<!-- Sugestões prontas (o campo continua aceitando texto livre) -->
<datalist id="dl-climas">
    <option value="Tropical"></option><option value="Equatorial"></option>
    <option value="Árido"></option><option value="Semiárido"></option>
    <option value="Mediterrânico"></option><option value="Temperado"></option>
    <option value="Subtropical"></option><option value="Continental"></option>
    <option value="Polar"></option><option value="Oceânico"></option>
</datalist>
<datalist id="dl-regimes">
    <option value="República Presidencialista"></option>
    <option value="República Parlamentar"></option>
    <option value="República Semipresidencialista"></option>
    <option value="Monarquia Parlamentar"></option>
    <option value="Monarquia Constitucional"></option>
    <option value="República Popular"></option>
</datalist>
<datalist id="dl-moedas">
    <option value="Real (BRL)"></option><option value="Dólar (USD)"></option>
    <option value="Euro (EUR)"></option><option value="Libra esterlina (GBP)"></option>
    <option value="Iene (JPY)"></option><option value="Yuan (CNY)"></option>
</datalist>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
