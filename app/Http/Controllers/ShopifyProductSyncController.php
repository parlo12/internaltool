<?php

namespace App\Http\Controllers;

use App\Services\StullerToShopifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopifyProductSyncController extends Controller
{

    public function test()
    {
        $service = new StullerToShopifyService();
        $products = $service->fetchAndConvertProducts();

        foreach ($products as $product) {
            $service->createProduct($product);
        }
    }
}
