@extends(Config::get('view.backend.master'))
@section('content')

<div class="tabs-vertical-env">
	@include(Config::get('view.backend.submenu'), array('datas' => Config::get('variable.backend.sub-menus.order'), 'dir' => 'right'))
	<div class="tab-content">
		<div class="tab-pane active">
			<div class="ajax-alert"></div>
			<form id="datatable-form" action-delete="{{route('deleteServices')}}"> 
				<div class="ajax-alert"></div>
				<table class="table table-bordered table-striped datatable">
					<thead>
						<tr>
							<th>{{trans('all.services')}}</th>
							<th>{{trans('all.date')}}</th>
							<th>{{trans('all.credit')}}</th>
							<th>{{trans('all.username')}}</th>
							<th>IMEI</th>
							<th>{{trans('all.result')}}</th>
							<th>{{trans('all.actions')}}</th>
						</tr>
					</thead>
					
					<tbody>
					</tbody>
				</table>
				</form>
				<a href="{{route('newImeiServices')}}" class="btn btn-primary btn-icon icon-left">
					<i class="entypo-plus"></i>
					{{trans('all.add-new-service')}}
				</a>

				<a id="deleteMulti" class="btn btn-danger btn-icon icon-left">
					<i class="entypo-cancel"></i>
					{{trans('all.delete')}}
				</a>



		</div>
	</div>
</div>





</div>

<!-- Modal 1 (Basic)-->
<div class="modal fade" id="confirm-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{{trans('all.confirm')}}</h4>
			</div>
			
			<div class="modal-body">
				{{trans('all.confirm-modal-delete')}}
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{{trans('all.close')}}</button>
				<button type="button" class="btn btn-info btn-confirm">{{trans('all.ok')}}</button>
			</div>
		</div>
	</div>
</div>
<div>

<div class="modal fade" id="edit-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{{trans('all.edit-service')}}</h4>
			</div>
			
			<div class="modal-body">
			
				{{trans('all.loading')}}
				
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{{trans('all.close')}}</button>
				<button type="button" class="btn btn-info btn-ok">{{trans('all.update')}}</button>
			</div>
		</div>
	</div>
</div>
<div>



<script type="text/javascript">
jQuery(window).load(function()
{
	var $ = jQuery;
	
	tableData  = $("table.datatable").dataTable({
		"sPaginationType": "bootstrap-backend",
		//"sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
		"sDom": "t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
		"bStateSave": false,
		"iDisplayLength": 10,
		"bServerSide": true,
    "sAjaxSource": "<?php echo URL::route('quickReplyImeiOrders') ?>",
		"aoColumns": [
			{"sWidth": "30%", "bSortable": false},
			{"sWidth": "10%", "bSortable": false},
			{"sWidth": "5%", "bSortable": false},
			{"sWidth": "10%", "bSortable": false},
			{"sWidth": "10%", "bSortable": false},
			{"sWidth": "15%", "bSortable": false},
			{"sWidth": "15%", "bSortable": false}
		],
		"oTableTools": {
			}
	});
	
	$('table tbody').editable({
    selector: 'a',
    emptytext: '{{trans('all.empty')}}',
    url: '{{route("quickReplyImeiOrders")}}',
    source: [
              {value: {{Config::get('variable.order-status.pending')}}, text: '{{trans("variable.order-status.pending")}}'},
              {value: {{Config::get('variable.order-status.denied')}}, text: '{{trans("variable.order-status.denied")}}'},
              {value: {{Config::get('variable.order-status.processing')}}, text: '{{trans("variable.order-status.processing")}}'},
           ],
    ajaxOptions: {type: "PUT"},
    success: function(response, newValue) {
        if(response.dataStatus === false) return response.message; //msg will be shown in editable form
    }
  });

 
});
</script>
@stop




