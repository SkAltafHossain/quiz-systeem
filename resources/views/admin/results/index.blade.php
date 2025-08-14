@extends('admin.layouts.app')

@section('title', 'Quiz Results')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .filter-card {
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1.25rem;
        background-color: #f8f9fa;
    }
    .filter-card .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #495057;
    }
    .filter-card .form-group {
        margin-bottom: 1rem;
    }
    .filter-card .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    .btn-export {
        margin-left: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Quiz Results</li>
                    </ol>
                </div>
                <h4 class="page-title">Quiz Results</h4>
                <p class="text-muted mb-0">View and manage all quiz results</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="filter-card">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="quizFilter" class="form-label">Quiz</label>
                            <select class="form-select" id="quizFilter">
                                <option value="">All Quizzes</option>
                                @foreach($quizzes as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="userFilter" class="form-label">User</label>
                            <select class="form-select" id="userFilter">
                                <option value="">All Users</option>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="dateRange" class="form-label">Date Range</label>
                            <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="button" class="btn btn-primary w-100" id="applyFilters">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('admin.results.export') }}" class="btn btn-outline-secondary w-100 mt-2" id="exportResults">
                                <i class="fas fa-file-export me-1"></i> Export to CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="results-table" class="table table-striped table-hover dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Quiz</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Completed At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this result? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span>Delete Result</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
            }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Initialize DataTable
        const table = $('#results-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.results.index") }}',
                data: function(d) {
                    d.quiz_id = $('#quizFilter').val();
                    d.user_id = $('#userFilter').val();
                    d.date_range = $('#dateRange').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'user_name', name: 'user_name' },
                { data: 'quiz_title', name: 'quiz_title' },
                { data: 'score_display', name: 'score_display', orderable: false },
                { data: 'status_badge', name: 'is_passed', orderable: false },
                { data: 'completion_time', name: 'completed_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-nowrap' }
            ],
            order: [[5, 'desc']], // Sort by completion time by default
            drawCallback: function() {
                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        // Apply filters
        $('#applyFilters').on('click', function() {
            table.ajax.reload();
            
            // Update export link with current filters
            const quizId = $('#quizFilter').val();
            const userId = $('#userFilter').val();
            const dateRange = $('#dateRange').val();
            
            let exportUrl = '{{ route("admin.results.export") }}';
            const params = new URLSearchParams();
            
            if (quizId) params.append('quiz_id', quizId);
            if (userId) params.append('user_id', userId);
            if (dateRange) params.append('date_range', dateRange);
            
            $('#exportResults').attr('href', exportUrl + '?' + params.toString());
        });

        // Delete result
        let deleteResultId = null;
        
        $(document).on('click', '.delete-result-btn', function(e) {
            e.preventDefault();
            deleteResultId = $(this).data('id');
            $('#deleteResultModal').modal('show');
        });

        $('#confirmDeleteBtn').on('click', function() {
            const btn = $(this);
            const spinner = btn.find('.spinner-border');
            const btnText = btn.find('span:not(.spinner-border)');
            
            // Show loading state
            spinner.removeClass('d-none');
            btnText.text('Deleting...');
            btn.prop('disabled', true);
            
            // Send delete request
            $.ajax({
                url: `/admin/results/${deleteResultId}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', 'Success', response.message);
                        table.ajax.reload(null, false);
                        $('#deleteResultModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    showToast('error', 'Error', xhr.responseJSON?.message || 'Failed to delete result');
                },
                complete: function() {
                    spinner.addClass('d-none');
                    btnText.text('Delete Result');
                    btn.prop('disabled', false);
                }
            });
        });
    });
    
    // Toast notification function
    function showToast(icon, title, message) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: icon,
            title: title,
            text: message
        });
    }
</script>
@endpush
