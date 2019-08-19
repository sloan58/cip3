@if($entry->type == "background-push")
<a href="https://cip3.test/storage/screenshots/{{$entry->id}}_{{$entry->phone}}.png" target="_blank">
    <img src="https://cip3.test/storage/screenshots/{{$entry->id}}_{{$entry->phone}}.png" style="
        height: 106px;
        width: 160px;
        border-radius: 3px;">
</a>
@endif
