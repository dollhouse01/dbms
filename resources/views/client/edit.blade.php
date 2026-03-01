{{ Form::model($user, ['route' => ['client.update', encrypt($user->id)], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }}
            {{ Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('Enter First Name'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('last_name', __('Last Name'), ['class' => 'form-label']) }}
            {{ Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('email', __('User Email'), ['class' => 'form-label']) }}
            {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter user email'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('phone_number', __('User Phone Number'), ['class' => 'form-label']) }}
            {{ Form::text('phone_number', null, ['class' => 'form-control', 'placeholder' => __('Enter user phone number')]) }}
            <small class="form-text text-muted">
                {{ __('Please enter the number with country code. e.g., +91XXXXXXXXXX') }}
            </small>

        </div>
        <div class="form-group col-md-12">
            {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
            {{ Form::textarea('address', null, ['class' => 'form-control', 'placeholder' => __('Enter user Address'), 'rows' => 2]) }}
        </div>
    </div>

</div>
<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
