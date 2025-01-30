<?php

namespace App\Http\Resources;

use App\Models\Step;
use App\Models\Workflow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        $step_name='';
        if($this->current_step){
            $step=Step::find($this->current_step);
            if($step){
            $step_name=$step->name;
            }
        }else{
            $step_name="WORKLOW_NOT_STARTED";
        }
       
        return [
            'id' => $this->id,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'response' => $this->response,
            'current_step'=>$step_name,
            'status'=>$this->status,
            'created_at' => (new Carbon($this->created_at))->format('Y-m-d H:i a'),
            'can_send_after' => (new Carbon($this->can_send_after))->format('l d-m-Y h:i a'),
            'valid_lead' => $this->valid_lead,
            'offer_made' => $this->offer_made,
            'contract_executed'=>$this->contract_executed,
            'deal_closed'=>$this->deal_closed,
            'email'=>$this->email
            //'current_step'=>$this->current_step
        ];
    }
}
