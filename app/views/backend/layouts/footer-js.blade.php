<!-- BEGIN PAGE LEVEL PLUGINS -->
@if(in_array(Route::currentRouteName(), array('indexDashboard')))

@endif

@if(in_array(Route::currentRouteName(), array('listPermissions', 'listGroups', 'showGroup', 'listUsers', 'showUser', 'newUser', 'newVote', 'showVote', 'listJobTitles', 'listDepartments', 'listRoles', 'listCriterias',  'listRatingTypes', 'showVoteGroup', 'copyVoteGroup', 'listReportYear', 'quickUserVote', 'listUserVotes')))
<script type="text/javascript" src="{{asset('assets/plugins/select2/select2.min.js')}}"></script>
@endif

@if(in_array(Route::currentRouteName(), array('listUsers')))
<script type="text/javascript" src="{{asset('assets/plugins/data-tables/jquery.dataTables.columnFilter.js')}}"></script>
@endif

@if(in_array(Route::currentRouteName(), array('listPermissions', 'listGroups', 'showGroup', 'listUsers', 'listVotes', 'listUserVotes', 'listJobTitles', 'listDepartments', 'listRoles', 'listCriterias', 'listRatingTypes', 'listReportPeriod', 'headGradingUserVote', 'listReportSpecifyUser')))
<script type="text/javascript" src="{{asset('assets/plugins/data-tables/jquery.dataTables.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/plugins/data-tables/DT_bootstrap.js')}}"></script>
@endif

@if(in_array(Route::currentRouteName(), array('showGroup', 'newGroup', 'showUser', 'newUser', 'newVote', 'showVote')))
<script type="text/javascript" src="{{asset('assets/plugins/jquery-multi-select/js/jquery.multi-select.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/plugins/jquery-multi-select/js/jquery.quicksearch.js')}}"></script>
@endif

@if(in_array(Route::currentRouteName(), array('showUser')))
<script src="{{asset('assets/plugins/bootstrap-switch/static/js/bootstrap-switch.min.js')}}" type="text/javascript"></script>
@endif

@if(in_array(Route::currentRouteName(), array('showUser')))
<script type="text/javascript" src="{{asset('assets/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js')}}"></script>
@endif

@if(in_array(Route::currentRouteName(), array('newVote', 'showVote')))
<script type="text/javascript" src="{{asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
@endif

@if(in_array(Route::currentRouteName(), array('quickUserVote', 'headGradingUserVote')))
<script type="text/javascript" src="{{asset('assets/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.min.js')}}"></script>
@endif

@if(in_array(Route::currentRouteName(), array('reportPeriodVote', 'reportPeriodVoteGroup', 'listReportPeriod', 'listReportSpecifyUser')))
<script type="text/javascript" src="{{asset('assets/plugins/jquery.battatech.excelexport.min.js')}}"></script>
@endif

@if(in_array(Route::currentRouteName(), array('importUsers')))
<script type="text/javascript" src="{{asset('assets/plugins/jquery-file-upload/js/jquery.fileupload.js')}}"></script>
@endif
<!-- END PAGE LEVEL PLUGINS -->