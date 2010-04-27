<?php
require_once($_SERVER['DOCUMENT_ROOT']."/lib/framework/db.php");

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
		if (isset($row[$prefix."ID"]))
			$obj->id = $row[$prefix."ID"];
		$obj->title = $row[$prefix."TITLE"];
		$obj->url = $row[$prefix."URL"];
		$obj->body = $row[$prefix."BODY"];
		$obj->metadescription = $row[$prefix."METADESCRIPTION"];
		$obj->metakeywords = $row[$prefix."METAKEYWORDS"];
		$obj->contenttype = $row[$prefix."CONTENTTYPE"];
		$obj->showinmenu = $row[$prefix."SHOWINMENU"];		
		$obj->status = $row[$prefix."STATUS"];		
		$obj->ordinal = $row[$prefix."ORDINAL"];	
		if (isset($row[$prefix."CREATEDDATE"]))
			$obj->createddate = $row[$prefix."CREATEDDATE"];				
		return $obj;
	}

	public function data_update($content)
	{		
		$db = new DB();
		return $db->query(sprintf("UPDATE content SET	TITLE = %s , 	URL = %s , 	BODY = %s , 	METADESCRIPTION = %s , 	METAKEYWORDS = %s , 	CONTENTTYPE = %d , 	SHOWINMENU = %d , 	STATUS = %d , 	ORDINAL = %d	WHERE	ID = %d ", $db->escapeString($content->title), $db->escapeString($content->url), $db->escapeString($content->body), $db->escapeString($content->metadescription), $db->escapeString($content->metakeywords), $content->contenttype, $content->showinmenu, $content->status, $content->ordinal, $content->id ));
	}

	public function data_add($content)
	{		
		$db = new DB();
		return $db->queryInsert(sprintf("INSERT INTO content 	(TITLE, 	URL, 	BODY, 	METADESCRIPTION, 	METAKEYWORDS, 	CONTENTTYPE, 	SHOWINMENU, 	STATUS, 	ORDINAL, CREATEDDATE	)	VALUES	(%s, 	%s, 	%s, 	%s, 	%s, 	%d, 	%d, 	%d, 	%d , now()	)", $db->escapeString($content->title),  $db->escapeString($content->url),  $db->escapeString($content->body),  $db->escapeString($content->metadescription),  $db->escapeString($content->metakeywords), $content->contenttype, $content->showinmenu, $content->status, $content->ordinal ));
	}

	public function data_get()
	{		
		$db = new DB();
		return $db->query(sprintf("SELECT * FROM CONTENT WHERE STATUS = 1 ORDER BY contenttype, COALESCE(ORDINAL, 1000000)"));		
	}	
	
	public function data_getAll()
	{		
		$db = new DB();
		return $db->query(sprintf("SELECT * FROM CONTENT ORDER BY contenttype, COALESCE(ORDINAL, 1000000)"));		
	}	
	
	public function data_getAllNoIndex()
	{		
		$db = new DB();
		return $db->query(sprintf("SELECT * FROM CONTENT WHERE STATUS=1 AND CONTENTTYPE != 3 ORDER BY CREATEDDATE DESC"));		
	}	
	
	public function data_delete($id)
	{		
		$db = new DB();
		return $db->query(sprintf("DELETE FROM CONTENT WHERE ID=%d", $id));		
	}	

	public function data_getByID($id)
	{		
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT * FROM CONTENT WHERE ID = %d", $id));		
	}		
	
	public function data_getIndex()
	{		
		$db = new DB();
		return $db->queryOneRow(sprintf("SELECT * FROM CONTENT WHERE STATUS=1 AND CONTENTTYPE = %d ", Contents::TYPEINDEX));		
	}		

	public function data_getForMenuByType($id)
	{		
		$db = new DB();
		return $db->query(sprintf("SELECT * FROM CONTENT WHERE SHOWINMENU=1 AND STATUS=1 AND CONTENTTYPE = %d ", $id));		
	}		
}
?>