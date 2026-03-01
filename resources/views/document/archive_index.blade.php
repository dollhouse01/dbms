<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Id') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Assigned To') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Sub Category') }}</th>
                <th>{{ __('Tags') }}</th>
                <th>{{ __('Stage') }}</th>
                <th>{{ __('Created By') }}</th>
                <th>{{ __('Created At') }}</th>
                @if (Gate::check('delete document') || Gate::check('archive document'))
                    <th class="text-right">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $document)
                <tr role="row">
                    <td>{{ documentPrefix() . $document->document_id }}</td>

                    <td>{{ $document->name }}</td>
                    <td>{{ optional($document->AssignTo)->name }}</td>
                    <td>{{ !empty($document->category) ? $document->category->title : '-' }}</td>
                    <td>{{ !empty($document->subCategory) ? $document->subCategory->title : '-' }}</td>
                    <td>
                        @foreach ($document->tags() as $tag)
                            {{ $tag->title }} <br>
                        @endforeach
                    </td>
                    <td>
                        @if (!empty($document->StageData))
                            <span class="d-inline badge text-bg-success"
                                style="background-color: {{ optional($document->StageData)->color }} !important">{{ optional($document->StageData)->title }}</span>
                        @endif
                    </td>
                    <td>{{ !empty($document->createdBy) ? $document->createdBy->name : '' }}</td>
                    <td>{{ dateFormat($document->created_at) }}</td>
                    @if (Gate::check('delete document') || Gate::check('archive document'))
                        <td class="text-right">
                            <div class="cart-action">
                                {!! Form::open(['method' => 'get', 'route' => ['unarchive', encrypt($document->id)], 'class' => 'd-inline']) !!}
                                @if (Gate::check('archive document'))
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                        data-dialog-title = "{{ __('Are you sure you want to unarchive this record ?') }}"
                                        data-dialog-text = "{{ __('Do you want to proceed?') }}"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('unarchived') }}"
                                        href="#!"> <i class="fas fa-archive" style="font-size: 20px"></i></a>
                                @endif
                                {!! Form::close() !!}
                                {!! Form::open(['method' => 'DELETE', 'route' => ['document.destroy', $document->id], 'class' => 'd-inline']) !!}
                                @if (Gate::check('delete document'))
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Detete') }}"
                                        href="#"> <i data-feather="trash-2"></i></a>
                                @endif
                                {!! Form::close() !!}
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach

        </tbody>
    </table>
</div>
