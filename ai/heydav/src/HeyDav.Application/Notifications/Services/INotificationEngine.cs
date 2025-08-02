using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.Notifications.Entities;
using HeyDav.Domain.Notifications.Enums;
using HeyDav.Domain.Notifications.ValueObjects;

namespace HeyDav.Application.Notifications.Services;

public interface INotificationEngine
{
    Task<Guid> SendNotificationAsync(
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
        CancellationToken cancellationToken = default);

    Task<Guid> SendNotificationFromTemplateAsync(
        Guid templateId,
        Dictionary<string, object> variables,
        string? recipientId = null,
        string? recipientEmail = null,
        string? recipientPhone = null,
        NotificationPriority? priority = null,
        NotificationChannel? channel = null,
        DateTime? scheduledAt = null,
        DateTime? expiresAt = null,
        CancellationToken cancellationToken = default);

    Task<bool> CancelNotificationAsync(Guid notificationId, CancellationToken cancellationToken = default);
    Task<bool> RescheduleNotificationAsync(Guid notificationId, DateTime newScheduledAt, CancellationToken cancellationToken = default);
    Task<bool> MarkAsReadAsync(Guid notificationId, CancellationToken cancellationToken = default);

    Task<List<Notification>> GetPendingNotificationsAsync(CancellationToken cancellationToken = default);
    Task<List<Notification>> GetNotificationsByRecipientAsync(string recipientId, int skip = 0, int take = 50, CancellationToken cancellationToken = default);
    Task<List<Notification>> GetNotificationsByTypeAsync(NotificationType type, int skip = 0, int take = 50, CancellationToken cancellationToken = default);

    Task ProcessScheduledNotificationsAsync(CancellationToken cancellationToken = default);
    Task RetryFailedNotificationsAsync(CancellationToken cancellationToken = default);
    Task CleanupExpiredNotificationsAsync(CancellationToken cancellationToken = default);

    Task<NotificationDeliveryResult> DeliverNotificationAsync(Guid notificationId, CancellationToken cancellationToken = default);
    Task<Dictionary<NotificationChannel, bool>> TestChannelsAsync(CancellationToken cancellationToken = default);
}