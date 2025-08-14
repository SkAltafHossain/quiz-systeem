@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    @if(request('category'))
                        {{ $categories->firstWhere('slug', request('category'))->name }} Quizzes
                    @else
                        All Quizzes
                    @endif
                </h1>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            @php
                                $sortLabels = [
                                    'newest' => 'Newest',
                                    'title' => 'Title',
                                    'popular' => 'Most Popular'
                                ];
                                $currentSort = request('sort', 'newest');
                            @endphp
                            Sort: {{ $sortLabels[$currentSort] ?? 'Newest' }}
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                            <li><a class="dropdown-item {{ request('sort', 'newest') === 'newest' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}">Newest</a></li>
                            <li><a class="dropdown-item {{ request('sort') === 'title' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'title']) }}">Title</a></li>
                            <li><a class="dropdown-item {{ request('sort') === 'popular' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}">Most Popular</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('quizzes.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" 
                                   name="search" 
                                   class="form-control form-control-lg" 
                                   placeholder="Search for quizzes..." 
                                   value="{{ request('search') }}">
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quiz Grid -->
            @if($quizzes->count() > 0)
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($quizzes as $quiz)
                        <div class="col">
                            <div class="card h-100 quiz-card">
                                @if($quiz->image)
                                    <img src="{{ Storage::url($quiz->image) }}" class="card-img-top" alt="{{ $quiz->title }}">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="fas fa-question-circle fa-4x text-muted"></i>
                                    </div>
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-primary">{{ $quiz->category->name }}</span>
                                        <small class="text-muted">{{ $quiz->question_count }} questions</small>
                                    </div>
                                    <h5 class="card-title">{{ $quiz->title }}</h5>
                                    <p class="card-text text-muted flex-grow-1">
                                        {{ Str::limit($quiz->description, 100) }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <i class="fas fa-users text-muted me-1"></i>
                                            <small class="text-muted">{{ $quiz->results_count }} attempts</small>
                                        </div>
                                        <a href="{{ route('quizzes.show', $quiz->slug) }}" class="btn btn-outline-primary">
                                            {{ $quiz->results->count() > 0 ? 'Retake' : 'Start' }} Quiz
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $quizzes->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-search fa-4x text-muted"></i>
                    </div>
                    <h4>No quizzes found</h4>
                    <p class="text-muted">
                        @if(request('search'))
                            No quizzes match your search criteria. Try different keywords.
                        @else
                            There are no quizzes available at the moment. Please check back later.
                        @endif
                    </p>
                    <a href="{{ route('quizzes.index') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to All Quizzes
                    </a>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Categories -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('quizzes.index') }}" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ !request('category') ? 'active' : '' }}">
                        All Categories
                        <span class="badge bg-primary rounded-pill">{{ \App\Models\Quiz::published()->count() }}</span>
                    </a>
                    @foreach($categories as $category)
                        @if($category->quizzes_count > 0)
                            <a href="{{ route('quizzes.index', ['category' => $category->slug]) }}" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request('category') === $category->slug ? 'active' : '' }}">
                                {{ $category->name }}
                                <span class="badge bg-primary rounded-pill">{{ $category->quizzes_count }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Popular Quizzes -->
            @if($popularQuizzes->count() > 0)
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Popular Quizzes</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($popularQuizzes as $quiz)
                            <a href="{{ route('quizzes.show', $quiz->slug) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $quiz->title }}</h6>
                                    <small>{{ $quiz->results_count }} <i class="fas fa-users"></i></small>
                                </div>
                                <small class="text-muted">{{ $quiz->category->name }}</small>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .quiz-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .quiz-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .card-img-top {
        height: 150px;
        object-fit: cover;
    }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .list-group-item.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>
@endpush
@endsection
