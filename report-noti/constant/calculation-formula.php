<?php

/**
 * Constants for calculation formula functionality
 *
 * This file contains all constants used in calculation formula operations
 * including validation limits, time formats, and business rules.
 */

// Validation constants
define('CALCULATION_FORMULA_MIN_KM_VALUE', 0);
define('CALCULATION_FORMULA_MAX_KM_VALUE', 999999);
define('CALCULATION_FORMULA_MIN_PRICE_VALUE', 0);
define('CALCULATION_FORMULA_MAX_PRICE_VALUE', 999999999);
define('CALCULATION_FORMULA_MAX_DESCRIPTION_LENGTH', 255);

// Time format constants
define('CALCULATION_FORMULA_TIME_FORMAT', 'H:i');
define('CALCULATION_FORMULA_DEFAULT_TIME', '00:00');

// Price rounding constants
define('CALCULATION_FORMULA_ROUNDING_THRESHOLD_6_DIGITS', 6);
define('CALCULATION_FORMULA_ROUNDING_THRESHOLD_7_DIGITS', 7);
define('CALCULATION_FORMULA_ROUNDING_FACTOR_6_DIGITS', -4);
define('CALCULATION_FORMULA_ROUNDING_FACTOR_7_DIGITS', -5);

// Distance constants
define('CALCULATION_FORMULA_MIN_DISTANCE', 0);
define('CALCULATION_FORMULA_MAX_DISTANCE', 999999);

// Field validation constants
define('CALCULATION_FORMULA_TIME_FIELD_MAX_LENGTH', 5);
define('CALCULATION_FORMULA_SCHEDULE_FIELD_MAX_LENGTH', 50);

// Business logic constants
define('CALCULATION_FORMULA_DEFAULT_PRICE', 0);
define('CALCULATION_FORMULA_DEFAULT_WAIT_PRICE', 0);
define('CALCULATION_FORMULA_DEFAULT_SURCHARGE', 0);
define('CALCULATION_FORMULA_DEFAULT_OVERNIGHT_FEE', 0);

// Error message constants
define('CALCULATION_FORMULA_ERROR_INVALID_DATA', 'invalid_data_provided');
define('CALCULATION_FORMULA_ERROR_NO_DATA', 'no_data_provided');
define('CALCULATION_FORMULA_ERROR_SYSTEM', 'system_error');
