<?php
/**
 * Created by 广州轻快科技有限公司.
 * Author: Jaden
 * Time: 2018/6/20-下午2:38
 * Description:
 * Version: v1.0
 */

namespace Org\Jaden\WxPay\Pay;

use Org\Jaden\WxPay\Exception\WxPayException;
use Org\Jaden\WxPay\Payment\Entity\BizPayUrl;
use Org\Jaden\WxPay\Payment\Entity\CloseOrder;
use Org\Jaden\WxPay\Payment\Entity\DownloadBill;
use Org\Jaden\WxPay\Payment\Entity\MicroPay;
use Org\Jaden\WxPay\Payment\Entity\OrderQuery;
use Org\Jaden\WxPay\Payment\Entity\Refund;
use Org\Jaden\WxPay\Payment\Entity\RefundQuery;
use Org\Jaden\WxPay\Payment\Entity\Report;
use Org\Jaden\WxPay\Payment\Entity\Results;
use Org\Jaden\WxPay\Payment\Entity\Reverse;
use Org\Jaden\WxPay\Payment\Entity\ShortUrl;
use Org\Jaden\WxPay\Payment\Entity\UnifiedOrder;

class NativePay extends BasePay
{

    /**
     * 统一下单接口
     * @param UnifiedOrder $order 订单参数
     * @return mixed
     * @throws WxPayException
     */
    public function unifiedOrder(UnifiedOrder $order)
    {
        $url = $order->host."/pay/unifiedorder";
        $order->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
        //$inputObj->SetSpbill_create_ip("1.1.1.1");
        //签名
        $order->SetSign();
        $xml = $order->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;
    }

    /**
     *
     * 查询订单，OrderQuery中out_trade_no、transaction_id至少填一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param OrderQuery $query
     * @return mixed
     * @throws WxPayException
     */
    public function orderQuery(OrderQuery $query)
    {
        $url = $query->host."/pay/orderquery";
        //检测必填参数
        $query->SetSign();//签名
        $xml = $query->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;
    }

    /**
     * 关闭订单,CloseOrder中out_trade_no必填
     * @param CloseOrder $order
     * @return mixed
     * @throws WxPayException
     */
    public function closeOrder(CloseOrder $order)
    {
        $url = $order->host."/pay/closeorder";
        $order->SetSign();//签名
        $xml = $order->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;

    }

    /**
     * 申请退款,Refund中out_trade_no、transaction_id至少填一个
     * @param Refund $refund
     * @return mixed
     * @throws WxPayException
     */
    public function refund(Refund $refund)
    {
        $url = $refund->host."/pay/refund";
        $refund->SetSign();//签名
        $xml = $refund->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;
    }

    /**
     * 退款查询,RefundQuery中out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个
     * @param RefundQuery $query
     * @return mixed
     * @throws WxPayException
     */
    public function refundQuery(RefundQuery $query)
    {
        $url = $query->host."/pay/refundquery";
        $query->SetSign();//签名
        $xml = $query->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;
    }

    /**
     * 下载对账单，WxPayDownloadBill中bill_date为必填参数
     * @param DownloadBill $bill
     * @return mixed
     * @throws WxPayException
     */
    public function downloadBill(DownloadBill $bill)
    {
        $url = $bill->host."/pay/downloadbill";
        $bill->SetSign();//签名
        $xml = $bill->ToXml();
        $response = self::postXmlCurl($xml, $url);
        if(substr($response, 0 , 5) == "<xml>"){
            return "";
        }
        return $response;
    }

    /**
     * 提交被扫支付API
     * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
     * 由商户收银台或者商户后台调用该接口发起支付。
     * PayMicroPay中body、out_trade_no、total_fee、auth_code参数必填
     * @param MicroPay $microPay
     * @return mixed
     * @throws WxPayException
     */
    public function micropay(MicroPay $microPay)
    {
        $url = $microPay->host."/pay/micropay";
        $microPay->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
        $microPay->SetSign();//签名
        $xml = $microPay->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /**
     * 撤销订单API接口，WxPayReverse中参数out_trade_no和transaction_id必须填写一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param Reverse $reverse
     * @return mixed
     * @throws WxPayException
     */
    public function reverse(Reverse $reverse)
    {
        $url = $reverse->host."/pay/reverse";
        $reverse->SetSign();//签名
        $xml = $reverse->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
    }

    /**
     * 测速上报，该方法内部封装在report中，使用时请注意异常流程
     * WxPayReport中interface_url、return_code、result_code、user_ip、execute_time_必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param Report $report
     * @return mixed
     * @throws WxPayException
     */
    public function report(Report $report)
    {
        $url = $report->host."/payitil/report";
        $report->SetUser_ip($_SERVER['REMOTE_ADDR']);//终端ip
        $report->SetTime(date("YmdHis"));//商户上报时间
        $report->SetSign();//签名
        $xml = $report->ToXml();
        $response = self::postXmlCurl($xml, $url);
        return $response;
    }

    /**
     * 生成二维码规则,模式一生成支付二维码
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param BizPayUrl $bizPayUrl
     * @return mixed
     */
    public function bizpayurl(BizPayUrl $bizPayUrl)
    {
        $bizPayUrl->SetTime_stamp(time());//时间戳
        $bizPayUrl->SetSign();//签名
        return $bizPayUrl->GetValues();
    }

    /**
     * 转换短链接
     * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
     * 减小二维码数据量，提升扫描速度和精确度。
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param ShortUrl $shortUrl
     * @return mixed
     * @throws WxPayException
     */
    public function shorturl(ShortUrl $shortUrl)
    {
        $url = $shortUrl->host."/tools/shorturl";
        $shortUrl->SetSign();//签名
        $xml = $shortUrl->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;
    }
}