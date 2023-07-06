<?php

function prepare_multiple_prices_in_table($state)
{
    $prices= $state->map(function ($model) {

        if ($model->pivot->price <= $model->pivot->notify_price)
            $color_string="green";
        else
            $color_string="red";

        return  "<span  style='color:$color_string'>" . $model->pivot->price/100 . " " .  $model->currency->code . " </span>";})->implode("<br>");


    return  \Illuminate\Support\Str::of($prices)->toHtmlString();
}


function prepare_multiple_notify_prices_in_table($state)
{
    $notify_prices= $state->map(function ($model) {
        return   $model->pivot->notify_price / 100 . " " .  $model->currency->code;
    })->implode("<br>");


    return  \Illuminate\Support\Str::of($notify_prices)->toHtmlString();
}

