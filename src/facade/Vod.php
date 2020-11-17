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

namespace uukule\facade;

if (class_exists('think\Facade')) {
    class Facade extends \think\Facade
    {}
} else {
    class Facade
    {
        /**
         * 始终创建新的对象实例
         * @var bool
         */
        protected static $alwaysNewInstance;

        protected static $instance;

        /**
         * 获取当前Facade对应类名
         * @access protected
         * @return string
         */
        protected static function getFacadeClass()
        {}

        /**
         * 创建Facade实例
         * @static
         * @access protected
         * @param  bool $newInstance 是否每次创建新的实例
         * @return object
         */
        protected static function createFacade(bool $newInstance = false)
        {
            $class = static::getFacadeClass() ?: 'think\DbManager';

            if (static::$alwaysNewInstance) {
                $newInstance = true;
            }

            if ($newInstance) {
                return new $class();
            }

            if (!self::$instance) {
                self::$instance = new $class();
            }

            return self::$instance;

        }

        // 调用实际类的方法
        public static function __callStatic($method, $params)
        {
            return call_user_func_array([static::createFacade(), $method], $params);
        }
    }
}

/**
 *
 * @method static array createUploadVideo() 获取视频上传地址和凭证
 * @method static array info(string $id) 获取视频信息
 * @method static array list(array $where) 获取视频列表
 *
 * @see \uukule\Vod
 * @mixin \uukule\Vod
 */
class Vod extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'uukule\Vod';
    }
}
