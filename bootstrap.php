<?php
/**
 * @package Phpf\Conversion
 * 
 * Converts between units of temperature, length, mass, and volume.
 * 
 * @author wells
 * @license MIT
 * @version 0.0.1
 */

/** Autoloader if not using composer
spl_autoload_register(function ($class) {
	if (0 === strpos($class, 'Phpf\\Conversion')) {
		include __DIR__.'/src/'.str_replace(array('Phpf\\Conversion\\', '\\'), array('', '/'), $class).'.php';
	}
});
*/

/**
 * Converts a given quantity of something to another unit.
 * 
 * @param number $quantity Quantity to convert.
 * @param string $from Unit the given quantity is in.
 * @param string $to Unit to convert the quantity to.
 * 
 * @return float Quantity (float) in the new unit if success, or null if fail.
 */
function convert_unit($quantity, $from, $to) {
	return \Phpf\Conversion\Unit::convert($quantity, $from, $to);
}

/**
 * Converts one of the known materials to another on an energy-equivalent basis.
 * 
 * @param number $quantity Quantity to convert.
 * @param string $material Material to convert from.
 * @param string $to_material Material to convert to.
 * 
 * @return float Quantity of new material if success, or null if fail.
 */
function convert_material($quantity, $material, $to_material) {
	return \Phpf\Conversion\Material::convert($quantity, $material, $to_material);
}

/**
 * Convert a temperature to another unit.
 * 
 * @param number $degrees Temperature to convert, given in degrees.
 * @param string $unit Given temperature unit: one of "C", "F", or "K"
 * @param string $unit_to Temperature unit to convert to.
 * 
 * @return float Temperature in new unit.
 * 
 * @throws InvalidArgumentException if either temperature unit is unknown.
 */
function convert_temp($degrees, $unit, $unit_to) {
	$unit_to = strtoupper($unit_to);
	switch(strtoupper($unit)) {
		case 'F':
			switch($unit_to) {
				case 'C' :
					return ($degrees - 32) * (5/9);
				case 'K' :
					return ($degrees - 32) * (5/9) + 273.15;
				default : break;
			}
		case 'C':
			switch($unit_to) {
				case 'F' :
					return ($degrees * (9/5)) + 32;
				case 'K' :
					return $degrees + 273.15;
				default : break;
			}
		case 'K' :
			switch($unit_to) {
				case 'C' :
					return $degrees - 273.15;
				case 'F' :
					return ($degrees - 273.15) * (9/5) + 32;
				default : break;
			}
		default :
			throw new InvalidArgumentException("Unknown temperature unit '$unit'.");
	}
	throw new InvalidArgumentException("Unknown temperature unit '$unit_to'.");
}
