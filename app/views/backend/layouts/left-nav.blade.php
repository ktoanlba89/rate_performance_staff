<!-- BEGIN SIDEBAR MENU -->
<ul class="page-sidebar-menu">
  <li class="sidebar-toggler-wrapper">
    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
    <div class="sidebar-toggler">
    </div>
    <div class="clearfix">
    </div>
    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
  </li>
  <li>
    <form class="search-form" role="form" action="index.html" method="get">
      <div class="input-icon right">
        <i class="fa fa-search"></i>
        <input type="text" class="form-control input-medium input-sm" name="query" placeholder="Search...">
      </div>
    </form>
  </li>

  <?php $arrRoutes = array('indexDashboard'); ?>
  {{ BackendSideBar::create_node(trans('all.dashboard'), 'indexDashboard', 'fa fa-home') }}  

  <?php $arrRoutes = array('listVotes', 'listUserVotes', 'listRoles', 'quickUserVote'); ?>
  {{ BackendSideBar::create_root_open(trans('all.votes'), $arrRoutes, 'fa fa-bar-chart-o')}}  
    {{ BackendSideBar::create_node(trans('all.vote-manage'), 'listVotes', 'fa fa-list') }}  
    {{ BackendSideBar::create_node(trans('all.join-vote'), 'listUserVotes', 'fa fa-signal') }}  
    {{ BackendSideBar::create_node(trans('all.quick-vote'), 'quickUserVote', 'fa fa-check') }}  
    {{ BackendSideBar::create_node(trans('all.role'), 'listRoles', 'fa fa-star') }}  
  {{ BackendSideBar::create_root_close() }} 

  <?php $arrRoutes = array('listPermissions', 'listJobTitles', 'listUsers', 'listGroups', 'listDepartments'); ?>
  {{ BackendSideBar::create_root_open(trans('all.user-manager'), $arrRoutes, 'fa fa-user')}}  
    {{ BackendSideBar::create_node(trans('all.user'), 'listUsers', 'fa fa-user') }}  
    {{ BackendSideBar::create_node(trans('all.job-title'), 'listJobTitles', 'fa fa-mortar-board') }}  
    {{ BackendSideBar::create_node(trans('all.department'), 'listDepartments', 'fa fa-sitemap') }}  
    {{ BackendSideBar::create_node(trans('all.group'), 'listGroups', 'fa fa-group') }}  
    {{ BackendSideBar::create_node(trans('all.permission'), 'listPermissions', 'fa fa-database') }}  
  {{ BackendSideBar::create_root_close() }} 
</ul>
<!-- END SIDEBAR MENU -->

