<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
exigirPost();
$usuario = exigirAutenticacao();

$pdo = db();
$id  = postInt('id');

if (!$id) {
    flash('erro', 'Registro inválido.');
    redirect('backend/continentes/index.php');
}

$st = $pdo->prepare('SELECT nome FROM continentes WHERE id_continente = ?');
$st->execute([$id]);
$reg = $st->fetch();

if (!$reg) {
    flash('erro', 'Continente não encontrado.');
    redirect('backend/continentes/index.php');
}

/* Integridade referencial: continente com países vinculados NÃO pode ser excluído */
$st = $pdo->prepare('SELECT COUNT(*) FROM paises WHERE id_continente = ?');
$st->execute([$id]);
if ((int) $st->fetchColumn() > 0) {
    flash('erro', 'Não é possível excluir “' . $reg['nome'] . '”: existem países vinculados a ele. Exclua ou mova os países primeiro.');
    redirect('backend/continentes/index.php');
}

$st = $pdo->prepare('DELETE FROM continentes WHERE id_continente = ?');
$st->execute([$id]);

flash('sucesso', 'Continente “' . $reg['nome'] . '” excluído com sucesso. 🗑️');
redirect('backend/continentes/index.php');
