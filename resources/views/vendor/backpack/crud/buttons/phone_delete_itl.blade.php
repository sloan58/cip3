@if ($entry->itl && $entry->isRegistered())
    <a href="{{ url($crud->route.'/'.$entry->getKey().'/delete-itl') }}" class="btn btn-xs btn-default" style="color:green">
        <i class="fa fa-bomb"></i> Delete ITL
    </a>
@endif
