@extends('admin.admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Tous les formateurs</h5>
                <a href="#" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Ajouter un formateur
                </a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Instructor Name </th>
                        <th>Username </th>
                        <th>Email </th>
                        <th>Phone </th>
                        <th>Status </th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($allinstructor as $key=> $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->username }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->phone }}</td>
                            <td> @if ($item->status == 1)
                                <span class="btn btn-success">Active </span>
                                @else
                                <span class="btn btn-danger">InActive </span>
                                @endif
                            </td>


                            <td>
    <div class="form-check-danger form-check form-switch">
        <input class="form-check-input status-toggle large-checkbox" type="checkbox" id="flexSwitchCheckCheckedDanger" data-user-id="{{ $item->id }}" {{ $item->status ? 'checked' : ''}}  >
        <label class="form-check-label" for="flexSwitchCheckCheckedDanger"> </label>
    </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Script de confirmation pour la suppression -->

<script>
    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            var userId = $(this).data('user-id');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "{{ route('update.user.status') }}",
                method: "POST",
                data: {
                    user_id : userId,
                    is_checked: isChecked ? 1 : 0,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response){
                    toastr.success(response.message);
                },
                error: function(){

                }
            });

        });
    });
</script>
<!-- jQuery (nécessaire pour Toastr) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<!-- Toastr CSS et JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if(session('success'))
    <script>
        $(document).ready(function() {
            toastr.success('{{ session('success') }}', 'Succès', {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            });
        });
    </script>
@endif

@if(session('error'))
    <script>
        $(document).ready(function() {
            toastr.error('{{ session('error') }}', 'Erreur', {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            });
        });
    </script>
@endif

@endsection
