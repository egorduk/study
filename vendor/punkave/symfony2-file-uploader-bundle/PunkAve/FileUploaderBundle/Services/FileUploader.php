<?php

namespace PunkAve\FileUploaderBundle\Services;

class FileUploader
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Get a list of files already present. The 'folder' option is required. 
     * If you pass consistent options to this method and handleFileUpload with
     * regard to paths, then you will get consistent results.
     */
    public function getFiles($options = array())
    {
        return $this->options['file_manager']->getFiles($options);
    }

    /**
     * Remove the folder specified by 'folder' and its contents.
     * If you pass consistent options to this method and handleFileUpload with
     * regard to paths, then you will get consistent results.
     */
    public function removeFiles($options = array())
    {
        return $this->options['file_manager']->removeFiles($options);
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
        return $this->options['file_manager']->syncFiles($options);
    }

    /**
     * Handles a file upload. Call this from an action, after validating the user's
     * right to upload and delete files and determining your 'folder' option. A good
     * example:
     *
     * $id = $this->getRequest()->get('id');
     * // Validate the id, make sure it's just an integer, validate the user's right to edit that 
     * // object, then...
     * $this->get('punkave.file_upload').handleFileUpload(array('folder' => 'photos/' . $id))
     * 
     * DOES NOT RETURN. The response is generated in native PHP by BlueImp's UploadHandler class.
     *
     * Note that if %file_uploader.file_path%/$folder already contains files, the user is 
     * permitted to delete those in addition to uploading more. This is why we use a
     * separate folder for each object's associated files.
     *
     * Any passed options are merged with the service parameters. You must specify
     * the 'folder' option to distinguish this set of uploaded files
     * from others.
     *
     */
    public function handleFileUpload($options = array())
    {
        if (!isset($options['folder'])) {
            throw new \Exception("You must pass the 'folder' option to distinguish this set of files from others");
        }
        $options = array_merge($this->options, $options);
        $allowedExtensions = $options['allowed_extensions'];
        // Build a regular expression like /(\.gif|\.jpg|\.jpeg|\.png)$/i
        $allowedExtensionsRegex = '/(' . implode('|', array_map(function($extension) { return '\.' . $extension; }, $allowedExtensions)) . ')$/i';
        $sizes = (isset($options['sizes']) && is_array($options['sizes'])) ? $options['sizes'] : array();
        $filePath = $options['file_base_path'] . '/' . $options['folder'];
        //var_dump($options['file_base_path']);
        $webPath = $options['web_base_path'] . '/' . $options['folder'];
        foreach ($sizes as &$size) {
            $size['upload_dir'] = $filePath . '/' . $size['folder'] . '/';
            $size['upload_url'] = $webPath . '/' . $size['folder'] . '/';
        }
        //$originals = $options['originals'];
        //$uploadDir = $filePath . '/' . $originals['folder'] . '/';
        $arrayExplode = explode('/', $options['folder']);
        $folder = end($arrayExplode);
        @mkdir($filePath, 0777, true);
        $uploadDir = str_replace($folder, 'thumbnails_' . $folder . '/', $filePath);
        //$uploadDir = $filePath . '/';
        //var_dump($uploadDir);
        /*foreach ($sizes as &$size) {
            @mkdir($size['upload_dir'], 0777, true);
        }*/
        @mkdir($uploadDir, 0777, true);
        $upload_handler = new \PunkAve\FileUploaderBundle\BlueImp\UploadHandler(
            array(
                'upload_dir' => $filePath . '/',
                'upload_url' => $webPath . '/',
                'script_url' => $options['request']->getUri(),
                'image_versions' => $sizes,
                'accept_file_types' => $allowedExtensionsRegex,
                'max_number_of_files' => $options['max_number_of_files'],
                'min_file_size' => $options['min_file_size'],
                'max_file_size' => $options['max_file_size'],
                //'test' => $options['test']
            ));
        //header('Content-type: text/plain');
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        //header('Content-Disposition: inline; filename="files.json"');
        header('X-Content-Type-Options: nosniff');
        //header('Access-Control-Allow-Origin: *');
       // header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $upload_handler->post();
        } else {
            header('HTTP/1.1 405 Method Not Allowed');
        }
        exit(0);
    }
}
