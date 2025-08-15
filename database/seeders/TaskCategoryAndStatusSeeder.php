<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskCategoryAndStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tblTaskCategories')->insert([
            ['category_type' => 'Task', 'category_color' => '#f87171'],
            ['category_type' => 'Bug', 'category_color' => '#fbbf24'],
            ['category_type' => 'Request', 'category_color' => '#2dd4bf'],
            ['category_type' => 'Improvement', 'category_color' => '#60a5fa'],
            ['category_type' => 'Research', 'category_color' => '#c084fc'],
            ['category_type' => 'Design', 'category_color' => '#f472b6'],
            ['category_type' => 'Testing', 'category_color' => '#34d399'],
            ['category_type' => 'Documentation', 'category_color' => '#a3a3a3'],
            ['category_type' => 'Deployment', 'category_color' => '#f97316'],
            ['category_type' => 'Other', 'category_color' => '#a6c5fd'],
        ]);

        DB::table('tblTaskStatuses')->insert([
            ['status_type' => 'Open', 'status_color' => '#f87171'],
            ['status_type' => 'In Progress', 'status_color' => '#fbbf24'],
            ['status_type' => 'In Review', 'status_color' => '#60a5fa'],
            ['status_type' => 'Pending Approval', 'status_color' => '#facc15'],
            ['status_type' => 'Blocked', 'status_color' => '#f43f5e'],
            ['status_type' => 'Resolved', 'status_color' => '#22c55e'],
            ['status_type' => 'Closed', 'status_color' => '#6b7280'],
            ['status_type' => 'Reopened', 'status_color' => '#a855f7'],
            ['status_type' => 'Cancelled', 'status_color' => '#9ca3af'],
        ]);
    }
}
