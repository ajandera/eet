<?php
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
    '../../src/PlaygroundService.wsdl',
    '../certifications/eet.key',
    '../certifications/eet.pem'
);

echo $sender->proceedSend($receipt); // return FIK and BPK code if success
