<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Marker extends MY_Controller
{
    public function index()
    {
        $latitude = $this->input->get('latitude');
        $longitude = $this->input->get('longitude');

        if (empty($latitude) || empty($longitude)) {
            $response = [
                'code' => -1,
                'message' => '必填字段不能为空'
            ];
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($response));
            return;
        }

        $open_id = $this->input->get('id');
        $range = $this->input->get('range') ?: 0.01; //默认方圆1公里范围

        $markerInfos = $this->db
                ->from('dogs')
                ->where([
                'latitude <' => $latitude+$range,
                'latitude >' => $latitude-$range,
                'longitude <' => $longitude+$range,
                'longitude >' => $longitude-$range])
                ->order_by('last_marked_at', 'DESC')
                ->limit(50)
                ->get()->result_array();

        $this->load->helper('url');
        array_walk($markerInfos, function (&$item, $key, $open_id) {
            $item['avatar_url'] = base_url('uploads/'.$item['avatar_url']);
            if ($item['id']== $open_id) {
                $item['myself'] = true;
                $today = substr($item['last_marked_at'], 0, 10);
                if ($today != date('Y-m-d') && $item['type'] == 'stop') {
                    $item['hide'] = true;
                }
            }
        }, $open_id);

        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => $markerInfos,
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

        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        $type = $this->input->post('type');

        if (empty($latitude) || empty($longitude) || empty($type)) {
            $response = [
                'code' => -1,
                'message' => '必填字段不能为空'
            ];
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($response));
            return;
        }
        //$dogName = $this->input->post('dogName');
        //$dogAvatarUrl = $this->input->post('dogAvatarUrl');
        $now = time();
        
        $dogInfo = $this->db->where('open_id', $open_id)->get('dogs')->row();
        if (!$dogInfo) {
            return;
        }
        //获取上次打卡时间
        $last_marked_at = substr($dogInfo->last_marked_at, 0, 10);
        $last = strtotime($last_marked_at);
        $diff = $now - $last;
        if ($diff < 86400) {
            //一天之内
            $continuous_day = $dogInfo->continuous_day;
        } else if ($diff < 86400*2) {
            //连续第二天
            $continuous_day = $dogInfo->continuous_day +1;
        } else {
            //不连续
            $continuous_day = 1;
        }
        $maximum_continuous_day = $continuous_day > $dogInfo->maximum_continuous_day ? $continuous_day : $dogInfo->maximum_continuous_day;
        
        //更新宠物信息表 开启事务
        $data = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'type' => $type,
            'last_marked_at' => date('Y-m-d H:i:s', $now),
            'continuous_day' => $continuous_day,
            'maximum_continuous_day' => $maximum_continuous_day
        ];
        $this->db->trans_start();
        $this->db->where('open_id', $open_id)
            ->set($data)
            ->update('dogs');

        $time = 0;
        if ($type!='start' && $dogInfo->type == 'start') {
            $time = $now - strtotime($dogInfo->last_marked_at);
        }

        //插入标记表（动态表）
        $this->db->set([
            'open_id' => $open_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'master_name' => $result['data']['userInfo']['nickName'],
            'master_avatar_url' => $result['data']['userInfo']['avatarUrl'],
            'dog_name' => $dogInfo->name,
            'dog_avatar_url' => $dogInfo->avatar_url,
            'type' => $type,
            'marked_at' => date('Y-m-d H:i:s', $now),
            'time' => $time
        ])->insert('markers');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $error = $this->db->error();
            $response = [
                'code' => $error['code'],
                'message' => $error['message'],
                'data' => $data,
            ];
        } else {
            $this->db->trans_commit();
            $response = array(
                'code' => 0,
                'message' => 'ok',
                'data' => $data,
            );
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
