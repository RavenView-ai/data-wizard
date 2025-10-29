<?php

namespace App\Http\Controllers;

use App\Models\ExtractionBucket\BucketCreationSource;
use App\Models\ExtractionRun;
use App\Models\File;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Request;
use App\Models\SavedExtractor;
use App\Models\ExtractionBucket;
use App\Jobs\GenerateArtifactJob;
use App\Jobs\GenerateDataJob;
use App\Listeners\AutoExtractArtifactsListener;
use Illuminate\Support\Facades\URL;

/**
 * A controller used to extract data from a file.
 * The endpoint is indepodent based on the state given by the user.
 * It receives an uploaded file, and kicksoff the extraction process.
 */
class ExtractFileController extends Controller
{
	public function __invoke(Request $request, string $extractorId)
	{
        $file = $request->file('file');

        if (!$file && $request->isJson()) {
            $url = $request->json('url');

            if (! $url) {
                return response()->json(['error' => 'Provide a url in a JSON request or a file in a multipart request.'], 400);
            }

            $file = file_get_contents($url);
        }

		if (! $file) {
			return response()->json(['error' => 'File: ' . $file], 400);
		}

        /** @var SavedExtractor $extractor */
        $extractor = SavedExtractor::find($extractorId);

        if (! $extractor) {
            return response()->json(['error' => 'Extractor not found'], 404);
        }

        /** @var ExtractionBucket $bucket */
		$bucket = ExtractionBucket::create([
			'description' => 'Extraction from ' . $file->getClientOriginalName(),
            'created_using' => BucketCreationSource::Api,
		]);

        AutoExtractArtifactsListener::avoidArtifactGenerationForBucket($bucket);

        /** @var File $file */
        $file = $bucket->addMedia($file)->preservingOriginal()->toMediaCollection('files');

        /** @var ExtractionRun $run */
        $run = $bucket->runs()->create([
            'saved_extractor_id' => $extractor->id,
            'started_by_id' => null,
            'target_schema' => $extractor->json_schema,
            'output_instructions' => $extractor->output_instructions,
            'model' => $extractor->model,
            'strategy' => $extractor->strategy,
            'include_text' => $extractor->include_text,
            'include_embedded_images' => $extractor->include_embedded_images,
            'mark_embedded_images' => $extractor->mark_embedded_images,
            'include_page_images' => $extractor->include_page_images,
            'mark_page_images' => $extractor->mark_page_images,
            'chunk_size' => $extractor->chunk_size ?? config('llm-magic.artifacts.default_max_tokens')
        ]);

        Bus::chain([
            new GenerateArtifactJob($bucket, $file),
            new GenerateDataJob($run),
        ])->dispatch();

        return response()->json(ExtractionStatusController::getStatusJson(
            run: $run,
            file: $file
        ));
	}
}
