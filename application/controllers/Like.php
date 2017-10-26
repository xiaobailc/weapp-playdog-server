<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Like extends CI_Controller
{
    public function index()
    {
        $result = LoginService::check();
        if ($result['code'] !== 0) {
            return;
        }
        
        $open_id = $result['data']['userInfo']['openId'];

        $likeInfos = $this->db->from('likes')
            ->where(['master_id' => $open_id])
            ->order_by('liked_at', 'DESC')
            ->limit(50)
            ->get()->result_array();
        //var_dump($likeInfos);exit;
        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => $likeInfos,
        );
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function store()
    {
        $result = LoginService::check();
        if ($result['code'] !== 0) {
            return;
        }
        
        $open_id = $result['data']['userInfo']['openId'];

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'code' => -1,
                'message' => '必填字段不能为空'
            ];
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($response));
            return;
        }

        $name = $result['data']['userInfo']['nickName'];
        $avatarUrl = $result['data']['userInfo']['avatarUrl'];

        $likeInfo = $this->db->from('likes')
            ->where([
                'master_id' => $id,
                'follow_id' => $open_id
            ])
            ->order_by('liked_at', 'DESC')
            ->get()->row();

        if ($likeInfo) {
            //判断上次时间是今天则直接返回（每天只能赞一次）
            $last_like_time = strtotime($likeInfo->liked_at);
            $today_time = strtotime(date("Y-m-d"));
            if ($last_like_time > $today_time) {
                $response = array(
                    'code' => 0,
                    'message' => 'ok',
                    'data' => $likeInfo,
                );
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
                return;
            }
            //更新likes
            $data = [
                'liked_at' => date('Y-m-d H:i:s'),
            ];
            $res = $this->db->where([
                'master_id' => $id,
                'follow_id' => $open_id
                ])->update('likes', $data);
        } else {
            //插入
            $data = [
                'master_id' => $id,
                'follow_id' => $open_id,
                'follow_name' => $name,
                'follow_avatar_url' => $avatarUrl,
                'liked_at' => date('Y-m-d H:i:s'),
            ];
            $res =  $this->db->insert('likes', $data);
        }

        if ($res) {
            //更新宠物表，修改liekNum，如果跨天则为1，当天则+1
            $this->db->set('like_num', "if(last_liked_at > '".date('Y-m-d')."', like_num+1, 1)", false);
            $this->db->set('last_liked_at', date('Y-m-d H:i:s'));
            $this->db->where('open_id', $id);
            $this->db->update('dogs');
            $response = array(
                'code' => 0,
                'message' => 'ok',
                'data' => $data,
            );
        } else {
            $error = $this->db->error();
            $response = array(
                'code' => $error['code'],
                'message' => $error['message'],
                'data' => $data,
            );
        }
        //echo json_encode($response, JSON_FORCE_OBJECT);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
