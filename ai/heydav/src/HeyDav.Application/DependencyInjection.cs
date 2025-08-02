using Microsoft.Extensions.DependencyInjection;
using System.Reflection;
using HeyDav.Application.Voice;
using HeyDav.Application.Email;
using HeyDav.Application.CommandProcessing;
using HeyDav.Application.CommandProcessing.Processors;
using HeyDav.Application.Common.Extensions;
using HeyDav.Application.TaskProcessing;
using HeyDav.Application.AgentManagement.Services;
using HeyDav.Application.AgentManagement.Communication;
using HeyDav.Application.AgentManagement.Workspace;
using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Application.Workflows.Engines;
using HeyDav.Application.Workflows.Services;
using HeyDav.Application.ToolIntegrations.Interfaces;
using HeyDav.Application.ToolIntegrations.Services;
using HeyDav.Application.Notifications.Services;
using HeyDav.Application.Notifications.Channels;

namespace HeyDav.Application;

public static class DependencyInjection
{
    public static IServiceCollection AddApplication(this IServiceCollection services)
    {
        services.AddMediator(Assembly.GetExecutingAssembly());

        // Register voice command handler
        services.AddScoped<IVoiceCommandHandler, VoiceCommandProcessor>();

        // Register email command processor
        services.AddScoped<IEmailCommandProcessor, EmailCommandProcessor>();

        // Register command processing system
        services.AddScoped<ICommandOrchestrator, CommandOrchestrator>();
        services.AddScoped<ICommandProcessorFactory, CommandProcessorFactory>();

        // Register enhanced task processing system
        services.AddScoped<IEnhancedCommandOrchestrator, EnhancedCommandOrchestrator>();
        services.AddScoped<ITaskAnalyzer, TaskAnalyzer>();
        services.AddScoped<ITaskExecutionEngine, TaskExecutionEngine>();

        // Register all command processors
        services.AddScoped<TodoCommandProcessor>();
        services.AddScoped<GoalCommandProcessor>();
        services.AddScoped<ScheduleCommandProcessor>();
        services.AddScoped<SystemCommandProcessor>();
        services.AddScoped<HelpCommandProcessor>();
        services.AddScoped<GeneralCommandProcessor>();
        services.AddScoped<ExternalAiCommandProcessor>();

        // Register agent management services
        services.AddScoped<IAgentManager, AgentManager>();
        services.AddScoped<IAgentCapabilityMatcher, AgentCapabilityMatcher>();
        services.AddScoped<IAgentWorkflowEngine, AgentWorkflowEngine>();
        services.AddScoped<IAgentTrainingSystem, AgentTrainingSystem>();
        services.AddScoped<IAgentCommunicationHub, AgentCommunicationHub>();
        services.AddScoped<ISharedWorkspaceManager, SharedWorkspaceManager>();
        services.AddScoped<IAgentDiscoveryService, AgentDiscoveryService>();
        services.AddScoped<IAgentLoadBalancer, AgentLoadBalancer>();

        // Register workflow system services
        services.AddScoped<IWorkflowTemplateEngine, WorkflowTemplateEngine>();
        services.AddScoped<ISmartSchedulingEngine, SmartSchedulingEngine>();
        services.AddScoped<IGoalProgressEngine, GoalProgressEngine>();
        services.AddScoped<IHabitTrackerService, HabitTrackerService>();

        // Register tool integration services
        services.AddScoped<IToolIntegrationManager, ToolIntegrationManager>();
        services.AddScoped<IWebhookManager, WebhookManager>();
        
        // Register notification services
        services.AddScoped<INotificationEngine, NotificationEngine>();
        services.AddScoped<ISmartNotificationScheduler, SmartNotificationScheduler>();
        services.AddScoped<INotificationPreferenceService, NotificationPreferenceService>();
        services.AddScoped<IAlertManager, AlertManager>();
        services.AddScoped<INotificationChannelManager, NotificationChannelManager>();
        
        // Register notification channels
        services.AddScoped<InAppNotificationChannel>();
        services.AddScoped<EmailNotificationChannel>();

        return services;
    }
}