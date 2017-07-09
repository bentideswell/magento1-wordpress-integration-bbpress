<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_BBPress_ReplyController extends Fishpig_Wordpress_Addon_BBPress_Controller_Abstract
{
	/**
	 * View the forum index (BBPress Homepage)
	 **/
	public function editAction()
	{
		if ($action = $this->getRequest()->getParam('action')) {
			if ($action === 'move') {
				return $this->_forward('move');
			}	
		}
		
		$this->loadLayout('bbpress_reply_edit');
		$this->renderLayout();
	}
	
	/**
	 * View the forum index (BBPress Homepage)
	 **/
	public function moveAction()
	{
		$this->loadLayout('bbpress_reply_move');
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
			return Mage::helper('wp_addon_bbpress')->getReplyEditHtml();
		}
		else if ($actionName === 'move') {
			return Mage::helper('wp_addon_bbpress')->getReplyMoveHtml();
		}

		
		return false;
	}
}
