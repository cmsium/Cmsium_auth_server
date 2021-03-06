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
        <?php echo '<li><a href="http://'.Config::get('main_domain').'/users/first_menu_page">Главная</a></li>' ?><li><a href="/users">Пользователи</a></li>
        <li><a href="/users/create">Создание пользователя</a></li>
        <li><a href="/users/new_role">Создать новую роль</a></li>
        <li><a href="/users/delete_role">Удалить роль</a></li>
        <li><a href="/users/actions_to_role_page">Установка разрешений на роли</a></li>
        <li><a href="/users/actions_out_of_role_page">Удаление разрешений на роли</a></li><li><a href="/users/draft">Новые пользователи</a></li>
    </ul>
</div>
<div id="node_003" class="users">
    <form action="/users/create/submit" method="post" accept-charset="UTF-8" name="formname" id="form_id" class="form">
        <h1 class="header">Регистрация пользователя</h1>
        <br>
        <label>Имя пользователя: </label>
        <input onfocusout="validate_alphanumeric()" type="text" name="username"><br>
        <label>Пароль: </label>
        <input onfocusout="validate_alphanumeric()" type="password" name="password"><br>
        <label>Повторите пароль: </label>
        <input onfocusout="validate_alphanumeric()" type="password" name="password_repeat"><br>
        <label>E-Mail: </label>
        <input onfocusout="validate_e-mail()" type="text" name="email"><br>
        <label>Телефон: </label>
        <input onfocusout="validate_digital()" type="text" name="phone"><br>
        <label>Имя: </label>
        <input onfocusout="validate_alpha()" type="text" name="firstname"><br>
        <label>Фамилия: </label>
        <input onfocusout="validate_alpha()" type="text" name="lastname"><br>
        <label>Отчество: </label>
        <input onfocusout="validate_alpha()" type="text" name="middlename"><br>
        <label>Дата рождения: </label>
        <input onfocusout="validate_alpha()" type="date" name="birth_date"><br>
        <label>Место рождения: </label>
        <input onfocusout="validate_alpha()" id="address_field" type="text" name="birthplace"><br>
        <label>Роль: </label>
        <select name="roles[]" multiple>
            <?php
            foreach ($roles as $role) {
                echo "<option value=\"{$role['id']}\">{$role['t_name']}</option>";
            }
            ?>
        </select>
        <br>
        <label><input type="submit" value="Send" class="active"></label>
    </form>
</div>
</body>
</html>
