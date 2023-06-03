@extends($activeTemplate . 'layouts.frontend')

@section('content')
    <div class="container pt-60 pb-60">
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">

                <div class="card custom--card">
                    <div class="card-body p-4">
                        <form action="" method="post" class="register">
                            @csrf
                            <div class="form-group">
                                <label for="password">@lang('Current Password')</label>
                                <div class="icon-input-field">
                                    <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
                                    <i class="las la-lock"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password">@lang('Password')</label>
                                <div class="icon-input-field">
                                    <input type="password" class="form-control" name="password" required autocomplete="current-password">
                                    <i class="las la-key"></i>
                                </div>
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
                                <label for="confirm_password">@lang('Confirm Password')</label>
                                <div class="icon-input-field">
                                    <input type="password" class="form-control" name="password_confirmation" required autocomplete="current-password">
                                    <i class="las la-key"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="submit" class="mt-4 btn btn--base w-100" value="@lang('Change Password')">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@if($general->secure_password)
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
