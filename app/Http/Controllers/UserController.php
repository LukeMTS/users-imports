<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadUsersRequest;
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
        $users = User::paginate(10);

        return response()->json($users);
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

            User::insert($users);

            return response()->json([
                'success' => true,
                'message' => __('validation.import_file.success'),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => __('validation.import_file.error'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
