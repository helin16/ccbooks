<?php
/**
 * W4U Paymate Payment Module
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
class W4U_Paymate_Model_Paymate extends Mage_Payment_Model_Method_Abstract
{
    const CGI_URL = 'https://www.paymate.com/PayMate/ExpressPayment';
    const CGI_URL_TEST = 'https://www.paymate.com/PayMate/TestExpressPayment';
    const REQUEST_AMOUNT_EDITABLE = 'N';

    protected $_code  = 'paymate';
    protected $_formBlockType = 'W4U_paymate_block_form';
    protected $_allowCurrencyCode = array('AUD', 'EUR', 'GBP', 'NZD', 'USD');
    
    protected $_isGateway               = false;
    protected $_isInitializeNeeded      = true;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    
    /**
     * Assign data to info model instance
     *
     * @param   mixed $data The request data
     * 
     * @return  W4U_Paymate_Model_Paymate
     */
    public function assignData($data)
    {
        $details = array();
        if ($this->getUsername()) {
            $details['username'] = $this->getUsername();
        }
        if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }
    /**
     * Getting the store username for paymate
     */
    public function getUsername()
    {
        return $this->getConfigData('username');
    }
    /**
     * Getting the CGI_uri for paymate
     * 
     * @return Ambigous <string, mixed, NULL, multitype:, multitype:Ambigous <string, multitype:, NULL> >
     */
    public function getUrl()
    {
    	$url = $this->getConfigData('cgi_url');
    	return (!$url ? self::CGI_URL_TEST : $url);
    }
    /**
     * Get session namespace
     *
     * @return W4U_Paymate_Model_Paymate_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('paymate/paymate_session');
    }
    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    /**
     * Get current the saved Order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
    }
	/**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
    /**
     * getting the checkout form fields
     * 
     * @return array The fields data
     */
	public function getCheckoutFormFields()
	{
	    $order = $this->getOrder();
		$shippAddr = $order->getShippingAddress();
		$billAddr = $order->getBillingAddress();
		$currency_code = $order->getOrderCurrencyCode();
// 		$cost = $shippAddr->getSubtotal() - $shippAddr->getDiscountAmount();
// 		$shipping = $shippAddr->getShippingAmount();

// 		$_shippingTax = $this->getQuote()->getShippingAddress()->getTaxAmount();
// 		$_billingTax = $this->getQuote()->getBillingAddress()->getTaxAmount();
// 		$tax = sprintf('%.2f', $_shippingTax + $_billingTax);
// 		$cost = sprintf('%.2f', $cost + $tax);
		$cost = $order->getGrandTotal();
		$fields = array(
			'mid'					=> $this->getUsername(),
			'ref'					=> $this->getCheckout()->getLastRealOrderId(),
			'amt'					=> sprintf('%.2f', $cost + $shipping),
			'amt_editable'			=> self::REQUEST_AMOUNT_EDITABLE,
			'currency'				=> $currency_code,
			'pmt_sender_email'		=> $billAddr->getEmail(),
			'pmt_contact_firstname'	=> $billAddr->getFirstname(),
			'pmt_contact_surname'	=> $billAddr->getLastname(),
			'pmt_contact_phone'		=> $billAddr->getTelephone(),
			'pmt_country'			=> $billAddr->getCountry(),
			'regindi_address1'		=> $billAddr->getStreet(1),
			'regindi_address2'		=> $billAddr->getStreet(2),
			'regindi_sub'			=> $billAddr->getCity(),
			'regindi_state'			=> $billAddr->getRegion(),		// Returns full state name
			'regindi_pcode'			=> $billAddr->getPostcode(),
			'return'				=> Mage::getUrl('paymate/paymate/complete'),
			'popup'					=> 'N',
		);
		return $fields;
	}
    /**
     * Creating form block 
     * 
     * @param string $name The name of the form
     * 
     * @return W4U_Paymate_Block_Form
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('paymate/paymate_form', $name)
            ->setMethod('paymate')
            ->setPayment($this->getPayment())
            ->setTemplate('W4U/paymate/form.phtml');

        return $block;
    }
    /**
     * (non-PHPdoc)
     * @see Mage_Payment_Model_Method_Abstract::validate()
     */
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('paymate')->__('Selected currency code ('.$currency_code.') is not compatabile with Paymate'));
        }
        return $this;
    }
    /**
     * Getting the url for redirecting when paymate success
     * 
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('paymate/paymate/start');
    }
}
