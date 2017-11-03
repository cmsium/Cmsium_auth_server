<?php
/**
 * Файл содержит константы, используемые для настроек приложения по умолчанию
 */

/**
 * Константа устанавливает абсолютный путь к корневой директории проекта
 */
define("ROOTDIR", dirname(__DIR__));

/**
 * Константа для определения пути к настройкам по умолчанию
 */
define("SETTINGS_PATH", ROOTDIR."/config/config.ini");

/**
 * Константа включает/выключает режим отладки
 */
define("DEBUG_MODE", true);

/**
 * Константа устанавливает секретную строку для генерации хэша пароля
 */
define("PASSGEN_KEYWORD", "be1f148abf98a0c19f114826ff71ed21");

/**
 * path to template of files to be included
 */
define("REQUIRES", ROOTDIR."/config/requires_templates/requires.debug.php");

/**
 * Sets the XLSX file upload watermark
 */
define("WATERMARK_HASH", "3c4866acc7da36d8ba7124f81969a8df");

/**
 * Sets a cell, where the watermark can be found
 */
define("WATERMARK_CELL", "N130");

define("ADMIN_WHITELIST", ['eeec1e618690fba21fd416df610da961']);

/**
 * Set custom authentification class
 */
define("AUTH_CUSTOM_CLASS","UserAuth");

/**
 * Set custom headers class
 */
define("HEADER_CUSTOM_CLASS","");

/**
 * Sets a token lifetime in secondsaddress_types
 */
define("TOKEN_LIFETIME",18000);

/**
 * Define default files storage path
 */
define('STORAGE',ROOTDIR."/storage/");

define('SANDBOX',ROOTDIR."/sandbox/");

/**
 * Maximum file upload size (bytes)
*/
define('MAX_FILE_UPLOAD_SIZE', 100000000);

/**
 * Event Ancestor class from app/modules/events
 */
define('EVENT_ANCESTOR_CLASS',"Event");

/**
 * Event Ancestor class from app/modules/documents
 */
define('DOCUMENT_ANCESTOR_CLASS',"Document");

/**
 * Event subclass format pattern
 */
define('EVENT_SCHEMA_PATTERN', '<?php
class EventSchema {

    public static $data_model = [%s];

}');

/**
 * Event subclass format pattern
 */
define('ROLES_SCHEMA_PATTERN', '<?php
class RolesSchema {

    public static $data_model = [%s];

}');

/**
 * Event subclass format pattern
 */
define('DOCUMENT_SCHEMA_PATTERN', '<?php
class DocumentSchema {

    public static $data_model = [%s];

}');

/**
 * Allowed event types
 */
define('EVENT_ALLOWED_TYPES', ['int','float','decimal','varchar', 'text','boolean','datetime','date','enum','set']);

define('EVENT_VALIDATE_MODEL_ITEMS', ['NOT NULL', 'UNIQUE', 'FOREIGN KEY [\dА-Яа-я\w\s\$]+\([\dА-Яа-я\w\s\$]+\)',
                                      'REFERENCE [\dА-Яа-я\w\s\$]+']);

define('TRANSLIT_MASK', [
    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
    'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
    'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
    'ъ' => 'ie', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'iu', 'я' => 'ia',
    'a' => 'a', 'b' => 'b', 'c' => 'c', 'd' => 'd', 'e' => 'e', 'f' => 'f', 'g' => 'g', 'h' => 'h', 'i' => 'i',
    'j' => 'j', 'k' => 'k', 'l' => 'l', 'm' => 'm', 'n' => 'n', 'o' => 'o', 'p' => 'p', 'q' => 'q', 'r' => 'r',
    's' => 's', 't' => 't', 'u' => 'u', 'v' => 'v', 'w' => 'w', 'x' => 'x', 'y' => 'y', 'z' => 'z', ' ' => '_',
    '_' => '_', '(' => '(', ')' => ')'
]);

define ("FILES_ALLOWED_TYPES",['jpg','jpeg','png','pdf','doc','docx']);

define ("ALLOWED_FILE_MIME_TYPES",['image/jpg','image/jpeg','image/png','application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword']);

define ('ACTION_PERMISSIONS_CLASS','RolesInActions');

define ('PAGES_OFFSET',10);
define ('PAGES_FOCUS_COUNT',10);

define ('FILES_PREVIEW_SIZE',100);

/**
 * Sandbox files expired time (in minutes)
 */
define('SANDBOX_FILES_EXPIRE_TIME',60);

define('SMTP_DEBUG', 2);
define('SMTP_FROM', 'no-reply');
define('SMTP_NAME', 'Uklad Ukladoff');

define('V_CODE_LIFETIME', 43200);