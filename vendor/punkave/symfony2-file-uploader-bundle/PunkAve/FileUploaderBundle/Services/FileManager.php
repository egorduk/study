<?php

namespace PunkAve\FileUploaderBundle\Services;

use Helper\Helper;
use PunkAve\FileUploaderBundle\BlueImp\UploadHandler;

class FileManager
{
    protected $options;

    public function __construct($options) {
        $this->options = $options;
    }

    /**
     * Get a list of files already present. The 'folder' option is required. 
     * If you pass consistent options to this method and handleFileUpload with
     * regard to paths, then you will get consistent results.
     */
    public function getFiles($options = array()) {  //Get existing files from folder
        $options = array_merge($this->options, $options);
        $folder = $options['file_base_path'] . '/' . $options['folder'];
        //var_dump($options);
        if (file_exists($folder)) {
            $dirs = glob("$folder/*.*");
            //var_dump($dirs);
            $fullPath = isset($options['full_path']) ? $options['full_path'] : false;
            if ($fullPath) {
                return $dirs;
            }
            if (!is_array($dirs)) {
                $dirs = array();
            }
            $result = [];
            //$finfo = finfo_open(FILEINFO_MIME_TYPE);
            $f = $options['folder'];
            $arrayExplode = explode('/', $f);
            $slug = end($arrayExplode);
            $f = str_replace($slug, 'thumbnails_' . $slug, $f);
            $arrayAllowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            $arrayFiles = [];
            foreach($dirs as $dir) {
                $arrayFiles[$dir] = date("d.m.Y H:i", filemtime($dir));
            }
            asort($arrayFiles);
            foreach($arrayFiles as $dir => $dateUpload) {
                $file = new \stdClass();
                $file->name = iconv('CP1251', 'UTF-8', preg_replace('|^.+[\\/]|', '', $dir));/*preg_replace('|^.+[\\/]|', '', $dir);*/
                $file->size = Helper::getSizeFile(filesize($dir));
                $file->date_upload = $dateUpload;
                $extensionFile = Helper::getExtensionFile($dir);
                $file->thumbnail = (!in_array($extensionFile, $arrayAllowedExt) || !file_exists($options['file_base_path'] . '/' . $f . '/' . iconv('UTF-8', 'CP1251', $file->name))) ? '/study/web/bundles/images/icons/' . $extensionFile . '.png' : null;
                $result[] = $file;
            }
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Remove the folder specified by 'folder' and its contents.
     * If you pass consistent options to this method and handleFileUpload with
     * regard to paths, then you will get consistent results.
     */
    public function removeFiles($options = array()) {
        $options = array_merge($this->options, $options);
        $folder = $options['file_base_path'] . '/' . $options['folder'];
        if (!strlen(trim($options['file_base_path']))) {
            throw \Exception("file_base_path option looks empty, bailing out");
        }
        if (!strlen(trim($options['folder']))) {
            throw \Exception("folder option looks empty, bailing out");
        }
        system("rm -rf " . escapeshellarg($folder));
    }

    /**
     * Sync existing files from one folder to another. The 'fromFolder' and 'toFolder'
     * options are required. As with the 'folder' option elsewhere, these are appended
     * to the file_base_path for you, missing parent folders are created, etc. If 
     * 'fromFolder' does not exist no error is reported as this is common if no files
     * have been uploaded. If there are files and the sync reports errors an exception
     * is thrown.
     * 
     * If you pass consistent options to this method and handleFileUpload with
     * regard to paths, then you will get consistent results.
     */
    public function syncFiles($options = array())
    {
        $options = array_merge($this->options, $options);

        // We're syncing and potentially deleting folders, so make sure
        // we were passed something - make it a little harder to accidentally
        // trash your site
        if (!strlen(trim($options['file_base_path'])))
        {
            throw \Exception("file_base_path option looks empty, bailing out");
        }
        if (!strlen(trim($options['from_folder'])))
        {
            throw \Exception("from_folder option looks empty, bailing out");
        }
        if (!strlen(trim($options['to_folder'])))
        {
            throw \Exception("to_folder option looks empty, bailing out");
        }

        $from = $options['file_base_path'] . '/' . $options['from_folder'];
        $to = $options['file_base_path'] . '/' . $options['to_folder'];
        if (file_exists($from))
        {
            if (isset($options['create_to_folder']) && $options['create_to_folder'])
            {
                @mkdir($to, 0777, true);
            }
            elseif (!file_exists($to))
            {
                throw new \Exception("to_folder does not exist");
            }
            $result = null;
            system("rsync -a --delete " . escapeshellarg($from . '/') . " " . escapeshellarg($to), $result);
            if ($result !== 0)
            {
                throw new \Exception("Sync failed");
            }
            if (isset($options['remove_from_folder']) && $options['remove_from_folder'])
            {
                system("rm -rf " . escapeshellarg($from));
            }
        }
        else
        {
            // A missing from_folder is not an error. This is commonly the case
            // when syncing from something that has nothing attached to it yet, etc.
        }
    }
}
