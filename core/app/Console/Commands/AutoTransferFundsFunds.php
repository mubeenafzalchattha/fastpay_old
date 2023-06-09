<?php

namespace App\Console\Commands;
use App\Models\AdminTransactions;
use App\Models\ExpTransaction;
use Illuminate\Console\Command;

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
        $exp = ExpTransaction::where(['move_to_admin'=>0])
           // ->leftJoin('crypto_wallets','crypto_wallets.user_id','=','exp_transactions.user_id') //for pkey and address
            ->get();//where admin_move =0

        //echo ($exp);

        foreach($exp as $row){
//            dd($row->cryptoWallet->wallet_address);
            $method = 'sendTransaction';
            $url = 'http://localhost:6545/'.$method;
            //dd($row->wallet_address);
            $arr = [];
            $arr['PrivateKey'] = decrypt($row->cryptoWallet->pkey);
            $arr['ToAddress'] = $row->toaddress;
            $arr['Amount'] = $row->amount;



            // $response = CurlRequest::curlPostContent($url, $arr);
            // $response = json_decode($response);

            // if ($response->status == 'error') {
            //     print_r($response->message);
            // }
            // print_r($response->message);

            /// update admin transactions table
            $admin = new AdminTransactions();
            $admin->user_id = $row->user_id;
            $admin->type = 'type';
            $admin->currency_id = 'kahan sy aye gii :D';
            $admin->amount = $row->amount;
            $admin->address = $row->address;
            $admin->hash = $row->hash;
            $admin->description = 'some description';
            $admin->datetime = now();
            $admin->save();

            // update exp_transactions here
            $row->move_to_admin = 1;
            $row->update_time = now();
            $row->save();
            echo 'This Cycle Completed.';
        }
    }
}
