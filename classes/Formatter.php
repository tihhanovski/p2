<?php
/**
 * Formatter
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

    const FORMAT_INT = "int";
    const FORMAT_FLOAT  = "float";
    const FORMAT_FLOAT0  = "float0";
    const FORMAT_FLOAT1  = "float1";
    const FORMAT_FLOAT2_NULLABLE  = "float2n";
    const FORMAT_FLOAT2  = "float2";
    const FORMAT_FLOAT3  = "float3";
    const FORMAT_FLOAT4  = "float4";
    const FORMAT_FLOAT6  = "float6";
    const FORMAT_DATE  = "date";
    const FORMAT_DATE2  = "date2";
    const FORMAT_DATETIME  = "datetime";
    const FORMAT_DATETIME_SHORT  = "datetimeshort";
    const FORMAT_TIME  = "time";
    const FORMAT_TIMEPERIOD  = "timeperiod";
    const FORMAT_VARCHAR  = "varchar";
    const FORMAT_DEFAULT  = "default";
    const FORMAT_TRANSLATED  = "translated";
    const FORMAT_LINK_TO_OBJECT  = "link";
    const FORMAT_FILESIZE  = "filesize";
    const FORMAT_ICON = "icon";

    const FORMATSTRING_DATETIME_MACHINE = "Y-m-d H:i:s";
    const FORMATSTRING_DATE_MACHINE = "Y-m-d";

    const FORMATSTRING_TIME_MACHINE = "H:i";
    const FORMATSTRING_TIMEPERIOD_HUMAN = "HHH:i";
    const MIN_DATE = 1910;

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

        /**
         * Returns data in human readable format.
         * @param mixed $s
         * @return string
         */
        function encodeHuman($s)
        {
            return $s;
        }

        /**
         * Returns data in inner "machine suited" format.
         * @param mixed $s
         * @return string
         */
        function decodeHuman($s)
        {
            return $s;
        }
    }

    /**
     * Encapsulates common method for datetime formatters
     */
    class DTFormatter extends Formatter
    {
        /**
         * Used to get year number from string.
         * @param string s - Date in string format. If $s is "NULL" or empty, returns 0, otherwise tries to get year from given string
         * @return int
         */
        protected function getYear($s)
        {
            if($s == "NULL" || $s == "")
                return 0;
            $y = (int)date("Y", strtotime($s));
            return $y;
        }
    }

    /**
     * Date formatter
     * @see FORMATSTRING_DATE_HUMAN, FORMATSTRING_DATE_MACHINE
     */
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

    /**
     * Time formatter
     * @see FORMATSTRING_TIME_HUMAN
     */
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

    /**
     * TimePeriod formatter
     @see FORMATSTRING_TIMEPERIOD_HUMAN
     */
    class TimePeriodFormatter extends Formatter
    {
        private static $c;

        static function singleton()
        {
            if(!is_object(self::$c))
                self::$c = new TimePeriodFormatter();
            return self::$c;
        }

        function encodeHuman($s)
        {
        	return $this->decodeHuman($s);
        }

        function decodeHuman($s)
        {
            if($s == "")
                return "";
            $a = explode(":", $s);
            $sec = isset($a[2]) ? 0 + $a[2] : 0;
            $min = isset($a[1]) ? 0 + $a[1] : $a[0];
            $hrs = isset($a[1]) ? 0 + $a[0] : 0;

            $min += round($sec / 60);
            $sec = 0;
            if($min > 60)
            {
            	$dh = floor($min / 60);
            	$hrs += $dh;
            	$min -= $dh * 60;
            }

            return $hrs . ":" . $min;
        }
    }

    /**
     * Formats datetime values
     * @see FORMATSTRING_DATETIME_HUMAN, MIN_DATE, FORMATSTRING_DATE_MACHINE
     */
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
        }
    }

    /**
     * Formats datetime values
     * @see FORMATSTRING_DATETIME_SHORT_HUMAN, MIN_DATE, FORMATSTRING_DATETIME_MACHINE
     */
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

    /**
     * Formats float value
     * @todo Usage of "," as decimals delimiter is not always needed
     */
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
            $numeric = str_replace(" ", "", str_replace(",", ".", $s));
            if(defined("NUMERIC_AUTOEVAL") && NUMERIC_AUTOEVAL)
            {
                //strip everything except 0-9 + - / * .
                //TODO use regexp
                $num = "";
                for($i = 0; $i < strlen($numeric); $i++)
                {
                    $s = substr($numeric, $i, 1);
                    if(($s >= "0" && $s <= "9") || $s == "." || $s == "+" || $s == "-" || $s == "*" || $s == "/" || $s == "(" || $s == ")")
                        $num .= $s;
                }

                $var = $num;
                if($num !== "")
                {
                    $script = "\$var = $num;";
                    //echo "now eval $script\n";
                    try
                    {
                        eval($script);
                    }catch(Exception $e)
                    {}
                }
                $numeric = $var;
            }
            return 0 + $numeric;
        }
    }

    /**
     * Formats numbers with 1 decimal places
     */
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

    /**
     * Formats numbers with 2 decimal places
     */
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

    /**
     * Formats numbers with 3 decimal places
     */
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

    /**
     * Formats nullable numbers
     */
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

    /**
     * Formats nullable numbers with 2 decimal places
     */
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

    /**
     * Formats numbers with no decimal places
     */
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

    /**
     * Formats numbers with 4 decimal places
     */
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

    /**
     * Formats numbers with 6 decimal places
     */
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

    /**
     * Formats file size in bytes, kb, mb, gb etc
     * encodeHuman only
     */
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
     * Icons formatter
     * encodeHuman only
     */
    class IconFormatter extends Formatter
    {
        private static $c;
        private $icons = array();

        static function singleton()
        {
            if(!is_object(self::$c))
                self::$c = new IconFormatter();
            return self::$c;
        }

        function encodeHuman($s)
        {
            if(!isset($this->icons[$s]))
            {
                $fn = "ui/img/16/" . $s . ".png";
                if(app()->getAbsoluteFile($fn) == "")
                    $fn = "ui/img/16/z.png";
                $this->icons[$s] = app()->url($fn);
            }
            return $this->icons[$s];
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
            case FORMAT_FLOAT:                return FloatFormatter::singleton();
            case FORMAT_FLOAT0:                return FloatFormatter0::singleton();
            case FORMAT_FLOAT1:                return FloatFormatter1::singleton();
            case FORMAT_FLOAT2:                return FloatFormatter2::singleton();
            case FORMAT_FLOAT3:                return FloatFormatter3::singleton();
            case FORMAT_FLOAT2_NULLABLE:    return FloatFormatter2Nullable::singleton();
            case FORMAT_FLOAT4:                return FloatFormatter4::singleton();
            case FORMAT_FLOAT6:                return FloatFormatter6::singleton();
            case FORMAT_DATE:                 return DateFormatter::singleton();
            case FORMAT_DATETIME:             return DateTimeFormatter::singleton();
            case FORMAT_DATETIME_SHORT:     return DateTimeShortFormatter::singleton();
            case FORMAT_TRANSLATED:         return TranslatedFormatter::singleton();
            case FORMAT_TIME:                 return TimeFormatter::singleton();
            case FORMAT_TIMEPERIOD:			return TimePeriodFormatter::singleton();
            case FORMAT_FILESIZE:            return FileSizeFormatter::singleton();
            case FORMAT_ICON:                 return IconFormatter::singleton();
        }
        return Formatter::singleton();
    }