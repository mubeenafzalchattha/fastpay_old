<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\Advertisement;
use App\Models\CommissionLog;
use App\Models\CryptoCurrency;
use App\Models\Form;
use App\Models\Referral;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function home()
    {
        $this->insertNewCryptoWallets();

        $user           = auth()->user();
        $pageTitle      = 'Dashboard';
        $wallets        = Wallet::where('user_id', $user->id)
                            ->leftJoin('crypto_currencies','crypto_currencies.id','=','crypto_currency_id')
                            ->selectRaw("crypto_currencies.id as cryptoId, code as cryptoCode, balance, image as cryptoImage, (balance * crypto_currencies.rate) as balanceInUsd")
                            ->orderByRaw("wallets.id desc")
                            ->get();
        $totalAdd       = Advertisement::where('user_id', $user->id)->count();
        $advertisements = Advertisement::where('user_id', auth()->user()->id)->latest()->with(['crypto', 'fiatGateway', 'fiat', 'user.wallets'])->latest()->limit(10)->get();

        return view($this->activeTemplate . 'user.dashboard', compact('pageTitle', 'user', 'wallets', 'totalAdd', 'advertisements'));
    }

    public function insertNewCryptoWallets()
    {
        $walletId  = Wallet::where('user_id', auth()->id())->pluck('crypto_currency_id');
        $cryptos   = CryptoCurrency::latest()->whereNotIn('id', $walletId)->pluck('id');
        $data      = [];

        foreach ($cryptos as $id) {
            $wallet['crypto_currency_id'] = $id;
            $wallet['user_id']            = auth()->id();
            $wallet['balance']            = 0;
            $data[]                       = $wallet;
        }

        if (!empty($data)) {
            Wallet::insert($data);
        }
    }

    public function depositHistory()
    {
        $pageTitle = 'Deposit History';
        $deposits  = auth()->user()->deposits()->searchable(['trx'])->where('user_id', auth()->id());

        if (request()->crypto) {
            $deposits = $deposits->where('crypto_currency_id', request()->crypto);
        }

        $deposits = $deposits->with(['crypto'])->orderBy('id', 'desc')->paginate(getPaginate());
        $cryptos = CryptoCurrency::orderBy('code')->get();

        return view($this->activeTemplate . 'user.deposit_history', compact('pageTitle', 'deposits', 'cryptos'));
    }


    public function referralCommissions()
    {
        $pageTitle = 'Referral Commissions';
        $logs = CommissionLog::where('to_id', auth()->user()->id)->with('bywho', 'crypto')->latest()->paginate(getPaginate());

        return view($this->activeTemplate . 'user.referral.commission', compact('pageTitle', 'logs'));
    }

    public function myRef()
    {
        $pageTitle = 'My Referred Users';
        $maxLevel = Referral::max('level');
        $user = auth()->user();

        return view($this->activeTemplate . 'user.referral.users', compact('pageTitle', 'maxLevel', 'user'));
    }

    public function show2faForm()
    {
        $general = gs();
        $ga = new GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . $general->site_name, $secret);
        $pageTitle = '2FA Setting';
        return view($this->activeTemplate . 'user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $this->validate($request, [
            'key' => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts = 1;
            $user->save();
            $notify[] = ['success', 'Google authenticator activated successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $user = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts = 0;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator deactivated successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }


    public function transactionIndex(Request $request)
    {
        $pageTitle    = 'Transactions';
        $remarks      = Transaction::distinct('remark')->whereNotNull('remark')->get('remark');
        $transactions = Transaction::where('user_id', Auth::id())->where('crypto_currency_id', '!=', null);

        if ($request->search) {
            $transactions = $transactions->where('trx', $request->search);
        }

        if ($request->type) {
            $transactions = $transactions->where('trx_type', $request->type);
        }

        if ($request->crypto) {
            $transactions = $transactions->where('crypto_currency_id', $request->crypto);
        }

        if ($request->remark) {
            $transactions = $transactions->where('remark', $request->remark);
        }

        $transactions = $transactions->with(['crypto'])->orderBy('id', 'desc')->paginate(getPaginate());
        $cryptos = CryptoCurrency::latest()->get();

        return view($this->activeTemplate . 'user.transaction', compact('pageTitle', 'transactions', 'cryptos', 'remarks'));
    }


    public function kycForm()
    {
        if (auth()->user()->kv == 2) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('user.home')->withNotify($notify);
        }
        if (auth()->user()->kv == 1) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('user.home')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form = Form::where('act', 'kyc')->first();
        return view($this->activeTemplate . 'user.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycData()
    {
        $user = auth()->user();
        $pageTitle = 'KYC Data';
        return view($this->activeTemplate . 'user.kyc.info', compact('pageTitle', 'user'));
    }

    public function kycSubmit(Request $request)
    {
        $form = Form::where('act', 'kyc')->first();
        $formData = $form->form_data;
        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);
        $user = auth()->user();
        $user->kyc_data = $userData;
        $user->kv = 2;
        $user->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('user.home')->withNotify($notify);
    }

    public function attachmentDownload($fileHash)
    {
        $filePath = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $general = gs();
        $title = slug($general->site_name) . '- attachments.' . $extension;
        $mimetype = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function userData()
    {
        $user = auth()->user();
        if ($user->profile_complete  == 1) {
            return to_route('user.home');
        }
        $pageTitle = 'User Data';
        return view($this->activeTemplate . 'user.user_data', compact('pageTitle', 'user'));
    }

    public function userDataSubmit(Request $request)
    {
        $user = auth()->user();
        if ($user->profile_complete  == 1) {
            return to_route('user.home');
        }
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
        ]);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'city' => $request->city,
        ];
        $user->profile_complete  = 1;
        $user->save();

        $notify[] = ['success', 'Registration process completed successfully'];
        return to_route('user.home')->withNotify($notify);
    }
}
