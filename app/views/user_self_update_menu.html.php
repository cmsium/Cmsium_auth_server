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
<ul>
    <?php
    foreach ($roles as $item) {
        if ($item['role'] == 'user_properties') {
            echo "<li><a href='/users/dashboard/edit'>Редактировать основную информацию</a></li>";
        } else {
            echo "<li><a href='/users/dashboard/edit/props?role={$item['role']}'>Редактировать информацию \"{$item['t_role']}\"</a></li>";
        }
    }
    ?>
</ul>
</div>
</body>
</html>

