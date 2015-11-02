<?php
/**
 * Formatter
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

	define("FORMAT_INT", "int");
	define("FORMAT_FLOAT", "float");
	define("FORMAT_FLOAT0", "float0");
	define("FORMAT_FLOAT1", "float1");
	define("FORMAT_FLOAT2_NULLABLE", "float2n");
	define("FORMAT_FLOAT2", "float2");
	define("FORMAT_FLOAT3", "float3");
	define("FORMAT_FLOAT4", "float4");
	define("FORMAT_FLOAT6", "float6");
	define("FORMAT_DATE", "date");
	define("FORMAT_DATE2", "date2");
	define("FORMAT_DATETIME", "datetime");
	define("FORMAT_DATETIME_SHORT", "datetimeshort");
	define("FORMAT_TIME", "time");
	define("FORMAT_VARCHAR", "varchar");
	define("FORMAT_DEFAULT", "default");
	define("FORMAT_TRANSLATED", "translated");
	define("FORMAT_LINK_TO_OBJECT", "link");
	define("FORMAT_FILESIZE", "filesize");

	const FORMATSTRING_DATETIME_MACHINE = "Y-m-d H:i:s";
	const FORMATSTRING_DATE_MACHINE = "Y-m-d";

	const FORMATSTRING_TIME_MACHINE = "H:i";
	const MIN_DATE = 1910;

	if(!defined("FORMATSTRING_DATE_HUMAN"))
		define("FORMATSTRING_DATE_HUMAN", FORMATSTRING_DATE_MACHINE);
	if(!defined("FORMATSTRING_TIME_HUMAN"))
		define("FORMATSTRING_TIME_HUMAN", FORMATSTRING_TIME_MACHINE);
	if(!defined("FORMATSTRING_DATETIME_HUMAN"))
		define("FORMATSTRING_DATETIME_HUMAN", FORMATSTRING_DATETIME_MACHINE);
	if(!defined("FORMATSTRING_DATETIME_SHORT_HUMAN"))
		define("FORMATSTRING_DATETIME_SHORT_HUMAN", FORMATSTRING_DATETIME_MACHINE);


	/**
	 * Formatters superclass
	 */
	class Formatter
	{
		private static $c;

		public static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new Formatter();
			return self::$c;
		}

		/*
		 ** @param mixed $s
		 ** @return string data in human readable format
		 */
		function encodeHuman($s)
		{
			return $s;
		}

		/*
		 ** @param mixed $s
		 ** @return string data in inner "machine suited" format
		 */
		function decodeHuman($s)
		{
			return $s;
		}
	}

	class DTFormatter extends Formatter
	{
		protected function getYear($s)
		{
			if($s == "NULL" || $s == "")
				return 0;
			$y = (int)date("Y", strtotime($s));
			return $y;
		}
	}

	class DateFormatter extends DTFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new DateFormatter();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if($this->getYear($s) < MIN_DATE)
				return "";
			else
				return date(FORMATSTRING_DATE_HUMAN, strtotime($s));
		}

		function decodeHuman($s)
		{
			if($s == "")
				return "NULL";
			else
				if(is_object($do = date_create_from_format(FORMATSTRING_DATE_HUMAN, $s)))
					return date(FORMATSTRING_DATE_MACHINE, $do->getTimestamp());
				else
					return date(FORMATSTRING_DATE_MACHINE, strtotime($s));
		}
	}

	class TimeFormatter extends Formatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new TimeFormatter();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if($s)
				return date(FORMATSTRING_TIME_HUMAN, strtotime($s));
			else
				return "";
		}

		function decodeHuman($s)
		{
			if($s == "")
				return "";
			else
				return date("H:i", strtotime($s));
		}
	}

	class DateTimeFormatter extends DTFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new DateTimeFormatter();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if($this->getYear($s) < MIN_DATE)
				return "";
			else
				return date(FORMATSTRING_DATETIME_HUMAN, strtotime($s));
		}

		function decodeHuman($s)
		{
			if($s == "")
				return "NULL";
			else
				return date(FORMATSTRING_DATE_MACHINE, date_create_from_format(FORMATSTRING_DATETIME_HUMAN, $s)->getTimestamp());
				//return date(FORMATSTRING_DATETIME_MACHINE, strtotime($s));
		}
	}

	class DateTimeShortFormatter extends DateTimeFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new DateTimeShortFormatter();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if($this->getYear($s) < MIN_DATE)
				return "";
			else
				return date(FORMATSTRING_DATETIME_SHORT_HUMAN, date_create_from_format(FORMATSTRING_DATETIME_MACHINE, $s)->getTimestamp());
		}

	}

	class FloatFormatter extends Formatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatter();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
				return str_replace(".", ",", $s);
			else
				return "";
		}

		function decodeHuman($s)
		{
			return 0 + str_replace(" ", "", str_replace(",", ".", $s));
		}
	}

	class FloatFormatter1 extends FloatFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatter1();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
				return number_format($s, 1, ",", " ");
			else
				return "";
		}
	}

	class FloatFormatter2 extends FloatFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatter2();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
				return number_format($s, 2, ",", " ");
			else
				return "";
		}
	}

	class FloatFormatter3 extends FloatFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatter3();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
				return number_format($s, 3, ",", " ");
			else
				return "";
		}

	}

	class FloatFormatterNullable extends FloatFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatterNullable();
			return self::$c;
		}

		function decodeHuman($s)
		{
			if($s != "")
				return 0 + str_replace(" ", "", str_replace(",", ".", $s));
			else
				return NULL;
		}
	}

	class FloatFormatter2Nullable extends FloatFormatterNullable
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatter2Nullable();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
				return number_format($s, 2, ",", " ");
			else
				return "";
		}

	}

	class FloatFormatter0 extends FloatFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatter0();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
				return number_format($s, 0, ",", " ");
			else
				return "";
		}

	}

	class FloatFormatter4 extends FloatFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatter4();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
				return number_format($s, 4, ",", " ");
			else
				return "";
		}

	}

	class FloatFormatter6 extends FloatFormatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FloatFormatter6();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
				return number_format($s, 6, ",", " ");
			else
				return "";
		}
	}

	class FileSizeFormatter extends FloatFormatter2
	{
		private static $c;
		private $filesizename = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new FileSizeFormatter();
			return self::$c;
		}

		function encodeHuman($s)
		{
			if(is_numeric($s))
  				return $s ? parent::encodeHuman(round($s/pow(1024, ($i = floor(log($s, 1024)))), 2)) . $this->filesizename[$i] : '0 B';
			else
				return "";
		}
	}

	/**
	 * Translates data, using i18n translation arrays.
	 * encodeHuman only
	 */
	class TranslatedFormatter extends Formatter
	{
		private static $c;

		static function singleton()
		{
			if(!is_object(self::$c))
				self::$c = new TranslatedFormatter();
			return self::$c;
		}

		function encodeHuman($s)
		{
			return t($s);
		}
	}

	/**
	 * instantiates (if needed) and returns formatter for specified format descriptor
	 * @param string $format
	 * @return Formatter
	 */
	function getFormatter($format)
	{
		switch ($format)
		{
			case FORMAT_FLOAT:				return FloatFormatter::singleton();
			case FORMAT_FLOAT0:				return FloatFormatter0::singleton();
			case FORMAT_FLOAT1:				return FloatFormatter1::singleton();
			case FORMAT_FLOAT2:				return FloatFormatter2::singleton();
			case FORMAT_FLOAT3:				return FloatFormatter3::singleton();
			case FORMAT_FLOAT2_NULLABLE:	return FloatFormatter2Nullable::singleton();
			case FORMAT_FLOAT4:				return FloatFormatter4::singleton();
			case FORMAT_FLOAT6:				return FloatFormatter6::singleton();
			case FORMAT_DATE: 				return DateFormatter::singleton();
			case FORMAT_DATETIME: 			return DateTimeFormatter::singleton();
			case FORMAT_DATETIME_SHORT: 	return DateTimeShortFormatter::singleton();
			case FORMAT_TRANSLATED: 		return TranslatedFormatter::singleton();
			case FORMAT_TIME: 				return TimeFormatter::singleton();
			case FORMAT_FILESIZE:			return FileSizeFormatter::singleton();
		}
		return Formatter::singleton();
	}