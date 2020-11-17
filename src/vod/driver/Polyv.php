<?php


namespace uukule\vod\driver;


use think\Exception;
use uukule\VodInterface;

class Polyv implements VodInterface
{

    protected $config = [
        'userid' => '', //主账号的userid
        'secretkey' => '', //主账号的sercrety
        'writeToken' => '', //主账号的writeToken
        'readtoken' => '',
//        'subAccountAppId' => 'q7Hvi9VDpp',//子账号的appId
//        'subAccountSecretkey' => 'ebfd6371984448b0a1cb28e6f84bf2d9',//子账号的sercrety
        'domain' => 'http://api.polyv.net',
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }


    /**
     * 获取视频上传地址和凭证
     *
     * @param array $param
     * @return array
     */
    public function createUploadVideo(array $param = []): array
    {
        $response = [];
        $timestamp = ceil(microtime(true) * 1000);
        if (empty($this->config['subAccountAppId'])) {
            //主帐号信息
            $response['userid'] = $this->config['userid'];
            $response['ptime'] = (string)$timestamp;
            $hash = md5($timestamp . $this->config['writeToken']);
            $sign = md5($this->config['secretkey'] . $timestamp);
            $response['sign'] = $sign;
            $response['hash'] = $hash;
        } else {
            $response['appId'] = $this->config['subAccountAppId'];
            $response['timestamp'] = (string)$timestamp;
            $subAccountSign = strtoupper(md5("{$this->config['subAccountSecretkey']}appId{$this->config['subAccountAppId']}timestamp{$timestamp}{$this->config['subAccountSecretkey']}"));
            $response['sign'] = $subAccountSign;
        }
        return $response;
    }

    /**
     * 获取播放参数
     *
     * @param string $id
     * @param bool $encryptType 是否加密
     * @return array
     * @throws \Exception
     */
    public function getPlayInfo(string $id, bool $encryptType = false): array
    {
        $response = [];
        $ts = (string)ceil(microtime(true) * 1000);
        if ($encryptType)
        {
            $data = [];
            $data['userId'] = $this->config['userid'];
            $data['videoId'] = $id;
            $data['ts'] = $ts;
            $data['viewerIp'] = $this->get_client_ip();
            $data['viewerId'] = '1';
            $data['viewerName'] = '1';
            $data['extraParams'] = '1';
            ksort($data);
            $concated = $this->config['secretkey'];
            foreach ($data as $k => $v) {
                $concated .= "{$k}{$v}";
            }
            $plain = $concated . $this->config['secretkey'];
            $data['sign'] = strtoupper(md5($plain));
            $url = 'https://hls.videocc.net/service/v1/token';
            $result = http_post($url, $data);
            $response['playsafe'] = $result['data']['token'];
        }
        $response['vid'] = $id;
        $response['ts'] = $ts;
        $response['sign'] = md5($this->config['secretkey'] . $id . $ts);
        return $response;
    }

    /**
     * 获取单个视频信息
     *
     * @param string $id
     * @return array
     */
    public function info(string $id): array
    {
        $uri = "/v2/video/{$this->config['userid']}/get-video-msg";
        $param = [
            'vid' => $id
        ];
        return $this->post($uri, $param);
    }

    /**
     * 全部视频列表
     * @param array $where
     * @return array
     */
    public function list(array $where = []): array
    {
        $uri = "/v2/video/{$this->config['userid']}/get-new-list";
        $param = [];
        return $this->post($uri, $param);
    }

    /**
     * 删除视频
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $uri = "/v2/video/{$this->config['userid']}/del-video";
        $param = [
            'vid' => $id
        ];
        $return = $this->post($uri, $param);
        if (200 === $return['code']) {
            return true;
        }
        throw new Exception($return['message'], $return['code']);
    }

    public function sign(array $param): string
    {
        foreach ($param as $k => $v) {
            if ('' === trim($v)) {
                unset($param[$k]);
            }
        }
        ksort($param);
        $_arr = [];
        foreach ($param as $k => $v) {
            $_arr[] = "{$k}={$v}";
        }
        $str = join('&', $_arr) . $this->config['secretkey'];
        $sign = sha1($str);
        return strtoupper($sign);
    }


    protected function get(string $uri, array $param = null): array
    {
        $param['ptime'] = ceil(microtime(true) * 1000);
        $param['format'] = 'json';
        foreach ($param as $k => $v) {
            if ('' === trim($v)) {
                unset($param[$k]);
            }
        }
        $url = $this->config['domain'] . $uri;
        $param['sign'] = $this->sign($param);
        $data = http_get($url, $param);
        return $data;
    }

    protected function post(string $uri, array $param = null): array
    {
        $param['ptime'] = ceil(microtime(true) * 1000);
        $param['format'] = 'json';
        foreach ($param as $k => $v) {
            if ('' === trim($v)) {
                unset($param[$k]);
            }
        }
        $url = $this->config['domain'] . $uri;
        $param['sign'] = $this->sign($param);
        $data = http_post($url, $param);
        return $data;
    }

    protected function get_client_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }
        return $ipaddress;
    }
}