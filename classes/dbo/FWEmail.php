<?php
/*
 * Created on Mar 21, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

require_once "Mail.php";
require_once "Mail/mime.php";

define("MSG_WRONG_EMAIL_FORMAT", "Wrong email format");
define("MSG_EMAIL_SENT", "email sent");

class WrongEmailFormatException extends Exception{}

class FWEmail extends WFWObject
{
    protected $formats = array(
    	"mdCreated" => 			FORMAT_DATETIME,
    	"mdUpdated" => 			FORMAT_DATETIME,
    	"sent" => 				FORMAT_DATETIME,
    );

    private $dynPropertiesLoaded = false;
    private function loadDynProperties()
    {
    	if(!$this->dynPropertiesLoaded)
    	{
    		app()->system()->loadDynamicProperties();
			app()->user()->loadDynamicProperties();
			$this->dynPropertiesLoaded = true;
    	}
    }

    public function checkEmailExc($obj, $field, $addr)
    {
    	if(!filter_var($addr, FILTER_VALIDATE_EMAIL))
    	{
    		$obj->addWarning(new Warning(MSG_WRONG_EMAIL_FORMAT, $field, WARNING_ERROR));
    		throw new WrongEmailFormatException(MSG_WRONG_EMAIL_FORMAT);
    	}
    	return true;
    }

    public function checkEmail($m)
    {
    	return filter_var($m, FILTER_VALIDATE_EMAIL);
    }

    function getDefaultFor_sender()
	{
		$this->loadDynProperties();
		$u = app()->user();
		$u->loadDynamicPropertiesIfNotLoaded();
		return $u->dynEmailName . " <" . $u->dynEmail . ">";
	}

    function getDefaultFor_body()
	{
		$this->loadDynProperties();
		$sys = app()->system();

		$lc = app()->getLocale();
		$hf = "dynEmailHeader_$lc";
		$ff = "dynEmailFooter_$lc";

		return $sys->$hf . "\n\n\n" . $sys->$ff;
	}

	function getDefaultFor_signature()
	{
		$this->loadDynProperties();
		$sys = app()->system();
		$u = app()->user();

		return "<table cellspacing=\"20\" border=\"0\">" .
				"<tr><td valign=\"top\"><img src=\"" . $sys->dynCompanyLogo . "\" border=\"0\" /></td>" .
				"<td valign=\"top\"><b>" . $u->dynEmailName . "</b><br/>\n" . $u->dynOccupation . "\n</td></tr>" .
				"<tr><td valign=\"top\"><b>" . $sys->dynCompanyName . "</b><br/>\n" . $sys->dynCompanyAddress . "<br/>\n" . $sys->dynCompanyWeb . "\n</td>" .
				"<td valign=\"top\"><table border=\"0\">" .
				($u->dynPhone ? "<tr><td>" . t("Phone") . " </td><td>" . $u->dynPhone . "\n</td></tr>" : "") .
				($u->dynFax ? "<tr><td>" . t("fax") . " </td><td>" . $u->dynFax . "\n</td></tr>" : "") .
				($u->dynMobile ? "<tr><td>" . t("mobile") . " </td><td>" . $u->dynMobile . "\n</td></tr>" : "") .
				($u->dynEmail ? "<tr><td>" . t("email") . " </td><td>" . $u->dynEmail . "\n</td></tr>" : "") .
				"</td></tr></table>" .
				"</td></tr></table>";
	}

	function simpleSend($to, $subject, $body)
	{
		dbglog("simpleSend($to, $subject, $body)");
		$this->setDefaultValues();
		$this->recipient = $to;
		$this->subject = $subject;
		$this->body = $body;
		$this->sendTo($this->recipient);
		$this->sent = app()->now();
		return $this->insert();
	}

	function send()
	{
		if($this->recipient)
			$this->sendTo($this->recipient);
		if($this->bcc)
			$this->sendTo($this->bcc);

		$this->sent = app()->now();
		$this->update();
	}

	public $contentType = "text/html; charset=UTF-8";

	function sendCalendar($invite)
	{
		$from = $this->sender;
		$to = $this->recipient;
		$subject = $this->subject;

		$headers = array (
			"From" => $from,
		  	"To" => $to,
		  	"Subject" => $subject,
		 );

        $params = array("text_charset" => "UTF-8",
                "html_charset" => "UTF-8",
                "head_charset" => "UTF-8",
                "head_encoding" => "base64");


        $textparams = array(
                'charset'       => 'utf-8',
                'content_type'  => 'text/plain',
                'encoding'      => 'base64',
        );

        $calendarparams = array(
                'charset'       => 'utf-8',
                'content_type'  => 'text/calendar;method=REQUEST',
                'encoding'      => 'base64',
        );


        $email = new Mail_mimePart('', array('content_type' => 'multipart/alternative'));

        //$textmime = $email->addSubPart($this->body, $textparams);
        $htmlmime = $email->addSubPart($invite, $calendarparams);


        $final = $email->encode();
        $final['headers'] = array_merge($final['headers'], $headers);

		$smtp = Mail::factory(
			'smtp',
			array
			(
				'host' => $sys->dynSmtpServer,
		    	'auth' => ($sys->dynSmtpPassword != ""),
		    	'username' => $sys->dynSmtpLogin,
		    	'password' => $sys->dynSmtpPassword
		    )
		);
        $res = $smtp -> send($recipient, $final['headers'], $final['body']);

        $this->result .= $res . "\n";
	}

	function sendTo($addr)
	{
		$sys = app()->system();
		$sys->loadDynamicProperties();

		$mimeparams = array(
			"text_encoding" => "8bit",
			"text_charset" => "UTF-8",
			"html_charset" => "UTF-8",
			"head_charset" => "UTF-8",
			"head_encoding" => "base64",
		);

		$message = new Mail_mime($mimeparams);

		$text = str_replace("\n", "<br/>\n", $this->body) . "<br/>\n<br/>\n" . $this->signature;

		$message->setTXTBody(strip_tags($text));
		$message->setHTMLBody($text);
		foreach (explode(";", $this->attachment) as $att)
			if($att)
				$message->addAttachment($att);

		$body = $message->getMessageBody();

		$from = $this->sender;
		$to = $addr;
		$subject = $this->subject;

		$headers = array (
		  "From" => $from,
		  "To" => $to,
		  "Subject" => $subject,
		  "Content-Type" => "text/html; charset=UTF-8",
		  "Content-Transfer-Encoding" => "8bit",
		  //"Date" => date(DATE_RFC2822),
		 );

		$headers = $message->headers($headers);


		$smtp = Mail::factory('smtp',
			array (
				'host' => $sys->dynSmtpServer,
				//'port' => isset($sys->dynSmtpPort) ? $sys->dynSmtpPort : 25,
		    	'auth' => ($sys->dynSmtpPassword != ""),
		    	'username' => $sys->dynSmtpLogin,
		    	'password' => $sys->dynSmtpPassword)
			);

		$mail = $smtp->send($to, $headers, $body);

		$this->result .= $mail . "\n";
	}

	function isLockable()
	{
		return true;
	}

	function isLocked()
	{
		return $this->sent;
	}

	function getHumanEncodedResult()
	{
		$ret = "";
		$retOk = true;
		foreach (explode("\n", $this->result) as $res)
			if($res != "1" && $res != "")
			{
				$retOk = false;
				$ret .= ($ret ? "; " : "") . $res;
			}
		return  "<b>" . t(($retOk ? "Successfull" : "Failed")) . "</b> " . ($ret ? " : " : "") . $ret;
	}
}