<?php
class AutoUpdater {

    /**
     * Compares md5 hashes of two files
     *
     * @param $file_download string First file path
     * @param $file_storage string Second file path
     * @return bool True if files have differences
     */
    public static function checkDiff($file_download, $file_storage) {
        if (!file_exists($file_storage)) {
            if (DEBUG) echo "Storage file does not exist.".PHP_EOL;
            return true;
        }
        $md5_download = md5_file($file_download);
        $md5_storage = md5_file($file_storage);
        if ($md5_download === $md5_storage) {
            return false;
        } else {
            if (DEBUG) echo "Files have differences!".PHP_EOL;
            return true;
        }
    }

    /**
     * Move file from one path to another
     *
     * @param $old_path string
     * @param $new_path string
     * @return bool False, if something went wrong
     */
    public static function replaceFile($old_path, $new_path) {
        if (file_exists($old_path)) {
            if (DEBUG) echo "File replaced to $new_path".PHP_EOL;
            $result = rename($old_path, $new_path);
            if ($result) self::chmodR($new_path, 0775);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Calls all the needed methods in order to update DB
     *
     * @param $name string Alpha-2 code
     * @param $url string Source URL
     */
    public static function update($name, $url) {
        $file_name = basename($url);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        self::downloadFile($url);
        if (self::checkDiff(TMP_DOWNLOAD_PATH."/$file_name", STORAGE_PATH."/$file_name")) {

            self::replaceFile(TMP_DOWNLOAD_PATH."/$file_name", STORAGE_PATH."/$file_name");
            self::unpackFile(STORAGE_PATH."/$file_name", $file_ext, TMP_PATH."/$name");
            // Decide which class to call
            switch ($name) {
                case "RU":
                    KLADRReader::update();
                    break;
                case "UA": break;
            }

        }
        self::deleteFile(TMP_DOWNLOAD_PATH."/$file_name");
        if (is_dir(TMP_PATH."/$name")) {
            self::deleteDirectory(TMP_PATH."/$name");
        }
    }

    /**
     * Downloads file using WGET utility
     *
     * @param $url string Source URL
     */
    public static function downloadFile($url) {
        $command = "wget -P ".TMP_DOWNLOAD_PATH." $url";
        $result = `$command`;
        echo $result;
        self::chmodR(TMP_DOWNLOAD_PATH, 0775);
    }

    /**
     * Unpacks file of .zip or .7z format
     *
     * @param $file_path string File path
     * @param $ext string File extension
     * @param $output_path string Unpack results path
     * @return bool False if something went wrong
     */
    public static function unpackFile($file_path, $ext, $output_path) {
        switch ($ext) {
            case 'zip':
                $zip = new ZipArchive;
                if ($zip->open($file_path) === TRUE) {
                    $zip->extractTo($output_path);
                    $zip->close();
                    self::chmodR($output_path, 0775);
                    return true;
                } else {
                    return false;
                }
                break;
            case '7z':
                $command = "7za x -o$output_path $file_path";
                echo `$command`;
                self::chmodR($output_path, 0775);
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Deletes file from path
     *
     * @param $path string File path
     * @return bool False if something went wrong
     */
    public static function deleteFile($path) {
        if (file_exists($path)) {
            return unlink($path);
        }
    }

    /**
     * Deletes directory from path
     *
     * @param $path string Directory path
     */
    public static function deleteDirectory($path) {
        $files = glob($path.'/*');
        foreach ($files as $file) {
            is_dir($file) ? deleteDirectory($file) : unlink($file);
        }
        rmdir($path);
        return;
    }

    public static function chmodR($path, $filemode) {
        if (!is_dir($path)) {
            return chmod($path, $filemode);
        }
        $dh = opendir($path);
        while ($file = readdir($dh)) {
            if ($file != '.' && $file != '..') {
                $fullpath = $path.'/'.$file;
                if(!is_dir($fullpath)) {
                    if (!chmod($fullpath, $filemode)){
                        return false;
                    }
                } else {
                    if (!self::chmodR($fullpath, $filemode)) {
                        return false;
                    }
                }
            }
        }

        closedir($dh);

        if ( chmod($path, $filemode) ) {
            return true;
        } else {
            return false;
        }
    }

}