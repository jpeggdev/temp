using Microsoft.EntityFrameworkCore;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.Goals.Entities;
using HeyDav.Domain.NewsAggregation.Entities;
using HeyDav.Domain.MoodAnalysis.Entities;
using HeyDav.Domain.FinancialGoals.Entities;
using HeyDav.Domain.Workflows.Entities;

namespace HeyDav.Application.Common.Interfaces;

public interface IApplicationDbContext
{
    DbSet<TodoItem> TodoItems { get; }
    DbSet<Category> Categories { get; }
    DbSet<Goal> Goals { get; }
    DbSet<Milestone> Milestones { get; }
    DbSet<NewsFeed> NewsFeeds { get; }
    DbSet<NewsArticle> NewsArticles { get; }
    DbSet<MoodEntry> MoodEntries { get; }
    DbSet<FinancialGoal> FinancialGoals { get; }
    DbSet<WorkflowTemplate> WorkflowTemplates { get; }
    DbSet<WorkflowStepTemplate> WorkflowStepTemplates { get; }
    DbSet<WorkflowInstance> WorkflowInstances { get; }
    DbSet<WorkflowStepInstance> WorkflowStepInstances { get; }
    DbSet<Habit> Habits { get; }
    DbSet<HabitEntry> HabitEntries { get; }

    Task<int> SaveChangesAsync(CancellationToken cancellationToken = default);
}