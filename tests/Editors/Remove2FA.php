<?php

namespace Yajra\DataTables\Tests\Editors;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Remove2FA
{
    public function handle(Request $request): JsonResponse
    {
        return new JsonResponse([
            'message' => '2FA has been removed successfully.',
        ]);
    }
}
