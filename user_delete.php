<?php

declare(strict_types=1);

require __DIR__ . '/connection.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Valida ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    http_response_code(400);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
    echo '<div class="container"><div class="card"><p>ID inválido.</p><p><a class="btn" href="index.php">Voltar</a></p></div></div>';
    exit;
}

$pdo = (new Connection())->getConnection();

try {
    $pdo->beginTransaction();
    $check = $pdo->prepare('SELECT id FROM users WHERE id = ?');
    $check->execute([$id]);
    $exists = (bool) $check->fetch();

    if ($exists) {
        // Remove o vínculo das cores e usuários
        $delLinks = $pdo->prepare('DELETE FROM user_colors WHERE user_id = ?');
        $delLinks->execute([$id]);

        // Remove o usuário
        $delUser = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $delUser->execute([$id]);
    }

    $pdo->commit();

    // Redireciona de volta
    header('Location: index.php');
    exit;
} catch (Throwable $e) {
    // Rollback para mostrar erros
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
    echo '<div class="container"><div class="card">';
    echo '<h1 class="title">Erro ao excluir</h1>';
    echo '<p>Não foi possível remover este registro. Tente novamente.</p>';
    echo '<p><a class="btn" href="index.php">Voltar</a></p>';
    echo '</div></div>';
    exit;
}
