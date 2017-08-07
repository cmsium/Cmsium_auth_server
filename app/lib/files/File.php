<?php

/**
 * Class File. Implements a number of methods for working with files.
 */
class File {

    public $path;
    public $content;

    public function __construct($path) {
        $this->path = $path;
    }

    /**
     * Функция для чтения контента любого файла
     *
     * @return string Возвращает содержимое файла
     */
    public function getContent() {
        if (file_exists($this->path)) {
            return file_get_contents($this->path);
        } else {
            ErrorHandler::throwException(NO_FILE_FOUND);
        }
    }

    public function exists() {
        return file_exists($this->path);
    }

    /**
     * Функция для записи любого содержания из строки в файл
     *
     * @param string $contents Строка для записи в файл (с перезаписыванием)
     * @return bool Результат выполнения
     */
    public function write($contents) {
        if (!file_put_contents($this->path, $contents)) {
            ErrorHandler::throwException(CANNOT_WRITE_FILE, 'page');
        }
        return true;
    }

    public function delete() {
        if (file_exists($this->path)) {
            return unlink($this->path);
        } else {
            ErrorHandler::throwException(NO_FILE_FOUND);
        }
    }

    public function compareWithPatch(File $file, $patch_dest) {
        return xdiff_file_bdiff($file->path, $this->path, $patch_dest);
    }

    public function applyDiffPatch(File $patch, $dest) {
        if (xdiff_file_bpatch($this->path, $patch->path, $dest)) {
            return new File($dest);
        } else {
            return false;
        }
    }

    public function __destruct() {}

}