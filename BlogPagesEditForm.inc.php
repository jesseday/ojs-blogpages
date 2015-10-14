<?php

/**
 * @file BlogPagesSettingsForm.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.blogPages
 * @class BlogPagesSettingsForm
 *
 * Form for journal managers to view and modify blog pages
 *
 */

import('lib.pkp.classes.form.Form');

class BlogPagesEditForm extends Form {
	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/** @var $blogPageId **/
	var $blogPageId;

	/** $var $errors string */
	var $errors;

	/**
	 * Constructor
	 * @param $journalId int
	 */
	function BlogPagesEditForm(&$plugin, $journalId, $blogPageId = null) {

		parent::Form($plugin->getTemplatePath() . 'editBlogPageForm.tpl');

		$this->journalId = $journalId;
		$this->plugin =& $plugin;
		$this->blogPageId = isset($blogPageId)? (int) $blogPageId: null;

		$this->addCheck(new FormValidatorCustom($this, 'pagePath', 'required', 'plugins.generic.blogPages.duplicatePath', array(&$this, 'checkForDuplicatePath'), array($journalId, $blogPageId)));
		$this->addCheck(new FormValidatorPost($this));

	}

	/**
	 * Custom Form Validator for PATH to ensure no duplicate PATHs are created
	 * @param $pagePath String the PATH being checked
	 * @param $journalId int
	 * @param $blogPageId int
	 */
	function checkForDuplicatePath($pagePath, $journalId, $blogPageId) {
		$blogPageDao =& DAORegistry::getDAO('BlogPagesDAO');

		return !$blogPageDao->duplicatePathExists($pagePath, $journalId, $blogPageId);
	}

	/**
	 * Initialize form data from current group group.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;

		// add the tiny MCE script
		$this->addTinyMCE();

		if (isset($this->blogPageId)) {
			$blogPageDao =& DAORegistry::getDAO('BlogPagesDAO');
			$blogPage =& $blogPageDao->getBlogPage($this->blogPageId);

			if ($blogPage != null) {
				$this->_data = array(
					'blogPageId' => $blogPage->getId(),
					'pagePath' => $blogPage->getPath(),
					'title' => $blogPage->getTitle(null),
					'content' => $blogPage->getContent(null)
				);
			} else {
				$this->blogPageId = null;
			}
		}
	}

	function addTinyMCE() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;
		$templateMgr =& TemplateManager::getManager();

		// Enable TinyMCE with specific params
		$additionalHeadData = $templateMgr->get_template_vars('additionalHeadData');

		import('classes.file.JournalFileManager');
		$publicFileManager = new PublicFileManager();
		$tinyMCE_script = '
		<script language="javascript" type="text/javascript" src="'.Request::getBaseUrl().'/'.TINYMCE_JS_PATH.'/tiny_mce.js"></script>
		<script language="javascript" type="text/javascript">
			tinyMCE.init({
			mode : "textareas",
			plugins : "safari,spellchecker,style,layer,table,save,advhr,jbimages,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,pagebreak,jbimages",
			theme_advanced_buttons1_add : "fontsizeselect",
			theme_advanced_buttons2_add : "separator,preview,separator,forecolor,backcolor",
			theme_advanced_buttons2_add_before: "search,replace,separator",
			theme_advanced_buttons3_add_before : "tablecontrols,separator",
			theme_advanced_buttons3_add : "media,separator",
			theme_advanced_buttons4 : "cut,copy,paste,pastetext,pasteword,separator,styleprops,|,spellchecker,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,print,separator",
			theme_advanced_disable: "styleselect",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			relative_urls : false,
			document_base_url : "'. Request::getBaseUrl() .'/'.$publicFileManager->getJournalFilesPath($journalId) .'/",
			theme : "advanced",
			theme_advanced_layout_manager : "SimpleLayout",
			extended_valid_elements : "span[*], div[*]",
			spellchecker_languages : "+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv"
			});
		</script>';

		$templateMgr->assign('additionalHeadData', $additionalHeadData."\n".$tinyMCE_script);

	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('blogPageId', 'pagePath', 'title', 'content'));
	}

	/**
	 * Get the names of localized fields
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'content');
	}

	/**
	 * Save page into DB
	 */
	function save() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;

		$plugin->import('BlogPage');
		$blogPagesDao =& DAORegistry::getDAO('BlogPagesDAO');
		if (isset($this->blogPageId)) {
			$blogPage =& $blogPagesDao->getBlogPage($this->blogPageId);
		}

		if (!isset($blogPage)) {
			$blogPage = new BlogPage();
		}

		$blogPage->setJournalId($journalId);
		$blogPage->setPath($this->getData('pagePath'));

		$blogPage->setTitle($this->getData('title'), null);		// Localized
		$blogPage->setContent($this->getData('content'), null);	// Localized

		if (isset($this->blogPageId)) {
			$blogPagesDao->updateBlogPage($blogPage);
		} else {
			$blogPagesDao->insertBlogPage($blogPage);
		}
	}

	function display() {
		$templateMgr =& TemplateManager::getManager();

		parent::display();
	}

}
?>
