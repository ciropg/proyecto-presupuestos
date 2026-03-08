<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->string('code')->nullable()->after('user_id');
            $table->date('budget_date')->nullable()->after('description');
            $table->unique('code');
        });

        $budgets = DB::table('budgets')
            ->select('id', 'created_at')
            ->orderBy('id')
            ->get();

        foreach ($budgets as $budget) {
            DB::table('budgets')
                ->where('id', $budget->id)
                ->update([
                    'code' => 'BGT-'.str_pad((string) $budget->id, 6, '0', STR_PAD_LEFT),
                    'budget_date' => $budget->created_at !== null
                        ? Carbon::parse($budget->created_at)->toDateString()
                        : now()->toDateString(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique('budgets_code_unique');
            $table->dropColumn(['code', 'budget_date']);
        });
    }
};
