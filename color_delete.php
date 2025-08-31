<?php

declare(strict_types=1);

require __DIR__ . '/connection.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: colors.php');
    exit;
}

// Valida ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    http_response_code(400);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
    echo '<div class="container"><div class="card"><p>ID inválido.</p><p><a class="btn" href="colors.php">Voltar</a></p></div></div>';
    exit;
}

$pdo = (new Connection())->getConnection();

try {
    $pdo->beginTransaction();

    // (Opcional) verificar existência
    $chk = $pdo->prepare('SELECT id FROM colors WHERE id = ?');
    $chk->execute([$id]);
    $exists = (bool) $chk->fetch();

    if ($exists) {
        // 1) remove vínculos
        $delLinks = $pdo->prepare('DELETE FROM user_colors WHERE color_id = ?');
        $delLinks->execute([$id]);

        // 2) remove a cor
        $delColor = $pdo->prepare('DELETE FROM colors WHERE id = ?');
        $delColor->execute([$id]);
    }

    $pdo->commit();
    header('Location: colors.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
    echo '<div class="container"><div class="card">';
    echo '<h1 class="title">Erro ao excluir</h1>';
    echo '<p>Não foi possível remover esta cor. Tente novamente.</p>';
    echo '<p><a class="btn" href="colors.php">Voltar</a></p>';
    echo '</div></div>';
    exit;
}
