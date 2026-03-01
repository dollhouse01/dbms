<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use Notifiable;
    use Impersonate;


    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'address',
        'type',
        'profile',
        'phone_number',
        'lang',
        'subscription',
        'subscription_expire_date',
        'parent_id',
        'password',
        'twofa_secret',
        'is_active'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function canImpersonate()
    {
        // Example: Only admins can impersonate others
        return $this->type == 'super admin';
    }

    public function totalUser()
    {
        return User::where('parent_id', $this->id)->count();
    }


    public function totalContact()
    {
        return Contact::where('parent_id', '=', parentId())->count();
    }

    public function roleWiseUserCount($role)
    {
        return User::where('type', $role)->where('parent_id', parentId())->count();
    }

    public static function getDevice($user)
    {
        $mobileType = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tabletType = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        if (preg_match_all($mobileType, $user)) {
            return 'mobile';
        } else {
            if (preg_match_all($tabletType, $user)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }

    public function totalDocument()
    {
        return Document::where('parent_id', '=', parentId())->count();
    }

    public function getNameAttribute()
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function subscriptions()
    {
        return $this->hasOne('App\Models\Subscription', 'id', 'subscription');
    }

    public static $systemModules = [
        'user',
        'client',
        'document',
        'reminder',
        'comment',
        'version',
        'category',
        'tag',
        'contact',
        'note',
        'logged history',
        'pricing transation',
        'settings',
        'mail'
    ];

    public function SubscriptionLeftDay()
    {
        $Subscription = Subscription::find($this->subscription);
        if ($Subscription->interval == 'Unlimited') {
            $return = '<span class="text-success">' . __('Unlimited Days Left') . '</span>';
        } else {
            $date1 = date_create(date('Y-m-d'));
            $date2 = date_create($this->subscription_expire_date);
            $diff = date_diff($date1, $date2);
            $days = $diff->format("%R%a");
            if ($days > 0) {
                $return = '<span class="text-success">' . $days . __(' Days Left') . '</span>';
            } else {
                $return = '<span class="text-danger">' . $days . __(' Days Left') . '</span>';
            }
        }


        return $return;
    }
}
