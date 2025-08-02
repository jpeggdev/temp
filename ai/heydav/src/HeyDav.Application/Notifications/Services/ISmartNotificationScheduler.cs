using HeyDav.Domain.Notifications.Enums;

namespace HeyDav.Application.Notifications.Services;

public interface ISmartNotificationScheduler
{
    Task<DateTime> GetOptimalDeliveryTimeAsync(
        string? recipientId,
        NotificationType type,
        NotificationPriority priority,
        NotificationChannel channel,
        CancellationToken cancellationToken = default);

    Task<List<DateTime>> GetOptimalDeliveryTimesAsync(
        string? recipientId,
        NotificationType type,
        NotificationPriority priority,
        NotificationChannel channel,
        int count,
        TimeSpan windowSize,
        CancellationToken cancellationToken = default);

    Task<bool> ShouldBatchNotificationAsync(
        string? recipientId,
        NotificationType type,
        NotificationPriority priority,
        CancellationToken cancellationToken = default);

    Task<List<Guid>> GetNotificationsForBatchingAsync(
        string? recipientId,
        NotificationType type,
        TimeSpan batchWindow,
        CancellationToken cancellationToken = default);

    Task<UserActivityPattern> AnalyzeUserActivityAsync(
        string userId,
        TimeSpan analysisWindow,
        CancellationToken cancellationToken = default);

    Task UpdateUserInteractionFeedbackAsync(
        string userId,
        Guid notificationId,
        NotificationInteractionType interactionType,
        DateTime interactionTime,
        CancellationToken cancellationToken = default);

    Task<NotificationTiming> GetTimingRecommendationAsync(
        string? recipientId,
        NotificationType type,
        NotificationPriority priority,
        CancellationToken cancellationToken = default);

    Task OptimizeDeliveryScheduleAsync(CancellationToken cancellationToken = default);
}

public class UserActivityPattern
{
    public string UserId { get; set; } = string.Empty;
    public List<TimeOnly> PreferredTimes { get; set; } = new();
    public List<DayOfWeek> ActiveDays { get; set; } = new();
    public TimeOnly? FocusTimeStart { get; set; }
    public TimeOnly? FocusTimeEnd { get; set; }
    public Dictionary<NotificationType, double> TypeResponseRates { get; set; } = new();
    public Dictionary<NotificationChannel, double> ChannelPreferences { get; set; } = new();
    public TimeSpan AverageResponseTime { get; set; }
    public double OverallEngagementScore { get; set; }
}

public class NotificationTiming
{
    public DateTime OptimalTime { get; set; }
    public double ConfidenceScore { get; set; }
    public string Reasoning { get; set; } = string.Empty;
    public List<DateTime> AlternativeTimes { get; set; } = new();
    public bool ShouldBatch { get; set; }
    public TimeSpan? BatchWindow { get; set; }
}