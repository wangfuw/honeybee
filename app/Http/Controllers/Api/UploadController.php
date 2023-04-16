<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class UploadController extends BaseController
{
    /**
     * 上传身份证
     * @param Request $request
     * @return mixed
     */
    public function uploadCard(Request $request)
    {
        $file = $request->file('image');
        $path = $this->uploadFile($file, "cards");
        return $this->success("success", ["path" => $path]);
    }

    /**
     * 上传头像
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadHeader(Request $request)
    {
        $file = $request->file('image');
        $path = $this->uploadFile($file, "headers");
        return $this->success("success", ["path" => $path]);
    }

//    public function uploadMany(Request $request)
//    {
//        foreach ($_FILES as $k => $v) {
//            $file = $request->file($k);
//            try {
//                $path = $this->uploadFile($file, "banners");
//                $scavenge[] = ["url" => config('app.url') . $path];
//            } catch (\Exception $exception) {
//                return response()->json([
//                    'error' => 1
//                ]);
//            }
//        }
//        return response()->json([
//            'errno' => 0, 'data' => $scavenge
//        ]);
//    }
}
