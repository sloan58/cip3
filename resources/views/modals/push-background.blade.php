<!-- Export Phones Modal -->
<div class="modal fade" id="pushBackground" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Push Background Image</h4>
            </div>
            <form method="POST" action="{{ backpack_url('push-background') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <div class="form-group">
                            <label for="image">Available Files</label>
                            <select class="form-control" id="image" name="image">
                                @foreach($images as $image)
                                    <option>{{ $image }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('after_scripts')
    <script>
        console.log('too');
        $('#pushBackground').on('show.bs.modal', function(e) {
            let phoneName = $(e.relatedTarget).data('phone-name');
            $(e.currentTarget).find('input[name="phone"]').val(phoneName);
        });
    </script>
@endpush
