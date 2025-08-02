using HeyDav.Domain.Goals.Entities;
using HeyDav.Application.Workflows.Models;

namespace HeyDav.Application.Workflows.Interfaces;

public interface IGoalProgressEngine
{
    Task<GoalProgressReport> GenerateProgressReportAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<List<ActionItem>> GenerateActionPlanAsync(Guid goalId, ActionPlanRequest request, CancellationToken cancellationToken = default);
    Task<GoalTrackingInsights> GetTrackingInsightsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<MilestoneRecommendation>> SuggestMilestonesAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<GoalOptimizationSuggestions> OptimizeGoalAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<List<Goal>> GetGoalsNeedingAttentionAsync(string userId, CancellationToken cancellationToken = default);
    Task<CourseCorrection> SuggestCourseCorrectionsAsync(Guid goalId, CancellationToken cancellationToken = default);
}

public interface IGoalRepository
{
    Task<Goal?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default);
    Task<List<Goal>> GetByUserIdAsync(string userId, bool includeCompleted = false, CancellationToken cancellationToken = default);
    Task AddAsync(Goal goal, CancellationToken cancellationToken = default);
    Task<int> SaveChangesAsync(CancellationToken cancellationToken = default);
}

public interface IGoalAnalytics
{
    Task<List<ProgressDataPoint>> GetProgressHistoryAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<GoalPerformanceMetrics> CalculatePerformanceMetricsAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<List<GoalTrend>> AnalyzeTrendsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<GoalCompletionPrediction> PredictCompletionAsync(Guid goalId, CancellationToken cancellationToken = default);
}

public interface IActionPlanGenerator
{
    Task<List<ActionItem>> GenerateActionItemsAsync(ActionPlanContext context, CancellationToken cancellationToken = default);
    Task<List<ActionItem>> RefineActionPlanAsync(List<ActionItem> existingPlan, Goal goal, CancellationToken cancellationToken = default);
    Task<ActionPlanTemplate> GetTemplateForGoalTypeAsync(GoalType goalType, CancellationToken cancellationToken = default);
}

public interface IProgressPredictor
{
    Task<DateTime?> PredictCompletionDateAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<GoalOutcomePrediction> PredictOutcomeAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<decimal> CalculateSuccessProbabilityAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task UpdatePredictionModelAsync(Guid goalId, GoalOutcome actualOutcome, CancellationToken cancellationToken = default);
}