<?php

namespace App\Http\Controllers;

use App\Models\ExtractionBucket\BucketCreationSource;
use App\Models\File;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Request;
use App\Models\SavedExtractor;
use App\Models\ExtractionBucket;
use App\Models\ExtractionRun;
use Illuminate\Support\Facades\URL;

/**
 * Get the status of an extraction run.
 */
class ExtractionStatusController extends Controller
{
	public function __invoke(Request $request, string $extractorId, string $runId)
	{
		$run = ExtractionRun::query()
            ->with(['saved_extractor', 'bucket'])
            ->find($runId);


		if (! $run) {
			return response()->json(['error' => 'Run not found'], 404);
		}

		$bucket = $run->bucket;

		if (! $bucket) {
			return response()->json(['error' => 'Bucket not found'], 404);
		}

		$file = $bucket->files()->first();

		if (! $file) {
			return response()->json(['error' => 'File not found'], 404);
		}

        return response()->json(static::getStatusJson(
            run: $run,
            file: $file,
        ));
	}

    public static function getStatusJson(ExtractionRun $run, File $file): array
    {
        return array_filter([
            'error' => $run->error,
            'run' => [
                'id' => $run->id,
                'status' => $run->status,
                'status_url' => URL::signedRoute('api.extractors.runs.status', [
                    'extractorId' => $run->saved_extractor->id,
                    'runId' => $run->id,
                ], expiration: now()->addDay()),
                'secret' => $run->getRunSecret(),
                'data' => $run->data ?? $run->partial_data,
                'steps' => [
                    'completed' => $run->getCompletedSteps(),
                    'estimated' => $run->getEstimatedSteps(),
                ],
                'token_stats' => $run->getEnrichedTokenStats()?->toArray(),
            ],
            'bucket' => [
                'id' => $run->bucket->id,
            ],
            'file' => [
                'id' => $file->id,
                'status' => $file->artifact_status
            ],
        ]);
    }
}
