<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    public $fillable = [
        'name',
        'archive',
        'stage_id',
        'assign_to',
        'category_id',
        'sub_category_id',
        'description',
        'tages',
        'created_by',
        'parent_id'
    ];

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function subCategory()
    {
        return $this->hasOne('App\Models\SubCategory', 'id', 'sub_category_id');
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function AssignTo()
    {
        return $this->hasOne(User::class, 'id', 'assign_to');
    }

    public function StageData()
    {
        return $this->hasOne(Stage::class, 'id', 'stage_id');
    }

    public function LastVersion()
    {
        return $this->hasOne(VersionHistory::class, 'document_id', 'id')->where('current_version', '=', 1);
    }

    public function tags()
    {
        $docTag = !empty($this->tages) ? explode(',', $this->tages) : [];
        $tags = [];
        foreach ($docTag as $tag) {
            $tags[] = Tag::find($tag);
        }
        return $tags;
    }
}
