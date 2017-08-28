<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Registration Page</title>
    <style>
        .admin {
            margin-left: auto;
            margin-right: auto;
            width: 900px;
            background: indianred;
        }

        table, th, td {
            border: 1px solid black;
        }

        .users {
            margin-left: auto;
            margin-right: auto;
            width: 900px;
            background: indianred;
        }

        table, th, td {
            border: 1px solid black;
        }
    </style>
</head>
<body>
<div id="node_001" class="admin">
    <ul>
        <li><a href="/users">Пользователи</a></li>
        <li><a href="/users/create">Создание пользователя</a></li>
        <li><a href="/users/new_role">Создать новую роль</a></li>
        <li><a href="/users/delete_role">Удалить роль</a></li>
        <li><a href="/users/actions_to_role_page">Установка разрешений на роли</a></li>
        <li><a href="/users/actions_out_of_role_page">Удаление разрешений на роли</a></li>
    </ul>
</div>
<div id="node_003" class="users">
    <form action="/users/destroy_role" method="GET" name="formname" id="form_id" class="form">
        <h1 class="header">Удаление ролей</h1>
        <label>Роль</label>
        <select name="role">
            <?php
            foreach (User::getAllRoles() as $role) {
                echo "<option value=\"{$role['id']}\">{$role['t_name']}</option>";
            }
            ?>
        </select>
        <label><input type="submit" value="Отправить" class="active"></label>
    </form>
</div>
</body>
</html>

