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
        $companyId = Uuid::uuid4()->toString();
        DB::table('company')->insert([
            'id' => $companyId,
            'name' => 'MyMicroCerts',
            'country' => 'Brazil',
            'email' => 'company@mymicrocerts.com',
            'contact_name' => 'company owner',
        ]);
        DB::table('candidate')->insert([
            'id' => Uuid::uuid4()->toString(),
            'company_id' => $companyId,
            'email' => 'company@mymicrocerts.com',
            'password' => Hash::make('company'),
            'first_name' => 'company',
            'last_name' => 'company',
            'verified' => true,
            'active' => true,
            'role' => Roles::COMPANY,
        ]);
        DB::table('candidate')->insert([
            'id' => Uuid::uuid4()->toString(),
            'company_id' => $companyId,
            'email' => 'user@mymicrocerts.com',
            'password' => Hash::make('user'),
            'first_name' => 'user',
            'last_name' => 'user',
            'verified' => true,
            'active' => true,
            'role' => Roles::CANDIDATE,
        ]);
        DB::table('candidate')->insert([
            'id' => Uuid::uuid4()->toString(),
            'email' => 'guest@mymicrocerts.com',
            'password' => Hash::make('guest'),
            'first_name' => 'guest',
            'last_name' => 'guest',
            'verified' => true,
            'active' => true,
            'role' => Roles::GUEST,
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
