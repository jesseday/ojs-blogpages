<?php

/**
 * @file plugins/generic/blogPages/BlogPagesHandler.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.blogPages
 * @class BlogPagesHandler
 *
 * Find the content and display the appropriate page
 *
 */

import('classes.handler.Handler');

class BlogPagesHandler extends Handler {
	function index( $args ) {
		Request::redirect(null, null, 'view', Request::getRequestedOp());
	}

	function view ($args) {
		if (count($args) > 0 ) {
			AppLocale::requireComponents(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_USER);
			$journal =& Request::getJournal();
			$journalId = $journal?$journal->getId():0;
			$path = $args[0];

			$blogPagesPlugin =& PluginRegistry::getPlugin('generic', STATIC_PAGES_PLUGIN_NAME);
			$templateMgr =& TemplateManager::getManager();

			$blogPagesDao =& DAORegistry::getDAO('BlogPagesDAO');
			$blogPage = $blogPagesDao->getBlogPageByPath($journalId, $path);

			if ( !$blogPage ) {
				Request::redirect(null, 'index');
			}

			// and assign the template vars needed
			$templateMgr->assign('title', $blogPage->getBlogPageTitle());
			$templateMgr->assign('content',  $blogPage->getBlogPageContent());
			$templateMgr->display($blogPagesPlugin->getTemplatePath().'content.tpl');
		}
	}
}

?>
