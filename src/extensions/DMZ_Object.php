<?php

namespace elanpl\DM\extensions;
/**
 * Array Extension for DataMapper classes.
 *
 * Quickly convert DataMapper models to-and-from PHP arrays.
 *
 * @license 	MIT License
 * @package		DMZ-Included-Extensions
 * @category	DMZ
 * @author  	Phil DeJarnett
 * @link    	http://www.overzealous.com/dmz/pages/extensions/array.html
 * @version 	1.0
 */

// --------------------------------------------------------------------------

/**
 * DMZ_Array Class
 *
 * @package		DMZ-Included-Extensions
 */
class DMZ_Object {

	/**
	 * Convert a DataMapper model into an associative array.
	 * If the specified fields includes a related object, the ids from the
	 * objects are collected into an array and stored on that key.
	 * This method does not recursively add objects.
	 *
	 * @param	DataMapper $object The DataMapper Object to convert
	 * @param	array $fields Array of fields to include.  If empty, includes all database columns.
	 * @return	array An associative array of the requested fields and related object ids.
	 */
	function to_object($object, $fields = '')
	{
		// assume all database columns if $fields is not provided.
		if(empty($fields))
		{
			$fields = $object->serializable_fields;
		}
		else
		{
			$fields = (array) $fields;
                        
			is_array($fields) or $fields = array($fields);
			if (is_array($fields) && in_array('*',$fields) ) {
					$fieldsO = $object->serializable_fields;
					$fields = array_merge($fieldsO, $fields );
					unset( $fields[ array_search ('*', $fields) ] );
			}
                        
		}

		$result = new \stdClass();

		foreach($fields as $f)
		{
			// handle related fields
			if(array_key_exists($f, $object->has_one))
			{
				$result->$f = $object->{$f}->to_object($fields);
			}
			elseif(array_key_exists($f, $object->has_many)){
				$result->$f = $object->{$f}->all_to_object($fields);
			}
			else
			{
				// just the field.
				$result->$f = $object->{$f};
			}
		}

		return $result;
	}

	/**
	 * Convert the entire $object->all array result set into an array of
	 * associative arrays.
	 *
	 * @see		to_array
	 * @param	DataMapper $object The DataMapper Object to convert
	 * @param	array $fields Array of fields to include.  If empty, includes all database columns.
	 * @return	array An array of associative arrays.
	 */
	function all_to_object($object, $fields = '')
	{
		// loop through each object in the $all array, convert them to
		// an array, and add them to a new array.
		$result = array();
		foreach($object as $o)
		{
			$result[] = $o->to_object($fields);
		}
		return $result;
	}


}

/* End of file array.php */
/* Location: ./application/datamapper/array.php */
