<?php
// 60's error codes
class GenreModel
{
    const GENRE_IDS = <<<SQL
    select min(zUid) as id
        from "Utility.Table"
        where lower(Genre_Type) != 'music'
        group by Genre_Type,Genre_Group
    union all
    select zUid as id
        from "Utility.Table"
        where lower(Genre_Type) = 'music'
SQL;
    
    const GENRE_OBJECT = <<<SQL
    select zUid as GenreId,
           Genre_Group as GenreName1,
           Genre as GenreName2,
           Genre_Type as GenreType
        from "Utility.Table"
        where zUid = ?
SQL;

	const GENRE_ALL = <<<SQL
	
    select  min(zUid) as GenreId,
            Genre_Group as GenreName, 
            Genre_Type as GenreType

    from "Utility.Table"
    where Genre_Type like 'Production%'
    group by Genre_Type, Genre_Group


    union all


    select  zUid as GenreId, 
            Genre as GenreName, 
            Genre_Type as GenreType

    from "Utility.Table"
    where Genre_Type = 'Music'
	
SQL;
	/**********
	*
	*  Rex's get_all()
	*
	***********/
	// public function get_all()
	// {
		// $raw_results = abstraction_query_multi_array("-600", GenreModel::GENRE_ALL);
		
		// $productions = array();
		// $music = array();
		// $celebrities = array();
		// foreach($raw_results as $value)
		// {
			// if ($value["GenreType"] == "Music")
			// {
				// $music[] = $value["GenreName"];
			// }
			// else if (preg_match("/Production/", $value["GenreType"]))
			// {
				// $productions[] = $value["GenreName"];
			// }
		// }
		
		// if (array_key_exists("music", $_GET))
		// {
			// return $music;
		// }
		// else if (array_key_exists("productions", $_GET))
		// {
			// return $productions;
		// }
		// else if (array_key_exists("celebrities", $_GET))
		// {
			// return $celebrities;
		// }
		
		
		// if nothing else just return them all
		// $results = array();
		// $results["productions"] = $productions;
		// $results["music"] = $music;
		// $results["celebrities"] = $celebrities;
	
		
		// return $results;
	// }
	
	public function get_by_dates($start_date, $end_date) {
		throw new Exception("get_by_dates(start_date, end_date) Not Supported");
	}
	
	
	public function get_all() {
		$results = abstraction_query_multi_array("-600", GenreModel::GENRE_ALL);
		$returning = array();
		foreach($results as $obj)
		{
			$obj["GenreTypes"] = array();
			if ($obj["GenreType"])
			{
				$arraycheck = preg_split("/[\r\n]/m", $obj["GenreType"]);
				foreach($arraycheck as $check)
				{
					$check = trim($check);
					if ($check)
					{
						$obj["GenreTypes"][] = $check;
					}
				}
			}
			unset($obj["GenreType"]);
			$returning[] = $obj;
		}
		return $returning;
	}
    
    public function get_all_ids()
    {
        return abstraction_get_ids("-60", GenreModel::GENRE_IDS);
    }
    
    public function get_by_id($id)
    {
        global $connection;
        $query_statement = odbc_prepare($connection, GenreModel::GENRE_OBJECT);
        if(!odbc_execute($query_statement, array($id)))
        {
            $code = odbc_error($connection);
            $msg = odbc_errormsg($connection);
            build_error_and_die("unable to run queries, odbc error: ".
                          "[error code: $code] $msg", "-61");
        }
        
        $query_object = odbc_fetch_object($query_statement);
        if ($query_object === false)
        {
            build_error_and_die("object not found [brand:$id]", "-62");
        }
        
        $result = new stdClass();
        
        $result->GenreId = $query_object->GenreId;
        $result->GenreName = ($query_object->GenreName1) ? $query_object->GenreName1 : $query_object->GenreName2;
        $result->GenreType = array();
        if ($query_object->GenreType)
        {
            $arraycheck = preg_split("/[\r\n]/m", $query_object->GenreType);
            foreach($arraycheck as $check)
            {
                $check = trim($check);
                if ($check)
                {
                    $result->GenreType[] = $check;
                }
            }
        }

        return $result;
    }
}

