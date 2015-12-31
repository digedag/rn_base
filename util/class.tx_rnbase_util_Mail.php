<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/


tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_util_Logger');
tx_rnbase::load('tx_rnbase_util_Strings');



/**
 * Encapsulate simple mailing functionality of TYPO3 for backward compatibility.
 */
class tx_rnbase_util_Mail {
	private $attachments = array();
	private $from, $fromName, $replyTo, $replyToName;

	/**
	 */
	public function __construct() {
	}
	public function send() {
		return $this->send45();
	}
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	public function setTo($emails) {
		$this->toAsString = $emails;
	}
	public function setFrom($email, $name='') {
		$this->from = $email;
		$this->fromName = $name;
	}
	public function setReplyTo($email, $name=NULL) {
		$this->replyTo = $email;
		$this->replyToName = $name;
	}
	public function setTextPart($part) {
		$this->textPart = $part;
	}
	public function setHtmlPart($part) {
		$this->htmlPart = $part;
	}
	public function addAttachment($src, $filename='', $contentType='') {
		$this->attachments[] = array('src'=>$src, 'filename'=>$filename, 'contentType'=>$contentType);
	}

	protected function send45() {
		/* @var $mail TYPO3\\CMS\\Core\\Mail\\MailMessage */
		$mail = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getMailMessageClass());
		$mail->setFrom(array($this->from => $this->fromName));
		$mail->setTo(tx_rnbase_util_Strings::trimExplode(',', $this->toAsString));
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

		$mail->send();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Mail.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Mail.php']);
}

