<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Bus\Batchable;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $seconds = 0)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $timeToSleep = $this->seconds > 0 ? $this->seconds : rand(1, 5);
        sleep($timeToSleep);        
    }
}
