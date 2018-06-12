<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{

       /**
 * @SWG\Swagger(
 *     basePath="/api/v1",
 *     schemes={"http", "https"},
 *     host=L5_SWAGGER_CONST_HOST,
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="RoomFinder",
 *         description="RoomFinder API description",
 *         @SWG\Contact(
 *             email="manis6062@gmail.com"
 *         ),
 *     )
 * )
 */

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
