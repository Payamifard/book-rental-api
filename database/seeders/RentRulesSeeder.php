<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RentRule;

class RentRulesSeeder extends Seeder
{
    public function run()
    {
        RentRule::create([
            'name' => 'late_fee',
            'description' => 'جریمه دیرکرد هر روز',
            'value' => 5000,
            'value_type' => 'day_rate',
            'active' => true,
        ]);

        RentRule::create([
            'name' => 'max_rent_days',
            'description' => 'حداکثر تعداد روز اجاره',
            'value' => 30,
            'value_type' => 'fixed',
            'active' => true,
        ]);

        RentRule::create([
            'name' => 'damage_fee',
            'description' => 'جریمه آسیب دیدن کتاب',
            'value' => 20000,
            'value_type' => 'fixed',
            'active' => true,
        ]);
    }
}
