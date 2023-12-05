<?php
use App\Models\Currency;
use App\Models\User;

function setup_stores() : array{
    return [
        [
            'name'=>'Amazon UAE',
            'domain'=>'amazon.ae',
            'image'=>'amazon_ae.png',
            'referral'=>'cybrarist0b-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'AED'])->id,
            'slug'=>'amazon_ae'
        ],
        [
            'name'=>'Amazon USA',
            'domain'=>'amazon.com',
            'image'=>'amazon.png',
            'referral'=>'cybrarist0e-20',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'amazon_com'
        ],
        [
            'name'=>'Amazon UK',
            'domain'=>'amazon.co.uk',
            'image'=>'amazon_co_uk.png',
            'referral'=>'cybrarist01-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'£'])->id,
            'slug'=>'amazon_uk'
        ],
        [
            'name'=>'Amazon Germany',
            'domain'=>'amazon.de',
            'image'=>'amazon_de.png',
            'referral'=>'cybrarist0f-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_de'
        ],
        [
            'name'=>'Amazon France',
            'domain'=>'amazon.fr',
            'image'=>'amazon_fr.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_fr'
        ],
        [
            'name'=>'Amazon Italy',
            'domain'=>'amazon.it',
            'image'=>'amazon_it.png',
            'referral'=>'cybrarist07f-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_it'
        ],
        [
            'name'=>'Amazon Saudi Arabia',
            'domain'=>'amazon.sa',
            'image'=>'amazon_sa.png',
            'referral'=>'cybrarist05-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'SAR'])->id,
            'slug'=>'amazon_sa'
        ],
        [
            'name'=>'Amazon Spain',
            'domain'=>'amazon.es',
            'image'=>'amazon_es.png',
            'referral'=>'cybrarist0e4-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_es'
        ],
        [
            'name'=>'Amazon Poland',
            'domain'=>'amazon.pl',
            'image'=>'amazon_pl.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'zł'])->id,
            'slug'=>'amazon_pl'
        ],
        [
            'name'=>'Amazon Turkey',
            'domain'=>'amazon.com.tr',
            'image'=>'amazon_com_tr.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'TL'])->id,
            'slug'=>'amazon_com_tr'
        ],
        [
            'name'=>'Amazon Australia',
            'domain'=>'amazon.com.au',
            'image'=>'amazon_com_au.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'amazon_com_au'
        ],
        [
            'name'=>'Amazon Brazil',
            'domain'=>'amazon.com.br',
            'image'=>'amazon_com_br.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'R$'])->id,
            'slug'=>'amazon_com_br'
        ],
        [
            'name'=>'Amazon Canada',
            'domain'=>'amazon.ca',
            'image'=>'amazon_ca.png',
            'referral'=>'cybrarist08-20',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'amazon_ca'
        ],
        [
            'name'=>'Amazon China',
            'domain'=>'amazon.cn',
            'image'=>'amazon.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'¥'])->id,
            'slug'=>'amazon_tr'
        ],
        [
            'name'=>'Amazon Egypt',
            'domain'=>'amazon.eg',
            'image'=>'amazon_eg.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'EGP'])->id,
            'slug'=>'amazon_eg'
        ],
        [
            'name'=>'Amazon Japan',
            'domain'=>'amazon.co.jp',
            'image'=>'amazon_co_jp.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'¥'])->id,
            'slug'=>'amazon_co_jp'
        ],
        [
            'name'=>'Amazon India',
            'domain'=>'amazon.in',
            'image'=>'amazon_in.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'₹'])->id,
            'slug'=>'amazon_in'
        ],
        [
            'name'=>'Amazon Mexicon',
            'domain'=>'amazon.com.mx',
            'image'=>'amazon_com_mx.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'amazon_com_mx'
        ],
        [
            'name'=>'Amazon Netherlands',
            'domain'=>'amazon.nl',
            'image'=>'amazon_nl.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_nl'
        ],
        [
            'name'=>'Amazon Singapore',
            'domain'=>'amazon.sg',
            'image'=>'amazon_sg.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'S$'])->id,
            'slug'=>'amazon_sg'
        ],
        [
            'name'=>'Amazon Sweden',
            'domain'=>'amazon.se',
            'image'=>'amazon_se.png',
            'referral'=>'cybrarist09-21',
            'currency_id'=>Currency::firstOrCreate(['code'=>'kr'])->id,
            'slug'=>'amazon_se'
        ],
        [
            'name'=>'Amazon Belgium',
            'domain'=>'amazon.com.be',
            'image'=>'amazon.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
            'slug'=>'amazon_com_be'
        ],

//Ebay
        [
            'name'=>'Ebay',
            'domain'=>'ebay.com',
            'image'=>'ebay.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'ebay_com'
        ],
        [
            'name'=>'Walmart USA',
            'domain'=>'walmart.com',
            'image'=>'walmart.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'walmart_com'
        ],

        [
            'name'=>'Walmart Canada',
            'domain'=>'walmart.ca',
            'image'=>'walmart.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'walmart_ca'
        ],

        [
            'name'=>'Target',
            'domain'=>'target.com',
            'image'=>'target.png',
            'referral'=>'none',
            'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
            'slug'=>'target_com'
        ],

    ];
}
//
//function setup_main_user() :void {
//    $users=User::all()->count();
//    if ($users == 0)
//        \App\Models\User::factory()->create([
//            'name' => env( 'USER_NAME' , 'Test User'),
//            'email' => env( 'USER_EMAIL_ADDRESS' , 'test@test.com'),
//            'password'=>env('USER_PASSWORD', 'password')
//        ]);
//}



///Check the following
/// applicablePromotionList_feature_div
