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
        $y = $this->input->post('y');
        $width = $this->input->post('width');
        $height = $this->input->post('height');

        if (!$this->upload->do_upload('petavatar')) {
            $response = [
                'error' => $this->upload->display_errors()
            ];
        } else {
            $data = $this->upload->data();
            //裁剪图片
            $config = [
                'image_library' => 'gd2',
                'source_image' => $data['full_path'],
                'quality' => '50%',
                'width' => $width,
                'height' => $height,
                'x_axis' => $x,
                'y_axis' => $y
            ];
            $this->load->library('image_lib', $config);

            if (!$this->image_lib->crop()) {
                $error = $this->image_lib->display_errors();
                $this->image_lib->clear();
                $response = [
                    'error' => $error,
                ];
            } else {
                $this->image_lib->clear();
                //生成缩略图
                $config = [
                    'image_library' => 'gd2',
                    'source_image' => $data['full_path'],
                    'quality' => '80%',
                    'width' => 100,
                    'height' => 100,
                    'create_thumb' => true,
                ];
                $this->image_lib->initialize($config);
                $this->image_lib->resize();
                //成功
                $response = [
                    'code' => 0,
                    'message' => 'ok',
                    'data' => $data
                ];
            }
        }
        //echo json_encode($response, JSON_FORCE_OBJECT);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
