<?php

namespace OCA\Tenant_Portal;

use \OCP\Template;
use \OCA\Tenant_Portal\Util;

class Mailer {
	private $mailer;
	private $to;
	private $from;
	private $subject;
	private $template;
	private $params;

	/**
	 * @param string|array $to
	 * @param string|array $from
	 * @param string $subject
	 * @param string $template
	 * @param array $params
	*/
	public function __construct($to, $from=null, $subject, $template, $params=Array()) {
		$this->mailer = \OC::$server->getMailer();
		$this->to = is_array($to) ? $to : Array($to);
		$this->from = is_array($from) ? $from : Array($from);
		$this->subject = $subject;
		$this->template = $template;
		$this->params = $params;
	}

	/**
	 * Send the message
	*/
	public function send() {
		try {
			$message = $this->createBase();
			list($html,$text) = $this->buildFromTemplate();
			if ($html) {
				$message->setHtmlBody($html);
			}
			if ($text) {
				$message->setPlainBody($text);
			}
			$this->mailer->send($message);
		} catch (\Exception $e) {
			Util::debugLog("@Mailer [to=>".implode(';',$this->to).", from=>".implode(';',$this->from).", subject=>".$this->subject.", type=>send, status=failed]");
			throw $e;
		}
	}

	/**
	 * Creates the base message object
	 * @return \OC\Mail\Message
	 */
	public function createBase() {
		try {
			$message = $this->mailer->createMessage();
			$message->setSubject($this->subject);
			$message->setTo($this->to);
			if (!empty($this->from)) {
				$message->setFrom($this->from);
			}
			return $message;
		} catch (\Exception $e) {
			Util::debugLog("@Mailer [to=>".implode(';',$this->to).", from=>".implode(';',$this->from).", subject=>".$this->subject.", type=>createBase, status=failed]");
			throw $e;
		}
	}

	/**
	 * Builds the email templates and returns them
	 * @return Array
	*/
	public function buildFromTemplate() {
		//html
		try {
			$template = new Template('tenant_portal', 'mail/'.$this->template);
			if ($this->params && !empty($this->params)) {
				foreach ($this->params as $key => $value) {
					$template->assign($key, $value);
				}
			}
			$html = $template->fetchPage();
		} catch (\Exception $e) {
			$html = null;
		}
		// plain text
		try {
			$template = new Template('tenant_portal', 'mail/'.$this->template.'.plain');
			if ($this->params && !empty($this->params)) {
				foreach ($this->params as $key => $value) {
					$template->assign($key, $value);
				}
			}
			$text = $template->fetchPage();
		} catch (\Exception $e) {
			$text = null;
		}
		return Array($html,$text);
	}
}
