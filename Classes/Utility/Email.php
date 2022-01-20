<?php

namespace Sys25\RnBase\Utility;

use Swift_Attachment;
use tx_rnbase;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016-2021 René Nitzsche <rene@system25.de>
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
 * Encapsulate simple mailing functionality of TYPO3 for backward compatibility.
 *
 * @author René Nitzsche
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Email
{
    protected $attachments = [];

    protected $from;

    protected $fromName;

    protected $replyTo;

    protected $replyToName;

    protected $to = [];

    protected $subject = '';

    protected $htmlPart = '';

    protected $textPart = '';

    public function __construct()
    {
    }

    /**
     * @param string $subject
     *
     * @return Email
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * comma separated list of mailaddresses.
     *
     * @param string $emailAsString
     */
    public function setTo($emailAsString)
    {
        $addresses = Strings::trimExplode(',', $emailAsString);
        $this->to = [];
        foreach ($addresses as $mailAddress) {
            $this->to[$mailAddress] = '';
        }
    }

    /**
     * @param string $email
     * @param string $name
     *
     * @return Email
     */
    public function addTo($email, $name = '')
    {
        $this->to[$email] = $name;

        return $this;
    }

    /**
     * @param string $email
     * @param string $name
     *
     * @return Email
     */
    public function setFrom($email, $name = '')
    {
        $this->from = $email;
        $this->fromName = $name;

        return $this;
    }

    /**
     * @param string $email
     * @param string $name
     *
     * @return Email
     */
    public function setReplyTo($email, $name = null)
    {
        $this->replyTo = $email;
        $this->replyToName = $name;

        return $this;
    }

    /**
     * @param string $part
     *
     * @return Email
     */
    public function setTextPart($part)
    {
        $this->textPart = $part;

        return $this;
    }

    /**
     * @param string $part
     *
     * @return Email
     */
    public function setHtmlPart($part)
    {
        $this->htmlPart = $part;

        return $this;
    }

    /**
     * @param string $src
     * @param string $filename
     * @param string $contentType
     *
     * @return Email
     */
    public function addAttachment($src, $filename = '', $contentType = '')
    {
        $this->attachments[] = ['src' => $src, 'filename' => $filename, 'contentType' => $contentType];

        return $this;
    }

    /**
     * @return int the number of recipients who were accepted for delivery
     */
    public function send()
    {
        /* @var $mail \TYPO3\CMS\Core\Mail\MailMessage */
        $mail = tx_rnbase::makeInstance(Typo3Classes::getMailMessageClass());
        $mail->setFrom($this->from, $this->fromName);

        foreach ($this->to as $email => $name) {
            $mail->addTo($email, $name);
        }
        $mail->setSubject($this->subject);
        if ($this->replyTo) {
            $mail->addReplyTo($this->replyTo, $this->replyToName);
        }

        $this->addBody($mail);

        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                if (!$mail->attach(Swift_Attachment::fromPath($attachment['src']))) {
                    Logger::warn(
                        'Adding attachment failed!',
                        'rn_base',
                        ['subject' => $mail->subject, 'to' => $this->toAsString, 'attachment' => $attachment]
                    );
                }
            }
        }

        return $this->sendMessage($mail);
    }

    private function addBody(\TYPO3\CMS\Core\Mail\MailMessage $mail)
    {
        if ($this->htmlPart) {
            \Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher() ?
                $mail->html($this->htmlPart) :
                $mail->setBody($this->htmlPart, 'text/html');
        }
        if ($this->textPart) {
            \Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher() ?
                $mail->text($this->textPart) :
                $mail->addPart($this->textPart, 'text/plain');
        }
    }

    protected function sendMessage($mail)
    {
        return $mail->send();
    }
}
