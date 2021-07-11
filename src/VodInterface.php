<?php


namespace uukule;


use uukule\vod\core\interface_api\PlayerInterface;
use uukule\vod\core\VideoItem;
use uukule\vod\core\VideoItems;

interface VodInterface
{

    /**
     * 获取视频上传地址和凭证
     *
     * @param array $param
     * @return array
     */
    public function createUploadVideo(array $param = []): array;

    /**
     * 获取播放参数
     *
     * @param string $id
     * @param bool $encryptType
     * @return array
     */
    public function getPlayInfo(string $id, bool $encryptType = false): array;

    /**
     * 获取单个视频信息
     *
     * @param string $id
     * @return VideoItem
     */
    public function info(string $id): VideoItem;

    /**
     * 全部视频列表
     * @param array $where
     * @return array
     */
    public function list(array $where): VideoItems;

    public function video();
    /**
     * 删除视频
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * 获取播放信息
     * @return PlayerInterface
     */
    public function player();

}