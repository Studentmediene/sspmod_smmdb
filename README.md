# SMMDB Module for SimpleSamlPhp

1. Copy the directory to the modules folder. It is enabled by default.
2. Add the following to config/authsources.php:

```php
'smmdb' => array(
	'smmdb:Rest',
	'smmdb_host' => 'hostname',
	'smmdb_api_key' => NULL,
	'smmdb_insecure' => true,
),
```
3. Change the theme in `config/config.php` to `smmdb:smmdb`.