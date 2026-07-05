<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    protected $table = 'module_permissions';
    public function permission()
    {
        return $this->hasMany(Permission::class, 'module_id');
    }
    public function modulePermissionParent()
    {
        return $this->hasOne(ModulePermission::class, 'parent_id', 'parent_id');
    }
    public function modulePermission()
    {
        return $this->hasMany(ModulePermission::class, 'parent_id', 'parent_id');
    }
}
