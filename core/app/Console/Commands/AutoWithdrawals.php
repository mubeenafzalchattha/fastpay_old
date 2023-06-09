<?php

namespace App\Console\Commands;

use App\Models\Withdrawal;
use Illuminate\Console\Command;
use App\Lib\CurlRequest;

class AutoWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:withdrawals';

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
        $admin_wallet_pkey = env('ADMIN_PKEY');
        $exp = Withdrawal::where(['status'=>2])->get();
        
        foreach($exp as $row){
            echo $row.'<br>/n';
            $method = 'sendTransaction';
            // $url = 'http://localhost:6545/'.$method;
            $url = env('CHAIN_URL').$method;
            $arr = [];
            $arr['PrivateKey'] = decrypt($admin_wallet_pkey); //decrypt($row->cryptoWallet->pkey);
            $arr['ToAddress'] = $row->wallet_address;
            $arr['Amount'] = $row->payable;
            // print_r($arr);die;
            $response = CurlRequest::curlPostContent($url, $arr);
            $response = json_decode($response);
            // print_r($response);

            if ($response->status) {
                /// update admin transactions table
                $admin = new AdminTransactions();
                $admin->user_id = $row->user_id;
                $admin->type = 'withdraw';
                $admin->currency_id = $row->crypto_currency_id;
                $admin->amount = $row->payable;
                $admin->address = $row->wallet_address;
                $admin->hash = $response->hash;
                $admin->description = 'user auto withdrawal';
                $admin->save();
                
                // print_r($admin);

                // update exp_transactions here
                $row->status = 1;
                $row->updated_at = now();
                $row->save();
            }
            echo '<br>This Cycle Completed.';
        }
    }
}
