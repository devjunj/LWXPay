<?php
/**
 * Created by 广州轻快科技有限公司.
 * Author: Jaden
 * Time: 2018/6/20-上午10:56
 * Description:
 * Version: v1.0
 */
namespace Org\Jaden\WxPay\Payment\Entity;
use Org\Jaden\WxPay\Exception\WxPayException;
use Illuminate\Support\Facades\Config;

class DataBase
{

    protected $key;
    protected $app_id;
    protected $mch_id;
    protected $app_secret;
    protected $notify_url;
    protected $api_cert;
    protected $api_key;
    public $host;
    /*
    *必须传的参数
    */
    protected $need = array();
    protected $values = array();

    /**
     * DataBase constructor.
     * @param array $value
     * @param string $trade_type 交易类型
     * @throws WxPayException
     */
    public function __construct(array $value = [])
    {
        $this->values = $value;
        $this->values['key'] = Config::get("wxpay.key");
        $this->values['appid'] = Config::get("wxpay.app_id");
        $this->values['mch_id'] = Config::get("wxpay.mch_id");
        $this->values['notify_url'] = Config::get("wxpay.notify_url");
        $host = Config::get('wxpay.host');
        $app_secret = Config::get("wxpay.app_secret");
        $api_cert = Config::get("wxpay.ssl_cert");
        $api_key = Config::get('wxpay.ssl_key');
        //检查必传参数有没有设置
        $this->checkParams();
    }

    /**
     * 检查必须字段
     * @throws WxPayException
     * @return bool
     */
    protected function checkParams()
    {
        if(!empty($this->need)) {
            $notSetKey = array();
            array_filter($this->need, function ($v, $k) use (&$notSetKey) {
                $result = collect($this->values)->where($k);
                if (!$result) {
                    array_push($notSetKey, $k);
                }
            }, ARRAY_FILTER_USE_BOTH);
            if (!empty($notSetKey)) {
                throw new WxPayException(explode(',', $notSetKey) . "参数必传");
            }
        }
        return true;
    }

    /**
     * 设置签名，详见签名生成算法
     * @param bool $noncestr
     * @return string
     **/
    public function SetSign($noncestr = true)
    {
        if($noncestr)
           $this->values['nonce_str'] = self::getNonceStr();
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }

    /**
     * 获取签名，详见签名生成算法的值
     * @return 值
     **/
    public function GetSign()
    {
        return $this->values['sign'];
    }

    /**
     * 判断签名，详见签名生成算法是否存在
     * @return true 或 false
     **/
    public function IsSignSet()
    {
        return array_key_exists('sign', $this->values);
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml()
    {
        if(!is_array($this->values)
            || count($this->values) <= 0)
        {
            throw new WxPayException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        if(!$xml){
            throw new WxPayException("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->values;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 获取设置的值
     */
    public function GetValues()
    {
        return $this->values;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
}














