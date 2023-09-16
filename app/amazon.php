<?php


use Filament\Notifications\Notification;

function validate_amazon_product(& $data , $parsed_url) : bool
{
    try {
        $parsed_url['path']=Str::replace("/gp/product/" , "/dp/" , $parsed_url['path'] , false);
        $after_dp=explode("/dp/" , $parsed_url['path']);
        $data=\Arr::add($data, "asin", $after_dp[1]);
        return true;
    }
    catch (Exception){
        Notification::make()
            ->danger()
            ->title("Unrecognized URL scheme")
            ->body("it should be like the following:<br>
                                    <span style='color:green'> https://" . $parsed_url['host'] . "/dp/unique_code</span>
                                    <br>or<br>
                                     <span style='color: green'> https://" . $parsed_url['host'] . "/gp/product/unique_code</span>")
            ->persistent()
            ->send();
        return false;
    }
}

function is_amazon($host)
{
    return \Str::contains( $host,"amazon" ,true);
}

function amazon_asin_unique($data): bool {
        if (
            DB::table('products')->where('asin', $data['asin'])->count()
        ){
            Notification::make()
                ->danger()
                ->title("Existing Product")
                ->body("This product already exists in your database")
                ->persistent()
                ->send();
            return  false;
        }
        return true;
}
