<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li><a href="{{ backpack_url('dashboard') }}"><i class="fa fa-dashboard"></i> <span>{{ trans('backpack::base.dashboard') }}</span></a></li>
{{--<li><a href="{{ backpack_url('elfinder') }}"><i class="fa fa-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>--}}
<li><a href='{{ backpack_url('ucm') }}'><i class='fa fa-server'></i> <span>UCMs</span></a></li>
<li class="treeview">
    <a href="#"><i class="fa fa-phone"></i> <span>Phones</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li><a href='{{ backpack_url('phone') }}'><span>Search</span></a></li>
{{--        <li><a href='{{ backpack_url('remote-operation') }}'><span>Action History</span></a></li>--}}
        <li><a href='{{ backpack_url('report') }}'><span>Download Reports</span></a></li>
    </ul>
</li>
<li class="treeview">
    <a href="#"><i class="fa fa-image"></i> <span>Background Images</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li><a href='{{ backpack_url('bgimage') }}'><span>Images</span></a></li>
        <li><a href='{{ backpack_url('bgimage-history') }}'><span>History</span></a></li>
    </ul>
</li>
<li><a href='{{ backpack_url('itl-history') }}'><i class='fa fa-lock'></i> <span>ITL Delete History</span></a></li>
<li><a href="{{ backpack_url('user') }}"><i class="fa fa-user"></i> <span>Users</span></a></li>
<li><a href="{{ backpack_url('settings') }}"><i class="fa fa-gears"></i> <span>Settings</span></a></li>

