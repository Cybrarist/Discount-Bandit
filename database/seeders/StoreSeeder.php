<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\Currency;
use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores=[
            [
                'name'=>'Amazon UAE',
                'domain'=>'amazon.ae',
                'image'=>'amazon_ae.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'AED'])->id,
                'slug'=>'amazon_ae'
            ],
            [
                'name'=>'Amazon USA',
                'domain'=>'amazon.com',
                'image'=>'amazon.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'amazon_com'
            ],
            [
                'name'=>'Amazon UK',
                'domain'=>'amazon.co.uk',
                'image'=>'amazon_co_uk.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'£'])->id,
                'slug'=>'amazon_uk'
            ],
            [
                'name'=>'Amazon Germany',
                'domain'=>'amazon.de',
                'image'=>'amazon_de.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
                'slug'=>'amazon_de'
            ],
            [
                'name'=>'Amazon France',
                'domain'=>'amazon.fr',
                'image'=>'amazon_fr.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
                'slug'=>'amazon_fr'
            ],
            [
                'name'=>'Amazon Italy',
                'domain'=>'amazon.it',
                'image'=>'amazon_it.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
                'slug'=>'amazon_it'
            ],
            [
                'name'=>'Amazon Saudi Arabia',
                'domain'=>'amazon.sa',
                'image'=>'amazon_sa.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'SAR'])->id,
                'slug'=>'amazon_sa'
            ],
            [
                'name'=>'Amazon Spain',
                'domain'=>'amazon.es',
                'image'=>'amazon_es.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
                'slug'=>'amazon_es'
            ],
            [
                'name'=>'Amazon Poland',
                'domain'=>'amazon.pl',
                'image'=>'amazon_pl.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'zł'])->id,
                'slug'=>'amazon_pl'
            ],
            [
                'name'=>'Amazon Turkey',
                'domain'=>'amazon.com.tr',
                'image'=>'amazon_com_tr.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'TL'])->id,
                'slug'=>'amazon_com_tr'
            ],
            [
                'name'=>'Amazon Australia',
                'domain'=>'amazon.com.au',
                'image'=>'amazon_com_au.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'amazon_com_au'
            ],
            [
                'name'=>'Amazon Brazil',
                'domain'=>'amazon.com.br',
                'image'=>'amazon_com_br.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'R$'])->id,
                'slug'=>'amazon_com_br'
            ],
            [
                'name'=>'Amazon Canada',
                'domain'=>'amazon.ca',
                'image'=>'amazon_ca.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'amazon_ca'
            ],
            [
                'name'=>'Amazon China',
                'domain'=>'amazon.cn',
                'image'=>'amazon.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'¥'])->id,
                'slug'=>'amazon_tr'
            ],
            [
                'name'=>'Amazon Egypt',
                'domain'=>'amazon.eg',
                'image'=>'amazon_eg.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'EGP'])->id,
                'slug'=>'amazon_eg'
            ],
            [
                'name'=>'Amazon Japan',
                'domain'=>'amazon.co.jp',
                'image'=>'amazon_co_jp.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'¥'])->id,
                'slug'=>'amazon_co_jp'
            ],
            [
                'name'=>'Amazon India',
                'domain'=>'amazon.in',
                'image'=>'amazon_in.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'₹'])->id,
                'slug'=>'amazon_in'
            ],
            [
                'name'=>'Amazon Mexicon',
                'domain'=>'amazon.com.mx',
                'image'=>'amazon_com_mx.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'amazon_com_mx'
            ],
            [
                'name'=>'Amazon Netherlands',
                'domain'=>'amazon.nl',
                'image'=>'amazon_nl.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
                'slug'=>'amazon_nl'
            ],
            [
                'name'=>'Amazon Singapore',
                'domain'=>'amazon.sg',
                'image'=>'amazon_sg.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'S$'])->id,
                'slug'=>'amazon_sg'
            ],
            [
                'name'=>'Amazon Sweden',
                'domain'=>'amazon.se',
                'image'=>'amazon_se.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'kr'])->id,
                'slug'=>'amazon_se'
            ],
            [
                'name'=>'Amazon Belgium',
                'domain'=>'amazon.com.be',
                'image'=>'amazon.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
                'slug'=>'amazon_com_be'
            ],
            [
                'name'=>'Argos UK',
                'domain'=>'argos.co.uk',
                'image'=>'argos.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'£'])->id,
                'slug'=>'argos_co_uk'
            ],
            [
                'name'=>'DIY',
                'domain'=>'diy.com',
                'image'=>'diy_com.svg',
                'currency_id'=>Currency::firstOrCreate(['code'=>'£'])->id,
                'slug'=>'diy_com'
            ],
            [
                'name'=>'Ebay',
                'domain'=>'ebay.com',
                'image'=>'ebay.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'ebay_com'
            ],
            [
                'name'=>'Fnac',
                'domain'=>'fnac.com',
                'image'=>'fnac_com.svg',
                'currency_id'=>Currency::firstOrCreate(['code'=>'£'])->id,
                'slug'=>'fnac_com'
            ],
            [
                'name'=>'Target',
                'domain'=>'target.com',
                'image'=>'target.svg',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'target_com'
            ],
            [
                'name'=>'Walmart USA',
                'domain'=>'walmart.com',
                'image'=>'walmart.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'walmart_com'
            ],
            [
                'name'=>'Walmart Canada',
                'domain'=>'walmart.ca',
                'image'=>'walmart.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'walmart_ca'
            ],
            [
                'name'=>'Noon UAE',
                'domain'=>'noon.com',
                'image'=>'noon.svg',
                'currency_id'=>Currency::firstOrCreate(['code'=>'AED'])->id,
                'slug'=>'noon_com'
            ],
            [
                'name'=>'Noon Egypt',
                'domain'=>'noon.com',
                'image'=>'noon.svg',
                'currency_id'=>Currency::firstOrCreate(['code'=>'EGP'])->id,
                'slug'=>'noon_com'
            ],
            [
                'name'=>'Noon Saudi',
                'domain'=>'noon.com',
                'image'=>'noon.svg',
                'currency_id'=>Currency::firstOrCreate(['code'=>'SAR'])->id,
                'slug'=>'noon_com'
            ],
            [
                'name'=>'Costco',
                'domain'=>'costco.com',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'costco_com'
            ],
            [
                'name'=>'Costco Australia',
                'domain'=>'costco.com.au',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'costco_com_au'
            ],
            [
                'name'=>'Costco Canada',
                'domain'=>'costco.ca',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'costco_ca'
            ],
            [
                'name'=>'Costco Iceland',
                'domain'=>'costco.is',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'kr'])->id,
                'slug'=>'costco_is'
            ],
            [
                'name'=>'Costco Japan',
                'domain'=>'costco.co.jp',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'¥'])->id,
                'slug'=>'costco_co_jp'
            ],
            [
                'name'=>'Costco Korea',
                'domain'=>'costco.co.kr',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'원'])->id,
                'slug'=>'costco_co_kr'
            ],
            [
                'name'=>'Costco Mexico',
                'domain'=>'costco.com.mx',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'costco_com_mx'
            ],
            [
                'name'=>'Costco Taiwan',
                'domain'=>'costco.com.tw',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'costco_com_tw'
            ],
            [
                'name'=>'Costco UK',
                'domain'=>'costco.co.uk',
                'image'=>'costco.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'£'])->id,
                'slug'=>'costco_co_uk'
            ],
            [
                'name'=>'Currys',
                'domain'=>'currys.co.uk',
                'image'=>'currys.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'£'])->id,
                'slug'=>'currys_co_uk'
            ],
            [
                'name'=>'Canadian Tire',
                'domain'=>'canadiantire.ca',
                'image'=>'canadiantire.svg',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'canadiantire_ca'
            ],
            [
                'name'=>'Princess Auto',
                'domain'=>'princessauto.com',
                'image'=>'princessauto.svg',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'princessauto_com'
            ],
            [
                'name'=>'Media Market Spain',
                'domain'=>'mediamarkt.es',
                'image'=>'mediamarket.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
                'slug'=>'mediamarkt_es'
            ],
            [
                'name'=>'Best Buy',
                'domain'=>'bestbuy.com',
                'image'=>'bestbuy.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'bestbuy_com'
            ],
            [
                'name'=>'Best Buy Canada',
                'domain'=>'bestbuy.ca',
                'image'=>'bestbuy.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'$'])->id,
                'slug'=>'bestbuy_ca'
            ],
            [
                'name'=>'Emax UAE',
                'domain'=>'emaxme.com',
                'image'=>'emax.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'AED'])->id,
                'slug'=>'uae_emaxme_com',
                'status'=>StatusEnum::Disabled
            ],
            [
                'name'=>'Ebay',
                'domain'=>'ebay.de',
                'image'=>'ebay.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'€'])->id,
                'slug'=>'ebay_de'
            ],
            [
                'name'=>'FlipKart',
                'domain'=>'flipkart.com',
                'image'=>'flipkart.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'₹'])->id,
                'slug'=>'flipkart_com'
            ],
            [
                'name'=>'Myntra',
                'domain'=>'myntra.com',
                'image'=>'myntra.png',
                'currency_id'=>Currency::firstOrCreate(['code'=>'₹'])->id,
                'slug'=>'myntra_com'
            ],
        ];



        foreach ($stores as $store)
            Store::updateOrCreate([
                'domain'=>$store["domain"],
                "currency_id" => $store["currency_id"]
            ],
                $store
            );
    }
}
