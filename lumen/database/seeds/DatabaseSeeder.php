<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use MyCerts\Domain\Roles;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('candidate')->insert([
            'id' => Uuid::uuid4()->toString(),
            'email' => 'admin@mymicrocerts.com',
            'password' => Hash::make('admin'),
            'first_name' => 'admin',
            'last_name' => 'admin',
            'verified' => true,
            'active' => true,
            'role' => Roles::ADMIN,
        ]);
    }
}
