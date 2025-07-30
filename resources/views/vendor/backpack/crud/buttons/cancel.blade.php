@if ($entry->status_id === 2 || $entry->status_id === 3)

    <a href="javascript:void(0)" onclick="cancelEntry({{ $entry->getKey() }})" class="btn btn-sm btn-link" data-button-type="delete"><i class="la la-trash"></i> Cancel</a>
@endif

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@loadOnce('cancel_button_script')
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    if (typeof cancelEntry != 'function') {
        $("[data-button-type=delete]").unbind('click');

        function cancelEntry(e) {
            // ask for confirmation before deleting an item
            // e.preventDefault();
            var route = '{{ env('APP_URL')  }}/event/' + e;
            console.log(e);
            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'Cancellation Reason',
                inputPlaceholder: 'Type your message here...',
                inputAttributes: {
                    'aria-label': 'Type your message here'
                },
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, cancel it!',
                customClass: {
                    validationMessage: 'my-validation-message'
                },
                preConfirm: (value) => {
                    if (!value) {
                        Swal.showValidationMessage(
                            '<i class="fa fa-info-circle"></i> Cancellation reason is required!'
                        )
                    }
                    if (value.length > 255) {
                        Swal.showValidationMessage(
                            '<i class="fa fa-info-circle"></i> Cancellation reason is length is too long!'
                        )
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: route,
                        method: 'DELETE',
                        dataType: 'JSON',
                        data: result,
                        success: function()
                        {
                            Swal.fire(
                                'Cancelled!',
                                'Your hearing has now been cancelled.',
                                'success'
                            ).then( () => {
                                window.location.replace('/event');
                            })
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            })
                        }
                    });
                }
            })

        }
    }

    // make it so that the function above is run after each DataTable draw event
    // crud.addFunctionToDataTablesDrawEventQueue('deleteEntry');
</script>
@if (!request()->ajax()) @endpush @endif
@endLoadOnce
