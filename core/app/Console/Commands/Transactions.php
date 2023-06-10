<?php

namespace App\Console\Commands;

use App\Components\GeneralHelper;
use App\Lib\CurlRequest;
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

        $cryptos   = CryptoWallet::all();
        foreach($cryptos as $crypto) {

            $parameters = [
                'module'=> 'account',
                'action' => 'txlist',
                'address' => rtrim($crypto->wallet_address),
                //'address_' => preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "", $crypto->wallet_address))

            ];

            $qs      = http_build_query($parameters); // query string encode the parameters
            $request = "{$url}?{$qs}"; // create the request URL
            $response = CurlRequest::curlPostContent($request);
            $response = json_decode($response);
            //print_r($response);die();

            $balance = 0;
            if(isset($response->result)) {
                $number =  pow(10, 18);;
                $transactions = $response->result;
                foreach ($transactions as $trx) {
                    $old_tranc = ExpTransaction::where('hash',$trx->hash)->first();
                    if(empty($old_tranc)) {
                        $transaction = new ExpTransaction();
                        $transaction->user_id = $crypto->user_id;
                        $transaction->value = $trx->value / $number;
                        $transaction->gas = $trx->gas * $trx->gasUsed / $number;
                        $transaction->gas_price = $trx->gasPrice * $trx->gasUsed / $number;
                        $transaction->hash = $trx->hash;
                        $transaction->trx_date = \Carbon\Carbon::createFromTimestamp($trx->timeStamp)->format('Y-m-d H:i:s');
                        if(strtolower(rtrim($trx->to)) == strtolower(rtrim($crypto->wallet_address))) {
                            $transaction->trx_type = 'deposit';
                            $balance = $balance + $transaction->value;
                            echo '<br> Depo';
                            echo $balance;
                        } else {
                            $transaction->trx_type = 'withdraw';
                            $balance = $balance - $transaction->value;
                            echo '<br> wd';
                            echo $balance;
                        }
                        $transaction->block_no = $trx->blockNumber;
                        $transaction->to_address = $trx->to;
                        $transaction->from_address = $trx->from;
                        $transaction->save();
                    }
                    echo  'skipping hash : '.$trx->hash;
                }
                //GeneralHelper::updateBalance($balance,$crypto->user_id,$crypto->crypto_currency_id);
                $wallet = Wallet::where(['user_id'=>$crypto->user_id,'crypto_currency_id'=>$crypto->crypto_currency_id])->first();
                $wallet->balance = $wallet->balance+$balance;
                $wallet->save();
                echo 'Done for user '.$crypto->user_id;
            }
        }
        echo 'This Transaction Cycle Completed.';

    }
}
