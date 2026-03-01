{{ Form::model($subscription, ['route' => ['subscriptions.update', encrypt($subscription->id)], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter subscription title'), 'required' => 'required']) }}
        </div>
        <div class="form-group">
            {{ Form::label('interval', __('Interval'), ['class' => 'form-label']) }}
            {!! Form::select('interval', $intervals, null, ['class' => 'form-control select2', 'required' => 'required']) !!}
        </div>
        <div class="form-group">
            {{ Form::label('package_amount', __('Package Amount'), ['class' => 'form-label']) }}
            {{ Form::number('package_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter package amount'), 'step' => '0.01']) }}
        </div>
        <div class="form-group">
            {{ Form::label('user_limit', __('User Limit'), ['class' => 'form-label']) }}
            {{ Form::number('user_limit', null, ['class' => 'form-control', 'placeholder' => __('Enter user limit'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('document_limit', __('Document Limit'), ['class' => 'form-label']) }}
            {{ Form::number('document_limit', null, ['class' => 'form-control', 'placeholder' => __('Enter document limit'), 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">

                <input class="form-check-input input-secondary" type="checkbox" name="enabled_document_history"
                    {{ $subscription->enabled_document_history == 1 ? 'checked' : '' }} id="enabled_document_history">

                {{ Form::label('enabled_document_history', __('Show Document History'), ['class' => 'form-label']) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">
                <input type="checkbox" class="form-check-input input-secondary" name="enabled_logged_history"
                    {{ $subscription->enabled_logged_history == 1 ? 'checked' : '' }} id="enabled_logged_history">
                {{ Form::label('enabled_logged_history', __('Show User Logged History'), ['class' => 'form-label']) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">
                <input type="checkbox" class="form-check-input input-secondary" name="enabled_openai"
                    {{ $subscription->enabled_openai == 1 ? 'checked' : '' }} id="enabled_openai">
                {{ Form::label('enabled_openai', __('Enabled Open Ai Support'), ['class' => 'form-label']) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check form-switch custom-switch-v1 mb-2">
                <input type="checkbox" class="form-check-input input-secondary" name="enabled_n8n"
                    {{ $subscription->enabled_n8n == 1 ? 'checked' : '' }} id="enabled_n8n">
                {{ Form::label('enabled_n8n', __('Enabled N8n'), ['class' => 'form-label']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">

    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
