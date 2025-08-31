<?php

declare(strict_types=1);

require __DIR__ . '/connection.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$pdo = (new Connection())->getConnection();

$errors = [];
$name   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));

    if ($name === '') {
        $errors['name'] = 'Informe o nome da cor.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Nome muito longo.';
    }

    if (!$errors) {
        $chk = $pdo->prepare('SELECT id FROM colors WHERE lower(name) = lower(?) LIMIT 1');
        $chk->execute([$name]);
        if ($chk->fetch()) {
            $errors['name'] = 'Já existe uma cor com esse nome.';
        }
    }

    if (!$errors) {
        $ins = $pdo->prepare('INSERT INTO colors(name) VALUES (?)');
        $ins->execute([$name]);
        header('Location: colors.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Nova cor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <header class="topbar">
            <h1 class="title">Nova cor</h1>
            <div class="actions">
                <a class="btn btn--secondary" href="colors.php">← Voltar</a>
            </div>
        </header>

        <div class="card">
            <form class="form" method="post" action="">
                <div class="form-row">
                    <label class="label" for="name">Nome da cor</label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input class="input" id="name" name="name" required
                            placeholder="Ex.: Blue, Red, #ff00ff..."
                            value="<?= e($name) ?>"
                            oninput="document.getElementById('preview').style.background=this.value||'transparent'">
                        <span id="preview" class="swatch" title="Prévia"></span>
                    </div>
                    <?php if (isset($errors['name'])): ?>
                        <div class="error"><?= e($errors['name']) ?></div>
                    <?php else: ?>
                        <small class="muted">Dica: pode usar nomes CSS (Blue, Red...) ou hex (#RRGGBB).</small>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit">Adicionar</button>
                    <a class="btn btn--secondary" href="colors.php">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>