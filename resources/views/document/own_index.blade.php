<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Id') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Sub Category') }}</th>
                <th>{{ __('Tags') }}</th>
                <th>{{ __('Created By') }}</th>
                <th>{{ __('Created At') }}</th>
                <th>{{ __('Expired At') }}</th>
                @if (Gate::check('edit my document') || Gate::check('delete my document') || Gate::check('show my document'))
                    <th class="text-right">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $document)
                <tr role="row">
                    <td>{{ documentPrefix() . $document->document_id }}</td>
                    <td>{{ $document->name }}</td>
                    <td>
                        {{ !empty($document->category) ? $document->category->title : '-' }}
                    </td>
                    <td>
                        {{ !empty($document->subCategory) ? $document->subCategory->title : '-' }}
                    </td>
                    <td>
                        @foreach ($document->tags() as $tag)
                            {{ !empty($tag) ? $tag->title : '-' }} <br>
                        @endforeach
                    </td>
                    <td>{{ !empty($document->createdBy) ? $document->createdBy->name : '' }}</td>
                    <td>{{ dateFormat($document->created_at) }}</td>
                    <td>{{ dateFormat($document->created_at) }}</td>
                    @if (Gate::check('edit my document') || Gate::check('delete my document') || Gate::check('show my document'))
                        <td class="text-right">
                            <div class="cart-action">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['document.destroy', encrypt($document->id)]]) !!}
                                @if (Gate::check('show my document'))
                                    <a class="avtar avtar-xs btn-link-warning text-warning" data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Show Details') }}"
                                        href="{{ route('document.show', \Illuminate\Support\Facades\Crypt::encrypt($document->id)) }}">
                                        <i data-feather="eye"></i></a>
                                @endif
                                @if (Gate::check('edit my document'))
                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"
                                        href="#" data-url="{{ route('document.edit', encrypt($document->id)) }}"
                                        data-title="{{ __('Edit Support') }}"> <i data-feather="edit"></i></a>
                                @endif
                                @if (Gate::check('delete my document'))
                                    <a class=" avtar avtar-xs btn-link-danger text-danger confirm_dialog"
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
