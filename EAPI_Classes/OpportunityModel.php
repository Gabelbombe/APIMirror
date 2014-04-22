<?php

// 80's error codes
class OpportunityModel
{

const OPP_GET_ALL_PRD = <<<SQL
  select
    'PRD' || strval(prod.zUid) as OpportunityId,
    prod.ProductionTitle as Title,
    prod.Type,
    prod.Status,
    prod.BEN_Channel as Channel,
    prod.Vehicle_Power as VehiclePower,
    prod.Media_Still_URL as CoverImageURL,
    prod.Media_Trailer_URL as VideoClipURL,
    prod.Content as SensitiveContent,
    prod.Rating_BEN_Smart as Rating,
    coalesce(prod.Date_Single_BEN, prod.Date_Range_Start_BEN) as StartDate,
    coalesce(prod.Date_Single_BEN, prod.Date_Range_End_BEN) as EndDate,
    substr(prod.Synopsis_Brief,1,2046) as Synopsis,
    prod.Flag_Ongoing as Ongoing,
    prod.AgeRange,
    prod.Ethnicity,
    prod.Gender,
    prod.Household_Income as IncomeRange,
    prod.Household_FamilyOriented as FamilyOriented,
    prod.Genre,
    prod.DistributorNetwork as Network,
    prod.WebAccessible,
    prod.Impressions_BEN as Impressions,
    prod.Impressions_Per as ImpressionsUnit,
    prod.Director,
    prod.Producer,
    prod.Writer,
    prod.Cast_calc,
    prod.SongName,
    prod.Artist,
    prod.Publisher,
    prod.Main_Studio,
    '' as Event,
    '' as Location,
    prod.Air_Day,
    prod.Air_Start,
    prod.Air_End,
    cat.FLAG_Excluded,
    cat.FLAG_BrandReady,
    cat.FLAG_BEN as FLAG_Included,
    coalesce(u.CategoryGroup, u.BEN_Category) as \,
    pc.zKeyF_Actor as CelebrityId,
    pc.Cast_Name CelebrityName,
    'Cast' as Role,
    coalesce(ac.QScore, 0) as QScore

  from Productions prod

    -- category join
    inner join "zCategories.Scenes.Table" cat
      on prod.zUid = cat.zKeyF_Production
    inner join "Utility.Table" u
      on cat.zKeyF_Category = u.zUid

    -- actor join
    inner join "zProductions.Cast" pc
      on prod.zUid = pc.zKeyF_Production
    inner join Actors ac
      on pc.zKeyF_Actor = ac.zUid

  where
    cat.zKeyF_Scene is null and
   (cat.FLAG_Excluded = 1 or cat.FLAG_BrandReady = 1) and
    prod.WebAccessible = 'Yes' and
    lower(pc.Type) = 'cast'
SQL;
    const OPP_GET_ALL_PRJ = <<<SQL
  select  'PRJ' || strval(proj.zUid) as OpportunityId,
    proj."Project Name" as Title,
    proj.Type,
    proj.Status,
    proj.BEN_Channel as Channel,
    proj.Vehicle_Power as VehiclePower,
    proj.Media_Still_URL as CoverImageURL,
    proj.Media_Trailer_URL as VideoClipURL,
    proj.Content as SensitiveContent,
    proj.Rating_BEN_Smart as Rating,
    coalesce(proj.Date_Single_BEN, proj.Date_Range_Start_BEN, "Start Date") StartDate,
    coalesce(proj.Date_Single_BEN, proj.Date_Range_End_BEN, "End Date") EndDate,
    substr(proj.Synopsis_Brief,1,2046) as Synopsis,
    proj.Flag_Ongoing as Ongoing,
    proj.AgeRange,
    proj.Ethnicity,
    proj.Gender,
    proj.Household_Income as IncomeRange,
    proj.Household_FamilyOriented as FamilyOriented,
    '' as Genre,
    '' as Network,
    proj.WebAccessible,
    proj.Impressions,
    '' as Cast_calc,
    '' as ImpressionsUnit,
    '' as Director,
    '' as Producer,
    '' as Writer,
    '' as SongName,
    '' as Artist,
    '' as Publisher,
    '' as Main_Studio,
    proj.EventName as Event,
    proj.Location1 as Location,
    '' as Air_Day,
    '' as Air_Start,
    '' as Air_End,
    cat.FLAG_Excluded,
    cat.FLAG_BrandReady,
    cat.FLAG_BEN as FLAG_Included,
    coalesce(u.CategoryGroup, u.BEN_Category) as Category,
    tal.zKeyF_Celebrity as CelebrityId,
    tal.Contact_Name CelebrityName,
    'Cast' as Role,
    coalesce(tal.QScore, 0) as QScore

  from Projects proj

    join "zCategories.Scenes.Table" cat
      on proj.zUid = cat.zKeyF_Project
    join "Utility.Table" u
      on cat.zKeyF_Category = u.zUid
    join "zProject.Talent" tal
      on tal.zKeyF_Project = proj.zUid

  where
    cat.zKeyF_Project is not null and
    cat.zKeyF_Scene is null and
   (cat.FLAG_Excluded = 1 or cat.FLAG_BrandReady = 1) and
    proj.WebAccessible = 'Yes' and
    lower(tal.Type) = 'talent'
SQL;


    public function get_all() {
      $results = array();


      $all_productions = abstraction_query_multi_array("-8011",
            OpportunityModel::OPP_GET_ALL_PRD);

      $id_on = "";
      $buffer = array();
      foreach($all_productions as $production) {
        if ($production["OpportunityId"] != $id_on) {
          if (sizeof($buffer) != 0) {
            $results[] = $this->convolve_single($buffer);
          }
          $buffer = array();
          $id_on = $production["OpportunityId"];
        }

        $buffer[] = $production;
      }

      $results[] = $this->convolve_single($buffer);


      $all_projects = abstraction_query_multi_array("-8012",
            OpportunityModel::OPP_GET_ALL_PRJ);

      $id_on = "";
      $buffer = array();
      foreach($all_projects as $project) {
        if ($project["OpportunityId"] != $id_on) {
          if (sizeof($buffer) != 0) {
            $results[] = $this->convolve_single($buffer);
          }
          $buffer = array();
          $id_on = $project["OpportunityId"];
        }

        $buffer[] = $project;
      }

      $results[] = $this->convolve_single($buffer);

      return $results;
    }

    public function get_by_dates($start_date, $end_date)
    {
        $results = array();

        return $results;
    }

//    public function get_all_ids()
//    {
//        return abstraction_get_ids("-80", VehicleModel::VEHICLE_IDS_FORMAT);
//    }

    public function get_by_id($id)
    {
        $target = substr($id, 0, 3);
        $emos_id = substr($id, 3);
//        echo "$id... $target... $emos_id..."; exit();

        switch($target)
        {
            case "PRJ":
                return $this->single_project($emos_id);

            case "PRD":
                return $this->single_production($emos_id);

            default:
                throw new Exception("unable to find source table for $id");
        }
    }





    const SINGLE_PROJECT = <<<SQL
  select  'PRJ' || strval(proj.zUid) as OpportunityId,
    proj."Project Name" as Title,
    proj.Type,
    proj.Status,
    proj.BEN_Channel as Channel,
    proj.Vehicle_Power as VehiclePower,
    proj.Media_Still_URL as CoverImageURL,
    proj.Media_Trailer_URL as VideoClipURL,
    proj.Content as SensitiveContent,
    proj.Rating_BEN_Smart as Rating,
    coalesce(proj.Date_Single_BEN, proj.Date_Range_Start_BEN, "Start Date") StartDate,
    coalesce(proj.Date_Single_BEN, proj.Date_Range_End_BEN, "End Date") EndDate,
    substr(proj.Synopsis_Brief,1,2046) as Synopsis,
    proj.Flag_Ongoing as Ongoing,
    proj.AgeRange,
    proj.Ethnicity,
    proj.Gender,
    proj.Household_Income as IncomeRange,
    proj.Household_FamilyOriented as FamilyOriented,
    '' as Genre,
    '' as Network,
    proj.WebAccessible,
    proj.Impressions,
    '' as Cast_calc,
    '' as ImpressionsUnit,
    '' as Director,
    '' as Producer,
    '' as Writer,
    '' as SongName,
    '' as Artist,
    '' as Publisher,
    '' as Main_Studio,
    proj.EventName as Event,
    proj.Location1 as Location,
    '' as Air_Day,
    '' as Air_Start,
    '' as Air_End,
    cat.FLAG_Excluded,
    cat.FLAG_BrandReady,
    cat.FLAG_BEN as FLAG_Included,
    coalesce(u.CategoryGroup, u.BEN_Category) as Category,
    tal.zKeyF_Celebrity as CelebrityId,
    tal.Contact_Name CelebrityName,
    'Cast' as Role,
    coalesce(tal.QScore, 0) as QScore

  from Projects proj

    join "zCategories.Scenes.Table" cat
      on proj.zUid = cat.zKeyF_Project
    join "Utility.Table" u
      on cat.zKeyF_Category = u.zUid
    join "zProject.Talent" tal
      on tal.zKeyF_Project = proj.zUid

  where proj.zUid = %s
SQL;

    public function single_project($project_id)
    {
        $main_query_array = abstraction_query_multi_array("-801",
                sprintf(OpportunityModel::SINGLE_PROJECT, $project_id));

        return $this->convolve_single($main_query_array);
    }





    const SINGLE_PRODUCTION = <<<SQL
  select
    'PRD' || strval(prod.zUid) as OpportunityId,
    prod.ProductionTitle as Title,
    prod.Type,
    prod.Status,
    prod.BEN_Channel as Channel,
    prod.Vehicle_Power as VehiclePower,
    prod.Media_Still_URL as CoverImageURL,
    prod.Media_Trailer_URL as VideoClipURL,
    prod.Content as SensitiveContent,
    prod.Rating_BEN_Smart as Rating,
    coalesce(prod.Date_Single_BEN, prod.Date_Range_Start_BEN) as StartDate,
    coalesce(prod.Date_Single_BEN, prod.Date_Range_End_BEN) as EndDate,
    substr(prod.Synopsis_Brief,1,2046) as Synopsis,
    prod.Flag_Ongoing as Ongoing,
    prod.AgeRange,
    prod.Ethnicity,
    prod.Gender,
    prod.Household_Income as IncomeRange,
    prod.Household_FamilyOriented as FamilyOriented,
    prod.Genre,
    prod.DistributorNetwork as Network,
    prod.WebAccessible,
    prod.Impressions_BEN as Impressions,
    prod.Impressions_Per as ImpressionsUnit,
    prod.Director,
    prod.Producer,
    prod.Writer,
    prod.Cast_calc,
    prod.SongName,
    prod.Artist,
    prod.Publisher,
    prod.Main_Studio,
    '' as Event,
    '' as Location,
    prod.Air_Day,
    prod.Air_Start,
    prod.Air_End,
    cat.FLAG_Excluded,
    cat.FLAG_BrandReady,
    cat.FLAG_BEN as FLAG_Included,
    coalesce(u.CategoryGroup, u.BEN_Category) as Category,
    pc.zKeyF_Actor as CelebrityId,
    pc.Cast_Name CelebrityName,
    'Cast' as Role,
    coalesce(ac.QScore, 0) as QScore

  from Productions prod

    -- category join
    inner join "zCategories.Scenes.Table" cat
      on prod.zUid = cat.zKeyF_Production
    inner join "Utility.Table" u
      on cat.zKeyF_Category = u.zUid

    -- actor join
    inner join "zProductions.Cast" pc
      on prod.zUid = pc.zKeyF_Production
    inner join Actors ac
      on pc.zKeyF_Actor = ac.zUid

  where prod.zUid = %s
SQL;

    public function single_production($production_id)
    {
        $main_query_array = abstraction_query_multi_array("-802",
                        sprintf(OpportunityModel::SINGLE_PRODUCTION, $production_id));
        return $this->convolve_single($main_query_array);
    }




    private function convolve_single($cursor) {

      // first lets build the main part of the object
      $base_row = $cursor[0];
      $result = array();
      $result["OpportunityId"]  = $base_row["OpportunityId"];
      $result["Channel"]        = utf8_encode_all($base_row["Channel"]);
      $result["CoverImageURL"]  = utf8_encode_all($base_row["CoverImageURL"]);
      $result["VideoClipURL"]   = utf8_encode_all($base_row["VideoClipURL"]);
      $result["Genre"]          = utf8_encode_all($base_row["Genre"]);
      $result["Impressions"]    = utf8_encode_all($base_row["Impressions"]);
      $result["Network"]        = utf8_encode_all($base_row["Network"]);
      $result["Rating"]         = utf8_encode_all($base_row["Rating"]);
      $result["Status"]         = utf8_encode_all($base_row["Status"]);
      $result["Synopsis"]       = utf8_encode_all($base_row["Synopsis"]);
      $result["Title"]          = utf8_encode_all($base_row["Title"]);
      $result["Type"]           = utf8_encode_all($base_row["Type"]);
      $result["VehiclePower"]   = utf8_encode_all($base_row["VehiclePower"]);
      $result["WebAccessible"]  = ($base_row["WebAccessible"] == 'Yes');

      $result["Release"] = new stdClass();
      $result["Release"]->StartDate = utf8_encode_all($base_row["StartDate"]);
      $result["Release"]->EndDate   = utf8_encode_all($base_row["EndDate"]);
      $result["Release"]->Ongoing   = ($base_row["Ongoing"] == '1');

      $result["TargetSegment"] = new stdClass();
      $result["TargetSegment"]->FamilyOriented = ($base_row["FamilyOriented"] == '1');
      $result["TargetSegment"]->Gender = emos_split($base_row["Gender"]);
      $result["TargetSegment"]->Ethnicity = emos_split($base_row["Ethnicity"]);
      $result["TargetSegment"]->AgeRange = emos_split($base_row["AgeRange"]);
      $result["TargetSegment"]->IncomeRange = emos_split($base_row["IncomeRange"]);

      $result["ChannelAttributes"] = new stdClass();
      $result["ChannelAttributes"]->SongName = utf8_encode_all($base_row["SongName"]);
      $result["ChannelAttributes"]->Artist = utf8_encode_all($base_row["Artist"]);
      $result["ChannelAttributes"]->Label = utf8_encode_all($base_row["Main_Studio"]);
      $result["ChannelAttributes"]->Publisher = utf8_encode_all($base_row["Publisher"]);
      $result["ChannelAttributes"]->Location = utf8_encode_all($base_row["Location"]);
      $result["ChannelAttributes"]->Event = utf8_encode_all($base_row["Event"]);
      $result["ChannelAttributes"]->Air_Day = utf8_encode_all($base_row["Air_Day"]);
      $result["ChannelAttributes"]->Air_Start = utf8_encode_all($base_row["Air_Start"]);
      $result["ChannelAttributes"]->Air_End = utf8_encode_all($base_row["Air_End"]);

      $result["Members"] = array();
      $directors = emos_split($base_row["Director"]);
      foreach($directors as $director) {
        $result["Members"][] = build_talent(null, $director, "Director", null);
      }
      $producers = emos_split($base_row["Producer"]);
      foreach($producers as $producer) {
        $result["Members"][] = build_talent(null, $producer, "Producer", null);
      }
      $writers = emos_split($base_row["Writer"]);
      foreach($writers as $writer) {
        $result["Members"][] = build_talent(null, $writer, "Writer", null);
      }

      $result["SensitiveContent"] = array();
      $result["ExcludedCategories"] = array();
      $result["BrandReadyCategories"] = array();

      $categories = array();
      $casts = array();
      foreach($cursor as $row) {
        $category = $row["Category"];
        if (!in_array($category, $categories)) {
          $categories[] = $category;
          if ($row["Flag_Excluded"] == '1') {
            $result["ExcludedCategories"][] = $category;
          }
          if ($row["Flag_BrandReady"] == '1') {
            $result["BrandReadyCategories"][] = $category;
          }
        }

        $cast = $row["CelebrityName"];
        if (!in_array($cast, $casts)) {
          $casts[] = $cast;
          $result["Members"][] = build_talent(
                  (int)$row["CelebrityId"],
                  $row["CelebrityName"],
                  $row["Role"],
                  $row["QScore"]);
        }
      }
      return $result;
    }
}