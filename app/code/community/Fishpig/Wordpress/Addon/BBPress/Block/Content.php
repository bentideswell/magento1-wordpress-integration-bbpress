<?php
/**
 *
**/
class Fishpig_Wordpress_Addon_BBPress_Block_Content extends Mage_Core_Block_Template
{
	/**
	 *
	**/
	public function setContent($html)
	{
		return $this->setData('content', $html);
	}
	
	/**
	 *
	**/
	public function getContent()
	{
		return $this->getData('content');
	}
	
	/**
	 *
	**/
	public function setPageTitle($title)
	{
		return $this->setData('page_title', $title);
	}
	
	/**
	 *
	**/
	public function getPageTitle()
	{
		return $this->getData('page_title');
	}
}
