<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentHistory;
use App\Models\Notification;
use App\Models\Reminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ReminderController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage reminder')) {

            if ($request->ajax()) {
                $document = $request->document ?? '';
                $dateRange = $request->date_range;
                $startDate = '';
                $endDate = '';

                if (!empty($dateRange)) {
                    [$start, $end] = explode(' - ', $dateRange);
                    $startDate = Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d');
                    $endDate = Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d');
                }

                $reminderQuery = Reminder::where(function ($q) {
                    $q->where('parent_id', parentId())
                        ->orWhereRaw('find_in_set(?, assign_user)', [\Auth::id()]);
                });

                if (!empty($request->document)) {
                    $reminderQuery->where('document_id', $request->document);
                }
                if (!empty($request->stages)) {
                    $reminderQuery->where('stage_id', $request->stages);
                }

                if ($startDate && $endDate) {
                    $reminderQuery->whereBetween('date', [$startDate, $endDate]);
                }

                $count = $reminderQuery->count();
                $reminders = $reminderQuery->orderBy('id', 'desc')->get();

                $html = view('reminder.reminder_index', compact('reminders', 'count'))->render();
                return response()->json(['html' => $html, 'count' => $count]);
            }
            $documents = Document::where('parent_id', parentId())
                ->get()
                ->mapWithKeys(function ($document) {
                    return [
                        $document->id => documentPrefix() . $document->document_id . '-' . $document->name
                    ];
                })
                ->prepend(__('Select document'), '');
            return view('reminder.index', compact('documents'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        $documents = Document::where('parent_id', parentId())->get()->pluck('name', 'id');
        $documents->prepend(__('Select Document'), '');
        $users = User::where('parent_id', parentId())->get()->where('type', '!=', 'client')->pluck('name', 'id');
        return view('reminder.create', compact('users', 'documents'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create reminder')) {
            // Validate the request
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'time' => 'required',
                    'subject' => 'required',
                    'message' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Create Reminder
            $reminder = new Reminder();
            $reminder->reminder = $this->reminderNumber();
            $reminder->document_id = $request->document_id ?? 0;
            $reminder->date = $request->date;
            $reminder->time = $request->time;
            $reminder->subject = $request->subject;
            $reminder->message = $request->message;
            $reminder->assign_user = !empty($request->assign_user) ? implode(',', $request->assign_user) : '';
            $reminder->created_by = \Auth::user()->id;
            $reminder->parent_id = parentId();
            $reminder->save();

            // Log document history
            $document = Document::find($request->document_id ?? 0);
            $data['document_id'] = $document->id ?? 0;
            $data['action'] = __('Create reminder');
            $data['description'] = __('Create reminder for') . ' ' . ($document->name ?? '') . ' ' . __('created by') . ' ' . \Auth::user()->name;
            DocumentHistory::history($data);

            // Handle Notifications and Emails
            $settings = settings();
            $userIds = explode(',', $reminder->assign_user);
            $users = User::whereIn('id', $userIds)->get();


            triggerN8n('create_reminder', [
                'reminder_id' => $reminder->id,
                'document_id' => $document->id ?? null,
                'document_name' => $document->name ?? null,

                'date' => $reminder->date,
                'time' => $reminder->time,
                'subject' => $reminder->subject,
                'message' => $reminder->message,

                'assigned_user_ids' => $reminder->assign_user,
                'assigned_user_emails' => implode(',', $users->pluck('email')->toArray()),

                'created_by' => \Auth::user()->id,
                'created_by_name' => \Auth::user()->name,

                'document_email' => $document->email ?? null,
                'document_phone' => $document->phone_number ?? null,

                'company_name' => $settings['company_name'] ?? null,
                'company_email' => $settings['company_email'] ?? null,
                'company_phone' => $settings['company_phone'] ?? null,

                'created_at' => now()->toDateTimeString(),
            ]);

            $module = 'reminder_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();

            $errorMessage = '';
            if (!empty($notification) && $notification->enabled_email == 1) {
                $notification_responce = MessageReplace($notification, $reminder->id);
                $to = User::whereIn('id', $userIds)->pluck('email')->toArray();
                $phone = User::whereIn('id', $userIds)->pluck('phone_number')->toArray();


                if (!empty($to)) {
                    $datas = [
                        'user' => $users,
                        'subject' => $notification_responce['subject'],
                        'message' => $notification_responce['message'],
                        'module' => $module,
                        'logo' => $settings['company_logo'],
                    ];

                    if ($notification->enabled_email == 1) {
                        $response = commonEmailSend($to, $data);
                        if ($response['status'] == 'error') {
                            $errorMessage = $response['message'];
                        }
                    }
                    if ($notification->enabled_sms == 1) {
                        $twilio_sid = getSettingsValByName('twilio_sid');
                        if (!empty($twilio_sid)) {
                            send_twilio_msg($phone, $notification_responce['sms_message']);
                        }
                    }
                }
            }



            return redirect()->back()->with('success', __('Reminder successfully created!') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show($id)
    {
        if (\Auth::user()->can('show reminder')) {
            $id = decrypt($id);
            $reminder = Reminder::find($id);
            return view('reminder.show', compact('reminder'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit($id)
    {
        $documents = Document::where('parent_id', parentId())->get()->pluck('name', 'id');
        $documents->prepend(__('Select Document'), '');
        $users = User::where('parent_id', parentId())->get()->where('type', '!=', 'client')->pluck('name', 'id');

        $id = decrypt($id);
        $reminder = Reminder::find($id);
        return view('reminder.edit', compact('users', 'documents', 'reminder'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit reminder')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'time' => 'required',
                    'subject' => 'required',
                    'message' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $id = decrypt($id);
            $reminder = Reminder::find($id);
            $reminder->document_id = $request->document_id;
            $reminder->date = $request->date;
            $reminder->time = $request->time;
            $reminder->subject = $request->subject;
            $reminder->message = $request->message;
            $reminder->assign_user = !empty($request->assign_user) ? implode(',', $request->assign_user) : '';
            $reminder->save();

            $document = Document::find($request->document_id);
            $data['document_id'] = !empty($document) ? $document->id : 0;
            $data['action'] = __('Create reminder');
            $data['description'] = __('Update reminder for') . ' ' . !empty($document) ? $document->name : '' . ' ' . __('updated by') . ' ' . \Auth::user()->name;
            $data['document_id'] = $document->id;
            DocumentHistory::history($data);

            return redirect()->back()->with('success', __('Reminder successfully updated!'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete reminder')) {
            $id = decrypt($id);
            $reminder = Reminder::find($id);
            $document = Document::find($reminder->document_id);

            $reminder->delete();

            $data['document_id'] = !empty($document) ? $document->id : 0;
            $data['action'] = __('Delete reminder');
            $data['description'] = __('Delete reminder for') . ' ' . !empty($document) ? $document->name : '' . ' ' . __('deleted by') . ' ' . \Auth::user()->name;
            $data['document_id'] = $document->id;
            DocumentHistory::history($data);
            return redirect()->back()->with('success', 'Reminder successfully deleted!');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function myReminder(Request $request)
    {
        if (\Auth::user()->can('manage my reminder')) {
            if ($request->ajax()) {
                $document = $request->document ?? '';
                $dateRange = $request->date_range;
                $startDate = '';
                $endDate = '';

                if (!empty($dateRange)) {
                    [$start, $end] = explode(' - ', $dateRange);
                    $startDate = Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d');
                    $endDate = Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d');
                }

                $reminderQuery = Reminder::where(function ($q) {
                    $q->where('parent_id', parentId())
                        ->orWhereRaw('find_in_set(?, assign_user)', [\Auth::id()]);
                });

                if (!empty($request->document)) {
                    $reminderQuery->where('document_id', $request->document);
                }
                if (!empty($request->stages)) {
                    $reminderQuery->where('stage_id', $request->stages);
                }

                if ($startDate && $endDate) {
                    $reminderQuery->whereBetween('date', [$startDate, $endDate]);
                }

                $count = $reminderQuery->count();
                $reminders = $reminderQuery->orderBy('id', 'desc')->get();

                $html = view('reminder.own_index', compact('reminders', 'count'))->render();
                return response()->json(['html' => $html, 'count' => $count]);
            }
            $documents = Document::where('parent_id', parentId())
                ->get()
                ->mapWithKeys(function ($document) {
                    return [
                        $document->id => documentPrefix() . $document->document_id . '-' . $document->name
                    ];
                })
                ->prepend(__('Select document'), '');
            return view('reminder.own', compact('documents'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function reminderNumber()
    {
        $latestReminder = Reminder::where('parent_id', parentId())->latest()->first();
        if ($latestReminder == null) {
            return 1;
        } else {
            return $latestReminder->reminder_id + 1;
        }
    }
}
