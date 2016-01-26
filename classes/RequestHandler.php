<?php
/*
 * Created on Nov 4, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

 	define("REQUEST_PATH", "path");
 	define("REQUEST_ACTION", "action");
 	define("REQUEST_ID", "id");

 	define("ACTION_EDIT", "edit");
 	define("ACTION_OPEN", "open");

 	define("REQUEST_SESSION_VARIABLE_NAME", "svname");
 	define("REQUEST_SESSION_VARIABLE_VAL", "svval");

 	define("ERROR_NO_PATH", "no path");
 	define("FIELD_UPDATE", "update");

 	define("LOCKACTION_LOCK", "lock");
 	define("LOCKACTION_UNLOCK", "unlock");


	class RequestHandler
	{
		function doHandle()
		{
				if($action = app()->request(REQUEST_ACTION, "index"))
				{
					if($registry = app()->request(REQUEST_REGISTRY))
					{
						app()->requireLogin();
						if(is_object($d = app()->getRegistryDescriptor()))
							if($d->handleRequest($action))
								return;
					}

					if(method_exists($this, $action))
					{
						$this->$action();
						return;
					}
				}
				else
				{
					$this->index();
					return;
				}
		}

		function handle()
		{
			try
			{
				$this->doHandle();
				app()->runDeferredTasks();
			}
			catch(Exception $e)
			{
				app()->addWarning(new Warning($e->getMessage(), "", WARNING_ERROR));
				echo app()->jsonMessage(RESULT_ERROR, t($e->getMessage()));
			}
		}

		function loginform()
		{
			include app()->getAbsoluteFile("ui/login.php");
		}

		function loginJson()
		{
			app()->loginJson();
		}

		function versionLog()
		{
			if($fn = app()->getAbsoluteFile(VERSIONLOG_FILE))
				echo file_get_contents($fn);
		}

		function announce()
		{
			if(!defined("ANNOUNCE_COMPONENTS"))
				define("ANNOUNCE_COMPONENTS", "Web framework");
			if(!defined("ANNOUNCE_LANG"))
				define("ANNOUNCE_LANG", "");

			$url = "http://intellisoft.ee/webservices/announce.php?l=" . urlencode(ANNOUNCE_LANG) . "&c=" . urlencode(ANNOUNCE_COMPONENTS);

			echo file_get_contents($url);
		}

		function index()
		{
			app()->requireLogin();
			include app()->getAbsoluteFile("ui/index.default.php");
		}

		function translationTool()
		{
			app()->requireLogin();
			include app()->getAbsoluteFile("ui/translationTool.php");
		}

		function dbdoc()
		{
			app()->requireLogin();
			app()->dbDocumentor()->run();
		}

		function tipSystemDisplayed()
		{
			app()->requireLoginJson();
			if($id = (int)app()->request("id"))
			{
				$ts = app()->dbo("tipshown");
				$ts->tipId = $id;
				$ts->userId = app()->user()->getIdValue();
				$ts->insert();
			}
		}

		function tipSystemDismiss()
		{
			app()->requireLoginJson();

			$uid = app()->user()->getIdValue();

			app()->query("insert into tipshown(tipId, userId) " .
					"select id as tipId, $uid from tipsystem " .
					"where id not in (select tipId from tipshown where userId = $uid)");
		}

		function requirePasswd()
		{
			app()->requireLogin();
			include app()->getAbsoluteFile("ui/passwd.php");
		}

		function passwd()
		{
			app()->requireLogin();
			$o = app()->request("old");
			$n = app()->request("new");
			echo app()->passwd($o, $n);
		}

		function clearSession()
		{
			app()->requireLogin();
			$_SESSION[INSTANCE_WEB] = array();
			app()->location("?action=showSession");
		}

		function showSession()
		{
			app()->requireLogin();
			echo "<pre><small>request: " . print_r($_REQUEST, true) . "<br/>session:<br/>";
			if(is_array($_SESSION[INSTANCE_WEB]))
				foreach ( $_SESSION[INSTANCE_WEB] as $key => $value )
					echo $key . ": " . print_r(unserialize($value), true) . "<br/><br/>";

       		echo "<hr/><a href=\"" . app()->url("?action=clearSession") . "\">clear</a>";
		}

		function mainMenuItems()
		{
			app()->requireLoginJson();
			echo app()->jsonMessage(RESULT_OK, "", array(
				"items" => app()->queryAsArray(
						"select distinct m.name as module, o.name as reg, " .
						"concat('ro_', o.name) as caption, p.name as rtype " .
						"from rmodule m " .
						"inner join robject o on o.module = m.id " .
						"inner join menupart p on p.id = o.menupartid " .
						"inner join objectright r on r.registryId = o.id " .
						"inner join userrole u on u.roleid = r.roleid and r.s = 1 " .
						"and u.userid = " . app()->user()->getIdValue() . " " .
						"order by m.pos, m.id, o.menupartid, o.typeid, o.pos, o.id",
						DB_FETCHMODE_OBJECT,
						array(
							"caption" => FORMAT_TRANSLATED,
							"module" => FORMAT_TRANSLATED,
							"rtype" => FORMAT_TRANSLATED
						)
					)
				));
		}

		function mainMenu()
		{
			if(!app()->user()->isNew())
			{
				mainMenuNew();
				echo hr(),
					linkItem("document.location = '" . app()->url() . "';", "Frontpage"),
					linkItem("app.mainMenu();", "close");
			}
		}

		function saveField()
		{
			app()->requireLoginJson();
			//check privileges in context!

			if($path = app()->request(REQUEST_PATH))
			{
				$add = array();

				list($pref, $path2) = app()->explodePath($path);

				if(strpos($path2, "filter" . CHILD_DELIMITER) === 0)
				{
					//saving filter
					list($f, $field) = explode(CHILD_DELIMITER, $path2, 2);

					if(is_object($descriptor = app()->getRegistryDescriptor($pref)))
						$descriptor->getFilter()->setValue($field, app()->request("v"));

					echo app()->jsonMessage(RESULT_OK, "ok", $add);
					return;
				}

				if(strpos($path2, "gridsetup" . CHILD_DELIMITER) === 0)
				{
					//saving filter
					list($f, $field) = explode(CHILD_DELIMITER, $path2, 2);

					if(is_object($descriptor = app()->getRegistryDescriptor($pref)))
						$descriptor->getGridSettings()->setValue($field, app()->request("v"));

					echo app()->jsonMessage(RESULT_OK, "ok", $add);
					return;
				}

				//saving context object field
				if(!is_object($context = app()->getContext($pref)))
				{
					if(is_object($descriptor = app()->getRegistryDescriptor($pref)))
					{
						$id = substr($pref, strlen($descriptor->registry));
						$_REQUEST[REQUEST_REGISTRY] = $descriptor->registry;
						$_REQUEST[REQUEST_ID] = $id;
						$context = $descriptor->createRegistryContextForId($id);
					}

					if(!(is_object($context) && $descriptor->loadContext($context)))
					{
						app()->addWarning(new Warning(t("Requested document does not exist"), WARNING_ERROR, $path2));
						echo app()->jsonMessage(RESULT_OK, "ok", $add);
						return;
					}
					app()->addWarning(new Warning(t("No context, reload document"), WARNING_ERROR, $path2));
					app()->requireReloadPage();
				}

				$context->setValueByPath($path2, app()->request("v"));
				app()->putContext($context);
				echo app()->jsonMessage(RESULT_OK, "ok", $add);
			}
			else
				echo app()->jsonMessage(RESULT_ERROR, ERROR_NO_PATH);
		}

		function writeToSession()
		{
			app()->requireLoginJson();
			if($name = app()->request(REQUEST_SESSION_VARIABLE_NAME))
			{
				$_SESSION[INSTANCE_WEB][$name] = app()->request(REQUEST_SESSION_VARIABLE_VAL);
				echo app()->jsonMessage(RESULT_OK);
			}
		}

		function readFromSession()
		{
			app()->requireLoginJson();
			if($name = app()->request(REQUEST_SESSION_VARIABLE_NAME))
				echo app()->jsonMessage(RESULT_OK, isset($_SESSION[INSTANCE_WEB][$name]) ? $_SESSION[INSTANCE_WEB][$name] : "");
		}


		function files()
		{
			app()->requireLoginJson();
			if($path = app()->request(REQUEST_PATH))
			{
				list($pref, $path2) = app()->explodePath($path); //explode(CHILD_DELIMITER, $path, 2);
				if(is_object($context = app()->getContext($pref)))
					if(is_object($obj = $context->obj))
					{
						app()->requirePrivilegeJson(PRIVILEGE_SELECT, $obj->__table);
						echo app()->jsonMessage(RESULT_OK, "ok", array("files" => $obj->getFiles()));
						return;
					}
			}
			echo app()->jsonMessage(RESULT_ERROR, "");
		}

		function messages()
		{
			app()->requireLoginJson();
			if($path = app()->request(REQUEST_PATH))
			{
				list($pref, $path2) = app()->explodePath($path);
				if(is_object($context = app()->getContext($pref)))
					if(is_object($obj = $context->obj))
					{
						app()->requirePrivilegeJson(PRIVILEGE_SELECT, $obj->__table);
						//app()->requirePrivilegeJson(PRIVILEGE_SELECT, "message");
						echo app()->jsonMessage(RESULT_OK, "ok", array("msg" => $obj->getMessages()));
						return;
					}
			}
			echo app()->jsonMessage(RESULT_ERROR, "");
		}

		function sendMessage()
		{
			app()->requireLoginJson();
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE, "message");

			if(!$reciever = app()->request("reciever"))
				die(app()->jsonMessage(RESULT_ERROR, t("reciever empty"), array("focus" => "Reciever")));
			if(!$subject = app()->request("subject"))
				die(app()->jsonMessage(RESULT_ERROR, t("subject empty"), array("focus" => "Subject")));
			if(!$body = app()->request("body"))
				die(app()->jsonMessage(RESULT_ERROR, t("body empty"), array("focus" => "Body")));

			$robj = "";
			if($path = app()->request(REQUEST_PATH))
			{
				list($pref, $path2) = app()->explodePath($path);
				if(is_object($context = app()->getContext($pref)))
					if(is_object($obj = $context->obj))
						$robj = $obj->getRObjectID();
			}

			$a = explode(LIST_DELIMITER, $reciever);
			$recv = array();
			foreach ( $a as $addr )
			{
				$u = app()->dbo("webuser");
				$u->uid = trim($addr);
				$u->find(true);
				if($u->getIdValue())
					$recv[] = $u->getIdValue();
				else
					die(app()->jsonMessage(RESULT_ERROR, t("reciever not found") . " " . $addr, array("focus" => "Reciever")));
			}

			foreach ( $recv as $userId )
			{
				$msg = app()->dbo("message");
				$msg->recieverId = $userId;
				$msg->caption = $subject;
				$msg->body = $body;
				if($i = app()->request("replyToID"))
					$msg->replyToID = $i;
				if($robj)
					$msg->robject = $robj;
				$msg->send();
			}

			app()->addWarning(new Warning(t("message sent")));
			echo app()->jsonMessage(RESULT_OK, "ok");
		}

		function newVersion()
		{
			app()->requireLoginJson();

			if($path = app()->request(REQUEST_PATH))
				if(is_object($context = app()->getContext($path)))
					if(is_object($obj = $context->obj))
						$obj->newVersion();

			echo app()->jsonMessage(RESULT_OK, "ok", array("reload" => "true"));
		}

		function delete()
		{
		 	app()->requireLoginJson();

			if($path = app()->request(REQUEST_PATH))
			{
				$context = app()->getContext($path);
				if($context->delete())
					echo app()->jsonMessage(RESULT_OK, "ok", array("goback" => "true"));
				else
					echo app()->jsonMessage(RESULT_ERROR, t("Cant delete"));
			}
		}

		private function lockAction($a)
		{
			app()->startTimer();
			app()->requireLoginJson();
			$qa = "can" . strtoupper(substr($a, 0, 1)) . substr($a, 1);

			if($path = app()->request(REQUEST_PATH))
			{
				if(strpos($path, CHILD_DELIMITER))
					list($pref, $path2) = app()->explodePath($path);
				else
					$pref = $path;
				if(is_object($context = app()->getContext($pref)))
				{
					if($a == LOCKACTION_LOCK)
					{
						$context->save();
						app()->putContext($context);
					}

					if(is_object($obj = $context->obj))
					{
						app()->requirePrivilegeJson(PRIVILEGE_LOCK, $obj->__table);
						if(method_exists($obj, $a) && method_exists($obj, $qa))
						{
							if(!($err = $obj->getLockErrors()))
							{
								$context->save();
								app()->putContext($context);
								if($obj->$qa())
								{
									if($a == LOCKACTION_LOCK)
										$obj->mdLockerId = app()->user()->getIdValue();
									if($a == LOCKACTION_UNLOCK)
										$obj->mdLockerId = NULL_VALUE;
									$obj->$a();
									if($a == LOCKACTION_LOCK)
										$obj->setTimestamp("LastLocked");

									echo app()->jsonMessage(RESULT_OK, "ok", array("reload" => "true"));
									app()->endTimerAndLogDbo("document " . $a . "ed", $context->obj);
									return;
								}
								else
									$err = "cant $a";
							}
						}
						else
							$err = "cant do this with such sort of object";
					}
					else
						$err = "no object in context";
				}
				else
					$err = "no context";
			}
			else
				$err = "no path";

			echo app()->jsonMessage(RESULT_ERROR, t($err));
		}

		function lock()
		{
			return $this->lockAction(LOCKACTION_LOCK);
		}

		function unlock()
		{
			return $this->lockAction(LOCKACTION_UNLOCK);
		}

		private function getContextByPath($path)
		{
			if($path)
			{
				list($pref, $path2) = app()->explodePath($path);
				if(is_object($context = app()->getContext($pref)))
					return $context;
			}
			throw new NoPathException(ERROR_NO_PATH);
		}

		function finishAndSaveContext($context)
		{
			$add = array();
			app()->putContext($context);
			if(count(app()->updatedFields))
				$add[FIELD_UPDATE] = app()->updatedFields;
			echo app()->jsonMessage(RESULT_OK, "ok", $add);
		}

		function moveChildUp()
		{
			app()->requireLoginJson();
			$context = $this->getContextByPath($path = app()->request(REQUEST_PATH));
			$context->reorderChild($path, -1);
			$this->finishAndSaveContext($context);
		}

		function moveChildDn()
		{
			app()->requireLoginJson();
			$context = $this->getContextByPath($path = app()->request(REQUEST_PATH));
			$context->reorderChild($path, 1);
			$this->finishAndSaveContext($context);
		}

		/**
		 * Delete document child
		 */
		public function deleteChild()
		{
			app()->requireLoginJson();
			if($path = app()->request(REQUEST_PATH))
			{
				$add = array();

				list($pref, $path2) = app()->explodePath($path);
				$context = app()->getContext($pref);
				$context->deleteChild($path);
				app()->putContext($context);

				if(count(app()->updatedFields))
					$add[FIELD_UPDATE] = app()->updatedFields;

				echo app()->jsonMessage(RESULT_OK, "ok", $add);
			}
			else
				echo app()->jsonMessage(RESULT_ERROR, ERROR_NO_PATH);
		}

		function addChild()
		{
		 	app()->requireLoginJson();
			if($path = app()->request(REQUEST_PATH))
			{
				list($pref, $path2) = app()->explodePath($path);
				$context = app()->getContext($pref);
				list($obj, $key) = $context->addChild($path2);
				app()->putContext($context);

				$add = array("obj" => $obj->get_data_for_json(), "key" => $key, "path" => $path);
				if(count(app()->updatedFields))
					$add[FIELD_UPDATE] = app()->updatedFields;

				echo app()->jsonMessage(RESULT_OK, "ok", $add);
			}
			else
				echo app()->jsonMessage(RESULT_ERROR, ERROR_NO_PATH);
		}

		function saveObject()
		{
		 	app()->requireLoginJson();

			if($path = app()->request(REQUEST_PATH))
			{
				$context = app()->getContext($path);
				$context->save();
				app()->putContext($context);
				app()->addWarning(new Warning(t("document saved")));
				echo app()->jsonMessage(RESULT_OK);
			}
		}

		function copyObject()
		{
		 	app()->requireLoginJson();

			if($path = app()->request(REQUEST_PATH))
			{
				$context = app()->getContext($path);
				$copyID = $context->copyObject();

				echo app()->jsonMessage(RESULT_OK, "copied ok", array("copyID" => $copyID));
			}
		}

		function comboData()
		{
			$combot = app()->request("combot");
		 	app()->requirePrivilegeJson(PRIVILEGE_SELECT, $combot);		//rights

			$idfield = app()->request("idfield");
			$fieldsstring = app()->request("rfield");
			$fields = explode(",", $fieldsstring);

			$rfield = $fields[0];

			$qfields = array();
			foreach ( $fields as $field)
				$qfields[] = "o." . $field;
			$qf = implode(", ", $qfields);


		 	$additionalSql = "";
		 	if(is_object($o = app()->dbo($combot)))
		 		$additionalSql = $o->keySelAdditionalSql(app()->request("af"));

			$sql = "select o.$idfield, $qf from $combot o $additionalSql ";	//TODO security

			//echo $sql . "<br/>";

			$c = app()->getDBConnection();

			//request variables
			$page = (int)(app()->request("page", 1));
			$rp = (int)(app()->request("rows"));
			$sortname = app()->request("sidx");
			$sortorder = app()->request("sord");
			$query = app()->request("searchTerm");
			$qtype = $rfield;

			$rows = array();
			$row = array();

			$sqlLimit = "";
			if($page || $rp)
				$sqlLimit = " limit " . (($page - 1) * $rp) . ", " . $rp;

			$sqlQuery = "";

			if($query)
			{
				$q = array();
				foreach ( $fields as $field )
					$q[] = $field . " like " . quote("%" . $query . "%");
				$sqlQuery = " (" . implode(" or ", $q) . ") ";
			}

			$sqlSort = "";
			if($sortname)
			{
				$sqlSort = " order by $sortname";
				if($sortorder)
					$sqlSort .= " " . $sortorder;
			}

			$sqlQuery = (stripos($sql, " where ") !== FALSE ? " and " : " where ") . $sqlQuery;

			$gsql = $sql . $sqlQuery . $sqlSort . $sqlLimit;


			//echo $gsql . "<br/>";

			$obj = new EmptyObject();

			//getting count;
			$a = explode("from", $sql);
			$csql = "select count(*) from " . $a[1] . $sqlQuery;
			$data =& $c->getRow($csql, array(), DB_FETCHMODE_ORDERED);
			$obj->records = (int)$data[0];
			$obj->page = $page;
			$obj->total = (int)($obj->records / $rp);
			if ($obj->total * $rp < $obj->records)
				 $obj->total +=1;

			$q =& $c->query($gsql);

			while($q->fetchInto($row))
			{
				$o1 = new EmptyObject();
				$o1->$idfield = $row[0];
				$nextindex = 1;
				foreach($fields as $nextfield){
					$o1->$nextfield = $row[$nextindex];
					$nextindex = $nextindex + 1;
				}

				$rows[] = $o1;
			}

			$obj->rows = $rows;

			echo json_encode($obj);
		}

		function ajaxTableValue()
		{
		 	app()->requirePrivilegeJson(PRIVILEGE_SELECT, app()->request("ctable"));		//rights

			echo app()->jsonMessage(RESULT_OK, tableValue(
				app()->request("cvalue"),
				app()->request("ctable"),
				app()->request("ctablekey"),
				app()->request("ctablefield")));
		}

		function ajaxTableRow()
		{
		 	app()->requirePrivilegeJson(PRIVILEGE_SELECT, app()->request("ctable"));		//rights

			$a = tableRow(
				app()->request("cvalue"),
				app()->request("ctable"),
				app()->request("ctablekey"));

			echo app()->jsonMessage(RESULT_OK, "ok", array("row" => $a->get_data_for_json()));
		 }

		 function rollback()
		 {
		 	echo "<pre><small>";
		 	app()->requireLogin();
			$l = app()->dbo("objlog");
			$l->setIdValue(app()->request("rev"));

			$l->find(true);

			$o = unserialize($l->val);

			print_r($o);

		 	app()->requirePrivilege(PRIVILEGE_UPDATE, $o->__table);

			if(is_object($d = app()->getRegistryDescriptor($o->__table)))
			{
				$o1 = app()->dbo($o->__table);
				$idf = $o1->getPrimaryKeyField();
				$o1->$idf = $o->$idf;
				if(!$o1->find(true));
					$o->stripPrimaryKeys($d->getChildrenTree());
				$o->persist();
				$o->saveChildren($d->getChildrenTree());

				//print_r($o);

				echo "<a href=\"?action=open&registry=" . $o->__table . "&id=" . $o->$idf . "\">open</a>";

				//app()->location();
			}
		}
	}