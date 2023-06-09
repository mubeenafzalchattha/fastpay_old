<?php

namespace App\Console\Commands;

use App\Models\CryptoWallet;
use App\Models\ExpTransaction;
use App\Models\Wallet;
use Illuminate\Console\Command;

class Transactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:transactions';

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

            //$wallet = Wallet::where(['user_id'=> $crypto->user_id])->first();

            $parameters = [
                'module'=> 'account',
                'action' => 'txlist',
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

            if(isset($a->result)) {
                $old_tranc = ExpTransaction::where('user_id',$crypto->user_id)->delete();
                $number =  1000000000000000000;
               
                $transactions = $a->result;
                foreach ($transactions as $trx) {
                    if(strtolower($crypto->wallet_address) == strtolower($trx->to)){
                        $transaction = new ExpTransaction();
                        $transaction->user_id = $crypto->user_id;
                        $transaction->value = $trx->value / $number;
                        $transaction->gas = $trx->gas / $number;
                        $transaction->gas_price = $trx->gasPrice / $number;
                        $transaction->hash = $trx->hash;
                        $transaction->trx_date = \Carbon\Carbon::createFromTimestamp($trx->timeStamp)->format('Y-m-d H:i:s');
                        $transaction->block_no = $trx->blockNumber;
                        $transaction->to_address = $trx->to;
                        $transaction->from_address = $trx->from;
                        $transaction->save();
                    }
                }
                echo '<br>Done for user '.$crypto->user_id;
            }
        }
        echo 'This Transaction Cycle Completed.';

    }
}
