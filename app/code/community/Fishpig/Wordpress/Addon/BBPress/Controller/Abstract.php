<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 * @SkipObfuscation
 */

abstract class Fishpig_Wordpress_Addon_BBPress_Controller_Abstract extends Fishpig_Wordpress_Controller_Abstract
{
	/**
	 * @const string
	**/
	const CONTENT_BLOCK_LAYOUT_NAME = 'bbpress.content';
	
	/**
	 * @return string
	**/
	abstract protected function _getContentHtml();

	/**
	 *
	 * @param array|string $handles = null
	 * @param bool $generateBlocks = true
	 * @param bool $generateXml = true
	**/
	public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true)
	{	
		// Get the HTML
		$html = Mage::helper('wp_addon_bbpress/core')->getHtml();

		// Load the layout
		parent::loadLayout(array_merge((array)$handles, array('default', 'bbpress_default')), $generateBlocks, $generateXml);
		
		// Get the content block
		if (!($contentBlock = $this->getLayout()->getBlock('bbpress.content'))) {
			Mage::throwException(
				$this->__(
					'bbPress content block not found. All bbPress actions should have a block of type \'%s\' and a layout name of \'%s\'.',
					'wp_addon_bbpress/content',
					self::CONTENT_BLOCK_LAYOUT_NAME
				)
			);
		}
		
		// Content not set so 404
		if (($contentHtml = $this->_getContentHtml()) === false) {
			return false;
		}
		
		// Add the content HTML
		$contentBlock->setContent($this->_getContentHtml());
		
		// Set the HTML head title
		if (preg_match('/<title>(.*)<\/title>/Ui', $html, $titleMatch)) {
			$this->_title($titleMatch[1]);
		}
		
		// Get the on page title
		if (preg_match_all('/<h1[^>]{0,}>(.*)<\/h1>/Ui', $html, $titleMatches)) {
			foreach($titleMatches[1] as $key => $titleMatch) {
				if (strpos($titleMatch, '<a') !== false) {
					unset($titleMatches[1][$key]);
				}
				else if (strpos($titleMatches[0][$key], 'link-modal-title') !== false) {
					unset($titleMatches[1][$key]);					
				}
			}
			
			// Title exists so set it to the content block
			if ($titleMatches[1]) {
				$contentBlock->setPageTitle(array_shift($titleMatches[1]));
			}
		}
	
		return $this;
	}
}
