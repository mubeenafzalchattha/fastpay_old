<?php

namespace App\Http\Controllers;

use App\Models\CryptoCurrency;
use App\Models\FiatCurrency;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CronController extends Controller
{
    public function fiatRate()
    {
        $general      = gs();

        $general->fiat_cron = Carbon::now();
        $general->save();

        $endpoint     = 'live';
        $access_key   = $general->fiat_api_key;
        $baseCurrency = 'USD';
        $ch           = curl_init('http://apilayer.net/api/' . $endpoint . '?access_key=' . $access_key . '&source=' . $baseCurrency);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        $exchangeRates = json_decode($json);

        if ($exchangeRates->success == false) {
            $errorMsg = $exchangeRates->error->info;
            echo "$errorMsg";
        } else {
            foreach ($exchangeRates->quotes as $key => $rate) {

                $curcode  = substr($key, -3);

                $currency = FiatCurrency::where('code', $curcode)->first();
                if ($currency) {
                    $currency->rate = $rate;
                    $currency->save();
                }
            }

            echo "EXECUTED";
        }
    }

    public function cryptoRate()
    {
        $general = gs();
        $general->crypto_cron = Carbon::now();
        $general->save();

        $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
        $cryptos = CryptoCurrency::pluck('code')->toArray();
        $cryptos = implode(',', $cryptos);

        $parameters = [
            'symbol' => $cryptos,
            'convert' => 'USD',
        ];
        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY:' . trim($general->crypto_api_key),
        ];
        $qs      = http_build_query($parameters); // query string encode the parameters
        $request = "{$url}?{$qs}"; // create the request URL
        $curl    = curl_init(); // Get cURL resource
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $request, // set the request URL
            CURLOPT_HTTPHEADER     => $headers, // set the headers
            CURLOPT_RETURNTRANSFER => 1, // ask for raw response instead of bool
        ));
        $response = curl_exec($curl); // Send the request, save the response
        $error = curl_error($curl); // Send the request, save the response
        curl_close($curl); // Close request

        $a = json_decode($response);

        if (!isset($a->data)) {
            return 'error';
        }

        $coins = $a->data;

        foreach ($coins as $coin) {

            $currency = CryptoCurrency::where('code', $coin->symbol)->first();
            if ($currency) {
                $defaultCurrency = 'USD';
                $currency->rate = $coin->quote->$defaultCurrency->price;
                $currency->save();
            }
        }

        echo "EXECUTED";
    }
}
