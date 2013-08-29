<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com) (original implementation)
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz) (use of Minify Library)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/*
 * @author     Kristof Ringleff
 * @package    Fooman_SpeedsterAdvanced
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 */

class Fooman_SpeedsterAdvanced_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{
    /**
     * Merge static and skin files of the same format into 1 set of HEAD directives or even into 1 directive
     *
     * Will attempt to merge into 1 directive, if merging callback is provided. In this case it will generate
     * filenames, rather than render urls.
     * The merger callback is responsible for checking whether files exist, merging them and giving result URL
     *
     * @param string   $format      - HTML element format for sprintf('<element src="%s"%s />', $src, $params)
     * @param array    $staticItems - array of relative names of static items to be grabbed from js/ folder
     * @param array    $skinItems   - array of relative names of skin items to be found in skins according to design config
     * @param callback $mergeCallback
     *
     * @return string
     */
    protected function &_prepareStaticAndSkinElements(
        $format, array $staticItems, array $skinItems, $mergeCallback = null
    )
    {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                $items['static'][$params][] = $mergeCallback ? Mage::getBaseDir() . DS . 'js' . DS . $name
                    : $baseJsUrl . $name;
            }
        }

        // lookup each file basing on current theme configuration
        foreach ($skinItems as $params => $rows) {
            foreach ($rows as $name) {
                $items['skin'][$params][] = $mergeCallback ? $designPackage->getFilename(
                    $name, array('_type' => 'skin')
                )
                    : $designPackage->getSkinUrl($name, array());
            }
        }

        $html = '';
        foreach ($items as $type) {
            foreach ($type as $params => $rows) {
                // attempt to merge
                $mergedUrl = false;
                if ($mergeCallback) {
                    $mergedUrl = call_user_func($mergeCallback, $rows);
                }
                // render elements
                $params = trim($params);
                $params = $params ? ' ' . $params : '';
                if ($mergedUrl) {
                    $html .= sprintf($format, $mergedUrl, $params);
                } else {
                    foreach ($rows as $src) {
                        $html .= sprintf($format, $src, $params);
                    }
                }
            }
        }
        return $html;
    }
}
