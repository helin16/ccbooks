<?php
/**
 * W4U Paymate Controller
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
class W4U_Paymate_PaymateController extends Mage_Core_Controller_Front_Action
{
    /**
     * removing the ajax
     * 
     * @return W4U_Paymate_PaymateController
     */
    protected function _expireAjax()
    {
        if (!$this->_getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
        return $this;
    }
    /**
     * Getting the paymate model object
     * 
     * @return W4U_Paymate_Model_Paymate
     */
    private function _getModel()
    {
        return Mage::getModel('paymate/paymate');
    }
    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
    {
        return $this->_getModel()->getCheckout();
    }
    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    private function _getQuote()
    {
        return $this->_getModel()->getCheckout()->getQuote();
    }
    /**
     * When a customer chooses Paymate on Checkout/Payment page
     * 
     * @return W4U_Paymate_PaymateController
     */
    public function startAction()
    {
        try {
            $session = $this->_getCheckoutSession();
            $session->setPaymateQuoteId($session->getQuoteId());
            if($this->_getModel()->getOrder()->getStatus() !== $this->_getModel()->getConfigData('order_status'))
                Mage::throwException(Mage::helper('checkout')->__('You can NOT resubmit the payment anymore.'));
            $form = $this->getLayout()->createBlock('paymate/redirect')->toHtml();
//             echo '<textarea>';
//             echo '</textarea>';
            $this->getResponse()->setBody($form);
            $session->unsQuoteId();
            return $this;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Unable to start Express Checkout.'));
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }
    /**
     * When a customer cancels payment from Paymate.
     * Currently this never actually occurs as Paymate does not provide a way
     * to cancel the order from their interface.
     */
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaymateQuoteId(true));
        $this->_redirect('checkout/cart');
     }
    /**
     * Where Paymate returns.
     * Paymate currently always returns the same code so there is little point
     * in attempting to process it.
     */
    public function completeAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->norouteAction();
            return $this;
        }
        
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaymateQuoteId(true));
        
        $response = $this->getRequest()->getPost();
        $responseText = '';

        // Check the response code
        if($response['responseCode'] == 'PP') {
            // Pending
            $responseText = 'Payment processing';
            
        } elseif($response['responseCode'] == 'PA') {
            // Authorised / processing
            $responseText = 'Payment processing';
            
        } else {
            // Declined
            $this->_cancelOrder($response);
            $this->_redirect('checkout/onepage/failure');
            return $this;
        }
        
        // Set the quote as inactive after returning from Paymate
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();

        // Send a confirmation email to customer
        $order = Mage::getModel('sales/order');
        $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
        if( $order->getId() )
		{
			$paymentInst = $order->getPayment()->getMethodInstance();
		    $paymentInst->setLastTransId($response['transactionID']);
		
			$order->sendNewOrderEmail();
			$order->setEmailSent( true );

            if($response['responseCode'] == 'PA') {
                // Only if the payment is fully authorised should it be invoiced
			    if( $order->canInvoice() )
			    {
				    $invoice = $order->prepareInvoice();
				    $invoice->register()->capture();
				    $order->addRelatedObject( $invoice );
			    }
			}
			
			$text = 'Order returned from Paymate<br />';
			$text .= 'Response: ' . $responseText . '<br />';
			$text .= 'Transaction ID: ' . $response['transactionID'] . '<br />';
			$text .= 'Amount Paid: ' . $response['paymentAmount'] . ' ' . $response['currency'] . '<br />';
			
			$order->addStatusToHistory($order->getStatus(), $text);
			$order->save();
		}
        Mage::getSingleton('checkout/session')->unsQuoteId();
        $this->_redirect('checkout/onepage/success');
    }
    /**
     * Canceling the order
     * 
     * @param array $response The response from Paymate
     * 
     * @return W4U_Paymate_PaymateController
     */
	protected function _cancelOrder($response)
	{
	    $session = Mage::getSingleton('checkout/session');
	    $order = Mage::getModel('sales/order');
        $order->load($session->getLastOrderId());
        $order->cancel();
        $order->addStatusToHistory($order->getStatus(), Mage::helper('paymate')->__('Payment was declined by gateway.<br />Transaction ID: ' . $response['transactionID']));
        $order->save();
        return $this;
	}

}
