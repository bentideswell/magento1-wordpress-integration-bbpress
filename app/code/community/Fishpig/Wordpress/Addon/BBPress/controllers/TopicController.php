<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_BBPress_TopicController extends Fishpig_Wordpress_Addon_BBPress_Controller_Abstract
{
	/**
	 * View a topic
	 **/
	public function viewAction()
	{
		$this->loadLayout('bbpress_topic_view');
		$this->renderLayout();
	}

	/**
	 * Edit a topic
	 **/
	public function editAction()
	{
		$this->loadLayout('bbpress_topic_edit');
		$this->renderLayout();
	}

	/**
	 * Get the content HTML
	 *
	 * @return string
	**/
	protected function _getContentHtml()
	{
		$actionName = $this->getRequest()->getActionName();
		
		if ($actionName === 'edit') {
			return Mage::helper('wp_addon_bbpress')->getTopicEditHtml();			
		}
		else if ($actionName === 'view') {
			return Mage::helper('wp_addon_bbpress')->getSingleTopicHtml();
		}
		
		return false;
	}
}
