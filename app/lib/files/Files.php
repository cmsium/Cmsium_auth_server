<?php
class Files {
    public $id;
    public $tmp_path;
    public $path;
    public $name;
    public $link;
    public $roles;
    public $type;


    public function __construct($params = []){
        if (isset($params['id']))
            $this->id = $params['id'];
        if (isset($params['path']))
            $this->path = $params['path'];
        if (isset($params['name']))
            $this->name = $params['name'];
        if (isset($params['link']))
            $this->link = $params['link'];
        if (isset($params['tmp_path']))
            $this->tmp_path = $params['tmp_path'];
        if (isset($params['roles']))
            $this->roles = $params['roles'];
        else
            $this->roles = [1,2,3];

    }

    /**Move file from temporary place to storage
     * @param $upload_path
     * @return bool Move status
     */
    public function upload($upload_path,$storage = '/storage'){
        if (!file_exists(ROOTDIR.$storage)){
            mkdir(ROOTDIR.$storage);
            chmod(ROOTDIR.$storage,0775);
        }
        return move_uploaded_file($this->tmp_path, $upload_path);
    }

    public function moveFileFromSandbox($sandbox_file){
       $this->path = STORAGE.$sandbox_file;
        chmod(SANDBOX.$sandbox_file,0775);
       $result = copy(SANDBOX.$sandbox_file,STORAGE.$sandbox_file);
       if ($result){
           chmod(STORAGE.$sandbox_file,0775);
           unlink(SANDBOX.$sandbox_file);
       }
       return $result;

    }


    /**
     * Generate file id
     * @return string File id
     */
    public function generateId(){
        $name = $this->name;
        $file_id = md5($name.time());
        //$path = $this->tmp_path;
        //$file_id = md5_file($path);
        $this->id = $file_id;
        return $file_id;
    }


    /**
     * Check is it a real image;
     * @param string $path Path to image
     */
    public function checkImage(){
        $path = $this->tmp_path;
        $check = getimagesize($path);
        if($check === false) {
            ErrorHandler::throwException(FILE_NOT_IMAGE_ERROR,'page');
        }
        return filesize($path);
    }

    public function checkMime(){
        $type = mime_content_type($this->tmp_path);
        if (!in_array($type,ALLOWED_FILE_MIME_TYPES))
            return false;
        $this->type = $type;
        return $type;
    }

    /**
     * Render and output file with basic headers
     *
     * @param string $path Path to requested file
     */
    public function renderFile(){
        $path = $this->path;
        if (file_exists($path.".zip")) {
            //TODO optimize
            $name = $this->getFileNameById();
            $zip = new ZipArchive();
            if ($zip->open($path.'.zip')) {
                $zip->extractTo(STORAGE);
            }
            rename(STORAGE.'/'.md5($name),STORAGE."/$name");
            $zip->close();
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$name.'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize(STORAGE . "/$name"));
            ob_clean();
            //flush();
            readfile(STORAGE . "/$name");
            unlink (STORAGE . "/$name");
        } else {
            ErrorHandler::throwException(UNDEFINED_FILE,'page');
        }
    }

    /**
     * Render and output system file with basic headers
     *
     * @param string $path Path to requested file
     * @param string $name File output name
     */
    public static function renderSysFile($path,$name){
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        ob_clean();
        readfile($path);
    }


    public function makeThumbnail($sandbox=false){
        try {
            if ($sandbox)
                $path = $this->path;
            else
                $path = $this->tmp_path;
            switch ($this->type) {
                case 'application/pdf':
                    $path = $path . "[0]";
                    break;
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                case 'application/msword':
                    return true;
                default:
                    break;
            }
            $image = new Imagick($path);
            $image->setImageFormat('png');
            $image->thumbnailImage(FILES_PREVIEW_SIZE, 0);
            $path = @end(explode('/', $this->path));
            if (!file_exists(ROOTDIR .'/images/previews')){
                mkdir(ROOTDIR .'/images/previews',0775);
                chmod(ROOTDIR .'/images/previews',0775);

            }
            $result = $image->writeImage(ROOTDIR . "/images/previews/{$path}.png");
            if ($result)
                chmod(ROOTDIR . "/images/previews/{$path}.png",0775);
            return $result;
        } catch(Exception $e){
            echo $e->getMessage();
        }
    }


    public function renderThumbnail(){
        $name = $this->getFileNameById();
        $path = @end(explode('/',$this->path));
        $thumb_path = ROOTDIR . "/images/previews/{$path}.png";
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($thumb_path));
        ob_clean();
        //flush();
        readfile($thumb_path);
    }


    /**
     * Get file output name by file id
     * @return string File output name
     */
    public function getFileNameById(){
        $id = $this->id;
        $conn = DBConnection::getInstance();
        $query = "CALL getFileNameById('$id');";
        $file_name =  $conn->performQueryFetch($query);
        $this->name = $file_name['name'];
        return $file_name['name'];
    }

    public function deleteFromSandBox(){
        $conn = DBConnection::getInstance();
        $query = "CALL deleteFileFromSandBox('{$this->id}');";
        return  $conn->performQuery($query);
    }


    /**
     * Add file to zip archive
     *
     * @param string $filename File name (path)
     */
    public function addToZip(){
        $path = $this->path;
        $zip = new ZipArchive();
        if ($zip->open($path.'.zip', ZipArchive::CREATE)!==TRUE) {
            ErrorHandler::throwException(ZIP_OPEN_ERROR,'page');
        }
        $zip->addFile($path ,md5($this->name));
        $zip->close();
        chmod($path.'.zip',0775);
        unlink ($path);
    }

    /**
     * Get file full name (in storage) from base by id
     * @return mixed File name|false
     */
    public function getFileFromBase(){
        $conn = DBConnection::getInstance();
        $file_id = $this->id;
        $query = "CALL getFilePathById('$file_id');";
        $file_name = $conn->performQueryFetch($query);
        if (!$file_name) {
            ErrorHandler::throwException(UNDEFINED_FILE,'page');
        }
        $this->id = $file_id;
        $this->path = STORAGE.$file_name['result'];
        return $file_name['result'];
    }

    /**
     * Get file full name (in sandbox) from base by id
     * @return mixed File name|false
     */
    public function getFileFromSandbox(){
        $conn = DBConnection::getInstance();
        $file_id = $this->id;
        $query = "CALL getFilePathByIdFromSandbox('$file_id');";
        $file_name = $conn->performQueryFetch($query);
        if (!$file_name) {
            ErrorHandler::throwException(UNDEFINED_FILE,'page');
        }
        $this->id = $file_id;
        $this->path = SANDBOX.$file_name['result'];
        return $file_name['result'];
    }

    public function getFileDataFromSandbox(){
        $conn = DBConnection::getInstance();
        $query = "CALL getFileDataFromSandbox('{$this->id}');";
        $data = $conn->performQueryFetch($query);
        if (!$data) {
            ErrorHandler::throwException(UNDEFINED_FILE,'page');
        }
        $this->name = $data['name'];
        $this->type = $data['type'];
        return $data;

    }
    /**
     * Get file id from base by temporary link
     * @return mixed File id|false
     */
    public function getFileIdByLink(){
        $link = $this->link;
        $conn = DBConnection::getInstance();
        $query = "CALL getFileIdByLink('$link');";
        $file_id =  $conn->performQueryFetch($query);
        $this->id = $file_id['file_id'];
        return $file_id['file_id'];
    }


    /**Check file permissions according to roles
     *
     * @param string $action Requested file action ('r' - read/'c' - create/'d' - delete)
     * @return mixed Check status
     */
    public function checkFileRoles($action){
        $file_id = $this->id;
        $conn = DBConnection::getInstance();
        $query = "CALL getFileRoles('$file_id');";
        $result = $conn->performQueryFetchAll($query);
        if (!$result)
            return false;
        $file_roles = [];
        foreach ($result as $value){
            $file_roles[$value["role_id"]] = $value["permissions"];
        }
        Engine::loadmodules(['users']);
        $user_id = User::getSessionUserId();
        User::setData($user_id);
        $user_roles = User::getRoles();
        $status = false;
        foreach ($user_roles as $urole){
            if (!isset($file_roles[$urole]))
                continue;
            $rights = $file_roles[$urole];
            $result = $this->checkActionRoles($action,$rights);
            if ($result)
                $status = true;
        }
        $query = "CALL getFileOwner('$file_id');";
        $owner = $conn->performQueryFetch($query);
        if ($user_id == $owner['owner_id'])
            $status = true;
        if (!$status)
            ErrorHandler::throwException(PERMISSIONS_ERROR,'page');
    }


    /**Check request action congruence to file permissions
     *
     * @param string $action Requested file action ('r' - read/'c' - create/'d' - delete)
     * @param int $rights File permissions
     * @return mixed Check status
     */
    public function checkActionRoles($action,$permissions){
        switch ($action){
            case 'd': $bit = 0; break;//delete
            case 'r': $bit = 1; break;//read
            case 'c': $bit = 2; break;//create
            default: return 0;
        }
        return ($permissions >> $bit) & 1;
    }


    /**
     * Create file record in base
     * @return mixed create status
     */
    public function createFile(){
        Engine::loadmodules(['users']);
        $owner_user_id = User::getSessionUserId();
        $file_id = $this->id;
        $name = $this->name;
        $conn = DBConnection::getInstance();
        $query = "CALL createFile('$file_id','$name','$owner_user_id');";
        return $conn->performQuery($query);
    }

    /**
     * Create file record in base
     * @return mixed create status
     */
    public function createFileFromData($data){
        $conn = DBConnection::getInstance();
        $query = "CALL createFileFromData('{$data['file_id']}','{$data['created_at']}','{$data['name']}','{$data['owner_id']}');";
        return $conn->performQuery($query);
    }

    /**
     * Create file record in base
     * @return mixed create status
     */
    public function createFileInSandbox(){
        Engine::loadmodules(['users']);
        $owner_user_id = User::getSessionUserId();
        $file_id = $this->id;
        $name = $this->name;
        $conn = DBConnection::getInstance();
        $query = "CALL createFileInSandbox('$file_id','{$this->type}','$name','$owner_user_id');";
        $result = $conn->performQuery($query);
        return $result;
    }


    /**
     * Delete file record from base
     * @return mixed Delete status
     */
    public function deleteFile(){
        $file_id = $this->id;
        $conn = DBConnection::getInstance();
        $query = "CALL deleteFile('$file_id');";
        return $conn->performQuery($query);
    }

    /**
     * Delete file link from base
     * @return mixed Delete status
     */
    public function deleteLink(){
        $conn = DBConnection::getInstance();
        $file_id = $this->id;
        $query = "CALL deleteLinkByFileId('$file_id');";
        return $conn->performQuery($query);
    }

    /**
     * Delete file roles records from base
     * @return mixed Delete status
     */
    public function deleteFileRoles(){
        $conn = DBConnection::getInstance();
        $file_id = $this->id;
        $query = "CALL deleteFileRoles('$file_id');";
        return $conn->performQuery($query);
    }


    /**
     * Create file roles in base
     * @return mixed Create status
     */
    public function createFileRoles(){
            $conn = DBConnection::getInstance();
            $file_id = $this->id;
            $roles = $this->roles;
            $status = true;
            foreach ($roles as $role) {
                $query = "CALL createFileRoles('$file_id','$role','7');";
                if (!$conn->performQuery($query))
                    $status = false;
            }
            return $status;
    }


    /**
     * Check file link existence
     * @return mixed Existence status
     */
    public function checkLink($file_id){
        $conn = DBConnection::getInstance();
        $query = "CALL checkFileLink('$file_id');";
        return $conn->performQuery($query);
    }

    //TODO catch download status, delete after download
    /**
     * Get temporary link by file id
     *
     * @return mixed Temporary link(hashed string)|false
     */
    public function getLink(){
        $conn = DBConnection::getInstance();
        $file_id = $this->id;
        $query = "CALL checkFileLink('$file_id');";
        $link =  $conn->performQueryFetch($query);
        if (!$link){
            $query = "CALL generateFileLink('$file_id');";
            $link = $conn->performQueryFetch($query);
        }
        return $link['link'];
    }
}