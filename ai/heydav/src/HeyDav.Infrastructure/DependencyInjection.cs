using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Configuration;
using Microsoft.EntityFrameworkCore;
using HeyDav.Infrastructure.Persistence;
using HeyDav.Infrastructure.Persistence.Repositories;
using HeyDav.Infrastructure.Services;
using HeyDav.Infrastructure.Email;
using HeyDav.Infrastructure.BackgroundServices;
using HeyDav.Infrastructure.Plugins;
using HeyDav.Infrastructure.Plugins.SamplePlugins;
using HeyDav.Infrastructure.Productivity;
using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.Email;
using HeyDav.Application.TaskProcessing;
using HeyDav.Application.Services;
using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Domain.Common.Interfaces;
using HeyDav.Domain.TodoManagement.Interfaces;
using HeyDav.Domain.AgentManagement.Interfaces;
using HeyDav.Domain.ToolIntegrations.Interfaces;
using HeyDav.Application.ToolIntegrations.Interfaces;
using HeyDav.Infrastructure.ExternalTools.GitHub;
using HeyDav.Infrastructure.ExternalTools.Common;
using HeyDav.Infrastructure.Automation;
using HeyDav.Infrastructure.Automation.Executors;
using HeyDav.Infrastructure.Automation.Triggers;
using HeyDav.Infrastructure.Analytics.Repositories;
using HeyDav.Domain.Analytics.Interfaces;
using HeyDav.Application.Analytics.Services;

namespace HeyDav.Infrastructure;

public static class DependencyInjection
{
    public static IServiceCollection AddInfrastructure(this IServiceCollection services, IConfiguration configuration)
    {
        // Add DbContext
        services.AddDbContext<HeyDavDbContext>(options =>
        {
            var connectionString = configuration.GetConnectionString("DefaultConnection") 
                ?? "Data Source=heydav.db";
            options.UseSqlite(connectionString);
        });

        // Register DbContext interface
        services.AddScoped<IApplicationDbContext>(provider => provider.GetRequiredService<HeyDavDbContext>());

        // Register repositories
        services.AddScoped(typeof(IRepository<>), typeof(Repository<>));
        services.AddScoped<ITodoRepository, TodoRepository>();
        services.AddScoped<IAgentRepository, AgentRepository>();
        services.AddScoped<IAgentTaskRepository, AgentTaskRepository>();
        services.AddScoped<IMapServerRepository, McpServerRepository>();
        
        // Register workflow and productivity repositories
        services.AddScoped<IWorkflowTemplateRepository, WorkflowTemplateRepository>();
        services.AddScoped<IWorkflowInstanceRepository, WorkflowInstanceRepository>();
        services.AddScoped<IHabitRepository, HabitRepository>();
        services.AddScoped<IGoalRepository, GoalRepository>();
        
        // Register tool integration repositories
        services.AddScoped<IToolConnectionRepository, ToolConnectionRepository>();
        services.AddScoped<IToolCapabilityRepository, ToolCapabilityRepository>();
        services.AddScoped<IWebhookEndpointRepository, WebhookEndpointRepository>();
        services.AddScoped<IWebhookEventRepository, WebhookEventRepository>();
        services.AddScoped<IToolSyncConfigurationRepository, ToolSyncConfigurationRepository>();
        services.AddScoped<ISyncExecutionLogRepository, SyncExecutionLogRepository>();
        
        // Register analytics repositories
        services.AddScoped<IProductivitySessionRepository, ProductivitySessionRepository>();
        services.AddScoped<ITimeEntryRepository, TimeEntryRepository>();
        services.AddScoped<IProductivityReportRepository, ProductivityReportRepository>();
        services.AddScoped<IAnalyticsDataRepository, AnalyticsDataRepository>();

        // Register services
        services.AddScoped<HttpClient>();
        services.AddScoped<IMcpClient, McpClient>();
        services.AddScoped<IAgentOrchestrator, AgentOrchestrator>();

        // Register voice services
        services.AddSingleton<ISpeechRecognitionService>(provider =>
        {
            var config = configuration.GetSection("Voice");
            var subscriptionKey = config["AzureSpeechKey"] ?? "";
            var region = config["AzureSpeechRegion"] ?? "";
            return new SpeechRecognitionService(subscriptionKey, region);
        });
        
        services.AddSingleton<IVoiceActivationService, VoiceActivationService>();
        services.AddSingleton<IVoiceCommandService, VoiceCommandService>();

        // Register email services
        services.AddScoped<IEmailService, EmailService>();
        services.AddScoped<IEmailCommandProcessor, EmailCommandProcessor>();
        services.AddHostedService<EmailMonitoringService>();

        // Register external AI services
        services.AddScoped<IExternalAiService, ExternalAiService>();

        // Register plugin management system
        services.AddSingleton<IPluginManager, PluginManager>();

        // Register sample plugins (in production, these would be loaded dynamically)
        services.AddTransient<EchoPlugin>();
        services.AddTransient<CalculatorPlugin>();

        // Register productivity and workflow services
        services.AddScoped<IWorkflowAnalytics, WorkflowAnalytics>();
        services.AddScoped<IProductivityPatternAnalyzer, ProductivityPatternAnalyzer>();
        services.AddScoped<IEnergyLevelPredictor, EnergyLevelPredictor>();
        services.AddScoped<ICalendarIntegration, CalendarIntegration>();
        services.AddScoped<ISchedulingOptimizer, SchedulingOptimizer>();
        
        // Register habit and goal analysis services
        services.AddScoped<IHabitAnalytics, HabitAnalytics>();
        services.AddScoped<IMotivationEngine, MotivationEngine>();
        services.AddScoped<IGamificationService, GamificationService>();
        services.AddScoped<IGoalAnalytics, GoalAnalytics>();
        services.AddScoped<IActionPlanGenerator, ActionPlanGenerator>();
        services.AddScoped<IProgressPredictor, ProgressPredictor>();

        // Register tool integration services (with placeholder implementations)
        services.AddScoped<IToolAuthenticationService, ToolAuthenticationService>();
        services.AddScoped<IToolHealthMonitor, ToolHealthMonitor>();
        services.AddScoped<IRateLimitManager, RateLimitManager>();
        services.AddScoped<IToolCapabilityDiscovery, ToolCapabilityDiscoveryService>();
        services.AddScoped<IWebhookSecurityValidator, WebhookSecurityValidator>();
        services.AddScoped<IWebhookEventRouter, WebhookEventRouter>();
        
        // Register specific tool integrations
        services.AddScoped<GitHubIntegration>();
        services.AddScoped<IToolIntegration>(provider => provider.GetRequiredService<GitHubIntegration>());

        // Register automation services
        services.AddScoped<IAutomationEngine, AutomationEngine>();
        services.AddScoped<IAutomationActionExecutor, AutomationActionExecutor>();
        services.AddScoped<IAutomationTriggerManager, AutomationTriggerManager>();
        
        // Register analytics services
        services.AddScoped<IProductivityAnalyticsEngine, ProductivityAnalyticsEngine>();
        services.AddScoped<ITimeTrackingSystem, TimeTrackingSystem>();
        services.AddScoped<IPerformanceMetricsEngine, PerformanceMetricsEngine>();
        services.AddScoped<IInsightGenerationSystem, InsightGenerationSystem>();
        
        // Register placeholder analytics engines (would be implemented with actual ML/AI)
        services.AddScoped<IPatternDetectionEngine, PlaceholderPatternDetectionEngine>();
        services.AddScoped<IAnomalyDetectionEngine, PlaceholderAnomalyDetectionEngine>();
        services.AddScoped<IRecommendationEngine, PlaceholderRecommendationEngine>();
        services.AddScoped<IPredictiveInsightEngine, PlaceholderPredictiveInsightEngine>();
        services.AddScoped<IContextualInsightEngine, PlaceholderContextualInsightEngine>();
        services.AddScoped<IInsightPersonalizationEngine, PlaceholderInsightPersonalizationEngine>();
        services.AddScoped<IBenchmarkingSystem, PlaceholderBenchmarkingSystem>();
        services.AddScoped<IMetricCalculationEngine, PlaceholderMetricCalculationEngine>();
        services.AddScoped<IAutomaticTimeTracker, PlaceholderAutomaticTimeTracker>();
        services.AddScoped<IFocusTracker, PlaceholderFocusTracker>();
        services.AddScoped<ITimeEstimationEngine, PlaceholderTimeEstimationEngine>();
        
        // Register background services
        services.AddHostedService<AgentTaskProcessingService>();
        services.AddHostedService<SystemHealthMonitoringService>();

        return services;
    }
}