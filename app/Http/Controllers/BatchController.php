<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOrder;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Bus\Batch;
use App\Notifications\BatchCompleted;
use Illuminate\Support\Facades\Bus;

class BatchController extends Controller
{
    public function startBatch(Request $request)
    {  
        $orderCount = $request->input('order_count');
        
        $user = $request->user();
        $seconds = $request->input("seconds", 0);

        $batch = Bus::batch([])->dispatch();
        for ($i = 0; $i < $orderCount; $i++) {
            $batch->add(new ProcessOrder($seconds));
        }
            
        return response()->json([
            'batchId' => $batch->id,
            'message' => 'Batch created successfully',
        ], 200);
    }

    public function batchStatus($batchId)
    {
        $batch = Bus::findBatch($batchId);

        if(!$batch) {
            return response()->json([
                'message' => 'Batch not found',
            ], 404);
        }

        return response()->json([
            'batchId' => $batch->id,
            'isFinished' => $batch->finished(),
            'progress' => $batch->progress(),
            'completedJobs' => $batch->processedJobs(),
            'totalJobs' => $batch->totalJobs,
            'failedJobs' => $batch->failedJobs,
        ]);
    }

    private function sendNotification(Batch $batch, User $user): void
    {
        $successfulJobs = $batch->processedJobs();
        $failedJobs = $batch->failedJobs;

        $user->notify(new BatchCompleted($successfulJobs, $failedJobs));
    }
}
