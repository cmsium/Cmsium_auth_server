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
        <li><a href="/users/dashboard/show">Информация о себе</a></li>
        <li><a href="/users/dashboard/update_menu">Редактировать информацию о себе</a></li>
        <li><a href="/users/dashboard/password">Сменить пароль</a></li>
    </ul>
</div>
<div id="node_003" class="users">
    <form action="/users/dashboard/password/update" method="post" accept-charset="UTF-8" name="formname" id="form_id" class="form">
        <h1 class="header">Изменить пароль</h1>
        <br>
        <label>Новый пароль: </label>
        <input onfocusout="validate_alphanumeric()" type="password" name="password"><br>
        <label>Повторите новый пароль: </label>
        <input onfocusout="validate_alphanumeric()" type="password" name="password_repeat"><br>
        <br>
        <label><input type="submit" value="Send" class="active"></label>
    </form>
</div>
</body>
</html>

