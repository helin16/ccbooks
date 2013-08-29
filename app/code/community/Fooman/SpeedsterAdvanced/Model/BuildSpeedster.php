<?php

/*
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 */

/*
 *
 * @author     Kristof Ringleff
 * @package    Fooman_SpeedsterAdvanced
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 */

require_once 'minify' . DS . 'Minify' . DS . 'Build.php';

class Fooman_SpeedsterAdvanced_Model_BuildSpeedster extends Minify_Build
{
    /**
     * Create a build object
     *
     * @param array $sources array of Minify_Source objects and/or file paths
     * @param string $base
     *
     * @return Fooman_SpeedsterAdvanced_Model_BuildSpeedster
     */
    public function __construct($sources, $base = BP)
    {
        $max = 0;
        foreach ((array)$sources as $source) {
            if ($source instanceof Minify_Source) {
                $max = max($max, $source->lastModified);
            } elseif (is_string($source)) {
                if (0 === strpos($source, '//')) {
                    $source = $base . substr($source, 1);
                }
                if (is_file($source)) {

                    $max = max($max, filemtime($source));
                }
            }
        }
        $this->lastModified = $max;
        return $this;
    }


    /**
     * Get last modified
     *
     * @param null
     *
     * @return string
     */
    public function getLastModified()
    {
        if (0 === stripos(PHP_OS, 'win')) {
            require_once 'Minify.php';
            Minify::setDocRoot(); // we may be on IIS
        }
        return $this->lastModified;
    }
}