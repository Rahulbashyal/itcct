<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceFile extends Model
{
    protected $fillable = [
        'workspace_id',
        'parent_id',
        'name',
        'type',
        'language',
        'content',
        'path'
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function parent()
    {
        return $this->belongsTo(WorkspaceFile::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(WorkspaceFile::class, 'parent_id');
    }
}
