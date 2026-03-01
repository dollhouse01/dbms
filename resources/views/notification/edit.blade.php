{{ Form::model($notification, ['route' => ['notification.update', encrypt($notification->id)], 'method' => 'PUT']) }}
<div class="modal-body">
    @php
        $subscriptionData = currentSubscription();
    @endphp
    @if (settings()['openai_module'] == 'on' &&
            (Auth::user()->type !== 'super admin' ||
                ($subscriptionData['pricing_feature_settings'] === 'off' ||
                    $subscriptionData['subscription']->enabled_openai == 1)))
        <div class="text-start">
            <a href="javascript:void(0)" class="btn btn-primary mb-2 aiModal" data-size="lg"
                data-url="{{ route('generate.template', ['notification']) }}"
                data-title="{{ __('AI Content Generator') }}">
                <span>{{ __('AI Content Generator') }}</span>
            </a>
        </div>
    @endif
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Module'), ['class' => 'form-label']) }}
            {!! Form::text('name', null, [
                'class' => 'form-control',
                'required' => 'required',
                'readonly' => 'readonly',
            ]) !!}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
            {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => __('Enter Subject'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('enabled_email', __('Enabled Email Notification'), ['class' => 'form-label']) }}
            <input class="form-check-input" type="hidden" name="enabled_email" value="0">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked"
                    name="enabled_email" value="1" {{ $notification->enabled_email == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="flexSwitchCheckChecked"></label>
            </div>
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('enabled_sms', __('Enabled SMS Notification'), ['class' => 'form-label']) }}
            <input class="form-check-input sms" type="hidden" name="enabled_sms" value="0">
            <div class="form-check form-switch">
                <input class="form-check-input sms" type="checkbox" role="switch" id="flexSwitchCheck"
                    name="enabled_sms" value="1" {{ $notification->enabled_sms == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="flexSwitchCheck"></label>
            </div>
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('message', __('User Message'), ['class' => 'form-label']) }}
            {!! Form::textarea('message', $notification->message, [
                'class' => 'form-control',
                'rows' => 5,
                'id' => 'message',
            ]) !!}
        </div>

        <div class="form-group col-md-12 smsMessage {{ $notification->enabled_sms == 1 ? '' : 'd-none' }}">
            {{ Form::label('sms_message', __('User SMS Message'), ['class' => 'form-label']) }}
            {!! Form::textarea('sms_message', $notification->sms_message, [
                'class' => 'form-control ',
                'rows' => 5,
                'id' => 'sms_message',
            ]) !!}

            <p class="mt-2"> <b>{{ __('Note') }}</b> :- {{ __('Maximum 160 characters allowed!') }}</p>
        </div>


        <div class="form-group col-md-12">
            <h4 class="mb-0">{{ __('Shortcodes') }}</h4>
            <p>{{ __('Click to add below shortcodes and insert in your Message') }}</p>

            @php
                $i = 0; // Counter for determining the display state of sections
            @endphp

            @if (!empty($notification->short_code) && is_array($notification->short_code))
                <section class="sortcode_var" style="display: {{ $i == 0 ? 'block' : 'none' }};">
                    @foreach ($notification->short_code as $item)
                        <a href="javascript:void(0);">
                            <span class="badge bg-light-primary rounded-pill f-14 px-2 sort_code_click m-2"
                                data-val="{{ $item }}">
                                {{ $item }}
                            </span>
                        </a>
                    @endforeach
                </section>
            @else
                <p>{{ __('No shortcodes available for this notification.') }}</p>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary ml-10']) }}
</div>
{{ Form::close() }}


<script>
    $(document).ready(function() {

        // Toggle SMS message visibility
        $(document).on('change', '.sms', function() {
            if ($(this).is(':checked')) {
                $('.smsMessage').removeClass('d-none');
            } else {
                $('.smsMessage').addClass('d-none');
            }
        });

        let editorInstance;
        let activeField = 'ckeditor'; // Default active field

        // Detect focus on CKEditor or SMS textarea
        $(document).on('focus', '#sms_message', function() {
            activeField = 'sms';
        });

        // Initialize CKEditor
        ClassicEditor
            .create(document.querySelector('#message'), {})
            .then(editor => {
                editorInstance = editor;

                // Detect when CKEditor is focused
                editor.editing.view.document.on('focus', () => {
                    activeField = 'ckeditor';
                });

                $(document).on('click', '.sort_code_click', function() {
                    const shortcode = $(this).data('val');

                    if (activeField === 'sms') {
                        // Insert into SMS textarea
                        const textarea = $('#sms_message')[0];
                        const start = textarea.selectionStart;
                        const end = textarea.selectionEnd;
                        textarea.value =
                            textarea.value.substring(0, start) +
                            shortcode +
                            textarea.value.substring(end);
                        textarea.selectionStart = textarea.selectionEnd = start + shortcode.length;
                    } else if (activeField === 'ckeditor' && editorInstance) {
                        // Insert into CKEditor
                        editor.model.change(writer => {
                            const viewFragment = editor.data.processor.toView(shortcode);
                            const modelFragment = editor.data.toModel(viewFragment);
                            editor.model.insertContent(modelFragment);
                        });
                    }
                });
            })
            .catch(error => console.log(error));

        // Module change logic
        $(document).on('change', '.module', function() {
            const modd = $('.module').val();
            $('.sortcode_var').hide();
            $('.sortcode_var.' + modd).show();

            const subject = $('.sortcode_tm.' + modd).attr('data-subject');
            $('.subject').val(subject);

            const templete = $('.sortcode_tm.' + modd).attr('data-templete');
            if (editorInstance) {
                editorInstance.setData(templete);
            }
            $('#sms_message').val(templete);
        });
    });
</script>
