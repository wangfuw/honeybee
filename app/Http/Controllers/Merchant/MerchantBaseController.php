<?php
namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Traits\AdminResponse;
use App\Traits\MerchantResponse;

class MerchantBaseController extends Controller {
    use MerchantResponse;

    protected $page = 1;
    protected $size = 10;
}
