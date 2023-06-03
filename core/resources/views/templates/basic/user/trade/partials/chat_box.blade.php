<div class="chat-box">
    <div class="chat-box__header">
        <div class="chat-author">
            <div class="thumb">
                <img src="{{ getImage($profileImage->path . '/' . @$topImage, null, true) }}" alt="image">
            </div>

            <div class="content">
                @if ($trade->buyer_id == $user->id)
                    <h6 class="text--base">{{ __($trade->seller->username) }}</h6>
                @elseif ($trade->seller_id == $user->id)
                    <h6 class="text--base">{{ __($trade->buyer->username) }}</h6>
                @endif
            </div>
        </div>
        <div class="trade-status flex-shrink-0">
            @if ($trade->status != Status::TRADE_COMPLETED)
                <button type="button" class="btn btn-sm btn--dark refresh" title="@lang('Click here to load new chat and trade current status')"><i class="las la-sync-alt"></i> @lang('Refresh')</button>
            @endif
        </div>
    </div>

    <div class="chat-box__thread">
        @foreach ($trade->chats as $chat)
            @php
                if ($chat->user_id == $trade->buyer_id) {
                    $senderName = null;
                    $senderImage = getImage(getFilePath('userProfile') . '/' . @$trade->buyer->image, $profileImage->size);
                } elseif ($chat->user_id == $trade->seller_id) {
                    $senderName = null;
                    $senderImage = getImage(getFilePath('userProfile') . '/' . @$trade->seller->image, $profileImage->size);
                } else {
                    $senderName = 'System';
                    $senderImage = getImage(getFilePath('logoIcon') . '/favicon.png');
                }
            @endphp


            <div class="single-message @if ($chat->user_id == auth()->id()) message--right @else message--left @endif  @if ($senderName == 'System') admin-message @endif">
                <div class="message-content-outer">
                    <div class="message-content">
                        <h6 class="name">{{ $senderName }}</h6>
                        <p class="message-text">{{ __($chat->message) }}.</p>

                        @if ($chat->file)
                            <div class="messgae-attachment">
                                <b class="text-sm d-block"> @lang('Attachment') </b>
                                <a href="{{ route('user.chat.download', [$trade->id, $chat->id]) }}" class="file-demo-btn">
                                    {{ __($chat->file) }}
                                </a>
                            </div>
                        @endif
                    </div>
                    <span class="message-time d-block text-end mt-2">{{ showDateTime($chat->created_at) }}</span>
                </div>
                <div class="message-author">
                    <img src="{{ $senderImage }}" alt="image" class="thumb">
                </div>

            </div><!-- single-message end -->
        @endforeach
    </div>

    @if ($trade->status == Status::TRADE_ESCROW_FUNDED || $trade->status == Status::TRADE_BUYER_SENT || $trade->status == Status::TRADE_DISPUTED)
        <div class="chat-box__footer">
            <form action="{{ route('user.chat.store', $trade->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="chat-send-area">
                    <div class="chat-send-field">
                        <textarea name="message" id="chat-message-field" placeholder="@lang('Type here')" class="form-control" required></textarea>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                        <div class="chat-send-file">
                            <div class="position-relative trade-chat-file-upload">
                                <input type="file" id="file" name="file" class="custom-file" accept=".jpg , .png, ,jpeg .pdf">
                            </div>
                        </div>
                        <div class="chat-send-btn">
                            <button type="sbumit" class="btn--base btn-sm">@lang('Send')</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div><!-- chat-box end -->


@push('script')
    <script>
        (function($) {
            "use strict";
            $('.refresh').on('click', function() {
                location.reload();
            });

            document.querySelector('.chat-box__thread').scrollTop = document.querySelector('.chat-box__thread').scrollHeight;

            $('.check-length').on('input', function() {
                let maxLength = $(this).data('length');
                let currentLength = $(this).val().length;
                let remain = maxLength - currentLength;
                let remainElement = $(this).parent('.form-group').find('.remaining');

                if (remain <= 4) {
                    remainElement.css('color', 'red');
                } else if (remain <= 20) {
                    remainElement.css('color', 'green');
                } else {
                    remainElement.css('color', '#6f6f6f');
                }

                remainElement.html(`<i class="las la-info-circle"></i> ${remain} @lang('characters remaining')`);
            });

            $('.check-length').on('keypress', function() {
                let maxLength = $(this).data('length');
                let currentLength = $(this).val().length;

                if (currentLength >= maxLength) {
                    return false;
                }
            });

            $('.check-length').on('paste', function(e) {
                let paste = false;
                let maxLength = parseInt($("#check-length").data('length'));
                let data = e.originalEvent.clipboardData.getData('text/plain');
                let currentLength = data.length + parseInt($("#check-length").val().length);

                if (currentLength < maxLength) {
                    paste = true;
                } else {
                    notify('error', `Maximum ${maxLength} characters allowed`);
                }

                return paste;
            });
        })(jQuery);
    </script>
@endpush
