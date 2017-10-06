<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class File extends CI_Controller {
    public function add() {
        $config['upload_path']      = '../../uploads/';
        $config['allowed_types']    = 'gif|jpg|png';
        $config['encrypt_name']     = true;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('petavatar')) {
            $response = array('error' => $this->upload->display_errors());
        } else {
            //$data = array('upload_data' => $this->upload->data());
            $response = array(
                'code' => 0,
                'message' => 'ok',
                'data' => array(
                    'fileInfo' => $this->upload->data(),
                ),
            );
        }

        echo json_encode($response, JSON_FORCE_OBJECT);
    }

    public function store(){
        echo json_encode([], JSON_FORCE_OBJECT);
    }
    
    public function update(){
        echo json_encode([], JSON_FORCE_OBJECT);
    }
}
