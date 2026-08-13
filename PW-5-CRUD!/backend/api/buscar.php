<?php
/**
 * backend/api/buscar.php — endpoint JSON da busca dinâmica (desafio extra).
 * Uso: GET /backend/api/buscar.php?q=brasil
 */
require dirname(__DIR__, 2) . '/backend/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim((string) ($_GET['q'] ?? ''));
if (mb_strlen($q) < 2) {
    echo '[]';
    exit;
}

$pdo  = db();
$like = '%' . $q . '%';
$out  = [];

$st = $pdo->prepare(
    'SELECT p.id_pais AS id, p.nome, c.nome AS extra
     FROM paises p LEFT JOIN continentes c ON c.id_continente = p.id_continente
     WHERE p.nome LIKE ? ORDER BY p.nome LIMIT 5'
);
$st->execute([$like]);
foreach ($st as $l) {
    $out[] = ['tipo' => 'País', 'entidade' => 'paises', 'id' => (int) $l['id'], 'nome' => $l['nome'], 'extra' => $l['extra']];
}

$st = $pdo->prepare(
    'SELECT ci.id_cidade AS id, ci.nome, p.nome AS extra
     FROM cidades ci LEFT JOIN paises p ON p.id_pais = ci.id_pais
     WHERE ci.nome LIKE ? ORDER BY ci.nome LIMIT 5'
);
$st->execute([$like]);
foreach ($st as $l) {
    $out[] = ['tipo' => 'Cidade', 'entidade' => 'cidades', 'id' => (int) $l['id'], 'nome' => $l['nome'], 'extra' => $l['extra']];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
