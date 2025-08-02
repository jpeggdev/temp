using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Notifications.Enums;

namespace HeyDav.Domain.Notifications.Entities;

public class NotificationInteraction : BaseEntity
{
    public Guid NotificationId { get; private set; }
    public NotificationInteractionType InteractionType { get; private set; }
    public DateTime InteractedAt { get; private set; }
    public string? Data { get; private set; }
    public string? UserAgent { get; private set; }
    public string? IpAddress { get; private set; }

    private NotificationInteraction() { } // For EF Core

    public NotificationInteraction(
        NotificationInteractionType interactionType,
        string? data = null,
        string? userAgent = null,
        string? ipAddress = null)
    {
        InteractionType = interactionType;
        Data = data;
        UserAgent = userAgent;
        IpAddress = ipAddress;
        InteractedAt = DateTime.UtcNow;
    }

    public NotificationInteraction(
        Guid notificationId,
        NotificationInteractionType interactionType,
        string? data = null,
        string? userAgent = null,
        string? ipAddress = null) : this(interactionType, data, userAgent, ipAddress)
    {
        NotificationId = notificationId;
    }
}