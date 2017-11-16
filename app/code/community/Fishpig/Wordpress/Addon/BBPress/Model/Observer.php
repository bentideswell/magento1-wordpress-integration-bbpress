<?php
/**
 * @category Fishpig
 * @package Fishpig_Wordpress
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_BBPress_Model_Observer extends Fishpig_Wordpress_Addon_BBPress_Model_Observer_Plugin_Abstract
{
	/**
	 * Retrieve the module alias
	 *
	 * @return string
	 */
	protected function _getModuleAlias()
	{
		return 'wp_addon_bbpress';
	}
	
	/**
	 * Retrieve the module alias
	 *
	 * @return string
	 */
	protected function _getPluginFile()
	{
		return 'bbpress/bbpress.php';
	}

	/**
	 * Remove the post type for the plugin (if CPT is installed)
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function initPostTypesObserver(Varien_Event_Observer $observer)
	{
		if (!$this->isEnabled()) {		
			return $this;
		}

		$helper = $observer->getEvent()->getHelper();
		
		if (!($postTypes = $observer->getEvent()->getTransport()->getPostTypes())) {
			return $this;
		}

		$types = get_post_types(array('_builtin' => false, 'public' => true), 'objects');	
		
		foreach(array('forum', 'topic', 'reply') as $type) {
			if (isset($types[$type])) {
				if (!isset($postTypes[$type])) {
					$postTypes[$type] = Mage::getModel('wordpress/post_type')
						->setData(json_decode(json_encode($types[$type]), true))
						->setPostType($type);
				}

				$postTypes[$type]->setCustomRoute('wp_addon_bbpress/' . $type . '/view')
					->setCustomArchiveRoute('wp_addon_bbpress/' . $type . '/index');
			}
		}

		return $this;
	}

	/**
	 * Add extra routes for the categories
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function initTaxonomiesObserver(Varien_Event_Observer $observer)
	{
		if (!$this->isEnabled()) {
			return $this;
		}

		$helper = $observer->getEvent()->getHelper();
		
		$taxonomies = $observer->getEvent()->getTransport()->getTaxonomies();
		$types = get_taxonomies(array('_builtin' => false, 'public' => true), 'objects');

		foreach(array('topic-tag') as $type) {
			if (isset($types[$type])) {
				if (!isset($taxonomies[$type])) {
					$taxonomies[$type] = Mage::getModel('wordpress/term_taxonomy')
						->setData(json_decode(json_encode($types[$type]), true))
						->setTaxonomyType($type);
				}

				$taxonomies[$type]->setCustomRoute('wp_addon_bbpress/' . str_replace('-', '_', $type) . '/view');
			}
		}

		$observer->getEvent()
			->getTransport()
				->setTaxonomies($taxonomies);

		return $this;
	}

	/**
	 * Attempt to match a WP route to a custom post type
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function matchRoutesObserver(Varien_Event_Observer $observer)
	{
		$observer->getEvent()
			->getRouter()
				->addRouteCallback(array($this, 'getRoutes'));
	}
	
	/**
	 * Generate routes based on $uri
	 *
	 * @param string $uri = ''
	 * @return $this
	 */
	public function getRoutes($uri = '')
	{
		if (!$this->isEnabled()) {
			return $this;
		}

		$helper = Mage::helper('wp_addon_bbpress');
		$router = Mage::app()->getFrontController()->getRouter('wordpress');

		$router->addRoute('/' . $helper->getRootSlug() . '$/', 'wp_addon_bbpress/forum/index');
		$router->addRoute('/' . $helper->getRootSlug() . '\/' . $helper->getTopicTagSlug() . '\//', 'wp_addon_bbpress/topic_tag/view');
		$router->addRoute('/' . $helper->getRootSlug() . '\/' . $helper->getForumSlug() . '\//', 'wp_addon_bbpress/forum/view');
		$router->addRoute('/' . $helper->getRootSlug() . '\/' . $helper->getTopicSlug() . '\/([^\/]{1,})$/', 'wp_addon_bbpress/topic/view');
		$router->addRoute('/' . $helper->getRootSlug() . '\/' . $helper->getTopicSlug() . '\/([^\/]{1,})\/edit/', 'wp_addon_bbpress/topic/edit');
		$router->addRoute('/' . $helper->getRootSlug() . '\/reply\/([0-9]{1,})\/edit/', 'wp_addon_bbpress/reply/edit');
		$router->addRoute('/' . $helper->getRootSlug() . '\/' . $helper->getSearchSlug() . '[\/]{0,1}$/', 'wp_addon_bbpress/search/index');
		$router->addRoute('/' . $helper->getRootSlug() . '\/' . $helper->getSearchSlug() . '\/(.*)$/', 'wp_addon_bbpress/search/view');

		// User pages
		$baseUserRoute = '/^' . $helper->getRootSlug() . '\/' . $helper->getUserRootSlug() . '\/[^\/]+';
		
		$router->addRoute($baseUserRoute . '$/', 'wp_addon_bbpress/user/index');
		$router->addRoute($baseUserRoute . '\/' . $helper->getUserTopicsSlug() . '$/', 'wp_addon_bbpress/user/topics');
		$router->addRoute($baseUserRoute . '\/' . $helper->getUserRepliesSlug() . '$/', 'wp_addon_bbpress/user/replies');
		$router->addRoute($baseUserRoute . '\/' . $helper->getUserFavouritesSlug() . '$/', 'wp_addon_bbpress/user/favourites');
		$router->addRoute($baseUserRoute . '\/' . $helper->getUserSubscriptionsSlug() . '$/', 'wp_addon_bbpress/user/subscriptions');
		$router->addRoute($baseUserRoute . '\/edit$/', 'wp_addon_bbpress/user/edit');
		
		return $this;
	}

	/**
	 * Add the JS/CSS elements
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */	
	protected function _getHeadFooterContent()
	{
		global $wp_styles, $wp_scripts;
		
		if (!$wp_styles || !$wp_scripts) {
			return false;
		}

		$wp_styles->do_concat = true;
		$wp_scripts->do_concat = true;	
		$jsCssFiles = array();

		foreach($wp_styles->registered as $style) {
			if (strpos($style->src, 'bbpress') !== false) {
				$wp_styles->print_html = '';

				if ($wp_styles->do_item($style->handle)) {
					$jsCssFiles[] = $wp_styles->print_html;
				}
			}
		}

		foreach($wp_scripts->registered as $script) {
			if (strpos($script->src, 'bbpress') !== false) {
				$wp_scripts->print_html = '';

				if ($wp_scripts->do_item($script->handle)) {
					if ($extra = $wp_scripts->print_extra_script($script->handle, false)) {
						$jsCssFiles[] = "<script type='text/javascript'>" . $extra . "</script>";
					}
					
					$jsCssFiles[] = $wp_scripts->print_html;
				}
			}
		}

		$this->_addToHeadFooterContent($jsCssFiles);
		
		return true;
	}
	
	/**
	 * Allows the controller to force JS/CSS inclusion
	 *
	 * @return $this
	 **/
	public function enableHeadFooterIncludes()
	{
		self::$_shortcodeIncluded = true;
		
		return $this;
	}
	
	/**
	 * @return array|false
	 **/
	public function getFilesToPatch()
	{
		if ($pluginDir = $this->_getPluginDir()) {
			$pluginDir = Mage::helper('wordpress')->getWordPressPath() . 'wp-content' . DS . 'plugins' . DS . 'bbpress' . DS;		

			return array(
				$pluginDir . 'includes' . DS . 'common' . DS . 'functions.php' => '_translationPatch',
				$pluginDir . 'includes' . DS . 'common' . DS . 'template.php' => '_translationPatch',
				$pluginDir . 'includes' . DS . 'extend' . DS . 'akismet.php' => '_translationPatch',
				$pluginDir . 'includes' . DS . 'replies' . DS . 'functions.php' => '_translationPatch',
				$pluginDir . 'includes' . DS . 'search' . DS . 'template.php' => '_translationPatch',
				$pluginDir . 'includes' . DS . 'topics' . DS . 'functions.php' => '_translationPatch',
				$pluginDir . 'includes' . DS . 'topics' . DS . 'functions.php' => '_translationPatch',
				$pluginDir . 'includes' . DS . 'topics' . DS . 'template.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'bbpress' . DS . 'form-forum.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'bbpress' . DS . 'form-reply-move.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'bbpress' . DS . 'form-reply.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'bbpress' . DS . 'form-topic-tag.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'bbpress' . DS . 'form-topic.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'bbpress' . DS . 'loop-search-forum.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'bbpress' . DS . 'user-details.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'bbpress' . DS . 'user-profile.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'extras' . DS . 'taxonomy-topic-tag-edit.php' => '_translationPatch',
				$pluginDir . 'templates' . DS . 'default' . DS . 'extras' . DS . 'taxonomy-topic-tag.php' => '_translationPatch',
			);
		}

		return false;
	}
}
