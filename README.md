# Teste de conhecimentos PHP + Banco de dados

## Rennan Alberto de Oliveira Nagel

##### Objetivo

Criar um Crud simples, totalmente desenvolvido em PHP, sem a utilização de frameworks, onde será possível Criar/Editar/Excluir/Listar usuários. O sistema também deve possuir a possibilidade de vincular/desvincular várias cores ao usuário.

## Para Rodar

```
# na raiz do projeto
php -S 0.0.0.0:7070
# abra no navegador
http://localhost:7070

```

## Páginas / Fluxo

`index.php`
Lista usuários, mostra as cores vinculadas (chips com quadradinho), botões:

- Novo Usuário → `user_form.php?action=create`

Editar → `user_form.php?action=edit&id=...`

Vincular cores → `user_colors.php?id=...`

Excluir → `user_delete.php`

`user_form.php`
Formulário único (criar/editar) com validação e checagem de e-mail único.

`user_delete.php`
Exclui o usuário e remove vínculos em user_colors (transação).

`colors.php`
Lista cores (quadradinho ao lado do nome) e usuários vinculados; botões:

- Nova Cor → `color_form.php`

Vincular usuários → `color_users.php?id=...`

Excluir → `color_delete.php`

`color_form.php`
Criação de cor (checa duplicidade case-insensitive).

`color_users.php`
Vincula/desvincula usuários a uma cor (checkbox).

`color_delete.php`
Exclui a cor e remove vínculos em user_colors (transação).

`style.css`
CSS centralizado (tabelas, botões, chips, formulários, swatches de cor).
