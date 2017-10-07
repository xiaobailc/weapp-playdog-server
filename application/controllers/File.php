<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class File extends CI_Controller {
    public function add() {
        $config['upload_path']      = './uploads/';
        $config['allowed_types']    = 'gif|jpg|png';
        $config['encrypt_name']     = true;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('petavatar')) {
            $response = array('error' => $this->upload->display_errors());
        } else {
            $response = array(
                'code' => 0,
                'message' => 'ok',
                'data' => $this->upload->data(),
            );
        }
        //echo json_encode($response, JSON_FORCE_OBJECT);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function store(){
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([]));
    }
    
    public function update(){
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([]));
    }
}
