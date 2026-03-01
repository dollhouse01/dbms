<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\PackageTransaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage user')) {
            if (\Auth::user()->type == 'super admin') {
                $users = User::where('parent_id', parentId())->where('type', 'owner')->OrderBy('id', 'desc')->get();
                return view('user.index', compact('users'));
            } else {
                $users = User::where('parent_id', '=', parentId())->whereNot('type', 'client')->OrderBy('id', 'desc')->get();
                return view('user.index', compact('users'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        $userRoles = Role::where('parent_id', parentId())->get()->pluck('name', 'id');
        return view('user.create', compact('userRoles'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create user')) {
            if (\Auth::user()->type == 'super admin') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $user = new User();
                $user->first_name = $request->name;
                $user->email = $request->email;
                $user->password = \Hash::make($request->password);
                $user->phone_number = $request->phone_number;
                $user->type = 'owner';
                $user->lang = 'english';
                $user->email_verified_at = now();
                $user->subscription = 1;
                $user->parent_id = parentId();
                $user->save();

                $userRole = Role::findByName('owner');
                $user->assignRole($userRole);
                defaultEmployeeCreate($user->id);
                defaultTemplate($user->id);

                $module = 'owner_create';
                $setting = settings();
                $errorMessage = '';
                if (!empty($user)) {
                    $data['subject'] = 'New User Created';
                    $data['module'] = $module;
                    $data['password'] = $request->password;
                    $data['name'] = $request->name;
                    $data['email'] = $request->email;
                    $data['url'] = env('APP_URL');
                    $data['logo'] = $setting['company_logo'];
                    $to = $user->email;
                    $response = commonEmailSend($to, $data);
                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }


                return redirect()->route('users.index')->with('success', __('User successfully created.') . '</br>' . $errorMessage);
            } else {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                        'role' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $ids = parentId();
                $authUser = \App\Models\User::find($ids);
                $totalUser = $authUser->totalUser();
                $subscription = Subscription::find($authUser->subscription);
                if ($totalUser >= $subscription->user_limit && $subscription->user_limit != 0) {
                    return redirect()->back()->with('error', __('Your user limit is over, please upgrade your subscription.'));
                }
                $userRole = Role::findById($request->role);
                $user = new User();
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->phone_number = $request->phone_number;
                $user->password = \Hash::make($request->password);
                $user->email_verified_at = now();
                $user->type = $userRole->name;
                $user->profile = 'avatar.png';
                $user->lang = 'english';
                $user->parent_id = parentId();
                $user->save();
                $user->assignRole($userRole);
                $setting = settings();

                triggerN8n('create_user', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                    'password' => $request->password,
                    'company_name' => $setting['company_name'],
                    'company_email' => $setting['company_email'],
                    'company_phone' => $setting['company_phone'],
                ]);

                $module = 'user_create';
                $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                $notification->password = $request->password;
                $errorMessage = '';
                if (!empty($notification) && $notification->enabled_email == 1) {
                    $notification_responce = MessageReplace($notification, $user->id);
                    $data['subject'] = $notification_responce['subject'];
                    $data['message'] = $notification_responce['message'];
                    $data['module'] = $module;
                    $data['password'] = $request->password;
                    $data['logo'] = $setting['company_logo'];
                    $to = $user->email;

                    if ($notification->enabled_email == 1) {
                        $response = commonEmailSend($to, $data);
                        if ($response['status'] == 'error') {
                            $errorMessage = $response['message'];
                        }
                    }
                    if ($notification->enabled_sms == 1) {
                        $twilio_sid = getSettingsValByName('twilio_sid');
                        if (!empty($twilio_sid)) {
                            send_twilio_msg($user->phone_number, $notification_responce['sms_message']);
                        }
                    }
                }

                return redirect()->route('users.index')->with('success', __('User successfully created.') . '</br>' . $errorMessage);
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function show($id)
    {
        $id = decrypt($id);
        if (!\Auth::user()->can('show user')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        } else {
            $user = User::findOrFail($id);
            if (!empty($user)) {
                $settings = settings();
                $transactions = PackageTransaction::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
                $subscriptions = Subscription::OrderBy('id', 'desc')->get();
                return view('user.show', compact('user', 'transactions', 'settings', 'subscriptions'));
            } else {
                return redirect()->back()->with('error', __('User not found.'));
            }
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $user = User::findOrFail($id);
        $userRoles = Role::where('parent_id', '=', parentId())->get()->pluck('name', 'id');
        $assignedRoleId = $user->roles->first()?->id;

        return view('user.edit', compact('user', 'userRoles', 'assignedRoleId'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        if (\Auth::user()->can('edit user')) {
            if (\Auth::user()->type == 'super admin') {
                $user = User::findOrFail($id);

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email,' . $id,
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $userData = $request->all();
                $userData['first_name'] = $userData['name'];
                $user->fill($userData)->save();

                return redirect()->route('users.index')->with('success', 'User successfully updated.');
            } else {


                $validator = \Validator::make(
                    $request->all(),
                    [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|email|unique:users,email,' . $id,
                        'role' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $userRole = Role::findById($request->role);
                $user = User::findOrFail($id);
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->phone_number = $request->phone_number;
                $user->type = $userRole->name;
                $user->save();
                $user->roles()->sync($userRole);
                return redirect()->route('users.index')->with('success', 'User successfully updated.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(Request $request, $id)
    {
        if (\Auth::user()->can('delete user')) {
            $id = decrypt($id);
            $user = User::find($id);
            $user->delete();

            if ($request->type == 'client') {
                return redirect()->route('client')->with('success', __('Client successfully deleted.'));
            }
            return redirect()->route('users.index')->with('success', __('User successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function loggedHistory()
    {
        $ids = parentId();
        $authUser = \App\Models\User::find($ids);
        $subscription = \App\Models\Subscription::find($authUser->subscription);

        if (\Auth::user()->can('manage logged history') && $subscription->enabled_logged_history == 1) {
            $histories = LoggedHistory::where('parent_id', parentId())->OrderBy('id', 'desc')->get();
            return view('logged_history.index', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryShow($id)
    {
        if (\Auth::user()->can('manage logged history')) {
            $histories = LoggedHistory::find($id);
            return view('logged_history.show', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryDestroy($id)
    {
        if (\Auth::user()->can('delete logged history')) {
            $histories = LoggedHistory::find($id);
            $histories->delete();
            return redirect()->back()->with('success', 'Logged history succefully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function client(Request $request)
    {
        $clients = User::where('parent_id', parentId())->where('type', 'client')->OrderBy('id', 'desc')->get();
        return view('client.index', compact('clients'));
    }

    public function clientCreate()
    {
        if (\Auth::user()->can('create client')) {
            return view('client.create');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function clientStore(Request $request)
    {
        if (\Auth::user()->can('create client')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'email' => 'required|email|unique:users',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->phone_number = $request->phone_number;
            $user->address = $request->address;
            $user->email = $request->email;
            $user->password = \Hash::make('123456789');
            $user->type = 'client';
            $user->lang = 'english';
            $user->email_verified_at = now();
            $user->subscription = 1;
            $user->parent_id = parentId();
            $user->save();

            return redirect()->route('client')->with('success', __('User successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function clientshow($id)
    {
        $id = decrypt($id);
        if (\Auth::user()->can('show client')) {
            $user = User::find($id);
            $documents = Document::where('assign_to', $id)->OrderBy('id', 'desc')->get();
            return view('client.show', compact('user', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function clientEdit($id)
    {
        if (\Auth::user()->can('edit client')) {
            $id = decrypt($id);
            $user = User::find($id);
            return view('client.edit', compact('user'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function clientUpdate(Request $request, $id)
    {
        if (\Auth::user()->can('edit client')) {
            $id = decrypt($id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'email' => 'required|email|unique:users,email,' . $id . ',id'
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $user = User::find($id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->phone_number = $request->phone_number;
            $user->address = $request->address;
            $user->email = $request->email;
            $user->save();

            return redirect()->route('client')->with('success', __('User successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
