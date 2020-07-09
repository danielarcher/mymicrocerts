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
        DB::table('plan')->insert([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Free Subscription',
            'description' => 'For developers and small teams',
            'currency' => 'USD',
            'price' => 0,
            'credits' => 50,
            'api_requests_per_hour' => 1000,
            'active' => true,
        ]);
        DB::table('plan')->insert([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Plan I',
            'description' => 'For small business',
            'currency' => 'USD',
            'price' => 19.90,
            'credits' => 200,
            'api_requests_per_hour' => 2500,
            'active' => true,
        ]);
        DB::table('plan')->insert([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Plan II',
            'description' => 'For medium business',
            'currency' => 'USD',
            'price' => 29.90,
            'credits' => 500,
            'api_requests_per_hour' => 5000,
            'active' => true,
        ]);
    }
}
