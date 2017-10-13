<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Dynamic extends CI_Controller
{
    public function index()
    {
        $latitude = $this->input->get('latitude')?:0;
        $longitude = $this->input->get('longitude')?:0;
        $range = $this->input->get('range')?:0.01;

        $this->db->select('open_id as id, master_name as masterName, master_avatar_url as masterAvatarUrl, dog_name as dogName, dog_avatar_url as dogAvatarUrl, marked_at as markedAt');
        $this->db->from('dynamics');
        if ($latitude && $longitude) {
            $this->db->where([
                'latitude <' => $latitude+$range,
                'latitude >' => $latitude-$range,
                'longitude <' => $longitude+$range,
                'longitude >' => $longitude-$range
            ]);
        }
        $dynamicInfos = $this->db->order_by('marked_at', 'DESC')->limit(50)->get()->result_array();
        //$dump($dynamicInfos);exit;

        array_walk($dynamicInfos, function (&$item, $key) {
            $item['showTime'] = $this->timeTranx($item['markedAt']);
        });
        //var_dump($dynamicInfos);exit;

        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => $dynamicInfos,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    private function timeTranx($showTime)
    {
        $now = time();
        $show = strtotime($showTime);
        $dur = $now - $show;
        if ($dur < 0) {
            return $showTime;
        }
        if ($dur < 60) {
            return $dur.'秒前';
        }
        if ($dur < 3600) {
            return floor($dur/60).'分钟前';
        }
        if ($dur < 86400) {
            return floor($dur/3600).'小时前';
        }
        if ($dur < 259200) {
            return floor($dur/86400).'天前';
        }
        return $showTime;
    }
}
