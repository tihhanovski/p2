<?php
/**
 * PasswordValidator
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	/**
	* Password validators superclass.
	* You can define your own validators list by overriding Application.initPasswordValidators method.
	* @see app() function
	*/
	class PasswordValidator
	{
		/**
		 * Error message.
		 * @return string error message (untranslated)
		 */
		public function getErrorMessage()
		{
			return "";
		}

		/**
		 * checks user (with password set) for errors.
		 * @param FWWebuser $user
		 * @return string error message or "" if no errors
		 */
		public function validate($user)
		{
			return "";
		}
	}

	//some default password validators

	/**
	 *	//Checks minimal password length
	 */
	class ShortPasswordValidator extends PasswordValidator
	{
		/**
		 * {@inheritdoc}
		*/
		public function getErrorMessage()
		{
			return "Password minimal length is 8 symbols";
		}

		/**
		 * {@inheritdoc}
		*/
		public function validate($user)
		{
			return strlen($user->pwd) < PWD_MIN_LENGTH ? $this->getErrorMessage() : "";
		}
	}

	/**
	 *	//Checks that username is not equal to password
	 */
	class SameWithUsernamePasswordValidator extends PasswordValidator
	{
		/**
		 * {@inheritdoc}
		*/
		public function getErrorMessage()
		{
			return "Password cant be same as user name";
		}

		/**
		 * {@inheritdoc}
		*/
		public function validate($user)
		{
			return $user->pwd == $user->uid ? $this->getErrorMessage() : "";
		}
	}

	/**
	 *	//Checks that password contains at least ona upper case char (A-Z)
	 */
	class UppercaseCharPasswordValidator extends PasswordValidator
	{
		/**
		 * {@inheritdoc}
		*/
		public function getErrorMessage()
		{
			return "Password should contain at least one upper case character (A-Z)";
		}

		/**
		 * {@inheritdoc}
		*/
		public function validate($user)
		{
			return preg_match("/[A-Z]/", $user->pwd) === 0 ? $this->getErrorMessage() : "";
		}
	}

	/**
	 *	//Checks that password contains at least ona lower case char (a-z)
	 */
	class LowerCharPasswordValidator extends PasswordValidator
	{
		/**
		 * {@inheritdoc}
		*/
		public function getErrorMessage()
		{
			return "Password should contain at least one lower case character (a-z)";
		}

		/**
		 * {@inheritdoc}
		*/
		public function validate($user)
		{
			return preg_match("/[a-z]/", $user->pwd) === 0 ? $this->getErrorMessage() : "";
		}
	}

	/**
	 *	//Checks that password contains at least ona number (0-9)
	 */
	class NumberPasswordValidator extends PasswordValidator
	{
		/**
		 * {@inheritdoc}
		*/
		public function getErrorMessage()
		{
			return "Password should contain at least one number (0-9)";
		}

		/**
		 * {@inheritdoc}
		*/
		public function validate($user)
		{
			return preg_match("/[0-9]/", $user->pwd) === 0 ? $this->getErrorMessage() : "";
		}
	}