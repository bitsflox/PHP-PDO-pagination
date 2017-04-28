<?php
/**
 * PHPSense Pagination Class
 *
 * PHP tutorials and scripts
 *
 * @package		PHPSense
 * @author		Jatinder Singh Thind
 * @copyright	Copyright (c) 2006, Jatinder Singh Thind
 * @link		http://www.phpsense.com
 */

// ------------------------------------------------------------------------


class PS_Pagination {
	var $php_self;
	var $rows_per_page = 10; //Number of records to display per page
	var $total_rows = 0; //Total number of rows returned by the query
	var $links_per_page = 5; //Number of links to display per page
	var $append = ""; //Paremeters to append to pagination links
	var $sql = "";
	var $debug = false;
	var $conn = false;
	var $page = 1;
	var $max_pages = 0;
	var $offset = 0;
	
	/**
	 * Constructor
	 *
	 * @param resource $connection Mysql connection link
	 * @param string $sql SQL query to paginate. Example : SELECT * FROM users
	 * @param integer $rows_per_page Number of records to display per page. Defaults to 10
	 * @param integer $links_per_page Number of links to display per page. Defaults to 5
	 * @param string $append Parameters to be appended to pagination links 
	 */
	
	function PS_Pagination($connection, $sql, $rows_per_page = 10, $links_per_page = 5, $append = "") {
		$this->conn = $connection;
		$this->sql = $sql;
		$this->rows_per_page = (int)$rows_per_page;
		if (intval($links_per_page ) > 0) {
			$this->links_per_page = (int)$links_per_page;
		} else {
			$this->links_per_page = 5;
		}
		$this->append = $append;
		$this->php_self = htmlspecialchars($_SERVER['PHP_SELF'] );
		if (isset($_GET['page'] )) {
			$this->page = intval($_GET['page'] );
		}
	}
	
	/**
	 * Executes the SQL query and initializes internal variables
	 *
	 * @access public
	 * @return resource
	 */
	function paginate() {
		/*Check for valid mysql connection
		if (! $this->conn || ! is_resource($this->conn )) {
			if ($this->debug)
				echo "MySQL connection missing<br />";
			return false;
		}*/
		
	/*	//Find total number of rows
		$all_rs = @mysql_query($this->sql );
		if (! $all_rs) {
			if ($this->debug)
				echo "SQL query failed. Check your query.<br /><br />Error Returned: " . mysql_error();
			return false;
		}
		$this->total_rows = mysql_num_rows($all_rs );
		@mysql_close($all_rs );
		*/
		$this->total_rows=$this->sql;
		//Return FALSE if no rows found
		if ($this->total_rows == 0) {
			if ($this->debug)
				echo "<h3 style='
text-align: center;
color: gray;
width: 690px;
font-size: 47px;
margin: 90px 0 0 0;
font-family: trebuchet ms;
'>No Record Found</h3>";
			return FALSE;
		}
		
		//Max number of pages
		$this->max_pages = ceil($this->total_rows / $this->rows_per_page );
		if ($this->links_per_page > $this->max_pages) {
			$this->links_per_page = $this->max_pages;
		}
		
		//Check the page value just in case someone is trying to input an aribitrary value
		if ($this->page > $this->max_pages || $this->page <= 0) {
			$this->page = 1;
		}
		
		//Calculate Offset
		$this->offset = $this->rows_per_page * ($this->page - 1);
		
		/*//Fetch the required result set
		$rs = @mysql_query($this->sql . " LIMIT {$this->offset}, {$this->rows_per_page}" );
		if (! $rs) {
			if ($this->debug)
				echo "Pagination query failed. Check your query.<br /><br />Error Returned: " . mysql_error();
			return false;
		}*/
		return "";
	}
	
	/**
	 * Display the link to the first page
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to 'First'
	 * @return string
	 */
	function renderFirst($tag = 'First') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page == 1) {
			return '<b style=" float:none;" class="page_link">'.$tag.'</b>';
		} else {
			return '<a style=" float:none;" href="' . $this->php_self . '?page=1' . $this->append . '">' . $tag . '</a> ';
		}
	}
	
	/**
	 * Display the link to the last page
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to 'Last'
	 * @return string
	 */
	function renderLast($tag = 'Last') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page == $this->max_pages) {
			return '<b style=" float:none;" class="page_link">'.$tag.'</b>';
		} else {
			return ' <a style=" float:none;" href="' . $this->php_self . '?page=' . $this->max_pages . '' . $this->append . '">' . $tag . '</a>';
		}
	}
	
	/**
	 * Display the next link
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to '>>'
	 * @return string
	 */
	function renderNext($tag = '&gt;&gt;') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page < $this->max_pages) {
			return '<a  style=" float:none;" href="' . $this->php_self . '?page=' . ($this->page + 1) . '' . $this->append . '">' . $tag . '</a>';
		} else {
			return $tag;
		}
	}
	
	/**
	 * Display the previous link
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to '<<'
	 * @return string
	 */
	function renderPrev($tag = '&lt;&lt;') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page > 1) {
			return ' <a  style=" float:none;" href="' . $this->php_self . '?page=' . ($this->page - 1) . '' . $this->append . '">' . $tag . '</a>';
		} else {
			return " $tag";
		}
	}
	
	/**
	 * Display the page links
	 *
	 * @access public
	 * @return string
	 */
	function renderNav($prefix = '<b style=" float:none;" class="page_link">', $suffix = '</b>') {
		if ($this->total_rows == 0)
			return FALSE;
		
		$batch = ceil($this->page / $this->links_per_page );
		$end = $batch * $this->links_per_page;
		if ($end == $this->page) {
			//$end = $end + $this->links_per_page - 1;
		//$end = $end + ceil($this->links_per_page/2);
		}
		if ($end > $this->max_pages) {
			$end = $this->max_pages;
		}
		$start = $end - $this->links_per_page + 1;
		$links = '';
		
		for($i = $start; $i <= $end; $i ++) {
			if ($i == $this->page) {
				$links .= $prefix . " $i " . $suffix;
			} else {
				$links .= ' <a style=" float:none;" href="' . $this->php_self . '?page=' . $i . '' . $this->append . '">' . $i . '</a> ';
			}
		}
		
		return $links;
	}
	
	/**
	 * Display full pagination navigation
	 *
	 * @access public
	 * @return string
	 */
	function renderFullNav() {
		return "<table class='paglink' ><tr><td>".$this->renderFirst() . '&nbsp;' . $this->renderPrev() . '&nbsp;' . $this->renderNav() . '&nbsp;' . $this->renderNext() . '&nbsp;' . $this->renderLast()."</td></tr></table>";
	}
	
	/**
	 * Set debug mode
	 *
	 * @access public
	 * @param bool $debug Set to TRUE to enable debug messages
	 * @return void
	 */
	function setDebug($debug) {
		$this->debug = $debug;
	}
}
?>
<style type="text/css">
.linkpagg {
	clear: both;
	font-size: 12px;
	font-family: arial;
	margin: 10px 0 0 0;
	float: left;
	width: inherit;
}
.linkpagg a {
	padding: 4px 8px;
font-weight: bold;
color: white;
background: #679dff;
background: -moz-linear-gradient(top,  #679dff 0%, #5080d8 100%);
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#679dff), color-stop(100%,#5080d8));
background: -webkit-linear-gradient(top,  #679dff 0%,#5080d8 100%);
background: -o-linear-gradient(top,  #679dff 0%,#5080d8 100%);
background: -ms-linear-gradient(top,  #679dff 0%,#5080d8 100%);
background: linear-gradient(to bottom,  #679dff 0%,#5080d8 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#679dff', endColorstr='#5080d8',GradientType=0 );

margin-left: 4px;
border: 1px solid #5080d8 !important;
	
	
}
.linkpagg b {
	color: #333;	
	padding: 4px 7px;
	border: 1px solid rgb(202, 202, 202);
	margin-left: 4px;
	margin-right: 4px;
	font-weight: bold;
	
	
	background: #f4f4f4;
	background: -moz-linear-gradient(top, #f4f4f4 0%, #e5e5e5 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #f4f4f4), color-stop(100%, #e5e5e5));
	background: -webkit-linear-gradient(top, #f4f4f4 0%, #e5e5e5 100%);
	background: -o-linear-gradient(top, #f4f4f4 0%, #e5e5e5 100%);
	background: -ms-linear-gradient(top, #f4f4f4 0%, #e5e5e5 100%);
	background: linear-gradient(to bottom, #f4f4f4 0%, #e5e5e5 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f4f4f4', endColorstr='#e5e5e5', GradientType=0 );
}
</style>