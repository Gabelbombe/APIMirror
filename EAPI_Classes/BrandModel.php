<?php

// 10's error codes
class BrandModel
{
	const BRAND_BASE = <<<SQL
	select 	b.zUID as BrandId, 
			b.BrandName, 
			b.zKeyF_ClientUID as ClientId, 
			b.Client as ClientName, 
			b.Status, 
			b.BrandLogo_URL as LogoUrl,
			u.CategoryGroup as Category
	from "zBrands.Table" b
		join "zCategories.Brands.Table" cat
			on b.zUID = cat.zKeyF_Brand
		join "Utility.Table" u
			on cat.zKeyF_Category = u.zUid
SQL;

	const BRAND_BY_ID = <<<SQL
	%s
	where b.zUID = %s
SQL;

	const BRAND_BY_DATE = <<<SQL
	%s
	where 	zDate_Modified BETWEEN {d '%s'} AND {d '%s'} and 
			zKeyF_ClientUID is not null
SQL;

    

    
    public function get_all() {
		$all_brands = abstraction_query_multi_array("-10", 
				BrandModel::BRAND_BASE);
		
		return $this->buffer_and_convolve($all_brands);
    }
    
    public function get_by_id($id) {
		$all_brands = abstraction_query_multi_array("-11", 
				sprintf(BrandModel::BRAND_BY_ID, BrandModel::BRAND_BASE, $id));
		
		return $this->convolve_single($all_brands);
    }
	
	public function get_by_dates($start_date, $end_date) {
		if (!$end_date){
			$end_date = date("Y-m-d");
		}
		$all_brands = abstraction_query_multi_array("-11", 
				sprintf(BrandModel::BRAND_BY_DATE, BrandModel::BRAND_BASE, $start_date, $end_date));
		
		return $this->buffer_and_convolve($all_brands);
	}
	
	
	
	
	
	private function buffer_and_convolve($all) {
		$results = array();
	
		$id_on = "";
		$buffer = array();
		foreach($all as $brand) {
			if ($brand["BrandId"] != $id_on) {
				if (sizeof($buffer) != 0) {
					$results[] = $this->convolve_single($buffer);
				}
				$buffer = array();
				$id_on = $brand["BrandId"];
			}
			
			$buffer[] = $brand;
		}
		
		if (sizeof($buffer) != 0) {
			$results[] = $this->convolve_single($buffer);
		}
		
		return $results;
	}
	
	private function convolve_single($cursor) {
		
		$base_row = $cursor[0];
		$result = array();
		$result["BrandId"] 		= $base_row["BrandId"];
		$result["BrandName"] 	= $base_row["BrandName"];
		$result["LogoUrl"] 		= $base_row["LogoUrl"];
		$result["ClientId"] 	= $base_row["ClientId"];
		$result["ClientName"] 	= $base_row["ClientName"];
		$result["Status"] 		= $base_row["Status"];
		$result["Categories"] 	= array();
		
		foreach($cursor as $cat) {
			if (!in_array($cat["Category"], $result["Categories"])) {
				$result["Categories"][] = $cat["Category"];
			}
		}
		
		return $result;
	}
}
