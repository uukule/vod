<?php


namespace uukule\vod\driver\polyv;


use uukule\Vod;
use uukule\vod\core\VideoItem;

class Video extends Request
{
    protected $status = [
        '6'  => Vod::VOD_STATUS_UPLOAD_SUCCESS,
        '60' => Vod::VOD_STATUS_NORMAL,
        '61' => Vod::VOD_STATUS_NORMAL,
        '10' => Vod::VOD_STATUS_TRANSCODE_AWIT,
        '20' => Vod::VOD_STATUS_TRANSCODING,
        '50' => Vod::VOD_STATUS_AUDIT_AWIT,
        '51' => Vod::VOD_STATUS_AUDIT_PASS,
        '-1' => Vod::VOD_STATUS_DELETE,
    ];

    public function read(string $id) : VideoItem
    {
        $response = [];
        $uri = "/v2/video/{$this->userid}/video-info";
        $queryParam = [
            'vid' => $id
        ];
        $request = self::post($uri, $queryParam, 'signABA');
        $item = $request['data'][0];
        $vod = new VideoItem();
        $vod->source_data = json_encode($item, JSON_UNESCAPED_UNICODE);
        $vod->title = $item['title'];
        $vod->cover_url = $item['first_image'];
        $vod->description = $item['context'] ?? '';
        $vod->video_id = $item['vid'];
        $vod->duration = $item['duration'] ?? null;
        $vod->size = $item['source_filesize'];
        $vod->create_time = $item['ptime'];
        $vod->status = $this->status[$item['status']] ?? $item['status'];
        $vod->file_mp4_url = $item['mp4'] ?? '';
        $vod->file_md5 = $item['md5checksum'] ?? '-';
        $vod->tags = explode(',', $item['tag']?? '');
        $vod->cate_id = (int) $item['cataid'];
        $vod->cate_name = $item['cataname'];
        $response = $vod;
        return $response;
    }

    public function update(string $id, array $data){
        $sourceData = $this->read($id);
        if(!empty($data['cate_id']) && is_numeric($data['cate_id']) && $data['cate_id'] != $sourceData->cate_id){
            $this->changeCate($id, $data['cate_id']);
        }
        if(!empty($data['cover_url']) && $data['cover_url'] != $sourceData->cover_url){
            $this->change_cover_image($id, $data['cover_url']);
        }

        $uri = "/v2/video/{$this->userid}/video-info";
        $queryParam = ['vid' => $id];
        if(!empty($data['description'])){
            $queryParam['describ'] = $data['description'];
        }
        if(!empty($data['tag'])){
            $queryParam['tag'] = $data['tags'];
        }
        if(!empty($data['title'])){
            $queryParam['title'] = $data['title'];
        }
        if(count($queryParam) > 1){
            self::post($uri, $queryParam, 'signABA');
        }

        return true;
    }

    /**
     * 删除视频
     */
    public function delete(string $ids, bool $is_to_recycle = true){
        $uri = "/v2/video/del-videos";
        $queryParam = [
            'userId' => self::$config['userid'],
            'vids' => $ids,
            'deleteType' => (string) ($is_to_recycle ? 1 : 2)
        ];
        return self::post($uri, $queryParam, 'signABA');
    }

    /**
     * 视频批量修改分类
     * @param string $ids
     * @param int $cate_id
     * @return array
     * @throws \think\Exception
     */
    public function changeCate(string $ids, int $cate_id){
        $uri = "/v2/video/{$this->userid}/changeCata";
        $queryParam = [
            'vids' => $ids,
            'cataid' => (string) $cate_id
        ];
        return self::post($uri, $queryParam, 'signBUA');
    }


    public function change_cover_image(string $ids, string $image_url){
        $uri = "/v2/video/upload-cover-image";
        $queryParam = [
            'userid' => $this->userid,
            'vids' => $ids,
            'imageUrl' => $image_url
        ];
        return self::post($uri, $queryParam, 'signABA');
    }
}