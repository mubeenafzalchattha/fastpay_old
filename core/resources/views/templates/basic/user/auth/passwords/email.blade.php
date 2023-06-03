@extends($activeTemplate . 'layouts.frontend')

@section('content')
    <section class="pt-60 pb-60">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="login-wrapper p-4">
                        <form class="w-100" method="POST" action="{{ route('user.password.email') }}">
                            @csrf
                            <div class="card custom--card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>@lang('Email or Username')</label>
                                        <input type="text" class="form-control" name="value" value="{{ old('value') }}" autofocus="off" required>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn--base w-100"> @lang('Send Password Code')</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
