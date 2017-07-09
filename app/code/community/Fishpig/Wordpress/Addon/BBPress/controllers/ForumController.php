<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_BBPress_ForumController extends Fishpig_Wordpress_Addon_BBPress_Controller_Abstract
{
	/**
	 * View the forum index (BBPress Homepage)
	 **/
	public function indexAction()
	{
		$this->loadLayout('bbpress_forum_index');
		$this->renderLayout();
	}

	/**
	 * View a single forum
	 **/
	public function viewAction()
	{
		$this->loadLayout('bbpress_forum_view');
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
		
		if ($actionName === 'index') {
			return Mage::helper('wp_addon_bbpress')->getForumIndexHtml();
		}
		else if ($actionName === 'view') {
			return Mage::helper('wp_addon_bbpress')->getSingleForumHtml();
		}
		
		return false;
	}
}
