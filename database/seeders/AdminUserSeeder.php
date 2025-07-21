<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
            DB::table('tblUsers')->insert([
            'username' => 'admin',
            'email' => 'admin@rikai.tech',
            'password' => Hash::make('Admin@1234'),
            'verify' => 1,
        ]);
    }
}
