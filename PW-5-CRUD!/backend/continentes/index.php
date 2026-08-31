<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
$usuario = exigirAutenticacao();
$pdo = db();

$itens = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM paises p WHERE p.id_continente = c.id_continente) AS vinculados
     FROM continentes c
     ORDER BY c.nome'
)->fetchAll();

$titulo = 'Continentes';
$ativo  = 'continentes';
require dirname(__DIR__, 2) . '/templates/header.php';
?>

<div class="pagina-topo">
    <div>
        <h1>🗺️ Continentes</h1>
        <p class="subtitulo"><?= count($itens) ?> continente(s) cadastrado(s)</p>
    </div>
    <a class="btn btn-primario" href="form.php">＋ Novo continente</a>
</div>

<div class="toolbar">
    <input type="search" class="campo-busca" placeholder="🔎 Filtrar por nome…" data-filtro="#tabela" aria-label="Filtrar tabela">
</div>

<div class="cartao tabela-caixa">
    <table class="tabela responsiva" id="tabela">
        <thead>
            <tr>
                <th>Nome</th>
                <th>População</th>
                <th>Área (km²)</th>
                <th>Total de países</th>
                <th>Países vinculados</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($itens as $c):
            $destaque = ((int) ($_GET['destaque'] ?? 0) === (int) $c['id_continente']) ? ' linha-destaque' : ''; ?>
            <tr class="linha<?= $destaque ?>">
                <td data-label="Nome"><strong><?= e($c['nome']) ?></strong></td>
                <td data-label="População"><?= nfmt($c['populacao']) ?></td>
                <td data-label="Área (km²)"><?= nfmt($c['area_km2']) ?></td>
                <td data-label="Total de países"><?= nfmt($c['total_paises']) ?></td>
                <td data-label="Países vinculados">
                    <span class="chip <?= $c['vinculados'] ? 'chip-info' : '' ?>"><?= (int) $c['vinculados'] ?></span>
                </td>
                <td class="acoes" data-label="">
                    <a class="btn btn-neutro btn-pequeno" href="form.php?id=<?= (int) $c['id_continente'] ?>">✏️ Editar</a>
                    <form method="post" action="excluir.php"
                          data-confirmar="Excluir o continente “<?= e($c['nome']) ?>”? Esta ação não pode ser desfeita.">
                        <?= campoCsrf() ?>
                        <input type="hidden" name="id" value="<?= (int) $c['id_continente'] ?>">
                        <button type="submit" class="btn btn-perigo btn-pequeno">🗑️ Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$itens): ?>
            <tr><td colspan="6" class="vazio">Nenhum continente cadastrado ainda. <a href="form.php">Cadastrar o primeiro →</a></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__, 2) . '/templates/footer.php'; ?>
