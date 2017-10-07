<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Dog extends CI_Controller {
    public function index() {
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

        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => $dogInfo,
        );

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
        
                $name = $this->input->post('name');
                $breed = $this->input->post('breed');
                $avatarUrl = $this->input->post('avatarUrl');
        
                $data = [
                    'open_id' => $open_id,
                    'name' => $name,
                    'breed' => $breed,
                    'avatar_url' => $avatarUrl,
                    'created_at' => date('Y-m-d H:i:s')
                ];
        
                $res = $this->db->insert('dogs', $data);
                if($res){
                    unset($data['open_id']);
                    $response = [
                        'code' => 0,
                        'message' => 'ok',
                        'data' => $data,
                    ];
                } else{
                    $error = $this->db->error();
                    $response = [
                        'code' => $error['code'],
                        'message' => $error['message'],
                        'data' => $data,
                    ];
                }
                //echo json_encode($response, JSON_FORCE_OBJECT);
                $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
    }
    
    public function update(){
        echo json_encode([], JSON_FORCE_OBJECT);
    }
}
