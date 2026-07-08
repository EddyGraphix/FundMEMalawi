<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'campaigns' => ['goal_amount_tambala', 'raised_amount_tambala'],
        'donations' => ['amount_tambala', 'tip_amount_tambala'],
        'withdrawals' => ['amount_tambala'],
        'wallets' => ['total_raised_tambala', 'available_balance_tambala', 'pending_balance_tambala', 'processing_withdrawals_tambala', 'completed_withdrawals_tambala'],
        'wallet_transactions' => ['amount_tambala', 'balance_before_tambala', 'balance_after_tambala'],
        'campaign_templates' => ['suggested_goal_tambala'],
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        foreach ($this->tables as $table => $columns) {
            $existing = array_filter($columns, fn ($col) => Schema::hasColumn($table, $col));

            if (! empty($existing)) {
                Schema::table($table, function ($t) use ($existing) {
                    $t->dropColumn($existing);
                });
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        $columnsToAdd = [
            'campaigns' => [
                ['name' => 'goal_amount_tambala', 'type' => 'bigInteger', 'default' => 0],
                ['name' => 'raised_amount_tambala', 'type' => 'bigInteger', 'default' => 0],
            ],
            'donations' => [
                ['name' => 'amount_tambala', 'type' => 'bigInteger', 'default' => 0],
                ['name' => 'tip_amount_tambala', 'type' => 'bigInteger', 'default' => 0],
            ],
            'withdrawals' => [
                ['name' => 'amount_tambala', 'type' => 'bigInteger', 'default' => 0],
            ],
            'wallets' => [
                ['name' => 'total_raised_tambala', 'type' => 'bigInteger', 'default' => 0],
                ['name' => 'available_balance_tambala', 'type' => 'bigInteger', 'default' => 0],
                ['name' => 'pending_balance_tambala', 'type' => 'bigInteger', 'default' => 0],
                ['name' => 'processing_withdrawals_tambala', 'type' => 'bigInteger', 'default' => 0],
                ['name' => 'completed_withdrawals_tambala', 'type' => 'bigInteger', 'default' => 0],
            ],
            'wallet_transactions' => [
                ['name' => 'amount_tambala', 'type' => 'bigInteger', 'default' => 0],
                ['name' => 'balance_before_tambala', 'type' => 'bigInteger', 'default' => 0],
                ['name' => 'balance_after_tambala', 'type' => 'bigInteger', 'default' => 0],
            ],
            'campaign_templates' => [
                ['name' => 'suggested_goal_tambala', 'type' => 'bigInteger', 'default' => null],
            ],
        ];

        foreach ($columnsToAdd as $table => $columns) {
            $toAdd = array_filter($columns, fn ($col) => ! Schema::hasColumn($table, $col['name']));

            if (! empty($toAdd)) {
                Schema::table($table, function ($t) use ($toAdd) {
                    foreach ($toAdd as $col) {
                        $method = $col['type'];
                        $c = $t->$method($col['name']);
                        if ($col['default'] !== null) {
                            $c->default($col['default']);
                        }
                    }
                });

                $updateSql = match ($table) {
                    'campaigns' => 'UPDATE campaigns SET goal_amount_tambala = CAST(goal_amount AS REAL) * 100, raised_amount_tambala = CAST(raised_amount AS REAL) * 100',
                    'donations' => 'UPDATE donations SET amount_tambala = CAST(amount AS REAL) * 100, tip_amount_tambala = CAST(tip_amount AS REAL) * 100',
                    'withdrawals' => 'UPDATE withdrawals SET amount_tambala = CAST(amount AS REAL) * 100',
                    'wallets' => 'UPDATE wallets SET total_raised_tambala = CAST(total_raised AS REAL) * 100, available_balance_tambala = CAST(available_balance AS REAL) * 100, pending_balance_tambala = CAST(pending_balance AS REAL) * 100, processing_withdrawals_tambala = CAST(processing_withdrawals AS REAL) * 100, completed_withdrawals_tambala = CAST(completed_withdrawals AS REAL) * 100',
                    'wallet_transactions' => 'UPDATE wallet_transactions SET amount_tambala = CAST(amount AS REAL) * 100, balance_before_tambala = CAST(balance_before AS REAL) * 100, balance_after_tambala = CAST(balance_after AS REAL) * 100',
                    'campaign_templates' => 'UPDATE campaign_templates SET suggested_goal_tambala = CAST(suggested_goal AS REAL) * 100',
                    default => null,
                };

                if ($updateSql) {
                    DB::statement($updateSql);
                }
            }
        }
    }
};
