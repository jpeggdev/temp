using HeyDav.Domain.Analytics.Entities;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Application.Analytics.Models;

namespace HeyDav.Application.Analytics.Services;

public interface ITimeTrackingSystem
{
    // Manual Time Tracking
    Task<TimeEntry> StartManualTrackingAsync(string userId, string activity, string? project = null, string? category = null, CancellationToken cancellationToken = default);
    Task<TimeEntry> StopTrackingAsync(string userId, CancellationToken cancellationToken = default);
    Task<TimeEntry> StopTrackingAsync(Guid timeEntryId, CancellationToken cancellationToken = default);
    Task<TimeEntry> CreateTimeEntryAsync(string userId, DateTime startTime, DateTime endTime, string activity, string? project = null, string? category = null, CancellationToken cancellationToken = default);
    Task<TimeEntry> UpdateTimeEntryAsync(Guid timeEntryId, TimeEntryUpdateRequest request, CancellationToken cancellationToken = default);
    Task DeleteTimeEntryAsync(Guid timeEntryId, CancellationToken cancellationToken = default);
    
    // Automatic Time Tracking
    Task StartAutomaticTrackingAsync(string userId, CancellationToken cancellationToken = default);
    Task StopAutomaticTrackingAsync(string userId, CancellationToken cancellationToken = default);
    Task<bool> IsAutomaticTrackingActiveAsync(string userId, CancellationToken cancellationToken = default);
    Task ProcessAutomaticTrackingDataAsync(string userId, List<ActivityDetectionResult> activities, CancellationToken cancellationToken = default);
    
    // Time Entry Queries
    Task<List<TimeEntry>> GetTimeEntriesAsync(string userId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default);
    Task<TimeEntry?> GetActiveTimeEntryAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetTimeEntriesByProjectAsync(string userId, string project, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetTimeEntriesByCategoryAsync(string userId, string category, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetBillableTimeEntriesAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Time Analysis and Reporting
    Task<TimeAllocationReport> GetTimeAllocationReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<ProjectTimeReport> GetProjectTimeReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<CategoryTimeReport> GetCategoryTimeReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<ProductivityTimeReport> GetProductivityTimeReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<BillableTimeReport> GetBillableTimeReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Time Estimation and Improvement
    Task<TimeEstimationInsight> GetTimeEstimationInsightAsync(string userId, string activity, CancellationToken cancellationToken = default);
    Task<List<TimeImprovementSuggestion>> GetTimeImprovementSuggestionsAsync(string userId, CancellationToken cancellationToken = default);
    Task UpdateTimeEstimationModelAsync(string userId, string activity, TimeSpan actualTime, TimeSpan estimatedTime, CancellationToken cancellationToken = default);
    
    // Focus and Deep Work Tracking
    Task<FocusSession> StartFocusSessionAsync(string userId, string activity, TimeSpan plannedDuration, CancellationToken cancellationToken = default);
    Task<FocusSession> EndFocusSessionAsync(Guid focusSessionId, int focusScore, int interruptionCount, CancellationToken cancellationToken = default);
    Task<List<FocusSession>> GetFocusSessionsAsync(string userId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default);
    Task<FocusMetrics> GetFocusMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Integration with Tasks and Goals
    Task LinkTimeEntryToTaskAsync(Guid timeEntryId, Guid taskId, CancellationToken cancellationToken = default);
    Task LinkTimeEntryToGoalAsync(Guid timeEntryId, Guid goalId, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetTimeEntriesForTaskAsync(Guid taskId, CancellationToken cancellationToken = default);
    Task<List<TimeEntry>> GetTimeEntriesForGoalAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<TaskTimeAnalysis> GetTaskTimeAnalysisAsync(Guid taskId, CancellationToken cancellationToken = default);
    Task<GoalTimeAnalysis> GetGoalTimeAnalysisAsync(Guid goalId, CancellationToken cancellationToken = default);
}

public interface IAutomaticTimeTracker
{
    Task<bool> IsTrackingAsync(string userId, CancellationToken cancellationToken = default);
    Task StartTrackingAsync(string userId, AutomaticTrackingSettings settings, CancellationToken cancellationToken = default);
    Task StopTrackingAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ActivityDetectionResult>> GetDetectedActivitiesAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task ProcessActivityDataAsync(string userId, ActivityData activityData, CancellationToken cancellationToken = default);
    Task UpdateTrackingSettingsAsync(string userId, AutomaticTrackingSettings settings, CancellationToken cancellationToken = default);
}

public interface IFocusTracker
{
    Task<FocusSession> StartFocusSessionAsync(string userId, FocusSessionRequest request, CancellationToken cancellationToken = default);
    Task<FocusSession> UpdateFocusSessionAsync(Guid sessionId, FocusSessionUpdate update, CancellationToken cancellationToken = default);
    Task<FocusSession> EndFocusSessionAsync(Guid sessionId, FocusSessionCompletion completion, CancellationToken cancellationToken = default);
    Task<List<FocusSession>> GetFocusSessionsAsync(string userId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default);
    Task<FocusMetrics> CalculateFocusMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<FocusInsight>> GenerateFocusInsightsAsync(string userId, CancellationToken cancellationToken = default);
}

public interface ITimeEstimationEngine
{
    Task<TimeEstimate> EstimateTaskTimeAsync(string userId, string taskDescription, string? category = null, CancellationToken cancellationToken = default);
    Task<TimeEstimate> EstimateProjectTimeAsync(string userId, string projectDescription, List<string> taskDescriptions, CancellationToken cancellationToken = default);
    Task UpdateEstimationModelAsync(string userId, string activity, TimeSpan actualTime, TimeSpan estimatedTime, CancellationToken cancellationToken = default);
    Task<TimeEstimationAccuracy> GetEstimationAccuracyAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<TimeEstimationInsight>> GetEstimationInsightsAsync(string userId, CancellationToken cancellationToken = default);
}