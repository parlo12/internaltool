<?php
// namespace App\Console\Commands;

// use Illuminate\Support\Facades\Cache;
// use Illuminate\Console\Command;
// use ElephantIO\Client;
// use Illuminate\Support\Facades\Log;

// class WebInternal extends Command
//{
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    // protected $signature = 'app:websocket-internal';

    /**
    * The console command description.
    *
    * @var string
    */
    // protected $description = 'Connects to WebSocket API and handles outgoing messages';

    /**
    * Execute the console command.
    */
    // public function handle()
    // {
    //     $url = 'https://coral-app-cazak.ondigitalocean.app/?apiKey=07c457bace117bc59709c69644821394';
    //     $client = null;

    //     while (true) {
    //         try {
                // Reconnect if client is not connected
                // if (!$client || !$this->isClientConnected($client)) {
                //     Log::info('Connecting to WebSocket API...');
                //     $client = Client::create($url);
                //     $client->connect();
                //     Log::info('Connected to WebSocket API');
                // }

                // Check for outgoing messages in the cache
                // if ($message = Cache::pull('outgoingSMS')) {
                //     $message = json_decode($message, true);

                //     Log::info('Sending message:', [
                //         'deviceId' => $message['device_id'],
                //         'receiver' => $message['phone'],
                //         'content'  => $message['message'],
                //     ]);

                    // Emit the outgoing message
                //     $client->emit('outgoingSMS', [
                //         'deviceId' => $message['device_id'],
                //         'receiver' => $message['phone'],
                //         'content'  => $message['message'],
                //     ]);
                // }

                // Keep connection alive by sending periodic ping messages
                // $client->emit('ping', ['timestamp' => time()]);

                // Wait to prevent excessive looping
            //     sleep(2);

            // } catch (\Exception $e) {
            //     Log::error('WebSocket error: ' . $e->getMessage());

            //     // Force reconnection on failure
            //     if ($client) {
            //         try {
            //             $client->disconnect();
            //             Log::info('WebSocket connection closed');
            //         } catch (\Exception $disconnectError) {
            //             Log::error('Error closing WebSocket connection: ' . $disconnectError->getMessage());
            //         }
            //     }

                // Short delay before retrying connection
    //             sleep(5);
    //         }
    //     }
    // }

    /**
     * Checks if WebSocket client is still connected
     */
//     private function isClientConnected($client)
//     {
//         try {
//             $client->emit('ping', ['check' => true]);
//             return true;
//         } catch (\Exception $e) {
//             return false;
//         }
//     }
// }
