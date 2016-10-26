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

use DOMDocument;
use WSSESoap;
use XMLSecurityDSig;
use XMLSecurityKey;

/**
 * Class SoapClient
 * @package Ajandera\EET
 */
class SoapClient extends \SoapClient {

    /** @var string */
    private $key;

    /** @var string */
    private $cert;

    /** @var boolean */
    private $traceRequired;

    /** @var float */
    private $connectionStartTime;

    /** @var float */
    private $lastResponseStartTime;

    /** @var float */
    private $lastResponseEndTime;

    /** @var string */
    private $lastRequest;

    /**
     * @param string $service
     * @param string $key
     * @param string $cert
     * @param boolean $trace
     */
    public function __construct($service, $key, $cert, $trace = false) {
        $this->connectionStartTime = microtime(true);
        parent::__construct($service, [
            'exceptions' => true,
            'trace' => $trace
        ]);
        $this->key = $key;
        $this->cert = $cert;
        $this->traceRequired = $trace;
    }

    /**
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param null $one_way
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $one_way = NULL) {
        $doc = new DOMDocument('1.0');
        $doc->loadXML($request);

        $objWSSE = new WSSESoap($doc);
        $objWSSE->addTimestamp();

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($this->key, true);
        $objWSSE->signSoapDoc($objKey, ["algorithm" => XMLSecurityDSig::SHA256]);

        $token = $objWSSE->addBinaryToken(file_get_contents($this->cert));
        $objWSSE->attachTokentoSig($token);

        $this->traceRequired && $this->lastResponseStartTime = microtime(true);

        $response = parent::__doRequest($this->lastRequest = $objWSSE->saveXML(), $location, $action, $version);

        $this->traceRequired && $this->lastResponseEndTime = microtime(true);

        return $response;
    }

    /**
     * @return float
     */
    public function __getLastResponseTime() {
        return $this->lastResponseEndTime - $this->lastResponseStartTime;
    }

    /**
     * @param bool $tillLastRequest
     * @return float
     */
    public function __getConnectionTime($tillLastRequest = false) {
        return $tillLastRequest ? $this->getConnectionTimeTillLastRequest() : $this->getConnectionTimeTillNow();
    }

    /**
     * @return float|null
     */
    private function getConnectionTimeTillLastRequest() {
        if (!$this->lastResponseEndTime || !$this->connectionStartTime) {
            return NULL;
        }
        return $this->lastResponseEndTime - $this->connectionStartTime;
    }

    /**
     * @return mixed|null
     */
    private function getConnectionTimeTillNow() {
        if (!$this->connectionStartTime) {
            return NULL;
        }
        return microtime(true) - $this->connectionStartTime;
    }

    /**
     * @return string
     */
    public function __getLastRequest() {
        return $this->lastRequest;
    }

}
