<?php

require __DIR__ . '/connection.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//Quadrado colorido ao lado das cores
function color_swatch(string $name): string
{
    $safe = e($name);
    $style = "background: {$safe};";
    return "<span class='swatch' style='{$style}'></span>";
}

$pdo = (new Connection())->getConnection();

// Verifica usuários vinculados as cores
$sql = "
    SELECT
        c.id,
        c.name,
        COALESCE(GROUP_CONCAT(u.name, '|'), '') AS user_list
    FROM colors c
    LEFT JOIN user_colors uc ON uc.color_id = c.id
    LEFT JOIN users u       ON u.id = uc.user_id
    GROUP BY c.id, c.name
    ORDER BY c.name ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Cores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <header class="topbar">
            <h1 class="title">Cores</h1>
            <div class="actions">
                <a class="btn" href="color_form.php">+ Nova Cor</a>
                <a class="btn btn--secondary" href="index.php">Usuários</a>
            </div>
        </header>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:80px">ID</th>
                            <th>Cor</th>
                            <th>Usuários vinculados</th>
                            <th style="width:320px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="4" class="muted">Nenhuma cor encontrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?= e((string)$r['id']) ?></td>
                                    <td>
                                        <?= color_swatch((string)$r['name']) ?>
                                        <strong><?= e((string)$r['name']) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        if ($r['user_list'] === '' || $r['user_list'] === null) {
                                            echo "<span class='muted'>—</span>";
                                        } else {
                                            foreach (explode('|', (string)$r['user_list']) as $uname) {
                                                $uname = trim($uname);
                                                if ($uname === '') continue;
                                                echo "<span class='chip'>" . e($uname) . "</span> ";
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td class="actions-col">

                                        <a class="btn btn--sm" href="color_users.php?id=<?= e((string)$r['id']) ?>">Vincular usuários</a>
                                        <form class="inline-form" method="post" action="color_delete.php"
                                            onsubmit="return confirm('Remover esta cor e desvincular seus usuários?');">
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