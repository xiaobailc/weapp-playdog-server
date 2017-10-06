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
        $query = $this->db->get_where('dogs', ['open_id'=> $open_id], 1);

        var_dump($query->result());exit;

        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => array(
                'userInfo' => $result['data']['userInfo'],
            ),
        );

        echo json_encode($response, JSON_FORCE_OBJECT);
    }

    public function store(){
        echo json_encode([], JSON_FORCE_OBJECT);
    }
    
    public function update(){
        echo json_encode([], JSON_FORCE_OBJECT);
    }
}
