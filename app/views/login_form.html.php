<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Authentication page</title>
    <style>
        .base {
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
            width: 900px;
            border: 1px solid #000000;
            background: yellow;
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
    <script defer></script><script defer></script>
</head>
<body>
<div id="node_003" class="users">
    <form action="/login/submit" method="POST" name="formname" id="form_id" class="form">
        <h1 class="header">Авторизация пользователя</h1>
        <br>
        <label>Способ авторизации: </label>
        <p><input onfocusout="validate_alphanumeric()" name="logintype" type="radio" value="username" checked> Имя пользователя</p>
        <p><input onfocusout="validate_e_mail()" name="logintype" type="radio" value="email"> E-Mail</p>
        <p><input onfocusout="validate_alphanumeric()" name="logintype" type="radio" value="phone"> Телефон</p>
        <label>Логин: </label>
        <input onfocusout="validate_alphanumeric()" type="text" name="login"><br>
        <br>
        <label>Пароль: </label>
        <input onfocusout="validate_alphanumeric()" type="password" name="password"><br>
        <br>
        <?php
        if ($uri) {
            echo "<input type=\"hidden\" name=\"redirect_uri\" value=\"$uri\">";
        }
        ?>
        <label><input type="submit" value="Отправить" class="active"></label>
    </form>
</div>
</body>
</html>