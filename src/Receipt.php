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

/**
 * Receipt for Ministry of Finance
 */
class Receipt {

    /**
     * Head part: message identifier
     * @var string
     */
    public $uuid_zpravy;

    /**
     * Head part: first sending
     * @var boolean
     */
    public $prvni_zaslani = true;

    /** @var string */
    public $dic_popl;

    /** @var string */
    public $dic_poverujiciho;

    /** @var string */
    public $id_provoz;

    /** @var string */
    public $id_pokl;

    /** @var string */
    public $porad_cis;

    /** @var \DateTime */
    public $dat_trzby;

    /** @var float */
    public $celk_trzba = 0;

    /** @var float */
    public $zakl_nepodl_dph = 0;

    /** @var float */
    public $zakl_dan1 = 0;

    /** @var float */
    public $dan1 = 0;

    /** @var float */
    public $zakl_dan2 = 0;

    /** @var float */
    public $dan2 = 0;

    /** @var float */
    public $zakl_dan3 = 0;

    /** @var float */
    public $dan3 = 0;

    /** @var int */
    public $rezim = 0;
}
