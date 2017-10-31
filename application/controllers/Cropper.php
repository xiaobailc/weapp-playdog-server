<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Cropper extends CI_Controller {
    public function index()
    {
        $config['upload_path']      = './uploads/';
        $config['allowed_types']    = '*';
        $config['encrypt_name']     = true;

        $this->load->library('upload', $config);
        $x = $this->input->post('x');
        $x1 = $this->input->get('x');

        if (!$this->upload->do_upload('petavatar')) {
            $response = array('error' => $this->upload->display_errors());
        } else {
            $response = array(
                'code' => 0,
                'message' => 'ok',
                'data' => $this->upload->data(),
                'x' => $x,
                'x1' => $x1
            );
        }
        //echo json_encode($response, JSON_FORCE_OBJECT);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
