<?php
/**
 * Created by PhpStorm.
 * User: WytheHuang
 * Date: 2018/12/24
 * Time: 21:35
 */

namespace Wythe\Logistics\Query;


use Wythe\Logistics\Exceptions\HttpException;
use Wythe\Logistics\Exceptions\InvalidArgumentException;

class BaiduQuery extends Query
{
    /**
     * 构造函数
     * BaiduQuery constructor.
     */
    public function __construct()
    {
        $this->url = 'https://sp0.baidu.com/9_Q4sjW91Qh3otqbppnN2DJv/pae/channel/data/asyncqury';
    }

    /**
     * 调用百度查询快递链接
     *
     * @param string $code
     * @return array
     * @throws \Wythe\Logistics\Exceptions\HttpException
     */
    public function callInterface(string $code): array
    {
        try {
            $rand = $this->randNumber();
            $urlParams = [
                'cb' => 'jQuery1102027' . $rand[0],
                'appid' => 4001,
                'com' => '',
                'nu'=> $code,
                'vscode' => '',
                'token' => '',
                '_' => $rand[1]
            ];
            $this->format($this->request($this->url, $urlParams));
            $this->response['logistics_bill_no'] = $code;
            return $this->response;
        } catch (\Exception $exception) {
            throw new HttpException($exception->getMessage());
        }
    }

    /**
     * 格式返回响应信息
     *
     * @param  $response
     * @return void
     */
    protected function format($response)
    {
        $pattern = '/(jQuery\d+_\d+\()({.*})\)$/i';
        if (preg_match($pattern, $response, $match)) {
            $response = \json_decode($match[2], true);
            $this->response = [
                'status'  => $response['status'],
                'message' => $response['msg'],
                'error_code' => $response['error_code'] ?? '',
                'data' => $response['data']['info']['context'] ?? '',
                'logistics_company' => $response['com'] ?? '',
            ];
        } else {
            $this->response = [
                'status' => -1,
                'message' => '查询不到数据',
                'error_code' => -1,
                'data' => '',
                'logistics_company' => ''
            ];
        }
    }

    /**
     * 生成请求随机字符串数组
     *
     * @return array
     */
    private function randNumber(): array
    {
        $str = $subStr = '';
        for ($i = 0; $i < 15; $i++) {
            $str .= \mt_rand(0, 9);
        }
        for ($i = 0; $i < 3; $i++) {
            $subStr .= \mt_rand(0, 9);
        }
        return [$str . '_' . \time() . $subStr, \time() . $subStr];
    }
}