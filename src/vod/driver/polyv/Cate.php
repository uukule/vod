<?php


namespace uukule\vod\driver\polyv;


class Cate extends Request
{


    /**
     * 获取视频分类目录
     * @param null $cataid
     * @return array
     * @throws \uukule\VodException
     */
    public function list($cataid = null): array
    {

        $uri = "/v2/video/{$this->userid}/cataJson";
        $param['userid'] = $this->userid;
        if (!is_null($cataid)) {
            $param['cataid'] = $cataid;
        }
        return self::get($uri, $param, 'signBA')['data'];
    }

    /**
     * 新建视频分类
     * @param string $name
     * @param int $parentid
     * @return array
     * @throws \think\Exception
     */
    public function save(string $name, int $parentid = 1): array
    {
        $uri = "/v2/video/{$this->userid}/addCata";
        $data = [
            'cataname' => $name, //分类名称 ,不超过40个字符
            'parentid' => $parentid, //新建的分类目录的上一级目录，值为1时表示根目录
        ];
        return self::post($uri, $data)['data'];
    }

    public function update(int $cate_id, array $param):bool
    {
        $uri = "/v2/video/{$this->userid}/updateCata";
        $data = [
            'cataname' => $param['name'], //分类名称 ,不超过40个字符
            'cataid' => $cate_id
        ];
        return self::post($uri, $data, 'signBUA')['data'];
    }

    public function delete(int $cate_id){

        $uri = "/v2/video/{$this->userid}/deleteCata";
        $data = [
            'cataid' => $cate_id
        ];
        return self::post($uri, $data, 'signBUA')['data'];
    }
}