<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th>{{ __('Id') }}</th>
                <td>{{ __('Docuement') }}</td>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Time') }}</th>
                <th>{{ __('Subject') }}</th>
                <th>{{ __('Created By') }}</th>
                <th>{{ __('Assigned') }}</th>
                <th class="text-right">{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reminders as $reminder)
                <tr role="row">
                    <td>{{ reminderPrefix() . $reminder->reminder_id }}</td>
                    <td>{{ !empty($reminder->document) ? $reminder->document->name : '-' }}</td>
                    <td>{{ dateFormat($reminder->date) }}</td>
                    <td>{{ timeFormat($reminder->time) }}</td>
                    <td> {{ $reminder->subject }} </td>
                    <td> {{ !empty($reminder->createdBy) ? $reminder->createdBy->name : '-' }}
                    </td>

                    <td>
                        @foreach ($reminder->users() as $user)
                            @if ($user)
                                {{-- Check if user is not null --}}
                                {{ $user->name }} <br>
                            @endif
                        @endforeach
                    </td>


                    @if (Gate::check('edit reminder') || Gate::check('delete reminder') || Gate::check('show reminder'))
                        <td class="text-right">
                            <div class="cart-action">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['reminder.destroy', encrypt($reminder->id)]]) !!}
                                @if (Gate::check('show reminder'))
                                    <a class="avtar avtar-xs btn-link-warning text-warning customModal" data-size="lg"
                                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Show') }}"
                                        href="#" data-url="{{ route('reminder.show', encrypt($reminder->id)) }}"
                                        data-title="{{ __('Details') }}"> <i data-feather="eye"></i></a>
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
