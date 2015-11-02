<?php

class FWSoftwareIssue extends WFWObject
{
    protected $formats = array(
    	"mdCreated" => 		FORMAT_DATETIME,
    	"mdUpdated" => 		FORMAT_DATETIME,
        "deadline" =>       FORMAT_DATE,
    	);

    protected $captionFields = array("id", "caption");

    protected $closedField = "closed";	//closable

    function getDefaultFor_ownerId()
    {
    	return app()->system()->dyndefsoftwareissueownerId;
    }

    function insert()
    {
    	$ret = parent::insert();
    	if(SOFTWAREISSUES_EMAIL_ON_INSERT)
    		$this->sendEmail();
    	return $ret;
    }

    function update()
    {
    	$bSendMail = false;
    	//if($this->ownerId)
    	//	$bSendMail = $this->isChanged("ownerId");
    	$ret = parent::update();
    	//if($bSendMail)
    	$this->sendEmail();
    	return $ret;
    }

    private function embpair($caption, $field = null)
    {
    	if(is_null($field))
    		$field = $caption;
    	return ($this->$field ? "<p><b>" . t($caption) . "</b><br/>" . $this->$field . "<br/></p>" : "");
    }

    function getEmailBody()
    {
    	return "<h2>" . $this->caption . "</h2>" .

    		$this->embpair("memo") .
    		$this->embpair("resolution") .
    		$this->embpair("state") .
    		"<hr/>" . $this->getLinkedCaption(array("id", "caption"));
    }

	function sendEmail()
	{
		$ret = false;
		if($eml = $this->getLink("ownerId")->uid . ($this->cc ? LIST_DELIMITER . $this->cc : ""))
			if(is_array($a = explode(LIST_DELIMITER, $eml)))
				foreach ($a as $e)
					if($e)
					{
						$e = trim($e);
						if(!filter_var($e, FILTER_VALIDATE_EMAIL))
						{
							$u = app()->dbo("webuser");
							$u->uid = $e;
							if($u->find(true))
							{
								$u->loadDynamicProperties();
								$e = $u->dynEmail;
							}
							else
								$e = "";
						}
						if($e)
						{
							$m = app()->dbo("email");
							$m->simpleSend($e, APP_TITLE . " " .  t("software issue") . " " . $this->caption, $this->getEmailBody());
							$ret = true;
						}
					}

		if($ret)
			app()->addWarning("Email sent");
		else
			app()->addWarning("No email to send");
	}
}