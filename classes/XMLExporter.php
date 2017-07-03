<?php
/**
 * XML Exporter for reports
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2017 Ilja Tihhanovski
 *
 */


	class XMLExporter
	{

		function __construct($title)
		{
			$this->title = $title ? $title : "report";
			$this->xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<report/>");

			$this->xml->addChild("title", $this->title);
		}

		function addPairs($a, $collectionName, $itemName)
		{
			$h = $this->xml->addChild($collectionName);
			if(is_array($a))
			 	foreach ( $a as $k => $v )
			 	{
			 		$hr = $h->addChild($itemName);
			 		$hr->addChild("title", $k);
			 		$hr->addChild("value", $v);
			 	}

		}

		function addRows($a)
		{
			$r = $this->xml->addChild("rows");
			foreach ($a as $row)
			{
				$xr = $r->addChild("row");
				foreach ($row as $k => $v)
					$xr->addChild($k, $v);
			}
		}

	    function arrayToXml(array $arr, SimpleXMLElement $xml)
	    {
	        foreach ($arr as $k => $v)
	        {
	            if(is_object($v) && method_exists($v, "toSimpleXml"))
	                $v->toSimpleXml($xml->addChild($v->__table));
	            else
	                is_array($v)
	                    ? $this->arrayToXml($v, $xml->addChild($k))
	                    : $xml->addChild($k, $v);
	        }
	        return $xml;
	    }

		function output($fn = "")
		{
			if($fn == "")
				$fn = $this->title;

		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename=' . $fn . ".xml");
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');

	
		    $s = $this->xml->asXML();

    		echo $s;

    	}
	}