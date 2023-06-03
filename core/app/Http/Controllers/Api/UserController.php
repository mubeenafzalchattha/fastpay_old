<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\CommissionLog;
use App\Models\CryptoCurrency;
use App\Models\FiatGateway;
use App\Models\Form;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Wallet;
use App\Models\CryptoWallet;
use App\Models\Referral;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function dashboard()
    {
        $this->insertNewCryptoWallets();

        $user                = auth()->user();
        $wallets             = Wallet::where('user_id', $user->id)
                                ->leftJoin('crypto_currencies','crypto_currencies.id','=','crypto_currency_id')
                                ->selectRaw("crypto_currencies.id as cryptoId, code as cryptoCode, balance, image as cryptoImage, (balance * crypto_currencies.rate) as balanceInUsd")
                                ->orderByRaw("wallets.id desc")
                                ->get();
        $basicQuery          = $user->advertisements();
        $totalBuyAd          = clone $basicQuery;
        $totalSellAd         = clone $basicQuery;
        $totalBuyAdCount     = $totalBuyAd->where('type', 1)->count();
        $totalSellAdCount    = $totalSellAd->where('type', 2)->count();
        $referralLink        = route('user.register', [auth()->user()->username]);
        $runningTradeCount   = $this->getTradeData('running');
        $completedTradeCount = $this->getTradeData('completed');

        $advertisements = $basicQuery->active()
            ->latest()
            ->limit(10)
            ->whereHas('crypto', function ($crypto) {
                return $crypto->active();
            })
            ->whereHas('fiatGateway', function ($fiatGateway) {
                return $fiatGateway->active();
            })
            ->whereHas('fiatGateway', function ($fiatGateway) {
                return $fiatGateway->active();
            })
            ->with(['crypto', 'fiatGateway', 'fiat'])
            ->get();


        $data = [];

        foreach ($advertisements as $ad) {
            $maxLimit    = getMaxLimit($ad->user->wallets, $ad);
            $isPublished = getPublishStatus($ad, $maxLimit);
            $advertise   = [];

            if ($isPublished) {
                $advertise['id']                 = $ad->id;
                $advertise['crypto_code']        = $ad->crypto->code;
                $advertise['crypto_image']       = $ad->crypto->image;
                $advertise['fiat_gateway']       = $ad->fiatGateway->name;
                $advertise['fiat_gateway_image'] = $ad->fiatGateway->image;
                $advertise['rate']               = strval(getRate($ad));
                $advertise['rate_attribute']     = getRateAttributeForApp($ad);
                $advertise['window']             = $ad->window . ' Minutes';
                $advertise['status']             = $ad->status ? 'Enabled' : 'Disabled';
                $advertise['fixed_margin']       = strip_tags($ad->marginValue);
                $advertise['type']               = $ad->type == 1 ? 'Buy' : 'Sell';
                $data[] = $advertise;
            }
        }

        $user->image = getImage(getFilePath('userProfile') . '/' . $user->image, null, true);

        $notify[] = 'User dashboard';
        return response()->json([
            'remark' => 'user_dashboard',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'gateway_image_path'    => getFilePath('gateway'),
                'wallets'               => $wallets,
                'total_buy_add'         => $totalBuyAdCount,
                'total_sell_add'        => $totalSellAdCount,
                'running_trade_count'   => $runningTradeCount,
                'completed_trade_count' => $completedTradeCount,
                'crypto_image_path'     => getFilePath('crypto'),
                'user_info'             => $user,
                'ads'                   => $data,
                'referral_link'         => $referralLink,
            ]
        ]);
    }

    protected function insertNewCryptoWallets()
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

    protected function getTradeData($scope)
    {
        $trades = Trade::$scope()->where(function ($q) {
            $q->where('buyer_id', auth()->id())->orWhere('seller_id', auth()->id());
        })->count();

        return $trades;
    }

    public function notifications()
    {
        $notifications = UserNotification::where('user_id', auth()->id())->latest()->paginate(getPaginate());
        $notify[]      = 'User notifications';

        return response()->json([
            'remark' => 'user_notification',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'notifications' => $notifications
            ]
        ]);
    }

    public function notificationMarkRead($id)
    {
        $notification = UserNotification::where('user_id', auth()->id())->find($id);

        if (!$notification) {
            $notify[] = 'Notification not found';
            return response()->json([
                'remark' => 'notification_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $notification->read_status = 1;
        $notification->save();

        $notify[] = 'Notification marked as read';
        return response()->json([
            'remark' => 'user_notification',
            'status' => 'success',
            'message' => ['success' => $notify]
        ]);
    }

    public function userDataSubmit(Request $request)
    {
        $user = auth()->user();
        if ($user->profile_complete == 1) {
            $notify[] = 'You\'ve already completed your profile';
            return response()->json([
                'remark' => 'already_completed',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }


        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->address   = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state'  => $request->state,
            'zip'    => $request->zip,
            'city'   => $request->city,
        ];
        $user->profile_complete = 1;
        $user->save();

        $notify[] = 'Profile completed successfully';
        return response()->json([
            'remark' => 'profile_completed',
            'status' => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function kycForm()
    {
        if (auth()->user()->kv == 2) {
            $notify[] = 'Your KYC is under review';
            return response()->json([
                'remark' => 'under_review',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        if (auth()->user()->kv == 1) {
            $notify[] = 'You are already KYC verified';
            return response()->json([
                'remark' => 'already_verified',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        $form = Form::where('act', 'kyc')->first();
        $notify[] = 'KYC field is below';
        return response()->json([
            'remark' => 'kyc_form',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'form' => $form->form_data
            ]
        ]);
    }

    public function kycSubmit(Request $request)
    {
        $form           = Form::where('act', 'kyc')->first();
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $validator      = Validator::make($request->all(), $validationRule);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $userData       = $formProcessor->processFormData($request, $formData);
        $user           = auth()->user();
        $user->kyc_data = $userData;
        $user->kv       = 2;
        $user->save();

        $notify[] = 'KYC data submitted successfully';
        return response()->json([
            'remark' => 'kyc_submitted',
            'status' => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function depositHistory()
    {
        $deposits = auth()->user()->deposits()->searchable(['trx']);

        if (request()->crypto_id) {
            $deposits = $deposits->where('crypto_currency_id', request()->crypto_id);
        }

        $deposits = $deposits->with(['crypto'])->orderBy('id', 'desc')->paginate(getPaginate());
        $cryptos  = CryptoCurrency::orderBy('name')->get();

        $notify[] = 'Deposit data';

        return response()->json([
            'remark' => 'deposits',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'crypto_image_path' => getFilePath('crypto'),
                'deposits'         => $deposits,
                'cryptos'          => $cryptos
            ]
        ]);
    }

    public function transactions(Request $request)
    {
        $remarks      = Transaction::distinct('remark')->whereNotNull('remark')->get('remark');
        $transactions = Transaction::where('user_id', auth()->id())->where('crypto_currency_id', '!=', null);

        if ($request->search) {
            $transactions = $transactions->where('trx', $request->search);
        }

        if ($request->type) {
            $type = $request->type == 'plus' ? '+' : '-';
            $transactions = $transactions->where('trx_type', $type);
        }

        if ($request->crypto_id) {
            $transactions = $transactions->where('crypto_currency_id', $request->crypto_id);
        }

        if ($request->remark) {
            $transactions = $transactions->where('remark', $request->remark);
        }

        $transactions = $transactions->with(['crypto'])->orderBy('id', 'desc')->paginate(getPaginate());
        $cryptos      = CryptoCurrency::orderBy('name')->get();
        $notify[]     = 'Transactions data';

        return response()->json([
            'remark' => 'transactions',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'crypto_image_path' => getFilePath('crypto'),
                'transactions'     => $transactions,
                'remarks'          => $remarks,
                'cryptos'          => $cryptos,
            ]
        ]);
    }

    public function wallets()
    {
        $wallets = Wallet::where('user_id', auth()->id())->with('crypto')->latest()->get();
        $notify[] = 'User wallets';

        return response()->json([
            'remark' => 'wallets',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'wallets'          => $wallets,
                'crypto_image_path' => getFilePath('crypto'),
            ]
        ]);
    }

    public function singleWallet($id)
    {
        $crypto = CryptoCurrency::find($id);

        if (!$crypto) {
            return response()->json([
                'remark' => 'crypto_error',
                'status' => 'error',
                'message' => ['error' => 'Crypto Currency not found'],
            ]);
        }

        $basicQuery            = CryptoWallet::where('user_id', auth()->id())->where('crypto_currency_id', $crypto->id);
        $totalAddress          = clone $basicQuery;
        $totalCryptoWallet     = clone $basicQuery;
        $totalAddressCount     = $totalAddress->count();
        $cryptoWalletAddresses = $totalCryptoWallet->latest()->paginate(getPaginate());

        $notify[] = 'User receiving wallets';

        return response()->json([
            'remark' => 'wallets_address',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'total_address_count'    => $totalAddressCount,
                'crypto_wallet_addresses' => $cryptoWalletAddresses,
                'crypto'                 => $crypto,
                'crypto_image_path'      => getFilePath('crypto'),
            ]
        ]);
    }

    public function publicProfile(Request $request, $username)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|gt:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = User::where('username', $username)->active()->first();

        if (!$user) {
            return response()->json([
                'remark' => 'user_error',
                'status' => 'error',
                'message' => ['error' => 'User not found or banned'],
            ]);
        }

        $basicQuery = $user->advertisements()->active()
            ->whereHas('fiat', function ($q) {
                $q->active();
            })->whereHas('crypto', function ($q) {
                $q->active();
            })
            ->whereHas('fiatGateway',  function ($q) {
                $q->active();
            });

        $buyAds        = clone $basicQuery;
        $sellAds       = clone $basicQuery;
        $latestBuyAds  = $buyAds->where('type', 2)->active();
        $latestSellAds = $sellAds->where('type', 1)->active();

        if ($request->crypto_id) {
            $latestBuyAds  = $latestBuyAds->where('crypto_currency_id', $request->crypto_id);
            $latestSellAds = $latestSellAds->where('crypto_currency_id', $request->crypto_id);
        }

        if ($request->fiat_gateway_id) {
            $latestBuyAds  = $latestBuyAds->where('fiat_gateway_id', $request->fiat_gateway_id);
            $latestSellAds = $latestSellAds->where('fiat_gateway_id', $request->fiat_gateway_id);
        }

        if ($request->amount) {
            $latestBuyAds  = $latestBuyAds->where('min', '<=', $request->amount)->where('max', '>=', $request->amount);
            $latestSellAds = $latestSellAds->where('min', '<=', $request->amount)->where('max', '>=', $request->amount);
        }

        $latestBuyAds  = $latestBuyAds->latest()->with(['crypto', 'user.wallets', 'fiatGateway', 'fiat'])->paginate(getPaginate());
        $latestSellAds = $latestSellAds->latest()->with(['crypto', 'user.wallets', 'fiatGateway', 'fiat'])->paginate(getPaginate());

        $buyData  = [];
        $sellData = [];

        foreach ($latestBuyAds as $ad) {
            $maxLimit   = getMaxLimit($ad->user->wallets, $ad);
            $show       = $maxLimit >= $ad->min ? true : false;
            $advertise  = [];

            if ($show) {
                $advertise['id']                 = $ad->id;
                $advertise['user_username']      = $ad->user->username;
                $advertise['user_id']            = $ad->user->id;
                $advertise['user_image']         = getImage(getFilePath('userProfile') . '/' . $ad->user->image, null, true);
                $advertise['fiat_gateway']       = $ad->fiatGateway->name;
                $advertise['fiat_gateway_image'] = $ad->fiatGateway->image;
                $advertise['rate']               = strval(getRate($ad));
                $advertise['rate_attribute']     = getRateAttributeForApp($ad);
                $advertise['window']             = $ad->window . ' Minutes';
                $advertise['max_limit']          = showAmount($ad->min) . ' - ' . showAmount($maxLimit) . ' ' . $ad->fiat->code;
                $advertise['avg_speed']          = avgTradeSpeed($ad);

                $buyData[] = $advertise;
            }
        }

        foreach ($latestSellAds as $ad) {
            $advertise['id']                 = $ad->id;
            $advertise['user_username']      = $ad->user->username;
            $advertise['user_id']            = $ad->user->id;
            $advertise['user_image']         = getImage(getFilePath('userProfile') . '/' . $ad->user->image, null, true);
            $advertise['fiat_gateway']       = $ad->fiatGateway->name;
            $advertise['fiat_gateway_image'] = $ad->fiatGateway->image;
            $advertise['rate']               = strval(getRate($ad));
            $advertise['rate_attribute']     = getRateAttributeForApp($ad);
            $advertise['window']             = $ad->window . ' Minutes';
            $advertise['max_limit']          = showAmount($ad->min) . ' - ' . showAmount($ad->max) . ' ' . $ad->fiat->code;
            $advertise['avg_speed']          = avgTradeSpeed($ad);

            $sellData[] = $advertise;
        }

        $allReviewCount      = $user->positiveFeedBacks->count() + $user->negativeFeedBacks->count();
        $positiveReviewCount = $user->positiveFeedBacks->count();
        $negativeReviewCount = $user->negativeFeedBacks->count();
        $cryptos             = CryptoCurrency::active()->orderBy('name')->get();
        $fiatGateways        = FiatGateway::active()->orderBy('name')->get();

        $notify[] = 'User public profile';

        return response()->json([
            'remark' => 'public_profile',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'gateway_image_path'     => getFilePath('gateway'),
                'user'                   => $user,
                'buy_ads'                => $buyData,
                'sell_ads'               => $sellData,
                'all_review_count'       => $allReviewCount,
                'buy_ads_next_page_url'  => $latestBuyAds->nextPageUrl(),
                'sell_ads_next_page_url' => $latestSellAds->nextPageUrl(),
                'positive_review_count'  => $positiveReviewCount,
                'negative_review_count'  => $negativeReviewCount,
                'cryptos'                => $cryptos,
                'fiat_gateways'          => $fiatGateways,
            ]
        ]);
    }

    public function referralCommissions()
    {
        $referralLogs = CommissionLog::where('to_id', auth()->id())->with('bywho', 'crypto')->latest()->paginate(getPaginate());
        $notify[]     = 'User referral commissions';

        return response()->json([
            'remark' => 'referral_commissions',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'crypto_image_path' => getFilePath('crypto'),
                'referral_logs'    => $referralLogs,
            ]
        ]);
    }

    public function myRef()
    {
        $maxLevel = Referral::max('level');
        $referees =  getReferees(auth()->user(), $maxLevel);

        $notify[]      = 'Refereed users';

        return response()->json([
            'remark' => 'referred_users',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'referral_users' => $referees,
            ]
        ]);
    }

    public function submitProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname'  => 'required',
            'image'     => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();
        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->address = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state'  => $request->state,
            'zip'    => $request->zip,
            'city'   => $request->city,
        ];

        if ($request->hasFile('image')) {
            $fileName = fileUploader($request->image, getFilePath('userProfile'), getFileSize('userProfile'), @$user->image);
            $user->image = $fileName;
        }

        $user->save();

        $notify[] = 'Profile updated successfully';
        return response()->json([
            'remark' => 'profile_updated',
            'status' => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function submitPassword(Request $request)
    {
        $passwordValidation = Password::min(6);
        $general = gs();
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', $passwordValidation]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = 'Password changed successfully';
            return response()->json([
                'remark' => 'password_changed',
                'status' => 'success',
                'message' => ['success' => $notify],
            ]);
        } else {
            $notify[] = 'The password doesn\'t match!';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
    }

    public function userData()
    {
        $notify[] = 'User information';
        $user = auth()->user();
        $user->image = getImage(getFilePath('userProfile') . '/' . $user->image, null, true);

        return response()->json([
            'remark'  => 'user_info',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'user' => $user,

            ]
        ]);
    }
}
