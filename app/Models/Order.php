<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\
{
    BelongsTo,
    HasMany,
};
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'notes',
    ];

    # START BOOTSTRAP
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            // Only set user_id if not already set and user is authenticated
            if (!$order->user_id && auth()->check()) {
                $order->user_id = auth()->id();
            }
        });
    }
    # END BOOTSTRAP

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total_amount' => 'decimal:2',
        ];
    }


    # START RELATIONS
    /**
     * Get the user that owns the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    # END RELATIONS

    # START METHODS
    public function hasPayments(): bool
    {
        return $this->payments()->exists();
    }
    # END METHODS

    # START SCOPES
    public function scopeByStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    # END SCOPES
}
