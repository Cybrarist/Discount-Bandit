<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Observers\UserObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use AuthenticationLoggable, HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rss_feed',
        'notification_settings',
        'notification_settings.ntfy_url',
        'notification_settings.ntfy_auth_username',
        'notification_settings.ntfy_auth_password',
        'notification_settings.ntfy_auth_token',
        'notification_settings.telegram_bot_token',
        'notification_settings.telegram_channel_id',
        'notification_settings.enable_rss_feed',
        'customization_settings',
        'customization_settings.timezone',
        'customization_settings.enable_top_navigation',
        'customization_settings.theme_color',
        'other_settings',
        'other_settings.max_links',
        'role',
        'currency_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_settings' => 'json',
            'customization_settings' => 'json',
            'other_settings' => 'json',
            'role' => RoleEnum::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function product_links()
    {
        return $this->hasMany(ProductLink::class);
    }

    public function rss_feed_items(): HasMany
    {
        return $this->hasMany(RssFeedItem::class);
    }
}
