<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Dynamic extends CI_Controller
{
    public function index()
    {
        $result = LoginService::check();

        // check failed
        if ($result['code'] !== 0) {
            return;
        }

        $open_id = $result['data']['userInfo']['openId'];
        //根据openid 获取宠物信息
        $dogInfo = $this->db->select('open_id as id, name, breed, avatar_url as avatarUrl')
            ->where(['open_id'=> $open_id])
            ->get('dogs')
            ->row_array();
        if ($dogInfo) {
            $marker = $this->db->where('open_id', $open_id)->get('markers')->row();
            if ($marker) {
                $dogInfo['markedAt'] = $marker->marked_at;
                $lastClockDay = substr($marker->marked_at, 0, 10);
                if ($lastClockDay == date('Y-m-d')) {
                    $dogInfo['clocked'] = true;
                }
            }
        }

        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => $dogInfo,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
}
