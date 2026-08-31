<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
$usuario = exigirAutenticacao();
$pdo = db();

$itens = $pdo->query(
    'SELECT p.*,
            c.nome AS continente,
            g.nome AS governante,
            (SELECT COUNT(*) FROM cidades ci WHERE ci.id_pais = p.id_pais) AS cidades_vinculadas
     FROM paises p
     LEFT JOIN continentes c ON c.id_continente = p.id_continente
     LEFT JOIN governantes g ON g.id_governante = p.id_governante
     ORDER BY p.nome'
)->fetchAll();

$titulo = 'Países';
$ativo  = 'paises';
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <h1>🚩 Países</h1>
        <p class="subtitulo"><?= count($itens) ?> país(es) cadastrado(s)</p>
    </div>
    <a class="btn btn-primario" href="form.php">＋ Novo país</a>
</div>

<div class="toolbar">
    <input type="search" class="campo-busca" placeholder="🔎 Filtrar por nome, continente, idioma…" data-filtro="#tabela" aria-label="Filtrar tabela">
</div>

<div class="cartao tabela-caixa">
    <table class="tabela responsiva" id="tabela">
        <thead>
            <tr>
                <th>País</th>
                <th>Continente</th>
                <th>Governante</th>
                <th>População</th>
                <th>Área (km²)</th>
                <th>Cidades</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($itens as $p):
            $destaque = ((int) ($_GET['destaque'] ?? 0) === (int) $p['id_pais']) ? ' linha-destaque' : '';
            $detalhes = array_filter([$p['idioma'], $p['clima'], $p['regime_politico'], $p['moeda']]); ?>
            <tr class="linha<?= $destaque ?>">
                <td data-label="País">
                    <strong><?= e($p['nome']) ?></strong>
                    <?php if ($detalhes): ?>
                        <small class="detalhe"><?= e(implode(' · ', $detalhes)) ?></small>
                    <?php endif; ?>
                </td>
                <td data-label="Continente"><span class="chip chip-info">🗺️ <?= e($p['continente'] ?? '—') ?></span></td>
                <td data-label="Governante"><?= $p['governante'] ? e($p['governante']) : '<span class="chip">sem vínculo</span>' ?></td>
                <td data-label="População"><?= nfmt($p['populacao']) ?></td>
                <td data-label="Área (km²)"><?= nfmt($p['area_km2']) ?></td>
                <td data-label="Cidades">
                    <span class="chip <?= $p['cidades_vinculadas'] ? 'chip-verde' : '' ?>"><?= (int) $p['cidades_vinculadas'] ?></span>
                </td>
                <td class="acoes" data-label="">
                    <a class="btn btn-neutro btn-pequeno" href="form.php?id=<?= (int) $p['id_pais'] ?>">✏️ Editar</a>
                    <form method="post" action="excluir.php"
                          data-confirmar="Excluir o país “<?= e($p['nome']) ?>”? Esta ação não pode ser desfeita.">
                        <?= campoCsrf() ?>
                        <input type="hidden" name="id" value="<?= (int) $p['id_pais'] ?>">
                        <button type="submit" class="btn btn-perigo btn-pequeno">🗑️ Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$itens): ?>
            <tr><td colspan="7" class="vazio">Nenhum país cadastrado ainda. <a href="form.php">Cadastrar o primeiro →</a></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
