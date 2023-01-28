<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * @OA\Info(title="My First API", version="0.1")
     */
//    /**
//     * @OA\Info(
//     *      version="1.0.0",
//     *      title=" OpenApi Documentation",
//     *      description=" Swagger OpenApi description",
//     *      @OA\Contact(
//     *          email="admin@admin.com"
//     *      ),
//     *      @OA\License(
//     *          name="Apache 2.0",
//     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
//     *      )
//     * )
//     *
//     * @OA\Server(
//     *      url=L5_SWAGGER_CONST_HOST,
//     *      description=" API Server"
//     * )
//
//     *
//     * @OA\Tag(
//     *     name="Projects",
//     *     description=" Api Endpoints"
//     * )
//     * @OA\Schemes(format="http")
//     * @OAS\SecurityScheme(
//     *      securityScheme="bearer_token",
//     *      type="http",
//     *      scheme="bearer"
//     * )
//     */
}
