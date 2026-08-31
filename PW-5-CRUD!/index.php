<?php
require __DIR__ . '/backend/helpers.php';
$usuario = exigirAutenticacao();
$pdo = db();

$titulo = 'Início';
$ativo  = 'inicio';

/* ------------- estatísticas gerais (desafio extra) ------------- */
$totais = [];
foreach (['continentes', 'paises', 'cidades', 'governantes'] as $tabela) {
    $totais[$tabela] = (int) $pdo->query("SELECT COUNT(*) FROM $tabela")->fetchColumn();
}

$cidadeTop = $pdo->query(
    'SELECT ci.nome AS cidade, ci.populacao, p.nome AS pais
     FROM cidades ci JOIN paises p ON p.id_pais = ci.id_pais
     ORDER BY ci.populacao DESC LIMIT 1'
)->fetch();

$porContinente = $pdo->query(
    'SELECT co.nome, COUNT(ci.id_cidade) AS total
     FROM continentes co
     LEFT JOIN paises  p  ON p.id_continente = co.id_continente
     LEFT JOIN cidades ci ON ci.id_pais = p.id_pais
     GROUP BY co.id_continente, co.nome
     ORDER BY total DESC, co.nome'
)->fetchAll();
$maxCidades = 0;
foreach ($porContinente as $pc) {
    $maxCidades = max($maxCidades, (int) $pc['total']);
}

$topPorPais = $pdo->query(
    'SELECT p.nome AS pais, ci.nome AS cidade, ci.populacao
     FROM paises p
     JOIN cidades ci ON ci.id_pais = p.id_pais
     JOIN (SELECT id_pais, MAX(populacao) AS maior FROM cidades GROUP BY id_pais) x
       ON x.id_pais = p.id_pais AND x.maior = ci.populacao
     ORDER BY p.nome'
)->fetchAll();

require __DIR__ . '/templates/header.php';
?>

<section class="hero">
    <h1>Gerencie o mundo em poucos cliques 🌍</h1>
    <p>Cadastre continentes, países, cidades e governantes em um só lugar — com dados consistentes e navegação simples.</p>
    <div class="hero-acoes">
        <a class="btn btn-claro" href="<?= e(url('backend/paises/form.php')) ?>">＋ Cadastrar país</a>
        <a class="btn btn-neutro" href="<?= e(url('backend/cidades/index.php')) ?>">Ver cidades</a>
    </div>
</section>

<section class="grade">
    <a class="cartao estat" href="<?= e(url('backend/continentes/index.php')) ?>">
        <span class="icone">🗺️</span>
        <span><span class="numero"><?= $totais['continentes'] ?></span><br><span class="rotulo">Continentes</span></span>
    </a>
    <a class="cartao estat" href="<?= e(url('backend/paises/index.php')) ?>">
        <span class="icone">🚩</span>
        <span><span class="numero"><?= $totais['paises'] ?></span><br><span class="rotulo">Países</span></span>
    </a>
    <a class="cartao estat" href="<?= e(url('backend/cidades/index.php')) ?>">
        <span class="icone">🏙️</span>
        <span><span class="numero"><?= $totais['cidades'] ?></span><br><span class="rotulo">Cidades</span></span>
    </a>
    <a class="cartao estat" href="<?= e(url('backend/governantes/index.php')) ?>">
        <span class="icone">🧑‍💼</span>
        <span><span class="numero"><?= $totais['governantes'] ?></span><br><span class="rotulo">Governantes</span></span>
    </a>
</section>

<section class="grade-2">
    <div class="cartao cartao-pad">
        <h2 class="cartao-titulo">🏆 Cidade mais populosa do sistema</h2>
        <p class="cartao-sub">Entre todas as cidades cadastradas</p>
        <?php if ($cidadeTop): ?>
            <div class="destaque-cidade">
                <span class="trofeu">🥇</span>
                <div>
                    <div class="nome-cidade"><?= e($cidadeTop['cidade']) ?></div>
                    <div class="info"><?= e($cidadeTop['pais']) ?> · <?= nfmt($cidadeTop['populacao']) ?> habitantes</div>
                </div>
            </div>
        <?php else: ?>
            <p class="cartao-sub">Nenhuma cidade cadastrada ainda.</p>
        <?php endif; ?>
    </div>

    <div class="cartao cartao-pad">
        <h2 class="cartao-titulo">📊 Cidades cadastradas por continente</h2>
        <p class="cartao-sub">Distribuição dos registros no sistema</p>
        <?php foreach ($porContinente as $pc): ?>
            <div class="barra-item">
                <div class="linha1"><b><?= e($pc['nome']) ?></b><span><?= (int) $pc['total'] ?> cidade(s)</span></div>
                <div class="barra-faixa">
                    <div class="barra-preenchida" style="width: <?= $maxCidades ? round((int) $pc['total'] / $maxCidades * 100) : 0 ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($topPorPais): ?>
<section class="cartao tabela-caixa" style="margin-bottom: 1.4rem;">
    <div style="padding: 1.1rem 1.25rem .2rem;">
        <h2 class="cartao-titulo">🥇 Cidade mais populosa de cada país</h2>
        <p class="cartao-sub">Estatística automática baseada nos dados cadastrados</p>
    </div>
    <table class="tabela responsiva">
        <thead><tr><th>País</th><th>Cidade mais populosa</th><th>População</th></tr></thead>
        <tbody>
        <?php foreach ($topPorPais as $l): ?>
            <tr>
                <td data-label="País"><strong><?= e($l['pais']) ?></strong></td>
                <td data-label="Cidade"><?= e($l['cidade']) ?></td>
                <td data-label="População"><?= nfmt($l['populacao']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>

<section class="atalhos">
    <a class="cartao atalho" href="<?= e(url('backend/continentes/index.php')) ?>">
        <span class="atalho-icone">🗺️</span>
        <h3>Continentes</h3>
        <p>Cadastre e gerencie os continentes do mundo.</p>
        <span class="ir">Gerenciar →</span>
    </a>
    <a class="cartao atalho" href="<?= e(url('backend/paises/index.php')) ?>">
        <span class="atalho-icone">🚩</span>
        <h3>Países</h3>
        <p>Países com continente, governante, moeda e mais.</p>
        <span class="ir">Gerenciar →</span>
    </a>
    <a class="cartao atalho" href="<?= e(url('backend/governantes/index.php')) ?>">
        <span class="atalho-icone">🧑‍💼</span>
        <h3>Governantes</h3>
        <p>Líderes com partido, nascimento e mandato.</p>
        <span class="ir">Gerenciar →</span>
    </a>
    <a class="cartao atalho" href="<?= e(url('backend/cidades/index.php')) ?>">
        <span class="atalho-icone">🏙️</span>
        <h3>Cidades</h3>
        <p>Cidades sempre vinculadas a um país existente.</p>
        <span class="ir">Gerenciar →</span>
    </a>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
