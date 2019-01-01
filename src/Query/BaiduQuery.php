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
use Wythe\Logistics\Traits\HttpRequest;

class BaiduQuery extends Query
{
    use HttpRequest;
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
     * @param string $type
     * @return array
     * @throws \Wythe\Logistics\Exceptions\HttpException
     */
    public function callInterface(string $code, string $type = ''): array
    {
        try {
            $rand = $this->randNumber();
            $urlParams = [
                'cb' => 'jQuery1102047' . $rand[0],
                'appid' => 4001,
                'com' => '',
                'nu'=> $code,
                'vscode' => '',
                'token' => '',
                '_' => $rand[1]
            ];
            $this->format($this->request($this->url, $urlParams, 1));
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
    protected function format($response): void
    {
        $pattern = '/(jQuery1102047\d{15}_\d+\()({.*})\)$/i';;
        $response = \preg_replace($pattern, '$2', $response);
        $response = \json_decode($response, true);
        $this->response = [
            'response_status'  => $response['status'],
            'message' => $response['msg'],
            'error_code' => $response['error_code'] ?? '',
            'data' => $response['data']['info']['context'] ?? '',
            'logistics_company' => $response['com'] ?? '',
        ];
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