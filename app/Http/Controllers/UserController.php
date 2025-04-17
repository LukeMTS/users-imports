<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadUsersRequest;
use App\Jobs\ImportUsersJob;
use App\Models\User;
use App\Services\UsersCsvValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $csvValidator;

    public function __construct(UsersCsvValidator $csvValidator)
    {
        $this->csvValidator = $csvValidator;
    }

    public function index(): JsonResponse
    {
        $users = User::select('name', 'email', 'birthdate')->simplePaginate(15);

        if ($users->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => __('validation.custom.get_users.empty'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('validation.custom.get_users.success'),
            'data' => $users,
        ]);
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
            collect($users)
                ->chunk(500)
                ->each(fn ($chunk) => ImportUsersJob::dispatch($chunk->toArray()));

            return response()->json([
                'success' => true,
                'message' => __('validation.import_file.success'),
            ]);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => __('validation.import_file.error'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
