<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$product->name}}</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{asset('/js/stores.js')}}"></script>
    <link rel="stylesheet" href="{{asset('/css/style.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>

    <img src="{{$product->image}}" class="w-auto mx-auto max-h-96 h-auto" alt="">
    <h1 class="text-2xl mt-10 text-center">{{$product->name}}</h1>

    <div class="flex justify-center items-center mt-10 gap-y-8">
        @forelse($product_stores as $product_store)

            @php
                $final_class_name="App\Helpers\StoresAvailable\\" . Str::ucfirst( explode(".",$product_store->store->domain)[0]);
               $url= call_user_func($final_class_name . '::prepare_url' , $product_store->store->domain, $product_store->key , $product_store->store );
            @endphp


            <a target="_blank" href="{{$url}}">
                <div class="single_store">
                    <div class="card bg-c-yellow order-card">
                        <div class="card-block">
                            <div>
                                <div style="float: left">
                                    <img style="  height: 20px;max-height:20px; object-fit: contain"
                                         src="{{\Illuminate\Support\Facades\Storage::url("/store/" . $product_store->store->image)  }}" alt="">
                                </div>
                                <h6 style="width: 50%; float: left; padding-left: 10px">{{$product_store->store->name}}</h6>
                            </div>

                            <h2 style="width: 100%; margin-top: 10px">
                                <i class="fa fa-cart-plus f-left"></i>
                                <span>{{$product_store->store->currency->code}}  {{$product_store->price}}</span></h2>

                            <div class="mt-5">
                                <p class="prices flex">
                                    <svg  aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="red" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4.5V19a1 1 0 0 0 1 1h15M7 14l4-4 4 4 5-5m0 0h-3.207M20 9v3.207"/>
                                    </svg>
                                    <span class="f-right highest">{{$product_store->store->currency->code}} {{$product_store->highest_price}}</span>
                                </p>

                                <p class="prices flex">
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="green" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4.5V19a1 1 0 0 0 1 1h15M7 10l4 4 4-4 5 5m0 0h-3.207M20 15v-3.207"/>
                                    </svg>
                                    <span class="f-right lowest">{{$product_store->store->currency->code}} {{$product_store->lowest_price}}</span>
                                </p>
                            </div>
                            <div class="seller">Seller<span class="f-right">{{$product_store->seller}}</span></h6>
                            </div>
                        </div>
                    </div>
                </div>

            </a>

        @empty
            <div></div>
        @endforelse

    </div>

    <div class="w-full mt-10 mb-32">
        <div id="chart"></div>
    </div>
    <script>
        var options = {
            chart: {
                type:'area',
                height:300
            },
            theme:{
                palette: "pallet1"
            },
            series: {!! $series !!},
            xaxis: {
                type:'datetime',
                categories:[
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec'
                ],
                labels:{
                    style:{
                        fontFamily:'inherit'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val, index) {
                        return val.toLocaleString('en-US');
                    }
                }
            },
            stroke : {
                curve:'smooth'
            },

            dataLabels: {
                enabled: false,
            },
            legend: {
                position: 'top'
            }
        }

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();


    </script>
</body>
</html>
