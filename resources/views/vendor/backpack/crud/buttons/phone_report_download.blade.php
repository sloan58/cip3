@if($entry->status == "finished")
    <a href="{{ Storage::url($entry->filename) }}" class="btn btn-xs btn-default" style="color:green">
        <i class="fa fa-cloud-download"></i> Download
    </a>
@endif
