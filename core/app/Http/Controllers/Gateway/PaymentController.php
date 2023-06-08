<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\CryptoCurrency;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Controllers\Gateway\Coinpayments\CoinPaymentHosted;
use App\Models\AdminNotification;
use App\Models\CryptoWallet;
use App\Models\Deposit;
use App\Models\Transaction;

class PaymentController extends Controller
{
    public function __construct()
    {
        return $this->activeTemplate = activeTemplate();
    }


    public function wallets()
    {
        $pageTitle = 'Your Receiving Wallets';
        $wallets = Wallet::where('user_id', auth()->id())->with('crypto')->latest()->get();
        $cryptoWallets = CryptoWallet::where('user_id', auth()->id())->latest()->with('crypto')->paginate(getPaginate());

        return view($this->activeTemplate . 'user.wallet', compact('pageTitle', 'wallets', 'cryptoWallets'));
    }

    public function singleWallet($id, $code)
    {
        $pageTitle     = $code . ' Wallets';
        $crypto        = CryptoCurrency::findOrFail($id);
        $wallets       = Wallet::where('user_id', auth()->user()->id)->with('crypto')->latest()->get();
        $cryptoWallets = CryptoWallet::where('user_id', auth()->user()->id)->where('crypto_currency_id', $crypto->id)->latest()->with('crypto')->paginate(getPaginate());
        $emptyMessage  = 'No wallet found';

        return view($this->activeTemplate . 'user.wallet', compact('pageTitle', 'wallets', 'cryptoWallets', 'crypto', 'emptyMessage'));
    }

    public function walletGenerate($code)
    {
        // not using coinpayment
        $crypto = CryptoCurrency::active()->where('code', $code)->first();

        if (!$crypto) {
            $notify[] = ['error', 'Generating new address with this crypto currency is currently disabled'];
            return back()->withNotify($notify);
        }
        $get_last_address_date = CryptoWallet::where('user_id',auth()->user()->id)->where('crypto_currency_id',$crypto->id)/*->orderby('created_at','desc')*/->latest()->first();
        /*$diff = 31;
        if() {
            $date = Carbon::parse($get_last_address_date->created_at);
            $now = Carbon::now();
            $diff = $date->diffInDays($now);
        }*/

        if(!$get_last_address_date) {
            $nrk_address = file_get_contents('https://fastpay.nordek.dev/address.php');
            $nrk_address = json_decode($nrk_address,true);
           // dd($nrk_address['privateKey']);
            /*
                    $coinPayAcc = gs();
                    $cps = new CoinPaymentHosted();
                    $cps->Setup($coinPayAcc->private_key, $coinPayAcc->public_key);
                    $callbackUrl = route('ipn.crypto');
                    $result = $cps->GetCallbackAddress($crypto->code, $callbackUrl);*/

            // $address = array($address);
            //print_r($address[0]['Output']);
//        $nrk_address = $address;

//        if ($result['error'] == 'ok') {
            $pkey = rtrim($nrk_address['privateKey']);
            $pkey = encrypt($nrk_address['privateKey']);
            if ($nrk_address) {
                $newCryptoWallet = new CryptoWallet();
                //$newCryptoWallet = CryptoWallet::where('user_id',auth()->user()->id)->first();
                $newCryptoWallet->user_id = auth()->user()->id;
                $newCryptoWallet->crypto_currency_id = $crypto->id;
//            $newCryptoWallet->wallet_address = $result['result']['address'];
                $newCryptoWallet->wallet_address = rtrim($nrk_address['address']);
                $newCryptoWallet->pkey = $pkey;
                $newCryptoWallet->save();
                /*$wallet['crypto_currency_id'] = $crypto->id;
                $wallet['user_id']            = auth()->id();
                $wallet['balance']            = 0;
                $data[]                       = $wallet;
                $old_wallet = Wallet::where('user_id',auth()->user()->id)->first();
                if(empty($old_wallet)) {
                    Wallet::insert($data);
                }
                $old_wallet->update($data);*/
                $notify[] = ['success', 'New wallet address generated successfully.'];
            } else {
//            $notify[] = ['error', 'API Error : ' . $result['error']];
                $notify[] = ['error', 'API Error : ' . 'error'];
            }
        } else {
            $notify[] = ['success', 'New wallet address cannot be generated.'];

        }

        return back()->withNotify($notify);

    }

    public function cryptoIpn(Request $request)
    {
        if ($request->status >= 100 || $request->status == 2) {

            $userCryptoWallet = CryptoWallet::where('wallet_address', $request->address)->first();
            $user = $userCryptoWallet->user;
            $general = gs();

            if ($general->merchant_id == $request->merchant) {

                $exist =  Deposit::where('cp_trx', $request->txn_id)->count();
                if ($exist == 0) {

                    $crypto = CryptoCurrency::find($userCryptoWallet->crypto_currency_id);
                    $sentAmount = $request->amount;

                    $charge                 = $crypto->deposit_charge_fixed + ($sentAmount * $crypto->deposit_charge_percent / 100);
                    $finalAmount            = $sentAmount - $charge;

                    if ($finalAmount > 0) {
                        $data                     = new Deposit();
                        $data->user_id            = $user->id;
                        $data->crypto_currency_id = $crypto->id;
                        $data->wallet_address     = $request->address;
                        $data->amount             = $sentAmount;
                        $data->charge             = $charge;
                        $data->final_amo          = $finalAmount;
                        $data->trx                = getTrx();
                        $data->status             = Status::PAYMENT_SUCCESS;
                        $data->cp_trx             = $request->txn_id;
                        $data->save();

                        $userWallet = Wallet::where('user_id', $userCryptoWallet->user_id)->where('crypto_currency_id', $userCryptoWallet->crypto_currency_id)->first();
                        $userWallet->balance +=  $finalAmount;
                        $userWallet->save();

                        $transaction = new Transaction();
                        $transaction->user_id = $data->user_id;
                        $transaction->crypto_currency_id = $crypto->id;
                        $transaction->amount = $data->amount;
                        $transaction->post_balance = getAmount($userWallet->balance, 8);
                        $transaction->charge = getAmount($data->charge, 8);
                        $transaction->trx_type = '+';
                        $transaction->details = 'Deposit Via ' . $data->crypto->code;
                        $transaction->remark = 'deposit';
                        $transaction->trx = $data->trx;
                        $transaction->save();


                        $adminNotification = new AdminNotification();
                        $adminNotification->user_id = $user->id;
                        $adminNotification->title = 'Deposit successful for ' . $data->crypto->code;
                        $adminNotification->click_url = urlPath('admin.deposit.successful');
                        $adminNotification->save();

                        notify($user, 'DEPOSIT_COMPLETE', [
                            'amount' => showAmount($data->amount, 8),
                            'charge' => showAmount($data->charge, 8),
                            'currency' => $data->crypto->code,
                            'payable' => showAmount($data->final_amo, 8),
                            'trx' => $data->trx,
                            'post_balance' => showAmount($userWallet->balance, 8)
                        ]);

                        if ($general->deposit_commission) {
                            levelCommission($user, $data->amount, $crypto->id, $data->trx, 'deposit');
                        }
                    }
                }
            }
        }
    }
}
