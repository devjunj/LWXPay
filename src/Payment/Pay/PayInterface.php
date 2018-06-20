<?php
/**
 * Created by 广州轻快科技有限公司.
 * Author: Jaden
 * Time: 2018/6/20-下午3:57
 * Description:
 * Version: v1.0
 */
namespace Org\Jaden\WxPay\Pay;

use Org\Jaden\WxPay\Payment\Entity\UnifiedOrder;
use Org\Jaden\WxPay\Payment\Entity\OrderQuery;
use Org\Jaden\WxPay\Payment\Entity\CloseOrder;
use Org\Jaden\WxPay\Payment\Entity\Refund;
use Org\Jaden\WxPay\Payment\Entity\RefundQuery;
use Org\Jaden\WxPay\Payment\Entity\DownloadBill;
use Org\Jaden\WxPay\Payment\Entity\MicroPay;
use Org\Jaden\WxPay\Payment\Entity\Reverse;
use Org\Jaden\WxPay\Payment\Entity\BizPayUrl;
use Org\Jaden\WxPay\Payment\Entity\ShortUrl;
use Org\Jaden\WxPay\Payment\Entity\Report;

interface PayInterface
{

    /**
     * 统一下单接口
     * @param UnifiedOrder $order 订单参数
     * @return mixed
     */
    public  function unifiedOrder(UnifiedOrder $order);

    /**
     *
     * 查询订单，OrderQuery中out_trade_no、transaction_id至少填一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param OrderQuery $query
     * @return mixed
     */
    public  function orderQuery(OrderQuery $query);

    /**
     * 关闭订单,CloseOrder中out_trade_no必填
     * @param CloseOrder $order
     * @return mixed
     */
    public  function closeOrder(CloseOrder $order);

    /**
     * 申请退款,Refund中out_trade_no、transaction_id至少填一个
     * @param Refund $refund
     * @return mixed
     */
    public  function refund(Refund $refund);

    /**
     * 退款查询,RefundQuery中out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个
     * @param RefundQuery $query
     * @return mixed
     */
    public  function refundQuery(RefundQuery $query);

    /**
     * 下载对账单，WxPayDownloadBill中bill_date为必填参数
     * @param DownloadBill $bill
     * @return mixed
     */
    public  function downloadBill(DownloadBill $bill);

    /**
     * 提交被扫支付API
     * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
     * 由商户收银台或者商户后台调用该接口发起支付。
     * PayMicroPay中body、out_trade_no、total_fee、auth_code参数必填
     * @param MicroPay $microPay
     * @return mixed
     */
    public  function micropay(MicroPay $microPay);

    /**
     * 撤销订单API接口，WxPayReverse中参数out_trade_no和transaction_id必须填写一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param Reverse $reverse
     * @return mixed
     */
    public  function reverse(Reverse $reverse);

    /**
     * 测速上报，该方法内部封装在report中，使用时请注意异常流程
     * WxPayReport中interface_url、return_code、result_code、user_ip、execute_time_必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param Report $report
     * @return mixed
     */
    public  function report(Report $report);

    /**
     * 生成二维码规则,模式一生成支付二维码
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param BizPayUrl $bizPayUrl
     * @return mixed
     */
    public  function bizpayurl(BizPayUrl $bizPayUrl);

    /**
     * 转换短链接
     * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
     * 减小二维码数据量，提升扫描速度和精确度。
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param ShortUrl $shortUrl
     * @return mixed
     */
    public  function shorturl(ShortUrl $shortUrl);

}