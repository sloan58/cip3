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
        // Set the phone name as a hidden input and fetch image files
        $('#pushBackground').on('show.bs.modal', function(e) {

            // Clear the select list
            $('select[name="image"]').html("");

            // Set the hidden input
            let phoneName = $(e.relatedTarget).data('phone-name');
            $(e.currentTarget).find('input[name="phone"]').val(phoneName);

            // Fetch the images available to this phone model
            fetch(`/api/phone-images/${phoneName}`)
                .then(
                    function(response) {
                        if (response.status !== 200) {
                            console.log('Looks like there was a problem. Status Code: ' +
                                response.status);
                            return;
                        }

                        // Build the select list
                        response.json().then(function(data) {
                            $('select[name="image"]')
                                .html( '<option>' + data.images.join( '</option><option>' ) + '</option>' );
                        });
                    }
                )
                .catch(function(err) {
                    console.log('Fetch Error :-S', err);
                });
        });
    </script>
@endpush
