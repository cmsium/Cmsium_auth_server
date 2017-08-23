<?php

$routes = [
    // Basic API calls
    '/' => 'loginForm',
    '/login/submit' => 'loginUser',
    '/login/submit/json' => 'loginUserJSON',
    '/register' => 'registerForm',
    '/register/submit' => 'registerUser',
    '/register/submit/json' => 'registerUserJSON',
    '/logout' => 'logoutUser',
    '/logout/json' => 'logoutUserJSON',
    '/token/check' => 'checkToken',
    '/info/roles' => 'getUserRolesJSON',
    // Users web interface
    '/users' => 'allUsers',
    '/users/show' => 'showUser',
    '/users/create' => 'createUserForm',
    '/users/update_menu' => 'updateMenu',
    '/users/edit' => 'updateForm',
    '/users/update' => 'updateUser',
    '/users/edit/props' => 'updatePropsForm',
    '/users/update/props' => 'updateUserProps'
];