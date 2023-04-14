<?php


namespace App\Traits;

use App\Exceptions\BusinessException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


trait AdminResponse
{
    /**
     * 成功
     * @param null $data
     * @param array $codeResponse
     * @return JsonResponse
     */
    public function success($msg, $data = null): JsonResponse
    {
        return $this->jsonResponse(1, $msg, $data, null);
    }

    /**
     * 失败
     * @param array $codeResponse
     * @param null $data
     * @param null $error
     * @return JsonResponse
     */
    public function fail($msg, $data = null): JsonResponse
    {
        return $this->jsonResponse(0, $msg, $data);
    }

    public function error($name, $data = null): JsonResponse
    {
        return $this->jsonResponse(0, $name . "错误", $data);
    }


    public function executeSuccess($msg, $data = null): JsonResponse
    {
        return $this->success($msg . "成功", $data);
    }

    public function executeFail($msg, $data = null): JsonResponse
    {
        return $this->fail($msg . "失败", $data);
    }

    /**
     * json响应
     * @param $status
     * @param $codeResponse
     * @param $data
     * @param $error
     * @return JsonResponse
     */
    private function jsonResponse($status, $message, $data = null): JsonResponse
    {
        $admin = auth("admin")->user();
        $token = "";
        if ($admin != null) {
            try{
                $token = auth("admin")->setTTL(15)->tokenById($admin->id);
            }catch (\Exception $e){
                var_dump($e);
            }
        }
        return response()->json([
            'status' => $status,
            'info' => $message,
            'result' => $data,
        ])->withHeaders(["Authorization" => $token]);
    }


    /**
     * 成功分页返回
     * @param $page array
     * @return JsonResponse
     */
    protected function successPaginate($page): JsonResponse
    {
        return $this->success($this->paginate($page));
    }

    private function paginate($page)
    {
        if ($page instanceof LengthAwarePaginator) {
            return [
                'total' => $page->total(),
                'page' => $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $page->lastPage(),
                'list' => $page->items()
            ];
        }
        if ($page instanceof Collection) {
            $page = $page->toArray();
        }
        if (!is_array($page)) {
            return $page;
        }
        $total = count($page);
        return [
            'total' => $total, //数据总数
            'page' => 1, // 当前页码
            'limit' => $total, // 每页的数据条数
            'pages' => 1, // 最后一页的页码
            'list' => $page // 数据
        ];
    }

    /**
     * 业务异常返回
     * @param array $codeResponse
     * @param string $info
     * @throws BusinessException
     */
    public function throwBusinessException(array $codeResponse = ResponseEnum::HTTP_ERROR, string $info = '')
    {
        throw new BusinessException($codeResponse, $info);
    }
}

