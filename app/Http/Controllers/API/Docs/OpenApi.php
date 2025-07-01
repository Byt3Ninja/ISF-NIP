<?php

namespace App\Http\Controllers\API\Docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="My Laravel API",
 *     version="1.0.0",
 *     description="This is the API documentation for the Laravel project.",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */
class OpenApi {}
