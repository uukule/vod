<?php


namespace uukule\vod\driver\polyv;

use app\Debug;
use uukule\VodException;

/**
 * Class Request
 * @package uukule\vod\driver\ployv
 * @property string $userid 主账号的userid
 * @property string $secretkey 主账号的sercrety
 * @property string $writeToken 主账号的writeToken
 * @property string $readtoken
 * @property string $domain 请求接口域名
 */
class Request
{
    static protected $config = [];

    public function __construct($config = [])
    {
        self::init($config);
    }

    public function __get($name)
    {
        return self::$config[$name];
    }

    public function __set($name, $value)
    {
        self::$config[$name] = $value;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array($name, $arguments);
    }

    static public function init(array $config = [])
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * @param string|array|null $param
     * @param $val 要设置的值
     */
    static public function config($param = null, $val = null)
    {
        if (is_null($param)) {
            return self::$config;
        } elseif (is_array($param)) {
            self::$config = array_merge(self::$config, $param);
        } elseif (is_string($param) && is_null($val)) {
            return self::$config[$param];
        } else {
            self::$config[$param] = $val;
        }
    }

    /**
     * 加签方式A
     * @param array $param
     * @return string
     */
    static public function sign(array $param): string
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
        $str = join('&', $_arr) . self::$config['secretkey'];
        $sign = sha1($str);
        return strtoupper($sign);
    }

    /**
     * 用最多的加签
     * @param array $param
     * @return string
     */
    static public function signABA(array $param): string
    {
        $param = array_filter($param, function ($val) {
            return !(is_null($val) || '' === trim($val));
        });
        ksort($param);
        $_arr = [];
        foreach ($param as $k => $v) {
            $_arr[] = "{$k}={$v}";
        }
        $str = join('&', $_arr) . self::$config['secretkey'];
        Debug::add($str);
        $sign = sha1($str);
        return strtoupper($sign);
    }

    /**
     * 用第二多的加签
     * @param array $param
     * @return string
     */
    static public function signBA(array $param): string
    {
        $param = array_filter($param, function ($val) {
            return !(is_null($val) || '' === trim($val));
        });
        ksort($param);
        $_arr = [];
        foreach ($param as $k => $v) {
            $_arr[] = "{$k}={$v}";
        }
        $str = join('&', $_arr) . self::$config['secretkey'];
        Debug::add($str);
        $sign = sha1($str);
        return strtoupper($sign);
    }

    /**
     * 哎又一个验签方法
     *
     * @param array $param
     * @return string
     */
    static public function signBUA(array $param): string
    {
        $param['userid'] = self::$config['userid'];
        $param = array_filter($param, function ($val) {
            return !(is_null($val) || '' === trim($val));
        });
        ksort($param);
        $_arr = [];
        foreach ($param as $k => $v) {
            $_arr[] = "{$k}={$v}";
        }
        $str = join('&', $_arr) . self::$config['secretkey'];
        Debug::add($str);
        $sign = sha1($str);
        return strtoupper($sign);
    }


    static public function get(string $uri, array $param = [], string $sign_type = 'signABA')
    {
        if (!array_key_exists('ptime', $param)) {
            $param['ptime'] = ceil(microtime(true) * 1000);
        }
        $param['sign'] = self::$sign_type($param);

        $url = self::$config['domain'] . $uri;
        $data = http_get($url, $param);
        $dataArr = json_decode($data, true);
        if (empty($dataArr)) {
            throw new VodException('获取失败');
        }
        if (200 != $dataArr['code']) {
            throw new VodException($dataArr['message'], $dataArr['code']);
        }
        return $dataArr;
    }

    /**
     * post 请求
     * @param string $uri
     * @param array|null $param
     * @return array
     * @throws \think\Exception
     */
    static public function post(string $uri, array $param = null, string $sign_type = 'signABA'): array
    {
        if (!array_key_exists('ptime', $param)) {
            $param['ptime'] = ceil(microtime(true) * 1000);
        }
        $param['sign'] = self::$sign_type($param);
        $url = self::$config['domain'] . $uri;
        Debug::add($url);
        Debug::add(http_build_query($param));
        $dataArr = http_post($url, $param);
        Debug::add($dataArr);
        if (empty($dataArr)) {
            throw new VodException('获取失败');
        }
        if (200 != $dataArr['code']) {
            throw new VodException($dataArr['message'], $dataArr['code']);
        }
        return $dataArr;
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