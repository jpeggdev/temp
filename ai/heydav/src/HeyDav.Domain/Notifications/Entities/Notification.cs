using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Notifications.Enums;
using HeyDav.Domain.Notifications.ValueObjects;

namespace HeyDav.Domain.Notifications.Entities;

public class Notification : BaseEntity
{
    public string Title { get; private set; } = string.Empty;
    public string Content { get; private set; } = string.Empty;
    public NotificationType Type { get; private set; }
    public NotificationPriority Priority { get; private set; }
    public NotificationStatus Status { get; private set; }
    public NotificationChannel Channel { get; private set; }
    
    public DateTime? ScheduledAt { get; private set; }
    public DateTime? SentAt { get; private set; }
    public DateTime? ReadAt { get; private set; }
    public DateTime? ExpiresAt { get; private set; }
    
    public string? RecipientId { get; private set; }
    public string? RecipientEmail { get; private set; }
    public string? RecipientPhone { get; private set; }
    
    public NotificationMetadata Metadata { get; private set; } = new();
    public NotificationActions Actions { get; private set; } = new();
    
    public string? RelatedEntityType { get; private set; }
    public Guid? RelatedEntityId { get; private set; }
    
    public int RetryCount { get; private set; } = 0;
    public int MaxRetries { get; private set; } = 3;
    public string? ErrorMessage { get; private set; }
    
    public List<NotificationDeliveryAttempt> DeliveryAttempts { get; private set; } = new();
    public List<NotificationInteraction> Interactions { get; private set; } = new();

    private Notification() { } // For EF Core

    public Notification(
        string title,
        string content,
        NotificationType type,
        NotificationPriority priority,
        NotificationChannel channel,
        string? recipientId = null,
        string? recipientEmail = null,
        string? recipientPhone = null,
        DateTime? scheduledAt = null,
        DateTime? expiresAt = null,
        NotificationMetadata? metadata = null,
        NotificationActions? actions = null,
        string? relatedEntityType = null,
        Guid? relatedEntityId = null)
    {
        Title = title ?? throw new ArgumentNullException(nameof(title));
        Content = content ?? throw new ArgumentNullException(nameof(content));
        Type = type;
        Priority = priority;
        Channel = channel;
        Status = NotificationStatus.Pending;
        RecipientId = recipientId;
        RecipientEmail = recipientEmail;
        RecipientPhone = recipientPhone;
        ScheduledAt = scheduledAt ?? DateTime.UtcNow;
        ExpiresAt = expiresAt;
        Metadata = metadata ?? new NotificationMetadata();
        Actions = actions ?? new NotificationActions();
        RelatedEntityType = relatedEntityType;
        RelatedEntityId = relatedEntityId;
    }

    public void UpdateStatus(NotificationStatus status, string? errorMessage = null)
    {
        Status = status;
        ErrorMessage = errorMessage;
        
        if (status == NotificationStatus.Sent)
        {
            SentAt = DateTime.UtcNow;
        }
        else if (status == NotificationStatus.Read)
        {
            ReadAt = DateTime.UtcNow;
        }
        
        UpdateTimestamp();
    }

    public void MarkAsRead()
    {
        if (Status == NotificationStatus.Sent)
        {
            Status = NotificationStatus.Read;
            ReadAt = DateTime.UtcNow;
            UpdateTimestamp();
        }
    }

    public void Schedule(DateTime scheduledAt)
    {
        if (Status == NotificationStatus.Pending)
        {
            ScheduledAt = scheduledAt;
            UpdateTimestamp();
        }
    }

    public void Reschedule(DateTime newScheduledAt)
    {
        if (Status == NotificationStatus.Pending || Status == NotificationStatus.Failed)
        {
            ScheduledAt = newScheduledAt;
            Status = NotificationStatus.Pending;
            RetryCount++;
            UpdateTimestamp();
        }
    }

    public bool CanRetry()
    {
        return RetryCount < MaxRetries && 
               (Status == NotificationStatus.Failed || Status == NotificationStatus.Pending);
    }

    public bool IsExpired()
    {
        return ExpiresAt.HasValue && DateTime.UtcNow > ExpiresAt.Value;
    }

    public bool IsScheduledForNow()
    {
        return ScheduledAt.HasValue && DateTime.UtcNow >= ScheduledAt.Value;
    }

    public void AddDeliveryAttempt(NotificationChannel channel, bool success, string? errorMessage = null)
    {
        var attempt = new NotificationDeliveryAttempt(channel, success, errorMessage);
        DeliveryAttempts.Add(attempt);
        
        if (!success)
        {
            RetryCount++;
        }
        
        UpdateTimestamp();
    }

    public void AddInteraction(NotificationInteractionType interactionType, string? data = null)
    {
        var interaction = new NotificationInteraction(interactionType, data);
        Interactions.Add(interaction);
        UpdateTimestamp();
    }

    public void UpdateContent(string title, string content)
    {
        Title = title ?? throw new ArgumentNullException(nameof(title));
        Content = content ?? throw new ArgumentNullException(nameof(content));
        UpdateTimestamp();
    }

    public void UpdateMetadata(NotificationMetadata metadata)
    {
        Metadata = metadata ?? throw new ArgumentNullException(nameof(metadata));
        UpdateTimestamp();
    }

    public void UpdateActions(NotificationActions actions)
    {
        Actions = actions ?? throw new ArgumentNullException(nameof(actions));
        UpdateTimestamp();
    }
}