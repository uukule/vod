<?php

namespace uukule\vod\driver\polyv;


use uukule\Vod;
use uukule\vod\core\VideoItem;
use uukule\vod\core\VideoItems;

class VideoList extends Request
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

    public function list(array $param): VideoItems
    {
        $queryParam = [];
        $queryParam['pageNum'] = $param['_page'] ?? 1;
        $queryParam['numPerPage'] = $param['_rows'] ?? 30;
        $queryParam['published'] = $param['published'] ?? 0;
        $re = [];
        if (empty($param['keyword'])) {
            $queryParam['startDate'] = $param['start_date'] ?? null;
            $queryParam['endDate'] = $param['end_date'] ?? null;
            $queryParam['startTime'] = $param['start_time'] ?? null;
            $queryParam['endTime'] = $param['end_time'] ?? null;
            $queryParam['published'] = $param['published'] ?? null;
            $re = $this->all($queryParam);
        } else {
            $queryParam['cataid'] = $param['cataid'] ?? null;
            $queryParam['keyword'] = $param['keyword'] ?? null;
            $queryParam['tag'] = $param['tag'] ?? null;
            $queryParam['vids'] = $param['vids'] ?? null;
            $queryParam['sort'] = $param['_order'] ?? 'creationTimeDesc';
            $re = $this->search($queryParam);
        }
        $response = new VideoItems();
        foreach ($re['data'] as $item) {
            $vod = new VideoItem();
            $vod->title = $item['title'];
            $vod->cover_url = $item['first_image'];
            $vod->description = $item['context'];
            $vod->video_id = $item['vid'];
            $vod->duration = $item['duration'] ?? null;
            $vod->size = $item['source_filesize'];
            $vod->create_time = $item['ptime'];
            $vod->status = $this->status[$item['status']] ?? $item['status'];
            $vod->file_md5 = $item['md5checksum'] ?? '-';
            $vod->tags = explode(',', $item['tag']?? '');
            $response[] = $vod;
        }
        $response->total = (int) ($re['total'] ?? 0);
        $response->current_page = (int) $queryParam['pageNum'];
        $response->per_page = (int) $queryParam['numPerPage'];
        return $response;
    }

    public function all(array $queryParam = []): array
    {
        $uri = "/v2/video/{$this->userid}/get-new-list";
        return self::post($uri, $queryParam, 'signBA');
    }

    public function search(array $queryParam = []): array
    {
        $uri = "/v2/video/{$this->userid}/search";
        return self::post($uri, $queryParam, 'signBA');
    }
}