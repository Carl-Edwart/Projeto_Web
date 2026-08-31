<?php
require __DIR__ . '/backend/helpers.php';

exigirPost();

$usuarioId = usuarioIdSessao();
if ($usuarioId !== null) {
    registrarLog(db(), $usuarioId, 'logout');
}

destruirSessao();
redirect('login.php?logout=1');
