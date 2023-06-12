@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $profileImage = fileManager()->userProfile();
        $user = auth()->user();
        $topImage = $trade->buyer_id == $user->id ? $trade->seller->image : $trade->buyer->image;
        $authBuyer = $user->id == $trade->buyer_id;

        $lastTime = Carbon\Carbon::parse($trade->paid_at)->addMinutes($trade->window);
        $remainingMin = $lastTime->diffInMinutes(now());

        $endTime = $trade->created_at->addMinutes($trade->window);
        $remainingMinitues = $endTime->diffInMinutes(now());
    @endphp

    <section class="pt-120 pb-120">
        <div class="container">
            <div class="row">

                <div class="col-lg-12 text-center mb-4">
                    <h3 class="mb-1">{{ $title }}</h3>
                    <h6 class="text--base">{{ $title2 }}</h6>
                </div>

                <div class="col-lg-6 pl-lg-5 mt-lg-0 mt-5">
                    @include($activeTemplate . 'user.trade.partials.chat_box')
                </div>

                <div class="col-lg-6 mt-lg-0 mt-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-sm text-muted">
                                <span>#{{ $trade->uid }}</span>
                            </span>
                            <span>
                                @php echo $trade->statusBadge @endphp
                            </span>
                        </div>
                        <div class="card-body">
                            @include($activeTemplate . 'user.trade.partials.alerts')
                            @include($activeTemplate . 'user.trade.partials.actions')
                            <div class="alert alert-warning" role="alert">
                                <p class="text-md">@lang('If the system identifies any false information or detects fraudulent activity, your account will be suspended.')</p>
                            </div>
                            @include($activeTemplate . 'user.trade.partials.info')
                            @include($activeTemplate . 'user.trade.partials.instructions')
                        </div>
                    </div>
                </div>

                @include($activeTemplate . 'user.trade.partials.review')

                 @if ($trade->reviewed == 1 && $trade->advertisement->user_id != auth()->id())
                    <div class="mt-5 alert alert-warning">
                        @lang('You\'ve already given feedback on this advertisement.') <a href="{{ route('user.trade.request.new', $trade->advertisement->id) }}" class="text--base">@lang('View Reviews')</a>
                    </div>
                @endif
            </div>
        </div>
    </section>
    {{-- APPROVE MODAL --}}
    <div id="paidModal" class="modal fade" tabindex="-1" role="dialog" >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Are you sure that you have paid the amount?')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
{{--
                    <span class="input-group-text bg--base text-white border-0" id="payment1">{{'Unique Transaction Number' }}</span>
--}}
                    <input type="text" id="unique_tranc_id" name="unique_tranc_id" class="form-control" placeholder="Unique Transaction Number" >
                    <p id="required_val"></p>
                </div>
                <hr>
                <div class="modal-header">
                    <button type="button" class="btn btn-sm btn--danger w-20" id="cancelPaid" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn--success w-20" id="confirmPaid">Submit</button>

                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        (function($) {
            "use strict";

            function startTimer(duration, display) {
                let timer = duration;
                let minutes;
                let seconds;
                if (display) {
                    setInterval(function() {
                        minutes = parseInt(timer / 60, 10);
                        seconds = parseInt(timer % 60, 10);

                        minutes = minutes < 10 ? "0" + minutes : minutes;
                        seconds = seconds < 10 ? "0" + seconds : seconds;
                        display.textContent = minutes + ":" + seconds;

                        if (--timer < 0) {
                            timer = duration;
                        }
                    }, 1000);
                }

            }

            @if ($trade->status == Status::TRADE_ESCROW_FUNDED)
                window.onload = function() {
                    let cancelMinutes = 60 * '{{ $remainingMinitues }}';
                    let display = document.querySelector('#cancel-min');
                    startTimer(cancelMinutes, display);
                };
            @endif

            @if ($trade->status == Status::TRADE_BUYER_SENT)
                window.onload = function() {
                    var disputeMinutes = 60 * '{{ $remainingMin }}';
                    let display = document.querySelector('#dispute-min');
                    startTimer(disputeMinutes, display);
                };
            @endif
        })(jQuery);

        $('#confirmPaid').on('click', function() {
            var txn_no = $('#unique_tranc_id').val();
            if(txn_no != '') {

                var id = {{$trade->id}};
                var url = "{{ route('user.trade.request.paid', ":txid") }}";
                url = url.replace(':txid', id);
                url = url+'?txn='+txn_no;
                $.ajax({
                    url: url,
                    data:{"_token": "{{csrf_token()}}",txn:txn_no},
                    method: 'POST',
                    success: function (response) {
                        $('#paidModal').modal('hide');
                    },
                    error: function (xhr, status, error) {
                        console.log('An error occurred while loading the content.');
                        html = '<span style="color:red;padding:10px">An error occurred while loading the content.</span> ';
                        $('#required_val').html(html);
                    }
                });
            } else {
                html = '<span style="color:red;padding:10px">Unique Transaction Number required as a proof.</span> ';
                $('#required_val').html(html);
            }
        });
    </script>
@endpush
