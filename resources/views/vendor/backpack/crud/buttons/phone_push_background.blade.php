@if ($entry->getFullSizeBgDimensions())
    <a data-toggle="modal" data-target="#pushBackground" data-phone-name="{{ $entry->id }}" class="btn btn-xs btn-default" style="color:green">
        <i class="fa fa-image"></i> Push Background
    </a>
@endif
