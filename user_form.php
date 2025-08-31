<?php

require __DIR__ . '/connection.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}


$pdo = (new Connection())->getConnection();

// Descobre se é create ou edit
$action = $_GET['action'] ?? $_POST['action'] ?? 'create';
$action = in_array($action, ['create', 'edit'], true) ? $action : 'create';

$id = null;
$user = ['name' => '', 'email' => ''];
$errors = [];

// Se for edição, carregue o usuário
if ($action === 'edit') {
    if (!isset($_GET['id']) && !isset($_POST['id'])) {
        http_response_code(400);
        exit('ID ausente para edição');
    }
    $id = (int)($_GET['id'] ?? $_POST['id']);
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$found) {
        http_response_code(404);
        exit('Usuário não encontrado');
    }
    $user = $found;
}

// Se recebeu POST, processa salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));

    if ($name === '') {
        $errors['name']  = 'Informe o nome.';
    }
    if ($email === '') {
        $errors['email'] = 'Informe o e-mail.';
    }
    if ($email !== '' && mb_strlen($email) > 100) {
        $errors['email'] = 'E-mail muito longo.';
    }
    if ($email !== '' && strpos($email, '@') === false) {
        $errors['email'] = 'E-mail deve conter "@".';
    }

    if (!$errors) {
        if ($action === 'create') {
            $q = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $q->execute([$email]);
            if ($q->fetch()) {
                $errors['email'] = 'Já existe usuário com esse e-mail.';
            }
        } else { // edit
            $q = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
            $q->execute([$email, $id]);
            if ($q->fetch()) {
                $errors['email'] = 'Já existe outro usuário com esse e-mail.';
            }
        }
    }

    if (!$errors) {
        if ($action === 'create') {
            $ins = $pdo->prepare('INSERT INTO users(name, email) VALUES(?, ?)');
            $ins->execute([$name, $email]);
        } else {
            $upd = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $upd->execute([$name, $email, $id]);
        }
        header('Location: index.php');
        exit;
    }

    $user['name']  = $name;
    $user['email'] = $email;
}

// Título e labels dinâmicos
$isEdit = ($action === 'edit');
$title  = $isEdit ? "Editar Usuário #" . e((string)$user['id']) : "Novo Usuário";
$btn    = $isEdit ? "Salvar alterações" : "Adicionar";
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title><?= e($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <header class="topbar">
            <h1 class="title"><?= e($title) ?></h1>
            <div class="actions">
                <a class="btn btn--secondary" href="index.php">← Voltar</a>
            </div>
        </header>

        <div class="card">
            <form class="form" method="post" action="">
                <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'create' ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= e((string)$user['id']) ?>">
                <?php endif; ?>

                <div class="form-row">
                    <label class="label" for="name">Nome</label>
                    <input class="input" id="name" name="name" required value="<?= e($user['name']) ?>">
                    <?php if (isset($errors['name'])): ?><div class="error"><?= e($errors['name']) ?></div><?php endif; ?>
                </div>

                <div class="form-row">
                    <label class="label" for="email">E-mail</label>
                    <input class="input" id="email" name="email" required value="<?= e($user['email']) ?>">
                    <?php if (isset($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit"><?= e($btn) ?></button>
                    <a class="btn btn--secondary" href="index.php">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>