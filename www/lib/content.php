<?php
require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");

class Content 
{
	public $id = '';
	public $title = '';
	public $url = '';
	public $body = '';
	public $metadescription = '';
	public $metakeywords = '';
	public $contenttype  = '';
	public $showinmenu = '';
	public $status = '';
	public $ordinal = '';
	public $createddate = '';
}

class Contents
{	
	const TYPEUSEFUL = 1;
	const TYPEARTICLE = 2;
	const TYPEINDEX = 3;
	
	public function get()
	{
		$arr = array();
		$rows = $this->data_get();
		if ($rows === false)
			return false;
				
		foreach($rows as $row)
			$arr[] = $this->row2Object($row);
		
		return $arr; 		
	}

	public function getAll()
	{
		$arr = array();
		$rows = $this->data_getAll();
		if ($rows === false)
			return false;

		foreach($rows as $row)
			$arr[] = $this->row2Object($row);
		
		return $arr; 		
	}
	
	public function getAllNoIndex()
	{
		$arr = array();
		$rows = $this->data_getAllNoIndex();
		if ($rows === false)
			return false;

		foreach($rows as $row)
			$arr[] = $this->row2Object($row);
		
		return $arr; 		
	}	
	
	public function getForMenuByType($id)
	{		

		$arr = array();
		$rows = $this->data_getForMenuByType($id);
		if ($rows === false)
			return false;
						
		foreach($rows as $row)
			$arr[] = $this->row2Object($row);

		return $arr; 
	}		
	
	public function getIndex()
	{		
		$row = $this->data_getIndex();
		if ($row === false)
			return false;
				
		return $this->row2Object($row);
	}	

	public function getByID($id)
	{		
		$row = $this->data_getByID($id);
		if ($row === false)
			return false;
				
		return $this->row2Object($row);
	}	

	public function validate($content)
	{
		if (substr($content->url,0,1) != '/')
		{
			$content->url = "/".$content->url;
		}
		
    if (substr($content->url, strlen($content->url) - 1) != '/')
		{
			$content->url = $content->url."/";
		}
		
		return $content;
	}

	public function add($form)
	{		
		$content = $this->row2Object($form);
		$content = $this->validate($content);
		return $this->data_add($content);
	}	
	
	public function delete($id)
	{		
		return $this->data_delete($id);
	}	

	public function update($form)
	{		
		$content = $this->row2Object($form);
		$content = $this->validate($content);
		$this->data_update($content);
		
		return $content;
	}	
		
	public function row2Object($row, $prefix="")
	{	
		$obj = new Content();
		if (isset($row[$prefix."id"]))
			$obj->id = $row[$prefix."id"];
		$obj->title = $row[$prefix."title"];
		$obj->url = $row[$prefix."url"];
		$obj->body = $row[$prefix."body"];
		$obj->metadescription = $row[$prefix."metadescription"];
		$obj->metakeywords = $row[$prefix."metakeywords"];
		$obj->contenttype = $row[$prefix."contenttype"];
		$obj->showinmenu = $row[$prefix."showinmenu"];		
		$obj->status = $row[$prefix."status"];		
		$obj->ordinal = $row[$prefix."ordinal"];	
		if (isset($row[$prefix."createddate"]))
			$obj->createddate = $row[$prefix."createddate"];				
		return $obj;
	}

	public function data_update($content)
	{		
		$db = new DB();
		return $db->query(sprintf("update content set	title = %s , 	url = %s , 	body = %s , 	metadescription = %s , 	metakeywords = %s , 	contenttype = %d , 	showinmenu = %d , 	status = %d , 	ordinal = %d	where	id = %d ", $db->escapeString($content->title), $db->escapeString($content->url), $db->escapeString($content->body), $db->escapeString($content->metadescription), $db->escapeString($content->metakeywords), $content->contenttype, $content->showinmenu, $content->status, $content->ordinal, $content->id ));
	}

	public function data_add($content)
	{		
		$db = new DB();
		return $db->queryInsert(sprintf("insert into content 	(title, 	url, 	body, 	metadescription, 	metakeywords, 	contenttype, 	showinmenu, 	status, 	ordinal, createddate	)	values	(%s, 	%s, 	%s, 	%s, 	%s, 	%d, 	%d, 	%d, 	%d , now()	)", $db->escapeString($content->title),  $db->escapeString($content->url),  $db->escapeString($content->body),  $db->escapeString($content->metadescription),  $db->escapeString($content->metakeywords), $content->contenttype, $content->showinmenu, $content->status, $content->ordinal ));
	}

	public function data_get()
	{		
		$db = new DB();
		return $db->query(sprintf("select * from content where status = 1 order by contenttype, coalesce(ordinal, 1000000)"));		
	}	
	
	public function data_getAll()
	{		
		$db = new DB();
		return $db->query(sprintf("select * from content order by contenttype, coalesce(ordinal, 1000000)"));		
	}	
	
	public function data_getAllNoIndex()
	{		
		$db = new DB();
		return $db->query(sprintf("select * from content where status=1 and contenttype != 3 order by createddate desc"));		
	}	
	
	public function data_delete($id)
	{		
		$db = new DB();
		return $db->query(sprintf("delete from content where id=%d", $id));		
	}	

	public function data_getByID($id)
	{		
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from content where id = %d", $id));		
	}		
	
	public function data_getIndex()
	{		
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from content where status=1 and contenttype = %d ", Contents::TYPEINDEX));		
	}		

	public function data_getForMenuByType($id)
	{		
		$db = new DB();
		return $db->query(sprintf("select * from content where showinmenu=1 and status=1 and contenttype = %d ", $id));		
	}		
}
?>
