<?php


namespace uukule\vod\core\interface_api;


interface  PlayerInterface
{

    /**
     * @param bool $is_encrypt
     * @return PlayerInterface
     */
    public function encrypt(bool $is_encrypt);

    /**
     * @param string $id
     * @param string $name
     * @return PlayerInterface
     */
    public function viewer(string $id, string $name);

    /**
     * @param string $video_id
     * @return array
     */
    public function info(string $video_id) : array;

}