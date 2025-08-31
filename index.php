<?php

require __DIR__ . '/connection.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function render_color_chip(string $name): string
{
    $safe = e($name);
    $style = "background: {$safe};";
    return "<span class='chip'><span class='chip__swatch' style='{$style}'></span>{$safe}</span>";
}

$conn = new Connection();
$pdo  = $conn->getConnection();

// Query para ver o vínculo das cores
$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        COALESCE(GROUP_CONCAT(c.name, '|'), '') AS color_list
    FROM users u
    LEFT JOIN user_colors uc ON uc.user_id = u.id
    LEFT JOIN colors c       ON c.id = uc.color_id
    GROUP BY u.id, u.name, u.email
    ORDER BY u.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Usuários</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <header class="topbar">
            <h1 class="title">Usuários</h1>
            <div class="actions">
                <a class="btn" href="user_form.php?action=create">+ Novo Usuário</a>
                <a class="btn btn--secondary" href="colors.php">Cores</a>
            </div>
        </header>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:80px">ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Cores vinculadas</th>
                            <th style="width:220px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="5" class="muted">Nenhum usuário encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?= e((string)$r['id']) ?></td>
                                    <td><?= e($r['name']) ?></td>
                                    <td><?= e($r['email']) ?></td>
                                    <td>
                                        <?php
                                        if ($r['color_list'] === '' || $r['color_list'] === null) {
                                            echo "<span class='muted'>—</span>";
                                        } else {
                                            foreach (explode('|', $r['color_list']) as $colorName) {
                                                echo render_color_chip($colorName) . ' ';
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td class="actions-col">
                                        <a class="btn btn--sm" href="user_form.php?action=edit&id=<?= e((string)$r['id']) ?>">Editar</a>

                                        <a class="btn btn--sm" href="user_colors.php?id=<?= e((string)$r['id']) ?>">Vincular cores</a>

                                        <form class="inline-form" method="post" action="user_delete.php"
                                            onsubmit="return confirm('Remover este usuário e desvincular suas cores?');">
                                            <input type="hidden" name="id" value="<?= e((string)$r['id']) ?>">
                                            <button class="btn btn--sm btn--danger" type="submit">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>