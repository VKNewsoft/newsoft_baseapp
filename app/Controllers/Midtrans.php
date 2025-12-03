<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers;

class Midtrans extends BaseController
{
    public function index()
	{
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = 'SB-Mid-server-xxxxxxxxxxxxxxxxxxxxxx';
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = false;
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;

        $params = array(
                "transaction_details" => [
                  "order_id" => rand(),
                  "gross_amount" => 500000
                ],
                "item_details" => [
                  [
                    "id" => "id1",
                    "price" => 200000,
                    "quantity" => 1,
                    "name" => "Contoh Barang 1"
                  ],
                  [
                    "id" => "id2",
                    "price" => 300000,
                    "quantity" => 1,
                    "name" => "Contoh Barang 2"
                  ]
                ],
                "customer_details" => [
                  "first_name" => "John",
                  "last_name" => "Doe",
                  "email" => "customer@example.com",
                  "phone" => "08123456789"
                ],
                "qris" => [
                  "acquirer" => "gopay"
                ]
        );

        $data = array(
            'link' => \Midtrans\CoreApi::charge($params)
        );

        dd($data);

        echo '<img class="swal2-image" src="https://api.sandbox.midtrans.com/v2/qris/'.$data['link']->transaction_id.'/qr-code" alt="Custom image" style="width: 400px; height: 400px;">';
    }
}