<?php
/**
 * Created by 广州轻快科技有限公司.
 * Author: Jaden
 * Time: 2018/6/20-上午11:15
 * Description:
 * Version: v1.0
 */
namespace Org\Jaden\WxPay;

use Illuminate\Support\ServiceProvider;
use Org\Jaden\WxPay\Pay\NativePay;
use Org\Jaden\WxPay\Payment\JsPay;

class WxPayServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/wxpay.php' => config_path('wxpay.php'),
        ]);
    }
    public function register()
    {
        $this->app->singleton('WXPay', function () {
            return new NativePay();
        });

        $this->app->singleton('WXJs',function ()
        {
           return new JsPay();
        });
    }
}