<?php
/**
 * This file is part of the shopyCRM (shopycrm.org)
 *
 * Copyright (c) 2016 Aleš Jandera <ales.jandera@gmail.com>
 *
 * For the full copyright and license information, please view
 * the file LICENSE.txt that was distributed with this source code.
 *
 */

namespace Ajandera\EET;

use Ajandera\EET\Exceptions\ClientException;
use Ajandera\EET\Exceptions\RequirementsException;
use Ajandera\EET\Exceptions\ServerException;
use XMLSecurityKey;

/**
 * Class Sender
 * @package Ajandera\EET
 */
class Sender {

    /**
     * Certificate key
     * @var string
     */
    private $key;

    /**
     * Certificate
     * @var string
     */
    private $cert;

    /**
     * WSDL path or URL
     * @var string
     */
    private $service;

    /**
     * @var boolean
     */
    public $trace;

    /**
     *
     * @var SoapClient
     */
    private $soapClient;

    /**
     * @param string $key
     * @param string $cert
     */
    public function __construct($key, $cert) {
        $this->service = __DIR__.'/PlaygroundService.wsdl';
        $this->key = $key;
        $this->cert = $cert;
        $this->checkRequirements();
    }

    /**
     * Proceed with sending.
     * @param Receipt $receipt
     * @return boolean|string
     */
    public function check(Receipt $receipt) {
        try {
            return $this->proceedSend($receipt, true);
        } catch (ServerException $e) {
            return $e;
        }
    }

    /**
     * Get connection time.
     * @param boolean $tillLastRequest optional If not set/false connection time till now is returned.
     * @return float
     */
    public function getConnectionTime($tillLastRequest = false) {
        !$this->trace && $this->throwTraceNotEnabled();
        return $this->getSoapClient()->__getConnectionTime($tillLastRequest);
    }

    /**
     * Get size of last response.
     * @return int
     */
    public function getLastResponseSize() {
        !$this->trace && $this->throwTraceNotEnabled();
        return mb_strlen($this->getSoapClient()->__getLastResponse(), '8bit');
    }

    /**
     * Get size of last request.
     * @return int
     */
    public function getLastRequestSize() {
        !$this->trace && $this->throwTraceNotEnabled();
        return mb_strlen($this->getSoapClient()->__getLastRequest(), '8bit');
    }

    /**
     * Get time of last response.
     * @return float time in ms
     */
    public function getLastResponseTime() {
        !$this->trace && $this->throwTraceNotEnabled();
        return $this->getSoapClient()->__getLastResponseTime();
    }

    /**
     * Throw a message is Trace is not enabled.
     * @throws ClientException
     */
    private function throwTraceNotEnabled() {
        throw new ClientException('Trace is not enabled! Set trace property to TRUE.');
    }

    /**
     * 
     * @param Receipt $receipt
     * @return array
     */
    public function getCheckCodes(Receipt $receipt) {
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($this->key, true);

        $arr = [
            $receipt->dic_popl,
            $receipt->id_provoz,
            $receipt->id_pokl,
            $receipt->porad_cis,
            $receipt->dat_trzby->format('c'),
            Strings::price($receipt->celk_trzba)
        ];
        $sign = $objKey->signData(join('|', $arr));

        return [
            'pkp' => [
                '_' => $sign,
                'digest' => 'SHA256',
                'cipher' => 'RSA2048',
                'encoding' => 'base64'
            ],
            'bkp' => [
                '_' => Strings::BKB(sha1($sign)),
                'digest' => 'SHA1',
                'encoding' => 'base16'
            ]
        ];
    }

    /**
     * Send recipient.
     * @param Receipt $receipt
     * @param boolean $check
     * @return boolean|string
     */
    public function proceedSend(Receipt $receipt, $check = false) {

        $this->initSoapClient();

        $response = $this->prepareData($receipt, $check);
        $bkp = $this->getCheckCodes($receipt);

        if(isset($response->Chyba)) {
            $this->catchError($response->Chyba);
        }

        return $check ? true : json_encode([
            'fik' => $response->Potvrzeni->fik,
            'bkp' => $bkp['bkp']['_']
        ]);
    }

    /**
     * Check requirement php extensions.
     * @throws RequirementsException
     * @return void
     */
    private function checkRequirements() {
        if (!class_exists('\SoapClient')) {
            throw new RequirementsException('Class SoapClient is not defined! Please, allow php extension php_soap.dll in php.ini');
        }
    }

    /**
     * Get SOAP client.
     * @return SoapClient
     */
    private function getSoapClient() {
        !isset($this->soapClient) && $this->initSoapClient();
        return $this->soapClient;
    }

    /**
     * Initialize SOAP client.
     * @return void
     */
    private function initSoapClient() {
        $this->soapClient = new SoapClient($this->service, $this->key, $this->cert, $this->trace);
    }

    /**
     * Prepare data for sending.
     * @param Receipt $receipt
     * @param boolean $check
     * @return object
     */
    private function prepareData(Receipt $receipt, $check = false) {
        $head = [
            'uuid_zpravy' => $receipt->uuid_zpravy ? $receipt->uuid_zpravy : Strings::generateUUID(),
            'dat_odesl' => time(),
            'prvni_zaslani' => $receipt->prvni_zaslani,
            'overeni' => $check
        ];

        $body = [
            'dic_popl' => $receipt->dic_popl,
            'dic_poverujiciho' => $receipt->dic_poverujiciho,
            'id_provoz' => $receipt->id_provoz,
            'id_pokl' => $receipt->id_pokl,
            'porad_cis' => $receipt->porad_cis,
            'dat_trzby' => $receipt->dat_trzby->format('c'),
            'celk_trzba' => Strings::price($receipt->celk_trzba),
            'zakl_nepodl_dph' => Strings::price($receipt->zakl_nepodl_dph),
            'zakl_dan1' => Strings::price($receipt->zakl_dan1),
            'dan1' => Strings::price($receipt->dan1),
            'zakl_dan2' => Strings::price($receipt->zakl_dan2),
            'dan2' => Strings::price($receipt->dan2),
            'zakl_dan3' => Strings::price($receipt->zakl_dan3),
            'dan3' => Strings::price($receipt->dan3),
            'rezim' => $receipt->rezim
        ];

        return $this->getSoapClient()->OdeslaniTrzby([
                'Hlavicka' => $head,
                'Data' => $body,
                'KontrolniKody' => $this->getCheckCodes($receipt)
            ]
        );
    }

    /**
     * Catch error in response object.
     * @param $error
     * @throws ServerException
     */
    private function catchError($error) {
        if ($error->kod) {
            $message = [
                -1 => 'Docasna technicka chyba zpracovani – odeslete prosim datovou zpravu pozdeji',
                2 => 'Kodovani XML neni platne',
                3 => 'XML zprava nevyhovela kontrole XML schematu',
                4 => 'Neplatny podpis SOAP zpravy',
                5 => 'Neplatny kontrolni bezpecnostni kod poplatnika (BKP)',
                6 => 'DIC poplatnika ma chybnou strukturu',
                7 => 'Datova zprava je prilis velka',
                8 => 'Datova zprava nebyla zpracovana kvuli technicke chybe nebo chybe dat',
            ];
            $msg = isset($message[$error->kod]) ? $message[$error->kod] : '';
            throw new ServerException($msg, $error->kod);
        }
    }

}
