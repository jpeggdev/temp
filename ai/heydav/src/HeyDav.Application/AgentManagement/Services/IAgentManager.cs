using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.ValueObjects;

namespace HeyDav.Application.AgentManagement.Services;

public interface IAgentManager
{
    // Agent Lifecycle Management
    Task<Guid> CreateAgentAsync(string name, AgentType type, string? description = null, CancellationToken cancellationToken = default);
    Task<bool> ActivateAgentAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> DeactivateAgentAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> SetAgentMaintenanceAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> RetireAgentAsync(Guid agentId, string reason, CancellationToken cancellationToken = default);

    // Agent Configuration
    Task<bool> UpdateAgentConfigurationAsync(Guid agentId, AgentConfiguration configuration, CancellationToken cancellationToken = default);
    Task<bool> AddAgentCapabilityAsync(Guid agentId, string capability, CancellationToken cancellationToken = default);
    Task<bool> RemoveAgentCapabilityAsync(Guid agentId, string capability, CancellationToken cancellationToken = default);
    Task<bool> AddAgentSpecializationAsync(Guid agentId, AgentSpecialization specialization, CancellationToken cancellationToken = default);
    Task<bool> RemoveAgentSpecializationAsync(Guid agentId, string domain, string subdomain, CancellationToken cancellationToken = default);

    // Agent Discovery and Matching
    Task<IEnumerable<AIAgent>> GetAgentsByTypeAsync(AgentType type, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAgentsByCapabilityAsync(string capability, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAgentsBySpecializationAsync(string domain, string? subdomain = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAvailableAgentsAsync(CancellationToken cancellationToken = default);
    Task<AIAgent?> FindBestAgentForTaskAsync(string taskDescription, string? domain = null, string? subdomain = null, IEnumerable<string>? keywords = null, CancellationToken cancellationToken = default);

    // Agent Performance and Health
    Task<bool> RecordAgentPerformanceAsync(Guid agentId, TimeSpan responseTime, bool successful, string? feedback = null, CancellationToken cancellationToken = default);
    Task<bool> PerformHealthCheckAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task PerformAllHealthChecksAsync(CancellationToken cancellationToken = default);
    Task<AgentPerformanceMetrics> GetAgentPerformanceMetricsAsync(Guid agentId, CancellationToken cancellationToken = default);

    // Agent Training and Learning
    Task<bool> UpdateAgentFromFeedbackAsync(Guid agentId, string taskDomain, string feedback, bool successful, CancellationToken cancellationToken = default);
    Task<bool> EnhanceAgentSpecializationAsync(Guid agentId, string domain, string subdomain, int skillImprovement, double confidenceAdjustment, CancellationToken cancellationToken = default);

    // Agent Collaboration
    Task<IEnumerable<AIAgent>> GetCompatibleAgentsAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> CanAgentsCollaborateAsync(Guid agentId1, Guid agentId2, CancellationToken cancellationToken = default);
    Task<AgentTeam> FormAgentTeamAsync(string teamName, IEnumerable<Guid> agentIds, string objective, CancellationToken cancellationToken = default);
}

public record AgentPerformanceMetrics(
    Guid AgentId,
    string AgentName,
    int TotalTasks,
    int SuccessfulTasks,
    int FailedTasks,
    double SuccessRate,
    double AverageResponseTime,
    DateTime LastActiveAt,
    IEnumerable<AgentSpecialization> TopSpecializations);

public record AgentTeam(
    Guid Id,
    string Name,
    string Objective,
    IEnumerable<Guid> AgentIds,
    DateTime CreatedAt);