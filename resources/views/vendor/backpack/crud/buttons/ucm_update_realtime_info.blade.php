@if ($crud->hasAccess('list'))
    @if($entry->sync_in_progress)
        <a href="#" class="btn btn-xs btn-default disabled" style="color:green" disabled>
            <i class="fa fa-exchange"></i> Sync In Progress
        </a>
    @else
        <a href="{{ url($crud->route.'/'.$entry->getKey().'/update-realtime') }} " class="btn btn-xs btn-default" style="color:green">
            <i class="fa fa-exchange"></i> Update Realtime Info
        </a>
    @endif
@endif
