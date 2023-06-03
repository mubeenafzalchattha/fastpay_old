<div class="custom--card">
    <div class="card-body p-0">
        <div class="table-responsive table-responsive--md">
            <table class="table custom--table">
                <thead>
                    <tr>
                        <th>@lang('Type')</th>
                        <th>@lang('Currency')</th>
                        <th>@lang('Payment Method')</th>
                        <th>@lang('Margin / Fixed')</th>
                        <th>@lang('Rate')</th>
                        <th>@lang('Payment Window')</th>
                        <th>@lang('Published')</th>
                        <th>@lang('Status')</th>
                        @if (!request()->routeIs('user.home'))
                            <th>@lang('Action')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($advertisements as $ad)
                        <tr>
                            @php $maxLimit = getMaxLimit($ad->user->wallets, $ad); @endphp

                            <td>
                                @php echo $ad->typeBadge @endphp
                            </td>

                            <td>{{ __($ad->fiat->code) }}</td>
                            <td>{{ __($ad->fiatGateway->name) }}</b></td>

                            <td>
                                @php echo $ad->marginValue; @endphp
                            </td>

                            <td>
                                {{ getRate($ad) }} {{ __($ad->fiat->code) }}/ {{ __($ad->crypto->code) }}
                            </td>

                            <td>{{ $ad->window }} @lang('Minutes')</td>

                            <td>
                                @php
                                    $isPublished = getPublishStatus($ad, $maxLimit);
                                @endphp

                                @if ($isPublished)
                                    <span class="badge badge--success">@lang('Yes')</span>
                                @else
                                    <button class="badge badge--danger" data-bs-toggle="modal" data-bs-target="#reasonModal" data-reasons='@json(getAdUnpublishReason($ad, $maxLimit))'><i class="fa fa-question-circle" aria-hidden="true"></i> @lang('No')</button>
                                @endif
                            </td>

                            <td>
                                @php echo $ad->statusBadge @endphp
                            </td>

                            @if (!request()->routeIs('user.home'))
                                <td>
                                    <div class="btn--group">

                                        <a href="{{ route('user.advertisement.reviews', $ad->id) }}" class="btn btn-outline--warning"><i class="lar la-thumbs-up"></i> @lang('Feedbacks')</a>

                                        <a href="{{ route('user.advertisement.edit', $ad->id) }}" class="btn btn-outline--info"><i class="la la-pencil"></i> @lang('Edit')</a>

                                        @if ($ad->status)
                                            <button class="btn btn-outline--danger confirmationBtn" data-action="{{ route('user.advertisement.status', $ad->id) }}" data-question="@lang('Are you sure to disable this ad?')"><i class="la la-eye-slash"></i> @lang('Disable')</button>
                                        @else
                                            <button class="btn btn-outline--success confirmationBtn" data-action="{{ route('user.advertisement.status', $ad->id) }}" data-question="@lang('Are you sure to enable this ad?')"><i class="las la-eye"></i> @lang('Enable')</button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if (blank($advertisements))
                <x-no-data message="No advertisement added yet"></x-no-data>
            @endif
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Unpublished Reason')</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>


@push('script')
    <script>
        (function($) {
            "use strict";
            $('#reasonModal').on('show.bs.modal', function(e) {
                let content = `<ul class="list-group list-group-flush">`;
                let reasons = $(e.relatedTarget).data('reasons');
                let i = 1;
                $.each(reasons, function(index, element) {
                    content += `<li class="list-group-item text--danger fw-bold"> ${i}. ${element} </li>`;
                    i++;
                });

                content += `</ul>`;
                $('#reasonModal').find('.modal-body').html(content);
            });

        })(jQuery)
    </script>
@endpush
