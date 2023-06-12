<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\Coinpayments\CoinPaymentHosted;
use App\Models\CryptoCurrency;
use App\Models\CryptoWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function walletGenerate($id)
    {
        $crypto = CryptoCurrency::active()->where('id', $id)->first();

        if (!$crypto) {
            return response()->json([
                'remark' => 'crypto_error',
                'status' => 'error',
                'message' => ['error' => 'Crypto currency not found or disabled'],
            ]);
        }

        $get_last_address_date = CryptoWallet::where('user_id',auth()->user()->id)->where('crypto_currency_id',$crypto->id)/*->orderby('created_at','desc')*/->latest()->first();
       /* $date = Carbon::parse($get_last_address_date->created_at);
        $now = Carbon::now();

        $diff = $date->diffInDays($now);
        if($diff>30) {*/

            /*
            $coinPayAcc = gs();
            $cps = new CoinPaymentHosted();
            $cps->Setup($coinPayAcc->private_key, $coinPayAcc->public_key);
            $callbackUrl = route('ipn.crypto');
            $result = $cps->GetCallbackAddress($crypto->code, $callbackUrl);
            */
        if(!$get_last_address_date) {
            $nrk_address = file_get_contents('https://fastpay.nordek.dev/address.php');
            $nrk_address = json_decode($nrk_address,true);
            $pkey = rtrim($nrk_address['privateKey']);
            $pkey = encrypt($pkey);

//        if ($result['error'] == 'ok') {
            if ($nrk_address) {
                $newCryptoWallet = new CryptoWallet();
                $newCryptoWallet->user_id = Auth::id();
                $newCryptoWallet->crypto_currency_id = $crypto->id;
                $newCryptoWallet->wallet_address = rtrim($nrk_address['address']);
                $newCryptoWallet->pkey = $pkey;
                $newCryptoWallet->save();
                return response()->json([
                    'remark' => 'wallet_address_generated',
                    'status' => 'success',
                    'message' => ['success' => 'New Wallet Address Generated Successfully'],
                ]);
            } else {
                return response()->json([
                    'remark' => 'node_error',
                    'status' => 'error',
                    'message' => ['error' => 'error'],
                ]);
            }
        } else {
            return response()->json([
                'remark' => 'duration error',
                'status' => 'success',
                'message' => ['success' => 'No New wallet address cannot be generated.'],
            ]);
        }
    }
}
