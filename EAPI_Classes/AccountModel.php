<?php
// 30's error codes
class AccountModel
{
	//Arguments: (CONTACT_BASE, SECURITY_BASE, start_date, end_date)
	const DATE_TEMPLATE = '
	%1$s
	WHERE zRecord_DateModified BETWEEN {d \'%3$s\'} AND {d \'%4$s\'}
	
	UNION
	
	%2$s
	WHERE u.zDate_Modified BETWEEN {d \'%3$s\'} AND {d \'%4$s\'}
	
	';
	
	 //Argument: (DATE_TEMPLATE)
	const DATE_TEMPLATE_ABSTRACT = <<<SQL
	%s
SQL;

	//Arguments: (CONTACT_BASE, SECURITY_BASE)
	const ALL_TEMPLATE = <<< SQL
	%s

	UNION
	
	%s
SQL;
    
	//Arguments: (CONTACT_BASE, id)
	const CONTACT_ID_TEMPLATE = <<<SQL
	%s
	where c.zzUid = %s
SQL;
	
	//Arguments: (SECURITY_BASE, id)
	const SECURITY_ID_TEMPLATE = <<<SQL
	%s
	where numval(u."zUID_t") = %s
SQL;
    
    const CONTACT_BASE = "
select --AccountFlags
	   'CON' || c.zzUid as ContactId, 
       '' as EmployeeId, 
	   '' as AccountId, --placeholder
	   '' as Brands, --placeholder
	   '' as Client, --placeholder
	   c.ContactEmailAddress as Email,
       c.\"First Name\" as FirstName, 
       c.\"Last Name\" as LastName,
       c.Name_Middle_Initial as MI, 
	   c.Roles_BEN as Role, 
       c.Status,
	   c.\"Job Title\" as Title, 
       c.\"DirectData_Username\" as \"Username\",
       cl.zUid as ClientId, 
       cl.\"Client Name\" as ClientName, 
       '' || b.zUid as BrandId, 
       b.BrandName
    from Contacts as c
    inner join Clients cl
        on c.\"_KeyF_Client\" = cl.zUID
    left join \"zClient_UserBrands.Table\" cb
        on c.zzUid = cb.KeyF_ClientContact
    left join \"zBrands.Table\" b
        on cb.KeyF_Brand = b.zUid";
    
    const SECURITY_BASE = "
select --AccountFlags
	   '' as ContactId,
       'EMP' || numval(u.\"zUID_t\") as EmployeeId, 
	   '' as AccountId, --placeholder
	   '' as Brands, --placeholder
	   '' as Client, --placeholder
	   u.\"Email_Address\" as Email,
	   u.\"First Name\" as FirstName, 
       u.\"Last Name\" as LastName, 
       u.\"Middle_Initial\" as MI, 
	   u.Roles_BEN as Role,
       u.Status, 
	   u.Title, 
       u.\"Username_Database\" as \"Username\", 
       cl.zUid as ClientId, 
       cl.\"Client Name\" as ClientName,
       '' as BrandId, 
       '' as BrandName
    from Security_Users u
    inner join Clients cl on cl.\"Client Code\" = 'NMA'";
	
	public function get_all() {
		return $this->get_multi_array("-34", sprintf(AccountModel::ALL_TEMPLATE, AccountModel::CONTACT_BASE, AccountModel::SECURITY_BASE));
	}
	
	public function get_by_dates($start_date, $end_date) {
		if(!$end_date) {
			$end_date = date("Y-m-d");
		}
			$date_template = sprintf(AccountModel::DATE_TEMPLATE, AccountModel::CONTACT_BASE, AccountModel::SECURITY_BASE, $start_date, $end_date);
			return $this->get_multi_array("-35", sprintf(AccountModel::DATE_TEMPLATE_ABSTRACT, $date_template));
	}
	
	public function get_by_id($id) {
		$type = substr($id, 0, 3);
		$id = substr($id, 3);
		
		$brands = array();
		
		
		if ($type === 'EMP') {
		
			$obj = abstraction_query_single_array("-31", sprintf(AccountModel::SECURITY_ID_TEMPLATE, AccountModel::SECURITY_BASE, $id));
			return $this->package_object($obj, $brands);
			
		} else if ($type === 'CON') {
		
			$obj_collection = abstraction_query_multi_array("-32", sprintf(AccountModel::CONTACT_ID_TEMPLATE, AccountModel::CONTACT_BASE, $id));
			//pull out all brand data and put into the brand array
			foreach($obj_collection as $account) {
				$brand = new stdClass();
				$brand->BrandName = $account['BrandName'];
				$brand->BrandId = $account['BrandId'];
				$brands[] = $brand;
			}
			//package and return 
			return $this->package_object($obj_collection[0], $brands);
			
		} else {
		
			build_error_and_die("invalid ID $id", "-33");
			
		}
	}
	
#region private helper functions

	private function package_object($query_object, $brands) {
		//select correct accountid for contacts or employees
		$query_object['AccountId'] = ($query_object['ContactId']) ? $query_object['ContactId'] : $query_object['EmployeeId'];
		unset($query_object['ContactId']);
		unset($query_object['EmployeeId']);
		
		//package the client into a class object
		$client = new stdClass();
		$client->ClientId = (int)$query_object['ClientId'];
		$client->ClientName = $query_object['ClientName'];		
		$client->Status = NULL;
		$client->Brands = array();
		$client->Address1 = NULL;
		$client->Address2 = NULL;
		$client->City = NULL;
		$client->State = NULL;
		$client->Zipcode = NULL;
		$client->Phone = NULL;
		$query_object['Client'] = $client;
		
		
		//unset for formatting
		unset($query_object['ClientName']);
		unset($query_object['ClientId']);
		unset($query_object['BrandName']);
		unset($query_object['BrandId']);
		$query_object['Brands'] = $brands;
		
		$query_object['Email'] = strtolower($query_object['Email']);
		
		//do the colliding
		$result = new stdClass();
		collide_object($result, $query_object);
		return $result;
	}

	private function get_multi_array($err, $sql) {
		$obj_collection = abstraction_query_multi_array($err, $sql);
		
		$results = array();
		for($i = 0; $i < count($obj_collection); $i++) {
			if(($curr_id = $obj_collection[$i]['ContactId']) != NULL) { // contacts
				
				// put first brand into brand array
				$base_offset = $i;
				$brands = array();
				$brand = new stdClass();
				if(($brand->BrandName = $obj_collection[$i]['BrandName']) != NULL) {
					$brand->BrandId = $obj_collection[$i]['BrandId'];
					
					$brands[] = $brand;
					
					// check if the next object is a brand of this account
					while($i+1 < count($obj_collection) && $obj_collection[$i+1]['ContactId'] == $curr_id) {
						// if yes, put the brand into the brand array and loop
						$brand = new stdClass();
						$brand->BrandName = $obj_collection[$i+1]['BrandName'];
						$brand->BrandId = $obj_collection[$i+1]['BrandId'];
						$brands[] = $brand;
						$i++;
					}
				}
				// if no, break and package the raw account
				$results[] = $this->package_object($obj_collection[$base_offset], $brands);
				
			} else { // security
			
				$results[] = $this->package_object($obj_collection[$i], $brands = array());
			}
		}
		return $results;
	}
#endregion	
}
