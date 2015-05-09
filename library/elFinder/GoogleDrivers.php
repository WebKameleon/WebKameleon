<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

abstract class elfinderGoogle
{
    /**
     * @var Google_Client
     */
    protected $client;

    public function __construct(Google_Client $client)
    {
        $this->client = $client;
        $this->init();
    }

    protected function init()
    {

    }

    /**
     * Returns root
     * @return array
     */
    abstract public function root();

    /**
     * Returns file info
     * @param string $path
     * @return array
     */
    abstract public function file($path);

    /**
     * Returns childrens list
     * @param $path
     * @return array
     */
    abstract public function children($path);

    /**
     * @param array $file
     * @return string|bool
     */
    abstract public function dirname($file);

    /**
     * @param array $file
     * @return string
     */
    abstract public function basename($file);

    /**
     * @param array $file
     * @return array
     */
    abstract public function stat($file);

    /**
     * @param string $path
     * @param string $name
     * @return array
     */
    abstract public function mkdir($path, $name);

    /**
     * @param string $path
     * @param string $name
     * @return array
     */
    abstract public function mkfile($path, $name);

    /**
     * @param array $file
     * @param string $dir
     * @param string $name
     * @return array
     */
    abstract public function copy($file, $dir, $name);

    /**
     * @param array $file
     * @param string $dir
     * @param string $name
     * @return array
     */
    abstract public function move($file, $dir, $name);

    /**
     * @param string $path
     * @return bool
     */
    abstract public function unlink($path);

    /**
     * @param string $path
     * @return bool
     */
    abstract public function rmdir($path);

    /**
     * @param string $content
     * @param string $dir
     * @param string $name
     * @param array $stat
     * @return array
     */
    abstract public function save($content, $dir, $name, $stat);

    /**
     * @param array $file
     * @return string mixed
     */
    abstract public function getContents($file);

    /**
     * @param array $file
     * @param string $content
     * @return bool
     */
    abstract public function putContents($file, $content);
}

class elfinderGoogleDrive extends elfinderGoogle
{
    /**
     * @var Google_DriveService
     */
    protected $drive;

    protected function init()
    {
        $this->drive = new Google_DriveService($this->client);
    }

    /**
     * @return array
     */
    public function root()
    {
        return $this->file('root');
    }

    /**
     * @param string $path
     * @return array
     */
    public function file($path)
    {
        
        return $this->drive->files->get($path);
    }

    /**
     * @param string $path
     * @return array
     */
    public function children($path)
    {
        $children = array();
        $tmp = $this->drive->children->listChildren($path);
        foreach ($tmp['items'] as $item) {
            $children[] = $item['id'];
        }
        
        return $children;
    }

    /**
     * @param array $file
     * @return bool|string
     */
    public function dirname($file)
    {
        if ($file['parents']) {
            return $file['parents'][0]['id'];
        }
        return false;
    }

    /**
     * @param array $file
     * @return string
     */
    public function basename($file)
    {
        $name = $file['title'];
        if (strpos($name, '.') === false && ($ext = elFinderVolumeGoogle::extensionDetect($file)) !== false) {
            $name .= '.' . $ext;
        }
        return $name;
    }

    /**
     * @param array $file
     * @return array
     */
    public function stat($file)
    {
        if (isset($file['explicitlyTrashed']) && $file['explicitlyTrashed']) return false;
        
        $stat = array(
            'size'  => $file['quotaBytesUsed'] + 0,
            'ts'    => strtotime($file['modifiedDate']),
            'mime'  => elFinderVolumeGoogle::mimetypeDetect($file),
            'read'  => $file['editable'],
            'write' => 0,
        );

        if ($stat['mime'] != 'directory') {
            $stat['tmb'] = $file['iconLink'];
        }

        if (empty($file['parents']) && defined('ELFINDER_IMG_PARENT_URL')) {
            $stat['tmb'] = $stat['icon'] = ELFINDER_IMG_PARENT_URL . '/img/volume_icon_gdrive.png';
        }

        return $stat;
    }

    /**
     * @param string $path
     * @param string $name
     * @return array
     */
    public function mkdir($path, $name)
    {
        $parent = new Google_ParentReference;
        $parent->setId($path);

        $file = new Google_DriveFile;
        $file->setTitle($name);
        $file->setMimeType('application/vnd.google-apps.folder');
        $file->setParents(array($parent));

        return $this->drive->files->insert($file);
    }

    /**
     * @param string $path
     * @param string $name
     * @return array
     */
    public function mkfile($path, $name)
    {
        $parent = new Google_ParentReference;
        $parent->setId($path);

        $file = new Google_DriveFile;
        $file->setTitle($name);
        $file->setParents(array($parent));

        return $this->drive->files->insert($file);
    }

    /**
     * @param array $file
     * @param string $dir
     * @param string $name
     * @return array
     */
    public function copy($file, $dir, $name)
    {
        $parent = new Google_ParentReference;
        $parent->setId($dir);

        $new = new Google_DriveFile;
        $new->setTitle($name);
        $new->setMimeType($file['mimeType']);
        $new->setParents(array(
            $parent
        ));

        return $this->drive->files->insert($new, array(
            'data' => $this->getContents($file)
        ));
    }

    /**
     * @param array $file
     * @param string $dir
     * @param string $name
     * @return array
     */
    public function move($file, $dir, $name)
    {
        $parent = new Google_ParentReference;
        $parent->setId($dir);

        $new = new Google_DriveFile($file);
        $new->setTitle($name);
        $new->setParents(array($parent));

        return $this->drive->files->update($file['id'], $new);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function unlink($path)
    {
        $this->drive->files->delete($path);
        return true;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function rmdir($path)
    {
        return $this->unlink($path);
    }

    /**
     * @param string $content
     * @param string $dir
     * @param string $name
     * @param array $stat
     * @return array
     */
    public function save($content, $dir, $name, $stat)
    {
        $parent = new Google_ParentReference;
        $parent->setId($dir);

        $file = new Google_DriveFile;
        $file->setTitle($name);
        $file->setParents(array($parent));

        if ($stat['mime']) {
            $file->setMimeType($stat['mime']);
        }

        return $this->drive->files->insert($file, array(
            'data' => $content
        ));
    }

    /**
     * @param array $file
     * @return string
     */
    public function getContents($file)
    {
        $mimeType = elFinderVolumeGoogle::mimetypeDetect($file);

        if (isset($file['exportLinks'][$mimeType])) {
            $downloadUrl = $file['exportLinks'][$mimeType];
        } else {
            $downloadUrl = $file['downloadUrl'];
        }

        return $this->client->getIo()->authenticatedRequest(
            new Google_HttpRequest($downloadUrl)
        )->getResponseBody();
    }

    /**
     * @param array $file
     * @param string $content
     * @return bool
     */
    public function putContents($file, $content)
    {
        return $this->drive->files->update($file['id'], new Google_DriveFile($file), array(
            'data' => $content
        ));
    }
}