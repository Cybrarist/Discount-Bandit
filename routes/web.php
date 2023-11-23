<?php

use App\Models\Currency;
use App\Notifications\NewDiscountNotification;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/test', function () {
    dd();
    dd( Filament::getCurrentPanel()->getId());
});

//$stores=[
//        [
//            'name'=>'Amazon UAE',
//            'domain'=>'amazon.ae',
//            'image'=>'amazon_ae.png',
//            'referral'=>'cybrarist0b-21',
//            'slug'=>'amazon_ae'
//        ],
//        [
//            'name'=>'Amazon USA',
//            'domain'=>'amazon.com',
//            'image'=>'amazon.png',
//            'referral'=>'cybrarist-20',
//            'slug'=>'amazon_com'
//        ],
//        [
//            'name'=>'Amazon UK',
//            'domain'=>'amazon.co.uk',
//            'image'=>'amazon_co_uk.png',
//            'referral'=>'cybrarist01-21',
//            'slug'=>'amazon_uk'
//        ],
//        [
//            'name'=>'Amazon Germany',
//            'domain'=>'amazon.de',
//            'image'=>'amazon_de.png',
//            'referral'=>'cybrarist0f-21',
//            'slug'=>'amazon_de'
//        ],
//        [
//            'name'=>'Amazon France',
//            'domain'=>'amazon.fr',
//            'image'=>'amazon_fr.png',
//            'referral'=>'cybrarist09-21',
//            'slug'=>'amazon_fr'
//        ],
//        [
//            'name'=>'Amazon Italy',
//            'domain'=>'amazon.it',
//            'image'=>'amazon_it.png',
//            'referral'=>'cybrarist07f-21',
//            'slug'=>'amazon_it'
//        ],
//        [
//            'name'=>'Amazon Saudi Arabia',
//            'domain'=>'amazon.sa',
//            'image'=>'amazon_sa.png',
//            'referral'=>'cybrarist05-21',
//            'slug'=>'amazon_sa'
//        ],
//        [
//            'name'=>'Amazon Spain',
//            'domain'=>'amazon.es',
//            'image'=>'amazon_es.png',
//            'referral'=>'cybrarist0e4-21',
//            'slug'=>'amazon_es'
//        ],
//        [
//            'name'=>'Amazon Poland',
//            'domain'=>'amazon.pl',
//            'image'=>'amazon_pl.png',
//            'referral'=>'none',
//            'slug'=>'amazon_pl'
//        ],
//        [
//            'name'=>'Amazon Turkey',
//            'domain'=>'amazon.com.tr',
//            'image'=>'amazon_com_tr.png',
//            'referral'=>'none',
//            'slug'=>'amazon_com_tr'
//        ],
//        [
//            'name'=>'Amazon Australia',
//            'domain'=>'amazon.com.au',
//            'image'=>'amazon_com_au.png',
//            'referral'=>'none',
//            'slug'=>'amazon_com_au'
//        ],
//        [
//            'name'=>'Amazon Brazil',
//            'domain'=>'amazon.com.br',
//            'image'=>'amazon_com_br.png',
//            'referral'=>'none',
//            'slug'=>'amazon_com_br'
//        ],
//        [
//            'name'=>'Amazon Canada',
//            'domain'=>'amazon.ca',
//            'image'=>'amazon_ca.png',
//            'referral'=>'cybrarist08-20',
//            'slug'=>'amazon_ca'
//        ],
//        [
//            'name'=>'Amazon China',
//            'domain'=>'amazon.cn',
//            'image'=>'amazon.png',
//            'referral'=>'none',
//            'slug'=>'amazon_tr'
//        ],
//        [
//            'name'=>'Amazon Egypt',
//            'domain'=>'amazon.eg',
//            'image'=>'amazon_eg.png',
//            'referral'=>'none',
//            'slug'=>'amazon_eg'
//        ],
//        [
//            'name'=>'Amazon Japan',
//            'domain'=>'amazon.co.jp',
//            'image'=>'amazon_co_jp.png',
//            'referral'=>'none',
//            'slug'=>'amazon_co_jp'
//        ],
//        [
//            'name'=>'Amazon India',
//            'domain'=>'amazon.in',
//            'image'=>'amazon_in.png',
//            'referral'=>'none',
//            'slug'=>'amazon_in'
//        ],
//        [
//            'name'=>'Amazon Mexicon',
//            'domain'=>'amazon.com.mx',
//            'image'=>'amazon_com_mx.png',
//            'referral'=>'none',
//            'slug'=>'amazon_com_mx'
//        ],
//        [
//            'name'=>'Amazon Netherlands',
//            'domain'=>'amazon.nl',
//            'image'=>'amazon_nl.png',
//            'referral'=>'none',
//            'slug'=>'amazon_nl'
//        ],
//        [
//            'name'=>'Amazon Singapore',
//            'domain'=>'amazon.sg',
//            'image'=>'amazon_sg.png',
//            'referral'=>'none',
//            'slug'=>'amazon_sg'
//        ],
//        [
//            'name'=>'Amazon Sweden',
//            'domain'=>'amazon.se',
//            'image'=>'amazon_se.png',
//            'referral'=>'cybrarist09-21',
//            'slug'=>'amazon_se'
//        ],
//        [
//            'name'=>'Amazon Belgium',
//            'domain'=>'amazon.com.be',
//            'image'=>'amazon.png',
//            'referral'=>'none',
//            'slug'=>'amazon_com_be'
//        ],
//
////Ebay
//        [
//            'name'=>'Ebay',
//            'domain'=>'ebay.com',
//            'image'=>'ebay.png',
//            'referral'=>'none',
//            'slug'=>'ebay_com'
//        ],
//        [
//            'name'=>'Walmart USA',
//            'domain'=>'walmart.com',
//            'image'=>'walmart.png',
//            'referral'=>'none',
//            'slug'=>'walmart_com'
//        ],
//
//        [
//            'name'=>'Walmart Canada',
//            'domain'=>'walmart.ca',
//            'image'=>'walmart.png',
//            'referral'=>'none',
//            'slug'=>'walmart_ca'
//        ],
//
//        [
//            'name'=>'Target',
//            'domain'=>'target.com',
//            'image'=>'target.png',
//            'referral'=>'none',
//            'slug'=>'target_com'
//        ],
//
//    ];
//
//
//
//    $path = "/usr/local/bin/php";
//    $project="/var/www/html/discount-bandit";
//
//    foreach ($stores as $store){
//        echo "(crontab -l ; echo \"*/6 * * * * $path $project/artisan queue:work --stop-when-empty --queue=".$store['slug'] . " >> /dev/null 2>&1\")  | crontab - && \\ <br>" ;
//    }
//
//});

