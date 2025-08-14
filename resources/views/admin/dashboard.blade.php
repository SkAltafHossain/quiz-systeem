@extends('admin.layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        .stat-card {
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
            position: relative;
        }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .stat-card .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .stat-card .stat-meta {
            font-size: 0.85rem;
            margin-top: 0.5rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .recent-activity {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .recent-activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            align-items: flex-start;
        }
        
        .recent-activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
            background-color: #f8f9fa;
            color: #6c757d;
        }
        
        .activity-details {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 500;
            margin-bottom: 0.1rem;
            line-height: 1.3;
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .card-header .btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
        }
        
        .progress {
            height: 6px;
            border-radius: 3px;
            margin-top: 0.5rem;
        }
        
        .progress-bar {
            background-color: #4e73df;
        }
        
        .quiz-score {
            font-weight: 600;
            color: #4e73df;
        }
        
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.5rem;
        }
        
        .table th {
            border-top: none;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
        }
        
        .table td {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Dashboard</h1>
            <div>
                <span class="text-muted me-2">Last updated:</span>
                <span class="fw-bold">{{ now()->format('F j, Y h:i A') }}</span>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <i class="fas fa-users stat-icon"></i>
                        <div class="stat-number">{{ number_format($stats['total_users']) }}</div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-meta">{{ $stats['active_users'] }} active this month</div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <i class="fas fa-question-circle stat-icon"></i>
                        <div class="stat-number">{{ number_format($stats['total_quizzes']) }}</div>
                        <div class="stat-label">Total Quizzes</div>
                        <div class="stat-meta">{{ $quizzesByCategory->sum('count') }} in categories</div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <i class="fas fa-question stat-icon"></i>
                        <div class="stat-number">{{ number_format($stats['total_questions']) }}</div>
                        <div class="stat-label">Total Questions</div>
                        <div class="stat-meta">Avg. {{ round($stats['total_questions'] / max($stats['total_quizzes'], 1)) }} per quiz</div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <i class="fas fa-chart-bar stat-icon"></i>
                        <div class="stat-number">{{ number_format($stats['total_attempts']) }}</div>
                        <div class="stat-label">Quiz Attempts</div>
                        <div class="stat-meta">{{ $stats['completion_rate'] }}% completion rate</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <!-- Quiz Attempts Chart -->
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Quiz Attempts (Last 30 Days)</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" data-period="7">Week</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-period="30">Month</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-period="90">Quarter</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="attemptsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quizzes by Category -->
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Quizzes by Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                        <div class="mt-3">
                            @foreach($quizzesByCategory as $category)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="category-color" style="width: 12px; height: 12px; background-color: {{ $loop->index % 2 == 0 ? '#4e73df' : '#1cc88a' }}; margin-right: 8px; border-radius: 2px;"></div>
                                        <span class="text-muted">{{ $category['name'] }}</span>
                                    </div>
                                    <span class="fw-bold">{{ $category['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Users -->
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Users</h5>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="recent-activity">
                            @forelse($recentUsers as $user)
                                <li class="recent-activity-item">
                                    <div class="activity-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="activity-details">
                                        <div class="activity-title">{{ $user->name }}</div>
                                        <div class="activity-time">
                                            Joined {{ $user->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-4 text-muted">No recent users found</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Recent Quiz Attempts -->
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Quiz Attempts</h5>
                        <a href="{{ route('admin.results.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="recent-activity">
                            @forelse($recentResults as $result)
                                <li class="recent-activity-item">
                                    <div class="activity-icon bg-{{ $result->is_passed ? 'success' : 'danger' }} bg-opacity-10 text-{{ $result->is_passed ? 'success' : 'danger' }}">
                                        <i class="fas fa-{{ $result->is_passed ? 'check' : 'times' }}"></i>
                                    </div>
                                    <div class="activity-details">
                                        <div class="activity-title">
                                            {{ $result->user->name }} {{ $result->is_passed ? 'passed' : 'failed' }} "{{ $result->quiz->title }}"
                                        </div>
                                        <div class="activity-time">
                                            Scored {{ $result->score }}/{{ $result->max_score }} • {{ $result->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-4 text-muted">No recent quiz attempts found</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Top Quizzes -->
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Top Quizzes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Quiz</th>
                                        <th class="text-end">Attempts</th>
                                        <th class="text-end">Avg. Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topQuizzes as $quiz)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        @if($quiz->image)
                                                            <img src="{{ asset('storage/' . $quiz->image) }}" alt="{{ $quiz->title }}" class="avatar">
                                                        @else
                                                            <div class="avatar bg-light text-dark d-flex align-items-center justify-content-center">
                                                                <i class="fas fa-question-circle"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <div class="fw-semibold">{{ Str::limit($quiz->title, 20) }}</div>
                                                        <small class="text-muted">{{ $quiz->questions_count }} questions</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">{{ $quiz->results_count }}</td>
                                            <td class="text-end">
                                                @php
                                                    $avgScore = $quiz->results_avg_score ? round($quiz->results_avg_score, 1) : 0;
                                                    $maxScore = $quiz->questions_count * 1; // Assuming 1 point per question
                                                    $percentage = $maxScore > 0 ? ($avgScore / $maxScore) * 100 : 0;
                                                @endphp
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <span class="me-2">{{ $avgScore }}/{{ $maxScore }}</span>
                                                    <span class="text-muted small">{{ round($percentage) }}%</span>
                                                </div>
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%" 
                                                         aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No quiz data available</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quizzes by Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Users -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Users</h5>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="recent-activity">
                            @forelse($recentUsers as $user)
                                <li class="recent-activity-item">
                                    <div class="activity-icon bg-light text-primary">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="activity-details">
                                        <div class="activity-title">{{ $user->name }}</div>
                                        <div class="activity-time">
                                            Joined {{ $user->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-3 text-muted">No recent users found</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Recent Quizzes -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Quizzes</h5>
                        <a href="{{ route('admin.quizzes.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="recent-activity">
                            @forelse($recentQuizzes as $quiz)
                                <li class="recent-activity-item">
                                    <div class="activity-icon bg-light text-success">
                                        <i class="fas fa-question-circle"></i>
                                    </div>
                                    <div class="activity-details">
                                        <div class="activity-title">
                                            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-dark">
                                                {{ Str::limit($quiz->title, 30) }}
                                            </a>
                                        </div>
                                        <div class="activity-time">
                                            {{ $quiz->category->name }} • {{ $quiz->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-3 text-muted">No quizzes found</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Recent Results -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Quiz Attempts</h5>
                        <a href="{{ route('admin.results.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="recent-activity">
                            @forelse($recentResults as $result)
                                <li class="recent-activity-item">
                                    <div class="activity-icon bg-light {{ $result->passed ? 'text-success' : 'text-danger' }}">
                                        <i class="fas {{ $result->passed ? 'fa-check' : 'fa-times' }}"></i>
                                    </div>
                                    <div class="activity-details">
                                        <div class="activity-title">
                                            <a href="{{ route('admin.users.show', $result->user) }}" class="text-dark">
                                                {{ $result->user->name }}
                                            </a>
                                            completed 
                                            <a href="{{ route('admin.quizzes.edit', $result->quiz) }}" class="text-dark">
                                                {{ Str::limit($result->quiz->title, 15) }}
                                            </a>
                                        </div>
                                        <div class="activity-time">
                                            Scored {{ $result->score }}% • {{ $result->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-3 text-muted">No quiz attempts found</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Attempts Chart (Line Chart)
            const attemptsCtx = document.getElementById('attemptsChart').getContext('2d');
            const attemptsChart = new Chart(attemptsCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($attemptsDates) !!},
                    datasets: [{
                        label: 'Quiz Attempts',
                        data: {!! json_encode($attemptsCount) !!},
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderColor: '#0d6efd',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0d6efd',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#2c3e50',
                            titleFont: {
                                weight: '600'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                drawBorder: false
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    }
                }
            });
            
            // Quizzes by Category Doughnut Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categoryNames) !!},
                    datasets: [{
                        data: {!! json_encode($categoryCounts) !!},
                        backgroundColor: [
                            '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545',
                            '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
                        ],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#2c3e50',
                            titleFont: {
                                weight: '600'
                            },
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Handle chart resize on tab change
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function () {
                    attemptsChart.resize();
                    categoryChart.resize();
                });
            });
        });
    </script>
@endpush
