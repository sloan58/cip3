@if($entry->result == "success")
<a href="{{env('APP_URL')}}/storage/screenshots/{{$entry->id}}_{{$entry->phone}}.png" target="_blank">
    <img src="{{env('APP_URL')}}/storage/screenshots/{{$entry->id}}_{{$entry->phone}}.png" style="
        height: 106px;
        width: 160px;
        border-radius: 3px;">
</a>
@endif

