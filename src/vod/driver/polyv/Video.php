<?php


namespace uukule\vod\driver\polyv;


use uukule\Vod;
use uukule\vod\core\VideoItem;
use uukule\vod\core\VideoItems;

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

    protected $sort = [
        'create_time_aes' => 'creationTimeAsc',
        'create_time_desc' => 'creationTimeDesc',
        'play_times_aes' => 'playTimesAsc',
        'play_times_desc' => 'playTimesDesc'
    ];

    public function list(array $param = []){
        $uri = "/v2/video/search-videos";
        $response = new VideoItems();
        $queryParam = [
            'userid' => $this->userid,
            'filters' => 'basicInfo,metaData,transcodeInfo,snapshotInfo',
            'containSubCate' => true,
            'page' => $param['page'] ?? 1,
            'pageSize' => $param['rows'] ?? $response->list_rows,
            'cateId' => $param['cate_id'] ?? 1,
            'title' => $param['title'] ?? '',
            'tag' => $param['tag'] ?? '',
            'status' => !empty($param['status']) ? array_search(($param['status'] ?? ''), $this->status) : '',
            'startTime' => !empty($param['create_time'][0]) ? strtotime($param['create_time'][0]) * 1000 : '',
            'endTime' => !empty($param['create_time'][1]) ? strtotime($param['create_time'][1]) * 1000 : '',
            'encrypted' => isset($param['is_encrypt']) ? (int) $param['is_encrypt']: '',
            'sort' => !empty($param['sort']) ? $this->sort[$param['sort']] : 'creationTimeDesc',
        ];

        $queryParam = array_filter($queryParam, fn($v)=> '' !== $v);
        $result =  self::post($uri, $queryParam, 'signABA');
        foreach ($result['data']['contents'] as $item) {
            $vod = new VideoItem();
            $vod->title = $item['basicInfo']['title'];
            $vod->cover_url = $item['basicInfo']['coverURL'];
            $vod->description = $item['basicInfo']['description'] ?? '';
            $vod->video_id = $item['vid'];
            $vod->duration = $item['basicInfo']['duration'] ?? null;
            $vod->size = $item['basicInfo']['size'];
            $vod->create_time = $item['basicInfo']['creationTime'];
            $vod->status = $this->status[$item['basicInfo']['status']] ?? $item['basicInfo']['status'];
            $vod->is_encrypt = (bool) $item['transcodeInfos'][0]['encrypt'];
            $vod->file_mp4_url = $vod->is_encrypt ? '' : $item['transcodeInfos'][0]['playUrl'];
            $vod->file_md5 = '-';
            $vod->tags = explode(',', $item['basicInfo']['tag']?? '');
            $vod->cate_id = (int) $item['basicInfo']['cateId'];
            $vod->cate_name = $item['basicInfo']['cateName'];
            $response[] = $vod;
        }
        $response->total = (int) ($result['data']['totalItems'] ?? 0);
        $response->current_page = (int) $result['data']['pageNumber'];
        $response->per_page = (int) $result['data']['totalPages'];
        $response->list_rows = (int) $result['data']['pageSize'];
        $response->last_page = (int) $result['data']['totalPages'];
        return $response;
    }

    public function read(string $id) : VideoItem
    {
        $response = [];
        $uri = "/v2/video/{$this->userid}/get-video-info";
        $queryParam = [
            'vid' => $id
        ];
        $request = self::post($uri, $queryParam, 'signABA');
        $item = $request['data'][0];
        $vod = new VideoItem();
        $vod->title = $item['basicInfo']['title'];
        $vod->cover_url = $item['basicInfo']['coverURL'];
        $vod->description = $item['basicInfo']['description'] ?? '';
        $vod->video_id = $item['vid'];
        $vod->duration = $item['basicInfo']['duration'] ?? null;
        $vod->size = $item['basicInfo']['size'];
        $vod->create_time = $item['basicInfo']['creationTime'];
        $vod->status = $this->status[$item['basicInfo']['status']] ?? $item['basicInfo']['status'];
        $vod->is_encrypt = (bool) $item['transcodeInfos'][0]['encrypt'];
        $vod->file_mp4_url = $vod->is_encrypt ? '' : $item['transcodeInfos'][0]['playUrl'];
        $vod->file_md5 = '-';
        $vod->tags = explode(',', $item['basicInfo']['tag']?? '');
        $vod->cate_id = (int) $item['basicInfo']['cateId'];
        $vod->cate_name = $item['basicInfo']['cateName'];
        $vod->source_data = json_encode($item, JSON_UNESCAPED_UNICODE);
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