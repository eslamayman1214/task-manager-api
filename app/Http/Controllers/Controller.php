<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Util\HttpStatusCodeUtil;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function __construct(Request $request)
    {
        App::setlocale($request->header('Content-Language', 'en'));
    }

    protected function response($payload, int $statusCode, string $message = '')
    {
        if (empty($message)) {
            switch ($statusCode) {
                case HttpStatusCodeUtil::OK:
                    $message = '';
                    break;
                case HttpStatusCodeUtil::CREATED:
                    $message = trans('common.resource_added_successfully');
                    break;
                case HttpStatusCodeUtil::UPDATED:
                    $message = trans('common.resource_updated_successfully');
                    break;
                case HttpStatusCodeUtil::INTERNAL_SERVER_ERROR:
                    $message = trans('common.something_went_wrong');
                    break;
                case HttpStatusCodeUtil::UNAUTHORIZED:
                    $message = trans('common.unauthorised');
                    break;
                default:
                    $message = null;
                    break;
            }
        }

        $response = [
            'version' => $this->getVersion(),
            'data' => $payload,
            'code' => $statusCode,
        ];
        $response['message'] = $message;

        return response()->json($response, $statusCode, []);
    }

    protected function unauthorised()
    {
        $response = [
            'version' => $this->getVersion(),
            'data' => [
                'errors' => [],
            ],
            'code' => HttpStatusCodeUtil::UNAUTHORIZED,
            'message' => 'Unauthorized',
        ];
        throw new HttpResponseException(response()->json($response, HttpStatusCodeUtil::UNAUTHORIZED));
    }

    protected function formatPaginationData(object $data, object $paginator): array
    {
        return [
            'items' => $data,
            'pagination' => [
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'nextPage' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
                'previousPage' => $paginator->currentPage() > 1 ? $paginator->currentPage() - 1 : null,
            ]
        ];
    }

    private function getVersion()
    {
        $uri = request()->getRequestUri();

        if (preg_match('/\/v(\d+)\//', $uri, $matches)) {
            return (int)$matches[1];
        }

        return 1;
    }
}
