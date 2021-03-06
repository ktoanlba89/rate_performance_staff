<?php
use Validators\Backend as BackendValidator;
use Carbon\Carbon;
class VoteReportBackendController extends BackendBaseController 
{

  /**
  * Display a list of all votes
  *
  * @return Response
  */
  public function getIndexPeriod()
  {
    if(Request::Ajax())
    {
      if (Input::get('mode') == 'datatable')
      {
        $limit = Input::get('limit');
        $votes = VoteGroup::select(array('id as button', 'vote_code', 'title', 'id as vote_group_id','id as actions'))->orderBy("created_at", "desc")
                ->limit($limit);
      return Datatables::of($votes)
        ->edit_column('button', '<span class="row-details row-details-close"><input type="hidden" value="{{$button}}" /></span>')
        ->edit_column('vote_group_id', function($row){
          $count = Vote::where('vote_group_id', $row->vote_group_id)
            ->whereIn('status', [Config::get("variable.vote-status.newly"), Config::get("variable.vote-status.opened")])
            ->count();
          if ($count > 0)
          {
            return '<span class="label label-success">'.trans("all.opened").'</span>';
          }else
          {
            return '<span class="label label-default">'.trans("all.closed").'</span>';
          }
        })
        ->edit_column('actions', '
            <a href="{{route(\'reportPeriodVoteGroup\', $actions)}}" class="ajaxify-child-page btn btn-default btn-xs purple"><i class="fa fa-search"></i> {{trans(\'all.report\')}}</a>
            <a href="{{route(\'exportExcelReport\')}}?item_id={{$actions}}&item_type=vote-group" class="export-excel-general btn btn-default btn-xs purple"><i class="fa fa-search"></i> Xuất Excel</a>
            <a href="{{route(\'exportExcelGeneralReport\')}}?item_id={{$actions}}&item_type=vote-group" class="export-excel-general btn btn-default btn-xs purple"><i class="fa fa-search"></i> Xuất tổng hợp</a>
          ')
        #->filter_column('vote_code', 'where', 'Vote.vote_code', '=', '$1')
        ->make();
      }else if (Input::get('mode') == 'votes_of_group')
      {
        $votesArr = array();
        $votes = Vote::where('vote_group_id', Input::get('vote_group_id'))->orderBy("created_at", "desc")->get();
        foreach ($votes as $vote) {
          switch ($vote->status) {
            case Config::get("variable.vote-status.newly"):
              $statusHtml = '<span class="label label-primary">'.trans("all.newly").'</span>';
              break;
            case Config::get("variable.vote-status.opened"):
              $statusHtml = '<span class="label label-success">'.trans("all.opened").'</span>';
              break;
            
            default:
              $statusHtml = '<span class="label label-default">'.trans("all.closed").'</span>';
              break;
          }
          $department = $vote->department;
          $departmentName = is_object($department) ? $department->name : '';
          $actionsHtml = '<a href="'.route('reportPeriodVote', $vote->id).'" class="ajaxify-child-page btn btn-default btn-xs purple"><i class="fa fa-search"></i> '.trans('all.report').'</a>
          <a class="export-excel btn btn-default btn-xs purple" data-file-name="'.camel_case($departmentName).'-'.$vote->id.'.xls" data-vote-type="vote" data-item-id="'.$vote->id.'"><i class="fa fa-search"></i> Xuất Excel</a>
          <a href="'.route('exportExcelGeneralReport').'?item_id='.$vote->id.'&item_type=vote" class="btn btn-default btn-xs purple"><i class="fa fa-search"></i> Xuất tổng hợp</a>';
            

          $votesArr[] = array(
            'department' => $departmentName,
            'created_at' => $vote->created_at->format('d-m-Y'),
            'status' => $statusHtml,
            'actions' => $actionsHtml,
            );
        }
        return Response::json(array('actionStatus' => true, 'data' => $votesArr));
      }
      
    }

    $voteGroups = VoteGroup::all();
    $votes = Vote::all();
    $params['votes'] = $votes;
    $params['voteGroups'] = $voteGroups;
    $this->layout = View::make(Config::get('view.backend.report-by-period-index'), $params);
  }


  public function getPeriodVote($VoteId)
  {
    $currentUser = Sentry::getUser();
    $pattern = '"user_id":"'.$currentUser->id.'","type_of_persion":"'.Config::get('variable.type-of-person.view-report').'"';

    $votes = Vote::where('id', $VoteId)->get();
    $vote = $votes->first();
    if (!is_object($vote) || (strpos($vote->specify_user, $pattern) === false && !$currentUser->hasAnyAccess(['reports-management_period'])))
    {
      App::abort(500, 'Bạn không có quyền xem báo cáo này.');
    }
    $params = $this->_get_params_reports($votes);

    return View::make(Config::get('view.backend.report-details-child'), $params);
  }

  public function getPeriodVoteGroup($voteGroupId)
  {
    $votes = Vote::whereVoteGroupId($voteGroupId)->get();
    $params = $this->_get_params_reports($votes);

    return View::make(Config::get('view.backend.report-details-child'), $params);
  }

  public function getDetailHeadGradingVote($voteId)
  {
    $votes = Vote::where('id', $voteId)->get();
    $params = $this->_get_params_reports($votes);

    return View::make(Config::get('view.backend.report-details-child'), $params);
    //return View::make(Config::get('view.backend.head-grading-vote'), $params);
  }

  public function getIndexYear()
  {
    $departments = Department::all();
    $params['departments'] = $departments;
    return View::make(Config::get('view.backend.report-by-year-index'), $params);
  }

  public function postYearVote()
  {
    $thisYear = array(Carbon::createFromDate(Input::get('year'), Input::get('month') , 1)->startOfMonth(), Carbon::createFromDate(Input::get('year'), Input::get('month') , 1)->endOfMonth());

    if(Input::get('vote_type') == 1)
    {
      $votes = Vote::where('department_id', Input::get('select2_department'))->whereBetween('created_at', $thisYear)->get();
      $params = $this->_get_params_reports($votes);

      $viewName = Config::get('view.backend.report-by-year-department');
    }else
    {
      $voteGroups = VoteGroup::whereBetween('created_at', $thisYear)->get();
      $voteGroupIds = [];
      foreach ($voteGroups as $voteGroup) {
        $voteGroupIds[] = $voteGroup->id;
      }

      $voteGroupIds = empty($voteGroupIds) ? [''] : $voteGroupIds;
      $votes = Vote::whereIn('vote_group_id', $voteGroupIds)->orderBy('vote_group_id')->get();
      
      $params = $this->_get_params_reports($votes);
      $params['voteGroups'] = $voteGroups;

      $viewName = Config::get('view.backend.report-by-year-total-departments');
    }

    return View::make($viewName, $params);
  }

  public function getSpecifyUser()
  {
    if (Request::Ajax())
    {
      if (Input::get('mode') == 'datatable')
      {
        $currentUser = Sentry::getUser();
        $pattern = '"user_id":"'.$currentUser->id.'","type_of_persion":"'.Config::get('variable.type-of-person.view-report').'"';
        
        $votes = Vote::select(array('id', 'vote_group_id', 'department_id', 'id as vote_code', 'id as title', 'id as department_name', 'id as actions'))->whereRaw("specify_user regexp '".$pattern."'")->whereNotIn('status', [Config::get('variable.vote-status.newly')]);
      return Datatables::of($votes)
        ->remove_column('id', 'vote_group_id', 'department_id')
        ->edit_column('vote_code', function($row){
          return $row->voteGroup->vote_code;
        })
        ->edit_column('title', function($row){
          return $row->voteGroup->title;
        })
        ->edit_column('department_name', function($row){
          return $row->department->name;
        })
        ->edit_column('actions', function($row){
          $departmentName = $row->department->name;
          $html = '<a href="'.route('reportPeriodVote', $row->id).'" class="ajaxify-child-page btn btn-default btn-xs purple"><i class="fa fa-search"></i> Xem báo cáo</a>';
          $html .= '<a href="'.route('exportExcelReport').'?item_id='.$row->id.'&item_type=vote" class="btn btn-default btn-xs purple"><i class="fa fa-search"></i> Xuất Excel</a>';
          $html .= '<a href="'.route('exportExcelGeneralReport').'?item_id='.$row->id.'&item_type=vote" class="btn btn-default btn-xs purple"><i class="fa fa-search"></i> Xuất tổng hợp</a>';
          return $html;
        })
        #->filter_column('vote_code', 'where', 'Vote.vote_code', '=', '$1')
        ->make();
      }
    }
    
    return View::make(Config::get('view.backend.report-by-specify-user'));
  }

  public function getExportExcel()
  {
    Excel::create('excel_report_'.Input::get('item_id').'_'.time(), function($excel) {
            $itemId = Input::get('item_id');
            $itemType = Input::get('item_type');

            $votes = ($itemType == 'vote-group') ? Vote::whereVoteGroupId($itemId)->get() : Vote::where('id', $itemId)->get();
            $firstVote = $votes->first();
            $currentUser = Sentry::getUser();
            $pattern = '"user_id":"'.$currentUser->id.'","type_of_persion":"'.Config::get('variable.type-of-person.view-report').'"';
            if (!is_object($firstVote) || (strpos($firstVote->specify_user, $pattern) === false && !$currentUser->hasAnyAccess(['reports-management_period'])))
            {
              App::abort(500, 'Bạn không có quyền xem báo cáo này.');
            }
            $params = $this->_get_params_reports($votes);
            foreach($params['voteByRole'] as $voteArray)
            {
              $params['voteArray'] = $voteArray;
              foreach($voteArray as $vote)
              {
                $params['vote'] = $vote;
                $params['firstVoteArray'] = array_values($params['voteByRole'])[0];
                $counter_header_cols = 0;
                foreach ($params['voterArr'][$voteArray[0]->id] as $roleId => $value) {
                  if(count($params['voterArr'][$params['vote']->id][$roleId]) > 1)
                  {
                    $counter_header_cols += 3;
                  }else
                  {
                    $counter_header_cols += 2;
                  }
                }
                $params['size_of_col'] = $counter_header_cols + 4;
                $excel->sheet(preg_replace('/\//', '', Str::limit($vote->get_department_name(), 25)), function($sheet) use($params) {
                  for($i=0;$i<=25;$i++) $sheet->setWidth(chr(65+$i), 15);
                  $sheet->loadView(Config::get('view.backend.report-export-excel'), $params);
                  $sheet->setFontFamily('Times New Roman');
                  $sheet->setFontSize(12);
                  $sheet->mergeCells('A4:'.chr(64 + $params['size_of_col']).'4');
                  $sheet->mergeCells('A5:'.chr(64 + $params['size_of_col']).'5');
                  $sheet->cells('A4:A5', function($cells) {
                    $cells->setAlignment('center');
                  });

                  //header ne
                  $sheet->mergeCells('A7:A9'); #STT
                  $sheet->cell('A7', 'STT');
                  $sheet->mergeCells('B7:B9'); #Ho ten
                  $sheet->cell('B7', trans('all.full-name'));
                  $sheet->mergeCells('C7:C9'); #Chuc vu
                  $sheet->cell('C7', trans('all.job-title'));
                  $sheet->mergeCells('D7:'.chr(64 + $params['size_of_col'] - 1).'7'); #Thanh phan tham gia
                  $sheet->cell('D7', trans('all.participant')); #Thanh phan tham gia danh gia
                  $sheet->mergeCells( chr(64 + $params['size_of_col']).'7:'.chr(64 + $params['size_of_col']).'9'); #Tong hop ket qua
                  $sheet->cell(chr(64 + $params['size_of_col']).'7', trans('all.general-results')); #Tong hop ket qua


                  //bordered
                  $number_of_row_data = count(explode(',', $params['vote']->entitled_vote))*$params['maxVoterArr'][$params['vote']->id] + 10;
                  $end_row_of_table = chr(64 + $params['size_of_col']).$number_of_row_data;
                  $sheet->setBorder('A7:'.$end_row_of_table, 'thin');

                  //tong hop ket qua ra giua 
                  $sheet->cells(chr(64 + $params['size_of_col']).'7:'.$end_row_of_table, function($cells) {
                    $cells->setAlignment('center');
                  });

                  //phan nhom danh gia
                  $start_at_col_num = 68;# D
                  foreach($params['voterArr'][$params['voteArray'][0]->id] as $roleId => $value)
                  {
                    $next_rol_num = 0;
                    if(count($params['voterArr'][$params['vote']->id][$roleId]) > 1)
                    {
                      $sheet->cell(chr($start_at_col_num).'9', trans('all.voter'));
                      $next_rol_num = 1;
                    }

                    $sheet->cell(chr($start_at_col_num + $next_rol_num).'9', trans('all.mark'));
                    $sheet->cell(chr($start_at_col_num + $next_rol_num + 1).'9', trans('all.content'));

                    $sheet->setWidth(chr($start_at_col_num), 10);
                    $sheet->setWidth(chr($start_at_col_num+1), 20);

                    $sheet->mergeCells(chr($start_at_col_num).'8:'.chr($start_at_col_num+1+$next_rol_num).'8');
                    $sheet->cell(chr($start_at_col_num).'8', CustomHelper::get_role_name($roleId));

                    // Diem ra giua
                    $sheet->cells(chr($start_at_col_num).'9:'.chr($start_at_col_num+$next_rol_num).$number_of_row_data, function($cells) {
                      $cells->setAlignment('center');
                    });

                    $sheet->mergeCells(chr($start_at_col_num).'10:'.chr($start_at_col_num+1+$next_rol_num).'10');

                    $voter_names = [];
                    //Debugbar::info($params['voterArr'][$params['vote']->id][$roleId]);
                    foreach($params['voterArr'][$params['vote']->id][$roleId] as $key => $user_id)
                    {
                      array_push($voter_names, CustomHelper::get_user_name($user_id));
                    }
                    if(count($params['voterArr'][$params['vote']->id][$roleId]) == 1)
                      $sheet->cell(chr($start_at_col_num).'10', join(" - ", $voter_names));

                    $start_at_col_num += (2 + $next_rol_num);
                  }
                  //row height
                  //$sheet->setHeight(8, 50);
                  
                  


                  //thay doi chieu dai
                  
                  $sheet->setWidth('A', 5);
                  $sheet->setWidth('B', 20);
                  $sheet->setWidth('C', 20);
                  $sheet->setWidth(chr(64 + $params['size_of_col']), 10);
                 //stle header
                  $sheet->cells('A7:Z10', function($cells) {
                    $cells->setAlignment('center');
                    $cells->setValignment('middle');
                    $cells->setFontWeight('bold');
                  });

                  $sheet->cells('A7:'.$end_row_of_table, function($cells){
                    $cells->setValignment('center');
                    $style = $cells->sheet->getStyle($cells->cells);
                    // Apply style from array
                    $style->applyFromArray(array(
                      'alignment' => array(
                        'wrap' => true
                      )
                    ));
                  });
                });
              }
            }
            //Debugbar::info($votes);
            
            //$sheet->setWidth('A', 5);
            //$sheet->setBorder('A7:M10', 'thin');
            //$sheet->setAllBorders('thin');

    })->download('xls');
    //return View::make(, $params)->render();
  }

  public function getExportExcelGeneral()
  {
    Excel::create('tong_hop_danh_gia_'.Input::get('item_id').'_'.time(), function($excel) {
      $itemId = Input::get('item_id');
      $itemType = Input::get('item_type');

      $votes = ($itemType == 'vote-group') ? Vote::whereVoteGroupId($itemId)->get() : Vote::where('id', $itemId)->get();
      $firstVote = $votes->first();
      $currentUser = Sentry::getUser();
      $pattern = '"user_id":"'.$currentUser->id.'","type_of_persion":"'.Config::get('variable.type-of-person.view-report').'"';
      if (!is_object($firstVote) || (strpos($firstVote->specify_user, $pattern) === false && !$currentUser->hasAnyAccess(['reports-management_period'])))
      {
        App::abort(500, 'Bạn không có quyền xem báo cáo này.');
      }
      $params = $this->_get_params_reports($votes);
      Debugbar::info($params);
      
      $params['voteGroup'] = $votes->first()->voteGroup;

      $excel->sheet("Tổng hợp kết quả", function($sheet) use($params) {
        for($i=0;$i<=25;$i++) $sheet->setWidth(chr(65+$i), 15);

        $sheet->loadView(Config::get('view.backend.report-export-excel-general'), $params);
        $sheet->setFontFamily('Times New Roman');
        $sheet->setFontSize(12);
        //thay doi chieu dai
        
        $sheet->setWidth('A', 10);
        $sheet->setWidth('B', 30);
        $sheet->setWidth('C', 30);
        $sheet->setWidth('D', 20);

        $sheet->cells('A5:D5', function($cells) {
          $cells->setAlignment('center');
          $cells->setFontWeight('bold');
        });

        $sheet->mergeCells('A2:D2');
        $sheet->cells('A2', function($cells) {
          $cells->setAlignment('center');
          $cells->setFontWeight('bold');
        });

        $number_of_entitled_voter = 0;
        foreach($params['voteByRole'] as $voteArray)
          foreach($voteArray as $vote)
            $number_of_entitled_voter += (count(explode(',', $vote->entitled_vote)) + 1);

        $sheet->setBorder('A5:D'.($number_of_entitled_voter + 5), 'thin');

        $sheet->cells('A5:A'.($number_of_entitled_voter + 5), function($cells) {
          $cells->setAlignment('center');
        });

        $sheet->cells('D5:D'.($number_of_entitled_voter + 5), function($cells) {
          $cells->setAlignment('center');
        });

      });

    })->download('xls');
  }

  protected function _get_params_reports($votes)
  {
    $voterArr = [];
    $maxVoterArr = [];
    $voteByRole = [];
    $checkExtendRole = false;
    $pattern = '"role_id":"'.Config::get('variable.extend-member-role').'"';
    $voteIds = [0];
    foreach ($votes as $vote) {
      $voteIds[] = $vote->id;
    }
    $countExtendRole = VoteResult::whereIn('vote_id', $voteIds)->whereRaw("mark regexp '".$pattern."'")->count();
    if($countExtendRole>0) $checkExtendRole = true;

    foreach ($votes as $vote) {
      $roleOfVote = [];
      $json_voter = json_decode($vote->voter, true);
      
      $voteResults = VoteResult::where('vote_id', $vote->id)->whereRaw("mark regexp '".$pattern."'")->get();
      foreach ($voteResults as $voteResult) {
        $json_voter[] = [
          'role_id' => Config::get('variable.extend-member-role'),
          'user_id' => $voteResult->voter_id,
        ];
        $checkExtendRole = true;
      }
      foreach ($json_voter as $value) {
        $voterArr[$vote->id][$value['role_id']][] = $value['user_id'];
        //
        $roleOfVote[] = $value['role_id'];
      }
      if(!in_array(Config::get('variable.extend-member-role'), $roleOfVote) && $checkExtendRole)
      {
        $roleOfVote[] = Config::get('variable.extend-member-role');
        $voterArr[$vote->id][Config::get('variable.extend-member-role')][] = -1;
      }

      $roleOfVote = array_unique($roleOfVote);
      sort($roleOfVote);
      $voteByRole[implode(',', $roleOfVote)][] = $vote;

      //find max voter
      $maxVoterArr[$vote->id] = count($voteResults->groupBy('entitled_vote_id'));
      foreach ($voterArr[$vote->id] as $roleId => $value) {
        if((count($value) > $maxVoterArr[$vote->id]) && $roleId != Config::get('variable.extend-member-role'))
        {
          $maxVoterArr[$vote->id] = count($value);
        }
      }
    }
    /*
    if($checkExtendRole)
    {
      $newVoteByRole = [];
      foreach ($voteByRole as $roleIds => $votes) {
        $arrRoleIds = explode(',', $roleIds);
        if(!in_array(Config::get('variable.extend-member-role'), $arrRoleIds))
        {
          $arrRoleIds[] = Config::get('variable.extend-member-role');
        }
        sort($arrRoleIds);
        $stringRoleIds = implode(',', $arrRoleIds);
        if(isset($newVoteByRole[$stringRoleIds]))
        {
          $newVoteByRole[$stringRoleIds] = array_merge($newVoteByRole[$stringRoleIds], $votes);
        }else
        {
          $newVoteByRole[$stringRoleIds] = $votes;
        }
        //$newVoteByRole = array_unique($newVoteByRole);
      }
      $voteByRole = $newVoteByRole;
    }
    */

    return ['voterArr' => $voterArr, 'maxVoterArr' => $maxVoterArr, 'votes' => $votes, 'voteByRole' => $voteByRole];
  }

}