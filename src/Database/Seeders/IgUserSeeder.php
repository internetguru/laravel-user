<?php

namespace InternetGuru\LaravelUser\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class IgUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->withSocialite(
            User::providers()::GOOGLE,
            '106988997789517413087',
            'George Pavelka',
            'pavelka.iix@gmail.com',
        )->create([
            'name' => 'George',
            'email' => 'george@giftcarder.io',
            'role' => User::roles()::ADMIN,
        ]);

        User::factory()->withSocialite(
            User::providers()::SEZNAM,
            'e273eac080e7aa2ea6158e56620ed3df7ca1c8d1',
            'Pavel Petržela',
            'poulikpetrzela@seznam.cz',
        )->withSocialite(
            User::providers()::GOOGLE,
            '108829846781865836650',
            'Pavel Petržela',
            'poulikpetrzela@gmail.com',
        )->create([
            'name' => 'Pavel',
            'email' => 'pavel@giftcarder.io',
            'role' => User::roles()::ADMIN,
        ]);
    }
}
