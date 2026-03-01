@extends('layouts.app')
@php
    $profile = asset(Storage::url('upload/profile/'));
@endphp
@section('page-title')
    {{ __('Client Details') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('client') }}">{{ __('Client') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Show') }}</li>
@endsection
@push('script-page')
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-lg-4 col-xxl-3">
                                <div class="card border">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img class="img-radius img-fluid wid-40"
                                                    src="{{ !empty($user->profile) ? $profile . '/' . $user->profile : $profile . '/avatar.png' }}"
                                                    alt="User image" />
                                            </div>
                                            <div class="flex-grow-1 mx-3">
                                                <h5 class="mb-1">{{ $user->name }}</h5>
                                                {{-- <h6 class="text-muted mb-0">{!! $user->SubscriptionLeftDay() !!}</h6> --}}
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="badge bg-primary rounded-pill text-base">
                                                    {{ $user->type }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body px-2 pb-0">
                                        <div class="list-group list-group-flush">
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="material-icons-two-tone f-20">email</i>
                                                    </div>
                                                    <div class="flex-grow-1 mx-3">
                                                        <h5 class="m-0">{{ __('Email') }}</h5>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <small>{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="material-icons-two-tone f-20">phonelink_ring</i>
                                                    </div>
                                                    <div class="flex-grow-1 mx-3">
                                                        <h5 class="m-0">{{ __('Phone') }}</h5>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <small>{{ $user->phone_number }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="material-icons-two-tone f-20">pin_drop</i>
                                                    </div>
                                                    <div class="flex-grow-1 mx-3">
                                                        <h5 class="m-0">{{ __('Address') }}</h5>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <small>{{ $user->address }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8 col-xxl-9">
                                <div class="card border">
                                    <div class="card-header">
                                        <h5>{{ __('Assign Documrnt') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Assigned To') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Sub Category') }}</th>
                                    <th>{{ __('Tags') }}</th>
                                    <th>{{ __('Stage') }}</th>
                                    <th>{{ __('Created By') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    @if (Gate::check('show document'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $document)
                                    <tr role="row">
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
                                                <span class="d-inline badge text-bg-success" style="background-color: {{ optional($document->StageData)->color }} !important">{{ optional($document->StageData)->title }}</span>
                                            @endif
                                        </td>
                                        <td>{{ !empty($document->createdBy) ? $document->createdBy->name : '' }}</td>
                                        <td>{{ dateFormat($document->created_at) }}</td>
                                        @if ( Gate::check('show document'))
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['document.destroy', encrypt($document->id)]]) !!}
                                                    @if (Gate::check('show document'))
                                                        <a class="avtar avtar-xs btn-link-warning text-warning"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Show Details') }}"
                                                            href="{{ route('document.show', \Illuminate\Support\Facades\Crypt::encrypt($document->id)) }}">
                                                            <i data-feather="eye"></i></a>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
