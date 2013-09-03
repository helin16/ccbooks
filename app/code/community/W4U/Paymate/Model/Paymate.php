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

/**
 * Paymate payment model
 *
 * @category   W4U
 * @package    W4U_Australia
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
     * @param   mixed $data
     * @return  W4U_Australia_Model_Payment_Paymate
     */
    public function assignData($data)
    {
        $details = array();
        if ($this->getUsername())
        {
            $details['username'] = $this->getUsername();
        }
        if (!empty($details)) 
        {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }

    public function getUsername()
    {
        return $this->getConfigData('username');
    }
    
    public function getUrl()
    {
    	$url = $this->getConfigData('cgi_url');
    	
    	if(!$url)
    	{
    		$url = self::CGI_URL_TEST;
    	}
    	
    	return $url;
    }
    
    /**
     * Get session namespace
     *
     * @return W4U_Australia_Model_Payment_Paymate_Session
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
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

	public function getCheckoutFormFields()
	{
		$a = $this->getQuote()->getShippingAddress();
		$b = $this->getQuote()->getBillingAddress();
		$currency_code = $this->getQuote()->getCurrencyCode();
		$cost = $a->getSubtotal() - $a->getDiscountAmount();
		$shipping = $a->getShippingAmount();

		$_shippingTax = $this->getQuote()->getShippingAddress()->getTaxAmount();
		$_billingTax = $this->getQuote()->getBillingAddress()->getTaxAmount();
		$tax = sprintf('%.2f', $_shippingTax + $_billingTax);
		$cost = sprintf('%.2f', $cost + $tax);
		
		$fields = array(
			'mid'					=> $this->getUsername(),
			'amt'					=> sprintf('%.2f', $cost + $shipping),
			'amt_editable'			=> self::REQUEST_AMOUNT_EDITABLE,
			'currency'				=> $currency_code,
			'ref'					=> $this->getCheckout()->getLastRealOrderId(),
			'pmt_sender_email'		=> $b->getEmail(),
			'pmt_contact_firstname'	=> $b->getFirstname(),
			'pmt_contact_surname'	=> $b->getLastname(),
			'pmt_contact_phone'		=> $b->getTelephone(),
			'pmt_country'			=> $b->getCountry(),
			'regindi_address1'		=> $b->getStreet(1),
			'regindi_address2'		=> $b->getStreet(2),
			'regindi_sub'			=> $b->getCity(),
			'regindi_state'			=> $b->getRegion(),		// Returns full state name
			'regindi_pcode'			=> $b->getPostcode(),
			'return'				=> Mage::getUrl('paymate/paymate/complete'),
			'popup'					=> 'N',
		);

		// Run through fields and replace any occurrences of & with the word 
		// 'and', as having an ampersand present will conflict with the HTTP
		// request.
		$filtered_fields = array();
        foreach ($fields as $k=>$v) {
            $value = str_replace("&","and",$v);
            $filtered_fields[$k] =  $value;
        }
        
        return $filtered_fields;
	}

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('paymate/paymate_form', $name)
            ->setMethod('paymate')
            ->setPayment($this->getPayment())
            ->setTemplate('W4U/paymate/form.phtml');

        return $block;
    }

    /*validate the currency code is avaialable to use for paypal or not*/
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('paymate')->__('Selected currency code ('.$currency_code.') is not compatabile with Paymate'));
        }
        return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
       return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {

    }

    public function canCapture()
    {
        return true;
    }

    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('paymate/paymate/redirect');
    }
}
