using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Application.AgentManagement.Models;

namespace HeyDav.Application.AgentManagement.Services;

public interface IAgentLoadBalancer
{
    // Load Balancing
    Task<AgentAssignment?> AssignTaskWithLoadBalancingAsync(TaskRequirements task, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentAssignment>> AssignMultipleTasksAsync(IEnumerable<TaskRequirements> tasks, CancellationToken cancellationToken = default);
    Task<bool> RebalanceTasksAsync(CancellationToken cancellationToken = default);
    Task<LoadBalancingReport> GetLoadBalancingReportAsync(CancellationToken cancellationToken = default);

    // Fallback Strategies
    Task<AgentAssignment?> AssignTaskWithFallbackAsync(TaskRequirements task, FallbackStrategy strategy, CancellationToken cancellationToken = default);
    Task<IEnumerable<FallbackOption>> GetFallbackOptionsAsync(TaskRequirements task, CancellationToken cancellationToken = default);
    Task<bool> ActivateFallbackAsync(Guid taskId, FallbackReason reason, CancellationToken cancellationToken = default);
    Task<FallbackAnalysis> AnalyzeFallbackNeedsAsync(TimeSpan? period = null, CancellationToken cancellationToken = default);

    // Dynamic Scaling
    Task<ScalingDecision> EvaluateScalingNeedsAsync(CancellationToken cancellationToken = default);
    Task<bool> ScaleUpAsync(ScalingRequest request, CancellationToken cancellationToken = default);
    Task<bool> ScaleDownAsync(ScalingRequest request, CancellationToken cancellationToken = default);
    Task<IEnumerable<AutoScalingRule>> GetAutoScalingRulesAsync(CancellationToken cancellationToken = default);

    // Circuit Breaker Pattern
    Task<bool> IsAgentAvailableAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<CircuitBreakerStatus> GetCircuitBreakerStatusAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> TripCircuitBreakerAsync(Guid agentId, string reason, CancellationToken cancellationToken = default);
    Task<bool> ResetCircuitBreakerAsync(Guid agentId, CancellationToken cancellationToken = default);

    // Health Monitoring
    Task<SystemHealthStatus> GetSystemHealthAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentHealthStatus>> GetAgentHealthStatusesAsync(CancellationToken cancellationToken = default);
    Task<bool> PerformHealthCheckAsync(Guid? agentId = null, CancellationToken cancellationToken = default);
    Task<HealthTrends> GetHealthTrendsAsync(TimeSpan? period = null, CancellationToken cancellationToken = default);

    // Resource Management
    Task<ResourceUtilization> GetResourceUtilizationAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<ResourceConstraint>> GetResourceConstraintsAsync(CancellationToken cancellationToken = default);
    Task<bool> ApplyResourceThrottlingAsync(Guid agentId, ThrottleSettings settings, CancellationToken cancellationToken = default);
    Task<bool> RemoveResourceThrottlingAsync(Guid agentId, CancellationToken cancellationToken = default);
}

public record AgentAssignment(
    Guid TaskId,
    AIAgent AssignedAgent,
    AssignmentStrategy Strategy,
    double ConfidenceScore,
    IEnumerable<AIAgent> BackupAgents,
    string Rationale,
    DateTime AssignedAt);

public record FallbackOption(
    AIAgent Agent,
    FallbackType Type,
    double Suitability,
    string Description,
    IEnumerable<string> Requirements,
    TimeSpan? ActivationTime);

public record FallbackAnalysis(
    int TotalFallbacksNeeded,
    IEnumerable<FallbackPattern> CommonPatterns,
    IEnumerable<string> ProblematicCapabilities,
    IEnumerable<FallbackImprovement> Recommendations,
    double SystemResilience);

public record FallbackPattern(
    string PatternName,
    int Frequency,
    IEnumerable<string> TriggerConditions,
    IEnumerable<Guid> AffectedAgents,
    double AverageResolutionTime);

public record FallbackImprovement(
    string Improvement,
    IEnumerable<string> Benefits,
    double Impact,
    TimeSpan ImplementationTime);

public record LoadBalancingReport(
    DateTime GeneratedAt,
    double SystemUtilization,
    IEnumerable<AgentLoadMetric> AgentLoads,
    IEnumerable<LoadImbalance> Imbalances,
    IEnumerable<LoadBalancingRecommendation> Recommendations);

public record AgentLoadMetric(
    Guid AgentId,
    string AgentName,
    double UtilizationPercentage,
    int CurrentTasks,
    int MaxTasks,
    double AverageResponseTime,
    LoadStatus Status);

public record LoadImbalance(
    string Type,
    IEnumerable<Guid> OverloadedAgents,
    IEnumerable<Guid> UnderutilizedAgents,
    double Severity,
    string RecommendedAction);

public record ScalingDecision(
    ScalingAction RecommendedAction,
    int RecommendedCount,
    IEnumerable<AgentType> TargetTypes,
    string Justification,
    double Confidence,
    TimeSpan ExpectedImpact);

public record ScalingRequest(
    ScalingAction Action,
    int Count,
    AgentType? TargetType = null,
    IEnumerable<string>? RequiredCapabilities = null,
    string? Justification = null);

public record AutoScalingRule(
    Guid Id,
    string Name,
    ScalingTrigger Trigger,
    ScalingAction Action,
    Dictionary<string, object> Parameters,
    bool IsEnabled,
    DateTime LastTriggered);

public record CircuitBreakerStatus(
    Guid AgentId,
    CircuitState State,
    DateTime LastStateChange,
    int FailureCount,
    int SuccessCount,
    TimeSpan? NextRetryAt);

public record SystemHealthStatus(
    HealthLevel OverallHealth,
    int TotalAgents,
    int HealthyAgents,
    int UnhealthyAgents,
    double SystemUtilization,
    IEnumerable<HealthAlert> Alerts);

public record AgentHealthStatus(
    Guid AgentId,
    string AgentName,
    HealthLevel Health,
    double ResponseTime,
    double SuccessRate,
    DateTime LastHealthCheck,
    IEnumerable<string> HealthIssues);

public record HealthTrends(
    TimeSpan Period,
    double AverageHealth,
    IEnumerable<HealthTrendPoint> Trends,
    IEnumerable<HealthAnomaly> Anomalies);

public record HealthTrendPoint(
    DateTime Timestamp,
    double HealthScore,
    int ActiveAgents,
    double SystemLoad);

public record HealthAnomaly(
    DateTime DetectedAt,
    string Type,
    string Description,
    IEnumerable<Guid> AffectedAgents,
    AnomalySeverity Severity);

public record HealthAlert(
    Guid Id,
    AlertLevel Level,
    string Message,
    IEnumerable<Guid> AffectedAgents,
    DateTime CreatedAt,
    bool IsAcknowledged);

public record ResourceUtilization(
    double CpuUtilization,
    double MemoryUtilization,
    double NetworkUtilization,
    double StorageUtilization,
    IEnumerable<AgentResourceUsage> AgentUsages);

public record AgentResourceUsage(
    Guid AgentId,
    string AgentName,
    double CpuUsage,
    double MemoryUsage,
    double NetworkUsage,
    int ActiveConnections);

public record ResourceConstraint(
    string ResourceType,
    double CurrentUsage,
    double MaxCapacity,
    double ThresholdWarning,
    double ThresholdCritical,
    ConstraintStatus Status);

public record ThrottleSettings(
    double CpuLimit,
    double MemoryLimit,
    int MaxConcurrentTasks,
    TimeSpan RequestInterval);

public enum AssignmentStrategy
{
    BestMatch,
    LoadBalanced,
    RoundRobin,
    CapabilityBased,
    PerformanceBased,
    Fallback
}

public enum FallbackType
{
    SimilarCapability,
    LowerSkillLevel,
    GeneralPurpose,
    HumanIntervention,
    TaskQueuing,
    TaskSplitting
}

public enum FallbackReason
{
    AgentUnavailable,
    AgentOverloaded,
    AgentFailed,
    CapabilityMismatch,
    PerformanceIssue,
    TimeoutExpired
}

public enum LoadStatus
{
    Idle,
    Light,
    Moderate,
    Heavy,
    Overloaded
}

public enum ScalingAction
{
    ScaleUp,
    ScaleDown,
    Maintain,
    Optimize
}

public enum ScalingTrigger
{
    HighUtilization,
    LowUtilization,
    QueueLength,
    ResponseTime,
    ErrorRate,
    Manual
}

public enum CircuitState
{
    Closed,
    Open,
    HalfOpen
}

public enum HealthLevel
{
    Excellent,
    Good,
    Fair,
    Poor,
    Critical
}

public enum AnomalySeverity
{
    Low,
    Medium,
    High,
    Critical
}

public enum AlertLevel
{
    Info,
    Warning,
    Error,
    Critical
}

public enum ConstraintStatus
{
    Normal,
    Warning,
    Critical,
    Violated
}