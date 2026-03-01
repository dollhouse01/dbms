<?php

use App\Mail\Common;
use App\Mail\EmailVerification;
use App\Mail\TestMail;
use App\Models\AiTemplate;
use App\Models\AuthPage;
use App\Models\Custom;
use App\Models\DocumentComment;
use App\Models\FAQ;
use App\Models\HomePage;
use App\Models\LoggedHistory;
use App\Models\N8n;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Reminder;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VersionHistory;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Dom\Document;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FAQRCode\Google2FA;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Twilio\Rest\Client;

if (!function_exists('settingsKeys')) {
    function settingsKeys()
    {
        return $settingsKeys = [
            "app_name" => "",
            "theme_mode" => "light",
            "layout_font" => "Roboto",
            "accent_color" => "preset-6",
            "color_type" => "preset",
            "custom_color" => "--primary-rgb: 0,0,0",
            "custom_color_code" => "#000000",
            "sidebar_caption" => "true",
            "theme_layout" => "ltr",
            "layout_width" => "false",
            "owner_email_verification" => "off",
            "landing_page" => "on",
            "register_page" => "on",
            "company_logo" => "logo.png",
            "company_favicon" => "favicon.png",
            "landing_logo" => "landing_logo.png",
            "light_logo" => "light_logo.png",
            "meta_seo_title" => "",
            "meta_seo_keyword" => "",
            "meta_seo_description" => "",
            "meta_seo_image" => "",
            "company_date_format" => "M j, Y",
            "company_time_format" => "g:i A",
            "company_name" => "",
            "company_phone" => "",
            "company_address" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "google_recaptcha" => "off",
            "recaptcha_key" => "",
            "recaptcha_secret" => "",
            'SERVER_DRIVER' => "",
            'SERVER_HOST' => "",
            'SERVER_PORT' => "",
            'SERVER_USERNAME' => "",
            'SERVER_PASSWORD' => "",
            'SERVER_ENCRYPTION' => "",
            'FROM_EMAIL' => "",
            'FROM_NAME' => "",
            "document_number_prefix" => "#DOC-000",
            "reminder_number_prefix" => "#REM-000",
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "timezone" => "",
            "footer_column_1" => "Quick Links",
            "footer_column_1_enabled" => "active",
            "footer_column_2" => "Help",
            "footer_column_2_enabled" => "active",
            "footer_column_3" => "OverView",
            "footer_column_3_enabled" => "active",
            "footer_column_4" => "Core System",
            "footer_column_4_enabled" => "active",
            "pricing_feature" => "on",
            "copyright" => "",

            "aws_s3_key" => "",
            "aws_s3_secret" => "",
            "aws_s3_region" => "",
            "aws_s3_bucket" => "",
            "aws_s3_url" => "",
            "aws_s3_endpoint" => "",
            "aws_s3_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            // "wasabi_root" => "",
            "wasabi_file_type" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "storage_type" => "local",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
            'openai_module' => 'off',
            'twilio_sid' => '',
            'twilio_token' => '',
            'twilio_from_number' => '',
            'openai_secret_key' => '',
        ];
    }
}

if (!function_exists('settings')) {
    function settings($userId = 0)
    {
        $settingData = DB::table('settings');
        if (empty($userId)) {
            if (\Auth::check()) {
                $userId = parentId();
                $settingData = $settingData->where('parent_id', $userId);
            } else {
                $settingData = $settingData->where('parent_id', 1);
            }
        } else {
            $settingData = $settingData->where('parent_id', $userId);
        }
        $settingData = $settingData->get();
        $details = settingsKeys();

        foreach ($settingData as $row) {
            $details[$row->name] = $row->value;
        }

        config(
            [
                'captcha.secret' => $details['recaptcha_secret'],
                'captcha.sitekey' => $details['recaptcha_key'],
                'options' => [
                    'timeout' => 30,
                ]
            ]
        );

        return $details;
    }
}

if (!function_exists('settingsById')) {

    function settingsById($userId)
    {
        $data = DB::table('settings');
        $data = $data->where('parent_id', $userId);
        $data = $data->get();
        $settings = settingsKeys();

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        config(
            [
                'captcha.secret' => $settings['recaptcha_key'],
                'captcha.sitekey' => $settings['recaptcha_secret'],
                'options' => [
                    'timeout' => 30,
                ],
            ]
        );

        return $settings;
    }
}

if (!function_exists('subscriptionPaymentSettings')) {
    function subscriptionPaymentSettings()
    {
        $settingData = DB::table('settings')->where('type', 'payment')->where('parent_id', '=', 1)->get();
        $result = [
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",

            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
        ];

        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }

        return $result;
    }
}

if (!function_exists('invoicePaymentSettings')) {
    function invoicePaymentSettings($id)
    {
        $settingData = DB::table('settings')->where('type', 'payment')->where('parent_id', $id)->get();
        $result = [
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",

            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
        ];

        foreach ($settingData as $row) {
            $result[$row->name] = $row->value;
        }
        return $result;
    }
}

if (!function_exists('getSettingsValByName')) {
    function getSettingsValByName($key)
    {
        $setting = settings();
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }
}

if (!function_exists('getSettingsValByIdName')) {
    function getSettingsValByIdName($id, $key)
    {
        $setting = settingsById($id);
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }
}

if (!function_exists('settingDateFormat')) {
    function settingDateFormat($settings, $date)
    {
        return date($settings['company_date_format'], strtotime($date));
    }
}

if (!function_exists('settingPriceFormat')) {
    function settingPriceFormat($settings, $price)
    {
        return $settings['CURRENCY_SYMBOL'] . $price;
    }
}

if (!function_exists('settingTimeFormat')) {
    function settingTimeFormat($settings, $time)
    {
        return date($settings['company_time_format'], strtotime($time));
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat($date)
    {
        $settings = settings();

        return date($settings['company_date_format'], strtotime($date));
    }
}

if (!function_exists('timeFormat')) {
    function timeFormat($time)
    {
        $settings = settings();

        return date($settings['company_time_format'], strtotime($time));
    }
}

if (!function_exists('priceFormat')) {
    function priceFormat($price)
    {
        $settings = settings();

        return $settings['CURRENCY_SYMBOL'] . $price;
    }
}

if (!function_exists('parentId')) {
    function parentId()
    {
        if (\Auth::user()->type == 'owner' || \Auth::user()->type == 'super admin') {
            return \Auth::user()->id;
        } else {
            return \Auth::user()->parent_id;
        }
    }
}

if (!function_exists('assignSubscription')) {
    function assignSubscription($id)
    {
        $subscription = Subscription::find($id);
        if ($subscription) {
            \Auth::user()->subscription = $subscription->id;
            if ($subscription->interval == 'Monthly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Quarterly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(3)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Yearly') {
                \Auth::user()->subscription_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                \Auth::user()->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }
            \Auth::user()->save();

            $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['super admin', 'owner'])->get();

            if ($subscription->user_limit == 0) {
                foreach ($users as $user) {
                    $user->is_active = 1;
                    $user->save();
                }
            } else {
                $userCount = 0;
                foreach ($users as $user) {
                    $userCount++;
                    if ($userCount <= $subscription->user_limit) {
                        $user->is_active = 1;
                        $user->save();
                    } else {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
            }
            return [
                'is_success' => true,
            ];
        } else {
            return [
                'is_success' => false,
                'error' => 'Subscription is deleted.',
            ];
        }
    }
}

if (!function_exists('assignManuallySubscription')) {
    function assignManuallySubscription($id, $userId)
    {
        $owner = User::find($userId);
        $subscription = Subscription::find($id);
        if ($subscription) {
            $owner->subscription = $subscription->id;
            if ($subscription->interval == 'Monthly') {
                $owner->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Quarterly') {
                $owner->subscription_expire_date = Carbon::now()->addMonths(3)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Yearly') {
                $owner->subscription_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                $owner->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }

            $owner->save();

            $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['super admin', 'owner'])->get();

            if ($subscription->user_limit == 0) {
                foreach ($users as $user) {
                    $user->is_active = 1;
                    $user->save();
                }
            } else {
                $userCount = 0;
                foreach ($users as $user) {
                    $userCount++;
                    if ($userCount <= $subscription->user_limit) {
                        $user->is_active = 1;
                        $user->save();
                    } else {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
            }
            return [
                'is_success' => true,
            ];
        } else {
            return [
                'is_success' => false,
                'error' => 'Subscription is deleted.',
            ];
        }
    }
}

if (!function_exists('smtpDetail')) {
    function smtpDetail($id)
    {
        $settings = emailSettings($id);

        $smtpDetail = config(
            [
                'mail.mailers.smtp.transport' => $settings['SERVER_DRIVER'],
                'mail.mailers.smtp.host' => $settings['SERVER_HOST'],
                'mail.mailers.smtp.port' => $settings['SERVER_PORT'],
                'mail.mailers.smtp.encryption' => $settings['SERVER_ENCRYPTION'],
                'mail.mailers.smtp.username' => $settings['SERVER_USERNAME'],
                'mail.mailers.smtp.password' => $settings['SERVER_PASSWORD'],
                'mail.from.address' => $settings['FROM_EMAIL'],
                'mail.from.name' => $settings['FROM_NAME'],
            ]
        );

        return $smtpDetail;
    }
}

if (!function_exists('documentPrefix')) {
    function documentPrefix()
    {
        $settings = settings();
        return $settings["document_number_prefix"];
    }
}

if (!function_exists('reminderPrefix')) {
    function reminderPrefix()
    {
        $settings = settings();
        return $settings["reminder_number_prefix"];
    }
}

if (!function_exists('timeCalculation')) {
    function timeCalculation($startDate, $startTime, $endDate, $endTime)
    {
        $startdate = $startDate . ' ' . $startTime;
        $enddate = $endDate . ' ' . $endTime;

        $startDateTime = new DateTime($startdate);
        $endDateTime = new DateTime($enddate);

        $interval = $startDateTime->diff($endDateTime);
        $totalHours = $interval->h + $interval->i / 60;

        return number_format($totalHours, 2);
    }
}

if (!function_exists('setup')) {
    function setup()
    {
        $setupPath = storage_path() . "/installed";
        return $setupPath;
    }
}

if (!function_exists('userLoggedHistory')) {
    function userLoggedHistory()
    {
        $serverip = $_SERVER['REMOTE_ADDR'];
        $data = @unserialize(file_get_contents('http://ip-api.com/php/' . $serverip));
        if (isset($data['status']) && $data['status'] == 'success') {
            $browser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            if ($browser->device->type == 'bot') {
                return redirect()->intended(RouteServiceProvider::HOME);
            }
            $referrerData = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
            $data['browser'] = $browser->browser->name ?? null;
            $data['os'] = $browser->os->name ?? null;
            $data['language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $data['device'] = User::getDevice($_SERVER['HTTP_USER_AGENT']);
            $data['referrer_host'] = !empty($referrerData['host']);
            $data['referrer_path'] = !empty($referrerData['path']);
            $result = json_encode($data);
            $details = new LoggedHistory();
            $details->type = Auth::user()->type;
            $details->user_id = Auth::user()->id;
            $details->date = date('Y-m-d H:i:s');
            $details->Details = $result;
            $details->ip = $serverip;
            $details->parent_id = parentId();
            $details->save();
        }
    }
}

if (!function_exists('defaultEmployeeCreate')) {
    function defaultEmployeeCreate($id)
    {
        // Default Employee role
        $employeeRoleData = [
            'name' => 'employee',
            'parent_id' => $id,
        ];
        $systemEmployeeRole = Role::create($employeeRoleData);
        // Default Employee permissions
        $systemEmployeePermissions = [
            ['name' => 'manage account settings'],
            ['name' => 'manage password settings'],
            ['name' => 'manage 2FA settings'],

            ['name' => 'manage contact'],
            ['name' => 'create contact'],
            ['name' => 'edit contact'],
            ['name' => 'delete contact'],
            ['name' => 'manage note'],
            ['name' => 'create note'],
            ['name' => 'edit note'],
            ['name' => 'delete note'],
            ['name' => 'manage my document'],
            ['name' => 'edit my document'],
            ['name' => 'delete my document'],
            ['name' => 'show my document'],
            ['name' => 'create my document'],
            ['name' => 'show reminder'],
            ['name' => 'manage my reminder'],
            ['name' => 'download document'],
            ['name' => 'preview document'],
            ['name' => 'manage comment'],
            ['name' => 'create comment'],
            ['name' => 'manage version'],
            ['name' => 'manage share document'],
            ['name' => 'create share document'],
        ];
        $permissins = Permission::whereIn('name', $systemEmployeePermissions)->get();
        $systemEmployeeRole->givePermissionTo($permissins);
        return $systemEmployeeRole;
    }
}

if (!function_exists('defaultTemplate')) {
    function defaultTemplate($id)
    {
        $templateData = [
            'user_create' => [
                'module' => 'user_create',
                'name' => 'New User',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{app_link}', '{username}', '{password}'],
                'subject' => 'Welcome',
                'templete' => '<p><strong>Dear {new_user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome to {company_name}! We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Your account details are as follows:</p><p><strong>App Link:</strong> <a href="{app_link}">{app_link}</a></p><p><strong>Username:</strong> {username}</p><p><strong>Password:</strong> {password}</p><p>&nbsp;</p><p>Thank you for choosing .</p></blockquote>',
                'sms_message' => 'Hi {new_user_name}, welcome to {company_name}!\nApp: {app_link}\nUsername: {username}\nPassword: {password}',

            ],
            'reminder_create' => [
                'module' => 'reminder_create',
                'name' => 'New Reminder',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{subject}', '{message}', '{created_by}'],
                'subject' => '{subject}',
                'templete' => '<p><strong>Reminder:</strong> {subject}</p><p>&nbsp;</p><blockquote><p>{message}</p></blockquote><p>&nbsp;</p><p><em>Created by:</em> {created_by}</p><p>Thank you!</p>',
                'sms_message' => 'Reminder: {subject}. {message} - Created by {created_by}. Thank you.'
            ],
            'document_share' => [
                'module' => 'document_share',
                'name' => 'Share Document',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{document_name}', '{share_by}'],
                'subject' => 'Document Share: {document_name}',
                'templete' => '<p><strong>Dear User,</strong></p><p>&nbsp;</p><blockquote><p>The document <strong>{document_name}</strong> has been shared with you by <strong>{share_by}</strong>.</p><p>Please review the document at your convenience.</p></blockquote><p>&nbsp;</p><p>If you have any questions or need further assistance, feel free to reach out to the sender.</p><p>Thank you!</p><p>Best regards,</p><p>{share_by}</p>',
                'sms_message' => 'Dear User, {share_by} shared the document "{document_name}" with you. Please review it. For help, contact the sender. Thank you.'
            ],
            'document_comment' => [
                'module' => 'document_comment',
                'name' => 'Document Comment',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{document_name}', '{comment_by}'],
                'subject' => 'New Comment Added: {document_name}',
                'templete' => '<p><strong>Dear User,</strong></p><p>&nbsp;</p><p>A new comment has been added to the document <strong>{document_name}</strong> by <strong>{comment_by}</strong>.</p><p>Please log in to your account to review the latest update.</p><p>&nbsp;</p><p>If you have any questions, feel free to contact the concerned user.</p><p>Thank you.</p><p>Best regards,</p><p>{company_name}</p>',
                'sms_message' => 'A new comment was added to "{document_name}" by {comment_by}. Please log in to review the update.'
            ],
            'document_version_update' => [
                'module' => 'document_version_update',
                'name' => 'Document Version Update',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{document_name}', '{uploaded_by}', '{updated_at}'],
                'subject' => 'New Version Uploaded: {document_name}',
                'templete' => '<p><strong>Dear User,</strong></p><p>A new version of the document <strong>{document_name}</strong> has been uploaded by <strong>{uploaded_by}</strong>.</p><p>Please log in to review the updated file.</p><p><em>Updated at:</em> {updated_at}</p><p>Thank you,<br>{company_name}</p>',
                'sms_message' => 'New version of "{document_name}" uploaded by {uploaded_by}. Please login to review.'
            ],
        ];

        // Store all created templates if needed
        $createdTemplates = [];

        foreach ($templateData as $key => $value) {

            $existingTemplate = Notification::where('module', $value['module'])
                ->where('parent_id', $id)
                ->first();

            if (!$existingTemplate) {

                $template = new Notification();
                $template->module = $value['module'];
                $template->name = $value['name'];
                $template->subject = $value['subject'];
                $template->message = $value['templete'];
                $template->sms_message = $value['sms_message'];
                $template->short_code = json_encode($value['short_code']);
                $template->enabled_email = 0;
                $template->enabled_sms = 0;
                $template->parent_id = $id;
                $template->save();

                $createdTemplates[] = $template;
            }
        }

        // Return all created templates if needed
        return $createdTemplates;
    }
}

if (!function_exists('MessageReplace')) {
    function MessageReplace($notification, $id = 0)
    {
        $return['subject'] = $notification->subject;
        $return['message'] = $notification->message;
        if (!empty($notification->password)) {
            $notification['password'] = $notification->password;
        }
        $settings = settings();
        if (!empty($notification)) {
            $search = [];
            $replace = [];
            if ($notification->module == 'user_create') {
                $user = User::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{app_link}', '{username}', '{password}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user->name, env('APP_URL'), $user->email, $notification['password']];
            }
            if ($notification->module == 'reminder_create') {
                $reminder = Reminder::find($id);
                $user = User::find($reminder->created_by);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{subject}', '{message}', '{created_by}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $reminder->subject, $reminder->message, $user->name];
            }
            if ($notification->module == 'document_share') {
                $share = \App\Models\Document::find($id);
                $user = User::find($share->created_by);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{document_name}', '{share_by}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $share->name, $user->name];
            }
            if ($notification->module == 'document_comment') {
                $document = \App\Models\Document::find($id);
                $commentUser = \Auth::user();
                $docComment = DocumentComment::where('document_id', $document->id)->latest()->first();
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{document_name}', '{comment_by}', '{comment}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $document->name, $commentUser->name, $docComment->comment ?? '',];

            }
            if ($notification->module == 'document_version_update') {
                $document = \App\Models\Document::find($id);
                $commentUser = \Auth::user();
                $docComment = VersionHistory::where('document_id', $document->id)->latest()->first();
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{document_name}', '{uploaded_by}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $document->name, $commentUser->name];

            }
            $return['subject'] = str_replace($search, $replace, $notification->subject);
            $return['message'] = str_replace($search, $replace, $notification->message);
            $return['sms_message'] = str_replace($search, $replace, $notification->sms_message);
        }

        return $return;
    }
}

if (!function_exists('sendEmail')) {
    function sendEmail($to, $datas)
    {
        $datas['settings'] = settings();
        try {
            emailSettings(parentId());
            Mail::to($to)->send(new TestMail($datas));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please add valide email smtp details first.')
            ];
        }
    }
}

if (!function_exists('commonEmailSend')) {
    function commonEmailSend($to, $datas)
    {
        $datas['settings'] = settings();
        try {
            if (Auth::check()) {
                if ($datas['module'] == 'owner_create') {
                    emailSettings(1);
                } else {
                    emailSettings(parentId());
                }
            } else {
                emailSettings($datas['parent_id']);
            }
            Mail::to($to)->send(new Common($datas));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please add valide email smtp details first.')
            ];
        }
    }
}

if (!function_exists('emailSettings')) {
    function emailSettings($id)
    {
        $settingData = DB::table('settings')
            ->where('type', 'smtp')
            ->where('parent_id', $id)
            ->get();

        $result = [
            'FROM_EMAIL' => "",
            'FROM_NAME' => "",
            'SERVER_DRIVER' => "",
            'SERVER_HOST' => "",
            'SERVER_PORT' => "",
            'SERVER_USERNAME' => "",
            'SERVER_PASSWORD' => "",
            'SERVER_ENCRYPTION' => "",
        ];

        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }

        // Apply settings dynamically
        config([
            'mail.default' => $result['SERVER_DRIVER'] ?? '',
            'mail.mailers.smtp.host' => $result['SERVER_HOST'] ?? '',
            'mail.mailers.smtp.port' => $result['SERVER_PORT'] ?? '',
            'mail.mailers.smtp.encryption' => $result['SERVER_ENCRYPTION'] ?? '',
            'mail.mailers.smtp.username' => $result['SERVER_USERNAME'] ?? '',
            'mail.mailers.smtp.password' => $result['SERVER_PASSWORD'] ?? '',
            'mail.from.name' => $result['FROM_NAME'] ?? '',
            'mail.from.address' => $result['FROM_EMAIL'] ?? '',
        ]);
        return $result;
    }
}

if (!function_exists('sendEmailVerification')) {
    function sendEmailVerification($to, $data)
    {
        $data['settings'] = emailSettings(1);
        try {
            Mail::to($to)->send(new EmailVerification($data));

            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::error('Email Sending Failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please contact the administrator to resolve this issue.')
            ];
            return redirect()->back()->with('error', __(''));
        }
    }
}

if (!function_exists('RoleName')) {
    function RoleName($permission_id = '0')
    {
        $retuen = '';
        $role_id_array = DB::table('role_has_permissions')->where('permission_id', $permission_id)->pluck('role_id');
        if (!empty($role_id_array)) {
            $role_id_array = DB::table('roles')->whereIn('id', $role_id_array)->pluck('name')->toArray();
            $retuen = implode(', ', $role_id_array);
        }

        return $retuen;
    }
}

if (!function_exists('HomePageSection')) {
    function HomePageSection()
    {
        $retuen = [
            [
                'title' => 'Header Menu',
                'section' => 'Section 0',
                'content_value' => '{"name":"Header Menu","menu_pages":["1","2"]}',
            ],
            [
                'title' => 'Banner',
                'section' => 'Section 1',
                'content_value' => '{"name":"Banner","section_enabled":"active","title":"DRMS SaaS - Digital Record Management System","sub_title":"Document and Records Management System (DRMS SaaS) software refers to the various features and functionalities that the software offers to help organizations manage their digital documents effectively.","btn_name":"Get Started","btn_link":"#","section_footer_text":"Manage your business efficiently with our all-in-one solution designed for performance, security, and scalability.","section_footer_image":{},"section_main_image":{},"section_footer_image_path":"upload\/homepage\/banner_2.png","section_main_image_path":"upload\/homepage\/banner_1.png","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'OverView',
                'section' => 'Section 2',
                'content_value' => '{"name":"OverView","section_enabled":"active","Box1_title":"Customers","Box1_number":"500+","Box2_title":"Subscription Plan","Box2_number":"4+","Box3_title":"Language","Box3_number":"11+","box1_number_image":{},"box2_number_image":{},"box3_number_image":{},"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"upload\/homepage\/OverView_1.svg","box_image_2_path":"upload\/homepage\/OverView_2.svg","box_image_3_path":"upload\/homepage\/OverView_3.svg","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'AboutUs',
                'section' => 'Section 3',
                'content_value' => '{"name":"AboutUs","section_enabled":"active","Box1_title":"Empower Your Business to Thrive with Us","Box1_info":"Unlock growth, streamline operations, and achieve success with our innovative solutions.","Box1_list":["Simplify and automate your business processes for maximum efficiency.","Receive tailored strategies to meet business needs and unlock potential.","Grow confidently with flexible solutions that adapt to your business needs.","Make smarter decisions with real-time analytics and performance tracking.","Rely on 24\/7 expert assistance to keep your business running smoothly."],"Box2_title":"Eliminate Paperwork, Elevate Productivity","Box2_info":"Simplify your operations with seamless digital solutions and focus on what truly matters.","Box2_list":["Replace manual paperwork with automated workflows.","Secure cloud storage lets you manage documents on the go.","Streamlined processes save time and reduce errors.","Keep your information safe with encrypted storage.","Reduce printing, storage, and administrative expenses.","Go green by minimizing paper use and waste."],"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"upload\/homepage\/img-customize-1.svg","Box2_image_path":"upload\/homepage\/img-customize-2.svg"}',
            ],
            [
                'title' => 'Offer',
                'section' => 'Section 4',
                'content_value' => '{"name":"Offer","section_enabled":"active","Sec4_title":"What Our Software Offers","Sec4_info":"Our software provides powerful, scalable solutions designed to streamline your business operations.","Sec4_box1_title":"User-Friendly Interface","Sec4_box1_enabled":"active","Sec4_box1_info":"Simplify operations with an intuitive and easy-to-use platform.","Sec4_box2_title":"End-to-End Automation","Sec4_box2_enabled":"active","Sec4_box2_info":"Automate repetitive tasks to save time and increase efficiency.","Sec4_box3_title":"Customizable Solutions","Sec4_box3_enabled":"active","Sec4_box3_info":"Tailor features to fit your unique business needs and workflows.","Sec4_box4_title":"Scalable Features","Sec4_box4_enabled":"active","Sec4_box4_info":"Grow your business with flexible solutions that scale with you.","Sec4_box5_title":"Enhanced Security","Sec4_box5_enabled":"active","Sec4_box5_info":"Protect your data with advanced encryption and security protocols.","Sec4_box6_title":"Real-Time Analytics","Sec4_box6_enabled":"active","Sec4_box6_info":"Gain actionable insights with live data tracking and reporting.","Sec4_box1_image":{},"Sec4_box2_image":{},"Sec4_box3_image":{},"Sec4_box4_image":{},"Sec4_box5_image":{},"Sec4_box6_image":{},"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"upload\/homepage\/offers_1.svg","Sec4_box2_image_path":"upload\/homepage\/offers_2.svg","Sec4_box3_image_path":"upload\/homepage\/offers_3.svg","Sec4_box4_image_path":"upload\/homepage\/offers_4.svg","Sec4_box5_image_path":"upload\/homepage\/offers_5.svg","Sec4_box6_image_path":"upload\/homepage\/offers_6.svg","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'Pricing',
                'section' => 'Section 5',
                'content_value' => '{"name":"Pricing","section_enabled":"active","Sec5_title":"Flexible Pricing","Sec5_info":"Get started for free, upgrade later in our application.","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'Core Features',
                'section' => 'Section 6',
                'content_value' => '{"name":"Core Features","section_enabled":"active","Sec6_title":"Core Features","Sec6_info":"Core Modules For Your Business","Sec6_Box_title":["Dashboard","Subscription Plan","Document","Document Details","User Logged History"],"Sec6_Box_subtitle":["DRMS SaaS software refers to the various features and functionalities that the software offers to help organizations manage their digital documents effectively.","DRMS SaaS software refers to the various features and functionalities that the software offers to help organizations manage their digital documents effectively.","DRMS SaaS software refers to the various features and functionalities that the software offers to help organizations manage their digital documents effectively.","DRMS SaaS software refers to the various features and functionalities that the software offers to help organizations manage their digital documents effectively.","DRMS SaaS software refers to the various features and functionalities that the software offers to help organizations manage their digital documents effectively.","DRMS SaaS software refers to the various features and functionalities that the software offers to help organizations manage their digital documents effectively."],"Sec6_box_image":[{},{},{},{},{},{}],"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec6_box0_image_path":"upload\/homepage\/1.png","Sec6_box1_image_path":"upload\/homepage\/2.png","Sec6_box2_image_path":"upload\/homepage\/3.png","Sec6_box3_image_path":"upload\/homepage\/4.png","Sec6_box4_image_path":"upload\/homepage\/5.png","Sec6_box5_image_path":"upload\/homepage\/6.png","Sec6_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'Testimonials',
                'section' => 'Section 7',
                'content_value' => '{"name":"Testimonials","section_enabled":"active","Sec7_title":"What Our Customers Say About Us","Sec7_info":"We\u2019re proud of the impact our software has had on businesses just like yours. Hear directly from our customers about how our solutions have made a difference in their day-to-day operations","Sec7_box1_name":"Lenore Becker","Sec7_box1_tag":null,"Sec7_box1_Enabled":"active","Sec7_box1_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box2_name":"Damian Morales","Sec7_box2_tag":"New","Sec7_box2_Enabled":"active","Sec7_box2_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum.","Sec7_box3_name":"Oleg Lucas","Sec7_box3_tag":null,"Sec7_box3_Enabled":"active","Sec7_box3_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box4_name":"Jerome Mccoy","Sec7_box4_tag":null,"Sec7_box4_Enabled":"active","Sec7_box4_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box5_name":"Rafael Carver","Sec7_box5_tag":null,"Sec7_box5_Enabled":"active","Sec7_box5_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend.","Sec7_box6_name":"Edan Rodriguez","Sec7_box6_tag":null,"Sec7_box6_Enabled":"active","Sec7_box6_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box7_name":"Kalia Middleton","Sec7_box7_tag":null,"Sec7_box7_Enabled":"active","Sec7_box7_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum.","Sec7_box8_name":"Zenaida Chandler","Sec7_box8_tag":null,"Sec7_box8_Enabled":"active","Sec7_box8_review":"Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Quisque ut nisi. Nulla porta dolor. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc.","Sec7_box1_image":{},"Sec7_box2_image":{},"Sec7_box3_image":{},"Sec7_box4_image":{},"Sec7_box5_image":{},"Sec7_box6_image":{},"Sec7_box7_image":{},"Sec7_box8_image":{},"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"upload\/homepage\/review_1.png","Sec7_box2_image_path":"upload\/homepage\/review_2.png","Sec7_box3_image_path":"upload\/homepage\/review_3.png","Sec7_box4_image_path":"upload\/homepage\/review_4.png","Sec7_box5_image_path":"upload\/homepage\/review_5.png","Sec7_box6_image_path":"upload\/homepage\/review_6.png","Sec7_box7_image_path":"upload\/homepage\/review_7.png","Sec7_box8_image_path":"upload\/homepage\/review_8.png"}'
            ],
            [
                'title' => 'Choose US',
                'section' => 'Section 8',
                'content_value' => '{"name":"Choose US","section_enabled":"active","Sec8_title":"Reason to Choose US","Sec8_box1_info":"Proven Expertise","Sec8_box2_info":"Customizable Solutions","Sec8_box3_info":"Seamless Integration","Sec8_box4_info":"Exceptional Support","Sec8_box5_info":"Scalable and Future-Proof","Sec8_box6_info":"Security You Can Trust","Sec8_box7_info":"User-Friendly Interface","Sec8_box8_info":"Innovation at Its Core","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'
            ],
            [
                'title' => 'FAQ',
                'section' => 'Section 9',
                'content_value' => '{"name":"FAQ","section_enabled":"active","Sec9_title":"Frequently Asked Questions (FAQ)","Sec9_info":"Please refer the Frequently ask question for your quick help","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'
            ],
            [
                'title' => 'AboutUS - Footer',
                'section' => 'Section 10',
                'content_value' => '{"name":"AboutUS - Footer","section_enabled":"active","Sec10_title":"About DRMS SaaS","Sec10_info":"Document and Records Management System (DRMS SaaS) software refers to the various features and functionalities that the software offers to help organizations manage their digital documents effectively.","section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}'
            ],
        ];

        foreach ($retuen as $key => $value) {
            $HomePage = new HomePage();
            $HomePage->title = $value['title'];
            $HomePage->section = $value['section'];
            if (!empty($value['content_value'])) {
                $HomePage->content_value = $value['content_value'];
            }
            $HomePage->enabled = 1;
            $HomePage->parent_id = 1;
            $HomePage->save();
        }
        return '';
    }
}

if (!function_exists('CustomPage')) {
    function CustomPage()
    {
        $retuen = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy_policy',
                'content' => "<h3><strong>1. Information We Collect</strong></h3><p>We may collect the following types of information from you:</p><h4><strong>a. Personal Information</strong></h4><ul><li>Name, email address, phone number, and other contact details.</li><li>Payment information (if applicable).</li></ul><h4><strong>b. Non-Personal Information</strong></h4><ul><li>Browser type, operating system, and device information.</li><li>Usage data, including pages visited, time spent, and other analytical data.</li></ul><h4><strong>c. Information You Provide</strong></h4><ul><li>Information you voluntarily provide when contacting us, signing up, or completing forms.</li></ul><h4><strong>d. Cookies and Tracking Technologies</strong></h4><ul><li>We use cookies, web beacons, and other tracking tools to enhance your experience and analyze usage patterns.</li></ul><h3><strong>2. How We Use Your Information</strong></h3><p>We use the information collected for the following purposes:</p><ul><li>To provide, maintain, and improve our Services.</li><li>To process transactions and send you confirmations.</li><li>To communicate with you, including responding to inquiries or providing updates.</li><li>To personalize your experience and deliver tailored content.</li><li>To comply with legal obligations and protect against fraud or misuse.</li></ul><h3><strong>3. How We Share Your Information</strong></h3><p>We do not sell your personal information. However, we may share your information with:</p><ul><li><strong>Service Providers:</strong> Third-party vendors who assist in providing our Services.</li><li><strong>Legal Authorities:</strong> When required to comply with legal obligations or protect our rights.</li><li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets, your information may be transferred.</li></ul><h3><strong>4. Data Security</strong></h3><p>We implement appropriate technical and organizational measures to protect your data against unauthorized access, disclosure, alteration, or destruction. However, no method of transmission or storage is 100% secure, and we cannot guarantee absolute security.</p><h3><strong>5. Your Rights</strong></h3><p>You have the right to:</p><ul><li>Access, correct, or delete your personal data.</li><li>Opt-out of certain data processing activities, including marketing communications.</li><li>Withdraw consent where processing is based on consent.</li></ul><p>To exercise your rights, please contact us at [contact email].</p><h3><strong>6. Third-Party Links</strong></h3><p>Our Services may contain links to third-party websites. We are not responsible for the privacy practices or content of these websites. Please review their privacy policies before engaging with them.</p><h3><strong>7. Children's Privacy</strong></h3><p>Our Services are not intended for children under the age of [13/16], and we do not knowingly collect personal information from them. If we become aware that a child has provided us with personal data, we will take steps to delete it.</p><h3><strong>8. Changes to This Privacy Policy</strong></h3><p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with a revised 'Last Updated' date. Your continued use of the Services after such changes constitutes your acceptance of the new terms.</p><h3>&nbsp;</h3>"
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms_conditions',
                'content' => "<h3><strong>1. Acceptance of Terms</strong></h3><p>By using our Services, you confirm that you are at least [18 years old or the legal age in your jurisdiction] and capable of entering into a binding agreement. If you are using our Services on behalf of an organization, you represent that you have the authority to bind that organization to these Terms.</p><h3><strong>2. Use of Services</strong></h3><p>You agree to use our Services only for lawful purposes and in accordance with these Terms. You must not:</p><ul><li>Violate any applicable laws or regulations.</li><li>Use our Services in a manner that could harm, disable, overburden, or impair them.</li><li>Attempt to gain unauthorized access to our systems or networks.</li><li>Transmit any harmful code, viruses, or malicious software.</li></ul><h3><strong>3. User Accounts</strong></h3><p>If you create an account with us, you are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account or breach of security.</p><h3><strong>4. Intellectual Property</strong></h3><p>All content, trademarks, logos, and intellectual property associated with our Services are owned by [Your Company Name] or our licensors. You are granted a limited, non-exclusive, non-transferable license to access and use the Services for personal or authorized business purposes. Any unauthorized use, reproduction, or distribution is prohibited.</p><h3><strong>5. Payment and Billing</strong> (if applicable)</h3><p>If our Services involve payments:</p><ul><li>All fees are due at the time of purchase unless otherwise agreed.</li><li>We reserve the right to change pricing or introduce new fees with prior notice.</li><li>Refunds, if applicable, will be handled according to our [Refund Policy].</li></ul><h3><strong>6. Termination of Services</strong></h3><p>We reserve the right to suspend or terminate your access to our Services at our discretion, without prior notice, if:</p><ul><li>You breach these Terms.</li><li>We are required to do so by law.</li><li>Our Services are discontinued or altered.</li></ul><h3><strong>7. Limitation of Liability</strong></h3><p>To the fullest extent permitted by law:</p><ul><li>[Your Company Name] and its affiliates shall not be liable for any direct, indirect, incidental, or consequential damages resulting from your use of our Services.</li><li>Our liability is limited to the amount you paid, if any, for accessing our Services.</li></ul><h3><strong>8. Indemnification</strong></h3><p>You agree to indemnify and hold [Your Company Name], its affiliates, employees, and partners harmless from any claims, liabilities, damages, losses, or expenses arising from your use of the Services or violation of these Terms.</p><h3><strong>9. Modifications to Terms</strong></h3><p>We may update these Terms from time to time. Any changes will be effective immediately upon posting, and your continued use of the Services constitutes your acceptance of the revised Terms.</p>"
            ],
        ];
        foreach ($retuen as $key => $value) {
            $Page = new Page();
            $Page->title = $value['title'];
            $Page->slug = $value['slug'];
            $Page->content = $value['content'];
            $Page->enabled = 1;
            $Page->parent_id = 1;
            $Page->save();
        }


        $FAQ_retuen = [
            [
                'question' => 'What features does your software offer?',
                'description' => 'Our software provides a range of features including automation tools, real-time analytics, cloud-based access, secure data storage, seamless integrations, and customizable solutions tailored to your business needs.',
            ],
            [
                'question' => 'Is your software easy to use?',
                'description' => 'Yes! Our platform is designed to be user-friendly and intuitive, so your team can get started quickly without a steep learning curve.',
            ],
            [
                'question' => 'Can I integrate your software with my existing systems?',
                'description' => 'Absolutely! Our software is built to easily integrate with your current tools and systems, making the transition seamless and efficient.',
            ],
            [
                'question' => 'Is customer support available?',
                'description' => 'Yes! We offer 24/7 customer support. Our dedicated team is ready to assist you with any questions or issues you may have.',
            ],
            [
                'question' => 'Is my data secure with your software?',
                'description' => 'Yes. We use advanced encryption and data protection protocols to ensure your data is secure and private at all times.',
            ],
            [
                'question' => 'Can I customize the software to fit my business needs?',
                'description' => 'Yes! Our software is highly customizable to adapt to your unique workflows and requirements.',
            ],
            [
                'question' => 'What types of businesses can benefit from your software?',
                'description' => 'Our solutions are suitable for a wide range of industries, including retail, healthcare, finance, marketing, and more. We tailor our offerings to meet the specific needs of each business.',
            ],

            [
                'question' => 'Is there a free trial available?',
                'description' => 'Yes! We offer a free trial so you can explore the features and capabilities of our software before committing.',
            ],

            [
                'question' => 'Do I need technical expertise to use the software?',
                'description' => 'Not at all. Our software is designed for users of all skill levels. Plus, our support team is available to guide you through any setup or usage questions.',
            ],

            [
                'question' => 'How often is the software updated?',
                'description' => 'We regularly release updates to improve features, security, and overall performance, ensuring that you always have access to the latest technology.',
            ],
        ];
        foreach ($FAQ_retuen as $key => $FAQ_value) {
            $FAQs = new FAQ();
            $FAQs->question = $FAQ_value['question'];
            $FAQs->description = $FAQ_value['description'];
            $FAQs->enabled = 1;
            $FAQs->parent_id = 1;
            $FAQs->save();
        }
        return '';
    }
}

if (!function_exists('DefaultCustomPage')) {
    function DefaultCustomPage()
    {
        $return = Page::where('enabled', 1)->whereIn('id', [1, 2])->get();
        return $return;
    }
}

if (!function_exists('DefaultBankTransferPayment')) {
    function DefaultBankTransferPayment()
    {
        $bankArray = [
            'bank_transfer_payment' => 'on',
            'bank_name' => 'Bank of America',
            'bank_holder_name' => 'SmartWeb Infotech',
            'bank_account_number' => '4242 4242 4242 4242',
            'bank_ifsc_code' => 'BOA45678',
            'bank_other_details' => '',
        ];

        foreach ($bankArray as $key => $val) {
            \DB::insert(
                'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $val,
                    $key,
                    'payment',
                    1,
                ]
            );
        }

        return '';
    }
}

if (!function_exists('QrCode2FA')) {
    function QrCode2FA()
    {
        $user = Auth::user();

        $google2fa = new Google2FA();

        // generate a secret
        $secret = $google2fa->generateSecretKey();

        // generate the QR code, indicating the address
        // of the web application and the user name
        // or email in this case
        $company = env('APP_NAME');
        if ($user->type != 'super admin') {
            $company = isset(settings()['company_name']) && !empty(settings()['company_name']) ? settings()['company_name'] : $company;
        }

        $qr_code = $google2fa->getQRCodeInline(
            $company,
            $user->email,
            $secret
        );

        // store the current secret in the session
        // will be used when we enable 2FA (see below)
        session(["2fa_secret" => $secret]);

        return $qr_code;
    }
}

if (!function_exists('authPage')) {
    function authPage($id)
    {

        $templateData = [
            'title' => [
                "Secure Access, Seamless Experience.",
                "Your Trusted Gateway to Digital Security.",
                "Fast, Safe & Effortless Login."
            ],
            'description' => [
                "Securely access your account with ease. Whether you're logging in, signing up, or resetting your password, we ensure a seamless and protected experience. Your data, your security, our priority.",
                "Fast, secure, and hassle-free authentication. Sign in with confidence and experience a seamless way to access your account—because your security matters.",
                "A seamless and secure way to access your account. Whether you're logging in, signing up, or recovering your password, we ensure your data stays protected at every step."
            ],
        ];

        $authPage = new AuthPage();
        $authPage->title = json_encode($templateData['title']);
        $authPage->description = json_encode($templateData['description']);
        $authPage->section = 1;
        $authPage->image = 'upload/images/auth_page.svg';
        $authPage->parent_id = $id;
        $authPage->save();

        $createdTemplates[] = $authPage;

        return $createdTemplates;
    }
}

if (!function_exists('NewPermission')) {
    function NewPermission()
    {
        $permissions = [
            ['name' => 'manage storage settings', 'guard_name' => 'web', 'roles' => ['super admin']],
            ['name' => 'manage client', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'create client', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'edit client', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'delete client', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'show client', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'manage Stage', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'create Stage', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'edit Stage', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'delete Stage', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'show Stage', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'share documents', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'archive document', 'guard_name' => 'web', 'roles' => ['owner', 'manager']],
            ['name' => 'manage twilio settings', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage n8n', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'create n8n', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'edit n8n', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'delete n8n', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage openai settings', 'guard_name' => 'web', 'roles' => ['super admin', 'owner']],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate([
                'name' => $permData['name'],
                'guard_name' => $permData['guard_name']
            ]);
        }

        $permissionsByRole = [];

        foreach ($permissions as $permData) {
            foreach ($permData['roles'] as $roleName) {
                $permissionsByRole[$roleName][] = $permData['name'];
            }
        }

        foreach ($permissionsByRole as $roleName => $permNames) {
            $roles = Role::where('name', $roleName)->get();

            foreach ($roles as $role) {
                $role->givePermissionTo($permNames);
            }
        }
        $removePermissions = [
            ['name' => 'create note', 'guard_name' => 'web', 'roles' => ['manager', 'employee']],
            ['name' => 'edit note', 'guard_name' => 'web', 'roles' => ['manager', 'employee']],
            ['name' => 'delete note', 'guard_name' => 'web', 'roles' => ['manager', 'employee']],
        ];

        foreach ($removePermissions as $permData) {
            $permission = Permission::where('name', $permData['name'])
                ->where('guard_name', $permData['guard_name'])
                ->first();

            if ($permission) {
                foreach ($permData['roles'] as $roleName) {
                    $roles = Role::where('name', $roleName)->get();
                    foreach ($roles as $role) {
                        $role->revokePermissionTo($permission);
                    }
                }
            }
        }
        $users = User::where('type', 'owner')->pluck('id');

        foreach ($users as $id) {
            defaultTemplate($id);
        }
        defaultAiTemplate();
        defaultSMSTemplate();
        return true;
    }
}

if (!function_exists('FilesExtension')) {
    function FilesExtension()
    {
        return [
            "jpeg" => "jpeg",
            "jpg" => "jpg",
            "png" => "png",
            "doc" => "doc",
            "csv" => "csv",
            "pptx" => "pptx",
            "xlsx" => "xlsx",
            "docx" => "docx",
            "mp4" => "mp4",
            "mp3" => "mp3",
            "xls" => "xls",
            "pdf" => "pdf",
            "zip" => "zip",
            "json" => "json",
            "txt" => "txt",
            "svg" => "svg",
            "ppt" => "ppt",
            "3dmf" => "3dmf",
            "3dm" => "3dm",
            "gtar" => "gtar",
            "flv" => "flv",
            "fh4" => "fh4",
            "fh5" => "fh5",
            "fhc" => "fhc",
            "help" => "help",
            "hlp" => "hlp",
            "avi" => "avi",
            "ai" => "ai",
            "bin" => "bin",
            "bmp" => "bmp",
            "cab" => "cab",
            "c" => "c",
            "c++" => "c++",
            "class" => "class",
            "css" => "css",
            "cdr" => "cdr",
            "dot" => "dot",
            "dwg" => "dwg",
            "eps" => "eps",
            "exe" => "exe",
            "gif" => "gif",
            "gz" => "gz",
            "js" => "js",
            "java" => "java",
            "latex" => "latex",
            "log" => "log",
            "m3u" => "m3u",
            "midi" => "midi",
            "mid" => "mid",
            "mov" => "mov",
            "ppz" => "ppz",
            "pot" => "pot",
            "ps" => "ps",
            "qt" => "qt",
            "qd3d" => "qd3d",
            "qd3" => "qd3",
            "qxd" => "qxd",
            "rar" => "rar",
            "sgml" => "sgml",
            "sgm" => "sgm",
            "tar" => "tar",
            "tiff" => "tiff",
            "tif" => "tif",
            "tgz" => "tgz",
            "tex" => "tex",
            "html" => "html",
            "htm" => "htm",
            "ico" => "ico",
            "vob" => "vob",
            "wav" => "wav",
            "wrl" => "wrl",
            "xla" => "xla",
            "xlc" => "xlc",
            "xml" => "xml",
            "imap" => "imap",
            "inf" => "inf",
            "jpe" => "jpe",
            "mpeg" => "mpeg",
            "mpg" => "mpg",
            "mp2" => "mp2",
            "ogg" => "ogg",
            "phtml" => "phtml",
            "php" => "php",
            "pgp" => "pgp",
            "pps" => "pps",
            "ra" => "ra",
            "ram" => "ram",
            "rm" => "rm",
            "rtf" => "rtf",
            "spr" => "spr",
            "sprite" => "sprite",
            "stream" => "stream",
            "swf" => "swf",
        ];
    }
}

if (!function_exists('fetch_file')) {
    function fetch_file($filename = '', $path = '')
    {
        $settings = settings(1);

        try {
            if ($settings['storage_type'] == 'wasabi') {
                config(
                    [
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]
                );
            } elseif ($settings['storage_type'] == 's3') {
                config(
                    [
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                        'filesystems.disks.s3.use_path_style_endpoint' => false,
                    ]
                );
            }

            return \Storage::disk($settings['storage_type'])->url($path . $filename);
        } catch (\Throwable $th) {
            return '';
        }
    }
}

if (!function_exists('handleFileUpload')) {
    function handleFileUpload($file, $uploadPath, $customValidation = [])
    {
        try {
            $settings = settings(1);

            if (empty($settings['storage_type'])) {
                throw new \Exception(__('Please set proper configuration for storage.'));
            }

            // Setup filename
            $originalName = $file->getClientOriginalName();
            $fileNameOnly = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = $fileNameOnly . '_' . time() . '.' . $extension;

            // Determine disk and MIME types
            switch ($settings['storage_type']) {
                case 'wasabi':
                    config([
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]);
                    $disk = 'wasabi';
                    $mimes = $settings['wasabi_file_type'] ?? '';
                    break;

                case 's3':
                    config([
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                    ]);
                    $disk = 's3';
                    $mimes = $settings['aws_s3_file_type'] ?? '';
                    break;

                default:
                    $disk = 'local';
                    $mimes = $settings['local_file_type'] ?? '';
                    break;
            }

            // Validate file
            $validation = count($customValidation) > 0 ? $customValidation : ['mimes:' . $mimes];
            $validator = \Validator::make(
                ['upload_file' => $file],
                ['upload_file' => $validation]
            );

            if ($validator->fails()) {
                return [
                    'flag' => 0,
                    'msg' => $validator->messages()->first()
                ];
            }

            // Upload logic
            if ($disk === 'local') {
                $destination = storage_path($uploadPath);
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }
                $file->move($destination, $fileName);
            } else {
                \Storage::disk($disk)->putFileAs($uploadPath, $file, $fileName);
            }

            return [
                'flag' => 1,
                'msg' => 'Upload successful',
                'path' => $uploadPath . '/' . $fileName,
                'filename' => $fileName,
                'disk' => $disk,
            ];
        } catch (\Exception $e) {
            return [
                'flag' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('uploadLogoFile')) {
    function uploadLogoFile($file, $fieldName, $parentId, $userType)
    {
        try {
            $settings = settings(1);

            if (empty($settings['storage_type'])) {
                throw new \Exception(__('Please set proper configuration for storage.'));
            }

            // Determine disk and config
            switch ($settings['storage_type']) {
                case 'wasabi':
                    config([
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                    ]);
                    $disk = 'wasabi';
                    break;

                case 's3':
                    config([
                        'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                        'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                        'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                    ]);
                    $disk = 's3';
                    break;

                default:
                    $disk = 'local';
                    break;
            }

            // Validate PNG file
            $validator = \Validator::make(
                ['upload_file' => $file],
                ['upload_file' => 'required|mimes:png']
            );

            if ($validator->fails()) {
                return [
                    'flag' => 0,
                    'msg' => $validator->messages()->first()
                ];
            }

            $uploadPath = 'upload/logo/';
            $filename = ($userType === 'super admin')
                ? "{$fieldName}.png"
                : "{$parentId}_{$fieldName}.png";

            if ($disk === 'local') {
                $destination = public_path($uploadPath);
                if (!file_exists($destination)) {
                    if (!mkdir($destination, 0777, true) && !is_dir($destination)) {
                        throw new \Exception("Unable to create directory: $destination");
                    }
                }
                $file->storeAs($uploadPath, $filename);
            } else {
                \Storage::disk($disk)->putFileAs($uploadPath, $file, $filename);
            }

            return [
                'flag' => 1,
                'msg' => 'Upload successful',
                'filename' => $filename,
                'path' => $uploadPath . '/' . $filename,
                'disk' => $disk
            ];
        } catch (\Exception $e) {
            \Log::error('Logo upload error: ' . $e->getMessage());
            return [
                'flag' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('deleteOldFile')) {
    function deleteOldFile($imgName, $path)
    {
        try {
            $settings = settings(1);
            $disk = $settings['storage_type'] ?? 'local';

            if ($disk === 'wasabi') {
                config([
                    'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                    'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                    'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                    'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                    'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com',
                ]);
            } elseif ($disk === 's3') {
                config([
                    'filesystems.disks.s3.key' => $settings['aws_s3_key'],
                    'filesystems.disks.s3.secret' => $settings['aws_s3_secret'],
                    'filesystems.disks.s3.region' => $settings['aws_s3_region'],
                    'filesystems.disks.s3.bucket' => $settings['aws_s3_bucket'],
                ]);
            }

            \Storage::disk($disk)->delete($path . $imgName);
        } catch (\Exception $e) {
            \Log::error("Failed to delete file: " . $e->getMessage());
        }
    }
}
if (!function_exists('defaultSMSTemplate')) {
    function defaultSMSTemplate()
    {
        $templateData = [
            'user_create' =>
                [
                    'module' => 'user_create',
                    'sms_message' => 'Hi {new_user_name}, welcome to {company_name}!\nApp: {app_link}\nUsername: {username}\nPassword: {password}',
                ],

            'reminder_create' =>
                [
                    'module' => 'reminder_create',
                    'sms_message' => 'Reminder: {subject}. {message} - Created by {created_by}. Thank you.'
                ],
            'document_share' =>
                [
                    'module' => 'document_share',
                    'sms_message' => 'Dear User, {share_by} shared the document "{document_name}" with you. Please review it. For help, contact the sender. Thank you.'
                ],
            'document_comment' =>
                [
                    'module' => 'document_comment',
                    'sms_message' => 'A new comment was added to "{document_name}" by {comment_by}. Please log in to review the update.'
                ],
            'document_version_update' =>
                [
                    'module' => 'document_version_update',
                    'sms_message' => 'New version of "{document_name}" is uploaded by {uploaded_by}. Please login to review.'
                ]
        ];

        $createdTemplates = [];

        foreach ($templateData as $key => $value) {
            $template = Notification::where('module', $value['module'])->whereNull('sms_message')->update(['sms_message' => $value['sms_message'], 'enabled_sms' => 0]);
            $createdTemplates[] = $template;
        }

        return $createdTemplates;
    }
}

if (!function_exists('triggerN8n')) {
    function triggerN8n(string $module, array $payload): bool
    {
        $webhook = N8n::where([
            'module' => $module,
            'status' => 1,
            'parent_id' => parentId(),
        ])->first();

        try {
            $method = strtolower($webhook->method);

            $response = Http::asJson()
                        ->timeout(10)
                ->$method($webhook->url, $payload);

            if ($response->failed()) {
                Log::error('N8n webhook failed', [
                    'module' => $module,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('N8n webhook exception', [
                'module' => $module,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

if (!function_exists('currentSubscription')) {
    function currentSubscription()
    {
        $ids = parentId();
        $authUser = User::find($ids);
        return [
            'subscription' => Subscription::find($authUser->subscription),
            'pricing_feature_settings' => getSettingsValByIdName(1, 'pricing_feature')
        ];
    }
}

if (!function_exists('defaultAiTemplate')) {
    function defaultAiTemplate()
    {
        $aiTemplateData = [
            [
                'title' => 'contact_subject',
                'template_prompt' => 'Generate a short, professional contact subject for "##subject##" in a ##communication_style## style.',
                'content_type' => 'contact',
                'field' => [
                    [
                        'label' => 'Contact Subject',
                        'placeholder' => 'Enter contact subject',
                        'type' => 'text',
                        'name' => 'subject'
                    ],
                ]
            ],
            [
                'title' => 'contact_description',
                'template_prompt' => 'Write a concise, professional description for this contact: ##subject##. using the communication style: ##communication_style##.',
                'content_type' => 'contact',
                'field' => [
                    [
                        'label' => 'Contact Description',
                        'placeholder' => 'Enter contact description',
                        'type' => 'textarea',
                        'name' => 'subject'
                    ],
                ]
            ],
            [
                'title' => 'noticeboard_title',
                'template_prompt' => 'Generate a concise, professional noticeboard title for "##title##" in a ##communication_style## style.',
                'content_type' => 'noticeboard',
                'field' => [
                    [
                        'label' => 'Noticeboard Title',
                        'placeholder' => 'Enter noticeboard title',
                        'type' => 'text',
                        'name' => 'title'
                    ],
                ],
            ],
            [
                'title' => 'noticeboard_description',
                'template_prompt' => 'Generate a concise, professional noticeboard description in a ##communication_style## style.',
                'content_type' => 'noticeboard',
                'field' => [
                    [
                        'label' => 'Noticeboard Description',
                        'placeholder' => 'Enter noticeboard description',
                        'type' => 'textarea',
                        'name' => 'description'
                    ],
                ],
            ],
            [
                'title' => 'faq_question',
                'template_prompt' => 'Generate a clear, concise FAQ question for "##question##" in a ##communication_style## style.',
                'content_type' => 'faq',
                'field' => [
                    'field' => [
                        [
                            'label' => 'FAQ Question',
                            'placeholder' => 'Enter FAQ topic or rough question',
                            'type' => 'text',
                            'name' => 'question'
                        ],
                    ]
                ],
            ],
            [
                'title' => 'faq_description',
                'template_prompt' => 'Generate a clear and helpful FAQ answer for "##question##" in a ##communication_style## style.',
                'content_type' => 'faq',
                'field' => [
                    [
                        'label' => 'FAQ Description',
                        'placeholder' => 'Enter FAQ description',
                        'type' => 'textarea',
                        'name' => 'description'
                    ],
                ],
            ],
            [
                'title' => 'page_name',
                'template_prompt' => 'Generate a clear, professional page name for "##name##" in a ##communication_style## style.',
                'content_type' => 'custom_page',
                'field' => [
                    [
                        'label' => 'Page Name',
                        'placeholder' => 'Enter page name or topic',
                        'type' => 'text',
                        'name' => 'name'
                    ],
                ],
            ],
            [
                'title' => 'page_content',
                'template_prompt' => 'Generate clear, engaging, and professional page content for "##name##" in a ##communication_style## style.',
                'content_type' => 'custom_page',
                'field' => [
                    [
                        'label' => 'Page Content',
                        'placeholder' => 'Enter page content',
                        'type' => 'textarea',
                        'name' => 'content'
                    ],
                ],
            ],
            [
                'title' => 'coupon_name',
                'template_prompt' => 'Generate a catchy, professional coupon name for "##coupon_name##" in a ##communication_style## style.',
                'content_type' => 'coupon',
                'field' => [

                    [
                        'label' => 'Coupon Name',
                        'placeholder' => 'Enter coupon name',
                        'type' => 'text',
                        'name' => 'coupon_name'
                    ],

                ],
            ],
            [
                'title' => 'coupon_code',
                'template_prompt' => 'Create a short, unique coupon code based on "##coupon_title##" using a ##communication_style## tone.',
                'content_type' => 'coupon',
                'field' => [
                    'field' => [
                        [
                            'label' => 'Coupon Title',
                            'placeholder' => 'e.g. Summer Sale, New User Discount',
                            'type' => 'text',
                            'name' => 'coupon_title'
                        ],
                    ]
                ],
            ],
            [
                'title' => 'subscription_title',
                'template_prompt' => 'Generate a catchy subscription name for "##subscription_title##" in a ##communication_style## tone.',
                'content_type' => 'subscription',
                'field' => [
                    [
                        'label' => 'Subscription Title',
                        'placeholder' => 'Enter subscription title',
                        'type' => 'text',
                        'name' => 'subscription_title'
                    ],
                ],
            ],
            [
                'title' => 'blog_name',
                'template_prompt' => 'Generate a clear, catchy, and professional blog title for "##title##" in a ##communication_style## style.',
                'content_type' => 'blog',
                'field' => [
                    [
                        'label' => 'Blog Title',
                        'placeholder' => 'Enter blog topic or working title',
                        'type' => 'text',
                        'name' => 'title'
                    ],
                ],
            ],
            [
                'title' => 'blog_content',
                'template_prompt' => 'Generate well-structured, engaging, and professional blog content for "##title##" in a ##communication_style## style.',
                'content_type' => 'blog',
                'field' => [
                    [
                        'label' => 'Blog Content',
                        'placeholder' => 'Enter blog content',
                        'type' => 'textarea',
                        'name' => 'content'
                    ],
                ],
            ],

            [
                'title' => 'email_subject',
                'template_prompt' => 'Generate a short, professional, and engaging email subject based on the following topic: ##email_subject## in a ##communication_style## style.',
                'content_type' => 'notification',
                'field' => [
                    [
                        'label' => 'Email Subject',
                        'placeholder' => 'Enter the email topic or idea',
                        'type' => 'text',
                        'name' => 'email_subject'
                    ],
                ]
            ],
            [
                'title' => 'email_template_content',
                'template_prompt' => 'Write a clear, professional, and engaging email content based on the topic: ##email_template_content## in a ##communication_style## style.',
                'content_type' => 'notification',
                'field' => [
                    [
                        'label' => 'Email Content',
                        'placeholder' => 'Enter the main email content or details',
                        'type' => 'textarea',
                        'name' => 'email_template_content'
                    ],
                ]
            ],
            [
                'title' => 'sms_content',
                'template_prompt' => 'Write a concise, clear, and engaging SMS message based on the following topic: ##sms_content## in a ##communication_style## style.',
                'content_type' => 'notification',
                'field' => [
                    [
                        'label' => 'SMS Content',
                        'placeholder' => 'Enter SMS message or key points',
                        'type' => 'textarea',
                        'name' => 'sms_content'
                    ],
                ]
            ],
            [
                'title' => 'document_name',
                'template_prompt' => 'Improve and standardize the following document name for clarity and consistency: ##document_name##. Keep it short and professional.',
                'content_type' => 'document',
                'field' => [
                    [
                        'label' => 'Document Name',
                        'placeholder' => 'Enter document name',
                        'type' => 'text',
                        'name' => 'document_name'
                    ],
                ]
            ],

            [
                'title' => 'document_description',
                'template_prompt' => 'Refine and improve the clarity of this document description: ##document_description##.',
                'content_type' => 'document',
                'field' => [
                    [
                        'label' => 'Document Description',
                        'placeholder' => 'Enter document description',
                        'type' => 'textarea',
                        'name' => 'document_description'
                    ],
                ]
            ],
            [
                'title' => 'reminder_subject',
                'template_prompt' => 'Improve and refine the following reminder subject to make it clear, concise, and attention-grabbing: ##reminder_subject##. Keep it short and professional.',
                'content_type' => 'reminder',
                'field' => [
                    [
                        'label' => 'Reminder Subject',
                        'placeholder' => 'Enter reminder subject',
                        'type' => 'text',
                        'name' => 'reminder_subject'
                    ],
                ]
            ],
            [
                'title' => 'reminder_message',
                'template_prompt' => 'Write a clear, concise, and professional reminder message based on the following details: ##reminder_message##. Make sure the message is polite, actionable, and easy to understand.',
                'content_type' => 'reminder',
                'field' => [
                    [
                        'label' => 'Reminder Message',
                        'placeholder' => 'Enter the reminder details or key points',
                        'type' => 'textarea',
                        'name' => 'reminder_message'
                    ],
                ]
            ],
            [
                'title' => 'document_category',
                'template_prompt' => 'Suggest a suitable document category based on the following document description: ##document_description##. Return only the category name.',
                'content_type' => 'category',
                'field' => [
                    [
                        'label' => 'Document category',
                        'placeholder' => 'Describe the document to generate a category',
                        'type' => 'textarea',
                        'name' => 'document_description'
                    ],
                ]
            ],
            [
                'title' => 'document_sub_category',
                'template_prompt' => 'Suggest a suitable document sub category based on the following document description: ##document_description##. Return only the sub category name.',
                'content_type' => 'sub_category',
                'field' => [
                    [
                        'label' => 'Document sub category',
                        'placeholder' => 'Describe the document to generate a  sub category',
                        'type' => 'textarea',
                        'name' => 'document_description'
                    ],
                ]
            ],
            [
                'title' => 'document_tags',
                'template_prompt' => 'Generate or refine relevant tags for this document based on the following description: ##document_description##. Separate tags with commas.',
                'content_type' => 'document_tag',
                'field' => [
                    [
                        'label' => 'Document Tags',
                        'placeholder' => 'Describe the document to generate relevant tags',
                        'type' => 'text',
                        'name' => 'document_tags'
                    ],
                ]
            ],

        ];
        $createdTemplates = [];

        foreach ($aiTemplateData as $value) {
            $exists = AiTemplate::where('title', $value['title'])->exists();

            if (!$exists) {
                $template = new AiTemplate();
                $template->title = $value['title'];
                $template->template_prompt = $value['template_prompt'];
                $template->content_type = $value['content_type'];
                $template->field = json_encode($value['field']);
                $template->parent_id = 1;
                $template->is_active = 1;
                $template->save();

                $createdTemplates[] = $template;
            }
        }

        return $createdTemplates;

    }
}

if (!function_exists('send_twilio_msg')) {
    function send_twilio_msg($to, $msg)
    {
        if (!empty($msg)) {
            $settings = settings();

            $sid = $settings['twilio_sid'];
            $token = $settings['twilio_token'];
            $from_number = $settings['twilio_from_number'];

            try {
                $client = new Client($sid, $token);
                $client->messages->create($to, [
                    'from' => $from_number,
                    'body' => $msg,
                ]);
            } catch (\Exception $e) {
                \Log::error('Twilio SMS send failed: ' . $e->getMessage());
            }
        }
    }
}
