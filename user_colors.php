<?php

declare(strict_types=1);

require __DIR__ . '/connection.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function swatch(string $name): string
{
    $safe = e($name);
    return "<span class='swatch' style='background: {$safe};'></span>";
}

$pdo = (new Connection())->getConnection();

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$userId || $userId <= 0) {
    http_response_code(400);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
    echo '<div class="container"><div class="card"><p>ID de usuário inválido.</p><p><a class="btn" href="index.php">Voltar</a></p></div></div>';
    exit;
}

$st = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
$st->execute([$userId]);
$user = $st->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    http_response_code(404);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
    echo '<div class="container"><div class="card"><p>Usuário não encontrado.</p><p><a class="btn" href="index.php">Voltar</a></p></div></div>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = array_map('intval', $_POST['colors'] ?? []);

    try {
        $pdo->beginTransaction();

        $del = $pdo->prepare('DELETE FROM user_colors WHERE user_id = ?');
        $del->execute([$userId]);

        if (!empty($selected)) {
            $ins = $pdo->prepare('INSERT INTO user_colors(user_id, color_id) VALUES(?, ?)');
            foreach ($selected as $cid) {
                $ins->execute([$userId, $cid]);
            }
        }

        $pdo->commit();
        header('Location: index.php');
        exit;
    } catch (Throwable $t) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
        echo '<div class="container"><div class="card">';
        echo '<h1 class="title">Erro ao salvar vínculos</h1>';
        echo '<p>Tente novamente.</p>';
        echo '<p><a class="btn" href="index.php">Voltar</a></p>';
        echo '</div></div>';
        exit;
    }
}

// Carrega as cores
$colors = $pdo->query('SELECT id, name FROM colors ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

// Carrega usuários das cores
$ownStmt = $pdo->prepare('SELECT color_id FROM user_colors WHERE user_id = ?');
$ownStmt->execute([$userId]);
$ownIds = array_column($ownStmt->fetchAll(PDO::FETCH_ASSOC), 'color_id');

?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Vincular cores — <?= e($user['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <header class="topbar">
            <h1 class="title">Vincular cores — <?= e($user['name']) ?></h1>
            <div class="actions">
                <a class="btn btn--secondary" href="index.php">← Usuários</a>
                <a class="btn" href="colors.php">Cores</a>
            </div>
        </header>

        <div class="card">
            <?php if (empty($colors)): ?>
                <p class="muted">Nenhuma cor cadastrada. <a class="btn btn--sm" href="colors.php">Cadastrar cores</a></p>
            <?php else: ?>
                <form class="form" method="post" action="">
                    <input type="hidden" name="id" value="<?= e((string)$userId) ?>">

                    <div class="checklist">
                        <?php foreach ($colors as $c):
                            $checked = in_array((int)$c['id'], $ownIds, true) ? 'checked' : '';
                        ?>
                            <label class="check-item">
                                <input type="checkbox" name="colors[]" value="<?= e((string)$c['id']) ?>" <?= $checked ?>>
                                <?= swatch((string)$c['name']) ?><span><?= e((string)$c['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <button class="btn" type="submit">Salvar vínculos</button>
                        <a class="btn btn--secondary" href="index.php">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>