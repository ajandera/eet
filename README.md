# PHP Client for EET

[![Downloads this Month](https://img.shields.io/packagist/dm/ajandera/eet.svg)](https://packagist.org/packages/ajandera/eet)
[![Latest stable](https://img.shields.io/packagist/v/ajandera/eet.svg)](https://packagist.org/packages/ajandera/eet)

## Installation
Install ajandera/eet using  [Composer](http://getcomposer.org/):

```sh
$ composer require ajandera/eet
```

### Dependencies
- PHP >=5.6
- php extensions: php_openssl.dll, php_soap.dll

Included WSDL, key and certificate for non-production usage (Playground).

## Example Usage
Sample codes are located in examples/ folder

```php
use Ajandera\EET\Receipt;
use Ajandera\EET\Strings;
use Ajandera\EET\Sender;

$receipt = new Receipt();
$receipt->uuid_zpravy = Strings::generateUUID();
$receipt->dic_popl = 'CZ78394560012';
$receipt->id_provoz = '567';
$receipt->id_pokl = '2';
$receipt->porad_cis = '1';
$receipt->dat_trzby = new \DateTime();
$receipt->celk_trzba = 100;

$sender = new Sender(
    '../../src/Schema/PlaygroundService.wsdl',
    '../certifications/eet.key',
    '../certifications/eet.pem'
);

echo $sender->sendReceipt($receipt); // return FIK code if success
```

### License
MIT

--  

### Library is used in CRM/ERP system shopyCRM (shopycrm.org)
