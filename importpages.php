<?php // NEEDED for any magento commend line
require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app();


// Now you can run ANY Magento code you want
?>

<?php // Create a cms page
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cmsPage = array(
			'title' => 'Test Page',
			'identifier' => 'test-page',
			'content' => 'Sample Test Page',
			'is_active' => 1,
			'sort_order' => 0,
			'stores' => array(0),
			'root_template' => 'three_columns'
			);

Mage::getModel('cms/page')->setData($cmsPage)->save();

?>

<?php // Create a cms page
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cmsPage = array(
			'title' => 'page-for-libraries',
			'identifier' => 'page-for-libraries',
			'content' => 'page-for-libraries',
			'is_active' => 1,
			'sort_order' => 0,
			'stores' => array(0),
			'root_template' => 'three_columns'
			);

Mage::getModel('cms/page')->setData($cmsPage)->save();

?>

<?php // Create a cms page
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cmsPage = array(
			'title' => 'page-for-schools',
			'identifier' => 'page-for-schools',
			'content' => 'page-for-schools',
			'is_active' => 1,
			'sort_order' => 0,
			'stores' => array(0),
			'root_template' => 'three_columns'
			);

Mage::getModel('cms/page')->setData($cmsPage)->save();

?>

<?php // Create a cms page
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cmsPage = array(
			'title' => 'page-wholesale',
			'identifier' => 'page-wholesale',
			'content' => 'page-wholesale',
			'is_active' => 1,
			'sort_order' => 0,
			'stores' => array(0),
			'root_template' => 'three_columns'
			);

Mage::getModel('cms/page')->setData($cmsPage)->save();

?>

<?php // Create a cms page
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cmsPage = array(
			'title' => 'page-special-orders',
			'identifier' => 'page-special-orders',
			'content' => 'page-special-orders',
			'is_active' => 1,
			'sort_order' => 0,
			'stores' => array(0),
			'root_template' => 'three_columns'
			);

Mage::getModel('cms/page')->setData($cmsPage)->save();

?>

<?php // Create a cms page
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cmsPage = array(
			'title' => 'page-faq',
			'identifier' => 'page-faq',
			'content' => 'page-faq',
			'is_active' => 1,
			'sort_order' => 0,
			'stores' => array(0),
			'root_template' => 'three_columns'
			);

Mage::getModel('cms/page')->setData($cmsPage)->save();

?>

<?php // Create a cms page
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cmsPage = array(
			'title' => 'page-contact-us',
			'identifier' => 'page-contact-us',
			'content' => 'page-contact-us',
			'is_active' => 1,
			'sort_order' => 0,
			'stores' => array(0),
			'root_template' => 'three_columns'
			);

Mage::getModel('cms/page')->setData($cmsPage)->save();

?>



<?php // Create a cms block
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
 
$staticBlock = array(
                'title' => 'cms page left 01',
                'identifier' => 'cms-page-left-01',
                'content' => '{{block type="ultramegamenu/navigation" template="infortis/ultramegamenu/categories.phtml"}}',
                'is_active' => 1,
                'stores' => array(1)
                );
 
Mage::getModel('cms/block')->setData($staticBlock)->save();
?>
