<?php
/**
 * @file plugins/generic/blogPages/BlogPagesDAO.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.blogPages
 * @class BlogPagesDAO
 *
 * Operations for retrieving and modifying BlogPages objects.
 *
 */
import('lib.pkp.classes.db.DAO');

class BlogPagesDAO extends DAO {
	/** @var $parentPluginName Name of parent plugin */
	var $parentPluginName;

	/**
	 * Constructor
	 */
	function BlogPagesDAO($parentPluginName) {
		$this->parentPluginName = $parentPluginName;
		parent::DAO();
	}

	function getBlogPage($blogPageId) {
		$result =& $this->retrieve(
			'SELECT * FROM blog_pages WHERE blog_page_id = ?', $blogPageId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnBlogPageFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	function &getBlogPagesByJournalId($journalId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM blog_pages WHERE journal_id = ?', $journalId, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnBlogPageFromRow');
		return $returner;
	}

	function getBlogPageByPath($journalId, $path) {
		$result =& $this->retrieve(
			'SELECT * FROM blog_pages WHERE journal_id = ? AND path = ?', array($journalId, $path)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnBlogPageFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	function insertBlogPage(&$blogPage) {
		$timestamp = $_SERVER['REQUEST_TIME'];
    $this->update(
			'INSERT INTO blog_pages
				(journal_id, path, blog_content, date_published, date_updated)
				VALUES
				(?, ?, ?, ?, ?)',
			array(
				$blogPage->getJournalId(),
				$blogPage->getPath(),
        $blogPage->getBlogPageContent(),
        date('Y-m-j H:I:s', $timestamp),
        date('Y-m-j H:I:s', $timestamp),
			)
		);

		$blogPage->setId($this->getInsertBlogPageId());
		$this->updateLocaleFields($blogPage);

		return $blogPage->getId();
	}

	function updateBlogPage(&$blogPage) {
		$timestamp = $_SERVER['REQUEST_TIME'];
    $returner = $this->update(
			'UPDATE blog_pages
				SET
					journal_id = ?,
					path = ?,
					blog_content = ?,
					date_updated = ?
				WHERE blog_page_id = ?',
				array(
					$blogPage->getJournalId(),
					$blogPage->getPath(),
          $blogPage->getBlogPageContent(),
          date('Y-m-j H:I:s', $timestamp),
					$blogPage->getId(),
					)
			);
		$this->updateLocaleFields($blogPage);
		return $returner;
	}

	function deleteBlogPageById($blogPageId) {
		$returner = $this->update(
			'DELETE FROM blog_pages WHERE blog_page_id = ?', $blogPageId
		);
		return $this->update(
			'DELETE FROM blog_page_settings WHERE blog_page_id = ?', $blogPageId
		);
	}

	function &_returnBlogPageFromRow(&$row) {
		$blogPagesPlugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		$blogPagesPlugin->import('BlogPage');

		$blogPage = new BlogPage();
		$blogPage->setId($row['blog_page_id']);
		$blogPage->setPath($row['path']);
		$blogPage->setJournalId($row['journal_id']);

		$this->getDataObjectSettings('blog_page_settings', 'blog_page_id', $row['blog_page_id'], $blogPage);
		return $blogPage;
	}

	function getInsertBlogPageId() {
		return $this->getInsertId('blog_pages', 'blog_page_id');
	}

	/**
	 * Get field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'content');
	}

	/**
	 * Update the localized data for this object
	 * @param $author object
	 */
	function updateLocaleFields(&$blogPage) {
		$this->updateDataObjectSettings('blog_page_settings', $blogPage, array(
			'blog_page_id' => $blogPage->getId()
		));
	}

	/**
	 * Find duplicate path
	 * @param $path String
	 * @param journalId int
	 * @param $blogPageId	int
	 * @return boolean
	 */
	function duplicatePathExists ($path, $journalId, $blogPageId = null) {
		$params = array(
					$journalId,
					$path
					);
		if (isset($blogPageId)) $params[] = $blogPageId;

		$result = $this->retrieve(
			'SELECT *
				FROM blog_pages
				WHERE journal_id = ?
				AND path = ?' .
				(isset($blogPageId)?' AND NOT (blog_page_id = ?)':''),
				$params
			);

		if($result->RecordCount() == 0) {
			// no duplicate exists
			$returner = false;
		} else {
			$returner = true;
		}
		return $returner;
	}
}
?>
