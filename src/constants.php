<?php /** @noinspection PhpDefineCanBeReplacedWithConstInspection */

define('APP_VERSION', '0.2.0');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'info');

define('OPENEHR_API_BASE_URL', trim(getenv('OPENEHR_API_BASE_URL') ?: 'http://localhost:8080/ehrbase/rest/openehr', "\/ \t\n\r\0\x0B") . '/');
define('CKM_API_BASE_URL', trim(getenv('CKM_API_BASE_URL') ?: 'https://ckm.openehr.org/ckm/rest', "\/ \t\n\r\0\x0B") . '/');

define('HTTP_SSL_VERIFY', getenv('HTTP_SSL_VERIFY') !== 'false' ? getenv('HTTP_SSL_VERIFY') : false);
define('HTTP_TIMEOUT', (float)getenv('HTTP_TIMEOUT') ?: 3.0);
