@extends('admin.layouts.app')

@section('title', 'Quizzes')

@push('styles')
    <style>
        .quiz-image {
            width: 60px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
        }
        
        .quiz-actions {
            white-space: nowrap;
            width: 140px;
        }
        
        .status-toggle {
            cursor: pointer;
        }
        
        .filter-card {
            margin-bottom: 1.5rem;
        }
        
        .filter-card .card-body {
            padding: 1rem;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            margin-top: 10px;
        }
        
        .image-preview-container {
            margin-top: 10px;
        }
        
        .datetime-picker {
            position: relative;
        }
        
        .datetime-picker .form-control[readonly] {
            background-color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Quizzes</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                <i class="fas fa-plus me-1"></i> Add New
            </button>
        </div>
        
        <!-- Filters -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-4">
                        <label for="categoryFilter" class="form-label">Category</label>
                        <select class="form-select" id="categoryFilter" name="category_id">
                            <option value="all">All Categories</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select class="form-select" id="statusFilter" name="status">
                            <option value="all">All Statuses</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="searchInput" class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search quizzes...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="applyFilters">
                            <i class="fas fa-filter me-1"></i> Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quizzes Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="quizzesTable" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th class="text-center">Questions</th>
                                <th class="text-center">Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quiz Form Modal (Used for both Create and Edit) -->
    <div class="modal fade" id="quizFormModal" tabindex="-1" aria-labelledby="quizFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quizFormModalLabel">Create New Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quizForm" action="{{ route('admin.quizzes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="form_method" name="_method" value="POST">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    <div class="invalid-feedback" id="titleError"></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    <div class="invalid-feedback" id="descriptionError"></div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="categoryIdError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                            <input type="number" class="form-control" id="time_limit" name="time_limit" min="1">
                                            <div class="form-text">Leave empty for no time limit</div>
                                            <div class="invalid-feedback" id="timeLimitError"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="passing_score" class="form-label">Passing Score (%)</label>
                                            <input type="number" class="form-control" id="passing_score" name="passing_score" value="70" min="1" max="100">
                                            <div class="invalid-feedback" id="passingScoreError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_attempts" class="form-label">Max Attempts</label>
                                            <input type="number" class="form-control" id="max_attempts" name="max_attempts" min="1">
                                            <div class="form-text">Leave empty for unlimited attempts</div>
                                            <div class="invalid-feedback" id="maxAttemptsError"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3 datetime-picker">
                                            <label for="started_at" class="form-label">Start Date & Time</label>
                                            <input type="datetime-local" class="form-control" id="started_at" name="started_at">
                                            <div class="invalid-feedback" id="startedAtError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3 datetime-picker">
                                            <label for="ended_at" class="form-label">End Date & Time</label>
                                            <input type="datetime-local" class="form-control" id="ended_at" name="ended_at">
                                            <div class="form-text">Leave empty for no end date</div>
                                            <div class="invalid-feedback" id="endedAtError"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1">
                                    <label class="form-check-label" for="is_published">Publish this quiz</label>
                                    <div class="invalid-feedback" id="isPublishedError"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Quiz Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div class="invalid-feedback" id="imageError"></div>
                                    <div class="mt-2">
                                        <img id="imagePreview" src="{{ asset('images/placeholder-image.png') }}" alt="Quiz Image Preview" class="img-thumbnail" style="max-width: 100%; height: auto;">
                                    </div>
                                    <div class="form-check mt-2" id="removeImageContainer" style="display: none;">
                                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                        <label class="form-check-label" for="remove_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="submitButtonText">Create Quiz</span>
                            </button>
                        </div>
                    </form>
                </div>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Update Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteQuizModal" tabindex="-1" aria-labelledby="deleteQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteQuizModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this quiz? This action cannot be undone.</p>
                    <p class="mb-0 text-danger"><strong>Warning:</strong> This will also delete all questions and results associated with this quiz.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteQuizForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Delete Quiz
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#quizzesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.quizzes.index") }}',
                    type: 'GET',
                    data: function(d) {
                        d.category_id = $('#categoryFilter').val();
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
                    { 
                        data: 'image', 
                        name: 'image',
                        render: function(data, type, row) {
                            if (data) {
                                return `<img src="{{ asset('storage') }}/${data}" alt="${row.title}" class="quiz-image">`;
                            }
                            return '<i class="fas fa-question-circle text-muted fa-2x"></i>';
                        },
                        orderable: false,
                        searchable: false,
                        width: '5%',
                        className: 'text-center'
                    },
                    { 
                        data: 'title',
                        name: 'title',
                        render: function(data, type, row) {
                            return `<strong>${data}</strong>`;
                        }
                    },
                    { 
                        data: 'category_name',
                        name: 'category_name'
                    },
                    { 
                        data: 'questions_count',
                        name: 'questions_count',
                        className: 'text-center',
                        searchable: false
                    },
                    { 
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    { 
                        data: 'created_at_formatted',
                        name: 'created_at',
                        className: 'text-nowrap'
                    },
                    { 
                        data: 'actions',
                        name: 'actions',
                        className: 'text-center quiz-actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[1, 'asc']], // Default sort by title
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: 'No quizzes found',
                    zeroRecords: 'No matching quizzes found'
                },
                responsive: true,
                drawCallback: function() {
                    // Re-initialize tooltips after table is drawn
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
            
            // Reset form when modal is hidden
            $('#quizFormModal').on('hidden.bs.modal', function () {
                const form = $(this).find('form');
                form.trigger('reset');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');
                
                // Reset image preview
                $('#imagePreview').attr('src', '{{ asset("images/placeholder-image.png") }');
                $('#removeImageContainer').hide();
            });
            
            // Image preview for create form
            $('#image').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Image preview for edit form
            $(document).on('change', '#edit_image', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#editImagePreview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Handle create button click
            $('#createQuizBtn').on('click', function() {
                const modal = $('#quizFormModal');
                const form = modal.find('form');
                
                // Reset form and set default values
                form.trigger('reset');
                form.attr('action', '{{ route("admin.quizzes.store") }}');
                form.find('input[name="_method"]').val('POST');
                modal.find('.modal-title').text('Create New Quiz');
                modal.find('#submitButtonText').text('Create Quiz');
                
                // Reset image preview and hide remove image option
                $('#imagePreview').attr('src', '{{ asset("images/placeholder-image.png") }');
                $('#removeImageContainer').hide();
                
                // Show modal
                modal.modal('show');
            });
            
            // Handle edit button click
            $(document).on('click', '.edit-quiz-btn', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                const quizId = $(this).data('id');
                const modal = $('#quizFormModal');
                const form = modal.find('form');
                
                // Show loading state
                const loadingHtml = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading quiz data...</p>
                    </div>`;
                
                // Set modal title and button text
                modal.find('.modal-title').text('Edit Quiz');
                modal.find('#submitButtonText').text('Update Quiz');
                
                // Show loading state
                modal.find('.modal-body').html(loadingHtml);
                modal.modal('show');
                
                // Load quiz data
                $.get(url, function(response) {
                    if (response.success) {
                        const quiz = response.data;
                        
                        // Update form action and method
                        form.attr('action', `/admin/quizzes/${quiz.id}`);
                        form.find('input[name="_method"]').val('PUT');
                        
                        // Fill form fields
                        form.find('#title').val(quiz.title);
                        form.find('#description').val(quiz.description || '');
                        form.find('#category_id').val(quiz.category_id);
                        form.find('#time_limit').val(quiz.time_limit || '');
                        form.find('#passing_score').val(quiz.passing_score || 70);
                        form.find('#max_attempts').val(quiz.max_attempts || '');
                        form.find('#is_published').prop('checked', quiz.is_published);
                        
                        // Set date and time fields
                        if (quiz.started_at) {
                            const startedAt = new Date(quiz.started_at);
                            form.find('#started_at').val(startedAt.toISOString().slice(0, 16));
                        } else {
                            form.find('#started_at').val('');
                        }
                        
                        if (quiz.ended_at) {
                            const endedAt = new Date(quiz.ended_at);
                            form.find('#ended_at').val(endedAt.toISOString().slice(0, 16));
                        } else {
                            form.find('#ended_at').val('');
                        }
                        
                        // Set image preview
                        if (quiz.image) {
                            $('#imagePreview').attr('src', `{{ asset('storage') }}/${quiz.image}`);
                            $('#removeImageContainer').show();
                        } else {
                            $('#imagePreview').attr('src', '{{ asset("images/placeholder-image.png") }');
                            $('#removeImageContainer').hide();
                        }
                        
                        // Reset remove image checkbox
                        form.find('#remove_image').prop('checked', false);
                    }
                }).fail(function(xhr) {
                    showToast('error', 'Error', xhr.responseJSON?.message || 'Failed to load quiz data');
                    modal.modal('hide');
                });
            });
            
            // Handle form submission (both create and update)
            $('#quizForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const spinner = submitBtn.find('.spinner-border');
                const isEdit = form.attr('action').includes('/quizzes/');
                
                // Reset validation
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');
                
                // Show loading state
                spinner.removeClass('d-none');
                submitBtn.prop('disabled', true);
                
                // Create FormData and append _method if needed
                const formData = new FormData(this);
                const method = form.find('input[name="_method"]').val();
                if (method && method !== 'POST') {
                    formData.append('_method', method);
                }
                
                // Submit form via AJAX
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#quizFormModal').modal('hide');
                            table.ajax.reload(null, false);
                            showToast('success', 'Success', response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            // Show validation errors
                            Object.keys(errors).forEach(field => {
                                const input = form.find(`[name="${field}"]`);
                                const errorMessage = errors[field][0];
                                
                                input.addClass('is-invalid');
                                $(`#${field}Error`).text(errorMessage);
                            });
                        } else {
                            showToast('error', 'Error', xhr.responseJSON?.message || 'An error occurred while saving the quiz');
                        }
                    },
                    complete: function() {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                    }
                });
            });
            
            // Image preview for the form
            $('#image').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Apply filters
            $('#applyFilters').on('click', function() {
                table.ajax.reload();
            });
            
            // Apply filters on Enter key in search input
            $('#searchInput').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    table.search(this.value).draw();
                }
            });
            
            // Clear search
            $('#clearSearch').on('click', function() {
                $('#searchInput').val('');
                table.search('').draw();
            });
            
            // Handle delete button click
            $(document).on('click', '.delete-quiz-btn', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                $('#deleteQuizForm').attr('action', url);
                $('#deleteQuizModal').modal('show');
            });
            
            // Handle delete form submission
            $('#deleteQuizForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const spinner = submitBtn.find('.spinner-border');
                
                // Show loading state
                spinner.removeClass('d-none');
                submitBtn.prop('disabled', true);
                
                // Submit form via AJAX
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteQuizModal').modal('hide');
                            table.ajax.reload(null, false);
                            showToast('success', 'Success', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Error', xhr.responseJSON?.message || 'An error occurred while deleting the quiz');
                    },
                    complete: function() {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                    }
                });
            });
            
            // Toggle quiz status
            $(document).on('change', '.status-toggle', function() {
                const checkbox = $(this);
                const url = checkbox.data('url');
                const isChecked = checkbox.is(':checked');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PATCH',
                        is_published: isChecked ? 1 : 0
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', 'Success', response.message);
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        checkbox.prop('checked', !isChecked);
                        showToast('error', 'Error', xhr.responseJSON?.message || 'Failed to update status');
                    }
                });
            });
        });
        
        // Helper function to show toast notifications
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
