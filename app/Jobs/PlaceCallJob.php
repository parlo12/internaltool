<?php

namespace App\Jobs;
use App\Http\Controllers\callController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PlaceCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $voiceRecording;
    protected $report_id;
    protected $detection_duration;
    protected $agent_phone_number;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phone, $voiceRecording,$report_id,$agent_phone_number,$detection_duration)
    {
        $this->phone = $phone;
        $this->voiceRecording = $voiceRecording;
        $this->report_id=$report_id;
        $this->detection_duration=$detection_duration;
        $this->agent_phone_number=$agent_phone_number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(callController $controller)
    {
        $controller->place_call($this->phone, $this->voiceRecording,$this->report_id,$this->agent_phone_number,$this->detection_duration);
    }
}
