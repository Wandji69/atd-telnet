<?php

/*
|--------------------------------------------------------------------------
| Local manager account
|--------------------------------------------------------------------------
|
| Values used only by Database\Seeders\ManagerSeeder to create one synthetic
| manager on a developer machine. The password has no default on purpose: it
| comes from your local .env, never from the repository.
|
*/

return [

    'name' => env('MANAGER_NAME', 'Demo Manager'),

    'email' => env('MANAGER_EMAIL', 'manager@example.test'),

    'password' => env('MANAGER_PASSWORD'),

];
