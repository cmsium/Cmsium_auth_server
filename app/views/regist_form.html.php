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
<div id="node_003" class="users">
    <form action="/register/submit" method="post" accept-charset="UTF-8" name="formname" id="form_id" class="form">
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
        <label><input type="submit" value="Send" class="active"></label>
    </form>
</div>
</body>
</html>

