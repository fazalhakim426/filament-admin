<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Manzil API Documentation",
 *     version="1.0.0",
 *     description="API documentation for the e-commerce platform designed for suppliers, resellers, and customers in Pakistan to sell and manage their products.",
 *     @OA\Contact(
 *         email="support@manzil.com"
 *     )
 * ) 
 * 
 * @OA\Server(
 *     url="http://manzil.test/api/",
 *     description="Local server"
 * )
 */
class OpenApiConfig
{
    // Additional configuration or methods can be added here if necessary.
}
