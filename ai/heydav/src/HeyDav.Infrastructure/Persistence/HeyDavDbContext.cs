using Microsoft.EntityFrameworkCore;
using System.Linq.Expressions;
using HeyDav.Domain.Common.Base;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.Goals.Entities;
using HeyDav.Domain.NewsAggregation.Entities;
using HeyDav.Domain.MoodAnalysis.Entities;
using HeyDav.Domain.FinancialGoals.Entities;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.Notifications.Entities;
using HeyDav.Domain.Automation.Entities;
using HeyDav.Domain.Analytics.Entities;
using HeyDav.Application.Common.Interfaces;

namespace HeyDav.Infrastructure.Persistence;

public class HeyDavDbContext(DbContextOptions<HeyDavDbContext> options) : DbContext(options), IApplicationDbContext
{
    // Todo Management
    public DbSet<TodoItem> TodoItems { get; set; }
    public DbSet<Category> Categories { get; set; }

    // Goals
    public DbSet<Goal> Goals { get; set; }
    public DbSet<Milestone> Milestones { get; set; }
    
    // News Aggregation
    public DbSet<NewsFeed> NewsFeeds { get; set; }
    public DbSet<NewsArticle> NewsArticles { get; set; }
    
    // Mood Analysis
    public DbSet<MoodEntry> MoodEntries { get; set; }
    
    // Financial Goals
    public DbSet<FinancialGoal> FinancialGoals { get; set; }
    
    // Agent Management
    public DbSet<AIAgent> AIAgents { get; set; }
    public DbSet<AgentTask> AgentTasks { get; set; }
    public DbSet<McpServer> McpServers { get; set; }
    
    // Workflows
    public DbSet<WorkflowTemplate> WorkflowTemplates { get; set; }
    public DbSet<WorkflowStepTemplate> WorkflowStepTemplates { get; set; }
    public DbSet<WorkflowInstance> WorkflowInstances { get; set; }
    public DbSet<WorkflowStepInstance> WorkflowStepInstances { get; set; }
    
    // Habits
    public DbSet<Habit> Habits { get; set; }
    public DbSet<HabitEntry> HabitEntries { get; set; }
    
    // Tool Integrations
    public DbSet<ToolConnection> ToolConnections { get; set; }
    public DbSet<ToolCapability> ToolCapabilities { get; set; }
    public DbSet<WebhookEndpoint> WebhookEndpoints { get; set; }
    public DbSet<WebhookEvent> WebhookEvents { get; set; }
    public DbSet<ToolSyncConfiguration> ToolSyncConfigurations { get; set; }
    public DbSet<SyncExecutionLog> SyncExecutionLogs { get; set; }
    
    // Notifications
    public DbSet<Notification> Notifications { get; set; }
    public DbSet<NotificationDeliveryAttempt> NotificationDeliveryAttempts { get; set; }
    public DbSet<NotificationInteraction> NotificationInteractions { get; set; }
    public DbSet<NotificationPreference> NotificationPreferences { get; set; }
    public DbSet<NotificationTemplate> NotificationTemplates { get; set; }
    
    // Automation
    public DbSet<AutomationRule> AutomationRules { get; set; }
    public DbSet<AutomationExecution> AutomationExecutions { get; set; }
    public DbSet<AutomationActionResult> AutomationActionResults { get; set; }
    
    // Analytics
    public DbSet<ProductivitySession> ProductivitySessions { get; set; }
    public DbSet<TimeEntry> TimeEntries { get; set; }
    public DbSet<ProductivityReport> ProductivityReports { get; set; }

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        // Apply all configurations from the current assembly
        modelBuilder.ApplyConfigurationsFromAssembly(typeof(HeyDavDbContext).Assembly);

        // Global query filter for soft delete
        foreach (var entityType in modelBuilder.Model.GetEntityTypes())
        {
            if (typeof(BaseEntity).IsAssignableFrom(entityType.ClrType))
            {
                modelBuilder.Entity(entityType.ClrType)
                    .HasQueryFilter(CreateIsNotDeletedFilter(entityType.ClrType));
            }
        }
    }

    private static LambdaExpression CreateIsNotDeletedFilter(Type entityType)
    {
        var parameter = Expression.Parameter(entityType, "e");
        var property = Expression.Property(parameter, nameof(BaseEntity.IsDeleted));
        var condition = Expression.Equal(property, Expression.Constant(false));
        return Expression.Lambda(condition, parameter);
    }

    public override async Task<int> SaveChangesAsync(CancellationToken cancellationToken = default)
    {
        // Update timestamps before saving
        var entries = ChangeTracker.Entries<BaseEntity>();
        foreach (var entry in entries)
        {
            switch (entry.State)
            {
                case EntityState.Added:
                    entry.Entity.UpdateTimestamp();
                    break;
                case EntityState.Modified:
                    entry.Entity.UpdateTimestamp();
                    break;
            }
        }

        return await base.SaveChangesAsync(cancellationToken);
    }
}