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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_util_Logger');


/**
 * Encapsulate simple mailing functionality of TYPO3 for backward compatibility.
 */
class tx_rnbase_util_Mail {
	private $attachments = array();

	/**
	 * Makes debug output
	 * Prints $var in bold between two vertical lines
	 * If not $var the word 'debug' is printed
	 * If $var is an array, the array is printed by t3lib_div::print_array()
	 * Wrapper method for TYPO3 debug methods 
	 * 
	 * @param	mixed		Variable to print
	 * @param	string		The header.
	 * @param	string		Group for the debug console
	 * @return	void
	 */
	public function __construct() {
	}
	public function send() {
		if(tx_rnbase_util_TYPO3::isTYPO45OrHigher()) {
			return $this->send45();
		}
		else {
			return $this->send40();
		}
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
	public function setTextPart($part) {
		$this->textPart = $part;
	}
	public function setHtmlPart($part) {
		$this->htmlPart = $part;
	}
	public function addAttachment($src, $filename='', $contentType='') {
		$this->attachments[] = array('src'=>$src, 'filename'=>$filename, 'contentType'=>$contentType);
	}
	/**
	 */
	protected function send40() {
		/* @var $mail t3lib_htmlmail */
		$mail = t3lib_div::makeInstance('t3lib_htmlmail');
		$mail->start();
		$mail->subject         = $this->subject;
		$mail->from_email      = $this->from;
		$mail->from_name       = $this->fromName;
		$mail->organisation    = '';
		$mail->priority        = 1;
		if($this->textPart)
			$mail->addPlain($this->textPart);
		if($this->htmlPart)
			$mail->setHTML($this->htmlPart);
		if(!empty($this->attachments)) {
			// Hier können nur vorhandene Dateien verschickt werden.
			foreach ($this->attachments AS $attachment) {
				if(!$mail->addAttachment($attachment['src'])) {
					tx_rnbase_util_Logger::warn('Adding attachment failed!', 'rn_base', 
						array('subject'=>$mail->subject, 'to'=>$this->toAsString, 'attachment'=>$attachment));
				}
				
			}
		}
		$mail->send($this->toAsString);
	}
	protected function send45() {
		$mail = t3lib_div::makeInstance('t3lib_mail_Message');
		$mail->setFrom(array($this->from => $this->fromName));
		$mail->setTo(t3lib_div::trimExplode(',', $this->toAsString));
		$mail->setSubject($this->subject);
		// Or set it after like this
		if($this->htmlPart)
			$mail->setBody($this->htmlPart, 'text/html');
		
		// Add alternative parts with addPart()
		if($this->textPart)
			$mail->addPart($this->textPart, 'text/plain');
		if(!empty($this->attachments)) {
			foreach ($this->attachments AS $attachment) {
				// TODO!
			}
		}

		$mail->send();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Mail.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Mail.php']);
}
