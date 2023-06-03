@extends('admin.layouts.app')

@php
    $walletImage = fileManager()->crypto();
@endphp

@section('panel')
    @if (@json_decode($general->system_info)->version > systemDetails()['version'])
        <div class="row">
            <div class="col-md-12">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">
                        <h3 class="card-title"> @lang('New Version Available') <button class="btn btn--dark float-end">@lang('Version') {{ json_decode($general->system_info)->version }}</button> </h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-dark">@lang('What is the Update ?')</h5>
                        <p>
                            <pre class="f-size--24">{{ json_decode($general->system_info)->details }}</pre>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (@json_decode($general->system_info)->message)
        <div class="row">
            @foreach (json_decode($general->system_info)->message as $msg)
                <div class="col-md-12">
                    <div class="alert border border--primary" role="alert">
                        <div class="alert__icon bg--primary"><i class="far fa-bell"></i></div>
                        <p class="alert__message">@php echo $msg; @endphp</p>
                        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @php
        $fiatCondition = Carbon\Carbon::parse($general->fiat_cron)->diffInSeconds() >= 900;
        $cryptoCondition = Carbon\Carbon::parse($general->crypto_cron)->diffInSeconds() >= 900;
    @endphp

    @if ($fiatCondition || $cryptoCondition)
        <div class="d-flex gap-3 mb-3">
            @if ($fiatCondition)
                <div class="bg--red-shade border border--danger p-3 rounded flex-fill">
                    <h4 class="text--danger text-center">
                        @lang('Last Fiat Cron Executed'): {{ diffForHumans($general->fiat_cron) }}
                    </h4>
                </div>
            @endif
            @if ($cryptoCondition)
                <div class="bg--red-shade border border--danger p-3 rounded flex-fill">
                    <h4 class="text--danger text-center">@lang('Last Crypto Cron Runs'): {{ diffForHumans($general->crypto_cron) }}</h4>
                </div>
            @endif
        </div>
    @endif

    <div class="row gy-4">
        <div class="col-xxl-3 col-sm-6">
            <x-widget link="{{ route('admin.users.all') }}" icon="las la-users f-size--56" title="Total Users" value="{{ $widget['totalUsers'] }}" bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget link="{{ route('admin.users.active') }}" icon="las la-user-check f-size--56" title="Active Users" value="{{ $widget['activeUsers'] }}" bg="success" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget link="{{ route('admin.users.email.unverified') }}" icon="lar la-envelope f-size--56" title="Email Unverified Users" value="{{ $widget['emailUnverifiedUsers'] }}" bg="danger" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget link="{{ route('admin.users.mobile.unverified') }}" icon="las la-comment-slash f-size--56" title="Mobile Unverified Users" value="{{ $widget['mobileUnverifiedUsers'] }}" bg="red" />
        </div>
    </div><!-- row end-->

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="3" link="{{ route('admin.withdraw.log') }}" icon="lar la-credit-card" title="Approved Withdrawal" value="{{ __($widget['totalWithdrawApproved']) }}" bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="3" link="{{ route('admin.withdraw.pending') }}" icon="las la-sync" title="Pending Withdrawals" value="{{ __($widget['totalWithdrawPending']) }}" bg="warning" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="3" link="{{ route('admin.withdraw.rejected') }}" icon="las la-times-circle" title="Rejected Withdrawals" value="{{ __($widget['totalWithdrawRejected']) }}" bg="red" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="3" link="{{ route('admin.withdraw.log') }}" icon="la la-bank" title="Total Withdrawals" value="{{ __($widget['totalWithdraw']) }}" bg="19" />
        </div>
    </div><!-- row end-->

    <div class="row gy-4 mt-2">
        <div class="col-md-12">
            <h4>@lang('Deposit Summary')</h4>
        </div>
    </div>

    <div class="row gy-4 mt-2">
        @foreach ($deposits as $deposit)
            <div class="col-xxl-3 col-sm-6">
                <div class="widget-two box--shadow2 b-radius--5 bg--white">
                    <div class="widget-two__icon b-radius--5 text--success">
                        <img src="{{ getImage($walletImage->path . '/' . $deposit->image, $walletImage->size) }}" alt="image">
                    </div>
                    <div class="widget-two__content">
                        <h3>{{ showAmount($deposit->deposits_sum_amount, 8) }} {{ __($deposit->code) }}</h3>
                        <span>@lang('Charge')</span>
                        <i class="fas fa-arrow-right text--danger"></i>
                        <span class="text--danger">{{ showAmount($deposit->deposits_sum_charge, 8) }} {{ __($deposit->code) }}</span>
                    </div>
                    <a href="{{ route('admin.deposit.list') }}" class="widget-two__btn border border--success btn-outline--success">@lang('View All')</a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="3" link="{{ route('admin.ad.index') }}" icon="lab la-adversal" title="Total Adveretisements" value="{{ __($widget['totalAd']) }}" bg="19" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="3" link="{{ route('admin.trade.index') }}" icon="las la-exchange-alt" title="Total Trades" value="{{ __($widget['totalTrade']) }}" bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="3" link="{{ route('admin.crypto.index') }}" icon="lab la-bitcoin" title="Total Cryptocurrency" value="{{ __($widget['totalCrypto']) }}" bg="1" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="3" link="{{ route('admin.fiat.currency.index') }}" icon="las la-coins" title="Total Fiat Currency" value="{{ __($widget['totalFiat']) }}" bg="success" />
        </div>
    </div><!-- row end-->

    <div class="row gy-4 mt-2">
        <div class="col-md-12">
            <h4>@lang('Withdrawal Summary')</h4>
        </div>
    </div>

    <div class="row gy-4 mt-2">
        @foreach ($withdrawals as $withdrawal)
            <div class="col-xxl-3 col-sm-6">
                <div class="widget-two box--shadow2 b-radius--5 bg--white">
                    <div class="widget-two__icon b-radius--5 text--success">
                        <img src="{{ getImage($walletImage->path . '/' . $withdrawal->image, $walletImage->size) }}" alt="image">
                    </div>
                    <div class="widget-two__content">
                        <h3>{{ showAmount($withdrawal->withdrawals_sum_amount, 8) }} {{ __($withdrawal->code) }}</h3>
                        <span>@lang('Charge')</span>
                        <i class="fas fa-arrow-right text--danger"></i>
                        <span class="text--danger">{{ showAmount($withdrawal->withdrawals_sum_charge, 8) }} {{ __($withdrawal->code) }}</span>
                    </div>
                    <a href="{{ route('admin.withdraw.log') }}" class="widget-two__btn border border--success btn-outline--success">@lang('View All')</a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mb-none-30 mt-5">
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Browser') (@lang('Last 30 days'))</h5>
                    <canvas id="userBrowserChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By OS') (@lang('Last 30 days'))</h5>
                    <canvas id="userOsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Country') (@lang('Last 30 days'))</h5>
                    <canvas id="userCountryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Cron Modal --}}
    <div id="cronModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> @lang('Cron Job Setting Instruction')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="cron mb-2 text-justify">@lang('To Automate the process of deactive the expired promotional featured ads, you need to set the cron job. Set The cron time as minimum as possible.')</p>
                    <label class="w-100 fw-bold">@lang('Fiat Currency Cron Command')</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control copyText" value="curl -s {{ route('cron.fiat.rate') }}" readonly>
                        <button class="input-group-text btn btn--primary copyBtn" data-clipboard-text="curl -s {{ route('cron.fiat.rate') }}" type="button"><i class="la la-copy"></i></button>
                    </div>

                    <label class="w-100 fw-bold">@lang('Cryptocurrency Cron Command')</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control copyText" value="curl -s {{ route('cron.crypto.rate') }}" readonly>
                        <button class="input-group-text btn btn--primary copyBtn" data-clipboard-text="curl -s {{ route('cron.crypto.rate') }}" type="button"><i class="la la-copy"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .bg--red-shade {
            background-color: #f3d6d6;
        }
    </style>
@endpush

@push('script')
    <script src="{{ asset('assets/admin/js/vendor/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/chart.js.2.8.0.js') }}"></script>
    <script>
        "use strict";

        @if ($fiatCondition || $cryptoCondition)
            (function($) {
                var cronModal = new bootstrap.Modal(document.getElementById('cronModal'));
                cronModal.show();

                $('.copyBtn').on('click', function() {
                    var copyText = $(this).siblings('.copyText')[0];
                    copyText.select();
                    copyText.setSelectionRange(0, 99999);
                    document.execCommand("copy");
                    copyText.blur();
                    $(this).addClass('copied');
                    setTimeout(() => {
                        $(this).removeClass('copied');
                    }, 1500);
                });
            })(jQuery);
        @endif

        $('.copy-address').on('click', function() {
            var clipboard = new ClipboardJS('.copy-address');
            notify('success', 'Copied : ' + $(this).data('clipboard-text'));
        });

        var ctx = document.getElementById('userBrowserChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_browser_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_browser_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                maintainAspectRatio: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            }
        });


        var ctx = document.getElementById('userOsChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_os_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_os_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(0, 0, 0, 0.05)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            },
        });


        // Donut chart
        var ctx = document.getElementById('userCountryChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_country_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_country_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            }
        });
    </script>
@endpush
