<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Currency;
use App\Models\Service;
use App\Models\User;
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

        $currencies=['AED', '$', '£', '€', 'SAR' , 'zł'];
        foreach ($currencies as $currency)
            Currency::updateOrCreate(
                [
                    'code'=>$currency
                ]
                ,[
                    'code'=>$currency
                ]);

        $services=[
            [
                'name'=>'Amazon UAE',
                'url'=>'https://amazon.ae',
                'image'=>'1.png',
                'referral'=>'cybrarist04-21',
                'currency_id'=>1
            ],
            [
                'name'=>'Amazon USA',
                'url'=>'https://amazon.com',
                'image'=>'2.png',
                'referral'=>'cybrarist-20',
                'currency_id'=>2
            ],
            [
                'name'=>'Amazon UK',
                'url'=>'https://amazon.co.uk',
                'image'=>'3.png',
                'referral'=>'cybrarist01-21',
                'currency_id'=>3
            ],
            [
                'name'=>'Amazon Germany',
                'url'=>'https://amazon.de',
                'image'=>'4.png',
                'referral'=>'cybrarist0f-21',
                'currency_id'=>4
            ],
            [
                'name'=>'Amazon France',
                'url'=>'https://amazon.fr',
                'image'=>'5.png',
                'referral'=>'cybrarist09-21',
                'currency_id'=>4
            ],
            [
                'name'=>'Amazon Italy',
                'url'=>'https://amazon.it',
                'image'=>'6.png',
                'referral'=>'cybrarist07f-21',
                'currency_id'=>4
            ],
            [
                'name'=>'Amazon Saudi Arabia',
                'url'=>'https://amazon.sa',
                'image'=>'7.png',
                'referral'=>'cybrarist05-21',
                'currency_id'=>5
            ],
            [
                'name'=>'Amazon Spain',
                'url'=>'https://amazon.es',
                'image'=>'8.png',
                'referral'=>'cybrarist0e4-21',
                'currency_id'=>4
            ],
            [
                'name'=>'Amazon Poland',
                'url'=>'https://amazon.pl',
                'image'=>'9.png',
                'referral'=>'cybrarist0e4-21',
                'currency_id'=>6
            ],
        ];

        foreach ($services as $service)
            Service::updateOrCreate(
                ['url'=>$service['url']],
                $service
            );
        $users=User::all()->count();
        if ($users == 0)
            \App\Models\User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@test.com',
                'password'=>Hash::make('password')
            ]);


    }
}
