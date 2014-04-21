Conversion
==========

Unit conversion library for PHP. Two-way conversion between temperatures (C, F, and K), as well as various units of length, masse, and volume.

Unit conversions are performed using "intelligent" parsing, where prefixes are used to determine multipliers for each unit; this means that only base units need to define conversion factors (see basic usage example, since this probably makes no sense).

####Basic Usage

The unit conversion class defines a conversion factor of 453.592 grams per pound. Using this one conversion factor, we can find the following:

```php

use Phpf\Conversion\Unit;

// Kilograms per pound
$kg_lb = Unit::convert(1, Unit::POUND, 'kg');
// same as: Unit::convert(1, 'lb', 'kg');

// Megagrams per pound
$Mg_lb = Unit::convert(1, Unit::POUND, 'Mg');

// Milligrams per pound
// note the significance of cases
$mg_lb = Unit::convert(1, Unit::POUND, 'mg');

// Pounds per gram 
// notice this is just the reverse of the defined conversion factor, 
// but no additional definition is required.
$lb_g = Unit::convert(1, Unit::GRAM, Unit::POUND);

// Million pounds per kilogram
$mlb_kg = Unit::convert(1, 'kg', 'Mlb');
```

As you can see, defining a single base unit conversion (grams to pounds) allows you to convert between multiples of both units using common prefixes.

