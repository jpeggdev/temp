using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;

namespace HeyDav.Application.AgentManagement.Services;

public interface IAgentDiscoveryService
{
    // Agent Discovery
    Task<IEnumerable<AgentDiscoveryResult>> DiscoverAgentsAsync(AgentDiscoveryRequest request, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> FindSimilarAgentsAsync(Guid agentId, int maxResults = 5, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAgentsByCapabilityAsync(string capability, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAgentsByTypeAsync(AgentType type, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAgentsBySpecializationAsync(string domain, string? subdomain = null, CancellationToken cancellationToken = default);

    // Agent Recommendations
    Task<IEnumerable<AgentRecommendation>> GetRecommendedAgentsAsync(TaskContext context, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentRecommendation>> GetComplementaryAgentsAsync(Guid agentId, string objective, CancellationToken cancellationToken = default);
    Task<IEnumerable<TeamRecommendation>> RecommendTeamsForTaskAsync(ComplexTaskContext context, CancellationToken cancellationToken = default);

    // Capability Analysis
    Task<CapabilityGapAnalysis> AnalyzeCapabilityGapsAsync(IEnumerable<string> requiredCapabilities, CancellationToken cancellationToken = default);
    Task<CapabilityCoverage> GetCapabilityCoverageAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<CapabilityCluster>> GetCapabilityClustersAsync(CancellationToken cancellationToken = default);

    // Agent Network Analysis
    Task<AgentNetwork> AnalyzeAgentNetworkAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentInfluenceMetric>> GetAgentInfluenceMetricsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<CollaborationPattern>> GetCollaborationPatternsAsync(TimeSpan? period = null, CancellationToken cancellationToken = default);

    // Agent Availability and Load
    Task<IEnumerable<AgentAvailability>> GetAgentAvailabilityAsync(IEnumerable<Guid>? agentIds = null, CancellationToken cancellationToken = default);
    Task<LoadBalancingRecommendation> GetLoadBalancingRecommendationAsync(TaskRequirements task, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetUnderutilizedAgentsAsync(TimeSpan? period = null, CancellationToken cancellationToken = default);

    // Dynamic Agent Provisioning
    Task<AgentProvisioningRecommendation> AnalyzeProvisioningNeedsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentScalingOpportunity>> GetScalingOpportunitiesAsync(CancellationToken cancellationToken = default);
}

public record AgentDiscoveryRequest(
    IEnumerable<string>? RequiredCapabilities = null,
    IEnumerable<string>? PreferredSpecializations = null,
    AgentType? PreferredType = null,
    TaskPriority? MinTaskHandlingPriority = null,
    double? MinSuccessRate = null,
    double? MinConfidenceLevel = null,
    bool? MustBeAvailable = null,
    int? MaxCurrentTasks = null,
    Dictionary<string, object>? AdditionalCriteria = null);

public record AgentDiscoveryResult(
    AIAgent Agent,
    double RelevanceScore,
    MatchQuality MatchQuality,
    IEnumerable<string> MatchedCapabilities,
    IEnumerable<string> MatchedSpecializations,
    AvailabilityStatus Availability,
    IEnumerable<string> MatchReasons,
    Dictionary<string, double> DetailedScores);

public record AgentRecommendation(
    AIAgent Agent,
    double RecommendationScore,
    RecommendationType Type,
    string Rationale,
    IEnumerable<string> Benefits,
    IEnumerable<string> Considerations,
    double ConfidenceLevel);

public record TeamRecommendation(
    IEnumerable<AgentTeamMemberRecommendation> Members,
    double TeamScore,
    string Formation,
    IEnumerable<string> Strengths,
    IEnumerable<string> PotentialChallenges,
    TimeSpan EstimatedCompletionTime,
    double CollaborationCompatibility);

public record AgentTeamMemberRecommendation(
    AIAgent Agent,
    TeamRole SuggestedRole,
    IEnumerable<string> Responsibilities,
    double ContributionScore,
    IEnumerable<Guid> SynergyPartners);

public record TaskContext(
    string Description,
    string? Domain = null,
    string? Category = null,
    TaskPriority Priority = TaskPriority.Medium,
    TimeSpan? EstimatedDuration = null,
    IEnumerable<string>? RequiredSkills = null,
    Dictionary<string, object>? Context = null);

public record ComplexTaskContext(
    string Description,
    IEnumerable<TaskComponent> Components,
    TaskPriority Priority = TaskPriority.Medium,
    TimeSpan? MaxDuration = null,
    bool AllowParallelExecution = true,
    Dictionary<string, object>? Context = null);

public record CapabilityGapAnalysis(
    IEnumerable<string> MissingCapabilities,
    IEnumerable<string> UnderrepresentedCapabilities,
    IEnumerable<CapabilityGap> DetailedGaps,
    IEnumerable<GapMitigationStrategy> MitigationStrategies,
    double OverallCoverageScore);

public record CapabilityGap(
    string Capability,
    int RequiredAgents,
    int AvailableAgents,
    double AverageSkillLevel,
    GapSeverity Severity);

public record GapMitigationStrategy(
    string Strategy,
    IEnumerable<string> TargetCapabilities,
    double EffectivenessScore,
    TimeSpan EstimatedImplementationTime,
    IEnumerable<string> RequiredActions);

public record CapabilityCoverage(
    int TotalCapabilities,
    int CoveredCapabilities,
    double CoveragePercentage,
    IEnumerable<CapabilityCoverageDetail> Details,
    IEnumerable<string> WellCoveredCapabilities,
    IEnumerable<string> PoorlyCoveredCapabilities);

public record CapabilityCoverageDetail(
    string Capability,
    int AgentCount,
    double AverageSkillLevel,
    double AverageConfidence,
    CoverageQuality Quality);

public record CapabilityCluster(
    string ClusterName,
    IEnumerable<string> Capabilities,
    IEnumerable<Guid> SpecializedAgents,
    double ClusterStrength,
    IEnumerable<string> CommonPatterns);

public record AgentNetwork(
    IEnumerable<AgentNetworkNode> Nodes,
    IEnumerable<AgentNetworkEdge> Edges,
    NetworkMetrics Metrics,
    IEnumerable<AgentCommunity> Communities);

public record AgentNetworkNode(
    Guid AgentId,
    string AgentName,
    int ConnectionCount,
    double CentralityScore,
    IEnumerable<string> PrimaryCapabilities);

public record AgentNetworkEdge(
    Guid FromAgentId,
    Guid ToAgentId,
    double ConnectionStrength,
    IEnumerable<string> CollaborationTypes,
    int InteractionCount,
    DateTime LastInteraction);

public record NetworkMetrics(
    int TotalNodes,
    int TotalEdges,
    double AverageConnectivity,
    double NetworkDensity,
    double ClusteringCoefficient,
    IEnumerable<Guid> CentralNodes);

public record AgentCommunity(
    string CommunityName,
    IEnumerable<Guid> Members,
    IEnumerable<string> SharedCapabilities,
    double CohesionScore,
    string PrimaryFocus);

public record AgentInfluenceMetric(
    Guid AgentId,
    string AgentName,
    double InfluenceScore,
    InfluenceType Type,
    IEnumerable<string> InfluenceAreas,
    int DirectInfluenceCount,
    int IndirectInfluenceCount);

public record CollaborationPattern(
    string PatternName,
    IEnumerable<Guid> ParticipatingAgents,
    string Description,
    double Frequency,
    double SuccessRate,
    IEnumerable<string> CommonScenarios);

public record AgentAvailability(
    Guid AgentId,
    string AgentName,
    AvailabilityStatus Status,
    int CurrentTaskCount,
    int MaxTaskCapacity,
    TimeSpan? EstimatedAvailableIn,
    IEnumerable<string> CurrentCommitments);

public record LoadBalancingRecommendation(
    AIAgent PrimaryRecommendation,
    IEnumerable<AIAgent> AlternativeOptions,
    LoadDistributionStrategy Strategy,
    string Rationale,
    double ExpectedLoadReduction);

public record AgentProvisioningRecommendation(
    IEnumerable<AgentProvisioningNeed> ProvisioningNeeds,
    IEnumerable<AgentRetirementCandidate> RetirementCandidates,
    IEnumerable<AgentUpgradeOpportunity> UpgradeOpportunities,
    double SystemUtilization,
    string OverallAssessment);

public record AgentProvisioningNeed(
    AgentType RecommendedType,
    IEnumerable<string> RequiredCapabilities,
    int RecommendedCount,
    ProvisioningPriority Priority,
    string Justification);

public record AgentRetirementCandidate(
    Guid AgentId,
    string AgentName,
    IEnumerable<string> RetirementReasons,
    double UtilizationScore,
    IEnumerable<Guid> ReplacementCandidates);

public record AgentUpgradeOpportunity(
    Guid AgentId,
    string AgentName,
    IEnumerable<string> SuggestedUpgrades,
    double ImpactScore,
    TimeSpan EstimatedUpgradeTime);

public record AgentScalingOpportunity(
    string OpportunityType,
    IEnumerable<Guid> TargetAgents,
    ScalingDirection Direction,
    double PotentialImpact,
    IEnumerable<string> RequiredActions);

public enum MatchQuality
{
    Excellent,
    Good,
    Fair,
    Poor,
    NoMatch
}

public enum AvailabilityStatus
{
    Immediately,
    Soon,
    Busy,
    Overloaded,
    Unavailable
}

public enum RecommendationType
{
    BestMatch,
    HighPerformer,
    Specialist,
    Generalist,
    Complementary,
    Learning,
    Backup
}

public enum GapSeverity
{
    Critical,
    High,
    Medium,
    Low
}

public enum CoverageQuality
{
    Excellent,
    Good,
    Adequate,
    Poor,
    Missing
}

public enum InfluenceType
{
    Expertise,
    Communication,
    Collaboration,
    Performance,
    Innovation
}

public enum LoadDistributionStrategy
{
    RoundRobin,
    CapabilityBased,
    PerformanceBased,
    AvailabilityBased,
    Hybrid
}

public enum ProvisioningPriority
{
    Critical,
    High,
    Medium,
    Low
}

public enum ScalingDirection
{
    ScaleUp,
    ScaleDown,
    Optimize,
    Redistribute
}