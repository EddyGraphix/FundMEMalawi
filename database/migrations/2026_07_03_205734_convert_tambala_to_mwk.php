<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE campaigns CHANGE goal_amount_tambala goal_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE campaigns CHANGE raised_amount_tambala raised_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('UPDATE campaigns SET goal_amount = goal_amount / 100, raised_amount = raised_amount / 100');

            DB::statement('ALTER TABLE donations CHANGE amount_tambala amount DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE donations CHANGE tip_amount_tambala tip_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('UPDATE donations SET amount = amount / 100, tip_amount = tip_amount / 100');

            DB::statement('ALTER TABLE withdrawals CHANGE amount_tambala amount DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('UPDATE withdrawals SET amount = amount / 100');

            DB::statement('ALTER TABLE wallets CHANGE total_raised_tambala total_raised DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE wallets CHANGE available_balance_tambala available_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE wallets CHANGE pending_balance_tambala pending_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE wallets CHANGE processing_withdrawals_tambala processing_withdrawals DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE wallets CHANGE completed_withdrawals_tambala completed_withdrawals DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('UPDATE wallets SET total_raised = total_raised / 100, available_balance = available_balance / 100, pending_balance = pending_balance / 100, processing_withdrawals = processing_withdrawals / 100, completed_withdrawals = completed_withdrawals / 100');

            DB::statement('ALTER TABLE wallet_transactions CHANGE amount_tambala amount DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE wallet_transactions CHANGE balance_before_tambala balance_before DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE wallet_transactions CHANGE balance_after_tambala balance_after DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('UPDATE wallet_transactions SET amount = amount / 100, balance_before = balance_before / 100, balance_after = balance_after / 100');

            DB::statement('ALTER TABLE campaign_templates CHANGE suggested_goal_tambala suggested_goal DECIMAL(15,2) NOT NULL DEFAULT 0.00');
            DB::statement('UPDATE campaign_templates SET suggested_goal = suggested_goal / 100');
        } else {
            // SQLite — add new decimal columns, copy data, drop old columns
            Schema::table('campaigns', function ($table) {
                $table->decimal('goal_amount', 15, 2)->default(0.00);
                $table->decimal('raised_amount', 15, 2)->default(0.00);
            });
            DB::statement('UPDATE campaigns SET goal_amount = CAST(goal_amount_tambala AS REAL) / 100, raised_amount = CAST(raised_amount_tambala AS REAL) / 100');
            Schema::table('campaigns', function ($table) {
                $table->dropColumn(['goal_amount_tambala', 'raised_amount_tambala']);
            });

            Schema::table('donations', function ($table) {
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->decimal('tip_amount', 15, 2)->default(0.00);
            });
            DB::statement('UPDATE donations SET amount = CAST(amount_tambala AS REAL) / 100, tip_amount = CAST(tip_amount_tambala AS REAL) / 100');
            Schema::table('donations', function ($table) {
                $table->dropColumn(['amount_tambala', 'tip_amount_tambala']);
            });

            Schema::table('withdrawals', function ($table) {
                $table->decimal('amount', 15, 2)->default(0.00);
            });
            DB::statement('UPDATE withdrawals SET amount = CAST(amount_tambala AS REAL) / 100');
            Schema::table('withdrawals', function ($table) {
                $table->dropColumn(['amount_tambala']);
            });

            Schema::table('wallets', function ($table) {
                $table->decimal('total_raised', 15, 2)->default(0.00);
                $table->decimal('available_balance', 15, 2)->default(0.00);
                $table->decimal('pending_balance', 15, 2)->default(0.00);
                $table->decimal('processing_withdrawals', 15, 2)->default(0.00);
                $table->decimal('completed_withdrawals', 15, 2)->default(0.00);
            });
            DB::statement('UPDATE wallets SET total_raised = CAST(total_raised_tambala AS REAL) / 100, available_balance = CAST(available_balance_tambala AS REAL) / 100, pending_balance = CAST(pending_balance_tambala AS REAL) / 100, processing_withdrawals = CAST(processing_withdrawals_tambala AS REAL) / 100, completed_withdrawals = CAST(completed_withdrawals_tambala AS REAL) / 100');
            Schema::table('wallets', function ($table) {
                $table->dropColumn(['total_raised_tambala', 'available_balance_tambala', 'pending_balance_tambala', 'processing_withdrawals_tambala', 'completed_withdrawals_tambala']);
            });

            Schema::table('wallet_transactions', function ($table) {
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->decimal('balance_before', 15, 2)->default(0.00);
                $table->decimal('balance_after', 15, 2)->default(0.00);
            });
            DB::statement('UPDATE wallet_transactions SET amount = CAST(amount_tambala AS REAL) / 100, balance_before = CAST(balance_before_tambala AS REAL) / 100, balance_after = CAST(balance_after_tambala AS REAL) / 100');
            Schema::table('wallet_transactions', function ($table) {
                $table->dropColumn(['amount_tambala', 'balance_before_tambala', 'balance_after_tambala']);
            });

            Schema::table('campaign_templates', function ($table) {
                $table->decimal('suggested_goal', 15, 2)->default(0.00);
            });
            DB::statement('UPDATE campaign_templates SET suggested_goal = CAST(suggested_goal_tambala AS REAL) / 100');
            Schema::table('campaign_templates', function ($table) {
                $table->dropColumn(['suggested_goal_tambala']);
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('UPDATE campaigns SET goal_amount = goal_amount * 100, raised_amount = raised_amount * 100');
            DB::statement('ALTER TABLE campaigns CHANGE goal_amount goal_amount_tambala BIGINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE campaigns CHANGE raised_amount raised_amount_tambala BIGINT NOT NULL DEFAULT 0');

            DB::statement('UPDATE donations SET amount = amount * 100, tip_amount = tip_amount * 100');
            DB::statement('ALTER TABLE donations CHANGE amount amount_tambala BIGINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE donations CHANGE tip_amount tip_amount_tambala BIGINT NOT NULL DEFAULT 0');

            DB::statement('UPDATE withdrawals SET amount = amount * 100');
            DB::statement('ALTER TABLE withdrawals CHANGE amount amount_tambala BIGINT NOT NULL DEFAULT 0');

            DB::statement('UPDATE wallets SET total_raised = total_raised * 100, available_balance = available_balance * 100, pending_balance = pending_balance * 100, processing_withdrawals = processing_withdrawals * 100, completed_withdrawals = completed_withdrawals * 100');
            DB::statement('ALTER TABLE wallets CHANGE total_raised total_raised_tambala BIGINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE wallets CHANGE available_balance available_balance_tambala BIGINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE wallets CHANGE pending_balance pending_balance_tambala BIGINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE wallets CHANGE processing_withdrawals processing_withdrawals_tambala BIGINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE wallets CHANGE completed_withdrawals completed_withdrawals_tambala BIGINT NOT NULL DEFAULT 0');

            DB::statement('UPDATE wallet_transactions SET amount = amount * 100, balance_before = balance_before * 100, balance_after = balance_after * 100');
            DB::statement('ALTER TABLE wallet_transactions CHANGE amount amount_tambala BIGINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE wallet_transactions CHANGE balance_before balance_before_tambala BIGINT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE wallet_transactions CHANGE balance_after balance_after_tambala BIGINT NOT NULL DEFAULT 0');

            DB::statement('UPDATE campaign_templates SET suggested_goal = suggested_goal * 100');
            DB::statement('ALTER TABLE campaign_templates CHANGE suggested_goal suggested_goal_tambala BIGINT NOT NULL DEFAULT 0');
        } else {
            Schema::table('campaigns', function ($table) {
                $table->dropColumn(['goal_amount', 'raised_amount']);
                $table->bigInteger('goal_amount_tambala');
                $table->bigInteger('raised_amount_tambala')->default(0);
            });
            DB::statement('UPDATE campaigns SET goal_amount_tambala = 0, raised_amount_tambala = 0');
            Schema::table('donations', function ($table) {
                $table->dropColumn(['amount', 'tip_amount']);
                $table->bigInteger('amount_tambala');
                $table->bigInteger('tip_amount_tambala')->default(0);
            });
            DB::statement('UPDATE donations SET amount_tambala = 0, tip_amount_tambala = 0');
            Schema::table('withdrawals', function ($table) {
                $table->dropColumn(['amount']);
                $table->bigInteger('amount_tambala');
            });
            DB::statement('UPDATE withdrawals SET amount_tambala = 0');
            Schema::table('wallets', function ($table) {
                $table->dropColumn(['total_raised', 'available_balance', 'pending_balance', 'processing_withdrawals', 'completed_withdrawals']);
                $table->bigInteger('total_raised_tambala')->default(0);
                $table->bigInteger('available_balance_tambala')->default(0);
                $table->bigInteger('pending_balance_tambala')->default(0);
                $table->bigInteger('processing_withdrawals_tambala')->default(0);
                $table->bigInteger('completed_withdrawals_tambala')->default(0);
            });
            Schema::table('wallet_transactions', function ($table) {
                $table->dropColumn(['amount', 'balance_before', 'balance_after']);
                $table->bigInteger('amount_tambala');
                $table->bigInteger('balance_before_tambala');
                $table->bigInteger('balance_after_tambala');
            });
            DB::statement('UPDATE wallet_transactions SET amount_tambala = 0, balance_before_tambala = 0, balance_after_tambala = 0');
            Schema::table('campaign_templates', function ($table) {
                $table->dropColumn(['suggested_goal']);
                $table->bigInteger('suggested_goal_tambala')->nullable();
            });
        }
    }
};
