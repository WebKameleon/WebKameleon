<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';

require_once 'elFinder/GoogleDrivers.php';

class elFinderVolumeGoogle extends elFinderVolumeDriver
{
    const SESSION_ROOT_KEY     = 'google_drive_root';
    const SESSION_FILES_KEY    = 'google_drive_files';
    const SESSION_CHILDREN_KEY = 'google_drive_children';

    protected $driverId = 'g';

    /**
     * @var array
     */
    protected $files;

    /**
     * @var array
     */
    protected $children;

    /**
     * @var elfinderGoogle[]
     */
    protected $drivers;

    public function __construct()
    {
        $this->options['icon'] = (defined('ELFINDER_IMG_PARENT_URL') ? ELFINDER_IMG_PARENT_URL : '') . '/img/volume_icon_local.png';
    }

    /**
     * @param array $file
     * @return string
     */
    public static function mimetypeDetect($file)
    {
        switch ($file['mimeType'])
        {
            case 'application/vnd.google-apps.folder':
                return 'directory';

            //case 'application/vnd.google-apps.drawing':
            //    return 'image/svg+xml';

            case 'application/vnd.google-apps.document':
                return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

            case 'application/vnd.google-apps.spreadsheet':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        return $file['mimeType'];
    }

    /**
     * @param array $file
     * @return string|bool
     */
    public static function extensionDetect($file)
    {
        return array_search(self::mimetypeDetect($file), self::$mimetypes);
    }

    /**
     * @return Google_Client
     */
    protected function getGoogleClient()
    {
        return $this->options['client'];
    }

    public function mount(array $opts)
    {
        $this->options = array_merge($this->options, $opts);
        $this->id = $this->driverId . (elFinder::$volumesCnt++) . '_';
        $this->separator = DIRECTORY_SEPARATOR;
        $this->today = mktime(0,0,0, date('m'), date('d'), date('Y'));
        $this->yesterday = $this->today - 86400;

        // default file attribute
        $this->defaults = array(
            'read'    => true,
            'write'   => false,
            'locked'  => false,
            'hidden'  => false
        );

        // root attributes
        $this->attributes[] = array(
            'pattern' => '~^' . preg_quote(DIRECTORY_SEPARATOR) . '$~',
            'locked'  => true,
            'hidden'  => false
        );

        $this->disabled = array();
        $this->mimeDetect = 'internal';
        $this->treeDeep = 1;
        $this->tmbSize  = 48;

        $this->diabled = array(
            'archive', 'extract'
        );
        
        

        $client = $this->getGoogleClient();
        $this->drivers = array(
            'g' => new elfinderGoogleDrive($client)
        );


        if (!$client->isAccessTokenExpired())
        {
            $this->loadFromSession();
            return $this->mounted = true;
        }
        
        return $this->mounted = false;
    }

    protected function loadFromSession()
    {
        $this->root = 'root';
        $this->rootName = 'Google Drive';

        
        $this->files = Bootstrap::$main->session(self::SESSION_FILES_KEY);
        if ($this->files == null || @$_GET['debug'] == 1) {
            $this->files = $this->children = array();
            $this->files[$this->root] = array(
                'id' => $this->root,
                'quotaBytesUsed' => 0,
                'modifiedDate' => date('c'),
                'mimeType' => 'directory',
                'editable' => 1,
            );
            foreach ($this->drivers as $driverId => $driver) {
                $root = $driver->root();
                $rootId = $driverId . '_' . $root['id'];
                $this->files[$rootId] = $root;
                $this->children[$this->root][$rootId] = $rootId;
            }
        } else {
            $this->children = Bootstrap::$main->session(self::SESSION_CHILDREN_KEY);
        }
        
    }

    protected function saveToSession()
    {
        Bootstrap::$main->session(self::SESSION_FILES_KEY, $this->files);
        Bootstrap::$main->session(self::SESSION_CHILDREN_KEY, $this->children);
    }

    /**
     * @param $path
     * @return array|Google_DriveFile
     */
    public function getfile(&$path)
    {
        $path = $this->_path($path);

        if (array_key_exists($path, $this->files) == false) {
            list ($driverId, $fileId) = explode('_', $path, 2);
            if (!isset($this->drivers[$driverId])) {
                return false;
            }
            try {
                $this->files[$path] = $this->drivers[$driverId]->file($fileId);
                $this->saveToSession();
            } catch (Google_ServiceException $e) {
                return false;
            }
        }
        return $this->files[$path];
    }

    /**
     * Return parent directory path
     *
     * @param  string $path  file path
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _dirname($path)
    {
        $file = $this->getfile($path);

        list ($driverId, $fileId) = explode('_', $path, 2);
        $parentId = $this->drivers[$driverId]->dirname($file);

        if ($parentId === false) {
            $parentId = $this->root;
        } else {
            $parentId = $driverId . '_' . $parentId;
        }

        return $parentId;
    }

    /**
     * Return file name
     *
     * @param  string $path  file path
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _basename($path)
    {
        $file = $this->getfile($path);

        list ($driverId, $fileId) = explode('_', $path, 2);
        return $this->drivers[$driverId]->basename($file);
    }

    /**
     * Join dir name and file name and return full path.
     * Some drivers (db) use int as path - so we give to concat path to driver itself
     *
     * @param  string $dir   dir path
     * @param  string $name  file name
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _joinPath($dir, $name)
    {
        return $name;
    }

    /**
     * Return normalized path
     *
     * @param  string $path  file path
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _normpath($path)
    {
        return $this->_path($path);
    }

    /**
     * Return file path related to root dir
     *
     * @param  string $path  file path
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _relpath($path)
    {
        return $this->_path($path);
    }

    /**
     * Convert path related to root dir into real path
     *
     * @param  string $path  rel file path
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _abspath($path)
    {
        return $this->_path($path);
    }

    /**
     * Return fake path started from root dir.
     * Required to show path on client side.
     *
     * @param  string $path  file path
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _path($path)
    {
        if ($path == 'root' || $path == '.' || $path == $this->root)
            return $this->root;

        return $path;
    }

    /**
     * Return true if $path is children of $parent
     *
     * @param  string $path    path to check
     * @param  string $dir  parent path
     * @return bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _inpath($path, $dir)
    {
        return array_key_exists($path, $this->_scandir($dir));
    }

    protected function encode($path)
    {
        return $this->id . $this->_path($path);
    }

    protected function decode($hash)
    {
        return substr($hash, strlen($this->id));
    }

    /**
     * Return stat for given path.
     * Stat contains following fields:
     * - (int)    size    file size in b. required
     * - (int)    ts      file modification time in unix time. required
     * - (string) mime    mimetype. required for folders, others - optionally
     * - (bool)   read    read permissions. required
     * - (bool)   write   write permissions. required
     * - (bool)   locked  is object locked. optionally
     * - (bool)   hidden  is object hidden. optionally
     * - (string) alias   for symlinks - link target path relative to root path. optionally
     * - (string) target  for symlinks - link target path. optionally
     *
     * If file does not exists - returns empty array or false.
     *
     * @param  string $path    file path
     * @return array|false
     * @author Dmitry (dio) Levashov
     **/
    protected function _stat($path)
    {
        if ($path == $this->root) {
            return array(
                'size'  => 0,
                'ts'    => time(),
                'mime'  => 'directory',
                'read'  => 1,
                'write' => 0,
            );
        }

        if ($file = $this->getfile($path)) {
            list ($driverId, $fileId) = explode('_', $path, 2);
            return $this->drivers[$driverId]->stat($file);
        }

        return false;
    }

    /**
     * Return true if path is dir and has at least one childs directory
     *
     * @param  string $path  dir path
     * @return bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _subdirs($path)
    {
        return !!count($this->_scandir($path));
    }

    /**
     * Return object width and height
     * Ususaly used for images, but can be realize for video etc...
     *
     * @param  string $path  file path
     * @param  string $mime  file mime type
     * @return string
     * @author Dmitry (dio) Levashov
     **/
    protected function _dimensions($path, $mime)
    {
        $file = $this->getfile($path);
        return isset($file['imageMediaMetadata'])
             ? $file['imageMediaMetadata']['width'] . 'x' . $file['imageMediaMetadata']['height']
             : false;
    }

    /**
     * Return files list in directory
     *
     * @param  string $path  dir path
     * @return array
     * @author Dmitry (dio) Levashov
     **/
    protected function _scandir($path)
    {
        $this->getfile($path);
        if (array_key_exists($path, $this->children) == false) {
            list ($driverId, $fileId) = explode('_', $path, 2);
            $this->children[$path] = array();
            foreach ($this->drivers[$driverId]->children($fileId) as $childId) {
                $id = $driverId . '_' . $childId;
                $this->children[$path][$id] = $id;
            }
            $this->saveToSession();
        }

        return $this->children[$path];
    }

    /**
     * Open file and return file pointer
     *
     * @param  string $path  file path
     * @param  bool $write open file for writing
     * @return resource|false
     * @author Dmitry (dio) Levashov
     **/
    protected function _fopen($path, $mode = "rb")
    {
        $fp = tmpfile();
        fwrite($fp, $this->_getContents($path));
        fseek($fp, 0);
        return $fp;
    }

    /**
     * Close opened file
     *
     * @param  resource $fp    file pointer
     * @param  string $path  file path
     * @return bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _fclose($fp, $path = '')
    {
        return @fclose($fp);
    }

    /**
     * Create dir and return created dir path or false on failed
     *
     * @param  string $path  parent dir path
     * @param string $name  new directory name
     * @return string|bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _mkdir($path, $name)
    {
        list ($driverId, $target) = explode('_', $path, 2);
        $file = $this->drivers[$driverId]->mkdir($target, $name);

        $fileId = $driverId . '_' . $file['id'];

        $this->files[$fileId] = $file;
        $this->children[$path][$fileId] = $fileId;
        $this->saveToSession();

        return $fileId;
    }

    /**
     * Create file and return it's path or false on failed
     *
     * @param  string $path  parent dir path
     * @param string $name  new file name
     * @return string|bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _mkfile($path, $name)
    {
        list ($driverId, $target) = explode('_', $path, 2);
        $file = $this->drivers[$driverId]->mkfile($target, $name);

        $fileId = $driverId . '_' . $file['id'];

        $this->files[$fileId] = $file;
        $this->children[$path][$fileId] = $fileId;
        $this->saveToSession();

        return $fileId;
    }

    /**
     * Create symlink
     *
     * @param  string $path    file to link to
     * @param  string $dir     folder to create link in
     * @param  string $name    symlink name
     * @return bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _symlink($path, $dir, $name)
    {
        return false;
    }

    /**
     * Copy file into another file (only inside one volume)
     *
     * @param  string $path  source file path
     * @param  string $dir   target dir path
     * @param  string $name  file name
     * @return bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _copy($path, $dir, $name)
    {
        list ($driverId, $target) = explode('_', $dir, 2);
        $file = $this->drivers[$driverId]->copy($this->getfile($path), $target, $name);

        $fileId = $driverId . '_' . $file['id'];

        $this->files[$fileId] = $file;
        $this->children[$dir][$fileId] = $fileId;
        $this->saveToSession();

        return true;
    }

    /**
     * Move file into another parent dir.
     * Return new file path or false.
     *
     * @param  string $path  source file path
     * @param  string $dir   target dir path
     * @param  string $name  file name
     * @return string|bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _move($path, $dir, $name)
    {
        list ($driverId, $target) = explode('_', $dir, 2);
        $file = $this->drivers[$driverId]->move($this->getfile($path), $target, $name);

        $fileId = $driverId . '_' . $file['id'];

        $this->files[$fileId] = $file;
        foreach ($this->children as $k => $children) {
            unset($this->children[$k][$path]);
        }
        $this->children[$dir][$fileId] = $fileId;
        $this->saveToSession();

        return $path;
    }

    /**
     * Remove file
     *
     * @param  string $path  file path
     * @return bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _unlink($path)
    {
        list ($driverId, $fileId) = explode('_', $path, 2);
        $this->drivers[$driverId]->unlink($fileId);

        unset($this->files[$path]);
        foreach ($this->children as $k => $children) {
            unset($this->children[$k][$path]);
        }
        $this->saveToSession();

        return true;
    }

    /**
     * Remove dir
     *
     * @param  string $path  dir path
     * @return bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _rmdir($path)
    {
        list ($driverId, $target) = explode('_', $path, 2);
        $this->drivers[$driverId]->rmdir($target);

        unset($this->children[$path]);
        unset($this->files[$path]);
        foreach ($this->children as $k => $children) {
            unset($this->children[$k][$path]);
        }
        $this->saveToSession();

        return true;
    }

    /**
     * Create new file and write into it from file pointer.
     * Return new file path or false on error.
     *
     * @param  resource $fp   file pointer
     * @param  string $dir  target dir path
     * @param  string $name file name
     * @param  array $stat file stat (required by some virtual fs)
     * @return bool|string
     * @author Dmitry (dio) Levashov
     **/
    protected function _save($fp, $dir, $name, $stat)
    {
        $content = '';
        while (!feof($fp)) {
            $content .= fread($fp, 8192);
        }

        list ($driverId, $target) = explode('_', $dir, 2);
        $file = $this->drivers[$driverId]->save($content, $target, $name, $stat);

        $fileId = $driverId . '_' . $file['id'];

        $this->files[$fileId] = $file;
        $this->children[$dir][$fileId] = $fileId;
        $this->saveToSession();

        return $fileId;
    }

    /**
     * Get file contents
     *
     * @param  string $path  file path
     * @return string|false
     * @author Dmitry (dio) Levashov
     **/
    protected function _getContents($path)
    {
        list ($driverId, $fileId) = explode('_', $path, 2);
        return $this->drivers[$driverId]->getContents($this->getfile($path));
    }

    /**
     * Write a string to a file
     *
     * @param  string $path     file path
     * @param  string $content  new file content
     * @return bool
     * @author Dmitry (dio) Levashov
     **/
    protected function _filePutContents($path, $content)
    {
        list ($driverId, $fileId) = explode('_', $path, 2);
        $file = $this->drivers[$driverId]->putContents($this->getfile($path), $content);

        $fileId = $driverId . '_' . $file['id'];

        $this->files[$fileId] = $file;
        $this->saveToSession();
        return true;
    }

    /**
     * Extract files from archive
     *
     * @param  string $path file path
     * @param  array $arc  archiver options
     * @return bool
     * @author Dmitry (dio) Levashov,
     * @author Alexey Sukhotin
     **/
    protected function _extract($path, $arc)
    {
        return false;
    }

    /**
     * Create archive and return its path
     *
     * @param  string $dir    target dir
     * @param  array $files  files names list
     * @param  string $name   archive name
     * @param  array $arc    archiver options
     * @return string|bool
     * @author Dmitry (dio) Levashov,
     * @author Alexey Sukhotin
     **/
    protected function _archive($dir, $files, $name, $arc)
    {
        return false;
    }

    /**
     * Detect available archivers
     *
     * @return void
     * @author Dmitry (dio) Levashov,
     * @author Alexey Sukhotin
     **/
    protected function _checkArchivers()
    {
        return false;
    }
}