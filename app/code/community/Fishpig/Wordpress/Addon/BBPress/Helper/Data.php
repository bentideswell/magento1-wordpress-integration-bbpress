<?php
/**
 * @category Fishpig
 * @package Fishpig_Wordpress
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <ben@fishpig.co.uk>
 * @SkipObfuscation
 */

class Fishpig_Wordpress_Addon_BBPress_Helper_Data extends Mage_Core_Helper_Abstract
{
	/*
	 *
	 *
	 */
	public function getForumIndexHtml()
	{
		return $this->_doShortcode('[bbp-forum-index]');
	}

	/*
	 *
	 *
	 */
	public function getSingleForumHtml($forumId = 0)
	{
		return $this->_doShortcode(
			sprintf(
				'[bbp-single-forum id=%d]',
				(int)$forumId === 0 ? bbp_get_forum_id() : (int)$forumId
			)
		);
	}

	/*
	 *
	 *
	 */
	public function getSingleTopicHtml($topicId = 0)
	{
		return $this->_doShortcode(
			sprintf(
				'[bbp-single-topic id=%d]',
				(int)$topicId === 0 ? bbp_get_topic_id() : (int)$topicId
			)
		);
	}

	/*
	 *
	 *
	 */
	public function getSingleTopicTagHtml($topicTagId = 0)
	{
		return $this->_doShortcode(
			sprintf(
				'[bbp-single-tag id=%d]',
				(int)$topicTagId === 0 ? bbp_get_topic_tag_id() : (int)$topicTagId
			)
		);
	}

	/*
	 *
	 *
	 */
	public function getReplyEditHtml()
	{
		if ($reply = bbp_get_reply(null)) {
			return $this->_updateTextareaValue(
				$this->_doShortcode('[bbp-reply-form]'),
				'bbp_reply_content',
				$reply->post_content
			);
		}
		
		return false;
	}
	
	/*
	 *
	 *
	 */
	public function getReplyMoveHtml()
	{
		return $this->_doTemplate('templates' . DS . 'default' . DS . 'bbpress' . DS . 'form-reply-move.php');
	}

	/*
	 *
	 *
	 */
	public function getTopicEditHtml()
	{
		if ($topic = bbp_get_topic(null)) {
			$html = $this->_doTemplate('templates' . DS . 'default' . DS . 'bbpress' . DS . 'form-topic.php');
		
			return $this->_updateTextareaValue(
				$html,
				'bbp_topic_content',
				$topic->post_content
			);
		}
		
		return false;
	}
		
	/*
	 *
	 *
	 */
	public function getUserProfileHtml()
	{
		return $this->_getUserHtmlTemplate('user-profile.php');
	}

	/*
	 *
	 *
	 */
	public function getUserRepliesHtml()
	{
		return $this->_getUserHtmlTemplate('user-replies-created.php');
	}

	/*
	 *
	 *
	 */
	public function getUserFavouritesHtml()
	{
		return $this->_getUserHtmlTemplate('user-favorites.php');
	}

	/*
	 *
	 *
	 */
	public function getUserTopicsHtml()
	{
		return $this->_getUserHtmlTemplate('user-topics-created.php');
	}

	/*
	 *
	 *
	 */
	public function getUserSubscriptionsHtml()
	{
		return $this->_getUserHtmlTemplate('user-subscriptions.php');
	}
	
	/*
	 *
	 *
	 */
	public function getUserEditHtml()
	{
		return $this->_getUserHtmlTemplate('form-user-edit.php');
	}

	/*
	 *
	 *
	 */
	protected function _getUserHtmlTemplate($template)
	{
		if ($templateHtml = trim($this->_doTemplate('templates' . DS . 'default' . DS . 'bbpress' . DS . $template))) {
			return '<div id="bbpress-forums">'
				. '<div id="bbp-user-wrapper">' . 
					$this->_doTemplate('templates' . DS . 'default' . DS . 'bbpress' . DS . 'user-details.php')
					. '<div id="bbp-user-body">' . $templateHtml . '</div>'
				. '</div>'
			. '</div>';
		}
		
		return false;
	}
	
	public function getSearchIndexHtml()
	{
		return $this->_doTemplate('templates' . DS . 'default' . DS . 'bbpress' . DS . 'form-search.php');
	}
	
	public function getSearchResultsHtml()
	{
		return $this->_doTemplate('templates' . DS . 'default' . DS . 'bbpress' . DS . 'content-search.php');
	}
	
	/*
	 *
	 *
	 */
	protected function _updateTextareaValue($html, $id, $value)
	{
		if (preg_match_all('/(<textarea[^>]{0,}>).*(<\/textarea>)/Us', $html, $matches)) {
			foreach($matches[0] as $mkey => $match) {
				if (strpos($match, $id) !== false) {
					$html = str_replace(
						$match, 
						$matches[1][$mkey] . htmlentities($value) . $matches[2][$mkey], 
						$html
					);
				}
			}
		}
		
		return $html;
	}
	
	/*
	 *
	 *
	 */
	protected function _doTemplate($template)
	{
		$templateFile = Mage::helper('wordpress')->getWordPressPath() . DS . 'wp-content' . DS . 'plugins' . DS . 'bbpress' . DS . $template;
		
		if (!is_file($templateFile)) {
			return false;
		}
		
		$value = Mage::helper('wp_addon_bbpress/core')->simulatedCallback(
			function($templateFile) {
				ob_start();
			
				include($templateFile);

				// Fix HTML entity data parameters
				return str_replace(array('&#091;', '&#093;'), array('[', ']'), ob_get_clean());
			}, array($templateFile)
		);
		
		return $this->_processShortcodeHtml($value);
	}

	/*
	 *
	 *
	 */
	protected function _doShortcode($shortcode)
	{
		return Mage::helper('wp_addon_bbpress/core')->doShortcode($shortcode);
	}

	/*
	 *
	 *
	 */
	protected function _processShortcodeHtml($html)
	{
		if (strpos($html, '<button') !== false) {
			if (preg_match_all('/(<button[^>]{1,}>)(.*)(<\/button>)/Us', $html, $matches)) {
				foreach($matches[2] as $mkey => $label) {
					$html = str_replace(
						$matches[0][$mkey],
						$matches[1][$mkey] . '<span><span>' . $label . '</span></span>' . $matches[3][$mkey],
						$html
					);
				}
			}
		}
		
		return $html;
	}
	
	/*
	 *
	 *
	 */
	public function getRootSlug()
	{
		return $this->_getOption('_bbp_root_slug', 'forums');
	}

	/*
	 *
	 *
	 */
	public function getForumSlug()
	{
		return $this->_getOption('_bbp_forum_slug', 'forum');
	}
	
	/*
	 *
	 *
	 */
	public function getTopicSlug()
	{
		return $this->_getOption('_bbp_topic_slug', 'topic');
	}
	
	/*
	 *
	 *
	 */
	public function getTopicTagSlug()
	{
		return $this->_getOption('_bbp_topic_tag_slug', 'topic-tag');
	}
	
	/*
	 *
	 *
	 */
	public function getTopicViewSlug()
	{
		return $this->_getOption('_bbp_topic_view_slug', 'view');
	}

	/*
	 *
	 *
	 */
	public function getReplySlug()
	{
		return $this->_getOption('_bbp_reply_slug', 'reply');
	}
	
	/*
	 *
	 *
	 */
	public function getSearchSlug()
	{
		return $this->_getOption('_bbp_search_slug', 'search');
	}
	
	/*
	 *
	 *
	 */
	public function getUserRootSlug()
	{
		return $this->_getOption('_bbp_user_slug', 'users');
	}
	
	/*
	 *
	 *
	 */
	public function getUserTopicsSlug()
	{
		return $this->_getOption('_bbp_topic_archive_slug', 'topics');
	}
	
	/*
	 *
	 *
	 */
	public function getUserRepliesSlug()
	{
		return $this->_getOption('_bbp_reply_archive_slug', 'replies');
	}
	
	/*
	 *
	 *
	 */
	public function getUserFavouritesSlug()
	{
		return $this->_getOption('_bbp_user_favs_slug', 'favourites');
	}
	
	/*
	 *
	 *
	 */
	public function getUserSubscriptionsSlug()
	{
		return $this->_getOption('_bbp_user_subs_slug', 'subscriptions');
	}
	
	/*
	 *
	 *
	 */
	public function urlsPrefixedWithForumRootSlug()
	{
		return (int)$this->_getOption('_bbp_include_root', 0) === 1;
	}	
	
	/*
	 *
	 *
	 */
	protected function _getOption($key, $default = null)
	{
		return Mage::helper('wordpress')->getWpOption($key, $default);
	}
}
