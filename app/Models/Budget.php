<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Budget extends Model
{
    use HasFactory;

    public const CODE_PREFIX = 'BGT-';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CANCELLED = 'cancelled';

    protected static function booted(): void
    {
        static::created(function (self $budget): void {
            if ($budget->code !== null && $budget->code !== '') {
                return;
            }

            $budget->forceFill([
                'code' => self::generateCodeForId($budget->id),
            ])->saveQuietly();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'title',
        'description',
        'budget_date',
        'status',
        'is_published',
        'total_cost',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budget_date' => 'date',
            'is_published' => 'boolean',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function generateCodeForId(int $id): string
    {
        return self::CODE_PREFIX.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function budgetItems(): HasMany
    {
        return $this->items();
    }

    public function rootItems(): HasMany
    {
        return $this->items()->rootItems()->ordered();
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_published', true)
            ->where('status', self::STATUS_PUBLISHED);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_published && $this->status === self::STATUS_PUBLISHED;
    }

    public function recalculateTotalCost(): void
    {
        $items = $this->items()->ordered()->get();
        $itemsByParent = $items->groupBy(fn (BudgetItem $item) => $item->parent_id ?? 0);

        $total = $itemsByParent
            ->get(0, collect())
            ->sum(fn (BudgetItem $item) => $this->calculateItemSubtotal($item, $itemsByParent));

        $this->forceFill([
            'total_cost' => round($total, 2),
        ])->saveQuietly();
    }

    /**
     * @param  Collection<int|string, Collection<int, BudgetItem>>  $itemsByParent
     * @param  array<int, bool>  $path
     */
    private function calculateItemSubtotal(BudgetItem $item, Collection $itemsByParent, array $path = []): float
    {
        if (isset($path[$item->id])) {
            return 0.0;
        }

        $path[$item->id] = true;
        $children = $itemsByParent->get($item->id, collect());

        if ($children->isNotEmpty()) {
            $subtotal = $children->sum(
                fn (BudgetItem $child) => $this->calculateItemSubtotal($child, $itemsByParent, $path)
            );
        } else {
            $subtotal = (float) $item->quantity * (float) $item->unit_price;
        }

        $subtotal = round($subtotal, 2);

        if ((float) $item->subtotal !== $subtotal) {
            $item->forceFill([
                'subtotal' => $subtotal,
            ])->saveQuietly();
        }

        return $subtotal;
    }
}
