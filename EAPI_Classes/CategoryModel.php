<?php
// 50's error codes
class CategoryModel
{
    const CATEGORY_IDS = <<<SQL
    select zUid as id
        from "Utility.Table"
        where BEN_Category is not null
SQL;
    
    const CATEGORY_OBJECT = <<<SQL
    select zUid as CategoryId, 
           BEN_Category as CategoryName 
        from "Utility.Table" 
        where zUid = ?
SQL;

	const CATEGORY_ALL = <<<SQL
    select  zUid as CategoryId, 
            BEN_Category as CategoryName 
		from "Utility.Table"
		where BEN_Category is not null
SQL;

	public function get_by_dates($start_date, $end_date) {
		throw new Exception("get_by_dates(start_date, end_date) Not Supported");
	}
	public function get_all()
	{
        global $connection;
        $query_statement = odbc_prepare($connection, CategoryModel::CATEGORY_ALL);
        if(!odbc_execute($query_statement))
        {
            $code = odbc_error($connection);
            $msg = odbc_errormsg($connection);
            build_error_and_die("unable to run queries, odbc error: ".
                          "[error code: $code] $msg", "-51");
        }
		
		$result = array();
		$query_object;
		while(($query_object = odbc_fetch_object($query_statement)) != false)
		{
			$obj = new stdClass();
			$obj->CategoryId = $query_object->CategoryId;
			$obj->CategoryName = $query_object->CategoryName;
			$result[] = $obj;
		}
        
		return $result;
	}
    
    public function get_all_ids()
    {
        return abstraction_get_ids("-50", CategoryModel::CATEGORY_IDS);
    }
    
    public function get_by_id($id)
    {
        global $connection;
        $query_statement = odbc_prepare($connection, CategoryModel::CATEGORY_OBJECT);
        if(!odbc_execute($query_statement, array($id)))
        {
            $code = odbc_error($connection);
            $msg = odbc_errormsg($connection);
            build_error_and_die("unable to run queries, odbc error: ".
                          "[error code: $code] $msg", "-51");
        }
        
        $query_object = odbc_fetch_object($query_statement);
        if ($query_object === false)
        {
            build_error_and_die("object not found [brand:$id]", "-52");
        }
        
        $result = new stdClass();
        
        $result->CategoryId = $query_object->CategoryId;
        $result->CategoryName = $query_object->CategoryName;

        return $result;
    }
}
