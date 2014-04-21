<?php

namespace Phpf\Conversion;

class Unit 
{
	
	// Prefixes
	const DECI	= 'd';	// Tenth
	const CENTI	= 'c';	// Hundredth
	const MILLI	= 'm';	// Thousandth
	const MICRO	= 'u';	// Millionth
	const NANO	= 'n';	// Billionth
	
	const DEKA	= 'da';	// Ten
	const HECTO = 'h';	// Hundred
	const KILO	= 'k';	// Thousand
	const MEGA	= 'M';	// Million
	const GIGA	= 'G';	// Billion
	const TERA	= 'T';	// Trillion
	
	// just semantics
	const MILLION = 'M';
	const BILLION = 'B';
	
	// mass
	const GRAM	= 'g';
	const OUNCE	= 'oz';
	const POUND	= 'lb';
	const TON	= 't';
	
	// volume
	const GALLON		= 'gal';
	const IMP_GALLON	= 'igal';
	const LITER			= 'L';
	const BARREL		= 'bbl';
	const CUBIC_INCH	= 'in^3';
	const CUBIC_FOOT	= 'ft^3';
	const CUBIC_METER	= 'm^3';
	
	// length
	const METER	= 'm';
	const INCH	= 'in';
	const FOOT	= 'ft';
	const YARD	= 'yd';
	const MILE	= 'mi';
	
	// other
	const PPM	= 'ppm';
	
	/**
	 * Unit prefixes and multipliers.
	 * 
	 * @var array
	 */
	protected static $prefixes = array(
		self::DECI		=> 0.1,
		self::CENTI		=> 0.01,
		self::MILLI		=> 0.001,
		self::MICRO		=> 0.0001,
		self::NANO		=> 0.00001,
		
		self::DEKA		=> 10,
		self::HECTO		=> 100,
		self::KILO		=> 1000,
		self::MEGA		=> 1000000,
		self::GIGA		=> 1000000000,
		
		self::MILLION	=> 1000000,
		self::BILLION	=> 1000000000,
		
		// e.g. mmbbl = million barrels
		'mm'			=> 1000000,
	);
	
	/**
	 * Unit names.
	 * 
	 * @var array
	 */
	protected static $names = array(
		self::GRAM	=> 'gram',
		self::OUNCE	=> 'ounce',
		self::POUND	=> 'pound',
		self::TON	=> 'ton',
		
		self::GALLON		=> 'gallon',
		self::IMP_GALLON	=> 'imperial gallon',
		self::LITER			=> 'liter',
		self::BARREL		=> 'barrel',
		self::CUBIC_INCH	=> 'cubic inch',
		self::CUBIC_FOOT	=> 'cubic foot',
		self::CUBIC_METER	=> 'cubic meter',
		
		self::METER	=> 'meter',
		self::INCH	=> 'inch',
		self::FOOT	=> 'foot',
		self::YARD	=> 'yard',
		self::MILE	=> 'mile',
		
		self::PPM	=> 'parts per million',
	);
	
	/**
	 * Unit conversions.
	 * 
	 * Read like: "{Top-level array key} per 1 {nested array key}"
	 * 
	 * @var array
	 */
	protected static $convert = array(
		
		// Mass
		self::GRAM	=> array(
			self::POUND	=> 453.592, // grams per pound
			self::TON	=> 907185, // grams per ton
		),
		self::OUNCE	=> array(
			self::POUND	=> 16, // ounces per pound
			self::TON	=> 32000, // etc...
		),
		self::POUND	=> array(
			self::TON	=> 2000,
		),
		
		// Volume
		self::GALLON		=> array(
			self::IMP_GALLON	=> 0.832674,
			self::LITER			=> 3.78541,
			self::BARREL		=> 42,
			self::CUBIC_INCH	=> 0.004329,
			self::CUBIC_FOOT	=> 0.133681,
			self::CUBIC_METER	=> 264.172,
		),
		self::LITER			=> array(
			self::IMP_GALLON	=> 4.54609,
		),
		self::CUBIC_INCH	=> array(
			self::CUBIC_FOOT	=> 1728,
			self::CUBIC_METER	=> 61023.7,
		),
		self::CUBIC_FOOT	=> array(
			self::CUBIC_METER	=> 35.3147,
		),
		
		// Length
		self::INCH	=> array(
			self::FOOT	=> 12,
			self::YARD	=> 36,
			self::MILE	=> 63360,
		),
		self::FOOT	=> array(
			self::YARD	=> 3,
			self::MILE	=> 5280,
		),
		self::METER	=> array(
			self::INCH	=> 0.0254,
			self::FOOT	=> 0.3048,
			self::YARD	=> 0.9144,
			self::MILE	=> 1609.34,
		),
	);
	
	/**
	 * Conversion closures.
	 * @var array
	 */
	protected static $providers = array();
	
	/**
	 * Converts a quantity from one unit to another.
	 * 
	 * @param number $quantity Number of units.
	 * @param string $from Unit in which quantity is given.
	 * @param string $to Unit to convert quantity to.
	 * @return float Quantity in new unit, or null if fail.
	 */
	public static function convert($quantity, $from, $to) {
		
		$fMult = $tMult = 1; // default multipliers = 1
		
		// remove prefixes and convert multipliers
		$from = static::parse($from, $fMult);
		$to = static::parse($to, $tMult);
		
		// get conversion factor for base units
		$cf = static::getConversionFactor($from, $to);
		
		if (null === $cf) {
			// no conversion factor => try a provider
			// providers provide ONE-WAY conversions only.
			if ($provider = static::getProvider($from)) {
				return $provider($to, $quantity);
			}
			return null;
		}
		
		// new value = Quantity x Factor x Multiplier Ratio
		return floatval($cf*$quantity*($fMult/$tMult));	
	}
	
	/**
	 * Returns conversion factor for a given pair of units.
	 * 
	 * @param string $from Unit to convert from.
	 * @param string $to Unit to convert to.
	 * @return float Conversion factor.
	 */
	public static function getConversionFactor($from, $to) {
		
		if (isset(static::$convert[$to])) {
			// have direct conversion factor from X to Y
			if (isset(static::$convert[$to][$from])) {
				return floatval(static::$convert[$to][$from]);
			}
		}
			
		if (isset(static::$convert[$from])) {
			// have inverse conversion factor from Y to X
			// X-to-Y CF = 1 / Y-to-X CF
			if (isset(static::$convert[$from][$to])){
				return floatval(1/static::$convert[$from][$to]);
			}
		}
		
		return null;
	}
	
	/**
	 * Returns a unit's name.
	 * 
	 * @param string $unit Unit
	 * @return string Name
	 */
	public static function getName($unit) {
		return isset(static::$names[$unit]) ? static::$names[$unit] : null;
	}
	
	/**
	 * Provides a conversion callback for a unit.
	 * Does not permit reverse conversions.
	 * 
	 * @param string $unit Unit the callback converts from.
	 * @param Closure $call Closure to call when converting.
	 * 						Will be passed 2 params:
	 * 						(1) unit to convert to
	 * 						(2) quantity in {$unit}s
	 * @return void
	 */
	public static function provide($unit, \Closure $call) {
		static::$providers[$unit] = $call;
	}
	
	/**
	 * Returns a provider closure if set, otherwise returns null.
	 * 
	 * @param string $unit Unit converting from.
	 * @return Closure|null
	 */
	protected static function getProvider($unit) {
		return isset(static::$providers[$unit]) ? static::$providers[$unit] : null;
	}
	
	/**
	 * Parses a unit, extracting prefixes and returns the base unit.
	 * 
	 * @param string $unit Unit to parse, possibly with a prefix.
	 * @param int &$multiplier Multiplier corresponding to prefix, if any.
	 * @return string Base unit
	 */
	protected static function parse($unit, &$multiplier = 1) {
		// only parse prefixes if not a base unit
		if (! isset(static::$names[$unit])) {
			foreach(static::$prefixes as $pre => $mult) {
				// found prefix => set multiplier and return base unit
				if  (0 === strpos($unit, $pre)) {
					$multiplier = $mult;
					return substr($unit, strlen($pre));
				}
			}
		}
		// base unit given, or no multiplier found
		return $unit; 
	}
	
}