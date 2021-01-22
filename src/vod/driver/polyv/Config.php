<?php


namespace uukule\vod\driver\polyv;

/**
 * Class Config
 * @property string $userid 主账号的userid
 * @property string $secretkey 主账号的sercrety
 * @property string $writeToken 主账号的writeToken
 * @property string $readtoken 主账号的userid
 * @property string $subAccountAppId 子账号的appId
 * @property string $subAccountSecretkey 子账号的sercrety
 * @property string $domain 请求域名
 * @package uukule\vod\driver\ployv
 */
class Config implements \ArrayAccess
{


    protected static $config = [
        'domain' => 'https://api.polyv.net'
    ];

    /**
     * Teacher constructor.
     * return $this
     */
    public function __construct(array $config = [])
    {
        self::$config = array_merge(self::$config, $config);
    }

    public function __set($name, $value)
    {
        self::$config[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, self::$config)) {
            return self::$config[$name];
        }
        return null;
    }



    public function __call($name, $arguments)
    {
        switch (count($arguments)) {
            case 0:
                return $this->__get($name);
                break;
            case 1:
                return $this->__set($name, $arguments[0]);
                break;
            default :
                throw new \Exception('method no existent!');
                break;
        }
    }

    public function toArray()
    {
        return self::$config;
    }

    public static function data(array $config)
    {
        foreach ($config as $name => $value) {
            self::$config[$name] = $value;
        }
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, self::$config);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset(self::$config[$offset]);
    }
}