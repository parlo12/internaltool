<?php

use App\Jobs\PrepareMessageJob;
use App\Jobs\sendSheduledMessages;
use App\Models\Contact;
use App\Models\ScheduledMessages;
use App\Models\Step;
use App\Models\Workflow;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
// Queue Worker 1 for InternalTools
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        //Log::info('Starting InternalTools queue worker 1.');
    })
    ->after(function () {
        //Log::info('InternalTools queue worker 1 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 1 failed.');
    });

// Queue Worker 2 for InternalTools
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        //Log::info('Starting InternalTools queue worker 2.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 2 finished.');
    })
    ->onFailure(function () {
        //  Log::error('InternalTools queue worker 2 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        //Log::info('Starting InternalTools queue worker 3.');
    })
    ->after(function () {
        /// Log::info('InternalTools queue worker 3 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 3 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        // Log::info('Starting InternalTools queue worker 4.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 4 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 4 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        // Log::info('Starting InternalTools queue worker 5.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 5 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 5 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        //  Log::info('Starting InternalTools queue worker 6.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 6 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 6 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        // Log::info('Starting InternalTools queue worker 7.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 7 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 7 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        // Log::info('Starting InternalTools queue worker 8.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 8 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 8 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        // Log::info('Starting InternalTools queue worker 9.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 9 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 9 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        //  Log::info('Starting InternalTools queue worker 10.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 10 finished.');
    })
    ->onFailure(function () {
        //  Log::error('InternalTools queue worker 10 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        //  Log::info('Starting InternalTools queue worker 10.');
    })
    ->after(function () {
        //  Log::info('InternalTools queue worker 10 finished.');
    })
    ->onFailure(function () {
        //   Log::error('InternalTools queue worker 10 failed.');
    });
Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        //  Log::info('Starting InternalTools queue worker 11.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 11 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 11 failed.');
    });


Schedule::command('queue:work --queue=InternalTools --max-time=60 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->before(function () {
        //  Log::info('Starting InternalTools queue worker 12.');
    })
    ->after(function () {
        // Log::info('InternalTools queue worker 12 finished.');
    })
    ->onFailure(function () {
        // Log::error('InternalTools queue worker 12 failed.');
    });

Schedule::call(function () {
    ini_set('memory_limit', '300M');
    $contacts = Contact::whereNull('current_step')
        ->where('created_at', '>=', Carbon::now()->subDay())
        ->get(); // Retrieve all contacts
    $now = Carbon::now();
    foreach ($contacts as $contact) {
        // Initializ current step if it's not set
        $workflow = Workflow::find($contact->workflow_id);
        if ($workflow && $workflow->active) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            $first_step = $steps_flow_array[0] ?? null;

            if ($first_step) {
                $step = Step::find($first_step);
                if ($step) {
                    $contact->update([
                        'current_step' => $first_step,
                        'can_send_after' => $now->copy()->addSeconds($step->delay * 60)->toDateTimeString(),
                    ]);
                }
            }
        }
    }
})->name('process')
    ->everyThreeMinutes()->withoutOverlapping(3)
    ->onFailure(function () {
        Log::error('process-console failed.');
    })
    ->onSuccess(function () {
        Log::info('process-console finished successfully.');
    });


Schedule::call(function () {
    $url = config('app.url'); // Uses the value from config/app.php
    $urls = [
        $url . '/calculate-cost',
    ];

    foreach ($urls as $url) {
        try {
            $response = Http::withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ])->get($url);

            if ($response->successful()) {
                //  Log::info("Successfully called URL: $url");
                sleep(5);
            } else {
                Log::error("Failed to call URL: $url. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Exception occurred while calling URL: $url. Message: " . $e->getMessage());
        }
    }
})->hourly();

Schedule::call(function () {
    // Log::info('Trying to delete files');
    // Absolute path to the uploads directory
    $directory = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads';

    $files = File::files($directory);

    // Iterate through each file
    foreach ($files as $file) {
        try {
            // Check if the file is an MP3 file and older than 4 days
            if ($file->getExtension() == 'mp3') {
                $lastModified = \Carbon\Carbon::createFromTimestamp(File::lastModified($file));

                if ($lastModified->lt(\Carbon\Carbon::now()->subHours(3))) {
                    File::delete($file);  // Delete the file
                    //Log::info('Deleted MP3 file: ' . $file->getFilename());
                }
            }
        } catch (\Exception $e) {
            Log::error('Error deleting file: ' . $file->getFilename() . '. Message: ' . $e->getMessage());
        }
    }
})->hourly();

Schedule::call(function () {
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '256M');
    $steps = Step::where('created_at', '>=', now()->subDays(14))
        ->get();
    foreach ($steps as $step) {
        $workflow = Workflow::find($step->workflow_id);
        $days_of_week = json_decode($step->days_of_week, true);

        if ($workflow != null && $workflow->active) {
            //Log::info("Processing workflow $workflow->id with step $step->id");
            $contacts = DB::table('contacts')
                ->where('response', 'No')
                ->where('can_send', 1)
                ->where('subscribed', 1)
                ->where('current_step', $step->id)
                ->get();
            // foreach ($contacts as $contact) {
            //     Log::info("Got $contact->id of workflow $contact->workflow_id") ;
            // }

            $start_time = $step->start_time ?: '08:00';
            $end_time = $step->end_time ?: '20:00';
            $chunk_size = $step->batch_size ?: '20';
            $interval = (int) $step->batch_delay * 60;
            $contactsChunks = $contacts->chunk($chunk_size);

            $now = Carbon::now();
            $startTime = Carbon::today()->setTimeFromTimeString($start_time);
            $endTime = Carbon::today()->setTimeFromTimeString($end_time);

            if ($now->between($startTime, $endTime)) {
                $startTime = $now;
            } elseif ($now->isAfter($endTime)) {
                $startTime = Carbon::tomorrow()->setTimeFromTimeString($start_time);
                $endTime = Carbon::tomorrow()->setTimeFromTimeString($end_time);
            }

            while (($days_of_week[$startTime->format('l')] ?? 0) == 0) {
                $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
                $endTime = $endTime->addDay();
            }

            foreach ($contactsChunks as $chunk) {
                if ($startTime->greaterThanOrEqualTo($endTime)) {
                    do {
                        $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
                        $endTime = $endTime->addDay();
                    } while (($days_of_week[$startTime->format('l')] ?? 0) == 0);
                }

                $dispatchTime = $startTime->copy();
                foreach ($chunk as $contact) {
                    // Log::info("Dispatching PrepareMessageJob for contact $contact->id of workflow $contact->workflow_id");
                    // Check if the job already exists in the queue
                    $existingJob = DB::table('jobs')
                        ->where('payload', 'like', '%PrepareMessageJob%')
                        ->where('payload', 'like', "%{$contact->uuid}%")
                        ->exists();
                    // Dispatch a job to prepare the message without making third-party requests here
                    if (!$existingJob) {

                        PrepareMessageJob::dispatch(
                            $contact->uuid,
                            $workflow->group_id,
                            $workflow->godspeedoffers_api,
                            $step,
                            $contact,
                            $dispatchTime
                        );
                        $contact = Contact::find($contact->id);
                        $contact->can_send = 0;
                        $contact->status = 'Waiting_For_Queau_Job';
                        $contact->save();
                    } else {
                        Log::info("This job exists, skipping");
                    }
                }

                $startTime->addSeconds($interval);

                while (($days_of_week[$startTime->format('l')] ?? 0) == 0) {
                    $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
                    $endTime = $endTime->addDay();
                }
            }
        }
    }
})->name('prepare-messages')
    ->everyThreeMinutes()->withoutOverlapping(3)
    ->onFailure(function () {
        Log::error('prepare-messages-console failed.');
    })
    ->onSuccess(function () {
        Log::info('prepare-messages-console finished successfully.');
    });

Schedule::call(function () {
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '256M');
    $expiredMessages = ScheduledMessages::where('dispatch_time', '<', Carbon::now())->get();
    foreach ($expiredMessages as $message) {
        $contact = Contact::find($message->contact_id);
        if (!$contact) {
            Log::error("Contact with ID {$message->contact_id} not found for scheduled message.");
            continue;
        }
        $workflow = Workflow::find($message->workflow_id);
        if (!$workflow) {
            Log::error("Workflow with ID {$message->workflow_id} not found for scheduled message.");
            continue;
        }
        Log::info("Sending scheduled message to contact ID {$contact->id} in workflow ID {$workflow->id}.");
        sendSheduledMessages::dispatch(
            $contact->phone,
            $message->content,
            $workflow->id,
            $contact->id,
            $contact->organisation_id,
            $message->id,
            $message->type

        );
    }
})->name('send_ready_messages')
    ->everyThreeMinutes()->withoutOverlapping(3)
    ->onFailure(function () {
        Log::error('send_ready_messages-console failed.');
    })
    ->onSuccess(function () {
        Log::info('send_ready_messages-console finished successfully.');
    });
