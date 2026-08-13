<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
exigirPost();

$pdo = db();
$id  = postInt('id');

if (!$id) {
    flash('erro', 'Registro inválido.');
    redirect('backend/governantes/index.php');
}

$st = $pdo->prepare('SELECT nome FROM governantes WHERE id_governante = ?');
$st->execute([$id]);
$reg = $st->fetch();

if (!$reg) {
    flash('erro', 'Governante não encontrado.');
    redirect('backend/governantes/index.php');
}

/*
 * Integridade referencial: as FKs de paises/cidades usam ON DELETE SET NULL,
 * então excluir o governante apenas desvincula — nada é apagado junto.
 */
$st = $pdo->prepare('DELETE FROM governantes WHERE id_governante = ?');
$st->execute([$id]);

flash('sucesso', 'Governante “' . $reg['nome'] . '” excluído. Países e cidades vinculados ficaram sem governante.');
redirect('backend/governantes/index.php');
