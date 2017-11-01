<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;

class Dynamic extends CI_Controller
{
    public function index()
    {
        $latitude = $this->input->get('latitude')?:0;
        $longitude = $this->input->get('longitude')?:0;
        $range = $this->input->get('range')?: 0.1;

        $this->db->from('markers');
        if ($latitude && $longitude) {
            $this->db->where([
                'latitude <' => $latitude+$range,
                'latitude >' => $latitude-$range,
                'longitude <' => $longitude+$range,
                'longitude >' => $longitude-$range
            ]);
        }
        $this->db->select('markers.*')
            ->join('(select open_id, max(marked_at) as time from markers group by open_id) as b', 'markers.open_id = b.open_id and markers.marked_at = b.time')
            ->order_by('marked_at', 'DESC')
            ->limit(50);
        $query = $this->db->get();
        $dynamicInfos = $query->result_array();
        
        $this->load->helper('url');
        array_walk($dynamicInfos, function (&$item, $key) {
            $item['dog_avatar_url'] = base_url('uploads/'.$item['dog_avatar_url']);
        });

        $response = array(
            'code' => 0,
            'message' => 'ok',
            'data' => $dynamicInfos,
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
