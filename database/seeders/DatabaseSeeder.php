<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Currency;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Psy\Util\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $currencies=['AED', '$', '£', '€', 'SAR'];
        foreach ($currencies as $currency)
            Currency::create([
                'code'=>$currency
            ]);

        $services=[
            [
                'name'=>'Amazon UAE',
                'url'=>'https://amazon.ae',
                'image'=>'1.svg',
                'referral'=>'cybrarist04-21',
                'currency_id'=>1
            ],
            [
                'name'=>'Amazon USA',
                'url'=>'https://amazon.com',
                'image'=>'2.svg',
                'referral'=>'cybrarist-20',
                'currency_id'=>2
            ],
            [
                'name'=>'Amazon UK',
                'url'=>'https://amazon.co.uk',
                'image'=>'3.svg',
                'referral'=>'cybrarist01-21',
                'currency_id'=>3
            ],
            [
                'name'=>'Amazon Germany',
                'url'=>'https://amazon.de',
                'image'=>'4.svg',
                'referral'=>'cybrarist0f-21',
                'currency_id'=>4
            ],
            [
                'name'=>'Amazon France',
                'url'=>'https://amazon.fr',
                'image'=>'5.svg',
                'referral'=>'cybrarist09-21',
                'currency_id'=>4
            ],
            [
                'name'=>'Amazon Italy',
                'url'=>'https://amazon.it',
                'image'=>'6.svg',
                'referral'=>'cybrarist07f-21',
                'currency_id'=>4
            ],
            [
                'name'=>'Amazon Saudi Arabia',
                'url'=>'https://amazon.sa',
                'image'=>'7.svg',
                'referral'=>'cybrarist05-21',
                'currency_id'=>5
            ],
            [
                'name'=>'Amazon Spain',
                'url'=>'https://amazon.es',
                'image'=>'8.svg',
                'referral'=>'cybrarist0e4-21',
                'currency_id'=>4
            ],
        ];

        foreach ($services as $service)
            Service::create($service);

        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password'=>Hash::make('password')
        ]);


    }
}
