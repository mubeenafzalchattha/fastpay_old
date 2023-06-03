@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="pb-120">
        <div class="coin-search-area">
            <div class="container">
                <form class="coin-search-form" action="{{ route('advertisement.search') }}" method="GET">
                    <div class="row align-items-end gy-3">
                        <div class="col-xxl-1 col-md-4 col-sm-6">
                            <label>@lang('Buy or Sell')</label>
                            <select class="select" name="type" required>
                                <option value="buy">@lang('Buy')</option>
                                <option value="sell">@lang('Sell')</option>
                            </select>
                        </div>
                        <div class="col-xxl-2 col-md-4 col-sm-6">
                            <label>@lang('Cryptocurrency')</label>
                            <select class="select" name="crypto_code" required>
                                @foreach ($cryptos as $cryptoData)
                                    <option value="{{ $cryptoData->code }}">{{ __($cryptoData->code) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xxl-2 col-md-4 col-sm-6">
                            <label>@lang('Payment Method')</label>
                            <select class="select" id="fiat-gateway" name="fiat_gateway_slug">
                                <option value="" selected disabled>@lang('Select One')</option>
                                @foreach ($fiatGateways as $gateway)
                                    <option value="{{ $gateway->slug }}" data-fiat="{{ @$gateway->fiat }}">{{ __($gateway->name) }} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xxl-2 col-md-4 col-sm-6">
                            <label>@lang('Fiat currency')</label>
                            <select class="select fiat-currency" name="fiat_code">

                            </select>
                        </div>
                        <div class="col-xxl-2 col-md-4 col-sm-6">
                            <label>@lang('Offer location')</label>
                            <input type="hidden" name="country_code">
                            <select class="select" name="country">
                                <option value="all" data-code="all">@lang('All')</option>
                                @foreach ($countries as $key => $country)
                                    <option value="{{ $country->country }}" data-code="{{ $key }}">{{ __($country->country) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xxl-2 col-md-4 col-sm-6">
                            <label>@lang('Limit')</label>
                            <input type="number" step="any" name="amount" value="{{ @$amount }}" placeholder="@lang('Enter Amount')" class="form-control">
                        </div>
                        <div class="col-xxl-1">
                            <button type="submit" class="btn--base w-100 px-xxl-2"> <i class="la la-search" aria-hidden="true"></i> @lang('Search')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="container pt-120">
            <div class="row">
                <div class="col-lg-12">
                    <div class="custom--card">
                        <div class="card-body p-0">
                            <div class="table-responsive--md">
                                <table class="table custom--table mb-0">

                                    <thead>
                                        <tr>
                                            @if ($type == 'buy')
                                                <th>@lang('Seller')</th>
                                            @else
                                                <th>@lang('Buyer')</th>
                                            @endif
                                            <th>@lang('Payment method')</th>
                                            <th>@lang('Rate')</th>
                                            <th>@lang('Payment Window')</th>
                                            <th>@lang('Limit')</th>
                                            <th>@lang('Avg. Trade Speed')</th>
                                            <th>@lang('Action')</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($advertisements as $ad)
                                            <tr class="@if (auth()->id() == $ad->user_id) own-trade-color @endif">
                                                <td>
                                                    <a href="{{ route('public.profile', $ad->username) }}" class="text--base">{{ __($ad->username) }}</a>
                                                </td>

                                                <td>{{ __($ad->gateway_name) }}</td>

                                                <td class="fw-bold">{{ showAmount($ad->rate_value) }} {{ __($ad->fiat_code) }}/ {{ __($ad->crypto_code) }}</td>

                                                <td>{{ $ad->window }} @lang('Minutes')</td>

                                                <td>
                                                    {{ showAmount($ad->min) }} {{ __($ad->fiat_code) }} - {{ showAmount($ad->max_limit) }} {{ __($ad->fiat_code) }}
                                                </td>

                                                <td>{{ avgTradeSpeed($ad) }}</td>

                                                <td>
                                                    @auth
                                                        <a href="{{ route('user.trade.request.new', $ad->id) }}" class="btn--base btn-sm">{{ __(ucfirst($type)) }}</a>
                                                    @else
                                                        <button class="btn--base btn-sm loginRequired">{{ __(ucfirst($type)) }}</button>
                                                    @endauth
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="100%" class="text-center">
                                                    <x-no-data message="No advertisement found"></x-no-data>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if ($advertisements->hasPages())
                        <div class="pagination-wrapper">
                            {{ $advertisements->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @include($activeTemplate . 'partials.login_required')
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            $('select[name="type"]').val('{{ @$type }}');
            $('select[name="crypto_code"]').val('{{ @$crypto }}');
            $('select[name="fiat_gateway_slug"]').val('{{ @$fiatGateway }}');
            $(`option[data-code={{ $countryCode }}]`).attr('selected', '');

            $('select[name=country]').change(function() {
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            }).change();

            $('#fiat-gateway').on('change', function() {
                var fiats = $(this).find('option:selected').data('fiat');
                var html = ``;

                if (fiats && fiats.length > 0) {
                    $.each(fiats, function(i, v) {
                        html += `<option value="${v.code}" ${v.code == `{{ @$fiat }}` ? 'selected': '' }>${v.code}</option>`;
                    });
                } else {
                    html = `<option value="">@lang('Select One')</option>`;
                }

                $('.fiat-currency').html(html);
            }).change();
        })(jQuery)
    </script>
@endpush
