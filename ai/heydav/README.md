# Hey-Dav 🎯

**How Everything You Do Adds Value**

A comprehensive personal productivity desktop application that intelligently aggregates and schedules all aspects of your life - from todos and goals to diet, fitness, finances, and news - into one dynamic, AI-powered dashboard.

[![.NET](https://img.shields.io/badge/.NET-8.0-blue.svg)](https://dotnet.microsoft.com/)
[![Avalonia](https://img.shields.io/badge/Avalonia-11.3-purple.svg)](https://avaloniaui.net/)
[![SQLite](https://img.shields.io/badge/SQLite-Local-green.svg)](https://sqlite.org/)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## 🌟 Features

### ✅ **Current Features**

- **Smart Todo Management**: Dynamic scheduling with priorities, due dates, and dependencies
- **Goal Setting & Tracking**: Hierarchical goals with milestones and progress monitoring
- **Category Organization**: Color-coded categorization for all tasks and goals
- **Cross-Platform**: Runs on Windows, macOS, and Linux
- **Local-First**: All data stored locally - no cloud dependencies required
- **Modern UI**: Clean, responsive interface built with Avalonia UI

### 🚀 **Planned Features**

- **AI-Powered Scheduling**: Intelligent task optimization based on your energy levels and patterns
- **News Aggregation Bot**: Curated news feeds with AI relevance scoring
- **Mood & Energy Tracking**: Webcam-based mood detection and productivity correlation
- **Fitness Integration**: Workout planning and progress tracking
- **Diet Management**: Meal planning, nutrition tracking, and shopping lists
- **Financial Goal Tracking**: Budget management and savings targets
- **Household Management**: Automated chore scheduling and reminders
- **Productivity Analytics**: Insights and recommendations based on your patterns

## 🏗️ Architecture

Hey-Dav follows **Clean Architecture** principles with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                     Desktop (Avalonia UI)                   │
│                         MVVM Pattern                        │
├─────────────────────────────────────────────────────────────┤
│                    Application Layer                        │
│              CQRS with MediatR + Use Cases                  │
├─────────────────────────────────────────────────────────────┤
│                      Domain Layer                           │
│              Entities + Business Logic                      │
├─────────────────────────────────────────────────────────────┤
│                  Infrastructure Layer                       │
│            Entity Framework + SQLite + Repositories         │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Getting Started

### Prerequisites

- [.NET 8 SDK](https://dotnet.microsoft.com/download) or later
- Any IDE with C# support (VS Code, Visual Studio, JetBrains Rider)

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/heydav.git
   cd heydav
   ```

2. **Restore dependencies**

   ```bash
   dotnet restore
   ```

3. **Build the solution**

   ```bash
   dotnet build
   ```

4. **Run the application**

   ```bash
   dotnet run --project src/HeyDav.Desktop
   ```

The application will automatically create the SQLite database on first run.

### Development Setup

1. **Install Entity Framework CLI tools**

   ```bash
   dotnet tool install --global dotnet-ef
   ```

2. **Create a new migration** (when making model changes)

   ```bash
   dotnet ef migrations add MigrationName --project src/HeyDav.Infrastructure --startup-project src/HeyDav.Desktop
   ```

3. **Update the database**

   ```bash
   dotnet ef database update --project src/HeyDav.Infrastructure --startup-project src/HeyDav.Desktop
   ```

## 📁 Project Structure

```
HeyDav/
├── src/
│   ├── HeyDav.Domain/           # Core business logic and entities
│   │   ├── Common/              # Base classes and interfaces
│   │   ├── TodoManagement/      # Todo-related domain models
│   │   ├── Goals/              # Goal and milestone entities
│   │   ├── NewsAggregation/    # News feed management
│   │   ├── MoodAnalysis/       # Mood tracking entities
│   │   └── FinancialGoals/     # Financial goal tracking
│   │
│   ├── HeyDav.Application/      # Use cases and application logic
│   │   ├── Common/             # Shared application interfaces
│   │   ├── TodoManagement/     # Todo commands and queries
│   │   └── Goals/              # Goal-related use cases
│   │
│   ├── HeyDav.Infrastructure/   # Data access and external services
│   │   ├── Persistence/        # Entity Framework configuration
│   │   ├── Repositories/       # Data access implementations
│   │   └── Services/           # External service integrations
│   │
│   └── HeyDav.Desktop/         # Avalonia UI application
│       ├── ViewModels/         # MVVM ViewModels
│       ├── Views/              # XAML Views
│       └── Services/           # UI-specific services
│
├── tests/                      # Unit and integration tests
└── docs/                       # Documentation
```

## 🛠️ Technology Stack

- **Framework**: .NET 8
- **UI Framework**: Avalonia UI 11.3
- **Database**: SQLite with Entity Framework Core
- **Architecture Pattern**: Clean Architecture + MVVM
- **Messaging**: MediatR for CQRS
- **Dependency Injection**: Microsoft.Extensions.Hosting
- **Testing**: xUnit, FluentAssertions

## 🎯 Core Modules

### Todo Management

- ✅ Dynamic task scheduling
- ✅ Priority-based organization
- ✅ Due date tracking
- ✅ Category assignment
- ✅ Recurring tasks
- 🚧 Dependency management
- 🚧 Time tracking

### Goal Setting

- ✅ Hierarchical goal structure
- ✅ Milestone tracking
- ✅ Progress monitoring
- 🚧 AI-generated action plans
- 🚧 Success prediction

### News Aggregation

- 🚧 RSS feed integration
- 🚧 AI content curation
- 🚧 Relevance scoring
- 🚧 Reading time optimization

### Mood & Productivity

- ✅ Manual mood tracking
- 🚧 Webcam-based detection
- 🚧 Productivity correlation
- 🚧 Energy level optimization

## 🤖 AI Integration Roadmap

1. **Smart Scheduling Algorithm**
   - Task optimization based on historical completion patterns
   - Energy level consideration for task assignment
   - Deadline pressure analysis

2. **Mood Detection System**
   - Webcam-based mood analysis (privacy-first, local processing)
   - Correlation with productivity metrics
   - Mood-based task recommendations

3. **Intelligent Goal Planning**
   - Automated milestone generation
   - Action plan suggestions
   - Progress prediction and course correction

4. **News Curation Bot**
   - Content relevance scoring
   - Personalized feed optimization
   - Reading time scheduling

## 🔒 Privacy & Security

- **Local-First**: All data stored locally in SQLite
- **No Cloud Dependencies**: Works completely offline
- **Privacy-Preserving AI**: Webcam analysis processed locally
- **Data Encryption**: Sensitive data encrypted at rest
- **User Control**: Full control over data retention and deletion

## 🧪 Testing

Run all tests:

```bash
dotnet test
```

Run specific test project:

```bash
dotnet test tests/HeyDav.Domain.Tests
dotnet test tests/HeyDav.Application.Tests
dotnet test tests/HeyDav.Infrastructure.Tests
```

## 📈 Performance

- **Lightweight**: SQLite database with minimal overhead
- **Responsive UI**: Avalonia UI with efficient data binding
- **Background Processing**: Heavy operations moved to background services
- **Smart Caching**: Intelligent caching for frequently accessed data

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Avalonia UI](https://avaloniaui.net/) for the amazing cross-platform UI framework
- [MediatR](https://github.com/jbogard/MediatR) for clean CQRS implementation
- [Entity Framework Core](https://docs.microsoft.com/en-us/ef/core/) for robust data access
- [Community Toolkit](https://github.com/CommunityToolkit/dotnet) for MVVM helpers

## 📬 Contact

- **Project Homepage**: [Hey-Dav Repository](https://github.com/yourusername/heydav)
- **Issues**: [GitHub Issues](https://github.com/yourusername/heydav/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/heydav/discussions)

---

**Hey-Dav** - *Making every action count towards your goals* 🎯
