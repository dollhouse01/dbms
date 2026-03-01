<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Id') }}</th>
                <th>{{ __('Document') }}</th>
                <th>{{ __('Action') }}</th>
                <th>{{ __('Action Time') }}</th>
                <th>{{ __('Action User') }}</th>
                <th>{{ __('Description') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($histories as $history)
                <tr role="row">
                    <td>{{ documentPrefix() . $history->documents->document_id }}</td>.
                    <td> {{ !empty($history->documents) ? $history->documents->name : '-' }} </td>
                    <td> {{ ucfirst($history->action) }} </td>
                    <td>{{ dateFormat($history->created_at) }} {{ timeFormat($history->created_at) }}
                    </td>
                    <td> {{ !empty($history->actionUser) ? $history->actionUser->name : '-' }} </td>
                    <td> {{ $history->description }} </td>
                </tr>
            @endforeach

        </tbody>
    </table>
</div>
