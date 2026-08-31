<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
$usuario = exigirAutenticacao();
$pdo = db();

$itens = $pdo->query(
    'SELECT ci.*, p.nome AS pais, g.nome AS governante
     FROM cidades ci
     LEFT JOIN paises p      ON p.id_pais = ci.id_pais
     LEFT JOIN governantes g ON g.id_governante = ci.id_governante
     ORDER BY ci.nome'
)->fetchAll();

$titulo = 'Cidades';
$ativo  = 'cidades';
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <h1>🏙️ Cidades</h1>
        <p class="subtitulo"><?= count($itens) ?> cidade(s) cadastrada(s)</p>
    </div>
    <a class="btn btn-primario" href="form.php">＋ Nova cidade</a>
</div>

<div class="toolbar">
    <input type="search" class="campo-busca" placeholder="🔎 Filtrar por nome, país, clima…" data-filtro="#tabela" aria-label="Filtrar tabela">
</div>

<div class="cartao tabela-caixa">
    <table class="tabela responsiva" id="tabela">
        <thead>
            <tr>
                <th>Cidade</th>
                <th>País</th>
                <th>Governante</th>
                <th>População</th>
                <th>Área (km²)</th>
                <th>Fundação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($itens as $c):
            $destaque = ((int) ($_GET['destaque'] ?? 0) === (int) $c['id_cidade']) ? ' linha-destaque' : ''; ?>
            <tr class="linha<?= $destaque ?>">
                <td data-label="Cidade">
                    <strong><?= e($c['nome']) ?></strong>
                    <?php if ($c['clima']): ?><small class="detalhe"><?= e($c['clima']) ?></small><?php endif; ?>
                </td>
                <td data-label="País"><span class="chip chip-info">🚩 <?= e($c['pais'] ?? '—') ?></span></td>
                <td data-label="Governante"><?= $c['governante'] ? e($c['governante']) : '<span class="chip">sem vínculo</span>' ?></td>
                <td data-label="População"><?= nfmt($c['populacao']) ?></td>
                <td data-label="Área (km²)"><?= nfmt($c['area_km2']) ?></td>
                <td data-label="Fundação"><?= fmtData($c['data_fundacao']) ?></td>
                <td class="acoes" data-label="">
                    <a class="btn btn-neutro btn-pequeno" href="form.php?id=<?= (int) $c['id_cidade'] ?>">✏️ Editar</a>
                    <form method="post" action="excluir.php"
                          data-confirmar="Excluir a cidade “<?= e($c['nome']) ?>”? Esta ação não pode ser desfeita.">
                        <?= campoCsrf() ?>
                        <input type="hidden" name="id" value="<?= (int) $c['id_cidade'] ?>">
                        <button type="submit" class="btn btn-perigo btn-pequeno">🗑️ Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$itens): ?>
            <tr><td colspan="7" class="vazio">Nenhuma cidade cadastrada ainda. <a href="form.php">Cadastrar a primeira →</a></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
