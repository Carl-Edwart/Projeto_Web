<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
$pdo = db();

$itens = $pdo->query(
    'SELECT g.*,
            (SELECT p.nome FROM paises  p WHERE p.id_governante = g.id_governante LIMIT 1) AS pais,
            (SELECT c.nome FROM cidades c WHERE c.id_governante = g.id_governante LIMIT 1) AS cidade
     FROM governantes g
     ORDER BY g.nome'
)->fetchAll();

$titulo = 'Governantes';
$ativo  = 'governantes';
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <h1>🧑‍💼 Governantes</h1>
        <p class="subtitulo"><?= count($itens) ?> governante(s) cadastrado(s)</p>
    </div>
    <a class="btn btn-primario" href="form.php">＋ Novo governante</a>
</div>

<div class="toolbar">
    <input type="search" class="campo-busca" placeholder="🔎 Filtrar por nome ou partido…" data-filtro="#tabela" aria-label="Filtrar tabela">
</div>

<div class="cartao tabela-caixa">
    <table class="tabela responsiva" id="tabela">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Partido</th>
                <th>Nascimento</th>
                <th>Idade</th>
                <th>Mandato</th>
                <th>Governa</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($itens as $g):
            $destaque = ((int) ($_GET['destaque'] ?? 0) === (int) $g['id_governante']) ? ' linha-destaque' : ''; ?>
            <tr class="linha<?= $destaque ?>">
                <td data-label="Nome"><strong><?= e($g['nome']) ?></strong></td>
                <td data-label="Partido"><?= e($g['partido_politico'] ?: '—') ?></td>
                <td data-label="Nascimento"><?= fmtData($g['data_nascimento']) ?></td>
                <td data-label="Idade"><?= $g['idade'] !== null ? (int) $g['idade'] . ' anos' : '—' ?></td>
                <td data-label="Mandato"><?= fmtData($g['inicio_mandato']) ?> → <?= fmtData($g['fim_mandato']) ?></td>
                <td data-label="Governa">
                    <?php if ($g['pais']): ?>
                        <span class="chip chip-info">🚩 <?= e($g['pais']) ?></span>
                    <?php elseif ($g['cidade']): ?>
                        <span class="chip chip-verde">🏙️ <?= e($g['cidade']) ?></span>
                    <?php else: ?>
                        <span class="chip">sem vínculo</span>
                    <?php endif; ?>
                </td>
                <td class="acoes" data-label="">
                    <a class="btn btn-neutro btn-pequeno" href="form.php?id=<?= (int) $g['id_governante'] ?>">✏️ Editar</a>
                    <form method="post" action="excluir.php"
                          data-confirmar="Excluir o governante “<?= e($g['nome']) ?>”? Países/cidades vinculados ficarão sem governante.">
                        <input type="hidden" name="id" value="<?= (int) $g['id_governante'] ?>">
                        <button type="submit" class="btn btn-perigo btn-pequeno">🗑️ Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$itens): ?>
            <tr><td colspan="7" class="vazio">Nenhum governante cadastrado ainda. <a href="form.php">Cadastrar o primeiro →</a></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
