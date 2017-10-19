<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Rank extends MY_Controller
{
    public function index()
    {
        $result = LoginService::check();
        
        if ($result['code'] !== 0) {
            return;
        }
        
        $open_id = $result['data']['userInfo']['openId'];
        //获得当日时间
        $today = date("Y-m-d");

        //暂时采取全国排名
        $rankInfos = $this->db->from('dogs')
                ->join('likes', "likes.master_id = dogs.open_id and likes.follow_id = '$open_id' and likes.liked_at > '$today'", 'left')
                ->order_by('dogs.continuous_day', 'DESC')
                ->limit(50)
                ->get()->result_array();

        //var_dump($rankInfos);exit;
        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => $rankInfos,
        );

        //echo json_encode($response, JSON_FORCE_OBJECT);
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
    }
}
