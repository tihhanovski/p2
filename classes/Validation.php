<?php
/*
 * Created on Mar 2, 2012
 *
 * (c) Ilja Tihhanovski
 *
 */


	const VALIDATION_NOT_EMPTY = "not empty";
	const VALIDATION_CLASS_METHOD = "class method";
	const VALIDATION_UNIQUE = "unique";

	const VALIDATION_FOREIGN = "fk";
	const VALIDATION_FOREIGN_MUST_EXIST = "fk must exist";
	const VALIDATION_FOREIGN_ADD_IF_NOT_EXIST = "fk add if not exist";

	$_validators = array();

	function getValidator($constraint)
	{
		global $_validators;

		if(isset($_validators[$constraint]) && is_object($_validators[$constraint]))
			return $_validators[$constraint];

		switch ($constraint)
		{
			case VALIDATION_NOT_EMPTY:
				return addValidator($constraint, new ValidatorNotEmpty());

			case VALIDATION_CLASS_METHOD:
				return addValidator($constraint, new ValidatorClassMethod());

			case VALIDATION_UNIQUE:
				return addValidator($constraint, new ValidatorUnique());

			default:
				return addValidator($constraint, new Validator());

		}
	}

	function addValidator($constraint, $v)
	{
		global $_validators;
		$v_validators[$constraint] = $v;
		return $v_validators[$constraint];
	}

	class ValidatorForeign extends Validator
	{
 		public function validate($obj, $field)
 		{
 			return true;
 		}		
	}

 	class Validator
 	{
 		public function validate($obj, $field)
 		{
 			return true;
 		}
 	}

 	class ValidatorClassMethod extends Validator
 	{
 		public function validate($obj, $field)
 		{
 			$m = "validate_" . $field;
 			if(method_exists($obj, $m))
 				return $obj->$m();
 			return true;
 		}
 	}

 	class ValidatorNotEmpty extends Validator
 	{
 		public function validate($obj, $field)
 		{
 			if(($obj->$field == "") || ($obj->$field == "NULL"))
 			{
 				$obj->addWarning(new Warning("Field empty", $field, WARNING_ERROR));
 				return false;
 			}

 			return true;
 		}
 	}

 	class ValidatorUnique extends Validator
 	{
 		public function validate($obj, $field)
 		{
	    	$m = app()->dbo($obj->__table);
	    	$m->$field = $obj->$field;
	    	if($obj->isInDatabase())
	    		$m->whereAdd($obj->getPrimaryKeyField() . " <> " . ((int)$obj->getIdValue()));
	    	if($m->find())
 			{
 				$obj->addWarning(new Warning("Field not unique", $field, WARNING_ERROR));
 				return false;
 			}
 			return true;
 		}
 	}