<?php

namespace Sys25\RnBase\Utility;

use Sys25\RnBase\Tests\BaseTestCase;

/***************************************************************
 * Copyright notice
 *
 *  (c) 2016-2021 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *        GNU Lesser General Public License, version 3 or later
 */
class MailTest extends BaseTestCase
{
    /**
     * @group unit
     * @test
     */
    public function testSendMailWithAddressList()
    {
        $mail = $this->createMailMock();
        $this->assertInstanceOf('Tx_Rnbase_Utility_Mail', $mail);
        $mail->setFrom('test@test.com', 'fromname');
        $mail->setSubject('my subject');

        $addresses = ['to1@test.de', 'to2@test.de', 'to3@test.de'];
        $mail->setTo(implode(', ', $addresses));

        /* @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = $mail->send();
        $this->assertInstanceOf('TYPO3\CMS\Core\Mail\MailMessage', $message);

        $tos = $message->getTo();
        $this->assertEquals(3, count($tos));

        foreach ($tos as $key => $value) {
            if (TYPO3::isTYPO104OrHigher()) {
                $this->assertContains($value->toString(), $addresses);
            } else {
                $this->assertContains($key, $addresses);
            }
        }
    }

    /**
     * @group unit
     * @test
     */
    public function testSendMail()
    {
        $mail = $this->createMailMock();
        $this->assertInstanceOf('Tx_Rnbase_Utility_Mail', $mail);
        $mail->setFrom('test@test.com', 'fromname');
        $mail->setSubject('my subject');

        $mail->addTo('to1@test.de', 'to1');

        /* @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = $mail->send();

        $this->assertInstanceOf('TYPO3\CMS\Core\Mail\MailMessage', $message);

        $this->assertEquals('my subject', $message->getSubject());
        /* @var $to \Symfony\Component\Mime\Address[] */
        $to = $message->getTo();
        if (TYPO3::isTYPO104OrHigher()) {
            $this->assertCount(1, $to);
            $this->assertSame('to1', $to[0]->getName());
            $this->assertSame('to1@test.de', $to[0]->getAddress());
        } else {
            $this->assertSame(['to1@test.de' => 'to1'], $to);
        }
    }

    /**
     * Erstellt Mail-Instanz. Aufruf von send liefert das interne Mail-Objekt als Ergebnis.
     *
     * @return Email
     */
    protected function createMailMock()
    {
        $mailMock = $this->getMock(\Sys25\RnBase\Utility\Email::class, ['sendMessage']);
        $mailMock->expects($this->any())->method('sendMessage')->will($this->returnArgument(0));

        return $mailMock;
    }
}
