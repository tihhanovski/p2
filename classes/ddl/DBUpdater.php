<?php
/**
 * Context
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

 	define("SQL_UPDATE_DIR", "updatedb/");
 	define("STRUCTURE_VIEWS", "structure/views");
 	define("STRUCTURE_TABLES", "structure/tables");
 	define("STRUCTURE_DATA", "structure/data");
 	define("STRUCTURE_INDICES", "structure/indices");


 	/**
 	 * DB structure check and update service.
 	 * Normally called from application instance's folder using php updatedb.php.
 	 * Looks for files in "structure" folders under framework, application and app instance itself and in every subfolder in "registries" of each.
 	 * Also executes once every different SQL (; delimeted) in "updatedb" folder in framework, application and app
 	 */
	class DBUpdater
	{
		/**
		 * start checking
		 */
		public function run()
		{
			app()->localAuth();
			$x = app()->dbo("SqlUpdateLog");
			$c =$x->getDatabaseConnection();
			$c->query("SET NAMES utf8");

			//TODO ensure that SqlUpdateLog table exists. It is necessary for DBUpdater to operate
			$c->query("create table SqlUpdateLog(ID int not null auto_increment primary key, file varchar(100), command text, result text);");

			$this->doUpdate(WFW_ROOT, "framework:");
			$this->doUpdate(APP_ROOT, "app:");
			$this->doUpdate(INSTANCE_ROOT, "instance:");
		}

		private $connection;

		/**
		 * @param string $sql
		 * @return object resultset
		 */
		private function query($sql)
		{
			//TODO switch to Application's query.
			if(!is_object($this->connection))
			{
				$x = app()->dbo("SqlUpdateLog");
				$this->connection =$x->getDatabaseConnection();
			}
			return $this->connection->query($sql);
		}

		/**
		 * update table structure
		 * @param string $name table name
		 * @param string $content table SQL
		 */
		private function updateTable($name, $content)
		{

			if(app()->isDBError($q = $this->query("describe $name")))
			{
				//does not exist

				$i = $i = strrpos($content, ")") + 1;
				$s1 = substr($content, 0, $i);
				$s3 = substr($content, $i);
				$s2 = "";
				$kws = array(
					"engine" => STRUCTURE_ENGINE,
					"charset" => STRUCTURE_CHARSET,
					"collate" => STRUCTURE_COLLATION,
					);
				foreach ($kws as $k => $v)
					if(stripos($s3, $k) === FALSE)
						$s2 .= " " . $k . "=" . $v;

				$sql = $s1 . $s2 . $s3;


				$q = $this->query($content);
				echo "\tcreated";
			}
			else
			{
				$cols = array();
				$a1 = explode("(", $content);

				$c = "";
				for($x = 1; $x < count($a1); $x++)
					$c .= $a1[$x] . "(";


				$a1 = explode(")", $c);
				$fields = "";
				for($x = 0; $x < count($a1) - 1; $x++)
					$fields .= $a1[$x] . ($x < count($a1) - 2 ? ")" : "");

				$c1 = explode("\n", $fields);
				foreach ( $c1 as $col )
				{
					$col = trim($col);
					if($col)
					{
						$a = explode(" ", $col);
						$n = $a[0];
						$f = "";
						for($x = 1; $x < count($a); $x++)
							$f .= $a[$x] . " ";
						if(substr($f, strlen($f) - 2, 2) == ", ")
							$f = substr($f, 0, strlen($f) - 2);
						$cols[$n] = trim($f);
					}
				}

				$o = array();
				while($q->fetchInto($o, DB_FETCHMODE_ASSOC))
					unset($cols[$o["Field"]]);

				foreach ( $cols as $f => $t )
				{
					$sql = "alter table $name add column $f $t";
					echo "\n\t\t" . $sql . "\n";
					$this->query($sql);
				}
			}
		}

		/**
		 * update view
		 * @param string $name view name
		 * @param string $content SQL select statement, NB! not view full DDL
		 */
		private function updateView($name, $content)
		{
			$this->query("drop view if exists $name");
			$this->query("create view $name as $content");
		}


		/**
		 * update view
		 * @param string $name index name
		 * @param string $content index data JSON encoded array of objects, something like {"name": "idxArticleCode", "fields": "code", "type": "index"},
		 */
		private function updateIndex($name, $content)
		{
			echo "\nIndices for $name\n";
			$sql = "";

			$q = app()->queryAsArray("show indexes from $name", DB_FETCHMODE_OBJECT);
			if(is_array($idx = json_decode($content)))
				foreach ($idx as $i)
				{
					$iFields = $i->fields;
					if($iFields)
					{
						$iType = strtolower(isset($i->type) ? $i->type : "index");
						$iName = isset($i->name) ? $i->name : $name . "_" . str_replace(",", "_", str_replace(" ", "", $iFields));
						$iRefs = isset($i->refs) ? $i->refs : "";
						$iOnDelete = isset($i->ondelete) ? $i->ondelete : "restrict";
						$iOnUpdate = isset($i->onupdate) ? $i->onupdate : "restrict";

						if($iType === "primary key")
							$iName = "PRIMARY";
						$ke = false;
						foreach($q as $ei)
							if(strtolower($ei->Key_name) === strtolower($iName))
								$ke = true;

						if(!$ke)
						{
							switch ($iType)
							{
								case "primary key":
									$sql = "alter table $name add primary key($iFields)";
									break;
								case "foreign key":
									//ALTER TABLE article
									//ADD CONSTRAINT fkArticleUnit
									//FOREIGN KEY (unitId)
									//REFERENCES unit (id)
									//ON DELETE RESTRICT ON UPDATE RESTRICT;
									$sql = "alter table $name add constraint $iName " .
										"foreign key($iFields) references $iRefs " .
										"on delete $iOnDelete on update $iOnUpdate";
									break;
								default:
									$sql = "create $iType $iName on $name ($iFields)";
							}
							app()->query($sql);
							echo "\t$iName\n";
							echo "\t$sql\n";
						}
					}
				}
		}

		/**
		 * lists file in $dir and processes every file with $processor method
		 * @param string $dir folder to list
		 * @param sting $processor processor function name
		 */
		private function processFiles($dir, $processor)
		{
			if(file_exists($dir))
			{
				echo "\n" . $dir . "\n";
				$d = dir($dir);
				while (false !== ($file = $d->read()))
					if(substr($file, 0, 1) != ".")
					{
						list($name, $ext) = explode(".", $file);
						echo "\t" . $name;
						$fc = file_get_contents($dir . "/" . $file);

						if(method_exists($this, $processor))
							$this->$processor($name, $fc);

						echo "\tOK\n";
					}
			}
		}


		/**
		 * updates tables data
		 * @param string $name table name to update
		 * @param string $content JSON encoded content
		 */
		private function updateData($name, $content)
		{
			if(is_object($data = json_decode($content)))
				if(is_array($data->data))
					foreach ( $data->data as $d )
					{
						$o = app()->dbo($name);
						foreach ( $o->keys() as $key)
							$o->$key = $d->$key;
						$found = $o->find(true);
						$changed = false;
						foreach ( $d as $key => $value )
						{
							if($o->$key != $value)
							{
								$o->$key = $value;
								$changed = true;
							}
						}
						if(!$found || $changed)
						{
							if($found)
								$o->update();
							else
							{
								$o->insert();

								$old = array();
								$new = array();
								foreach ( $o->keys() as $key)
								{
									$old[] = "$key = '" . $o->$key . "'";
									if($o->$key != $d->$key)
										 $new[] = "$key = '" . $d->$key . "'";
								}

								$sql = "update $name set " . implode($new, ", ") . " where " . implode($old, " and ");
								app()->query($sql);
							}
						}
					}
		}

		/**
		 * run updater on $dir
		 * @param string $dir folder to run
		 * @param string $alias human readable alias of folder
		 */
		private function doUpdate($dir, $alias)
		{
			$model = array(
				STRUCTURE_VIEWS => "updateView",
				STRUCTURE_TABLES => "updateTable",
				STRUCTURE_INDICES => "updateIndex",
				STRUCTURE_DATA => "updateData",
			);

			foreach ( $model as $k => $v )
				$this->processFiles($dir . $k, $v);

			if(file_exists($dir . "registries/"))
			{
				$d = dir($dir . "registries/");
				$fs = array();
				while (false !== ($file = $d->read()))
					if(substr($file, 0, 1) != ".")
						if(file_exists($dir . "registries/" . $file . "/structure/"))
							$fs[] = $file;
					$d->close();
					sort($fs);
					foreach ( $fs as $file)
						foreach ( $model as $k => $v )
							$this->processFiles($dir . "registries/" . $file . "/" . $k, $v);
			}


			if(file_exists($dir . SQL_UPDATE_DIR))
			{
				echo "\n" . $dir . SQL_UPDATE_DIR . "\n";

				$d = dir($dir . SQL_UPDATE_DIR);
				$fs = array();
				while (false !== ($file = $d->read()))
					if(substr($file, 0, 1) != ".")
						$fs[] = $file;
				$d->close();

				sort($fs);

				foreach ( $fs as $file)
				{
					echo $file, "\n";
					$f = file_get_contents($dir . SQL_UPDATE_DIR . $file);
					$cs = explode(";", $f);
					foreach ( $cs as $sql )
						if($sql = trim($sql))
						{
							$s = app()->dbo("SqlUpdateLog");
							$s->file = $alias . $file;
							$s->command = $sql;
							if($s->execute())
								echo $s->output();
						}
				}
			}
		}
	}