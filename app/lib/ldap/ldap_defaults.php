<?php
/**
 * LDAP default constants file
 */

/**
 * Defines an LDAP authentication class
 */
define('LDAP_AUTH_CUSTOM_CLASS', 'UserLDAPAuth');

/**
 * LDAP connection config file path
 */
define("LDAP_SETTINGS_PATH", ROOTDIR."/config/config_ldap.ini");

/**
 * Create user account when logged in with LDAP the first time
 */
define("LDAP_CREATE_ON_SIGNIN", true);

/**
 * Create LDAP user entry during registration
 */
define("LDAP_CREATE_ENTRY_ON_SIGNUP", true);

/**
 * Modify LDAP user entry during user editing
 */
// define("LDAP_MODIFY_ENTRY_ON_EDIT", true);

/**
 * Update system user password when LDAP user entry password attribute is updated
 */
define("LDAP_SYNCHRONIZE_PASSWORDS", true);

/**
 * Delete LDAP user entry during system user destruction
 */
define("LDAP_DELETE_ENTRY_ON_DESTROY", true);