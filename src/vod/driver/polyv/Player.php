<?php

namespace uukule\vod\driver\polyv;


use app\Debug;
use uukule\Vod;
use uukule\vod\core\interface_api\PlayerInterface;
use uukule\vod\core\VideoItem;
use uukule\vod\core\VideoItems;

class Player extends Request implements PlayerInterface
{
    /**
     * 是否为加密视频
     * @var bool
     */
    private $is_encrypt = false;
    private $viewer = [
        'id' => '1',
        'name' => '1'
    ];

    public function encrypt(bool $is_encrypt) : Player
    {
        $this->is_encrypt = $is_encrypt;
        return $this;
    }

    /**
     * @param string $id
     * @param string $name
     * @return $this
     */
    public function viewer(string $id, string $name = '') : Player
    {
        $this->viewer = [
            'id' => $id,
            'name' => $name
        ];
        return $this;
    }

    public function info(string $id): array
    {
        $response = [];
        $ts = (string)ceil(microtime(true) * 1000);
        if ($this->is_encrypt)
        {
            $response['playsafe'] = $this->playsafe($id, $ts);
        }
        $response['vid'] = $id;
        $response['ts'] = $ts;
        $response['sign'] = md5($this->secretkey . $id . $ts);
        return $response;
    }

    protected function playsafe($id, $ts){

        $data = [];
        $data['userId'] = $this->userid;
        $data['videoId'] = $id;
        $data['ts'] = $ts;
        $data['viewerIp'] = $this->get_client_ip();
        $data['viewerId'] = $this->viewer['id'];
        $data['viewerName'] = $this->viewer['name'];
        //$data['extraParams'] = '1';
        $data = array_filter($data, function ($val){
            return !(is_null($val) || '' === trim($val));
        });

        ksort($data);
        $concated = $this->secretkey;
        foreach ($data as $k => $v) {
            $concated .= "{$k}{$v}";
        }
        $plain = $concated . $this->secretkey;
        Debug::add($plain);
        $data['sign'] = strtoupper(md5($plain));
        $url = 'https://hls.videocc.net/service/v1/token';
        $result = http_post($url, $data);
        return  $result['data']['token'];
    }

}