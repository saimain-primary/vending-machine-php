<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiResponse
{
    protected function respondOk(string $message, mixed $data = null): JsonResponse
    {
        return $this->buildPayload($message, $data, 200);
    }

    protected function respondCreated(string $message, mixed $data = null): JsonResponse
    {
        return $this->buildPayload($message, $data, 201);
    }

    protected function respondNoContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function respondWithResource(JsonResource $resource, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resource->toArray(request()),
        ], $status);
    }

    protected function respondWithCollection(AnonymousResourceCollection $resource, string $message): JsonResponse
    {
        $inner = json_decode($resource->toResponse(request())->getContent(), true);

        return response()->json(array_filter([
            'success' => true,
            'message' => $message,
            'data' => $inner['data'] ?? [],
            'meta' => $inner['meta'] ?? null,
            'links' => $inner['links'] ?? null,
        ], fn ($v) => $v !== null));
    }

    private function buildPayload(string $message, mixed $data, int $status): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }
}
