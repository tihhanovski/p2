<?php
/**
 * Application
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

    define ("RD_TYPE_REGISTRY", 1);
    define ("RD_TYPE_REPORT", 2);
    define ("RD_TYPE_SETUP", 3);

    define("EXCEPTION_NO_CONTEXT", "no such context");

    class RegistryDescriptor
    {
        public $gridSql;
        public $registry;

        protected $indexMode = false;
        protected $logMode = false;
        protected $fullTextFind = false;
        //public $currentContext;

        public $secondSortName = "";
        public $secondSortOrder = "";

        public function needAskBeforeLeavePage()
        {
            return defined("ASKBEFORELEAVE") && ASKBEFORELEAVE;
        }

        public function handleRequest($action)
        {
            if(method_exists($this, $action))
            {
                $this->$action();
                return true;
            }
            return false;
        }

        /**
         * returns JSON data for keysel3 input control
         * uses request parameter id
         * TODO experimental!
         */
        public function keysel3value()
        {
          app()->requirePrivilegeJson(PRIVILEGE_SELECT);
          $id = (int)app()->request("id", "0");
          if($id)
          {
            $f = app()->get(app()->request(REQUEST_REGISTRY), $id);
            echo app()->jsonMessage(RESULT_OK, RESULT_OK, array("data" => $f->getKeysel3Data()));
            return;
          }
          echo app()->jsonMessage(RESULT_ERROR, t("Something went wrong"));
        }

        /**
         * returns JSON data for keysel3 input control
         * uses request parameter term
         * TODO experimental!
         */
        public function keysel3data()
        {
          $ret = array();
          app()->requirePrivilegeJson(PRIVILEGE_SELECT);
          $q = app()->request("term");
        	if(strlen($q) > 2)
          {
            $f = app()->dbo(app()->request(REQUEST_REGISTRY));
            $f->setupKeysel3SearchTerm($q);

          	if($f->find())
          		while($f->fetch())
          			$ret[] = $f->getKeysel3Data();
          }

        	echo json_encode($ret);
        }

        /**
         * adds comment
         */
        public function comment()
        {
            app()->requirePrivilegeJson(PRIVILEGE_SELECT);    //TODO update privilege?
            if(is_object($context = app()->getContext($this->getContextName())))
                if(is_object($obj = $context->obj))
                    if($obj->comment(app()->request("comment")))
                    {
                        echo app()->jsonMessage();
                        return;
                    }
            echo app()->jsonMessage(RESULT_ERROR, t("Cant comment"));
        }

        public function getComments()
        {
            app()->requirePrivilegeJson(PRIVILEGE_SELECT);    //TODO update privilege?
            if(is_object($context = app()->getContext($this->getContextName())))
                if(is_object($obj = $context->obj))
                {
                    echo app()->jsonMessage(RESULT_OK, "comments", array("items" => $obj->getComments()));
                    return;
                }
            echo app()->jsonMessage(RESULT_ERROR, t("Cant retriev comments"));
        }

        /**
         * uploads file
         */
        public function uploadRobjfile()
        {
            app()->requirePrivilegeJson(PRIVILEGE_UPDATE);
            if(is_object($context = app()->getContext($this->getContextName())))
            {
                if(is_object($obj = $context->obj))
                {
                    $utmp = $_FILES["fc"]["tmp_name"];
                    $fileName = $_FILES["fc"]["name"];
                    $tmp = app()->tempFile($fileName);
                    move_uploaded_file($utmp, $tmp);
                    $obj->appendObjFile($tmp, $fileName);
                    $obj->getObjFiles();
                    app()->putContext($context);
                    header("Location: " . $_SERVER["HTTP_REFERER"]);
                }
            }
        }

        public function outputIndexForm()
        {
                if(is_object($this->getGrid()))
                {
                    $this->indexMode = true;
                    include app()->getAbsoluteFile("ui/index.grid.php");
                    return;
                }
        }

        function index()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);
            app()->addStat("index", app()->getCurrentRegistry());

            $this->outputIndexForm();
        }

        function getLinkedEmailsAsHtml()
        {
            $html = "";
            app()->requirePrivilege(PRIVILEGE_SELECT);
            if(is_object($context = app()->getContext($this->getContextName())))
                if(is_object($obj = $context->obj))
                    foreach ($obj->getLinkedEmails() as $eml)
                        $html .= "<div class=\"linkedEmailItem\">" .
                            "<div class=\"linkedEmailCaption\">" . app()->getLinkedCaption($eml, array("recipient", "bcc", "mdUpdated"), "; ") . "<div>" .
                            "</div class=\"linkedEmailResult\">" . $eml->getHumanEncodedResult() .  "</div>" .
                            "</div>";
            echo $html;
        }

        function getTopToolbar()
        {
            if($this->indexMode)
                return $this->getIndexTopToolbar();

            if($this->logMode)
                return $this->getLogTopToolbar();

            return $this->getEditorTopToolbar();
        }

        function hasAddNewButton()
        {
            return app()->canUpdate();
        }

        function hasMoreMenu()
        {
            return method_exists($this, "moreMenuItemsHtml");
        }

        function getIndexTopToolbar()
        {
            $buttons = array();

            if($this->hasAddNewButton())
                $buttons["New"] = "newDocument('". app()->request(REQUEST_REGISTRY) ."')";

            if($this->hasMoreMenu())
                $buttons["More"] = "grid.showMore()";

            return toolbar($buttons) . $this->getTopFilterToolbarItem();
        }

        function getTopFilterToolbarItem()
        {
            return "<div onclick=\"grid.toggleFilter();\" class=\"gridFilterCaption gridFilterCaptionTop pointerCursor\"></div>";
        }

        function getLogTopToolbar()
        {
            $buttons = array(
                "Back" => "reopenDocument()",
            );

            return toolbar($buttons);
        }

        function getEditorTopToolbar()
        {
            $buttons = array(
                "Docs list" => "openDocumentList()",
            );

            if(is_object($obj = $this->getContextNoLoad()->obj))
            {
                if(app()->canUpdate())
                {
                    if($obj->isEditable())
                        $buttons["Save"] = "app.saveDocument()";
                    if($obj->canCopy())
                        $buttons["Copy"] = "app.copyDocument()";
                }

                if($obj->isDeletable())
                {
                    if(app()->canDelete())
                        $buttons["Delete"] = "deleteDocument()";
                }
                 if(app()->canUpdate())
                    $buttons["Undo"] = "reopenDocument()";
            }


            $buttons["Log"] = "showLog()";

            return toolbar($buttons);
        }

        function getReportContext($regDescriptor = null)
        {
            if($regDescriptor == null)
                $regDescriptor = $this;
            $context = app()->getContext(app()->getRegistryDescriptor()->registry);
            if(!is_object($context))
            {
                $context = $regDescriptor->createContext();
                $context->load();
                app()->putContext($context);
            }
            return $context;
        }

        function previewReport()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);

            if($this->getType() == RD_TYPE_REPORT)
            {
                $context = $this->getReportContext($this);

                if(file_exists($fn = $this->getPrintFormPath(isset($context->obj->form) ? $context->obj->form : "")))
                {
                    include $fn;
                    return;
                }

                if(is_array($frms = $this->getAvailableForms()))
                    foreach ( $frms as $k => $v )
                        if(file_exists($fn = $this->getPrintFormPath($k)))
                        {
                            include $fn;
                            return;
                        }



                echo "<a href=\"JavaScript:history.go(-1);\">" . t("print form not found") . "</a>";
            }
        }

        /**
         * Cancel filter
         */
        public function cancelFilter()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);
            $fo = $this->getFilter();
            $fo->cancelFilter();
            echo app()->jsonMessage();
        }

        /**
         * Applies filter
         */
        public function applyFilter()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);
            $fo = $this->getFilter();
            $fo->applyFilter();
            echo app()->jsonMessage();
        }

        /**
         * Empties filter and sets all data to defaults
         */
        public function emptyFilter()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);
            $fo = $this->getFilter();
            $fo->emptyFilter();
            echo app()->jsonMessage();
        }

        public function filterUi()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);

            if($fn = $this->getFilterFormPath())
            {
                $fo = $this->getFilter();
                include($fn);
            }
            else
            {
                $fields = $this->getFilterFields();
                if($fn = app()->getAbsoluteFile("ui/filter.php"));
                include $fn;
            }
        }

        public function edit()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);

            if(!is_object($context = app()->getContext($this->getContextName())))
                app()->location("?" . REQUEST_REGISTRY . "=" . app()->request(REQUEST_REGISTRY));

            $this->indexMode = false;
            include app()->getAbsoluteFile("ui/pagestart.php");

            if($fn = $this->getDetailFormPath())
            {
                include app()->getAbsoluteFile("ui/edit.start.php");
                include $fn;
                include app()->getAbsoluteFile("ui/edit.finish.php");
            }
            else
            {
                if(method_exists($this, "getSimpleformComponents"))
                {
                    include app()->getAbsoluteFile("ui/edit.start.php");
                    echo simpleform($this->getSimpleformComponents($obj));
                    include app()->getAbsoluteFile("ui/edit.finish.php");
                }
                else
                    include app()->getAbsoluteFile("ui/detailNotFound.php");
            }

            include app()->getAbsoluteFile("ui/pagefinish.php");
        }

        function closeDocument()
        {
            if(!is_object($context = $this->getContext()))
                throw new NoContextException("context not found");
            if(!is_object($obj = $context->obj))
                throw new NoContextException("context has no document");
            if(!$obj->canCloseDocument())
                throw new CloseDocumentException("cant close document");
            $obj->closeDocument();
            $context->save();
            app()->putContext($context);
            app()->requireReloadContext();
            echo app()->jsonMessage();
        }

        function reopenDocument()
        {
            if(!is_object($context = $this->getContext()))
                throw new NoContextException("context not found");
            if(!is_object($obj = $context->obj))
                throw new NoContextException("context has no document");
            if(!$obj->canOpenDocument())
                throw new CloseDocumentException("cant reopen document");
            $obj->reopenDocument();
            $context->save();
            app()->putContext($context);
            app()->requireReloadContext();
            echo app()->jsonMessage();
        }

        function printDocument()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);

            if(!is_object($context = app()->getContext($this->getContextName())))
            {
                $context = $this->createContext();
                if($context->load())
                    app()->putContext($context);
            }

            if(file_exists($fn = $this->getPrintFormPath(app()->request("form"))))
                include $fn;
            else
                die("<a href=\"JavaScript:history.go(-1);\">" . t("print form not found") . "</a>");
        }

        public function canCloseDocument()
        {
            app()->requireLoginJson();
            $context = $this->getContext();
            if(is_object($obj = $context->obj))
                echo app()->jsonMessage(RESULT_OK, "1"); //$obj->hasUnsavedChanges() ? "1" : "0");
        }

        public function saveDocument()
        {
            app()->startTimer();
            app()->requireLoginJson();
            $context = $this->getContext();
            if(app()->request("fieldsData") == "1")
            {
                foreach ($_REQUEST as $k => $v)
                {
                    list($path, $path2) = app()->explodePath($k);
                    if($path == $context->namePrefix)
                        $context->setValueByPath($path2, $v);
                }
            }
            if($this->saveContext())
            {
                app()->addWarning(new Warning("Document saved"));
                $context = $this->getContext();
                echo app()->jsonMessage(RESULT_OK, $context->obj->getIdValue());
            }
            else
                echo app()->jsonMessage(RESULT_ERROR, t("Document not saved"));
            app()->endTimerAndLogDbo("document saved", $context->obj);
        }

        function setupGridStuff($g)
        {
            $this->setupSortStuff($g);
            $g->filterWidth = $this->getFilterWidth();
            $g->hasFilteredFields = $this->hasFilteredFields();
        }

        function setupSortStuff($g)
        {
            $m = $g->modifier ?  "." . $g->modifier : "";
            $g->sortname =  app()->getUserProperty($this->registry . $m . ".sortname", $g->sortname);
            $g->sortorder =  app()->getUserProperty($this->registry . $m . ".sortorder", $g->sortorder);
        }

        function hasFilteredFields()
        {
            $a = $this->getFilterFields();
            return is_array($a) && (count($a) > 0);
        }

        function getFilterWidth()
        {
            return 800;
        }

        function getSqlQueryPart()
        {
            if($this->query && $this->qtype)
            {
                $findStr = "";
                if($this->fullTextFind)
                {
                    $a = array("' '");
                    foreach ($this->grid->colModel as $col)
                        if($col->isPrintable() && $this->columnVisibilityOn($col))
                        {
                            $a[] = "lower(coalesce(" . $col->findSql . ", ''))";
                            $a[] = "' '";
                        }
                    if(count($a) > 1)
                        $findStr = "concat(" . implode(", ", $a) . ")";
                }
                else
                    if($this->qtype)
                        $findStr = $this->qtype;

                $leftPct = "%";
                $rightPct = "%";
                $q = $this->query;
                if(substr($q, 0, 1) == "\"")
                {
                    $leftPct = "";
                    $q = substr($q, 1);
                }
                if(substr($q, -1) == "\"")
                {
                    $rightPct = "";
                    $q = substr($q, 0, -1);
                }

                if($findStr)
                    return ((strpos($this->sql, "where")) ? " and " : " where ") .
                        $findStr .
                        " like lower(" .
                        quote($leftPct . $q . $rightPct) .
                        ")";
            }


            return "";
        }

        /**
         * sets grid variables, such as pagination, sorting etc
         */
        public function initGridVariables()
        {
            $gridMethod = "getGrid";
            $gridSqlMethod = "getGridSql";
            $gridGroupByMethod = "getGridGroupBy";

            $this->mod = app()->request("mod");

            if($modifier = app()->request("mod"))
            {
                if(method_exists($this, $m = $gridMethod . "_" . $modifier))
                    $gridMethod = $m;
                if(method_exists($this, $m = $gridSqlMethod . "_" . $modifier))
                    $gridSqlMethod = $m;
                ;
                if(method_exists($this, $m = $gridGroupByMethod . "_" . $modifier))
                    $gridGroupByMethod = $m;
                $modifier = "." . $modifier;
            }
            //$ga = explode("group by", );
            $this->sql = $this->$gridSqlMethod(); //$ga[0];
            $this->sqlGroupBy = $this->$gridGroupByMethod(); //isset($ga[1]) ? $ga[1] : "";

            $this->grid = $this->$gridMethod();


            //request variables
            $this->page = (int)(app()->request("page", 1));
            $this->rp = (int)(app()->request("rp", 50));
            $this->sortname = app()->request("sortname", app()->getUserProperty($this->registry . $modifier . ".sortname", $this->grid->sortname));
            $this->sortorder = app()->request("sortorder", app()->getUserProperty($this->registry . $modifier . ".sortorder", $this->grid->sortorder));
            $this->query = app()->request("query");
            $this->qtype = $this->grid->getSortSqlColumn(app()->request("qtype"));
            if(!$modifier)
                $this->filterOnStartup = (isset($this->getFilter()->filterOnStartup) ? $this->getFilter()->filterOnStartup : false);

            $this->sqlLimit = "";
            if($this->page || $this->rp)
                $this->sqlLimit = " limit " . (($this->page - 1) * $this->rp) . ", " . $this->rp;

            $this->sqlQuery = $this->getSqlQueryPart();

            $this->sqlRrQuery = "";
            if($reloadRow = (int)app()->request("reloadRow"))
            {
                //find first col sql
                $s = $this->plainSql($this->sql);
                $a = explode(",", $s);
                $idc = $a[0];
                $a = explode(" ", $idc);
                $idc = $a[1];
                $this->sqlRrQuery = ((strpos($this->sql . $this->sqlQuery, "where")) ? " and " : " where ") .
                    " " . $idc . " = " . $reloadRow;
            }

            $this->initSort();
        }

        function plainSql($s)
        {
            $s = str_replace("\n", " ", $s);
            $s = str_replace("\t", " ", $s);
            $a = array();
            foreach(explode(" ", $s) as $i)
                if($i <> "")
                    $a[] = $i;
            return implode(" ", $a);
        }

        /**
         * Initializes sorting for grid query.
         * Called from initGridVariables, sets $sqlSort.
         * One can override it to change default sort behaviour
         * @return void
         */
        protected function initSort()
        {
            $modifier = app()->request("mod");
            $this->sqlSort = "";
            if($this->sortname)
            {
                app()->setUserProperty($this->registry . $modifier . ".sortname", $this->sortname);
                app()->setUserProperty($this->registry . $modifier . ".sortorder", $this->sortorder);

                $this->sqlSort = " order by {$this->sortname}";
                if($this->sortorder)
                    $this->sqlSort .= " " . $this->sortorder;

                if(isset($this->secondSortName) && $this->secondSortName)
                    $this->sqlSort .= ", " . $this->secondSortName .
                    ($this->secondSortOrder ? " " . $this->secondSortOrder : ($this->sortorder ? " " . $this->sortorder : ""));
            }
        }

        function replaceQueryVariables($sql)
        {
            $sql = str_replace("[user.id]", app()->user()->id, $sql);        //TODO more vars
            return $sql;

        }

        function getGridDataSql($limited = true)
        {
            $sql = $this->sql .
                        $this->sqlQuery .
                        $this->sqlRrQuery .
                        ($this->sqlGroupBy ? " group by " . $this->sqlGroupBy : "") .
                        $this->sqlSort .
                        (($limited && ! $this->sqlRrQuery) ? $this->sqlLimit : "");
            return $sql;
        }

        function exportGridAsJSON()
        {
            app()->requirePrivilegeJson(PRIVILEGE_SELECT);
            $c = app()->getDBConnection();
            $this->initGridVariables();
            $gsql = $this->getGridDataSql(false);

            header("Content-Disposition: attachment; filename=\"" . t("ro_" . app()->request("registry")) . ".json\"");
            echo json_encode(app()->queryAsArray($gsql));
        }

        function exportGridAsPDF()
        {
            app()->requirePrivilegeJson(PRIVILEGE_SELECT);
            $c = app()->getDBConnection();
            $this->initGridVariables();

            app()->initReporting();

            $model = new ReportModel();
            $model->landscape();

            foreach ( $this->getGrid()->colModel as $col)
                if($col->isPrintable() && $this->columnVisibilityOn($col))
                    $model->addColumn(new PdfReportColumn(
                        $col->name,
                        $col->display,
                        $col->width,
                        $col->align,
                        $col->getFormat()
                        ));

            $model->fillBySql($this->getGridDataSql(false));
            $model->output();
        }

        private $_gridSetup;
        private function columnVisibilityOn($col)
        {
            if(!isset($this->_gridSetup))
                $this->_gridSetup = $this->getGridSettings();
            $cvisible = true;
            $cn = $col->name;
            if(isset($this->_gridSetup->$cn))
                $cvisible = $this->_gridSetup->$cn;
            return $cvisible;
        }

        function exportGridAsXLS()
        {
            app()->requirePrivilegeJson(PRIVILEGE_SELECT);
            $c = app()->getDBConnection();

            $w = new XLSExporter();
            $fn = t("ro_" . app()->request("registry"));
            $w->sheetName = substr($fn, 0, MAX_XLS_WORKSHEET_NAME_LENGTH);
            $w->fileName = str_replace(" ", "_", $fn) . "_" . date("Y_m_d_H_i_s") . ".xlsx";

            $this->initGridVariables();
            $gsql = $this->getGridDataSql(false);

            $q =& $c->query($gsql);

            if(app()->isDBError($q))
                die($gsql . "\n\n\n" . print_r($q, true));

            $headerNotWritten = true;
            $y = 1;
            $row = array();
            while($q->fetchInto($row))
            {
                if($headerNotWritten)
                {
                    $x = 0;
                    reset($row);
                    list($k, $v) = each($row);    //ID
                    while(list($k, $v) = each($row))
                    {
                        if(is_object($col = $this->grid->colModel[$k - 1]))
                        {
                            $cvisible = true;
                            $cn = $col->name;
                            if(isset($setup->$cn))
                                $cvisible = $setup->$cn;
                            if($col->isPrintable() && $this->columnVisibilityOn($col))
                            {
                                $x++;
                                $w->write($w->getCoord($x, $y), $col->display);
                            }
                        }
                    }

                    $headerNotWritten = false;
                }

                $y++;
                $x = 0;

                reset($row);
                list($k, $v) = each($row);    //ID
                while(list($k, $v) = each($row))
                    if(is_object($col = $this->grid->colModel[$k - 1]))
                    {
                        if($col->isPrintable() && $this->columnVisibilityOn($col))
                        {
                            $value = $v;
                            $f = $col->getFormat();
                            if($f == FORMAT_TRANSLATED || $f == FORMAT_DATE)
                                $value = $col->format($v);

                            $x++;
                            $w->write($w->getCoord($x, $y), $value);    //TODO UTF8 data?!
                        }
                    }
            }
            $w->autosize();
            $w->download();
        }

        /**
         * @return array of filtered fields captions for UI
         */
        protected function getFilterCaption()
        {
            $ret = array();
            if (is_array($ff = $this->getFilterFields()))
                foreach ($ff as $c)
                    if(is_object($c))
                    {
                        $mr = $c->getMinimalRepresentation();
                        if($c->field == "active")
                        {
                            if($mr == "")
                                return array();
                        }
                        else
                            if($c->field != "filterOnStartup")
                                if(is_array($mr))
                                {
                                    foreach ($mr as $a)
                                        $ret[] = $a;
                                }
                                else
                                    $ret[] = $mr;
                    }
            return $ret;
        }

        protected function getQueryStatsFields()
        {
            return "count(*) as cnt";
        }

        protected function getQueryStatsFormatters()
        {
            return null;
        }

        public function getQueryStats($c, $obj)
        {
            //getting count;
            $a = explode("from", $this->sql);
            unset($a[0]);
            $csql = "select " . $this->getQueryStatsFields() . " from " . implode("from", $a) . $this->sqlQuery;
            $data =& $c->getRow($csql, array(), DB_FETCHMODE_ASSOC);
            if(!is_array($data))
                if(is_object($data) && app()->isDBError($data))
                    die(app()->jsonMessage(RESULT_ERROR, $data->userinfo));


            $obj->total = (int)$data["cnt"];
            $obj->stats = $data;
            if(is_array($sf = $this->getQueryStatsFormatters()))
            {
                foreach ($obj->stats as $k => $v)
                    if(isset($sf[$k]))
                        $obj->stats[$k] = getFormatter($sf[$k])->encodeHuman($v);
            }

            //die("mod = " . $this->mod . "<br/>" . $csql . "<hr/>" . print_r($obj->stats, 1));
        }

        /**
         * Construct and write out grid data in JSON format
         */
        public function gridData()
        {
            app()->requirePrivilegeJson(PRIVILEGE_SELECT);

            $c = app()->getDBConnection();


            $this->initGridVariables();


            $rows = array();
            $row = array();

            $gsql = $this->getGridDataSql();

            $obj = new EmptyObject();
            $obj->page = (int)(app()->request("page", 1));
            $obj->filterOnStartup = isset($this->filterOnStartup) && $this->filterOnStartup;

            //getting count and stats;
            $this->getQueryStats($c, $obj);

            $q =& $c->query($gsql);

            if(app()->isDBError($q))
                die($gsql . "\n\n\n" . print_r($q, true));

            while($q->fetchInto($row))
            {
                $o1 = new EmptyObject();
                $o1->cell = array();

                reset($row);
                list($k, $v) = each($row);
                $o1->id = $v;
                while(list($k, $v) = each($row))
                {
                    if(isset($this->grid->colModel[$k - 1]) && is_object($col = $this->grid->colModel[$k - 1]))
                        $o1->cell[] = sanitize($col->format($v));
                    else
                        $o1->cell[] = sanitize($v);
                }


                $rows[] = $o1;
            }
            $obj->rows = $rows;

            $obj->filterCaption = $this->getFilterCaption();

            echo json_encode($obj);
        }

        function openrev()
        {
            app()->requireLogin();

            $context = $this->createContext();

            if($this->loadContext($context))
            {
                if($rev = app()->request("rev"))
                {
                    $log = app()->get("objlog", $rev);
                    $obj = unserialize($log->val);
                    $context->obj = $obj;
                    $msg = sprintf(t("Loaded revision %s"), $rev);
                }
                app()->putContext($context);

                $url =    "?" . REQUEST_REGISTRY . "=" . app()->request(REQUEST_REGISTRY) .
                        "&" . REQUEST_ACTION . "=edit" .
                        "&id=" . app()->request("id") .
                        "&" . REQUEST_STARTUPMSG . "=" . urlencode($msg);
                app()->location($url);
            }
            else
                app()->showError("Requested document does not exist");
        }

        protected function getEditDocumentUrl($msg = "")
        {
            return "?" . REQUEST_REGISTRY . "=" . app()->request(REQUEST_REGISTRY) .
                        "&" . REQUEST_ACTION . "=edit" .
                        "&id=" . app()->request("id") .
                        ($msg ? "&" . REQUEST_STARTUPMSG . "=" . urlencode($msg) : "");
        }

        /**
         * Loads document data from database and opens document for editing
         */
        public function open()
        {
            app()->requireLogin();
            $context = $this->createContext();
            if($context->load())
            {
                app()->putContext($context);
                app()->location($this->getEditDocumentUrl(app()->request(REQUEST_STARTUPMSG)));
            }
            else
                app()->showError("Requested document does not exist");
        }

        function showlog()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);
            $this->logMode = true;

            if(is_object($obj = app()->get(app()->request(REQUEST_REGISTRY), app()->request("id"))))
            {
                include app()->getAbsoluteFile("ui/pagestart.php");
                include app()->getAbsoluteFile("ui/edit.start.php");
                include app()->getAbsoluteFile("ui/logviewer.php");
                include app()->getAbsoluteFile("ui/edit.finish.php");
                include app()->getAbsoluteFile("ui/pagefinish.php");
            }

            die();


            if($rev = app()->request("rev"))
            {
                $log = app()->get("objlog", $rev);
                $context = new LogContext($log);

                include app()->getAbsoluteFile("ui/pagestart.php");

                if($fn = $this->getDetailFormPath())
                {
                    include app()->getAbsoluteFile("ui/edit.start.php");
                    include $fn;
                    include app()->getAbsoluteFile("ui/edit.finish.php");
                }
                else
                    include app()->getAbsoluteFile("ui/detailNotFound.php");
                include app()->getAbsoluteFile("ui/pagefinish.php");
            }
        }

        function showrev()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);
            $this->logMode = true;

            if($rev = app()->request("rev"))
            {
                $log = app()->get("objlog", $rev);
                $context = new LogContext($log);
                $obj = $context->obj;

                include app()->getAbsoluteFile("ui/pagestart.php");

                if($fn = $this->getDetailFormPath())
                {
                    //include app()->getAbsoluteFile("ui/edit.start.php");
                    include $fn;
                    //include app()->getAbsoluteFile("ui/edit.finish.php");
                }
                else
                    include app()->getAbsoluteFile("ui/detailNotFound.php");
                include app()->getAbsoluteFile("ui/pagefinish.php");
            }
        }

        function __construct($reg)
        {
            $this->registry = $reg;
        }

        function getAvailableForms()
        {
            return null;
        }

        function getType()
        {
            return RD_TYPE_REGISTRY;
        }

        function getContextName()
        {
            return $this->registry . app()->request("id");
        }

        function saveContext()
        {
            $context = $this->getContext();
            $ret = $context->save();
            app()->putContext($context);
            return $ret;
        }

        private $_fo;
        function getFilter()
        {
            if(!is_object($this->_fo))
            {
                $this->_fo = new FilterObject($this->registry);
                $this->setupFilterFormats($this->_fo);
                $this->_fo->get();
            }
            return $this->_fo;
        }

        function getGridSettings()
        {
            $o = new GridSetupObject($this->registry);
            $o->get();
            return $o;
        }

        function getFilterFields()
        {
            return array();
        }

        function setupFilterFormats($fo)
        {
        }

        function addWhere($sql, $clause)
        {
            $ga = explode("group by", $sql);
            $s1 = $ga[0];

            if(stripos($s1, "where") === false)
                $s1 = $s1 . " where " . $clause;
            else
                $s1 = $s1 . " and " . $clause;

            if(count($ga) > 1)
            {
                $ga[0] = $s1;
                return implode(" group by ", $ga);
            }
            else
                return $s1;
        }

        function appendFilter($sql, $filter)
        {
            return $sql;
        }

        function gridSelectAll()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);
            $this->initGridVariables();
            $gsql = $this->getGridDataSql(false);
            $q = app()->query($gsql);
            if(app()->isDBError($q))
                die($gsql . "\n\n\n" . print_r($q, true));    //TODO

            $userId = (int)app()->user()->id;
            $reg = app()->request("registry");

            $cnt = 0;
            while($q->fetchInto($row))
            {
                $cnt++;
                $id = (int)$row[0];
                $this->selectRow($id, $userId, $reg, true);
            }
            echo app()->jsonMessage(RESULT_OK, "1", array("selected" => $cnt));
        }

        function gridUnselectAll()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);

            $userId = (int)app()->user()->id;
            $reg = quote(app()->request("registry"));

            app()->query("delete from selected where userId = $userId and objreg = $reg");

            echo app()->jsonMessage(RESULT_OK, "0");
        }

        protected function getGlobalSelection()
        {
            $userId = (int)app()->user()->id;
            $reg = quote(app()->request("registry"));
            $q = app()->query("select objId from selected where userId = $userId and objreg = $reg");
            if(app()->isDBError($q))
                die($gsql . "\n\n\n" . print_r($q, true));    //TODO
            $a = array();
            while($q->fetchInto($row))
                $a[$row[0]] = $row[0];
            return $a;
        }

        public function gridInvertSelection()
        {
            app()->requirePrivilege(PRIVILEGE_SELECT);

            $this->initGridVariables();
            $gsql = $this->getGridDataSql(false);
            $q = app()->query($gsql);
            if(app()->isDBError($q))
                die($gsql . "\n\n\n" . print_r($q, true));    //TODO

            $a = $this->getGlobalSelection();

            $userId = (int)app()->user()->id;
            $reg = app()->request("registry");

            while($q->fetchInto($row))
            {
                $id = (int)$row[0];
                $this->selectRow($id, $userId, $reg, !isset($a[$id]));
            }
            echo app()->jsonMessage(RESULT_OK, "1");

        }

        private function selectRow($id, $userId, $reg, $m)
        {
                $reg = quote($reg);
                if($m)
                    $sql = "insert into selected(userId, objreg, objid)values($userId, $reg, $id)";
                else
                    $sql = "delete from selected where userId = $userId and objreg = $reg and objid = $id";
            app()->query($sql);
        }

        private function selectionMethod($m)
        {
            app()->requirePrivilegeJson(PRIVILEGE_SELECT);
            $id = (int)app()->request("id");
            $userId = (int)app()->user()->id;
            $reg = app()->request("registry");
            if($id && $reg)
            {
                $this->selectRow($id, $userId, $reg, $m);
                echo app()->jsonMessage(RESULT_OK, $m ? "1" : "0");
            }
        }

        function setSelected()
        {
            $this->selectionMethod(true);
        }

        function unsetSelected()
        {
            $this->selectionMethod(false);
        }

        function getGridSql()
        {
            $sql = $this->gridSql;
            $filter = $this->getFilter();
            if($filter->isActive())
                $sql = $this->appendFilter($sql, $filter);

            $sql = $this->replaceQueryVariables($sql);
            return $sql;
        }

        function getGridGroupBy()
        {
            return "";
        }

        function getReportInputPath()
        {
            if($s = app()->getAbsoluteFile("registries/" . $this->registry . "/" . $this->registry . ".input.php"))
                return $s;
            return app()->getAbsoluteFile("registries/" . $this->registry . ".input.php");
        }

        function getSetupInputPath()
        {
            if($s = app()->getAbsoluteFile("registries/" . $this->registry . "/" . $this->registry . ".input.php"))
                return $s;
            return app()->getAbsoluteFile("registries/" . $this->registry . ".input.php");
        }

        function isEditable()
        {
            if(!app()->canUpdate())
                return false;
            if(is_object($obj = app()->getContext($this->getContextName())->obj))
            {
                if($obj->isLockable() && $obj->isLocked())
                    return false;

                if($obj->isClosable())
                    if($obj->canOpenDocument())
                        return false;
            }
            return true;
        }

        function getDetailFormPath()
        {
            if(!$this->isEditable())
                if($p = app()->getFormFile($this->registry, ".view.php"))    //getAbsoluteFile("registries/" . $this->registry . ".view.php"))
                    return $p;
            if($p = app()->getFormFile($this->registry, ".df.php"))        //app()->getAbsoluteFile("registries/" . $this->registry . ".df.php");
                return $p;

            return "";
        }

        function getFilterFormPath()
        {
            if($fn = app()->getAbsoluteFile("registries/" . $this->registry . "/" . $this->registry . ".filter.php"))
                return $fn;
            return app()->getAbsoluteFile("registries/" . $this->registry . ".filter.php");
        }

        /**
         * Finds absolute printform file name.
         * @param string $form - form name
         * @return string
         * If no form file found, empty string returned
         */
        public function getPrintFormPath($form = "")
        {
            $fs = $this->registry . ".print";
            if($form)
                $fs .= "-" . stripDirBack($form);
            $fs .= ".php";

            if($fn = app()->getAbsoluteFile("registries/" . $this->registry . "/" . $fs))
                return $fn;
            if($fn = app()->getAbsoluteFile("registries/" . $fs))
                return $fn;

            if($form)
                return $this->getPrintFormPath();
        }

        /**
         * Returns data object dependencies for current registry descriptor.
         * For example return array("webuser" => array("userroles" => "userrole"));
         * means that DBO_Userrole objects linked with DBO_Webuser will be loaded into array userroles of webuser
         * @return array<array<string>>
         */
        public function getChildrenTree()
        {
            return null;
        }

        function loadContext(&$context)
        {
            return $context->load();
        }

        public function getExistingContext()
        {
            if(is_object($ret = $this->getContextNoLoad()))
                return $ret;
            throw new NoContextException(EXCEPTION_NO_CONTEXT);
        }

        private function getContextNoLoad()
        {
            return app()->getContext($this->getContextName());
        }

        function getContext()
        {
            //TODO change it!
            //for report?
            if(!is_object($context = $this->getContextNoLoad()))
            {
                $context = $this->createContext();
                $this->loadContext($context);
                app()->putContext($context);
            }
            return $context;
        }


        function createContext()
        {
            if($this->getType() == RD_TYPE_REGISTRY)
                return $this->createRegistryContextForId(app()->request("id"));

            if($this->getType() == RD_TYPE_REPORT)
            {
                $c = new ReportContext($this->getContextName());
                $c->obj->fetch();
                return $c;
            }
        }

        function createRegistryContextForId($id)
        {
            $tree = $this->getChildrenTree();
            if(!is_array($tree))
            {
                $req = app()->request(REQUEST_REGISTRY);
                $tree = array($req => array());
            }

            $context = new Context($this->getContextName(), $tree, $id);
            return $context;
        }

        private function setDatesValues($f1, $f2, $val1, $val2)
        {

            list($pref1, $path12) = app()->explodePath($f1);
            list($pref2, $path22) = app()->explodePath($f2);

            $c = $this->getContext();

            if($pref1 == "filter")
                $this->getFilter()->setValue($path12, $val1);
            else
                $c->obj->setValue($f1, $val1);

            if($pref2 == "filter")
                $this->getFilter()->setValue($path22, $val2);
            else
                $c->obj->setValue($f2, $val2);

            app()->putContext($c);
        }

        public function prevYear()
        {
            if(($f1 = app()->request("f1")) && ($f2 = app()->request("f2")))
            {
                $f = getFormatter(FORMAT_DATE);
                $y = (int)date("Y") - 1;
                $this->setDatesValues($f1, $f2, $f->encodeHuman("" . $y . "-01-01"), $f->encodeHuman("" . $y . "-12-31"));
            }
            echo app()->jsonMessage();
        }

        public function thisYear()
        {
            if(($f1 = app()->request("f1")) && ($f2 = app()->request("f2")))
            {
                $f = getFormatter(FORMAT_DATE);
                $y = (int)date("Y");
                $this->setDatesValues($f1, $f2, $f->encodeHuman("" . $y . "-01-01"), $f->encodeHuman("" . $y . "-12-31"));
            }
            echo app()->jsonMessage();
        }

        protected function setThisMonthPeriod($obj, $f1, $f2)
        {
                    $y = date("Y");
                    $m = date("m");
                    $dt1 = "$y-$m-01";
                    $m = 1 + $m;
                    if($m > 12)
                    {
                        $y++;
                        $m = 1;
                    }
                    $dt2 = "$y-$m-01";

                    $d1 = strtotime($dt1);
                    $d2 = strtotime($dt2) - SECS_DAY;

                    $dt1 = date("Y-m-d", $d1);
                    $dt2 = date("Y-m-d", $d2);

                    $f = getFormatter(FORMAT_DATE);


                    $obj->setValue($f1, $f->encodeHuman($dt1));
                    $obj->setValue($f2, $f->encodeHuman($dt2));
        }

        public function thisMonth()
        {
            if(($f1 = app()->request("f1")) && ($f2 = app()->request("f2")))
                if(is_object($c = $this->getContext()))
                {
                    list($pref1, $path12) = app()->explodePath($f1);
                    list($pref2, $path22) = app()->explodePath($f2);

                    if($pref1 == "filter")
                        $this->setThisMonthPeriod($this->getFilter(), $path12, $path22);
                    else
                        $this->setThisMonthPeriod($c->obj, $f1, $f2);
                    app()->putContext($c);
                }

            echo app()->jsonMessage();
        }

        public function prevMonth()
        {
            if(($f1 = app()->request("f1")) && ($f2 = app()->request("f2")))
                if(is_object($c = $this->getContext()))
                {
                    $y = date("Y");
                    $m = date("m");
                    $dt2 = "$y-$m-01";
                    $m = $m - 1;
                    if($m < 1)
                    {
                        $y--;
                        $m = 12;
                    }
                    $dt1 = "$y-$m-01";

                    $d1 = strtotime($dt1);
                    $d2 = strtotime($dt2) - SECS_DAY;

                    $dt1 = date("Y-m-d", $d1);
                    $dt2 = date("Y-m-d", $d2);

                    $f = getFormatter(FORMAT_DATE);

                    $this->setDatesValues($f1, $f2, $f->encodeHuman($dt1), $f->encodeHuman($dt2));
                }

            echo app()->jsonMessage();
        }
    }

    class GridSetupObject extends SetupObject
    {
        function getSuffix()
        {
            return "gridsetup";
        }
    }

    /**
     * Class for simple standard registry consisting of fields id, code, name, memo and created/updated metadata fields.
     */
    class SimpleCodedAndNamedRegistryDescriptor extends RegistryDescriptor
    {
        public $gridSql;

        /**
         * {@inheritdoc}
         */
        public function __construct()
        {
            $reg = app()->getCurrentRegistry();
            $this->registry = $reg;
            $this->init($reg);
        }

        /**
         * {@inheritdoc}
         */
        public function init($table)
        {
            $this->gridSql = "select t.id, if(t.closed = 1, 'gclosed', '') as style,
                t.closed,
                t.code, t.name, t.memo,
                t.mdCreated, c.uid as creator,
                t.mdUpdated, u.uid as updater
                from $table t
                left join webuser c on c.id = t.mdCreatorId
                left join webuser u on u.id = t.mdUpdaterId";
        }

        /**
         * {@inheritdoc}
         */
        public function getGrid()
        {
            $ret = new RegFlexiGrid();
            $ret->sortname = "t.name";
            $ret->sortorder = "asc";
            $ret->addColumn(new StyleColumn());
            $ret->addClosedIconColumn();
            $ret->addColumn(new MGridColumn("code", "code", "t.code", 80));
            $ret->addColumn(new MGridColumn("name", "name", "t.name", 200));
            $ret->addColumn(new MGridColumn("memo", "memo", "t.memo", 200));
            $ret->addColumn(new MGridColumn("mdCreated", "mdCreated", "t.mdCreated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
            $ret->addColumn(new MGridColumn("creator", "creator", "c.uid", 80));
            $ret->addColumn(new MGridColumn("mdUpdated", "mdUpdated", "t.mdUpdated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
            $ret->addColumn(new MGridColumn("updater", "updater", "u.uid", 80));

            return $ret;
        }

        /**
         * Constructs and returns array of simpleform components
         * @param DB_Dataobject $obj - data object for created for
         */
        public function getSimpleformComponents($obj)
        {
            return array(
                    textbox($obj, "code", "Code"),
                    textbox($obj, "name", "Name"),
                    textarea($obj, "memo", "Memo")
                );
        }
    }


    class SimpleNamedRegistryDescriptor extends RegistryDescriptor
    {
        public $gridSql;

        public function __construct()
        {
            $reg = app()->getCurrentRegistry();
            $this->registry = $reg;
            $this->init($reg);
        }

        public function init($table)
        {
            $this->gridSql = "select t.id, if(t.closed = 1, 'gclosed', '') as style, t.closed,
                t.name, t.memo,
                t.mdCreated, c.uid as creator,
                t.mdUpdated, u.uid as updater
                from $table t
                left join webuser c on c.id = t.mdCreatorId
                left join webuser u on u.id = t.mdUpdaterId";
        }

        public function getGrid()
        {
            $ret = new RegFlexiGrid();
            $ret->sortname = "t.name";
            $ret->sortorder = "asc";
            $ret->addColumn(new StyleColumn());
            $ret->addClosedIconColumn();
            $ret->addColumn(new MGridColumn("name", "name", "t.name", 200));
            $ret->addColumn(new MGridColumn("memo", "memo", "t.memo", 200));
            $ret->addColumn(new MGridColumn("mdCreated", "mdCreated", "t.mdCreated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
            $ret->addColumn(new MGridColumn("creator", "creator", "c.uid", 80));
            $ret->addColumn(new MGridColumn("mdUpdated", "mdUpdated", "t.mdUpdated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
            $ret->addColumn(new MGridColumn("updater", "updater", "u.uid", 80));

            return $ret;
        }

        /**
         * Constructs and returns array of simpleform components
         * @param DB_Dataobject $obj - data object for created for
         */
        public function getSimpleformComponents($obj)
        {
            return array(
                    textbox($obj, "name", "Name"),
                    textarea($obj, "memo", "Memo")
                );
        }
    }
