@props([
    'editRoute' => '#',
    'deleteRoute' => '#',
    'viewRoute' => null,
    'deleteClass' => 'delete-item-btn',
    'model' => null,
    'showView' => false,
    'showEdit' => true,
    'showDelete' => true,
    'showStatusToggle' => true,
])

<div class="d-flex justify-content-center">
    @if($showView && $viewRoute)
        <a href="{{ $viewRoute }}" class="btn btn-sm btn-info text-white me-1" data-bs-toggle="tooltip" title="View">
            <i class="fas fa-eye"></i>
        </a>
    @endif
    
    @if($showEdit)
        <button type="button" class="btn btn-sm btn-primary me-1 edit-item-btn" 
                data-url="{{ $editRoute }}" 
                data-bs-toggle="tooltip" 
                title="Edit">
            <i class="fas fa-edit"></i>
        </button>
    @endif
    
    @if($showDelete)
        <button type="button" class="btn btn-sm btn-danger me-1 {{ $deleteClass }}" 
                data-url="{{ $deleteRoute }}" 
                data-bs-toggle="tooltip" 
                title="Delete">
            <i class="fas fa-trash"></i>
        </button>
    @endif
    
    @if($showStatusToggle && $model)
        <div class="form-check form-switch d-flex align-items-center ms-2">
            <input class="form-check-input status-toggle" 
                   type="checkbox" 
                   role="switch"
                   data-url="{{ route('admin.categories.toggle-status', $model->id) }}"
                   id="statusToggle{{ $model->id }}"
                   {{ $model->is_active ? 'checked' : '' }}
                   data-bs-toggle="tooltip"
                   title="{{ $model->is_active ? 'Active' : 'Inactive' }}">
        </div>
    @endif
</div>
