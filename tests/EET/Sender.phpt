<?php

namespace Ajandera\EET\Test;

require_once '../../vendor/autoload.php';

use Ajandera\EET\Sender as TestedSender;
use Ajandera\EET\Exceptions\ClientException;
use Ajandera\EET\Exceptions\ServerException;
use Ajandera\EET\Receipt;
use Tester\Assert;
use Tester\TestCase;

class TestSender extends TestCase {

    /**
     * Create example Sender.
     * @return TestedSender
     */
    private function getSender() {
        return new TestedSender(
            '../../src/Schema/PlaygroundService.wsdl',
            '../../examples/certifications/eet.key',
            '../../examples/certifications/eet.pem'
        );
    }

    /**
     * Create example Receipt.
     * @return Receipt
     */
    private function getReceipt() {
        $receipt = new Receipt();
        $receipt->uuid_zpravy = 'aa5f94ad-446f-4b15-9a71-1b0235467c1c';
        $receipt->dic_popl = 'CZ78394560012';
        $receipt->id_provoz = '567';
        $receipt->id_pokl = '2';
        $receipt->porad_cis = '1';
        $receipt->dat_trzby = new \DateTime();
        $receipt->celk_trzba = 100;
        return $receipt;
    }

    /**
     * Test send correct.
     */
    public function testSend() {
        $fik = $this->getSender()->proceedSend($this->getReceipt());
        Assert::type('string', $fik);
    }

    /**
     * Test send error.
     */
    public function testSendError() {
        $receipt = $this->getReceipt();
        $receipt->dic_popl = 'x';
        Assert::exception(function() use ($receipt) {
            $this->getSender()->proceedSend($receipt);
        }, ServerException::class);
    }

    /**
     * Test get connection time.
     */
    public function testGetConnectionTime() {
        $sender = $this->getSender();
        $sender->trace = true;
        $sender->proceedSend($this->getReceipt());
        $time = $sender->getConnectionTime();
        Assert::type('float', $time);
        Assert::true($time > 0);
    }

    /**
     * Test get connection time till last request.
     */
    public function testGetConnectionTimeTillLastRequest() {
        $sender = $this->getSender();
        $sender->trace = true;
        $sender->proceedSend($this->getReceipt());
        $time = $sender->getConnectionTime(true);
        Assert::type('float', $time);
        Assert::true($time > 0);
    }

    /**
     * Test get last response time.
     */
    public function testGetLastResponseTime() {
        $sender = $this->getSender();
        $sender->trace = true;
        $sender->proceedSend($this->getReceipt());
        $time = $sender->getLastResponseTime();
        Assert::type('float', $time);
        Assert::true($time > 0);
    }

    /**
     * Get last request size.
     */
    public function testGetLastRequestSize() {
        $sender = $this->getSender();
        $sender->trace = true;
        $sender->proceedSend($this->getReceipt());
        $size = $sender->getLastRequestSize();
        Assert::type('int', $size);
        Assert::true($size > 0);
    }

    /**
     * Get last response size.
     */
    public function testGetLastResponseSize() {
        $sender = $this->getSender();
        $sender->trace = true;
        $sender->proceedSend($this->getReceipt());
        $size = $sender->getLastResponseSize();
        Assert::type('int', $size);
        Assert::true($size > 0);
    }

    /**
     * Test trace no enabled.
     */
    public function testTraceNotEnabled() {
        $sender = $this->getSender();
        $sender->proceedSend($this->getReceipt());
        Assert::exception(function() use ($sender) {
            $sender->getLastResponseSize();
        }, ClientException::class);
    }
}

(new TestSender())->run();