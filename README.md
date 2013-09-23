# SMMDB Module for SimpleSamlPhp

* Copy the directory to the modules folder. It is enabled by default.
* Add the following to `config/authsources.php`:

```php
'smmdb' => array(
	'smmdb:Rest',
	'smmdb_host' => 'hostname',
	'smmdb_api_key' => NULL,
	'smmdb_insecure' => true,
),
```
* Change the theme in `config/config.php` to `smmdb:smmdb`.
