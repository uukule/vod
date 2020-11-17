<?php


namespace uukule;


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
     * @return array
     */
    public function info(string $id): array;

    /**
     * 全部视频列表
     * @param array $where
     * @return array
     */
    public function list(array $where): array;

    /**
     * 删除视频
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool;


}