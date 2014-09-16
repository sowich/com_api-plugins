<?php
defined('_JEXEC') or die( 'Restricted access' );
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

jimport('joomla.user.user');
jimport( 'simpleschema.category' );
jimport( 'simpleschema.person' );
jimport( 'simpleschema.blog.post' );

require_once( JPATH_ROOT . '/components/com_easyblog/constants.php' );
require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'date.php' );
require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'helper.php' );
require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'string.php' );
require_once( EBLOG_CLASSES . DIRECTORY_SEPARATOR . 'adsense.php' );

class EasyblogApiResourceCategory extends ApiResource
{

	public function __construct( &$ubject, $config = array()) {
		
		parent::__construct( $ubject, $config = array() );
		$easyblog 	= JPATH_ROOT . '/administrator/components/com_easyblog/easyblog.php';
		if (!JFile::exists($easyblog)) {
			$this->plugin->setResponse( $this->getErrorResponse(404, 'Easyblog not installed') );
			return;
		}
		require_once( JPATH_ROOT . '/components/com_easyblog/helpers/helper.php' );
	}
	public function delete()
	{    	
   	   $this->plugin->setResponse( 'in delete' ); 
	}

	public function post()
	{    	
   	   $this->plugin->setResponse( 'in post' ); 
	}
	
	public function get() {
		$input = JFactory::getApplication()->input;
		$model = EasyBlogHelper::getModel( 'Blog' );
		$category = EasyBlogHelper::getTable( 'Category', 'Table' );
		$id = $input->get('id', null, 'INT');
		$search = $input->get('search', null, 'STRING');
		$posts = array();
		
		$category->load($id);

		// private category shouldn't allow to access.
		$privacy	= $category->checkPrivacy();
		
		if(! $privacy->allowed )
		{
			$this->plugin->setResponse( $this->getErrorResponse(404, 'Category not found') );
			return;
		}
		
		$catIds     = array();
		$catIds[]   = $category->id;
		EasyBlogHelper::accessNestedCategoriesId($category, $catIds);

		$sorting	= $this->plugin->params->get( 'sorting' , 'latest' );
		$total 		= (int) $this->plugin->params->get( 'total' , 20 );
		$rows 		= $model->getBlogsBy( 'category' , $catIds , $sorting , $total, EBLOG_FILTER_PUBLISHED, $search );
		
		foreach ($rows as $k => $v) {
			$item = EasyBlogHelper::getHelper( 'SimpleSchema' )->mapPost($v, '', 100, array('text'));
			$posts[] = $item;
		}
		
		$this->plugin->setResponse( $posts );
	}
	
}