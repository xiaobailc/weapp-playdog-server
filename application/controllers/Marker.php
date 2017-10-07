<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Marker extends MY_Controller {
    public function index() {
        $latitude = $this->input->get('latitude');
        $longitude = $this->input->get('longitude');
        $range = $this->input->get('range') ?: '0.003'; //默认方圆300米范围

        $query = $this->db->where([
                'latitude <' => $latitude+$range,
                'latitude >' => $latitude-$range,
                'longitude <' => $longitude+$range,
                'longitude >' => $longitude-$range])
                ->get('markers');

        $markers = $query->result_array();

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

    public function store(){
        $result = LoginService::check();

        if ($result['code'] !== 0) {
            return;
        }

        $open_id = $result['data']['userInfo']['openId'];

        $marker = $this->db->where('open_id', $open_id)->get('markers')->row();

        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        
        if($marker){
            //获取上次打卡时间
            $last_marked_at = substr($marker->marked_at,0,10);
            $last = strtotime($last_marked_at);
            $now = time();
            $diff = $now - $last;
            if($diff < 86400) {
                //一天之内
                $continuous_day = $marker->continuous_day;
                $maximum_continuous_day = $marker->maximum_continuous_day;
            } else if ($diff < 86400*2){
                //连续第二天
                $continuous_day = $marker->continuous_day +1;
                $maximum_continuous_day = $continuous_day > $marker->maximum_continuous_day ? $continuous_day : $marker->maximum_continuous_day;
            }else{
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
        }else{
            //插入
            $data = [
                'open_id' => $open_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'marked_at' => date('Y-m-d H:i:s')
            ];
            $res = $this->db->insert('markers', $data);
        }

        if ($res) {
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
    
    public function update(){
        $result = LoginService::check();
        
        if ($result['code'] !== 0) {
            return;
        }
        
        $open_id = $result['data']['userInfo']['openId'];
        
        $this->db->where('open_id', $open_id);

        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');

        

        
        if($res){
            $response = array(
                'code' => 0,
                'message' => 'ok',
                'data' => array(
                    'markerInfo' => $data,
                ),
            );
        } else{
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
}
