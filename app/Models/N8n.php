<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class N8n extends Model
{
    use HasFactory;

    public static function method()
    {
        return [
            'GET' => __('GET'),
            'POST' => __('POST'),
            'PATCH' => __('PATCH'),
            'PUT' => __('PUT'),
            'HEAD' => __('HEAD')
        ];
    }

    public static function module()
    {
        return [
            'create_user' => __('Create User'),
            'create_reminder' => __('Create Reminder'),
            'document_share' => __('Docuemnt Share'),
            'new_doc_comment' => __('New Document Comment'),
            'document_version_update' => __('Document Version Update')
        ];
    }
}
