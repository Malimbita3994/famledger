<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    protected function success($data = null, int $code = 200): JsonResponse
    {
        return response()->json($data ?? ['message' => 'OK'], $code);
    }

    protected function error(string $message, int $code = 422): JsonResponse
    {
        return response()->json(['message' => $message], $code);
    }
}
