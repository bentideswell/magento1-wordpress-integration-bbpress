<?php
/**
 * @package Fishpig_Wordpress_Addon_BBPress
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
abstract class Fishpig_Wordpress_Addon_BBPress_Model_Observer_Plugin_Abstract
{
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
			$html = Mage::helper('wp_addon_bbpress/core')->getHtml();
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
	
	/*
	 * 
	 *
	 * @param  string $html
	 * @param  string $html
	 * @return array|false
	 */
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
			$html = Mage::helper('wp_addon_bbpress/core')->getHtml();
		}

		if ($scripts = $this->_matchAll('/<script[^>]{1,}>.*<\/script>/Us', $html, 0)) {
			foreach($scripts as $key => $script) {
				if (preg_match('/<script[^>]{1,}src=/U', $script)) {
					unset($scripts[$key]);
					continue;
				}

				if (!preg_match('/(' . $tokens . ')/i', $script)) {
					unset($scripts[$key]);
					continue;					
				}
			}
			
			if (count($scripts) > 0) {
				return $scripts;
			}
		}
		
		return array();
		
		/*
		if (is_null($html)) {
			$html = Mage::helper('wp_addon_bbpress/core')->getHtml();
		}

		return $this->_matchAll(
			'/<script[^>]{1,}>((?!<\/script>).)*(' . $tokens . ')((?!<\/script>).)*<\/script>/Us',
			$html, 
			0
		);
		*/
	}
	
	/**
	 * Start WP simulation, run the shortcode and then end WP simulation
	 *
	 * @param string $code
	 * @return mixed
	 */
	protected function _doShortcode($code)
	{
		return Mage::helper('wp_addon_bbpress/core')->doShortcode($code);
	}

	/**
	 * Get the wp_head() and wp_footer() content
	 *
	 * @return string
	 **/
	protected function _getWpHeadAndWpFooter()
	{
		return Mage::helper('wp_addon_bbpress/core')->simulatedCallback(
			function() {
				ob_start();
		
				wp_head();
				wp_footer();
				
				return trim(ob_get_clean());
			}
		);
	}
}
