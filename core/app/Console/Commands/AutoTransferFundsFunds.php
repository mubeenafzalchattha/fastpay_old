<?php

namespace App\Console\Commands;
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
        $exp = ExpTransaction::where(['move_to_admin'=>0])->get();//where admin_move =0
        echo ($exp);

        foreach($exp as $row){
            $method = 'sendTransaction';            
            $url = 'http://localhost:6545/'.$method;
            
            $arr = [];
            $arr['PrivateKey'] = $row->pkey;
            $arr['ToAddress'] = $row->toaddress; 
            $arr['Amount'] = $row->amount;
            
            // $response = CurlRequest::curlPostContent($url, $arr);
            // $response = json_decode($response);

            // if ($response->status == 'error') {
            //     print_r($response->message);
            // }
            // print_r($response->message);
            echo 'This Cycle Completed.';
        }
    }
}
