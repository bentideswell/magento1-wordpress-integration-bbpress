<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_BBPress_UserController extends Fishpig_Wordpress_Addon_BBPress_Controller_Abstract
{
	/**
	 * Redirect to the profile action
	**/
	public function indexAction()
	{
		return $this->_forward('profile');
	}

	/**
	 * View the user profile
	 **/
	public function profileAction()
	{
		$this->loadLayout('bbpress_user_profile');
		$this->renderLayout();
	}
	
	/*
	 *
	 *
	 */
	public function repliesAction()
	{
		$this->loadLayout('bbpress_user_replies');
		$this->renderLayout();
	}
	
	/*
	 *
	 *
	 */
	public function favouritesAction()
	{
		$this->loadLayout('bbpress_user_favourites');
		$this->renderLayout();
	}
	
	/*
	 *
	 *
	 */
	public function topicsAction()
	{
		$this->loadLayout('bbpress_user_topics');
		$this->renderLayout();
	}
	
	/*
	 *
	 *
	 */
	public function subscriptionsAction()
	{
		$this->loadLayout('bbpress_user_subscriptions');
		$this->renderLayout();
	}
	
	/*
	 *
	 *
	 */
	public function editAction()
	{
		$this->loadLayout('bbpress_user_edit');
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

		if ($actionName === 'profile') {
			return Mage::helper('wp_addon_bbpress')->getUserProfileHtml();
		}
		else if ($actionName === 'replies') {
			return Mage::helper('wp_addon_bbpress')->getUserRepliesHtml();
		}
		else if ($actionName === 'favourites') {
			return Mage::helper('wp_addon_bbpress')->getUserFavouritesHtml();
		}
		else if ($actionName === 'topics') {
			return Mage::helper('wp_addon_bbpress')->getUserTopicsHtml();
		}
		else if ($actionName === 'subscriptions') {
			return Mage::helper('wp_addon_bbpress')->getUserSubscriptionsHtml();
		}
		else if ($actionName === 'edit') {
			return Mage::helper('wp_addon_bbpress')->getUserEditHtml();
		}

		return false;
	}
}
