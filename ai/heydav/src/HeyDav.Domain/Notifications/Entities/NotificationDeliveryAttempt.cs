using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Notifications.Enums;

namespace HeyDav.Domain.Notifications.Entities;

public class NotificationDeliveryAttempt : BaseEntity
{
    public Guid NotificationId { get; private set; }
    public NotificationChannel Channel { get; private set; }
    public bool Success { get; private set; }
    public string? ErrorMessage { get; private set; }
    public DateTime AttemptedAt { get; private set; }
    public TimeSpan Duration { get; private set; }
    public string? ResponseData { get; private set; }

    private NotificationDeliveryAttempt() { } // For EF Core

    public NotificationDeliveryAttempt(
        NotificationChannel channel,
        bool success,
        string? errorMessage = null,
        string? responseData = null)
    {
        Channel = channel;
        Success = success;
        ErrorMessage = errorMessage;
        ResponseData = responseData;
        AttemptedAt = DateTime.UtcNow;
        Duration = TimeSpan.Zero;
    }

    public NotificationDeliveryAttempt(
        Guid notificationId,
        NotificationChannel channel,
        bool success,
        string? errorMessage = null,
        string? responseData = null) : this(channel, success, errorMessage, responseData)
    {
        NotificationId = notificationId;
    }

    public void SetDuration(TimeSpan duration)
    {
        Duration = duration;
        UpdateTimestamp();
    }

    public void UpdateResult(bool success, string? errorMessage = null, string? responseData = null)
    {
        Success = success;
        ErrorMessage = errorMessage;
        ResponseData = responseData;
        UpdateTimestamp();
    }
}