<?php

$routes = [
    '/' => 'loginForm',
    '/login/submit' => 'loginUser',
    '/login/submit/json' => 'loginUserJSON',
    '/register' => 'registerForm',
    '/register/submit' => 'registerUser',
    '/register/submit/json' => 'registerUserJSON',
    '/logout' => 'logoutUser',
    '/logout/json' => 'logoutUserJSON',
    '/token/check' => 'checkToken',
    '/info/roles' => 'getUserRolesJSON'
];