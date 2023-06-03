@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="pt-60 pb-60">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="login-wrapper p-4 overflow-visible">
                        <p class="mb-4">@lang('Your account is verified successfully. Now you can change your password. Please enter a strong password and don\'t share it with anyone.')</p>

                        <form class="w-100" method="POST" action="{{ route('user.password.update') }}">
                            @csrf
                            <div class="card custom--card">
                                <div class="card-body">
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    <input type="hidden" name="token" value="{{ $token }}">

                                    <div class="form-group">
                                        <label>@lang('Password')</label>
                                        <input type="password" class="form-control" name="password" required>

                                        @if ($general->secure_password)
                                            <div class="input-popup">
                                                <p class="error lower">@lang('1 small letter minimum')</p>
                                                <p class="error capital">@lang('1 capital letter minimum')</p>
                                                <p class="error number">@lang('1 number minimum')</p>
                                                <p class="error special">@lang('1 special character minimum')</p>
                                                <p class="error minimum">@lang('6 character password')</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>@lang('Confirm Password')</label>
                                        <input type="password" class="form-control" name="password_confirmation" required>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn--base"> @lang('Reset Password')</button>
                                    </div>

                                    <p class="mt-2"><a href="{{ route('user.login') }}" class="text--base"> @lang('Login Here')?</a></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@if($general->secure_password)
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
