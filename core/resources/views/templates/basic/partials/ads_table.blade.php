@php
    $cryptos = App\Models\CryptoCurrency::active()->get();
@endphp

<div class="row">
    <div class="col-lg-12">
        <ul class="nav nav-tabs custom--style-two justify-content-center bg-transparent">
            @foreach ($cryptos as $cryptoData)
                <li class="nav-item goto-more-{{ $type }}">
                    <a class="nav-link crypto-currency-{{ $type }} @if ($loop->first) active @endif" data-id="{{ $cryptoData->id }}" data-code="{{ $cryptoData->code }}" id="{{ $cryptoData->code }}-{{ $type }}-tab" data-bs-toggle="tab" href="#{{ $cryptoData->code }}-{{ $type }}" role="tab">{{ __($cryptoData->code) }}</a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content mt-4">
            @foreach ($cryptos as $cryptoData)
                <div class="tab-pane bg-transparent fade content-load-{{ $type }} @if ($loop->first) show active @endif" id="{{ $cryptoData->code }}-{{ $type }}" role="tabpanel" aria-labelledby="{{ $cryptoData->code }}-{{ $type }}-tab">
                    <div class="d-flex justify-content-center align-items-center currency-loading">
                        <h4><i class="fa fa-spinner fa-spin text-muted"></i></h4>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="btn-group mt-4 justify-content-center">
            <form action="{{ route('advertisement.search') }}" method="GET" class="{{ $type }}-submit">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="crypto_code">
                <input type="hidden" name="country" value="all">
                <input type="hidden" name="country_code" value="all">
                <button id="{{ $type }}-more" class="btn--base btn-sm">@lang('More')</button>
            </form>
        </div>
    </div>
</div>

@push('style')
    <style>
        .currency-loading {
            height: 400px;
            background-color: #fca12038
        }
    </style>
@endpush

@push('script')
    <script>
        'use strict';

        (function($) {
            let cryptoCount = '{{ $cryptos->count() }}';
            let type = `{{ $type }}`

            if (parseInt(cryptoCount) > 0) {
                getAds(`{{ $cryptos->first()->id ?? 0 }}`);
            }

            $(`.crypto-currency-${type}`).on('click', function() {
                $(`.content-load-${type}`).html(
                    `<div class="d-flex justify-content-center align-items-center currency-loading">
                        <h4><i class="fa fa-spinner fa-spin text-muted"></i></h4>
                    </div>`
                );
                getAds($(this).data('id'));
            });

            function getAds(id) {
                $.get(`{{ route('advertisement.currency.wise', '') }}/${id}`, {
                        type: type
                    },
                    function(data, status) {
                        if (status == 'success') {
                            $(`.content-load-${type}`).html(data.html);
                            tableResponsive();
                        }
                    }
                );
            }
            $(`#${type}-more`).on('click', function() {
                if ($(`.crypto-currency-${type}`).hasClass('active')) {
                    var cryptoCode = $(`.goto-more-${type}`).find('.active').data('code');
                    $(`.${type}-submit`).find('input[name="crypto_code"]').val(cryptoCode);
                }
            });
        })(jQuery);
    </script>
@endpush
