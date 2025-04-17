<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
class UserController extends Controller
{
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
}
