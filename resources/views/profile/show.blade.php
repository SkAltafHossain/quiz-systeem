@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Profile</h5>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <img src="{{ Auth::user()->avatar_url }}" 
                                 alt="{{ Auth::user()->name }}" 
                                 class="img-fluid rounded-circle mb-3" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                            <h4>{{ Auth::user()->name }}</h4>
                            <p class="text-muted">
                                Member since {{ Auth::user()->created_at->format('F Y') }}
                            </p>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h5>About Me</h5>
                                <p class="text-muted">
                                    {{ Auth::user()->bio ?? 'No bio provided.' }}
                                </p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light mb-3">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Quizzes Taken</h6>
                                            <h3 class="mb-0">{{ Auth::user()->quizzes_taken }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light mb-3">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Average Score</h6>
                                            <h3 class="mb-0">{{ number_format(Auth::user()->average_score, 1) }}%</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($recentResults->isNotEmpty())
                        <hr>
                        <h5 class="mb-3">Recent Quiz Results</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Quiz</th>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentResults as $result)
                                        <tr>
                                            <td>{{ $result->quiz->title }}</td>
                                            <td>{{ $result->created_at->format('M d, Y') }}</td>
                                            <td>{{ $result->score }}/{{ $result->total_questions }} ({{ $result->percentage }}%)</td>
                                            <td>{{ $result->time_taken_formatted }}</td>
                                            <td>
                                                @if($result->passed)
                                                    <span class="badge bg-success">Passed</span>
                                                @else
                                                    <span class="badge bg-danger">Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="#" class="btn btn-outline-primary btn-sm">View All Results</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-trophy fa-4x text-muted"></i>
                            </div>
                            <h5>No quiz results yet</h5>
                            <p class="text-muted">Take your first quiz to see your results here!</p>
                            <a href="{{ route('quizzes.index') }}" class="btn btn-primary">Browse Quizzes</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
