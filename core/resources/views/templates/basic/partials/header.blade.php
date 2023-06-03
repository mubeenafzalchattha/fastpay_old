@php
    $cryptos = App\Models\CryptoCurrency::active()
        ->orderBy('name')
        ->get();
    $pages = App\Models\Page::where('tempname', $activeTemplate)
        ->where('is_default', Status::NO)
        ->get();
@endphp

<header class="header">
    <div class="header__bottom">
        <div class="container">
            <nav class="navbar navbar-expand-xl p-0 align-items-center">
                <a class="site-logo site-title" href="{{ route('home') }}"><img src="{{ getImage('assets/images/logoIcon/logo.png') }}" alt="site-logo"></a>
                <button class="navbar-toggler ms-auto shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="menu-toggle"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav main-menu m-auto">
                        <li> <a href="{{ route('home') }}">@lang('Home')</a></li>

                        <li class="menu_has_children"><a href="javascript:void(0)">@lang('Buy')</a>
                            <ul class="sub-menu">
                                @foreach ($cryptos as $crypto)
                                    <li><a href="{{ route('advertisement.all', ['buy', $crypto->code, 'all']) }}">{{ $crypto->code }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="menu_has_children"><a href="javascript:void(0)">@lang('Sell')</a>
                            <ul class="sub-menu">
                                @foreach ($cryptos as $crypto)
                                    <li><a href="{{ route('advertisement.all', ['sell', $crypto->code, 'all']) }}">{{ $crypto->code }}</a></li>
                                @endforeach
                            </ul>
                        </li>

                        @auth
                            <li><a href="{{ route('user.advertisement.index') }}">@lang('Advertisements')</a></li>
                            <li class="menu_has_children"><a href="javascript:void(0)">@lang('Trades')</a>
                                <ul class="sub-menu">
                                    <li><a href="{{ route('user.trade.request.running') }}">@lang('Running')</a></li>
                                    <li><a href="{{ route('user.trade.request.completed') }}">@lang('Completed')</a></li>
                                </ul>
                            </li>

                            <li><a href="{{ route('user.wallets') }}">@lang('Wallets')</a></li>
                            <li><a href="{{ route('user.transaction.index') }}">@lang('Transactions')</a></li>
                        @endauth

                        @foreach ($pages as $k => $data)
                            <li><a href="{{ route('pages', [$data->slug]) }}" class="nav-link">{{ __($data->name) }}</a></li>
                        @endforeach

                        <li><a href="{{ route('contact') }}">@lang('Contact')</a></li>

                        @auth
                            <li class="menu_has_children"><a href="javascript:void(0)">@lang('More')</a>
                                <ul class="sub-menu">
                                    <li><a href="{{ route('ticket.index') }}">@lang('Support')</a></li>
                                    <li><a href="{{ route('user.deposit.history') }}">@lang('Deposits')</a></li>
                                    <li><a href="{{ route('user.withdraw.history') }}">@lang('Withdrawals')</a></li>
                                    <li><a href="{{ route('user.referral.commissions.trade') }}">@lang('Referral')</a></li>
                                    <li><a href="{{ route('user.change.password') }}">@lang('Password')</a></li>
                                    <li><a href="{{ route('user.profile.setting') }}">@lang('Profile Setting')</a></li>
                                    <li><a href="{{ route('user.twofactor') }}">@lang('2FA Security')</a></li>
                                    <li><a href="{{ route('user.logout') }}">@lang('Logout')</a></li>
                                </ul>
                            </li>

                        @endauth
                    </ul>

                    <div class="nav-right">

                        @if ($general->multi_language)
                            <select class="language-select langSel rounded-2 h-100">
                                @foreach ($language as $item)
                                    <option value="{{ $item->code }}" @if (session('lang') == $item->code) selected @endif>{{ __($item->name) }}</option>
                                @endforeach

                            </select>
                        @endif

                        <ul class="account-menu ms-3">
                            @auth
                                <li>
                                    <a href="{{ route('user.home') }}" class="btn btn--base btn-sm">@lang('Dashboard')</a>
                                </li>
                            @else
                                <li class="icon"><i class="las la-user"></i>
                                    <ul class="account-submenu">
                                        <li><a href="{{ route('user.login') }}">@lang('Login')</a></li>
                                        <li><a href="{{ route('user.register') }}">@lang('Registration')</a></li>
                                    </ul>
                                </li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div><!-- header__bottom end -->
</header>
