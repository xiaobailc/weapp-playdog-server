<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Marker extends MY_Controller
{
    public function index()
    {
        $latitude = $this->input->get('latitude');
        $longitude = $this->input->get('longitude');
        $open_id = $this->input->get('id');
        $range = $this->input->get('range') ?: '0.003'; //默认方圆300米范围

        $markers = $this->db->select('markers.open_id as id, markers.latitude, markers.longitude, markers.marked_at, markers.continuous_day as cd, markers.maximum_continuous_day as mcd, dogs.name, dogs.breed, dogs.avatar_url as avatarUrl')
                ->from('markers')
                ->join('dogs', 'dogs.open_id = markers.open_id')
                ->where([
                'markers.latitude <' => $latitude+$range,
                'markers.latitude >' => $latitude-$range,
                'markers.longitude <' => $longitude+$range,
                'markers.longitude >' => $longitude-$range])
                ->get()->result_array();

        array_walk($markers, function (&$item, $key, $open_id) {
            if ($item['id']== $open_id) {
                $item['myself'] = true;
                $today = substr($item['marked_at'], 0, 10);
                if ($today != date('Y-m-d')) {
                    $item['hide'] = true;
                }
            }
        }, $open_id);

        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => $markers,
        );

        //echo json_encode($response, JSON_FORCE_OBJECT);
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
        $dogName = $this->input->post('dogName');
        $dogAvatarUrl = $this->input->post('dogAvatarUrl');
        $now = time();
        
        //插入地图标记表
        $marker = $this->db->where('open_id', $open_id)->get('markers')->row();
        if ($marker) {
            //获取上次打卡时间
            $last_marked_at = substr($marker->marked_at, 0, 10);
            $last = strtotime($last_marked_at);
            $diff = $now - $last;
            if ($diff < 86400) {
                //一天之内
                $continuous_day = $marker->continuous_day;
                $maximum_continuous_day = $marker->maximum_continuous_day;
            } else if ($diff < 86400*2) {
                //连续第二天
                $continuous_day = $marker->continuous_day +1;
                $maximum_continuous_day = $continuous_day > $marker->maximum_continuous_day ? $continuous_day : $marker->maximum_continuous_day;
            } else {
                //不连续
                $continuous_day = 1;
                $maximum_continuous_day = $marker->maximum_continuous_day;
            }

            //更新
            $data = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'marked_at' => date('Y-m-d H:i:s', $now),
                'continuous_day' => $continuous_day,
                'maximum_continuous_day' => $maximum_continuous_day
            ];
            $res = $this->db->where('open_id', $open_id)->update('markers', $data);
        } else {
            //插入
            $data = [
                'open_id' => $open_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'marked_at' => date('Y-m-d H:i:s'),
                'continuous_day' => 1,
                'maximum_continuous_day' => 1
            ];
            $res = $this->db->insert('markers', $data);
        }

        if ($res) {
            //插入动态表
            $this->db->insert('dynamics', [
                'open_id' => $open_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'marked_at' => date('Y-m-d H:i:s', $now),
                'master_name' => $result['data']['userInfo']['nickName'],
                'master_avatar_url' => $result['data']['userInfo']['avatarUrl'],
                'dog_name' => $dogName,
                'dog_avatar_url' => $dogAvatarUrl
            ]);
            unset($data['open_id']);
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
    
    public function update()
    {
        $result = LoginService::check();
        
        if ($result['code'] !== 0) {
            return;
        }
        
        $open_id = $result['data']['userInfo']['openId'];
        
        $this->db->where('open_id', $open_id);

        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');

        if ($res) {
            $response = array(
                'code' => 0,
                'message' => 'ok',
                'data' => array(
                    'markerInfo' => $data,
                ),
            );
        } else {
            $error = $this->db->error();
            $response = array(
                'code' => $error['code'],
                'message' => $error['message'],
                'data' => array(
                    'markerInfo' => $data,
                ),
            );
        }
        //echo json_encode($response, JSON_FORCE_OBJECT);
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
    }

    public function rank()
    {
        $result = LoginService::check();
        
        if ($result['code'] !== 0) {
            return;
        }
        
        $open_id = $result['data']['userInfo']['openId'];
        //获得当日时间
        $today = date("Y-m-d");

        //暂时采取全国排名
        $rankInfos = $this->db->select('markers.open_id as id, markers.continuous_day as cd, markers.maximum_continuous_day as mcd, dogs.name, dogs.breed, dogs.avatar_url as avatarUrl, dogs.like_num as likeNum, likes.liked_at as likeAt')
                ->from('markers')
                ->join('dogs', 'dogs.open_id = markers.open_id')
                ->join('likes', "likes.master_id = markers.open_id and likes.follow_id = '$open_id' and likes.liked_at > '$today'", 'left')
                ->order_by('markers.continuous_day', 'DESC')
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
