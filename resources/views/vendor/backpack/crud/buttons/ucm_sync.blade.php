@if ($crud->hasAccess('list'))
    @if($entry->sync_in_progress)
    <!-- Split button -->
    <div class="btn-group">
        <button type="button" class="btn btn-xs btn-default disabled">Sync In Progress</button>
    </div>
    @else
    <div class="btn-group">
{{--        <button type="button" class="btn btn-xs btn-success">Sync Actions</button>--}}
{{--        <button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--            <span class="caret"></span>--}}
{{--            <span class="sr-only">Toggle Dropdown</span>--}}
{{--        </button>--}}
{{--        <ul class="dropdown-menu">--}}
{{--            <li><a href="{{ url($crud->route.'/'.$entry->getKey().'/sync') }}">Device & Status</a></li>--}}
{{--            <li><a href="{{ url($crud->route.'/'.$entry->getKey().'/update-realtime') }}">Status Only</a></li>--}}
{{--        </ul>--}}

{{--        <button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--            Sync Actions--}}
{{--            <span class="caret"></span>--}}
{{--            <span class="sr-only">Toggle Dropdown</span>--}}
{{--        </button>--}}
{{--        <ul class="dropdown-menu">--}}
{{--            <li><a href="{{ url($crud->route.'/'.$entry->getKey().'/sync') }}">Device & Status</a></li>--}}
{{--            <li><a href="{{ url($crud->route.'/'.$entry->getKey().'/update-realtime') }}">Status Only</a></li>--}}
{{--        </ul>--}}

        <div class="dropdown">
            <button class="btn btn-xs btn-default dropdown-toggle" type="button" data-toggle="dropdown" style="color:green">Sync Actions
                <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><a href="{{ url($crud->route.'/'.$entry->getKey().'/sync') }}">Device & Status</a></li>
                <li><a href="{{ url($crud->route.'/'.$entry->getKey().'/update-realtime') }}">Status Only</a></li>
            </ul>
        </div>

    </div>
    @endif
@endif
