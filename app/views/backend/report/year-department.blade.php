@extends(Config::get('view.backend.master'))
@section('content')

@foreach($votes as $vote)
  <?php $voteGroup = $vote->voteGroup; ?>
<!-- BEGIN EXAMPLE TABLE PORTLET-->
<div class="portlet box light-grey">
<div class="portlet-title">
  <div class="caption">
    <i class="fa fa-list"></i>{{$voteGroup->title}}
  </div>
  <div class="actions">
    <a data-file-name="{{camel_case($voteGroup->vote_code)}}-{{$vote->id}}.xls" data-vote-id="{{$vote->id}}" class="btn btn-info export-excel"><i class="fa fa-pencil"></i> {{trans('all.export-excel')}}</a>
  </div>
</div>
<div class="portlet-body panel-content-area">

  <table class="table table-striped table-bordered table-hover" id="vote-data-table-{{$vote->id}}" action-delete="{{route('deleteVoteGroup')}}">
  <thead>
    <tr>
      <th rowspan="3">STT</th>
      <th rowspan="3">{{trans('all.full-name')}}</th>
      <th rowspan="3">{{trans('all.job-title')}}</th>
      <th colspan="{{count($voterArr[$vote->id])*3}}">{{trans('all.participant')}}</th>
      <th rowspan="3">{{trans('all.general-results')}}</th>
    </tr>
    <tr>
      @foreach($voterArr[$vote->id] as $key => $value)
        <td colspan="3">{{CustomHelper::get_role_name($key)}}</td>  
      @endforeach
    </tr>
    <tr>
      @foreach($voterArr[$vote->id] as $key => $value)
        <td>{{trans('all.voter')}}</td>
        <td>{{trans('all.mark')}}</td>
        <td>{{trans('all.content')}}</td>
      @endforeach
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="{{4+count($voterArr[$vote->id])*3}}"><strong>{{trans('all.department')}}</strong>: {{$vote->get_department_name()}}</td>
    </tr>

  <?php $countEntitled = 1; ?>
  @foreach(explode(',', $vote->entitled_vote) as $userId)

  <?php $entitledUser = User::find($userId); ?>
    <tr>
      <td rowspan="{{ $maxVoterArr[$vote->id] }}">{{$countEntitled}}</td>
      <td rowspan="{{ $maxVoterArr[$vote->id] }}">{{ $entitledUser->full_name }}</td>
      <td rowspan="{{ $maxVoterArr[$vote->id] }}">{{$entitledUser->job_titles_name()}}</td>
      @foreach($voterArr[$vote->id] as $voteGroupId => $voterIdArr)
      <?php $voteResult = VoteResult::whereVoteId($vote->id)->whereVoterId($voterIdArr[0])->whereEntitledVoteId($entitledUser->id)->first(); ?>
      <td>{{ CustomHelper::get_user_name($voterIdArr[0]) }}</td>
      <td>
        @foreach(explode(',', $vote->criteria) as $criteriaId)
          {{ CustomHelper::get_mark_with_criteria($voteResult, $criteriaId) }} <br />
        @endforeach
      </td>
      <td>{{ CustomHelper::get_mark_with_criteria($voteResult, 'content') }}</td>
      @endforeach
      <td rowspan="{{$maxVoterArr[$vote->id]}}">{{CustomHelper::get_general_result($vote->id, $userId)}}</td>
    </tr>
    @for($i=1; $i < $maxVoterArr[$vote->id]; $i++)
    <tr>
      @foreach($voterArr[$vote->id] as $voteGroupId => $voterIdArr)
      <?php $voteResult = VoteResult::whereVoteId($vote->id)->whereVoterId($voterIdArr[0])->whereEntitledVoteId($entitledUser->id)->first(); ?>
        @if(isset($voterIdArr[$i]))
          <td>{{ CustomHelper::get_user_name($voterIdArr[$i]) }}</td>
          <td>
          @foreach(explode(',', $vote->criteria) as $criteriaId)
            {{ CustomHelper::get_mark_with_criteria($voteResult, $criteriaId) }} <br />
          @endforeach
          </td>
          <td>{{ CustomHelper::get_mark_with_criteria($voteResult, 'content') }}</td>
        @else
          <td colspan="3"></td>
        @endif
      @endforeach
    </tr>
    @endfor
    <?php $countEntitled++; ?>
    @endforeach


  </tbody>
  </table>
</div>
</div>
@endforeach

<script type="text/javascript">
  $(document).ready(function () {
    $("a.export-excel").click(function () {
      var table_id = 'vote-data-table-' + $(this).data('vote-id');
      var uri = $("#" + table_id).btechco_excelexport({
          containerid: table_id
          , datatype: $datatype.Table
          , returnUri: true
      });

      $(this).attr('download', $(this).data('file-name')).attr('href', uri).attr('target', '_blank');
    });
  });
</script>
@stop