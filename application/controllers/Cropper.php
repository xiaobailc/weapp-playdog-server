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
            $config_crop = [
                'image_library' => 'gd2',
                'source_image' => $data['full_path'],
                'quality' => '50%',
                'maintain_ratio' => false,
                'width' => $width,
                'height' => $height,
                'x_axis' => $x,
                'y_axis' => $y
            ];
            $this->load->library('image_lib', $config_crop);

            if (!$this->image_lib->crop()) {
                $error = $this->image_lib->display_errors();
                $this->image_lib->clear();
                $response = [
                    'error' => $error,
                ];
            } else {
                $this->image_lib->clear();
                //生成大缩略图（替换）
                $config_big_thumb = $this->config->item("config_big_thumb");
                $config_big_thumb['source_image'] = $data['full_path'];
                $this->image_lib->initialize($config_big_thumb);
                $this->image_lib->resize();
                //生成小缩略图（副本）
                $config_small_thumb = $this->config->item("config_small_thumb");
                $config_small_thumb['source_image'] = $data['full_path'];
                $this->image_lib->initialize($config_small_thumb);
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
