@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $kycContent   = getContent('kyc.content', true);
        $walletImage  = fileManager()->crypto();
        $profileImage = fileManager()->userProfile();
    @endphp

    <section class="pt-60 pb-60 section--bg">
        <div class="container">

            @if ($user->kv == 0)
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">@lang('KYC Verification Required')</h4>
                    <hr>
                    <p class="mb-0">{{ __(@$kycContent->data_values->kyc_required) }} <a href="{{ route('user.kyc.form') }}" class="text--base">@lang('Click Here to Verify')</a></p>
                </div>
            @elseif($user->kv == 2)
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">@lang('KYC Verification Pending')</h4>
                    <hr>
                    <p class="mb-0">{{ __(@$kycContent->data_values->kyc_pending) }} <a href="{{ route('user.kyc.data') }}" class="text--base">@lang('See KYC Data')</a></p>
                </div>
            @endif

            <div class="row gy-4 flex-wrap-reverse">
                <div class="col-xl-3 col-lg-4">
                    <div class="profile-sidebar">
                        <div class="profile-sidebar__widget">
                            <div class="profile-author">
                                <div class="thumb">
                                    <img src="{{ getImage($profileImage->path . '/' . @$user->image, null, true) }}" alt="image">
                                </div>
                                <div class="content text-center">
                                    <h5>{{ $user->username }}</h5>
                                </div>
                                <a href="{{ route('user.profile.setting') }}" class="border-btn d-block text-center btn-md mt-4">@lang('Profile Setting')</a>
                                <a href="{{ route('user.advertisement.index') }}" class="border-btn d-block text-center btn-md mt-3">@lang('Advertisements')</a>
                                <a href="{{ route('user.trade.request.running') }}" class="border-btn d-block text-center btn-md mt-3">@lang('Running Trades')</a>
                            </div>
                        </div>

                        <div class="profile-sidebar__widget">
                            <h4 class="profile-sidebar__title">@lang('Verifications')</h4>
                            <ul class="profile-verify-list">
                                <li class="{{$user->ev ? 'verified' : 'unverified'}}"><i class="las la-envelope"></i>
                                    {{$user->ev ? trans('verified') : trans('unverified')}}
                                </li>

                                <li class="{{$user->sv ? 'verified' : 'unverified'}}"><i class="las la-mobile-alt"></i>
                                    {{$user->sv ? trans('verified') : trans('unverified')}}
                                </li>

                                <li class="{{$user->kv ? 'verified' : 'unverified'}}"><i class="las la-user-check"></i>
                                    {{$user->kv ? trans('verified') : trans('unverified')}}
                                </li>
                            </ul>
                        </div>

                        <div class="profile-sidebar__widget">
                            <h4 class="profile-sidebar__title">@lang('Informations')</h4>
                            <ul class="profile-info-list">
                                <li>
                                    <span class="caption">@lang('Joined On')</span>
                                    <span class="value">{{ showDateTime($user->created_at, 'F Y') }}</span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Advertisements')</span>
                                    <span class="value">{{ $totalAdd }}</span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Completed Trade') </span>
                                    <span class="value">{{ $user->completed_trade }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8">

                    <div class="row gy-4">
                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <label>@lang('Referral Link')</label>
                            <div class="input-group">
                                <input type="text" name="key" value="{{ route('user.register', [auth()->user()->username]) }}" class="form-control bg-white" id="referralURL" readonly>

                                <button class="input-group-text bg--base text-white border-0 copytext" id="copyBoard">
                                    <i class="lar la-copy"></i>
                                </button>
                            </div>
                        </div>

                        @foreach ($wallets as $wallet)
                            <div class="col-xl-4 col-md-6 d-widget-item">
                                <a class="d-block" href="{{ route('user.transaction.index') }}?crypto={{ $wallet->cryptoId }}">
                                    <div class="d-widget">
                                        <div class="d-widget__icon">
                                            <img src="{{ getImage($walletImage->path . '/' . $wallet->cryptoImage, $walletImage->size) }}" alt="image">
                                        </div>
                                        <div class="d-widget__content">
                                            <p class="d-widget__caption">{{ __($wallet->cryptoCode) }} </p>
                                            <h2 class="d-widget__amount">{{ showAmount($wallet->balance, 8) }}</h2>
                                            <h6 class="d-widget__usd text--base">
                                                @lang('In USD') <i class="las la-arrow-right"></i> {{ showAmount($wallet->balanceInUsd) }}
                                            </h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <h4 class="my-3">@lang('Latest Advertisements')</h4>

                    @include($activeTemplate . 'partials.user_ads_table')
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.copytext').on('click', function() {
                var copyText = document.getElementById("referralURL");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                document.execCommand("copy");
                iziToast.success({
                    message: "Copied: " + copyText.value,
                    position: "topRight"
                });
            });
        })(jQuery);
    </script>
@endpush

@push('style-lib')
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@endpush

@push('style')
    <style>
        .d-widget__usd {
            font-size: 15px;
            margin-top: 5px;
        }
    </style>
@endpush



