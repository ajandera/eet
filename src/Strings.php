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
 * Class Strings
 * @package Ajandera\EET
 */
class Strings {

    /**
     * Format price to valid format
     * @param $value
     * @return string
     */
    public static function price($value) {
        return number_format($value, 2, '.', '');
    }

    /**
     * Create BKB code
     * @param $code
     * @return string
     */
    public static function BKB($code)
    {
        $r = '';
        for ($i = 0; $i < 40; $i++) {
            if ($i % 8 == 0 && $i != 0) {
                $r .= '-';
            }
            $r .= $code[$i];
        }
        return $r;
    }

    /**
     * Generate UUID v4
     * @return string
     */
    public static function generateUUID() {
       return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
           // 32 bits for "time_low"
           mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

           // 16 bits for "time_mid"
           mt_rand( 0, 0xffff ),

           // 16 bits for "time_hi_and_version",
           // four most significant bits holds version number 4
           mt_rand( 0, 0x0fff ) | 0x4000,

           // 16 bits, 8 bits for "clk_seq_hi_res",
           // 8 bits for "clk_seq_low",
           // two most significant bits holds zero and one for variant DCE1.1
           mt_rand( 0, 0x3fff ) | 0x8000,

           // 48 bits for "node"
           mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
       );
   }
}
