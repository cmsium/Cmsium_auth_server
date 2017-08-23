<?php
class FileActions{
    private static $instance;
    private $last_file_id;

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}
    private function __clone(){}


    public function getId(){
        return $this->last_file_id;
    }

    /**
     * Redirect to temporary file link
     *
     * @param string $file_id File id
     */
    public function get($file_id,$return = false){
        if (!isset($file_id))
            return NULL;
            //ErrorHandler::throwException(FILE_PARAM_ABSENT, "page");
        $validator = Validator::getInstance();
        $file_id = $validator->Check('Md5Type',$file_id,[]);
        if (!$file_id)
            ErrorHandler::throwException(DATA_FORMAT_ERROR,"page");
        $file = new Files(['id'=>$file_id]);
        $file->checkFileRoles('r');
        $file->getFileFromBase();
        $link = $file->getLink();
        $host_url = Config::get('host_url');
        if ($return){
            unset($file);
            return "<a href=\"".$host_url."/file/link/".$link."\"><img src=\"".$host_url."/file/preview/".$link."\" alt='скачать'/></a>";
        }
        header('Location: '.$host_url."/file/link/".$link, true, 303);
        unset($file);
        return true;
    }

    public function getPicture($file_id,$url,$description){
        if (!isset($file_id))
            return NULL;
        //ErrorHandler::throwException(FILE_PARAM_ABSENT, "page");
        $validator = Validator::getInstance();
        $file_id = $validator->Check('Md5Type',$file_id,[]);
        if (!$file_id)
            ErrorHandler::throwException(DATA_FORMAT_ERROR,"page");
        $file = new Files(['id'=>$file_id]);
        $file->checkFileRoles('r');
        $file->getFileFromBase();
        $link = $file->getLink();
        $host_url = Config::get('host_url');
        unset($file);
        return "<div class='inline'><img src=\"".$host_url."/file/preview/".$link."\"/><br />
        <a href=\"".$host_url."$url\">$description</a></div>";
    }

    /**
     * Get requested file by temporary link
     *
     * @param string $link Temporary link
     */
    public function preview($link){
        $validator = Validator::getInstance();
        $link = $validator->Check('Md5Type',$link,[]);
        if (!$link)
            ErrorHandler::throwException(DATA_FORMAT_ERROR,"page");
        $file = new Files(['link'=>$link]);
        $file->getFileIdByLink();
        $file->checkFileRoles('r');
        $file->getFileFromBase();
        $file->renderThumbnail();
        unset($file);
    }

    /**
     * Get requested file by temporary link
     *
     * @param string $link Temporary link
     */
    public function link($link){
        $validator = Validator::getInstance();
        $link = $validator->Check('Md5Type',$link,[]);
        if (!$link)
            ErrorHandler::throwException(DATA_FORMAT_ERROR,"page");
        $file = new Files(['link'=>$link]);
        $file->getFileIdByLink();
        $file->checkFileRoles('r');
        $file->getFileFromBase();
        $file->deleteLink();
        $file->renderFile();
        unset($file);
    }


    /**
     *Create new file using file create page
     */
    public function create($file_column_name = false) {
        if (!$file_column_name)
            $file_column_name = 'userfile';
        if (isset($_FILES[$file_column_name]) and !empty($_FILES[$file_column_name]['name'])) {
            $auth = AuthHandler::getInstance();
            $auth->check();
            $validator = Validator::getInstance();
            $file_data = $validator->ValidateAllByMask($_FILES[$file_column_name], 'fileUploadMask');
            if (!$file_data) {
                ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
            }
            $file = new Files(['name'=>$file_data['name'],'tmp_path'=>$_FILES[$file_column_name]['tmp_name']]);
            if (!$file->checkMime()) {
                ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
            }
            $size = filesize($_FILES[$file_column_name]['tmp_name']);
            if (($file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
                ErrorHandler::throwException(FILE_SIZE_ERROR,'page');
            }
            $id = $file->generateId();
            $this->last_file_id = $id;
            $conn = DBConnection::getInstance();
            $conn->startTransaction();
            if (!$file->createFile()){
                $conn->rollback();
                ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
            }
            if (!$file->createFileRoles()){
                $conn->rollback();
                ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
            }
            $file_name = $file->getFileFromBase();
            if (!$file->makeThumbnail()){
                $conn->rollback();
                ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
            }
            if ($file->upload(STORAGE.$file_name)) {
                $file->addToZip();
                $conn->commit();
                return $id;
                //ErrorHandler::throwException(FILE_UPLOAD_SUCCESS,'page');
            } else {
                $conn->rollback();
                ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
            }
            unset($file);
            unset($conn);

        } else{
            return NULL;
        }
    }



    /**
     *Create new file using file create page
     */
    public function multipleCreateInSandbox($file_column_name = false) {
        if (!$file_column_name)
            $file_column_name = 'userfile';
        if (isset($_FILES[$file_column_name]) and !empty($_FILES[$file_column_name]['name'])
            and !in_array("4",$_FILES[$file_column_name]['error'])) {
            $auth = AuthHandler::getInstance();
            $auth->check();
            $validator = Validator::getInstance();
            $file_data = $validator->ValidateAllByMask($_FILES[$file_column_name], 'filesUploadMask');
            if ($file_data === false) {
                ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
            }
            $new_files_data=[];
            foreach ($file_data as $key=>$value){
                $i = 0;
                foreach ($value as $item) {
                    $new_files_data[$i][$key] = $item;
                    $i++;
                }
            }
            $files_id=[];
            $conn = DBConnection::getInstance();
            foreach ($new_files_data as $new_file_data) {
                $file = new Files(['name' => $new_file_data['name'], 'tmp_path' => $new_file_data['tmp_name']]);
                if (!$file->checkMime()) {
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                $size = filesize($new_file_data['tmp_name']);
                if (($new_file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
                    ErrorHandler::throwException(FILE_SIZE_ERROR, 'page');
                }
                $id = $file->generateId();
                $this->last_file_id = $id;
                $conn->startTransaction();
                if (!$file->createFileInSandbox()) {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                $file_name = $file->getFileFromSandbox();
                if ($file->upload(SANDBOX . $file_name,'/sandbox')) {
                    $conn->commit();
                    $files_id[] = $id;
                    //ErrorHandler::throwException(FILE_UPLOAD_SUCCESS,'page');
                } else {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                unset($file);
            }
            unset($conn);
            return $files_id;
        } else{
            return NULL;
        }
    }

    public function createFilesFromSandbox($files_id){
        if (!empty($files_id)){
            $conn = DBConnection::getInstance();
            foreach ($files_id as $file_id){
                $file = new Files(['id'=>$file_id]);
                $conn->startTransaction();
                $data = $file->getFileDataFromSandbox();
                if (!$data){
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                if (!$file->createFileFromData($data)) {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                if (!$file->createFileRoles()) {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                $file_name = $file->getFileFromSandbox();
                if (!$file->makeThumbnail(true)) {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                if (!$file->deleteFromSandBox()) {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                if ($file->moveFileFromSandbox($file_name)) {
                    $file->addToZip();
                    $conn->commit();
                    //ErrorHandler::throwException(FILE_UPLOAD_SUCCESS,'page');
                } else {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                unset($file);
            }
        }
    }


    /**
     *Create new file using file create page
     */
    public function multiple_create($file_column_name = false) {
        if (!$file_column_name)
            $file_column_name = 'userfile';
        if (isset($_FILES[$file_column_name]) and !empty($_FILES[$file_column_name]['name'])
            and !in_array("4",$_FILES[$file_column_name]['error'])) {
            $auth = AuthHandler::getInstance();
            $auth->check();
            $validator = Validator::getInstance();
            $file_data = $validator->ValidateAllByMask($_FILES[$file_column_name], 'filesUploadMask');
            if ($file_data === false) {
                ErrorHandler::throwException(FILE_UPLOAD_ERROR,'page');
            }
            $new_files_data=[];
            foreach ($file_data as $key=>$value){
                $i = 0;
                foreach ($value as $item) {
                    $new_files_data[$i][$key] = $item;
                    $i++;
                }
            }
            $files_id=[];
            $conn = DBConnection::getInstance();
            foreach ($new_files_data as $new_file_data) {
                $file = new Files(['name' => $new_file_data['name'], 'tmp_path' => $new_file_data['tmp_name']]);
                if (!$file->checkMime()) {
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                $size = filesize($new_file_data['tmp_name']);
                if (($new_file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
                    ErrorHandler::throwException(FILE_SIZE_ERROR, 'page');
                }
                $id = $file->generateId();
                $this->last_file_id = $id;
                $conn->startTransaction();
                if (!$file->createFile()) {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                if (!$file->createFileRoles()) {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                $file_name = $file->getFileFromBase();
                if (!$file->makeThumbnail()) {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                if ($file->upload(STORAGE . $file_name)) {
                    $file->addToZip();
                    $conn->commit();
                    $files_id[] = $id;
                    //ErrorHandler::throwException(FILE_UPLOAD_SUCCESS,'page');
                } else {
                    $conn->rollback();
                    ErrorHandler::throwException(FILE_UPLOAD_ERROR, 'page');
                }
                unset($file);
            }
            unset($conn);
            return $files_id;
        } else{
            return NULL;
        }
    }

    /**
     * Delete file from system by id
     *
     * @param string $file_id File id
     */
    public function delete($file_id){
        if (!isset($file_id))
            return true;
            //ErrorHandler::throwException(FILE_PARAM_ABSENT, "page");
        $validator = Validator::getInstance();
        $file_id = $validator->Check('Md5Type',$file_id,[]);
        if (!$file_id)
            ErrorHandler::throwException(DATA_FORMAT_ERROR);
        $file = new Files(['id'=>$file_id]);
        $file->checkFileRoles('d');
        $file_name = $file->getFileFromBase();
        $conn = DBConnection::getInstance();
        $conn->startTransaction();
        if (!$file->deleteLink()) {
            $conn->rollback();
            ErrorHandler::throwException(FILE_DELTE_BASE_ERROR,'page');
        }
        if (!$file->deleteFile()) {
            $conn->rollback();
            ErrorHandler::throwException(FILE_DELTE_BASE_ERROR,'page');
        }
        if (!$file->deleteFileRoles()) {
            $conn->rollback();
            ErrorHandler::throwException(FILE_DELTE_BASE_ERROR,'page');
        }
        if (!unlink(STORAGE.$file_name.'.zip')){
            $conn->rollback();
            ErrorHandler::throwException(FILE_DELTE_ERROR,'page');
        }
        @unlink(ROOTDIR . "/images/previews/".$file_name.'.png');
        $conn->commit();
        unset($file);
        unset($conn);
        return true;
        //ErrorHandler::throwException(FILE_DELTE_SUCCESS,'page');
    }

    public function deleteFromSandbox($file_id){
        $file = new Files(['id'=>$file_id]);
        $file_name = $file->getFileFromSandbox();
        $conn = DBConnection::getInstance();
        $conn->startTransaction();
        if (!$file->deleteFromSandBox()) {
            $conn->rollback();
            ErrorHandler::throwException(FILE_DELTE_BASE_ERROR,'page');
        }
        if (!unlink(SANDBOX.$file_name)){
            $conn->rollback();
            ErrorHandler::throwException(FILE_DELTE_ERROR,'page');
        }
        $conn->commit();
        unset($file);
        unset($conn);
        return true;
    }

    public function deleteExpiredSandboxFiles(){
        $conn = DBConnection::getInstance();
        $expire_time = SANDBOX_FILES_EXPIRE_TIME;
        $query = "CALL expiredSandboxFiles('$expire_time');";
        $expired_files = $conn->performQueryFetchAll($query);
        if ($expired_files AND !empty($expired_files)){
            foreach ($expired_files as $file){
                $this->deleteFromSandbox($file['file_id']);
            }
        }
    }

    /**
     * Update current file
     *
     * @param string $old_file_id Updated file id
     * @param string $new_file_column_name New file column name from FILES
     * @return null|string Update status (new file id if success)
     */
    public function update($old_file_id, $new_file_column_name){
        if (!empty($old_file_id))
            $this->delete($old_file_id);
        return $this->create($new_file_column_name);
    }

    public static function clearStorage() {
        $files = glob(ROOTDIR.'/storage/*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }
    }
}