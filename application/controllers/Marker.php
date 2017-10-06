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
            'data' => array(
                'markerInfo' => $markers,
            ),
        );

        echo json_encode($response, JSON_FORCE_OBJECT);
    }

    public function store(){
        $result = LoginService::check();

        if ($result['code'] !== 0) {
            return;
        }

        $open_id = $result['data']['userInfo']['openId'];

        //$marker = $this->db->where('open_id', $open_id)->get('markers')->row();

        //var_dump($marker);exit;

        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');

        $data = [
            'open_id' => $open_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'marked_at' => date('Y-m-d H:i:s')
        ];

        $res = $this->db->insert('markers', $data);
        if($res){
            unset($data['open_id']);
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
        echo json_encode($response, JSON_FORCE_OBJECT);
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

        $data = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'marked_at' => date('Y-m-d H:i:s')
        ];

        $res = $this->db->update('markers', $data);
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
        echo json_encode($response, JSON_FORCE_OBJECT);
    }
}
