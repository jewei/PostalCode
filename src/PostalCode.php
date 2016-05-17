<?php

namespace Jewei\PostalCode;

use Exception;
use SplFileObject;

/**
 * A utility to find or validate postcode.
 */
class PostalCode
{
    /**
     * List of regex in array.
     */
    protected $_resource;

    /**
     * Class constructor.
     *
     * @param  SplFileObject  $file
     */
    public function __construct(SplFileObject $file = null)
    {
        if (is_null($file)) {
            $file = new SplFileObject(__DIR__.'/postcodes.json', 'r');
            $file->rewind();
        }

        if (!$content = $file->fread($file->getSize())) {
            throw new Exception("Error reading file");
        }

        $this->_resource = json_decode($content);
    }

    /**
     * Find the regex parttern according to country.
     *
     * Country is string accepting the official name, ISO code in 2 or 3 digits.
     *
     * @param  string  $country
     *
     * @return string
     */
    private function getRegexPattern($country)
    {
        if (empty($country)) {
            throw new Exception("Country not provided");
        }

        static $_cache = null;
        $country = strtolower($country);

        if (!isset($_cache[$country])) {
            $pattern = false;
            foreach ($this->_resource as $key => $value) {
                if (!$pattern) {
                    $value = (array) $value;
                    switch (true) {
                        case ($country == strtolower($value['iso_3166_1_alpha_2'])):
                        case ($country == strtolower($value['iso_3166_1_alpha_3'])):
                        case ($country == strtolower($value['name'])):
                            $pattern = $value['regex_pattern'];
                            break;
                    }
                } else {
                    continue;
                }
            }
            $_cache[$country] = $pattern;
        }

        return $_cache[$country];
    }

    /**
     * Perform regular expression match.
     *
     * @param  string  $subject
     * @param  string  $pattern
     *
     * @return string|false
     */
    private function match($subject, $pattern)
    {
        if ($pattern) {
            @preg_match($pattern, $subject, $matches);
            if ($failed = error_get_last()) {
                throw new Exception("Zipcode pattern failed.");
            }
            if (!empty($matches)) {
                return $matches[0];
            }
        }
        return false;
    }

    /**
     * Find postal code in a string.
     *
     * @param  string  $str
     * @param  string  $country
     *
     * @return string|false
     */
    public function find($str, $country)
    {
        $pattern = $this->getRegexPattern($country);
        return $this->match($str, '/\b'.$pattern.'\b/');
    }

    /**
     * Validate postal code.
     *
     * @param  string  $postcode
     * @param  string  $country
     *
     * @return 0|boolean
     */
    public function validate($postcode, $country)
    {
        $pattern = $this->getRegexPattern($country);

        if (!$pattern) {
            // No pattern found.
            return 0;
        }

        return (bool) $this->match($postcode, '/^'.$pattern.'$/');
    }
}
