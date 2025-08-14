# Quiz Platform – Development Plan

## Project Overview
A web-based quiz application with two interfaces:
- **User Side (Web)**: Users can sign up, log in, take quizzes, see results, and view leaderboards.
- **Admin Panel (Web)**: Admins manage categories, quizzes, questions, and view analytics.

---

## 1. Core Features

### 👨‍🎓 User Side (Web)
- Sign up / log in (email + optional social login)
- Browse quizzes by category
- Timer-based quizzes (auto-submit when time runs out)
- Show score and detailed answer summary after each quiz
- View history of past results
- Global leaderboard for all users

### 👨‍💻 Admin Panel (Web)
- Admin authentication
- Manage categories (add, edit, delete)
- Manage quizzes (title, time limit, category)
- Manage questions (MCQs: 1 correct, 4 options)
- View user results
- Dashboard with charts (users, attempts, scores)

---

## 2. Database Structure (MySQL)

### Users
- id, name, email, password, role, created_at

### Categories
- id, name, status, created_at

### Quizzes
- id, title, category_id, time_limit (minutes), created_at

### Questions
- id, quiz_id, question_text, created_at

### Options
- id, question_id, option_text, is_correct (boolean)

### Results
- id, user_id, quiz_id, score, time_taken, taken_at

---

## 3. Admin Dashboard Metrics
- Total users
- Total quizzes
- Total attempts
- Graphs:  
  - Users over time  
  - Attempts over time  
  - Average scores per quiz

---

## 4. Additional Features (Optional)
- Role-based authentication middleware (Admin/User separation)
- Dark mode UI toggle
- Push notifications for new quizzes
- Firebase Analytics (for mobile integration)
- Bulk CSV upload for questions

---

## 5. Development Roadmap

### Phase 1 – Setup
- Confirm Laravel project is running
- Configure authentication system
- Create database migrations
- Set up roles and permissions

### Phase 2 – Core User Features
- Category listing
- Quiz listing by category
- Timer-based quiz interface
- Results page with score and answers
- Leaderboard

### Phase 3 – Admin Panel
- Category CRUD
- Quiz CRUD
- Question and option management
- Results viewing
- Dashboard charts

### Phase 4 – Enhancements
- CSV bulk upload
- Dark mode
- Notifications
- Mobile API endpoints

---

## 6. Deliverables
- Fully functional Laravel web application
- Responsive UI for both user and admin panels
- MySQL database with all tables and relationships
- Admin dashboard with analytics
- Documentation for installation and usage

---

## 7. Success Criteria
- Users can register, log in, and complete quizzes without errors
- Timer functions correctly for quizzes
- Results are stored and displayed accurately
- Admin can manage all quiz-related data from the panel
- Dashboard analytics display correct data
