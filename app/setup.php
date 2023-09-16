<?php
use App\Models\Currency;
use App\Models\Store;
use App\Models\User;

function setup_stores() : void{
    $stores=[
        [
            'name'=>'Amazon UAE',
            'host'=>'amazon.ae',
            'image'=>'amazon_ae.png',
            'referral'=>'cybrarist08-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'AED'])->id,
            'slug'=>'amazon_ae'
        ],
        [
            'name'=>'Amazon USA',
            'host'=>'amazon.com',
            'image'=>'amazon.png',
            'referral'=>'cybrarist-20',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'amazon_com'
        ],
        [
            'name'=>'Amazon UK',
            'host'=>'amazon.co.uk',
            'image'=>'amazon_co_uk.png',
            'referral'=>'cybrarist01-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'£'])->id,
            'slug'=>'amazon_uk'
        ],
        [
            'name'=>'Amazon Germany',
            'host'=>'amazon.de',
            'image'=>'amazon_de.png',
            'referral'=>'cybrarist0f-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_de'
        ],
        [
            'name'=>'Amazon France',
            'host'=>'amazon.fr',
            'image'=>'amazon_fr.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_fr'
        ],
        [
            'name'=>'Amazon Italy',
            'host'=>'amazon.it',
            'image'=>'amazon_it.png',
            'referral'=>'cybrarist07f-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_it'
        ],
        [
            'name'=>'Amazon Saudi Arabia',
            'host'=>'amazon.sa',
            'image'=>'amazon_sa.png',
            'referral'=>'cybrarist05-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'SAR'])->id,
            'slug'=>'amazon_sa'
        ],
        [
            'name'=>'Amazon Spain',
            'host'=>'amazon.es',
            'image'=>'amazon_es.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_es'
        ],
        [
            'name'=>'Amazon Poland',
            'host'=>'amazon.pl',
            'image'=>'amazon_pl.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'zł'])->id,
            'slug'=>'amazon_pl'
        ],
        [
            'name'=>'Amazon Turkey',
            'host'=>'amazon.com.tr',
            'image'=>'amazon_com_tr.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'TL'])->id,
            'slug'=>'amazon_com_tr'
        ],
        [
            'name'=>'Amazon Australia',
            'host'=>'amazon.com.au',
            'image'=>'amazon_com_au.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'amazon_com_au'
        ],
        [
            'name'=>'Amazon Brazil',
            'host'=>'amazon.com.br',
            'image'=>'amazon_com_br.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'R$'])->id,
            'slug'=>'amazon_com_br'
        ],
        [
            'name'=>'Amazon Canada',
            'host'=>'amazon.ca',
            'image'=>'amazon_ca.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'amazon_ca'
        ],
        [
            'name'=>'Amazon China',
            'host'=>'amazon.cn',
            'image'=>'amazon.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'¥'])->id,
            'slug'=>'amazon_tr'
        ],
        [
            'name'=>'Amazon Egypt',
            'host'=>'amazon.eg',
            'image'=>'amazon_eg.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'EGP'])->id,
            'slug'=>'amazon_eg'
        ],
        [
            'name'=>'Amazon Japan',
            'host'=>'amazon.co.jp',
            'image'=>'amazon_co_jp.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'¥'])->id,
            'slug'=>'amazon_co_jp'
        ],
        [
            'name'=>'Amazon India',
            'host'=>'amazon.in',
            'image'=>'amazon_in.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'₹'])->id,
            'slug'=>'amazon_in'
        ],

        [
            'name'=>'Amazon Mexicon',
            'host'=>'amazon.com.mx',
            'image'=>'amazon_com_mx.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'amazon_com_mx'
        ],


        [
            'name'=>'Amazon Netherlands',
            'host'=>'amazon.nl',
            'image'=>'amazon_nl.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_nl'
        ],


        [
            'name'=>'Amazon Singapore',
            'host'=>'amazon.sg',
            'image'=>'amazon_sg.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'S$'])->id,
            'slug'=>'amazon_sg'
        ],


        [
            'name'=>'Amazon Sweden',
            'host'=>'amazon.se',
            'image'=>'amazon_se.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'kr'])->id,
            'slug'=>'amazon_se'
        ],
        [
            'name'=>'Amazon Belgium',
            'host'=>'amazon.com.be',
            'image'=>'amazon.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_com_be'
        ],


//        Ebay
        [
            'name'=>'Ebay',
            'host'=>'ebay.com',
            'image'=>'ebay.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'ebay_com'
        ],

    ];

    foreach ($stores as $store)
    {
        Store::updateOrCreate(
            ['host'=>$store['host']],
            $store
        );
    }
}
function setup_main_user() :void {
    $users=User::all()->count();
    if ($users == 0)
        \App\Models\User::factory()->create([
            'name' => env( 'USER_NAME' , 'Test User'),
            'email' => env( 'USER_EMAIL_ADDRESS' , 'test@test.com'),
            'password'=>env('USER_PASSWORD', 'password')
        ]);
}



///Check the following
/// applicablePromotionList_feature_div
