<?php

$routes = [
    // Basic API calls
    '/' => ['loginForm'],
    '/login/submit' => ['loginUser'],
    '/login/submit/json' => ['loginUserJSON'],
    '/register' => ['registerForm'],
    '/register/submit' => ['registerUser'],
    '/register/submit/json' => ['registerUserJSON'],
    '/logout' => ['logoutUser'],
    '/logout/json' => ['logoutUserJSON'],
    '/token/check' => ['checkToken'],
    '/info/roles' => ['getUserRolesJSON'],
    // Users web interface
    '/users' => ['allUsers', 'auth' => [1]],
    '/users/show' => ['showUser', 'auth' => [1]],
    '/users/create' => ['createUserForm', 'auth' => [1]],
    '/users/update_menu' => ['updateMenu', 'auth' => [1]],
    '/users/edit' => ['updateForm', 'auth' => [1]],
    '/users/update' => ['updateUser', 'auth' => [1]],
    '/users/edit/props' => ['updatePropsForm', 'auth' => [1]],
    '/users/update/props' => ['updateUserProps', 'auth' => [1]],
    '/users/destroy/confirm' => ['deleteUserConfirm', 'auth' => [1]],
    '/users/destroy' => ['deleteUser', 'auth' => [1]],
    // Users dashboard
    // [0] - allowed for any role
    '/users/dashboard' => ['dashboardMenu', 'auth' => [0]],
    '/users/dashboard/show' => ['showSelfUser', 'auth' => [0]],
    '/users/dashboard/update_menu' => ['updateSelfMenu', 'auth' => [0]],
    '/users/dashboard/edit' => ['updateSelfUserForm', 'auth' => [0]],
    '/users/dashboard/update' => ['updateSelfUser', 'auth' => [0]],
    '/users/dashboard/edit/props' => ['updateSelfUserPropsForm', 'auth' => [0]],
    '/users/dashboard/update/props' => ['updateSelfUserProps', 'auth' => [0]],
    '/users/dashboard/password' => ['updatePasswordForm', 'auth' => [0]],
    '/users/dashboard/password/update' => ['updatePassword', 'auth' => [0]],
    // Roles
    '/users/new_role' => ['newRoleForm', 'auth' => [1]],
    '/users/create_role' => ['createRole', 'auth' => [1]],
    '/users/delete_role' => ['deleteRoleForm', 'auth' => [1]],
    '/users/destroy_role' => ['deleteRole', 'auth' => [1]]
];