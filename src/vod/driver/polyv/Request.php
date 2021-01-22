<?php


namespace uukule\vod\driver\ployv;


class Request
{
    protected $config;
    public function __construct($config)
    {
        $this->config = $config;
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
        $param['ptime'] = time()*1000;
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