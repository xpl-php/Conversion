<?php

namespace Phpf\Conversion;

class Material {
	
	const GAS		= 'gas';
	const OIL		= 'oil';
	const NGL		= 'ngl';
	const BITUMEN	= 'bitumen';
	const SCO		= 'sco';
	const COAL		= 'coal';
	
	protected static $materials = array(
	
		// todo Add energy densities, etc.
		self::GAS => array(
			'name' => 'Natural Gas',
			'unit' => 'Bcf',
		),
		self::OIL => array(
			'name' => 'Crude Oil',
			'unit' => 'mmbbl',
		),
		self::NGL => array(
			'name' => 'Natural Gas Liquids',
			'unit' => 'mmbbl',
		),
		self::BITUMEN => array(
			'name' => 'Bitumen',
			'unit' => 'mmbbl',
		),
		self::SCO => array(
			'name' => 'Synthetic Oil',
			'unit' => 'mmbbl',
		),
		self::COAL => array(
			'name' => 'Coal',
			'unit' => 'Mt',
		),
	);
	
	protected static $convert = array(
		
		// these are on an energy-equivalent basis
		// todo attribution/documentation
		self::GAS => array(
			self::OIL => 0.16667,
		),
		self::NGL => array(
			self::OIL => 1,
		),
		self::BITUMEN => array(
			self::OIL => 1,
		),
		self::SCO => array(
			self::OIL => 1,
		),
		self::COAL => array(
			self::OIL => 4.7904,
		),
	);
	
	public static function convert($quantity, $material, $to_material) {
		if ($cf = self::getConversionFactor($material, $to_material)) {
			return floatval($cf*$quantity);
		}
		return null;
	}
	
	public static function getConversionFactor($material, $to_material) {
		
		if (isset(static::$convert[$material])) {
			if (isset(static::$convert[$material][$to_material])) {
				return floatval(static::$convert[$material][$to_material]);
			}
		}
		
		if (isset(static::$convert[$to_material])) {
			if (isset(static::$convert[$to_material][$material])) {
				return floatval(1/static::$convert[$to_material][$material]);
			}
		}
		
		return null;
	}
	
	public static function getExpectedUnit($material) {
		if (isset(static::$materials[$material])) {
			return static::$materials[$material]['unit'];
		}
		return null;
	}
	
}
