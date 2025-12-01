<?php

namespace Database\Seeders;

use App\Models\BoardStage;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::create([
            'name' => 'Ariel Recto',
            'email' => 'arielrecto29@gmail.com',
            'password' => Hash::make('ariel123'),
        ]);


        $defaultStages = [
            ['name' => 'To Do', 'position' => 1],
            ['name' => 'In Progress', 'position' => 2],
            ['name' => 'Done', 'position' => 3],
        ];


        collect($defaultStages)->each(function ($stage) use ($user) {
            BoardStage::create([
                'name' => $stage['name'],
                'position' => $stage['position'],
                'boardable_type' => User::class,
                'boardable_id' => $user->id,
                'color' => '#000000',
            ]);
        });
    }
}
