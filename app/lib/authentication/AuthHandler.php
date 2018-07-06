<?php
/**
 * Class AuthHandler Controls authentication modes
 */
class AuthHandler {

    /**
     * Returns an instance of auth class based on auth mode
     * Constant naming: {AUTH_METHOD_NAME}_AUTH_CUSTOM_CLASS
     *
     * @return mixed
     */
    public static function getInstance() {
        if (AUTH_METHOD === 'default') {
            $classname = AUTH_CUSTOM_CLASS;
        } else {
            $classname = constant(strtoupper(AUTH_METHOD).'_AUTH_CUSTOM_CLASS');
        }
        include_once ROOTDIR."/app/lib/authentication/custom_classes/$classname.php";
        return $classname::getInstance();
    }

}