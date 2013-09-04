<?php
/**
 * W4U Paymate Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so one can be sent to you a copy immediately.
 *
 * @category   W4U
 * @package    W4U_Paymate
 * @copyright  Copyright (c) 2013 http://websiteforyou.com.au
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class W4U_Paymate_Block_Form extends Mage_Payment_Block_Form
{
	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Block_Template::_toHtml()
	 */
    protected function _toHtml()
    {
        $_code = $this->getMethodCode();
        $html = '<ul id="payment_form_' . $_code . '" class="form-list" style="display:none"><li>';
        $html .= $this->__('You will be redirected to the Paymate website when you place an order.');
        $html .= '</li></ul>';
        return $html;
    }
}
