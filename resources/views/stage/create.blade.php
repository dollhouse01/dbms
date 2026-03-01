{{ Form::open(['url' => 'Stage', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-12">
            {{ Form::label('title', __('title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('color', __('color'), ['class' => 'form-label']) }}
            {!! Form::input('color', 'color', null, ['class' => 'form-control','style' => 'height: 50px;']) !!}
        </div>
    </div>
</div>
<div class="modal-footer">

    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
