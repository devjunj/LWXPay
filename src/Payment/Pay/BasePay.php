<?php
/**
 * Created by 广州轻快科技有限公司.
 * Author: Jaden
 * Time: 2018/6/20-下午2:37
 * Description:
 * Version: v1.0
 */
namespace Org\Jaden\WxPay\Pay;

use Illuminate\Support\Facades\Config;
use Org\Jaden\WxPay\Payment\Entity\Report;
use Org\Jaden\WxPay\Payment\Entity\Results;
use Org\Jaden\WxPay\Exception\WxPayException;

abstract class BasePay implements PayInterface
{

    /**
     *
     * 支付结果通用通知
     * @param function $callback
     * 直接回调函数使用方法: notify(you_function);
     * 回调类成员函数方法:notify(array($this, you_function));
     * $callback  原型为：function function_name($data){}
     */
    public  function notify($callback, &$msg)
    {
        //获取通知的数据
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA'])?$GLOBALS['HTTP_RAW_POST_DATA']:'';
        //如果返回成功则验证签名
        try {
            $result = Results::Init($xml);
        } catch (WxPayException $e){
            $msg = $e->errorMessage();
            return false;
        }

        return call_user_func($callback, $result);
    }


    /**
     * 直接输出xml
     * @param string $xml
     */
    public  function replyNotify($xml)
    {
        echo $xml;
    }

    /**
     * 上报数据， 上报的时候将屏蔽所有异常流程
     * @param string $url
     * @param int $startTimeStamp
     * @param array $data
     */
    protected  function reportCostTime($url, $startTimeStamp, $data)
    {
        //如果不需要上报数据
        $reportLevel = Config::get('wxpay.report_level');
        if($reportLevel == 0){
            return;
        }
        //如果仅失败上报
        if($reportLevel == 1 &&
            array_key_exists("return_code", $data) &&
            $data["return_code"] == "SUCCESS" &&
            array_key_exists("result_code", $data) &&
            $data["result_code"] == "SUCCESS")
        {
            return;
        }

        try{
        //上报逻辑
        $endTimeStamp = self::getMillisecond();
        $objInput = new Report();
        $objInput->SetInterface_url($url);
        $objInput->SetExecute_time_($endTimeStamp - $startTimeStamp);
        //返回状态码
        if(array_key_exists("return_code", $data)){
            $objInput->SetReturn_code($data["return_code"]);
        }
        //返回信息
        if(array_key_exists("return_msg", $data)){
            $objInput->SetReturn_msg($data["return_msg"]);
        }
        //业务结果
        if(array_key_exists("result_code", $data)){
            $objInput->SetResult_code($data["result_code"]);
        }
        //错误代码
        if(array_key_exists("err_code", $data)){
            $objInput->SetErr_code($data["err_code"]);
        }
        //错误代码描述
        if(array_key_exists("err_code_des", $data)){
            $objInput->SetErr_code_des($data["err_code_des"]);
        }
        //商户订单号
        if(array_key_exists("out_trade_no", $data)){
            $objInput->SetOut_trade_no($data["out_trade_no"]);
        }
        //设备号
        if(array_key_exists("device_info", $data)){
            $objInput->SetDevice_info($data["device_info"]);
        }

        self::report($objInput);
        } catch (WxPayException $e){
            //不做任何处理
        }
    }

    /**
     * 以post方式提交xml到对应的接口url
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @throws WxPayException
     * @return mixed
     */
    protected  function postXmlCurl($xml, $url)
    {
        $timeout = Config::get('wxpay.timeout');
        $sslcert = Config::get('wxpay.ssl_cert');
        $sslkey = Config::get('wxpay.ssl_key');
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $proxy_host = Config::get("wxpay.curl_proxy_host");
        $proxy_port = Config::get("wxpay.curl_proxy_port");
        //如果有配置代理这里就设置代理
        if($proxy_host != "0.0.0.0"
            && $proxy_port != 0){
            curl_setopt($ch,CURLOPT_PROXY, $proxy_host);
            curl_setopt($ch,CURLOPT_PROXYPORT, $proxy_port);
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if(!empty($sslcert)||!empty($sslkey)){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $sslcert);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $sslkey);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }

    /**
     * 获取毫秒级别的时间戳
     */
    public static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }
}