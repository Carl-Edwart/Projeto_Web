<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
exigirPost();

$pdo = db();
$id  = postInt('id');

if (!$id) {
    flash('erro', 'Registro inválido.');
    redirect('backend/cidades/index.php');
}

$st = $pdo->prepare('SELECT nome FROM cidades WHERE id_cidade = ?');
$st->execute([$id]);
$reg = $st->fetch();

if (!$reg) {
    flash('erro', 'Cidade não encontrada.');
    redirect('backend/cidades/index.php');
}

/* A cidade não possui dependentes: pode ser excluída diretamente. */
$st = $pdo->prepare('DELETE FROM cidades WHERE id_cidade = ?');
$st->execute([$id]);

flash('sucesso', 'Cidade “' . $reg['nome'] . '” excluída com sucesso. 🗑️');
redirect('backend/cidades/index.php');
