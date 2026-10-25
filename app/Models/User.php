<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Language;

class User extends Authenticatable
{
    use HasRoles, SoftDeletes;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'identity',
        'dob',
        'role',
        'email_verified_at',
        'password',
        'remember_token',
        'status',
        'language_preference',
        'data_access',
        'image',
      	'identity_expired_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */


    protected $hidden = [
        'created_at',
        'updated_at',
        'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function ModelHasPermission()
    {
        return $this->hasMany(ModelHasPermission::class, 'model_id');
    }
   protected $appends = ['image_url', 'language_preference_label', 'created_date', 'data_access_badge', 'data_access_label'];
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }

    public function getLanguagePreferenceLabelAttribute()
    {
        return Language::label(Language::resolve($this->language_preference));
    }

    public function getCreatedDateAttribute()
    {
        return $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('d/M/Y h:i A') : null;
    }

    public function hasFullDataAccess(): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        return ($this->data_access ?? 'own') === 'all';
    }

    public function getDataAccessLabelAttribute(): string
    {
        return $this->hasFullDataAccess()
            ? __('user.data_access.all')
            : __('user.data_access.own');
    }

    public function getDataAccessBadgeAttribute(): string
    {
        if ($this->hasFullDataAccess()) {
            return '<span class="badge" style="display: inline-block; font-size: 11px;padding: 5px 10px; border-radius: 14px; background: #2196F3; color: #0369a1; font-weight: 600;">' . __('user.data_access.all') . '</span>';
        }

        return '<span class="badge" style="display: inline-block; font-size: 11px;padding: 5px 10px; border-radius: 14px; background: #FFC107; color: #92400e; font-weight: 600;">' . __('user.data_access.own') . '</span>';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }
}
