<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\DocumentHistory;
use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\Reminder;
use App\Models\shareDocument;
use App\Models\Stage;
use App\Models\SubCategory;
use App\Models\Subscription;
use App\Models\Tag;
use App\Models\User;
use App\Models\VersionHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Mail;

class DocumentController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage document')) {
            if ($request->ajax()) {
                $category = $request->category ?? '';
                $stage = $request->stage ?? '';
                $dateRange = $request->date_range;
                $startDate = '';
                $endDate = '';

                if (!empty($dateRange)) {
                    [$start, $end] = explode(' - ', $dateRange);
                    $startDate = Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d');
                    $endDate = Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d');
                }
                $documents_query = Document::where('parent_id', '=', parentId())->where('archive', 0);
                $documents = $documents_query->OrderBy('id', 'desc')->get();


                if (!empty($request->category)) {
                    $documents_query->where('category_id', $request->category);
                }
                if (!empty($request->stages)) {
                    $documents_query->where('stage_id', $request->stages);
                }

                if ($startDate && $endDate) {
                    $documents_query->whereBetween('created_at', [$startDate, $endDate]);
                }

                $count = $documents_query->count();
                $documents = $documents_query->orderBy('id', 'desc')->get();

                $html = view('document.document_index', compact('documents', 'count'))->render();
                return response()->json(['html' => $html, 'count' => $count]);
            }

            $category = Category::where('parent_id', parentId())->get()->pluck('title', 'id')->prepend(__('Select Category'), '');
            $category->prepend(__('Select Category'), '');

            $stages = Stage::where('parent_id', parentId())->get()->pluck('title', 'id')->prepend(__('Select Stage'), '');
            $stages->prepend(__('Select Stage'), '');

            return view('document.index', compact('category', 'stages'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        $category = Category::where('parent_id', parentId())->get()->pluck('title', 'id');
        $category->prepend(__('Select Category'), '');
        $tages = Tag::where('parent_id', parentId())->get()->pluck('title', 'id');
        $stage_id = Stage::where('parent_id', parentId())->get()->pluck('title', 'id');
        $client = User::where('parent_id', parentId())->where('type', 'client')->get()->mapWithKeys(function ($user) {
            return [$user->id => $user->first_name . ' ' . $user->last_name];
        });
        return view('document.create', compact('category', 'tages', 'client', 'stage_id'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create document') || \Auth::user()->can('create my document')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'category_id' => 'required',
                    'sub_category_id' => 'required',
                    'document' => 'required',
                    'stage_id' => 'required',
                    'assign_to' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $ids = parentId();
            $authUser = User::find($ids);
            $totalDocument = $authUser->totalDocument();
            $subscription = Subscription::find($authUser->subscription);
            if ($totalDocument >= $subscription->total_document && $subscription->total_document != 0) {
                return redirect()->back()->with('error', __('Your document limit is over, please upgrade your subscription.'));
            }

            $document = new Document();
            $document->name = $request->name;
            $document->stage_id = $request->stage_id;
            $document->assign_to = $request->assign_to;
            $document->category_id = $request->category_id;
            $document->sub_category_id = $request->sub_category_id;
            $document->description = $request->description;
            $document->document_id = $this->documentNumber();
            $document->tages = !empty($request->tages) ? implode(',', $request->tages) : '';
            $document->created_by = \Auth::user()->id;
            $document->parent_id = parentId();
            $document->save();

            if ($request->hasFile('document')) {
                $uploadResult = handleFileUpload($request->file('document'), 'upload/document');

                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $version = new VersionHistory();
                $version->document = $uploadResult['filename'];
                $version->current_version = 1;
                $version->document_id = $document->id;
                $version->created_by = \Auth::user()->id;
                $version->parent_id = parentId();
                $version->save();
            }

            $data['document_id'] = $document->id;
            $data['action'] = __('Document Create');
            $data['description'] = __('New document') . ' ' . $document->name . ' ' . __('created by') . ' ' . \Auth::user()->name;
            DocumentHistory::history($data);

            return redirect()->back()->with('success', __('Document successfully created!'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show($cid)
    {
        $id = Crypt::decrypt($cid);
        $document = Document::find($id);
        $latestVersion = VersionHistory::where('document_id', $id)->where('current_version', 1)->first();
        return view('document.show', compact('document', 'latestVersion'));
    }


    public function edit($id)
    {
        $id = decrypt($id);
        $document = Document::find($id);

        $category = Category::where('parent_id', parentId())->get()->pluck('title', 'id');
        $category->prepend(__('Select Category'), '');
        $tages = Tag::where('parent_id', parentId())->get()->pluck('title', 'id');

        $stage_id = Stage::where('parent_id', parentId())->get()->pluck('title', 'id');
        $client = User::where('parent_id', parentId())->where('type', 'client')->get()->mapWithKeys(function ($user) {
            return [$user->id => $user->first_name . ' ' . $user->last_name];
        });
        return view('document.edit', compact('document', 'category', 'tages', 'stage_id', 'client'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit document') || \Auth::user()->can('create my document')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'category_id' => 'required',
                    'sub_category_id' => 'required',
                    'stage_id' => 'required',
                    'assign_to' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $id = decrypt($id);
            $document = Document::find($id);
            $document->name = $request->name;
            $document->stage_id = $request->stage_id;
            $document->assign_to = $request->assign_to;
            $document->category_id = $request->category_id;
            $document->sub_category_id = $request->sub_category_id;
            $document->description = $request->description;
            $document->tages = !empty($request->tages) ? implode(',', $request->tages) : '';
            $document->save();

            $data['document_id'] = $document->id;
            $data['action'] = __('Document Update');
            $data['description'] = __('Document update') . ' ' . $document->name . ' ' . __('updated by') . ' ' . \Auth::user()->name;
            DocumentHistory::history($data);

            return redirect()->back()->with('success', __('Document successfully created!'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete document')) {
            $id = decrypt($id);
            $document = Document::find($id);
            $document->delete();
            $data['document_id'] = $document->id;
            $data['action'] = __('Document Delete');
            $data['description'] = __('Document delete') . ' ' . $document->name . ' ' . __('deleted by') . ' ' . \Auth::user()->name;
            DocumentHistory::history($data);

            $versions = VersionHistory::where('document_id', $document->id)->get();
            if (!empty($versions)) {
                foreach ($versions as $key => $value) {
                    if (!empty($value->document)) {
                        deleteOldFile($value->document, 'upload/document/');
                    }
                }
            }
            VersionHistory::where('document_id', $document->id)->delete();

            return redirect()->back()->with('success', 'Document successfully deleted!');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function myDocument(Request $request)
    {
        if (!\Auth::user()->can('manage my document')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        if ($request->ajax()) {

            $userId = \Auth::id();

            $startDate = null;
            $endDate = null;

            if (!empty($request->date_range)) {
                try {
                    [$start, $end] = explode(' - ', $request->date_range);

                    $startDate = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                    $endDate = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();
                } catch (\Exception $e) {
                    $startDate = null;
                    $endDate = null;
                }
            }

            $assignedDocumentIds = ShareDocument::where('user_id', $userId)
                ->pluck('document_id')
                ->toArray();

            $documentsQuery = Document::query()
                ->where('archive', 0)
                ->where(function ($q) use ($userId, $assignedDocumentIds) {
                    $q->where('created_by', $userId);

                    if (!empty($assignedDocumentIds)) {
                        $q->orWhereIn('id', $assignedDocumentIds);
                    }
                });

            if (!empty($request->category)) {
                $documentsQuery->where('category_id', $request->category);
            }

            if (!empty($request->stage)) {
                $documentsQuery->where('stage_id', $request->stage);
            }

            if ($startDate && $endDate) {
                $documentsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $count = $documentsQuery->count();

            $documents = $documentsQuery
                ->orderByDesc('id')
                ->get();

            $html = view('document.own_index', compact('documents', 'count'))->render();

            return response()->json([
                'html' => $html,
                'count' => $count,
            ]);
        }

        $category = Category::where('parent_id', parentId())
            ->pluck('title', 'id')
            ->prepend(__('Select Category'), '');

        $stages = Stage::where('parent_id', parentId())
            ->pluck('title', 'id')
            ->prepend(__('Select Stage'), '');

        return view('document.own', compact('category', 'stages'));
    }


    public function comment($ids)
    {
        if (\Auth::user()->can('manage comment')) {
            $id = Crypt::decrypt($ids);
            $document = Document::find($id);
            $comments = DocumentComment::where('document_id', $id)->OrderBy('id', 'desc')->get();
            return view('document.comment', compact('document', 'comments'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function commentData(Request $request, $ids)
    {
        if (!\Auth::user()->can('create comment')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make($request->all(), [
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->messages()->first());
        }

        $id = Crypt::decrypt($ids);
        $document = Document::findOrFail($id);

        // 🔹 Save Comment
        $comment = new DocumentComment();
        $comment->comment = $request->comment;
        $comment->user_id = \Auth::id();
        $comment->document_id = $document->id;
        $comment->parent_id = parentId();
        $comment->save();

        $commentUser = \Auth::user();

        $assignedUserIds = ShareDocument::where('document_id', $document->id)
            ->pluck('user_id')
            ->toArray();

        if (!in_array($document->parent_id, $assignedUserIds)) {
            $assignedUserIds[] = $document->parent_id;
        }

        $assignedUserIds = array_unique($assignedUserIds);

        $assignedUserIds = array_filter($assignedUserIds, function ($uid) use ($commentUser) {
            return $uid != $commentUser->id;
        });

        $assignedUsers = User::whereIn('id', $assignedUserIds)->get();
        $setting = settings();
        triggerN8n('new_doc_comment', [

            'document_id' => $document->id,
            'document_name' => $document->name,

            'comment_id' => $comment->id,
            'comment_by_id' => $commentUser->id,
            'comment_by_name' => $commentUser->name,
            'comment_by_email' => $commentUser->email,
            'comment_by_phone' => $commentUser->phone_number ?? null,

            'comment_text' => $comment->comment,

            'assigned_user_ids' => $assignedUsers->pluck('id')->implode(','),
            'assigned_user_names' => $assignedUsers->pluck('name')->implode(','),
            'assigned_user_emails' => $assignedUsers->pluck('email')->implode(','),

            'owner_id' => $document->created_by,
            'commented_at' => now()->toDateTimeString(),
        ]);

        $module = 'document_comment';
        $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
        if (!empty($notification) && $notification->enabled_email == 1) {
            $notification_responce = MessageReplace($notification, $comment->document_id);

            $users = $assignedUsers->pluck('name')->implode(',');
            $to = $assignedUsers->pluck('email')->filter()->toArray();
            $phones = $assignedUsers->pluck('phone_number')->filter()->toArray();

            if (!empty($to)) {
                $datas = [
                    'user' => $users,
                    'subject' => $notification_responce['subject'],
                    'message' => $notification_responce['message'],
                    'module' => $module,
                    'logo' => $setting['company_logo'],
                ];
                // Send emails to all recipients
                if ($notification->enabled_email == 1) {
                    $response = commonEmailSend($to, $datas);
                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }
                if ($notification->enabled_sms == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        send_twilio_msg($phones, $notification_responce['sms_message']);
                    }
                }

            }
        }

        $data['document_id'] = $document->id;
        $data['action'] = __('Comment Create');
        $data['description'] = __('Comment created for') . ' ' . $document->name . ' ' . __('by') . ' ' . $commentUser->name;
        DocumentHistory::history($data);

        return redirect()->back()->with('success', 'Document comment successfully created!');
    }

    public function reminder($ids)
    {
        if (\Auth::user()->can('manage reminder')) {
            $id = Crypt::decrypt($ids);
            $document = Document::find($id);
            $reminders = Reminder::where('document_id', $id)->OrderBy('id', 'desc')->get();
            $users = User::where('parent_id', parentId())->get()->pluck('name', 'id');
            return view('document.reminder', compact('document', 'reminders', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function addReminder($id)
    {
        if (\Auth::user()->can('create reminder')) {
            $document = Document::find($id);
            $reminders = Reminder::where('document_id', $id)->get();
            $users = User::where('parent_id', parentId())->get()->pluck('name', 'id');
            return view('document.add_reminder', compact('document', 'reminders', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function versionHistory($ids)
    {
        if (\Auth::user()->can('manage version')) {
            $id = Crypt::decrypt($ids);
            $document = Document::find($id);
            $versions = VersionHistory::where('document_id', $id)->OrderBy('id', 'desc')->get();

            return view('document.version_history', compact('document', 'versions'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function newVersion(Request $request, $ids)
    {
        if (!\Auth::user()->can('create version')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make($request->all(), [
            'document' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->messages()->first());
        }

        $id = Crypt::decrypt($ids);

        VersionHistory::where('document_id', $id)->update(['current_version' => 0]);

        if ($request->hasFile('document')) {

            $uploadResult = handleFileUpload($request->file('document'), 'upload/document');

            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }

            $version = new VersionHistory();
            $version->document = $uploadResult['filename'];
            $version->current_version = 1;
            $version->document_id = $id;
            $version->created_by = \Auth::id();
            $version->parent_id = parentId();
            $version->save();
        }

        $document = Document::findOrFail($id);
        $uploadedBy = \Auth::user();

        $assignedUserIds = ShareDocument::where('document_id', $document->id)
            ->pluck('user_id')
            ->toArray();

        if (!in_array($document->parent_id, $assignedUserIds)) {
            $assignedUserIds[] = $document->parent_id;
        }

        $assignedUserIds = array_unique($assignedUserIds);

        $assignedUserIds = array_filter($assignedUserIds, function ($uid) use ($uploadedBy) {
            return $uid != $uploadedBy->id;
        });

        $assignedUsers = User::whereIn('id', $assignedUserIds)->get();

        triggerN8n('document_version_update', [

            'document_id' => $document->id,
            'document_name' => $document->name,

            'version_id' => isset($version) ? $version->id : null,

            'uploaded_by_id' => $uploadedBy->id,
            'uploaded_by_name' => $uploadedBy->name,
            'uploaded_by_email' => $uploadedBy->email,
            'uploaded_by_phone' => $uploadedBy->phone_number ?? null,

            'assigned_user_ids' => $assignedUsers->pluck('id')->implode(','),
            'assigned_user_names' => $assignedUsers->pluck('name')->implode(','),
            'assigned_user_emails' => $assignedUsers->pluck('email')->implode(','),

            'owner_id' => $document->created_by,
            'updated_at' => now()->toDateTimeString(),
        ]);

        $module = 'document_version_update';

        $notification = Notification::where('parent_id', parentId())
            ->where('module', $module)
            ->first();

        if (!empty($notification)) {

            $notification_responce = MessageReplace($notification, $document->id);

            $emails = $assignedUsers->pluck('email')->filter()->toArray();
            $phones = $assignedUsers->pluck('phone_number')->filter()->toArray();

            $setting = settings();

            if (!empty($emails) && $notification->enabled_email == 1) {

                $datas = [
                    'user' => implode(',', $emails),
                    'subject' => $notification_responce['subject'],
                    'message' => $notification_responce['message'],
                    'module' => $module,
                    'logo' => $setting['company_logo'],
                ];

                commonEmailSend($emails, $datas);
            }

            if (!empty($phones) && $notification->enabled_sms == 1) {

                $twilio_sid = getSettingsValByName('twilio_sid');

                if (!empty($twilio_sid)) {
                    send_twilio_msg($phones, $notification_responce['sms_message']);
                }
            }
        }

        $data['document_id'] = $document->id;
        $data['action'] = __('New version');
        $data['description'] = __('Upload new version for') . ' ' . $document->name . ' ' . __('uploaded by') . ' ' . $uploadedBy->name;

        DocumentHistory::history($data);

        return redirect()->back()->with('success', __('New version successfully uploaded!'));
    }

    public function shareDocument($ids)
    {
        if (\Auth::user()->can('manage share document')) {
            $id = Crypt::decrypt($ids);
            $document = Document::find($id);
            $shareDocuments = shareDocument::where('document_id', $id)->OrderBy('id', 'desc')->get();
            $users = User::where('parent_id', parentId())->get()->pluck('name', 'id');
            return view('document.share', compact('document', 'shareDocuments', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function addshareDocumentData($id)
    {
        if (\Auth::user()->can('create share document')) {
            $id = decrypt($id);
            $document = Document::find($id);
            $shareDocuments = shareDocument::where('document_id', $id)->get();
            $users = User::where('parent_id', parentId())->get()->pluck('name', 'id');
            return view('document.add_share', compact('document', 'shareDocuments', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function shareDocumentData(Request $request, $ids)
    {
        if (\Auth::user()->can('create share document')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'assign_user' => 'required',
                ]
            );
            if (isset($request->time_duration)) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'start_date' => 'required',
                        'end_date' => 'required',
                    ]
                );
            }
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            foreach ($request->assign_user as $user) {
                $share = new shareDocument();
                $share->user_id = $user;
                $share->document_id = $request->document_id;
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $share->start_date = $request->start_date;
                    $share->end_date = $request->end_date;
                }
                $share->parent_id = parentId();
                $share->save();
            }
            $id = Crypt::decrypt($ids);
            $document = Document::find($id);
            $data['document_id'] = $id;
            $data['action'] = __('Share document');
            $data['description'] = __('Share document') . ' ' . $document->name . ' ' . __('shared by') . ' ' . \Auth::user()->name;
            DocumentHistory::history($data);
            $users = User::whereIn('id', $request->assign_user)->get();
            $setting = settings();

            triggerN8n('share_document', [
                'document_id' => $document->id ?? null,
                'document_name' => $document->name ?? null,

                'shared_by_id' => \Auth::user()->id,
                'shared_by_name' => \Auth::user()->name,
                'shared_by_email' => \Auth::user()->email,
                'shared_by_phone' => \Auth::user()->phone_number,

                'assigned_user_ids' => implode(',', $request->assign_user),
                'assigned_user_names' => implode(',', $users->pluck('name')->toArray()),
                'assigned_user_emails' => implode(',', $users->pluck('email')->toArray()),

                'start_date' => $request->start_date ?? null,
                'end_date' => $request->end_date ?? null,

                'company_name' => $setting['company_name'] ?? null,
                'company_email' => $setting['company_email'] ?? null,
                'company_phone' => $setting['company_phone'] ?? null,

                'shared_at' => now()->toDateTimeString(),
            ]);


            // Handle notifications and emails
            $module = 'document_share';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $errorMessage = '';
            if (!empty($notification) && $notification->enabled_email == 1) {
                $notification_responce = MessageReplace($notification, $request->document_id);

                // Fetch users and their emails
                $users = User::whereIn('id', $request->assign_user)->get();
                $to = $users->pluck('email')->toArray();
                $phone = $users->pluck('phone_number')->toArray();

                if (!empty($to)) {
                    $datas = [
                        'user' => $users,
                        'subject' => $notification_responce['subject'],
                        'message' => $notification_responce['message'],
                        'module' => $module,
                        'logo' => $setting['company_logo'],
                    ];
                    // Send emails to all recipients
                    if ($notification->enabled_email == 1) {
                        $response = commonEmailSend($to, $datas);
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

            return redirect()->back()->with('success', __('Document successfully assigned!') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function shareDocumentDelete($id)
    {
        if (\Auth::user()->can('delete share document')) {
            $id = decrypt($id);
            $shareDoc = shareDocument::find($id);
            $document = Document::find($shareDoc->document_id);
            $shareDoc->delete();

            $data['document_id'] = $id;
            $data['action'] = __('Share document delete');
            $data['description'] = __('Share document') . ' ' . $document->name . ' ' . __('delete,deleted by') . ' ' . \Auth::user()->name;
            DocumentHistory::history($data);

            return redirect()->back()->with('success', 'Assigned document successfully removed!');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function sendEmail($ids)
    {
        if (\Auth::user()->can('manage mail')) {
            $id = Crypt::decrypt($ids);
            $document = Document::find($id);

            return view('document.send_email', compact('document'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function sendEmailData(Request $request, $ids)
    {
        if (\Auth::user()->can('send mail')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'email' => 'required',
                    'subject' => 'required',
                    'message' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Handle notifications and emails

            $to = $request->email;
            $errorMessage = '';
            if (!empty($to)) {
                $datas = [
                    'subject' => $request->subject,
                    'message' => $request->message,
                    'module' => 'send_email',
                    'logo' => settings()['company_logo'],
                ];

                // Send emails to all recipients
                $response = commonEmailSend($to, $datas);
                if ($response['status'] == 'error') {
                    $errorMessage = $response['message'];
                }
            }

            $id = Crypt::decrypt($ids);
            $document = Document::find($id);
            $data['document_id'] = $id;
            $data['action'] = __('Mail send');
            $data['description'] = __('Mail send for') . ' ' . $document->name . ' ' . __('sended by') . ' ' . \Auth::user()->name;
            DocumentHistory::history($data);

            return redirect()->back()->with('success', __('Mail successfully sent!') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function history(Request $request)
    {
        $ids = parentId();
        $authUser = User::find($ids);
        $subscription = Subscription::find($authUser->subscription);

        if (\Auth::user()->can('manage document history') && $subscription->enabled_document_history == 1) {
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
                $documents_query = DocumentHistory::where('parent_id', parentId())
                    ->whereBetween('created_at', [
                        now()->subDays(7)->startOfDay(),
                        now()->endOfDay()
                    ]);
                $documents = $documents_query->OrderBy('id', 'desc')->get();


                if (!empty($request->category)) {
                    $documents_query->where('category_id', $request->category);
                }
                if (!empty($request->documents)) {
                    $documents_query->where('document_id', $request->documents);
                }

                if ($startDate && $endDate) {
                    $documents_query->whereBetween('created_at', [$startDate, $endDate]);
                }

                $count = $documents_query->count();
                $histories = $documents_query->orderBy('id', 'desc')->get();

                $html = view('document.history_index', compact('histories', 'count'))->render();
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

            return view('document.history', compact('documents'));

        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }




    public function Sharelink($id)
    {
        if (\Auth::user()->can('share documents')) {
            return view('document.Sharelink', compact('id'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function GenerateSharelink(Request $request)
    {
        $id = $request->did;
        $data['exp_date'] = $request->exp_date;
        $data['password'] = $request->password;
        $data['today'] = date('Y-m-d');
        if (\Auth::user()->can('share documents')) {
            $return['url'] = route('document.view', $id) . '?data=' . encrypt($data);
            return response()->json($return);
        } else {
            $return['url'] = __('Something went wrong! Please try again later.');
            return response()->json($return);
        }
    }

    public function view(Request $request, $id)
    {

        try {
            $id = decrypt($id);
            $data = decrypt($request->data);

            $exp_date = $data['exp_date'] ?? null;
            $password = $data['password'] ?? null;
            $today = $data['today'] ?? Carbon::now()->toDateString();

            $Document = Document::find($id);
            if (!$Document) {
                return abort(404, 'Document not found.');
            }
            // Check if expired
            if (!empty($exp_date) && $today > $exp_date) {
                return view('document.expired', compact('Document', 'today'));
            }

            // If password is required and not passed via form, show password form
            if (!empty($password)) {
                if (!$request->has('verified') || $request->input('verified') != 'true') {
                    return view('document.password', compact('id', 'exp_date', 'password', 'today', 'Document'));
                }
            }
            return view('document.view', compact('id', 'exp_date', 'password', 'today', 'Document'));
        } catch (\Exception $e) {
            return abort(403, 'Invalid or expired document access link.');
        }
    }


    public function validatePassword(Request $request, $id)
    {
        $id = decrypt($id);
        $data = decrypt($request->data);
        $password = $data['password'] ?? null;

        if ($request->input('password') === $password) {
            return redirect()->route('document.view', [
                'id' => encrypt($id),
                'data' => $request->data,
                'verified' => 'true'
            ]);
        }

        return back()->withErrors(['password' => 'Incorrect password']);
    }

    public function archive(Request $request, $id)
    {
        if (\Auth::user()->can('archive document')) {
            $id = decrypt($id);
            $document = Document::find($id);
            if (!empty($document)) {
                $document->update(['archive' => 1]);

                $data['document_id'] = $id;
                $data['action'] = __('Document Archive');
                $data['description'] = __(':document was archived by :user', [
                    'document' => $document->name,
                    'user' => \Auth::user()->name,
                ]);
                DocumentHistory::history($data);
                return redirect()->back()->with('success', __('Document successfully archived!'));
            } else {
                return redirect()->back()->with('error', __('Document not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    public function unarchive(Request $request, $id)
    {
        if (\Auth::user()->can('archive document')) {
            $id = decrypt($id);
            $document = Document::find($id);
            if (!empty($document)) {
                $document->update(['archive' => 0]);
                $data['document_id'] = $document->id;
                $data['action'] = __('Document Unarchive');
                $data['description'] = __(':document was unarchived by :user', [
                    'document' => $document->name,
                    'user' => \Auth::user()->name,
                ]);
                DocumentHistory::history($data);
                return redirect()->back()->with('success', __('Document successfully unarchived!'));
            } else {
                return redirect()->back()->with('error', __('Document not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function documentArchive(Request $request)
    {
        if (\Auth::user()->can('archive document')) {
            if ($request->ajax()) {
                $category = $request->category ?? '';
                $stage = $request->stage ?? '';
                $dateRange = $request->date_range;
                $startDate = '';
                $endDate = '';

                if (!empty($dateRange)) {
                    [$start, $end] = explode(' - ', $dateRange);
                    $startDate = Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d');
                    $endDate = Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d');
                }
                $documents_query = Document::where('parent_id', '=', parentId())->where('archive', 1);
                $documents = $documents_query->OrderBy('id', 'desc')->get();


                if (!empty($request->category)) {
                    $documents_query->where('category_id', $request->category);
                }
                if (!empty($request->stages)) {
                    $documents_query->where('stage_id', $request->stages);
                }

                if ($startDate && $endDate) {
                    $documents_query->whereBetween('created_at', [$startDate, $endDate]);
                }

                $count = $documents_query->count();
                $documents = $documents_query->orderBy('id', 'desc')->get();

                $html = view('document.archive_index', compact('documents', 'count'))->render();
                return response()->json(['html' => $html, 'count' => $count]);
            }

            $category = Category::where('parent_id', parentId())->get()->pluck('title', 'id')->prepend(__('Select Category'), '');
            $category->prepend(__('Select Category'), '');

            $stages = Stage::where('parent_id', parentId())->get()->pluck('title', 'id')->prepend(__('Select Stage'), '');
            $stages->prepend(__('Select Stage'), '');

            return view('document.archive', compact('category', 'stages'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function documentNumber()
    {
        $latestDoc = Document::where('parent_id', parentId())->latest()->first();
        if ($latestDoc == null) {
            return 1;
        } else {
            return $latestDoc->document_id + 1;
        }
    }
}
