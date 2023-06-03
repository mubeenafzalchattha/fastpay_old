@extends($activeTemplate.'layouts.frontend')
@section('content')

    <section class="pt-60 pb-60">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12 mb-4 ">
                @php
                    echo __($maintenance->data_values->description);
                @endphp
            </div>
        </div>
        </div>
    </section>
@endsection
