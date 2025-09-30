<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'order' => $this->whenLoaded('order', fn() => OrderResource::make($this->order)),
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'amount' => $this->amount,
            'meta' => $this->meta,
            'transaction_id' => $this->transaction_id,
            'gateway_response' => $this->gateway_response,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
