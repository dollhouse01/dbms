{{ Form::open(['route' => ['reminder.store'], 'method' => 'post']) }}
<div class="modal-body">
    @php
        $subscriptionData = currentSubscription();
    @endphp
    @if (settings()['openai_module'] == 'on' &&
            (Auth::user()->type !== 'super admin' ||
                ($subscriptionData['pricing_feature_settings'] === 'off' ||
                    $subscriptionData['subscription']->enabled_openai == 1)))
        <div class="col-auto">

            <a href="javascript:void(0)" class="btn btn-primary mb-2 aiModal" data-size="lg"
                data-url="{{ route('generate.template', ['reminder']) }}" data-title="{{ __('AI Content Generator') }}">
                <span>{{ __('AI Content Generator') }}</span>
            </a>
        </div>
    @endif
    <div class="row">
        <div class="form-group  col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('date', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('time', __('Time'), ['class' => 'form-label']) }}
            {{ Form::time('time', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('document_id', __('Document'), ['class' => 'form-label']) }}
            {{ Form::select('document_id', $documents, null, ['class' => 'form-control select2']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('assign_user', __('Assign Users'), ['class' => 'form-label']) }}
            {{ Form::select('assign_user[]', $users, null, ['class' => 'form-control select2', 'multiple']) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
            {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => __('Enter reminder subject')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('message', __('Message'), ['class' => 'form-label']) }}
            {{ Form::textarea('message', null, ['class' => 'form-control', 'placeholder' => __('Enter reminder message'), 'rows' => 2]) }}
        </div>
        <div class="form-group  col-md-12 text-end">
            {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
        </div>
    </div>
</div>
{{ Form::close() }}
