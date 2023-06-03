<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\CryptoCurrency;
use App\Models\FiatCurrency;
use App\Models\FiatGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends Controller {

    public function searchAdvertisements(Request $request) {
        return redirect()->route('advertisement.all', [$request->type, $request->crypto_code, $request->country_code, $request->fiat_gateway_slug, $request->fiat_code, $request->amount]);
    }

    public function allAds($type, $crypto, $countryCode, $fiatGateway = null, $fiat = null, $amount = null) {
        $cryptoCurrency = CryptoCurrency::where('code', $crypto)->active()->firstOrFail();
        $ads            = $this->adsQuery($cryptoCurrency->id, $type == 'buy' ? 2 : 1);

        if ($fiatGateway) {
            $fiatGatewayCheck = FiatGateway::where('slug', $fiatGateway)->active()->firstOrFail();
            $ads->where('advertisements.fiat_gateway_id', $fiatGatewayCheck->id);
        }

        if ($fiat) {
            $fiatCheck = FiatCurrency::where('code', $fiat)->active()->firstOrFail();
            $ads->where('advertisements.fiat_currency_id', $fiatCheck->id);
        }

        if ($countryCode != 'all') {
            $ads->whereHas('user', function ($q) use ($countryCode) {
                $q->active()->where('country_code', $countryCode);
            });
        }

        if ($amount) {
            $ads->where('advertisements.min', '<=', $amount)->where('advertisements.max', '>=', $amount);
        }

        $advertisements = $ads->orderBy('advertisements.id', 'desc')->paginate(getPaginate());
        $cryptos        = CryptoCurrency::active()->orderBy('name')->get();
        $countries      = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $fiatGateways   = FiatGateway::getGateways();
        $pageTitle      = ucfirst($type) . ' ' . $cryptoCurrency->name;

        return view($this->activeTemplate . 'advertisement.all', compact('pageTitle', 'advertisements', 'type', 'crypto', 'cryptos', 'fiatGateways', 'fiat', 'amount', 'fiatGateway', 'countries', 'countryCode'));
    }

    public function currencyWiseAds(Request $request, $id) {
        $validator = Validator::make($request->all(), ['type' => 'required|in:buy,sell']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error'   => $validator->errors()->all()]);
        }

        $type = $request->type;
        $ads  = $this->adsQuery($id, $type == 'buy' ? 2 : 1)->orderBy('advertisements.id', 'desc')->limit(6)->get();
        return response()->json(['html' => view($this->activeTemplate . "advertisement.$type", compact('ads'))->render()]);
    }

    protected function adsQuery($id, $type) {
        $operator = $type == 2 ? '+' : '-';
        $ads      = Advertisement::selectRaw('advertisements.*, users.username, wallets.balance, crypto_currencies.rate AS crypto_rate, crypto_currencies.code AS crypto_code, fiat_currencies.rate AS fiat_rate, fiat_currencies.code AS fiat_code, fiat_gateways.name AS gateway_name')
            ->selectRaw("IF(advertisements.fixed_price > 0, advertisements.fixed_price, (crypto_currencies.rate * fiat_currencies.rate) $operator (crypto_currencies.rate * fiat_currencies.rate * advertisements.margin / 100)) AS rate_value");

        if ($type == 2) {
            $ads->selectRaw('LEAST(advertisements.max, wallets.balance * IF(advertisements.fixed_price > 0, advertisements.fixed_price, (crypto_currencies.rate * fiat_currencies.rate) + (crypto_currencies.rate * fiat_currencies.rate * advertisements.margin / 100))) AS max_limit');
        }

        $ads->leftJoin('users', 'advertisements.user_id', '=', 'users.id')
            ->leftJoin('wallets', function ($join) use ($id) {
                $join->on('advertisements.user_id', '=', 'wallets.user_id')
                    ->on('advertisements.crypto_currency_id', '=', 'wallets.crypto_currency_id')
                    ->where('advertisements.crypto_currency_id', '=', $id);
            })

            ->leftJoin('crypto_currencies', 'advertisements.crypto_currency_id', '=', 'crypto_currencies.id')
            ->leftJoin('fiat_currencies', 'advertisements.fiat_currency_id', '=', 'fiat_currencies.id')
            ->leftJoin('fiat_gateways', 'advertisements.fiat_gateway_id', '=', 'fiat_gateways.id')
            ->where('advertisements.type', $type)
            ->where('advertisements.status', 1)
            ->where('crypto_currencies.status', 1)
            ->where('fiat_currencies.status', 1)
            ->where('fiat_gateways.status', 1)
            ->where('crypto_currencies.id', $id);
        if ($type == 2) {
            $ads->having('advertisements.min', '<=', DB::raw('max_limit'));
        }

        return $ads;
    }
}
