<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\CryptoCurrency;
use App\Models\FiatCurrency;
use App\Models\FiatGateway;
use App\Models\AdLimit;
use App\Models\Wallet;
use App\Models\Review;
use App\Models\PaymentWindow;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public function index()
    {
        $pageTitle      = 'My Advertisements';
        $advertisements = Advertisement::where('user_id', auth()->id())->latest()->with(['crypto', 'fiatGateway', 'fiat', 'user.wallets'])->paginate(getPaginate());
        $wallets        = Wallet::where('user_id', auth()->id())->first();
        return view($this->activeTemplate . 'user.advertisement.index', compact('pageTitle', 'advertisements', 'wallets'));
    }

    public function create()
    {
        $pageTitle      = 'New Advertisement';
        $isPermitted    = $this->checkAdLimit();
        $cryptos        = CryptoCurrency::active()->orderBy('name')->get();
        $paymentWindows = PaymentWindow::orderBy('minute')->get();
        $fiatGateways   = FiatGateway::getGateways();

        return view($this->activeTemplate . 'user.advertisement.create', compact('pageTitle', 'cryptos', 'fiatGateways', 'paymentWindows', 'isPermitted'));
    }

    public function edit($id)
    {
        $advertisement  = Advertisement::where('user_id', auth()->id())->findOrFail($id);
        $pageTitle      = 'Update Advertisement';
        $cryptos        = CryptoCurrency::orderBy('name')->get();
        $paymentWindows = PaymentWindow::orderBy('minute')->get();

        $fiatGateways   = FiatGateway::get()->map(function ($gateway) {
            $fiat = FiatCurrency::whereIn('id', $gateway->code)->get();
            $gateway['fiat'] = $fiat;
            return $gateway;
        });

        return view($this->activeTemplate . 'user.advertisement.edit', compact('pageTitle', 'advertisement', 'cryptos', 'fiatGateways', 'paymentWindows'));
    }

    public function store(Request $request, $id = 0)
    {
        $this->validation($request);

        $check = $this->checkData($request, $id);

        if ($check[0] == 'error') {
            $notify[] = $check;
            return back()->withNotify($notify);
        }

        if ($id) {
            $advertisement = Advertisement::where('user_id', auth()->id())->findOrFail($id);
            $message       = 'Your advertisement updated successfully';
        } else {
            $advertisement          = new Advertisement();
            $advertisement->user_id = auth()->id();
            $message                = 'Your advertisement added successfully';
        }

        $advertisement->type                = $request->type;
        $advertisement->crypto_currency_id  = $request->crypto_id;
        $advertisement->fiat_gateway_id     = $request->fiat_gateway_id;
        $advertisement->fiat_currency_id    = $request->fiat_id;
        $advertisement->margin              = $request->margin ? $request->margin : 0;
        $advertisement->fixed_price         = $request->fixed_price ? $request->fixed_price : 0;
        $advertisement->window              = $request->window;
        $advertisement->min                 = $request->min;
        $advertisement->max                 = $request->max;
        $advertisement->details             = $request->details;
        $advertisement->terms               = $request->terms;
        $advertisement->save();

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function updateStatus($id)
    {
        $advertisement = Advertisement::where('user_id', auth()->id())->findOrFail($id);

        if ($advertisement->status == Status::ENABLE) {
            $advertisement->status = Status::DISABLE;
            $notify[] = ['success', 'Advertisement deactivated successfully'];
        } else {
            $advertisement->status = Status::ENABLE;
            $notify[] = ['success', 'Advertisement activated successfully'];
        }

        $advertisement->save();
        return back()->withNotify($notify);
    }

    public function reviews($id)
    {
        $pageTitle = 'Feedbacks';
        $reviews   = Review::where('advertisement_id', $id)->where('to_id', auth()->id())->with(['user'])->paginate(getPaginate());
        return view($this->activeTemplate . 'user.advertisement.reviews', compact('pageTitle', 'reviews'));
    }

    protected function checkData($request, $id)
    {
        if (!$id) {
            $isPermitted  = $this->checkAdLimit();

            if (!$isPermitted) {
                return ['error', 'You have reached the maximum limit of creating advertisement'];
            }
        }

        $crypto      = CryptoCurrency::query();
        $fiatGateway = FiatGateway::query();
        $fiat        = FiatCurrency::query();

        if (!$id) {
            $crypto      = $crypto->active();
            $fiatGateway = $fiatGateway->active();
            $fiat        = $fiat->active();
        }

        $crypto = $crypto->where('id', $request->crypto_id)->first();

        if (!$crypto) {
            return ['error', 'Crypto currency not found or disabled'];
        }

        $fiatGateway = $fiatGateway->where('id', $request->fiat_gateway_id)->first();

        if (!$fiatGateway) {
            return ['error', 'Fiat gateway not found or disabled'];
        }

        $fiat = $fiat->where('id', $request->fiat_id)->first();

        if (!$fiat) {
            return ['error', 'Fiat currency not found or disabled'];
        }

        $request->merge([
            'crypto' => $crypto,
            'fiat' => $fiat,
        ]);

        if (getRate($request) <= 0) {
            return ['error', 'Price Equation must be positive greater than zero'];
        }

        return ['success'];
    }

    protected function validation($request)
    {
        $request->validate([
            'type'               => 'required|in:1,2',
            'crypto_id'          => 'required|integer:gt:0',
            'fiat_gateway_id'    => 'required|integer:gt:0',
            'fiat_id'            => 'required|integer:gt:0',
            'price_type'         => 'required|in:1,2',
            'margin'             => 'required_if:price_type,1|numeric|min:0',
            'fixed_price'        => 'required_if:price_type,2|numeric|gt:0',
            'window'             => 'required|integer|gt:0',
            'min'                => 'required|numeric|gt:0',
            'max'                => 'required|numeric|gt:min',
            'details'            => 'required',
            'terms'              => 'required'
        ]);
    }

    protected function checkAdLimit()
    {
        $user           = auth()->user();
        $isPermitted    = true;
        $completedTrade = $user->completed_trade;
        $createdAd      = Advertisement::where('user_id', $user->id)->count();
        $limitCount     = AdLimit::count();

        if ($limitCount == 1) {
            $limit = AdLimit::first();
        } elseif ($limitCount > 1) {
            $limit = AdLimit::where('completed_trade', '<=', $completedTrade)->orderBy('completed_trade', 'DESC')->first();
        } else {
            $limit = null;
        }

        if ($limit && $completedTrade < $limit->completed_trade) {
            $isPermitted    = false;
        }

        if ($limit && $createdAd >= $limit->ad_limit) {
            $isPermitted = false;
        }

        return $isPermitted;
    }
}
