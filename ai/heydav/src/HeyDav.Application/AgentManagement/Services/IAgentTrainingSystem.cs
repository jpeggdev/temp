using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.ValueObjects;

namespace HeyDav.Application.AgentManagement.Services;

public interface IAgentTrainingSystem
{
    // Performance Tracking
    Task<bool> RecordTaskPerformanceAsync(Guid agentId, TaskPerformanceRecord record, CancellationToken cancellationToken = default);
    Task<bool> RecordUserFeedbackAsync(Guid agentId, UserFeedbackRecord feedback, CancellationToken cancellationToken = default);
    Task<AgentPerformanceAnalysis> AnalyzeAgentPerformanceAsync(Guid agentId, TimeSpan? period = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<PerformanceTrend>> GetPerformanceTrendsAsync(Guid agentId, CancellationToken cancellationToken = default);

    // Learning and Adaptation
    Task<bool> UpdateAgentFromPerformanceAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> LearnFromTaskOutcomeAsync(Guid agentId, TaskOutcome outcome, CancellationToken cancellationToken = default);
    Task<bool> AdaptSpecializationsAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> RefineCapabilitiesAsync(Guid agentId, CancellationToken cancellationToken = default);

    // Knowledge Management
    Task<bool> AddLearningExampleAsync(Guid agentId, LearningExample example, CancellationToken cancellationToken = default);
    Task<IEnumerable<LearningExample>> GetRelevantExamplesAsync(Guid agentId, string domain, string? subdomain = null, CancellationToken cancellationToken = default);
    Task<bool> UpdateKnowledgeBaseAsync(Guid agentId, KnowledgeUpdate update, CancellationToken cancellationToken = default);

    // Training Programs
    Task<Guid> CreateTrainingProgramAsync(TrainingProgram program, CancellationToken cancellationToken = default);
    Task<bool> EnrollAgentInProgramAsync(Guid agentId, Guid programId, CancellationToken cancellationToken = default);
    Task<bool> ExecuteTrainingAsync(Guid agentId, Guid programId, CancellationToken cancellationToken = default);
    Task<TrainingProgress?> GetTrainingProgressAsync(Guid agentId, Guid programId, CancellationToken cancellationToken = default);

    // Comparative Analysis
    Task<AgentComparison> CompareAgentsAsync(IEnumerable<Guid> agentIds, string? domain = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetTopPerformingAgentsAsync(string? domain = null, int count = 10, CancellationToken cancellationToken = default);
    Task<BenchmarkResults> BenchmarkAgentAsync(Guid agentId, BenchmarkSuite suite, CancellationToken cancellationToken = default);

    // Automated Optimization
    Task OptimizeAllAgentsAsync(CancellationToken cancellationToken = default);
    Task<OptimizationSuggestion> AnalyzeOptimizationOpportunitiesAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> ApplyOptimizationAsync(Guid agentId, OptimizationSuggestion suggestion, CancellationToken cancellationToken = default);
}

public record TaskPerformanceRecord(
    Guid TaskId,
    string TaskDescription,
    string Domain,
    string? Subdomain,
    IEnumerable<string> Keywords,
    bool Success,
    TimeSpan ExecutionTime,
    double QualityScore,
    string? ErrorMessage = null,
    Dictionary<string, object>? Metadata = null,
    DateTime Timestamp = default);

public record UserFeedbackRecord(
    Guid TaskId,
    int Rating, // 1-5 scale
    string? Comments,
    IEnumerable<string>? PositiveAspects,
    IEnumerable<string>? AreasForImprovement,
    FeedbackCategory Category,
    DateTime Timestamp = default);

public record TaskOutcome(
    Guid TaskId,
    string TaskType,
    string Domain,
    bool Success,
    TimeSpan Duration,
    double Confidence,
    IEnumerable<string> ChallengesFaced,
    IEnumerable<string> SkillsUsed,
    string? LessonsLearned = null);

public record LearningExample(
    Guid Id,
    string Domain,
    string Subdomain,
    string Scenario,
    string Context,
    string Action,
    string Result,
    double Effectiveness,
    IEnumerable<string> Keywords,
    DateTime CreatedAt = default);

public record KnowledgeUpdate(
    string Domain,
    string Type, // "fact", "pattern", "rule", "example"
    string Content,
    double Confidence,
    IEnumerable<string> Sources,
    DateTime ValidFrom = default);

public record TrainingProgram(
    Guid Id,
    string Name,
    string Description,
    IEnumerable<TrainingModule> Modules,
    TimeSpan EstimatedDuration,
    IEnumerable<string> Prerequisites,
    TrainingObjective Objective);

public record TrainingModule(
    string Id,
    string Name,
    TrainingModuleType Type,
    IEnumerable<TrainingExercise> Exercises,
    TimeSpan Duration);

public record TrainingExercise(
    string Id,
    string Description,
    ExerciseType Type,
    Dictionary<string, object> Parameters,
    double PassingScore);

public record TrainingProgress(
    Guid AgentId,
    Guid ProgramId,
    TrainingStatus Status,
    double CompletionPercentage,
    IEnumerable<ModuleProgress> ModuleProgress,
    double OverallScore,
    DateTime StartedAt,
    DateTime? CompletedAt = null);

public record ModuleProgress(
    string ModuleId,
    TrainingStatus Status,
    double Score,
    IEnumerable<ExerciseResult> ExerciseResults,
    DateTime StartedAt,
    DateTime? CompletedAt = null);

public record ExerciseResult(
    string ExerciseId,
    bool Passed,
    double Score,
    TimeSpan Duration,
    string? Feedback = null);

public record AgentPerformanceAnalysis(
    Guid AgentId,
    string AgentName,
    TimeSpan AnalysisPeriod,
    PerformanceMetrics Overall,
    IEnumerable<DomainPerformance> ByDomain,
    IEnumerable<PerformanceTrend> Trends,
    IEnumerable<string> Strengths,
    IEnumerable<string> WeakAreas,
    double LearningVelocity);

public record PerformanceMetrics(
    int TotalTasks,
    double SuccessRate,
    double AverageQualityScore,
    TimeSpan AverageExecutionTime,
    double UserSatisfactionScore,
    int ImprovementCount,
    int RegressionCount);

public record DomainPerformance(
    string Domain,
    string? Subdomain,
    PerformanceMetrics Metrics,
    double SpecializationLevel,
    IEnumerable<string> TopSkills);

public record AgentComparison(
    IEnumerable<AgentPerformanceRanking> Rankings,
    string ComparisonDomain,
    DateTime AnalysisDate,
    IEnumerable<ComparisonInsight> Insights);

public record AgentPerformanceRanking(
    Guid AgentId,
    string AgentName,
    int Rank,
    double Score,
    IEnumerable<string> StrongAreas,
    IEnumerable<string> WeakAreas);

public record ComparisonInsight(
    string Category,
    string Description,
    IEnumerable<Guid> RelevantAgents,
    double Impact);

public record BenchmarkResults(
    Guid AgentId,
    string SuiteName,
    double OverallScore,
    IEnumerable<BenchmarkTestResult> TestResults,
    TimeSpan TotalDuration,
    DateTime ExecutedAt);

public record BenchmarkTestResult(
    string TestName,
    string Category,
    bool Passed,
    double Score,
    TimeSpan Duration,
    string? Details = null);

public record BenchmarkSuite(
    string Name,
    IEnumerable<BenchmarkTest> Tests,
    double PassingScore);

public record BenchmarkTest(
    string Name,
    string Category,
    string Description,
    Dictionary<string, object> Parameters,
    double Weight);

public record OptimizationSuggestion(
    Guid AgentId,
    OptimizationType Type,
    string Description,
    double ExpectedImprovement,
    IEnumerable<OptimizationAction> Actions,
    double ConfidenceLevel,
    string Rationale);

public record OptimizationAction(
    OptimizationActionType Type,
    Dictionary<string, object> Parameters,
    double Priority);

public enum FeedbackCategory
{
    Quality,
    Speed,
    Accuracy,
    Creativity,
    Communication,
    ProblemSolving,
    General
}

public enum TrainingModuleType
{
    Skills,
    Knowledge,
    Practice,
    Simulation,
    Assessment
}

public enum ExerciseType
{
    MultipleChoice,
    PracticalTask,
    CaseStudy,
    Simulation,
    PeerReview
}

public enum TrainingStatus
{
    NotStarted,
    InProgress,
    Completed,
    Failed,
    Suspended
}

public enum TrainingObjective
{
    SkillImprovement,
    KnowledgeExpansion,
    PerformanceOptimization,
    SpecializationDevelopment,
    CrossTraining
}

public enum OptimizationType
{
    SpecializationAdjustment,
    CapabilityEnhancement,
    PerformanceTuning,
    KnowledgeUpdate,
    ConfigurationOptimization
}

public enum OptimizationActionType
{
    UpdateSpecialization,
    AddCapability,
    RemoveCapability,
    AdjustConfiguration,
    AddTraining,
    UpdateKnowledge
}