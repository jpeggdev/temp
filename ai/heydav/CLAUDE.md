# Hey-Dav Project Context for Claude

## Project Overview

Hey-Dav (How Everything You Do Adds Value) is a comprehensive personal productivity desktop application built with C# and Avalonia UI. It aggregates multiple productivity domains into a single, intelligent dashboard that dynamically schedules tasks and provides AI-driven insights.

## Architecture

### Clean Architecture Pattern

- **Domain Layer** (`HeyDav.Domain`): Core business logic, entities, value objects
- **Application Layer** (`HeyDav.Application`): Use cases, commands, queries (CQRS with MediatR)
- **Infrastructure Layer** (`HeyDav.Infrastructure`): Data access, external services, repositories
- **Presentation Layer** (`HeyDav.Desktop`): Avalonia UI with MVVM pattern

### Technology Stack

- **Framework**: .NET 8, C#
- **UI**: Avalonia UI 11.3 (cross-platform desktop)
- **Database**: SQLite with Entity Framework Core 9.0
- **Patterns**: CQRS, Repository, MVVM, Dependency Injection
- **Packages**: MediatR, CommunityToolkit.Mvvm, Microsoft.Extensions.Hosting

## Key Modules

### 1. Todo Management

- **Entities**: `TodoItem`, `Category`
- **Features**: Dynamic scheduling, priorities, due dates, dependencies, recurrence
- **Status**: Fully implemented with repository and ViewModels

### 2. Goal Setting & Planning

- **Entities**: `Goal`, `Milestone`
- **Features**: Hierarchical goals, progress tracking, AI-generated action plans
- **Status**: Domain and basic structure complete

### 3. News Aggregation

- **Entities**: `NewsFeed`, `NewsArticle`
- **Features**: RSS/API aggregation, AI curation, relevance scoring
- **Status**: Domain structure ready for implementation

### 4. Mood & Energy Tracking

- **Entities**: `MoodEntry`
- **Features**: Manual entry, webcam analysis, productivity correlation
- **Status**: Domain models complete

### 5. Financial Goals

- **Entities**: `FinancialGoal`
- **Features**: Target tracking, progress monitoring, budget integration
- **Status**: Basic structure implemented

## Database Schema

### Core Tables

- `TodoItems`: Main task management with JSON fields for tags/dependencies
- `Categories`: Task categorization with color coding
- `Goals`/`Milestones`: Hierarchical goal structure
- `NewsFeeds`/`NewsArticles`: Content aggregation
- `MoodEntries`: Mood and energy tracking
- `FinancialGoals`: Financial target management

### Key Features

- Soft delete pattern on all entities
- Audit trails (CreatedAt, UpdatedAt)
- JSON serialization for complex properties
- Proper indexing for performance

## Current Implementation Status

### ✅ Completed

- Solution structure and project setup
- Domain models and entities
- Entity Framework configuration and migrations
- Repository pattern implementation
- MVVM ViewModels (DashboardViewModel, TodoItemViewModel)
- Dependency injection setup
- Database creation and basic queries

### 🚧 In Progress

- Dashboard UI implementation
- Basic XAML views

### 📋 Pending

- News bot implementation
- AI/ML integration (mood detection, smart scheduling)
- Background services
- Webcam integration
- Advanced dashboard features

## Development Commands

### Database Operations

```bash
# Create migration
dotnet ef migrations add MigrationName --project src/HeyDav.Infrastructure --startup-project src/HeyDav.Desktop

# Update database
dotnet ef database update --project src/HeyDav.Infrastructure --startup-project src/HeyDav.Desktop

# Remove last migration
dotnet ef migrations remove --project src/HeyDav.Infrastructure --startup-project src/HeyDav.Desktop
```

### Build & Run

```bash
# Build solution
dotnet build

# Run desktop app
dotnet run --project src/HeyDav.Desktop

# Run tests
dotnet test
```

## Key Design Decisions

1. **Local-First**: All data stored locally in SQLite, no cloud dependencies
2. **Cross-Platform**: Avalonia UI ensures Windows/macOS/Linux compatibility
3. **Extensible**: Plugin-like module architecture for easy feature additions
4. **Privacy-Focused**: Webcam data processed locally, no external API calls for sensitive data
5. **Event-Driven**: Domain events for loosely coupled module communication

## Future AI Integrations

### Planned Features

- **Smart Scheduling**: ML-based task optimization based on energy levels, deadlines, and completion patterns
- **Mood Analysis**: Webcam-based mood detection to correlate with productivity
- **Goal Planning**: AI-generated action plans with milestone suggestions
- **News Curation**: Relevance scoring and personalized content filtering
- **Productivity Insights**: Pattern recognition and recommendation system

### ML Models Needed

- Computer vision for presence/mood detection
- Natural language processing for task parsing
- Time series analysis for productivity patterns
- Recommendation systems for task prioritization

## Important Notes for Claude

- **Database Connection**: Uses SQLite with connection string in `appsettings.json`
- **Migrations**: Located in `src/HeyDav.Infrastructure/Migrations/`
- **ViewModels**: Use CommunityToolkit.Mvvm source generators (`[ObservableProperty]`, `[RelayCommand]`)
- **Dependency Injection**: Configured in `App.axaml.cs` using Microsoft.Extensions.Hosting
- **Entity Configuration**: Located in `src/HeyDav.Infrastructure/Persistence/Configurations/`

## Testing Strategy

- **Unit Tests**: Domain logic and business rules
- **Integration Tests**: Repository and database operations
- **UI Tests**: ViewModel behavior and command execution
- **End-to-End Tests**: Complete user workflows

## Performance Considerations

- **Lazy Loading**: Disabled in favor of explicit includes
- **Query Optimization**: Proper indexing on frequently queried columns
- **Batch Operations**: Bulk inserts/updates for large datasets
- **Caching**: In-memory caching for frequently accessed data
- **Background Processing**: Heavy operations moved to background services
