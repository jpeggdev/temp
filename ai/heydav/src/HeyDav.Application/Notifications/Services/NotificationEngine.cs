using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.Notifications.Channels;
using HeyDav.Domain.Notifications.Entities;
using HeyDav.Domain.Notifications.Enums;
using HeyDav.Domain.Notifications.ValueObjects;
using Microsoft.Extensions.Logging;
using Microsoft.EntityFrameworkCore;

namespace HeyDav.Application.Notifications.Services;

public class NotificationEngine : INotificationEngine
{
    private readonly IApplicationDbContext _context;
    private readonly INotificationChannelManager _channelManager;
    private readonly ISmartNotificationScheduler _scheduler;
    private readonly INotificationPreferenceService _preferenceService;
    private readonly ILogger<NotificationEngine> _logger;

    public NotificationEngine(
        IApplicationDbContext context,
        INotificationChannelManager channelManager,
        ISmartNotificationScheduler scheduler,
        INotificationPreferenceService preferenceService,
        ILogger<NotificationEngine> logger)
    {
        _context = context ?? throw new ArgumentNullException(nameof(context));
        _channelManager = channelManager ?? throw new ArgumentNullException(nameof(channelManager));
        _scheduler = scheduler ?? throw new ArgumentNullException(nameof(scheduler));
        _preferenceService = preferenceService ?? throw new ArgumentNullException(nameof(preferenceService));
        _logger = logger ?? throw new ArgumentNullException(nameof(logger));
    }

    public async Task<Guid> SendNotificationAsync(
        string title,
        string content,
        NotificationType type,
        NotificationPriority priority = NotificationPriority.Medium,
        NotificationChannel channel = NotificationChannel.InApp,
        string? recipientId = null,
        string? recipientEmail = null,
        string? recipientPhone = null,
        DateTime? scheduledAt = null,
        DateTime? expiresAt = null,
        NotificationMetadata? metadata = null,
        NotificationActions? actions = null,
        string? relatedEntityType = null,
        Guid? relatedEntityId = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            // Apply user preferences if recipient is specified
            if (!string.IsNullOrEmpty(recipientId))
            {
                var preferences = await _preferenceService.GetPreferencesAsync(recipientId, type, cancellationToken);
                if (preferences != null)
                {
                    if (!preferences.ShouldReceiveNotification(priority, scheduledAt ?? DateTime.UtcNow))
                    {
                        _logger.LogInformation("Notification blocked by user preferences for recipient {RecipientId}", recipientId);
                        return Guid.Empty;
                    }

                    channel = preferences.PreferredChannel;
                }
            }

            // Use smart scheduler to optimize timing
            var optimizedScheduledAt = scheduledAt ?? await _scheduler.GetOptimalDeliveryTimeAsync(
                recipientId, type, priority, channel, cancellationToken);

            var notification = new Notification(
                title, content, type, priority, channel,
                recipientId, recipientEmail, recipientPhone,
                optimizedScheduledAt, expiresAt, metadata, actions,
                relatedEntityType, relatedEntityId);

            _context.Notifications.Add(notification);
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Notification {NotificationId} created for recipient {RecipientId} scheduled at {ScheduledAt}",
                notification.Id, recipientId, optimizedScheduledAt);

            // If scheduled for immediate delivery, process it
            if (notification.IsScheduledForNow())
            {
                _ = Task.Run(async () => await DeliverNotificationAsync(notification.Id, CancellationToken.None));
            }

            return notification.Id;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send notification");
            throw;
        }
    }

    public async Task<Guid> SendNotificationFromTemplateAsync(
        Guid templateId,
        Dictionary<string, object> variables,
        string? recipientId = null,
        string? recipientEmail = null,
        string? recipientPhone = null,
        NotificationPriority? priority = null,
        NotificationChannel? channel = null,
        DateTime? scheduledAt = null,
        DateTime? expiresAt = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var template = await _context.NotificationTemplates
                .FirstOrDefaultAsync(t => t.Id == templateId && !t.IsDeleted, cancellationToken);

            if (template == null)
            {
                throw new InvalidOperationException($"Notification template {templateId} not found");
            }

            if (!template.IsActive)
            {
                throw new InvalidOperationException($"Notification template {templateId} is not active");
            }

            if (!template.ValidateVariables(variables))
            {
                throw new ArgumentException("Required template variables are missing");
            }

            var title = template.RenderTitle(variables);
            var content = template.RenderContent(variables);

            return await SendNotificationAsync(
                title, content, template.Type,
                priority ?? template.DefaultPriority,
                channel ?? template.DefaultChannel,
                recipientId, recipientEmail, recipientPhone,
                scheduledAt, expiresAt,
                template.Metadata?.ImageUrl != null ? new NotificationMetadata().WithImage(template.Metadata.ImageUrl) : null,
                template.DefaultActions,
                cancellationToken: cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send notification from template {TemplateId}", templateId);
            throw;
        }
    }

    public async Task<bool> CancelNotificationAsync(Guid notificationId, CancellationToken cancellationToken = default)
    {
        try
        {
            var notification = await _context.Notifications
                .FirstOrDefaultAsync(n => n.Id == notificationId && !n.IsDeleted, cancellationToken);

            if (notification == null) return false;

            if (notification.Status == NotificationStatus.Sent || notification.Status == NotificationStatus.Read)
            {
                return false; // Cannot cancel already sent notifications
            }

            notification.UpdateStatus(NotificationStatus.Cancelled);
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Notification {NotificationId} cancelled", notificationId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to cancel notification {NotificationId}", notificationId);
            return false;
        }
    }

    public async Task<bool> RescheduleNotificationAsync(Guid notificationId, DateTime newScheduledAt, CancellationToken cancellationToken = default)
    {
        try
        {
            var notification = await _context.Notifications
                .FirstOrDefaultAsync(n => n.Id == notificationId && !n.IsDeleted, cancellationToken);

            if (notification == null) return false;

            if (notification.Status != NotificationStatus.Pending && notification.Status != NotificationStatus.Scheduled)
            {
                return false; // Can only reschedule pending notifications
            }

            notification.Reschedule(newScheduledAt);
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Notification {NotificationId} rescheduled to {NewScheduledAt}", notificationId, newScheduledAt);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to reschedule notification {NotificationId}", notificationId);
            return false;
        }
    }

    public async Task<bool> MarkAsReadAsync(Guid notificationId, CancellationToken cancellationToken = default)
    {
        try
        {
            var notification = await _context.Notifications
                .FirstOrDefaultAsync(n => n.Id == notificationId && !n.IsDeleted, cancellationToken);

            if (notification == null) return false;

            notification.MarkAsRead();
            notification.AddInteraction(NotificationInteractionType.Viewed);
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Notification {NotificationId} marked as read", notificationId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to mark notification {NotificationId} as read", notificationId);
            return false;
        }
    }

    public async Task<List<Notification>> GetPendingNotificationsAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Notifications
            .Where(n => !n.IsDeleted && 
                       (n.Status == NotificationStatus.Pending || n.Status == NotificationStatus.Scheduled) &&
                       n.IsScheduledForNow() &&
                       !n.IsExpired())
            .OrderBy(n => n.Priority)
            .ThenBy(n => n.ScheduledAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<Notification>> GetNotificationsByRecipientAsync(string recipientId, int skip = 0, int take = 50, CancellationToken cancellationToken = default)
    {
        return await _context.Notifications
            .Where(n => !n.IsDeleted && n.RecipientId == recipientId)
            .OrderByDescending(n => n.CreatedAt)
            .Skip(skip)
            .Take(take)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<Notification>> GetNotificationsByTypeAsync(NotificationType type, int skip = 0, int take = 50, CancellationToken cancellationToken = default)
    {
        return await _context.Notifications
            .Where(n => !n.IsDeleted && n.Type == type)
            .OrderByDescending(n => n.CreatedAt)
            .Skip(skip)
            .Take(take)
            .ToListAsync(cancellationToken);
    }

    public async Task ProcessScheduledNotificationsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var pendingNotifications = await GetPendingNotificationsAsync(cancellationToken);

            _logger.LogInformation("Processing {Count} pending notifications", pendingNotifications.Count);

            var tasks = pendingNotifications.Select(async notification =>
            {
                try
                {
                    await DeliverNotificationAsync(notification.Id, cancellationToken);
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Failed to process notification {NotificationId}", notification.Id);
                }
            });

            await Task.WhenAll(tasks);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to process scheduled notifications");
            throw;
        }
    }

    public async Task RetryFailedNotificationsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var failedNotifications = await _context.Notifications
                .Where(n => !n.IsDeleted && 
                           n.Status == NotificationStatus.Failed && 
                           n.CanRetry() &&
                           !n.IsExpired())
                .ToListAsync(cancellationToken);

            _logger.LogInformation("Retrying {Count} failed notifications", failedNotifications.Count);

            foreach (var notification in failedNotifications)
            {
                await DeliverNotificationAsync(notification.Id, cancellationToken);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to retry failed notifications");
            throw;
        }
    }

    public async Task CleanupExpiredNotificationsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var expiredNotifications = await _context.Notifications
                .Where(n => !n.IsDeleted && n.IsExpired())
                .ToListAsync(cancellationToken);

            foreach (var notification in expiredNotifications)
            {
                notification.UpdateStatus(NotificationStatus.Expired);
            }

            if (expiredNotifications.Any())
            {
                await _context.SaveChangesAsync(cancellationToken);
                _logger.LogInformation("Marked {Count} notifications as expired", expiredNotifications.Count);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to cleanup expired notifications");
            throw;
        }
    }

    public async Task<NotificationDeliveryResult> DeliverNotificationAsync(Guid notificationId, CancellationToken cancellationToken = default)
    {
        try
        {
            var notification = await _context.Notifications
                .FirstOrDefaultAsync(n => n.Id == notificationId && !n.IsDeleted, cancellationToken);

            if (notification == null)
            {
                return new NotificationDeliveryResult(false, "Notification not found");
            }

            if (notification.IsExpired())
            {
                notification.UpdateStatus(NotificationStatus.Expired);
                await _context.SaveChangesAsync(cancellationToken);
                return new NotificationDeliveryResult(false, "Notification expired");
            }

            notification.UpdateStatus(NotificationStatus.Sending);
            await _context.SaveChangesAsync(cancellationToken);

            var startTime = DateTime.UtcNow;
            var channel = _channelManager.GetChannel(notification.Channel);
            var result = await channel.SendAsync(notification, cancellationToken);

            var duration = DateTime.UtcNow - startTime;
            notification.AddDeliveryAttempt(notification.Channel, result.Success, result.ErrorMessage);

            if (result.Success)
            {
                notification.UpdateStatus(NotificationStatus.Sent);
                _logger.LogInformation("Notification {NotificationId} delivered successfully via {Channel} in {Duration}ms",
                    notificationId, notification.Channel, duration.TotalMilliseconds);
            }
            else
            {
                notification.UpdateStatus(NotificationStatus.Failed, result.ErrorMessage);
                _logger.LogWarning("Notification {NotificationId} delivery failed via {Channel}: {Error}",
                    notificationId, notification.Channel, result.ErrorMessage);
            }

            await _context.SaveChangesAsync(cancellationToken);
            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to deliver notification {NotificationId}", notificationId);
            return new NotificationDeliveryResult(false, ex.Message);
        }
    }

    public async Task<Dictionary<NotificationChannel, bool>> TestChannelsAsync(CancellationToken cancellationToken = default)
    {
        var results = new Dictionary<NotificationChannel, bool>();
        var testNotification = new Notification(
            "Test Notification",
            "This is a test notification to verify channel connectivity.",
            NotificationType.System,
            NotificationPriority.Low,
            NotificationChannel.InApp);

        foreach (var channelType in Enum.GetValues<NotificationChannel>())
        {
            try
            {
                var channel = _channelManager.GetChannel(channelType);
                var result = await channel.TestAsync(cancellationToken);
                results[channelType] = result;
            }
            catch
            {
                results[channelType] = false;
            }
        }

        return results;
    }
}

public class NotificationDeliveryResult
{
    public bool Success { get; }
    public string? ErrorMessage { get; }
    public Dictionary<string, object> Metadata { get; }

    public NotificationDeliveryResult(bool success, string? errorMessage = null, Dictionary<string, object>? metadata = null)
    {
        Success = success;
        ErrorMessage = errorMessage;
        Metadata = metadata ?? new Dictionary<string, object>();
    }
}