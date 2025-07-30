<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Holiday::create([
            'name' => 'Martin Luther King\'s Birthday',
            'date' => Carbon::create(2022, 1, 17)
        ]);

        Holiday::create([
            'name' => 'Good Friday',
            'date' => Carbon::create(2022, 4, 15)
        ]);

        Holiday::create([
            'name' => 'Memorial Day',
            'date' => Carbon::create(2022, 5, 30)
        ]);

        Holiday::create([
            'name' => 'Independence Day',
            'date' => Carbon::create(2022, 7, 04)
        ]);

        Holiday::create([
            'name' => 'Labor Day',
            'date' => Carbon::create(2022, 9, 05)
        ]);

        Holiday::create([
            'name' => 'Rosh Hashanah',
            'date' => Carbon::create(2022, 9, 26)
        ]);

        Holiday::create([
            'name' => 'Yom Kippur',
            'date' => Carbon::create(2022, 10, 05)
        ]);

        Holiday::create([
            'name' => 'Veteran\'s Day',
            'date' => Carbon::create(2022, 11, 11)
        ]);

        Holiday::create([
            'name' => 'Thanksgiving Day',
            'date' => Carbon::create(2022, 11, 24)
        ]);

        Holiday::create([
            'name' => 'Friday after Thanksgiving',
            'date' => Carbon::create(2022, 11, 25)
        ]);

        Holiday::create([
            'name' => 'Chief Judge Holiday',
            'date' => Carbon::create(2022, 12, 23)
        ]);

        Holiday::create([
            'name' => 'Christmas Day',
            'date' => Carbon::create(2022, 12, 26)
        ]);

        Holiday::create([
            'name' => 'New Year\'s Day',
            'date' => Carbon::create(2023, 1, 2)
        ]);
    }
}
