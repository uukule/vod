<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace uukule;

/**
 * @see \uukule\OvdInterface
 */
class Vod
{

    const VOD_STATUS_UPLOADING = 0; //上传中
    const VOD_STATUS_UPLOAD_FAIL = 1; //上传失败
    const VOD_STATUS_UPLOAD_SUCCESS = 2; //上传完成
    const VOD_STATUS_TRANSCODE_AWIT = 4; //等待转码
    const VOD_STATUS_TRANSCODING = 8; //转码中
    const VOD_STATUS_TRANSCODE_FAIL = 16; //转码失败
    const VOD_STATUS_AUDIT_AWIT = 32; //等待审核
    const VOD_STATUS_AUDIT_PASS = 64; //审核不通过
    const VOD_STATUS_NORMAL = 128; //已发布
    const VOD_STATUS_DELETE = 256; //视频已删除


    /**
     * @var array 文件的实例
     */
    public static $instance = [];

    /**
     * @var object 操作句柄
     */
    public static $handler;

    /**
     * 自动初始化缓存
     * @param null $config 配置数组
     * @return VodInterface
     * @throws \Exception
     */
    public static function init($config = null)
    {

        if (is_null($config)) {
            return self::connect(config('ovd.default'));
        } elseif (is_array($config)) {
            return self::connect($config);
        }else{
            throw new \Exception('请指定文件驱动类型');
        }
    }

    /**
     * 连接文件驱动
     * @access public
     * @param array $config 配置数组
     * @param bool|string $name 缓存连接标识 true 强制重新连接
     * @return Driver
     */
    public static function connect(array $config = [], $name = false)
    {
        $type = $config['type'];

        if (false === $name) {
            $name = md5(serialize($config));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            $class = false === strpos($type, '\\') ?
                '\\uukule\\vod\\driver\\' . ucwords($type) :
                $type;

            if (true === $name) {
                return new $class($config);
            }

            self::$instance[$name] = new $class($config);
        }

        return self::$instance[$name];
    }

    public function __call($method, $args)
    {
        $instance = $this->init();
        return $instance->$method(...$args);
    }




    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = self::init();
        return $instance->$method(...$args);
    }
}
