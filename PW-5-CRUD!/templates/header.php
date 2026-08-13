<?php
/**
 * templates/header.php — topo comum de todas as páginas.
 * Variáveis esperadas: $titulo (string) e $ativo (chave do menu).
 */
$titulo = $titulo ?? 'CRUD Mundo';
$ativo  = $ativo  ?? '';

$abas = [
    'inicio'      => ['Início',      'index.php',                        '🏠'],
    'continentes' => ['Continentes', 'backend/continentes/index.php',    '🗺️'],
    'paises'      => ['Países',      'backend/paises/index.php',         '🚩'],
    'governantes' => ['Governantes', 'backend/governantes/index.php',    '🧑‍💼'],
    'cidades'     => ['Cidades',     'backend/cidades/index.php',        '🏙️'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · CRUD Mundo</title>
    <meta name="description" content="Sistema de gerenciamento de países, cidades, continentes e governantes.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌍</text></svg>">
    <link rel="stylesheet" href="<?= e(url('frontend/css/style.css')) ?>">
    <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
    <script defer src="<?= e(url('frontend/js/app.js')) ?>"></script>
</head>
<body>
<header class="topo">
    <div class="topo-interno">
        <a class="logo" href="<?= e(url('index.php')) ?>">🌍&nbsp;<b>CRUD</b>&nbsp;Mundo</a>

        <button type="button" class="nav-btn" aria-label="Abrir menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav>
            <?php foreach ($abas as $chave => [$rotulo, $href, $icone]): ?>
                <a href="<?= e(url($href)) ?>" class="<?= $ativo === $chave ? 'ativo' : '' ?>">
                    <span class="nav-icone"><?= $icone ?></span><?= e($rotulo) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="busca-caixa">
            <input type="search" id="busca-global" placeholder="🔎 Buscar país ou cidade…"
                   autocomplete="off" aria-label="Busca dinâmica por nome">
            <ul id="resultados-busca" hidden></ul>
        </div>
    </div>
</header>

<main class="container pagina">
<?php foreach (flashes() as $f): ?>
    <div class="alerta alerta-<?= e($f['tipo']) ?>" data-auto>
        <span><?= e($f['mensagem']) ?></span>
        <button type="button" class="alerta-fechar" aria-label="Fechar">×</button>
    </div>
<?php endforeach; ?>
