<?php
/**
 * RightPanel component
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 21.11.2011 Intellisoft OÜ
 *
 */

	function rightPanel($context, $controls = null, $filesCtrl = RPCONTROL_FILES_ENABLED, $msgCtrl = RPCONTROL_MESSAGES_ENABLED, $commentsCtrl = RPCONTROL_COMMENTS_ENABLED)
	{
		if(isset($context->obj->logEntry) && $context->obj->logEntry)
			return "";

		$rp = new RightPanel($controls);

		$rp->addControl(new CopyOfButton($context));
		if($filesCtrl)
			$rp->addControl(new FilesControl());
		if($msgCtrl)
			$rp->addControl(new MsgControl());
		if($commentsCtrl)
			$rp->addControl(new CommentsControl());

		return $rp->toHtml();
	}

	class RightPanel
	{
		public $controls;
		public $id = "rightPanel";

		function __construct($controls = null)
		{
			if(is_array($controls))
				$this->controls = $controls;
			else
				$this->controls = array();
		}

		function addControl($c)
		{
			$this->controls[] = $c;
			return $this;
		}

		function toHtml()
		{
			$html = "";
			foreach ( $this->controls as $c )
			{
				if(is_string($c))
					$html .= $c;
				else
					$html .= $c->toHtml();
			}

			if($html)
				$html = "<div " . ($this->id? "id=\"{$this->id}\"" : "") . " class=\"rightPanel\" style=\"display: none;\">$html</div>";
			return $html;
		}
	}

	class RightPanelItem
	{
		protected $context;

		function __construct($context = null)
		{
			$this->context = $context;
		}

		function icon($img)
		{
			return "<img src=\"" . app()->url("ui/img/16/" . $img) . "\" width=\"16\" height=\"16\" border=\"0\"/>";
		}

		function item($icon, $func, $caption)
		{
			return "<div class=\"rightPanelItem\">" .
				"<a href=\"JavaScript:$func;\" tabindex=\"-1\">" .
				$this->icon($icon) .
				t($caption) .
				"</a>" .
				"</div>";
		}

		function noIconPlainTextItem($caption)
		{
			return "<div class=\"rightPanelItem rightPanelItemNoIcon\">" .
				$caption .
				"</div>";
		}

		function toHtml()
		{
			return "";
		}
	}

	class TextRightPanelItem extends RightPanelItem
	{
		function __construct($h)
		{
			$this->html = $h;
		}

		function toHtml()
		{
			if($this->html)
				return $this->noIconPlainTextItem($this->html);
			else
				return "";
		}
	}

	class CopyOfButton extends RightPanelItem
	{
		function __construct($context)
		{
			$this->context = $context;
		}

		function toHtml()
		{
			$obj = $this->context->obj;
			if(is_object($cs = $obj->getCopySource()))
				return "<div class=\"rightPanelItem rightPanelItemNoIcon smallText\">" . t("Copy of") . " " . app()->getLinkedCaption($cs) . "</div>";
			else
				return "";
		}
	}

	class JSFuncButton extends RightPanelItem
	{
		private $action, $caption;

		function __construct($context, $action, $caption, $icon = "z.png")
		{
			$this->context = $context;
			$this->action = $action;
			$this->caption = $caption;
			$this->icon = $icon;
		}

		function toHtml()
		{
			return $this->item($this->icon, "JavaScript:{$this->action}", $this->caption);
		}
	}

	class ModificationDataRightPanel extends RightPanelItem
	{
		private $action, $caption;

		function __construct($context)
		{
			$this->context = $context;
		}

		function toHtml()
		{
			$html = "";
			if(is_object($obj = $this->context->obj))
			{
				$df = getFormatter(FORMAT_DATETIME);
				app()->uiHelper()->modificationDataWritten = true;

				$cl = trim(
						(is_object($c = $obj->getCreatorUser()) ? "<span class=\"smallText\">" . app()->getLinkedCaption($c) . "</span>" : "") .
						(isset($obj->mdCreated) && ($cd = $obj->mdCreated) ? "<div class=\"smallText\">" . $df->encodeHuman($cd) . "</div>" : "")
					);
				$ul = trim(
						(is_object($u = $obj->getUpdaterUser()) ? "<span  class=\"smallText\">" . app()->getLinkedCaption($u) . "</span>" : "") .
						(isset($obj->mdUpdated) && ($ud = $obj->mdUpdated) ? "<div class=\"smallText\">" . $df->encodeHuman($ud) . "</div>" : "")
					);
				$html =
					($cl ? "<span class=\"smallText rightPanelModDataCaption\">" . t("Created") . "</span>" . $cl : "") .
					($ul ? "<span class=\"smallText rightPanelModDataCaption\">" . t("Updated") . "</span>" . $ul : "");

			}
			return $this->noIconPlainTextItem($html);
		}
	}

	class AppFuncButton extends RightPanelItem
	{
		private $action, $caption;

		function __construct($context, $action, $caption)
		{
			$this->context = $context;
			$this->action = $action;
			$this->caption = $caption;
		}

		function toHtml()
		{
			return $this->item("z.png", "JavaScript:app.func('{$this->action}')", $this->caption);
		}
	}

	class AppConfirmedFuncButton extends RightPanelItem
	{
		private $action, $caption;

		function __construct($context, $action, $caption, $confirmMessage)
		{
			$this->context = $context;
			$this->action = $action;
			$this->caption = $caption;
			$this->confirmMessage = $confirmMessage;
		}

		function toHtml()
		{
			return $this->item("z.png", "JavaScript:app.confirmedFunc('" . t("$this->confirmMessage") . "', '{$this->action}')", $this->caption);
		}
	}

	class AjaxCommandButton extends RightPanelItem
	{
		private $action, $caption;

		function __construct($context, $action, $caption)
		{
			$this->context = $context;
			$this->action = $action;
			$this->caption = $caption;
		}

		function toHtml()
		{
			return $this->item("z.png", "JavaScript:ajaxCommand('{$this->action}')", $this->caption);
		}
	}

	class NewVersionButton extends RightPanelItem
	{
		function __construct($context)
		{
			$this->context = $context;
		}

		function toHtml()
		{
			$o = $this->context->obj;

			$ret = "<div class=\"rightPanelItem\">" .
					"<a href=\"JavaScript:newVersion();\">" .
					"<img src=\"" . app()->url("ui/img/16/print.png") . "\" border=\"0\"/>" .
					t("New version") . "</a></div>";

			return $ret;
		}
	}

	class LockButton extends RightPanelItem
	{
		function __construct($context)
		{
			$this->context = $context;
		}

		function toHtml()
		{
			$o = $this->context->obj;

			if(!app()->canLock($o->__table))
				return "";

			$action = "";
			if($o->canLock())
				$action = "lock";
			if($o->canUnlock())
				$action = "unlock";
			if($action)
			{
				$ret = "<div class=\"rightPanelItem\">" .
					"<a href=\"JavaScript:{$action}Document();\" id=\"rightPanelLockButtonLink\">" .
					"<img src=\"" . app()->url("ui/img/16/$action.png") . "\" border=\"0\"/>" .
					"<span id=\"rightPanelLockButtonText\">" . t($action) . "</span></a>";

				if($action == "unlock")
					if(isset($o->dynLastLocked))
						$ret .= "<div class=\"rightPanelItemNoIcon smallText\">" .
							(isset($o->mdLockerId) && $o->mdLockerId ?
							t("Locked by") . " " . app()->getLinkedCaption($o->getLink("mdLockerId"))
							: t("Last locked")) .
							"<br/>" . getFormatter(FORMAT_DATETIME)->encodeHuman($o->dynLastLocked) .
							"</div>";

				$ret .= "</div>";
			}
			else
				$ret = "";

			return $ret;
		}
	}

	class PrintLayoutSelector extends RightPanelItem
	{
		function __construct($obj)
		{
			$this->obj = $obj;
		}

		function toHtml()
		{
			$s = new Select();
			$s->prepareInput($this->obj, "dynPrintLayout", "Print layout", array("portrait" => "Portrait", "landscape" => "Landscape"));

			return "<div class=\"rightPanelItem rightPanelItemNoIcon\">" .

					$s->getInputPart() .

					"</div>";
		}
	}

	class PrintButtonOnlyInDB extends PrintButton
	{
		function toHtml()
		{
			return $this->context->obj->isInDatabase() ? parent::toHtml() : "";
		}
	}

	class PrintButton extends RightPanelItem
	{
		function __construct($context, $form = "", $caption = "print document")
		{
			$this->context = $context;
			$this->form = $form;
			$this->caption = $caption;
		}

		function toHtml()
		{
			return "<div class=\"rightPanelItem\">" .
					"<a href=\"?action=printDocument&registry=" . $this->context->obj->__table .
					($this->form ? "&form=" . $this->form : "") . 
					"&id=" . $this->context->obj->getIdValue() . "\" target=\"_blank\" tabindex=\"-1\">" .
					"<img src=\"" . app()->url("ui/img/16/print.png") . "\" border=\"0\"/>" . t($this->caption) .
					"</a></div>";
		}
	}

	class PrintButtonAsRequested extends RightPanelItem
	{
		function __construct($context, $form = "", $caption = "print document")
		{
			$this->context = $context;
			$this->form = $form;
			$this->caption = $caption;
		}

		function toHtml()
		{
			return "<div class=\"rightPanelItem\">" .
					"<a href=\"?action=printDocument&registry=" . app()->request(REQUEST_REGISTRY) .
					($this->form ? "&form=" . $this->form : "") .
					"&id=" . $this->context->obj->getIdValue() . "\" target=\"_blank\" tabindex=\"-1\">" .
					"<img src=\"" . app()->url("ui/img/16/print.png") . "\" border=\"0\"/>" . t($this->caption) .
					"</a></div>";
		}
	}

	class EmailButton extends RightPanelItem
	{
		function __construct($context, $form, $caption)
		{
			$this->context = $context;
			$this->form = $form;
			$this->caption = $caption;
		}

		function toHtml()
		{
			$obj = $this->context->obj;
			$ret = "<div class=\"rightPanelItem\">" .
					"<a href=\"JavaScript:app.ajaxCommand('?action=printDocument&registry=" . $this->context->obj->__table .
					"&form=" . $this->form . "&id=" . $obj->getIdValue() . "&email=1&send=1', 'sending email', app.loadLinkedEmails);\">" .
					"<img src=\"" . app()->url("ui/img/16/print.png") . "\" border=\"0\"/>" . t($this->caption) .
					"</a></div>";
			if(isset($obj->dynEmailLastSent))
				$ret .= "<div class=\"rightPanelItem\" style=\"padding-left: 16px; font-size: 8pt;\">" .
					t("Last e-mail sent") . "<br/>" . getFormatter(FORMAT_DATETIME)->encodeHuman($obj->dynEmailLastSent) .
					"</div>";
			return $ret;
		}
	}

	class FilesControl extends RightPanelItem
	{
		function toHtml()
		{
			return "<div class=\"rightPanelItem\" " .
					"style=\"padding-left: 16px; background: url('" . app()->url("ui/img/16/files.png") . "'); " .
					"background-repeat: no-repeat; background-position: left top; min-height: 16px;\" " .
					"id=\"filesControl\"></div>";
		}
	}

	class MsgControl extends RightPanelItem
	{
		function toHtml()
		{
			return "<div class=\"rightPanelItem rightPanelItemFiles\" " .
					"style=\"padding-left: 16px; background: url('" . app()->url("ui/img/16/messages.png") . "'); " .
					"background-repeat: no-repeat; background-position: left top; min-height: 16px;\" " .
					"id=\"msgControl\"></div>";
		}
	}

	class CommentsControl extends RightPanelItem
	{
		function toHtml()
		{
			if(app()->getCurrentContext()->obj->isNew())
				return "";
			else
				return "<div class=\"rightPanelItem rightPanelItemFiles\" " .
						"style=\"padding-left: 16px; min-height: 16px;\" " .
						"id=\"commentsControl\"></div>" .
						wrapScript("\$(function(){commentsControl.create($(\"#commentsControl\"))});");
		}
	}