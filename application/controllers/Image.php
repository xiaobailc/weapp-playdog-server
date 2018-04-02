<?php

defined('BASEPATH') or exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Conf as Conf;

class Image extends CI_Controller
{
    public function index($id)
    {
        $id = ($id-100200300) ^ 715827882;
        $debug = $_GET['debug'] ?? false;

        $name = $_GET['name'] ?? null;
        if (!$name || strlen($name)>20) {
            return $this->json(['code' => 1,'error' => '参数错误']);
        }

        $breed = $_GET['breed'] ?? null;
        if (!$breed) {
            return $this->json(['code' => 2,'error' => '参数错误']);
        }

        $jueyu = $_GET['jueyu'] ?? 0;
        $jueyu = intval($jueyu) == 1 ? true :false;

        $gender = $_GET['gender'] ?? 0;
        $gender = intval($gender) == 1 ? 1 :0;

        $value = $_GET['value'] ?? null;
        if (!$value || intval($value) !== $id) {
            return $this->json(['code' => 4,'error' => '参数错误']);
        }
        $value = $jueyu ? 50 : intval($value);
        $level = ceil($value/20);

        $avatar_url = $_GET['avatarurl'] ?? null;
        if (!$avatar_url) {
            return $this->json(['code' => 5,'error' => '参数错误']);
        }

        //$image_path = APPPATH.'views/images/shareimage.jpg';
        $image_path = APPPATH.'views/images/share_bg.png';
        $image_flower = APPPATH.'views/images/thy_flower.png';
        $image_outline = APPPATH.'views/images/thy_outline.png';
        $font_path = APPPATH.'views/fonts/HappyZcool-seguiemj.ttf';
        //$font_path = APPPATH.'views/fonts/MicrosoftYaHei.ttf';
        //if (!file_exists($image_path) || !file_exists($image_open) || !file_exists($font_path)) {
        if (!file_exists($image_path) || !file_exists($image_flower) || !file_exists($image_outline) || !file_exists($font_path)) {
            return $this->json([
                'code' => 5,
                'error' => '图片模板或字体不存在',
            ]);
        }

        $text_mail = [
            '2018年狗狗的桃花运势可以用三个字形容——“狗不理”，建议多买一些毛绒狗狗来弥补狗狗创伤的心灵！',
            '多带ta出去散散步吧！毕竟狗生漫长，机会还有很多……',
            '如果上天再给我一次重来的机会，我会对ta说：汪(I)汪(❤)汪(Y)！如果要在这份“汪”上加个期限的话，我希望是主人下班前。',
            '我对你摇尾巴，你却视而不见。幸运的是这段单恋之苦，马上会否极泰来哦。所以，遛弯记得多瞅瞅。',
            '这段时间桃花指数不低，但要防止不利对象出现，且骑且珍惜。',
            '今年桃花运在及格线徘徊。俗话说，人靠衣装狗靠扮，三分天注定，七分看打扮。狗子能否找到心仪的对象，还需要主人助力一把哇。',
            '问狗世间情为何物，你若“汪汪”一声，我愿和你浪迹天涯！',
            '要瞪大我汪的双眼看清是好桃花还是烂桃花，避免「及时行乐」，建议主子系好牵引绳，路边的野花不要采哦！',
            '今年桃花运可以说是相当不错啦！如果狗子成年了，建议多出去见见母汪，也许啥时候就碰到真命天子了呢！',
            '划船不用桨，全靠浪！如此高分桃花运，狗生无憾了!'
        ];

        $text_femail = [
            '得了公主病，却没公主命，桃花碎了一地，好运随风而去，宝宝不哭！',
            '今年狗仔桃花运势稍有低迷，朝三暮四，不知所从，温馨警示狗仔：如果有人甘愿做你的铠甲，就别逞强装作百毒不侵的模样！',
            '如果上天再给我一次重来的机会，我会对我的二汪说：你丫，再骑我一下试试！',
            '情若难了，梦也难了，愿汪生还能再度拥抱！倚高楼，望窗外，盼汪归来，咚咚一声敲门，原来是外卖！',
            '今夜寂寞如海，今夜相思成恋。对你的爱已化成这片寂寞的海，想你的心已在我狗脑中泛滥成灾！',
            '撒尿之时，与你相识相知，追逐之间，与你朝夕相伴。',
            '桃花运势不差，可惜汪内心戏是：桃花潭水深千尺 不及“汪”伦送我情。',
            '今年汪仔桃花运还是很不错的，可以在被追求中慢慢选择，多去狗子集中的公园，享受一种被宠爱的感觉也还不错！',
            '社交手腕高级，汪友遍及五湖四海，今年，公园里追求你的狗子还是一如既往地多到让你纠结和卵疼，桃花运很旺盛!',
            '简直就是母汪中的极品贤妻良母！每次出门不乏众多追求者，这其中定有你的真命天子，也许明年你就升级为汪妈了呢！'
        ];

        $text_jueyu = [
          '桃花屋里桃花庵，桃花庵下桃花仙，桃花仙人种桃树，反正与我都无关！',
          '我的肉体虽然无法与你天衣无缝，但我的心灵永远和你情投意合，我是长命狗，我为自己打call!'
        ];

        $text = $gender ? $text_mail[ceil($value / 10) - 1] : $text_femail[ceil($value / 10) - 1];
        $text = $jueyu ? $text_jueyu[$gender] : $text;

        $width = 750;
        $height = 1080;
        $avatarWidth = 180;
        $avatarX = 245;
        $avatarY = 65;

        $textX = 320;
        $textY_name = 350;
        $textY_breed = 420;
        $textY_value = 490;
        $flowerY = 515;
        $flowerX = 320;
        $textY_text = 695;
        $textSize = 36;
        $maxWidth = 630;

        //创建画布
        $img = new Imagick();
        $img->newImage($width, $height, new ImagickPixel('white'));

        //获取分享图模板
        $imgShare = new Imagick($image_path);
        $imgFlower = new Imagick($image_flower);
        $imgOutline = new Imagick($image_outline);

        //生成头像
        $avatar = new Imagick($avatar_url);
        $avatar->resizeImage($avatarWidth, $avatarWidth, Imagick::FILTER_CATROM, 1);
        //合并头像
        $img->compositeImage($avatar, imagick::COMPOSITE_OVER, $avatarX, $avatarY);
        //合并分享图模板
        $img->compositeImage($imgShare, imagick::COMPOSITE_OVER, 0, 0);

        //合并桃花模板
        for ($i=0; $i<5; $i++) {
            $img->compositeImage($level>$i ? $imgFlower : $imgOutline, imagick::COMPOSITE_OVER, $flowerX, $flowerY);
            $flowerX += 60;
        }

        //添加文字
        //list($textSize, $textY, $note, $box) = $this->getSize($note, $textSize, $font_path, $textWidth, $textHeight);
        // $image = new Imagick();
        // $draw = new ImagickDraw();
        // $draw->setFont($font_path);
        // $draw->setStrokeAntialias(true);
        // $draw->setTextAntialias(true);

        // list($textSize, $textY, $note, $box) = $this->getTextSize($image, $draw, $note, 70, 700, 170);

        // if ($debug) {
        //     echo 'width:'.$textWidth.' ';
        //     echo 'height:'.$textHeight.' ';
        //     echo 'size:'.$textSize.' ';
        //     echo 'textY:'.$textY.' ';
        //     var_dump($box);
        //     var_dump($note);
        //     exit;
        // }
        $draw = new ImagickDraw();
        $draw->setFillColor(new ImagickPixel('#444444'));

        $draw->setFontSize($textSize);
        $draw->setFont($font_path);
        //$draw->setTextAlignment(Imagick::ALIGN_CENTER);
        $draw->annotation($textX, $textY_name, $name);
        $img->drawImage($draw);
        $draw->annotation($textX, $textY_breed, $breed);
        $img->drawImage($draw);
        $draw->annotation($textX, $textY_value, $value);
        $img->drawImage($draw);

        //宣言
        list($line, $metrics) = $this->getTextRows($img, $draw, $text, $maxWidth, 9999);
        //$line = str_split($text);
        //$line = implode("\n", $line);
        $draw->setTextInterlineSpacing(22);
        $draw->annotation(70, $textY_text, $line);
        $img->drawImage($draw);
        $img->setImageFormat('jpg');
        //var_dump($img);exit;

        header('Content-Type: image/jpeg');
        echo $img;

        return;
    }

    private function getTextSize($img, $draw, $text, $fontsize, $maxWidth, $maxHeight)
    {
        $draw->setFontSize($fontsize);
        list($line, $metrics) = $this->getTextRows($img, $draw, $text, $maxWidth, $maxHeight);
        if ($line === false) {
            $fontsize = $fontsize - 2;
            return $this->getTextSize($img, $draw, $text, $fontsize, $maxWidth, $maxHeight);
        } else {
            $ty = 255 + ($maxHeight - $metrics['textHeight']) / 2 + $metrics['ascender'];
            return [$fontsize, $ty, $line, $metrics];
        }
    }

    private function getTextRows($img, $draw, $text, $maxWidth, $maxHeight)
    {
        $words = [];
        $content = $teststr = '';
        for ($i = 0; $i < mb_strlen($text); ++$i) {
            $words[] = mb_substr($text, $i, 1);
        }

        for ($j = 0; $j <= $i; $j++) {
        //foreach ($words as $l) {
            $teststr = $content.($words[$j] ?? '');
            $metrics = $img->queryFontMetrics($draw, $teststr);
            //echo "$teststr::width({$metrics['textWidth']})::height({$metrics['textHeight']})\n";
            // 判断拼接后的字符串是否超过预设的宽度
            if ($metrics['textWidth'] > $maxWidth && ($content !== '')) {
                $content .= "\n";
            }
            if ($metrics['textHeight'] > $maxHeight) {
                return [false, $metrics];
            }
            $content .= ($words[$j] ?? '');
        }

        return [$content, $metrics];
    }
}
