<?php


use App\Models\Store;
use Filament\Notifications\Notification;

function validate_ebay_product(& $data , $parsed_url) : bool
{
    $after_itm=explode("/itm/" , $parsed_url['path']);
    if (count($after_itm)>1 )
    {
        $data=\Arr::add($data, "ebay_id", $after_itm[1]);
        return true;
    }
    else
    {
        Notification::make()
            ->danger()
            ->title("Unrecognized URL scheme")
            ->body("it should be like the following:<br><span style='color:green'> https://" . $parsed_url['host'] . "/itm/unique_code</span>")
            ->persistent()
            ->send();
        return false;
    }
}

function is_ebay($host)
{
    return \Str::contains( $host,"ebay" ,true);
}


function ebay_itm_unique($data): bool {
    if (
        DB::table('product_store')->where('ebay_id' , $data['ebay_id'])->count()
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
