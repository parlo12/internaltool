<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReportResource extends JsonResource
{

    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_name' => $this->group_name,
            'call_status' => $this->call_status,
            'call_sid' => $this->call_sid,
            'phone' => $this->phone,
            'contact_name'=>$this->contact_name,
            'campaign_id'=>$this->campaign_id,
            'created_at' => (new Carbon($this->created_at))->format('Y-m-d')
        ];
    }
}
