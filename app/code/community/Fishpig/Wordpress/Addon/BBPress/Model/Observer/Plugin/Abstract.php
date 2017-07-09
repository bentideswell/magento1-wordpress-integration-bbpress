<?php
/**
 * @category Fishpig
 * @package Fishpig_Wordpress_Addon_BBPress
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <help@fishpig.co.uk>
 */

abstract class Fishpig_Wordpress_Addon_BBPress_Model_Observer_Plugin_Abstract
{
	/**
	 * Retrieve the module alias
	 *
	 * @return string
	 */
	abstract protected function _getModuleAlias();
	
	/**
	 * Retrieve the module alias
	 *
	 * @return string
	 */
	abstract protected function _getPluginFile();
	
	/**
	 * An array containing content from WP to be added to Magento
	 *
	 * @var array
	 **/
	static protected $_headFooterContent = array();
	
	/**
	 * Determines whether the shortcode has been included or not
	 *
	 * @var static bool
	 **/
	static protected $_shortcodeIncluded = false;

	/**
	 * Get a module helper object
	 *
	 * @param string $type
	 * @return Mage_Core_Helper_Abstract
	 */
	protected function _getHelper($type = '')
	{
		return Mage::helper($this->_getModuleAlias() . ($type ? '/' . $type : ''));
	}
	
	/**
	 * Determine whether the plugin in WordPress is enabled and core is active
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->_isEnabled() && $this->_getHelper('core')->isActive();
	}
	
	/**
	 * Determine whether the plugin in WordPress is enabled
	 *
	 * @return bool
	 */
	protected function _isEnabled()
	{
		return Mage::helper('wordpress/plugin')->isEnabled($this->_getPluginFile());
	}
	
	/**
	 *
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function applyStringFiltersObserver(Varien_Event_Observer $observer)
	{
		if (!$this->isEnabled()) {
			return $this;
		}

		self::$_shortcodeIncluded = $this->_applyStringFilters($observer) !== false || self::$_shortcodeIncluded;
		
		return $this;
	}

	/**
	 * Apply the necessary filters to the string
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	protected function _applyStringFilters(Varien_Event_Observer $observer)
	{
		return false;
	}
	
	/**
	 * Get the current set post model
	 *
	 * @return Fishpig_Wordpress_Model_Post
	 */
	protected function _getPost()
	{
		return ($post = Mage::registry('wordpress_post')) !== null
			? $post : false;
	}
	
	/**
	 * A wrapper for preg_match
	 *
	 * @param string $pattern
	 * @param string $haystack
	 * @param null|int $return = null
	 * @return string|false
	 */
	protected function _match($pattern, $haystack, $return = null)
	{
		if (preg_match($pattern, $haystack, $matches)) {
			if (is_null($return)) {
				return $matches;
			}
			
			return isset($matches[$return]) ? $matches[$return] : false;
		}
		
		return false;
	}

	/**
	 * A wrapper for preg_match
	 *
	 * @param string $pattern
	 * @param string $haystack
	 * @param null|int $return = null
	 * @return string|false
	 */
	protected function _matchAll($pattern, $haystack, $return = null)
	{
		if (preg_match_all($pattern, $haystack, $matches)) {
			if (is_null($return)) {
				return $matches;
			}
			
			return isset($matches[$return]) ? $matches[$return] : false;
		}
		
		return false;
	}
	
	/**
	 * Get all includes from the HTML that match the patterns
	 *
	 * @param string $patterns
	 * @param string $html
	 * @return false|array
	 */
	protected function _getIncludeHtmlFromString($patterns, $html = null)
	{
		if (is_null($html)) {
			$html = $this->_getHelper('core')->getHtml();
		}

		$includes = $this->_matchAll(
			'/<(script|link)[^>]+(href|src|id)=[\'"]{1}[^\'"]{0,}(' . str_replace('/', '\/', implode('|', $patterns)) . ')[^\'"]{1,}[\'"]{1}[^>]*>(<\/script>)*/i',
			$html
		);
		
		if ($includes) {
			foreach($includes[0] as $key => $include) {
				if ($includes[1][$key] === 'link') {
					if (!$this->_match('/rel=[\'"]{1}stylesheet[\'"]{1}/Ui', $include)) {
						unset($includes[0][$key]);
						continue;
					}
				}
				
				if (strpos($include, 'ie') !== false) {
					if ($match = $this->_match('/<!--\[if[ a-z]{0,}IE[ ]{0,}[0-9]+\]>' . preg_quote($include, '/') . '<!\[endif\]-->/sUi', $html, 0)) {
						$includes[0][$key] = $match;
					}
				}
			}
			
			return $includes[0];
		}
		
		return false;
	}
	
	/**
	 * Check whether the post_content field contains $string
	 * $string can be an array or string
	 *
	 * @param array|string $strings
	 * @return bool
	 */
	protected function _postContentContains($strings)
	{
		if ($this->_getPost()) {
			if (!is_array($strings)) {
				$strings = (array)$strings;
			}
			
			foreach($strings as $str) {
				if (strpos($this->_getPost()->getData('post_content'), $str) !== false) {
					return true;
				}
			}
		}

		return false;
	}
	
	public function getScriptAndLinkTags($html, $path)
	{
		if (preg_match_all('/<(script|link)[^>]+(href|src)=[\'"]{1}([^\'"]{1,}' . preg_quote($path, '/') . '.*)[\'"]{1}[^>]{0,}>/U', $html, $matches)) {
			foreach($matches[0] as $it => $match) {
				if ($matches[1][$it] === 'script') {
					$matches[0][$it] = $match . '</script>';
				}
			}
			
			return $matches[0];
		}
		
		return false;
	}
	
	/**
	 * Get inline scripts that contain $tokens
	 *
	 * @param stirng $html
	 * @param string $tokens
	 * @return array
	 */
	public function getRelatedInlineScripts($tokens, $html = null)
	{
		if (is_null($html)) {
			$html = $this->_getHelper('core')->getHtml();
		}

		return $this->_matchAll(
			'/<script[^>]{1,}>((?!<\/script>).)*(' . $tokens . ')((?!<\/script>).)*<\/script>/Us',
			$html, 
			0
		);
	}
	
	/**
	 * Start WP simulation, run the shortcode and then end WP simulation
	 *
	 * @param string $code
	 * @return mixed
	 */
	protected function _doShortcode($code)
	{
		try {
			if ($this->_getHelper('core')->isActive()) {
				$this->_getHelper('core')->startWordPressSimulation();
				$value = do_shortcode($code);
				$this->_getHelper('core')->endWordPressSimulation();
				
				// Fix HTML entity data parameters
				$value = str_replace(array('&#091;', '&#093;'), array('[', ']'), $value);
	
				return $value;
			}
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
		}
		
		return $code;
	}
	
	/**
	 * Add a string to head and footer content array
	 *
	 * @param string|array $content
	 * @return $this
	 **/
	protected function _addToHeadFooterContent($content)
	{
		if (!is_array($content)) {
			if ($content === false || $content === '') {
				return $this;
			}
			
			$content = array($content);
		}
		
		foreach($content as $item) {
			$item = trim($item);
			
			if (!in_array($item, self::$_headFooterContent)) {
				self::$_headFooterContent[] = $item;
			}
		}
		
		return $this;
	}

	/**
	 * Add body classes to the head and footer content array
	 *
	 * @param array $content
	 * @return $this
	 **/
	protected function _addBodyClassesToHeadFooter($content)
	{
		if (!is_array($content) || !$content) {
			return $this;
		}	
		
		array_unshift(self::$_headFooterContent, sprintf("<script type=\"text/javascript\">document.getElementsByTagName('body')[0].className+=' %s';</script>", implode(' ', $content)));
		
		return $this;
	}
	
	/**
	 * Get the head and footer content array
	 *
	 * @return string
	 **/
	public function getHeadFooterContent()
	{
		if ($this->_isEnabled() && self::$_shortcodeIncluded && $this->_getHeadFooterContent()) {
			return implode("\n", self::$_headFooterContent);
		}
		
		return '';
	}
}
