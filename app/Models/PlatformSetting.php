<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description', 'is_public'];

    protected static array $defaults = [
        ['key' => 'platform_fee_percentage', 'value' => '5', 'type' => 'float', 'group' => 'fees', 'label' => 'Platform Fee Percentage'],
        ['key' => 'minimum_donation_amount', 'value' => '1', 'type' => 'integer', 'group' => 'donations', 'label' => 'Minimum Donation (MWK)'],
        ['key' => 'minimum_withdrawal_amount', 'value' => '500', 'type' => 'integer', 'group' => 'withdrawals', 'label' => 'Minimum Withdrawal (MWK)'],
        ['key' => 'payment_providers', 'value' => '["airtel_money","tnm_mpamba","card"]', 'type' => 'json', 'group' => 'payments', 'label' => 'Supported Payment Providers'],
        ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'general', 'label' => 'Maintenance Mode'],
        ['key' => 'platform_name', 'value' => 'FundMe Malawi', 'type' => 'string', 'group' => 'branding', 'label' => 'Platform Name', 'is_public' => true],
        ['key' => 'support_email', 'value' => 'support@fundme.mw', 'type' => 'string', 'group' => 'contact', 'label' => 'Support Email', 'is_public' => true],
        ['key' => 'max_campaign_duration_days', 'value' => '90', 'type' => 'integer', 'group' => 'campaigns', 'label' => 'Max Campaign Duration (days)'],
        ['key' => 'auto_approve_campaigns', 'value' => 'false', 'type' => 'boolean', 'group' => 'campaigns', 'label' => 'Auto-Approve Campaigns'],
        ['key' => 'sms_provider', 'value' => 'null', 'type' => 'json', 'group' => 'notifications', 'label' => 'SMS Provider Config'],
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public static function ensureDefaults(): void
    {
        foreach (self::$defaults as $setting) {
            static::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'label' => $setting['label'],
                    'description' => $setting['description'] ?? null,
                    'is_public' => $setting['is_public'] ?? false,
                ],
            );
        }
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function setValue(string $key, mixed $value, ?string $type = null): void
    {
        if ($type === null) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_float($value) => 'float',
                is_array($value) => 'json',
                default => 'string',
            };
        }

        $casted = is_array($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $casted, 'type' => $type],
        );
    }
}
