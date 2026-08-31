<?php
require dirname(__DIR__, 2) . '/backend/helpers.php';
exigirPost();
$usuario = exigirAutenticacao();

$pdo = db();
$id  = postInt('id');

if (!$id) {
    flash('erro', 'Registro inválido.');
    redirect('backend/paises/index.php');
}

$st = $pdo->prepare('SELECT nome FROM paises WHERE id_pais = ?');
$st->execute([$id]);
$reg = $st->fetch();

if (!$reg) {
    flash('erro', 'País não encontrado.');
    redirect('backend/paises/index.php');
}

/*
 * Integridade referencial (estratégia RESTRICT):
 * país com cidades vinculadas NÃO pode ser excluído.
 * Alternativa: mudar a FK de cidades para ON DELETE CASCADE no bd_mundo.sql.
 */
$st = $pdo->prepare('SELECT COUNT(*) FROM cidades WHERE id_pais = ?');
$st->execute([$id]);
$qtd = (int) $st->fetchColumn();

if ($qtd > 0) {
    flash('erro', 'Não é possível excluir “' . $reg['nome'] . '”: ele possui ' . $qtd . ' cidade(s) vinculada(s). Exclua as cidades primeiro.');
    redirect('backend/paises/index.php');
}

$st = $pdo->prepare('DELETE FROM paises WHERE id_pais = ?');
$st->execute([$id]);

flash('sucesso', 'País “' . $reg['nome'] . '” excluído com sucesso. 🗑️');
redirect('backend/paises/index.php');
