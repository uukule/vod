<?php


namespace uukule\vod\core;

use Traversable;

/**
 * Class VideoItem
 * @property string $cover_url 封面
 * @property string $title 视频标题
 * @property string $description 视频描述
 * @property string $status 视频状态
 * @property string $video_id 视频ID
 * @property int $size 视频源文件大小。单位：字节
 * @property float $duration 视频时长。单位：秒。
 * @property int $cate_id 视频分类ID
 * @property string $cate_name 视频分类名称
 * @property string $file_mp4_url 视频MP4链接地址
 * @property string $create_time 视频创建时间 yyyy-MM-dd HH:mm:ss 东8区时间
 * @property string $update_time 视频最后更新时间 yyyy-MM-dd HH:mm:ss 东8区时间
 * @property string $file_md5 文件哈希值
 * @property array $tags 标签
 * @property array $snapshots 快照
 * @property string $source_data 原始数据
 * @package uukule\vod\core
 *
 *
 * ----------------------------------------------------------------
 * Uploading：上传中。
 * UploadFail：上传失败。
 * UploadSucc：上传完成。
 * Transcoding：转码中。
 * TranscodeFail：转码失败。
 * Blocked：屏蔽。
 * Normal：正常。
 */
class VideoItem implements \IteratorAggregate, \ArrayAccess
{

    protected $data = [];


    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name];
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
        return $this->data;
    }

    public function data(array $data)
    {
        foreach ($data as $name => $value) {
            $this->$name = $value;
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
        return array_key_exists($offset, $this->data);
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
        unset($this->data[$offset]);
    }
}