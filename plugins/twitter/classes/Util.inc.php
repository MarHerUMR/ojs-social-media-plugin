<?php
/**
 * @file plugins.generic.socialMedia.plugins.twitter.classes.Util.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class Util
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Utility class to provide methods use by the Twitter requests.
 */

class Util {
    /**
     * Return the encoded object
     *
     * @param string|array The input to encode
     *
     * @return string|array
     */
    public static function urlEncodeRfc3986($input) {
        $output = '';

        if (is_array($input)) {
            $output = array_map(['Util', 'urlEncodeRfc3986'], $input);
        }  elseif (is_scalar($input)) {
            $output = rawurlencode($input);
        }

        return $output;
    }

    /**
     * Return the decoded string
     *
     * @param $string string
     *
     * @return string
     */
    public static function urlDecodeRfc3986($string) {
        return urldecode($string);
    }

    /**
     * Return an http query
     *
     * @param array $parameters
     *
     * @return string
     */
    public static function getHttpQuery(array $parameters) {
        if (empty($parameters)) {
            return '';
        }

        $keys = Util::urlEncodeRfc3986(array_keys($parameters));
        $values = Util::urlEncodeRfc3986(array_values($parameters));

        $parameters = array_combine($keys, $values);

        uksort($parameters, "strcmp");

        $keyValuePairs = [];

        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                sort($value, SORT_STRING);

                foreach ($value as $duplicate) {
                    $keyValuePairs[] = $parameter . '=' . $duplicate;
                }
            } else {
                $keyValuePairs[] = $key . '=' . $value;
            }
        }

        return implode('&', $keyValuePairs);
    }
}

?>
