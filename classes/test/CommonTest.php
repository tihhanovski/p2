<?php
/*
 * Created on Mar 19, 2013
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	require_once WFW_ROOT . "classes/index.php";

	class CommonTest
	{
		function run()
		{
			echo "<html><title>" . get_class($this) . "</title>" .
					"<style>" .
					".testDiv{margin-bottom: 10px;} " .
					".h1{margin-bottom: 2px;} " .
					".ta{width: 95%; min-height: 80px; height: 200px; margin-left: 10px;} " .
					".FAIL{color: red;} " .
					".OK{color: green;}" .
					"</style>";


			$startTime = microtime(true);
			$ret = true;
			$output = "";
			$countOK = 0;
			$countFail = 0;

			$rc = new ReflectionClass($this);
			$a = $rc->getMethods();
			foreach ( $a as $md )
			{
				if(stripos($mn = $md->name, "test") === 0)
				{
					$this->testOutput = "";
					$r = $this->$mn();
					$st = $r ? "OK" : "FAIL";
					$output .= "<div class=\"testResultDiv $st\">$st\t<strong>" . $mn . "</strong></div><div class=\"testDiv\">";
					if($this->testOutput)
						$output .= "<textarea class=\"ta\">$this->testOutput</textarea>";
					$output .= "</div>";
					$ret = $ret && $r;

					if($r)
						$countOK++;
					else
						$countFail++;
				}
			}

			$t = round(microtime(true) - $startTime, 3);
			echo "<pre><small><h1>" . ($ret ? "OK" : "FAIL") . "(successfull $countOK / failed $countFail)</h1>" .
				"time: " . $t . " sec<br/>" .
				$output;
		}

		protected function log($s)
		{
			$this->testOutput .= $s . "\n";
		}

		protected function errorMessage($s)
		{
			$this->log($s);
			return false;
		}

	}
