@extends($activeTemplate . 'layouts.frontend')
@section('content')

    @php
        $bannerContent = getContent('banner.content', true);
    @endphp

    <section class="hero bg_img" data-background="{{ getImage('assets/images/frontend/banner/' . @$bannerContent->data_values->image, '1920x1270') }}">
        <div class="container position-relative">
            <div class="row justify-content-between align-items-center">
                <div class="col-xl-5 text-xl-start text-center">
                    <h2 class="hero__title text-white mb-3">{{ __(@$bannerContent->data_values->heading) }}</h2>
                    <p class="hero__details text-white">{{ __(@$bannerContent->data_values->subheading) }}</p>
                </div>
                <div class="col-xl-6 mt-5">
                    <div class="bitcoin-form-wrapper">
                        <div class="form-image"><img src="{{ getImage('assets/images/frontend/banner/' . @$bannerContent->data_values->form_bg, '700x465') }}" alt="image"></div>
                        <h5 class="title text-white">@lang(@$bannerContent->data_values->form_header)</h5>
                        <form class="bitcoin-form" action="{{ route('advertisement.search') }}" method="GET">
                            <div class="row align-items-center">
                                <div class="col-md-6 form-group">
                                    <select class="select" name="type" required>
                                        <option value="">@lang('Select Buy or Sell')</option>
                                        <option value="buy">@lang('Buy')</option>
                                        <option value="sell">@lang('Sell')</option>
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <select class="select" name="crypto_code" required>
                                        <option value="">@lang('Select Cryptocurrency')</option>
                                        @foreach ($cryptos as $cryptoData)
                                            <option value="{{ $cryptoData->code }}">{{ __($cryptoData->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <select class="select" id="fiat-gateway" name="fiat_gateway_slug" required>
                                        <option value="">@lang('Select Payment Method')</option>
                                        @foreach ($fiatGateways as $gateway)
                                            <option value="{{ $gateway->slug }}" data-fiat="{{ @$gateway->fiat }}">{{ __($gateway->name) }} </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <select class="select fiat-currency" name="fiat_code" required>

                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <input type="hidden" name="country_code">
                                    <select class="select" name="country">
                                        <option value="all" data-code="all">@lang('Select Location')</option>
                                        @foreach ($countries as $key => $country)
                                            <option value="{{ $country->country }}" data-code="{{ $key }}">{{ __($country->country) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <input type="number" step="any" name="amount" class="form-control" placeholder="@lang('Preferred Amount')">
                                </div>

                                <div class="col-lg-12">
                                    <button type="submit" class="btn--base w-100 mt-3">@lang(@$bannerContent->data_values->form_button_text)</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection


@push('script')
    <script>
        (function($) {
            "use strict";

            @if ($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected', '');
            @endif

            $('select[name=country]').change(function() {
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            }).change();

            $('#fiat-gateway').on('change', function() {
                var fiats = $(this).find('option:selected').data('fiat');
                var html = ``;

                if (fiats && fiats.length > 0) {
                    $.each(fiats, function(i, v) {
                        html += `<option value="${v.code}">${v.code}</option>`;
                    });
                } else {
                    html = `<option value="">@lang('Select Fiat Currency')</option>`;
                }

                $('.fiat-currency').html(html);
            }).change();

        })(jQuery)
    </script>
@endpush
