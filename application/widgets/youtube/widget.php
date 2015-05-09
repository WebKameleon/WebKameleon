<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class youtubeWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'youtube';

    /**
     * @var string
     */
    public $video_url;

    public function init()
    {
        parent::init();

        $this->video_url = $this->getVideoUrl();
        
    }
    
    public function run()
    {
        parent::run();
        $this->loadJS('youtube.js');
        Bootstrap::$main->tokens->loadJQuery = true;       
        $this->video_url.=(strstr($this->video_url,'?')?'&':'?').'enablejsapi=1';
    }

    public function edit()
    {
        
        $this->check_scope('youtube',$_GET['page']);  
        
        require_once __DIR__ . '/../common/widget.php';
        commonWidget::loadFancybox();
    }

    /**
     * @return string
     */
    public function getVideoUrl()
    {
        if ($this->data['video_id'] && filter_var($this->data['video_id'], FILTER_VALIDATE_URL) !== false) {
            $video_url = $this->data['video_id'];
            $video_url = str_replace('/watch?v=', '/embed/', $video_url);
            $video_url = str_replace('&list=', '?list=', $video_url);
            return $video_url;
        }

        if ($this->data['playlist_id']) {
            return 'http://www.youtube.com/embed/videoseries?list=' . $this->data['playlist_id'] . '&rel=0';
        }

        if ($this->data['video_id']) {
            return 'http://www.youtube.com/embed/' . $this->data['video_id'] . '?rel=0';
        }

//        if ($this->data['playlist_id'] && ($video_id = $this->parseVideoId($this->data['playlist_id'])) !== false) {
//            return 'http://www.youtube.com/embed/videoseries?list=' . $video_id . '&rel=0';
//        }
//
//        if ($this->data['video_id'] && ($video_id = $this->parseVideoId($this->data['video_id'])) !== false) {
//            return 'http://www.youtube.com/embed/' . $video_id . '?rel=0';
//        }

        return null;
    }

    /**
     * @param string $video_id
     * @return bool|string
     */
    public function parseVideoId($video_id)
    {
        if (filter_var($video_id, FILTER_VALIDATE_URL) === false) {
            return $video_id;
        }

        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_id, $match)) {
            return $match[1];
        }

        return false;
    }
}