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

## Prints
<img width="1142" height="541" alt="image" src="https://github.com/user-attachments/assets/ac9ae421-31ca-4942-8ed9-a1952b1c4aa3" />

<img width="998" height="331" alt="image" src="https://github.com/user-attachments/assets/5a81b5cc-a773-48d7-a096-c7f4a9c9943b" />

<img width="995" height="551" alt="image" src="https://github.com/user-attachments/assets/990c2727-1ee7-483f-ae1d-65380cd79e86" />

<img width="1018" height="345" alt="image" src="https://github.com/user-attachments/assets/dce3565f-63be-409a-9f82-21b736c3b960" />
