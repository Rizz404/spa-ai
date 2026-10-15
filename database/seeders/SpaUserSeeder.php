<?php

namespace Database\Seeders;

use App\Models\Spa;
use App\Models\User;
use Illuminate\Database\Seeder;

class SpaUserSeeder extends Seeder
{
    public function run(): void
    {
        $spa = Spa::where('name', SpaSeeder::NAME)->firstOrFail();
        $owner = User::where('email', UserSeeder::OWNER_EMAIL)->firstOrFail();

        $spa->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'owner', 'status' => 'active'],
        ]);
    }
}
