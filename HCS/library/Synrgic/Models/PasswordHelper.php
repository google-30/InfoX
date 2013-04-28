<?
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * A helper class to deal with password generation
 */
class Synrgic_Models_PasswordHelper
{
	/**
	 * Generates a random password for the user. The random password
	 * is not salted. The password generated is 8 characters 
	 * in length
	 *
	 * The generator is probably not cryptographically safe, hence the password
	 * should be combined with a salt value before checked.
	 *
	 * @return A random password
	 */
	public function generatePassword()
	{
			return substr($this->generateSalt(),0,8);
	}

	/**
	 * Using SHA256 a random salt and 5000 rounds, encrypt the give password.
	 *
	 * @return An encrypted string safe for storage in a database and sutible for use in checkPassword
	 */
	public function cryptPassword($password)
	{
	    	$salt = $this->generateSalt();
		$qualifiedSalt='$5$rounds=5000$' . $salt . '$';
		return crypt($password, $qualifiedSalt);
	}

	/**
	 * This routine is used to check the password for a user in the system
	 * it makes use of the salt for that particular user in calculating if the
	 * password is valid. Checking is performed by concat
	 *
	 * @param $original The original password to check against
	 * @param $password The password to check
	 * @return true if the password matches, false otherwise
	 */
	public function checkPassword($original, $password)
	{
		if( crypt( $password, $original) == $original ){
			return true;
		}
		return false;
	}

	/**
	 * Generates the a defined length string of random
	 * bits which can be added to a password to increase it's strength.
	 *
	 * @return A string of 16 characters
	 */
	private function generateSalt()
	{
		$random = openssl_random_pseudo_bytes (16);
		$string = base64_encode($random);

		// base64 version may be longer tha 16 characters, truncate to
		// 16
		return substr($string, 0, 16);
	}
}

?>
