<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            <div class="form-check form-switch">
                {!! Form::checkbox('exp_date_status', 1, null, [
                    'class' => 'form-check-input scsh',
                    'id' => 'flexSwitchCheckChecked',
                    'role' => 'switch',
                ]) !!}
                {!! Form::label('flexSwitchCheckChecked', __('Link expiration'), ['class' => 'form-check-label h4']) !!}
            </div>
            {{ Form::date('exp_date', null, ['class' => 'form-control sse d-none']) }}
        </div>
        <hr>
        <div class="form-group col-md-12">
            <div class="form-check form-switch">
                <input class="form-check-input scsh" type="checkbox" role="switch" id="flexSwitchCheckChecked"
                    name="password_status" value="1">
                <label class="form-check-label h4" for="flexSwitchCheckChecked">{{ __('Password Protection') }}</label>
            </div>
            {{ Form::text('password', null, ['class' => 'form-control sse d-none', 'placeholder' => __('Enter Password')]) }}
        </div>
        <hr>
        <div class="col-md-12 mb-2 text-end">
            {{ Form::button(__('Genarate Link'), ['class' => 'btn btn-secondary btn-rounded genarate_link']) }}
        </div>
        <div class="form-group col-md-12">
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="shareableLinkInput" value="">
                <span class="input-group-text pointer" id="copyButton" onclick="copyToClipboard('')"><i
                        data-feather="copy"></i></span>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">

</div>


<script>
    $(document).on('change keyup', '.scsh, .sse', function() {
        $('#shareableLinkInput').val('');
        $('.scsh').each(function() {
            const $wrapper = $(this).closest('.form-group');
            const $relatedInput = $wrapper.find('.sse');
            if ($(this).is(':checked')) {
                $relatedInput.removeClass('d-none');
            } else {
                $relatedInput.addClass('d-none');
            }
        });
    });


    $(document).on('click', '.genarate_link', function() {
        $.ajax({
            url: '{{ route('generate.shareable.link') }}',
            type: 'GET',
            data: {
                exp_date: $('input[name="exp_date_status"]').is(':checked') ? $(
                    'input[name="exp_date"]').val() : '',
                password: $('input[name="password_status"]').is(':checked') ? $(
                    'input[name="password"]').val() : '',
                did: '{{ $id }}',
            },
            success: function(response) {
                if (response.url) {
                    $('#shareableLinkInput').val(response.url);
                    $('#copyButton').attr('onclick', "copyToClipboard('" + response.url + "')");
                }
            },
            error: function() {
                alert('Failed to generate link.');
            }
        });
    });
</script>
