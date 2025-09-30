<?php

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notes' => $this->notes,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', fn() => UserResource::make($this->user)),
            'items' => $this->whenLoaded('orderItems', fn() => OrderItemResource::collection($this->orderItems)),
            'payments' => $this->whenLoaded('payments', fn() => PaymentResource::collection($this->payments)),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
