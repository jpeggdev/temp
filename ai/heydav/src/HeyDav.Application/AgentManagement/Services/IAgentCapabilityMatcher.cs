using HeyDav.Domain.AgentManagement.Entities;

namespace HeyDav.Application.AgentManagement.Services;

public interface IAgentCapabilityMatcher
{
    Task<IEnumerable<AgentMatch>> FindMatchingAgentsAsync(TaskRequirements requirements, CancellationToken cancellationToken = default);
    Task<AgentMatch?> FindBestMatchAsync(TaskRequirements requirements, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentMatch>> FindBackupAgentsAsync(TaskRequirements requirements, int maxResults = 3, CancellationToken cancellationToken = default);
    Task<bool> CanAgentHandleTaskAsync(Guid agentId, TaskRequirements requirements, CancellationToken cancellationToken = default);
    Task<AgentCapabilityAnalysis> AnalyzeAgentCapabilitiesAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<TeamFormationSuggestion> SuggestTeamForComplexTaskAsync(ComplexTaskRequirements requirements, CancellationToken cancellationToken = default);
}

public record TaskRequirements(
    string Description,
    string? Domain = null,
    string? Subdomain = null,
    IEnumerable<string>? Keywords = null,
    IEnumerable<string>? RequiredCapabilities = null,
    TaskPriority Priority = TaskPriority.Medium,
    TimeSpan? EstimatedDuration = null,
    int? MinSkillLevel = null,
    double? MinConfidence = null,
    bool RequireHighAvailability = false);

public record ComplexTaskRequirements(
    string Description,
    IEnumerable<TaskComponent> Components,
    bool RequireSequentialExecution = false,
    bool AllowParallelExecution = true,
    TimeSpan? MaxExecutionTime = null);

public record TaskComponent(
    string Name,
    TaskRequirements Requirements,
    IEnumerable<string>? Dependencies = null);

public record AgentMatch(
    AIAgent Agent,
    double Score,
    MatchReason PrimaryReason,
    IEnumerable<string> MatchedCapabilities,
    IEnumerable<string> MatchedSpecializations,
    double AvailabilityScore,
    double PerformanceScore,
    double RelevanceScore,
    IEnumerable<string> Concerns);

public record AgentCapabilityAnalysis(
    Guid AgentId,
    string AgentName,
    IEnumerable<CapabilityAssessment> Capabilities,
    IEnumerable<SpecializationAssessment> Specializations,
    PerformanceProfile Performance,
    AvailabilityProfile Availability);

public record CapabilityAssessment(
    string Capability,
    double Strength,
    int UsageCount,
    DateTime? LastUsed);

public record SpecializationAssessment(
    string Domain,
    string Subdomain,
    int SkillLevel,
    double Confidence,
    int UsageCount,
    DateTime? LastUsed,
    IEnumerable<string> Keywords);

public record PerformanceProfile(
    double SuccessRate,
    double AverageResponseTime,
    int TotalTasks,
    DateTime? LastActive,
    IEnumerable<PerformanceTrend> Trends);

public record AvailabilityProfile(
    bool IsAvailable,
    int CurrentTaskCount,
    int MaxConcurrentTasks,
    TimeSpan? EstimatedFreeTime,
    IEnumerable<DateTime> PeakHours);

public record PerformanceTrend(
    string Metric,
    string Direction,
    double ChangePercentage,
    TimeSpan Period);

public record TeamFormationSuggestion(
    IEnumerable<AgentTeamMember> SuggestedTeam,
    double TeamScore,
    string Rationale,
    IEnumerable<TaskComponent> ComponentAssignments,
    TimeSpan EstimatedExecutionTime,
    IEnumerable<string> PotentialRisks);

public record AgentTeamMember(
    AIAgent Agent,
    TeamRole Role,
    IEnumerable<TaskComponent> AssignedComponents,
    double ContributionScore);

public enum MatchReason
{
    PerfectMatch,
    SpecializationMatch,
    CapabilityMatch,
    TypeMatch,
    BestAvailable,
    FallbackOption
}

public enum TaskPriority
{
    Low = 1,
    Medium = 2,
    High = 3,
    Critical = 4
}

public enum TeamRole
{
    Lead,
    Specialist,
    Support,
    Reviewer,
    Coordinator
}