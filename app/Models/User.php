<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'invited_by',
        'invited_at',
        'invitation_sent_at',
        'last_login_at',
        'timezone',
        'phone',
        'address',
        'avatar',
        'is_active',
        'deactivated_at',
        'deactivation_reason',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'invited_at' => 'datetime',
        'invitation_sent_at' => 'datetime',
        'last_login_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'is_active' => 'boolean',
        'preferences' => 'array',
    ];

    // Relationships
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function invitedUsers()
    {
        return $this->hasMany(User::class, 'invited_by');
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePendingInvitation($query)
    {
        return $query->whereNull('email_verified_at');
    }

    public function scopeInvitedBy($query, $userId)
    {
        return $query->where('invited_by', $userId);
    }

    public function scopeRecentlyInvited($query, $days = 7)
    {
        return $query->where('invited_at', '>=', now()->subDays($days));
    }

    // Attributes
    public function getInvitationStatusAttribute()
    {
        if ($this->email_verified_at) {
            return 'completed';
        }
        
        if ($this->invitation_sent_at) {
            return 'sent';
        }
        
        return 'created';
    }

    public function getIsPendingAttribute()
    {
        return is_null($this->email_verified_at);
    }

    public function getInitialsAttribute()
    {
        return collect(explode(' ', $this->name))
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->join('');
    }

    // Methods
    public function markInvitationSent()
    {
        $this->update(['invitation_sent_at' => now()]);
    }

    public function recordLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    public function deactivate($reason = null)
    {
        $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivation_reason' => $reason,
        ]);
    }

    public function activate()
    {
        $this->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivation_reason' => null,
        ]);
    }

    public function hasPendingInvitation()
    {
        return $this->isPending && $this->invitation_sent_at;
    }
}