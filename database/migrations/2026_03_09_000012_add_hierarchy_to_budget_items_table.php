<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('budget_id')
                ->constrained('budget_items')
                ->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->nullable()->after('parent_id');
        });

        $this->migrateLegacyBreakdownsToSubitems();
        $this->initializeSortOrder();
        $this->recalculateHierarchyTotals();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('sort_order');
        });
    }

    private function migrateLegacyBreakdownsToSubitems(): void
    {
        if (! Schema::hasTable('item_breakdowns')) {
            return;
        }

        $breakdowns = DB::table('item_breakdowns')
            ->join('budget_items as parents', 'parents.id', '=', 'item_breakdowns.budget_item_id')
            ->join('resources', 'resources.id', '=', 'item_breakdowns.resource_id')
            ->select([
                'item_breakdowns.id',
                'item_breakdowns.budget_item_id',
                'item_breakdowns.resource_id',
                'item_breakdowns.quantity',
                'item_breakdowns.unit_price',
                'item_breakdowns.subtotal',
                'item_breakdowns.created_at',
                'item_breakdowns.updated_at',
                'parents.budget_id',
                'resources.unit_id',
                'resources.name as resource_name',
                'resources.description as resource_description',
            ])
            ->orderBy('item_breakdowns.budget_item_id')
            ->orderBy('item_breakdowns.id')
            ->get();

        $childCounters = [];

        foreach ($breakdowns as $breakdown) {
            $parentKey = (string) $breakdown->budget_item_id;
            $childCounters[$parentKey] = ($childCounters[$parentKey] ?? 0) + 1;

            DB::table('budget_items')->insert([
                'budget_id' => $breakdown->budget_id,
                'parent_id' => $breakdown->budget_item_id,
                'resource_id' => $breakdown->resource_id,
                'unit_id' => $breakdown->unit_id,
                'name' => $breakdown->resource_name,
                'description' => $breakdown->resource_description,
                'quantity' => $breakdown->quantity,
                'unit_price' => $breakdown->unit_price,
                'subtotal' => $breakdown->subtotal,
                'sort_order' => $childCounters[$parentKey],
                'created_at' => $breakdown->created_at,
                'updated_at' => $breakdown->updated_at,
            ]);
        }
    }

    private function initializeSortOrder(): void
    {
        $items = DB::table('budget_items')
            ->select(['id', 'budget_id', 'parent_id'])
            ->orderBy('budget_id')
            ->orderByRaw('COALESCE(parent_id, 0)')
            ->orderBy('id')
            ->get();

        $counters = [];

        foreach ($items as $item) {
            $groupKey = $item->budget_id.'|'.($item->parent_id ?? 'root');
            $counters[$groupKey] = ($counters[$groupKey] ?? 0) + 1;

            DB::table('budget_items')
                ->where('id', $item->id)
                ->update([
                    'sort_order' => $counters[$groupKey],
                ]);
        }
    }

    private function recalculateHierarchyTotals(): void
    {
        $budgetIds = DB::table('budget_items')
            ->distinct()
            ->orderBy('budget_id')
            ->pluck('budget_id');

        foreach ($budgetIds as $budgetId) {
            $items = DB::table('budget_items')
                ->where('budget_id', $budgetId)
                ->orderBy('id')
                ->get(['id', 'parent_id', 'quantity', 'unit_price']);

            $itemsById = [];
            $childrenByParent = [];

            foreach ($items as $item) {
                $itemsById[$item->id] = $item;
                $childrenByParent[$item->parent_id ?? 0][] = $item->id;
            }

            $calculatedSubtotals = [];

            $calculateSubtotal = function (int $itemId) use (&$calculateSubtotal, $childrenByParent, $itemsById, &$calculatedSubtotals): float {
                $childIds = $childrenByParent[$itemId] ?? [];

                if ($childIds !== []) {
                    $subtotal = 0.0;

                    foreach ($childIds as $childId) {
                        $subtotal += $calculateSubtotal($childId);
                    }
                } else {
                    $item = $itemsById[$itemId];
                    $subtotal = (float) $item->quantity * (float) $item->unit_price;
                }

                $calculatedSubtotals[$itemId] = round($subtotal, 2);

                return $calculatedSubtotals[$itemId];
            };

            $total = 0.0;

            foreach ($childrenByParent[0] ?? [] as $rootItemId) {
                $total += $calculateSubtotal($rootItemId);
            }

            foreach ($calculatedSubtotals as $itemId => $subtotal) {
                DB::table('budget_items')
                    ->where('id', $itemId)
                    ->update([
                        'subtotal' => $subtotal,
                    ]);
            }

            DB::table('budgets')
                ->where('id', $budgetId)
                ->update([
                    'total_cost' => round($total, 2),
                ]);
        }
    }
};
