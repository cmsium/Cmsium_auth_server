<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Dashboard menu</title>
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
<div id="node_002" class="users">
    <ul>
        <?php echo '<li><a href="http://'.Config::get('main_domain').'/users/first_menu_page">Главная</a></li>' ?><li><a href="/users/dashboard/show">Информация о себе</a></li>
        <li><a href="/users/dashboard/update_menu">Редактировать информацию о себе</a></li>
        <li><a href="/users/dashboard/password">Сменить пароль</a></li>
        <?php
            echo '<li><a href="http://'.Config::get('main_domain').'/portfolio/select_event">Портфолио</a></li>
        <li><a href="http://'.Config::get('main_domain').'/portfolio/create_event_page">Создать новое событие</a></li>';
        ?>
    </ul>
</div>
</body>
</html>

