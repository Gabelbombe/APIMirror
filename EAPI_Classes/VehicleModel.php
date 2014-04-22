<?php 

class VehicleModel {
  
  const EPISODE_ID = <<<SQL
  SELECT DISTINCT
    'EPS' || eps.zUID as ID,
    'PRD' || eps.zKeyF_Production as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    eps.Title_Display as Name,
    prd.ProductionTitle as ParentName,
    coalesce(strval(eps.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(eps.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    eps.zModifiedBy as ModifiedBy,
    coalesce(strval(eps.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(eps.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    eps.zCreated_By as CreatedBy,
    prd.DistributorNetwork as Network,
    prd.BEN_Channel as Channel,
    prd.Vehicle_Power as Power,
    prd.Media_Still_URL as CoverImageURL,
    prd.Media_Trailer_URL as VideoClipURL,
    substr(prd.Synopsis_Brief,1,2046) as Description, 
    eps.Season as SeasonNumber,
    coalesce(eps.EpisodeNumberSEQ, -1) as EpisodeNumber,
    prd.WebAccessible
  from Episodes eps
      left join "zPlacements.Table" plac
        on plac.zKeyF_EpisodeID = eps.zUID
      left join Productions as prd
        on prd.zUID = eps.zKeyF_Production
  where eps.zUID = %s
SQL;
  
  const PRODUCTION_ID = <<<SQL
  SELECT DISTINCT
    'PRD' || prd.zUID as ID,
    'PRD' || prd.zUID as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    prd.ProductionTitle as Name,
    prd.ProductionTitle as ParentName,
    coalesce(strval(prd.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(prd.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    prd.zModifiedBy as ModifiedBy,
    coalesce(strval(prd.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(prd.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    prd.zCreated_By as CreatedBy,
    prd.DistributorNetwork as Network,
    prd.BEN_Channel as Channel,
    prd.Vehicle_Power as Power,
    prd.Media_Still_URL as CoverImageURL,
    prd.Media_Trailer_URL as VideoClipURL,
    substr(prd.Synopsis_Brief,1,2046) as Description, 
    '' as SeasonNumber,
    -1 as EpisodeNumber,
    prd.WebAccessible
  from Productions prd
      left join "zPlacements.Table" plac
        on plac.zKeyF_ProductionCodeMaster = prd.zUID
  where prd.zUID = %s
SQL;
  
  const PROJECT_ID = <<<SQL
  SELECT DISTINCT
    'PRJ' || prj.zUid as ID,
    'PRJ' || prj.zUid as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    prj."Project Name" as Name,
    prj."Project Name" as ParentName,
    coalesce(strval(prj.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(prj.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    prj.zModifiedBy as ModifiedBy,
    coalesce(strval(prj.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(prj.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    prj.zCreated_By as CreatedBy,
    '' as Network,
    prj.BEN_Channel as Channel,
    prj.Vehicle_Power as Power,
    prj.Media_Still_URL as CoverImageURL,
    prj.Media_Trailer_URL as VideoClipURL,
    substr(prj.Synopsis_Brief,1,2046) as Description, 
    '' as SeasonNumber,
    -1 as EpisodeNumber,
    prj.WebAccessible
  from Projects prj
      left join "zPlacements.Table" plac
        on plac.zKeyF_ProductionCodeMaster = prj.zUid
  where prj.zUid = %s
SQL;
  
  
  const ALL_TEMPLATE = <<<SQL
  SELECT DISTINCT
    'EPS' || eps.zUID as ID,
    'PRD' || eps.zKeyF_Production as ParentID,
    coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
    coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
      as AirDate,
    eps.Title_Display as Name,
    prd.ProductionTitle as ParentName,
    coalesce(strval(eps.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(eps.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    eps.zModifiedBy as ModifiedBy,
    coalesce(strval(eps.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(eps.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    eps.zCreated_By as CreatedBy,
    prd.BEN_Channel as Channel,
    prd.Vehicle_Power as Power,
    prd.Media_Still_URL as CoverImageURL,
    prd.Media_Trailer_URL as VideoClipURL,
    substr(prd.Synopsis_Brief,1,2046) as Description, 
    eps.Season as SeasonNumber,
    coalesce(eps.EpisodeNumberSEQ, -1) as EpisodeNumber,
    prd.WebAccessible
  from "zPlacements.Table" plac
      left join Episodes eps
        on eps.zUID = plac.zKeyF_EpisodeID
      left join Productions as prd
        on prd.zUID = plac.zKeyF_ProductionCodeMaster
  where plac.zKeyF_EpisodeID IS NOT NULL AND
        eps.zDate_Modified > {d '2013-07-25'} 



  UNION



  SELECT DISTINCT
    'PRD' || prd.zUID as ID,
    'PRD' || prd.zUID as ParentID,
    coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
    coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
      as AirDate,
    prd.ProductionTitle as Name,
    prd.ProductionTitle as ParentName,
    coalesce(strval(prd.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(prd.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    prd.zModifiedBy as ModifiedBy,
    coalesce(strval(prd.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(prd.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    prd.zCreated_By as CreatedBy,
    prd.BEN_Channel as Channel,
    prd.Vehicle_Power as Power,
    prd.Media_Still_URL as CoverImageURL,
    prd.Media_Trailer_URL as VideoClipURL,
    substr(prd.Synopsis_Brief,1,2046) as Description, 
    '' as SeasonNumber,
    -1 as EpisodeNumber,
    prd.WebAccessible
  from "zPlacements.Table" plac
      left join Productions as prd
        on prd.zUID = plac.zKeyF_ProductionCodeMaster
  where plac.zKeyF_EpisodeID IS NULL AND
        plac.zKeyF_ProductionCodeMaster IS NOT NULL AND
        prd.zDate_Modified > {d '2013-07-25'} 



  UNION



  SELECT DISTINCT
    'PRJ' || prj.zUid as ID,
    'PRJ' || prj.zUid as ParentID,
    coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
    coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
      as AirDate,
    prj."Project Name" as Name,
    prj."Project Name" as ParentName,
    coalesce(strval(prj.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(prj.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    prj.zModifiedBy as ModifiedBy,
    coalesce(strval(prj.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(prj.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    prj.zCreated_By as CreatedBy,
    prj.BEN_Channel as Channel,
    prj.Vehicle_Power as Power,
    prj.Media_Still_URL as CoverImageURL,
    prj.Media_Trailer_URL as VideoClipURL,
    substr(prj.Synopsis_Brief,1,2046) as Description, 
    '' as SeasonNumber,
    -1 as EpisodeNumber,
    prj.WebAccessible
  from "zPlacements.Table" plac
      left join Projects prj
        on plac.zKeyF_Project = prj.zUid
  where plac.zKeyF_Project IS NOT NULL AND
        prj.zDate_Modified > {d '2013-07-25'} 
SQL;
  
  const MODIFY_DATES = <<<SQL
  SELECT DISTINCT
    'EPS' || eps.zUID as ID,
    'PRD' || eps.zKeyF_Production as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    eps.Title_Display as Name,
    prd.ProductionTitle as ParentName,
    coalesce(strval(eps.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(eps.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    eps.zModifiedBy as ModifiedBy,
    coalesce(strval(eps.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(eps.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    eps.zCreated_By as CreatedBy,
    prd.DistributorNetwork as Network,
    prd.BEN_Channel as Channel,
    prd.Vehicle_Power as Power,
    prd.Media_Still_URL as CoverImageURL,
    prd.Media_Trailer_URL as VideoClipURL,
    substr(prd.Synopsis_Brief,1,2046) as Description, 
    eps.Season as SeasonNumber,
    coalesce(eps.EpisodeNumberSEQ, -1) as EpisodeNumber,
    prd.WebAccessible
  from "zPlacements.Table" plac
      left join Episodes eps
        on eps.zUID = plac.zKeyF_EpisodeID
      left join Productions as prd
        on prd.zUID = plac.zKeyF_ProductionCodeMaster
  where plac.WebAccessible = 'Yes' AND
        plac.zKeyF_MediaPlanID IS NOT NULL AND
        plac.zKeyF_EpisodeID IS NOT NULL AND
        eps.zDate_Modified BETWEEN {d '%s'} AND {d '%s'}



  UNION
  


  SELECT DISTINCT
    'PRD' || prd.zUID as ID,
    'PRD' || prd.zUID as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    prd.ProductionTitle as Name,
    prd.ProductionTitle as ParentName,
    coalesce(strval(prd.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(prd.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    prd.zModifiedBy as ModifiedBy,
    coalesce(strval(prd.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(prd.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    prd.zCreated_By as CreatedBy,
    prd.DistributorNetwork as Network,
    prd.BEN_Channel as Channel,
    prd.Vehicle_Power as Power,
    prd.Media_Still_URL as CoverImageURL,
    prd.Media_Trailer_URL as VideoClipURL,
    substr(prd.Synopsis_Brief,1,2046) as Description, 
    '' as SeasonNumber,
    -1 as EpisodeNumber,
    prd.WebAccessible
  from "zPlacements.Table" plac
      left join Productions as prd
        on prd.zUID = plac.zKeyF_ProductionCodeMaster
  where plac.WebAccessible = 'Yes' AND
        plac.zKeyF_MediaPlanID IS NOT NULL AND
        plac.zKeyF_EpisodeID IS NULL AND
        plac.zKeyF_ProductionCodeMaster IS NOT NULL AND
        prd.zDate_Modified BETWEEN {d '%s'} AND {d '%s'}
        


  UNION
  


  SELECT DISTINCT
    'PRJ' || prj.zUid as ID,
    'PRJ' || prj.zUid as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    prj."Project Name" as Name,
    prj."Project Name" as ParentName,
    coalesce(strval(prj.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(prj.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    prj.zModifiedBy as ModifiedBy,
    coalesce(strval(prj.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(prj.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    prj.zCreated_By as CreatedBy,
    '' as Network,
    prj.BEN_Channel as Channel,
    prj.Vehicle_Power as Power,
    prj.Media_Still_URL as CoverImageURL,
    prj.Media_Trailer_URL as VideoClipURL,
    substr(prj.Synopsis_Brief,1,2046) as Description, 
    '' as SeasonNumber,
    -1 as EpisodeNumber,
    prj.WebAccessible
  from "zPlacements.Table" plac
      left join Projects prj
        on plac.zKeyF_Project = prj.zUid
  where plac.WebAccessible = 'Yes' AND
        plac.zKeyF_MediaPlanID IS NOT NULL AND
        plac.zKeyF_Project IS NOT NULL AND
        prj.zDate_Modified BETWEEN {d '%s'} AND {d '%s'}
SQL;
  
  const EPISODE_IDS = <<<SQL
  SELECT DISTINCT
    'EPS' || eps.zUID as ID,
    'PRD' || eps.zKeyF_Production as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    eps.Title_Display as Name,
    prd.ProductionTitle as ParentName,
    coalesce(strval(eps.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(eps.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    eps.zModifiedBy as ModifiedBy,
    coalesce(strval(eps.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(eps.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    eps.zCreated_By as CreatedBy,
    prd.DistributorNetwork as Network,
    prd.BEN_Channel as Channel,
    prd.Vehicle_Power as Power,
    prd.Media_Still_URL as CoverImageURL,
    prd.Media_Trailer_URL as VideoClipURL,
    substr(prd.Synopsis_Brief,1,2046) as Description, 
    eps.Season as SeasonNumber,
    coalesce(eps.EpisodeNumberSEQ, -1) as EpisodeNumber,
    prd.WebAccessible
  from "zPlacements.Table" plac
      left join Episodes eps
        on eps.zUID = plac.zKeyF_EpisodeID
      left join Productions as prd
        on prd.zUID = plac.zKeyF_ProductionCodeMaster
  where plac.zKeyF_EpisodeID IS NOT NULL AND
        (%s)
SQL;
  
  const PRODUCTION_IDS = <<<SQL
  SELECT DISTINCT
    'PRD' || prd.zUID as ID,
    'PRD' || prd.zUID as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    prd.ProductionTitle as Name,
    prd.ProductionTitle as ParentName,
    coalesce(strval(prd.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(prd.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    prd.zModifiedBy as ModifiedBy,
    coalesce(strval(prd.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(prd.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    prd.zCreated_By as CreatedBy,
    prd.DistributorNetwork as Network,
    prd.BEN_Channel as Channel,
    prd.Vehicle_Power as Power,
    prd.Media_Still_URL as CoverImageURL,
    prd.Media_Trailer_URL as VideoClipURL,
    substr(prd.Synopsis_Brief,1,2046) as Description, 
    '' as SeasonNumber,
    -1 as EpisodeNumber,
    prd.WebAccessible
  from "zPlacements.Table" plac
      left join Productions as prd
        on prd.zUID = plac.zKeyF_ProductionCodeMaster
  where plac.zKeyF_EpisodeID IS NULL AND
        plac.zKeyF_ProductionCodeMaster IS NOT NULL AND
        (%s)
SQL;
  
  const PROJECT_IDS = <<<SQL
  SELECT DISTINCT
    'PRJ' || prj.zUid as ID,
    'PRJ' || prj.zUid as ParentID,
		coalesce(strval(plac.AirDate), '0001-01-01') || 'T' || 
		coalesce(strval(plac.AirTime), '00:00:00') || '.00Z'
			as AirDate,
    prj."Project Name" as Name,
    prj."Project Name" as ParentName,
    coalesce(strval(prj.zDate_Modified), '0001-01-01') || 'T' || 
    coalesce(strval(prj.zTime_Modified), '00:00:00') || '.00-08:00' 
      as ModifiedDateTime,
    prj.zModifiedBy as ModifiedBy,
    coalesce(strval(prj.zDate_Creation), '0001-01-01') || 'T' || 
    coalesce(strval(prj.zTime_Created), '00:00:00') || '.00-08:00' 
      as CreatedDateTime,
    prj.zCreated_By as CreatedBy,
    '' as Network,
    prj.BEN_Channel as Channel,
    prj.Vehicle_Power as Power,
    prj.Media_Still_URL as CoverImageURL,
    prj.Media_Trailer_URL as VideoClipURL,
    substr(prj.Synopsis_Brief,1,2046) as Description, 
    '' as SeasonNumber,
    -1 as EpisodeNumber,
    prj.WebAccessible
  from "zPlacements.Table" plac
      left join Projects prj
        on plac.zKeyF_Project = prj.zUid
  where plac.zKeyF_Project IS NOT NULL AND
        (%s)
SQL;
  
  public function get_by_dates($start_date, $end_date) {
    
    if (!$end_date) {
      $end_date = date("Y-m-d");
    }
    
    $sql = sprintf(VehicleModel::MODIFY_DATES, 
            $start_date, $end_date, 
            $start_date, $end_date, 
            $start_date, $end_date);
    
    $obj_collection = abstraction_query_multi_array("-85", $sql);
    
    
    $results = array();
    
    foreach ($obj_collection as $obj) {
      $results[] = $this->package_object($obj);
    }
    
    return $results;
  }

  public function get_all() {
    
    $obj_collection = abstraction_query_multi_array("-84", 
            VehicleModel::ALL_TEMPLATE);
    $results = array();
    
    foreach ($obj_collection as $obj) {
      $results[] = $this->package_object($obj);
    }
    
    return $results;
  }

  public function get_by_ids($ids) {

    $eps = false;
    $prj = false;
    $prd = false;

    foreach ($ids as $id) {
      $split_id = $this->split_id($id);

      switch ($split_id[0]) {
        case "EPS":
          if (!$eps) {
            $eps = array();
          }
          $eps[] = $split_id[1];
          break;

        case "PRJ":
          if (!$prj) {
            $prj = array();
          }
          $prj[] = $split_id[1];
          break;

        case "PRD":
          if (!$prd) {
            $prd = array();
          }
          $prd[] = $split_id[1];
          break;

        default:
          throw new Exception("unable to find source table for $id");
      }
    }

    // first build the individual sql statement 
    $eps_sql = false;
    if ($eps) {
      $eps_ors = $this->join_ids($eps, "eps.zUid=%s");
      $eps_sql = sprintf(VehicleModel::EPISODE_IDS, $eps_ors);
    }

    $prj_sql = false;
    if ($prj) {
      $prj_ors = $this->join_ids($prj, "prj.zUid=%s");
      $prj_sql = sprintf(VehicleModel::PROJECT_IDS, $prj_ors);
    }

    $prd_sql = false;
    if ($prd) {
      $prd_ors = $this->join_ids($prd, "prd.zUid=%s");
      $prd_sql = sprintf(VehicleModel::PRODUCTION_IDS, $prd_ors);
    }


    // now build the final sql.. but if there
    // is no individual, then we just return
    // an empty array...
    $final_sql = false;
    if (!$eps_sql && !$prj_sql && !$prd_sql) {
      return array();
    }

    foreach (array($eps_sql, $prj_sql, $prd_sql) as $sql) {
      if (!$sql) {
        continue;
      }

      if ($final_sql) {
        $final_sql .= " UNION $sql";
      } else {
        $final_sql = $sql;
      }
    }

//    echo $final_sql;
//    exit();
    // now finally put the objects together
    $obj_collection = abstraction_query_multi_array("-84", $final_sql);
    $results = array();
    foreach ($obj_collection as $obj) {
      $dup_check = $this->package_object($obj);
      $finds = array_keys($ids, $dup_check->ID);
      if (sizeof($finds) > 0) {
        $results[] = $dup_check;
        for($i = sizeof($finds)-1; $i >= 0; $i--) {
          array_splice($ids, $finds[$i], 1);
        }
      }
    }
    return $results;
  }
  
  public function get_by_id($id) {
    
    $split_id = $this->split_id($id);
    
    switch ($split_id[0]) {
      case "EPS":
        $sql = sprintf(VehicleModel::EPISODE_ID, $split_id[1]);
        break;
        
      case "PRJ":
        $sql = sprintf(VehicleModel::PROJECT_ID, $split_id[1]);
        break;
        
      case "PRD":
        $sql = sprintf(VehicleModel::PRODUCTION_ID, $split_id[1]);
        break;
        
      default:
        throw new Exception("unable to find source table for $id");
    }
    
    $obj_array = abstraction_query_single_array("-81", $sql);
    return $this->package_object($obj_array);
  }

  private function join_ids($ids, $format) {

    $result = "";
    foreach ($ids as $id) {
      if ($result) {
        $result .= " OR ";
      }
      $result .= sprintf($format, $id);
    }
    return $result;
  }

  private function split_id($id) {

    $target = substr($id, 0, 3);
    $emos_id = substr($id, 3);
    if ($emos_id == null || $target == null) {
      return array("?", "?");
    }

    return array($target, $emos_id);
  }
  
  private function package_object($obj) {
    
    $result = new stdClass();
    collide_object($result, $obj);
    
    $result->WebAccessible = ($result->WebAccessible == 'Yes');
    //$result->AssetIDs = array();
    //$result->Attributes = new stdClass();
    
    return $result;
  }

}