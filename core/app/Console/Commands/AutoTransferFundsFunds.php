<?php

namespace App\Console\Commands;
use App\Models\AdminTransactions;
use App\Models\ExpTransaction;
use Illuminate\Console\Command;
use App\Lib\CurlRequest;
use App\Constants\Status;

class AutoTransferFundsFunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-trans:cron';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $admin_wallet_address = env('ADMIN_DEPOSIT_ADDRESS');
        $exp = ExpTransaction::where(['move_to_admin'=>0,'trx_type' => 'deposit'])
            ->join('crypto_wallets', function ($join) {
                $join->
                on('crypto_wallets.user_id', '=', 'exp_transactions.user_id')->
                on('crypto_wallets.crypto_currency_id', '=', 'exp_transactions.crypto_currency_id');
            })
            ->get();
       
        foreach($exp as $row){
            if($row->pkey
                && strtolower(rtrim($admin_wallet_address)) != strtolower(rtrim($row->to_address))
                && strtolower(rtrim($row->to_address)) == strtolower(rtrim($row->wallet_address))
            ){
                $method = 'sendFullTransaction';
                // $url = 'http://localhost:6545/'.$method;
                $url = env('CHAIN_URL').$method;
                $arr = [];

                $arr['PrivateKey'] = decrypt($row->pkey); //decrypt($row->cryptoWallet->pkey);
                $arr['ToAddress'] = $admin_wallet_address;
                $arr['txn_addr'] = $row->to_address;
                $arr['user_id'] = $row->user_id;
                $arr['Amount'] = $row->value;

                print_r($arr);

                // die;
                /* $response = CurlRequest::curlPostContent($url, $arr);
                $response = json_decode($response);
                print_r($response);
                die;
                
                if ($response->status) {
                    /// update admin transactions table
                    $admin = new AdminTransactions();
                    $admin->user_id = $row->user_id;
                    $admin->type = 'deposit';
                    $admin->crypto_currency_id = $row->crypto_currency_id;
                    $admin->amount = $row->value;
                    $admin->address = $row->to_address;
                    $admin->hash = $response->hash; //$response->hash;
                    $admin->description = 'auto transfer to admin';
                    $admin->save();
                    
                    // print_r($admin);
                    
                    // update exp_transactions here
                    $row->move_to_admin = Status::PAYMENT_SUCCESS;
                    $row->save();
                }
                */
            }
            echo '<br>This Cycle Completed.';
        }
    }
}
