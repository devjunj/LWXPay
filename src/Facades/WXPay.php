<?php
/**
 * Created by 广州轻快科技有限公司.
 * Author: Jaden
 * Time: 2018/6/20-下午5:18
 * Description:
 * Version: v1.0
 */

namespace Org\Jaden\WxPay\Facades;
use Illuminate\Support\Facades\Facade;

class WXPay extends Facade
{

    protected static function getFacadeAccessor()
    {
        return "WXPay";
    }
}