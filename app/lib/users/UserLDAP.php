<?php

/**
 * Class UserLDAP Working with LDAP user entry
 */
class UserLDAP {

    /**
     * Create user entry in LDAP directory
     *
     * @param string $user_id User ID
     * @param array $params_array Array of user params from input
     * @return bool Result
     */
    public static function create($user_id, $params_array) {
        $ldap = LDAPConnection::getInstance();
        // Check if no such user was created
        $result = $ldap->search("(cn={$params_array['username']})");
        if ($result['count'] === 0) {
            $data = [
                $params_array['username'],
                $params_array['lastname'],
                $user_id,
                time(),
                $params_array['firstname'],
                $params_array['email'],
                LDAPConnection::prepareMD5Password($params_array['password'])
            ];
            $processed_data = LDAPConnection::getDataPreset('default_user', $data);
            return $ldap->addRecord($result[0]['dn'], $processed_data);
        } else {
            return false;
        }
    }

    /**
     * Updates LDAP user entry's password attribute
     *
     * @param string $user_id
     * @param string $password
     * @return mixed
     */
    public static function updatePassword($user_id, $password) {
        $ldap = LDAPConnection::getInstance();
        $result = $ldap->search("(uid=$user_id)");
        $data = [
            'userPassword' => LDAPConnection::prepareMD5Password($password)
        ];
        return $ldap->editRecord($result[0]['dn'], $data);
    }

    /**
     * Deletes LDAP user entry
     *
     * @param string $id ID of user to delete
     * @return mixed Result
     */
    public static function destroy($id) {
        $ldap = LDAPConnection::getInstance();
        $result = $ldap->search("(uid=$id)");
        return $ldap->deleteRecord($result[0]['dn']);
    }

}