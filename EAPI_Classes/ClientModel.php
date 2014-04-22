<?php
// 20's error codes
class ClientModel
{

    const CLIENT_OBJECT = <<<SQL
select distinct c.zUID as ClientId, 
                c."Client Name" as ClientName, 
                c.Status, 
                --c.Type, 
				c."Contact Address1" as Address1,
				c."Contact Address2" as Address2,
				c."Contact City" as City,
				c."Contact State" as State,
				c."Contact Zip" as Zipcode,
				c."Office Phone" as Phone,
                br.zUid BrandId,
                br.BrandName
    from "Clients" c
    left join "zBrands.Table" br
        on c.zUid = br.zKeyF_ClientUID
    where c.zUid = ?
SQL;

	const ALL_CLIENTS = <<<SQL
    select distinct 
        c.zUID as ClientId,
        c."Client Name" as ClientName,
        c.Status,
		c.Type,
        c."Contact Address1" as Address1, 
        c."Contact Address2" as Address2, 
        c."Contact City" as City,
        c."Contact State" as State, 
        c."Contact Zip" as Zipcode, 
        c."Office Phone" as Phone, 
        br.zUid BrandId,
        br.BrandName

    from "Clients" c
        left join "zBrands.Table" br
            on c.zUid = br.zKeyF_ClientUID

    order by c.zUid, br.zUid
SQL;

	public function get_by_dates($start_date, $end_date)
	{
		throw new Exception("use get_all(), this doesnt work");
		
		include "./EAPI_Classes/BrandModel.php";
		
		if (!$end_date)
		{
			$end_date = date("Y-m-d");
		}
		
		$sql = sprintf(BrandModel::BRAND_DATE, $start_date, $end_date);
		$id_collection = abstraction_get_ids("-800", $sql);
		
		
		$results = array();
		foreach($id_collection as $client_id)
		{
			$check = $this->get_by_id($brand_id);
			if ($check != null)
			{
				$results[] = $check;
			}
		}
		return $results;
	}

	public function get_all() {
        $main_query_array = abstraction_query_multi_array(
				"-802", ClientModel::ALL_CLIENTS);
				
		$results = array();

		$id_on = "";
		$buffer = array();
		foreach($main_query_array as $client_part) {
			if ($client_part["ClientId"] != $id_on) {
				if (sizeof($buffer) != 0) {
					$results[] = $this->convolve_single($buffer);
				}
				$buffer = array();
				$id_on = $client_part["ClientId"];
			}

			$buffer[] = $client_part;
		}

		$results[] = $this->convolve_single($buffer);
				
		return $results;
	}
	
	private function convolve_single($parts) {
		$base_row = $parts[0];
		$result = array();
		$result["ClientId"]  	= (int) $base_row["ClientId"];
		$result["ClientName"]	= utf8_encode_all($base_row["ClientName"]);
		$result["Status"]		= utf8_encode_all($base_row["Status"]);
		$result["Address1"]		= utf8_encode_all($base_row["Address1"]);
		$result["Address2"]		= utf8_encode_all($base_row["Address2"]);
		$result["City"]			= utf8_encode_all($base_row["City"]);
		$result["State"]		= utf8_encode_all($base_row["State"]);
		$result["Zipcode"]		= utf8_encode_all($base_row["Zipcode"]);
		$result["Phone"]		= utf8_encode_all($base_row["Phone"]);
		
		if (strpos($base_row["Type"], "BEN") === false) {
			$result["Status"] = "InActive";
		}
		
		$brands = array();
		foreach($parts as $row) {
			$brand = new stdClass();
			$brand->BrandId = (int) $row["BrandId"];
			$brand->BrandName = $row["BrandName"];
			$brands[] = $brand;
		}
		
		$result["Brands"] = $brands;
		return $result;
	}
    
    public function get_by_id($id)
    {
		throw new Exception("use get_all(), this doesnt work");
        global $connection;
        $query_statement = odbc_prepare($connection, ClientModel::CLIENT_OBJECT);
        if(!odbc_execute($query_statement, array($id)))
        {
            $code = odbc_error($connection);
            $msg = odbc_errormsg($connection);
            build_error_and_die("unable to run queries, odbc error: ".
                          "[error code: $code] $msg", "-21");
        }
        
        $query_object = odbc_fetch_object($query_statement);
        if ($query_object === false)
        {
            //build_error_and_die("object not found [client:$id]", "-22");
			return NULL;
        }
        
        $result = new stdClass();
        $result->ClientId = $query_object->ClientId;
        $result->ClientName = $query_object->ClientName;
        //$result->Type = $query_object->Type;
        $result->Status = $query_object->Status;
		$result->Address1 = $query_object->Address1;
		$result->Address2 = $query_object->Address2;
		$result->City = $query_object->City;
		$result->State = $query_object->State;
		$result->Zipcode = $query_object->Zipcode;
		$result->Phone = $query_object->Phone;
        $result->Categories = array();
        
        do
        {
            if ($query_object->BrandId != -1)
            {
                $brand = new stdClass();
                $brand->BrandId = (int)$query_object->BrandId;
                $brand->BrandName = ($query_object->BrandName)
                        ? $query_object->BrandName
                        : "Unknown Brand ($query_object->BrandId)";
                $result->Brands[] = $brand;
            }
        }
        while(($query_object = odbc_fetch_object($query_statement)) != false);

        return $result;
    }
}
