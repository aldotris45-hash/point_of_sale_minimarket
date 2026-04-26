<?php
$user = App\Models\User::firstOrCreate(
    ['email' => 'admin@selalufresh.com'],
    [
        'name' => 'Admin Selalu Fresh',
        'password' => bcrypt('password'),
        'role' => App\Enums\RoleStatus::ADMIN->value
    ]
);
echo "User created: " . $user->email . "\n";
