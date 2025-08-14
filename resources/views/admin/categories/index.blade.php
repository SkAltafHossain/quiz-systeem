@extends('admin.layouts.app')

@section('title', 'Categories')

@push('styles')
    <style>
        .category-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
        }
        
        .category-actions {
            white-space: nowrap;
            width: 120px;
        }
        
        .status-toggle {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Categories</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="fas fa-plus me-1"></i> Add New
            </button>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="categoriesTable" class="table table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Quizzes</th>
                                <th>Status</th>
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
    
    <!-- Create Category Modal -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="createCategoryForm" action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input class="form-control @error('image') is-invalid @enderror" type="file" id="image" name="image" accept="image/*">
                            <div class="form-text">Recommended size: 400x300px. Max file size: 2MB.</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCategoryForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3" id="categoryImagePreview">
                            <img src="" alt="Category Image" class="img-thumbnail mb-2" style="max-height: 150px; display: none;">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="edit_name" name="name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="edit_description" name="description" rows="3"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Image</label>
                            <input class="form-control @error('image') is-invalid @enderror" type="file" id="edit_image" name="image" accept="image/*">
                            <div class="form-text">Leave empty to keep current image. Recommended size: 400x300px. Max file size: 2MB.</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                <label class="form-check-label" for="remove_image">Remove current image</label>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteCategoryModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this category? This action cannot be undone.</p>
                    <p class="mb-0"><strong>Note:</strong> Categories with associated quizzes cannot be deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteCategoryForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Category</button>
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
            const table = $('#categoriesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.categories.index") }}',
                    type: 'GET',
                    data: function(d) {
                        // Add any additional filters here
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
                                return `<img src="{{ asset('storage') }}/${data}" alt="${row.name}" class="category-image">`;
                            }
                            return '<i class="fas fa-folder text-muted fa-2x"></i>';
                        },
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    { 
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            return `<strong>${data}</strong>`;
                        }
                    },
                    { 
                        data: 'description',
                        name: 'description',
                        render: function(data) {
                            return data ? data : '<span class="text-muted">No description</span>';
                        }
                    },
                    { 
                        data: 'quizzes_count',
                        name: 'quizzes_count',
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
                        className: 'text-center category-actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[2, 'asc']], // Default sort by name
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: 'No categories found',
                    zeroRecords: 'No matching categories found'
                },
                responsive: true,
                drawCallback: function() {
                    // Re-initialize tooltips after table is drawn
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
            
            // Handle create form submission
            $('#createCategoryForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalBtnText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#createCategoryModal').modal('hide');
                            table.ajax.reload(null, false);
                            showToast('success', 'Success', response.message);
                            form.trigger('reset');
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            // Clear previous errors
                            form.find('.is-invalid').removeClass('is-invalid');
                            form.find('.invalid-feedback').remove();
                            
                            // Show new errors
                            Object.keys(errors).forEach(field => {
                                const input = form.find(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                
                                let errorMessage = errors[field][0];
                                if (field === 'image' && errorMessage.includes('must be an image')) {
                                    errorMessage = 'Please upload a valid image file (jpeg, png, jpg, gif, svg)';
                                }
                                
                                input.after(`<div class="invalid-feedback d-block">${errorMessage}</div>`);
                            });
                        } else {
                            showToast('error', 'Error', xhr.responseJSON?.message || 'An error occurred while saving the category');
                        }
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });
            
            // Handle edit button click
            $(document).on('click', '.edit-category-btn', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                
                // Show loading state
                const modal = $('#editCategoryModal');
                modal.find('.modal-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                modal.modal('show');
                
                // Load category data
                $.get(url, function(response) {
                    const category = response.data;
                    const form = $('#editCategoryForm');
                    
                    // Update form action URL
                    form.attr('action', `/admin/categories/${category.id}`);
                    
                    // Fill form fields
                    form.find('#edit_name').val(category.name);
                    form.find('#edit_description').val(category.description || '');
                    form.find('#edit_is_active').prop('checked', category.is_active);
                    form.find('#remove_image').prop('checked', false);
                    
                    // Update image preview
                    const imagePreview = modal.find('#categoryImagePreview');
                    if (category.image_url) {
                        imagePreview.find('img')
                            .attr('src', category.image_url)
                            .show();
                        imagePreview.find('.remove-image').show();
                    } else {
                        imagePreview.find('img').hide();
                        imagePreview.find('.remove-image').hide();
                    }
                    
                    // Show the form
                    modal.find('.modal-body').html(form.html());
                }).fail(function(xhr) {
                    showToast('error', 'Error', xhr.responseJSON?.message || 'Failed to load category data');
                    modal.modal('hide');
                });
            });
            
            // Handle edit form submission
            $(document).on('submit', '#editCategoryForm', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalBtnText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#editCategoryModal').modal('hide');
                            table.ajax.reload(null, false);
                            showToast('success', 'Success', response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            // Clear previous errors
                            form.find('.is-invalid').removeClass('is-invalid');
                            form.find('.invalid-feedback').remove();
                            
                            // Show new errors
                            Object.keys(errors).forEach(field => {
                                const input = form.find(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                
                                let errorMessage = errors[field][0];
                                if (field === 'image' && errorMessage.includes('must be an image')) {
                                    errorMessage = 'Please upload a valid image file (jpeg, png, jpg, gif, svg)';
                                }
                                
                                input.after(`<div class="invalid-feedback d-block">${errorMessage}</div>`);
                            });
                        } else {
                            showToast('error', 'Error', xhr.responseJSON?.message || 'An error occurred while updating the category');
                        }
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });
            
            // Handle delete button click
            $(document).on('click', '.delete-category-btn', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                $('#deleteCategoryForm').attr('action', url);
                $('#deleteCategoryModal').modal('show');
            });
            
            // Handle delete form submission
            $('#deleteCategoryForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalBtnText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteCategoryModal').modal('hide');
                            table.ajax.reload(null, false);
                            showToast('success', 'Success', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Error', xhr.responseJSON?.message || 'An error occurred while deleting the category');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });
            
            // Toggle category status
            $(document).on('click', '.status-toggle', function() {
                const checkbox = $(this);
                const url = checkbox.data('url');
                const isChecked = checkbox.is(':checked');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PATCH',
                        is_active: isChecked ? 1 : 0
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
            
            // Reset form when modal is hidden
            $('#createCategoryModal, #editCategoryModal').on('hidden.bs.modal', function() {
                $(this).find('form').trigger('reset');
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').remove();
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
