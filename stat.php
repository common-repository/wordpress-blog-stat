<?php
/*
Plugin Name: Wordpress Stat
Plugin URI: http://smallwebsitehost.com/wordpress-newsletter-plugin/wordpress
Description: This plugin is a simple referal stat. You can see where does your traffic come from. But it is still simple. In the future I will extend the feature. 
Version: 1.0
Autdor: Ian Sani
Autdor URI: http://www.smallwebsitehost.com/

    Copyright 2008  Ian sani (email : yulianto@solusiwebindo.com)

    tdis program is free software; you can redistribute it and/or modify
    it under tde terms of tde GNU General Public License as published by
    tde Free Software Foundation; eitder version 2 of tde License, or
    (at your option) any later version.

    tdis program is distributed in tde hope tdat it will be useful,
    but WItdOUT ANY WARRANTY; witdout even tde implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See tde
    GNU General Public License for more details.

    You should have received a copy of tde GNU General Public License
    along witd tdis program; if not, write to tde Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$wpnewsletter_db_version = "1.0";

class stat
{

	function stat() {
		add_action('activate_' . strtr(plugin_basename(__FILE__), '\\', '/'), array(&$this, 'stat_install'));
		add_action('init', array(&$this, 'save_stat'));
		add_action('admin_menu', array(&$this, 'set_menu'));
		$admin_stylesheet_url = get_option('siteurl') . '/wp-content/plugins/stat/css/stylesheet.css';
			echo '<link rel="stylesheet" href="' . $admin_stylesheet_url . '" type="text/css" />';
		
	}

	function stat_install()
	{
		global $wpdb;
		$wpdb->query("create table if not exists `" . $table_prefix . "refererstat` (id int(10) unsigned NOT NULL auto_increment, referer varchar(100),
statdate datetime,
count int,
 PRIMARY KEY(`id`), KEY `" . $table_prefix . "refererstatid` (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;
");
	
		$wpdb->query("create table if not exists `" . $table_prefix . "viewstat` (id int(10) unsigned NOT NULL auto_increment, view varchar(100),
statdate datetime,
count int,
 PRIMARY KEY(`id`), KEY `" . $table_prefix . "refererstatid` (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;
");
	}
	
	function save_stat()
	{
		global $wpdb;
		if(!is_admin())
		{
			$this->save_referer();
			$this->save_page_view();
		}
	}
	
	function save_referer()
	{
		global $wpdb;
		$referer = $_SERVER['HTTP_REFERER'];
		
		//check if data exist
		$curdate = date("Y-m-d");
		$row = $wpdb->get_row("select count(*) as data_count from " . $table_prefix . "refererstat where referer = '". $referer ."' and statdate = '" . $curdate ." 00:00:00'", ARRAY_A);
			
		if($row['data_count'] == 0)
		{
			//inser
			$query = "insert into " . $table_prefix . "refererstat (referer , statdate , count)  values ('" ;
			$query .= $referer . "','". $curdate . " 00:00:00',1);";
			$wpdb->query($query);				
		}
		else
		{
			//get row id
			$row = $wpdb->get_row("select id from " . $table_prefix . "refererstat where referer = '". $referer ." ' and statdate = '" . $curdate . " 00:00:00'", ARRAY_A);
			//update
			$query = "update " . $table_prefix . "refererstat set count =count + 1 where id = " . $row['id'];
			$wpdb->query($query);				
		}			
	}
	
	function save_page_view()
	{
		global $wpdb;
		$page = $_SERVER['REQUEST_URI'];
		
		//check if data exist
		$curdate = date("Y-m-d");
		$row = $wpdb->get_row("select count(*) as data_count from " . $table_prefix . "viewstat where view = '". $page ."' and statdate = '" . $curdate ." 00:00:00'", ARRAY_A);
			
		if($row['data_count'] == 0)
		{
			//inser
			$query = "insert into " . $table_prefix . "viewstat (view , statdate , count)  values ('" ;
			$query .= $page . "','". $curdate . " 00:00:00',1);";
			$wpdb->query($query);				
		}
		else
		{
			//get row id
			$row = $wpdb->get_row("select id from " . $table_prefix . "viewstat where view = '". $page ." ' and statdate = '" . $curdate . " 00:00:00'", ARRAY_A);
			//update
			$query = "update " . $table_prefix . "viewstat set count =count + 1 where id = " . $row['id'];
			$wpdb->query($query);				
		}			
	}
	
	function set_menu()
	{
		add_options_page('Stat', 'Stat', 6, __FILE__, array(&$this, 'stat_settings') );
	}
	
	function stat_settings()
	{
		?><div id=stat>
		<form action="">
		<a href="options-general.php?page=stat/stat.php&task=view">View page view</a> | <a href="options-general.php?page=stat/stat.php&task=referal">View page referal</a> | <a href="options-general.php?page=stat/stat.php&task=delete">Delete all data</a>
		</form></div>
		<?php
		$this->task($_GET['task']);
	}
	
	function show_page_view()
	{
		global $wpdb, $table_prefix;
	
		$curdate = date("Y-m-d");

		$row = $wpdb->get_results("select count(*) as count_view from " . $table_prefix . "viewstat where statdate = '" . $curdate . " 00:00:00'");

		echo("<div id=stat><h1>Today total view</h1>".$row[0]->count_view);
		echo("<h1>Today page view stat</h1>");
		
		//query

		$row = $wpdb->get_results("select * from " . $table_prefix . "viewstat where statdate = '" . $curdate . " 00:00:00' order by count desc");

		for($i=0;$i<count($row)-1;$i++)
		{
			echo("<li>".$row[$i]->view . " " .$row[$i]->count . "<br>");
		}
		echo("</div>");
	}
	
	function show_page_referal()
	{
		global $wpdb, $table_prefix;

		echo("<div id=stat><h1>Today referer stat</h1>");
		
		//query
		$curdate = date("Y-m-d");
		$row = $wpdb->get_results("select * from " . $table_prefix . "refererstat where statdate = '" . $curdate . " 00:00:00' order by count desc");

		for($i=0;$i<count($row)-1;$i++)
		{
			echo("<li>".$row[$i]->referer . " " .$row[$i]->count . "<br>");
		}
		echo("</div>");
	}
	
	function delete()
	{		
		global $wpdb, $table_prefix;
		echo ("<div id=stat>");

		$wpdb->query("delete from " . $table_prefix . "refererstat");
		$wpdb->query("delete from " . $table_prefix . "viewstat");

		echo("Done deleting");
		echo ("</div>");
	}
	
	function task($task)
	{
		switch($task)
		{
			case "view":
				$this->show_page_view();
				break;
			case "referal":
				$this->show_page_referal();
				break;
			case "delete":
				$this->delete();
				break;
		}
	}
}
$stat = new stat();
?>