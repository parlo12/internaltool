<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TotalContactsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calculate the total cost by summing up the cost field of each contact
        $totalCost = $this->collection->sum('cost');

        return [
            'total_cost' => $totalCost, // Total cost
        ];
    }
}
