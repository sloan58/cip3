<!-- Export Phones Modal -->
<div class="modal fade" id="bulkDeleteItl" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Bulk Delete ITL's</h4>
            </div>
            <form method="POST" action="{{ backpack_url('bulk-itl') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">File input</label>
                        <input type="file" id="bulkItlDeleteInputFile" name="bulkItlDeleteInputFile">
                        <p class="help-block">Load a txt/csv file of the MAC addresses to process</p>
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
