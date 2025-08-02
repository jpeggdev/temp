using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.Notifications.Entities;
using HeyDav.Domain.Notifications.Enums;
using Microsoft.Extensions.Logging;
using Microsoft.EntityFrameworkCore;

namespace HeyDav.Application.Notifications.Services;

public class SmartNotificationScheduler : ISmartNotificationScheduler
{
    private readonly IApplicationDbContext _context;
    private readonly INotificationPreferenceService _preferenceService;
    private readonly ILogger<SmartNotificationScheduler> _logger;

    public SmartNotificationScheduler(
        IApplicationDbContext context,
        INotificationPreferenceService preferenceService,
        ILogger<SmartNotificationScheduler> logger)
    {
        _context = context ?? throw new ArgumentNullException(nameof(context));
        _preferenceService = preferenceService ?? throw new ArgumentNullException(nameof(preferenceService));
        _logger = logger ?? throw new ArgumentNullException(nameof(logger));
    }

    public async Task<DateTime> GetOptimalDeliveryTimeAsync(
        string? recipientId,
        NotificationType type,
        NotificationPriority priority,
        NotificationChannel channel,
        CancellationToken cancellationToken = default)
    {
        try
        {
            // For critical/urgent notifications, send immediately
            if (priority >= NotificationPriority.Urgent)
            {
                return DateTime.UtcNow;
            }

            // If no recipient ID, use default timing
            if (string.IsNullOrEmpty(recipientId))
            {
                return GetDefaultOptimalTime(type, priority);
            }

            // Get user preferences and activity pattern
            var preferences = await _preferenceService.GetPreferencesAsync(recipientId, type, cancellationToken);
            var activityPattern = await AnalyzeUserActivityAsync(recipientId, TimeSpan.FromDays(30), cancellationToken);

            // Check do-not-disturb settings
            var now = DateTime.UtcNow;
            if (preferences?.DoNotDisturbSettings.IsInDoNotDisturbPeriod(now) == true)
            {
                if (!preferences.DoNotDisturbSettings.ShouldAllowNotification(type, priority))
                {
                    return GetNextAvailableTime(preferences, activityPattern, now);
                }
            }

            // Use machine learning-based prediction if available
            var mlPrediction = await GetMLOptimalTimeAsync(recipientId, type, priority, channel, cancellationToken);
            if (mlPrediction.HasValue)
            {
                return mlPrediction.Value;
            }

            // Fall back to pattern-based scheduling
            return GetPatternBasedOptimalTime(activityPattern, preferences, type, priority);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get optimal delivery time for recipient {RecipientId}", recipientId);
            return DateTime.UtcNow; // Fall back to immediate delivery
        }
    }

    public async Task<List<DateTime>> GetOptimalDeliveryTimesAsync(
        string? recipientId,
        NotificationType type,
        NotificationPriority priority,
        NotificationChannel channel,
        int count,
        TimeSpan windowSize,
        CancellationToken cancellationToken = default)
    {
        var results = new List<DateTime>();
        var currentTime = DateTime.UtcNow;

        for (int i = 0; i < count; i++)
        {
            var optimalTime = await GetOptimalDeliveryTimeAsync(recipientId, type, priority, channel, cancellationToken);
            
            // Ensure we don't suggest the same time twice
            while (results.Any(r => Math.Abs((r - optimalTime).TotalMinutes) < 5))
            {
                optimalTime = optimalTime.AddMinutes(15);
            }

            // Ensure we stay within the window
            if (optimalTime > currentTime + windowSize)
            {
                break;
            }

            results.Add(optimalTime);
            currentTime = optimalTime.AddMinutes(15); // Minimum 15-minute gap
        }

        return results.OrderBy(t => t).ToList();
    }

    public async Task<bool> ShouldBatchNotificationAsync(
        string? recipientId,
        NotificationType type,
        NotificationPriority priority,
        CancellationToken cancellationToken = default)
    {
        try
        {
            // Never batch critical or urgent notifications
            if (priority >= NotificationPriority.Urgent)
            {
                return false;
            }

            // If no recipient ID, use default batching logic
            if (string.IsNullOrEmpty(recipientId))
            {
                return GetDefaultBatchingDecision(type, priority);
            }

            var preferences = await _preferenceService.GetPreferencesAsync(recipientId, type, cancellationToken);
            if (preferences?.SchedulingPreference.ShouldBatch(0, TimeSpan.Zero) == false)
            {
                return false;
            }

            // Check recent notification volume
            var recentNotifications = await _context.Notifications
                .Where(n => n.RecipientId == recipientId && 
                           n.CreatedAt > DateTime.UtcNow.AddHours(-2) &&
                           !n.IsDeleted)
                .CountAsync(cancellationToken);

            // Batch if user has received many notifications recently
            return recentNotifications >= 3;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to determine batching for recipient {RecipientId}", recipientId);
            return false;
        }
    }

    public async Task<List<Guid>> GetNotificationsForBatchingAsync(
        string? recipientId,
        NotificationType type,
        TimeSpan batchWindow,
        CancellationToken cancellationToken = default)
    {
        var cutoffTime = DateTime.UtcNow.Subtract(batchWindow);
        
        return await _context.Notifications
            .Where(n => n.RecipientId == recipientId &&
                       n.Type == type &&
                       n.Status == NotificationStatus.Pending &&
                       n.Priority < NotificationPriority.Urgent &&
                       n.CreatedAt >= cutoffTime &&
                       !n.IsDeleted)
            .Select(n => n.Id)
            .ToListAsync(cancellationToken);
    }

    public async Task<UserActivityPattern> AnalyzeUserActivityAsync(
        string userId,
        TimeSpan analysisWindow,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var cutoffTime = DateTime.UtcNow.Subtract(analysisWindow);
            
            var interactions = await _context.NotificationInteractions
                .Include(i => i.Notification)
                .Where(i => i.Notification.RecipientId == userId &&
                           i.InteractedAt >= cutoffTime &&
                           !i.IsDeleted)
                .ToListAsync(cancellationToken);

            var sentNotifications = await _context.Notifications
                .Where(n => n.RecipientId == userId &&
                           n.SentAt >= cutoffTime &&
                           n.Status == NotificationStatus.Sent &&
                           !n.IsDeleted)
                .ToListAsync(cancellationToken);

            return AnalyzeActivityPattern(userId, interactions, sentNotifications);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to analyze user activity for user {UserId}", userId);
            return new UserActivityPattern { UserId = userId };
        }
    }

    public async Task UpdateUserInteractionFeedbackAsync(
        string userId,
        Guid notificationId,
        NotificationInteractionType interactionType,
        DateTime interactionTime,
        CancellationToken cancellationToken = default)
    {
        try
        {
            // Record the interaction for future analysis
            var notification = await _context.Notifications
                .FirstOrDefaultAsync(n => n.Id == notificationId, cancellationToken);

            if (notification != null)
            {
                // Update machine learning model with feedback
                await UpdateMLModelAsync(userId, notification, interactionType, interactionTime, cancellationToken);
            }

            _logger.LogDebug("Updated interaction feedback for user {UserId}, notification {NotificationId}", 
                userId, notificationId);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update interaction feedback for user {UserId}", userId);
        }
    }

    public async Task<NotificationTiming> GetTimingRecommendationAsync(
        string? recipientId,
        NotificationType type,
        NotificationPriority priority,
        CancellationToken cancellationToken = default)
    {
        var optimalTime = await GetOptimalDeliveryTimeAsync(recipientId, type, priority, NotificationChannel.InApp, cancellationToken);
        var shouldBatch = await ShouldBatchNotificationAsync(recipientId, type, priority, cancellationToken);
        
        var alternatives = await GetOptimalDeliveryTimesAsync(
            recipientId, type, priority, NotificationChannel.InApp, 3, TimeSpan.FromHours(6), cancellationToken);

        return new NotificationTiming
        {
            OptimalTime = optimalTime,
            ConfidenceScore = CalculateConfidenceScore(recipientId, type, priority),
            Reasoning = GenerateReasoningText(recipientId, type, priority, optimalTime),
            AlternativeTimes = alternatives.Where(t => t != optimalTime).ToList(),
            ShouldBatch = shouldBatch,
            BatchWindow = shouldBatch ? TimeSpan.FromMinutes(30) : null
        };
    }

    public async Task OptimizeDeliveryScheduleAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            // Get all pending notifications that can be optimized
            var notifications = await _context.Notifications
                .Where(n => n.Status == NotificationStatus.Pending &&
                           n.Priority < NotificationPriority.Urgent &&
                           n.ScheduledAt > DateTime.UtcNow &&
                           !n.IsDeleted)
                .ToListAsync(cancellationToken);

            var optimizedCount = 0;

            foreach (var notification in notifications)
            {
                try
                {
                    var newOptimalTime = await GetOptimalDeliveryTimeAsync(
                        notification.RecipientId,
                        notification.Type,
                        notification.Priority,
                        notification.Channel,
                        cancellationToken);

                    // Only reschedule if the new time is significantly different and better
                    if (Math.Abs((newOptimalTime - notification.ScheduledAt!.Value).TotalMinutes) > 15)
                    {
                        notification.Reschedule(newOptimalTime);
                        optimizedCount++;
                    }
                }
                catch (Exception ex)
                {
                    _logger.LogWarning(ex, "Failed to optimize notification {NotificationId}", notification.Id);
                }
            }

            if (optimizedCount > 0)
            {
                await _context.SaveChangesAsync(cancellationToken);
                _logger.LogInformation("Optimized delivery schedule for {Count} notifications", optimizedCount);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to optimize delivery schedule");
            throw;
        }
    }

    private DateTime GetDefaultOptimalTime(NotificationType type, NotificationPriority priority)
    {
        var now = DateTime.UtcNow;
        var localNow = DateTime.Now;

        return type switch
        {
            NotificationType.TaskDeadline => now.AddMinutes(5), // Slight delay for task deadlines
            NotificationType.MeetingReminder => now.AddMinutes(15), // 15 minutes before meeting
            NotificationType.HabitReminder => GetNextHabitReminderTime(localNow),
            NotificationType.EmailDigest => GetNextDigestTime(localNow),
            _ => priority switch
            {
                NotificationPriority.High => now.AddMinutes(2),
                NotificationPriority.Medium => now.AddMinutes(15),
                NotificationPriority.Low => GetNextQuietTime(localNow),
                _ => now
            }
        };
    }

    private DateTime GetNextAvailableTime(NotificationPreference preferences, UserActivityPattern activityPattern, DateTime from)
    {
        var candidate = from.AddHours(1);
        
        while (preferences.DoNotDisturbSettings.IsInDoNotDisturbPeriod(candidate))
        {
            candidate = candidate.AddMinutes(30);
            
            // Don't go more than 24 hours in the future
            if (candidate > from.AddDays(1))
            {
                break;
            }
        }

        return candidate;
    }

    private async Task<DateTime?> GetMLOptimalTimeAsync(
        string recipientId,
        NotificationType type,
        NotificationPriority priority,
        NotificationChannel channel,
        CancellationToken cancellationToken)
    {
        // Placeholder for machine learning integration
        // This would interface with an ML model trained on user interaction patterns
        return null;
    }

    private DateTime GetPatternBasedOptimalTime(
        UserActivityPattern activityPattern,
        NotificationPreference? preferences,
        NotificationType type,
        NotificationPriority priority)
    {
        var now = DateTime.UtcNow;
        var localNow = DateTime.Now;

        // Use user's preferred times if available
        if (activityPattern.PreferredTimes.Any())
        {
            var currentTime = TimeOnly.FromDateTime(localNow);
            var nextPreferredTime = activityPattern.PreferredTimes
                .Where(t => t > currentTime)
                .OrderBy(t => t)
                .FirstOrDefault();

            if (nextPreferredTime != default)
            {
                var today = DateOnly.FromDateTime(localNow);
                return today.ToDateTime(nextPreferredTime).ToUniversalTime();
            }
        }

        // Use scheduling preferences if available
        if (preferences?.SchedulingPreference != null)
        {
            return preferences.SchedulingPreference.GetNextPreferredTime(localNow).ToUniversalTime();
        }

        // Fall back to default timing
        return GetDefaultOptimalTime(type, priority);
    }

    private UserActivityPattern AnalyzeActivityPattern(
        string userId,
        List<NotificationInteraction> interactions,
        List<Notification> sentNotifications)
    {
        var pattern = new UserActivityPattern { UserId = userId };

        if (!interactions.Any()) return pattern;

        // Analyze preferred interaction times
        var interactionTimes = interactions.Select(i => TimeOnly.FromDateTime(i.InteractedAt.ToLocalTime())).ToList();
        pattern.PreferredTimes = FindPreferredTimeSlots(interactionTimes);

        // Analyze active days
        var interactionDays = interactions.Select(i => i.InteractedAt.DayOfWeek).ToList();
        pattern.ActiveDays = interactionDays.GroupBy(d => d)
            .OrderByDescending(g => g.Count())
            .Take(5)
            .Select(g => g.Key)
            .ToList();

        // Calculate response rates by type
        var notificationsByType = sentNotifications.GroupBy(n => n.Type);
        foreach (var group in notificationsByType)
        {
            var typeInteractions = interactions.Count(i => 
                sentNotifications.Any(n => n.Id == i.NotificationId && n.Type == group.Key));
            pattern.TypeResponseRates[group.Key] = (double)typeInteractions / group.Count();
        }

        // Calculate average response time
        var responseTimes = interactions
            .Join(sentNotifications, i => i.NotificationId, n => n.Id, (i, n) => new { i, n })
            .Where(x => x.n.SentAt.HasValue)
            .Select(x => x.i.InteractedAt - x.n.SentAt!.Value)
            .Where(duration => duration.TotalHours < 24) // Ignore very late responses
            .ToList();

        if (responseTimes.Any())
        {
            pattern.AverageResponseTime = TimeSpan.FromMilliseconds(responseTimes.Average(t => t.TotalMilliseconds));
        }

        // Calculate overall engagement score
        var totalSent = sentNotifications.Count;
        var totalInteractions = interactions.Count;
        pattern.OverallEngagementScore = totalSent > 0 ? (double)totalInteractions / totalSent : 0.0;

        return pattern;
    }

    private List<TimeOnly> FindPreferredTimeSlots(List<TimeOnly> interactionTimes)
    {
        if (!interactionTimes.Any()) return new List<TimeOnly>();

        // Group times into 2-hour slots and find most active periods
        var slots = interactionTimes
            .GroupBy(t => new TimeOnly(t.Hour - (t.Hour % 2), 0))
            .OrderByDescending(g => g.Count())
            .Take(3)
            .Select(g => g.Key)
            .OrderBy(t => t)
            .ToList();

        return slots;
    }

    private async Task UpdateMLModelAsync(
        string userId,
        Notification notification,
        NotificationInteractionType interactionType,
        DateTime interactionTime,
        CancellationToken cancellationToken)
    {
        // Placeholder for ML model updates
        // This would send feedback to a machine learning service
        await Task.CompletedTask;
    }

    private double CalculateConfidenceScore(string? recipientId, NotificationType type, NotificationPriority priority)
    {
        // Calculate confidence based on available data
        var baseScore = 0.5; // 50% base confidence

        if (!string.IsNullOrEmpty(recipientId))
        {
            baseScore += 0.3; // +30% for having user data
        }

        if (priority >= NotificationPriority.High)
        {
            baseScore += 0.2; // +20% for high priority (more predictable)
        }

        return Math.Min(1.0, baseScore);
    }

    private string GenerateReasoningText(string? recipientId, NotificationType type, NotificationPriority priority, DateTime optimalTime)
    {
        var reasons = new List<string>();

        if (priority >= NotificationPriority.Urgent)
        {
            reasons.Add("High priority notification scheduled for immediate delivery");
        }
        else if (optimalTime > DateTime.UtcNow.AddMinutes(30))
        {
            reasons.Add("Scheduled during user's active hours based on historical patterns");
        }
        else
        {
            reasons.Add("Scheduled for immediate delivery based on notification priority");
        }

        if (!string.IsNullOrEmpty(recipientId))
        {
            reasons.Add("Timing optimized based on user preferences and activity patterns");
        }

        return string.Join(". ", reasons);
    }

    private bool GetDefaultBatchingDecision(NotificationType type, NotificationPriority priority)
    {
        return type switch
        {
            NotificationType.EmailDigest => true,
            NotificationType.NewsUpdate => true,
            NotificationType.System => false,
            _ => priority <= NotificationPriority.Medium
        };
    }

    private DateTime GetNextHabitReminderTime(DateTime localNow)
    {
        // Schedule habit reminders for the next "motivation hour" (9 AM, 1 PM, or 6 PM)
        var motivationHours = new[] { 9, 13, 18 };
        var currentHour = localNow.Hour;
        
        var nextHour = motivationHours.FirstOrDefault(h => h > currentHour);
        if (nextHour == 0) // No more hours today, use first hour tomorrow
        {
            nextHour = motivationHours[0];
            return new DateTime(localNow.Year, localNow.Month, localNow.Day).AddDays(1).AddHours(nextHour).ToUniversalTime();
        }

        return new DateTime(localNow.Year, localNow.Month, localNow.Day).AddHours(nextHour).ToUniversalTime();
    }

    private DateTime GetNextDigestTime(DateTime localNow)
    {
        // Schedule digests for 8 AM or 6 PM
        var digestHours = new[] { 8, 18 };
        var currentHour = localNow.Hour;
        
        var nextHour = digestHours.FirstOrDefault(h => h > currentHour);
        if (nextHour == 0) // No more hours today, use first hour tomorrow
        {
            nextHour = digestHours[0];
            return new DateTime(localNow.Year, localNow.Month, localNow.Day).AddDays(1).AddHours(nextHour).ToUniversalTime();
        }

        return new DateTime(localNow.Year, localNow.Month, localNow.Day).AddHours(nextHour).ToUniversalTime();
    }

    private DateTime GetNextQuietTime(DateTime localNow)
    {
        // Schedule low priority notifications during quiet hours (10 AM, 2 PM, 7 PM)
        var quietHours = new[] { 10, 14, 19 };
        var currentHour = localNow.Hour;
        
        var nextHour = quietHours.FirstOrDefault(h => h > currentHour);
        if (nextHour == 0) // No more hours today, use first hour tomorrow
        {
            nextHour = quietHours[0];
            return new DateTime(localNow.Year, localNow.Month, localNow.Day).AddDays(1).AddHours(nextHour).ToUniversalTime();
        }

        return new DateTime(localNow.Year, localNow.Month, localNow.Day).AddHours(nextHour).ToUniversalTime();
    }
}