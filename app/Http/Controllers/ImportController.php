<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadUsersRequest;
use App\Jobs\ImportUsersJob;
use App\Models\ImportsInfo;
use App\Services\UsersCsvValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    protected $csvValidator;

    public function __construct(UsersCsvValidator $csvValidator)
    {
        $this->csvValidator = $csvValidator;
    }

    public function upload(UploadUsersRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $response = $this->csvValidator->validate($file);

            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'message' => __('validation.import_file.error'),
                    'data' => $response['errors'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $users = $response['data'];

            if (empty($users)) {
                return response()->json([
                    'success' => false,
                    'message' => __('validation.import_file.empty'),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $file->store();

            // Importa os usuários em lotes de 500
            $importsIds = [];
            collect($users)
                ->chunk(500)
                ->each(function ($chunk) use (&$importsIds) {
                    $importInfo = ImportsInfo::create([
                        'name' => ImportUsersJob::class,
                        'status' => ImportsInfo::STATUS_PENDING,
                    ]);

                    ImportUsersJob::dispatch($chunk->toArray(), $importInfo->id);

                    $importsIds[] = $importInfo->id;
                });

        
            return response()->json([
                'success' => true,
                'message' => __('validation.import_file.success'),
                'data' => $importsIds,
            ]);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => __('validation.import_file.error'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getStatus(int $id): JsonResponse
    {
        $import = ImportsInfo::find($id);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => __('validation.import_status.not_found'),
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => __('validation.import_status.' . $import->status),
            'data' => $import,
        ]);
    }
}
