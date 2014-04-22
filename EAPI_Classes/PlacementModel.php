<?php
// table name:
// Compitition

// ?layout=placements&start_date=2013-11-01&end_date=2013-11-15

// 70's error codes
class PlacementModel
{

    const ALL_TEMPLATE = <<<SQL
	%s
	
	WHERE 	plac.WebAccessible = 'Yes' AND
			plac.zKeyF_MediaPlanID IS NOT NULL
SQL;

    const ID_TEMPLATE = <<<SQL
	%s
	
	WHERE plac.zUID = %s 
SQL;

	const DATE_TEMPLATE = <<<SQL
	%s
	
	WHERE 	plac.WebAccessible = 'Yes' AND
			plac.zKeyF_MediaPlanID IS NOT NULL AND
			plac.zDate_Modified BETWEEN {d '%s'} AND {d '%s'}
SQL;

    const PLACEMENT = <<<SQL
	select 
		-- ids and such
		plac.zUID as PlacementId,
		plac.zKeyF_MediaPlanID as MediaPlanID,
		plac.zKeyF_BrandID as BrandId,
		plac.BrandName,
		plac.zKeyF_ClientID as ClientId,
		plac.ClientNameDisplay as ClientName,
		coalesce(strval(plac.zDate_Creation), '0001-01-01') || 'T00:00:00.00-08:00' 
			as CreateDateTime,
		plac.zCreated_By as CreatedBy,
		coalesce(strval(plac.zDate_Modified), '0001-01-01') || 'T' || 
		coalesce(strval(plac.zTime_Modified), '00:00:00') || '.00-08:00' 
			as UpdateDateTime,
		plac.zModifiedBy as UpdateBy,
		
		-- every placement
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
		plac.PlacementName_BEN as PlacementName,
		plac.Description,
		plac.ViewerImpressions as Impressions,
		plac.TotalTime as Duration,
		plac.VerbalCount,
		plac.Grade as Quality,
		plac.WebAccessible,
		
		-- media
		plac.Media_Still_URL as StillImageUrl,
		plac.Media_Thumbnail_URL as ThumbNail,
		plac.Media_Trailer_URL as ClipUrl,
		
		-- misc 
		plac.Outlet as Outlet,
		plac.OutletType as OutletType,
		plac.EpisodeNumberSEQ as EpisodeNumberSEQ,
		plac.Network,
		plac.Flag_Repeat as Repeat,
		eps.Season as SeasonNumber,
		plac.PlacementStartTime as StartTime,
		plac.Display_Placements as VehicleName,
		plac.CPM,
		
		-- references
		plac.zKeyF_Project as celeb_id,
		plac.zKeyF_ProductionCodeMaster as prod_id,
		plac.zKeyF_EpisodeID as episode_id,
		
		-- vehicle specific
		proj.BEN_Channel as prj_Channel,
		proj."Project Name" as prj_VehicleName,
		proj.Media_Still_URL as prj_VehicleCoverImageURL, 
		proj.Media_Trailer_URL as prj_VehicleVideoClipURL, 
		proj.Vehicle_Power as prj_VehiclePower,
		
		prod.BEN_Channel as prd_Channel, 
		prod.ProductionTitle as prd_VehicleName,
		prod.Media_Still_URL as prd_VehicleCoverImageURL, 
		prod.Media_Trailer_URL as prd_VehicleVideoClipURL,
		prod.Vehicle_Power as prd_VehiclePower,
		
		
		img."CelebrityStill_URL",
		img."Location",
		img."Size",
		img."Flag_Main"
		
	from "zPlacements.Table" plac
	left join Projects as proj
		on plac.zKeyF_Project = proj.zUID
	left join Productions as prod
		on plac.zKeyF_ProductionCodeMaster = prod.zUID
	left join Episodes as eps
		on plac.zKeyF_EpisodeID = eps.zUID
	left join PlacementImages as img
		on plac.zUid = img.KeyF_PlacementID 
		and img.WebAccessible = 'Yes'
SQL;
    
	
	public function get_by_dates($start_date, $end_date) {
	
		if (!$end_date)
		{
			$end_date = date("Y-m-d");
		}
		
		$sql = sprintf(PlacementModel::DATE_TEMPLATE, 
				PlacementModel::PLACEMENT,
				$start_date, 
				$end_date);

		return $this->buffer_and_package($sql, "-7033");
	}
	
	public function get_all() {
		
		$sql = sprintf(PlacementModel::ALL_TEMPLATE, PlacementModel::PLACEMENT);
	
		return $this->buffer_and_package($sql, "-7022");
	}
	
	public function get_by_id($id) {
		
		$sql = sprintf(PlacementModel::ID_TEMPLATE, 
				PlacementModel::PLACEMENT,
				$id);
		$all_placements = abstraction_query_multi_array("-7011", $sql);

		return $this->package_object($all_placements);
	}
	
	
	private function buffer_and_package($sql, $err) {
		
		$results = array();
		
		$all_placements = abstraction_query_multi_array($err, $sql);
		
		// echo var_dump($all_placements);
		// exit();
		
		$id_on = null;
		$buffer = false;
		foreach($all_placements as $record) {
			if ($record["PlacementId"] != $id_on) {
				if ($buffer && sizeof($buffer) != 0) {
					$results[] = $this->package_object($buffer);
				}
				$buffer = array();
				$id_on = $record["PlacementId"];
			}
			
			$buffer[] = $record;
		}
		
		if ($buffer && sizeof($buffer) != 0) {
			$results[] = $this->package_object($buffer);
		}
		
		return $results;
	}
    
	
	private function package_object($buffer) {
		$result = array();
		$base = $buffer[0];
		
		$result["PlacementId"] 		= (int)$base["PlacementId"];
		$result["MediaPlanID"]		= $base["MediaPlanID"];
		$result["EpisodeID"]		= ($base["episode_id"] ? "EPS".$base["episode_id"] : null);
		$result["BrandId"] 			= (int)$base["BrandId"];
		$result["BrandName"] 		= $base["BrandName"];
		$result["ClientId"] 		= (int)$base["ClientId"];
		$result["ClientName"] 		= $base["ClientName"];
		$result["CreateDateTime"] 	= $base["CreateDateTime"];
		$result["CreatedBy"] 		= $base["CreatedBy"];
		$result["UpdateDateTime"] 	= $base["UpdateDateTime"];
		$result["UpdateBy"] 		= $base["UpdateBy"];
		
		$result["AirDate"] 			= $base["AirDate"];
		$result["PlacementName"] 	= $base["PlacementName"];
		$result["Description"] 		= $base["Description"];
		$result["Impressions"] 		= $base["Impressions"];
		$result["Duration"] 		= $base["Duration"];
		$result["VerbalCount"] 		= $base["VerbalCount"];
		$result["Quality"] 			= $base["Quality"];
		$result["WebAccessible"] 	= ($base["WebAccessible"] == 'Yes');
		
		$result["StillImageUrl"] 	= $base["StillImageUrl"];
		$result["ThumbNail"] 		= $base["ThumbNail"];
		$result["ClipUrl"] 			= $base["ClipUrl"];
		
		$result["Outlet"] 			= $base["Outlet"];
		$result["OutletType"] 		= $base["OutletType"];
		$result["EpisodeNumberSEQ"] = $base["EpisodeNumberSEQ"];
		$result["Network"] 			= $base["Network"];
		$result["Repeat"] 			= ($base["Repeat"] == 1);
		$result["SeasonNumber"] 	= $base["SeasonNumber"];
		$result["StartTime"] 		= $base["StartTime"];
		$result["VehicleName"] 		= $base["VehicleName"];
		$result["CPM"] 				= $base["CPM"];
		
		if ($base["celeb_id"] != null) {
			$result["VehicleID"] 			= ("PRJ".$base["celeb_id"]);
			$reuslt["VehicleParentID"]		= ("PRJ".$base["celeb_id"]);
			$result["Channel"] 				= $base["prj_Channel"];
			$result["VehicleName"] 			= $base["prj_VehicleName"];
			$result["VehicleParentName"] 	= $base["prj_VehicleName"];
			$result["VehicleCoverImageURL"] = $base["prj_VehicleCoverImageURL"];
			$result["VehicleVideoClipURL"] 	= $base["prj_VehicleVideoClipURL"];
			$result["VehiclePower"] 		= $base["prj_VehiclePower"];
		}
		else if ($base["prod_id"] != null) {
			if ($base["episode_id"] != null) {
				$result["VehicleID"] 		= ("EPS".$base["episode_id"]);
				$reuslt["VehicleParentID"]	= ("PRD".$base["prod_id"]);
			}
			else {
				$result["VehicleID"] 		= ("PRD".$base["prod_id"]);
				$reuslt["VehicleParentID"]	= ("PRD".$base["prod_id"]);
			}
			$result["Channel"] 				= $base["prd_Channel"];
			$result["VehicleName"] 			= $base["prd_VehicleName"];
			$result["VehicleParentName"] 	= $base["prd_VehicleName"];
			$result["VehicleCoverImageURL"] = $base["prd_VehicleCoverImageURL"];
			$result["VehicleVideoClipURL"] 	= $base["prd_VehicleVideoClipURL"];
			$result["VehiclePower"] 		= $base["prd_VehiclePower"];
		}
		
		$result["PlacementImages"] = array();
		foreach($buffer as $row) {
			if ($row["CelebrityStill_URL"]) {
				
				$image = array();
				$image["URL"] 		= $row["CelebrityStill_URL"];
				$image["Location"] 	= $row["Location"];
				$image["Size"] 		= $row["Size"];
				$image["IsMain"] 	= ($row["Flag_Main"] == 1);
				$result["PlacementImages"][] = $image;
			}
		}
		
		return $result;
	}
	
}
