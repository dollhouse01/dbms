@extends('layouts.app')

@section('page-title')
    {{ __('My Reminder') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __(' My Reminder') }}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2 flex-wrap">
                        <div class="col-12 col-md">
                            <h5>{{ __('Reminder') }}</h5>
                        </div>
                        <div class="col-12 col-md-auto">
                            {{ Form::open(['method' => 'get', 'route' => 'reminder.index', 'id' => 'reminder-my-table', 'class' => 'attendance-date']) }}
                            <div class="d-flex flex-wrap gap-2 align-items-end">
                                <div class="d-flex flex-column" style="width: 200px">
                                    <b>{{ Form::label('documment', __('Document'), ['class' => 'form-label']) }}</b>
                                    {{ Form::select('document', $documents, null, ['class' => 'form-control select2']) }}
                                </div>

                                <div>
                                    <b>{{ Form::label('date_range', __('Date'), ['class' => 'form-label']) }}</b>
                                    {{ Form::text('date_range',null, ['class' => 'form-control', 'placeholder' => __('Select Date Range')]) }}
                                </div>

                                <div>
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="ti ti-search align-text-bottom"></i> </button>
                                    <button type="button" class="btn btn-secondary btn-rounded"
                                        onclick="window.location.reload();">
                                        <i class="ti ti-refresh align-text-bottom"></i>
                                    </button>
                                </div>
                                @if (Gate::check('create reminder'))
                                    <div>
                                        <a class="btn btn-secondary customModal" href="#!" data-size="lg"
                                            data-url="{{ route('reminder.create') }}"
                                            data-title="{{ __('Create Reminder') }}">
                                            <i class="ti ti-circle-plus align-text-bottom"></i>
                                            {{ __('Create Reminder') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0" id="reminder-my-report">

                </div>
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $('#date_range').daterangepicker({
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, function(start, end) {
            var start_date = start.format('MM/DD/YYYY');
            var end_date = end.format('MM/DD/YYYY');
            $('#date_range').val(start_date + ' - ' + end_date);
        });
        $(document).ready(function() {
            $('#reminder-my-table').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route('reminder.index') }}',
                    method: 'GET',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#reminder-my-report').html(response.html);
                        datatable();
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while fetching the report.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                    }
                });
            });

            $('#reminder-my-table').trigger('submit');
        });
    </script>
@endpush
