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

/**
 * Pars PKCS#12 and store X.509
 */
class Certificates
{
    /** @var string */
    private $privateKey;

    /** @var string */
    private $certificate;

    public function __construct($certificate, $password)
    {
        if(!file_exists($certificate)){
            throw new ClientException("Certificate not found");
        }

        $certificates = [];
        $pkcs12 = file_get_contents($certificate);

        $openSSL = openssl_pkcs12_read($pkcs12, $certificates, $password);

        if(!$openSSL) {
            throw new ClientException("Certificates export failed.");
        }

        $this->privateKey = $certificates['pkey'];
        $this->certificate = $certificates['cert'];
    }

    /**
     * @return string
     */
    public function getPrivateKey() {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function getCert() {
        return $this->certificate;
    }
}
