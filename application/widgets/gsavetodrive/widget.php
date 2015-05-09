<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class gsavetodriveWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'gsavetodrive';

    /**
     * @var string
     */
    public $parent = 'gplusone';

    /**
     * @var string
     */
    public $fileUrl;

    /**
     * @var string
     */
    public $fileName;

    public function run()
    {
        if ($this->data['src']) {
            if (filter_var($this->data['src'], FILTER_VALIDATE_URL) === false) {
                $this->fileUrl = Bootstrap::$main->getServerHttpUrl() . $this->getUimagesUrl() . '/' . $this->data['src'];
            } else {
                $this->fileUrl = $this->data['src'];
            }

            if (empty($this->data['filename'])) {
                $this->fileName = basename($this->fileUrl);
            } else {
                $this->fileName = $this->data['filename'];
            }
        }

        $this->loadJS('https://apis.google.com/js/plusone.js');

        parent::run();
    }
}