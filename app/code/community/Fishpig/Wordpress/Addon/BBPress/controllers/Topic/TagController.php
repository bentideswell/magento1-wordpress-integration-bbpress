<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_BBPress_Topic_TagController extends Fishpig_Wordpress_Addon_BBPress_Controller_Abstract
{
	/**
	 * View a single forum
	 **/
	public function viewAction()
	{
		$this->loadLayout('bbpress_topic_tag_view');
		$this->renderLayout();
	}
	
	/**
	 * Get the content HTML
	 *
	 * @return string
	**/
	protected function _getContentHtml()
	{
		return Mage::helper('wp_addon_bbpress')->getSingleTopicTagHtml();
	}
}
