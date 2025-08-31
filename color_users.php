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

$colorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$colorId) {
    $colorId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}
if (!$colorId || $colorId <= 0) {
    http_response_code(400);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
    echo '<div class="container"><div class="card"><p>ID da cor inválido.</p><p><a class="btn" href="colors.php">Voltar</a></p></div></div>';
    exit;
}

$st = $pdo->prepare('SELECT id, name FROM colors WHERE id = ?');
$st->execute([$colorId]);
$color = $st->fetch(PDO::FETCH_ASSOC);
if (!$color) {
    http_response_code(404);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
    echo '<div class="container"><div class="card"><p>Cor não encontrada.</p><p><a class="btn" href="colors.php">Voltar</a></p></div></div>';
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $selected = array_map('intval', $_POST['users'] ?? []);

    try {
        $pdo->beginTransaction();

        $del = $pdo->prepare('DELETE FROM user_colors WHERE color_id = ?');
        $del->execute([$colorId]);

        if (!empty($selected)) {
            $ins = $pdo->prepare('INSERT INTO user_colors(user_id, color_id) VALUES(?, ?)');
            foreach ($selected as $uid) {
                if ($uid > 0) {
                    $ins->execute([$uid, $colorId]);
                }
            }
        }

        $pdo->commit();
        header('Location: colors.php');
        exit;
    } catch (Throwable $t) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="style.css">';
        echo '<div class="container"><div class="card">';
        echo '<h1 class="title">Erro ao salvar vínculos</h1>';
        echo '<p>Tente novamente.</p>';
        echo '<p><a class="btn" href="colors.php">Voltar</a></p>';
        echo '</div></div>';
        exit;
    }
}

// Carrega todos os usuários
$users = $pdo->query('SELECT id, name, email FROM users ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);

// Carrega os IDs de usuários atualmente vinculados a cor
$ownStmt = $pdo->prepare('SELECT user_id FROM user_colors WHERE color_id = ?');
$ownStmt->execute([$colorId]);
$ownIds = array_map('intval', array_column($ownStmt->fetchAll(PDO::FETCH_ASSOC), 'user_id'));

?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Vincular usuários — <?= e($color['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <header class="topbar">
            <h1 class="title">Vincular usuários — <?= swatch($color['name']) ?> <?= e($color['name']) ?></h1>
            <div class="actions">
                <a class="btn btn--secondary" href="colors.php">← Cores</a>
                <a class="btn" href="index.php">Usuários</a>
            </div>
        </header>

        <div class="card">
            <?php if (empty($users)): ?>
                <p class="muted">Nenhum usuário cadastrado. <a class="btn btn--sm" href="index.php">Cadastrar usuários</a></p>
            <?php else: ?>
                <form class="form" method="post" action="">
                    <input type="hidden" name="id" value="<?= e((string)$colorId) ?>">

                    <div class="checklist">
                        <?php foreach ($users as $u):
                            $uid = (int)$u['id'];
                            $checked = in_array($uid, $ownIds, true) ? 'checked' : '';
                        ?>
                            <label class="check-item">
                                <input type="checkbox" name="users[]" value="<?= e((string)$uid) ?>" <?= $checked ?>>
                                <span><?= e($u['name']) ?></span>
                                <?php if (!empty($u['email'])): ?>
                                    <small class="muted" style="margin-left:6px;">(<?= e($u['email']) ?>)</small>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <button class="btn" type="submit">Salvar vínculos</button>
                        <a class="btn btn--secondary" href="colors.php">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>