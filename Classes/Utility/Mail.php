<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2016 René Nitzsche <rene@system25.de>
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
 * @package TYPO3
 * @subpackage rn_base
 * @author René Nitzsche
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_Mail {
	protected $attachments = array();
	protected $from, $fromName, $replyTo, $replyToName;
	protected $to = array();

	/**
	 */
	public function __construct() {
	}

	/**
	 * @param string $subject
	 * @return Tx_Rnbase_Utility_Mail
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * comma separated list of mailaddresses.
	 * @param string $emailAsString
	 */
	public function setTo($emailAsString) {
		$addresses = tx_rnbase_util_Strings::trimExplode(',', $emailAsString);
		$this->to = array();
		foreach ($addresses As $mailAddress) {
			$this->to[$mailAddress] = '';
		}

	}
	/**
	 *
	 * @param string $email
	 * @param string $name
	 * @return Tx_Rnbase_Utility_Mail
	 */
	public function addTo($email, $name='') {
		$this->to[$email] = $name;
		return $this;
	}

	/**
	 *
	 * @param string $email
	 * @param string $name
	 * @return Tx_Rnbase_Utility_Mail
	 */
	public function setFrom($email, $name='') {
		$this->from = $email;
		$this->fromName = $name;
		return $this;
	}
	/**
	 *
	 * @param string $email
	 * @param string $name
	 * @return Tx_Rnbase_Utility_Mail
	 */
	public function setReplyTo($email, $name=NULL) {
		$this->replyTo = $email;
		$this->replyToName = $name;
		return $this;
	}
	/**
	 *
	 * @param string $part
	 * @return Tx_Rnbase_Utility_Mail
	 */
	public function setTextPart($part) {
		$this->textPart = $part;
		return $this;
	}
	/**
	 *
	 * @param string $part
	 * @return Tx_Rnbase_Utility_Mail
	 */
	public function setHtmlPart($part) {
		$this->htmlPart = $part;
		return $this;
	}
	/**
	 *
	 * @param string $src
	 * @param string $filename
	 * @param string $contentType
	 * @return Tx_Rnbase_Utility_Mail
	 */
	public function addAttachment($src, $filename='', $contentType='') {
		$this->attachments[] = array('src'=>$src, 'filename'=>$filename, 'contentType'=>$contentType);
		return $this;
	}

	/**
	 *
	 * @return integer the number of recipients who were accepted for delivery
	 */
	public function send() {
		/* @var $mail TYPO3\CMS\Core\Mail\MailMessage */
		$mail = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getMailMessageClass());
		$mail->setFrom($this->from, $this->fromName);

		foreach ($this->to As $email => $name) {
			$mail->addTo($email, $name);
		}
		$mail->setSubject($this->subject);
		if ($this->replyTo) {
			$mail->addReplyTo($this->replyTo, $this->replyToName);
		}
		// Or set it after like this
		if($this->htmlPart)
			$mail->setBody($this->htmlPart, 'text/html');

		// Add alternative parts with addPart()
		if($this->textPart)
			$mail->addPart($this->textPart, 'text/plain');

		if(!empty($this->attachments)) {
			foreach ($this->attachments AS $attachment) {
				if(!$mail->attach(Swift_Attachment::fromPath($attachment['src']))){
					tx_rnbase_util_Logger::warn('Adding attachment failed!', 'rn_base',
						array('subject'=>$mail->subject, 'to'=>$this->toAsString, 'attachment'=>$attachment));
				}
			}
		}

		return $this->sendMessage($mail);
	}
	protected function sendMessage($mail) {
		return $mail->send();
	}
}
