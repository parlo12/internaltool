<?php

namespace App\Http\Resources;

use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TextSentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if(Contact::find($this->contact_id)){
            $phone=Contact::find($this->contact_id)->phone;
        }else{
            $phone="N/A";
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cost' => $this->cost,
            'phone' => $phone,
            'zipcode'=>$this->zipcode,
            'state'=>$this->state,
            'city'=>$this->city,
            'response'=>$this->response,
            'marketing_channel'=>$this->marketing_channel,
            'sending_number' => $this->sending_number,
            'created_at' => (new Carbon($this->created_at))->format('Y-m-d'),
        ];
    }
}
