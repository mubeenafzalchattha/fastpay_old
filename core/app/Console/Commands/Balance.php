<?php

namespace App\Console\Commands;

use App\Models\CryptoWallet;
use App\Models\Wallet;
use Illuminate\Console\Command;

class Balance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    /*public function __construct()
    {
        parent::__construct();
    }*/

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $url = 'https://nordekscan.com/api';
        $headers = [
            'Accepts: application/json',
        ];

        $cryptos   = CryptoWallet::all();
        foreach($cryptos as $crypto) {

            $wallet = Wallet::where(['user_id'=> $crypto->user_id])->first();

            $parameters = [
                'module'=> 'account',
                'action' => 'balance',
                'address' => rtrim($crypto->wallet_address),
                //'address_' => preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "", $crypto->wallet_address))

            ];

            $qs      = http_build_query($parameters); // query string encode the parameters
            $request = "{$url}?{$qs}"; // create the request URL
            $curl    = curl_init(); // Get cURL resource
            // Set cURL options
            curl_setopt_array($curl, array(
                CURLOPT_URL            => $request, // set the request URL
           //     CURLOPT_HTTPHEADER     => $headers, // set the headers
                CURLOPT_RETURNTRANSFER => 1, // ask for raw response instead of bool
                CURLOPT_SSL_VERIFYHOST => 0, // ask for raw response instead of bool
                CURLOPT_SSL_VERIFYPEER => 0, // ask for raw response instead of bool
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl); // Send the request, save the response
            $error = curl_error($curl); // Send the request, save the response
            curl_close($curl); // Close request
            $info = curl_getinfo($curl);
            curl_close($curl);
            $a = json_decode($response);

            if(isset($a)) {
                $balance = ($a->result) / 1000000000000000000;
                $wallet->balance = $balance;
                $wallet->save();
            }
        }
        echo 'This Cycle Completed.';

    }
}
