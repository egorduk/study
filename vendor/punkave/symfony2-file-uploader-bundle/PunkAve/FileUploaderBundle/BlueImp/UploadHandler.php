<?php

namespace PunkAve\FileUploaderBundle\BlueImp;

/*
 * jQuery File Upload Plugin PHP Class 5.11
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

use Helper\Helper;

class UploadHandler
{
    protected $options;

    function __construct($options = null) {
        //var_dump($options);
        $this->options = array(
            'script_url' => $this->getFullUrl().'/',
            //'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']) . '/files/',
            //'upload_url' => $this->getFullUrl() . '/files/',
            'icon_url' => '/study/web/bundles/images/icons/',
            'param_name' => 'files',
            'thumbnail_dir' => dirname($_SERVER['SCRIPT_FILENAME']),
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            //'delete_type' => 'DELETE',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => null,
            'accept_file_types' => '/.+$/i',
            // The maximum number of files for the upload directory:
            //'max_number_of_files' => null,
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images. You can also add additional versions with
                // their own upload directories:
                /*
                'large' => array(
                    'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
                    'upload_url' => $this->getFullUrl().'/files/',
                    'max_width' => 1920,
                    'max_height' => 1200,
                    'jpeg_quality' => 95
                ),
                */
                'thumbnail' => array(
                    //'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']) . '/thumbnails/',
                    //'upload_url' => $this->getFullUrl() . '/thumbnails/',
                    'max_width' => 80,
                    'max_height' => 80
                )
            ),
            'avatar_param' => array(
              //  'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']) . '/thumbnails/',
                //'upload_url' => $this->getFullUrl() . '/thumbnails/',
                'max_width' => 110,
                'max_height' => 110
            )
        );
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }
    }

    protected function getFullUrl() {
      	return
    		(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
    		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function set_file_delete_url($file) {
        $file->delete_url = $this->options['script_url'] . '?file='.rawurlencode($file->name);
        /*$file->delete_type = $this->options['delete_type'];
        if ($file->delete_type !== 'DELETE') {
            $file->delete_url .= '&_method=DELETE';
        }*/
    }

    protected function get_file_object($file_name) {
        $file_path = $this->options['upload_dir'] . $file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new \stdClass();
            $file->name = iconv('CP1251', 'UTF-8', $file_name);
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'] . rawurlencode($file->name);
            /*foreach($this->options['image_versions'] as $version => $options) {
                if (is_file($options['upload_dir'].$file_name)) {
                    $file->{$version.'_url'} = $options['upload_url'] . rawurlencode($file->name);
                }
            }
            $this->set_file_delete_url($file);*/
            return $file;
        }
        return null;
    }

    protected function get_file_objects() {
        return array_values(array_filter(array_map(array($this, 'get_file_object'), scandir($this->options['upload_dir']))));
    }

    protected function create_scaled_image($file_name, $options) {
        $file_path = $this->options['upload_dir'] . iconv('utf-8','cp1251', $file_name);
        //var_dump($file_path);die;
        //$new_file_path = $options['upload_dir'] . iconv('utf-8','cp1251', $file_name);
        if ($this->options['mode'] == 'order') {
            if (preg_match('/\/uploads\/attachments\/orders\/[\d]*?\/(author|client)/', $options['upload_url'])) {
                $arrayExplode = explode('/', $options['upload_url']);
                $slug = $arrayExplode[5];
                $thumbnailUrl = $options['upload_url'];
                $thumbnailUrl = str_replace($slug . '/', 'thumbnails_' . $slug, $thumbnailUrl);
            } else {
                $thumbnailUrl = null;
            }
            $new_file_path = $this->options['thumbnail_dir'] . $thumbnailUrl . iconv('utf-8','cp1251', $file_name);
            list($img_width, $img_height) = @getimagesize($file_path);
        } elseif ($this->options['mode'] == 'profile') {
            $new_file_path = $file_path;
            //var_dump($new_file_path);die;
            $img_width = $options['width'];
            $img_height = $options['height'];
        }
        if (!$img_width || !$img_height) {
            return false;
        }
        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );
        if ($scale >= 1) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        if ($this->options['mode'] == 'profile') {
            $file_path = $options['tmp'];
        }
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ? $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ? $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path, $image_quality);
        //var_dump($src_img);die;
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }

    protected function validate($uploaded_file, $file, $error) {
        //var_dump($this->options);
        if ($error) {
            $file->error = $error;
            return false;
        }
        if (!$file->name) {
            $file->error = 'missingFileName';
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = 'acceptFileTypes';
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && ($file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size'])) {
            $file->error = 'maxFileSize';
            return false;
        }
        if ($this->options['min_file_size'] && $file_size < $this->options['min_file_size']) {
            $file->error = 'minFileSize';
            return false;
        }
        if (is_int($this->options['max_number_of_files']) && (count($this->get_file_objects()) >= $this->options['max_number_of_files'])) {
            $file->error = 'maxNumberOfFiles';
            return false;
        }
        /*list($img_width, $img_height) = @getimagesize($uploaded_file);
        if (is_int($img_width)) {
            if ($this->options['max_width'] && $img_width > $this->options['max_width'] || $this->options['max_height'] && $img_height > $this->options['max_height']) {
                $file->error = 'maxResolution';
                return false;
            }
            if ($this->options['min_width'] && $img_width < $this->options['min_width'] || $this->options['min_height'] && $img_height < $this->options['min_height']) {
                $file->error = 'minResolution';
                return false;
            }
        }*/
        return true;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function trim_file_name($name, $type) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Add missing file extension for known image types:
        if (strpos($file_name, '.') === false && preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $file_name .= '.' . $matches[1];
        }
        if ($this->options['discard_aborted_uploads']) {
            while(is_file($this->options['upload_dir'] . $file_name)) {
                $file_name = $this->upcount_name($file_name);
            }
        }
        return $file_name;
    }

    /*protected function handle_form_data($file, $index) {
        // Handle form data, e.g. $_REQUEST['description'][$index]
    }*/

    protected function orient_image($file_path) {
      	$exif = @exif_read_data($file_path);
        if ($exif === false) {
            return false;
        }
      	$orientation = intval(@$exif['Orientation']);
      	if (!in_array($orientation, array(3, 6, 8))) { 
      	    return false;
      	}
      	$image = @imagecreatefromjpeg($file_path);
      	switch ($orientation) {
        	  case 3:
          	    $image = @imagerotate($image, 180, 0);
          	    break;
        	  case 6:
          	    $image = @imagerotate($image, 270, 0);
          	    break;
        	  case 8:
          	    $image = @imagerotate($image, 90, 0);
          	    break;
          	default:
          	    return false;
      	}
      	$success = imagejpeg($image, $file_path);
      	// Free up memory (imagedestroy does not delete files):
      	@imagedestroy($image);
      	return $success;
    }

    /**
     * Main handler
     * @param $uploaded_file
     * @param $name
     * @param $size
     * @param $type
     * @param $error
     * @return \stdClass
     */
    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error) { // if uploaded
        $file = new \stdClass();
        $file->size = intval($size);
        //$file->type = Helper::getMimeType($type);
        //$file->type = Helper::getExtensionFile($file->name);
        $file->type = Helper::getExtensionFile($name);
        if ($this->options['mode'] == 'order') {
            $file->name = $this->trim_file_name($name, $type);
        } elseif ($this->options['mode'] == 'profile') {
            $file->name = $name;
        }
        if ($this->validate($uploaded_file, $file, $error)) {
            //$this->handle_form_data($file, $index);
            $file_path = $this->options['upload_dir'] . $file->name;
           // $append_file = !$this->options['discard_aborted_uploads'] && is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($this->options['mode'] == 'order') {
                if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                    $file_path = iconv("UTF-8", "CP1251", $file_path);
                    if (mb_detect_encoding($file->name) != 'ASCII') {
                        if (file_exists($file_path)) {
                            $count = 0;
                            $arr = explode('.', $name);
                            $fileHandler = opendir($this->options['upload_dir']);
                            while (false !== ($filename = readdir($fileHandler))) {
                                if ($filename != "." && $filename != "..") {
                                    if (preg_match(iconv('UTF-8', 'CP1251', '/' . $arr[0] . '\(*?[\d]*?\)*?.' . $arr[1] . '/'), $filename) != 0) {
                                        $count++;
                                    }
                                }
                            }
                            closedir($fileHandler);
                            $name = str_replace('.' . $file->type, '', $name);
                            $name = str_replace($name, $name . ' (' . $count . ').', $name);
                            $file_path = $this->options['upload_dir'] . iconv('UTF-8', 'CP1251', $name) . $file->type;
                            $file->name = $name . $file->type;
                        }
                    }
                    move_uploaded_file($uploaded_file, $file_path);
                }
                $file_size = filesize($file_path);
            } elseif ($this->options['mode'] == 'profile') {
                $file_size = $file->size;
            }
            if ($file_size === $file->size) {
            	/*if ($this->options['orient_image']) {
            		$this->orient_image($file_path);
            	}*/
                $file->url = '/study/web' . $this->options['upload_url'] . rawurlencode($file->name);
                //var_dump($file->name);die;
                if ($this->options['mode'] == 'order') {
                    foreach($this->options['image_versions'] as $version => $options) {
                        //var_dump($this->create_scaled_image($file->name, $options));die;
                        if ($this->create_scaled_image($file->name, $options)) {
                            if ($this->options['upload_dir'] !== $options['upload_dir']) {
                                //$file->{$version.'_url'} = '/study/web/' . $options['upload_url'] . rawurlencode($file->name);
                                //var_dump($options);
                                if (preg_match('/\/uploads\/attachments\/orders\/[\d]*?\/(author|client)/', $options['upload_url'])) {
                                    $arrayExplode = explode('/', $options['upload_url']);
                                    //$slug = end($arrayExplode);
                                    $slug = $arrayExplode[5];
                                    $thumbnailUrl = $options['upload_url'];
                                    $thumbnailUrl = str_replace($slug . '/', 'thumbnails_' . $slug, $thumbnailUrl);
                                } else {
                                    $thumbnailUrl = null;
                                }
                                $file->{$version.'_url'} = '/study/web/' . $thumbnailUrl . rawurlencode($file->name);
                            } else {
                                clearstatcache();
                                $file_size = filesize($file_path);
                            }
                        } else {
                            $file->thumbnail_url = $this->options['icon_url'] . $file->type . '.png';
                        }
                    }
                    $file->size = Helper::getSizeFile($file_size);
                    $file->date_upload = Helper::addNewOrderFile($file);
                    //return $file;
                } elseif ($this->options['mode'] == 'profile') {
                    $options = $this->options['avatar_param'];
                    list($img_width, $img_height) = @getimagesize($uploaded_file);
                    $options['width'] = $img_width;
                    $options['height'] = $img_height;
                    $options['tmp'] = $uploaded_file;
                    $dir = $this->options['upload_dir'];
                    //var_dump($dir);die;
                    Helper::deleteAllFilesFromFolder($dir);
                    //var_dump($this->create_scaled_image($file->name, $options));die;
                    if ($this->create_scaled_image($file->name, $options)) {
                        Helper::updateUserAvatar($file->name, null, 'uploader');
                    } else {
                        //unlink($file_path);
                        $file->error = 'incorrectImage';
                    }
                    //return isset($file->error) ? $file->error : true;
                    //var_dump($file);die;
                    //return $file;
                }
            } elseif ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'abort';
            }
        }
       // var_dump($this->options['num_order']);die;
        $file->url = Helper::getFileUrl($file->name, $this->options['num_order']);
        return $file;
    }

    /*public function get() {
        $file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }
        header('Content-type: text/plain');
        echo json_encode($info);
    }*/

    public function post() { //if file uploaded
        /*if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete();
        }
        var_dump($_REQUEST);die;*/
        if (isset($_REQUEST['file']) && (isset($_REQUEST['file']) && $_REQUEST['file'] != "") && (isset($_REQUEST['action']) && $_REQUEST['action'] == 'order')) {
            return $this->delete();
        }
        $upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
        $info = array();
        if ($upload && is_array($upload['tmp_name'])) {
            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    //iconv('UTF-8', 'CP1251', $upload['tmp_name'][$index]),
                    $upload['tmp_name'][$index],
                    //iconv('cp1251', 'utf-8', $upload['tmp_name'][$index]),
                    isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    //$upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index]
                );
            }
        } /*elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:
            $info[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ? $upload['name'] : null),
                isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ? $upload['size'] : null),
                isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ? $upload['type'] : null),
                isset($upload['error']) ? $upload['error'] : null
            );
        }*/
        header('Vary: Accept');
        $json = json_encode($info); //response about uploaded file
        /*if ($this->options['mode'] == 'profile') {
            if (count($upload['tmp_name']) > 1) {
                $json = json_encode(end($info));
            }
        }*/
        $redirect = isset($_REQUEST['redirect']) ? stripslashes($_REQUEST['redirect']) : null;
        if ($redirect) {
            header('Location: '.sprintf($redirect, rawurlencode($json)));
            return;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        echo $json;
    }

    public function delete() { // deletes selected file
        $file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
        $file_path = $this->options['upload_dir'] . $file_name;
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        if ($success) {
            foreach($this->options['image_versions'] as $options) {
                $file = $options['upload_dir'] . iconv('UTF-8', 'CP1251', $file_name);
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        header('Content-type: application/json');
        echo json_encode($success);
    }

}