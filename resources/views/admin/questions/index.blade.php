@extends('admin.layouts.app')

@section('title', 'Manage Questions - ' . $quiz->title)

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .question-type-badge { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
    .option-item { position: relative; margin-bottom: 0.75rem; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 0.25rem; background-color: #f8f9fa; }
    .option-actions { position: absolute; top: 0.5rem; right: 0.5rem; }
    .option-item.correct-option { border-color: #198754; background-color: rgba(25, 135, 84, 0.1); }
    .sortable-ghost { opacity: 0.5; background: #c8ebfb; }
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
                        <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">Quizzes</a></li>
                        <li class="breadcrumb-item active">Questions - {{ $quiz->title }}</li>
                    </ol>
                </div>
                <h4 class="page-title">Manage Questions</h4>
                <p class="text-muted mb-0">
                    Add, edit, or remove questions for the quiz: <strong>{{ $quiz->title }}</strong>
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title">Questions</h4>
                        <button type="button" class="btn btn-primary" id="addQuestionBtn">
                            <i class="fas fa-plus me-1"></i> Add Question
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="questions-table" class="table table-striped table-hover dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question</th>
                                    <th>Type</th>
                                    <th>Points</th>
                                    <th>Options</th>
                                    <th>Status</th>
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

<!-- Question Form Modal -->
<div class="modal fade" id="questionFormModal" tabindex="-1" aria-labelledby="questionFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionFormModalLabel">Add New Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="questionForm" action="{{ route('admin.quizzes.questions.store', $quiz->id) }}" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="question_text" class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                            <div id="question_textError" class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Question Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="single_choice">Single Choice</option>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True/False</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                            <div id="typeError" class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="points" class="form-label">Points <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="points" name="points" min="1" value="1" required>
                            <div id="pointsError" class="invalid-feedback"></div>
                        </div>

                        <div class="col-12 mb-3" id="optionsContainer">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Options <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addOptionBtn">
                                    <i class="fas fa-plus"></i> Add Option
                                </button>
                            </div>
                            <div id="optionsList">
                                <!-- Options will be added here dynamically -->
                            </div>
                            <div id="optionsError" class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active (visible to users)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <span id="submitButtonText">Add Question</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this question? This action cannot be undone.</p>
                <p class="text-danger">All options and related data will also be deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span>Delete Question</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#questions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("admin.quizzes.questions.index", $quiz->id) }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'question_text', name: 'question_text' },
                { data: 'type', name: 'type' },
                { data: 'points', name: 'points' },
                { data: 'options_count', name: 'options_count' },
                { 
                    data: 'is_active', 
                    name: 'is_active',
                    render: function(data) {
                        return data ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                    }
                },
                { 
                    data: 'actions', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-nowrap'
                }
            ]
        });

        // Add new question button
        $('#addQuestionBtn').on('click', function() {
            const modal = $('#questionFormModal');
            const form = modal.find('form');
            
            // Reset form
            form.trigger('reset');
            form.attr('action', '{{ route("admin.quizzes.questions.store", $quiz->id) }}');
            form.find('input[name="_method"]').val('POST');
            
            // Update UI
            modal.find('.modal-title').text('Add New Question');
            modal.find('#submitButtonText').text('Add Question');
            
            // Reset options
            $('#optionsList').empty();
            addOption('', true);
            addOption('', false);
            
            // Show modal
            modal.modal('show');
        });

        // Form submission
        $('#questionForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            
            // Reset validation
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            
            // Show loading state
            spinner.removeClass('d-none');
            submitBtn.prop('disabled', true);
            
            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                type: form.find('input[name="_method"]').val() === 'PUT' ? 'POST' : 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#questionFormModal').modal('hide');
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
                        showToast('error', 'Error', xhr.responseJSON?.message || 'An error occurred');
                    }
                },
                complete: function() {
                    spinner.addClass('d-none');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        // Delete question
        let deleteQuestionId = null;
        $(document).on('click', '.delete-question-btn', function() {
            deleteQuestionId = $(this).data('id');
            $('#deleteQuestionModal').modal('show');
        });

        // Confirm delete
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
                url: `/admin/quizzes/{{ $quiz->id }}/questions/${deleteQuestionId}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', 'Success', response.message);
                        table.ajax.reload(null, false);
                        $('#deleteQuestionModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    showToast('error', 'Error', xhr.responseJSON?.message || 'Failed to delete question');
                },
                complete: function() {
                    spinner.addClass('d-none');
                    btnText.text('Delete Question');
                    btn.prop('disabled', false);
                }
            });
        });

        // Add option button
        $('#addOptionBtn').on('click', function() {
            addOption();
        });

        // Remove option button
        $(document).on('click', '.remove-option', function() {
            if ($('.option-item').length > 2) {
                $(this).closest('.option-item').remove();
                updateOptionIndexes();
            } else {
                showToast('warning', 'Warning', 'A question must have at least 2 options');
            }
        });

        // Toggle options container based on question type
        $('#type').on('change', function() {
            const type = $(this).val();
            const isMultiple = type === 'multiple_choice';
            
            // Update correct answer input type
            $('.option-correct').attr('type', isMultiple ? 'checkbox' : 'radio');
            
            // If switching to true/false, set default options
            if (type === 'true_false') {
                $('#optionsList').empty();
                addOption('True', true);
                addOption('False', false);
            } else if ($('.option-item').length < 2) {
                // Ensure at least 2 options for other types
                $('#optionsList').empty();
                addOption('', true);
                addOption('', false);
            }
        });

        // Add a new option
        function addOption(text = '', isCorrect = false) {
            const index = $('.option-item').length;
            const optionHtml = `
                <div class="option-item" data-index="${index}">
                    <div class="d-flex align-items-start">
                        <div class="form-check me-2 mt-1">
                            <input class="form-check-input option-correct" 
                                   type="${$('#type').val() === 'multiple_choice' ? 'checkbox' : 'radio'}" 
                                   name="options[${index}][is_correct]" 
                                   value="1"
                                   ${isCorrect ? 'checked' : ''}>
                        </div>
                        <div class="flex-grow-1">
                            <input type="text" class="form-control mb-2 option-text" 
                                   name="options[${index}][option_text]" 
                                   placeholder="Option ${index + 1}" 
                                   value="${text}" required>
                        </div>
                        <div class="option-actions">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-option" title="Remove option">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('#optionsList').append(optionHtml);
            updateOptionIndexes();
        }

        // Update option indexes for form submission
        function updateOptionIndexes() {
            $('.option-item').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('.option-text').attr('name', `options[${index}][option_text]`);
                $(this).find('.option-correct').attr('name', `options[${index}][is_correct]`);
            });
        }
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
