<?php
namespace App\Extensions\Gateways\Alipay;

use App\Classes\Extensions\Gateway;
use App\Helpers\ExtensionHelper;
use Alipay\OpenAPISDK\Util\AlipayConfigUtil;
use Alipay\OpenAPISDK\Util\GenericExecuteApi;
use Alipay\OpenAPISDK\Util\Model\AlipayConfig;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class Alipay extends Gateway
{

    /**
     * Get the extension metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return [
            'display_name' => 'AliPay',
            'version' => '1.0.0',
            'author' => 'X-Zero-L',
            'website' => 'https://github.com/X-Zero-L/Paymenter-AliPay-Gateway',
        ];
    }

    /**
     * Get all the configuration for the extension
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            [
                'name' => 'app_id',
                'type' => 'text',
                'friendlyName' => 'APP ID',
                'required' => true,
            ],
            [
                'name' => 'private_key',
                'type' => 'text',
                'friendlyName' => 'Private Key',
                'required' => true,
            ],
            [
                'name' => 'alipay_public_key',
                'type' => 'text',
                'friendlyName' => 'AliPay Public Key',
                'required' => true,
            ],
            [
                'name' => 'encrypt_key',
                'type' => 'text',
                'friendlyName' => 'Encrypt Key',
                'required' => false,
            ],
            [
                'name' => 'live',
                'type' => 'boolean',
                'friendlyName' => 'Live',
                'required' => false,
            ],
        ];
    }

    /**
     * Get the URL to redirect to
     *
     * @param float $total
     * @param array $products
     * @param int $invoiceId
     * @return string 
     */
    public function pay($total, $products, $invoiceId): string
    {
        try {
            $alipayConfig = $this->getAlipayConfig();
            $alipayConfigUtil = new AlipayConfigUtil($alipayConfig);
            $apiInstance = new GenericExecuteApi(
                $alipayConfigUtil,
                new Client()
            );
            $bizParams = [];
            $bizContent = [];
            $bizContent['out_trade_no'] = strval($invoiceId);
            $bizContent['total_amount'] = strval(round($total, 2));
            $bizContent['subject'] = config('app.name', 'Paymenter') . $invoiceId;
            $bizContent['product_code'] = 'FAST_INSTANT_TRADE_PAY';
            if (!empty($products)) {
                $goodsDetail = [];
                foreach ($products as $index => $product) {
                    $item = [
                        'goods_id' => isset($product['id']) ? strval($product['id']) : 'item-' . $index,
                        'goods_name' => isset($product['name']) ? $product['name'] : 'Product ' . ($index + 1),
                        'quantity' => isset($product['quantity']) ? intval($product['quantity']) : 1,
                        'price' => isset($product['price']) ? strval(round($product['price'], 2)) : '0.00',
                    ];
                    $goodsDetail[] = $item;
                }
                $bizContent['goods_detail'] = $goodsDetail;
            }
            Log::info('goods_detail' . json_encode($products));
            $bizContent['integration_type'] = 'PCWEB';
            $bizContent['timeout_express'] = "90m";
            $bizContent["qr_pay_mode"] = 2;
            $bizParams['biz_content'] = $bizContent;
            $bizParams['return_url'] = route('clients.invoice.show', $invoiceId);
            $notify_url = url('/extensions/alipay/webhook');
            $bizParams['notify_url'] = $notify_url;
            $pageRedirectionData = $apiInstance->pageExecute('alipay.trade.page.pay', 'GET', $bizParams);
            return $pageRedirectionData;
        } catch (\Exception $e) {
            Log::error('Alipay payment error: ' . $e->getMessage());
            ExtensionHelper::error('Alipay', $e->getMessage());
        }
        return "";
    }

    /**
     * 获取支付宝配置
     *
     * @return AlipayConfig
     */
    private function getAlipayConfig()
    {
        $app_id = ExtensionHelper::getConfig('Alipay', 'app_id');
        $private_key = ExtensionHelper::getConfig('Alipay', 'private_key');
        $alipay_public_key = ExtensionHelper::getConfig('Alipay', 'alipay_public_key');
        $encrypt_key = ExtensionHelper::getConfig('Alipay', 'encrypt_key');
        $live = ExtensionHelper::getConfig('Alipay', 'live');
        $server_url = $live ? 'https://openapi.alipay.com' : 'https://openapi-sandbox.dl.alipaydev.com';
        $alipayConfig = new AlipayConfig();
        $alipayConfig->setAppId($app_id);
        $alipayConfig->setPrivateKey($private_key);
        $alipayConfig->setAlipayPublicKey($alipay_public_key);
        if (!empty($encrypt_key)) {
            $alipayConfig->setEncryptKey($encrypt_key);
        }
        $alipayConfig->setServerUrl($server_url);

        return $alipayConfig;
    }

    function check($respBody, $headers, $isCheckSign)
    {
        try {
            $alipayConfig = $this->getAlipayConfig();
            $alipayConfigUtil = new AlipayConfigUtil($alipayConfig);
            $alipayConfigUtil->verifyResponse($respBody, $headers, $isCheckSign);
            return true;
        } catch (\Exception $e) {
            Log::error('支付宝签名验证异常: ' . $e->getMessage());
            return false;
        }
    }

    public function webhook(Request $request)
    {
        Log::info('支付宝异步通知开始处理');
        $params = $request->all();
        Log::debug('支付宝异步通知参数', $params);

        try {
            $signVerified = $this->check(
                json_encode($params),
                $request->header(),
                true
            );
            if ($signVerified) {
                $out_trade_no = $params['out_trade_no'];
                $trade_no = $params['trade_no'];
                $trade_status = $params['trade_status'];
                $total_amount = $params['total_amount'];

                // TODO: 金额验证逻辑

                if ($params['app_id'] != ExtensionHelper::getConfig('Alipay', 'app_id')) {
                    Log::warning('支付宝异步通知APP_ID不匹配', [
                        'expected' => ExtensionHelper::getConfig('Alipay', 'app_id'),
                        'received' => $params['app_id']
                    ]);
                    return 'fail';
                }

                if ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED') {
                    ExtensionHelper::paymentDone($out_trade_no, 'Alipay', $trade_no);
                    Log::info('支付宝支付成功', [
                        'out_trade_no' => $out_trade_no,
                        'trade_no' => $trade_no
                    ]);
                    return 'success';
                }
            } else {
                Log::warning('支付宝异步通知签名验证失败');
                return 'fail';
            }
        } catch (\Exception $e) {
            Log::error('支付宝异步通知处理异常: ' . $e->getMessage());
            return 'fail';
        }

        return 'success';
    }
}