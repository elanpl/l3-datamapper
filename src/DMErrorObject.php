<?php

namespace elanpl\DM;

/**
 * Simple class to prevent errors with unset fields.
 * @package DMZ
 *
 * @param string $FIELD Get the error message for a given field or custom error
 * @param string $RELATED Get the error message for a given relationship
 * @param string $transaction Get the transaction error.
 */
class DMErrorObject {
	/**
	 * Array of all error messages.
	 * @var array
	 */
	public $all = array();

	/**
	 * String containing entire error message.
	 * @var string
	 */
	public $string = '';

	/**
	 * All unset fields are returned as empty strings by default.
	 * @ignore
	 * @param string $field
	 * @return string Empty string
	 */
	public function __get($field) {
		return '';
	}
}