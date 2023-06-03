@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $advertisementContent = getContent('advertisement.content', true);
    @endphp

    <section class="pt-60 pb-60">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-end">
                        <a href="{{ route('user.advertisement.index') }}" class="btn btn-sm btn--base">
                            <i class="lab la-adversal"></i> @lang('My Ads')
                        </a>
                    </div>
                </div>
                <div class="col-lg-10 mt-4">
                    @if ($isPermitted)
                        <form class="create-trade-form" action="{{ route('user.advertisement.store') }}" method="POST">
                            @csrf
                            <div class="create-trade-form__block">
                                <div class="line-title-wrapper mb-3">
                                    <h4 class="line-title">@lang('Advertisement Information')</h4>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>@lang('I Want To')
                                                <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->i_want_to) }}"></i>
                                            </label>

                                            <select class="select" name="type" required>
                                                <option value="1">@lang('Buy')</option>
                                                <option value="2">@lang('Sell')</option>
                                            </select>
                                            <code class="text--base tradeChargeText"></code>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>
                                                @lang('Cryptocurrency')
                                                <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->crypto_currency) }}"></i>
                                            </label>

                                            <select class="select" name="crypto_id" required>
                                                <option value="">@lang('Select One')</option>
                                                @foreach ($cryptos as $crypto)
                                                    <option value="{{ $crypto->id }}" data-crypto="{{ $crypto->rate }}" data-currency="{{ $crypto->code }}">{{ __($crypto->name) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="create-trade-form__block">

                                <div class="line-title-wrapper mb-3">
                                    <h4 class="line-title">@lang('Payment Information')</h4>
                                </div>

                                <div class="row gy-3">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>@lang('Payment Method') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->fiat_payment_method) }}"></i></label>

                                            <select class="select" id="fiat-gateway" name="fiat_gateway_id" required>
                                                <option value="">@lang('Select One')</option>
                                                @foreach ($fiatGateways as $gateway)
                                                    <option value="{{ $gateway->id }}" data-fiat="{{ @$gateway->fiat }}">{{ __($gateway->name) }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>@lang('Currency')</label>
                                            <select class="select fiat-currency" name="fiat_id" required></select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>
                                                @lang('Price Type') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->price_type) }}"></i>
                                            </label>

                                            <select class="select" name="price_type" required>
                                                <option value="1" selected>@lang('Margin')</option>
                                                <option value="2">@lang('Fixed')</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 margin-fixed">
                                        <div class="form-group">
                                            <label>@lang('Margin') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->margin) }}"></i></label>

                                            <div class="input-group">
                                                <input type="number" step="0.01" class="form-control" name="margin" value="0" placeholder="@lang('Margin rate')" required>
                                                <span class="input-group-text border-0">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>@lang('Payment Window') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->payment_window) }}"></i></label>

                                            <select class="select" name="window" required>
                                                <option value="">@lang('Select One')</option>
                                                @foreach ($paymentWindows as $window)
                                                    <option value="{{ $window->minute }}">{{ $window->minute }}
                                                        @lang('Minutes')
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>@lang('Minimum Limit') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->minimum_limit) }}"></i></label>
                                            <div class="input-group">
                                                <input type="number" step="any" name="min" value="{{ old('min') }}" placeholder="@lang('Minimum amount')" class="form-control" required>
                                                <span class="input-group-text currency-text border-0"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>@lang('Maximum Limit') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->maximum_limit) }}"></i></label>
                                            <div class="input-group">
                                                <input type="number" step="any" name="max" value="{{ old('max') }}" placeholder="@lang('Maximum amount')" class="form-control" required>
                                                <span class="input-group-text currency-text border-0"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>@lang('Price Equation') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->price_equation) }}"></i></label>
                                            <p id="priceEquation" class="text--base">0.00</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>@lang('Payment Details') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->payment_details) }}"></i></label>
                                            <textarea name="details" class="form-control" placeholder="@lang('Write about your convenient payment method')" required>{{ old('details') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>@lang('Terms of Trade') <i class="la la-info-circle " title="{{ __(@$advertisementContent->data_values->terms_of_trades) }}"></i></label>
                                        <textarea name="terms" class="form-control" placeholder="@lang('If you have any condition write here')" required>{{ old('terms') }}</textarea>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <h2 class="text-center mb-3">
                                @lang('Limit Exhausted')!
                            </h2>
                            <p class="text-center">
                                @lang('You have reached the maximum limit for advertising. Complete more trade to publish more advertisement.')
                            </p>
                        </div>
                    @endif


                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            $('[name=type]').on('change', function() {
                if (this.value == 1) {
                    $('.tradeChargeText').empty();
                } else {
                    if (parseFloat('{{ $general->trade_charge }}') > 0) {
                        $('.tradeChargeText').text(`@lang('For selling') {{ getAmount($general->trade_charge) }}% @lang('will be charged for each completed trade.')`);
                    }
                }
            });

            $('[name=price_type]').on('change', function() {
                let html = ``;

                if (this.value == 1) {
                    html += `
                        <label>@lang('Margin') <i class="la la-info-circle "  title="{{ __(@$advertisementContent->data_values->margin) }}"></i></label>

                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="margin" value="0"
                            placeholder="@lang('Margin rate')" required>
                            <span class="input-group-text border-0">%</span>
                        </div>
                    `;

                    margin = 0;
                    fixedRate = null;

                } else {
                    var currencyText;

                    if (fiatCurrency) {
                        currencyText = fiatCurrency;
                    } else {
                        currencyText = '';
                    }

                    html += `
                        <label>@lang('Fixed Price') <i class="la la-info-circle "  title="{{ __(@$advertisementContent->data_values->fixed_price) }}"></i></label>

                        <div class="input-group">
                            <input type="number" step="any" class="form-control" name="fixed_price" value="0"
                            placeholder="@lang('Fixed Price')" required>
                            <span class="input-group-text currency-text border-0">${currencyText}</span>
                        </div>
                    `;

                    fixedRate = 0;
                    margin = null;
                }

                priceEquation();
                $('.margin-fixed').html(html);
                triggerTooltip();
            });


            $('#fiat-gateway').on('change', function() {
                var fiats = $(this).find('option:selected').data('fiat');
                var html = `<option data-fiat="" value="">@lang('Select One')</option>`;

                if (fiats && fiats.length > 0) {
                    $.each(fiats, function(i, v) {
                        html += `<option value="${v.id}" data-fiat="${parseFloat(v.rate)}" data-currency="${v.code}">${v.code}</option>`;
                    });
                }

                $('.fiat-currency').html(html);
            }).change();


            var type = $('select[name="type"]').find('option:selected').val();
            var cryptoRate = $('select[name="crypto_id"]').find('option:selected').data('crypto');
            var margin = $('input[name="margin"]').val();
            var cryptoCurrency = $('select[name="crypto_id"]').find('option:selected').data('currency');
            var fiatRate = null;
            var fiatCurrency = null;
            var fixedRate = null;

            $('select[name="type"]').on('change', function() {
                type = $(this).find('option:selected').val();
                priceEquation();
            });

            $('select[name="crypto_id"]').on('change', function() {
                cryptoRate = $(this).find('option:selected').data('crypto');
                cryptoCurrency = $(this).find('option:selected').data('currency');
                priceEquation();
            });

            $('select[name="fiat_id"]').on('change', function() {
                fiatRate = $(this).find('option:selected').data('fiat');
                fiatCurrency = $(this).find('option:selected').data('currency');
                $(document).find('.currency-text').text(`@lang('${fiatCurrency}')`);
                priceEquation();
            });

            $(document).on('input', '[name=margin]', function() {
                margin = $(this).val();
                priceEquation();
            });

            $(document).on('input', '[name=fixed_price]', function() {
                fixedRate = $(this).val();
                priceEquation();
            });

            function priceEquation() {

                if (!fiatRate) {
                    $('#priceEquation').text('0.00');
                } else {

                    if ($('[name=price_type]').val() == 1) {

                        var amount = parseFloat(cryptoRate) * parseFloat(fiatRate);

                        if (parseFloat(margin) >= 0) {
                            var rate;

                            if (parseInt(type) == 1) {
                                rate = parseFloat(amount) - ((amount * parseFloat(margin)) / 100);
                            } else if (parseInt(type) == 2) {
                                rate = parseFloat(amount) + ((amount * parseFloat(margin)) / 100);
                            }

                            if (parseFloat(rate) <= 0) {
                                $('[name=margin]').val(0);
                                notify('error', 'Price equation or rate must be positive grater than zero')
                            }

                            $('#priceEquation').text(parseFloat(rate).toFixed(2) + ' ' + fiatCurrency + '/' + cryptoCurrency);

                        } else {
                            $('#priceEquation').text(parseFloat(amount).toFixed(2) + ' ' + fiatCurrency + '/' + cryptoCurrency);
                        }
                    } else {
                        if (parseFloat(fixedRate) > 0) {

                            var rate = parseFloat(fixedRate).toFixed(2);
                            $('#priceEquation').text(parseFloat(rate).toFixed(2) + ' ' + fiatCurrency + '/' + cryptoCurrency);

                        } else {
                            $('#priceEquation').text('0.00' + ' ' + fiatCurrency + '/' + cryptoCurrency);
                        }
                    }
                }
            }
        })(jQuery);
    </script>
@endpush
