<?php
/**
 * Created by 广州轻快科技有限公司.
 * Author: Jaden
 * Time: 2018/6/20-上午11:11
 * Description:
 * Version: v1.0
 */
namespace Org\Jaden\WxPay\Exception;

use Exception;
use Throwable;

/**
 * 微信支付错误类
 * Class WxPayException
 */
class WxPayException extends Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}