<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    protected function uploadFile($file, $subDirPath)
    {
        $filename = md5($file->getContent()) . "." . $file->extension();
        $file->storeAs('public/' . $subDirPath, $filename);
        return "/storage/".$subDirPath.$filename;
    }
}
