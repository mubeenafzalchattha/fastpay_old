@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $registrationContent = getContent('registration.content', true);
        $policyElements = getContent('policy_pages.element');
    @endphp

    <section class="account-section style--two">
        <div class="left">
            <div class="line-bg">
                <img src="{{ asset($activeTemplateTrue . 'images/line-bg.png') }}" alt="image">
            </div>
            <div class="account-form-area">
                <div class="text-center">
                    <a href="{{ url('/') }}" class="account-logo"><img src="{{ getImage('assets/images/logoIcon/logo.png') }}" alt="image"></a>
                </div>

                <form class="mt-5" action="{{ route('user.register') }}" method="POST" onsubmit="return submitUserForm();">
                    @csrf

                    <div class="row">
                        @if (session()->get('reference') != null)
                            <div class="form-group col-sm-12">
                                <label>@lang('Referred By')</label>
                                <input type="text" name="referBy" id="referenceBy" class="form-control" value="{{ session()->get('reference') }}" readonly>
                            </div>
                        @endif

                        <div class="form-group col-sm-6">
                            <label>@lang('Username')</label>
                            <input id="username" type="text" class="form-control checkUser" name="username" value="{{ old('username') }}" required>
                            <small class="text-danger usernameExist"></small>
                        </div>

                        <div class="form-group col-sm-6">
                            <label>@lang('Email Address')</label>
                            <input id="email" type="email" class="form-control checkUser" name="email" value="{{ old('email') }}" required>
                        </div>

                        <div class="form-group col-sm-6">
                            <label>@lang('Country')</label>
                            <select name="country" id="country" class="form-control" required>
                                @foreach ($countries as $key => $country)
                                    <option data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" data-code="{{ $key }}">{{ __($country->country) }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group col-sm-6">
                            <label>@lang('Mobile')</label>
                            <div class="input-group ">
                                <span class="input-group-text mobile-code">

                                </span>
                                <input type="hidden" name="mobile_code">
                                <input type="hidden" name="country_code">

                                <input type="number" name="mobile" value="{{ old('mobile') }}" class="form-control checkUser" placeholder="@lang('Your Phone Number')" required>
                            </div>
                            <small class="text-danger mobileExist"></small>
                        </div>

                        <div class="form-group col-sm-6">
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
                        <div class="form-group col-sm-6">
                            <label>@lang('Confirm Password')</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>

                    @if ($general->agree)
                        <div class="form-group custom-checkbox mt-2">
                            <input class="form-check-input" type="checkbox" name="agree" id="agree" @checked(old('agree'))>
                            <label class="form-check-label" for="agree">
                                @lang('I agree with')&nbsp;
                                @foreach ($policyElements as $policy)
                                    <a href="{{ route('policy.pages', [slug(@$policy->data_values->title), $policy->id]) }}" class="text--base">{{ __($policy->data_values->title) }}</a>
                                    @if (!$loop->last)
                                        ,&nbsp;
                                    @endif
                                @endforeach
                            </label>
                        </div>
                    @endif

                    <x-captcha />

                    <div>
                        <button type="submit" id="recaptcha" class="btn--base w-100">@lang('Register Now')</button>
                    </div>
                    <div class="row align-items-center mt-3">
                        <div class="col-lg-12">
                            <p>@lang('Already Have An Account')? <a href="{{ route('user.login') }}" class="mt-3 base--color">@lang('Login Now')</a></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="right bg_img" data-background="{{ getImage('assets/images/frontend/registration/' . @$registrationContent->data_values->image, '1150x950') }}">
            <div class="content text-center">
                <h2 class="text-white mb-4">{{ __(@$registrationContent->data_values->heading) }}</h2>
                <p class="text-white">{{ __(@$registrationContent->data_values->sub_heading) }}</p>
            </div>
        </div>
    </section>

    <div class="modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-labelledby="existModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <h6 class="text-center">@lang('You already have an account please Login')</h6>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('user.login') }}" class="btn btn-sm btn--base">@lang('Login')</a>
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

@push('script')
    <script>
        "use strict";
        (function($) {
            @if ($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected', '');
            @endif

            $('select[name=country]').change(function() {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
            });
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';
                if ($(this).attr('name') == 'mobile') {
                    var mobile = `${$('.mobile-code').text().substr(1)}${value}`;
                    var data = {
                        mobile: mobile,
                        _token: token
                    }
                }
                if ($(this).attr('name') == 'email') {
                    var data = {
                        email: value,
                        _token: token
                    }
                }
                if ($(this).attr('name') == 'username') {
                    var data = {
                        username: value,
                        _token: token
                    }
                }
                $.post(url, data, function(response) {
                    if (response.data != false && response.type == 'email') {
                        $('#existModalCenter').modal('show');
                    } else if (response.data != false) {
                        $(`.${response.type}Exist`).text(`${response.type} already exist`);
                    } else {
                        $(`.${response.type}Exist`).text('');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
